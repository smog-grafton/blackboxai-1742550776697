<?php
require_once __DIR__ . '/../../classes/Session.php';
require_once __DIR__ . '/../../classes/Post.php';
require_once __DIR__ . '/../../classes/Category.php';
require_once __DIR__ . '/../../classes/Validator.php';

$session = Session::getInstance();
$postModel = new Post();
$categoryModel = new Category();

// Check if user is logged in and is admin
if (!$session->isLoggedIn() || $session->getUser()['role'] !== 'admin') {
    header('Location: /admin/login.php');
    exit;
}

$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $validator = new Validator($_POST, [
        'title' => 'required',
        'content' => 'required',
        'category_id' => 'required|numeric'
    ]);

    if ($validator->validate()) {
        try {
            $data = [
                'title' => $_POST['title'],
                'content' => $_POST['content'],
                'excerpt' => $_POST['excerpt'] ?? substr(strip_tags($_POST['content']), 0, 200),
                'category_id' => $_POST['category_id'],
                'status' => $_POST['status'],
                'author_id' => $session->getUser()['id'],
                'slug' => Utility::generateSlug($_POST['title']),
                'is_featured' => isset($_POST['is_featured']) ? 1 : 0
            ];

            // Handle featured image upload
            if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../../uploads/media/';
                $fileName = basename($_FILES['featured_image']['name']);
                $targetFilePath = $uploadDir . $fileName;

                if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $targetFilePath)) {
                    $data['featured_image'] = '/uploads/media/' . $fileName;
                }
            }

            // Create or update post
            if (isset($_POST['post_id'])) {
                $postModel->update($_POST['post_id'], $data);
                $message = 'Post updated successfully';
            } else {
                $postModel->create($data);
                $message = 'Post created successfully';
            }
        } catch (Exception $e) {
            $error = 'Failed to save post: ' . $e->getMessage();
        }
    } else {
        $error = 'Please check the form for errors';
    }
}

// Get post data if editing
$post = null;
if (isset($_GET['post_id'])) {
    $post = $postModel->find($_GET['post_id']);
}

// Get categories for dropdown
$categories = $categoryModel->all();

// Get all posts for listing
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$posts = $postModel->paginate($page, 20);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $post ? 'Edit Post' : 'Create Post' ?> - EAVA Admin</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/5/tinymce.min.js"></script>
    <script>
        tinymce.init({
            selector: '#content',
            height: 400,
            plugins: 'link image code table lists',
            toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist | link image | code'
        });
    </script>
