import 'package:flutter/material.dart';
import 'package:geolocator/geolocator.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../api_service.dart';
import 'exam_screen.dart';

class SecurityCheckScreen extends StatefulWidget {
  final dynamic exam;

  const SecurityCheckScreen({super.key, required this.exam});

  @override
  State<SecurityCheckScreen> createState() => _SecurityCheckScreenState();
}

class _SecurityCheckScreenState extends State<SecurityCheckScreen>
    with SingleTickerProviderStateMixin {
  // Koordinat Sekolah & Batas Radius (250 meter)
  static const double schoolLatitude = -6.3400545;
  static const double schoolLongitude = 107.4686982;
  static const double maxRadiusMeters = 250.0;

  late AnimationController _controller;
  late Animation<double> _shackleAnimation;
  late Animation<double> _glowAnimation;

  bool _isChecking = true;
  bool _isSecure = false;
  String _statusMessage = 'Memverifikasi profil & lingkungan ujian...';

  @override
  void initState() {
    super.initState();

    _controller = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 700),
    );

    _shackleAnimation = Tween<double>(begin: -8.0, end: 0.0).animate(
      CurvedAnimation(
        parent: _controller,
        curve: const Interval(0.0, 0.7, curve: Curves.easeOutBack),
      ),
    );

    _glowAnimation = Tween<double>(begin: 0.0, end: 1.0).animate(
      CurvedAnimation(
        parent: _controller,
        curve: const Interval(0.4, 1.0, curve: Curves.easeOutCubic),
      ),
    );

    _performSecurityCheck();
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  Future<void> _performSecurityCheck() async {
    setState(() {
      _isChecking = true;
      _isSecure = false;
      _statusMessage = 'Memverifikasi profil & lingkungan ujian...';
    });
    _controller.reset();

    await Future.delayed(const Duration(milliseconds: 600));

    // 1. Cek apakah siswa berstatus PKL (Bebas GPS)
    bool isPkl = false;
    try {
      final prefs = await SharedPreferences.getInstance();
      isPkl = prefs.getBool('is_pkl') ?? false;

      // Sinkronisasi status terbaru dari server
      final userRes = await ApiService.getMe();
      if (userRes['success'] == true && userRes['data'] != null) {
        isPkl = (userRes['data']['is_pkl'] == true || userRes['data']['is_pkl'] == 1);
        await prefs.setBool('is_pkl', isPkl);
      }
    } catch (_) {}

    // JIKA SISWA PKL: Bebaskan pengecekan GPS
    if (isPkl) {
      if (mounted) {
        setState(() {
          _isChecking = false;
          _isSecure = true;
          _statusMessage = 'Status: PKL / Magang (Verifikasi GPS Dinonaktifkan).';
        });
        _controller.forward();
      }
      return;
    }

    // JIKA SISWA REGULER (BUKAN PKL): Wajib lolos verifikasi GPS & Radius Sekolah 250m
    try {
      if (mounted) {
        setState(() {
          _statusMessage = 'Memverifikasi koordinat & lokasi sekolah...';
        });
      }

      final serviceEnabled = await Geolocator.isLocationServiceEnabled();
      if (!serviceEnabled) {
        if (mounted) {
          setState(() {
            _isChecking = false;
            _isSecure = false;
            _statusMessage = 'GPS belum aktif. Silakan nyalakan lokasi perangkat Anda.';
          });
          _controller.forward();
        }
        return;
      }

      LocationPermission permission = await Geolocator.checkPermission();
      if (permission == LocationPermission.denied) {
        permission = await Geolocator.requestPermission();
        if (permission == LocationPermission.denied) {
          if (mounted) {
            setState(() {
              _isChecking = false;
              _isSecure = false;
              _statusMessage = 'Izin lokasi dibutuhkan untuk verifikasi ujian.';
            });
            _controller.forward();
          }
          return;
        }
      }

      if (permission == LocationPermission.deniedForever) {
        if (mounted) {
          setState(() {
            _isChecking = false;
            _isSecure = false;
            _statusMessage = 'Izin lokasi ditolak permanen. Buka Pengaturan HP untuk mengizinkan.';
          });
          _controller.forward();
        }
        return;
      }

      Position? position;
      try {
        position = await Geolocator.getLastKnownPosition();
      } catch (_) {}

      if (position == null) {
        try {
          position = await Geolocator.getCurrentPosition(
            locationSettings: const LocationSettings(
              accuracy: LocationAccuracy.high,
            ),
          );
        } catch (e) {
          debugPrint('Gagal mengambil posisi saat ini: $e');
        }
      }

      if (position == null) {
        if (mounted) {
          setState(() {
            _isChecking = false;
            _isSecure = false;
            _statusMessage = 'Tidak dapat mendeteksi titik lokasi. Pastikan GPS aktif.';
          });
          _controller.forward();
        }
        return;
      }

      if (position.isMocked) {
        if (mounted) {
          setState(() {
            _isChecking = false;
            _isSecure = false;
            _statusMessage = 'Terdeteksi menggunakan aplikasi lokasi palsu (Fake GPS).';
          });
          _controller.forward();
        }
        return;
      }

      final distance = Geolocator.distanceBetween(
        schoolLatitude,
        schoolLongitude,
        position.latitude,
        position.longitude,
      );

      debugPrint('📍 Posisi User: ${position.latitude}, ${position.longitude}');
      debugPrint('🏫 Titik Sekolah: $schoolLatitude, $schoolLongitude');
      debugPrint('📏 Jarak Terhitung: ${distance.round()} meter');

      if (distance > maxRadiusMeters) {
        if (mounted) {
          setState(() {
            _isChecking = false;
            _isSecure = false;
            _statusMessage =
                'Di luar area sekolah (${distance.round()}m dari target, maks: ${maxRadiusMeters.round()}m).\nPosisi Anda: ${position!.latitude.toStringAsFixed(6)}, ${position!.longitude.toStringAsFixed(6)}';
          });
          _controller.forward();
        }
        return;
      }

      // Lolos Verifikasi Lokasi Siswa Reguler
      if (mounted) {
        setState(() {
          _isChecking = false;
          _isSecure = true;
          _statusMessage = 'Perangkat terverifikasi di area sekolah (${distance.round()}m).';
        });
        _controller.forward();
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _isChecking = false;
          _isSecure = false;
          _statusMessage = 'Gagal mendeteksi lokasi. Pastikan GPS & internet aktif, lalu coba lagi.';
        });
        _controller.forward();
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF0B1320),
      body: SafeArea(
        child: Center(
          child: Padding(
            padding: const EdgeInsets.symmetric(
              horizontal: 32.0,
              vertical: 24.0,
            ),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                const Spacer(),

                // Security Badge Pill
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 14,
                    vertical: 6,
                  ),
                  decoration: BoxDecoration(
                    color: const Color(0xFF162338),
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(
                      color: const Color(0xFF273852),
                      width: 1,
                    ),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Container(
                        width: 6,
                        height: 6,
                        decoration: BoxDecoration(
                          shape: BoxShape.circle,
                          color: _isChecking
                              ? const Color(0xFF38BDF8)
                              : (_isSecure
                                  ? const Color(0xFF10B981)
                                  : const Color(0xFFF43F5E)),
                        ),
                      ),
                      const SizedBox(width: 8),
                      Text(
                        _isChecking
                            ? 'DIAGNOSTIC RUNNING'
                            : (_isSecure ? 'LOKASI TERVERIFIKASI' : 'SECURITY ALERT'),
                        style: const TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.w700,
                          color: Color(0xFF94A3B8),
                          letterSpacing: 1.0,
                        ),
                      ),
                    ],
                  ),
                ),

                const SizedBox(height: 36),

                // Modern Architectural Padlock with Hairline Sweep Spinner
                _buildModernLockVisual(),

                const SizedBox(height: 36),

                // Title
                Text(
                  _isChecking
                      ? 'Memverifikasi Lokasi'
                      : (_isSecure
                            ? 'Lingkungan Ujian Aman'
                            : 'Peringatan Keamanan'),
                  textAlign: TextAlign.center,
                  style: const TextStyle(
                    fontSize: 22,
                    fontWeight: FontWeight.w700,
                    color: Colors.white,
                    letterSpacing: -0.3,
                  ),
                ),

                const SizedBox(height: 10),

                // Subtitle (Fixed Height to prevent layout jumping)
                SizedBox(
                  height: 44,
                  child: AnimatedSwitcher(
                    duration: const Duration(milliseconds: 250),
                    child: Text(
                      _statusMessage,
                      key: ValueKey<String>(_statusMessage),
                      textAlign: TextAlign.center,
                      style: const TextStyle(
                        fontSize: 13.5,
                        color: Color(0xFF64748B),
                        height: 1.45,
                        fontWeight: FontWeight.w400,
                      ),
                    ),
                  ),
                ),

                const Spacer(),

                // Action Buttons
                if (!_isChecking)
                  if (_isSecure)
                    SizedBox(
                      width: double.infinity,
                      height: 52,
                      child: ElevatedButton(
                        onPressed: () async {
                          final nav = Navigator.of(context);
                          final result = await nav.push(
                            MaterialPageRoute(
                              builder: (context) =>
                                  ExamScreen(exam: widget.exam),
                            ),
                          );
                          if (mounted) {
                            nav.pop(result);
                          }
                        },
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.white,
                          foregroundColor: const Color(0xFF0B1320),
                          elevation: 0,
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                        ),
                        child: const Text(
                          'Mulai Ujian',
                          style: TextStyle(
                            fontSize: 14,
                            fontWeight: FontWeight.w700,
                            letterSpacing: 0.3,
                          ),
                        ),
                      ),
                    )
                  else
                    Column(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        SizedBox(
                          width: double.infinity,
                          height: 50,
                          child: ElevatedButton.icon(
                            onPressed: _performSecurityCheck,
                            icon: const Icon(Icons.refresh_rounded, size: 18),
                            label: const Text(
                              'Coba Verifikasi Lagi',
                              style: TextStyle(
                                fontSize: 14,
                                fontWeight: FontWeight.w700,
                                letterSpacing: 0.2,
                              ),
                            ),
                            style: ElevatedButton.styleFrom(
                              backgroundColor: const Color(0xFF38BDF8),
                              foregroundColor: const Color(0xFF0B1320),
                              elevation: 0,
                              shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(12),
                              ),
                            ),
                          ),
                        ),
                        const SizedBox(height: 10),
                        SizedBox(
                          width: double.infinity,
                          height: 44,
                          child: TextButton(
                            onPressed: () => Navigator.pop(context),
                            style: TextButton.styleFrom(
                              foregroundColor: const Color(0xFF94A3B8),
                            ),
                            child: const Text(
                              'Kembali ke Beranda',
                              style: TextStyle(
                                fontSize: 13,
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                          ),
                        ),
                      ],
                    )
                else
                  const SizedBox(height: 52),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildModernLockVisual() {
    return SizedBox(
      width: 140,
      height: 140,
      child: Stack(
        alignment: Alignment.center,
        children: [
          // 1. Dual Hairline Precision Spinner
          if (_isChecking)
            const SizedBox(
              width: 130,
              height: 130,
              child: CircularProgressIndicator(
                strokeWidth: 2.0,
                valueColor: AlwaysStoppedAnimation<Color>(Color(0xFF38BDF8)),
                backgroundColor: Color(0xFF17263C),
              ),
            )
          else
            AnimatedBuilder(
              animation: _glowAnimation,
              builder: (context, child) {
                final glow = _glowAnimation.value;
                final color = _isSecure
                    ? const Color(0xFF10B981)
                    : const Color(0xFFF43F5E);
                return Container(
                  width: 130,
                  height: 130,
                  decoration: BoxDecoration(
                    shape: BoxShape.circle,
                    border: Border.all(
                      color: color.withValues(alpha: 0.3 + (glow * 0.4)),
                      width: 1.5,
                    ),
                    boxShadow: [
                      BoxShadow(
                        color: color.withValues(alpha: 0.2 * glow),
                        blurRadius: 28,
                        spreadRadius: 2,
                      ),
                    ],
                  ),
                );
              },
            ),

          // 2. Glassmorphic Core Container
          Container(
            width: 98,
            height: 98,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              gradient: const LinearGradient(
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
                colors: [Color(0xFF182840), Color(0xFF0E1A2B)],
              ),
              border: Border.all(
                color: Colors.white.withValues(alpha: 0.1),
                width: 1.2,
              ),
            ),
            child: AnimatedBuilder(
              animation: _controller,
              builder: (context, child) {
                final shackleOffset = _isChecking
                    ? -6.0
                    : _shackleAnimation.value;
                final isSuccess = !_isChecking && _isSecure;
                final isFailed = !_isChecking && !_isSecure;

                return Center(
                  child: SizedBox(
                    width: 44,
                    height: 50,
                    child: Stack(
                      alignment: Alignment.topCenter,
                      children: [
                        // Shackle (Tangkai Gembok Arsitektural Presisi)
                        Transform.translate(
                          offset: Offset(0, shackleOffset),
                          child: Container(
                            width: 24,
                            height: 24,
                            decoration: BoxDecoration(
                              color: Colors.transparent,
                              borderRadius: const BorderRadius.vertical(
                                top: Radius.circular(12),
                              ),
                              border: Border.all(
                                color: isSuccess
                                    ? const Color(0xFF34D399)
                                    : (isFailed
                                          ? const Color(0xFFFB7185)
                                          : Colors.white),
                                width: 3.5,
                              ),
                            ),
                          ),
                        ),

                        // Padlock Body (Bodi Gembok Modern Minimalis)
                        Positioned(
                          bottom: 0,
                          child: Container(
                            width: 40,
                            height: 30,
                            decoration: BoxDecoration(
                              gradient: LinearGradient(
                                begin: Alignment.topCenter,
                                end: Alignment.bottomCenter,
                                colors: isSuccess
                                    ? [
                                        const Color(0xFF10B981),
                                        const Color(0xFF059669),
                                      ]
                                    : (isFailed
                                          ? [
                                              const Color(0xFFF43F5E),
                                              const Color(0xFFE11D48),
                                            ]
                                          : [
                                              const Color(0xFFF8FAFC),
                                              const Color(0xFFE2E8F0),
                                            ]),
                              ),
                              borderRadius: BorderRadius.circular(8),
                              boxShadow: [
                                BoxShadow(
                                  color: Colors.black.withValues(alpha: 0.3),
                                  blurRadius: 8,
                                  offset: const Offset(0, 4),
                                ),
                              ],
                            ),
                            child: Center(
                              child: isSuccess
                                  ? const Icon(
                                      Icons.check_rounded,
                                      size: 18,
                                      color: Colors.white,
                                    )
                                  : isFailed
                                  ? const Icon(
                                      Icons.close_rounded,
                                      size: 18,
                                      color: Colors.white,
                                    )
                                  : Container(
                                      width: 5,
                                      height: 8,
                                      decoration: BoxDecoration(
                                        color: const Color(0xFF0F172A),
                                        borderRadius: BorderRadius.circular(
                                          2.5,
                                        ),
                                      ),
                                    ),
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                );
              },
            ),
          ),
        ],
      ),
    );
  }
}
