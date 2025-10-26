import 'dart:async';
import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:news_app/services/notification_service.dart';
import 'package:shared_preferences/shared_preferences.dart';

class NewsService {
  // Use base URL from ApiService for consistency
  static String get baseUrl => 'https://www.firstinmarket.com/app/api/';
  static String get notificationsUrl =>
      '${baseUrl}routes/notifications/notifications.php';

  static final StreamController<List<NotificationItem>>
      _notificationController =
      StreamController<List<NotificationItem>>.broadcast();

  static Stream<List<NotificationItem>> get notificationStream =>
      _notificationController.stream;

  static Timer? _pollingTimer;

  /// Fetch latest notifications (last 24 hours)
  static Future<List<NotificationItem>> fetchNotifications(
      {int? userId, int hours = 24}) async {
    try {
      String url = '$notificationsUrl?hours=$hours&limit=50';
      if (userId != null) {
        url += '&user_id=$userId';
      }

      final response = await http.get(Uri.parse(url));

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true) {
          final List notifications = data['data'];
          final items =
              notifications.map((n) => NotificationItem.fromJson(n)).toList();

          // Update stream
          _notificationController.add(items);

          // Save last fetch time
          final prefs = await SharedPreferences.getInstance();
          await prefs.setString(
              'last_notification_fetch', DateTime.now().toIso8601String());

          return items;
        }
      }
      return [];
    } catch (e) {
      print("Error fetching notifications: $e");
      return [];
    }
  }

  /// Check for new posts and notifications
  static Future<void> checkForNewPosts() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final userId = prefs.getInt('user_id');
      final lastNotificationId = prefs.getInt('last_notification_id') ?? 0;

      // Fetch latest notifications
      final notifications = await fetchNotifications(userId: userId, hours: 24);

      if (notifications.isNotEmpty) {
        final latestNotification = notifications.first;

        // Check if this is a new notification
        if (latestNotification.id > lastNotificationId) {
          // Show local notification
          await NotificationService.showNotification(
            title: "📰 ${latestNotification.post.title}",
            body: "Tap to read more",
          );

          // Save latest notification ID
          await prefs.setInt('last_notification_id', latestNotification.id);

          print("New notification detected: ${latestNotification.post.title}");
        }
      }
    } catch (e) {
      print("Error checking new posts: $e");
    }
  }

  /// Get unread notifications count
  static Future<int> getUnreadCount() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final userIdString = prefs.getString('user_id');
      final userId = userIdString != null ? int.tryParse(userIdString) : null;
      final lastSeenId = prefs.getInt('last_seen_notification_id') ?? 0;

      final notifications = await fetchNotifications(userId: userId, hours: 24);
      return notifications.where((n) => n.id > lastSeenId).length;
    } catch (e) {
      print("Error getting unread count: $e");
      return 0;
    }
  }

  /// Mark notifications as seen
  static Future<void> markNotificationsAsSeen(int lastNotificationId) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setInt('last_seen_notification_id', lastNotificationId);
  }

  /// Start polling for new notifications (every 5 minutes)
  static void startPolling() {
    stopPolling(); // Clear any existing timer

    // Initial check
    checkForNewPosts();

    // Poll every 5 minutes
    _pollingTimer = Timer.periodic(const Duration(minutes: 5), (timer) async {
      await checkForNewPosts();
    });

    print("Started notification polling (every 5 minutes)");
  }

  /// Stop polling
  static void stopPolling() {
    _pollingTimer?.cancel();
    _pollingTimer = null;
    print("Stopped notification polling");
  }

  /// Cleanup notifications older than 24 hours from local storage
  static Future<void> cleanupOldNotifications() async {
    final prefs = await SharedPreferences.getInstance();
    final lastFetch = prefs.getString('last_notification_fetch');

    if (lastFetch != null) {
      final lastFetchTime = DateTime.parse(lastFetch);
      final now = DateTime.now();
      final difference = now.difference(lastFetchTime);

      // If last fetch was more than 24 hours ago, reset
      if (difference.inHours >= 24) {
        await prefs.remove('last_notification_id');
        await prefs.remove('last_seen_notification_id');
        print("Cleaned up old notifications");
      }
    }
  }

  /// Dispose
  static void dispose() {
    stopPolling();
    _notificationController.close();
  }
}

/// Notification Item Model
class NotificationItem {
  final int id;
  final int postId;
  final int sentCount;
  final int failedCount;
  final DateTime sentAt;
  final String sentBy;
  final String notificationType;
  final PostSummary post;

  NotificationItem({
    required this.id,
    required this.postId,
    required this.sentCount,
    required this.failedCount,
    required this.sentAt,
    required this.sentBy,
    required this.notificationType,
    required this.post,
  });

  factory NotificationItem.fromJson(Map<String, dynamic> json) {
    return NotificationItem(
      id: json['id'],
      postId: json['post_id'],
      sentCount: json['sent_count'],
      failedCount: json['failed_count'],
      sentAt: DateTime.parse(json['sent_at']),
      sentBy: json['sent_by'],
      notificationType: json['notification_type'],
      post: PostSummary.fromJson(json['post']),
    );
  }

  bool get isNew {
    final now = DateTime.now();
    final difference = now.difference(sentAt);
    return difference.inHours < 1; // New if less than 1 hour old
  }

  bool get isExpired {
    final now = DateTime.now();
    final difference = now.difference(sentAt);
    return difference.inHours >= 24; // Expired after 24 hours
  }

  String get timeAgo {
    final now = DateTime.now();
    final difference = now.difference(sentAt);

    if (difference.inMinutes < 1) {
      return 'Just now';
    } else if (difference.inMinutes < 60) {
      return '${difference.inMinutes}m ago';
    } else if (difference.inHours < 24) {
      return '${difference.inHours}h ago';
    } else {
      return '${difference.inDays}d ago';
    }
  }
}

/// Post Summary Model
class PostSummary {
  final String title;
  final String? image;
  final String status;
  final String? categories;
  final String? content;

  PostSummary({
    required this.title,
    this.image,
    required this.status,
    this.categories,
    this.content,
  });

  factory PostSummary.fromJson(Map<String, dynamic> json) {
    return PostSummary(
      title: json['title'],
      image: json['image'],
      status: json['status'],
      categories: json['categories'],
      content: json['content'],
    );
  }
}
