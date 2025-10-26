import 'package:flutter/material.dart';
import 'screens/auth/loginScreen.dart';
import 'screens/auth/signupScreen.dart';
import 'screens/profile/ProfileScreen.dart';
import 'screens/posts/PostScreen.dart';
import 'screens/posts/SavedPostsScreen.dart';
import 'screens/posts/UnreadScreen.dart';
import 'screens/mainScreen.dart';
import 'services/notification_service.dart';
import 'services/news_service.dart';
import 'package:onesignal_flutter/onesignal_flutter.dart';

final GlobalKey<NavigatorState> navigatorKey = GlobalKey<NavigatorState>();

void main() async {
  WidgetsFlutterBinding.ensureInitialized();

 
  OneSignal.Debug.setLogLevel(OSLogLevel.verbose); 
  OneSignal.initialize("48392a9a-9863-4cb1-96ba-3a7820029e4f");
  OneSignal.Notifications.requestPermission(
      false); 

  try {
    await NotificationService.initialize();
    debugPrint('Local notifications initialized');

    NewsService.startPolling();
    debugPrint('Background polling started');
  } catch (e) {
    debugPrint('Initialization error: $e');
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
