<?php
/**
 * Dashboard Template
 * Displays overview statistics and recent activity
 */

function renderDashboardCard($title, $value, $icon, $color = 'blue', $change = null) {
    ?>
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-700"><?= $title ?></h3>
            <div class="w-12 h-12 rounded-full bg-<?= $color ?>-100 flex items-center justify-center">
                <i class="fas <?= $icon ?> text-2xl text-<?= $color ?>-500"></i>
            </div>
        </div>
        <div class="text-3xl font-bold mb-2"><?= $value ?></div>
        <?php if ($change !== null): ?>
            <div class="flex items-center text-sm">
                <i class="fas fa-<?= $change >= 0 ? 'arrow-up text-green-500' : 'arrow-down text-red-500' ?> mr-1"></i>
                <span class="<?= $change >= 0 ? 'text-green-500' : 'text-red-500' ?>">
                    <?= abs($change) ?>%
                </span>
                <span class="text-gray-500 ml-1">vs last month</span>
            </div>
        <?php endif; ?>
    </div>
    <?php
}

function renderChart($id, $type, $data, $options = []) {
    $options = array_merge([
        'height' => '300px',
        'responsive' => true,
        'maintainAspectRatio' => false
    ], $options);
    ?>
    <div style="height: <?= $options['height'] ?>">
        <canvas id="<?= $id ?>"></canvas>
    </div>
    <script>
        new Chart(document.getElementById('<?= $id ?>'), {
            type: '<?= $type ?>',
            data: <?= json_encode($data) ?>,
            options: <?= json_encode($options) ?>
        });
    </script>
    <?php
}

function renderActivityItem($item) {
    $icons = [
        'donation' => 'fa-donate text-green-500',
        'campaign' => 'fa-bullhorn text-blue-500',
        'event' => 'fa-calendar text-purple-500',
        'user' => 'fa-user text-yellow-500',
        'post' => 'fa-newspaper text-red-500'
    ];
    ?>
    <div class="flex items-start space-x-4 p-4 hover:bg-gray-50 rounded-lg transition-colors">
        <div class="flex-shrink-0">
            <div class="w-10 h-10 rounded-full bg-<?= explode(' ', $icons[$item['type']])[1] ?>-100 flex items-center justify-center">
                <i class="fas <?= $icons[$item['type']] ?>"></i>
            </div>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-900">
                <?= htmlspecialchars($item['title']) ?>
            </p>
            <p class="text-sm text-gray-500">
                <?= htmlspecialchars($item['description']) ?>
            </p>
        </div>
        <div class="flex-shrink-0 text-sm text-gray-500">
            <?= Utility::timeAgo($item['timestamp']) ?>
        </div>
    </div>
    <?php
}

