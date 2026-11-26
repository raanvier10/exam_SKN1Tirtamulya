import 'dart:math';
import 'dart:ui' as ui;
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../api_service.dart';
import 'dashboard_screen.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  static const String captchaCharset = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
  static const int captchaLength = 5;

  @visibleForTesting
  static String generateCaptchaCode([Random? random]) {
    final rng = random ?? Random.secure();
    return List<String>.generate(
      captchaLength,
      (_) => captchaCharset[rng.nextInt(captchaCharset.length)],
    ).join();
  }

  @visibleForTesting
  static String? validateCaptchaInput(String input, String expected) {
    final normalized = input.trim().toUpperCase();
    if (normalized.isEmpty) {
      return 'Kode verifikasi wajib diisi.';
    }
    if (normalized.length != captchaLength) {
      return 'Kode verifikasi harus $captchaLength karakter.';
    }
    if (normalized != expected.trim().toUpperCase()) {
      return 'Kode verifikasi tidak sesuai. Silakan coba lagi.';
    }
    return null;
  }

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _usernameController = TextEditingController();
  final _passwordController = TextEditingController();
  final _captchaController = TextEditingController();
  final Random _captchaRng = Random.secure();
  late String _captchaCode;
  late int _captchaSeed;
  bool _isLoading = false;
  bool _obscurePassword = true;
  String _errorMessage = '';

  @override
  void initState() {
    super.initState();
    _refreshCaptcha(clearInput: false, notify: false);
  }

  void _refreshCaptcha({bool clearInput = true, bool notify = true}) {
    final nextCode = LoginScreen.generateCaptchaCode(_captchaRng);
    final nextSeed = _captchaRng.nextInt(1 << 30);
    if (clearInput) {
      _captchaController.clear();
    }
    if (notify && mounted) {
      setState(() {
        _captchaCode = nextCode;
        _captchaSeed = nextSeed;
      });
    } else {
      _captchaCode = nextCode;
      _captchaSeed = nextSeed;
    }
  }

  @override
  void dispose() {
    _usernameController.dispose();
    _passwordController.dispose();
    _captchaController.dispose();
    super.dispose();
  }

  Future<void> _login() async {
    final username = _usernameController.text.trim();
    final password = _passwordController.text;

    if (username.isEmpty || password.isEmpty) {
      setState(() {
        _errorMessage = 'NIS/Username dan kata sandi tidak boleh kosong.';
      });
      return;
    }

    final captchaError = LoginScreen.validateCaptchaInput(
      _captchaController.text,
      _captchaCode,
    );
    if (captchaError != null) {
      if (captchaError.startsWith('Kode verifikasi tidak sesuai')) {
        _refreshCaptcha(clearInput: true, notify: false);
      }
      setState(() {
        _errorMessage = captchaError;
      });
      return;
    }

    setState(() {
      _isLoading = true;
      _errorMessage = '';
    });

    try {
      final response = await ApiService.login(username, password);

      if (response['success'] == true) {
        final token = response['data']['token'];
        await ApiService.saveToken(token);

        final user = response['data']['user'];
        if (user != null) {
          final prefs = await SharedPreferences.getInstance();
          await prefs.setBool(
            'is_pkl',
            user['is_pkl'] == true || user['is_pkl'] == 1,
          );
          final bool isGoogleConnected = (user['google_connected'] == true || user['google_connected'] == 1);
          await prefs.setBool(
            'is_google_connected',
            isGoogleConnected,
          );
        }

        if (!mounted) return;
        Navigator.pushReplacement(
          context,
          MaterialPageRoute(builder: (context) => const DashboardScreen()),
        );
      } else {
        _refreshCaptcha(clearInput: true, notify: false);
        setState(() {
          _errorMessage =
              response['message'] ?? 'Login gagal. Periksa kembali akun Anda.';
        });
      }
    } catch (e) {
      _refreshCaptcha(clearInput: true, notify: false);
      setState(() {
        _errorMessage =
            'Koneksi ke server gagal. Periksa jaringan atau URL server Anda.';
      });
    } finally {
      if (mounted) {
        setState(() {
          _isLoading = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final logoCachePx = (84 * MediaQuery.devicePixelRatioOf(context)).round();
    return Scaffold(
      backgroundColor: const Color(0xFF0A2540),
      body: DecoratedBox(
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            colors: [
              Color(0xFF07192F),
              Color(0xFF0A2540),
              Color(0xFF0F3460),
            ],
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
          ),
        ),
        child: LayoutBuilder(
          builder: (context, viewportConstraints) {
            return SingleChildScrollView(
              physics: const ClampingScrollPhysics(),
              child: ConstrainedBox(
                constraints: BoxConstraints(
                  minHeight: viewportConstraints.maxHeight,
                ),
                child: RepaintBoundary(
                  child: CustomPaint(
                    painter: _LoginBackgroundPatternPainter(),
                    child: SafeArea(
                      child: Padding(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 24.0,
                          vertical: 32.0,
                        ),
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          crossAxisAlignment: CrossAxisAlignment.center,
                          children: [
                            const SizedBox.shrink(),
                            Column(
                              mainAxisSize: MainAxisSize.min,
                              crossAxisAlignment: CrossAxisAlignment.center,
                              children: [

                            // School Logo
                            Container(
                              width: 84,
                              height: 84,
                              padding: const EdgeInsets.all(12),
                              decoration: BoxDecoration(
                                color: Colors.white,
                                borderRadius: BorderRadius.circular(24),
                                border: Border.all(
                                  color: Colors.white.withValues(alpha: 0.35),
                                  width: 2,
                                ),
                                boxShadow: [
                                  BoxShadow(
                                    color: Colors.black.withValues(alpha: 0.2),
                                    blurRadius: 12,
                                    offset: const Offset(0, 6),
                                  ),
                                ],
                              ),
                              child: Image.asset(
                                'assets/images/logo.webp',
                                cacheWidth: logoCachePx,
                                cacheHeight: logoCachePx,
                                fit: BoxFit.contain,
                                errorBuilder: (context, error, stackTrace) {
                                  return const Icon(
                                    Icons.school_rounded,
                                    size: 42,
                                    color: Color(0xFF0A2540),
                                  );
                                },
                              ),
                            ),
                            const SizedBox(height: 20),

                            // Title & Subtitle
                            const Text(
                              'Selamat Datang',
                              textAlign: TextAlign.center,
                              style: TextStyle(
                                fontSize: 28,
                                fontWeight: FontWeight.w800,
                                color: Colors.white,
                                letterSpacing: -0.5,
                              ),
                            ),
                            const SizedBox(height: 6),
                            const Text(
                              'Portal Ujian SMKN 1 Tirtamulya',
                              textAlign: TextAlign.center,
                              style: TextStyle(
                                fontSize: 14,
                                color: Color(0xFF94A3B8),
                                fontWeight: FontWeight.w500,
                              ),
                            ),
                            const SizedBox(height: 28),

                            // Login Form Card
                            Container(
                              decoration: BoxDecoration(
                                color: Colors.white,
                                borderRadius: BorderRadius.circular(24),
                                border: Border.all(
                                  color: const Color(0xFFE2E8F0),
                                  width: 1,
                                ),
                                boxShadow: [
                                  BoxShadow(
                                    color: Colors.black.withValues(alpha: 0.12),
                                    blurRadius: 14,
                                    offset: const Offset(0, 8),
                                  ),
                                ],
                              ),
                              padding: const EdgeInsets.all(24),
                                child: Column(
                                  crossAxisAlignment:
                                      CrossAxisAlignment.stretch,
                                  children: [
                                    const Text(
                                      'Silakan masuk dengan akun siswa untuk memulai ujian.',
                                      textAlign: TextAlign.center,
                                      style: TextStyle(
                                        fontSize: 13,
                                        fontWeight: FontWeight.w500,
                                        color: Color(0xFF64748B),
                                        height: 1.4,
                                      ),
                                    ),
                                    const SizedBox(height: 18),

                                    // Error Alert Banner
                                    AnimatedSize(
                                      duration: const Duration(
                                        milliseconds: 250,
                                      ),
                                      curve: Curves.easeInOut,
                                      child: _errorMessage.isNotEmpty
                                          ? Padding(
                                              padding: const EdgeInsets.only(
                                                bottom: 16.0,
                                              ),
                                              child: Container(
                                                padding:
                                                    const EdgeInsets.symmetric(
                                                      horizontal: 14,
                                                      vertical: 10,
                                                    ),
                                                decoration: BoxDecoration(
                                                  color: const Color(
                                                    0xFFFEF2F2,
                                                  ),
                                                  borderRadius:
                                                      BorderRadius.circular(12),
                                                  border: Border.all(
                                                    color: const Color(
                                                      0xFFFECACA,
                                                    ),
                                                  ),
                                                ),
                                                child: Row(
                                                  crossAxisAlignment:
                                                      CrossAxisAlignment.start,
                                                  children: [
                                                    const Icon(
                                                      Icons
                                                          .error_outline_rounded,
                                                      color: Color(0xFFDC2626),
                                                      size: 18,
                                                    ),
                                                    const SizedBox(width: 10),
                                                    Expanded(
                                                      child: Text(
                                                        _errorMessage,
                                                        style: const TextStyle(
                                                          color: Color(
                                                            0xFFB91C1C,
                                                          ),
                                                          fontSize: 12.5,
                                                          fontWeight:
                                                              FontWeight.w500,
                                                          height: 1.4,
                                                        ),
                                                      ),
                                                    ),
                                                  ],
                                                ),
                                              ),
                                            )
                                          : const SizedBox.shrink(),
                                    ),

                                    // Username / NIS Input
                                    const Text(
                                      'NIS / Username',
                                      style: TextStyle(
                                        fontSize: 12.5,
                                        fontWeight: FontWeight.w600,
                                        color: Color(0xFF334155),
                                      ),
                                    ),
                                    const SizedBox(height: 6),
                                    TextFormField(
                                      controller: _usernameController,
                                      style: const TextStyle(
                                        fontSize: 14.5,
                                        fontWeight: FontWeight.w500,
                                        color: Color(0xFF0F172A),
                                      ),
                                      onChanged: (v) => _errorMessage.isNotEmpty
                                          ? setState(() => _errorMessage = '')
                                          : null,
                                      decoration: InputDecoration(
                                        hintText: 'Masukkan NIS atau Username',
                                        hintStyle: const TextStyle(
                                          color: Color(0xFF94A3B8),
                                          fontSize: 13.5,
                                        ),
                                        prefixIcon: const Icon(
                                          Icons.person_outline_rounded,
                                          color: Color(0xFF64748B),
                                          size: 20,
                                        ),
                                        filled: true,
                                        fillColor: const Color(0xFFF8FAFC),
                                        contentPadding:
                                            const EdgeInsets.symmetric(
                                              horizontal: 16,
                                              vertical: 14,
                                            ),
                                        border: OutlineInputBorder(
                                          borderRadius: BorderRadius.circular(
                                            12,
                                          ),
                                          borderSide: const BorderSide(
                                            color: Color(0xFFE2E8F0),
                                          ),
                                        ),
                                        enabledBorder: OutlineInputBorder(
                                          borderRadius: BorderRadius.circular(
                                            12,
                                          ),
                                          borderSide: const BorderSide(
                                            color: Color(0xFFE2E8F0),
                                          ),
                                        ),
                                        focusedBorder: const OutlineInputBorder(
                                          borderRadius: BorderRadius.all(
                                            Radius.circular(12),
                                          ),
                                          borderSide: BorderSide(
                                            color: Color(0xFF0A2540),
                                            width: 1.8,
                                          ),
                                        ),
                                      ),
                                    ),
                                    const SizedBox(height: 16),

                                    // Password Input with Eye Toggle
                                    const Text(
                                      'Kata Sandi',
                                      style: TextStyle(
                                        fontSize: 12.5,
                                        fontWeight: FontWeight.w600,
                                        color: Color(0xFF334155),
                                      ),
                                    ),
                                    const SizedBox(height: 6),
                                    TextFormField(
                                      controller: _passwordController,
                                      obscureText: _obscurePassword,
                                      style: const TextStyle(
                                        fontSize: 14.5,
                                        fontWeight: FontWeight.w500,
                                        color: Color(0xFF0F172A),
                                      ),
                                      onChanged: (v) => _errorMessage.isNotEmpty
                                          ? setState(() => _errorMessage = '')
                                          : null,
                                      decoration: InputDecoration(
                                        hintText: 'Masukkan kata sandi',
                                        hintStyle: const TextStyle(
                                          color: Color(0xFF94A3B8),
                                          fontSize: 13.5,
                                        ),
                                        prefixIcon: const Icon(
                                          Icons.lock_outline_rounded,
                                          color: Color(0xFF64748B),
                                          size: 20,
                                        ),
                                        suffixIcon: IconButton(
                                          icon: Icon(
                                            _obscurePassword
                                                ? Icons.visibility_off_outlined
                                                : Icons.visibility_outlined,
                                            color: const Color(0xFF64748B),
                                            size: 20,
                                          ),
                                          onPressed: () {
                                            setState(() {
                                              _obscurePassword =
                                                  !_obscurePassword;
                                            });
                                          },
                                        ),
                                        filled: true,
                                        fillColor: const Color(0xFFF8FAFC),
                                        contentPadding:
                                            const EdgeInsets.symmetric(
                                              horizontal: 16,
                                              vertical: 14,
                                            ),
                                        border: OutlineInputBorder(
                                          borderRadius: BorderRadius.circular(
                                            12,
                                          ),
                                          borderSide: const BorderSide(
                                            color: Color(0xFFE2E8F0),
                                          ),
                                        ),
                                        enabledBorder: OutlineInputBorder(
                                          borderRadius: BorderRadius.circular(
                                            12,
                                          ),
                                          borderSide: const BorderSide(
                                            color: Color(0xFFE2E8F0),
                                          ),
                                        ),
                                        focusedBorder: const OutlineInputBorder(
                                          borderRadius: BorderRadius.all(
                                            Radius.circular(12),
                                          ),
                                          borderSide: BorderSide(
                                            color: Color(0xFF0A2540),
                                            width: 1.8,
                                          ),
                                        ),
                                      ),
                                    ),
                                    const SizedBox(height: 16),

                                    // Kode Verifikasi (5 karakter, sama seperti portal web)
                                    const Text(
                                      'Kode Verifikasi',
                                      style: TextStyle(
                                        fontSize: 12.5,
                                        fontWeight: FontWeight.w600,
                                        color: Color(0xFF334155),
                                      ),
                                    ),
                                    const SizedBox(height: 6),
                                    Row(
                                      children: [
                                        Expanded(
                                          child: Semantics(
                                            label: 'Kode Verifikasi',
                                            child: Container(
                                              height: 48,
                                              decoration: BoxDecoration(
                                                color: const Color(0xFFF5F8FF),
                                                borderRadius:
                                                    BorderRadius.circular(12),
                                                border: Border.all(
                                                  color: const Color(
                                                    0xFFE2E8F0,
                                                  ),
                                                ),
                                              ),
                                              clipBehavior: Clip.antiAlias,
                                              child: RepaintBoundary(
                                                child: CustomPaint(
                                                  painter: _CaptchaCodePainter(
                                                    code: _captchaCode,
                                                    seed: _captchaSeed,
                                                  ),
                                                  child: const SizedBox.expand(),
                                                ),
                                              ),
                                            ),
                                          ),
                                        ),
                                        const SizedBox(width: 10),
                                        SizedBox(
                                          height: 48,
                                          child: OutlinedButton.icon(
                                            onPressed: _isLoading
                                                ? null
                                                : () => _refreshCaptcha(),
                                            icon: const Icon(
                                              Icons.refresh_rounded,
                                              size: 17,
                                              color: Color(0xFF2457E8),
                                            ),
                                            label: const Text(
                                              'Ganti',
                                              style: TextStyle(
                                                fontSize: 12.5,
                                                fontWeight: FontWeight.w700,
                                                color: Color(0xFF334155),
                                              ),
                                            ),
                                            style: OutlinedButton.styleFrom(
                                              backgroundColor: const Color(
                                                0xFFF8FAFC,
                                              ),
                                              side: const BorderSide(
                                                color: Color(0xFFE2E8F0),
                                              ),
                                              padding:
                                                  const EdgeInsets.symmetric(
                                                    horizontal: 14,
                                                  ),
                                              shape: RoundedRectangleBorder(
                                                borderRadius:
                                                    BorderRadius.circular(12),
                                              ),
                                            ),
                                          ),
                                        ),
                                      ],
                                    ),
                                    const SizedBox(height: 10),
                                    TextFormField(
                                      controller: _captchaController,
                                      textCapitalization:
                                          TextCapitalization.characters,
                                      textInputAction: TextInputAction.done,
                                      autocorrect: false,
                                      enableSuggestions: false,
                                      onFieldSubmitted: (_) =>
                                          _isLoading ? null : _login(),
                                      inputFormatters: [
                                        FilteringTextInputFormatter.allow(
                                          RegExp(r'[a-zA-Z0-9]'),
                                        ),
                                        LengthLimitingTextInputFormatter(
                                          LoginScreen.captchaLength,
                                        ),
                                        _UpperCaseTextFormatter(),
                                      ],
                                      style: const TextStyle(
                                        fontSize: 14.5,
                                        fontWeight: FontWeight.w700,
                                        letterSpacing: 3.0,
                                        color: Color(0xFF0F172A),
                                      ),
                                      onChanged: (v) => _errorMessage.isNotEmpty
                                          ? setState(() => _errorMessage = '')
                                          : null,
                                      decoration: InputDecoration(
                                        hintText: 'Masukkan 5 karakter',
                                        hintStyle: const TextStyle(
                                          color: Color(0xFF94A3B8),
                                          fontSize: 13.5,
                                          fontWeight: FontWeight.w500,
                                          letterSpacing: 0.2,
                                        ),
                                        prefixIcon: const Icon(
                                          Icons.verified_user_outlined,
                                          color: Color(0xFF64748B),
                                          size: 20,
                                        ),
                                        filled: true,
                                        fillColor: const Color(0xFFF8FAFC),
                                        contentPadding:
                                            const EdgeInsets.symmetric(
                                              horizontal: 16,
                                              vertical: 14,
                                            ),
                                        border: OutlineInputBorder(
                                          borderRadius: BorderRadius.circular(
                                            12,
                                          ),
                                          borderSide: const BorderSide(
                                            color: Color(0xFFE2E8F0),
                                          ),
                                        ),
                                        enabledBorder: OutlineInputBorder(
                                          borderRadius: BorderRadius.circular(
                                            12,
                                          ),
                                          borderSide: const BorderSide(
                                            color: Color(0xFFE2E8F0),
                                          ),
                                        ),
                                        focusedBorder: const OutlineInputBorder(
                                          borderRadius: BorderRadius.all(
                                            Radius.circular(12),
                                          ),
                                          borderSide: BorderSide(
                                            color: Color(0xFF0A2540),
                                            width: 1.8,
                                          ),
                                        ),
                                      ),
                                    ),
                                    const SizedBox(height: 24),

                                    // Login Button
                                    SizedBox(
                                      height: 50,
                                      child: ElevatedButton(
                                        onPressed: _isLoading ? null : _login,
                                        style: ElevatedButton.styleFrom(
                                          backgroundColor: const Color(
                                            0xFF0A2540,
                                          ),
                                          foregroundColor: Colors.white,
                                          disabledBackgroundColor:
                                              const Color(0xFFA1A1AA),
                                          elevation: 0,
                                          shape: RoundedRectangleBorder(
                                            borderRadius:
                                                BorderRadius.circular(12),
                                          ),
                                          shadowColor: const Color(
                                            0xFF0A2540,
                                          ).withValues(alpha: 0.35),
                                        ),
                                        child: _isLoading
                                            ? const SizedBox(
                                                width: 22,
                                                height: 22,
                                                child:
                                                    CircularProgressIndicator(
                                                      strokeWidth: 2.2,
                                                      color: Colors.white,
                                                    ),
                                              )
                                            : const Text(
                                                'Masuk Sekarang',
                                                style: TextStyle(
                                                  fontSize: 15,
                                                  fontWeight: FontWeight.w700,
                                                  letterSpacing: 0.2,
                                                ),
                                              ),
                                      ),
                                    ),
                                  ],
                                ),
                              ),

                              ],
                            ),

                              // Footer
                              const Padding(
                                padding: EdgeInsets.only(top: 20.0),
                                child: Center(
                                  child: Row(
                                    mainAxisAlignment: MainAxisAlignment.center,
                                    children: [
                                      Icon(
                                        Icons.shield_outlined,
                                        size: 15,
                                        color: Color(0xFF94A3B8),
                                      ),
                                      SizedBox(width: 6),
                                      Flexible(
                                        child: Text(
                                          'Protected by ExaSatrya Engine',
                                          overflow: TextOverflow.ellipsis,
                                          style: TextStyle(
                                            fontSize: 12,
                                            color: Color(0xFF94A3B8),
                                            fontWeight: FontWeight.w500,
                                          ),
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                              ),
                          ],
                        ),
                      ),
                    ),
                  ),
                ),
              ),
            );
          },
        ),
      ),
    );
  }
}

class _LoginBackgroundPatternPainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    // 1. Ambient Glows (lightweight radial paints)
    final glowPaint1 = Paint()
      ..color = Colors.white.withValues(alpha: 0.04);
    canvas.drawCircle(Offset(size.width * 0.88, 50), 130, glowPaint1);

    final glowPaint2 = Paint()
      ..color = const Color(0xFF38BDF8).withValues(alpha: 0.05);
    canvas.drawCircle(Offset(size.width * 0.12, size.height * 0.75), 130, glowPaint2);

    // 2. Subtle Concentric Decorative Ring
    final ringPaint = Paint()
      ..color = Colors.white.withValues(alpha: 0.04)
      ..style = PaintingStyle.stroke
      ..strokeWidth = 1.0;
    canvas.drawCircle(Offset(size.width * 0.5, 120), 100, ringPaint);
    canvas.drawCircle(Offset(size.width * 0.5, 120), 160, ringPaint);

    // 3. Batched Dot Grid Matrix Pattern (1 draw call untuk CPU/GPU rendah)
    final dotPaint = Paint()
      ..color = Colors.white.withValues(alpha: 0.06)
      ..strokeWidth = 2.2
      ..strokeCap = StrokeCap.round;

    final points = <Offset>[];
    const double step = 24.0;
    for (double x = 14; x < size.width; x += step) {
      for (double y = 14; y < size.height - 10; y += step) {
        points.add(Offset(x, y));
      }
    }
    canvas.drawPoints(ui.PointMode.points, points, dotPaint);
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}

class _UpperCaseTextFormatter extends TextInputFormatter {
  @override
  TextEditingValue formatEditUpdate(
    TextEditingValue oldValue,
    TextEditingValue newValue,
  ) {
    return newValue.copyWith(text: newValue.text.toUpperCase());
  }
}

class _CaptchaCodePainter extends CustomPainter {
  final String code;
  final int seed;

  const _CaptchaCodePainter({required this.code, required this.seed});

  @override
  void paint(Canvas canvas, Size size) {
    final rng = Random(seed);
    const navy = Color(0xFF142C5C);
    const blue = Color(0xFF2457E8);
    const lightBlue = Color(0xFFB4CDF8);

    // Garis & titik pengganggu ringan (mengikuti gaya AdminAuthController)
    final linePaint = Paint()..strokeWidth = 1.2;
    for (int i = 0; i < 5; i++) {
      linePaint.color = (i % 2 == 0)
          ? lightBlue
          : blue.withValues(alpha: 0.35);
      canvas.drawLine(
        Offset(rng.nextDouble() * size.width, rng.nextDouble() * size.height),
        Offset(rng.nextDouble() * size.width, rng.nextDouble() * size.height),
        linePaint,
      );
    }

    final dotPaint = Paint()
      ..color = lightBlue
      ..strokeWidth = 2.0
      ..strokeCap = StrokeCap.round;
    final dots = List<Offset>.generate(
      45,
      (_) => Offset(
        rng.nextDouble() * size.width,
        rng.nextDouble() * size.height,
      ),
    );
    canvas.drawPoints(ui.PointMode.points, dots, dotPaint);

    // Gambar tiap karakter dengan rotasi & posisi vertikal sedikit acak
    if (code.isEmpty) return;
    final stepX = size.width / (code.length + 1);
    for (int i = 0; i < code.length; i++) {
      final tp = TextPainter(
        text: TextSpan(
          text: code[i],
          style: TextStyle(
            fontSize: 22,
            fontWeight: FontWeight.w800,
            color: i % 2 == 0 ? navy : blue,
            letterSpacing: 1.0,
          ),
        ),
        textDirection: TextDirection.ltr,
      )..layout();

      final dx = stepX * (i + 1) + (rng.nextDouble() * 4 - 2);
      final dy = (size.height / 2) + (rng.nextDouble() * 8 - 4);
      final angle = (rng.nextDouble() * 0.44) - 0.22; // sekitar +-12.5 derajat

      canvas.save();
      canvas.translate(dx, dy);
      canvas.rotate(angle);
      tp.paint(canvas, Offset(-tp.width / 2, -tp.height / 2));
      canvas.restore();
    }
  }

  @override
  bool shouldRepaint(covariant _CaptchaCodePainter oldDelegate) {
    return oldDelegate.code != code || oldDelegate.seed != seed;
  }
}
