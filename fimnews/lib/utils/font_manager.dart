import 'package:shared_preferences/shared_preferences.dart';

/// FontManager - Controls dynamic font sizing for content only
/// 
/// BEHAVIOR:
/// - ✅ Content text (Tamil/English) scales with user preference
/// - ❌ Titles, headers, buttons remain FIXED size
/// - ❌ Container heights remain FIXED (based on screen percentage)
/// 
/// This ensures only readability content scales while UI remains consistent
class FontManager {
  static const String _fontScaleKey = 'font_scale_factor';
  static const double _defaultFontScale = 1.0;
  static const double _minFontScale = 0.8;
  static const double _maxFontScale = 1.5;

  // Get current font scale factor
  static Future<double> getFontScale() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getDouble(_fontScaleKey) ?? _defaultFontScale;
  }

  // Set font scale factor
  static Future<void> setFontScale(double scale) async {
    final prefs = await SharedPreferences.getInstance();
    final clampedScale = scale.clamp(_minFontScale, _maxFontScale);
    await prefs.setDouble(_fontScaleKey, clampedScale);
  }

  // Increase font size
  static Future<void> increaseFontSize() async {
    final currentScale = await getFontScale();
    final newScale = (currentScale + 0.1).clamp(_minFontScale, _maxFontScale);
    await setFontScale(newScale);
  }

  // Decrease font size
  static Future<void> decreaseFontSize() async {
    final currentScale = await getFontScale();
    final newScale = (currentScale - 0.1).clamp(_minFontScale, _maxFontScale);
    await setFontScale(newScale);
  }

  // Reset to default
  static Future<void> resetFontSize() async {
    await setFontScale(_defaultFontScale);
  }

  // Apply scale to a base font size
  static double getScaledFontSize(double baseFontSize, double fontScale) {
    return baseFontSize * fontScale;
  }

  // Helper method for Tamil content font size (ONLY content text scales)
  static double getTamilContentFontSize(bool isTamilContent, double fontScale) {
    final baseFontSize = isTamilContent ? 12.0 : 14.2;
    return getScaledFontSize(baseFontSize, fontScale);
  }

  // Helper method for title font size (FIXED - does not scale)
  static double getTitleFontSize(double fontScale) {
    return 17.0; // Fixed size, ignores fontScale
  }

  // Helper method for action button font size (FIXED - does not scale)
  static double getActionFontSize(double fontScale) {
    return 13.0; // Fixed size, ignores fontScale
  }

  // Helper method for comment font size (FIXED - does not scale)
  static double getCommentFontSize(double fontScale) {
    return 15.0; // Fixed size, ignores fontScale
  }

  // Helper method for header font size (FIXED - does not scale)
  static double getHeaderFontSize(double fontScale) {
    return 20.0; // Fixed size, ignores fontScale
  }
}
