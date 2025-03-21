<?php
require_once __DIR__ . '/../../classes/Campaign.php';
require_once __DIR__ . '/../../classes/Category.php';

$campaignModel = new Campaign();
$categoryModel = new Category();

// Get current page number for pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 9; // Number of campaigns per page

// Get active campaigns with pagination
$campaigns = $campaignModel->getActive($page, $perPage);

// Get featured campaigns
$featuredCampaigns = $campaignModel->getFeatured(3);

// Get categories for filtering
$categories = $categoryModel->all();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campaigns - EAVA</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100">
    <header class="bg-white shadow">
        <div class="container mx-auto px-4 py-6">
            <h1 class="text-3xl font-bold text-gray-800">Current Campaigns</h1>
        </div>
    </header>

    <main class="container mx-auto px-4 py-8">
        <!-- Featured Campaigns -->
        <?php if ($featuredCampaigns): ?>
        <section class="mb-12">
            <h2 class="text-2xl font-semibold mb-6">Featured Campaigns</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <?php foreach ($featuredCampaigns as $campaign): ?>
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                        <?php if ($campaign['featured_image']): ?>
                            <img src="<?= $campaign['featured_image'] ?>" 
                                 alt="<?= htmlspecialchars($campaign['title']) ?>"
                                 class="w-full h-48 object-cover">
                        <?php endif; ?>
                        <div class="p-6">
                            <h3 class="text-xl font-semibold mb-2">
                                <a href="/campaign/<?= $campaign['slug'] ?>" class="text-blue-600 hover:text-blue-800">
                                    <?= htmlspecialchars($campaign['title']) ?>
                                </a>
                            </h3>
                            <div class="mb-4">
                                <div class="w-full bg-gray-200 rounded-full h-2.5">
                                    <div class="bg-blue-600 h-2.5 rounded-full" style="width: <?= $campaign['progress'] ?>%"></div>
                                </div>
                                <div class="flex justify-between mt-2 text-sm text-gray-600">
                                    <span><?= Utility::formatCurrency($campaign['current_amount']) ?> raised</span>
                                    <span><?= round($campaign['progress']) ?>% of <?= Utility::formatCurrency($campaign['goal_amount']) ?></span>
                                </div>
                            </div>
                            <a href="/campaign/<?= $campaign['slug'] ?>" 
                               class="block w-full text-center bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600">
                                Support This Campaign
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Campaign Filters -->
        <section class="mb-8">
            <div class="flex flex-wrap gap-4">
                <a href="?category=all" class="px-4 py-2 rounded-full bg-blue-500 text-white hover:bg-blue-600">
                    All Campaigns
                </a>
                <?php foreach ($categories as $category): ?>
                    <a href="?category=<?= $category['slug'] ?>" 
                       class="px-4 py-2 rounded-full bg-gray-200 text-gray-700 hover:bg-gray-300">
                        <?= htmlspecialchars($category['name']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- All Campaigns -->
        <section>
            <?php if ($campaigns['data']): ?>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <?php foreach ($campaigns['data'] as $campaign): ?>
                        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                            <?php if ($campaign['featured_image']): ?>
                                <img src="<?= $campaign['featured_image'] ?>" 
                                     alt="<?= htmlspecialchars($campaign['title']) ?>"
                                     class="w-full h-48 object-cover">
                            <?php endif; ?>
                            <div class="p-6">
                                <h3 class="text-xl font-semibold mb-2">
                                    <a href="/campaign/<?= $campaign['slug'] ?>" class="text-blue-600 hover:text-blue-800">
                                        <?= htmlspecialchars($campaign['title']) ?>
                                    </a>
                                </h3>
                                <p class="text-gray-600 mb-4"><?= htmlspecialchars(substr($campaign['description'], 0, 150)) ?>...</p>
                                <div class="mb-4">
                                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                                        <div class="bg-blue-600 h-2.5 rounded-full" 
                                             style="width: <?= ($campaign['current_amount'] / $campaign['goal_amount']) * 100 ?>%">
                                        </div>
                                    </div>
                                    <div class="flex justify-between mt-2 text-sm text-gray-600">
                                        <span><?= Utility::formatCurrency($campaign['current_amount']) ?> raised</span>
                                        <span><?= round(($campaign['current_amount'] / $campaign['goal_amount']) * 100) ?>% of <?= Utility::formatCurrency($campaign['goal_amount']) ?></span>
                                    </div>
                                </div>
                                <div class="flex justify-between items-center text-sm text-gray-500">
                                    <span><i class="fas fa-clock"></i> <?= ceil((strtotime($campaign['end_date']) - time()) / (60 * 60 * 24)) ?> days left</span>
                                    <a href="/campaign/<?= $campaign['slug'] ?>" 
                                       class="text-blue-500 hover:text-blue-700">
                                        Learn More <i class="fas fa-arrow-right ml-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <div class="mt-8 flex justify-center">
                    <div class="flex space-x-2">
                        <?php for ($i = 1; $i <= $campaigns['last_page']; $i++): ?>
                            <a href="?page=<?= $i ?>" 
                               class="px-4 py-2 border rounded <?= $i === $page ? 'bg-blue-500 text-white' : 'text-gray-600 hover:bg-gray-50' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                </div>
            <?php else: ?>
                <p class="text-center text-gray-600">No active campaigns at the moment.</p>
            <?php endif; ?>
        </section>
    </main>

    <footer class="bg-gray-800 text-white py-8 mt-8">
        <div class="container mx-auto px-4 text-center">
            <p>&copy; 2023 EAVA. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>