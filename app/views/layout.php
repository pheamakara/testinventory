<!-- app/views/layouts/main.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Server Management System'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            900: '#1e3a8a',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-100 dark:bg-gray-900 transition-colors duration-200">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="w-64 bg-white dark:bg-gray-800 shadow-lg z-10">
            <div class="p-4 border-b dark:border-gray-700">
                <h1 class="text-xl font-bold text-gray-800 dark:text-white">
                    <?php 
                    $appName = "Server Management";
                    try {
                        $stmt = $this->db->prepare("SELECT setting_value FROM system_settings WHERE setting_key = 'app_name'");
                        $stmt->execute();
                        $result = $stmt->fetch();
                        if ($result && !empty($result['setting_value'])) {
                            $appName = htmlspecialchars($result['setting_value']);
                        }
                    } catch (Exception $e) {
                        // Use default name if settings table doesn't exist yet
                    }
                    echo $appName;
                    ?>
                </h1>
            </div>
            <nav class="p-4">
                <div class="mb-6">
                    <p class="text-xs uppercase text-gray-500 dark:text-gray-400 mb-3">Main</p>
                    <a href="/dashboard" class="flex items-center p-2 text-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 mb-2 <?php echo ($_SERVER['REQUEST_URI'] === '/dashboard' || $_SERVER['REQUEST_URI'] === '/') ? 'bg-gray-100 dark:bg-gray-700' : ''; ?>">
                        <i class="fas fa-tachometer-alt w-5"></i>
                        <span class="ml-3">Dashboard</span>
                    </a>
                </div>
                
                <div class="mb-6">
                    <p class="text-xs uppercase text-gray-500 dark:text-gray-400 mb-3">Server Assets</p>
                    <a href="/servers" class="flex items-center p-2 text-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 mb-2 <?php echo strpos($_SERVER['REQUEST_URI'], '/servers') === 0 ? 'bg-gray-100 dark:bg-gray-700' : ''; ?>">
                        <i class="fas fa-server w-5"></i>
                        <span class="ml-3">Servers</span>
                    </a>
                    <a href="/servers/create" class="flex items-center p-2 text-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 mb-2 <?php echo $_SERVER['REQUEST_URI'] === '/servers/create' ? 'bg-gray-100 dark:bg-gray-700' : ''; ?>">
                        <i class="fas fa-plus-circle w-5"></i>
                        <span class="ml-3">Add Server</span>
                    </a>
                </div>
                
                <div class="mb-6">
                    <p class="text-xs uppercase text-gray-500 dark:text-gray-400 mb-3">Deployments</p>
                    <a href="/deployments" class="flex items-center p-2 text-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 mb-2 <?php echo strpos($_SERVER['REQUEST_URI'], '/deployments') === 0 ? 'bg-gray-100 dark:bg-gray-700' : ''; ?>">
                        <i class="fas fa-tasks w-5"></i>
                        <span class="ml-3">Deployment Requests</span>
                    </a>
                    <a href="/deployments/create" class="flex items-center p-2 text-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 mb-2 <?php echo $_SERVER['REQUEST_URI'] === '/deployments/create' ? 'bg-gray-100 dark:bg-gray-700' : ''; ?>">
                        <i class="fas fa-plus-circle w-5"></i>
                        <span class="ml-3">New Request</span>
                    </a>
                </div>
                
                <?php if (isset($_SESSION['user_role']) && ($_SESSION['user_role'] === 'Admin')): ?>
                <div class="mb-6">
                    <p class="text-xs uppercase text-gray-500 dark:text-gray-400 mb-3">Admin</p>
                    <a href="/users" class="flex items-center p-2 text-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 mb-2 <?php echo strpos($_SERVER['REQUEST_URI'], '/users') === 0 ? 'bg-gray-100 dark:bg-gray-700' : ''; ?>">
                        <i class="fas fa-users w-5"></i>
                        <span class="ml-3">User Management</span>
                    </a>
                    <a href="/settings" class="flex items-center p-2 text-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 mb-2 <?php echo strpos($_SERVER['REQUEST_URI'], '/settings') === 0 ? 'bg-gray-100 dark:bg-gray-700' : ''; ?>">
                        <i class="fas fa-cog w-5"></i>
                        <span class="ml-3">System Settings</span>
                    </a>
                </div>
                <?php endif; ?>
            </nav>
        </div>

        <!-- Main content -->
        <div class="flex-1 overflow-auto">
            <!-- Header -->
            <header class="bg-white dark:bg-gray-800 shadow-sm">
                <div class="flex justify-between items-center p-4">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-800 dark:text-white"><?php echo $pageTitle ?? 'Dashboard'; ?></h2>
                    </div>
                    <div class="flex items-center space-x-4">
                        <button id="theme-toggle" class="p-2 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200">
                            <i class="fas fa-moon dark:hidden"></i>
                            <i class="fas fa-sun hidden dark:block"></i>
                        </button>
                        
                        <!-- Notifications Bell -->
                        <div class="relative">
                            <button id="notifications-btn" class="p-2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-white relative">
                                <i class="fas fa-bell"></i>
                                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center hidden" id="notification-count">0</span>
                            </button>
                            <!-- Notifications Dropdown -->
                            <div id="notifications-dropdown" class="absolute right-0 mt-2 w-80 bg-white dark:bg-gray-800 rounded-lg shadow-lg z-50 hidden border border-gray-200 dark:border-gray-700">
                                <div class="p-4 border-b dark:border-gray-700">
                                    <h3 class="font-semibold text-gray-800 dark:text-white">Notifications</h3>
                                </div>
                                <div class="max-h-60 overflow-y-auto" id="notifications-list">
                                    <div class="p-4 text-center text-gray-500 dark:text-gray-400">
                                        No new notifications
                                    </div>
                                </div>
                                <div class="p-2 border-t dark:border-gray-700">
                                    <a href="#" class="block text-center text-sm text-primary-600 dark:text-primary-400 hover:underline">View all notifications</a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex items-center">
                            <div class="mr-3 text-right">
                                <p class="text-sm font-medium text-gray-800 dark:text-white"><?php echo $_SESSION['username'] ?? 'Guest'; ?></p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 capitalize"><?php echo $_SESSION['user_role'] ?? ''; ?></p>
                            </div>
                            <div class="relative">
                                <img class="w-10 h-10 rounded-full" src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['username'] ?? 'User'); ?>&background=3b82f6&color=fff" alt="User avatar">
                            </div>
                        </div>
                        
                        <!-- User Dropdown -->
                        <div class="relative">
                            <button id="user-menu-btn" class="p-2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-white">
                                <i class="fas fa-chevron-down"></i>
                            </button>
                            <div id="user-dropdown" class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg z-50 hidden border border-gray-200 dark:border-gray-700">
                                <div class="py-2">
                                    <a href="/profile" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                        <i class="fas fa-user-circle mr-2"></i>Profile
                                    </a>
                                    <a href="/logout" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <main class="p-6">
                <?php echo $content; ?>
            </main>
        </div>
    </div>

    <script>
        // Dark mode toggle
        const themeToggle = document.getElementById('theme-toggle');
        const html = document.documentElement;
        
        // Check for saved theme preference or respect OS preference
        if (localStorage.getItem('theme') === 'dark' || 
            (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            html.classList.add('dark');
        } else {
            html.classList.remove('dark');
        }
        
        themeToggle.addEventListener('click', () => {
            html.classList.toggle('dark');
            localStorage.setItem('theme', html.classList.contains('dark') ? 'dark' : 'light');
        });

        // User dropdown toggle
        const userMenuBtn = document.getElementById('user-menu-btn');
        const userDropdown = document.getElementById('user-dropdown');
        
        userMenuBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            userDropdown.classList.toggle('hidden');
            notificationsDropdown.classList.add('hidden');
        });

        // Notifications dropdown toggle
        const notificationsBtn = document.getElementById('notifications-btn');
        const notificationsDropdown = document.getElementById('notifications-dropdown');
        
        notificationsBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            notificationsDropdown.classList.toggle('hidden');
            userDropdown.classList.add('hidden');
            loadNotifications();
        });

        // Close dropdowns when clicking outside
        document.addEventListener('click', (e) => {
            if (!userDropdown.contains(e.target) && !userMenuBtn.contains(e.target)) {
                userDropdown.classList.add('hidden');
            }
            if (!notificationsDropdown.contains(e.target) && !notificationsBtn.contains(e.target)) {
                notificationsDropdown.classList.add('hidden');
            }
        });

        // Load notifications
        function loadNotifications() {
            fetch('/api/notifications')
                .then(response => response.json())
                .then(data => {
                    const notificationsList = document.getElementById('notifications-list');
                    const notificationCount = document.getElementById('notification-count');
                    
                    if (data.length > 0) {
                        notificationCount.textContent = data.length;
                        notificationCount.classList.remove('hidden');
                        
                        notificationsList.innerHTML = '';
                        data.forEach(notification => {
                            const notificationItem = document.createElement('div');
                            notificationItem.className = 'p-4 border-b dark:border-gray-700';
                            notificationItem.innerHTML = `
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 mr-3">
                                        <i class="fas ${notification.icon} text-${notification.type}-500"></i>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">${notification.title}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">${notification.message}</p>
                                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">${notification.time}</p>
                                    </div>
                                </div>
                            `;
                            notificationsList.appendChild(notificationItem);
                        });
                    } else {
                        notificationCount.classList.add('hidden');
                        notificationsList.innerHTML = `
                            <div class="p-4 text-center text-gray-500 dark:text-gray-400">
                                No new notifications
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error loading notifications:', error);
                });
        }

        // Check for new notifications periodically
        setInterval(loadNotifications, 30000); // Every 30 seconds

        // Initial load
        loadNotifications();
    </script>
</body>
</html>