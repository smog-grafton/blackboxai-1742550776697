<?php
require_once __DIR__ . '/../../classes/Project.php';
require_once __DIR__ . '/../../classes/Category.php';

$projectModel = new Project();
$categoryModel = new Category();

// Handle form submission for creating/editing a project
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'title' => $_POST['title'],
        'description' => $_POST['description'],
        'goal_amount' => $_POST['goal_amount'],
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

    // Create or update project
    if (isset($_POST['project_id'])) {
        $projectModel->update($_POST['project_id'], $data);
    } else {
        $projectModel->create($data);
    }

    header('Location: /admin/projects.php');
    exit;
}

// Get project data if editing
$project = null;
if (isset($_GET['project_id'])) {
    $project = $projectModel->find($_GET['project_id']);
}

// Get categories for the dropdown
$categories = $categoryModel->all();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $project ? 'Edit Project' : 'Create Project' ?> - EAVA</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body>
    <header>
        <h1><?= $project ? 'Edit Project' : 'Create Project' ?></h1>
    </header>
    <main>
        <form action="" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="project_id" value="<?= $project['id'] ?? '' ?>">
            <label for="title">Title:</label>
            <input type="text" name="title" id="title" value="<?= $project['title'] ?? '' ?>" required>

            <label for="description">Description:</label>
            <textarea name="description" id="description" required><?= $project['description'] ?? '' ?></textarea>

            <label for="goal_amount">Goal Amount:</label>
            <input type="number" name="goal_amount" id="goal_amount" value="<?= $project['goal_amount'] ?? '' ?>" required>

            <label for="start_date">Start Date:</label>
            <input type="date" name="start_date" id="start_date" value="<?= $project['start_date'] ?? '' ?>" required>

            <label for="end_date">End Date:</label>
            <input type="date" name="end_date" id="end_date" value="<?= $project['end_date'] ?? '' ?>" required>

            <label for="category_id">Category:</label>
            <select name="category_id" id="category_id" required>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= $category['id'] ?>" <?= (isset($project) && $project['category_id'] == $category['id']) ? 'selected' : '' ?>><?= $category['name'] ?></option>
                <?php endforeach; ?>
            </select>

            <label for="status">Status:</label>
            <select name="status" id="status" required>
                <option value="draft" <?= (isset($project) && $project['status'] == 'draft') ? 'selected' : '' ?>>Draft</option>
                <option value="active" <?= (isset($project) && $project['status'] == 'active') ? 'selected' : '' ?>>Active</option>
                <option value="completed" <?= (isset($project) && $project['status'] == 'completed') ? 'selected' : '' ?>>Completed</option>
            </select>

            <label for="featured_image">Featured Image:</label>
            <input type="file" name="featured_image" id="featured_image">

            <button type="submit"><?= $project ? 'Update Project' : 'Create Project' ?></button>
        </form>
    </main>
    <footer>
        <p>&copy; 2023 EAVA. All rights reserved.</p>
    </footer>
</body>
</html>