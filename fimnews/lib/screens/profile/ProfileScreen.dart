import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:vibration/vibration.dart';
import '../../services/api_service.dart';
import '../../services/session_manager.dart';
import '../components/navigation.dart';
import '../legal/PrivacyPolicyScreen.dart';
import '../legal/DisclaimerScreen.dart';

class ProfileScreen extends StatefulWidget {
  const ProfileScreen({super.key});

  @override
  _ProfileScreenState createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  bool notificationsEnabled = false;
  Map<String, dynamic> user = {
    'user_id': '',
    'name': '',
    'email': '',
    'phone': '',
    'bio': '',
    'language': '',
    'interests': [],
  };
  bool fieldModalVisible = false;
  bool editCategoryModal = false;
  String editField = '';
  String tempValue = '';
  List<String> tempInterests = [];
  List<Map<String, dynamic>> categories = [];

  void _showErrorDialog(String message) {
    debugPrint('ProfileScreen: Showing error dialog: $message');
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Error'),
        content: Text(message),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('OK'),
          ),
        ],
      ),
    );
  }

  void _showSuccessDialog(String title, String message, {VoidCallback? onOk}) {
    debugPrint(
        'ProfileScreen: Showing success dialog: $title, message: $message, has onOk: ${onOk != null}');
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Text(title),
        content: message.isNotEmpty ? Text(message) : null,
        actions: [
          TextButton(
            onPressed: () {
              debugPrint('ProfileScreen: Success dialog OK pressed');
              Navigator.pop(context);
              if (onOk != null) {
                debugPrint('ProfileScreen: Executing onOk callback');
                onOk();
              }
            },
            child: const Text('OK'),
          ),
        ],
      ),
    );
  }

  // Helper function to get first letter of user's name
  String _getFirstLetter(String name) {
    if (name.isEmpty) return 'U'; // Default 'U' for User
    return name.trim().substring(0, 1).toUpperCase();
  }

  // Helper function to get icon for field
  IconData _getFieldIcon(String field) {
    switch (field) {
      case 'name':
        return Icons.person;
      case 'email':
        return Icons.email;
      case 'phone':
        return Icons.phone;
      case 'bio':
        return Icons.description;
      case 'language':
        return Icons.language;
      default:
        return Icons.edit;
    }
  }

  @override
  void initState() {
    super.initState();
    debugPrint('ProfileScreen: Initializing');
    fetchProfile();
    fetchCategories();
    checkNotificationStatus();
  }

  Future<void> checkNotificationStatus() async {
    setState(() {
      notificationsEnabled = false;
    });
  }

  Future<void> saveFcmToken() async {
    String? token;
    try {
      token = await FirebaseMessaging.instance.getToken();
    } catch (e) {
      debugPrint('Error getting FCM token: $e');
      _showErrorDialog('Could not get notification token.');
      return;
    }
    if (token == null) {
      _showErrorDialog('Could not get notification token.');
      return;
    }
    final prefs = await SharedPreferences.getInstance();
    final userId = prefs.getString('user_id');
    if (userId == null) {
      _showErrorDialog('User ID not found. Please log in again.');
      return;
    }
    try {
      final response = await ApiService.apiPost('profile/save_fcm_token.php', {
        'user_id': userId,
        'fcm_token': token,
      });
      if (response is Map<String, dynamic> && response['success'] == true) {
        setState(() {
          notificationsEnabled = true;
        });
        _showSuccessDialog('Success', 'Notifications enabled!');
      } else {
        _showErrorDialog('Failed to save notification token.');
      }
    } catch (e) {
      _showErrorDialog('Error saving notification token: $e');
    }
  }

  Future<void> fetchProfile() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final userId = prefs.getString('user_id');
      if (userId == null) {
        debugPrint('ProfileScreen: User ID not found');
        _showErrorDialog('User ID not found. Please log in again.');
        Navigator.pushReplacementNamed(context, '/login');
        return;
      }
      debugPrint('ProfileScreen: Fetching profile for user_id: $userId');
      final response = await ApiService.apiGet('profile/profile.php',
          query: {'user_id': userId});
      if (response is Map<String, dynamic> &&
          response['success'] == true &&
          response['profile'] != null) {
        final profile = response['profile'];
        final userInfo = profile['user'] ?? {};
        final categories = profile['categories'] ?? [];

        debugPrint('ProfileScreen: Raw categories from API: $categories');

        setState(() {
          user = {
            ...user,
            'user_id': userId,
            'name': userInfo['name'] ?? '',
            'email': userInfo['email'] ?? '',
            'phone': userInfo['mobile'] ?? '',
            'bio': userInfo['bio'] ?? '',
            'language': userInfo['language'] ?? 'english',
            'interests': List<String>.from(
              categories
                  .map((cat) => cat['category_name'] ?? '')
                  .where((name) => name != null && name.toString().isNotEmpty)
                  .toList(),
            ),
          };
          debugPrint('ProfileScreen: Profile loaded: ${user['name']}');
          debugPrint('ProfileScreen: Loaded interests: ${user['interests']}');
        });
      } else {
        _showErrorDialog(response is Map<String, dynamic>
            ? response['message'] ?? 'Failed to load profile'
            : 'Invalid response from server');
      }
    } catch (err) {
      debugPrint('ProfileScreen: Fetch profile error: $err');
      _showErrorDialog('Network error: $err');
    }
  }

  Future<void> fetchCategories() async {
    try {
      debugPrint('ProfileScreen: Fetching categories');
      final response = await ApiService.apiGet('categories/categories.php',
          query: {'type': 'categories'});
      if (response is List) {
        setState(() {
          categories = List<Map<String, dynamic>>.from(response);
          debugPrint('ProfileScreen: Categories loaded: ${categories.length}');
        });
      } else {
        _showErrorDialog('Invalid categories data received');
      }
    } catch (err) {
      debugPrint('ProfileScreen: Fetch categories error: $err');
      _showErrorDialog('Network error: $err');
    }
  }

  void openFieldModal(String field) async {
    if (await Vibration.hasVibrator()) {
      Vibration.vibrate(duration: 100);
    }
    showDialog(
      context: context,
      barrierDismissible: true,
      barrierColor: Colors.black.withOpacity(0.7),
      builder: (context) => StatefulBuilder(
        builder: (context, setModalState) => Dialog(
          backgroundColor: Colors.transparent,
          elevation: 0,
          child: TweenAnimationBuilder(
            duration: const Duration(milliseconds: 300),
            tween: Tween<double>(begin: 0.0, end: 1.0),
            builder: (context, double value, child) {
              return Transform.scale(
                scale: value,
                child: Opacity(
                  opacity: value,
                  child: child,
                ),
              );
            },
            child: Container(
              width: MediaQuery.of(context).size.width * 0.9,
              padding: const EdgeInsets.all(24),
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                  colors: [
                    const Color(0xFF1A1F2E),
                    const Color(0xFF0F141E),
                  ],
                ),
                borderRadius: BorderRadius.circular(20),
                border: Border.all(
                  color: const Color(0xFF5F8DFF).withOpacity(0.5),
                  width: 2,
                ),
                boxShadow: [
                  BoxShadow(
                    color: const Color(0xFF5F8DFF).withOpacity(0.3),
                    blurRadius: 20,
                    spreadRadius: 2,
                    offset: const Offset(0, 8),
                  ),
                  BoxShadow(
                    color: Colors.black.withOpacity(0.5),
                    blurRadius: 30,
                    spreadRadius: 5,
                    offset: const Offset(0, 10),
                  ),
                ],
              ),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  // Header with icon and close button
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Container(
                        padding: const EdgeInsets.all(10),
                        decoration: BoxDecoration(
                          gradient: const LinearGradient(
                            colors: [Color(0xFF5F8DFF), Color(0xFF4A73E8)],
                          ),
                          borderRadius: BorderRadius.circular(12),
                          boxShadow: [
                            BoxShadow(
                              color: const Color(0xFF5F8DFF).withOpacity(0.4),
                              blurRadius: 8,
                              offset: const Offset(0, 4),
                            ),
                          ],
                        ),
                        child: Icon(
                          _getFieldIcon(field),
                          color: Colors.white,
                          size: 24,
                        ),
                      ),
                      Container(
                        decoration: BoxDecoration(
                          color: const Color(0xFF232A3B),
                          borderRadius: BorderRadius.circular(10),
                          border: Border.all(
                            color: const Color(0xFF5F8DFF).withOpacity(0.3),
                            width: 1,
                          ),
                        ),
                        child: IconButton(
                          onPressed: () => Navigator.pop(context),
                          icon: const Icon(Icons.close,
                              color: Colors.white, size: 20),
                          padding: const EdgeInsets.all(8),
                          constraints: const BoxConstraints(),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 20),
                  // Title
                  Text(
                    'Edit ${editField[0].toUpperCase() + editField.substring(1)}',
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 22,
                      fontWeight: FontWeight.bold,
                      letterSpacing: 0.5,
                    ),
                  ),
                  const SizedBox(height: 8),
                  Container(
                    width: 60,
                    height: 4,
                    decoration: BoxDecoration(
                      gradient: const LinearGradient(
                        colors: [Color(0xFF5F8DFF), Color(0xFF4A73E8)],
                      ),
                      borderRadius: BorderRadius.circular(2),
                    ),
                  ),
                  const SizedBox(height: 24),
                  // Input field or dropdown
                  if (editField == 'language')
                    Container(
                      padding: const EdgeInsets.symmetric(
                          horizontal: 16, vertical: 4),
                      decoration: BoxDecoration(
                        color: const Color(0xFF232A3B),
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(
                          color: const Color(0xFF5F8DFF).withOpacity(0.5),
                          width: 2,
                        ),
                        boxShadow: [
                          BoxShadow(
                            color: const Color(0xFF5F8DFF).withOpacity(0.1),
                            blurRadius: 10,
                            offset: const Offset(0, 4),
                          ),
                        ],
                      ),
                      child: DropdownButtonHideUnderline(
                        child: DropdownButton<String>(
                          value:
                              (tempValue == 'english' || tempValue == 'tamil')
                                  ? tempValue
                                  : 'english',
                          isExpanded: true,
                          dropdownColor: const Color(0xFF232A3B),
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 16,
                            fontWeight: FontWeight.w500,
                          ),
                          icon: Container(
                            padding: const EdgeInsets.all(6),
                            decoration: BoxDecoration(
                              color: const Color(0xFF5F8DFF).withOpacity(0.2),
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: const Icon(Icons.arrow_drop_down,
                                color: Color(0xFF5F8DFF)),
                          ),
                          items: const [
                            DropdownMenuItem(
                              value: 'english',
                              child: Row(
                                children: [
                                  Icon(Icons.language,
                                      color: Color(0xFF5F8DFF), size: 20),
                                  SizedBox(width: 12),
                                  Text('English'),
                                ],
                              ),
                            ),
                            DropdownMenuItem(
                              value: 'tamil',
                              child: Row(
                                children: [
                                  Icon(Icons.language,
                                      color: Color(0xFF5F8DFF), size: 20),
                                  SizedBox(width: 12),
                                  Text('Tamil'),
                                ],
                              ),
                            ),
                          ],
                          onChanged: (String? newValue) {
                            if (newValue != null) {
                              final previousValue = tempValue;
                              setState(() {
                                tempValue = newValue;
                                debugPrint(
                                    'Language changed from: $previousValue to: $newValue');
                              });
                              setModalState(() {}); // Rebuild modal UI
                            }
                          },
                        ),
                      ),
                    )
                  else
                    Container(
                      decoration: BoxDecoration(
                        borderRadius: BorderRadius.circular(12),
                        boxShadow: [
                          BoxShadow(
                            color: const Color(0xFF5F8DFF).withOpacity(0.1),
                            blurRadius: 10,
                            offset: const Offset(0, 4),
                          ),
                        ],
                      ),
                      child: TextField(
                        controller: TextEditingController(text: tempValue),
                        onChanged: (value) => tempValue = value,
                        maxLines: editField == 'bio' ? 3 : 1,
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 16,
                          fontWeight: FontWeight.w500,
                        ),
                        decoration: InputDecoration(
                          hintText: 'Enter your $editField',
                          hintStyle: TextStyle(
                            color: Colors.white.withOpacity(0.4),
                            fontSize: 15,
                          ),
                          filled: true,
                          fillColor: const Color(0xFF232A3B),
                          prefixIcon: Icon(
                            _getFieldIcon(field),
                            color: const Color(0xFF5F8DFF),
                            size: 22,
                          ),
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12),
                            borderSide: BorderSide(
                              color: const Color(0xFF5F8DFF).withOpacity(0.5),
                              width: 2,
                            ),
                          ),
                          enabledBorder: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12),
                            borderSide: BorderSide(
                              color: const Color(0xFF5F8DFF).withOpacity(0.5),
                              width: 2,
                            ),
                          ),
                          focusedBorder: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12),
                            borderSide: const BorderSide(
                              color: Color(0xFF5F8DFF),
                              width: 2,
                            ),
                          ),
                          contentPadding: const EdgeInsets.symmetric(
                            horizontal: 16,
                            vertical: 16,
                          ),
                        ),
                      ),
                    ),
                  const SizedBox(height: 24),
                  // Update button
                  Container(
                    width: double.infinity,
                    decoration: BoxDecoration(
                      gradient: const LinearGradient(
                        colors: [Color(0xFF5F8DFF), Color(0xFF4A73E8)],
                        begin: Alignment.centerLeft,
                        end: Alignment.centerRight,
                      ),
                      borderRadius: BorderRadius.circular(12),
                      boxShadow: [
                        BoxShadow(
                          color: const Color(0xFF5F8DFF).withOpacity(0.4),
                          blurRadius: 12,
                          offset: const Offset(0, 6),
                        ),
                      ],
                    ),
                    child: ElevatedButton(
                      onPressed: () {
                        Navigator.pop(context);
                        updateField();
                      },
                      style: ElevatedButton.styleFrom(
                        backgroundColor: Colors.transparent,
                        shadowColor: Colors.transparent,
                        padding: const EdgeInsets.symmetric(vertical: 16),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                      ),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: const [
                          Icon(Icons.check_circle_outline,
                              color: Colors.white, size: 22),
                          SizedBox(width: 8),
                          Text(
                            'Update',
                            style: TextStyle(
                              color: Colors.white,
                              fontSize: 16,
                              fontWeight: FontWeight.bold,
                              letterSpacing: 0.5,
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
    );
    setState(() {
      editField = field;
      if (field == 'language') {
        // Ensure language value is valid for dropdown
        String currentLanguage = user[field]?.toString() ?? 'english';
        tempValue = (currentLanguage == 'english' || currentLanguage == 'tamil')
            ? currentLanguage
            : 'english';
        debugPrint(
            'ProfileScreen: Language modal - current: $currentLanguage, temp: $tempValue');
      } else {
        tempValue = user[field]?.toString() ?? '';
      }
      fieldModalVisible = true;
      editCategoryModal = false;
      debugPrint('ProfileScreen: Opening field modal for: $field');
    });

    // Force UI refresh after modal opens
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (mounted) {
        setState(() {});
      }
    });
  }

  void updateField() async {
    if (editField == 'name' ||
        editField == 'phone' ||
        editField == 'bio' ||
        editField == 'email' ||
        editField == 'language') {
      if (editField == 'phone' &&
          (tempValue.isEmpty || tempValue.length != 10)) {
        _showErrorDialog('Mobile number must be exactly 10 digits');
        return;
      }
      if (editField == 'bio' && tempValue == user['bio']) {
        _showErrorDialog('Bio is unchanged. Please enter a new bio to update.');
        return;
      }
      if (editField == 'email') {
        if (tempValue == user['email']) {
          _showErrorDialog(
              'Email is unchanged. Please enter a new email to update.');
          return;
        }
        if (!RegExp(r'^\S+@\S+\.\S+$').hasMatch(tempValue)) {
          _showErrorDialog('Please enter a valid email address.');
          return;
        }
      }
      final prefs = await SharedPreferences.getInstance();
      final userId = prefs.getString('user_id');
      if (userId == null) {
        _showErrorDialog('User ID not found');
        setState(() => fieldModalVisible = false);
        return;
      }
      String endpoint = '';
      Map<String, dynamic> payload = {'user_id': userId};
      String title = '';
      String successMsg = '';
      if (editField == 'name') {
        endpoint = 'profile/update_name.php';
        payload['new_name'] = tempValue;
        title = 'Name Updated';
        successMsg = 'Name updated successfully';
      } else if (editField == 'phone') {
        endpoint = 'profile/update_mobile.php';
        payload['new_mobile'] = tempValue;
        title = 'Phone Updated';
        successMsg = 'Mobile number updated successfully';
      } else if (editField == 'bio') {
        endpoint = 'profile/update_bio.php';
        payload['new_bio'] = tempValue;
        title = 'Bio Updated';
        successMsg = 'Bio updated successfully';
      } else if (editField == 'email') {
        endpoint = 'profile/update_email.php';
        payload['new_email'] = tempValue;
        title = 'Email Updated';
        successMsg = 'Email updated successfully';
      } else if (editField == 'language') {
        endpoint = 'profile/update_language.php';
        payload['new_language'] = tempValue;
        title = 'Language Updated';
        successMsg = 'Language updated successfully';
      }
      try {
        debugPrint('ProfileScreen: Updating $editField with payload: $payload');
        final response = await ApiService.apiPut(endpoint, payload);
        if (response['success'] == true) {
          debugPrint('ProfileScreen: Update successful, response: $response');
          _showSuccessDialog(title, response['message'] ?? successMsg);
          setState(() {
            user[editField] = tempValue;
          });
        } else {
          debugPrint('ProfileScreen: Update failed, response: $response');
          final errorMsg = response['message'] != null
              ? response['message']
              : 'Unable to update ${editField[0].toUpperCase() + editField.substring(1)}. Please try again.';
          _showErrorDialog(errorMsg);
        }
      } catch (err) {
        debugPrint('ProfileScreen: Update $editField error: $err');
        _showErrorDialog('Network error: $err');
      } finally {
        setState(() => fieldModalVisible = false);
      }
    } else {
      setState(() {
        user[editField] = tempValue;
        fieldModalVisible = false;
        debugPrint('ProfileScreen: Updated $editField locally: $tempValue');
      });
    }
  }

  void openCategoryModal() async {
    if (await Vibration.hasVibrator()) {
      Vibration.vibrate(duration: 100);
    }
    showDialog(
      context: context,
      barrierDismissible: true,
      barrierColor: Colors.black.withOpacity(0.7),
      builder: (context) => StatefulBuilder(
        builder: (context, setModalState) => Dialog(
          backgroundColor: Colors.transparent,
          elevation: 0,
          child: TweenAnimationBuilder(
            duration: const Duration(milliseconds: 300),
            tween: Tween<double>(begin: 0.0, end: 1.0),
            builder: (context, double value, child) {
              return Transform.scale(
                scale: 0.8 + (0.2 * value),
                child: Opacity(
                  opacity: value,
                  child: child,
                ),
              );
            },
            child: Container(
              width: MediaQuery.of(context).size.width * 0.9,
              constraints: BoxConstraints(
                maxHeight: MediaQuery.of(context).size.height * 0.7,
              ),
              padding: const EdgeInsets.all(24),
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                  colors: [
                    const Color(0xFF1A1F2E),
                    const Color(0xFF0F141E),
                  ],
                ),
                borderRadius: BorderRadius.circular(20),
                border: Border.all(
                  color: const Color(0xFF5F8DFF).withOpacity(0.5),
                  width: 2,
                ),
                boxShadow: [
                  BoxShadow(
                    color: const Color(0xFF5F8DFF).withOpacity(0.3),
                    blurRadius: 20,
                    spreadRadius: 2,
                    offset: const Offset(0, 8),
                  ),
                  BoxShadow(
                    color: Colors.black.withOpacity(0.5),
                    blurRadius: 30,
                    spreadRadius: 5,
                    offset: const Offset(0, 10),
                  ),
                ],
              ),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  // Header
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(10),
                        decoration: BoxDecoration(
                          gradient: const LinearGradient(
                            colors: [Color(0xFF5F8DFF), Color(0xFF4A73E8)],
                          ),
                          borderRadius: BorderRadius.circular(12),
                          boxShadow: [
                            BoxShadow(
                              color: const Color(0xFF5F8DFF).withOpacity(0.4),
                              blurRadius: 8,
                              offset: const Offset(0, 4),
                            ),
                          ],
                        ),
                        child: const Icon(
                          Icons.category,
                          color: Colors.white,
                          size: 24,
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text(
                              'Edit Interests',
                              style: TextStyle(
                                color: Colors.white,
                                fontSize: 20,
                                fontWeight: FontWeight.bold,
                                letterSpacing: 0.5,
                              ),
                              overflow: TextOverflow.ellipsis,
                              maxLines: 1,
                            ),
                            Text(
                              '${tempInterests.length} selected',
                              style: TextStyle(
                                color: Colors.white.withOpacity(0.6),
                                fontSize: 12,
                              ),
                              overflow: TextOverflow.ellipsis,
                              maxLines: 1,
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(width: 8),
                      Container(
                        decoration: BoxDecoration(
                          color: const Color(0xFF232A3B),
                          borderRadius: BorderRadius.circular(10),
                          border: Border.all(
                            color: const Color(0xFF5F8DFF).withOpacity(0.3),
                            width: 1,
                          ),
                        ),
                        child: IconButton(
                          onPressed: () => Navigator.pop(context),
                          icon: const Icon(Icons.close,
                              color: Colors.white, size: 20),
                          padding: const EdgeInsets.all(8),
                          constraints: const BoxConstraints(
                            minWidth: 36,
                            minHeight: 36,
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),
                  Container(
                    width: double.infinity,
                    height: 4,
                    decoration: BoxDecoration(
                      gradient: const LinearGradient(
                        colors: [Color(0xFF5F8DFF), Color(0xFF4A73E8)],
                      ),
                      borderRadius: BorderRadius.circular(2),
                    ),
                  ),
                  const SizedBox(height: 20),
                  // Categories
                  Flexible(
                    child: SingleChildScrollView(
                      child: Container(
                        width: double.infinity,
                        padding: const EdgeInsets.symmetric(vertical: 8),
                        child: Wrap(
                          spacing: 10,
                          runSpacing: 10,
                          alignment: WrapAlignment.start,
                          children: categories.map((cat) {
                            final isSelected =
                                tempInterests.contains(cat['name']);
                            return GestureDetector(
                              onTap: () {
                                debugPrint(
                                    'Tapping category: ${cat['name']}, currently selected: $isSelected');
                                toggleInterest(cat['name']);
                                setModalState(
                                    () {}); // Rebuild modal UI immediately
                              },
                              child: AnimatedContainer(
                                duration: const Duration(milliseconds: 250),
                                curve: Curves.easeInOut,
                                padding: const EdgeInsets.symmetric(
                                    vertical: 12, horizontal: 20),
                                decoration: BoxDecoration(
                                  gradient: isSelected
                                      ? const LinearGradient(
                                          colors: [
                                            Color(0xFF5F8DFF),
                                            Color(0xFF4A73E8)
                                          ],
                                          begin: Alignment.topLeft,
                                          end: Alignment.bottomRight,
                                        )
                                      : null,
                                  color: isSelected
                                      ? null
                                      : const Color(0xFF232A3B),
                                  borderRadius: BorderRadius.circular(25),
                                  border: Border.all(
                                    color: isSelected
                                        ? const Color(0xFF5F8DFF)
                                        : const Color(0xFF5F8DFF)
                                            .withOpacity(0.3),
                                    width: isSelected ? 2 : 1,
                                  ),
                                  boxShadow: isSelected
                                      ? [
                                          BoxShadow(
                                            color: const Color(0xFF5F8DFF)
                                                .withOpacity(0.4),
                                            blurRadius: 12,
                                            offset: const Offset(0, 4),
                                          ),
                                        ]
                                      : null,
                                ),
                                child: Row(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    if (isSelected)
                                      const Padding(
                                        padding: EdgeInsets.only(right: 8),
                                        child: Icon(
                                          Icons.check_circle,
                                          color: Colors.white,
                                          size: 18,
                                        ),
                                      ),
                                    Flexible(
                                      child: AnimatedDefaultTextStyle(
                                        duration:
                                            const Duration(milliseconds: 250),
                                        style: TextStyle(
                                          color: isSelected
                                              ? Colors.white
                                              : const Color(0xFF5F8DFF),
                                          fontSize: isSelected ? 15 : 14,
                                          fontWeight: isSelected
                                              ? FontWeight.bold
                                              : FontWeight.w600,
                                          letterSpacing: 0.3,
                                        ),
                                        child: Text(
                                          cat['name'],
                                          overflow: TextOverflow.ellipsis,
                                          maxLines: 1,
                                        ),
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            );
                          }).toList(),
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(height: 20),
                  // Update button
                  Container(
                    width: double.infinity,
                    decoration: BoxDecoration(
                      gradient: const LinearGradient(
                        colors: [Color(0xFF5F8DFF), Color(0xFF4A73E8)],
                        begin: Alignment.centerLeft,
                        end: Alignment.centerRight,
                      ),
                      borderRadius: BorderRadius.circular(12),
                      boxShadow: [
                        BoxShadow(
                          color: const Color(0xFF5F8DFF).withOpacity(0.4),
                          blurRadius: 12,
                          offset: const Offset(0, 6),
                        ),
                      ],
                    ),
                    child: ElevatedButton(
                      onPressed: () {
                        Navigator.pop(context);
                        updateInterests();
                      },
                      style: ElevatedButton.styleFrom(
                        backgroundColor: Colors.transparent,
                        shadowColor: Colors.transparent,
                        padding: const EdgeInsets.symmetric(vertical: 16),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                      ),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: const [
                          Icon(Icons.check_circle_outline,
                              color: Colors.white, size: 22),
                          SizedBox(width: 8),
                          Text(
                            'Update Interests',
                            style: TextStyle(
                              color: Colors.white,
                              fontSize: 16,
                              fontWeight: FontWeight.bold,
                              letterSpacing: 0.5,
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
    );
    setState(() {
      // Ensure tempInterests is properly initialized with a fresh copy
      tempInterests = List<String>.from(user['interests'] ?? []);
      editCategoryModal = true;
      fieldModalVisible = false;
      debugPrint(
          'ProfileScreen: Opening category modal with ${tempInterests.length} pre-selected interests');
      debugPrint('ProfileScreen: Initial interests: $tempInterests');
    });
  }

  void toggleInterest(String cat) {
    final wasSelected = tempInterests.contains(cat);

    setState(() {
      if (wasSelected) {
        // Deselecting - remove from list
        tempInterests.remove(cat);
        debugPrint('ProfileScreen: 🔴 DESELECTED interest: $cat');
      } else {
        // Selecting - add to list
        tempInterests.add(cat);
        debugPrint('ProfileScreen: 🟢 SELECTED interest: $cat');
      }
      debugPrint(
          'ProfileScreen: Current interests count: ${tempInterests.length}');
      debugPrint('ProfileScreen: All interests: $tempInterests');
    });

    // Add haptic feedback for better user experience
    try {
      Vibration.vibrate(duration: 50);
    } catch (e) {
      // Ignore if vibration is not available
    }
  }

  void updateInterests() async {
    if (await Vibration.hasVibrator()) {
      Vibration.vibrate(duration: 100);
    }
    try {
      final prefs = await SharedPreferences.getInstance();
      final userId = prefs.getString('user_id');
      if (userId == null) {
        _showErrorDialog('User ID not found');
        setState(() => editCategoryModal = false);
        return;
      }
      // Find category IDs for selected interests
      List<dynamic> selectedCategoryIds = categories
          .where((cat) => tempInterests.contains(cat['name']))
          .map((cat) => cat['id'])
          .toList();
      debugPrint(
          'ProfileScreen: Updating interests (categories) with payload: {user_id: $userId, categories: $selectedCategoryIds}');
      final response =
          await ApiService.apiPost('profile/update_categories.php', {
        'user_id': userId,
        'categories': selectedCategoryIds,
      });
      if (response['success'] == true) {
        debugPrint(
            'ProfileScreen: Interests (categories) update successful, response: $response');

        // Update local state immediately
        setState(() {
          user = {...user, 'interests': List<String>.from(tempInterests)};
          editCategoryModal = false;
        });

        // Refresh profile from server to ensure sync
        await fetchProfile();

        _showSuccessDialog('Interests Updated',
            response['message'] ?? 'Interests updated successfully');
      } else {
        debugPrint(
            'ProfileScreen: Interests (categories) update failed, response: $response');
        _showErrorDialog(response['message'] ?? 'Failed to update interests');
      }
    } catch (err) {
      debugPrint('ProfileScreen: Update interests (categories) error: $err');
      _showErrorDialog('Network error: $err');
    }
  }

  void handleLogout() async {
    if (await Vibration.hasVibrator()) {
      Vibration.vibrate(duration: 100);
    }

    // Show confirmation dialog before logout
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Confirm Logout'),
        content: const Text('Are you sure you want to logout?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Cancel'),
          ),
          TextButton(
            onPressed: () async {
              Navigator.pop(context); // Close confirmation dialog
              await _performLogout();
            },
            child: const Text('Logout', style: TextStyle(color: Colors.red)),
          ),
        ],
      ),
    );
  }

  Future<void> _performLogout() async {
    try {
      // Show loading indicator
      showDialog(
        context: context,
        barrierDismissible: false,
        builder: (context) => const Center(child: CircularProgressIndicator()),
      );

      // Clear all session data using SessionManager
      await SessionManager.clearSession();

      // Call logout API to clear server session
      try {
        await ApiService.apiPost('auth/logout.php', {});
      } catch (e) {
        debugPrint('Server logout error (non-critical): $e');
      }

      Navigator.pop(context); // Close loading dialog

      debugPrint('ProfileScreen: Complete logout - all data cleared');
      _showSuccessDialog('Logged Out', 'You have been logged out successfully',
          onOk: () {
        Navigator.of(context).pushNamedAndRemoveUntil('/', (route) => false);
      });
    } catch (err) {
      Navigator.pop(context); // Close loading dialog if open
      debugPrint('ProfileScreen: Logout error: $err');
      _showErrorDialog('Error during logout');
    }
  }

  void _openPrivacyPolicy() async {
    if (await Vibration.hasVibrator()) {
      Vibration.vibrate(duration: 100);
    }
    Navigator.of(context).push(
      MaterialPageRoute(
        builder: (context) => const PrivacyPolicyScreen(),
      ),
    );
  }

  void _openDisclaimer() async {
    if (await Vibration.hasVibrator()) {
      Vibration.vibrate(duration: 100);
    }
    Navigator.of(context).push(
      MaterialPageRoute(
        builder: (context) => const DisclaimerScreen(),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    debugPrint('ProfileScreen: Building UI');
    return Scaffold(
      backgroundColor: const Color(0xFF0F0F0F),
      appBar: AppBar(
        backgroundColor: const Color(0xFF0F0F0F),
        elevation: 0,
        centerTitle: true,
        automaticallyImplyLeading: false,
        title: const Text(
          'FIM TECH',
          style: TextStyle(
            fontSize: 22,
            fontWeight: FontWeight.w700,
            color: Color(0xFF5F8DFF),
          ),
        ),
        actions: [
          IconButton(
            onPressed: handleLogout,
            icon: const Icon(
              Icons.logout,
              color: Color(0xFF5F8DFF),
              size: 24,
            ),
            tooltip: 'Logout',
          ),
        ],
      ),
      body: SafeArea(
        child: SingleChildScrollView(
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              children: [
                // Profile container
                const SizedBox(height: 20),
                Row(
                  children: [
                    Container(
                      width: 64,
                      height: 64,
                      decoration: BoxDecoration(
                        gradient: const LinearGradient(
                          colors: [Color(0xFF5F8DFF), Color(0xFF4A73E8)],
                          begin: Alignment.topLeft,
                          end: Alignment.bottomRight,
                        ),
                        borderRadius: BorderRadius.circular(32),
                        border: Border.all(
                            color: const Color(0xFF5F8DFF), width: 2),
                        boxShadow: [
                          BoxShadow(
                            color: const Color(0xFF5F8DFF).withOpacity(0.3),
                            blurRadius: 8,
                            offset: const Offset(0, 2),
                          ),
                        ],
                      ),
                      child: Center(
                        child: Text(
                          _getFirstLetter(user['name'] ?? ''),
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 24,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            user['name'],
                            style: const TextStyle(
                              fontSize: 22,
                              color: Color(0xFF5F8DFF),
                              fontWeight: FontWeight.w700,
                            ),
                            overflow: TextOverflow.ellipsis,
                            maxLines: 1,
                          ),
                          const SizedBox(height: 4),
                          Text(
                            user['email'],
                            style: const TextStyle(
                              color: Color(0xFFEEEEEE),
                              fontSize: 14,
                            ),
                            overflow: TextOverflow.ellipsis,
                            maxLines: 1,
                          ),
                        ],
                      ),
                    ),
                    IconButton(
                      onPressed: () => openFieldModal('name'),
                      icon: const Icon(
                        Icons.edit,
                        size: 20,
                        color: Color(0xFF5F8DFF),
                      ),
                      padding: EdgeInsets.zero,
                      constraints: const BoxConstraints(
                        minWidth: 40,
                        minHeight: 40,
                      ),
                    ),
                  ],
                ),
                // Personal Details
                const SizedBox(height: 30),
                Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: const Color(0xFF232A3B),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(
                        color: const Color(0xFF5F8DFF).withOpacity(0.3)),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Row(
                        children: [
                          Expanded(
                            child: Text(
                              'Personal Details',
                              style: TextStyle(
                                fontSize: 16,
                                fontWeight: FontWeight.w700,
                                color: Color(0xFF5F8DFF),
                              ),
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 16),
                      _buildDetailRow(
                          'Email', user['email']?.toString(), 'email'),
                      const SizedBox(height: 12),
                      _buildDetailRow(
                          'Phone', user['phone']?.toString(), 'phone'),
                      const SizedBox(height: 12),
                      _buildDetailRow('Bio', user['bio']?.toString(), 'bio'),
                      const SizedBox(height: 12),
                      _buildDetailRow(
                          'Language',
                          user['language']?.toString().isNotEmpty == true
                              ? '${user['language']?.toString()[0].toUpperCase()}${user['language']?.toString().substring(1).toLowerCase()}'
                              : 'Not set',
                          'language'),
                    ],
                  ),
                ),
                // Interested Topics
                const SizedBox(height: 30),
                Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: const Color(0xFF232A3B),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(
                        color: const Color(0xFF5F8DFF).withOpacity(0.3)),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          const Expanded(
                            child: Text(
                              'Interested Topics',
                              style: TextStyle(
                                fontSize: 16,
                                fontWeight: FontWeight.w700,
                                color: Color(0xFF5F8DFF),
                              ),
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                          IconButton(
                            onPressed: openCategoryModal,
                            icon: const Icon(Icons.edit,
                                size: 18, color: Color(0xFF5F8DFF)),
                            padding: EdgeInsets.zero,
                            constraints: const BoxConstraints(
                              minWidth: 36,
                              minHeight: 36,
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 16),
                      user['interests'].isEmpty
                          ? Text(
                              'No interests selected',
                              style: TextStyle(
                                color: Colors.white.withOpacity(0.5),
                                fontSize: 14,
                                fontStyle: FontStyle.italic,
                              ),
                            )
                          : Wrap(
                              spacing: 8,
                              runSpacing: 8,
                              children: user['interests'].map<Widget>((topic) {
                                return Container(
                                  padding: const EdgeInsets.symmetric(
                                      vertical: 6, horizontal: 12),
                                  decoration: BoxDecoration(
                                    color: const Color(0xFF5F8DFF),
                                    borderRadius: BorderRadius.circular(20),
                                  ),
                                  child: Text(
                                    topic,
                                    style: const TextStyle(
                                      color: Colors.white,
                                      fontSize: 13,
                                    ),
                                    overflow: TextOverflow.ellipsis,
                                    maxLines: 1,
                                  ),
                                );
                              }).toList(),
                            ),
                    ],
                  ),
                ),

                // Version and Legal Section
                const SizedBox(height: 40),
                Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: const Color(0xFF232A3B),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(
                        color: const Color(0xFF5F8DFF).withOpacity(0.3)),
                  ),
                  child: Column(
                    children: [
                      // Privacy Policy and Disclaimer Links
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                        children: [
                          GestureDetector(
                            onTap: () => _openPrivacyPolicy(),
                            child: Container(
                              padding: const EdgeInsets.symmetric(
                                  horizontal: 16, vertical: 10),
                              decoration: BoxDecoration(
                                color: const Color(0xFF5F8DFF).withOpacity(0.1),
                                borderRadius: BorderRadius.circular(8),
                                border: Border.all(
                                    color: const Color(0xFF5F8DFF), width: 1),
                              ),
                              child: const Text(
                                'Privacy Policy',
                                style: TextStyle(
                                  color: Color(0xFF5F8DFF),
                                  fontSize: 13,
                                  fontWeight: FontWeight.w600,
                                ),
                              ),
                            ),
                          ),
                          GestureDetector(
                            onTap: () => _openDisclaimer(),
                            child: Container(
                              padding: const EdgeInsets.symmetric(
                                  horizontal: 16, vertical: 10),
                              decoration: BoxDecoration(
                                color: const Color(0xFF5F8DFF).withOpacity(0.1),
                                borderRadius: BorderRadius.circular(8),
                                border: Border.all(
                                    color: const Color(0xFF5F8DFF), width: 1),
                              ),
                              child: const Text(
                                'Disclaimer',
                                style: TextStyle(
                                  color: Color(0xFF5F8DFF),
                                  fontSize: 13,
                                  fontWeight: FontWeight.w600,
                                ),
                              ),
                            ),
                          ),
                        ],
                      ),

                      const SizedBox(height: 16),

                      // App Version
                      const Text(
                        'Version 1.0.0',
                        style: TextStyle(
                          color: Color(0xFFAAAAAA),
                          fontSize: 12,
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                      const SizedBox(height: 4),
                      const Text(
                        '© 2025 FIM TECH',
                        style: TextStyle(
                          color: Color(0xFFAAAAAA),
                          fontSize: 10,
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 20),
              ],
            ),
          ),
        ),
      ),
      bottomNavigationBar: const BottomNavigation(),
    );
  }

  Widget _buildDetailRow(String label, String? value, String field) {
    final displayValue = value ?? 'Not set';
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Expanded(
                child: Text(
                  label,
                  style: const TextStyle(
                    color: Color(0xFF5F8DFF),
                    fontSize: 14,
                    fontWeight: FontWeight.w600,
                  ),
                  overflow: TextOverflow.ellipsis,
                ),
              ),
              IconButton(
                onPressed: () => openFieldModal(field),
                icon: const Icon(
                  Icons.edit,
                  size: 16,
                  color: Color(0xFF5F8DFF),
                ),
                padding: EdgeInsets.zero,
                constraints: const BoxConstraints(
                  minWidth: 36,
                  minHeight: 36,
                ),
              ),
            ],
          ),
          const SizedBox(height: 4),
          Text(
            displayValue,
            style: const TextStyle(
              color: Color(0xFFEEEEEE),
              fontSize: 15,
            ),
            maxLines: 2,
            overflow: TextOverflow.ellipsis,
          ),
        ],
      ),
    );
  }
}
