<?php
require_once __DIR__ . '/../../classes/Media.php';
require_once __DIR__ . '/../../classes/Category.php';

$mediaModel = new Media();
$categoryModel = new Category();

// Get current page number for pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 12; // Number of resources per page

// Get filter parameters
$type = isset($_GET['type']) ? $_GET['type'] : null;
$categoryId = isset($_GET['category']) ? (int)$_GET['category'] : null;

// Get resources with pagination
$resources = $mediaModel->getByType($type ?? 'document', $page, $perPage);

// Get categories for filtering
$categories = $categoryModel->all();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resources - EAVA</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100">
    <header class="bg-white shadow">
        <div class="container mx-auto px-4 py-6">
            <h1 class="text-3xl font-bold text-gray-800">Resources</h1>
        </div>
    </header>

    <main class="container mx-auto px-4 py-8">
        <!-- Resource Type Filters -->
        <section class="mb-8">
            <div class="flex flex-wrap gap-4">
                <a href="?type=document" 
                   class="px-4 py-2 rounded-full <?= $type === 'document' ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700' ?> hover:bg-blue-600 hover:text-white">
                    <i class="fas fa-file-alt mr-2"></i>Documents
                </a>
                <a href="?type=image" 
                   class="px-4 py-2 rounded-full <?= $type === 'image' ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700' ?> hover:bg-blue-600 hover:text-white">
                    <i class="fas fa-image mr-2"></i>Images
                </a>
                <a href="?type=video" 
                   class="px-4 py-2 rounded-full <?= $type === 'video' ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700' ?> hover:bg-blue-600 hover:text-white">
                    <i class="fas fa-video mr-2"></i>Videos
                </a>
            </div>
        </section>

        <!-- Resource Grid -->
        <section>
            <?php if ($resources): ?>
                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    <?php foreach ($resources as $resource): ?>
                        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                            <?php if ($resource['file_type'] === 'image'): ?>
                                <img src="<?= $resource['file_path'] ?>" 
                                     alt="<?= htmlspecialchars($resource['file_name']) ?>"
                                     class="w-full h-48 object-cover">
                            <?php elseif ($resource['file_type'] === 'video'): ?>
                                <div class="relative bg-black h-48">
                                    <video src="<?= $resource['file_path'] ?>" 
                                           class="w-full h-full object-cover"
                                           poster="/assets/images/video-placeholder.jpg"></video>
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <i class="fas fa-play-circle text-white text-4xl"></i>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="h-48 flex items-center justify-center bg-gray-100">
                                    <i class="fas fa-file-alt text-gray-400 text-4xl"></i>
                                </div>
                            <?php endif; ?>

                            <div class="p-4">
                                <h3 class="text-lg font-semibold mb-2">
                                    <?= htmlspecialchars($resource['file_name']) ?>
                                </h3>
                                <div class="flex items-center justify-between text-sm text-gray-500">
                                    <span>
                                        <i class="fas fa-calendar-alt mr-1"></i>
                                        <?= date('M j, Y', strtotime($resource['created_at'])) ?>
                                    </span>
                                    <span>
                                        <i class="fas fa-file mr-1"></i>
                                        <?= strtoupper(pathinfo($resource['file_name'], PATHINFO_EXTENSION)) ?>
                                    </span>
                                </div>
                                <div class="mt-4">
                                    <a href="<?= $resource['file_path'] ?>" 
                                       class="block w-full text-center bg-blue-500 text-white py-2 rounded hover:bg-blue-600"
                                       download>
                                        <i class="fas fa-download mr-2"></i>Download
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($resources['last_page'] > 1): ?>
                    <div class="mt-8 flex justify-center">
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
            <?php else: ?>
                <p class="text-center text-gray-600">No resources found.</p>
            <?php endif; ?>
        </section>
    </main>

    <footer class="bg-gray-800 text-white py-8 mt-8">
        <div class="container mx-auto px-4 text-center">
            <p>&copy; 2023 EAVA. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // Handle video preview
        document.querySelectorAll('video').forEach(video => {
            video.addEventListener('click', function() {
                this.paused ? this.play() : this.pause();
            });
        });
    </script>
</body>
</html>