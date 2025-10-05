import 'package:flutter/material.dart';
import 'screens/mainScreen.dart';
import 'screens/auth/loginScreen.dart';
import 'screens/auth/signupScreen.dart';
import 'screens/profile/ProfileScreen.dart';
import 'screens/posts/PostScreen.dart';
import 'screens/posts/SavedPostsScreen.dart';
import 'screens/posts/UnreadScreen.dart';

void main() {
  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      debugShowCheckedModeBanner: false,
      initialRoute: '/',
      routes: {
        '/': (context) => const MainScreen(),
        '/login': (context) => const LoginScreen(),
        '/signup': (context) => const SignupScreen(),
        '/profile': (context) => const ProfileScreen(),
        '/posts': (context) => const PostScreen(),
        '/saved': (context) => const SavedPostsScreen(),
        '/unread': (context) => const UnreadScreen(),
      },
    );
  }
}
