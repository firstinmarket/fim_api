import 'package:flutter/material.dart';
import 'package:news_app/services/api_service.dart';

class SignupScreen extends StatefulWidget {
  const SignupScreen({super.key});

  @override
  _SignupScreenState createState() => _SignupScreenState();
}

class _SignupScreenState extends State<SignupScreen> {
  final _nameController = TextEditingController();
  final _mobileController = TextEditingController();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  final List<TextEditingController> _otpControllers =
      List.generate(6, (_) => TextEditingController());
  final List<FocusNode> _otpFocusNodes = List.generate(6, (_) => FocusNode());
  String _emailError = '';
  bool _showOtp = false;

  Future<void> _handleSignup() async {
    final name = _nameController.text;
    final mobile = _mobileController.text;
    final email = _emailController.text;
    final password = _passwordController.text;

    if (name.isEmpty || mobile.isEmpty || email.isEmpty || password.isEmpty) {
      _showErrorDialog('Please fill all fields');
      return;
    }
    if (mobile.length != 10 || !RegExp(r'^[0-9]{10}$').hasMatch(mobile)) {
      _showErrorDialog('Mobile number must be exactly 10 digits');
      return;
    }
    if (!RegExp(r'^\S+@\S+\.\S+$').hasMatch(email)) {
      _showErrorDialog('Please enter a valid email address');
      return;
    }

    try {
      final response = await ApiService.apiPost('auth/signup.php', {
        'name': name,
        'mobile': mobile,
        'email': email,
        'password': password,
      });

      if (response['success'] || response['status'] == 'success') {
        setState(() {
          _showOtp = true;
        });
        _showSuccessDialog(
            'OTP Sent', 'Please enter the OTP sent to your email');
      } else {
        _showErrorDialog(
            response['error'] ?? response['message'] ?? 'Could not send OTP');
      }
    } catch (err) {
      _showErrorDialog('Network error');
    }
  }

  Future<void> _handleResendOtp() async {
    final name = _nameController.text;
    final mobile = _mobileController.text;
    final email = _emailController.text;
    final password = _passwordController.text;

    try {
      final response = await ApiService.apiPost('auth/signup.php', {
        'name': name,
        'mobile': mobile,
        'email': email,
        'password': password,
      });

      if (response['success'] == true) {
        _showSuccessDialog(
            'OTP Resent', 'A new OTP has been sent to your email');
      } else {
        _showErrorDialog(response['message'] ?? 'Could not resend OTP');
      }
    } catch (err) {
      _showErrorDialog('Network error');
    }
  }

  Future<void> _handleVerifyOtp() async {
    final otp = _otpControllers.map((controller) => controller.text).join();
    if (otp.length != 6) {
      _showErrorDialog('Please enter the 6-digit OTP');
      return;
    }

    try {
      final response = await ApiService.apiPost('auth/verify_otp.php', {
        'email': _emailController.text,
        'otp': otp,
      });

      if (response['success']) {
        _showSuccessDialog('Signup Successful', '', onOk: () {
          Navigator.pushReplacementNamed(context, '/login');
        });
      } else {
        _showErrorDialog(response['message'] ?? 'Invalid OTP');
      }
    } catch (err) {
      _showErrorDialog('Network error');
    }
  }

