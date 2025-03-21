<?php
require_once __DIR__ . '/../../classes/Grant.php';
require_once __DIR__ . '/../../classes/Category.php';

$grantModel = new Grant();
$categoryModel = new Category();

// Handle form submission for creating/editing a grant
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'title' => $_POST['title'],
        'description' => $_POST['description'],
        'amount' => $_POST['amount'],
        'deadline' => $_POST['deadline'],
        'category_id' => $_POST['category_id'],
        'status' => $_POST['status'],
        'slug' => Utility::generateSlug($_POST['title'])
    ];

    // Create or update grant
    if (isset($_POST['grant_id'])) {
        $grantModel->update($_POST['grant_id'], $data);
    } else {
        $grantModel->create($data);
    }

    header('Location: /admin/grants.php');
    exit;
}

// Get grant data if editing
$grant = null;
if (isset($_GET['grant_id'])) {
    $grant = $grantModel->find($_GET['grant_id']);
}

// Get categories for the dropdown
$categories = $categoryModel->all();

// Get grant statistics
$stats = $grantModel->getStatistics();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $grant ? 'Edit Grant' : 'Create Grant' ?> - EAVA Admin</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <header class="bg-white shadow">
        <div class="container mx-auto px-4 py-6">
            <h1 class="text-3xl font-bold text-gray-800">
                <?= $grant ? 'Edit Grant' : 'Create Grant' ?>
            </h1>
        </div>
    </header>

    <main class="container mx-auto px-4 py-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Main Form -->
            <div class="md:col-span-2">
                <form action="" method="POST" class="bg-white rounded-lg shadow p-6">
                    <input type="hidden" name="grant_id" value="<?= $grant['id'] ?? '' ?>">
                    
                    <div class="mb-6">
                        <label for="title" class="block text-gray-700 font-medium mb-2">Title:</label>
                        <input type="text" name="title" id="title" 
                               value="<?= $grant['title'] ?? '' ?>" 
                               class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                               required>
                    </div>

                    <div class="mb-6">
                        <label for="description" class="block text-gray-700 font-medium mb-2">Description:</label>
                        <textarea name="description" id="description" rows="6" 
                                  class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                  required><?= $grant['description'] ?? '' ?></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="amount" class="block text-gray-700 font-medium mb-2">Amount:</label>
                            <input type="number" name="amount" id="amount" 
                                   value="<?= $grant['amount'] ?? '' ?>" 
                                   class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   required>
                        </div>

                        <div>
                            <label for="deadline" class="block text-gray-700 font-medium mb-2">Deadline:</label>
                            <input type="date" name="deadline" id="deadline" 
                                   value="<?= $grant['deadline'] ?? '' ?>" 
                                   class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   required>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="category_id" class="block text-gray-700 font-medium mb-2">Category:</label>
                            <select name="category_id" id="category_id" 
                                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    required>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['id'] ?>" 
                                            <?= (isset($grant) && $grant['category_id'] == $category['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($category['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label for="status" class="block text-gray-700 font-medium mb-2">Status:</label>
                            <select name="status" id="status" 
                                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    required>
                                <option value="draft" <?= (isset($grant) && $grant['status'] == 'draft') ? 'selected' : '' ?>>
                                    Draft
                                </option>
                                <option value="open" <?= (isset($grant) && $grant['status'] == 'open') ? 'selected' : '' ?>>
                                    Open
                                </option>
                                <option value="closed" <?= (isset($grant) && $grant['status'] == 'closed') ? 'selected' : '' ?>>
                                    Closed
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" 
                                class="bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <?= $grant ? 'Update Grant' : 'Create Grant' ?>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Sidebar Stats -->
            <div class="md:col-span-1">
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Grant Statistics</h2>
                    <div class="space-y-4">
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <h3 class="text-gray-600 mb-2">Total Grants</h3>
                            <p class="text-2xl font-bold"><?= $stats['total'] ?></p>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <h3 class="text-gray-600 mb-2">Open Grants</h3>
                            <p class="text-2xl font-bold"><?= $stats['open'] ?></p>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <h3 class="text-gray-600 mb-2">Total Amount</h3>
                            <p class="text-2xl font-bold"><?= Utility::formatCurrency($stats['total_amount']) ?></p>
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