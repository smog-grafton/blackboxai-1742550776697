<?php
require_once __DIR__ . '/../../classes/Post.php';
require_once __DIR__ . '/../../classes/Category.php';

$postModel = new Post();
$categoryModel = new Category();

// Get current page number for pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10; // Number of posts per page

// Get posts with pagination
$posts = $postModel->paginate($page, $perPage);

// Get categories for filtering
$categories = $categoryModel->all();

// Render the blog page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog - EAVA</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body>
    <header>
        <h1>Blog</h1>
        <nav>
            <ul>
                <?php foreach ($categories as $category): ?>
                    <li><a href="?category=<?= $category['slug'] ?>"><?= $category['name'] ?></a></li>
                <?php endforeach; ?>
            </ul>
        </nav>
    </header>
    <main>
        <?php if ($posts['data']): ?>
            <ul>
                <?php foreach ($posts['data'] as $post): ?>
                    <li>
                        <h2><a href="/post/<?= $post['slug'] ?>"><?= $post['title'] ?></a></h2>
                        <p><?= $post['excerpt'] ?></p>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="pagination">
                <?php for ($i = 1; $i <= $posts['last_page']; $i++): ?>
                    <a href="?page=<?= $i ?>"><?= $i ?></a>
                <?php endfor; ?>
            </div>
        <?php else: ?>
            <p>No posts found.</p>
        <?php endif; ?>
    </main>
    <footer>
        <p>&copy; 2023 EAVA. All rights reserved.</p>
    </footer>
</body>
</html>