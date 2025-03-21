<?php
require_once __DIR__ . '/../../classes/Program.php';
require_once __DIR__ . '/../../classes/Category.php';

$programModel = new Program();
$categoryModel = new Category();

// Get current page number for pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10; // Number of programs per page

// Get programs with pagination
$programs = $programModel->paginate($page, $perPage);

// Get categories for filtering
$categories = $categoryModel->all();

// Render the programs page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Programs - EAVA</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body>
    <header>
        <h1>Programs</h1>
        <nav>
            <ul>
                <?php foreach ($categories as $category): ?>
                    <li><a href="?category=<?= $category['slug'] ?>"><?= $category['name'] ?></a></li>
                <?php endforeach; ?>
            </ul>
        </nav>
    </header>
    <main>
        <?php if ($programs['data']): ?>
            <ul>
                <?php foreach ($programs['data'] as $program): ?>
                    <li>
                        <h2><a href="/program/<?= $program['slug'] ?>"><?= $program['title'] ?></a></h2>
                        <p><?= $program['description'] ?></p>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="pagination">
                <?php for ($i = 1; $i <= $programs['last_page']; $i++): ?>
                    <a href="?page=<?= $i ?>"><?= $i ?></a>
                <?php endfor; ?>
            </div>
        <?php else: ?>
            <p>No programs found.</p>
        <?php endif; ?>
    </main>
    <footer>
        <p>&copy; 2023 EAVA. All rights reserved.</p>
    </footer>
</body>
</html>