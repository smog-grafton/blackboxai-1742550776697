<?php
require_once __DIR__ . '/../../classes/Event.php';
require_once __DIR__ . '/../../classes/Category.php';

$eventModel = new Event();
$categoryModel = new Category();

// Handle form submission for creating/editing an event
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'title' => $_POST['title'],
        'description' => $_POST['description'],
        'start_date' => $_POST['start_date'],
        'end_date' => $_POST['end_date'],
        'category_id' => $_POST['category_id'],
        'status' => $_POST['status'],
        'slug' => Utility::generateSlug($_POST['title']),
    ];

    // Handle featured image upload
    if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../../uploads/media/';
        $fileName = basename($_FILES['featured_image']['name']);
        $targetFilePath = $uploadDir . $fileName;

        // Move uploaded file
        if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $targetFilePath)) {
            $data['featured_image'] = $targetFilePath;
        }
    }

    // Create or update event
    if (isset($_POST['event_id'])) {
        $eventModel->update($_POST['event_id'], $data);
    } else {
        $eventModel->create($data);
    }

    header('Location: /admin/events.php');
    exit;
}

// Get event data if editing
$event = null;
if (isset($_GET['event_id'])) {
    $event = $eventModel->find($_GET['event_id']);
}

// Get categories for the dropdown
$categories = $categoryModel->all();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $event ? 'Edit Event' : 'Create Event' ?> - EAVA</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body>
    <header>
        <h1><?= $event ? 'Edit Event' : 'Create Event' ?></h1>
    </header>
    <main>
        <form action="" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="event_id" value="<?= $event['id'] ?? '' ?>">
            <label for="title">Title:</label>
            <input type="text" name="title" id="title" value="<?= $event['title'] ?? '' ?>" required>

            <label for="description">Description:</label>
            <textarea name="description" id="description" required><?= $event['description'] ?? '' ?></textarea>

            <label for="start_date">Start Date:</label>
            <input type="datetime-local" name="start_date" id="start_date" value="<?= $event['start_date'] ?? '' ?>" required>

            <label for="end_date">End Date:</label>
            <input type="datetime-local" name="end_date" id="end_date" value="<?= $event['end_date'] ?? '' ?>" required>

            <label for="category_id">Category:</label>
            <select name="category_id" id="category_id" required>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= $category['id'] ?>" <?= (isset($event) && $event['category_id'] == $category['id']) ? 'selected' : '' ?>><?= $category['name'] ?></option>
                <?php endforeach; ?>
            </select>

            <label for="status">Status:</label>
            <select name="status" id="status" required>
                <option value="draft" <?= (isset($event) && $event['status'] == 'draft') ? 'selected' : '' ?>>Draft</option>
                <option value="published" <?= (isset($event) && $event['status'] == 'published') ? 'selected' : '' ?>>Published</option>
            </select>

            <label for="featured_image">Featured Image:</label>
            <input type="file" name="featured_image" id="featured_image">

            <button type="submit"><?= $event ? 'Update Event' : 'Create Event' ?></button>
        </form>
    </main>
    <footer>
        <p>&copy; 2023 EAVA. All rights reserved.</p>
    </footer>
</body>
</html>