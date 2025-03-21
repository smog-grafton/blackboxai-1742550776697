<?php
require_once __DIR__ . '/../classes/Session.php';
require_once __DIR__ . '/../classes/Settings.php';
require_once __DIR__ . '/../classes/Menu.php';

$session = Session::getInstance();
if (!$session->isLoggedIn() || $session->getUser()['role'] !== 'admin') {
    header('Location: /admin/login.php');
    exit;
}

$settings = new Settings();
$menu = new Menu();
$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate CSRF token
        if (!$session->validateCsrfToken($_POST['csrf_token'])) {
            throw new Exception('Invalid security token');
        }

        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'save_menu':
                    $menuData = json_decode($_POST['menu_data'], true);
                    $menu->saveMenu($_POST['menu_location'], $menuData);
                    $message = 'Menu saved successfully';
                    break;

                case 'add_item':
                    $menu->addItem($_POST['menu_location'], [
                        'type' => $_POST['item_type'],
                        'label' => $_POST['item_label'],
                        'url' => $_POST['item_url'],
                        'target' => $_POST['item_target'] ?? '_self',
                        'parent_id' => $_POST['parent_id'] ?? null
                    ]);
                    $message = 'Menu item added successfully';
                    break;

                case 'delete_item':
                    $menu->deleteItem($_POST['menu_location'], $_POST['item_id']);
                    $message = 'Menu item deleted successfully';
                    break;
            }
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$menuLocations = [
    'main_menu' => 'Main Navigation',
    'footer_menu' => 'Footer Navigation',
    'social_menu' => 'Social Links'
];

$pageTitle = 'Menu Management';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Management - <?= htmlspecialchars($settings->get('site_name')) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/modular/sortable.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/modular/sortable.min.js"></script>
</head>
<body class="bg-gray-100">
    <?php include 'includes/header.php'; ?>

    <div class="flex">
        <?php include 'includes/sidebar.php'; ?>

        <main class="flex-1 p-8">
            <div class="max-w-6xl mx-auto">
                <h1 class="text-3xl font-bold mb-8">Menu Management</h1>

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

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Menu Builder -->
                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-lg shadow-lg p-6">
                            <div class="flex items-center justify-between mb-6">
                                <h2 class="text-xl font-semibold">Menu Structure</h2>
                                <select id="menuLocation" 
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <?php foreach ($menuLocations as $key => $label): ?>
                                        <option value="<?= $key ?>"><?= $label ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Menu Items List -->
                            <div id="menuItems" class="space-y-2">
                                <!-- Items will be loaded here dynamically -->
                            </div>

                            <!-- Save Button -->
                            <button type="button" 
                                    onclick="saveMenu()"
                                    class="mt-6 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                Save Menu
                            </button>
                        </div>
                    </div>

                    <!-- Add Menu Items -->
                    <div>
                        <div class="bg-white rounded-lg shadow-lg p-6">
                            <h2 class="text-xl font-semibold mb-6">Add Menu Item</h2>

                            <form id="addItemForm" class="space-y-4">
                                <input type="hidden" name="csrf_token" value="<?= $session->getCsrfToken() ?>">
                                <input type="hidden" name="action" value="add_item">

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Item Type</label>
                                    <select name="item_type" 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="custom">Custom Link</option>
                                        <option value="page">Page</option>
                                        <option value="category">Category</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Label</label>
                                    <input type="text" 
                                           name="item_label" 
                                           required
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">URL</label>
                                    <input type="text" 
                                           name="item_url" 
                                           required
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Target</label>
                                    <select name="item_target"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="_self">Same Window</option>
                                        <option value="_blank">New Window</option>
                                    </select>
                                </div>

                                <button type="submit"
                                        class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                                    Add Item
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Initialize Sortable
        const menuItems = document.getElementById('menuItems');
        new Sortable(menuItems, {
            animation: 150,
            handle: '.drag-handle',
            ghostClass: 'bg-gray-100',
            onEnd: function() {
                // Update menu order
                updateMenuOrder();
            }
        });

        // Load menu items
        function loadMenu(location) {
            fetch(`/api/menu/${location}`)
                .then(response => response.json())
                .then(data => {
                    menuItems.innerHTML = '';
                    data.forEach(item => {
                        menuItems.appendChild(createMenuItem(item));
                    });
                });
        }

        // Create menu item element
        function createMenuItem(item) {
            const div = document.createElement('div');
            div.className = 'bg-white border rounded-lg p-4 flex items-center justify-between';
            div.dataset.id = item.id;

            div.innerHTML = `
                <div class="flex items-center">
                    <button class="drag-handle mr-3 text-gray-400 hover:text-gray-600">
                        <i class="fas fa-grip-vertical"></i>
                    </button>
                    <div>
                        <div class="font-medium">${item.label}</div>
                        <div class="text-sm text-gray-500">${item.url}</div>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <button onclick="editMenuItem(${item.id})" 
                            class="text-blue-600 hover:text-blue-800">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button onclick="deleteMenuItem(${item.id})" 
                            class="text-red-600 hover:text-red-800">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;

            return div;
        }

        // Save menu
        function saveMenu() {
            const location = document.getElementById('menuLocation').value;
            const items = Array.from(menuItems.children).map(item => ({
                id: item.dataset.id,
                order: Array.from(menuItems.children).indexOf(item)
            }));

            fetch('/api/menu/save', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': document.querySelector('[name="csrf_token"]').value
                },
                body: JSON.stringify({
                    location,
                    items
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Menu saved successfully', 'success');
                } else {
                    showNotification('Failed to save menu', 'error');
                }
            });
        }

        // Edit menu item
        function editMenuItem(id) {
            // Implementation for editing menu item
        }

        // Delete menu item
        function deleteMenuItem(id) {
            if (confirm('Are you sure you want to delete this menu item?')) {
                const location = document.getElementById('menuLocation').value;
                
                fetch('/api/menu/delete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': document.querySelector('[name="csrf_token"]').value
                    },
                    body: JSON.stringify({
                        location,
                        id
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadMenu(location);
                        showNotification('Menu item deleted successfully', 'success');
                    } else {
                        showNotification('Failed to delete menu item', 'error');
                    }
                });
            }
        }

        // Show notification
        function showNotification(message, type) {
            // Implementation for showing notifications
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            const location = document.getElementById('menuLocation').value;
            loadMenu(location);

            // Handle location change
            document.getElementById('menuLocation').addEventListener('change', function() {
                loadMenu(this.value);
            });

            // Handle form submission
            document.getElementById('addItemForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                formData.append('menu_location', document.getElementById('menuLocation').value);

                fetch('/api/menu/add', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.reset();
                        loadMenu(document.getElementById('menuLocation').value);
                        showNotification('Menu item added successfully', 'success');
                    } else {
                        showNotification('Failed to add menu item', 'error');
                    }
                });
            });
        });
    </script>
</body>
</html>