<?php
require_once __DIR__ . '/../../classes/Grant.php';
require_once __DIR__ . '/../../classes/Category.php';

$grantModel = new Grant();
$categoryModel = new Category();

// Get current page number for pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10; // Number of grants per page

// Get open grants with pagination
$grants = $grantModel->getOpen($page, $perPage);

// Get categories for filtering
$categories = $categoryModel->all();

// Get upcoming deadlines
$upcomingDeadlines = $grantModel->getUpcomingDeadlines(5);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grants - EAVA</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <header class="bg-white shadow">
        <div class="container mx-auto px-4 py-6">
            <h1 class="text-3xl font-bold text-gray-800">Available Grants</h1>
        </div>
    </header>

    <main class="container mx-auto px-4 py-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="md:col-span-2">
                <section class="bg-white rounded-lg shadow p-6 mb-8">
                    <h2 class="text-2xl font-semibold mb-4">Current Opportunities</h2>
                    <?php if ($grants['data']): ?>
                        <div class="space-y-6">
                            <?php foreach ($grants['data'] as $grant): ?>
                                <div class="border-b pb-6 last:border-0">
                                    <h3 class="text-xl font-semibold mb-2">
                                        <a href="/grant/<?= $grant['slug'] ?>" class="text-blue-600 hover:text-blue-800">
                                            <?= htmlspecialchars($grant['title']) ?>
                                        </a>
                                    </h3>
                                    <p class="text-gray-600 mb-3"><?= htmlspecialchars($grant['description']) ?></p>
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-500">
                                            Amount: <?= Utility::formatCurrency($grant['amount']) ?>
                                        </span>
                                        <span class="text-sm text-red-500">
                                            Deadline: <?= date('F j, Y', strtotime($grant['deadline'])) ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-6 flex justify-center">
                            <div class="flex space-x-2">
                                <?php for ($i = 1; $i <= $grants['last_page']; $i++): ?>
                                    <a href="?page=<?= $i ?>" 
                                       class="px-4 py-2 border rounded <?= $i === $page ? 'bg-blue-500 text-white' : 'text-gray-600 hover:bg-gray-50' ?>">
                                        <?= $i ?>
                                    </a>
                                <?php endfor; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-600">No grants are currently available.</p>
                    <?php endif; ?>
                </section>
            </div>

            <!-- Sidebar -->
            <div class="md:col-span-1">
                <!-- Categories -->
                <section class="bg-white rounded-lg shadow p-6 mb-6">
                    <h3 class="text-xl font-semibold mb-4">Categories</h3>
                    <ul class="space-y-2">
                        <?php foreach ($categories as $category): ?>
                            <li>
                                <a href="?category=<?= $category['slug'] ?>" 
                                   class="text-gray-600 hover:text-blue-600">
                                    <?= htmlspecialchars($category['name']) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </section>

                <!-- Upcoming Deadlines -->
                <section class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-xl font-semibold mb-4">Upcoming Deadlines</h3>
                    <?php if ($upcomingDeadlines): ?>
                        <ul class="space-y-4">
                            <?php foreach ($upcomingDeadlines as $deadline): ?>
                                <li class="border-b pb-4 last:border-0">
                                    <a href="/grant/<?= $deadline['slug'] ?>" 
                                       class="text-blue-600 hover:text-blue-800 font-medium">
                                        <?= htmlspecialchars($deadline['title']) ?>
                                    </a>
                                    <p class="text-sm text-red-500 mt-1">
                                        Deadline: <?= date('F j, Y', strtotime($deadline['deadline'])) ?>
                                    </p>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-gray-600">No upcoming deadlines.</p>
                    <?php endif; ?>
                </section>
            </div>
        </div>
    </main>

    <footer class="bg-gray-800 text-white py-8">
        <div class="container mx-auto px-4 text-center">
            <p>&copy; 2023 EAVA. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>