<?php
require_once __DIR__ . '/../../classes/Project.php';
require_once __DIR__ . '/../../classes/Category.php';

$projectModel = new Project();
$categoryModel = new Category();

// Get current page number for pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10; // Number of projects per page

// Get projects with pagination
$projects = $projectModel->paginate($page, $perPage);

// Get categories for filtering
$categories = $categoryModel->all();

// Render the projects page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projects - EAVA</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body>
    <header>
        <h1>Projects</h1>
        <nav>
            <ul>
                <?php foreach ($categories as $category): ?>
                    <li><a href="?category=<?= $category['slug'] ?>"><?= $category['name'] ?></a></li>
                <?php endforeach; ?>
            </ul>
        </nav>
    </header>
    <main>
        <?php if ($projects['data']): ?>
            <ul>
                <?php foreach ($projects['data'] as $project): ?>
                    <li>
                        <h2><a href="/project/<?= $project['slug'] ?>"><?= $project['title'] ?></a></h2>
                        <p><?= $project['description'] ?></p>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="pagination">
                <?php for ($i = 1; $i <= $projects['last_page']; $i++): ?>
                    <a href="?page=<?= $i ?>"><?= $i ?></a>
                <?php endfor; ?>
            </div>
        <?php else: ?>
            <p>No projects found.</p>
        <?php endif; ?>
    </main>
    <footer>
        <p>&copy; 2023 EAVA. All rights reserved.</p>
    </footer>
</body>
</html>