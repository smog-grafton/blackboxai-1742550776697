<?php
require_once __DIR__ . '/../../classes/Event.php';
require_once __DIR__ . '/../../classes/Category.php';

$eventModel = new Event();
$categoryModel = new Category();

// Handle form submission for creating/editing a festival event
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'title' => $_POST['title'],
        'description' => $_POST['description'],
        'start_date' => $_POST['start_date'],
        'end_date' => $_POST['end_date'],
        'location' => $_POST['location'],
        'category_id' => $_POST['category_id'],
        'status' => $_POST['status'],
        'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
        'slug' => Utility::generateSlug($_POST['title'])
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

    // Create or update event
    if (isset($_POST['event_id'])) {
        $eventModel->update($_POST['event_id'], $data);
    } else {
        $eventModel->create($data);
    }

    header('Location: /admin/festival.php');
    exit;
}

// Get event data if editing
$event = null;
if (isset($_GET['event_id'])) {
    $event = $eventModel->find($_GET['event_id']);
}

// Get categories for the dropdown
$categories = $categoryModel->all();

// Get festival statistics
$stats = $eventModel->getFestivalStatistics();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Festival - EAVA Admin</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100">
    <header class="bg-white shadow">
        <div class="container mx-auto px-4 py-6">
            <h1 class="text-3xl font-bold text-gray-800">
                <?= $event ? 'Edit Festival Event' : 'Create Festival Event' ?>
            </h1>
        </div>
    </header>

    <main class="container mx-auto px-4 py-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Main Form -->
            <div class="md:col-span-2">
                <form action="" method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow p-6">
                    <input type="hidden" name="event_id" value="<?= $event['id'] ?? '' ?>">
                    
                    <div class="mb-6">
                        <label for="title" class="block text-gray-700 font-medium mb-2">Title:</label>
                        <input type="text" name="title" id="title" 
                               value="<?= $event['title'] ?? '' ?>" 
                               class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                               required>
                    </div>

                    <div class="mb-6">
                        <label for="description" class="block text-gray-700 font-medium mb-2">Description:</label>
                        <textarea name="description" id="description" rows="6" 
                                  class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                  required><?= $event['description'] ?? '' ?></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="start_date" class="block text-gray-700 font-medium mb-2">Start Date & Time:</label>
                            <input type="datetime-local" name="start_date" id="start_date" 
                                   value="<?= $event['start_date'] ?? '' ?>" 
                                   class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   required>
                        </div>

                        <div>
                            <label for="end_date" class="block text-gray-700 font-medium mb-2">End Date & Time:</label>
                            <input type="datetime-local" name="end_date" id="end_date" 
                                   value="<?= $event['end_date'] ?? '' ?>" 
                                   class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   required>
                        </div>
                    </div>

                    <div class="mb-6">
                        <label for="location" class="block text-gray-700 font-medium mb-2">Location:</label>
                        <input type="text" name="location" id="location" 
                               value="<?= $event['location'] ?? '' ?>" 
                               class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                               required>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="category_id" class="block text-gray-700 font-medium mb-2">Category:</label>
                            <select name="category_id" id="category_id" 
                                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    required>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['id'] ?>" 
                                            <?= (isset($event) && $event['category_id'] == $category['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($category['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label for="status" class="block text-gray-700 font-medium mb-2">Status:</label>
                            <select name="status" id="status" 
                                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    required>
                                <option value="draft" <?= (isset($event) && $event['status'] == 'draft') ? 'selected' : '' ?>>
                                    Draft
                                </option>
                                <option value="published" <?= (isset($event) && $event['status'] == 'published') ? 'selected' : '' ?>>
                                    Published
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="flex items-center">
                            <input type="checkbox" name="is_featured" 
                                   <?= (isset($event) && $event['is_featured']) ? 'checked' : '' ?>
                                   class="form-checkbox h-5 w-5 text-blue-500">
                            <span class="ml-2 text-gray-700">Feature this event</span>
                        </label>
                    </div>

                    <div class="mb-6">
                        <label for="featured_image" class="block text-gray-700 font-medium mb-2">Featured Image:</label>
                        <?php if (isset($event['featured_image'])): ?>
                            <div class="mb-2">
                                <img src="<?= $event['featured_image'] ?>" alt="Current featured image" class="w-32 h-32 object-cover">
                            </div>
                        <?php endif; ?>
                        <input type="file" name="featured_image" id="featured_image" 
                               class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                               accept="image/*">
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" 
                                class="bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <?= $event ? 'Update Event' : 'Create Event' ?>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Sidebar Stats -->
            <div class="md:col-span-1">
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Festival Statistics</h2>
                    <div class="space-y-4">
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <h3 class="text-gray-600 mb-2">Total Events</h3>
                            <p class="text-2xl font-bold"><?= $stats['total_events'] ?></p>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <h3 class="text-gray-600 mb-2">Featured Events</h3>
                            <p class="text-2xl font-bold"><?= $stats['featured_events'] ?></p>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <h3 class="text-gray-600 mb-2">Upcoming Events</h3>
                            <p class="text-2xl font-bold"><?= $stats['upcoming_events'] ?></p>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <h3 class="text-gray-600 mb-2">Total Registrations</h3>
                            <p class="text-2xl font-bold"><?= $stats['total_registrations'] ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-gray-800 text-white py-8 mt-8">
        <div class="container mx-auto px-4 text-center">
            <p>&copy; 2023 EAVA Festival. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>