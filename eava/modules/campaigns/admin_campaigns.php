<?php
require_once __DIR__ . '/../../classes/Campaign.php';
require_once __DIR__ . '/../../classes/Category.php';

$campaignModel = new Campaign();
$categoryModel = new Category();

// Handle form submission for creating/editing a campaign
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'title' => $_POST['title'],
        'description' => $_POST['description'],
        'goal_amount' => $_POST['goal_amount'],
        'start_date' => $_POST['start_date'],
        'end_date' => $_POST['end_date'],
        'category_id' => $_POST['category_id'],
        'status' => $_POST['status'],
        'slug' => Utility::generateSlug($_POST['title'])
    ];

    // Handle featured image upload
    if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../../uploads/media/';
        $fileName = basename($_FILES['featured_image']['name']);
        $targetFilePath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $targetFilePath)) {
            $data['featured_image'] = '/uploads/media/' . $fileName;
        }
    }

    // Create or update campaign
    if (isset($_POST['campaign_id'])) {
        $campaignModel->update($_POST['campaign_id'], $data);
    } else {
        $campaignModel->create($data);
    }

    header('Location: /admin/campaigns.php');
    exit;
}

// Get campaign data if editing
$campaign = null;
if (isset($_GET['campaign_id'])) {
    $campaign = $campaignModel->find($_GET['campaign_id']);
}

// Get categories for the dropdown
$categories = $categoryModel->all();

// Get campaign statistics
$stats = $campaignModel->getStatistics();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $campaign ? 'Edit Campaign' : 'Create Campaign' ?> - EAVA Admin</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <header class="bg-white shadow">
        <div class="container mx-auto px-4 py-6">
            <h1 class="text-3xl font-bold text-gray-800">
                <?= $campaign ? 'Edit Campaign' : 'Create Campaign' ?>
            </h1>
        </div>
    </header>

    <main class="container mx-auto px-4 py-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Main Form -->
            <div class="md:col-span-2">
                <form action="" method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow p-6">
                    <input type="hidden" name="campaign_id" value="<?= $campaign['id'] ?? '' ?>">
                    
                    <div class="mb-6">
                        <label for="title" class="block text-gray-700 font-medium mb-2">Title:</label>
                        <input type="text" name="title" id="title" 
                               value="<?= $campaign['title'] ?? '' ?>" 
                               class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                               required>
                    </div>

                    <div class="mb-6">
                        <label for="description" class="block text-gray-700 font-medium mb-2">Description:</label>
                        <textarea name="description" id="description" rows="6" 
                                  class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                  required><?= $campaign['description'] ?? '' ?></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="goal_amount" class="block text-gray-700 font-medium mb-2">Goal Amount:</label>
                            <input type="number" name="goal_amount" id="goal_amount" 
                                   value="<?= $campaign['goal_amount'] ?? '' ?>" 
                                   class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   required>
                        </div>

                        <div>
                            <label for="category_id" class="block text-gray-700 font-medium mb-2">Category:</label>
                            <select name="category_id" id="category_id" 
                                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    required>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['id'] ?>" 
                                            <?= (isset($campaign) && $campaign['category_id'] == $category['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($category['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="start_date" class="block text-gray-700 font-medium mb-2">Start Date:</label>
                            <input type="date" name="start_date" id="start_date" 
                                   value="<?= $campaign['start_date'] ?? '' ?>" 
                                   class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   required>
                        </div>

                        <div>
                            <label for="end_date" class="block text-gray-700 font-medium mb-2">End Date:</label>
                            <input type="date" name="end_date" id="end_date" 
                                   value="<?= $campaign['end_date'] ?? '' ?>" 
                                   class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   required>
                        </div>
                    </div>

                    <div class="mb-6">
                        <label for="status" class="block text-gray-700 font-medium mb-2">Status:</label>
                        <select name="status" id="status" 
                                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required>
                            <option value="draft" <?= (isset($campaign) && $campaign['status'] == 'draft') ? 'selected' : '' ?>>
                                Draft
                            </option>
                            <option value="active" <?= (isset($campaign) && $campaign['status'] == 'active') ? 'selected' : '' ?>>
                                Active
                            </option>
                            <option value="completed" <?= (isset($campaign) && $campaign['status'] == 'completed') ? 'selected' : '' ?>>
                                Completed
                            </option>
                        </select>
                    </div>

                    <div class="mb-6">
                        <label for="featured_image" class="block text-gray-700 font-medium mb-2">Featured Image:</label>
                        <?php if (isset($campaign['featured_image'])): ?>
                            <div class="mb-2">
                                <img src="<?= $campaign['featured_image'] ?>" alt="Current featured image" class="w-32 h-32 object-cover">
                            </div>
                        <?php endif; ?>
                        <input type="file" name="featured_image" id="featured_image" 
                               class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                               accept="image/*">
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" 
                                class="bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <?= $campaign ? 'Update Campaign' : 'Create Campaign' ?>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Sidebar Stats -->
            <div class="md:col-span-1">
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Campaign Statistics</h2>
                    <div class="space-y-4">
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <h3 class="text-gray-600 mb-2">Total Campaigns</h3>
                            <p class="text-2xl font-bold"><?= $stats['total'] ?></p>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <h3 class="text-gray-600 mb-2">Active Campaigns</h3>
                            <p class="text-2xl font-bold"><?= $stats['active'] ?></p>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <h3 class="text-gray-600 mb-2">Total Raised</h3>
                            <p class="text-2xl font-bold"><?= Utility::formatCurrency($stats['total_raised']) ?></p>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <h3 class="text-gray-600 mb-2">Goal Amount</h3>
                            <p class="text-2xl font-bold"><?= Utility::formatCurrency($stats['total_goal']) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-gray-800 text-white py-8 mt-8">
        <div class="container mx-auto px-4 text-center">
            <p>&copy; 2023 EAVA. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>