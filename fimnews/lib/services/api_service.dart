import 'package:http/http.dart' as http;
import 'dart:convert';

class ApiService {
  static const String baseUrl = 'http://localhost/fim_api/api/routes/';

  static Future<Map<String, dynamic>> apiPost(String endpoint, Map<String, dynamic> body) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl$endpoint'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode(body),
      ).timeout(const Duration(seconds: 30));
      return jsonDecode(response.body) as Map<String, dynamic>;
    } catch (e) {
      throw Exception('API Post Error: $e');
    }
  }

  static Future<dynamic> apiGet(String endpoint, {Map<String, dynamic>? query}) async {
    try {
      final uri = Uri.parse('$baseUrl$endpoint').replace(queryParameters: query?.map((k, v) => MapEntry(k, v.toString())));
      final response = await http.get(
        uri,
        headers: {'Content-Type': 'application/json'},
      ).timeout(const Duration(seconds: 30));
      return jsonDecode(response.body);
    } catch (e) {
      throw Exception('API Get Error: $e');
    }
  }

  static Future<Map<String, dynamic>> apiPut(String endpoint, Map<String, dynamic> body) async {
    try {
      final response = await http.put(
        Uri.parse('$baseUrl$endpoint'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode(body),
      ).timeout(const Duration(seconds: 30));
      return jsonDecode(response.body) as Map<String, dynamic>;
    } catch (e) {
      throw Exception('API Put Error: $e');
    }
  }
}