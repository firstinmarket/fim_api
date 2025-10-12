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
        <aside class="w-64 bg-gray-800 border-r border-gray-700 flex-shrink-0 transition-all duration-300 ease-in-out" :class="{'hidden': !sidebarOpen && !isMobile, 'block': sidebarOpen || !isMobile}" v-show="!isMobile || sidebarOpen">
            <div class="p-4 flex items-center space-x-3">
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary to-secondary flex items-center justify-center">
                    <i data-feather="zap" class="w-5 h-5 text-white"></i>
                </div>
                <h1 class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-primary to-secondary">
                    FIM News Dashboard
                </h1>
            </div>
            <nav class="mt-8 px-2 space-y-1">
                <a 
                    href="#" 
                    @click.prevent="currentPage = 'dashboard'"
                    :class="['sidebar-link flex items-center px-4 py-3 rounded-lg group', currentPage === 'dashboard' ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700']">
                    <i data-feather="home" class="sidebar-icon w-5 h-5 mr-3 text-primary"></i>
                    Dashboard
                </a>
                <a 
                    href="#" 
                    @click.prevent="currentPage = 'posts'"
                    :class="['sidebar-link flex items-center px-4 py-3 rounded-lg group', currentPage === 'posts' ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700']">
                    <i data-feather="file-text" class="sidebar-icon w-5 h-5 mr-3 text-blue-400"></i>
                    Posts
                </a>
                <a 
                    href="#" 
                    @click.prevent="currentPage = 'users'"
                    :class="['sidebar-link flex items-center px-4 py-3 rounded-lg group', currentPage === 'users' ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700']">
                    <i data-feather="users" class="sidebar-icon w-5 h-5 mr-3 text-green-400"></i>
                    Users
                </a>
                <a 
                    href="#" 
                    @click.prevent="currentPage = 'categories'"
                    :class="['sidebar-link flex items-center px-4 py-3 rounded-lg group', currentPage === 'categories' ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700']">
                    <i data-feather="folder" class="sidebar-icon w-5 h-5 mr-3 text-purple-400"></i>
                    Categories
                </a>
                <a 
                    href="#" 
                    @click.prevent="currentPage = 'analytics'"
                    :class="['sidebar-link flex items-center px-4 py-3 rounded-lg group', currentPage === 'analytics' ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700']">
                    <i data-feather="bar-chart-2" class="sidebar-icon w-5 h-5 mr-3 text-yellow-400"></i>
                    Analytics
                </a>
                <a 
                    href="#" 
                    @click.prevent="currentPage = 'settings'"
                    :class="['sidebar-link flex items-center px-4 py-3 rounded-lg group', currentPage === 'settings' ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700']">
                    <i data-feather="settings" class="sidebar-icon w-5 h-5 mr-3 text-gray-400"></i>
                    Settings
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Mobile Header -->
            <header class="md:hidden bg-gray-800 border-b border-gray-700 p-4 flex items-center justify-between">
                <button @click="toggleSidebar" class="text-gray-400 hover:text-white transition-colors">
                    <i data-feather="menu" class="w-6 h-6"></i>
                </button>
                <h1 class="text-lg font-bold bg-clip-text text-transparent bg-gradient-to-r from-primary to-secondary">
                    FIM News Dashboard
                </h1>
                <div class="w-6"></div> <!-- Spacer -->
            </header>

            <!-- Desktop Header -->
            <header class="hidden md:flex items-center justify-between bg-gray-800 border-b border-gray-700 p-4">
                <div class="flex items-center space-x-4">
                    <button @click="toggleSidebar" class="text-gray-400 hover:text-white transition-colors mr-4">
                        <i data-feather="menu" class="w-5 h-5"></i>
                    </button>
                    <h2 class="text-xl font-semibold" :class="{
                        'text-primary': currentPage === 'dashboard',
                        'text-blue-400': currentPage === 'posts',
                        'text-green-400': currentPage === 'users',
                        'text-purple-400': currentPage === 'categories',
                        'text-yellow-400': currentPage === 'analytics',
                        'text-gray-400': currentPage === 'settings'
                    }">
                        {{ pageTitles[currentPage] || 'Dashboard Overview' }}
                    </h2>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="relative">
                        <i data-feather="search" class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4"></i>
                        <input 
                            type="text" 
                            placeholder="Search..." 
                            v-model="searchQuery"
                            class="bg-gray-700 text-white pl-10 pr-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200 w-64"
                            @focus="searchFocused = true"
                            @blur="searchFocused = false"
                        >
                    </div>
                    <button class="relative p-1 rounded-full text-gray-400 hover:text-white hover:bg-gray-700">
                        <i data-feather="bell" class="w-5 h-5"></i>
                        <span class="absolute top-0 right-0 w-2 h-2 bg-red-500 rounded-full"></span>
                    </button>
                    <div class="flex items-center space-x-2 cursor-pointer">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-primary to-secondary flex items-center justify-center">
                            <i data-feather="user" class="w-4 h-4 text-white"></i>
                        </div>
                        <span class="text-sm font-medium">Admin</span>
                    </div>
                </div>
            </header>

            <!-- Mobile Sidebar Overlay -->
            <transition name="fade">
                <div v-if="sidebarOpen && isMobile" @click="toggleSidebar" class="fixed inset-0 bg-black bg-opacity-50 z-40 md:hidden"></div>
            </transition>
            <transition name="slide-left">
                <aside v-if="sidebarOpen && isMobile" class="fixed inset-y-0 left-0 w-64 bg-gray-800 z-50 shadow-xl transform md:hidden">
                    <div class="p-4 flex items-center justify-between border-b border-gray-700">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-primary to-secondary flex items-center justify-center">
                                <i data-feather="zap" class="w-4 h-4 text-white"></i>
                            </div>
                            <h1 class="text-lg font-bold bg-clip-text text-transparent bg-gradient-to-r from-primary to-secondary">
                                FIM News Dashboard
                            </h1>
                        </div>
                        <button @click="toggleSidebar" class="text-gray-400 hover:text-white transition-colors">
                            <i data-feather="x" class="w-5 h-5"></i>
                        </button>
                    </div>
                    <nav class="mt-4 px-2 space-y-1">
                        <a 
                            href="#" 
                            @click.prevent="setCurrentPage('dashboard')"
                            :class="['sidebar-link flex items-center px-4 py-3 rounded-lg group', currentPage === 'dashboard' ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700']">
                            <i data-feather="home" class="sidebar-icon w-5 h-5 mr-3 text-primary"></i>
                            Dashboard
                        </a>
                        <a 
                            href="#" 
                            @click.prevent="setCurrentPage('posts')"
                            :class="['sidebar-link flex items-center px-4 py-3 rounded-lg group', currentPage === 'posts' ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700']">
                            <i data-feather="file-text" class="sidebar-icon w-5 h-5 mr-3 text-blue-400"></i>
                            Posts
                        </a>
                        <a 
                            href="#" 
                            @click.prevent="setCurrentPage('users')"
                            :class="['sidebar-link flex items-center px-4 py-3 rounded-lg group', currentPage === 'users' ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700']">
                            <i data-feather="users" class="sidebar-icon w-5 h-5 mr-3 text-green-400"></i>
                            Users
                        </a>
                        <a 
                            href="#" 
                            @click.prevent="setCurrentPage('categories')"
                            :class="['sidebar-link flex items-center px-4 py-3 rounded-lg group', currentPage === 'categories' ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700']">
                            <i data-feather="folder" class="sidebar-icon w-5 h-5 mr-3 text-purple-400"></i>
                            Categories
                        </a>
                        <a 
                            href="#" 
                            @click.prevent="setCurrentPage('analytics')"
                            :class="['sidebar-link flex items-center px-4 py-3 rounded-lg group', currentPage === 'analytics' ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700']">
                            <i data-feather="bar-chart-2" class="sidebar-icon w-5 h-5 mr-3 text-yellow-400"></i>
                            Analytics
                        </a>
                        <a 
                            href="#" 
                            @click.prevent="setCurrentPage('settings')"
                            :class="['sidebar-link flex items-center px-4 py-3 rounded-lg group', currentPage === 'settings' ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700']">
                            <i data-feather="settings" class="sidebar-icon w-5 h-5 mr-3 text-gray-400"></i>
                            Settings
                        </a>
                    </nav>
                </aside>
            </transition>

            <!-- Main Content Area -->
            <main class="flex-1 overflow-y-auto p-4 md:p-6 bg-gray-900">
                
                <!-- Dashboard Section -->
                <div v-if="currentPage === 'dashboard'">
                    <!-- Stats Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                        <div class="bg-gray-800 rounded-xl p-6 shadow-lg border-l-4 border-primary">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-400 text-sm font-medium">Total Posts</p>
                                    <h3 class="text-2xl font-bold mt-1">{{ stats.totalPosts }}</h3>
                                    <p class="text-green-400 text-xs mt-1 flex items-center">
                                        <i data-feather="trending-up" class="w-3 h-3 mr-1"></i>
                                        12.5% from last month
                                    </p>
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
                                    <h3 class="text-2xl font-bold mt-1">{{ stats.totalViews }}</h3>
                                    <p class="text-green-400 text-xs mt-1 flex items-center">
                                        <i data-feather="trending-up" class="w-3 h-3 mr-1"></i>
                                        8.2% from last month
                                    </p>
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
                                    <h3 class="text-2xl font-bold mt-1">{{ stats.totalLikes }}</h3>
                                    <p class="text-green-400 text-xs mt-1 flex items-center">
                                        <i data-feather="trending-up" class="w-3 h-3 mr-1"></i>
                                        5.3% from last month
                                    </p>
                                </div>
                                <div class="w-12 h-12 rounded-full bg-green-500 bg-opacity-10 flex items-center justify-center">
                                    <i data-feather="heart" class="w-5 h-5 text-green-500"></i>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-800 rounded-xl p-6 shadow-lg border-l-4 border-purple-500">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-400 text-sm font-medium">Active Users</p>
                                    <h3 class="text-2xl font-bold mt-1">{{ stats.totalUsers }}</h3>
                                    <p class="text-red-400 text-xs mt-1 flex items-center">
                                        <i data-feather="trending-down" class="w-3 h-3 mr-1"></i>
                                        2.1% from last month
                                    </p>
                                </div>
                                <div class="w-12 h-12 rounded-full bg-purple-500 bg-opacity-10 flex items-center justify-center">
                                    <i data-feather="users" class="w-5 h-5 text-purple-500"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts Row -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                        <!-- Views Chart -->
                        <div class="bg-gray-800 rounded-xl p-6 shadow-lg">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="font-semibold text-lg">Views Analytics</h3>
                                <div class="flex space-x-2">
                                    <button class="px-3 py-1 text-xs rounded-lg bg-gray-700 text-gray-300 hover:bg-gray-600">Weekly</button>
                                    <button class="px-3 py-1 text-xs rounded-lg bg-primary text-white">Monthly</button>
                                    <button class="px-3 py-1 text-xs rounded-lg bg-gray-700 text-gray-300 hover:bg-gray-600">Yearly</button>
                                </div>
                            </div>
                            <div class="h-64">
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

                    <!-- Recent Activity -->
                    <div class="bg-gray-800 rounded-xl p-6 shadow-lg">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-semibold text-lg">Recent Activity</h3>
                            <button class="text-primary text-sm font-medium flex items-center hover:underline">
                                View All
                                <i data-feather="chevron-right" class="w-4 h-4 ml-1"></i>
                            </button>
                        </div>
                        <div class="space-y-4">
                            <div v-for="activity in recentActivities" :key="activity.id" class="flex items-start">
                                <div class="flex-shrink-0 mt-1">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center" :class="`bg-${activity.color}-500 bg-opacity-10`">
                                        <i :data-feather="activity.icon" class="w-4 h-4" :class="`text-${activity.color}-500`"></i>
                                    </div>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium">{{ activity.action }}</p>
                                    <p class="text-xs text-gray-400 mt-1">{{ activity.time }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Posts Management Section -->
                <div v-if="currentPage === 'posts'">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold text-white">News Articles Management</h2>
                        <button @click="showPostModal = true; editingPost = null" class="bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-lg flex items-center">
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
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
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
                    
                    // Search and filter states
                    const postSearch = ref('');
                    const postFilter = ref('all');
                    const userSearch = ref('');
                    const userRoleFilter = ref('all');
                    
                    // Stats data
                    const stats = ref({
                        totalPosts: 2847,
                        totalViews: 156789,
                        totalLikes: 23456,
                        totalUsers: 5672
                    });
                    
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
                    
                    const categories = ref([
                        { id: 1, name: 'Technology', description: 'Latest technology news and innovations in India', postsCount: 287, subcategories: [{ id: 1, name: 'Startups' }, { id: 2, name: 'AI & ML' }, { id: 3, name: 'Mobile Tech' }] },
                        { id: 2, name: 'Business', description: 'Indian business news, market updates, and economy', postsCount: 234, subcategories: [{ id: 4, name: 'Markets' }, { id: 5, name: 'Economy' }, { id: 6, name: 'Finance' }] },
                        { id: 3, name: 'Sports', description: 'Cricket, football, and other sports news', postsCount: 198, subcategories: [{ id: 7, name: 'Cricket' }, { id: 8, name: 'Football' }, { id: 9, name: 'Olympics' }] },
                        { id: 4, name: 'Politics', description: 'Political news and government updates', postsCount: 167, subcategories: [{ id: 10, name: 'Elections' }, { id: 11, name: 'Policy' }] },
                        { id: 5, name: 'Health', description: 'Health news, medical breakthroughs, and wellness', postsCount: 145, subcategories: [{ id: 12, name: 'Medical' }, { id: 13, name: 'Wellness' }] },
                        { id: 6, name: 'Education', description: 'Educational news, exams, and academic updates', postsCount: 123, subcategories: [{ id: 14, name: 'Exams' }, { id: 15, name: 'Universities' }] },
                        { id: 7, name: 'Entertainment', description: 'Bollywood, regional cinema, and celebrity news', postsCount: 189, subcategories: [{ id: 16, name: 'Bollywood' }, { id: 17, name: 'Regional' }, { id: 18, name: 'Music' }] },
                        { id: 8, name: 'Lifestyle', description: 'Fashion, food, travel, and lifestyle trends', postsCount: 156, subcategories: [{ id: 19, name: 'Fashion' }, { id: 20, name: 'Food' }, { id: 21, name: 'Travel' }] }
                    ]);
                    
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
                        
                        // Add event listener for window resize
                        window.addEventListener('resize', handleResize);
                        
                        // Re-replace icons after any DOM changes if needed
                        setTimeout(() => feather.replace(), 100);
                        
                        // Initialize charts after DOM is ready
                        setTimeout(() => {
                            // Initialize charts
                            const viewsCtx = document.getElementById('viewsChart')?.getContext('2d');
                            if (viewsCtx) {
                                new Chart(viewsCtx, {
                                type: 'line',
                                data: {
                                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
                                    datasets: [{
                                        label: 'Views',
                                        data: [3200, 4500, 5200, 5800, 6700, 7200, 8400],
                                        borderColor: '#0ea5e9',
                                        backgroundColor: 'rgba(14, 165, 233, 0.1)',
                                        borderWidth: 2,
                                        tension: 0.4,
                                        fill: true
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: {
                                        legend: {
                                            display: false
                                        }
                                    },
                                    scales: {
                                        y: {
                                            beginAtZero: true,
                                            grid: {
                                                color: 'rgba(255, 255, 255, 0.05)'
                                            },
                                            ticks: {
                                                color: 'rgba(255, 255, 255, 0.6)'
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
                        
                        // Modal states
                        showPostModal,
                        showUserModal,
                        showCategoryModal,
                        editingPost,
                        editingUser,
                        editingCategory,
                        
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