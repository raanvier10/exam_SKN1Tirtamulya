import 'dart:io' show Platform;
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:webview_flutter/webview_flutter.dart';
import '../api_service.dart';

class GoogleAuthScreen extends StatefulWidget {
  const GoogleAuthScreen({super.key});

  @override
  State<GoogleAuthScreen> createState() => _GoogleAuthScreenState();
}

class _GoogleAuthScreenState extends State<GoogleAuthScreen> {
  static const platform = MethodChannel('exam.bro/secure');
  late final WebViewController _controller;
  bool _isLoading = true;
  bool _hasSaved = false;

  @override
  void initState() {
    super.initState();
    if (!kIsWeb) {
      _controller = WebViewController()
        ..setJavaScriptMode(JavaScriptMode.unrestricted)
        ..setUserAgent(
          Platform.isIOS
              ? 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.5 Mobile/15E148 Safari/604.1'
              : 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Mobile Safari/537.36',
        )
        ..setNavigationDelegate(
          NavigationDelegate(
            onPageStarted: (String url) {
              if (mounted) setState(() => _isLoading = true);
            },
            onPageFinished: (String url) async {
              if (mounted) setState(() => _isLoading = false);

              // 1. Simpan fisik (flush) cookies ke disk agar sesi login tidak hilang saat app di-close/restart
              try {
                await platform.invokeMethod('flushCookies');
              } catch (_) {}

              // 2. Deteksi otomatis keberhasilan login Google
              // Setelah berhasil login, Google akan redirect ke myaccount.google.com atau ManageAccount
              final uri = Uri.tryParse(url);
              final host = uri?.host.toLowerCase() ?? '';
              final path = uri?.path.toLowerCase() ?? '';

              final bool isLoginSuccess = host.contains('myaccount.google.com') ||
                  path.contains('manageaccount') ||
                  url.contains('CheckCookie') ||
                  (host.contains('accounts.google.com') && (path == '/' || path.isEmpty || path.startsWith('/b/')));

              if (isLoginSuccess && !_hasSaved) {
                _hasSaved = true;
                await _handleSaveAndExit(autoDetected: true);
              }
            },
            onWebResourceError: (WebResourceError error) {
              if (mounted) setState(() => _isLoading = false);
            },
          ),
        )
        ..loadRequest(Uri.parse('https://accounts.google.com/ServiceLogin'));
    }
  }

