<?php
require_once __DIR__ . '/../../classes/Event.php';
require_once __DIR__ . '/../../classes/Category.php';

$eventModel = new Event();
$categoryModel = new Category();

// Get current page number for pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10; // Number of events per page

// Get events with pagination
$events = $eventModel->paginate($page, $perPage);

// Get categories for filtering
$categories = $categoryModel->all();

// Render the events page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events - EAVA</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body>
    <header>
        <h1>Events</h1>
        <nav>
            <ul>
                <?php foreach ($categories as $category): ?>
                    <li><a href="?category=<?= $category['slug'] ?>"><?= $category['name'] ?></a></li>
                <?php endforeach; ?>
            </ul>
        </nav>
    </header>
    <main>
        <?php if ($events['data']): ?>
            <ul>
                <?php foreach ($events['data'] as $event): ?>
                    <li>
                        <h2><a href="/event/<?= $event['slug'] ?>"><?= $event['title'] ?></a></h2>
                        <p><?= $event['description'] ?></p>
                        <p><strong>Date:</strong> <?= date('F j, Y', strtotime($event['start_date'])) ?></p>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="pagination">
                <?php for ($i = 1; $i <= $events['last_page']; $i++): ?>
                    <a href="?page=<?= $i ?>"><?= $i ?></a>
                <?php endfor; ?>
            </div>
        <?php else: ?>
            <p>No events found.</p>
        <?php endif; ?>
    </main>
    <footer>
        <p>&copy; 2023 EAVA. All rights reserved.</p>
    </footer>
</body>
</html>