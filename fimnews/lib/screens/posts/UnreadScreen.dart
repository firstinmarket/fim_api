import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:share_plus/share_plus.dart';
import '../../services/api_service.dart';
import '../components/navigation.dart';

class UnreadScreen extends StatefulWidget {
  const UnreadScreen({super.key});

  @override
  _UnreadScreenState createState() => _UnreadScreenState();
}

class _UnreadScreenState extends State<UnreadScreen> {
  // State variables
  Set<String> likedPostIds = <String>{};
  Set<String> savedPostIds = <String>{};
  Set<String> readPostIds = <String>{}; // Track read posts
  List<String> categories = [];
  List<Map<String, dynamic>> unreadPosts = [];
  int currentIndex = 0;
  bool loading = false;
  bool refreshing = false;
  String selectedCategory = '';
  final PageController _pageController = PageController();
  bool showActions = true;

  @override
  void initState() {
    super.initState();
    fetchUnreadPosts(showLoading: true);
    loadReadPostIds();

    _pageController.addListener(() {
      setState(() {
        currentIndex = _pageController.page?.round() ?? 0;
      });
      final filteredPosts = _getFilteredPosts();
      if (filteredPosts.isNotEmpty && currentIndex < filteredPosts.length) {
        final postId = filteredPosts[currentIndex]['id']?.toString() ?? '';
        if (postId.isNotEmpty) {
          _markPostAsRead(postId);
          _updatePostCount(postId, 'views_count');
        }
      }
    });
  }

  // Load read post IDs from SharedPreferences
  Future<void> loadReadPostIds() async {
    final prefs = await SharedPreferences.getInstance();
    final readIds = prefs.getStringList('read_post_ids') ?? [];
    setState(() {
      readPostIds = readIds.toSet();
    });
    debugPrint('Loaded read post IDs: $readPostIds');
  }

  // Mark a post as read and save to SharedPreferences
  Future<void> _markPostAsRead(String postId) async {
    if (!readPostIds.contains(postId)) {
      setState(() {
        readPostIds.add(postId);
      });

      final prefs = await SharedPreferences.getInstance();
      await prefs.setStringList('read_post_ids', readPostIds.toList());
      debugPrint(
          'Marked post $postId as read. Total read: ${readPostIds.length}');

      // Remove from unread posts after a short delay
      Future.delayed(const Duration(seconds: 2), () {
        if (mounted) {
          setState(() {
            unreadPosts.removeWhere((post) => post['id']?.toString() == postId);
          });

          // If no more unread posts, navigate back
          if (unreadPosts.isEmpty) {
            Navigator.of(context).pop();
          }
        }
      });
    }
  }

  // Toggle like functionality
  Future<void> toggleLike(String postId, String userId, bool isLiked) async {
    try {
      final payload = {
        'post_id': postId,
        'user_id': userId,
        'field': isLiked ? 'unlike' : 'like',
      };
      debugPrint(
          'Toggling like for post $postId: ${isLiked ? "unlike" : "like"}');
      final response = await ApiService.apiPut('posts/posts.php', payload);
      debugPrint('Like toggle response: $response');

      setState(() {
        if (isLiked) {
          likedPostIds.remove(postId);
          debugPrint('Removed post $postId from likedPostIds');
        } else {
          likedPostIds.add(postId);
          debugPrint('Added post $postId to likedPostIds');
        }
        debugPrint('Current likedPostIds: $likedPostIds');

        // Also update the post data
        final postIndex =
            unreadPosts.indexWhere((p) => p['id']?.toString() == postId);
        if (postIndex != -1) {
          final post = unreadPosts[postIndex];
          final currentLikes =
              int.tryParse(post['likes_count']?.toString() ?? '0') ?? 0;
          post['likes_count'] = (currentLikes + (isLiked ? -1 : 1)).toString();
          post['is_liked'] = isLiked ? 0 : 1;
          debugPrint(
              'Updated post $postId is_liked to: ${post['is_liked']} (was liked: $isLiked)');
        }
      });
    } catch (e) {
      debugPrint('Error toggling like: $e');
    }
  }