</head>
<body class="bg-gray-100">
    <?php include '../../admin/includes/header.php'; ?>

    <div class="flex">
        <?php include '../../admin/includes/sidebar.php'; ?>

        <main class="flex-1 p-8">
            <div class="max-w-4xl mx-auto">
                <h1 class="text-3xl font-bold mb-8"><?= $post ? 'Edit Post' : 'Create Post' ?></h1>

                <?php if ($message): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        <?= htmlspecialchars($message) ?>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <!-- Post Form -->
                <form action="" method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow p-6 mb-8">
                    <input type="hidden" name="post_id" value="<?= $post['id'] ?? '' ?>">
                    
                    <div class="mb-6">
                        <label for="title" class="block text-gray-700 font-medium mb-2">Title</label>
                        <input type="text" name="title" id="title" 
                               value="<?= $post['title'] ?? '' ?>" 
                               class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                               required>
                    </div>

                    <div class="mb-6">
                        <label for="content" class="block text-gray-700 font-medium mb-2">Content</label>
                        <textarea name="content" id="content" rows="10" 
                                  class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                  required><?= $post['content'] ?? '' ?></textarea>
                    </div>

                    <div class="mb-6">
                        <label for="excerpt" class="block text-gray-700 font-medium mb-2">Excerpt</label>
                        <textarea name="excerpt" id="excerpt" rows="3" 
                                  class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"><?= $post['excerpt'] ?? '' ?></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="category_id" class="block text-gray-700 font-medium mb-2">Category</label>
                            <select name="category_id" id="category_id" 
                                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    required>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['id'] ?>" 
                                            <?= (isset($post) && $post['category_id'] == $category['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($category['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label for="status" class="block text-gray-700 font-medium mb-2">Status</label>
                            <select name="status" id="status" 
                                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    required>
                                <option value="draft" <?= (isset($post) && $post['status'] == 'draft') ? 'selected' : '' ?>>
                                    Draft
                                </option>
                                <option value="published" <?= (isset($post) && $post['status'] == 'published') ? 'selected' : '' ?>>
                                    Published
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-6">
                        <label for="featured_image" class="block text-gray-700 font-medium mb-2">Featured Image</label>
                        <?php if (isset($post['featured_image'])): ?>
                            <div class="mb-2">
                                <img src="<?= $post['featured_image'] ?>" alt="Current featured image" class="w-32 h-32 object-cover">
                            </div>
                        <?php endif; ?>
                        <input type="file" name="featured_image" id="featured_image" 
                               class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                               accept="image/*">
                    </div>

                    <div class="mb-6">
                        <label class="flex items-center">
                            <input type="checkbox" name="is_featured" 
                                   <?= (isset($post) && $post['is_featured']) ? 'checked' : '' ?>
                                   class="form-checkbox h-5 w-5 text-blue-500">
                            <span class="ml-2 text-gray-700">Feature this post</span>
                        </label>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" 
                                class="bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <?= $post ? 'Update Post' : 'Create Post' ?>
                        </button>
                    </div>
                </form>

                <!-- Posts List -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="p-6">
                        <h2 class="text-xl font-semibold mb-4">All Posts</h2>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="bg-gray-50">
                                        <th class="px-4 py-2 text-left">Title</th>
                                        <th class="px-4 py-2 text-left">Category</th>
                                        <th class="px-4 py-2 text-left">Status</th>
                                        <th class="px-4 py-2 text-left">Date</th>
                                        <th class="px-4 py-2 text-left">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    <?php foreach ($posts['data'] as $post): ?>
                                        <tr>
                                            <td class="px-4 py-3"><?= htmlspecialchars($post['title']) ?></td>
                                            <td class="px-4 py-3"><?= htmlspecialchars($post['category_name']) ?></td>
                                            <td class="px-4 py-3">
                                                <span class="px-2 py-1 rounded text-sm 
                                                    <?= $post['status'] === 'published' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' ?>">
                                                    <?= ucfirst($post['status']) ?>
                                                </span>
                                            </td>
                                            <td class="px-4 py-3"><?= date('M j, Y', strtotime($post['created_at'])) ?></td>
                                            <td class="px-4 py-3">
                                                <div class="flex space-x-2">
                                                    <a href="?post_id=<?= $post['id'] ?>" 
                                                       class="text-blue-500 hover:text-blue-700">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button onclick="deletePost(<?= $post['id'] ?>)" 
                                                            class="text-red-500 hover:text-red-700">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($posts['last_page'] > 1): ?>
                            <div class="mt-6 flex justify-center">
                                <div class="flex space-x-2">
                                    <?php for ($i = 1; $i <= $posts['last_page']; $i++): ?>
                                        <a href="?page=<?= $i ?>" 
                                           class="px-4 py-2 border rounded <?= $i === $page ? 'bg-blue-500 text-white' : 'text-gray-600 hover:bg-gray-50' ?>">
                                            <?= $i ?>
                                        </a>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        async function deletePost(id) {
            if (confirm('Are you sure you want to delete this post?')) {
                try {
                    const response = await fetch(`/api/posts/delete.php`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ id })
                    });

                    if (response.ok) {
                        location.reload();
                    } else {
                        alert('Failed to delete post');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the post');
                }
            }
        }
    </script>
</body>
</html>