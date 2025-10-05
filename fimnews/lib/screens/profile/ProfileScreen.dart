import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:vibration/vibration.dart';
import '../../services/api_service.dart';
import '../components/navigation.dart';
import '../legal/PrivacyPolicyScreen.dart';
import '../legal/DisclaimerScreen.dart';

class ProfileScreen extends StatefulWidget {
  const ProfileScreen({super.key});

  @override
  _ProfileScreenState createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
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

  @override
  void initState() {
    super.initState();
    debugPrint('ProfileScreen: Initializing');
    fetchProfile();
    fetchCategories();
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
                  .map((cat) => cat['subcategory_name'] ?? '')
                  .where((name) => name != null && name != '')
                  .toList(),
            ),
          };
          debugPrint('ProfileScreen: Profile loaded: ${user['name']}');
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
          query: {'type': 'subcategories'});
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
      builder: (context) => Dialog(
        backgroundColor: Colors.transparent,
        child: Container(
          width: MediaQuery.of(context).size.width * 0.85,
          padding: const EdgeInsets.all(20),
          decoration: BoxDecoration(
            color: const Color(0xFF1E1E1E).withOpacity(0.95),
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: const Color(0xFF5F8DFF), width: 1),
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.end,
                children: [
                  IconButton(
                    onPressed: () => Navigator.pop(context),
                    icon:
                        const Icon(Icons.close, color: Colors.white, size: 22),
                  ),
                ],
              ),
              Text(
                'Edit ${editField[0].toUpperCase() + editField.substring(1)}',
                style: const TextStyle(
                  color: Color(0xFF5F8DFF),
                  fontSize: 16,
                  fontWeight: FontWeight.w700,
                ),
              ),
              const SizedBox(height: 16),
              if (field == 'language')
                Container(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                  decoration: BoxDecoration(
                    color: const Color(0xFF232A3B),
                    borderRadius: BorderRadius.circular(10),
                    border:
                        Border.all(color: const Color(0xFF5F8DFF), width: 1),
                  ),
                  child: DropdownButtonHideUnderline(
                    child: DropdownButton<String>(
                      value: (tempValue == 'english' || tempValue == 'tamil')
                          ? tempValue
                          : 'english',
                      isExpanded: true,
                      dropdownColor: const Color(0xFF232A3B),
                      style: const TextStyle(color: Colors.white),
                      icon: const Icon(Icons.arrow_drop_down,
                          color: Color(0xFF5F8DFF)),
                      items: const [
                        DropdownMenuItem(
                            value: 'english', child: Text('English')),
                        DropdownMenuItem(value: 'tamil', child: Text('Tamil')),
                      ],
                      onChanged: (String? newValue) {
                        if (newValue != null) {
                          setState(() {
                            tempValue = newValue;
                          });
                        }
                      },
                    ),
                  ),
                )
              else
                TextField(
                  controller: TextEditingController(text: tempValue),
                  onChanged: (value) => tempValue = value,
                  decoration: InputDecoration(
                    hintText: 'Enter $editField',
                    hintStyle: const TextStyle(color: Color(0xFFAAAAAA)),
                    filled: true,
                    fillColor: const Color(0xFF232A3B),
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(10),
                      borderSide:
                          const BorderSide(color: Color(0xFF5F8DFF), width: 1),
                    ),
                  ),
                  style: const TextStyle(color: Colors.white),
                ),
              const SizedBox(height: 14),
              ElevatedButton(
                onPressed: () {
                  Navigator.pop(context);
                  updateField();
                },
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF5F8DFF),
                  padding: const EdgeInsets.symmetric(vertical: 12),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(10),
                    side: const BorderSide(color: Color(0xFF5F8DFF), width: 1),
                  ),
                ),
                child: const Text(
                  'Update',
                  style: TextStyle(
                      color: Colors.white, fontWeight: FontWeight.w600),
                ),
              ),
            ],
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
      } else {
        tempValue = user[field]?.toString() ?? '';
      }
      fieldModalVisible = true;
      editCategoryModal = false;
      debugPrint('ProfileScreen: Opening field modal for: $field');
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
      builder: (context) => Dialog(
        backgroundColor: Colors.transparent,
        child: Container(
          width: MediaQuery.of(context).size.width * 0.7,
          constraints: BoxConstraints(
            maxHeight: MediaQuery.of(context).size.height * 0.6,
          ),
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: const Color(0xFF1E1E1E).withOpacity(0.98),
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: const Color(0xFF5F8DFF), width: 1),
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.end,
                children: [
                  IconButton(
                    onPressed: () => Navigator.pop(context),
                    icon:
                        const Icon(Icons.close, color: Colors.white, size: 22),
                  ),
                ],
              ),
              const Text(
                'Edit Interests',
                style: TextStyle(
                  color: Color(0xFF5F8DFF),
                  fontSize: 16,
                  fontWeight: FontWeight.w700,
                ),
              ),
              const SizedBox(height: 10),
              Expanded(
                child: SingleChildScrollView(
                  child: Wrap(
                    spacing: 8,
                    runSpacing: 8,
                    children: categories.map((cat) {
                      final isSelected = tempInterests.contains(cat['name']);
                      return GestureDetector(
                        onTap: () => toggleInterest(cat['name']),
                        child: Container(
                          padding: const EdgeInsets.symmetric(
                              vertical: 6, horizontal: 14),
                          decoration: BoxDecoration(
                            color: isSelected
                                ? const Color(0xFF5F8DFF)
                                : const Color(0xFF232A3B),
                            borderRadius: BorderRadius.circular(20),
                            border: Border.all(
                                color: const Color(0xFF5F8DFF), width: 1),
                          ),
                          child: Text(
                            cat['name'],
                            style: TextStyle(
                              color: isSelected
                                  ? Colors.white
                                  : const Color(0xFF5F8DFF),
                              fontSize: 15,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ),
                      );
                    }).toList(),
                  ),
                ),
              ),
              const SizedBox(height: 14),
              ElevatedButton(
                onPressed: () {
                  Navigator.pop(context);
                  updateInterests();
                },
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF5F8DFF),
                  padding: const EdgeInsets.symmetric(vertical: 12),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(10),
                    side: const BorderSide(color: Color(0xFF5F8DFF), width: 1),
                  ),
                ),
                child: Padding(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
                  child: const Text(
                    'Update Interests',
                    style: TextStyle(
                        color: Colors.white, fontWeight: FontWeight.w600),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
    setState(() {
      tempInterests = List.from(user['interests']);
      editCategoryModal = true;
      fieldModalVisible = false;
      debugPrint('ProfileScreen: Opening category modal');
    });
  }

  void toggleInterest(String cat) {
    setState(() {
      tempInterests = tempInterests.contains(cat)
          ? tempInterests.where((c) => c != cat).toList()
          : [...tempInterests, cat];
      debugPrint(
          'ProfileScreen: Toggled interest: $cat, new interests: $tempInterests');
    });
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
        _showSuccessDialog('Interests Updated',
            response['message'] ?? 'Interests updated successfully');
        setState(() {
          user = {...user, 'interests': tempInterests};
          editCategoryModal = false;
        });
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
    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.remove('user_id');
      debugPrint('ProfileScreen: Logged out, user_id cleared');
      _showSuccessDialog('Logged Out', 'You have been logged out successfully',
          onOk: () {
        Navigator.pushReplacementNamed(context, '/login');
      });
    } catch (err) {
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
          'FIM NEWS',
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
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: const Color(0xFF232A3B),
                        borderRadius: BorderRadius.circular(50),
                        border: Border.all(color: const Color(0xFF5F8DFF)),
                      ),
                      child: const Icon(
                        Icons.person,
                        color: Color(0xFF5F8DFF),
                        size: 40,
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
                          ),
                          const SizedBox(height: 4),
                          Text(
                            user['email'],
                            style: const TextStyle(
                              color: Color(0xFFEEEEEE),
                              fontSize: 14,
                            ),
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
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          const Text(
                            'Personal Details',
                            style: TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.w700,
                              color: Color(0xFF5F8DFF),
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
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          const Text(
                            'Interested Topics',
                            style: TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.w700,
                              color: Color(0xFF5F8DFF),
                            ),
                          ),
                          IconButton(
                            onPressed: openCategoryModal,
                            icon: const Icon(Icons.edit,
                                size: 18, color: Color(0xFF5F8DFF)),
                          ),
                        ],
                      ),
                      const SizedBox(height: 16),
                      Wrap(
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
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                label,
                style: const TextStyle(
                  color: Color(0xFF5F8DFF),
                  fontSize: 14,
                  fontWeight: FontWeight.w600,
                ),
              ),
              IconButton(
                onPressed: () => openFieldModal(field),
                icon: const Icon(
                  Icons.edit,
                  size: 16,
                  color: Color(0xFF5F8DFF),
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
