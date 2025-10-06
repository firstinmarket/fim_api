import 'package:flutter/material.dart';
import 'package:vibration/vibration.dart';
import '../services/session_manager.dart';

class MainScreen extends StatefulWidget {
  const MainScreen({super.key});

  @override
  State<MainScreen> createState() => _MainScreenState();
}

class _MainScreenState extends State<MainScreen> {
  @override
  void initState() {
    super.initState();
    _checkLoginStatus();
  }

  Future<void> _checkLoginStatus() async {
    try {
      // Check if user has valid session
      final isValidSession = await SessionManager.validateAndRefreshSession();

      if (isValidSession) {
        final userId = await SessionManager.getCurrentUserId();
        debugPrint('User already logged in with ID: $userId');

        // Add a small delay to show splash screen briefly
        await Future.delayed(const Duration(seconds: 1));

        // Navigate directly to posts section when app reopens
        if (mounted) {
          Navigator.pushReplacementNamed(context, '/posts');
        }
      } else {
        debugPrint('No valid session found, showing main screen');
      }
    } catch (e) {
      debugPrint('Error checking login status: $e');
    }
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
            color: Colors.black.withOpacity(0.2),
            child: Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  // Title
                  const Text(
                    'FIM NEWS',
                    style: TextStyle(
                      color: Colors.white,
                      fontSize: 44,
                      fontWeight: FontWeight.bold,
                      letterSpacing: 2,
                    ),
                  ),
                  // Subtitle
                  const Padding(
                    padding: EdgeInsets.only(top: 40, bottom: 80),
                    child: Text(
                      'Read Less, Know More.',
                      style: TextStyle(
                        color: Colors.white,
                        fontSize: 25,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                  // Button
                  ElevatedButton(
                    onPressed: () async {
                      // Trigger vibration
                      if (await Vibration.hasVibrator() == true) {
                        Vibration.vibrate(duration: 100); // Vibrate for 100ms
                      }
                      // Navigate to login screen
                      Navigator.pushNamed(context, '/login');
                    },
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.black.withOpacity(0.4),
                      padding: const EdgeInsets.symmetric(
                        vertical: 12,
                        horizontal: 32,
                      ),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(25),
                        side: const BorderSide(
                          color: Colors.white,
                          width: 2,
                        ),
                      ),
                      elevation: 4,
                      shadowColor: Colors.black.withOpacity(0.3),
                    ),
                    child: const Text(
                      'Get Started',
                      style: TextStyle(
                        color: Colors.white,
                        fontSize: 18,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}
