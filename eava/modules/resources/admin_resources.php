<?php
require_once __DIR__ . '/../../classes/Media.php';
require_once __DIR__ . '/../../classes/Category.php';

$mediaModel = new Media();
$categoryModel = new Category();

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_FILES['file'])) {
            $mediaModel->upload($_FILES['file'], $_SESSION['user_id']);
            header('Location: /admin/resources.php?success=1');
            exit;
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Get filter parameters
$type = isset($_GET['type']) ? $_GET['type'] : null;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 20;

// Get resources
$resources = $mediaModel->getByType($type ?? 'document', $page, $perPage);

// Get statistics
$stats = $mediaModel->getStatistics();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Resources - EAVA Admin</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100">
    <header class="bg-white shadow">
        <div class="container mx-auto px-4 py-6">
            <h1 class="text-3xl font-bold text-gray-800">Manage Resources</h1>
        </div>
    </header>

    <main class="container mx-auto px-4 py-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <!-- Statistics -->
            <div class="md:col-span-1">
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Resource Statistics</h2>
                    <div class="space-y-4">
                        <?php foreach ($stats as $type => $typeStats): ?>
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <h3 class="text-gray-600 mb-2"><?= ucfirst($type) ?></h3>
                                <p class="text-2xl font-bold"><?= $typeStats['count'] ?></p>
                                <p class="text-sm text-gray-500">
                                    Total Size: <?= number_format($typeStats['total_size'] / 1024 / 1024, 2) ?> MB
                                </p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Upload Form and Resource List -->
            <div class="md:col-span-3">
                <!-- Upload Form -->
                <div class="bg-white rounded-lg shadow p-6 mb-8">
                    <h2 class="text-xl font-semibold mb-4">Upload New Resource</h2>
                    <?php if (isset($error)): ?>
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>
                    <form action="" method="POST" enctype="multipart/form-data" class="space-y-4">
                        <div>
                            <label for="file" class="block text-gray-700 font-medium mb-2">Select File:</label>
                            <input type="file" name="file" id="file" 
                                   class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   required>
                            <p class="text-sm text-gray-500 mt-1">
                                Allowed types: Images (JPG, PNG, GIF), Documents (PDF, DOC, DOCX), Videos (MP4)
                            </p>
                        </div>
                        <button type="submit" 
                                class="bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Upload Resource
                        </button>
                    </form>
                </div>

                <!-- Resource List -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="p-6">
                        <h2 class="text-xl font-semibold mb-4">Manage Resources</h2>
                        
                        <!-- Type Filters -->
                        <div class="flex gap-4 mb-6">
                            <a href="?type=document" 
                               class="px-4 py-2 rounded-full <?= $type === 'document' ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700' ?>">
                                Documents
                            </a>
                            <a href="?type=image" 
                               class="px-4 py-2 rounded-full <?= $type === 'image' ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700' ?>">
                                Images
                            </a>
                            <a href="?type=video" 
                               class="px-4 py-2 rounded-full <?= $type === 'video' ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700' ?>">
                                Videos
                            </a>
                        </div>

                        <!-- Resource Table -->
                        <table class="w-full">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-4 py-2 text-left">File Name</th>
                                    <th class="px-4 py-2 text-left">Type</th>
                                    <th class="px-4 py-2 text-left">Size</th>
                                    <th class="px-4 py-2 text-left">Uploaded</th>
                                    <th class="px-4 py-2 text-left">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <?php foreach ($resources as $resource): ?>
                                    <tr>
                                        <td class="px-4 py-3">
                                            <?= htmlspecialchars($resource['file_name']) ?>
                                        </td>
                                        <td class="px-4 py-3">
                                            <?= strtoupper(pathinfo($resource['file_name'], PATHINFO_EXTENSION)) ?>
                                        </td>
                                        <td class="px-4 py-3">
                                            <?= number_format($resource['file_size'] / 1024, 2) ?> KB
                                        </td>
                                        <td class="px-4 py-3">
                                            <?= date('M j, Y', strtotime($resource['created_at'])) ?>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex gap-2">
                                                <a href="<?= $resource['file_path'] ?>" 
                                                   class="text-blue-500 hover:text-blue-700"
                                                   target="_blank">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="<?= $resource['file_path'] ?>" 
                                                   class="text-green-500 hover:text-green-700"
                                                   download>
                                                    <i class="fas fa-download"></i>
                                                </a>
                                                <button onclick="deleteResource(<?= $resource['id'] ?>)" 
                                                        class="text-red-500 hover:text-red-700">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <!-- Pagination -->
                        <?php if ($resources['last_page'] > 1): ?>
                            <div class="mt-6 flex justify-center">
                                <div class="flex space-x-2">
                                    <?php for ($i = 1; $i <= $resources['last_page']; $i++): ?>
                                        <a href="?page=<?= $i ?>&type=<?= $type ?>" 
                                           class="px-4 py-2 border rounded <?= $i === $page ? 'bg-blue-500 text-white' : 'text-gray-600 hover:bg-gray-50' ?>">
                                            <?= $i ?>
                                        </a>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        <?php endif; ?>
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

    <script>
        async function deleteResource(id) {
            if (confirm('Are you sure you want to delete this resource?')) {
                try {
                    const response = await fetch(`/api/resources/delete.php`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ id })
                    });

                    if (response.ok) {
                        location.reload();
                    } else {
                        alert('Failed to delete resource');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the resource');
                }
            }
        }
    </script>
</body>
</html>