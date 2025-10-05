import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:share_plus/share_plus.dart';
import '../../services/api_service.dart';
import '../components/navigation.dart';

class SavedPostsScreen extends StatefulWidget {
  const SavedPostsScreen({super.key});

  @override
  _SavedPostsScreenState createState() => _SavedPostsScreenState();
}

class _SavedPostsScreenState extends State<SavedPostsScreen> {
  // State variables
  Set<String> likedPostIds = <String>{};
  Set<String> savedPostIds = <String>{};
  List<String> categories = [];
  List<Map<String, dynamic>> savedPosts = [];
  int currentIndex = 0;
  bool loading = false;
  bool refreshing = false;
  String selectedCategory = '';
  final PageController _pageController = PageController();
  bool showActions = true;

  @override
  void initState() {
    super.initState();
    fetchSavedPosts(showLoading: true);

    _pageController.addListener(() {
      setState(() {
        currentIndex = _pageController.page?.round() ?? 0;
      });
      final filteredPosts = _getFilteredPosts();
      if (filteredPosts.isNotEmpty && currentIndex < filteredPosts.length) {
        final postId = filteredPosts[currentIndex]['id']?.toString() ?? '';
        if (postId.isNotEmpty) {
          _updatePostCount(postId, 'views_count');
        }
      }
    });
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
            savedPosts.indexWhere((p) => p['id']?.toString() == postId);
        if (postIndex != -1) {
          final post = savedPosts[postIndex];
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

  // Toggle save functionality (unsave from saved posts)
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
          // Remove from saved posts list when unsaved
          savedPosts.removeWhere((post) => post['id']?.toString() == postId);
          debugPrint('Removed post $postId from savedPostIds and savedPosts');
        } else {
          savedPostIds.add(postId);
          debugPrint('Added post $postId to savedPostIds');
        }
        debugPrint('Current savedPostIds: $savedPostIds');
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
          savedPosts.indexWhere((p) => p['id']?.toString() == postId);
      if (postIndex != -1) {
        final post = savedPosts[postIndex];
        final currentCount = int.tryParse(post[field]?.toString() ?? '0') ?? 0;
        post[field] = (currentCount + 1).toString();
      }
      if (mounted) setState(() {});
    } catch (e) {
      debugPrint('Error updating post $field: $e');
    }
  }

  Future<void> fetchSavedPosts({bool showLoading = true}) async {
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
        'posts/saved_posts.php',
        query: {'user_id': userId},
      );

      debugPrint('Saved Posts API Response: $postsResponse');

      if (postsResponse is List) {
        savedPosts =
            postsResponse.map((e) => Map<String, dynamic>.from(e)).toList();
      } else if (postsResponse is Map<String, dynamic> &&
          postsResponse['body'] is List) {
        savedPosts = (postsResponse['body'] as List)
            .map((e) => Map<String, dynamic>.from(e))
            .toList();
      }

      debugPrint('Total saved posts: ${savedPosts.length}');

      // Clear existing sets before rebuilding
      likedPostIds.clear();
      savedPostIds.clear();

      // Build like/save status for saved posts
      for (final post in savedPosts) {
        final postId = post['id']?.toString() ?? '';
        if (postId.isNotEmpty) {
          // All posts in this screen are saved by definition
          savedPostIds.add(postId);

          // Check like status
          final isLiked =
              (post['is_liked']?.toString() == '1' || post['is_liked'] == true);
          if (isLiked) {
            likedPostIds.add(postId);
            debugPrint('✓ Post $postId is liked');
          }
        }
      }

      final Set<String> catSet =
          savedPosts.map((p) => p['category_name']?.toString() ?? '').toSet();
      categories = catSet.where((c) => c.isNotEmpty).toList();

      categories.insert(0, 'All');

      if (categories.isNotEmpty && selectedCategory.isEmpty) {
        selectedCategory = 'All';
      }

      debugPrint('Final likedPostIds for saved posts: $likedPostIds');
      debugPrint('Final savedPostIds for saved posts: $savedPostIds');
    } catch (e) {
      debugPrint("Error fetching saved posts: $e");
    }
    if (mounted) {
      setState(() => loading = false);
    }
  }

  List<Map<String, dynamic>> _getFilteredPosts() {
    if (selectedCategory.isEmpty || selectedCategory == 'All')
      return savedPosts;
    final filtered = savedPosts
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
    final isLongContent = contentWords.length > 30 || fullContent.length > 300;
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

    // Calculate time ago for when it was saved

    // Calculate time ago for post creation
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
                      // Saves (Unsave button)
                      GestureDetector(
                        onTap: () async {
                          final prefs = await SharedPreferences.getInstance();
                          final userId = prefs.getString('user_id') ?? '';
                          if (userId.isNotEmpty && postId.isNotEmpty) {
                            // Show confirmation dialog before unsaving
                            final shouldUnsave = await showDialog<bool>(
                              context: context,
                              builder: (BuildContext context) {
                                return AlertDialog(
                                  backgroundColor: const Color(0xFF232A3B),
                                  title: const Text(
                                    'Unsave Post',
                                    style: TextStyle(color: Colors.white),
                                  ),
                                  content: const Text(
                                    'Are you sure you want to remove this post from your saved posts?',
                                    style: TextStyle(color: Colors.white70),
                                  ),
                                  actions: [
                                    TextButton(
                                      onPressed: () =>
                                          Navigator.of(context).pop(false),
                                      child: const Text('Cancel'),
                                    ),
                                    TextButton(
                                      onPressed: () =>
                                          Navigator.of(context).pop(true),
                                      child: const Text(
                                        'Unsave',
                                        style:
                                            TextStyle(color: Colors.redAccent),
                                      ),
                                    ),
                                  ],
                                );
                              },
                            );

                            if (shouldUnsave == true) {
                              await toggleSave(postId, userId, isSaved);
                            }
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
                                Icons
                                    .bookmark, // Always filled since all posts here are saved
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
    await fetchSavedPosts(showLoading: true);
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
                            'Saved Posts',
                            style: TextStyle(
                              fontSize: 20,
                              fontWeight: FontWeight.w700,
                              color: Colors.white,
                            ),
                          ),
                          Text(
                            '${savedPosts.length} saved posts',
                            style: TextStyle(
                              fontSize: 12,
                              color: const Color(0xFF5F8DFF),
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
                        Icons.bookmark_border,
                        size: 64,
                        color: Colors.grey[600],
                      ),
                      const SizedBox(height: 16),
                      const Text(
                        'No Saved Posts',
                        style: TextStyle(
                            color: Colors.white,
                            fontSize: 24,
                            fontWeight: FontWeight.bold),
                      ),
                      const SizedBox(height: 8),
                      const Text(
                        'Posts you save will appear here.',
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
