import 'package:flutter/material.dart';
import 'package:flutter/rendering.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:share_plus/share_plus.dart';
import '../../services/api_service.dart';
import '../components/navigation.dart';
import 'UnreadScreen.dart';

class PostScreen extends StatefulWidget {
  const PostScreen({super.key});

  @override
  _PostScreenState createState() => _PostScreenState();
}

class _PostScreenState extends State<PostScreen> {
  // State variables
  Set<String> likedPostIds = <String>{};
  Set<String> savedPostIds = <String>{};
  Set<String> readPostIds = <String>{};
  Set<String> viewedPostIds = <String>{}; // Track viewed posts for view count
  List<String> categories = [];
  List<Map<String, dynamic>> posts = [];
  int currentIndex = 0;
  bool loading = false;
  bool refreshing = false;
  bool commentsVisible = false;
  List<Map<String, dynamic>> comments = [];
  String newComment = '';
  String selectedCategory = '';
  final PageController _pageController = PageController();
  bool showActions = true;
  int unreadCount = 0;
  final ScrollController _scrollController = ScrollController();

  @override
  void initState() {
    super.initState();
    loadReadPostIds();
    loadViewedPostIds();
    fetchPostsAndCategories(showLoading: true);

    _pageController.addListener(() {
      setState(() {
        currentIndex = _pageController.page?.round() ?? 0;
      });
      final filteredPosts = _getFilteredPosts();
      if (filteredPosts.isNotEmpty && currentIndex < filteredPosts.length) {
        final postId = filteredPosts[currentIndex]['id']?.toString() ?? '';
        if (postId.isNotEmpty) {
          _markPostAsRead(postId);
          _markPostAsViewed(postId);
        }
      }
    });

    _scrollController.addListener(() {
      // Hide/show actions based on scroll direction with animation
      if (_scrollController.position.userScrollDirection ==
          ScrollDirection.reverse) {
        if (showActions) {
          setState(() => showActions = false);
        }
      } else if (_scrollController.position.userScrollDirection ==
          ScrollDirection.forward) {
        if (!showActions) {
          setState(() => showActions = true);
        }
      }
    });
  }

  @override
  void dispose() {
    _pageController.dispose();
    _scrollController.dispose();
    super.dispose();
  }

