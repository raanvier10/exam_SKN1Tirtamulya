import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:webview_flutter/webview_flutter.dart';
import '../api_service.dart';

class ExamScreen extends StatefulWidget {
  final dynamic exam;
  final double? latitude;
  final double? longitude;

  const ExamScreen({
    super.key,
    required this.exam,
    this.latitude,
    this.longitude,
  });

  @override
  State<ExamScreen> createState() => _ExamScreenState();
}

class _ExamScreenState extends State<ExamScreen> with WidgetsBindingObserver {
  static const platform = MethodChannel('exam.bro/secure');
  late final WebViewController _controller;
  bool _isLoading = true;
  int _violationCount = 0;
  bool _isLocked = false;
  int? _sessionId;
  Timer? _examTimer;
  late DateTime _endTime;

  bool _wasAway = false;
  DateTime? _awayStartTime;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);

    // Listener deteksi Jendela Mengambang (Floating Window) & Split-Screen dari Native Android
    platform.setMethodCallHandler((call) async {
      if (call.method == 'onWindowFocusLost') {
        final reason = call.arguments?.toString() ?? '';
        final desc = reason == 'MULTI_WINDOW'
            ? 'Terdeteksi mengaktifkan mode Layar Belah (Split-Screen)'
            : 'Terdeteksi membuka Jendela Mengambang (Floating Window / Smart Sidebar)';
        _handleViolation('FLOATING_WINDOW', desc);
      }
    });

    // 1. Kiosk Mode Immersive: Lock status bar & nav bar
    SystemChrome.setEnabledSystemUIMode(SystemUiMode.immersiveSticky);

    // 2. Lock screen capture / screenshot & Native App Pinning
    _secureScreen();

    // 3. Setup end time & live countdown timer
    _endTime = DateTime.parse(widget.exam['end_at']).toLocal();
    _examTimer = Timer.periodic(const Duration(seconds: 1), (timer) {
      if (mounted) {
        setState(() {});
        if (timer.tick % 3 == 0 && !_isLoading) {
          _injectAntiCopyProtection();
        }
        if (DateTime.now().isAfter(_endTime) && !_isLocked) {
          _handleTimeExpired();
        }
      }
    });

    _controller = WebViewController()
      ..setJavaScriptMode(JavaScriptMode.unrestricted)
      ..setNavigationDelegate(
        NavigationDelegate(
          onPageStarted: (String url) {
            if (mounted) setState(() => _isLoading = true);
          },
          onPageFinished: (String url) {
            if (mounted) setState(() => _isLoading = false);
            _injectAntiCopyProtection();
          },
          onNavigationRequest: (NavigationRequest request) {
            final uri = Uri.tryParse(request.url);
            final host = uri?.host.toLowerCase() ?? '';
            final path = uri?.path.toLowerCase() ?? '';

            // Explicitly block YouTube and Google Search
            if (host.contains('youtube.com') ||
                host.contains('youtu.be') ||
                path.startsWith('/search')) {
              return NavigationDecision.prevent;
            }

            // Whitelist Google Forms, Google Auth, Google Drive assets, and static assets
            bool isAllowed = (host == 'docs.google.com' &&
                    (path.contains('/forms') || path.contains('/document'))) ||
                host == 'accounts.google.com' ||
                host == 'drive.google.com' ||
                host.endsWith('gstatic.com') ||
                host.endsWith('googleusercontent.com') ||
                host.endsWith('googleapis.com');

            if (!isAllowed) {
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(
                  content: Text('Akses ke situs di luar ujian diblokir.'),
                  backgroundColor: Color(0xFFEF4444),
                  duration: Duration(seconds: 2),
                  behavior: SnackBarBehavior.floating,
                ),
              );
              return NavigationDecision.prevent;
            }
            return NavigationDecision.navigate;
          },
        ),
      );

    _startExamSession();
  }

  Future<void> _secureScreen() async {
    try {
      await platform.invokeMethod('secureScreen');
    } catch (e) {
      // Ignored
    }
  }

  Future<void> _injectAntiCopyProtection() async {
    const jsCode = """
      (function() {
        function enforceLock() {
          try {
            if (!document.getElementById('exambro-lock-style')) {
              const style = document.createElement('style');
              style.id = 'exambro-lock-style';
              style.innerHTML = `
                *, *::before, *::after {
                  -webkit-touch-callout: none !important;
                  -webkit-user-select: none !important;
                  -khtml-user-select: none !important;
                  -moz-user-select: none !important;
                  -ms-user-select: none !important;
                  user-select: none !important;
                  -webkit-user-drag: none !important;
                  user-drag: none !important;
                }
                input, textarea, [contenteditable="true"] {
                  -webkit-user-select: text !important;
                  user-select: text !important;
                }
              `;
              (document.head || document.documentElement).appendChild(style);
            }

            const active = document.activeElement;
            if (!active || (active.tagName !== 'INPUT' && active.tagName !== 'TEXTAREA')) {
              const sel = window.getSelection();
              if (sel && sel.rangeCount > 0) {
                sel.removeAllRanges();
              }
            }
          } catch(e) {}
        }

        enforceLock();

        if (!window._exambroObserverSet) {
          window._exambroObserverSet = true;

          try {
            const observer = new MutationObserver(function() {
              enforceLock();
            });
            if (document.documentElement) {
              observer.observe(document.documentElement, {
                childList: true,
                subtree: true,
                attributes: true
              });
            }
          } catch(e) {}

          ['contextmenu', 'copy', 'cut', 'drag', 'dragstart', 'dragend', 'drop'].forEach(function(evt) {
            document.addEventListener(evt, function(e) {
              const tag = (e.target && e.target.tagName) ? e.target.tagName.toUpperCase() : '';
              if (tag !== 'INPUT' && tag !== 'TEXTAREA') {
                e.preventDefault();
                e.stopPropagation();
                return false;
              }
            }, true);
          });

          document.addEventListener('selectionchange', function(e) {
            const active = document.activeElement;
            if (!active || (active.tagName !== 'INPUT' && active.tagName !== 'TEXTAREA')) {
              const sel = window.getSelection();
              if (sel && !sel.isCollapsed) {
                sel.removeAllRanges();
              }
            }
          }, true);

          document.addEventListener('selectstart', function(e) {
            const tag = (e.target && e.target.tagName) ? e.target.tagName.toUpperCase() : '';
            if (tag !== 'INPUT' && tag !== 'TEXTAREA') {
              e.preventDefault();
              return false;
            }
          }, true);
        }
      })();
    """;
    try {
      await _controller.runJavaScript(jsCode);
    } catch (e) {
      // Ignored
    }
  }

  Future<void> _startExamSession() async {
    try {
      final examId = int.tryParse(widget.exam['id']?.toString() ?? '0') ?? 0;
      final response = await ApiService.startExamSession(
        examId,
        'device_dummy_id',
        latitude: widget.latitude,
        longitude: widget.longitude,
      );
      if (response['success'] == true) {
        _sessionId = response['data']?['id'] != null
            ? int.tryParse(response['data']['id'].toString())
            : null;
        final formUrl = response['data']?['google_form_url']?.toString() ??
            widget.exam['google_form_url']?.toString();
        if (formUrl != null && formUrl.isNotEmpty) {
          _controller.loadRequest(Uri.parse(formUrl));
        }
      } else if (response['locked'] == true) {
        if (!mounted) return;
        _lockExamPermanently();
      } else {
        if (!mounted) return;
        final errorMsg = response['message'] ?? 'Gagal memulai sesi ujian.';
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(errorMsg),
            backgroundColor: const Color(0xFFEF4444),
            duration: const Duration(seconds: 4),
            behavior: SnackBarBehavior.floating,
          ),
        );
        Navigator.pop(context);
      }
    } catch (e) {
      if (widget.exam['google_form_url'] != null) {
        _controller.loadRequest(Uri.parse(widget.exam['google_form_url']));
      }
    }
  }

  void _lockExamPermanently() {
    if (!mounted || _isLocked) return;
    setState(() {
      _isLocked = true;
    });

    final int maxViolations = int.tryParse(widget.exam['max_violation']?.toString() ?? '3') ?? 3;

    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (dialogContext) => PopScope(
        canPop: false,
        child: Dialog(
          backgroundColor: const Color(0xFF0B132B),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(24),
            side: const BorderSide(color: Color(0xFFEF4444), width: 1.5),
          ),
          child: Padding(
            padding: const EdgeInsets.all(24.0),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Container(
                  width: 70,
                  height: 70,
                  decoration: BoxDecoration(
                    color: const Color(0xFFEF4444).withValues(alpha: 0.15),
                    shape: BoxShape.circle,
                    border: Border.all(color: const Color(0xFFEF4444).withValues(alpha: 0.4), width: 2),
                  ),
                  child: const Icon(Icons.lock_rounded, color: Color(0xFFF87171), size: 36),
                ),
                const SizedBox(height: 18),
                const Text(
                  'UJIAN TELAH DIKUNCI',
                  textAlign: TextAlign.center,
                  style: TextStyle(
                    color: Color(0xFFFCA5A5),
                    fontWeight: FontWeight.w900,
                    fontSize: 18,
                    letterSpacing: 0.5,
                  ),
                ),
                const SizedBox(height: 8),
                Text(
                  'Anda telah mencapai batas maksimum ($maxViolations/$maxViolations) pelanggaran keamanan. Sistem telah mengunci sesi ujian Anda.',
                  textAlign: TextAlign.center,
                  style: const TextStyle(color: Color(0xFF94A3B8), fontSize: 13, height: 1.45),
                ),
                const SizedBox(height: 16),
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                  decoration: BoxDecoration(
                    color: const Color(0xFF1C2541),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: const Color(0xFF3A506B)),
                  ),
                  child: const Row(
                    children: [
                      Icon(Icons.info_outline_rounded, size: 16, color: Color(0xFF38BDF8)),
                      SizedBox(width: 10),
                      Expanded(
                        child: Text(
                          'Hubungi Pengawas / Guru IT untuk membuka kunci.',
                          style: TextStyle(color: Color(0xFFE2E8F0), fontSize: 12, fontWeight: FontWeight.w600),
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 22),
                SizedBox(
                  width: double.infinity,
                  height: 48,
                  child: ElevatedButton(
                    onPressed: () {
                      Navigator.pop(dialogContext);
                      Navigator.pop(context, 'locked');
                    },
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFFEF4444),
                      foregroundColor: Colors.white,
                      elevation: 0,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    ),
                    child: const Text('KEMBALI KE PORTAL', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 13.5)),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    _examTimer?.cancel();
    SystemChrome.setEnabledSystemUIMode(SystemUiMode.edgeToEdge);
    try {
      platform.invokeMethod('clearSecureScreen');
    } catch (e) {
      // Ignored
    }
    super.dispose();
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (state == AppLifecycleState.inactive ||
        state == AppLifecycleState.paused ||
        state == AppLifecycleState.hidden) {
      _wasAway = true;
      _awayStartTime = DateTime.now();
    } else if (state == AppLifecycleState.resumed) {
      if (_wasAway) {
        _wasAway = false;
        final secondsAway = _awayStartTime != null
            ? DateTime.now().difference(_awayStartTime!).inSeconds
            : 1;
        _handleViolation(
          'FLOATING_WINDOW',
          'Terdeteksi membuka jendela mengambang / keluar aplikasi ($secondsAway detik)',
        );
      }
    }
  }

  void _handleTimeExpired() {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (dialogContext) => Dialog(
        backgroundColor: const Color(0xFF0F172A),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(24),
          side: const BorderSide(color: Color(0xFF334155), width: 1.2),
        ),
        child: Padding(
          padding: const EdgeInsets.all(24.0),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                width: 64,
                height: 64,
                decoration: BoxDecoration(
                  color: const Color(0xFFD97706).withValues(alpha: 0.15),
                  shape: BoxShape.circle,
                  border: Border.all(color: const Color(0xFFD97706).withValues(alpha: 0.3)),
                ),
                child: const Icon(Icons.timer_off_rounded, color: Color(0xFFFBBF24), size: 32),
              ),
              const SizedBox(height: 18),
              const Text(
                'Waktu Ujian Telah Habis',
                textAlign: TextAlign.center,
                style: TextStyle(
                  color: Colors.white,
                  fontWeight: FontWeight.w800,
                  fontSize: 18,
                  letterSpacing: -0.4,
                ),
              ),
              const SizedBox(height: 8),
              const Text(
                'Durasi pengerjaan ujian telah selesai. Sesi Anda akan otomatis diakhiri dan jawaban tersimpan.',
                textAlign: TextAlign.center,
                style: TextStyle(color: Color(0xFF94A3B8), fontSize: 13, height: 1.4),
              ),
              const SizedBox(height: 24),
              SizedBox(
                width: double.infinity,
                height: 46,
                child: ElevatedButton(
                  onPressed: () async {
                    Navigator.pop(dialogContext);
                    await ApiService.finishExamSession(widget.exam['id']);
                    if (!mounted) return;
                    Navigator.pop(context);
                  },
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF2563EB),
                    foregroundColor: Colors.white,
                    elevation: 0,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                  child: const Text('KEMBALI KE PORTAL', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 13.5)),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Future<void> _handleViolation(String type, String desc) async {
    if (_isLocked) return;

    HapticFeedback.heavyImpact();

    final int maxViolations = int.tryParse(widget.exam['max_violation']?.toString() ?? '3') ?? 3;
    final int examId = int.tryParse(widget.exam['id']?.toString() ?? '0') ?? 0;

    setState(() {
      _violationCount++;
    });

    if (_sessionId != null) {
      ApiService.reportViolation(examId, _sessionId!, type, desc).then((res) {
        if (res['locked'] == true && !_isLocked) {
          _lockExamPermanently();
        }
      }).catchError((_) {});
    }

    if (!mounted) return;

    if (_violationCount >= maxViolations) {
      _lockExamPermanently();
    } else {
      final int remainingChances = maxViolations - _violationCount;
      showDialog(
        context: context,
        barrierDismissible: false,
        builder: (dialogContext) => Dialog(
          backgroundColor: const Color(0xFF0F172A),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(24),
            side: BorderSide(
              color: const Color(0xFFEF4444).withValues(alpha: 0.4),
              width: 1.5,
            ),
          ),
          child: Padding(
            padding: const EdgeInsets.all(22.0),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Container(
                  width: 60,
                  height: 60,
                  decoration: BoxDecoration(
                    color: const Color(0xFFEF4444).withValues(alpha: 0.12),
                    shape: BoxShape.circle,
                    border: Border.all(color: const Color(0xFFEF4444).withValues(alpha: 0.3), width: 1.5),
                  ),
                  child: const Icon(
                    Icons.security_update_warning_rounded,
                    color: Color(0xFFF87171),
                    size: 30,
                  ),
                ),
                const SizedBox(height: 16),
                const Text(
                  'PELANGGARAN TERDETEKSI',
                  textAlign: TextAlign.center,
                  style: TextStyle(
                    color: Colors.white,
                    fontWeight: FontWeight.w900,
                    fontSize: 16.5,
                    letterSpacing: 0.3,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  desc,
                  textAlign: TextAlign.center,
                  style: const TextStyle(
                    color: Color(0xFFFCA5A5),
                    fontSize: 12.5,
                    fontWeight: FontWeight.w600,
                  ),
                ),
                const SizedBox(height: 16),
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: const Color(0xFF1E293B),
                    borderRadius: BorderRadius.circular(14),
                    border: Border.all(color: const Color(0xFF334155)),
                  ),
                  child: Column(
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          const Text(
                            'Status Toleransi:',
                            style: TextStyle(color: Color(0xFF94A3B8), fontSize: 12, fontWeight: FontWeight.w600),
                          ),
                          Text(
                            '$_violationCount dari $maxViolations kali',
                            style: const TextStyle(color: Color(0xFFF87171), fontSize: 12, fontWeight: FontWeight.w800),
                          ),
                        ],
                      ),
                      const SizedBox(height: 10),
                      Row(
                        children: List.generate(maxViolations, (index) {
                          bool isFilled = index < _violationCount;
                          return Expanded(
                            child: Container(
                              height: 6,
                              margin: EdgeInsets.only(right: index < maxViolations - 1 ? 6 : 0),
                              decoration: BoxDecoration(
                                color: isFilled ? const Color(0xFFEF4444) : const Color(0xFF334155),
                                borderRadius: BorderRadius.circular(4),
                              ),
                            ),
                          );
                        }),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 14),
                Text(
                  'Tersisa $remainingChances kesempatan sebelum ujian otomatis dikunci oleh sistem.',
                  textAlign: TextAlign.center,
                  style: const TextStyle(
                    color: Color(0xFF94A3B8),
                    fontSize: 12,
                    height: 1.35,
                  ),
                ),
                const SizedBox(height: 20),
                SizedBox(
                  width: double.infinity,
                  height: 46,
                  child: ElevatedButton(
                    onPressed: () => Navigator.pop(dialogContext),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFFEF4444),
                      foregroundColor: Colors.white,
                      elevation: 0,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    ),
                    child: const Text(
                      'LANJUTKAN UJIAN',
                      style: TextStyle(fontWeight: FontWeight.w800, fontSize: 13, letterSpacing: 0.3),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),
      );
    }
  }

  Future<void> _onTapFinishExam() async {
    final now = DateTime.now();
    final remainingDiff = _endTime.difference(now);
    final duration = widget.exam['duration'] ?? 60;

    // Aturan 10 Menit Terakhir (hanya bisa selesai di 10 menit terakhir ujian)
    bool canFinish = (duration <= 10) || (remainingDiff.inMinutes < 10) || (remainingDiff.inSeconds <= 600);

    if (!canFinish) {
      int minutesToWait = remainingDiff.inMinutes - 9;
      showDialog(
        context: context,
        builder: (dialogContext) => Dialog(
          backgroundColor: const Color(0xFF0F172A),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(24),
            side: const BorderSide(color: Color(0xFF334155), width: 1.2),
          ),
          child: Padding(
            padding: const EdgeInsets.all(22.0),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Container(
                  width: 56,
                  height: 56,
                  decoration: BoxDecoration(
                    color: const Color(0xFFD97706).withValues(alpha: 0.15),
                    shape: BoxShape.circle,
                    border: Border.all(color: const Color(0xFFD97706).withValues(alpha: 0.3)),
                  ),
                  child: const Icon(Icons.lock_clock_rounded, color: Color(0xFFFBBF24), size: 28),
                ),
                const SizedBox(height: 16),
                const Text(
                  'Tombol Belum Dapat Digunakan',
                  textAlign: TextAlign.center,
                  style: TextStyle(
                    color: Colors.white,
                    fontWeight: FontWeight.w800,
                    fontSize: 16.5,
                    letterSpacing: -0.3,
                  ),
                ),
                const SizedBox(height: 8),
                Text(
                  'Sesuai tata tertib, tombol "Selesai" baru dapat diakses pada 10 menit terakhir sebelum ujian berakhir. Silakan periksa kembali jawaban Anda.',
                  textAlign: TextAlign.center,
                  style: const TextStyle(color: Color(0xFF94A3B8), fontSize: 12.5, height: 1.4),
                ),
                const SizedBox(height: 14),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                  decoration: BoxDecoration(
                    color: const Color(0xFF1E293B),
                    borderRadius: BorderRadius.circular(10),
                    border: Border.all(color: const Color(0xFF334155)),
                  ),
                  child: Text(
                    'Dapat diakses dalam ~$minutesToWait menit lagi',
                    style: const TextStyle(color: Color(0xFF38BDF8), fontSize: 12, fontWeight: FontWeight.w700),
                  ),
                ),
                const SizedBox(height: 20),
                SizedBox(
                  width: double.infinity,
                  height: 44,
                  child: ElevatedButton(
                    onPressed: () => Navigator.pop(dialogContext),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF2563EB),
                      foregroundColor: Colors.white,
                      elevation: 0,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                    ),
                    child: const Text('KEMBALI MENGERJAKAN', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 12.5)),
                  ),
                ),
              ],
            ),
          ),
        ),
      );
      return;
    }

    // 1. Cek status Google Form & otomatis klik Kirim jika belum dikirim
    String formState = 'UNKNOWN';
    try {
      final result = await _controller.runJavaScriptReturningResult("""
        (function() {
          const url = window.location.href.toLowerCase();
          const bodyText = document.body ? document.body.innerText.toLowerCase() : '';
          
          if (url.includes('formresponse') ||
              bodyText.includes('tanggapan anda telah direkam') ||
              bodyText.includes('your response has been recorded') ||
              bodyText.includes('jawaban anda telah direkam')) {
            return 'ALREADY_SUBMITTED';
          }

          const buttons = Array.from(document.querySelectorAll('div[role="button"], button, [role="button"]'));
          const submitBtn = buttons.find(b => {
            const t = (b.innerText || '').trim().toLowerCase();
            return t === 'kirim' || t === 'submit' || t === 'kirimkan' || t === 'send';
          });

          if (submitBtn) {
            submitBtn.click();
            return 'AUTO_CLICKED';
          }

          return 'NOT_SUBMITTED';
        })();
      """);
      formState = result.toString().replaceAll('"', '');
    } catch (e) {
      // Ignored
    }

    if (formState == 'AUTO_CLICKED') {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: const Row(
            children: [
              Icon(Icons.touch_app_rounded, color: Colors.white, size: 18),
              SizedBox(width: 8),
              Text('Otomatis menekan tombol "Kirim" di Google Form...'),
            ],
          ),
          backgroundColor: const Color(0xFF2563EB),
          duration: const Duration(seconds: 2),
          behavior: SnackBarBehavior.floating,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
        ),
      );
      await Future.delayed(const Duration(milliseconds: 1000));
    }

    if (!mounted) return;

    bool isAlreadySubmitted = formState == 'ALREADY_SUBMITTED';

    bool? confirm = await showDialog<bool>(
      context: context,
      builder: (dialogContext) => Dialog(
        backgroundColor: const Color(0xFF0F172A),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(24),
          side: const BorderSide(color: Color(0xFF334155), width: 1.2),
        ),
        child: Padding(
          padding: const EdgeInsets.all(22.0),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                width: 56,
                height: 56,
                decoration: BoxDecoration(
                  color: (isAlreadySubmitted ? const Color(0xFF10B981) : const Color(0xFF38BDF8)).withValues(alpha: 0.15),
                  shape: BoxShape.circle,
                  border: Border.all(
                    color: (isAlreadySubmitted ? const Color(0xFF10B981) : const Color(0xFF38BDF8)).withValues(alpha: 0.3),
                  ),
                ),
                child: Icon(
                  isAlreadySubmitted ? Icons.verified_rounded : Icons.check_circle_outline_rounded,
                  color: isAlreadySubmitted ? const Color(0xFF34D399) : const Color(0xFF38BDF8),
                  size: 28,
                ),
              ),
              const SizedBox(height: 16),
              Text(
                isAlreadySubmitted ? 'Jawaban Terkirim!' : 'Akhiri Sesi Ujian?',
                textAlign: TextAlign.center,
                style: const TextStyle(
                  color: Colors.white,
                  fontWeight: FontWeight.w800,
                  fontSize: 17,
                  letterSpacing: -0.3,
                ),
              ),
              const SizedBox(height: 8),
              Text(
                isAlreadySubmitted
                    ? 'Tanggapan Google Form Anda telah terekam di sistem. Tekan tombol di bawah untuk mengakhiri sesi.'
                    : 'Sistem sudah otomatis memicu tombol "Kirim". Pastikan semua soal wajib telah lengkap di Google Form.',
                textAlign: TextAlign.center,
                style: const TextStyle(color: Color(0xFF94A3B8), fontSize: 12.5, height: 1.4),
              ),
              const SizedBox(height: 22),
              Row(
                children: [
                  Expanded(
                    child: OutlinedButton(
                      onPressed: () => Navigator.pop(dialogContext, false),
                      style: OutlinedButton.styleFrom(
                        foregroundColor: const Color(0xFF94A3B8),
                        side: const BorderSide(color: Color(0xFF334155)),
                        padding: const EdgeInsets.symmetric(vertical: 12),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                      child: const Text('Periksa Lagi', style: TextStyle(fontWeight: FontWeight.w700)),
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: ElevatedButton(
                      onPressed: () => Navigator.pop(dialogContext, true),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF10B981),
                        foregroundColor: Colors.white,
                        elevation: 0,
                        padding: const EdgeInsets.symmetric(vertical: 12),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                      child: const Text('Akhiri Ujian', style: TextStyle(fontWeight: FontWeight.w800)),
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );

    if (confirm == true && mounted) {
      final int examId = int.tryParse(widget.exam['id']?.toString() ?? '0') ?? 0;
      await ApiService.finishExamSession(examId);
      if (!mounted) return;
      Navigator.pop(context);
    }
  }

  String _formatRemainingTime() {
    final now = DateTime.now();
    if (now.isAfter(_endTime)) return "00:00:00";
    final diff = _endTime.difference(now);
    final hours = diff.inHours.toString().padLeft(2, '0');
    final minutes = (diff.inMinutes % 60).toString().padLeft(2, '0');
    final seconds = (diff.inSeconds % 60).toString().padLeft(2, '0');
    return "$hours:$minutes:$seconds";
  }

  String _formatCurrentClock() {
    final now = DateTime.now();
    final h = now.hour.toString().padLeft(2, '0');
    final m = now.minute.toString().padLeft(2, '0');
    return "$h:$m WIB";
  }

  @override
  Widget build(BuildContext context) {
    final int maxViolations = int.tryParse(widget.exam['max_violation']?.toString() ?? '3') ?? 3;
    final now = DateTime.now();
    final remainingDiff = _endTime.difference(now);
    final isLowTime = remainingDiff.inMinutes < 5 && !now.isAfter(_endTime);
    final int duration = int.tryParse(widget.exam['duration']?.toString() ?? '60') ?? 60;
    final bool canFinish = (duration <= 10) || (remainingDiff.inMinutes < 10) || (remainingDiff.inSeconds <= 600);

    return PopScope(
      canPop: false,
      child: Scaffold(
        backgroundColor: const Color(0xFF071426),
        body: SafeArea(
          top: true,
          bottom: true,
          child: Column(
            children: [
              // 1. REFINED SLEEK TOP BAR (Fixed & Cockpit Aligned)
              Container(
                width: double.infinity,
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                decoration: const BoxDecoration(
                  color: Color(0xFF091728),
                  border: Border(
                    bottom: BorderSide(color: Color(0xFF1E293B), width: 1),
                  ),
                ),
                child: Row(
                  children: [
                    // Left: Subject & Violation Pill
                    Expanded(
                      flex: 4,
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Text(
                            widget.exam['title'] ?? 'Ujian',
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                            style: const TextStyle(
                              color: Colors.white,
                              fontWeight: FontWeight.w800,
                              fontSize: 14,
                              letterSpacing: -0.3,
                            ),
                          ),
                          const SizedBox(height: 2),
                          Row(
                            children: [
                              Text(
                                _formatCurrentClock(),
                                style: const TextStyle(
                                  color: Color(0xFF64748B),
                                  fontSize: 11,
                                  fontWeight: FontWeight.w600,
                                ),
                              ),
                              const SizedBox(width: 6),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 1),
                                decoration: BoxDecoration(
                                  color: _violationCount > 0
                                      ? const Color(0xFFEF4444).withValues(alpha: 0.2)
                                      : const Color(0xFF10B981).withValues(alpha: 0.15),
                                  borderRadius: BorderRadius.circular(4),
                                ),
                                child: Text(
                                  '$_violationCount/$maxViolations Viol.',
                                  style: TextStyle(
                                    color: _violationCount > 0 ? const Color(0xFFFCA5A5) : const Color(0xFF6EE7B7),
                                    fontSize: 10,
                                    fontWeight: FontWeight.w700,
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),

                    const SizedBox(width: 6),

                    // Center: Countdown Timer Box
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                      decoration: BoxDecoration(
                        color: isLowTime ? const Color(0xFFEF4444) : const Color(0xFF112238),
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(
                          color: isLowTime ? const Color(0xFFF87171) : const Color(0xFF233B5D),
                          width: 1,
                        ),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(
                            Icons.timer_outlined,
                            size: 13,
                            color: isLowTime ? Colors.white : const Color(0xFF38BDF8),
                          ),
                          const SizedBox(width: 5),
                          Text(
                            _formatRemainingTime(),
                            style: TextStyle(
                              color: isLowTime ? Colors.white : const Color(0xFFF1F5F9),
                              fontSize: 12.5,
                              fontWeight: FontWeight.w800,
                              letterSpacing: 0.4,
                            ),
                          ),
                        ],
                      ),
                    ),

                    const SizedBox(width: 8),

                    // Right: Selesai Button with Auto-Submit & 10-Minute Lock Rule
                    ElevatedButton.icon(
                      onPressed: _onTapFinishExam,
                      icon: Icon(
                        canFinish ? Icons.check_circle_rounded : Icons.lock_outline_rounded,
                        size: 13,
                      ),
                      label: Text(
                        'Selesai',
                        style: TextStyle(
                          fontWeight: FontWeight.w800,
                          fontSize: 12,
                          color: canFinish ? Colors.white : const Color(0xFF94A3B8),
                        ),
                      ),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: canFinish ? const Color(0xFF10B981) : const Color(0xFF1E293B),
                        foregroundColor: canFinish ? Colors.white : const Color(0xFF94A3B8),
                        elevation: 0,
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                        minimumSize: const Size(60, 32),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(8),
                          side: canFinish
                              ? BorderSide.none
                              : const BorderSide(color: Color(0xFF334155)),
                        ),
                      ),
                    ),
                  ],
                ),
              ),

              // 2. MAIN WEBVIEW BODY
              Expanded(
                child: _isLocked
                    ? Container(
                        color: const Color(0xFF0B132B),
                        padding: const EdgeInsets.all(32),
                        child: const Center(
                          child: Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Icon(Icons.lock_rounded, size: 64, color: Color(0xFFEF4444)),
                              SizedBox(height: 20),
                              Text(
                                'UJIAN DIKUNCI',
                                style: TextStyle(
                                  fontSize: 22,
                                  color: Color(0xFFF87171),
                                  fontWeight: FontWeight.w800,
                                  letterSpacing: -0.5,
                                ),
                              ),
                              SizedBox(height: 8),
                              Text(
                                'Toleransi batas pelanggaran telah habis.',
                                textAlign: TextAlign.center,
                                style: TextStyle(color: Color(0xFF94A3B8), fontSize: 14),
                              ),
                            ],
                          ),
                        ),
                      )
                    : Stack(
                        children: [
                          WebViewWidget(controller: _controller),
                          if (_isLoading)
                            const LinearProgressIndicator(
                              color: Color(0xFF38BDF8),
                              backgroundColor: Color(0xFF091728),
                              minHeight: 2.5,
                            ),
                        ],
                      ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
