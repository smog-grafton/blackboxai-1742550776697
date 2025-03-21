<?php
$currentPage = basename($_SERVER['PHP_SELF']);

function isActive($page) {
    global $currentPage;
    return $currentPage === $page ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white';
}
?>
<aside id="sidebar" class="md:w-64 bg-gray-800 text-white fixed inset-y-0 left-0 transform -translate-x-full md:translate-x-0 transition-transform duration-200 ease-in-out z-30">
    <div class="h-full flex flex-col">
        <!-- Sidebar content -->
        <nav class="flex-1 px-2 py-4 space-y-1">
            <!-- Dashboard -->
            <a href="/admin/dashboard.php" 
               class="group flex items-center px-2 py-2 text-sm font-medium rounded-md <?= isActive('dashboard.php') ?>">
                <i class="fas fa-tachometer-alt w-6 h-6 mr-3"></i>
                Dashboard
            </a>

            <!-- Content Management -->
            <div class="space-y-1">
                <button type="button"
                        onclick="toggleSubmenu('content')"
                        class="group w-full flex items-center px-2 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white">
                    <i class="fas fa-file-alt w-6 h-6 mr-3"></i>
                    <span class="flex-1">Content</span>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div id="content-submenu" class="space-y-1 ml-8 hidden">
                    <a href="/admin/posts.php" 
                       class="group flex items-center px-2 py-2 text-sm font-medium rounded-md <?= isActive('posts.php') ?>">
                        <i class="fas fa-newspaper w-6 h-6 mr-3"></i>
                        Posts
                    </a>
                    <a href="/admin/pages.php" 
                       class="group flex items-center px-2 py-2 text-sm font-medium rounded-md <?= isActive('pages.php') ?>">
                        <i class="fas fa-file w-6 h-6 mr-3"></i>
                        Pages
                    </a>
                    <a href="/admin/categories.php" 
                       class="group flex items-center px-2 py-2 text-sm font-medium rounded-md <?= isActive('categories.php') ?>">
                        <i class="fas fa-folder w-6 h-6 mr-3"></i>
                        Categories
                    </a>
                </div>
            </div>

            <!-- Programs & Events -->
            <div class="space-y-1">
                <button type="button"
                        onclick="toggleSubmenu('programs')"
                        class="group w-full flex items-center px-2 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white">
                    <i class="fas fa-calendar-alt w-6 h-6 mr-3"></i>
                    <span class="flex-1">Programs & Events</span>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div id="programs-submenu" class="space-y-1 ml-8 hidden">
                    <a href="/admin/programs.php" 
                       class="group flex items-center px-2 py-2 text-sm font-medium rounded-md <?= isActive('programs.php') ?>">
                        <i class="fas fa-graduation-cap w-6 h-6 mr-3"></i>
                        Programs
                    </a>
                    <a href="/admin/events.php" 
                       class="group flex items-center px-2 py-2 text-sm font-medium rounded-md <?= isActive('events.php') ?>">
                        <i class="fas fa-calendar w-6 h-6 mr-3"></i>
                        Events
                    </a>
                    <a href="/admin/festival.php" 
                       class="group flex items-center px-2 py-2 text-sm font-medium rounded-md <?= isActive('festival.php') ?>">
                        <i class="fas fa-star w-6 h-6 mr-3"></i>
                        Festival
                    </a>
                </div>
            </div>

            <!-- Fundraising -->
            <div class="space-y-1">
                <button type="button"
                        onclick="toggleSubmenu('fundraising')"
                        class="group w-full flex items-center px-2 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white">
                    <i class="fas fa-hand-holding-usd w-6 h-6 mr-3"></i>
                    <span class="flex-1">Fundraising</span>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div id="fundraising-submenu" class="space-y-1 ml-8 hidden">
                    <a href="/admin/campaigns.php" 
                       class="group flex items-center px-2 py-2 text-sm font-medium rounded-md <?= isActive('campaigns.php') ?>">
                        <i class="fas fa-bullhorn w-6 h-6 mr-3"></i>
                        Campaigns
                    </a>
                    <a href="/admin/donations.php" 
                       class="group flex items-center px-2 py-2 text-sm font-medium rounded-md <?= isActive('donations.php') ?>">
                        <i class="fas fa-donate w-6 h-6 mr-3"></i>
                        Donations
                    </a>
                    <a href="/admin/grants.php" 
                       class="group flex items-center px-2 py-2 text-sm font-medium rounded-md <?= isActive('grants.php') ?>">
                        <i class="fas fa-gift w-6 h-6 mr-3"></i>
                        Grants
                    </a>
                </div>
            </div>

            <!-- Media -->
            <a href="/admin/media.php" 
               class="group flex items-center px-2 py-2 text-sm font-medium rounded-md <?= isActive('media.php') ?>">
                <i class="fas fa-images w-6 h-6 mr-3"></i>
                Media Library
            </a>

            <!-- Users -->
            <a href="/admin/users.php" 
               class="group flex items-center px-2 py-2 text-sm font-medium rounded-md <?= isActive('users.php') ?>">
                <i class="fas fa-users w-6 h-6 mr-3"></i>
                Users
            </a>

            <!-- Menu Management -->
            <a href="/admin/menu-management.php" 
               class="group flex items-center px-2 py-2 text-sm font-medium rounded-md <?= isActive('menu-management.php') ?>">
                <i class="fas fa-bars w-6 h-6 mr-3"></i>
                Menu Management
            </a>

            <!-- Settings -->
            <div class="space-y-1">
                <button type="button"
                        onclick="toggleSubmenu('settings')"
                        class="group w-full flex items-center px-2 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white">
                    <i class="fas fa-cog w-6 h-6 mr-3"></i>
                    <span class="flex-1">Settings</span>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div id="settings-submenu" class="space-y-1 ml-8 hidden">
                    <a href="/admin/settings.php" 
                       class="group flex items-center px-2 py-2 text-sm font-medium rounded-md <?= isActive('settings.php') ?>">
                        <i class="fas fa-sliders-h w-6 h-6 mr-3"></i>
                        General Settings
                    </a>
                    <a href="/admin/appearance.php" 
                       class="group flex items-center px-2 py-2 text-sm font-medium rounded-md <?= isActive('appearance.php') ?>">
                        <i class="fas fa-paint-brush w-6 h-6 mr-3"></i>
                        Appearance
                    </a>
                    <a href="/admin/integrations.php" 
                       class="group flex items-center px-2 py-2 text-sm font-medium rounded-md <?= isActive('integrations.php') ?>">
                        <i class="fas fa-plug w-6 h-6 mr-3"></i>
                        Integrations
                    </a>
                </div>
            </div>
        </nav>

        <!-- Sidebar footer -->
        <div class="p-4 border-t border-gray-700">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <img src="<?= Utility::getGravatar($user['email'], 32) ?>" 
                         alt="<?= htmlspecialchars($user['full_name']) ?>"
                         class="h-8 w-8 rounded-full">
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-white">
                        <?= htmlspecialchars($user['full_name']) ?>
                    </p>
                    <p class="text-xs font-medium text-gray-300">
                        <?= htmlspecialchars($user['role']) ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</aside>

<script>
    // Toggle submenu
    function toggleSubmenu(id) {
        const submenu = document.getElementById(`${id}-submenu`);
        const button = submenu.previousElementSibling;
        const icon = button.querySelector('.fa-chevron-down');
        
        submenu.classList.toggle('hidden');
        icon.classList.toggle('transform');
        icon.classList.toggle('rotate-180');
    }

    // Show active submenu on page load
    document.addEventListener('DOMContentLoaded', function() {
        const currentPage = '<?= $currentPage ?>';
        const submenus = {
            'content': ['posts.php', 'pages.php', 'categories.php'],
            'programs': ['programs.php', 'events.php', 'festival.php'],
            'fundraising': ['campaigns.php', 'donations.php', 'grants.php'],
            'settings': ['settings.php', 'appearance.php', 'integrations.php']
        };

        for (const [menu, pages] of Object.entries(submenus)) {
            if (pages.includes(currentPage)) {
                document.getElementById(`${menu}-submenu`).classList.remove('hidden');
                const icon = document.querySelector(`[onclick="toggleSubmenu('${menu}')"] .fa-chevron-down`);
                icon.classList.add('transform', 'rotate-180');
            }
        }
    });
</script>