  // Toggle save functionality
  Future<void> toggleSave(String postId, String userId, bool isSaved) async {
    try {
      final payload = {
        'post_id': postId,
        'user_id': userId,
        'field': isSaved ? 'unsave' : 'save',
      };
      debugPrint(
          'Toggling save for post $postId: ${isSaved ? "unsave" : "save"}');
      final response = await ApiService.apiPut('posts/posts.php', payload);
      debugPrint('Save toggle response: $response');

      setState(() {
        if (isSaved) {
          savedPostIds.remove(postId);
          debugPrint('Removed post $postId from savedPostIds');
        } else {
          savedPostIds.add(postId);
          debugPrint('Added post $postId to savedPostIds');
        }
        debugPrint('Current savedPostIds: $savedPostIds');

        // Also update the post data
        final postIndex =
            unreadPosts.indexWhere((p) => p['id']?.toString() == postId);
        if (postIndex != -1) {
          final post = unreadPosts[postIndex];
          final currentSaves =
              int.tryParse(post['saves_count']?.toString() ?? '0') ?? 0;
          post['saves_count'] = (currentSaves + (isSaved ? -1 : 1)).toString();
          post['is_saved'] = isSaved ? 0 : 1;
          debugPrint(
              'Updated post $postId is_saved to: ${post['is_saved']} (was saved: $isSaved)');
        }
      });
    } catch (e) {
      debugPrint('Error toggling save: $e');
    }
  }

  Future<void> _updatePostCount(String postId, String field) async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final userId = prefs.getString('user_id');
      if (userId == null) return;
      final payload = {
        'post_id': postId,
        'field': field,
        'user_id': userId,
      };
      await ApiService.apiPut('posts/posts.php', payload);

