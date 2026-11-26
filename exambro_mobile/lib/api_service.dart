import 'dart:convert';
import 'dart:math';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

class ApiService {
  // Gunakan --dart-define=API_BASE_URL=https://domain-sekolah/api saat build production
  static const String _envBaseUrl = String.fromEnvironment('API_BASE_URL');

  // Daftar URL server kandidat (dipakai hanya jika API_BASE_URL tidak disetel)
  static const List<String> candidateUrls = [
    'https://smkn1tirtamulya.site/api',
    'http://127.0.0.1:8000/api',
    'http://10.122.152.181:8000/api',
    'http://192.168.1.5:8000/api',
  ];

  static const List<String> _webCandidateUrls = [
    'https://smkn1tirtamulya.site/api',
    'http://localhost:8000/api',
    'http://127.0.0.1:8000/api',
  ];

  static String? _cachedBaseUrl;
  static String? _cachedToken;
  static SharedPreferences? _prefs;
  static final http.Client _client = http.Client();

  static Future<SharedPreferences> get _preferences async =>
      _prefs ??= await SharedPreferences.getInstance();

  static Future<String> getBaseUrl() async {
    if (_envBaseUrl.isNotEmpty) return _envBaseUrl;
    if (_cachedBaseUrl != null) return _cachedBaseUrl!;
    return kIsWeb ? _webCandidateUrls.first : candidateUrls.first;
  }

  static Future<http.Response> _requestWithFallback(
    Future<http.Response> Function(String baseUrl) requestFn,
  ) async {
    final defaults = kIsWeb ? _webCandidateUrls : candidateUrls;
    final urls = _envBaseUrl.isNotEmpty
        ? <String>[_envBaseUrl]
        : <String>[
            ?_cachedBaseUrl,
            ...defaults.where((u) => u != _cachedBaseUrl),
          ];

    Object? lastError;
    for (final base in urls) {
      try {
        final res = await requestFn(base).timeout(const Duration(seconds: 8));
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
    final prefs = await _preferences;
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
    if (_cachedToken != null) return _cachedToken;
    final prefs = await _preferences;
    _cachedToken = prefs.getString('api_token');
    return _cachedToken;
  }

  static Future<void> saveToken(String token) async {
    _cachedToken = token;
    final prefs = await _preferences;
    await prefs.setString('api_token', token);
  }

  static Future<void> removeToken() async {
    _cachedToken = null;
    final prefs = await _preferences;
    await prefs.remove('api_token');
    await prefs.remove('is_google_connected');
    await prefs.remove('is_pkl');
  }

  static Future<void> logout() async {
    try {
      final headers = await getHeaders();
      await _requestWithFallback(
        (base) => _client.post(Uri.parse('$base/logout'), headers: headers),
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

  static Map<String, dynamic> _decodeResponse(http.Response response) {
    try {
      final decoded = jsonDecode(response.body);
      if (decoded is Map<String, dynamic>) {
        if (response.statusCode == 429 && !decoded.containsKey('success')) {
          return {
            'success': false,
            'message': 'Terlalu banyak permintaan. Tunggu beberapa detik lalu coba lagi.',
          };
        }
        return decoded;
      }
    } catch (_) {}

    if (response.statusCode == 429) {
      return {
        'success': false,
        'message': 'Terlalu banyak percobaan. Tunggu 1 menit lalu coba lagi.',
      };
    }
    if (response.statusCode == 401) {
      return {
        'success': false,
        'message': 'Unauthenticated.',
      };
    }
    return {
      'success': false,
      'message': 'Server sedang sibuk atau gangguan jaringan (HTTP ${response.statusCode}).',
    };
  }

  static Future<Map<String, dynamic>> login(
    String username,
    String password,
  ) async {
    final response = await _requestWithFallback(
      (base) => _client.post(
        Uri.parse('$base/login'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({'username': username, 'password': password}),
      ),
    );
    return _decodeResponse(response);
  }

  static Future<Map<String, dynamic>> getExamsToday() async {
    final headers = await getHeaders();
    final response = await _requestWithFallback(
      (base) => _client.get(
        Uri.parse('$base/student/exams/today'),
        headers: headers,
      ),
    );
    return _decodeResponse(response);
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
      (base) => _client.post(
        Uri.parse('$base/exams/$examId/start'),
        headers: headers,
        body: jsonEncode(body),
      ),
    );
    return _decodeResponse(response);
  }

  static Future<Map<String, dynamic>> finishExamSession(int examId) async {
    final headers = await getHeaders();
    final response = await _requestWithFallback(
      (base) => _client.post(
        Uri.parse('$base/exams/$examId/finish'),
        headers: headers,
      ),
    );
    return _decodeResponse(response);
  }

  static Future<Map<String, dynamic>> getMe() async {
    final headers = await getHeaders();
    final response = await _requestWithFallback(
      (base) => _client.get(Uri.parse('$base/me'), headers: headers),
    );
    return _decodeResponse(response);
  }

  static Future<Map<String, dynamic>> updateGoogleStatus(
    bool connected, {
    String? email,
  }) async {
    final headers = await getHeaders();
    final Map<String, dynamic> payload = {'connected': connected};
    if (email != null) payload['email'] = email;

    final response = await _requestWithFallback(
      (base) => _client.post(
        Uri.parse('$base/student/google-status'),
        headers: headers,
        body: jsonEncode(payload),
      ),
    );
    return _decodeResponse(response);
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
        (base) => _client.post(
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
      return _decodeResponse(response);
    } catch (_) {
      return {'success': false, 'message': 'Gagal mengirim laporan pelanggaran'};
    }
  }

  static Future<void> saveExamDraft(int examId, String jsonDraft) async {
    final prefs = await _preferences;
    await prefs.setString('exam_draft_$examId', jsonDraft);
  }

  static Future<String?> getExamDraft(int examId) async {
    final prefs = await _preferences;
    return prefs.getString('exam_draft_$examId');
  }

  static Future<void> clearExamDraft(int examId) async {
    final prefs = await _preferences;
    await prefs.remove('exam_draft_$examId');
  }
}
