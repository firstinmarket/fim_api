import 'package:flutter/material.dart';
import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';

import 'screens/mainScreen.dart';
import 'screens/auth/loginScreen.dart';
import 'screens/auth/signupScreen.dart';
import 'screens/profile/ProfileScreen.dart';
import 'screens/posts/PostScreen.dart';
import 'screens/posts/SavedPostsScreen.dart';
import 'screens/posts/UnreadScreen.dart';

import 'services/notification_service.dart';
import 'services/news_service.dart';

// Global navigator key so background/terminated notifications can navigate
final GlobalKey<NavigatorState> navigatorKey = GlobalKey<NavigatorState>();

Future<void> _firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  await Firebase.initializeApp();
  debugPrint("Background message received: ${message.messageId}");
}

void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  try {
    await Firebase.initializeApp();
    debugPrint('Firebase initialized successfully');

    // Firebase Messaging setup
    FirebaseMessaging messaging = FirebaseMessaging.instance;

    // Request permission (needed for iOS)
    NotificationSettings settings = await messaging.requestPermission(
      alert: true,
      badge: true,
      sound: true,
    );
    debugPrint('User granted permission: ${settings.authorizationStatus}');

    // Get FCM token for this device
    String? token = await messaging.getToken();
    debugPrint('FCM Token: $token');

    // Handle background messages
    FirebaseMessaging.onBackgroundMessage(_firebaseMessagingBackgroundHandler);

    // Handle foreground messages
    FirebaseMessaging.onMessage.listen((RemoteMessage message) {
      debugPrint('Foreground message: ${message.notification?.title}');
      NotificationService.showNotification(
        title: message.notification?.title ?? '',
        body: message.notification?.body ?? '',
      );
      // Show popup dialog when app is open
      if (navigatorKey.currentState != null) {
        showDialog(
          context: navigatorKey.currentState!.overlay!.context,
          builder: (context) {
            return AlertDialog(
              title: Text(message.notification?.title ?? 'Notification'),
              content: Text(message.notification?.body ?? ''),
              actions: [
                TextButton(
                  onPressed: () => Navigator.of(context).pop(),
                  child: const Text('OK'),
                ),
              ],
            );
          },
        );
      }
    });

    FirebaseMessaging.onMessageOpenedApp.listen((RemoteMessage message) {
      debugPrint('Notification opened: ${message.notification?.title}');
      final routeFromMessage = message.data['route'];
      if (routeFromMessage != null) {
        navigatorKey.currentState?.pushNamed(routeFromMessage);
      }
    });

    RemoteMessage? initialMessage =
        await FirebaseMessaging.instance.getInitialMessage();
    if (initialMessage != null) {
      final routeFromMessage = initialMessage.data['route'];
      if (routeFromMessage != null) {
        // Wait until UI is built
        Future.delayed(const Duration(seconds: 1), () {
          navigatorKey.currentState?.pushNamed(routeFromMessage);
        });
      }
    }

    // Local notification setup
    await NotificationService.initialize();
    debugPrint('Local notifications initialized');

    // Background news polling
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
      navigatorKey: navigatorKey,
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
