<?php
require_once __DIR__ . '/../../classes/Post.php';
require_once __DIR__ . '/../../classes/Category.php';

$postModel = new Post();
$categoryModel = new Category();

// Get current page number for pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10; // Number of posts per page

// Get category filter
$categoryId = isset($_GET['category']) ? (int)$_GET['category'] : null;

// Get search query
$search = isset($_GET['search']) ? trim($_GET['search']) : null;

// Get posts with filters
$conditions = ['status' => 'published'];
if ($categoryId) {
    $conditions['category_id'] = $categoryId;
}
$posts = $postModel->paginate($page, $perPage, $conditions);

// Get featured posts
$featuredPosts = $postModel->getFeatured(3);

// Get categories for sidebar
$categories = $categoryModel->all();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog - EAVA</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100">
    <header class="bg-white shadow">
        <div class="container mx-auto px-4 py-6">
            <h1 class="text-3xl font-bold text-gray-800">EAVA Blog</h1>
        </div>
    </header>

    <main class="container mx-auto px-4 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2">
                <!-- Search Bar -->
                <div class="mb-8">
                    <form action="" method="GET" class="flex gap-2">
                        <input type="text" 
                               name="search" 
                               placeholder="Search articles..." 
                               value="<?= htmlspecialchars($search ?? '') ?>"
                               class="flex-1 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <button type="submit" 
                                class="bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>

                <!-- Featured Posts -->
                <?php if ($page === 1 && !$search && !$categoryId): ?>
                    <div class="mb-8">
                        <h2 class="text-2xl font-bold mb-4">Featured Articles</h2>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <?php foreach ($featuredPosts as $post): ?>
                                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                                    <?php if ($post['featured_image']): ?>
                                        <img src="<?= $post['featured_image'] ?>" 
                                             alt="<?= htmlspecialchars($post['title']) ?>"
                                             class="w-full h-48 object-cover">
                                    <?php endif; ?>
                                    <div class="p-6">
                                        <h3 class="text-xl font-semibold mb-2">
                                            <a href="/post/<?= $post['slug'] ?>" class="text-blue-600 hover:text-blue-800">
                                                <?= htmlspecialchars($post['title']) ?>
                                            </a>
                                        </h3>
                                        <p class="text-gray-600 mb-4"><?= htmlspecialchars($post['excerpt']) ?></p>
                                        <div class="flex items-center text-sm text-gray-500">
                                            <span><i class="far fa-calendar mr-2"></i><?= date('F j, Y', strtotime($post['created_at'])) ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Blog Posts -->
                <div class="space-y-8">
                    <?php foreach ($posts['data'] as $post): ?>
                        <article class="bg-white rounded-lg shadow-lg overflow-hidden">
                            <div class="md:flex">
                                <?php if ($post['featured_image']): ?>
                                    <div class="md:flex-shrink-0">
                                        <img src="<?= $post['featured_image'] ?>" 
                                             alt="<?= htmlspecialchars($post['title']) ?>"
                                             class="h-48 w-full md:w-48 object-cover">
                                    </div>
                                <?php endif; ?>
                                <div class="p-6">
                                    <h2 class="text-2xl font-semibold mb-2">
                                        <a href="/post/<?= $post['slug'] ?>" class="text-blue-600 hover:text-blue-800">
                                            <?= htmlspecialchars($post['title']) ?>
                                        </a>
                                    </h2>
                                    <p class="text-gray-600 mb-4"><?= htmlspecialchars($post['excerpt']) ?></p>
                                    <div class="flex items-center justify-between text-sm text-gray-500">
                                        <div class="flex items-center">
                                            <span class="mr-4">
                                                <i class="far fa-calendar mr-2"></i>
                                                <?= date('F j, Y', strtotime($post['created_at'])) ?>
                                            </span>
                                            <span>
                                                <i class="far fa-folder mr-2"></i>
                                                <?= htmlspecialchars($post['category_name']) ?>
                                            </span>
                                        </div>
                                        <a href="/post/<?= $post['slug'] ?>" class="text-blue-500 hover:text-blue-700">
                                            Read More <i class="fas fa-arrow-right ml-1"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>

                    <!-- Pagination -->
                    <?php if ($posts['last_page'] > 1): ?>
                        <div class="flex justify-center space-x-2">
                            <?php for ($i = 1; $i <= $posts['last_page']; $i++): ?>
                                <a href="?page=<?= $i ?><?= $categoryId ? '&category=' . $categoryId : '' ?><?= $search ? '&search=' . urlencode($search) : '' ?>" 
                                   class="px-4 py-2 border rounded <?= $i === $page ? 'bg-blue-500 text-white' : 'text-gray-600 hover:bg-gray-50' ?>">
                                    <?= $i ?>
                                </a>
                            <?php endfor; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <!-- Categories -->
                <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
                    <h3 class="text-xl font-semibold mb-4">Categories</h3>
                    <ul class="space-y-2">
                        <li>
                            <a href="/blog" class="text-gray-600 hover:text-blue-500 <?= !$categoryId ? 'font-semibold text-blue-500' : '' ?>">
                                All Categories
                            </a>
                        </li>
                        <?php foreach ($categories as $category): ?>
                            <li>
                                <a href="?category=<?= $category['id'] ?>" 
                                   class="text-gray-600 hover:text-blue-500 <?= $categoryId === $category['id'] ? 'font-semibold text-blue-500' : '' ?>">
                                    <?= htmlspecialchars($category['name']) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Recent Posts -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-xl font-semibold mb-4">Recent Posts</h3>
                    <div class="space-y-4">
                        <?php foreach ($postModel->getRecent(5) as $post): ?>
                            <div class="flex items-center">
                                <?php if ($post['featured_image']): ?>
                                    <img src="<?= $post['featured_image'] ?>" 
                                         alt="<?= htmlspecialchars($post['title']) ?>"
                                         class="w-16 h-16 object-cover rounded">
                                <?php endif; ?>
                                <div class="ml-4">
                                    <h4 class="font-medium">
                                        <a href="/post/<?= $post['slug'] ?>" class="text-gray-800 hover:text-blue-500">
                                            <?= htmlspecialchars($post['title']) ?>
                                        </a>
                                    </h4>
                                    <span class="text-sm text-gray-500">
                                        <?= date('F j, Y', strtotime($post['created_at'])) ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
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