      final postIndex =
          unreadPosts.indexWhere((p) => p['id']?.toString() == postId);
      if (postIndex != -1) {
        final post = unreadPosts[postIndex];
        final currentCount = int.tryParse(post[field]?.toString() ?? '0') ?? 0;
        post[field] = (currentCount + 1).toString();
      }
      if (mounted) setState(() {});
    } catch (e) {
      debugPrint('Error updating post $field: $e');
    }
  }

  Future<void> fetchUnreadPosts({bool showLoading = true}) async {
    if (showLoading) {
      setState(() => loading = true);
    }
    try {
      final prefs = await SharedPreferences.getInstance();
      final userId = prefs.getString('user_id');
      if (userId == null) {
        if (mounted && showLoading) setState(() => loading = false);
        return;
      }

      final postsResponse = await ApiService.apiGet(
        'posts/posts.php',
        query: {'user_id': userId},
      );

      debugPrint('Unread Posts API Response: $postsResponse');

      List<Map<String, dynamic>> allPosts = [];
      if (postsResponse is List) {
        allPosts =
            postsResponse.map((e) => Map<String, dynamic>.from(e)).toList();
      } else if (postsResponse is Map<String, dynamic> &&
          postsResponse['body'] is List) {
        allPosts = (postsResponse['body'] as List)
            .map((e) => Map<String, dynamic>.from(e))
            .toList();
      }

      // Filter out read posts to get only unread posts
      await loadReadPostIds(); // Ensure we have the latest read post IDs
      unreadPosts = allPosts.where((post) {
        final postId = post['id']?.toString() ?? '';
        return postId.isNotEmpty && !readPostIds.contains(postId);
      }).toList();

      // Clear existing sets before rebuilding
      likedPostIds.clear();
      savedPostIds.clear();

      // Build like/save status for unread posts
      for (final post in unreadPosts) {
        final postId = post['id']?.toString() ?? '';
        if (postId.isNotEmpty) {
          // Check for like/save status using individual API calls (since they work)
          try {
            final likeResponse = await ApiService.apiGet(
              'posts/posts.php',
              query: {'post_id': postId, 'user_id': userId, 'status': 'like'},
            );
            if (likeResponse is Map<String, dynamic> &&
                likeResponse['is_liked'] == true) {
              likedPostIds.add(postId);
              debugPrint('✓ Post $postId is liked');
            }

            final saveResponse = await ApiService.apiGet(
              'posts/posts.php',
              query: {'post_id': postId, 'user_id': userId, 'status': 'save'},
            );
            if (saveResponse is Map<String, dynamic> &&
                saveResponse['is_saved'] == true) {
              savedPostIds.add(postId);
              debugPrint('✓ Post $postId is saved');
            }
          } catch (e) {
            debugPrint('Error fetching like/save status for post $postId: $e');
          }
        }
      }

      final Set<String> catSet =
          unreadPosts.map((p) => p['category_name']?.toString() ?? '').toSet();
      categories = catSet.where((c) => c.isNotEmpty).toList();

      categories.insert(0, 'All');

      if (categories.isNotEmpty && selectedCategory.isEmpty) {
        selectedCategory = 'All';
      }

      debugPrint('Final likedPostIds for unread: $likedPostIds');
      debugPrint('Final savedPostIds for unread: $savedPostIds');
    } catch (e) {
      debugPrint("Error fetching unread posts: $e");
    }
    if (mounted) {
      setState(() => loading = false);
    }
  }

  List<Map<String, dynamic>> _getFilteredPosts() {
    if (selectedCategory.isEmpty || selectedCategory == 'All')
      return unreadPosts;
    final filtered = unreadPosts
        .where((p) => p['category_name'] == selectedCategory)
        .toList();
    return filtered;
  }

  Widget _buildPostCard(Map<String, dynamic> currentPost) {
    final postId = currentPost['id']?.toString() ?? '';
    if (postId.isEmpty) return const SizedBox.shrink();

    final isLiked = likedPostIds.contains(postId);
    final isSaved = savedPostIds.contains(postId);

    final fullContent = currentPost['content']?.toString() ?? '';
    final contentWords = fullContent.split(' ');

    // Detect Tamil content using Unicode range for Tamil characters
    final isTamilContent = RegExp(r'[\u0B80-\u0BFF]').hasMatch(fullContent);
    // Check for long content using both word count and character length
    // This handles Tamil content better as Tamil words can be longer
    final isLongContent = contentWords.length > 50 || fullContent.length > 350;
    String title = currentPost['title']?.toString() ?? '';
    if (title.length > 47) title = '${title.substring(0, 47)}...';

    String imageUrl =
        currentPost['image']?.toString().replaceAll(r'\/', '/') ?? '';
    if (imageUrl.isNotEmpty && !imageUrl.startsWith('http')) {
      imageUrl = 'http://localhost/fim_api/api/uploads/' + imageUrl;
    }

    final updatedAt = currentPost['created_at']?.toString() ?? '';
    final viewsCount = currentPost['views_count']?.toString() ?? '0';
    final likesCount = currentPost['likes_count']?.toString() ?? '0';
    String savesCount = currentPost['saves_count']?.toString() ?? '0';
    if (savesCount.isEmpty) {
      savesCount = currentPost['saved_counts']?.toString() ?? '0';
    }

    String timeAgo = '';
    if (updatedAt.isNotEmpty) {
      final now = DateTime.now();
      DateTime? updated;
      try {
        updated = DateTime.parse(updatedAt);
      } catch (_) {}
      if (updated != null) {
        final diff = now.difference(updated);
        if (diff.inDays > 0) {
          timeAgo = '${diff.inDays} day${diff.inDays > 1 ? 's' : ''} ago';
        } else if (diff.inHours > 0) {
          timeAgo = '${diff.inHours} hr${diff.inHours > 1 ? 's' : ''} ago';
        } else if (diff.inMinutes > 0) {
          timeAgo = '${diff.inMinutes} min${diff.inMinutes > 1 ? 's' : ''} ago';
        } else {
          timeAgo = 'Just now';
        }
      }
    }

    return AnimatedContainer(
      duration: const Duration(milliseconds: 300),
      margin: const EdgeInsets.symmetric(vertical: 10, horizontal: 16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const SizedBox(height: 8),
          if (imageUrl.isNotEmpty)
            Stack(
              children: [
                ClipRRect(
                  borderRadius: BorderRadius.circular(12),
                  child: Image.network(
                    imageUrl,
                    height: 180,
                    width: double.infinity,
                    fit: BoxFit.cover,
                    errorBuilder: (ctx, error, stack) =>
                        Container(height: 180, color: Colors.grey[800]),
                  ),
                ),
                Positioned(
                  left: 10,
                  top: 10,
                  child: Container(
                    padding:
                        const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                    decoration: BoxDecoration(
                      color: Colors.black.withOpacity(0.6),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Text(
                      timeAgo,
                      style: const TextStyle(
                          color: Colors.white,
                          fontSize: 13,
                          fontWeight: FontWeight.w500),
                    ),
                  ),
                ),
                Positioned(
                  right: 10,
                  top: 10,
                  child: Container(
                    padding:
                        const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                    decoration: BoxDecoration(
                      color: Colors.black.withOpacity(0.6),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Row(
                      children: [
                        const Icon(Icons.remove_red_eye,
                            color: Colors.white, size: 15),
                        const SizedBox(width: 4),
                        Text(
                          viewsCount,
                          style: const TextStyle(
                              color: Colors.white,
                              fontSize: 13,
                              fontWeight: FontWeight.w500),
                        ),
                      ],
                    ),
                  ),
                ),
                // Share icon bottom left
                Positioned(
                  left: 10,
                  bottom: 10,
                  child: GestureDetector(
                    onTap: _shareContent,
                    child: Container(
                      padding: const EdgeInsets.symmetric(
                          horizontal: 8, vertical: 4),
                      decoration: BoxDecoration(
                        color: Colors.black.withOpacity(0.6),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: const Icon(Icons.share,
                          color: Colors.white, size: 15),
                    ),
                  ),
                ),
                // Likes and Saves bottom right
                Positioned(
                  right: 10,
                  bottom: 10,
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      // Likes
                      GestureDetector(
                        onTap: () async {
                          final prefs = await SharedPreferences.getInstance();
                          final userId = prefs.getString('user_id') ?? '';
                          if (userId.isNotEmpty && postId.isNotEmpty) {
                            await toggleLike(postId, userId, isLiked);
                          }
                        },
                        child: Container(
                          padding: const EdgeInsets.symmetric(
                              horizontal: 8, vertical: 4),
                          decoration: BoxDecoration(
                            color: Colors.black.withOpacity(0.6),
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: Row(
                            children: [
                              Icon(
                                isLiked
                                    ? Icons.favorite
                                    : Icons.favorite_border,
                                color: Colors.redAccent,
                                size: 15,
                              ),
                              const SizedBox(width: 4),
                              Text(
                                likesCount,
                                style: const TextStyle(
                                    color: Colors.white,
                                    fontSize: 13,
                                    fontWeight: FontWeight.w500),
                              ),
                            ],
                          ),
                        ),
                      ),
                      const SizedBox(width: 8),
                      // Saves
                      GestureDetector(
                        onTap: () async {
                          final prefs = await SharedPreferences.getInstance();
                          final userId = prefs.getString('user_id') ?? '';
                          if (userId.isNotEmpty && postId.isNotEmpty) {
                            await toggleSave(postId, userId, isSaved);
                          }
                        },
                        child: Container(
                          padding: const EdgeInsets.symmetric(
                              horizontal: 8, vertical: 4),
                          decoration: BoxDecoration(
                            color: Colors.black.withOpacity(0.6),
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: Row(
                            children: [
                              Icon(
                                isSaved
                                    ? Icons.bookmark
                                    : Icons.bookmark_border,
                                color: const Color(0xFF5F8DFF),
                                size: 15,
                              ),
                              const SizedBox(width: 4),
                              Text(
                                savesCount,
                                style: const TextStyle(
                                    color: Colors.white,
                                    fontSize: 13,
                                    fontWeight: FontWeight.w500),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          const SizedBox(height: 12),
          Text(
            title,
            style: const TextStyle(
              fontSize: 17,
              fontWeight: FontWeight.w700,
              color: Color(0xFF5F8DFF),
            ),
            textAlign: TextAlign.justify,
            maxLines: 2,
            overflow: TextOverflow.ellipsis,
          ),
          const SizedBox(height: 10),
          Container(
            height: isLongContent
                ? MediaQuery.of(context).size.height * 0.3
                : null, // Responsive height for long content
            padding: const EdgeInsets.all(6),
            decoration: BoxDecoration(
              color: const Color(0xFF232A3B).withOpacity(0.5),
              borderRadius: BorderRadius.circular(8),
            ),
            child: isLongContent
                ? Scrollbar(
                    thumbVisibility: true,
                    thickness: 4,
                    radius: const Radius.circular(4),
                    child: SingleChildScrollView(
                      physics: const BouncingScrollPhysics(),
                      padding: const EdgeInsets.only(right: 8),
                      child: Text(
                        fullContent,
                        style: TextStyle(
                          fontSize: isTamilContent ? 12.0 : 14.2,
                          color: Color(0xFFCCCCCC),
                          height: 1.7,
                        ),
                        textAlign: TextAlign.justify,
                      ),
                    ),
                  )
                : Text(
                    fullContent,
                    style: TextStyle(
                      fontSize: isTamilContent ? 12.0 : 14.2,
                      color: Color(0xFFCCCCCC),
                      height: 1.7,
                    ),
                    textAlign: TextAlign.justify,
                  ),
          ),
        ],
      ),
    );
  }

  void _selectCategory(String category) {
    setState(() {
      selectedCategory = category;
      currentIndex = 0;
      _pageController.jumpToPage(0);
    });
  }

  void _onRefresh() async {
    setState(() => refreshing = true);
    await fetchUnreadPosts(showLoading: true);
    setState(() => refreshing = false);
  }

  void _shareContent() {
    final filteredPosts = _getFilteredPosts();
    if (filteredPosts.isEmpty || currentIndex >= filteredPosts.length) return;
    final post = filteredPosts[currentIndex];
    final postId = post['id']?.toString() ?? '';
    final title = post['title'] ?? '';
    final content = post['content'] ?? '';
    final imageUrl = post['image'] ?? '';
    String shareText = title;
    if (content.isNotEmpty) shareText += '\n\n$content';
    if (imageUrl.isNotEmpty) shareText += '\n\n$imageUrl';
    Share.share(shareText, subject: title);
    if (postId.isNotEmpty) {
      _updatePostCount(postId, 'shares_count');
    }
  }

  @override
  Widget build(BuildContext context) {
    final filteredPosts = _getFilteredPosts();

    return Scaffold(
      backgroundColor: const Color(0xFF0F0F0F),
      body: SafeArea(
        child: Column(
          children: [
            // Top Bar
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Row(
                    children: [
                      const SizedBox(width: 16),
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text(
                            'Unread Posts',
                            style: TextStyle(
                              fontSize: 20,
                              fontWeight: FontWeight.w700,
                              color: Colors.white,
                            ),
                          ),
                          Text(
                            '${unreadPosts.length} new posts',
                            style: TextStyle(
                              fontSize: 12,
                              color: Color(0xFF5F8DFF),
                              fontWeight: FontWeight.w500,
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                  GestureDetector(
                    onTap: _onRefresh,
                    child: Container(
                      padding: const EdgeInsets.all(8),
                      decoration: BoxDecoration(
                        color: const Color(0xFF232A3B),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: refreshing
                          ? const SizedBox(
                              width: 20,
                              height: 20,
                              child: CircularProgressIndicator(
                                strokeWidth: 2,
                                valueColor: AlwaysStoppedAnimation<Color>(
                                    Color(0xFF5F8DFF)),
                              ),
                            )
                          : const Icon(
                              Icons.refresh,
                              color: Color(0xFF5F8DFF),
                              size: 20,
                            ),
                    ),
                  ),
                ],
              ),
            ),

            // Categories
            if (categories.isNotEmpty)
              Container(
                height: 35,
                padding: const EdgeInsets.symmetric(horizontal: 16),
                child: ListView.separated(
                  scrollDirection: Axis.horizontal,
                  itemCount: categories.length,
                  separatorBuilder: (_, __) => const SizedBox(width: 8),
                  itemBuilder: (context, index) {
                    final category = categories[index];
                    final isSelected = selectedCategory == category;
                    return GestureDetector(
                      onTap: () => _selectCategory(category),
                      child: AnimatedContainer(
                        duration: const Duration(milliseconds: 200),
                        padding: const EdgeInsets.symmetric(
                            horizontal: 16, vertical: 8),
                        decoration: BoxDecoration(
                          color: isSelected
                              ? const Color(0xFF5F8DFF)
                              : const Color(0xFF232A3B),
                          borderRadius: BorderRadius.circular(20),
                        ),
                        child: Text(
                          category,
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
                  },
                ),
              ),

            // Posts
            if (loading)
              const Expanded(child: Center(child: CircularProgressIndicator()))
            else if (filteredPosts.isEmpty)
              Expanded(
                child: Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(
                        Icons.check_circle_outline,
                        size: 64,
                        color: Colors.green,
                      ),
                      const SizedBox(height: 16),
                      const Text(
                        'All caught up!',
                        style: TextStyle(
                            color: Colors.white,
                            fontSize: 24,
                            fontWeight: FontWeight.bold),
                      ),
                      const SizedBox(height: 8),
                      const Text(
                        'No unread posts available.',
                        style: TextStyle(color: Colors.white70, fontSize: 16),
                      ),
                    ],
                  ),
                ),
              )
            else
              Expanded(
                child: PageView.builder(
                  controller: _pageController,
                  scrollDirection: Axis.vertical,
                  itemCount: filteredPosts.length,
                  itemBuilder: (context, index) {
                    return _buildPostCard(filteredPosts[index]);
                  },
                ),
              ),
          ],
        ),
      ),
      bottomNavigationBar: const BottomNavigation(),
    );
  }
}
