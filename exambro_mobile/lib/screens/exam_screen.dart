import 'dart:async';
import 'dart:io' show Platform;
import 'package:flutter/foundation.dart';
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
  bool _timeExpiredHandled = false;
  int? _sessionId;
  String? _originalFormUrl;
  Timer? _examTimer;
  late DateTime _endTime;

  DateTime? _awayStartTime;
  DateTime? _entryTime;
  DateTime? _lastViolationTime;

  @override
  void initState() {
    super.initState();
    _entryTime = DateTime.now();
    WidgetsBinding.instance.addObserver(this);

    if (!kIsWeb) {
      // Listener deteksi Jendela Mengambang (Floating Window) & Split-Screen dari Native Android
      platform.setMethodCallHandler((call) async {
        if (call.method == 'onScreenOff') {
          _handleViolation(
            'SCREEN_OFF',
            'Layar HP dimatikan / dikunci saat ujian berlangsung',
          );
        } else if (call.method == 'onWindowFocusLost') {
          final reason = call.arguments?.toString() ?? '';
          final String violationType;
          final String desc;
          if (reason == 'MULTI_WINDOW') {
            violationType = 'SPLIT_SCREEN';
            desc = 'Terdeteksi mengaktifkan mode Layar Belah (Split-Screen)';
          } else if (reason == 'SCREEN_RECORDING') {
            violationType = 'SCREEN_RECORDING';
            desc = 'Terdeteksi perekaman layar atau proyeksi layar (Screen Recording / AirPlay)';
          } else {
            violationType = 'FLOATING_WINDOW';
            desc = 'Terdeteksi membuka Jendela Mengambang (Floating Window / Smart Sidebar)';
          }
          _handleViolation(violationType, desc);
        }
      });

      // 1. Kiosk Mode Immersive: Lock status bar & nav bar
      SystemChrome.setEnabledSystemUIMode(SystemUiMode.immersiveSticky);

      // 2. Lock screen capture / screenshot & Native App Pinning
      _secureScreen();
    }

    // 3. Setup end time & live countdown timer
    _endTime = DateTime.parse(widget.exam['end_at']).toLocal();
    _examTimer = Timer.periodic(const Duration(seconds: 1), (timer) {
      if (mounted) {
        setState(() {});
        if (DateTime.now().isAfter(_endTime) && !_isLocked && !_timeExpiredHandled) {
          timer.cancel();
          _handleTimeExpired();
        }
      }
    });

    if (!kIsWeb) {
      _controller = WebViewController()
        ..setJavaScriptMode(JavaScriptMode.unrestricted)
        ..setUserAgent(
          Platform.isIOS
              ? 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.5 Mobile/15E148 Safari/604.1'
              : 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Mobile Safari/537.36',
        )
        ..addJavaScriptChannel(
          'ExambroSecurity',
          onMessageReceived: (JavaScriptMessage message) {
            if (message.message == 'COPY_ATTEMPT') {
              _handleViolation(
                'UNAUTHORIZED_COPY',
                'Mencoba menyalin (copy) teks soal ujian',
              );
            }
          },
        )
        ..setNavigationDelegate(
          NavigationDelegate(
            onPageStarted: (String url) {
              if (mounted) setState(() => _isLoading = true);
            },
            onPageFinished: (String url) {
              if (mounted) setState(() => _isLoading = false);
              _injectAntiCopyProtection();
              try {
                platform.invokeMethod('flushCookies');
              } catch (_) {}
            },
            onWebResourceError: (WebResourceError error) {
              if (mounted) {
                setState(() => _isLoading = false);
                if (error.isForMainFrame ?? true) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(
                      content: Text('Koneksi terganggu. Tekan tombol refresh di kanan atas jika soal belum termuat.'),
                      backgroundColor: Color(0xFFD97706),
                      duration: Duration(seconds: 4),
                      behavior: SnackBarBehavior.floating,
                    ),
                  );
                }
              }
            },
            onNavigationRequest: (NavigationRequest request) {
              final uri = Uri.tryParse(request.url);
              final scheme = uri?.scheme.toLowerCase() ?? '';
              final host = uri?.host.toLowerCase() ?? '';
              final path = uri?.path.toLowerCase() ?? '';

              // Tolak skema non-HTTP(S) (mis. file://, content://, intent://, javascript:)
              if (scheme != 'http' && scheme != 'https') {
                return NavigationDecision.prevent;
              }

              // Blokir streaming video YouTube, AI assistant, pencarian, dan layanan non-ujian Google
              // CATATAN: accounts.youtube.com TIDAK diblokir karena digunakan Google SSO untuk SetSID sinkronisasi session
              const blockedGoogleHosts = <String>{
                'www.youtube.com',
                'm.youtube.com',
                'youtube.com',
                'youtu.be',
                'gemini.google.com',
                'bard.google.com',
                'ai.google.com',
                'aistudio.google.com',
                'notebooklm.google.com',
                'scholar.google.com',
                'translate.google.com',
                'mail.google.com',
                'chat.google.com',
                'classroom.google.com',
                'keep.google.com',
                'books.google.com',
                'sites.google.com',
                'news.google.com',
                'maps.google.com',
                'play.google.com',
                'search.google.com',
                'support.google.com',
              };

              final isSearch = path.startsWith('/search') ||
                  ((path == '/' || path.isEmpty) &&
                      (host == 'google.com' ||
                          host == 'www.google.com' ||
                          host == 'google.co.id' ||
                          host == 'www.google.co.id'));

              final isGoogleHost = host == 'google.com' ||
                  host == 'www.google.com' ||
                  host == 'google.co.id' ||
                  host == 'www.google.co.id';
              final isGoogleJunkPage = isGoogleHost &&
                  (path.startsWith('/webhp') ||
                   path.startsWith('/sorry') ||
                   path.startsWith('/settings') ||
                   path.startsWith('/preferences') ||
                   path.startsWith('/intl') ||
                   path.startsWith('/about'));

              final isDocsNonForm = host == 'docs.google.com' &&
                  (path.startsWith('/document') ||
                   path.startsWith('/spreadsheets') ||
                   path.startsWith('/presentation') ||
                   path.startsWith('/drawings'));

              if (blockedGoogleHosts.contains(host) || isSearch || isGoogleJunkPage || isDocsNonForm) {
                _handleViolation(
                  'UNAUTHORIZED_URL',
                  'Mencoba membuka layanan terlarang di luar soal ujian ($host${path.isNotEmpty ? path : ""})',
                );
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(
                    content: Text('Akses ke halaman atau layanan di luar form ujian diblokir.'),
                    backgroundColor: Color(0xFFEF4444),
                    duration: Duration(seconds: 2),
                    behavior: SnackBarBehavior.floating,
                  ),
                );
                // Auto-reload ke form URL asli jika terjebak di halaman Google
                if (_originalFormUrl != null) {
                  _controller.loadRequest(Uri.parse(_originalFormUrl!));
                }
                return NavigationDecision.prevent;
              }

              // Allow exam original target URL domain if configured
              final examUrl = widget.exam['google_form_url']?.toString();
              final examUri = examUrl != null ? Uri.tryParse(examUrl) : null;
              if (examUri != null &&
                  examUri.host.isNotEmpty &&
                  host == examUri.host.toLowerCase()) {
                return NavigationDecision.navigate;
              }

              // Whitelist ketat hanya host Google Forms, Google Auth, dan aset statis
              final isAllowed = host == 'forms.gle' ||
                  host.endsWith('.forms.gle') ||
                  host == 'forms.google.com' ||
                  host == 'docs.google.com' ||
                  host == 'accounts.google.com' ||
                  host == 'myaccount.google.com' ||
                  host == 'ogs.google.com' ||
                  host == 'apis.google.com' ||
                  host == 'content.googleapis.com' ||
                  host == 'accounts.youtube.com' ||
                  host.endsWith('.gstatic.com') ||
                  host == 'gstatic.com' ||
                  host.endsWith('.googleusercontent.com') ||
                  host == 'googleusercontent.com' ||
                  host.endsWith('.googleapis.com') ||
                  host == 'googleapis.com' ||
                  host.endsWith('.1e100.net') ||
                  host == '1e100.net';

              if (!isAllowed) {
                _handleViolation(
                  'UNAUTHORIZED_URL',
                  'Mencoba membuka tautan terlarang di luar ujian ($host)',
                );
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
        if (!document.getElementById('exambro-lock-style')) {
          const style = document.createElement('style');
          style.id = 'exambro-lock-style';
          style.innerHTML = `
            body {
              -webkit-touch-callout: none;
              -webkit-user-select: none;
              user-select: none;
            }
            input, textarea, [contenteditable="true"], [role="textbox"], .whsOnd {
              -webkit-user-select: text !important;
              user-select: text !important;
              -webkit-touch-callout: default !important;
            }
          `;
          (document.head || document.documentElement).appendChild(style);
        }

        if (!window._exambroProtectionSet) {
          window._exambroProtectionSet = true;

          // Cegah copy / cut hanya pada teks soal ujian (di luar input)
          ['copy', 'cut'].forEach(function(evt) {
            document.addEventListener(evt, function(e) {
              const active = document.activeElement;
              const isInput = active && (
                active.tagName === 'INPUT' ||
                active.tagName === 'TEXTAREA' ||
                active.isContentEditable ||
                active.getAttribute('role') === 'textbox'
              );
              if (!isInput) {
                e.preventDefault();
                if (e.clipboardData) {
                  e.clipboardData.clearData();
                }
                if (window.ExambroSecurity) {
                  window.ExambroSecurity.postMessage('COPY_ATTEMPT');
                }
              }
            }, true);
          });

          // Cegah contextmenu (long-press menu Android) pada teks soal, tetapi izinkan pada field input
          document.addEventListener('contextmenu', function(e) {
            const target = e.target;
            const isInput = target && (
              target.tagName === 'INPUT' ||
              target.tagName === 'TEXTAREA' ||
              target.isContentEditable ||
              target.getAttribute('role') === 'textbox' ||
              (target.closest && target.closest('input, textarea, [contenteditable="true"], [role="textbox"]') !== null)
            );
            if (!isInput) {
              e.preventDefault();
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
      final deviceId = await ApiService.getOrCreateDeviceId();
      final response = await ApiService.startExamSession(
        examId,
        deviceId,
        latitude: widget.latitude,
        longitude: widget.longitude,
      );
      if (response['success'] == true) {
        _sessionId = response['data']?['id'] != null
            ? int.tryParse(response['data']['id'].toString())
            : null;
        if (response['data']?['expired_at'] != null) {
          final serverExpiry = DateTime.tryParse(response['data']['expired_at'].toString())?.toLocal();
          if (serverExpiry != null && mounted) {
            setState(() {
              _endTime = serverExpiry;
            });
          }
        }
        if (response['data']?['violation_count'] != null) {
          final serverViolations = int.tryParse(response['data']['violation_count'].toString()) ?? 0;
          if (mounted) {
            setState(() {
              _violationCount = serverViolations;
            });
          }
        }
        if (response['data']?['resumed_violation'] == true && mounted) {
          WidgetsBinding.instance.addPostFrameCallback((_) {
            if (mounted) {
              _handleViolation(
                'APP_RESTART_OR_DEVICE_OFF',
                'Terdeteksi HP dimatikan atau aplikasi dimulai ulang saat ujian sedang berlangsung',
                reportToServer: false,
              );
            }
          });
        }
        String? formUrl = response['data']?['google_form_url']?.toString() ??
            widget.exam['google_form_url']?.toString();
        if (formUrl != null && formUrl.trim().isNotEmpty) {
          formUrl = formUrl.trim();
          if (!formUrl.startsWith('http://') && !formUrl.startsWith('https://')) {
            formUrl = 'https://$formUrl';
          }
          if (formUrl.contains('docs.google.com/forms') && !formUrl.contains('embedded=true')) {
            final sep = formUrl.contains('?') ? '&' : '?';
            formUrl = '$formUrl${sep}embedded=true';
          }
          _originalFormUrl = formUrl;
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
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Gagal terhubung ke server ujian. Periksa jaringan dan coba kembali.'),
          backgroundColor: Color(0xFFEF4444),
          duration: Duration(seconds: 4),
          behavior: SnackBarBehavior.floating,
        ),
      );
      Navigator.pop(context);
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
          backgroundColor: Colors.transparent,
          insetPadding: const EdgeInsets.symmetric(horizontal: 20, vertical: 24),
          child: Container(
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                colors: [Color(0xFF0F172A), Color(0xFF0B1120)],
                begin: Alignment.topCenter,
                end: Alignment.bottomCenter,
              ),
              borderRadius: BorderRadius.circular(28),
              border: Border.all(
                color: const Color(0xFFEF4444).withValues(alpha: 0.45),
                width: 1.5,
              ),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: 0.6),
                  blurRadius: 36,
                  offset: const Offset(0, 16),
                ),
                BoxShadow(
                  color: const Color(0xFFEF4444).withValues(alpha: 0.2),
                  blurRadius: 28,
                  offset: const Offset(0, 4),
                ),
              ],
            ),
            padding: const EdgeInsets.all(26.0),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                // Glowing Lock Badge
                Container(
                  width: 80,
                  height: 80,
                  decoration: BoxDecoration(
                    gradient: const LinearGradient(
                      colors: [Color(0xFFEF4444), Color(0xFF991B1B)],
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                    ),
                    shape: BoxShape.circle,
                    boxShadow: [
                      BoxShadow(
                        color: const Color(0xFFEF4444).withValues(alpha: 0.35),
                        blurRadius: 20,
                        offset: const Offset(0, 6),
                      ),
                    ],
                  ),
                  child: const Center(
                    child: Icon(Icons.lock_rounded, color: Colors.white, size: 40),
                  ),
                ),
                const SizedBox(height: 20),

                // Pill Tag
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 5),
                  decoration: BoxDecoration(
                    color: const Color(0xFFEF4444).withValues(alpha: 0.15),
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(
                      color: const Color(0xFFEF4444).withValues(alpha: 0.35),
                      width: 1,
                    ),
                  ),
                  child: const Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(Icons.gpp_bad_rounded, color: Color(0xFFF87171), size: 14),
                      SizedBox(width: 6),
                      Text(
                        'AKSES UJIAN DITANGGUHKAN',
                        style: TextStyle(
                          color: Color(0xFFFCA5A5),
                          fontWeight: FontWeight.w800,
                          fontSize: 11,
                          letterSpacing: 0.8,
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 14),

                const Text(
                  'Ujian Telah Dikunci',
                  textAlign: TextAlign.center,
                  style: TextStyle(
                    color: Colors.white,
                    fontWeight: FontWeight.w800,
                    fontSize: 22,
                    letterSpacing: -0.3,
                  ),
                ),
                const SizedBox(height: 8),
                Text(
                  'Anda telah mencapai batas maksimum ($maxViolations/$maxViolations) pelanggaran keamanan. Sesi pengerjaan Anda otomatis dinonaktifkan oleh sistem.',
                  textAlign: TextAlign.center,
                  style: const TextStyle(
                    color: Color(0xFF94A3B8),
                    fontSize: 13,
                    height: 1.45,
                  ),
                ),
                const SizedBox(height: 20),

                // Instructor Contact Card
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.all(14),
                  decoration: BoxDecoration(
                    color: const Color(0xFF1E293B).withValues(alpha: 0.8),
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(
                      color: const Color(0xFF334155),
                      width: 1,
                    ),
                  ),
                  child: const Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Icon(
                        Icons.support_agent_rounded,
                        size: 22,
                        color: Color(0xFF38BDF8),
                      ),
                      SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              'Langkah Selanjutnya:',
                              style: TextStyle(
                                color: Colors.white,
                                fontSize: 12.5,
                                fontWeight: FontWeight.w700,
                              ),
                            ),
                            SizedBox(height: 2),
                            Text(
                              'Segera temui Pengawas Ujian / Guru IT di ruangan untuk proses verifikasi dan pembukaan kunci ujian.',
                              style: TextStyle(
                                color: Color(0xFFCBD5E1),
                                fontSize: 12,
                                height: 1.4,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 24),

                // Exit Button: Langsung lempar ke beranda
                SizedBox(
                  width: double.infinity,
                  height: 48,
                  child: ElevatedButton(
                    onPressed: () {
                      Navigator.pop(dialogContext);
                      if (mounted) {
                        Navigator.pop(context, 'locked');
                      }
                    },
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFFEF4444),
                      foregroundColor: Colors.white,
                      elevation: 0,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(14),
                      ),
                    ),
                    child: const Text(
                      'SAYA PAHAM (KEMBALI KE BERANDA)',
                      style: TextStyle(
                        fontWeight: FontWeight.w800,
                        fontSize: 13.5,
                        letterSpacing: 0.3,
                      ),
                    ),
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
      platform.invokeMethod('flushCookies');
      platform.invokeMethod('clearSecureScreen');
    } catch (e) {
      // Ignored
    }
    super.dispose();
  }

  bool _wasPaused = false;

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (state == AppLifecycleState.paused ||
        state == AppLifecycleState.hidden) {
      _wasPaused = true;
      _awayStartTime = DateTime.now();
    } else if (state == AppLifecycleState.resumed) {
      if (_wasPaused) {
        _wasPaused = false;
        final secondsAway = _awayStartTime != null
            ? DateTime.now().difference(_awayStartTime!).inSeconds
            : 0;
        _handleViolation(
          'APP_SWITCH',
          'Terdeteksi keluar aplikasi / layar mati ($secondsAway detik)',
        );
      }
    }
  }

  void _handleTimeExpired() {
    if (_timeExpiredHandled) return;
    _timeExpiredHandled = true;
    _examTimer?.cancel();
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
                'Durasi pengerjaan ujian telah habis. Sesi ujian Anda telah berakhir.',
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
                    try {
                      await ApiService.finishExamSession(widget.exam['id']);
                    } catch (_) {}
                    if (!mounted) return;
                    Navigator.pop(context);
                  },
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF2563EB),
                    foregroundColor: Colors.white,
                    elevation: 0,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                  child: const Text('KEMBALI KE BERANDA', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 13.5)),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Future<void> _handleViolation(String type, String desc, {bool reportToServer = true}) async {
    if (_isLocked) return;

    final now = DateTime.now();
    // 1. Grace period saat pertama kali masuk (5 detik pertama)
    // Cegah dialog sistem Android (Sematkan Aplikasi / Immersive Mode / Keyboard) dihitung pelanggaran
    if (type != 'SCREEN_OFF' &&
        type != 'APP_RESTART_OR_DEVICE_OFF' &&
        _entryTime != null &&
        now.difference(_entryTime!).inSeconds < 5) {
      return;
    }

    // 2. Cooldown antar-pelanggaran (minimal 4 detik jeda agar 1 kali beralih tidak terhitung ganda)
    if (_lastViolationTime != null && now.difference(_lastViolationTime!).inSeconds < 4) {
      return;
    }
    _lastViolationTime = now;

    HapticFeedback.heavyImpact();

    final int maxViolations = int.tryParse(widget.exam['max_violation']?.toString() ?? '3') ?? 3;
    final int examId = int.tryParse(widget.exam['id']?.toString() ?? '0') ?? 0;

    if (reportToServer) {
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
    }

    if (!mounted) return;

    if (_violationCount >= maxViolations) {
      _lockExamPermanently();
    } else {
      final int remainingChances = maxViolations - _violationCount;
      final bool isCritical = remainingChances == 1;

      showDialog(
        context: context,
        barrierDismissible: false,
        builder: (dialogContext) => Dialog(
          backgroundColor: Colors.transparent,
          insetPadding: const EdgeInsets.symmetric(horizontal: 20, vertical: 24),
          child: Container(
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                colors: [Color(0xFF0F172A), Color(0xFF0C1322)],
                begin: Alignment.topCenter,
                end: Alignment.bottomCenter,
              ),
              borderRadius: BorderRadius.circular(28),
              border: Border.all(
                color: isCritical
                    ? const Color(0xFFEF4444).withValues(alpha: 0.5)
                    : const Color(0xFFF59E0B).withValues(alpha: 0.45),
                width: 1.5,
              ),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: 0.6),
                  blurRadius: 36,
                  offset: const Offset(0, 16),
                ),
                BoxShadow(
                  color: isCritical
                      ? const Color(0xFFEF4444).withValues(alpha: 0.2)
                      : const Color(0xFFF59E0B).withValues(alpha: 0.15),
                  blurRadius: 24,
                  offset: const Offset(0, 4),
                ),
              ],
            ),
            padding: const EdgeInsets.all(24.0),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                // Top Glowing Warning Icon
                Container(
                  width: 72,
                  height: 72,
                  decoration: BoxDecoration(
                    gradient: LinearGradient(
                      colors: isCritical
                          ? [const Color(0xFFEF4444), const Color(0xFFDC2626)]
                          : [const Color(0xFFF59E0B), const Color(0xFFD97706)],
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                    ),
                    shape: BoxShape.circle,
                    boxShadow: [
                      BoxShadow(
                        color: (isCritical
                                ? const Color(0xFFEF4444)
                                : const Color(0xFFF59E0B))
                            .withValues(alpha: 0.35),
                        blurRadius: 20,
                        offset: const Offset(0, 6),
                      ),
                    ],
                  ),
                  child: Center(
                    child: Icon(
                      isCritical
                          ? Icons.warning_rounded
                          : Icons.security_update_warning_rounded,
                      color: Colors.white,
                      size: 38,
                    ),
                  ),
                ),
                const SizedBox(height: 18),

                // Pill Badge
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 4.5),
                  decoration: BoxDecoration(
                    color: isCritical
                        ? const Color(0xFFEF4444).withValues(alpha: 0.15)
                        : const Color(0xFFF59E0B).withValues(alpha: 0.15),
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(
                      color: isCritical
                          ? const Color(0xFFEF4444).withValues(alpha: 0.35)
                          : const Color(0xFFF59E0B).withValues(alpha: 0.35),
                      width: 1,
                    ),
                  ),
                  child: Text(
                    isCritical ? '⚠️ PERINGATAN TERAKHIR' : '⚠️ PELANGGARAN TERDETEKSI',
                    style: TextStyle(
                      color: isCritical
                          ? const Color(0xFFFCA5A5)
                          : const Color(0xFFFDE68A),
                      fontWeight: FontWeight.w800,
                      fontSize: 11,
                      letterSpacing: 0.8,
                    ),
                  ),
                ),
                const SizedBox(height: 12),

                // Title
                const Text(
                  'Aktivitas Tidak Diizinkan',
                  textAlign: TextAlign.center,
                  style: TextStyle(
                    color: Colors.white,
                    fontWeight: FontWeight.w800,
                    fontSize: 20,
                    letterSpacing: -0.3,
                  ),
                ),
                const SizedBox(height: 10),

                // Description Box
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 11),
                  decoration: BoxDecoration(
                    color: const Color(0xFF1E293B).withValues(alpha: 0.85),
                    borderRadius: BorderRadius.circular(14),
                    border: Border.all(color: const Color(0xFF334155)),
                  ),
                  child: Row(
                    children: [
                      Icon(
                        Icons.info_outline_rounded,
                        size: 18,
                        color: isCritical
                            ? const Color(0xFFF87171)
                            : const Color(0xFFFBBF24),
                      ),
                      const SizedBox(width: 10),
                      Expanded(
                        child: Text(
                          desc,
                          style: TextStyle(
                            color: isCritical
                                ? const Color(0xFFFECACA)
                                : const Color(0xFFFEF3C7),
                            fontSize: 12.5,
                            fontWeight: FontWeight.w600,
                            height: 1.35,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 16),

                // Tolerance & Segmented Progress Card
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.all(14),
                  decoration: BoxDecoration(
                    color: const Color(0xFF162032),
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(color: const Color(0xFF26354A)),
                  ),
                  child: Column(
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          const Text(
                            'Batas Toleransi:',
                            style: TextStyle(
                              color: Color(0xFF94A3B8),
                              fontSize: 12,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                            decoration: BoxDecoration(
                              color: isCritical
                                  ? const Color(0xFFEF4444).withValues(alpha: 0.2)
                                  : const Color(0xFFF59E0B).withValues(alpha: 0.2),
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: Text(
                              '$_violationCount dari $maxViolations kali',
                              style: TextStyle(
                                color: isCritical
                                    ? const Color(0xFFF87171)
                                    : const Color(0xFFFBBF24),
                                fontSize: 12,
                                fontWeight: FontWeight.w800,
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 12),
                      Row(
                        children: List.generate(maxViolations, (index) {
                          bool isFilled = index < _violationCount;
                          Color barColor = const Color(0xFF334155);
                          if (isFilled) {
                            barColor = isCritical
                                ? const Color(0xFFEF4444)
                                : (index == 0
                                    ? const Color(0xFFF59E0B)
                                    : const Color(0xFFF97316));
                          }
                          return Expanded(
                            child: Container(
                              height: 7,
                              margin: EdgeInsets.only(right: index < maxViolations - 1 ? 6 : 0),
                              decoration: BoxDecoration(
                                color: barColor,
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
                  isCritical
                      ? '⚠️ PERINGATAN: 1 pelanggaran lagi akan langsung MENGUNCI ujian Anda!'
                      : 'Tersisa $remainingChances kesempatan sebelum sistem mengunci ujian Anda.',
                  textAlign: TextAlign.center,
                  style: TextStyle(
                    color: isCritical
                        ? const Color(0xFFFCA5A5)
                        : const Color(0xFF94A3B8),
                    fontSize: 12,
                    fontWeight: isCritical ? FontWeight.w700 : FontWeight.w500,
                    height: 1.35,
                  ),
                ),
                const SizedBox(height: 20),

                // Continue Button
                SizedBox(
                  width: double.infinity,
                  height: 48,
                  child: ElevatedButton(
                    onPressed: () {
                      Navigator.pop(dialogContext);
                      if (_isLocked || _violationCount >= maxViolations) {
                        if (mounted) {
                          Navigator.pop(context, 'locked');
                        }
                      }
                    },
                    style: ElevatedButton.styleFrom(
                      backgroundColor: isCritical
                          ? const Color(0xFFEF4444)
                          : const Color(0xFFF59E0B),
                      foregroundColor: isCritical ? Colors.white : const Color(0xFF0F172A),
                      elevation: 0,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(14),
                      ),
                    ),
                    child: Text(
                      'SAYA PAHAM & LANJUTKAN',
                      style: TextStyle(
                        fontWeight: FontWeight.w900,
                        fontSize: 13,
                        letterSpacing: 0.4,
                        color: isCritical ? Colors.white : const Color(0xFF0F172A),
                      ),
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
    // 1. Deteksi status Google Form (TANPA auto-click submit)
    // ponytail: auto-click dihapus karena menyebabkan form kosong ter-submit
    // saat siswa baru masuk lalu keluar. Siswa harus klik Kirim sendiri.
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

          return 'NOT_SUBMITTED';
        })();
      """);
      formState = result.toString().replaceAll('"', '');
    } catch (e) {
      // Ignored
    }

    if (!mounted) return;

    bool isAlreadySubmitted = formState == 'ALREADY_SUBMITTED';

    // Jika BELUM submit: tampilkan peringatan keras, jangan izinkan langsung akhiri
    if (!isAlreadySubmitted) {
      final bool? forceFinish = await showDialog<bool>(
        context: context,
        builder: (dialogContext) => Dialog(
          backgroundColor: const Color(0xFF0F172A),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(24),
            side: const BorderSide(color: Color(0xFFF59E0B), width: 1.2),
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
                    color: const Color(0xFFF59E0B).withValues(alpha: 0.15),
                    shape: BoxShape.circle,
                    border: Border.all(
                      color: const Color(0xFFF59E0B).withValues(alpha: 0.3),
                    ),
                  ),
                  child: const Icon(
                    Icons.warning_amber_rounded,
                    color: Color(0xFFFBBF24),
                    size: 28,
                  ),
                ),
                const SizedBox(height: 16),
                const Text(
                  'Jawaban Belum Dikirim!',
                  textAlign: TextAlign.center,
                  style: TextStyle(
                    color: Colors.white,
                    fontWeight: FontWeight.w800,
                    fontSize: 17,
                    letterSpacing: -0.3,
                  ),
                ),
                const SizedBox(height: 8),
                const Text(
                  'Anda BELUM menekan tombol "Kirim" di Google Form. Jawaban Anda belum terekam. Kembali ke form dan klik tombol "Kirim" terlebih dahulu.',
                  textAlign: TextAlign.center,
                  style: TextStyle(color: Color(0xFFFDE68A), fontSize: 12.5, height: 1.4),
                ),
                const SizedBox(height: 22),
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton(
                    onPressed: () => Navigator.pop(dialogContext, false),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFFF59E0B),
                      foregroundColor: const Color(0xFF0F172A),
                      elevation: 0,
                      padding: const EdgeInsets.symmetric(vertical: 12),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    ),
                    child: const Text('KEMBALI KE FORM', style: TextStyle(fontWeight: FontWeight.w800)),
                  ),
                ),
                const SizedBox(height: 8),
                SizedBox(
                  width: double.infinity,
                  child: TextButton(
                    onPressed: () => Navigator.pop(dialogContext, true),
                    style: TextButton.styleFrom(
                      foregroundColor: const Color(0xFF64748B),
                    ),
                    child: const Text('Tetap akhiri tanpa kirim', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600)),
                  ),
                ),
              ],
            ),
          ),
        ),
      );

      // Jika siswa memilih kembali ke form, batalkan
      if (forceFinish != true || !mounted) return;
    }

    // Form sudah di-submit ATAU siswa force-finish: konfirmasi akhiri sesi
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
                  color: const Color(0xFF10B981).withValues(alpha: 0.15),
                  shape: BoxShape.circle,
                  border: Border.all(
                    color: const Color(0xFF10B981).withValues(alpha: 0.3),
                  ),
                ),
                child: const Icon(
                  Icons.verified_rounded,
                  color: Color(0xFF34D399),
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
                    : 'Anda memilih mengakhiri sesi tanpa mengirim jawaban. Sesi akan ditutup.',
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
                      child: const Text('Batal', style: TextStyle(fontWeight: FontWeight.w700)),
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
      try {
        await platform.invokeMethod('flushCookies');
      } catch (_) {}
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
                          FittedBox(
                            fit: BoxFit.scaleDown,
                            alignment: Alignment.centerLeft,
                            child: Row(
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

                    const SizedBox(width: 6),

                    // Tombol Muat Ulang / Reload Soal
                    Container(
                      decoration: BoxDecoration(
                        color: const Color(0xFF112238),
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(color: const Color(0xFF233B5D), width: 1),
                      ),
                      child: IconButton(
                        icon: const Icon(Icons.refresh_rounded, size: 16, color: Color(0xFF38BDF8)),
                        tooltip: 'Muat Ulang Soal',
                        padding: const EdgeInsets.all(6),
                        constraints: const BoxConstraints(minWidth: 32, minHeight: 32),
                        onPressed: () {
                          // Reload ke form URL asli (bukan current URL)
                          // agar siswa yang terjebak di halaman Google
                          // langsung kembali ke soal ujian
                          if (_originalFormUrl != null) {
                            _controller.loadRequest(Uri.parse(_originalFormUrl!));
                          } else {
                            _controller.reload();
                          }
                          ScaffoldMessenger.of(context).showSnackBar(
                            const SnackBar(
                              content: Text('Memuat ulang soal...'),
                              duration: Duration(seconds: 2),
                              behavior: SnackBarBehavior.floating,
                            ),
                          );
                        },
                      ),
                    ),

                    const SizedBox(width: 8),

                    // Right: Selesai Button
                    ElevatedButton.icon(
                      onPressed: _onTapFinishExam,
                      icon: const Icon(Icons.check_circle_rounded, size: 13),
                      label: const Text(
                        'Selesai',
                        style: TextStyle(
                          fontWeight: FontWeight.w800,
                          fontSize: 12,
                          color: Colors.white,
                        ),
                      ),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF10B981),
                        foregroundColor: Colors.white,
                        elevation: 0,
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                        minimumSize: const Size(60, 32),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(8),
                        ),
                      ),
                    ),
                  ],
                ),
              ),

              // 2. MAIN WEBVIEW BODY
              Expanded(
                child: kIsWeb
                    ? Center(
                        child: Container(
                          margin: const EdgeInsets.all(24),
                          padding: const EdgeInsets.all(24),
                          decoration: BoxDecoration(
                            color: const Color(0xFF0F172A),
                            borderRadius: BorderRadius.circular(20),
                            border: Border.all(color: const Color(0xFF334155)),
                          ),
                          child: const Column(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Icon(Icons.phone_iphone_rounded, size: 56, color: Color(0xFF38BDF8)),
                              SizedBox(height: 16),
                              Text(
                                'Mode Ujian Khusus Aplikasi Mobile',
                                textAlign: TextAlign.center,
                                style: TextStyle(
                                  color: Colors.white,
                                  fontWeight: FontWeight.w800,
                                  fontSize: 17,
                                ),
                              ),
                              SizedBox(height: 8),
                              Text(
                                'Fitur Kiosk Keamanan Ujian (Lock Screen, Anti-Kecurangan, dsb.) dan Google Form WebView hanya berjalan di perangkat Android / iOS fisik.\\nDi browser Web desktop, WebView tidak didukung.',
                                textAlign: TextAlign.center,
                                style: TextStyle(color: Color(0xFF94A3B8), fontSize: 13, height: 1.4),
                              ),
                            ],
                          ),
                        ),
                      )
                    : _isLocked
                        ? Container(
                            color: const Color(0xFF0B132B),
                            padding: const EdgeInsets.all(32),
                            child: Center(
                              child: Column(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  const Icon(Icons.lock_rounded, size: 64, color: Color(0xFFEF4444)),
                                  const SizedBox(height: 20),
                                  const Text(
                                    'UJIAN DIKUNCI',
                                    style: TextStyle(
                                      fontSize: 22,
                                      color: Color(0xFFF87171),
                                      fontWeight: FontWeight.w800,
                                      letterSpacing: -0.5,
                                    ),
                                  ),
                                  const SizedBox(height: 8),
                                  const Text(
                                    'Toleransi batas pelanggaran telah habis.',
                                    textAlign: TextAlign.center,
                                    style: TextStyle(color: Color(0xFF94A3B8), fontSize: 14),
                                  ),
                                  const SizedBox(height: 24),
                                  ElevatedButton.icon(
                                    onPressed: () {
                                      if (mounted) {
                                        Navigator.pop(context, 'locked');
                                      }
                                    },
                                    icon: const Icon(Icons.home_rounded, size: 18),
                                    label: const Text(
                                      'KEMBALI KE BERANDA',
                                      style: TextStyle(fontWeight: FontWeight.w800, fontSize: 13),
                                    ),
                                    style: ElevatedButton.styleFrom(
                                      backgroundColor: const Color(0xFFEF4444),
                                      foregroundColor: Colors.white,
                                      elevation: 0,
                                      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
                                      shape: RoundedRectangleBorder(
                                        borderRadius: BorderRadius.circular(12),
                                      ),
                                    ),
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
