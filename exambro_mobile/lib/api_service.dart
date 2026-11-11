import 'dart:convert';
import 'dart:math';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

class ApiService {
  // Gunakan --dart-define=API_BASE_URL=https://domain-sekolah/api saat build production
  static const String _envBaseUrl = String.fromEnvironment('API_BASE_URL');

  // Daftar URL server kandidat lokal (dipakai hanya jika API_BASE_URL tidak disetel)
  static const List<String> candidateUrls = [
    'http://127.0.0.1:8000/api',
    'http://10.122.152.181:8000/api',
    'http://192.168.1.5:8000/api',
  ];

  static String? _cachedBaseUrl;

  static Future<String> getBaseUrl() async {
    if (_envBaseUrl.isNotEmpty) return _envBaseUrl;
    if (_cachedBaseUrl != null) return _cachedBaseUrl!;
    return candidateUrls.first;
  }

  static Future<http.Response> _requestWithFallback(
    Future<http.Response> Function(String baseUrl) requestFn,
  ) async {
    final urls = _envBaseUrl.isNotEmpty
        ? <String>[_envBaseUrl]
        : <String>[
            ?_cachedBaseUrl,
            ...candidateUrls.where((u) => u != _cachedBaseUrl),
          ];

    Object? lastError;
    for (final base in urls) {
      try {
        final res = await requestFn(base).timeout(const Duration(seconds: 4));
        _cachedBaseUrl = base;
        return res;
      } catch (e) {
        lastError = e;
        continue;
      }
    }
    throw lastError ?? Exception('Gagal terhubung ke server');
  }

  static Future<String> getOrCreateDeviceId() async {
    final prefs = await SharedPreferences.getInstance();
    final existing = prefs.getString('device_id');
    if (existing != null && existing.isNotEmpty) {
      return existing;
    }

    final random = Random.secure();
    final bytes = List<int>.generate(16, (_) => random.nextInt(256));
    final generated = bytes
        .map((b) => b.toRadixString(16).padLeft(2, '0'))
        .join();
    await prefs.setString('device_id', generated);
    return generated;
  }

  static Future<String?> getToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString('api_token');
  }

  static Future<void> saveToken(String token) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('api_token', token);
  }

  static Future<void> removeToken() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('api_token');
    await prefs.remove('is_google_connected');
    await prefs.remove('is_pkl');
  }

  static Future<void> logout() async {
    try {
      final headers = await getHeaders();
      await _requestWithFallback(
        (base) => http.post(Uri.parse('$base/logout'), headers: headers),
      );
    } catch (_) {}
    await removeToken();
  }

  static Future<Map<String, String>> getHeaders() async {
    final token = await getToken();
    return {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      if (token != null) 'Authorization': 'Bearer $token',
    };
  }

  static Future<Map<String, dynamic>> login(
    String username,
    String password,
  ) async {
    final response = await _requestWithFallback(
      (base) => http.post(
        Uri.parse('$base/login'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({'username': username, 'password': password}),
      ),
    );
    return jsonDecode(response.body);
  }

  static Future<Map<String, dynamic>> getExamsToday() async {
    final headers = await getHeaders();
    final response = await _requestWithFallback(
      (base) => http.get(
        Uri.parse('$base/student/exams/today'),
        headers: headers,
      ),
    );
    return jsonDecode(response.body);
  }

  static Future<Map<String, dynamic>> startExamSession(
    int examId,
    String deviceId, {
    double? latitude,
    double? longitude,
  }) async {
    final headers = await getHeaders();
    final Map<String, dynamic> body = {'device_id': deviceId};
    if (latitude != null) body['latitude'] = latitude;
    if (longitude != null) body['longitude'] = longitude;

    final response = await _requestWithFallback(
      (base) => http.post(
        Uri.parse('$base/exams/$examId/start'),
        headers: headers,
        body: jsonEncode(body),
      ),
    );
    return jsonDecode(response.body);
  }

  static Future<Map<String, dynamic>> finishExamSession(int examId) async {
    final headers = await getHeaders();
    final response = await _requestWithFallback(
      (base) => http.post(
        Uri.parse('$base/exams/$examId/finish'),
        headers: headers,
      ),
    );
    return jsonDecode(response.body);
  }

  static Future<Map<String, dynamic>> getMe() async {
    final headers = await getHeaders();
    final response = await _requestWithFallback(
      (base) => http.get(Uri.parse('$base/me'), headers: headers),
    );
    return jsonDecode(response.body);
  }

  static Future<Map<String, dynamic>> updateGoogleStatus(
    bool connected, {
    String? email,
  }) async {
    final headers = await getHeaders();
    final Map<String, dynamic> payload = {'connected': connected};
    if (email != null) payload['email'] = email;

    final response = await _requestWithFallback(
      (base) => http.post(
        Uri.parse('$base/student/google-status'),
        headers: headers,
        body: jsonEncode(payload),
      ),
    );
    return jsonDecode(response.body);
  }

  static Future<Map<String, dynamic>> reportViolation(
    dynamic examId,
    dynamic sessionId,
    String type,
    String description,
  ) async {
    try {
      final headers = await getHeaders();
      final response = await _requestWithFallback(
        (base) => http.post(
          Uri.parse('$base/violations'),
          headers: headers,
          body: jsonEncode({
            'exam_id': int.tryParse(examId.toString()) ?? examId,
            'session_id': int.tryParse(sessionId.toString()) ?? sessionId,
            'type': type,
            'description': description,
          }),
        ),
      );
      return jsonDecode(response.body);
    } catch (_) {
      return {'success': false, 'message': 'Gagal mengirim laporan pelanggaran'};
    }
  }
}
