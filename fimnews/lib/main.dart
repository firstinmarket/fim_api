import 'package:flutter/material.dart';
import 'package:firebase_core/firebase_core.dart';
import 'screens/mainScreen.dart';
import 'screens/auth/loginScreen.dart';
import 'screens/auth/signupScreen.dart';
import 'screens/profile/ProfileScreen.dart';
import 'screens/posts/PostScreen.dart';
import 'screens/posts/SavedPostsScreen.dart';
import 'screens/posts/UnreadScreen.dart';

import 'services/notification_service.dart';
import 'services/news_service.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  try {
    await Firebase.initializeApp();
    debugPrint('Firebase initialized successfully');

    await NotificationService.initialize();
    debugPrint('Local notifications initialized');

    NewsService.startPolling();
    debugPrint('Background polling started');
  } catch (e) {
    debugPrint('Error initializing Firebase: $e');
  }

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
