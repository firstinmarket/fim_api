<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}
?>
<?php
include './resource/conn.php';
$pdo = getDB();
try {
    $stmt = $pdo->prepare("
        UPDATE posts
        SET status = 'published', updated_at = NOW()
        WHERE status = 'scheduled'
          AND scheduled_time IS NOT NULL
          AND scheduled_time <= NOW()
    ");
    $stmt->execute();
} catch (Exception $e) {
    
}
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FIM News | Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="https://unpkg.com/feather-icons"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#FF6B35',
                        secondary: '#F7931E',
                        'primary-dark': '#E55A2B',
                        'fim-blue': '#1E40AF',
                        'fim-green': '#10B981'
                    }
                }
            }
        }
    </script>
    <style>
        [v-cloak] { display: none }
        .sidebar-link:hover .sidebar-icon {
            transform: translateX(3px);
        }
        .sidebar-icon {
            transition: transform 0.2s ease;
        }
        .fade-enter-active, .fade-leave-active {
            transition: opacity 0.3s ease;
        }
        .fade-enter-from, .fade-leave-to {
            opacity: 0;
        }
        .slide-left-enter-active, .slide-left-leave-active {
            transition: transform 0.3s ease;
        }
        .slide-left-enter-from, .slide-left-leave-to {
            transform: translateX(-100%);
        }
        .sidebar-toggle {
            transition: all 0.3s ease;
        }
        .search-focused {
            box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.1);
        }
    </style>