  Future<void> _handleSaveAndExit({bool autoDetected = false}) async {
    // 1. Paksa flush cookie ke penyimpanan disk permanen
    try {
      await platform.invokeMethod('flushCookies');
    } catch (_) {}

    // 2. Simpan status di SharedPreferences lokal HP
    final prefs = await SharedPreferences.getInstance();
    await prefs.setBool('is_google_connected', true);

    // 3. Simpan status di Database Server
    try {
      await ApiService.updateGoogleStatus(true);
    } catch (e) {
      // Abaikan jika offline
    }

    if (!mounted) return;
    Navigator.pop(context, true);

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Row(
          children: [
            const Icon(Icons.check_circle_rounded, color: Colors.white, size: 18),
            const SizedBox(width: 8),
            Expanded(
              child: Text(
                autoDetected
                    ? 'Akun Google berhasil terhubung otomatis & tersimpan permanen!'
                    : 'Akun Google berhasil terhubung & siap ujian!',
                style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13),
              ),
            ),
          ],
        ),
        backgroundColor: const Color(0xFF10B981),
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
        duration: const Duration(seconds: 4),
      ),
    );
  }

  Future<void> _handleDisconnect() async {
    final bool? confirm = await showDialog<bool>(
      context: context,
      builder: (dialogCtx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Text('Keluar dari Akun Google?', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 17)),
        content: const Text(
          'Semua sesi Google di perangkat ini akan dihapus. Anda harus login kembali jika ingin mengakses Google Form yang dibatasi.',
          style: TextStyle(fontSize: 13, color: Color(0xFF64748B), height: 1.4),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(dialogCtx, false),
            child: const Text('Batal', style: TextStyle(fontWeight: FontWeight.w700, color: Color(0xFF64748B))),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(dialogCtx, true),
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFFEF4444),
              foregroundColor: Colors.white,
              elevation: 0,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
            ),
            child: const Text('Ya, Keluar Akun', style: TextStyle(fontWeight: FontWeight.w700)),
          ),
        ],
      ),
    );

    if (confirm != true) return;

    final cookieManager = WebViewCookieManager();
    await cookieManager.clearCookies();

    try {
      await platform.invokeMethod('flushCookies');
    } catch (_) {}

    final prefs = await SharedPreferences.getInstance();
    await prefs.setBool('is_google_connected', false);

    try {
      await ApiService.updateGoogleStatus(false);
    } catch (_) {}

    _hasSaved = false;
    _controller.loadRequest(Uri.parse('https://accounts.google.com/ServiceLogin'));

    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: const Text('Sesi akun Google telah dihapus.'),
        backgroundColor: const Color(0xFF64748B),
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return PopScope(
      canPop: true,
      onPopInvokedWithResult: (didPop, result) async {
        // Saat tombol back ditekan, pastikan flush cookies terpanggil
        try {
          await platform.invokeMethod('flushCookies');
        } catch (_) {}
      },
      child: Scaffold(
        backgroundColor: const Color(0xFFF8FAFC),
        appBar: AppBar(
          backgroundColor: Colors.white,
          elevation: 0,
          leading: IconButton(
            icon: const Icon(Icons.arrow_back_rounded, color: Color(0xFF0F172A)),
            onPressed: () async {
              try {
                await platform.invokeMethod('flushCookies');
              } catch (_) {}
              if (!context.mounted) return;
              Navigator.pop(context, _hasSaved);
            },
          ),
          title: const Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                'Login Akun Google',
                style: TextStyle(
                  color: Color(0xFF0F172A),
                  fontWeight: FontWeight.w800,
                  fontSize: 16,
                ),
              ),
              Text(
                'Tersimpan permanen untuk Google Form',
                style: TextStyle(
                  color: Color(0xFF64748B),
                  fontSize: 12,
                  fontWeight: FontWeight.w500,
                ),
              ),
            ],
          ),
          actions: [
            // Tombol Opsi: Hapus Sesi / Ganti Akun
            IconButton(
              icon: const Icon(Icons.logout_rounded, color: Color(0xFFEF4444), size: 20),
              tooltip: 'Keluar / Ganti Akun Google',
              onPressed: _handleDisconnect,
            ),
            Padding(
              padding: const EdgeInsets.only(right: 12.0, top: 8, bottom: 8),
              child: ElevatedButton.icon(
                icon: const Icon(Icons.check_rounded, size: 16),
                label: const Text('Selesai', style: TextStyle(fontWeight: FontWeight.w700)),
                onPressed: () => _handleSaveAndExit(autoDetected: false),
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF10B981),
                  foregroundColor: Colors.white,
                  elevation: 0,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                ),
              ),
            ),
          ],
          bottom: PreferredSize(
            preferredSize: const Size.fromHeight(1),
            child: Container(color: const Color(0xFFE2E8F0), height: 1),
          ),
        ),
        body: kIsWeb
            ? Center(
                child: Container(
                  margin: const EdgeInsets.all(24),
                  padding: const EdgeInsets.all(24),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(16),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withValues(alpha: 0.05),
                        blurRadius: 16,
                        offset: const Offset(0, 4),
                      ),
                    ],
                  ),
                  child: const Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(Icons.phone_iphone_rounded, size: 56, color: Color(0xFF2563EB)),
                      SizedBox(height: 16),
                      Text(
                        'Hanya Didukung di Perangkat Mobile',
                        textAlign: TextAlign.center,
                        style: TextStyle(fontWeight: FontWeight.w800, fontSize: 17, color: Color(0xFF0F172A)),
                      ),
                      SizedBox(height: 8),
                      Text(
                        'Fitur WebView Google Auth hanya dapat berjalan di perangkat mobile (Android / iOS).\\nDi browser Web desktop, WebView tidak didukung.',
                        textAlign: TextAlign.center,
                        style: TextStyle(color: Color(0xFF64748B), fontSize: 13, height: 1.4),
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
                      color: Color(0xFF0A2540),
                      backgroundColor: Color(0xFFE2E8F0),
                    ),
                ],
              ),
      ),
    );
  }
}
