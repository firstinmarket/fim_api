import 'package:shared_preferences/shared_preferences.dart';

class SessionManager {
  static const String _userIdKey = 'user_id';
  static const String _userEmailKey = 'user_email';
  static const String _isLoggedInKey = 'is_logged_in';
  static const String _loginTimestampKey = 'login_timestamp';
  static const String _readPostIdsKey = 'read_post_ids';
  static const String _viewedPostIdsKey = 'viewed_post_ids';

  // Check if user is currently logged in
  static Future<bool> isLoggedIn() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final userId = prefs.getString(_userIdKey);
      final isLoggedIn = prefs.getBool(_isLoggedInKey) ?? false;
      return userId != null && userId.isNotEmpty && isLoggedIn;
    } catch (e) {
      return false;
    }
  }

  // Get current user ID
  static Future<String?> getCurrentUserId() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      return prefs.getString(_userIdKey);
    } catch (e) {
      return null;
    }
  }

  // Get current user email
  static Future<String?> getCurrentUserEmail() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      return prefs.getString(_userEmailKey);
    } catch (e) {
      return null;
    }
  }

  // Save login session
  static Future<void> saveLoginSession({
    required String userId,
    required String email,
  }) async {
    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString(_userIdKey, userId);
      await prefs.setString(_userEmailKey, email);
      await prefs.setBool(_isLoggedInKey, true);
      await prefs.setString(
          _loginTimestampKey, DateTime.now().toIso8601String());
    } catch (e) {
      print('Error saving login session: $e');
    }
  }

  // Clear all session data (logout)
  static Future<void> clearSession() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.remove(_userIdKey);
      await prefs.remove(_userEmailKey);
      await prefs.setBool(_isLoggedInKey, false);
      await prefs.remove(_loginTimestampKey);
      await prefs.remove(_readPostIdsKey);
      await prefs.remove(_viewedPostIdsKey);
    } catch (e) {
      print('Error clearing session: $e');
    }
  }

  // Get login timestamp
  static Future<DateTime?> getLoginTimestamp() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final timestampStr = prefs.getString(_loginTimestampKey);
      if (timestampStr != null) {
        return DateTime.parse(timestampStr);
      }
      return null;
    } catch (e) {
      return null;
    }
  }

  // Check if session is valid (not expired)
  static Future<bool> isSessionValid() async {
    try {
      final isLoggedIn = await SessionManager.isLoggedIn();
      if (!isLoggedIn) return false;

      final loginTime = await getLoginTimestamp();
      if (loginTime == null) return false;

      // Session valid for 30 days
      final expiryTime = loginTime.add(const Duration(days: 30));
      return DateTime.now().isBefore(expiryTime);
    } catch (e) {
      return false;
    }
  }

  // Auto-logout if session expired
  static Future<bool> validateAndRefreshSession() async {
    try {
      final isValid = await isSessionValid();
      if (!isValid) {
        await clearSession();
        return false;
      }

      // Refresh login timestamp
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString(
          _loginTimestampKey, DateTime.now().toIso8601String());
      return true;
    } catch (e) {
      return false;
    }
  }
}