  // Load read post IDs from SharedPreferences
  Future<void> loadReadPostIds() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final readIds = prefs.getStringList('read_post_ids') ?? [];
      setState(() {
        readPostIds = readIds.toSet();
      });
    } catch (e) {
      debugPrint('Error loading read post IDs: $e');
      setState(() {
        readPostIds = <String>{};
      });
    }
    debugPrint('Loaded read post IDs: $readPostIds');
  }

  // Load viewed post IDs from SharedPreferences
  Future<void> loadViewedPostIds() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final viewedIds = prefs.getStringList('viewed_post_ids') ?? [];
      setState(() {
        viewedPostIds = viewedIds.toSet();
      });
    } catch (e) {
      debugPrint('Error loading viewed post IDs: $e');
      setState(() {
        viewedPostIds = <String>{};
      });
    }
    debugPrint('Loaded viewed post IDs: $viewedPostIds');
  }

  // Mark a post as read and save to SharedPreferences
  Future<void> _markPostAsRead(String postId) async {
    if (postId.isEmpty) return;
    if (!readPostIds.contains(postId)) {
      setState(() {
        readPostIds.add(postId);
      });

      final prefs = await SharedPreferences.getInstance();
      await prefs.setStringList('read_post_ids', readPostIds.toList());
      debugPrint(
          'Marked post $postId as read. Total read: ${readPostIds.length}');

      // Update unread count
      _calculateUnreadCount();
    }
  }

  // Mark a post as viewed and increment view count only once per device
  Future<void> _markPostAsViewed(String postId) async {
    if (postId.isEmpty) return;
    if (!viewedPostIds.contains(postId)) {
      setState(() {
        viewedPostIds.add(postId);
      });

      final prefs = await SharedPreferences.getInstance();
      await prefs.setStringList('viewed_post_ids', viewedPostIds.toList());
      debugPrint(
          'Marked post $postId as viewed. Total viewed: ${viewedPostIds.length}');

      // Only increment view count if this is the first time viewing this post
      await _updatePostCount(postId, 'views_count');
    }
  }

  // Calculate unread count
  void _calculateUnreadCount() {
    final totalPosts = posts.length;
    final readCount = readPostIds.length;
    setState(() {
      unreadCount = totalPosts > readCount ? totalPosts - readCount : 0;
    });
    debugPrint(
        'Unread count: $unreadCount (Total: $totalPosts, Read: $readCount)');
  }

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

      // Update likedPostIds set and trigger UI refresh
      setState(() {
        if (isLiked) {
          likedPostIds.remove(postId);
          debugPrint('Removed post $postId from likedPostIds');
        } else {
          likedPostIds.add(postId);
          debugPrint('Added post $postId to likedPostIds');
        }
        debugPrint('Current likedPostIds: $likedPostIds');

        // Also update the post data with new like status and count
        final postIndex =
            posts.indexWhere((p) => p['id']?.toString() == postId);
        if (postIndex != -1) {
          final post = posts[postIndex];
          final currentLikes =
              int.tryParse(post['likes_count']?.toString() ?? '0') ?? 0;
          post['likes_count'] = (currentLikes + (isLiked ? -1 : 1)).toString();
          post['is_liked'] = isLiked ? 0 : 1; // Update like status in post data
          debugPrint('Updated post $postId is_liked to: ${post['is_liked']}');
        }
      });
    } catch (e) {
      debugPrint('Error toggling like: $e');
    }
  }

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

      if (response['success'] == true) {
        // Update local state
        if (isSaved) {
          savedPostIds.remove(postId);
          debugPrint('Removed post $postId from savedPostIds');
        } else {
          savedPostIds.add(postId);
          debugPrint('Added post $postId to savedPostIds');
        }

        // Update count in local posts list
        final postIndex =
            posts.indexWhere((p) => p['id']?.toString() == postId);
        if (postIndex != -1) {
          final post = posts[postIndex];
          final currentSaves =
              int.tryParse(post['saves_count']?.toString() ?? '0') ?? 0;
          post['saves_count'] = (currentSaves + (isSaved ? -1 : 1)).toString();
          post['is_saved'] = isSaved ? 0 : 1; // Update save status in post data
          debugPrint('Updated post $postId is_saved to: ${post['is_saved']}');
        }

        if (mounted) {
          setState(() {});
        }
        debugPrint('Save toggled successfully for post $postId');
      } else {
        debugPrint(
            'Save toggle failed: ${response['message'] ?? 'Unknown error'}');
      }
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

      final postIndex = posts.indexWhere((p) => p['id']?.toString() == postId);
      if (postIndex != -1) {
        final post = posts[postIndex];
        final currentCount = int.tryParse(post[field]?.toString() ?? '0') ?? 0;
        post[field] = (currentCount + 1).toString();
      }
      if (mounted) setState(() {});
    } catch (e) {
      debugPrint('Error updating post $field: $e');
    }
  }

  Future<void> fetchPostsAndCategories({bool showLoading = true}) async {
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

      if (postsResponse is List) {
        posts = postsResponse.map((e) => Map<String, dynamic>.from(e)).toList();
      } else if (postsResponse is Map<String, dynamic> &&
          postsResponse['body'] is List) {
        posts = (postsResponse['body'] as List)
            .map((e) => Map<String, dynamic>.from(e))
            .toList();
      }

      // Clear existing sets before rebuilding (ensure they exist)
      likedPostIds = <String>{};
      savedPostIds = <String>{};

      // Build like/save status from posts data (no separate API calls needed)
      for (final post in posts) {
        final postId = post['id']?.toString() ?? '';
        if (postId.isNotEmpty) {
          // Check like status from post data
          final isLiked =
              (post['is_liked']?.toString() == '1' || post['is_liked'] == true);
          if (isLiked) {
            likedPostIds.add(postId);
            debugPrint('Added post $postId to likedPostIds (from posts data)');
          }

          // Check save status from post data
          final isSaved =
              (post['is_saved']?.toString() == '1' || post['is_saved'] == true);
          if (isSaved) {
            savedPostIds.add(postId);
            debugPrint('Added post $postId to savedPostIds (from posts data)');
          }
        }
      }

      final Set<String> catSet =
          posts.map((p) => p['category_name']?.toString() ?? '').toSet();
      categories = catSet.where((c) => c.isNotEmpty).toList();

      categories.insert(0, 'All');

      if (categories.isNotEmpty && selectedCategory.isEmpty) {
        selectedCategory = 'All';
      }

      debugPrint('Final likedPostIds after fetching: $likedPostIds');
      debugPrint('Final savedPostIds after fetching: $savedPostIds');

      // Calculate unread count after fetching posts
      _calculateUnreadCount();
    } catch (e) {
      debugPrint("Error fetching posts: $e");
    }
    if (mounted) {
      setState(() => loading = false);
    }
  }

  Future<void> _refreshPostsPreservingPosition(
      {bool showLoading = true}) async {
    final filteredPosts = _getFilteredPosts();
    String? currentPostId;
    if (filteredPosts.isNotEmpty && currentIndex < filteredPosts.length) {
      currentPostId = filteredPosts[currentIndex]['id']?.toString();
    }

    await fetchPostsAndCategories(showLoading: showLoading);

    if (currentPostId != null && mounted) {
      final newFilteredPosts = _getFilteredPosts();
      final newIndex = newFilteredPosts
          .indexWhere((p) => p['id']?.toString() == currentPostId);
      if (newIndex != -1) {
        setState(() {
          currentIndex = newIndex;
        });
        WidgetsBinding.instance.addPostFrameCallback((_) {
          if (mounted) {
            _pageController.jumpToPage(newIndex);
          }
        });
      }
    }
  }

  List<Map<String, dynamic>> _getFilteredPosts() {
    if (selectedCategory.isEmpty || selectedCategory == 'All') return posts;
    final filtered =
        posts.where((p) => p['category_name'] == selectedCategory).toList();
    return filtered;
  }

  Widget _buildPostCard(Map<String, dynamic> currentPost) {
    final postId = currentPost['id']?.toString() ?? '';
    if (postId.isEmpty) return const SizedBox.shrink();

    final isLiked = likedPostIds.contains(postId);
    final isSaved = savedPostIds.contains(postId);

    final fullContent = currentPost['content']?.toString() ?? '';
    final contentWords = fullContent.split(' ');

    final isTamilContent = RegExp(r'[\u0B80-\u0BFF]').hasMatch(fullContent);
    
    final isLongContent = contentWords.length > 70 || fullContent.length > 500;
    String title = currentPost['title']?.toString() ?? '';
    if (title.length > 47) title = '${title.substring(0, 47)}...';

    String imageUrl =
        currentPost['image']?.toString().replaceAll(r'\/', '/') ?? '';
    if (imageUrl.isNotEmpty && !imageUrl.startsWith('http')) {
      imageUrl = 'https://www.firstinmarket.com/app/api/uploads/' + imageUrl;
      debugPrint(imageUrl);
    }
    final updatedAt = currentPost['created_at']?.toString() ?? '';
    final viewsCount = currentPost['views_count']?.toString() ?? '0';
    final likesCount = currentPost['likes_count']?.toString() ?? '0';
    String savesCount = currentPost['saves_count']?.toString() ?? '0';
    if (savesCount.isEmpty) {
      savesCount = currentPost['saved_counts']?.toString() ?? '';
    }
    if (savesCount.isEmpty) {
      savesCount = currentPost['saves_count']?.toString() ?? '0';
    }
    debugPrint('Post $postId savesCount: $savesCount');

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
        children: [
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
                      child: const Icon(
                        Icons.share,
                        color: Colors.white,
                        size: 15,
                      ),
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
          const SizedBox(height: 25),
          Container(
            height:
                isLongContent ? MediaQuery.of(context).size.height * 0.3 : null,
            padding: const EdgeInsets.all(10),
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
    await _refreshPostsPreservingPosition(showLoading: true);
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
                  const Text(
                    'Posts',
                    style: TextStyle(
                      fontSize: 20,
                      fontWeight: FontWeight.w700,
                      color: Colors.white,
                    ),
                  ),
                  Row(
                    children: [
                      // Unread posts button
                      if (unreadCount > 0)
                        GestureDetector(
                          onTap: () {
                            Navigator.of(context)
                                .push(
                              MaterialPageRoute(
                                builder: (context) => const UnreadScreen(),
                              ),
                            )
                                .then((_) {
                              // Refresh the read count when coming back
                              loadReadPostIds();
                              _calculateUnreadCount();
                            });
                          },
                          child: Container(
                            padding: const EdgeInsets.symmetric(
                                horizontal: 12, vertical: 8),
                            margin: const EdgeInsets.only(right: 8),
                            decoration: BoxDecoration(
                              color: Colors.orange.withOpacity(0.2),
                              borderRadius: BorderRadius.circular(8),
                              border:
                                  Border.all(color: Colors.orange, width: 1),
                            ),
                            child: Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Icon(
                                  Icons.fiber_new,
                                  color: Colors.orange,
                                  size: 16,
                                ),
                                const SizedBox(width: 4),
                                Text(
                                  '$unreadCount',
                                  style: TextStyle(
                                    color: Colors.orange,
                                    fontSize: 12,
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),
                      // Refresh button
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
              const Expanded(
                  child: Center(
                      child: Text(
                'No posts available.',
                style: TextStyle(color: Colors.white70, fontSize: 18),
              )))
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
