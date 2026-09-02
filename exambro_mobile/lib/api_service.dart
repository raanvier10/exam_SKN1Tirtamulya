import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

class ApiService {
  static const String baseUrl =
      'https://cement-chevy-exec-pam.trycloudflare.com/api'; // Changed for Physical Device Testing via ngrok

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
    final response = await http.post(
      Uri.parse('$baseUrl/login'),
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: jsonEncode({'username': username, 'password': password}),
    );
    return jsonDecode(response.body);
  }

  static Future<Map<String, dynamic>> getExamsToday() async {
    final headers = await getHeaders();
    final response = await http.get(
      Uri.parse('$baseUrl/student/exams/today'),
      headers: headers,
    );
    return jsonDecode(response.body);
  }

  static Future<Map<String, dynamic>> startExamSession(
    int examId,
    String deviceId,
  ) async {
    final headers = await getHeaders();
    final response = await http.post(
      Uri.parse('$baseUrl/exams/$examId/start'),
      headers: headers,
      body: jsonEncode({'device_id': deviceId}),
    );
    return jsonDecode(response.body);
  }

  static Future<Map<String, dynamic>> finishExamSession(int examId) async {
    final headers = await getHeaders();
    final response = await http.post(
      Uri.parse('$baseUrl/exams/$examId/finish'),
      headers: headers,
    );
    return jsonDecode(response.body);
  }

  static Future<Map<String, dynamic>> getMe() async {
    final headers = await getHeaders();
    final response = await http.get(Uri.parse('$baseUrl/me'), headers: headers);
    return jsonDecode(response.body);
  }

  static Future<Map<String, dynamic>> updateGoogleStatus(
    bool connected, {
    String? email,
  }) async {
    final headers = await getHeaders();
    final Map<String, dynamic> payload = {'connected': connected};
    if (email != null) payload['email'] = email;

    final response = await http.post(
      Uri.parse('$baseUrl/student/google-status'),
      headers: headers,
      body: jsonEncode(payload),
    );
    return jsonDecode(response.body);
  }

  static Future<void> reportViolation(
    int examId,
    int sessionId,
    String type,
    String description,
  ) async {
    final headers = await getHeaders();
    await http.post(
      Uri.parse('$baseUrl/violations'),
      headers: headers,
      body: jsonEncode({
        'exam_id': examId,
        'session_id': sessionId,
        'type': type,
        'description': description,
      }),
    );
  }
}
