import 'package:http/http.dart' as http;
import 'dart:convert';

class ApiService {
  static const String baseUrl = 'https://www.firstinmarket.com/app/api/routes/';

  // static const String localUrl = 'http://10.0.2.2/fim_api/api/routes/';

 
  static Future<bool> testConnection() async {
    try {
      print('Testing connection to: $baseUrl');
      final response = await http.get(
        Uri.parse('$baseUrl../config/cors.php'),
        headers: {'Accept': 'application/json'},
      ).timeout(const Duration(seconds: 10));

      print('Connection test response: ${response.statusCode}');
      return response.statusCode == 200;
    } catch (e) {
      print('Connection test failed: $e');
      return false;
    }
  }

  static Future<Map<String, dynamic>> apiPost(
      String endpoint, Map<String, dynamic> body) async {
    print('Making POST request to: $baseUrl$endpoint');
    print('Request body: ${jsonEncode(body)}');

    try {
      final response = await http
          .post(
            Uri.parse('$baseUrl$endpoint'),
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
            },
            body: jsonEncode(body),
          )
          .timeout(const Duration(seconds: 45)); // Increased timeout for mobile

      print('Response status: ${response.statusCode}');
      print('Response body: ${response.body}');

      if (response.statusCode == 200 || response.statusCode == 201) {
        final responseData = jsonDecode(response.body);
        if (responseData is Map<String, dynamic>) {
          return responseData;
        } else {
          return {'data': responseData};
        }
      } else {
        throw Exception(
            'HTTP Error: ${response.statusCode} - ${response.body}');
      }
    } catch (e) {
      print('API Post Error: $e');
      throw Exception(
          'Network Error: Unable to connect to server. Please check your internet connection.');
    }
  }

  static Future<dynamic> apiGet(String endpoint,
      {Map<String, dynamic>? query}) async {
    final uri = Uri.parse('$baseUrl$endpoint').replace(
        queryParameters: query?.map((k, v) => MapEntry(k, v.toString())));
    print('Making GET request to: $uri');

    try {
      final response = await http.get(
        uri,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
      ).timeout(const Duration(seconds: 45)); // Increased timeout for mobile

      print('Response status: ${response.statusCode}');
      print('Response body: ${response.body}');

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        throw Exception(
            'HTTP Error: ${response.statusCode} - ${response.body}');
      }
    } catch (e) {
      print('API Get Error: $e');
      throw Exception(
          'Network Error: Unable to connect to server. Please check your internet connection.');
    }
  }

  static Future<Map<String, dynamic>> apiPut(
      String endpoint, Map<String, dynamic> body) async {
    print('Making PUT request to: $baseUrl$endpoint');
    print('Request body: ${jsonEncode(body)}');

    try {
      final response = await http
          .put(
            Uri.parse('$baseUrl$endpoint'),
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
            },
            body: jsonEncode(body),
          )
          .timeout(const Duration(seconds: 45)); // Increased timeout for mobile

      print('Response status: ${response.statusCode}');
      print('Response body: ${response.body}');

      if (response.statusCode == 200 || response.statusCode == 201) {
        final responseData = jsonDecode(response.body);
        if (responseData is Map<String, dynamic>) {
          return responseData;
        } else {
          return {'data': responseData};
        }
      } else {
        throw Exception(
            'HTTP Error: ${response.statusCode} - ${response.body}');
      }
    } catch (e) {
      print('API Put Error: $e');
      throw Exception(
          'Network Error: Unable to connect to server. Please check your internet connection.');
    }
  }
}
