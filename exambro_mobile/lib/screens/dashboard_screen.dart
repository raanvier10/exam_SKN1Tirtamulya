import 'dart:async';
import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../api_service.dart';
import 'google_auth_screen.dart';
import 'login_screen.dart';
import 'security_check_screen.dart';

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({super.key});

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  List<dynamic> _exams = [];
  bool _isLoading = true;
  bool _isGoogleConnected = false;
  Timer? _timer;

  @override
  void initState() {
    super.initState();
    _checkGoogleStatus();
    _fetchExams();
    _timer = Timer.periodic(const Duration(seconds: 1), (timer) {
      if (mounted) setState(() {});
    });
  }

  @override
  void dispose() {
    _timer?.cancel();
    super.dispose();
  }

  Future<void> _checkGoogleStatus() async {
    final prefs = await SharedPreferences.getInstance();
    bool localConnected = prefs.getBool('is_google_connected') ?? false;
    if (mounted) {
      setState(() {
        _isGoogleConnected = localConnected;
      });
    }

    // Sinkronisasi status dari Database Server
    try {
      final userRes = await ApiService.getMe();
      if (userRes['success'] == true && userRes['data'] != null) {
        final userData = userRes['data'];
        bool dbConnected = (userData['google_connected'] == true || userData['google_connected'] == 1);
        if (dbConnected != localConnected) {
          await prefs.setBool('is_google_connected', dbConnected);
          if (mounted) {
            setState(() {
              _isGoogleConnected = dbConnected;
            });
          }
        }
        await prefs.setBool('is_pkl', userData['is_pkl'] == true || userData['is_pkl'] == 1);
      }
    } catch (e) {
      // Ignored if offline
    }
  }

  Future<void> _fetchExams() async {
    try {
      final response = await ApiService.getExamsToday();
      if (response['success'] == true) {
        if (mounted) {
          setState(() {
            _exams = response['data'] ?? [];
            _isLoading = false;
          });
        }
      }
    } catch (e) {
      if (!mounted) return;
      setState(() => _isLoading = false);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: const Text('Gagal memperbarui jadwal ujian'),
          backgroundColor: const Color(0xFFEF4444),
          behavior: SnackBarBehavior.floating,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
        ),
      );
    }
  }

  void _confirmLogout() {
    showDialog(
      context: context,
      builder: (dialogContext) => Dialog(
        backgroundColor: Colors.white,
        surfaceTintColor: Colors.transparent,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(28),
        ),
        insetPadding: const EdgeInsets.symmetric(horizontal: 24, vertical: 24),
        child: Padding(
          padding: const EdgeInsets.all(24.0),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              // 1. Sleek Floating Icon with Subtle Glow
              Container(
                width: 64,
                height: 64,
                decoration: BoxDecoration(
                  color: const Color(0xFFFEE2E2),
                  shape: BoxShape.circle,
                  border: Border.all(color: const Color(0xFFFECACA), width: 2),
                  boxShadow: [
                    BoxShadow(
                      color: const Color(0xFFEF4444).withValues(alpha: 0.15),
                      blurRadius: 16,
                      offset: const Offset(0, 6),
                    ),
                  ],
                ),
                child: const Icon(
                  Icons.logout_rounded,
                  color: Color(0xFFDC2626),
                  size: 28,
                ),
              ),

              const SizedBox(height: 18),

              // 2. Title & Description
              const Text(
                'Keluar Aplikasi?',
                textAlign: TextAlign.center,
                style: TextStyle(
                  fontWeight: FontWeight.w800,
                  fontSize: 19,
                  color: Color(0xFF0F172A),
                  letterSpacing: -0.4,
                ),
              ),

              const SizedBox(height: 8),

              const Text(
                'Sesi akun Anda akan ditutup. Anda harus login kembali menggunakan NIS & Password untuk mengakses portal ujian.',
                textAlign: TextAlign.center,
                style: TextStyle(
                  fontSize: 13,
                  color: Color(0xFF64748B),
                  height: 1.45,
                ),
              ),

              const SizedBox(height: 18),

              // 3. Info Notice Card
              Container(
                width: double.infinity,
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                decoration: BoxDecoration(
                  color: const Color(0xFFF8FAFC),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: const Color(0xFFE2E8F0)),
                ),
                child: const Row(
                  children: [
                    Icon(Icons.shield_outlined, size: 16, color: Color(0xFF64748B)),
                    SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        'Jadwal ujian tersimpan aman di server',
                        style: TextStyle(
                          fontSize: 11.5,
                          fontWeight: FontWeight.w600,
                          color: Color(0xFF475569),
                        ),
                      ),
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 24),

              // 4. Enhanced Dual Action Buttons (Batal vs Ya, Keluar)
              Row(
                children: [
                  Expanded(
                    child: SizedBox(
                      height: 46,
                      child: OutlinedButton(
                        onPressed: () => Navigator.pop(dialogContext),
                        style: OutlinedButton.styleFrom(
                          foregroundColor: const Color(0xFF475569),
                          side: const BorderSide(color: Color(0xFFCBD5E1), width: 1.2),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(14),
                          ),
                          elevation: 0,
                        ),
                        child: const Text(
                          'Batal',
                          style: TextStyle(
                            fontSize: 13.5,
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: SizedBox(
                      height: 46,
                      child: ElevatedButton(
                        onPressed: () async {
                          Navigator.pop(dialogContext);
                          await ApiService.removeToken();
                          if (!mounted) return;
                          Navigator.pushReplacement(
                            context,
                            MaterialPageRoute(builder: (context) => const LoginScreen()),
                          );
                        },
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFFEF4444),
                          foregroundColor: Colors.white,
                          elevation: 2,
                          shadowColor: const Color(0xFFEF4444).withValues(alpha: 0.35),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(14),
                          ),
                        ),
                        child: const Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(Icons.logout_rounded, size: 16),
                            SizedBox(width: 6),
                            Text(
                              'Ya, Keluar',
                              style: TextStyle(
                                fontSize: 13.5,
                                fontWeight: FontWeight.w800,
                                letterSpacing: 0.2,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  String _formatDayDate() {
    final now = DateTime.now();
    const days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
    const months = [
      'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
      'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    final dayName = days[now.weekday - 1];
    final monthName = months[now.month - 1];
    return '$dayName, ${now.day} $monthName ${now.year}';
  }

  @override
  Widget build(BuildContext context) {
    return PopScope(
      canPop: false,
      onPopInvokedWithResult: (didPop, result) {
        if (didPop) return;
        _confirmLogout();
      },
      child: Scaffold(
        backgroundColor: const Color(0xFFF8FAFC),
        body: CustomScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          slivers: [
            // 1. Hero Header Sliver
            SliverToBoxAdapter(
              child: _buildHeroHeader(),
            ),

            // 2. Google Pre-Auth Quick Banner with Active Status
            SliverToBoxAdapter(
              child: Padding(
                padding: const EdgeInsets.fromLTRB(20, 16, 20, 0),
                child: _buildGoogleAuthCard(),
              ),
            ),

            // 3. Main Content Body
            SliverPadding(
              padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
              sliver: _isLoading
                  ? const SliverFillRemaining(
                      hasScrollBody: false,
                      child: Center(
                        child: CircularProgressIndicator(
                          strokeWidth: 3,
                          color: Color(0xFF0A2540),
                        ),
                      ),
                    )
                  : _exams.isEmpty
                      ? SliverFillRemaining(
                          hasScrollBody: false,
                          child: _buildEmptyState(),
                        )
                      : SliverList(
                          delegate: SliverChildBuilderDelegate(
                            (context, index) => _buildModernExamCard(_exams[index]),
                            childCount: _exams.length,
                          ),
                        ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildHeroHeader() {
    return Container(
      width: double.infinity,
      decoration: const BoxDecoration(
        color: Color(0xFF0A2540),
        borderRadius: BorderRadius.only(
          bottomLeft: Radius.circular(32),
          bottomRight: Radius.circular(32),
        ),
      ),
      child: SafeArea(
        bottom: false,
        child: Padding(
          padding: const EdgeInsets.fromLTRB(20, 16, 20, 24),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Top Bar
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(7),
                        decoration: BoxDecoration(
                          color: Colors.white.withValues(alpha: 0.1),
                          borderRadius: BorderRadius.circular(10),
                          border: Border.all(color: Colors.white.withValues(alpha: 0.15)),
                        ),
                        child: Image.asset(
                          'assets/images/logo.webp',
                          width: 22,
                          height: 22,
                          fit: BoxFit.contain,
                        ),
                      ),
                      const SizedBox(width: 10),
                      const Text(
                        'ExaSatria Portal',
                        style: TextStyle(
                          color: Colors.white,
                          fontSize: 16,
                          fontWeight: FontWeight.w800,
                          letterSpacing: -0.3,
                        ),
                      ),
                    ],
                  ),
                  Row(
                    children: [
                      // Refresh Button
                      IconButton.filledTonal(
                        onPressed: _fetchExams,
                        style: IconButton.styleFrom(
                          backgroundColor: Colors.white.withValues(alpha: 0.1),
                          foregroundColor: Colors.white,
                          padding: const EdgeInsets.all(8),
                          minimumSize: const Size(36, 36),
                          tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                        ),
                        icon: const Icon(Icons.refresh_rounded, size: 18),
                      ),
                      const SizedBox(width: 8),
                      // Logout Button
                      IconButton.filledTonal(
                        onPressed: _confirmLogout,
                        style: IconButton.styleFrom(
                          backgroundColor: const Color(0xFFEF4444).withValues(alpha: 0.15),
                          foregroundColor: const Color(0xFFFCA5A5),
                          padding: const EdgeInsets.all(8),
                          minimumSize: const Size(36, 36),
                          tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                        ),
                        icon: const Icon(Icons.power_settings_new_rounded, size: 18),
                      ),
                    ],
                  ),
                ],
              ),

              const SizedBox(height: 20),

              // Date & Greeting
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                decoration: BoxDecoration(
                  color: Colors.white.withValues(alpha: 0.08),
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    const Icon(Icons.calendar_today_rounded, size: 12, color: Color(0xFF38BDF8)),
                    const SizedBox(width: 6),
                    Text(
                      _formatDayDate(),
                      style: const TextStyle(
                        color: Color(0xFF94A3B8),
                        fontSize: 11.5,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 10),

              const Text(
                'Jadwal Ujian Hari Ini',
                style: TextStyle(
                  color: Colors.white,
                  fontSize: 22,
                  fontWeight: FontWeight.w800,
                  letterSpacing: -0.5,
                ),
              ),

              const SizedBox(height: 4),

              const Text(
                'Pilih ujian di bawah dan pastikan koneksi stabil sebelum memulai.',
                style: TextStyle(
                  color: Color(0xFF94A3B8),
                  fontSize: 12.5,
                  height: 1.4,
                ),
              ),

              const SizedBox(height: 14),

              // Security Enclave Info Pill
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                decoration: BoxDecoration(
                  color: const Color(0xFF132F4C).withValues(alpha: 0.6),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(
                    color: const Color(0xFF38BDF8).withValues(alpha: 0.2),
                  ),
                ),
                child: const Row(
                  children: [
                    Icon(Icons.security_rounded, size: 15, color: Color(0xFF38BDF8)),
                    SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        'Kiosk Lock & Anti-Cheat Aktif',
                        style: TextStyle(
                          color: Color(0xFFE2E8F0),
                          fontSize: 11.5,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ),
                    Icon(Icons.check_circle_rounded, size: 13, color: Color(0xFF10B981)),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildGoogleAuthCard() {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
      decoration: BoxDecoration(
        color: _isGoogleConnected ? const Color(0xFFF0FDF4) : Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
          color: _isGoogleConnected ? const Color(0xFFBBF7D0) : const Color(0xFFE2E8F0),
          width: 1.2,
        ),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF0F172A).withValues(alpha: 0.03),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(8),
            decoration: BoxDecoration(
              color: _isGoogleConnected ? const Color(0xFFDCFCE7) : const Color(0xFFEFF6FF),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(
              _isGoogleConnected ? Icons.verified_user_rounded : Icons.account_circle_rounded,
              color: _isGoogleConnected ? const Color(0xFF16A34A) : const Color(0xFF2563EB),
              size: 22,
            ),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisSize: MainAxisSize.min,
              children: [
                Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Flexible(
                      child: Text(
                        _isGoogleConnected ? 'Akun Google Terhubung' : 'Akun Google',
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: TextStyle(
                          fontSize: 13,
                          fontWeight: FontWeight.w800,
                          color: _isGoogleConnected ? const Color(0xFF15803D) : const Color(0xFF0F172A),
                        ),
                      ),
                    ),
                    if (_isGoogleConnected) ...[
                      const SizedBox(width: 4),
                      const Icon(Icons.check_circle, size: 13, color: Color(0xFF16A34A)),
                    ],
                  ],
                ),
                const SizedBox(height: 2),
                Text(
                  _isGoogleConnected
                      ? 'Sesi aktif — Siap buka Google Form'
                      : 'Login sekali agar form terbuka otomatis',
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: TextStyle(
                    fontSize: 11,
                    color: _isGoogleConnected ? const Color(0xFF166534) : const Color(0xFF64748B),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(width: 8),
          ElevatedButton(
            onPressed: () async {
              final result = await Navigator.push<bool>(
                context,
                MaterialPageRoute(builder: (context) => const GoogleAuthScreen()),
              );
              if (result == true) {
                _checkGoogleStatus();
              }
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: _isGoogleConnected ? const Color(0xFFDCFCE7) : const Color(0xFF2563EB),
              foregroundColor: _isGoogleConnected ? const Color(0xFF15803D) : Colors.white,
              elevation: 0,
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
              minimumSize: const Size(60, 32),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(8),
                side: _isGoogleConnected
                    ? const BorderSide(color: Color(0xFF86EFAC))
                    : BorderSide.none,
              ),
            ),
            child: Text(
              _isGoogleConnected ? 'Ganti' : 'Hubungkan',
              style: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.w700),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildModernExamCard(dynamic exam) {
    DateTime startTime = DateTime.parse(exam['start_at']).toLocal();
    DateTime endTime = DateTime.parse(exam['end_at']).toLocal();
    DateTime now = DateTime.now();

    bool isCompletedByStudent = (exam['student_status'] == 'finished');
    bool isLockedByViolation = (exam['student_status'] == 'locked');
    bool isStarted = now.isAfter(startTime);
    bool isFinished = now.isAfter(endTime) || isCompletedByStudent;

    Duration diff = startTime.difference(now);
    String countdown = "";
    if (!isStarted && !isCompletedByStudent && !isLockedByViolation) {
      final hours = diff.inHours.toString().padLeft(2, '0');
      final minutes = (diff.inMinutes % 60).toString().padLeft(2, '0');
      final seconds = (diff.inSeconds % 60).toString().padLeft(2, '0');
      countdown = "$hours : $minutes : $seconds";
    }

    final duration = exam['duration'] ?? 60;
    final maxViolation = exam['max_violation'] ?? 3;

    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: isLockedByViolation ? const Color(0xFFFECACA) : const Color(0xFFE2E8F0)),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF0F172A).withValues(alpha: 0.04),
            blurRadius: 14,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header Card with Status Badge
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 16, 16, 10),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        exam['title'] ?? 'Ujian Tanpa Judul',
                        style: const TextStyle(
                          fontSize: 17,
                          fontWeight: FontWeight.w800,
                          color: Color(0xFF0F172A),
                          letterSpacing: -0.4,
                        ),
                      ),
                      if (exam['description'] != null && exam['description'].toString().isNotEmpty) ...[
                        const SizedBox(height: 4),
                        Text(
                          exam['description'],
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                          style: const TextStyle(
                            fontSize: 12.5,
                            color: Color(0xFF64748B),
                            height: 1.35,
                          ),
                        ),
                      ],
                    ],
                  ),
                ),
                const SizedBox(width: 10),
                // Status Pill
                _buildStatusBadge(
                  isStarted: isStarted,
                  isFinished: isFinished,
                  isCompleted: isCompletedByStudent,
                  isLocked: isLockedByViolation,
                ),
              ],
            ),
          ),

          // Divider
          const Divider(color: Color(0xFFF1F5F9), height: 1),

          // Meta Info Grid (3 Columns)
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
            child: Row(
              children: [
                _buildMetaItem(
                  icon: Icons.timer_outlined,
                  label: 'DURASI',
                  value: '$duration Mnt',
                ),
                Container(height: 24, width: 1, color: const Color(0xFFE2E8F0)),
                _buildMetaItem(
                  icon: Icons.schedule_rounded,
                  label: 'JAM MULAI',
                  value: '${startTime.hour.toString().padLeft(2, '0')}:${startTime.minute.toString().padLeft(2, '0')}',
                ),
                Container(height: 24, width: 1, color: const Color(0xFFE2E8F0)),
                _buildMetaItem(
                  icon: Icons.shield_outlined,
                  label: 'TOLERANSI',
                  value: '$maxViolation Kali',
                ),
              ],
            ),
          ),

          // Countdown banner if not started
          if (!isFinished && !isStarted && !isLockedByViolation)
            Container(
              width: double.infinity,
              margin: const EdgeInsets.symmetric(horizontal: 16),
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
              decoration: BoxDecoration(
                color: const Color(0xFFFEF3C7),
                borderRadius: BorderRadius.circular(10),
                border: Border.all(color: const Color(0xFFFDE68A)),
              ),
              child: Row(
                children: [
                  const Icon(Icons.access_time_filled_rounded, size: 15, color: Color(0xFFD97706)),
                  const SizedBox(width: 6),
                  const Text(
                    'Dimulai dalam:',
                    style: TextStyle(
                      fontSize: 11.5,
                      fontWeight: FontWeight.w600,
                      color: Color(0xFF92400E),
                    ),
                  ),
                  const Spacer(),
                  Text(
                    countdown,
                    style: const TextStyle(
                      fontSize: 12.5,
                      fontWeight: FontWeight.w800,
                      color: Color(0xFFB45309),
                      letterSpacing: 0.5,
                    ),
                  ),
                ],
              ),
            ),

          // Action Button
          Padding(
            padding: const EdgeInsets.all(16),
            child: SizedBox(
              width: double.infinity,
              height: 48,
              child: isLockedByViolation
                  ? ElevatedButton(
                      onPressed: () {
                        showDialog(
                          context: context,
                          builder: (ctx) => AlertDialog(
                            backgroundColor: const Color(0xFF0F172A),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(20),
                              side: const BorderSide(color: Color(0xFFEF4444)),
                            ),
                            title: const Row(
                              children: [
                                Icon(Icons.lock_rounded, color: Color(0xFFEF4444)),
                                SizedBox(width: 10),
                                Text(
                                  'Ujian Terkunci',
                                  style: TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold),
                                ),
                              ],
                            ),
                            content: const Text(
                              'Sesi ujian Anda telah dikunci karena melebihi batas toleransi pelanggaran keamanan. Silakan temui pengawas atau guru IT di ruangan untuk membuka kunci.',
                              style: TextStyle(color: Color(0xFF94A3B8), fontSize: 13, height: 1.4),
                            ),
                            actions: [
                              TextButton(
                                onPressed: () => Navigator.pop(ctx),
                                child: const Text(
                                  'Mengerti',
                                  style: TextStyle(color: Color(0xFF38BDF8), fontWeight: FontWeight.bold),
                                ),
                              ),
                            ],
                          ),
                        );
                      },
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFFFEF2F2),
                        foregroundColor: const Color(0xFFDC2626),
                        elevation: 0,
                        side: const BorderSide(color: Color(0xFFFCA5A5)),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                      child: const Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(Icons.lock_rounded, size: 17, color: Color(0xFFDC2626)),
                          SizedBox(width: 8),
                          Text(
                            'TERKUNCI - HUBUNGI PENGAWAS',
                            style: TextStyle(fontSize: 12.5, fontWeight: FontWeight.w800, letterSpacing: 0.3),
                          ),
                        ],
                      ),
                    )
                  : isCompletedByStudent
                      ? Container(
                          decoration: BoxDecoration(
                            color: const Color(0xFFF0FDF4),
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(color: const Color(0xFFBBF7D0)),
                          ),
                          child: const Row(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Icon(Icons.check_circle_rounded, size: 18, color: Color(0xFF16A34A)),
                              SizedBox(width: 8),
                              Text(
                                'SUDAH DIKERJAKAN',
                                style: TextStyle(
                                  fontSize: 13.5,
                                  fontWeight: FontWeight.w800,
                                  color: Color(0xFF15803D),
                                  letterSpacing: 0.3,
                                ),
                              ),
                            ],
                          ),
                        )
                      : isFinished
                          ? ElevatedButton(
                              onPressed: null,
                              style: ElevatedButton.styleFrom(
                                backgroundColor: const Color(0xFFF1F5F9),
                                disabledBackgroundColor: const Color(0xFFF1F5F9),
                                disabledForegroundColor: const Color(0xFF94A3B8),
                                elevation: 0,
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                              ),
                              child: const Row(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  Icon(Icons.check_circle_outline_rounded, size: 17),
                                  SizedBox(width: 6),
                                  Text('UJIAN SELESAI', style: TextStyle(fontSize: 13.5, fontWeight: FontWeight.w700)),
                                ],
                              ),
                            )
                          : !isStarted
                              ? ElevatedButton(
                                  onPressed: null,
                                  style: ElevatedButton.styleFrom(
                                    backgroundColor: const Color(0xFFF1F5F9),
                                    disabledBackgroundColor: const Color(0xFFF1F5F9),
                                    disabledForegroundColor: const Color(0xFF94A3B8),
                                    elevation: 0,
                                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                  ),
                                  child: const Row(
                                    mainAxisAlignment: MainAxisAlignment.center,
                                    children: [
                                      Icon(Icons.lock_clock_rounded, size: 17),
                                      SizedBox(width: 6),
                                      Text('BELUM DIMULAI', style: TextStyle(fontSize: 13.5, fontWeight: FontWeight.w700)),
                                    ],
                                  ),
                                )
                              : ElevatedButton(
                                  onPressed: () async {
                                    final result = await Navigator.push(
                                      context,
                                      MaterialPageRoute(
                                        builder: (context) => SecurityCheckScreen(exam: exam),
                                      ),
                                    );
                                    if (result == 'locked' && mounted) {
                                      setState(() {
                                        exam['student_status'] = 'locked';
                                      });
                                    }
                                    _fetchExams();
                                  },
                                  style: ElevatedButton.styleFrom(
                                    backgroundColor: const Color(0xFF4F46E5),
                                    foregroundColor: Colors.white,
                                    elevation: 0,
                                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                  ),
                                  child: const Row(
                                    mainAxisAlignment: MainAxisAlignment.center,
                                    children: [
                                      Icon(Icons.play_arrow_rounded, size: 19),
                                      SizedBox(width: 4),
                                      Text(
                                        'KERJAKAN UJIAN',
                                        style: TextStyle(
                                          fontSize: 13.5,
                                          fontWeight: FontWeight.w800,
                                          letterSpacing: 0.3,
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildStatusBadge({
    required bool isStarted,
    required bool isFinished,
    required bool isCompleted,
    bool isLocked = false,
  }) {
    if (isLocked) {
      return Container(
        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
        decoration: BoxDecoration(
          color: const Color(0xFFFEE2E2),
          borderRadius: BorderRadius.circular(6),
          border: Border.all(color: const Color(0xFFFCA5A5)),
        ),
        child: const Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(Icons.lock_rounded, size: 11, color: Color(0xFFDC2626)),
            SizedBox(width: 4),
            Text(
              'Terkunci',
              style: TextStyle(color: Color(0xFFDC2626), fontSize: 11, fontWeight: FontWeight.w700),
            ),
          ],
        ),
      );
    }
    if (isCompleted) {
      return Container(
        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
        decoration: BoxDecoration(
          color: const Color(0xFFECFDF5),
          borderRadius: BorderRadius.circular(6),
          border: Border.all(color: const Color(0xFFA7F3D0)),
        ),
        child: const Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(Icons.check_circle_rounded, size: 11, color: Color(0xFF10B981)),
            SizedBox(width: 4),
            Text(
              'Selesai',
              style: TextStyle(color: Color(0xFF047857), fontSize: 11, fontWeight: FontWeight.w700),
            ),
          ],
        ),
      );
    }

    if (isFinished) {
      return Container(
        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
        decoration: BoxDecoration(
          color: const Color(0xFFF1F5F9),
          borderRadius: BorderRadius.circular(6),
        ),
        child: const Text(
          'Selesai',
          style: TextStyle(color: Color(0xFF64748B), fontSize: 11, fontWeight: FontWeight.w700),
        ),
      );
    }

    if (isStarted) {
      return Container(
        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
        decoration: BoxDecoration(
          color: const Color(0xFFECFDF5),
          borderRadius: BorderRadius.circular(6),
          border: Border.all(color: const Color(0xFFA7F3D0)),
        ),
        child: const Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(Icons.fiber_manual_record, size: 7, color: Color(0xFF10B981)),
            SizedBox(width: 4),
            Text(
              'Aktif',
              style: TextStyle(color: Color(0xFF047857), fontSize: 11, fontWeight: FontWeight.w700),
            ),
          ],
        ),
      );
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
      decoration: BoxDecoration(
        color: const Color(0xFFEFF6FF),
        borderRadius: BorderRadius.circular(6),
        border: Border.all(color: const Color(0xFFBFDBFE)),
      ),
      child: const Text(
        'Menunggu',
        style: TextStyle(color: Color(0xFF1D4ED8), fontSize: 11, fontWeight: FontWeight.w700),
      ),
    );
  }

  Widget _buildMetaItem({
    required IconData icon,
    required String label,
    required String value,
  }) {
    return Expanded(
      child: Column(
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(icon, size: 12, color: const Color(0xFF94A3B8)),
              const SizedBox(width: 3),
              Flexible(
                child: Text(
                  label,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: const TextStyle(
                    fontSize: 9.5,
                    fontWeight: FontWeight.w700,
                    color: Color(0xFF94A3B8),
                    letterSpacing: 0.3,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 3),
          Text(
            value,
            textAlign: TextAlign.center,
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
            style: const TextStyle(
              fontSize: 12,
              fontWeight: FontWeight.w700,
              color: Color(0xFF334155),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildEmptyState() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 32, vertical: 40),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              width: 72,
              height: 72,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: const Color(0xFFF1F5F9),
                border: Border.all(color: const Color(0xFFE2E8F0)),
              ),
              child: const Center(
                child: Icon(
                  Icons.event_available_rounded,
                  size: 36,
                  color: Color(0xFF94A3B8),
                ),
              ),
            ),
            const SizedBox(height: 18),
            const Text(
              'Tidak Ada Ujian Hari Ini',
              textAlign: TextAlign.center,
              style: TextStyle(
                fontSize: 17,
                fontWeight: FontWeight.w800,
                color: Color(0xFF0F172A),
                letterSpacing: -0.4,
              ),
            ),
            const SizedBox(height: 6),
            const Text(
              'Belum ada jadwal ujian yang dijadwalkan untuk hari ini. Silakan hubungi proktor atau guru jika jadwal belum muncul.',
              textAlign: TextAlign.center,
              style: TextStyle(
                fontSize: 13,
                color: Color(0xFF64748B),
                height: 1.4,
              ),
            ),
            const SizedBox(height: 20),
            ElevatedButton.icon(
              onPressed: _fetchExams,
              icon: const Icon(Icons.refresh_rounded, size: 17),
              label: const Text('Perbarui Jadwal', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 13)),
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF0A2540),
                foregroundColor: Colors.white,
                elevation: 0,
                padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 10),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
