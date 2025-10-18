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
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Post</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Category</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Stats</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-300 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-gray-800 divide-y divide-gray-700">
                                    <tr v-for="post in filteredPosts" :key="post.id" class="hover:bg-gray-700">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center">
                                                <div class="h-12 w-12 flex-shrink-0">
                                                    <img class="h-12 w-12 rounded-lg object-cover" :src="post.image" :alt="post.title">
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-white">{{ post.title }}</div>
                                                    <div class="text-sm text-gray-400">{{ post.author }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 text-xs font-medium bg-purple-500 bg-opacity-10 text-purple-400 rounded-full">
                                                {{ post.category }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="getStatusClass(post.status)">
                                                {{ post.status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">
                                            <div class="flex space-x-4">
                                                <span class="flex items-center">
                                                    <i data-feather="eye" class="w-4 h-4 mr-1"></i>
                                                    {{ post.views }}
                                                </span>
                                                <span class="flex items-center">
                                                    <i data-feather="heart" class="w-4 h-4 mr-1"></i>
                                                    {{ post.likes }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">
                                            {{ post.date }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <button @click="viewPost(post)" class="text-blue-400 hover:text-blue-300 mr-3">
                                                <i data-feather="eye" class="w-4 h-4"></i>
                                            </button>
                                            <button @click="editPost(post)" class="text-primary hover:text-primary-dark mr-3">
                                                <i data-feather="edit" class="w-4 h-4"></i>
                                            </button>
                                            <button @click="deletePost(post.id)" class="text-red-400 hover:text-red-300">
                                                <i data-feather="trash-2" class="w-4 h-4"></i>
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
                            <select v-model="userRoleFilter" class="bg-gray-700 text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                                <option value="all">All Roles</option>
                                <option value="admin">Admin</option>
                                <option value="editor">Editor</option>
                                <option value="contributor">Contributor</option>
                                <option value="viewer">Subscriber</option>
                            </select>
                        </div>
                    </div>

                    <!-- Users Table -->
                    <div class="bg-gray-800 rounded-xl shadow-lg overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-700">
                                <thead class="bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">User</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Role</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Joined</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Posts</th>
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
                                            <span :class="getRoleClass(user.role)">
                                                {{ user.role }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="user.status === 'active' ? 'px-2 py-1 text-xs font-medium bg-green-500 bg-opacity-10 text-green-400 rounded-full' : 'px-2 py-1 text-xs font-medium bg-red-500 bg-opacity-10 text-red-400 rounded-full'">
                                                {{ user.status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">
                                            {{ user.joinedDate }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">
                                            {{ user.postsCount }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <button @click="editUser(user)" class="text-primary hover:text-primary-dark mr-3">
                                                <i data-feather="edit" class="w-4 h-4"></i>
                                            </button>
                                            <button @click="toggleUserStatus(user)" :class="user.status === 'active' ? 'text-red-400 hover:text-red-300' : 'text-green-400 hover:text-green-300'">
                                                <i :data-feather="user.status === 'active' ? 'user-x' : 'user-check'" class="w-4 h-4"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Categories Management Section -->
                <div v-if="currentPage === 'categories'">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold text-white">Categories Management</h2>
                        <button @click="showCategoryModal = true; editingCategory = null" class="bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-lg flex items-center">
                            <i data-feather="folder-plus" class="w-4 h-4 mr-2"></i>
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
                                        <i data-feather="folder" class="w-6 h-6 text-purple-400"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-semibold text-white">{{ category.name }}</h3>
                                        <p class="text-sm text-gray-400">{{ category.postsCount }} posts</p>
                                    </div>
                                </div>
                                <div class="flex space-x-2">
                                    <button @click="editCategory(category)" class="text-primary hover:text-primary-dark">
                                        <i data-feather="edit" class="w-4 h-4"></i>
                                    </button>
                                    <button @click="deleteCategory(category.id)" class="text-red-400 hover:text-red-300">
                                        <i data-feather="trash-2" class="w-4 h-4"></i>
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
                                    <h3 class="text-xl font-bold mt-1 text-white">{{ analytics.mostViewed.title }}</h3>
                                    <p class="text-primary text-sm mt-1">{{ analytics.mostViewed.views }} views</p>
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
                                    <h3 class="text-xl font-bold mt-1 text-white">{{ analytics.mostLiked.title }}</h3>
                                    <p class="text-green-400 text-sm mt-1">{{ analytics.mostLiked.likes }} likes</p>
                                </div>
                                <div class="w-12 h-12 rounded-full bg-green-500 bg-opacity-10 flex items-center justify-center">
                                    <i data-feather="heart" class="w-6 h-6 text-green-400"></i>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-800 rounded-xl p-6 shadow-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-400 text-sm font-medium">Top Author</p>
                                    <h3 class="text-xl font-bold mt-1 text-white">{{ analytics.topAuthor.name }}</h3>
                                    <p class="text-purple-400 text-sm mt-1">{{ analytics.topAuthor.posts }} posts</p>
                                </div>
                                <div class="w-12 h-12 rounded-full bg-purple-500 bg-opacity-10 flex items-center justify-center">
                                    <i data-feather="award" class="w-6 h-6 text-purple-400"></i>
                                </div>
                            </div>
                        </div>
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

                <!-- Settings Section -->
                <div v-if="currentPage === 'settings'">
                    <h2 class="text-2xl font-bold text-white mb-6">Settings</h2>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- General Settings -->
                        <div class="bg-gray-800 rounded-xl p-6 shadow-lg">
                            <h3 class="text-lg font-semibold text-white mb-4">General Settings</h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">Site Name</label>
                                    <input v-model="settings.siteName" type="text" class="w-full bg-gray-700 text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">Site Description</label>
                                    <textarea v-model="settings.siteDescription" rows="3" class="w-full bg-gray-700 text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"></textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">Contact Email</label>
                                    <input v-model="settings.contactEmail" type="email" class="w-full bg-gray-700 text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                                </div>
                            </div>
                        </div>

                        <!-- Security Settings -->
                        <div class="bg-gray-800 rounded-xl p-6 shadow-lg">
                            <h3 class="text-lg font-semibold text-white mb-4">Security Settings</h3>
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-white font-medium">Two-Factor Authentication</p>
                                        <p class="text-gray-400 text-sm">Add an extra layer of security</p>
                                    </div>
                                    <button class="bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-lg text-sm">
                                        Enable
                                    </button>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-white font-medium">Session Timeout</p>
                                        <p class="text-gray-400 text-sm">Auto logout after inactivity</p>
                                    </div>
                                    <select class="bg-gray-700 text-white px-3 py-2 rounded-lg">
                                        <option value="30">30 minutes</option>
                                        <option value="60">1 hour</option>
                                        <option value="120">2 hours</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Save Settings Button -->
                    <div class="mt-6">
                        <button @click="saveSettings" class="bg-primary hover:bg-primary-dark text-white px-6 py-3 rounded-lg font-medium">
                            Save Settings
                        </button>
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

        <script>
            const { createApp, ref, onMounted, computed } = Vue;            createApp({
                setup() {
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
                        imageName: ''
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
                            imageName: ''
                        };
                        selectedCategorySubcategories.value = [];
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
                        
                        // Load categories when categories page is accessed
                        if (page === 'categories') {
                            fetchCategories();
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
                            imageName: post.image ? 'Current image' : ''
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
                    
                    const deletePost = (postId) => {
                        if (confirm('Are you sure you want to delete this post?')) {
                            posts.value = posts.value.filter(p => p.id !== postId);
                        }
                    };
                    
                    const editUser = (user) => {
                        editingUser.value = { ...user };
                        showUserModal.value = true;
                    };
                    
                    const toggleUserStatus = (user) => {
                        const userIndex = users.value.findIndex(u => u.id === user.id);
                        if (userIndex !== -1) {
                            users.value[userIndex].status = users.value[userIndex].status === 'active' ? 'inactive' : 'active';
                        }
                    };
                    
                    const editCategory = (category) => {
                        editingCategory.value = { ...category };
                        showCategoryModal.value = true;
                    };
                    
                    const deleteCategory = (categoryId) => {
                        if (confirm('Are you sure you want to delete this category?')) {
                            categories.value = categories.value.filter(c => c.id !== categoryId);
                        }
                    };
                    
                    const saveSettings = () => {
                        alert('Settings saved successfully!');
                    };
                    
                    const pageTitles = {
                        dashboard: 'FIM News Dashboard',
                        posts: 'News Articles Management',
                        users: 'Users & Subscribers Management',
                        categories: 'News Categories Management',
                        analytics: 'News Analytics & Insights',
                        settings: 'FIM News Settings'
                    };
                    
                    // Sample data
                    const posts = ref([
                        { id: 1, title: 'Breaking: Technology Market Shows Strong Growth in 2024', author: 'Rajesh Kumar', date: 'Oct 10, 2025', status: 'Published', views: 4567, likes: 234, category: 'Technology', image: 'https://via.placeholder.com/150x150?text=Tech+News' },
                        { id: 2, title: 'Local Business Innovation Awards Winners Announced', author: 'Priya Sharma', date: 'Oct 09, 2025', status: 'Published', views: 3245, likes: 189, category: 'Business', image: 'https://via.placeholder.com/150x150?text=Business' },
                        { id: 3, title: 'Sports Update: Cricket Tournament Finals This Weekend', author: 'Amit Singh', date: 'Oct 08, 2025', status: 'Published', views: 5432, likes: 367, category: 'Sports', image: 'https://via.placeholder.com/150x150?text=Sports' },
                        { id: 4, title: 'Health Ministry Issues New Guidelines for Winter Season', author: 'Dr. Sunita Patel', date: 'Oct 07, 2025', status: 'Draft', views: 0, likes: 0, category: 'Health', image: 'https://via.placeholder.com/150x150?text=Health' },
                        { id: 5, title: 'Education Sector Embraces Digital Learning Methods', author: 'Vikram Mehta', date: 'Oct 06, 2025', status: 'Published', views: 2876, likes: 145, category: 'Education', image: 'https://via.placeholder.com/150x150?text=Education' },
                        { id: 6, title: 'Entertainment Industry Gears Up for Festival Season', author: 'Kavya Reddy', date: 'Oct 05, 2025', status: 'Scheduled', views: 0, likes: 0, category: 'Entertainment', image: 'https://via.placeholder.com/150x150?text=Entertainment' }
                    ]);
                    
                    const users = ref([
                        { id: 1, name: 'Rajesh Kumar', email: 'rajesh@fimnews.com', role: 'admin', status: 'active', joinedDate: 'Jan 15, 2024', postsCount: 47 },
                        { id: 2, name: 'Priya Sharma', email: 'priya@fimnews.com', role: 'editor', status: 'active', joinedDate: 'Mar 20, 2024', postsCount: 32 },
                        { id: 3, name: 'Amit Singh', email: 'amit@fimnews.com', role: 'editor', status: 'active', joinedDate: 'May 10, 2024', postsCount: 28 },
                        { id: 4, name: 'Dr. Sunita Patel', email: 'sunita@fimnews.com', role: 'contributor', status: 'active', joinedDate: 'Jul 05, 2024', postsCount: 15 },
                        { id: 5, name: 'Vikram Mehta', email: 'vikram@fimnews.com', role: 'contributor', status: 'active', joinedDate: 'Aug 12, 2024', postsCount: 22 },
                        { id: 6, name: 'Kavya Reddy', email: 'kavya@fimnews.com', role: 'editor', status: 'active', joinedDate: 'Sep 01, 2024', postsCount: 18 }
                    ]);
                    
                    const categories = ref([]);
                    const isLoadingCategories = ref(false);
                    const categoriesError = ref(null);
                    
                    const analytics = ref({
                        mostViewed: { title: 'Breaking: Technology Market Shows Strong Growth in 2024', views: 4567 },
                        mostLiked: { title: 'Sports Update: Cricket Tournament Finals This Weekend', likes: 367 },
                        topAuthor: { name: 'Rajesh Kumar', posts: 47 }
                    });
                    
                    const settings = ref({
                        siteName: 'FIM News',
                        siteDescription: 'First In Market - Your trusted source for breaking news, business updates, technology trends, and comprehensive coverage across India',
                        contactEmail: 'admin@fimnews.com'
                    });
                    
                    const recentPosts = ref(posts.value.slice(0, 5));
                    
                    const recentActivities = ref([
                        { id: 1, action: 'Rajesh Kumar published "Breaking: Technology Market Shows Strong Growth"', time: '1 hour ago', icon: 'edit-2', color: 'blue' },
                        { id: 2, action: 'New subscriber from Mumbai registered', time: '2 hours ago', icon: 'user-plus', color: 'green' },
                        { id: 3, action: 'Priya Sharma updated Business news category', time: '3 hours ago', icon: 'folder', color: 'purple' },
                        { id: 4, action: 'Cricket news article got 500+ views in 30 minutes', time: '4 hours ago', icon: 'trending-up', color: 'yellow' },
                        { id: 5, action: 'Amit Singh scheduled sports tournament coverage', time: '5 hours ago', icon: 'calendar', color: 'blue' },
                        { id: 6, action: 'Daily newsletter sent to 15,000+ subscribers', time: '6 hours ago', icon: 'mail', color: 'green' }
                    ]);
                    
                    onMounted(() => {
                        feather.replace();
                        
                        // Fetch dashboard statistics
                        fetchStats();
                        
                        // Add event listener for window resize
                        window.addEventListener('resize', handleResize);
                        
                        // Re-replace icons after any DOM changes if needed
                        setTimeout(() => feather.replace(), 100);
                        
                        // Initialize charts after DOM is ready
                        setTimeout(() => {
                            // Initialize charts
                            const viewsCtx = document.getElementById('viewsChart')?.getContext('2d');
                            if (viewsCtx) {
                                viewsChart = new Chart(viewsCtx, {
                                    type: 'line',
                                    data: {
                                        labels: [],
                                        datasets: []
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        plugins: {
                                            legend: {
                                                display: false
                                            },
                                            tooltip: {
                                                backgroundColor: 'rgba(17, 24, 39, 0.8)',
                                                titleColor: 'rgba(255, 255, 255, 0.9)',
                                                bodyColor: 'rgba(255, 255, 255, 0.9)',
                                                borderColor: 'rgba(59, 130, 246, 0.5)',
                                                borderWidth: 1,
                                                callbacks: {
                                                    label: function(context) {
                                                        return `Views: ${formatNumber(context.parsed.y)}`;
                                                    }
                                                }
                                            }
                                        },
                                        scales: {
                                            y: {
                                                beginAtZero: true,
                                                grid: {
                                                    color: 'rgba(255, 255, 255, 0.05)'
                                                },
                                                ticks: {
                                                    color: 'rgba(255, 255, 255, 0.6)',
                                                    callback: function(value) {
                                                        return formatNumber(value);
                                                    }
                                                }
                                            },
                                            x: {
                                                grid: {
                                                    display: false
                                                },
                                                ticks: {
                                                    color: 'rgba(255, 255, 255, 0.6)'
                                                }
                                            }
                                        }
                                    }
                                });
                                
                                // Fetch views analytics data
                                fetchViewsAnalytics('monthly');
                            }
                            
                            const categoriesCtx = document.getElementById('categoriesChart')?.getContext('2d');
                            if (categoriesCtx) {
                                new Chart(categoriesCtx, {
                                    type: 'doughnut',
                                    data: {
                                        labels: ['Technology', 'Business', 'Design', 'Marketing', 'Other'],
                                        datasets: [{
                                            data: [35, 25, 20, 15, 5],
                                            backgroundColor: [
                                                '#0ea5e9',
                                                '#3b82f6',
                                                '#8b5cf6',
                                                '#10b981',
                                                '#f59e0b'
                                            ],
                                            borderWidth: 0
                                        }]
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        cutout: '70%',
                                        plugins: {
                                            legend: {
                                                position: 'right',
                                                labels: {
                                                    color: 'rgba(255, 255, 255, 0.7)'
                                                }
                                            }
                                        }
                                    }
                                });
                            }
                        }, 500);
                    });
                    
                    return {
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
                        toggleUserStatus,
                        editCategory,
                        deleteCategory,
                        saveSettings
                    };
                }
            }).mount('#app');
        </script>
</body>
</html>