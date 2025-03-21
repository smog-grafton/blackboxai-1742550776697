<?php
if (!defined('SITE_NAME')) {
    require_once __DIR__ . '/../../config/config.php';
}
?>
<nav class="bg-white shadow-lg">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="flex-shrink-0 flex items-center">
                    <a href="dashboard.php" class="text-2xl font-bold text-indigo-600">
                        <?php echo SITE_NAME; ?> Admin
                    </a>
                </div>
            </div>
            <div class="flex items-center">
                <div class="ml-3 relative">
                    <div class="flex items-center">
                        <span class="mr-3 text-gray-700">
                            Welcome, <?php echo htmlspecialchars($currentUser['full_name']); ?>
                        </span>
                        <a href="logout.php" class="text-red-600 hover:text-red-800">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>