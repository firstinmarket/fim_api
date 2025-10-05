import 'package:flutter/material.dart';
import '../../services/api_service.dart';
import '../../services/session_manager.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  _LoginScreenState createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  final _forgotEmailController = TextEditingController();
  final _newPasswordController = TextEditingController();
  String _emailError = '';
  bool _isResettingPassword = false;

  Future<void> _handleLogin() async {
    final email = _emailController.text.trim();
    final password = _passwordController.text.trim();

    if (email.isEmpty || password.isEmpty) {
      _showErrorDialog('Please enter both email and password');
      return;
    }

    // Show loading indicator
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => const Center(
        child: CircularProgressIndicator(),
      ),
    );

    try {
      // Test connection first
      print('Testing API connection...');
      bool connectionAvailable = await ApiService.testConnection();
      if (!connectionAvailable) {
        Navigator.pop(context); // Close loading dialog
        _showErrorDialog(
            'Unable to connect to server. Please check your internet connection and try again.');
        return;
      }

      print('Attempting login...');
      final response = await ApiService.apiPost('auth/login.php', {
        'email': email,
        'password': password,
      });

      Navigator.pop(context); // Close loading dialog

      print('Login response: $response');

      if (response['success'] == true && response['user_id'] != null) {
        // Save session using SessionManager
        await SessionManager.saveLoginSession(
          userId: response['user_id'].toString(),
          email: email,
        );

        debugPrint('User logged in successfully with persistent session');
        if (!mounted) return;
        Navigator.pushReplacementNamed(context, '/profile');
      } else {
        final errorMsg = response['error'] ?? 'Invalid credentials';
        _showErrorDialog(errorMsg);
      }
    } catch (err) {
      Navigator.pop(context); // Close loading dialog
      print('Login error: $err');
      String errorMessage = 'Network error: Unable to connect to server';

      if (err.toString().contains('SocketException')) {
        errorMessage =
            'No internet connection. Please check your network and try again.';
      } else if (err.toString().contains('TimeoutException')) {
        errorMessage = 'Connection timeout. Please try again.';
      } else if (err.toString().contains('FormatException')) {
        errorMessage = 'Server response error. Please try again later.';
      }

      _showErrorDialog(errorMessage);
    }
  }

  void _showErrorDialog(String message) {
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

  void _showSuccessDialog(String message) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Success'),
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

  Future<void> _handleForgotPassword() async {
    final email = _forgotEmailController.text.trim();
    final newPassword = _newPasswordController.text.trim();

    if (email.isEmpty || newPassword.isEmpty) {
      _showErrorDialog('Please enter both email and new password');
      return;
    }

    if (!RegExp(r'^\S+@\S+\.\S+$').hasMatch(email)) {
      _showErrorDialog('Please enter a valid email address');
      return;
    }

    if (newPassword.length < 6) {
      _showErrorDialog('Password must be at least 6 characters long');
      return;
    }

    setState(() {
      _isResettingPassword = true;
    });

    try {
      final response = await ApiService.apiPost('auth/forgot_password.php', {
        'email': email,
        'new_password': newPassword,
      });

      setState(() {
        _isResettingPassword = false;
      });

      if (response['success'] == true) {
        Navigator.pop(context);
        _showSuccessDialog(
            'Password updated successfully! You can now login with your new password.');

        _forgotEmailController.clear();
        _newPasswordController.clear();
      } else {
        final errorMsg = response['error'] ?? 'Failed to reset password';
        _showErrorDialog(errorMsg);
      }
    } catch (err) {
      setState(() {
        _isResettingPassword = false;
      });
      _showErrorDialog('Network error. Please check your connection.');
    }
  }

  void _showForgotPasswordDialog() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text(
          'Reset Password',
          style: TextStyle(
            fontWeight: FontWeight.bold,
            color: Color(0xFF333333),
          ),
        ),
        content: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Text(
                'Enter your email and new password to reset your account password.',
                style: TextStyle(color: Color(0xFF666666)),
              ),
              const SizedBox(height: 20),
              // Email input
              TextFormField(
                controller: _forgotEmailController,
                keyboardType: TextInputType.emailAddress,
                decoration: InputDecoration(
                  labelText: 'Email',
                  hintText: 'Enter your email',
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(8),
                  ),
                  contentPadding: const EdgeInsets.all(12),
                ),
              ),
              const SizedBox(height: 16),
              // New password input
              TextFormField(
                controller: _newPasswordController,
                obscureText: true,
                decoration: InputDecoration(
                  labelText: 'New Password',
                  hintText: 'Enter new password (min 6 characters)',
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(8),
                  ),
                  contentPadding: const EdgeInsets.all(12),
                ),
              ),
            ],
          ),
        ),
        actions: [
          TextButton(
            onPressed: () {
              Navigator.pop(context);
              _forgotEmailController.clear();
              _newPasswordController.clear();
            },
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            onPressed: _isResettingPassword ? null : _handleForgotPassword,
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFF5F8DFF),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(8),
              ),
            ),
            child: _isResettingPassword
                ? const SizedBox(
                    width: 16,
                    height: 16,
                    child: CircularProgressIndicator(
                      strokeWidth: 2,
                      valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                    ),
                  )
                : const Text(
                    'Update Password',
                    style: TextStyle(color: Colors.white),
                  ),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Stack(
        children: [
          // Background image
          Container(
            decoration: const BoxDecoration(
              image: DecorationImage(
                image: AssetImage('assets/main/main.webp'),
                fit: BoxFit.cover,
              ),
            ),
          ),
          // Overlay
          Container(
            color: Colors.black.withOpacity(0.5),
            padding: const EdgeInsets.symmetric(horizontal: 24),
            child: Center(
              child: SingleChildScrollView(
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    // Title
                    const Text(
                      'Login',
                      style: TextStyle(
                        color: Colors.white,
                        fontSize: 28,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 32),
                    // Email input
                    TextFormField(
                      controller: _emailController,
                      keyboardType: TextInputType.emailAddress,
                      style: const TextStyle(color: Colors.white, fontSize: 16),
                      decoration: InputDecoration(
                        hintText: 'Email',
                        hintStyle: const TextStyle(color: Colors.white70),
                        filled: true,
                        fillColor: Colors.white.withOpacity(0.2),
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(8),
                          borderSide: BorderSide.none,
                        ),
                        contentPadding: const EdgeInsets.all(12),
                      ),
                      onChanged: (value) {
                        setState(() {
                          if (value.isNotEmpty &&
                              !RegExp(r'^\S+@\S+\.\S+$').hasMatch(value)) {
                            _emailError = 'Please enter a valid email address';
                          } else {
                            _emailError = '';
                          }
                        });
                      },
                    ),
                    // Email error
                    if (_emailError.isNotEmpty)
                      Padding(
                        padding: const EdgeInsets.only(bottom: 8),
                        child: Text(
                          _emailError,
                          style: const TextStyle(
                            color: Colors.red,
                            fontSize: 14,
                          ),
                        ),
                      ),
                    const SizedBox(height: 16),
                    // Password input
                    TextFormField(
                      controller: _passwordController,
                      obscureText: true,
                      style: const TextStyle(color: Colors.white, fontSize: 16),
                      decoration: InputDecoration(
                        hintText: 'Password',
                        hintStyle: const TextStyle(color: Colors.white70),
                        filled: true,
                        fillColor: Colors.white.withOpacity(0.2),
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(8),
                          borderSide: BorderSide.none,
                        ),
                        contentPadding: const EdgeInsets.all(12),
                      ),
                    ),
                    const SizedBox(height: 40),
                    // Login button
                    ElevatedButton(
                      onPressed: _handleLogin,
                      style: ElevatedButton.styleFrom(
                        backgroundColor: Colors.black.withOpacity(0.2),
                        padding: const EdgeInsets.symmetric(
                          vertical: 8,
                          horizontal: 30,
                        ),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(25),
                          side: const BorderSide(color: Colors.white, width: 2),
                        ),
                        elevation: 4,
                        shadowColor: Colors.black.withOpacity(0.3),
                      ),
                      child: const Text(
                        'Login',
                        style: TextStyle(
                          color: Colors.white,
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ),
                    const SizedBox(height: 24),
                    // Forgot Password
                    TextButton(
                      onPressed: _showForgotPasswordDialog,
                      child: const Text(
                        'Forgot Password?',
                        style: TextStyle(
                          color: Colors.white,
                          fontSize: 16,
                          decoration: TextDecoration.underline,
                        ),
                      ),
                    ),
                    const SizedBox(height: 24),
                    // Sign Up
                    TextButton(
                      onPressed: () {
                        Navigator.pushNamed(context, '/signup');
                      },
                      child: const Text(
                        "Don't have an account? Sign Up",
                        style: TextStyle(
                          color: Colors.white,
                          fontSize: 16,
                          decoration: TextDecoration.underline,
                        ),
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

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    _forgotEmailController.dispose();
    _newPasswordController.dispose();
    super.dispose();
  }
}
