<?php
require_once __DIR__ . '/../../classes/Post.php';
require_once __DIR__ . '/../../classes/Category.php';

$postModel = new Post();
$categoryModel = new Category();

// Handle form submission for creating/editing a post
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'title' => $_POST['title'],
        'content' => $_POST['content'],
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

    // Create or update post
    if (isset($_POST['post_id'])) {
        $postModel->update($_POST['post_id'], $data);
    } else {
        $postModel->create($data);
    }

    header('Location: /admin/blog.php');
    exit;
}

// Get post data if editing
$post = null;
if (isset($_GET['post_id'])) {
    $post = $postModel->find($_GET['post_id']);
}

// Get categories for the dropdown
$categories = $categoryModel->all();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $post ? 'Edit Post' : 'Create Post' ?> - EAVA</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body>
    <header>
        <h1><?= $post ? 'Edit Post' : 'Create Post' ?></h1>
    </header>
    <main>
        <form action="" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="post_id" value="<?= $post['id'] ?? '' ?>">
            <label for="title">Title:</label>
            <input type="text" name="title" id="title" value="<?= $post['title'] ?? '' ?>" required>

            <label for="content">Content:</label>
            <textarea name="content" id="content" required><?= $post['content'] ?? '' ?></textarea>

            <label for="category_id">Category:</label>
            <select name="category_id" id="category_id" required>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= $category['id'] ?>" <?= (isset($post) && $post['category_id'] == $category['id']) ? 'selected' : '' ?>><?= $category['name'] ?></option>
                <?php endforeach; ?>
            </select>

            <label for="status">Status:</label>
            <select name="status" id="status" required>
                <option value="draft" <?= (isset($post) && $post['status'] == 'draft') ? 'selected' : '' ?>>Draft</option>
                <option value="published" <?= (isset($post) && $post['status'] == 'published') ? 'selected' : '' ?>>Published</option>
            </select>

            <label for="featured_image">Featured Image:</label>
            <input type="file" name="featured_image" id="featured_image">

            <button type="submit"><?= $post ? 'Update Post' : 'Create Post' ?></button>
        </form>
    </main>
    <footer>
        <p>&copy; 2023 EAVA. All rights reserved.</p>
    </footer>
</body>
</html>