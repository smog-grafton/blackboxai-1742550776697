<?php
require_once __DIR__ . '/../../classes/Program.php';
require_once __DIR__ . '/../../classes/Category.php';

$programModel = new Program();
$categoryModel = new Category();

// Handle form submission for creating/editing a program
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'title' => $_POST['title'],
        'description' => $_POST['description'],
        'goal_amount' => $_POST['goal_amount'],
        'status' => $_POST['status'],
        'category_id' => $_POST['category_id'],
        'slug' => Utility::generateSlug($_POST['title']),
    ];

    // Create or update program
    if (isset($_POST['program_id'])) {
        $programModel->update($_POST['program_id'], $data);
    } else {
        $programModel->create($data);
    }

    header('Location: /admin/programs.php');
    exit;
}

// Get program data if editing
$program = null;
if (isset($_GET['program_id'])) {
    $program = $programModel->find($_GET['program_id']);
}

// Get categories for the dropdown
$categories = $categoryModel->all();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $program ? 'Edit Program' : 'Create Program' ?> - EAVA</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body>
    <header>
        <h1><?= $program ? 'Edit Program' : 'Create Program' ?></h1>
    </header>
    <main>
        <form action="" method="POST">
            <input type="hidden" name="program_id" value="<?= $program['id'] ?? '' ?>">
            <label for="title">Title:</label>
            <input type="text" name="title" id="title" value="<?= $program['title'] ?? '' ?>" required>

            <label for="description">Description:</label>
            <textarea name="description" id="description" required><?= $program['description'] ?? '' ?></textarea>

            <label for="goal_amount">Goal Amount:</label>
            <input type="number" name="goal_amount" id="goal_amount" value="<?= $program['goal_amount'] ?? '' ?>" required>

            <label for="category_id">Category:</label>
            <select name="category_id" id="category_id" required>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= $category['id'] ?>" <?= (isset($program) && $program['category_id'] == $category['id']) ? 'selected' : '' ?>><?= $category['name'] ?></option>
                <?php endforeach; ?>
            </select>

            <label for="status">Status:</label>
            <select name="status" id="status" required>
                <option value="draft" <?= (isset($program) && $program['status'] == 'draft') ? 'selected' : '' ?>>Draft</option>
                <option value="active" <?= (isset($program) && $program['status'] == 'active') ? 'selected' : '' ?>>Active</option>
                <option value="completed" <?= (isset($program) && $program['status'] == 'completed') ? 'selected' : '' ?>>Completed</option>
            </select>

            <button type="submit"><?= $program ? 'Update Program' : 'Create Program' ?></button>
        </form>
    </main>
    <footer>
        <p>&copy; 2023 EAVA. All rights reserved.</p>
    </footer>
</body>
</html>