function renderDashboard($data) {
    ?>
    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <?php
        renderDashboardCard(
            'Total Donations',
            '$' . number_format($data['donations']['total']),
            'fa-donate',
            'green',
            $data['donations']['change']
        );

        renderDashboardCard(
            'Active Campaigns',
            $data['campaigns']['active'],
            'fa-bullhorn',
            'blue',
            $data['campaigns']['change']
        );

        renderDashboardCard(
            'Upcoming Events',
            $data['events']['upcoming'],
            'fa-calendar',
            'purple',
            $data['events']['change']
        );

        renderDashboardCard(
            'Total Members',
            $data['users']['total'],
            'fa-users',
            'yellow',
            $data['users']['change']
        );
        ?>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Donations Chart -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-semibold mb-4">Donation Trends</h3>
            <?php
            renderChart('donationsChart', 'line', [
                'labels' => array_keys($data['donations']['monthly']),
                'datasets' => [[
                    'label' => 'Monthly Donations',
                    'data' => array_values($data['donations']['monthly']),
                    'borderColor' => '#10B981',
                    'tension' => 0.4
                ]]
            ]);
            ?>
        </div>

        <!-- Campaign Progress -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-semibold mb-4">Campaign Progress</h3>
            <?php
            renderChart('campaignsChart', 'doughnut', [
                'labels' => ['Completed', 'Active', 'Upcoming'],
                'datasets' => [[
                    'data' => [
                        $data['campaigns']['completed'],
                        $data['campaigns']['active'],
                        $data['campaigns']['upcoming']
                    ],
                    'backgroundColor' => ['#10B981', '#3B82F6', '#8B5CF6']
                ]]
            ]);
            ?>
        </div>
    </div>

    <!-- Recent Activity & Tasks -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Recent Activity -->
        <div class="lg:col-span-2 bg-white rounded-lg shadow-lg">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold">Recent Activity</h3>
            </div>
            <div class="divide-y divide-gray-200">
                <?php foreach ($data['activity'] as $item): ?>
                    <?php renderActivityItem($item); ?>
                <?php endforeach; ?>
            </div>
            <div class="p-4 bg-gray-50 rounded-b-lg">
                <a href="/admin/activity" class="text-sm text-blue-600 hover:text-blue-800">
                    View all activity <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        </div>

        <!-- Tasks & Reminders -->
        <div class="bg-white rounded-lg shadow-lg">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold">Tasks & Reminders</h3>
            </div>
            <div class="p-6 space-y-4">
                <?php foreach ($data['tasks'] as $task): ?>
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <input type="checkbox" 
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                   <?= $task['completed'] ? 'checked' : '' ?>>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900 <?= $task['completed'] ? 'line-through' : '' ?>">
                                <?= htmlspecialchars($task['title']) ?>
                            </p>
                            <?php if ($task['due_date']): ?>
                                <p class="text-sm text-gray-500">
                                    Due <?= Utility::timeAgo($task['due_date']) ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="p-4 bg-gray-50 rounded-b-lg">
                <button type="button" 
                        onclick="showAddTaskModal()"
                        class="text-sm text-blue-600 hover:text-blue-800">
                    Add new task <i class="fas fa-plus ml-1"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Add Task Modal -->
    <div id="addTaskModal" class="modal hidden">
        <div class="modal-backdrop"></div>
        <div class="modal-content max-w-lg w-full mx-4">
            <div class="p-6">
                <h3 class="text-lg font-semibold mb-4">Add New Task</h3>
                <form id="addTaskForm" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Title</label>
                        <input type="text" 
                               name="title" 
                               required 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Due Date</label>
                        <input type="datetime-local" 
                               name="due_date" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Priority</label>
                        <select name="priority" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" 
                                onclick="hideAddTaskModal()"
                                class="btn btn-outline">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="btn btn-primary">
                            Add Task
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Task management
        function showAddTaskModal() {
            document.getElementById('addTaskModal').classList.remove('hidden');
        }

        function hideAddTaskModal() {
            document.getElementById('addTaskModal').classList.add('hidden');
        }

        document.getElementById('addTaskForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            try {
                const response = await fetch('/admin/api/tasks', {
                    method: 'POST',
                    body: formData
                });
                
                if (response.ok) {
                    notifications.success('Task added successfully');
                    hideAddTaskModal();
                    location.reload();
                } else {
                    throw new Error('Failed to add task');
                }
            } catch (error) {
                notifications.error(error.message);
            }
        });

        // Initialize date/time pickers
        flatpickr('input[type="datetime-local"]', {
            enableTime: true,
            dateFormat: 'Y-m-d H:i'
        });
    </script>
    <?php
}
?>

<!-- Usage Example -->
<?php if (!isset($hideExample)): ?>
    <!--
    <?php
    $dashboardData = [
        'donations' => [
            'total' => 50000,
            'change' => 12.5,
            'monthly' => [
                'Jan' => 5000,
                'Feb' => 6000,
                'Mar' => 4500,
                // ...
            ]
        ],
        'campaigns' => [
            'active' => 5,
            'completed' => 10,
            'upcoming' => 3,
            'change' => -5
        ],
        'events' => [
            'upcoming' => 8,
            'change' => 25
        ],
        'users' => [
            'total' => 1200,
            'change' => 8.3
        ],
        'activity' => [
            [
                'type' => 'donation',
                'title' => 'New Donation Received',
                'description' => 'John Doe donated $500',
                'timestamp' => '2024-01-20 15:30:00'
            ],
            // ...
        ],
        'tasks' => [
            [
                'title' => 'Review grant applications',
                'completed' => false,
                'due_date' => '2024-01-25 17:00:00'
            ],
            // ...
        ]
    ];

    renderDashboard($dashboardData);
    ?>
    -->
<?php endif; ?>