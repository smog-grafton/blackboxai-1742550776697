<?php
$user = $session->getUser();
$notifications = [];  // TODO: Implement notifications system
?>
<header class="bg-white shadow-md">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <!-- Left side -->
            <div class="flex">
                <!-- Logo -->
                <div class="flex-shrink-0 flex items-center">
                    <a href="/admin/dashboard.php" class="flex items-center">
                        <?php if ($logo = $settings->get('site_logo')): ?>
                            <img src="<?= htmlspecialchars($logo) ?>" 
                                 alt="<?= htmlspecialchars($settings->get('site_name')) ?>" 
                                 class="h-8">
                        <?php else: ?>
                            <span class="text-xl font-bold">
                                <?= htmlspecialchars($settings->get('site_name')) ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </div>

                <!-- Mobile menu button -->
                <button type="button" 
                        onclick="toggleSidebar()"
                        class="md:hidden px-4 text-gray-500 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500">
                    <span class="sr-only">Open sidebar</span>
                    <i class="fas fa-bars"></i>
                </button>
            </div>

            <!-- Right side -->
            <div class="flex items-center space-x-4">
                <!-- Visit Site -->
                <a href="/" 
                   target="_blank"
                   class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-external-link-alt"></i>
                    <span class="hidden sm:inline ml-1">Visit Site</span>
                </a>

                <!-- Notifications -->
                <div class="relative">
                    <button type="button"
                            onclick="toggleNotifications()"
                            class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-bell"></i>
                        <?php if (!empty($notifications)): ?>
                            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center">
                                <?= count($notifications) ?>
                            </span>
                        <?php endif; ?>
                    </button>

                    <!-- Notifications dropdown -->
                    <div id="notificationsDropdown" 
                         class="hidden absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg ring-1 ring-black ring-opacity-5">
                        <div class="p-4">
                            <h3 class="text-lg font-semibold mb-2">Notifications</h3>
                            <?php if (empty($notifications)): ?>
                                <p class="text-gray-500">No new notifications</p>
                            <?php else: ?>
                                <div class="space-y-4">
                                    <?php foreach ($notifications as $notification): ?>
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0">
                                                <i class="fas fa-<?= $notification['icon'] ?> text-<?= $notification['color'] ?>-500"></i>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium">
                                                    <?= htmlspecialchars($notification['title']) ?>
                                                </p>
                                                <p class="text-sm text-gray-500">
                                                    <?= htmlspecialchars($notification['message']) ?>
                                                </p>
                                                <p class="text-xs text-gray-400 mt-1">
                                                    <?= Utility::timeAgo($notification['created_at']) ?>
                                                </p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- User menu -->
                <div class="relative">
                    <button type="button"
                            onclick="toggleUserMenu()"
                            class="flex items-center space-x-2 text-gray-500 hover:text-gray-700">
                        <img src="<?= Utility::getGravatar($user['email'], 32) ?>" 
                             alt="<?= htmlspecialchars($user['full_name']) ?>"
                             class="h-8 w-8 rounded-full">
                        <span class="hidden sm:inline"><?= htmlspecialchars($user['full_name']) ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </button>

                    <!-- User dropdown -->
                    <div id="userDropdown" 
                         class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg ring-1 ring-black ring-opacity-5">
                        <div class="py-1">
                            <a href="/admin/profile.php" 
                               class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-user mr-2"></i>
                                Profile
                            </a>
                            <a href="/admin/settings.php" 
                               class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-cog mr-2"></i>
                                Settings
                            </a>
                            <div class="border-t border-gray-100 my-1"></div>
                            <a href="/admin/logout.php" 
                               class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                <i class="fas fa-sign-out-alt mr-2"></i>
                                Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<script>
    // Toggle sidebar
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('-translate-x-full');
    }

    // Toggle notifications dropdown
    function toggleNotifications() {
        const dropdown = document.getElementById('notificationsDropdown');
        dropdown.classList.toggle('hidden');
        
        // Close user menu if open
        document.getElementById('userDropdown').classList.add('hidden');
    }

    // Toggle user menu dropdown
    function toggleUserMenu() {
        const dropdown = document.getElementById('userDropdown');
        dropdown.classList.toggle('hidden');
        
        // Close notifications if open
        document.getElementById('notificationsDropdown').classList.add('hidden');
    }

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(event) {
        const notificationsButton = event.target.closest('[onclick="toggleNotifications()"]');
        const userMenuButton = event.target.closest('[onclick="toggleUserMenu()"]');
        const notificationsDropdown = document.getElementById('notificationsDropdown');
        const userDropdown = document.getElementById('userDropdown');

        if (!notificationsButton && !notificationsDropdown.contains(event.target)) {
            notificationsDropdown.classList.add('hidden');
        }

        if (!userMenuButton && !userDropdown.contains(event.target)) {
            userDropdown.classList.add('hidden');
        }
    });

    // Handle escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            document.getElementById('notificationsDropdown').classList.add('hidden');
            document.getElementById('userDropdown').classList.add('hidden');
        }
    });
</script>