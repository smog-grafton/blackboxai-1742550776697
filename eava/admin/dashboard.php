<?php
require_once '../config/config.php';
require_once '../classes/Database.php';
require_once '../classes/User.php';

session_start();

$user = new User();

// Check if user is logged in and is admin
if (!$user->isLoggedIn() || !$user->isAdmin()) {
    header('Location: index.php');
    exit;
}

$currentUser = $user->getCurrentUser();
$db = Database::getInstance();

// Get quick statistics
try {
    // Get total posts
    $db->query("SELECT COUNT(*) as count FROM posts");
    $totalPosts = $db->findOne()['count'];

    // Get total events
    $db->query("SELECT COUNT(*) as count FROM events");
    $totalEvents = $db->findOne()['count'];

    // Get total projects
    $db->query("SELECT COUNT(*) as count FROM projects");
    $totalProjects = $db->findOne()['count'];

    // Get total donations
    $db->query("SELECT COUNT(*) as count FROM donations WHERE status = 'completed'");
    $totalDonations = $db->findOne()['count'];

    // Get recent posts
    $db->query("SELECT p.*, u.username as author_name 
                FROM posts p 
                LEFT JOIN users u ON p.author_id = u.id 
                ORDER BY p.created_at DESC LIMIT 5");
    $recentPosts = $db->findAll();

    // Get upcoming events
    $db->query("SELECT * FROM events 
                WHERE start_date >= CURDATE() 
                ORDER BY start_date ASC LIMIT 5");
    $upcomingEvents = $db->findAll();

} catch (Exception $e) {
    error_log("Dashboard Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Top Navigation Bar -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <span class="text-2xl font-bold text-indigo-600"><?php echo SITE_NAME; ?></span>
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

    <div class="flex h-screen bg-gray-100">
        <!-- Sidebar -->
        <div class="hidden md:flex md:flex-shrink-0">
            <div class="flex flex-col w-64">
                <div class="flex flex-col h-0 flex-1 bg-white shadow-lg">
                    <nav class="flex-1 px-2 py-4 space-y-1">
                        <a href="dashboard.php" class="bg-indigo-50 text-indigo-600 group flex items-center px-2 py-2 text-sm font-medium rounded-md">
                            <i class="fas fa-home mr-3"></i>
                            Dashboard
                        </a>
                        <a href="posts.php" class="text-gray-600 hover:bg-gray-50 hover:text-gray-900 group flex items-center px-2 py-2 text-sm font-medium rounded-md">
                            <i class="fas fa-newspaper mr-3"></i>
                            Posts
                        </a>
                        <a href="events.php" class="text-gray-600 hover:bg-gray-50 hover:text-gray-900 group flex items-center px-2 py-2 text-sm font-medium rounded-md">
                            <i class="fas fa-calendar mr-3"></i>
                            Events
                        </a>
                        <a href="projects.php" class="text-gray-600 hover:bg-gray-50 hover:text-gray-900 group flex items-center px-2 py-2 text-sm font-medium rounded-md">
                            <i class="fas fa-project-diagram mr-3"></i>
                            Projects
                        </a>
                        <a href="programs.php" class="text-gray-600 hover:bg-gray-50 hover:text-gray-900 group flex items-center px-2 py-2 text-sm font-medium rounded-md">
                            <i class="fas fa-tasks mr-3"></i>
                            Programs
                        </a>
                        <a href="donations.php" class="text-gray-600 hover:bg-gray-50 hover:text-gray-900 group flex items-center px-2 py-2 text-sm font-medium rounded-md">
                            <i class="fas fa-hand-holding-heart mr-3"></i>
                            Donations
                        </a>
                        <a href="grants.php" class="text-gray-600 hover:bg-gray-50 hover:text-gray-900 group flex items-center px-2 py-2 text-sm font-medium rounded-md">
                            <i class="fas fa-gift mr-3"></i>
                            Grants
                        </a>
                        <a href="media.php" class="text-gray-600 hover:bg-gray-50 hover:text-gray-900 group flex items-center px-2 py-2 text-sm font-medium rounded-md">
                            <i class="fas fa-images mr-3"></i>
                            Media
                        </a>
                        <a href="users.php" class="text-gray-600 hover:bg-gray-50 hover:text-gray-900 group flex items-center px-2 py-2 text-sm font-medium rounded-md">
                            <i class="fas fa-users mr-3"></i>
                            Users
                        </a>
                        <a href="settings.php" class="text-gray-600 hover:bg-gray-50 hover:text-gray-900 group flex items-center px-2 py-2 text-sm font-medium rounded-md">
                            <i class="fas fa-cog mr-3"></i>
                            Settings
                        </a>
                    </nav>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 overflow-auto focus:outline-none">
            <main class="flex-1 relative pb-8 z-0">
                <!-- Page header -->
                <div class="bg-white shadow">
                    <div class="px-4 sm:px-6 lg:max-w-6xl lg:mx-auto lg:px-8">
                        <div class="py-6 md:flex md:items-center md:justify-between">
                            <div class="flex-1 min-w-0">
                                <h1 class="text-2xl font-bold leading-7 text-gray-900 sm:leading-9 sm:truncate">
                                    Dashboard
                                </h1>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-8">
                    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                        <!-- Stats -->
                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                            <!-- Posts stat -->
                            <div class="bg-white overflow-hidden shadow rounded-lg">
                                <div class="p-5">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-newspaper text-2xl text-indigo-600"></i>
                                        </div>
                                        <div class="ml-5 w-0 flex-1">
                                            <dl>
                                                <dt class="text-sm font-medium text-gray-500 truncate">
                                                    Total Posts
                                                </dt>
                                                <dd class="flex items-baseline">
                                                    <div class="text-2xl font-semibold text-gray-900">
                                                        <?php echo $totalPosts; ?>
                                                    </div>
                                                </dd>
                                            </dl>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-gray-50 px-5 py-3">
                                    <div class="text-sm">
                                        <a href="posts.php" class="font-medium text-indigo-600 hover:text-indigo-900">
                                            View all posts
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Events stat -->
                            <div class="bg-white overflow-hidden shadow rounded-lg">
                                <div class="p-5">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-calendar text-2xl text-indigo-600"></i>
                                        </div>
                                        <div class="ml-5 w-0 flex-1">
                                            <dl>
                                                <dt class="text-sm font-medium text-gray-500 truncate">
                                                    Total Events
                                                </dt>
                                                <dd class="flex items-baseline">
                                                    <div class="text-2xl font-semibold text-gray-900">
                                                        <?php echo $totalEvents; ?>
                                                    </div>
                                                </dd>
                                            </dl>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-gray-50 px-5 py-3">
                                    <div class="text-sm">
                                        <a href="events.php" class="font-medium text-indigo-600 hover:text-indigo-900">
                                            View all events
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Projects stat -->
                            <div class="bg-white overflow-hidden shadow rounded-lg">
                                <div class="p-5">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-project-diagram text-2xl text-indigo-600"></i>
                                        </div>
                                        <div class="ml-5 w-0 flex-1">
                                            <dl>
                                                <dt class="text-sm font-medium text-gray-500 truncate">
                                                    Total Projects
                                                </dt>
                                                <dd class="flex items-baseline">
                                                    <div class="text-2xl font-semibold text-gray-900">
                                                        <?php echo $totalProjects; ?>
                                                    </div>
                                                </dd>
                                            </dl>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-gray-50 px-5 py-3">
                                    <div class="text-sm">
                                        <a href="projects.php" class="font-medium text-indigo-600 hover:text-indigo-900">
                                            View all projects
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Donations stat -->
                            <div class="bg-white overflow-hidden shadow rounded-lg">
                                <div class="p-5">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-hand-holding-heart text-2xl text-indigo-600"></i>
                                        </div>
                                        <div class="ml-5 w-0 flex-1">
                                            <dl>
                                                <dt class="text-sm font-medium text-gray-500 truncate">
                                                    Total Donations
                                                </dt>
                                                <dd class="flex items-baseline">
                                                    <div class="text-2xl font-semibold text-gray-900">
                                                        <?php echo $totalDonations; ?>
                                                    </div>
                                                </dd>
                                            </dl>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-gray-50 px-5 py-3">
                                    <div class="text-sm">
                                        <a href="donations.php" class="font-medium text-indigo-600 hover:text-indigo-900">
                                            View all donations
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Activity -->
                        <div class="mt-8 grid grid-cols-1 gap-5 lg:grid-cols-2">
                            <!-- Recent Posts -->
                            <div class="bg-white shadow rounded-lg">
                                <div class="px-4 py-5 sm:px-6">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                                        Recent Posts
                                    </h3>
                                </div>
                                <div class="border-t border-gray-200">
                                    <ul class="divide-y divide-gray-200">
                                        <?php foreach ($recentPosts as $post): ?>
                                        <li class="px-4 py-4">
                                            <div class="flex items-center justify-between">
                                                <div class="text-sm font-medium text-indigo-600 truncate">
                                                    <?php echo htmlspecialchars($post['title']); ?>
                                                </div>
                                                <div class="ml-2 flex-shrink-0 flex">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $post['status'] === 'published' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                                        <?php echo ucfirst($post['status']); ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="mt-2 flex justify-between">
                                                <div class="sm:flex">
                                                    <div class="mr-6 flex items-center text-sm text-gray-500">
                                                        <i class="fas fa-user mr-1.5"></i>
                                                        <?php echo htmlspecialchars($post['author_name']); ?>
                                                    </div>
                                                    <div class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0">
                                                        <i class="fas fa-calendar mr-1.5"></i>
                                                        <?php echo date('M j, Y', strtotime($post['created_at'])); ?>
                                                    </div>
                                                </div>
                                                <div>
                                                    <a href="posts.php?action=edit&id=<?php echo $post['id']; ?>" class="text-indigo-600 hover:text-indigo-900">
                                                        Edit
                                                    </a>
                                                </div>
                                            </div>
                                        </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>

                            <!-- Upcoming Events -->
                            <div class="bg-white shadow rounded-lg">
                                <div class="px-4 py-5 sm:px-6">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                                        Upcoming Events
                                    </h3>
                                </div>
                                <div class="border-t border-gray-200">
                                    <ul class="divide-y divide-gray-200">
                                        <?php foreach ($upcomingEvents as $event): ?>
                                        <li class="px-4 py-4">
                                            <div class="flex items-center justify-between">
                                                <div class="text-sm font-medium text-indigo-600 truncate">
                                                    <?php echo htmlspecialchars($event['title']); ?>
                                                </div>
                                                <div class="ml-2 flex-shrink-0 flex">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                        <?php echo ucfirst($event['status']); ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="mt-2 flex justify-between">
                                                <div class="sm:flex">
                                                    <div class="mr-6 flex items-center text-sm text-gray-500">
                                                        <i class="fas fa-map-marker-alt mr-1.5"></i>
                                                        <?php echo htmlspecialchars($event['location']); ?>
                                                    </div>
                                                    <div class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0">
                                                        <i class="fas fa-calendar mr-1.5"></i>
                                                        <?php echo date('M j, Y', strtotime($event['start_date'])); ?>
                                                    </div>
                                                </div>
                                                <div>
                                                    <a href="events.php?action=edit&id=<?php echo $event['id']; ?>" class="text-indigo-600 hover:text-indigo-900">
                                                        Edit
                                                    </a>
                                                </div>
                                            </div>
                                        </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Mobile menu toggle
        document.addEventListener('DOMContentLoaded', function() {
            const menuButton = document.createElement('button');
            menuButton.className = 'md:hidden fixed bottom-4 right-4 bg-indigo-600 text-white p-3 rounded-full shadow-lg';
            menuButton.innerHTML = '<i class="fas fa-bars"></i>';
            menuButton.onclick = function() {
                const sidebar = document.querySelector('.md\\:flex-shrink-0');
                sidebar.classList.toggle('hidden');
                sidebar.classList.toggle('fixed');
                sidebar.classList.toggle('inset-0');
                sidebar.classList.toggle('z-50');
            };
            document.body.appendChild(menuButton);
        });
    </script>
</body>
</html>