  void _handleOtpChange(String value, int index) {
    final digit = value.replaceAll(RegExp(r'[^0-9]'), '').substring(0, 1);
    _otpControllers[index].text = digit;
    if (digit.isNotEmpty && index < 5) {
      _otpFocusNodes[index + 1].requestFocus();
    } else if (digit.isEmpty && index > 0) {
      _otpFocusNodes[index - 1].requestFocus();
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

  void _showSuccessDialog(String title, String message, {VoidCallback? onOk}) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Text(title),
        content: message.isNotEmpty ? Text(message) : null,
        actions: [
          TextButton(
            onPressed: () {
              Navigator.pop(context);
              if (onOk != null) onOk();
            },
            child: const Text('OK'),
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
            padding: EdgeInsets.symmetric(
              horizontal: MediaQuery.of(context).size.width < 350 ? 16 : 24,
            ),
            child: Center(
              child: SingleChildScrollView(
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    // Title
                    const Text(
                      'Sign Up',
                      style: TextStyle(
                        color: Colors.white,
                        fontSize: 28,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 32),
                    if (!_showOtp) ...[
                      // Name input
                      TextFormField(
                        controller: _nameController,
                        style:
                            const TextStyle(color: Colors.white, fontSize: 16),
                        decoration: InputDecoration(
                          hintText: 'Name',
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
                      const SizedBox(height: 16),
                      // Mobile input
                      TextFormField(
                        controller: _mobileController,
                        keyboardType: TextInputType.phone,
                        style:
                            const TextStyle(color: Colors.white, fontSize: 16),
                        decoration: InputDecoration(
                          hintText: 'Mobile Number',
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
                      const SizedBox(height: 16),
                      // Email input
                      TextFormField(
                        controller: _emailController,
                        keyboardType: TextInputType.emailAddress,
                        style:
                            const TextStyle(color: Colors.white, fontSize: 16),
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
                              _emailError =
                                  'Please enter a valid email address';
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
                        style:
                            const TextStyle(color: Colors.white, fontSize: 16),
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
                      const SizedBox(height: 8),
                      // Sign Up button
                      ElevatedButton(
                        onPressed: _handleSignup,
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.black.withOpacity(0.2),
                          padding: const EdgeInsets.symmetric(
                            vertical: 12,
                            horizontal: 32,
                          ),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(25),
                            side:
                                const BorderSide(color: Colors.white, width: 2),
                          ),
                          elevation: 4,
                          shadowColor: Colors.black.withOpacity(0.3),
                          minimumSize: const Size(double.infinity, 0),
                        ),
                        child: const Text(
                          'Sign Up',
                          style: TextStyle(
                            color: Colors.white,
                            fontSize: 18,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ),
                      const SizedBox(height: 24),
                      // Login link
                      TextButton(
                        onPressed: () {
                          Navigator.pushNamed(context, '/login');
                        },
                        child: const Text(
                          'Already have an account? Login',
                          style: TextStyle(
                            color: Colors.white,
                            fontSize: 16,
                            decoration: TextDecoration.underline,
                          ),
                        ),
                      ),
                    ] else ...[
                      // Back button
                      ElevatedButton(
                        onPressed: () {
                          setState(() {
                            _showOtp = false;
                          });
                        },
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFF222222),
                          padding: EdgeInsets.symmetric(
                            vertical: MediaQuery.of(context).size.width < 350
                                ? 10
                                : 12,
                            horizontal: MediaQuery.of(context).size.width < 350
                                ? 24
                                : 32,
                          ),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(25),
                            side:
                                const BorderSide(color: Colors.white, width: 2),
                          ),
                          elevation: 4,
                          shadowColor: Colors.black.withOpacity(0.3),
                          minimumSize: const Size(double.infinity, 0),
                        ),
                        child: const Text(
                          'Back',
                          style: TextStyle(
                            color: Colors.white,
                            fontSize: 18,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ),
                      const SizedBox(height: 10),
                      // OTP label
                      const Text(
                        'Enter OTP',
                        style: TextStyle(
                          color: Colors.white,
                          fontSize: 18,
                        ),
                      ),
                      const SizedBox(height: 16),
                      // OTP input fields - Responsive
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8),
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                          children: List.generate(6, (index) {
                            return Flexible(
                              child: Container(
                                margin:
                                    const EdgeInsets.symmetric(horizontal: 4),
                                constraints: const BoxConstraints(
                                  minWidth: 35,
                                  maxWidth: 60,
                                  minHeight: 45,
                                  maxHeight: 60,
                                ),
                                child: AspectRatio(
                                  aspectRatio: 1.0,
                                  child: TextFormField(
                                    controller: _otpControllers[index],
                                    focusNode: _otpFocusNodes[index],
                                    keyboardType: TextInputType.number,
                                    textAlign: TextAlign.center,
                                    maxLength: 1,
                                    style: TextStyle(
                                      color: Colors.white,
                                      fontSize:
                                          MediaQuery.of(context).size.width <
                                                  350
                                              ? 18
                                              : 22,
                                      fontWeight: FontWeight.bold,
                                    ),
                                    decoration: InputDecoration(
                                      counterText: '',
                                      filled: true,
                                      fillColor: Colors.white.withOpacity(0.15),
                                      border: OutlineInputBorder(
                                        borderRadius: BorderRadius.circular(12),
                                        borderSide: const BorderSide(
                                          color: Colors.white70,
                                          width: 1.5,
                                        ),
                                      ),
                                      focusedBorder: OutlineInputBorder(
                                        borderRadius: BorderRadius.circular(12),
                                        borderSide: const BorderSide(
                                          color: Color(0xFF5F8DFF),
                                          width: 2.5,
                                        ),
                                      ),
                                      enabledBorder: OutlineInputBorder(
                                        borderRadius: BorderRadius.circular(12),
                                        borderSide: const BorderSide(
                                          color: Colors.white54,
                                          width: 1.5,
                                        ),
                                      ),
                                      contentPadding: EdgeInsets.zero,
                                    ),
                                    onChanged: (value) =>
                                        _handleOtpChange(value, index),
                                    onFieldSubmitted: (value) {
                                      if (index == 5 && value.isNotEmpty) {
                                        _otpFocusNodes[index].unfocus();
                                      }
                                    },
                                  ),
                                ),
                              ),
                            );
                          }),
                        ),
                      ),
                      SizedBox(
                        height:
                            MediaQuery.of(context).size.width < 350 ? 20 : 24,
                      ),
                      // Verify OTP button
                      ElevatedButton(
                        onPressed: _handleVerifyOtp,
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.black.withOpacity(0.2),
                          padding: EdgeInsets.symmetric(
                            vertical: MediaQuery.of(context).size.width < 350
                                ? 10
                                : 12,
                            horizontal: MediaQuery.of(context).size.width < 350
                                ? 24
                                : 32,
                          ),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(25),
                            side:
                                const BorderSide(color: Colors.white, width: 2),
                          ),
                          elevation: 4,
                          shadowColor: Colors.black.withOpacity(0.3),
                          minimumSize: const Size(double.infinity, 0),
                        ),
                        child: const Text(
                          'Verify & Login',
                          style: TextStyle(
                            color: Colors.white,
                            fontSize: 18,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ),
                      const SizedBox(height: 10),
                      // Resend OTP button
                      ElevatedButton(
                        onPressed: _handleResendOtp,
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFF5F8DFF),
                          padding: const EdgeInsets.symmetric(
                            vertical: 12,
                            horizontal: 32,
                          ),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(25),
                            side:
                                const BorderSide(color: Colors.white, width: 2),
                          ),
                          elevation: 4,
                          shadowColor: Colors.black.withOpacity(0.3),
                          minimumSize: const Size(double.infinity, 0),
                        ),
                        child: const Text(
                          'Resend OTP',
                          style: TextStyle(
                            color: Colors.white,
                            fontSize: 18,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ),
                      const SizedBox(height: 24),
                      // Login link
                      TextButton(
                        onPressed: () {
                          Navigator.pushNamed(context, '/login');
                        },
                        child: const Text(
                          'Already have an account? Login',
                          style: TextStyle(
                            color: Colors.white,
                            fontSize: 16,
                            decoration: TextDecoration.underline,
                          ),
                        ),
                      ),
                    ],
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
    _nameController.dispose();
    _mobileController.dispose();
    _emailController.dispose();
    _passwordController.dispose();
    for (var controller in _otpControllers) {
      controller.dispose();
    }
    for (var focusNode in _otpFocusNodes) {
      focusNode.dispose();
    }
    super.dispose();
  }
}
