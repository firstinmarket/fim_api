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
                    
               
                    <div class="flex items-center space-x-2 cursor-pointer">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-primary to-secondary flex items-center justify-center">
                            <i data-feather="log-out" class="w-4 h-4 text-white"></i>
                        </div>
                        
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