</head>
<body class="bg-gray-900 text-gray-100 min-h-screen">
    <div id="app" v-cloak class="flex min-h-screen">
        <!-- Sidebar -->
    <?php include('./resource/sidebar.php') ?>

            <!-- Main Content Area -->
            <main class="flex-1 overflow-y-auto p-4 md:p-6 bg-gray-900">
                
                <!-- Dashboard Section -->
                <div v-if="currentPage === 'dashboard'">
                    <!-- Dashboard Header -->
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h2 class="text-2xl font-bold text-white">Dashboard Overview</h2>
                            <p class="text-gray-400" v-if="statsError">{{ statsError }}</p>
                        </div>
                        <button @click="fetchStats" :disabled="isLoadingStats" class="bg-primary hover:bg-primary-dark disabled:opacity-50 text-white px-4 py-2 rounded-lg flex items-center transition-all">
                            <i data-feather="refresh-cw" class="w-4 h-4 mr-2" :class="{ 'animate-spin': isLoadingStats }"></i>
                           
                        </button>
                    </div>
                    
                    <!-- Stats Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                        <div class="bg-gray-800 rounded-xl p-6 shadow-lg border-l-4 border-primary">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-400 text-sm font-medium">Total Posts</p>
                                    <h3 class="text-2xl font-bold mt-1" :class="{ 'animate-pulse': isLoadingStats }">
                                        {{ isLoadingStats ? '...' : formatNumber(stats.totalPosts) }}
                                    </h3>
                                  
                                </div>
                                <div class="w-12 h-12 rounded-full bg-primary bg-opacity-10 flex items-center justify-center">
                                    <i data-feather="file-text" class="w-5 h-5 text-primary"></i>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-800 rounded-xl p-6 shadow-lg border-l-4 border-blue-500">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-400 text-sm font-medium">Total Views</p>
                                    <h3 class="text-2xl font-bold mt-1" :class="{ 'animate-pulse': isLoadingStats }">
                                        {{ isLoadingStats ? '...' : formatNumber(stats.totalViews) }}
                                    </h3>
                                   
                                </div>
                                <div class="w-12 h-12 rounded-full bg-blue-500 bg-opacity-10 flex items-center justify-center">
                                    <i data-feather="eye" class="w-5 h-5 text-blue-500"></i>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-800 rounded-xl p-6 shadow-lg border-l-4 border-green-500">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-400 text-sm font-medium">Total Likes</p>
                                    <h3 class="text-2xl font-bold mt-1" :class="{ 'animate-pulse': isLoadingStats }">
                                        {{ isLoadingStats ? '...' : formatNumber(stats.totalLikes) }}
                                    </h3>
                                  
                                </div>
                                <div class="w-12 h-12 rounded-full bg-green-500 bg-opacity-10 flex items-center justify-center">
                                    <i data-feather="heart" class="w-5 h-5 text-green-500"></i>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-800 rounded-xl p-6 shadow-lg border-l-4 border-purple-500">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-400 text-sm font-medium">Total Users</p>
                                    <h3 class="text-2xl font-bold mt-1" :class="{ 'animate-pulse': isLoadingStats }">
                                        {{ isLoadingStats ? '...' : formatNumber(stats.totalUsers) }}
                                    </h3>
                                   
                                </div>
                                <div class="w-12 h-12 rounded-full bg-purple-500 bg-opacity-10 flex items-center justify-center">
                                    <i data-feather="users" class="w-5 h-5 text-purple-500"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Stats Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                        <div class="bg-gray-800 rounded-xl p-6 shadow-lg border-l-4 border-yellow-500">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-400 text-sm font-medium">Total Shares</p>
                                    <h3 class="text-2xl font-bold mt-1" :class="{ 'animate-pulse': isLoadingStats }">
                                        {{ isLoadingStats ? '...' : formatNumber(stats.totalShares) }}
                                    </h3>
                                    <p class="text-blue-400 text-xs mt-1">Avg: {{ formatNumber(stats.avgLikesPerPost) }} per post</p>
                                </div>
                                <div class="w-12 h-12 rounded-full bg-yellow-500 bg-opacity-10 flex items-center justify-center">
                                    <i data-feather="share-2" class="w-5 h-5 text-yellow-500"></i>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-800 rounded-xl p-6 shadow-lg border-l-4 border-pink-500">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-400 text-sm font-medium">Total Saves</p>
                                    <h3 class="text-2xl font-bold mt-1" :class="{ 'animate-pulse': isLoadingStats }">
                                        {{ isLoadingStats ? '...' : formatNumber(stats.totalSaves) }}
                                    </h3>
                                    <p class="text-pink-400 text-xs mt-1">User engagement</p>
                                </div>
                                <div class="w-12 h-12 rounded-full bg-pink-500 bg-opacity-10 flex items-center justify-center">
                                    <i data-feather="bookmark" class="w-5 h-5 text-pink-500"></i>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-800 rounded-xl p-6 shadow-lg border-l-4 border-cyan-500">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-400 text-sm font-medium">Verified Users</p>
                                    <h3 class="text-2xl font-bold mt-1" :class="{ 'animate-pulse': isLoadingStats }">
                                        {{ isLoadingStats ? '...' : formatNumber(stats.verifiedUsers) }}
                                    </h3>
                                    <p class="text-cyan-400 text-xs mt-1">{{ stats.verificationRate }}% verified</p>
                                </div>
                                <div class="w-12 h-12 rounded-full bg-cyan-500 bg-opacity-10 flex items-center justify-center">
                                    <i data-feather="check-circle" class="w-5 h-5 text-cyan-500"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts Row -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                        <!-- Views Chart -->
                        <div class="bg-gray-800 rounded-xl p-6 shadow-lg">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h3 class="font-semibold text-lg">Views Analytics</h3>
                                    <div class="flex items-center space-x-4 mt-2">
                                        <span class="text-sm text-gray-400">
                                            Total: {{ formatNumber(viewsAnalytics.total_views) }} views
                                        </span>
                                        <span v-if="viewsAnalytics.summary.growth_rate !== 0" 
                                              :class="viewsAnalytics.summary.growth_rate > 0 ? 'text-green-400' : 'text-red-400'" 
                                              class="text-sm flex items-center">
                                            <i :data-feather="viewsAnalytics.summary.growth_rate > 0 ? 'trending-up' : 'trending-down'" 
                                               class="w-3 h-3 mr-1"></i>
                                            {{ Math.abs(viewsAnalytics.summary.growth_rate) }}%
                                        </span>
                                    </div>
                                </div>
                                <div class="flex space-x-2">
                                    <button @click="changeViewsPeriod('weekly')" 
                                            :disabled="isLoadingViews"
                                            :class="viewsAnalytics.period === 'weekly' ? 'bg-primary text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600'"
                                            class="px-3 py-1 text-xs rounded-lg transition-all disabled:opacity-50">
                                        Weekly
                                    </button>
                                    <button @click="changeViewsPeriod('monthly')" 
                                            :disabled="isLoadingViews"
                                            :class="viewsAnalytics.period === 'monthly' ? 'bg-primary text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600'"
                                            class="px-3 py-1 text-xs rounded-lg transition-all disabled:opacity-50">
                                        Monthly
                                    </button>
                                    <button @click="changeViewsPeriod('yearly')" 
                                            :disabled="isLoadingViews"
                                            :class="viewsAnalytics.period === 'yearly' ? 'bg-primary text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600'"
                                            class="px-3 py-1 text-xs rounded-lg transition-all disabled:opacity-50">
                                        Yearly
                                    </button>
                                </div>
                            </div>
                            <div class="h-64 relative">
                                <div v-if="isLoadingViews" class="absolute inset-0 flex items-center justify-center bg-gray-800 bg-opacity-75 rounded-lg">
                                    <div class="flex items-center space-x-2 text-gray-400">
                                        <i data-feather="loader" class="w-5 h-5 animate-spin"></i>
                                        <span>Loading analytics...</span>
                                    </div>
                                </div>
                                <canvas id="viewsChart"></canvas>
                            </div>
                        </div>
                        
                        <!-- Top Categories Chart -->
                        <div class="bg-gray-800 rounded-xl p-6 shadow-lg">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="font-semibold text-lg">Top Categories</h3>
                                <div class="flex items-center space-x-2 text-gray-400 text-sm">
                                    <i data-feather="calendar" class="w-4 h-4"></i>
                                    <span>Last 30 days</span>
                                </div>
                            </div>
                            <div class="h-64">
                                <canvas id="categoriesChart"></canvas>
                            </div>
                        </div>
                    </div>

                   
                </div>

                <!-- Posts Management Section -->
                <div v-if="currentPage === 'posts'">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold text-white">News Articles Management</h2>
                        <button @click="showPostModal = true; editingPost = null; resetPostForm()" class="bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-lg flex items-center">
                            <i data-feather="plus" class="w-4 h-4 mr-2"></i>
                            Add News Article
                        </button>
                    </div>
                    
                    <!-- Posts Filter & Search -->
                    <div class="bg-gray-800 rounded-xl p-4 mb-6">
                        <div class="flex flex-col md:flex-row gap-4">
                            <div class="flex-1">
                                <input v-model="postSearch" type="text" placeholder="Search news articles..." class="w-full bg-gray-700 text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                            </div>
                            <select v-model="postFilter" class="bg-gray-700 text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                                <option value="all">All Articles</option>
                                <option value="published">Published</option>
                                <option value="draft">Draft</option>
                                <option value="scheduled">Scheduled</option>
                                <option value="breaking">Breaking News</option>
                            </select>
                        </div>
                    </div>

                    <!-- Posts Table -->
                    <div class="bg-gray-800 rounded-xl shadow-lg overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-700">
                                <thead class="bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Post</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Category</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Stats</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Language</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-gray-800 divide-y divide-gray-700">
                                    <tr v-for="post in filteredPosts" :key="post.id" class="hover:bg-gray-700 transition">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <img :src="'../api/uploads/' + post.image" alt="" class="w-10 h-10 rounded mr-3" />
                                                <span class="font-semibold text-white cursor-pointer" @click="showPostDetails(post)">{{ post.title }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{post.subcategory_name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="getStatusClass(post.status)">{{ post.status }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-xs text-gray-400">Likes: {{ post.likes_count }} | Shares: {{ post.shares_count }} | Saves: {{ post.saves_count }} | Views: {{ post.views_count }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ post.created_at }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap capitalize">{{ post.language }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <button @click="openPostDetails(post)" class="text-blue-400 hover:text-blue-600 mr-2" title="View">
                                                <!-- Eye SVG -->
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            </button>
                                            <button @click="editPost(post)" class="text-primary hover:text-primary-dark mr-2" title="Edit">
                                                <!-- Edit SVG -->
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5h2m-1 0v14m-7-7h14"/></svg>
                                            </button>
                                            <button @click="deletePost(post.id)" class="text-red-500 hover:text-red-700" title="Delete">
                                                <!-- Trash SVG -->
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Users Management Section -->
                <div v-if="currentPage === 'users'">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold text-white">Users Management</h2>
                        <button @click="showUserModal = true; editingUser = null" class="bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-lg flex items-center">
                            <i data-feather="user-plus" class="w-4 h-4 mr-2"></i>
                            Add New User
                        </button>
                    </div>

                    <!-- Users Filter & Search -->
                    <div class="bg-gray-800 rounded-xl p-4 mb-6">
                        <div class="flex flex-col md:flex-row gap-4">
                            <div class="flex-1">
                                <input v-model="userSearch" type="text" placeholder="Search users..." class="w-full bg-gray-700 text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                            </div>
                           
                        </div>
                    </div>

                    <!-- Users Table -->
                    <div class="bg-gray-800 rounded-xl shadow-lg overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-700">
                                <thead class="bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">User</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Phone</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Joined Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Language</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Requests</th>
                                  
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-300 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-gray-800 divide-y divide-gray-700">
                                    <tr v-for="user in filteredUsers" :key="user.id" class="hover:bg-gray-700">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center">
                                                <div class="h-10 w-10 flex-shrink-0">
                                                    <div class="h-10 w-10 rounded-full bg-gradient-to-br from-primary to-secondary flex items-center justify-center">
                                                        <span class="text-white text-sm font-medium">{{ user.name.charAt(0) }}</span>
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-white">{{ user.name }}</div>
                                                    <div class="text-sm text-gray-400">{{ user.email }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-sm text-gray-400">
                                                {{ user.mobile }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="user.status === 'active' ? 'px-2 py-1 text-xs font-medium bg-green-500 bg-opacity-10 text-green-400 rounded-full' : 'px-2 py-1 text-xs font-medium bg-red-500 bg-opacity-10 text-red-400 rounded-full'">
                                                {{ user.created_at }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">
                                            {{ user.language }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">
                                            {{ user.remove }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <button @click="editUser(user)" class="text-primary hover:text-primary-dark mr-3" title="Update">
                                                <!-- Edit SVG -->
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5h2m-1 0v14m-7-7h14"/></svg>
                                            </button>
                                            <button @click="deleteUser(user.id)" class="text-red-400 hover:text-red-300" title="Delete">
                                                <!-- Trash SVG -->
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Categories Management Section -->
                <!-- Category Add/Edit Modal -->
                <div v-if="showCategoryModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" @click="showCategoryModal = false">
                    <div class="bg-gray-800 rounded-xl p-6 w-full max-w-lg max-h-[90vh] overflow-y-auto" @click.stop>
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-xl font-bold text-white">{{ editingCategory ? 'Edit Category' : 'Add Category' }}</h3>
                            <button @click="showCategoryModal = false" class="text-gray-400 hover:text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <form @submit.prevent="saveCategory" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Category Name</label>
                                <input v-model="editingCategory.name" type="text" class="w-full bg-gray-700 text-white px-4 py-2 rounded-lg" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Subcategories</label>
                                <div v-for="(sub, idx) in editingCategory.subcategories" :key="sub.id || idx" class="flex items-center mb-2 gap-2">
                                    <input v-model="sub.name" type="text" class="flex-1 bg-gray-700 text-white px-3 py-2 rounded-lg" placeholder="Subcategory name" required>
                                    <button type="button" @click="editingCategory.subcategories.splice(idx, 1)" class="text-red-400 hover:text-red-300" title="Remove">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                                <button type="button" @click="editingCategory.subcategories.push({ name: '' })" class="mt-2 bg-primary hover:bg-primary-dark text-white px-3 py-1 rounded-lg text-xs">+ Add Subcategory</button>
                            </div>
                            <div class="flex justify-end space-x-4 pt-4">
                                <button type="button" @click="showCategoryModal = false" class="px-6 py-2 text-gray-400 hover:text-white">Cancel</button>
                                <button type="submit" class="bg-primary hover:bg-primary-dark text-white px-6 py-2 rounded-lg">{{ editingCategory.id ? 'Update' : 'Add' }}</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div v-if="currentPage === 'categories'">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold text-white">Categories Management</h2>
                        <button @click="showCategoryModal = true; editingCategory = null" class="bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-lg flex items-center">
                            <!-- Folder Plus SVG -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4h16v16H4V4zm8 8v4m0-4h4m-4 0H8"/></svg>
                            Add Category
                        </button>
                    </div>

                    <!-- Categories Grid -->
                    <div v-if="isLoadingCategories" class="flex items-center justify-center py-12">
                        <div class="text-center">
                            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary mx-auto mb-4"></div>
                            <p class="text-gray-400">Loading categories...</p>
                        </div>
                    </div>

                    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div v-for="category in categories" :key="category.id" class="bg-gray-800 rounded-xl p-6 shadow-lg">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center">
                                    <div class="w-12 h-12 rounded-full bg-purple-500 bg-opacity-10 flex items-center justify-center mr-4">
                                        <!-- Folder SVG -->
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 7v10a2 2 0 002 2h14a2 2 0 002-2V7"/></svg>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-semibold text-white">{{ category.name }}</h3>
                                        <p class="text-sm text-gray-400">{{ category.postsCount }} posts</p>
                                    </div>
                                </div>
                                <div class="flex space-x-2">
                                    <button @click="editCategory(category)" class="text-primary hover:text-primary-dark" title="Edit">
                                        <!-- Edit SVG -->
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5h2m-1 0v14m-7-7h14"/></svg>
                                    </button>
                                    <button @click="deleteCategory(category.id)" class="text-red-400 hover:text-red-300" title="Delete">
                                        <!-- Trash SVG -->
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            </div>
                            <p class="text-gray-300 text-sm mb-4">{{ category.description }}</p>
                            <div v-if="category.subcategories && category.subcategories.length > 0">
                                <h4 class="text-sm font-medium text-gray-400 mb-2">Subcategories:</h4>
                                <div class="flex flex-wrap gap-2">
                                    <span v-for="sub in category.subcategories" :key="sub.id" class="px-2 py-1 text-xs bg-gray-700 text-gray-300 rounded-full">
                                        {{ sub.name }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Analytics Section -->
                <div v-if="currentPage === 'analytics'">
                    <h2 class="text-2xl font-bold text-white mb-6">Analytics & Metrics</h2>
                    
                    <!-- Analytics Stats -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <div class="bg-gray-800 rounded-xl p-6 shadow-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-400 text-sm font-medium">Most Viewed Post</p>
                                    <h3 class="text-xl font-bold mt-1 text-white">{{ analytics.mostViewed && analytics.mostViewed.title ? analytics.mostViewed.title : 'N/A' }}</h3>
                                    <p class="text-primary text-sm mt-1">{{ analytics.mostViewed && analytics.mostViewed.views !== undefined ? analytics.mostViewed.views : 0 }} views</p>
                                </div>
                                <div class="w-12 h-12 rounded-full bg-blue-500 bg-opacity-10 flex items-center justify-center">
                                    <i data-feather="trending-up" class="w-6 h-6 text-blue-400"></i>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-800 rounded-xl p-6 shadow-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-400 text-sm font-medium">Most Liked Post</p>
                                    <h3 class="text-xl font-bold mt-1 text-white">{{ analytics.mostLiked && analytics.mostLiked.title ? analytics.mostLiked.title : 'N/A' }}</h3>
                                    <p class="text-green-400 text-sm mt-1">{{ analytics.mostLiked && analytics.mostLiked.likes !== undefined ? analytics.mostLiked.likes : 0 }} likes</p>
                                </div>
                                <div class="w-12 h-12 rounded-full bg-green-500 bg-opacity-10 flex items-center justify-center">
                                    <i data-feather="heart" class="w-6 h-6 text-green-400"></i>
                                </div>
                            </div>
                        </div>
                        <!-- <div class="bg-gray-800 rounded-xl p-6 shadow-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-400 text-sm font-medium">Top Author</p>
                                    <h3 class="text-xl font-bold mt-1 text-white">{{ analytics.topAuthor && analytics.topAuthor.name ? analytics.topAuthor.name : 'N/A' }}</h3>
                                    <p class="text-purple-400 text-sm mt-1">{{ analytics.topAuthor && analytics.topAuthor.posts !== undefined ? analytics.topAuthor.posts : 0 }} posts</p>
                                </div>
                                <div class="w-12 h-12 rounded-full bg-purple-500 bg-opacity-10 flex items-center justify-center">
                                    <i data-feather="award" class="w-6 h-6 text-purple-400"></i>
                                </div>
                            </div>
                        </div> -->
                    </div>

                    <!-- Analytics Charts -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="bg-gray-800 rounded-xl p-6 shadow-lg">
                            <h3 class="font-semibold text-lg text-white mb-4">Posts Per Month</h3>
                            <div class="h-64">
                                <canvas id="postsChart"></canvas>
                            </div>
                        </div>
                        <div class="bg-gray-800 rounded-xl p-6 shadow-lg">
                            <h3 class="font-semibold text-lg text-white mb-4">User Engagement</h3>
                            <div class="h-64">
                                <canvas id="engagementChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

               

            </main>
        </div>

        <!-- Add/Edit News Article Modal -->
        <div v-if="showPostModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" @click="showPostModal = false">
            <div class="bg-gray-800 rounded-xl p-6 w-full max-w-4xl max-h-[90vh] overflow-y-auto" @click.stop>
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-white">
                        {{ editingPost ? 'Edit News Article' : 'Add New Article' }}
                    </h3>
                    <button @click="showPostModal = false" class="text-gray-400 hover:text-white">
                        <i data-feather="x" class="w-6 h-6"></i>
                    </button>
                </div>

                <form @submit.prevent="savePost" class="space-y-6">
                    <!-- Article Title -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Article Title *</label>
                        <input v-model="postForm.title" type="text" required 
                               class="w-full bg-gray-700 text-white px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                               placeholder="Enter article title...">
                    </div>

           <!-- Article Content -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Article Content *</label>
                        <textarea v-model="postForm.content" required rows="8"
                                  class="w-full bg-gray-700 text-white px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary resize-none"
                                  placeholder="Write your article content here..."></textarea>
                    </div>
                  

                    <!-- Category Selection -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Category *</label>
                            <select v-model="postForm.category_id" @change="onCategoryChange" @focus="fetchCategories" :disabled="isLoadingCategories" required 
                                    class="w-full bg-gray-700 text-white px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary disabled:opacity-50">
                                <option value="">{{ isLoadingCategories ? 'Loading categories...' : 'Select a category' }}</option>
                                <option v-for="category in categories" :key="category.id" :value="category.id">
                                    {{ category.name }}
                                </option>
                            </select>
                        </div>
                        <div v-if="selectedCategorySubcategories.length > 0">
                            <label class="block text-sm font-medium text-gray-300 mb-2">Subcategory</label>
                            <select v-model="postForm.subcategory_id"
                                    class="w-full bg-gray-700 text-white px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                                <option value="">Select a subcategory (optional)</option>
                                <option v-for="subcategory in selectedCategorySubcategories" :key="subcategory.id" :value="subcategory.id">
                                    {{ subcategory.name }}
                                </option>
                            </select>
                        </div>
                    </div>

                    

                    <!-- Image Upload -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Featured Image</label>
                        <div class="flex items-center space-x-4">
                            <input type="file" @change="handleImageUpload" accept="image/*" 
                                   class="hidden" ref="imageInput">
                            <button type="button" @click="$refs.imageInput.click()" 
                                    class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg flex items-center">
                                <i data-feather="upload" class="w-4 h-4 mr-2"></i>
                                Choose Image
                            </button>
                            <span v-if="postForm.image" class="text-sm text-gray-400">{{ postForm.imageName }}</span>
                        </div>
                    </div>

                    <!-- Status and Publishing Options -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Status Selection -->
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Status *</label>
                            <select v-model="postForm.status" required 
                                    class="w-full bg-gray-700 text-white px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                                <option value="draft">Draft</option>
                                <option value="published">Published</option>
                                <option value="scheduled">Scheduled</option>
                            </select>
                        </div>

                        <!-- Schedule DateTime (only show when status is 'scheduled') -->
                        <div v-if="postForm.status === 'scheduled'">
                            <label class="block text-sm font-medium text-gray-300 mb-2">Schedule Date & Time *</label>
                            <input v-model="postForm.scheduled_at" type="datetime-local" 
                                   :required="postForm.status === 'scheduled'"
                                   class="w-full bg-gray-700 text-white px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>
                    </div>

                    <!-- Language Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Language *</label>
                        <select v-model="postForm.language" required
                            class="w-full bg-gray-700 text-white px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                            <option value="english">English</option>
                            <option value="tamil">Tamil</option>
                        </select>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-end space-x-4 pt-6 border-t border-gray-600">
                        <button type="button" @click="showPostModal = false" 
                                class="px-6 py-2 text-gray-400 hover:text-white transition-colors">
                            Cancel
                        </button>
                        <button type="submit" :disabled="isSubmittingPost"
                                class="bg-primary hover:bg-primary-dark disabled:opacity-50 text-white px-6 py-2 rounded-lg flex items-center">
                            <i v-if="isSubmittingPost" data-feather="loader" class="w-4 h-4 mr-2 animate-spin"></i>
                            <i v-else data-feather="save" class="w-4 h-4 mr-2"></i>
                            {{ isSubmittingPost ? 'Saving...' : (editingPost ? 'Update Article' : 'Save Article') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Post Details Modal -->
        <div v-if="showPostDetailsModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" @click="showPostDetailsModal = false">
            <div class="bg-gray-800 rounded-xl p-6 w-full max-w-3xl max-h-[90vh] overflow-y-auto" @click.stop>
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-white" v-if="postDetails">
                        {{ postDetails.title }}
                    </h3>
                    <button @click="showPostDetailsModal = false" class="text-gray-400 hover:text-white">
                        <i data-feather="x" class="w-6 h-6"></i>
                    </button>
                </div>

                <div v-if="postDetails" class="space-y-4">
                    <!-- Post Image -->
                    <div class="aspect-w-16 aspect-h-9 rounded-xl overflow-hidden">
                        <img :src="'../api/uploads/' + postDetails.image" alt="" class="object-cover w-full h-full" v-if="postDetails.image">
                    </div>

                    <!-- Post Content -->
                    <div class="text-gray-300" v-html="postDetails.content"></div>

                    <!-- Meta Info -->
                    <div class="flex flex-col sm:flex-row sm:justify-between text-sm text-gray-400">
                        <div>
                            <span class="font-medium text-white">Category:</span> {{ postDetails.subcategory_name }}
                        </div>
                        <div>
                            <span class="font-medium text-white">Status:</span> <span :class="getStatusClass(postDetails.status)">{{ postDetails.status }}</span>
                        </div>
                        <div>
                            <span class="font-medium text-white">Published on:</span> {{ new Date(postDetails.created_at).toLocaleDateString() }}
                        </div>
                        <div>
                            <span class="font-medium text-white">Language:</span> {{ postDetails.language }}
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col sm:flex-row gap-4 mt-4">
                        <button @click="editPost(postDetails)" class="bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-lg flex items-center justify-center">
                            <i data-feather="edit" class="w-4 h-4 mr-2"></i>
                            Edit Article
                        </button>
                        <button @click="deletePost(postDetails.id)" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg flex items-center justify-center">
                            <i data-feather="trash" class="w-4 h-4 mr-2"></i>
                            Delete Article
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit User Modal -->
        <div v-if="showUserModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" @click="showUserModal = false">
            <div class="bg-gray-800 rounded-xl p-6 w-full max-w-xl max-h-[90vh] overflow-y-auto" @click.stop>
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-white">Edit User</h3>
                    <button @click="showUserModal = false" class="text-gray-400 hover:text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form @submit.prevent="saveUser" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Name</label>
                        <input v-model="editingUser.name" type="text" class="w-full bg-gray-700 text-white px-4 py-2 rounded-lg" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Mobile</label>
                        <input v-model="editingUser.mobile" type="text" class="w-full bg-gray-700 text-white px-4 py-2 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Email</label>
                        <input v-model="editingUser.email" type="email" class="w-full bg-gray-700 text-white px-4 py-2 rounded-lg" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Language</label>
                        <select v-model="editingUser.language" class="w-full bg-gray-700 text-white px-4 py-2 rounded-lg">
                            <option value="english">English</option>
                            <option value="tamil">Tamil</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Bio</label>
                        <textarea v-model="editingUser.bio" class="w-full bg-gray-700 text-white px-4 py-2 rounded-lg"></textarea>
                    </div>
                    <div class="flex justify-end space-x-4 pt-4">
                        <button type="button" @click="showUserModal = false" class="px-6 py-2 text-gray-400 hover:text-white">Cancel</button>
                        <button type="submit" class="bg-primary hover:bg-primary-dark text-white px-6 py-2 rounded-lg">Save</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            const { createApp, ref, onMounted, computed, watch } = Vue;            createApp({
                setup() {
                    // Fetch analytics stats (most viewed, most liked, top author)
                    const fetchAnalytics = async () => {
                        try {
                            const response = await fetch('./backend/get_analytics.php', {
                                method: 'GET',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json'
                                }
                            });
                            const result = await response.json();
                            if (result.success && result.data) {
                                analytics.value = result.data;
                            } else {
                                analytics.value = {};
                                console.error(result.error || 'Failed to fetch analytics');
                            }
                        } catch (error) {
                            analytics.value = {};
                            console.error('Error fetching analytics:', error);
                        }
                    };
                    // Save (add/update) category and subcategories
                    const saveCategory = async () => {
                        if (!editingCategory.value.name) {
                            alert('Category name is required');
                            return;
                        }
                        // Prepare data
                        const payload = {
                            id: editingCategory.value.id || null,
                            name: editingCategory.value.name,
                            subcategories: editingCategory.value.subcategories.filter(s => s.name && s.name.trim() !== '')
                        };
                        try {
                            const response = await fetch('./backend/save_category.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify(payload)
                            });
                            const result = await response.json();
                            if (result.success) {
                                alert(editingCategory.value.id ? 'Category updated!' : 'Category added!');
                                showCategoryModal.value = false;
                                fetchCategories();
                            } else {
                                alert(result.error || 'Failed to save category');
                            }
                        } catch (error) {
                            alert('Error saving category. Please try again.');
                        }
                    };
                    // Edit category
                    const editCategory = (category) => {
                        editingCategory.value = { ...category };
                        showCategoryModal.value = true;
                    };

                    // Delete category
                    const deleteCategory = async (categoryId) => {
                        if (!confirm('Are you sure you want to delete this category?')) return;
                        try {
                            const response = await fetch('./backend/delete_category.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({ id: categoryId })
                            });
                            const result = await response.json();
                            if (result.success) {
                                categories.value = categories.value.filter(c => c.id !== categoryId);
                                alert('Category deleted successfully!');
                            } else {
                                alert(result.error || 'Failed to delete category.');
                            }
                        } catch (error) {
                            alert('Error deleting category. Please try again.');
                        }
                    };

                    // Save settings
                    const saveSettings = async () => {
                        // Implement settings save logic here
                        alert('Settings saved!');
                    };
                    // Toggle user status (active/inactive)
                    const toggleUserStatus = async (user) => {
                        if (!user || !user.id) return;
                        const newStatus = user.status === 'active' ? 'inactive' : 'active';
                        try {
                            const formData = new FormData();
                            formData.append('id', user.id);
                            formData.append('status', newStatus);
                            const response = await fetch('./backend/users/update_user.php', {
                                method: 'POST',
                                body: formData
                            });
                            const result = await response.json();
                            if (result.success) {
                                user.status = newStatus;
                                fetchUsers();
                            } else {
                                alert(result.error || 'Failed to update user status');
                            }
                        } catch (error) {
                            alert('Error updating user status. Please try again.');
                        }
                    };
                    const isLoadingCategories = ref(false);
                    const categoriesError = ref(null);
                    // Fetch posts from backend
                    const fetchPosts = async () => {
                        try {
                            const response = await fetch('./backend/get_posts.php', {
                                method: 'GET',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json'
                                }
                            });
                            const result = await response.json();
                            if (result.success && result.data) {
                                posts.value = result.data;
                            } else {
                                posts.value = [];
                                console.error(result.error || 'Failed to fetch posts');
                            }
                        } catch (error) {
                            posts.value = [];
                            console.error('Error fetching posts:', error);
                        }
                    };
                    const recentPosts = ref([]);
                    const recentActivities = ref([]);
                    const analytics = ref({});
                    const settings = ref({});
                    const categories = ref([]);
                    const posts = ref([]);
                    const sidebarOpen = ref(false);
                    const currentPage = ref('dashboard');
                    const searchQuery = ref('');
                    const searchFocused = ref(false);
                    const isMobile = ref(window.innerWidth < 768);
                    
                    // Modal states
                    const showPostModal = ref(false);
                    const showUserModal = ref(false);
                    const showCategoryModal = ref(false);
                    const editingPost = ref(null);
                    const editingUser = ref(null);
                    const editingCategory = ref(null);
                    
                    // Post details modal state
                    const showPostDetailsModal = ref(false);
                    const postDetails = ref(null);
                    
                    // Post form state
                    const isSubmittingPost = ref(false);
                    const selectedCategorySubcategories = ref([]);
                    const postForm = ref({
                        title: '',
                        content: '',
                        category_id: '',
                        subcategory_id: '',
                        status: 'draft',
                        scheduled_at: '',
                        image: null,
                        imageName: '',
                        language: 'english' // default
                    });
                    
                    // Reset post form
                    const resetPostForm = () => {
                        postForm.value = {
                            title: '',
                            description: '',
                            content: '',
                            category_id: '',
                            subcategory_id: '',
                            status: 'draft',
                            scheduled_at: '',
                            image: null,
                            imageName: '',
                            language: 'english'
                        };
                        selectedCategorySubcategories.value = [];
                    };
                    
                    // Open post details modal
                    const openPostDetails = (post) => {
                        postDetails.value = { ...post };
                        showPostDetailsModal.value = true;
                    };
                    
                    // Handle category change to load subcategories
                    const onCategoryChange = () => {
                        selectedCategorySubcategories.value = [];
                        postForm.value.subcategory_id = '';
                        
                        if (postForm.value.category_id) {
                            const selectedCategory = categories.value.find(cat => cat.id == postForm.value.category_id);
                            if (selectedCategory && selectedCategory.subcategories) {
                                selectedCategorySubcategories.value = selectedCategory.subcategories;
                            }
                        }
                    };
                    
                    // Handle image upload
                    const handleImageUpload = (event) => {
                        const file = event.target.files[0];
                        if (file) {
                            postForm.value.image = file;
                            postForm.value.imageName = file.name;
                        }
                    };
                    
                    // Save post function
                    const savePost = async () => {
                        isSubmittingPost.value = true;
                        
                        try {
                            const formData = new FormData();
                            formData.append('title', postForm.value.title);
                            formData.append('description', postForm.value.description);
                            formData.append('content', postForm.value.content);
                            formData.append('category_id', postForm.value.category_id);
                            formData.append('subcategory_id', postForm.value.subcategory_id || '');
                            formData.append('status', postForm.value.status);
                            formData.append('scheduled_at', postForm.value.scheduled_at);
                            formData.append('language', postForm.value.language);
                            
                            if (postForm.value.image) {
                                formData.append('image', postForm.value.image);
                            }
                            
                            if (editingPost.value) {
                                formData.append('id', editingPost.value.id);
                            }
                            
                            const endpoint = editingPost.value ? 
                                './backend/update_post.php' : 
                                './backend/create_post.php';
                            
                            const response = await fetch(endpoint, {
                                method: 'POST',
                                body: formData
                            });
                            
                            const result = await response.json();
                            
                            if (result.success) {
                                alert(editingPost.value ? 'Article updated successfully!' : 'Article created successfully!');
                                showPostModal.value = false;
                                resetPostForm();
                                editingPost.value = null;
                               
                            } else {
                                alert('Error: ' + (result.message || 'Failed to save article'));
                            }
                        } catch (error) {
                            console.error('Error saving post:', error);
                            alert('Error saving article. Please try again.');
                        } finally {
                            isSubmittingPost.value = false;
                        }
                    };
                    
                    // Search and filter states
                    const postSearch = ref('');
                    const postFilter = ref('all');
                    const userSearch = ref('');
                    const userRoleFilter = ref('all');
                    
                    // Stats data
                    const stats = ref({
                        totalPosts: 0,
                        totalViews: 0,
                        totalLikes: 0,
                        totalShares: 0,
                        totalSaves: 0,
                        totalUsers: 0,
                        verifiedUsers: 0,
                        avgLikesPerPost: 0,
                        avgViewsPerPost: 0,
                        verificationRate: 0
                    });
                    
                    const isLoadingStats = ref(false);
                    const statsError = ref(null);
                    
                    // Function to format numbers with commas
                    const formatNumber = (num) => {
                        if (num === null || num === undefined) return '0';
                        return new Intl.NumberFormat().format(num);
                    };
                    
                    // Function to fetch stats from API
                    const fetchStats = async () => {
                        isLoadingStats.value = true;
                        statsError.value = null;
                        
                        try {
                            const response = await fetch('./backend/get_dashboard_stats.php', {
                                method: 'GET',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json'
                                }
                            });
                            
                            if (!response.ok) {
                                throw new Error(`HTTP error! status: ${response.status}`);
                            }
                            
                            const result = await response.json();
                            
                            if (result.success && result.data) {
                                stats.value = {
                                    ...stats.value,
                                    ...result.data
                                };
                                console.log('Stats fetched successfully:', result);
                            } else {
                                throw new Error(result.error || 'Failed to fetch statistics');
                            }
                        } catch (error) {
                            console.error('Error fetching stats:', error);
                            statsError.value = error.message;
                            
                            // Fallback to sample data if API fails
                            stats.value = {
                                totalPosts: 156,
                                totalViews: 28947,
                                totalLikes: 4523,
                                totalShares: 892,
                                totalSaves: 234,
                                totalUsers: 2347,
                                verifiedUsers: 1876,
                                avgLikesPerPost: 29.0,
                                avgViewsPerPost: 185.6,
                                verificationRate: 79.92
                            };
                        } finally {
                            isLoadingStats.value = false;
                            // Re-render feather icons after data update
                            setTimeout(() => feather.replace(), 100);
                        }
                    };
                    

                    const fetchCategories = async () => {
                        if (categories.value.length > 0) {
                           
                            return;
                        }
                        
                        isLoadingCategories.value = true;
                        categoriesError.value = null;
                        
                        try {
                            const response = await fetch('./backend/get_categories.php', {
                                method: 'GET',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json'
                                }
                            });
                          
                            if (!response.ok) {
                                throw new Error(`HTTP error! status: ${response.status}`);
                            }
                            
                            const result = await response.json();
                       
                            if (result.success && result.data) {
                                categories.value = result.data;
                                console.log('Categories fetched successfully:', result);
                            } else {
                                throw new Error(result.error || 'Failed to fetch categories');
                            }
                        } catch (error) {
                            console.error('Error fetching categories:', error);
                            categoriesError.value = error.message;
                            
                            
                            categories.value = [
                                { id: 1, name: 'Technology', subcategories: [{ id: 1, name: 'AI & ML' }, { id: 2, name: 'Mobile Tech' }, { id: 3, name: 'Startups' }] },
                                { id: 2, name: 'Business', subcategories: [{ id: 4, name: 'Markets' }, { id: 5, name: 'Economy' }, { id: 6, name: 'Finance' }] },
                                { id: 3, name: 'Sports', subcategories: [{ id: 7, name: 'Cricket' }, { id: 8, name: 'Football' }, { id: 9, name: 'Olympics' }] },
                                { id: 4, name: 'Politics', subcategories: [{ id: 10, name: 'Elections' }, { id: 11, name: 'Policy' }] },
                                { id: 5, name: 'Health', subcategories: [{ id: 12, name: 'Medical' }, { id: 13, name: 'Wellness' }] }
                            ];
                        } finally {
                            isLoadingCategories.value = false;
                        }
                    };
                    
                    // Views Analytics
                    const viewsAnalytics = ref({
                        period: 'monthly',
                        total_views: 0,
                        chart_data: {
                            labels: [],
                            datasets: []
                        },
                        summary: {
                            current_period: 0,
                            previous_period: 0,
                            growth_rate: 0
                        }
                    });
                    
                    const isLoadingViews = ref(false);
                    let viewsChart = null;
                    
                    // Function to fetch views analytics
                    const fetchViewsAnalytics = async (period = 'monthly') => {
                        isLoadingViews.value = true;
                        
                        try {
                            const response = await fetch(`./backend/get_views_analytics.php?period=${period}`, {
                                method: 'GET',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json'
                                }
                            });
                            
                            if (!response.ok) {
                                throw new Error(`HTTP error! status: ${response.status}`);
                            }
                            
                            const result = await response.json();
                            
                            if (result.success && result.data) {
                                viewsAnalytics.value = result.data;
                                updateViewsChart();
                                console.log('Views analytics fetched:', result);
                            } else {
                                throw new Error(result.error || 'Failed to fetch views analytics');
                            }
                        } catch (error) {
                            console.error('Error fetching views analytics:', error);
                            // Use fallback data
                            viewsAnalytics.value = {
                                period: period,
                                total_views: stats.value.totalViews || 0,
                                chart_data: {
                                    labels: period === 'weekly' ? 
                                        ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] :
                                        period === 'yearly' ? 
                                        ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'] :
                                        ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
                                    datasets: [{
                                        label: 'Views',
                                        data: period === 'weekly' ? 
                                            [150, 280, 320, 410, 380, 450, 520] :
                                            period === 'yearly' ?
                                            [2800, 3200, 3800, 4200, 4800, 5200, 4900, 5600, 6100, 5800, 6400, 7200] :
                                            [1200, 1580, 1820, 2100],
                                        borderColor: '#0ea5e9',
                                        backgroundColor: 'rgba(14, 165, 233, 0.1)',
                                        borderWidth: 2,
                                        tension: 0.4,
                                        fill: true
                                    }]
                                },
                                summary: {
                                    current_period: 2100,
                                    previous_period: 1820,
                                    growth_rate: 15.4
                                }
                            };
                            updateViewsChart();
                        } finally {
                            isLoadingViews.value = false;
                        }
                    };
                    
                    // Function to update views chart
                    const updateViewsChart = () => {
                        if (viewsChart) {
                            viewsChart.data.labels = viewsAnalytics.value.chart_data.labels;
                            viewsChart.data.datasets = viewsAnalytics.value.chart_data.datasets;
                            viewsChart.update();
                        }
                    };
                    
                    // Function to change views period
                    const changeViewsPeriod = (period) => {
                        viewsAnalytics.value.period = period;
                        fetchViewsAnalytics(period);
                    };
                    
                    // Handle window resize
                    const handleResize = () => {
                        isMobile.value = window.innerWidth < 768;
                        if (!isMobile.value) {
                            sidebarOpen.value = false;
                        }
                    };
                    
                    const toggleSidebar = () => {
                        sidebarOpen.value = !sidebarOpen.value;
                    };
                    
                    const setCurrentPage = (page) => {
                        currentPage.value = page;
                        if (isMobile.value) {
                            sidebarOpen.value = false;
                        }
                        if (page === 'categories') {
                            fetchCategories();
                        }
                        // Load analytics when analytics page is accessed
                        if (page === 'analytics') {
                            fetchAnalytics();
                        }
                    };
                    
                    // Computed properties
                    const filteredPosts = computed(() => {
                        let filtered = posts.value;
                        
                        if (postSearch.value) {
                            filtered = filtered.filter(post => 
                                post.title.toLowerCase().includes(postSearch.value.toLowerCase()) ||
                                post.author.toLowerCase().includes(postSearch.value.toLowerCase())
                            );
                        }
                        
                        if (postFilter.value !== 'all') {
                            filtered = filtered.filter(post => post.status.toLowerCase() === postFilter.value);
                        }
                        
                        return filtered;
                    });
                    
                    const filteredUsers = computed(() => {
                        let filtered = users.value;
                        
                        if (userSearch.value) {
                            filtered = filtered.filter(user => 
                                user.name.toLowerCase().includes(userSearch.value.toLowerCase()) ||
                                user.email.toLowerCase().includes(userSearch.value.toLowerCase())
                            );
                        }
                        
                        if (userRoleFilter.value !== 'all') {
                            filtered = filtered.filter(user => user.role === userRoleFilter.value);
                        }
                        
                        return filtered;
                    });
                    
                    // Utility methods
                    const getStatusClass = (status) => {
                        const classes = {
                            'Published': 'px-2 py-1 text-xs font-medium bg-green-500 bg-opacity-10 text-green-400 rounded-full',
                            'Draft': 'px-2 py-1 text-xs font-medium bg-yellow-500 bg-opacity-10 text-yellow-400 rounded-full',
                            'Scheduled': 'px-2 py-1 text-xs font-medium bg-blue-500 bg-opacity-10 text-blue-400 rounded-full'
                        };
                        return classes[status] || 'px-2 py-1 text-xs font-medium bg-gray-500 bg-opacity-10 text-gray-400 rounded-full';
                    };
                    
                    const getRoleClass = (role) => {
                        const classes = {
                            'admin': 'px-2 py-1 text-xs font-medium bg-red-500 bg-opacity-10 text-red-400 rounded-full',
                            'editor': 'px-2 py-1 text-xs font-medium bg-blue-500 bg-opacity-10 text-blue-400 rounded-full',
                            'contributor': 'px-2 py-1 text-xs font-medium bg-green-500 bg-opacity-10 text-green-400 rounded-full',
                            'viewer': 'px-2 py-1 text-xs font-medium bg-purple-500 bg-opacity-10 text-purple-400 rounded-full'
                        };
                        return classes[role] || 'px-2 py-1 text-xs font-medium bg-gray-500 bg-opacity-10 text-gray-400 rounded-full';
                    };
                    
                    const getCategoryName = (categoryId) => {
                        const cat = categories.value.find(c => c.id == categoryId);
                        return cat ? cat.name : 'Unknown';
                    };
                    
                    // CRUD methods
                    const viewPost = (post) => {
                        alert(`Viewing post: ${post.title}`);
                    };
                    
                    const editPost = (post) => {
                        editingPost.value = { ...post };
                        postForm.value = {
                            title: post.title || '',
                            description: post.description || '',
                            content: post.content || '',
                            category_id: post.category_id || '',
                            subcategory_id: post.subcategory_id || '',
                            status: post.status || 'draft',
                            scheduled_at: post.scheduled_at || '',
                            image: null,
                            imageName: post.image ? 'Current image' : '',
                            language: post.language || 'english',
                        };
                        
                        // Load subcategories for the selected category
                        selectedCategorySubcategories.value = [];
                        if (post.category_id) {
                            const selectedCategory = categories.value.find(cat => cat.id == post.category_id);
                            if (selectedCategory && selectedCategory.subcategories) {
                                selectedCategorySubcategories.value = selectedCategory.subcategories;
                            }
                        }
                        
                        showPostModal.value = true;
                    };
                    
                    const deletePost = async (postId) => {
                        if (!confirm('Are you sure you want to delete this post?')) return;
                        try {
                            const response = await fetch('./backend/delete_post.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({ id: postId })
                            });
                            const result = await response.json();
                            if (result.success) {
                                posts.value = posts.value.filter(p => p.id !== postId);
                                alert('Post deleted successfully!');
                            } else {
                                alert(result.error || 'Failed to delete post.');
                            }
                        } catch (error) {
                            alert('Error deleting post. Please try again.');
                        }
                    };
                    
                    // Users API integration
                    const users = ref([]);
                    const isLoadingUsers = ref(false);
                    const usersError = ref(null);
                    
                    const fetchUsers = async () => {
                        isLoadingUsers.value = true;
                        usersError.value = null;
                        try {
                            const response = await fetch('./backend/users/get_users.php', {
                                method: 'GET',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json'
                                }
                            });
                            const result = await response.json();
                            if (result.success && result.data) {
                                users.value = result.data;
                            } else {
                                throw new Error(result.error || 'Failed to fetch users');
                            }
                        } catch (error) {
                            usersError.value = error.message;
                            users.value = [];
                        } finally {
                            isLoadingUsers.value = false;
                        }
                    };
                    
                    const editUser = (user) => {
                        editingUser.value = { ...user };
                        showUserModal.value = true;
                    };
                    
                    const saveUser = async () => {
                        if (!editingUser.value) return;
                        try {
                            const formData = new FormData();
                            Object.keys(editingUser.value).forEach(key => {
                                formData.append(key, editingUser.value[key]);
                            });
                            const response = await fetch('./backend/users/update_user.php', {
                                method: 'POST',
                                body: formData
                            });
                            const result = await response.json();
                            if (result.success) {
                                alert('User updated successfully!');
                                showUserModal.value = false;
                                fetchUsers();
                            } else {
                                alert(result.error || 'Failed to update user');
                            }
                        } catch (error) {
                            alert('Error updating user. Please try again.');
                        }
                    };
                    
                    const deleteUser = async (userId) => {
                        if (!confirm('Are you sure you want to delete this user?')) return;
                        try {
                            const response = await fetch('./backend/users/delete_user.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({ id: userId })
                            });
                            const result = await response.json();
                            if (result.success) {
                                alert('User deleted successfully!');
                                fetchUsers();
                            } else {
                                alert(result.error || 'Failed to delete user');
                            }
                        } catch (error) {
                            alert('Error deleting user. Please try again.');
                        }
                    };
                    
                    const pageTitles = {
                        dashboard: 'FIM News Dashboard',
                        posts: 'News Articles Management',
                        users: 'Users & Subscribers Management',
                        categories: 'News Categories Management',
                        analytics: 'News Analytics & Insights',
                        settings: 'FIM News Settings'
                    };
                    
                    onMounted(() => {
                        feather.replace();
                        fetchStats();
                        fetchPosts();
                        fetchUsers();
                        fetchCategories();
                        fetchAnalytics();
                        window.addEventListener('resize', handleResize);
                        setTimeout(() => feather.replace(), 100);
                        setTimeout(() => {
                            // Chart.js initialization
                            const viewsCtx = document.getElementById('viewsChart');
                            if (viewsCtx) {
                                if (viewsChart) viewsChart.destroy();
                                viewsChart = new Chart(viewsCtx, {
                                    type: 'line',
                                    data: viewsAnalytics.value.chart_data,
                                    options: {
                                        responsive: true,
                                        plugins: {
                                            legend: { display: true },
                                            title: { display: false }
                                        },
                                        scales: {
                                            y: { beginAtZero: true }
                                        }
                                    }
                                });
                            }

                            // Categories Chart
                            const categoriesCtx = document.getElementById('categoriesChart');
                            if (categoriesCtx) {
                                if (window.categoriesChart) window.categoriesChart.destroy();
                                window.categoriesChart = new Chart(categoriesCtx, {
                                    type: 'bar',
                                    data: {
                                        labels: categories.value.map(c => c.name),
                                        datasets: [{
                                            label: 'Posts',
                                            data: categories.value.map(c => (c.posts_count || 0)),
                                            backgroundColor: '#6366f1',
                                        }]
                                    },
                                    options: {
                                        responsive: true,
                                        plugins: {
                                            legend: { display: false },
                                            title: { display: false }
                                        },
                                        scales: {
                                            y: { beginAtZero: true }
                                        }
                                    }
                                });
                            }

                            // Posts Per Month Chart
                            const postsCtx = document.getElementById('postsChart');
                            if (postsCtx) {
                                if (window.postsChart) window.postsChart.destroy();
                                window.postsChart = new Chart(postsCtx, {
                                    type: 'bar',
                                    data: {
                                        labels: analytics.value && analytics.value.postsPerMonth && analytics.value.postsPerMonth.labels ? analytics.value.postsPerMonth.labels : [],
                                        datasets: [{
                                            label: 'Posts',
                                            data: analytics.value && analytics.value.postsPerMonth && analytics.value.postsPerMonth.data ? analytics.value.postsPerMonth.data : [],
                                            backgroundColor: '#0ea5e9',
                                        }]
                                    },
                                    options: {
                                        responsive: true,
                                        plugins: {
                                            legend: { display: false },
                                            title: { display: false }
                                        },
                                        scales: {
                                            y: { beginAtZero: true }
                                        }
                                    }
                                });
                            }

                            // Engagement Chart
                            const engagementCtx = document.getElementById('engagementChart');
                            if (engagementCtx) {
                                if (window.engagementChart) window.engagementChart.destroy();
                                window.engagementChart = new Chart(engagementCtx, {
                                    type: 'line',
                                    data: {
                                        labels: analytics.value && analytics.value.engagement && analytics.value.engagement.labels ? analytics.value.engagement.labels : [],
                                        datasets: analytics.value && analytics.value.engagement && analytics.value.engagement.datasets ? analytics.value.engagement.datasets : []
                                    },
                                    options: {
                                        responsive: true,
                                        plugins: {
                                            legend: { display: true },
                                            title: { display: false }
                                        },
                                        scales: {
                                            y: { beginAtZero: true }
                                        }
                                    }
                                });
                            }
                        }, 500);

                        // Watch analytics and update charts when data changes
                        watch(analytics, (newVal) => {
                            // Posts Per Month Chart
                            const postsCtx = document.getElementById('postsChart');
                            if (postsCtx) {
                                if (window.postsChart) window.postsChart.destroy();
                                window.postsChart = new Chart(postsCtx, {
                                    type: 'bar',
                                    data: {
                                        labels: newVal && newVal.postsPerMonth && newVal.postsPerMonth.labels ? newVal.postsPerMonth.labels : [],
                                        datasets: [{
                                            label: 'Posts',
                                            data: newVal && newVal.postsPerMonth && newVal.postsPerMonth.data ? newVal.postsPerMonth.data : [],
                                            backgroundColor: '#0ea5e9',
                                        }]
                                    },
                                    options: {
                                        responsive: true,
                                        plugins: {
                                            legend: { display: false },
                                            title: { display: false }
                                        },
                                        scales: {
                                            y: { beginAtZero: true }
                                        }
                                    }
                                });
                            }
                            // Engagement Chart
                            const engagementCtx = document.getElementById('engagementChart');
                            if (engagementCtx) {
                                if (window.engagementChart) window.engagementChart.destroy();
                                window.engagementChart = new Chart(engagementCtx, {
                                    type: 'line',
                                    data: {
                                        labels: newVal && newVal.engagement && newVal.engagement.labels ? newVal.engagement.labels : [],
                                        datasets: newVal && newVal.engagement && newVal.engagement.datasets ? newVal.engagement.datasets : []
                                    },
                                    options: {
                                        responsive: true,
                                        plugins: {
                                            legend: { display: true },
                                            title: { display: false }
                                        },
                                        scales: {
                                            y: { beginAtZero: true }
                                        }
                                    }
                                });
                            }
                        }, 500);
                    });

                    // Watch analytics and update charts when data changes
                    watch(analytics, (newVal) => {
                        // Posts Per Month Chart
                        const postsCtx = document.getElementById('postsChart');
                        if (postsCtx) {
                            if (window.postsChart) window.postsChart.destroy();
                            window.postsChart = new Chart(postsCtx, {
                                type: 'bar',
                                data: {
                                    labels: newVal && newVal.postsPerMonth && newVal.postsPerMonth.labels ? newVal.postsPerMonth.labels : [],
                                    datasets: [{
                                        label: 'Posts',
                                        data: newVal && newVal.postsPerMonth && newVal.postsPerMonth.data ? newVal.postsPerMonth.data : [],
                                        backgroundColor: '#0ea5e9',
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    plugins: {
                                        legend: { display: false },
                                        title: { display: false }
                                    },
                                    scales: {
                                        y: { beginAtZero: true }
                                    }
                                }
                            });
                        }
                        // Engagement Chart
                        const engagementCtx = document.getElementById('engagementChart');
                        if (engagementCtx) {
                            if (window.engagementChart) window.engagementChart.destroy();
                            window.engagementChart = new Chart(engagementCtx, {
                                type: 'line',
                                data: {
                                    labels: newVal && newVal.engagement && newVal.engagement.labels ? newVal.engagement.labels : [],
                                    datasets: newVal && newVal.engagement && newVal.engagement.datasets ? newVal.engagement.datasets : []
                                },
                                options: {
                                    responsive: true,
                                    plugins: {
                                        legend: { display: true },
                                        title: { display: false }
                                    },
                                    scales: {
                                        y: { beginAtZero: true }
                                    }
                                }
                            });
                        }
                    }, { deep: true });
                    
                    return {
                        fetchAnalytics,
                        saveCategory,
                        fetchPosts,
                        sidebarOpen,
                        currentPage,
                        searchQuery,
                        searchFocused,
                        isMobile,
                        toggleSidebar,
                        setCurrentPage,
                        pageTitles,
                        
                        // Data
                        stats,
                        posts,
                        users,
                        categories,
                        analytics,
                        settings,
                        recentPosts,
                        recentActivities,
                        
                        // API States
                        isLoadingStats,
                        statsError,
                        isLoadingViews,
                        isLoadingCategories,
                        categoriesError,
                        isLoadingUsers,
                        usersError,
                        
                        // Analytics Data
                        viewsAnalytics,
                        
                        // API Functions
                        fetchStats,
                        fetchCategories,
                        fetchViewsAnalytics,
                        changeViewsPeriod,
                        formatNumber,
                        
                        // Modal states
                        showPostModal,
                        showUserModal,
                        showCategoryModal,
                        editingPost,
                        editingUser,
                        editingCategory,
                        showPostDetailsModal,
                        
                        // Post Details
                        postDetails,
                        openPostDetails,
                        
                        // Post Form
                        postForm,
                        isSubmittingPost,
                        selectedCategorySubcategories,
                        resetPostForm,
                        onCategoryChange,
                        handleImageUpload,
                        savePost,
                        
                        // Search & filter
                        postSearch,
                        postFilter,
                        userSearch,
                        userRoleFilter,
                        filteredPosts,
                        filteredUsers,
                        
                        // Methods
                        getStatusClass,
                        getRoleClass,
                        viewPost,
                        editPost,
                        deletePost,
                        editUser,
                        saveUser,
                        deleteUser,
                        toggleUserStatus,
                        editCategory,
                        deleteCategory,
                        saveSettings,
                        getCategoryName
                    };
                }
            }).mount('#app');
        </script>
</body>
</html>