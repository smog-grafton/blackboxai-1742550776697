<?php
// Get current page name
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<div class="hidden md:flex md:flex-shrink-0">
    <div class="flex flex-col w-64">
        <div class="flex flex-col h-0 flex-1 bg-white shadow-lg">
            <nav class="flex-1 px-2 py-4 space-y-1">
                <a href="dashboard.php" 
                   class="<?php echo $currentPage === 'dashboard' ? 'bg-indigo-50 text-indigo-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'; ?> 
                          group flex items-center px-2 py-2 text-sm font-medium rounded-md">
                    <i class="fas fa-home mr-3"></i>
                    Dashboard
                </a>

                <a href="posts.php" 
                   class="<?php echo $currentPage === 'posts' ? 'bg-indigo-50 text-indigo-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'; ?> 
                          group flex items-center px-2 py-2 text-sm font-medium rounded-md">
                    <i class="fas fa-newspaper mr-3"></i>
                    Posts
                </a>

                <a href="events.php" 
                   class="<?php echo $currentPage === 'events' ? 'bg-indigo-50 text-indigo-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'; ?> 
                          group flex items-center px-2 py-2 text-sm font-medium rounded-md">
                    <i class="fas fa-calendar mr-3"></i>
                    Events
                </a>

                <a href="projects.php" 
                   class="<?php echo $currentPage === 'projects' ? 'bg-indigo-50 text-indigo-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'; ?> 
                          group flex items-center px-2 py-2 text-sm font-medium rounded-md">
                    <i class="fas fa-project-diagram mr-3"></i>
                    Projects
                </a>

                <a href="programs.php" 
                   class="<?php echo $currentPage === 'programs' ? 'bg-indigo-50 text-indigo-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'; ?> 
                          group flex items-center px-2 py-2 text-sm font-medium rounded-md">
                    <i class="fas fa-tasks mr-3"></i>
                    Programs
                </a>

                <a href="donations.php" 
                   class="<?php echo $currentPage === 'donations' ? 'bg-indigo-50 text-indigo-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'; ?> 
                          group flex items-center px-2 py-2 text-sm font-medium rounded-md">
                    <i class="fas fa-hand-holding-heart mr-3"></i>
                    Donations
                </a>

                <a href="grants.php" 
                   class="<?php echo $currentPage === 'grants' ? 'bg-indigo-50 text-indigo-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'; ?> 
                          group flex items-center px-2 py-2 text-sm font-medium rounded-md">
                    <i class="fas fa-gift mr-3"></i>
                    Grants
                </a>

                <a href="campaigns.php" 
                   class="<?php echo $currentPage === 'campaigns' ? 'bg-indigo-50 text-indigo-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'; ?> 
                          group flex items-center px-2 py-2 text-sm font-medium rounded-md">
                    <i class="fas fa-bullhorn mr-3"></i>
                    Campaigns
                </a>

                <a href="festival.php" 
                   class="<?php echo $currentPage === 'festival' ? 'bg-indigo-50 text-indigo-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'; ?> 
                          group flex items-center px-2 py-2 text-sm font-medium rounded-md">
                    <i class="fas fa-theater-masks mr-3"></i>
                    Festival
                </a>

                <a href="media.php" 
                   class="<?php echo $currentPage === 'media' ? 'bg-indigo-50 text-indigo-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'; ?> 
                          group flex items-center px-2 py-2 text-sm font-medium rounded-md">
                    <i class="fas fa-images mr-3"></i>
                    Media
                </a>

                <a href="users.php" 
                   class="<?php echo $currentPage === 'users' ? 'bg-indigo-50 text-indigo-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'; ?> 
                          group flex items-center px-2 py-2 text-sm font-medium rounded-md">
                    <i class="fas fa-users mr-3"></i>
                    Users
                </a>

                <a href="settings.php" 
                   class="<?php echo $currentPage === 'settings' ? 'bg-indigo-50 text-indigo-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'; ?> 
                          group flex items-center px-2 py-2 text-sm font-medium rounded-md">
                    <i class="fas fa-cog mr-3"></i>
                    Settings
                </a>
            </nav>
        </div>
    </div>
</div>

<!-- Mobile menu button -->
<div class="md:hidden fixed bottom-4 right-4 z-50">
    <button type="button" onclick="toggleMobileMenu()" 
            class="bg-indigo-600 text-white p-3 rounded-full shadow-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
        <i class="fas fa-bars"></i>
    </button>
</div>

<!-- Mobile menu -->
<div id="mobile-menu" class="md:hidden fixed inset-0 z-40 hidden">
    <div class="fixed inset-0 bg-gray-600 bg-opacity-75" onclick="toggleMobileMenu()"></div>
    <div class="relative flex-1 flex flex-col max-w-xs w-full bg-white">
        <div class="flex-1 h-0 pt-5 pb-4 overflow-y-auto">
            <nav class="mt-5 px-2 space-y-1">
                <!-- Same links as desktop menu -->
                <a href="dashboard.php" 
                   class="<?php echo $currentPage === 'dashboard' ? 'bg-indigo-50 text-indigo-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'; ?> 
                          group flex items-center px-2 py-2 text-base font-medium rounded-md">
                    <i class="fas fa-home mr-3"></i>
                    Dashboard
                </a>
                <!-- Repeat for other menu items -->
            </nav>
        </div>
    </div>
</div>

<script>
function toggleMobileMenu() {
    const mobileMenu = document.getElementById('mobile-menu');
    mobileMenu.classList.toggle('hidden');
}

// Close mobile menu when screen size changes to desktop
window.addEventListener('resize', function() {
    if (window.innerWidth >= 768) { // md breakpoint
        const mobileMenu = document.getElementById('mobile-menu');
        if (!mobileMenu.classList.contains('hidden')) {
            mobileMenu.classList.add('hidden');
        }
    }
});
</script>