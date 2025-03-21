<?php
require_once __DIR__ . '/../classes/Session.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/Settings.php';
require_once __DIR__ . '/../classes/Post.php';
require_once __DIR__ . '/../classes/Event.php';
require_once __DIR__ . '/../classes/Campaign.php';
require_once __DIR__ . '/../classes/Grant.php';
require_once __DIR__ . '/../classes/Donation.php';

$session = Session::getInstance();
if (!$session->isLoggedIn() || $session->getUser()['role'] !== 'admin') {
    header('Location: /admin/login.php');
    exit;
}

$user = $session->getUser();
$settings = new Settings();

// Get statistics
$postModel = new Post();
$eventModel = new Event();
$campaignModel = new Campaign();
$grantModel = new Grant();
$donationModel = new Donation();

$stats = [
    'posts' => $postModel->getStatistics(),
    'events' => $eventModel->getStatistics(),
    'campaigns' => $campaignModel->getStatistics(),
    'grants' => $grantModel->getStatistics(),
    'donations' => $donationModel->getStatistics()
];

$pageTitle = 'Dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?= htmlspecialchars($settings->get('site_name')) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100">
    <?php include 'includes/header.php'; ?>

    <div class="flex">
        <?php include 'includes/sidebar.php'; ?>

        <main class="flex-1 p-8">
            <div class="max-w-7xl mx-auto">
                <!-- Welcome Section -->
                <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
                    <h1 class="text-2xl font-bold mb-2">Welcome back, <?= htmlspecialchars($user['full_name']) ?>!</h1>
                    <p class="text-gray-600">Here's what's happening with your organization today.</p>
                </div>

                <!-- Quick Stats -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Donations -->
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold">Donations</h3>
                            <i class="fas fa-donate text-blue-500 text-2xl"></i>
                        </div>
                        <div class="text-3xl font-bold mb-2">
                            $<?= number_format($stats['donations']['total_amount'], 2) ?>
                        </div>
                        <p class="text-gray-600">
                            <?= number_format($stats['donations']['total_donations']) ?> total donations
                        </p>
                    </div>

                    <!-- Campaigns -->
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold">Campaigns</h3>
                            <i class="fas fa-bullhorn text-green-500 text-2xl"></i>
                        </div>
                        <div class="text-3xl font-bold mb-2">
                            <?= number_format($stats['campaigns']['active_campaigns']) ?>
                        </div>
                        <p class="text-gray-600">
                            Active campaigns
                        </p>
                    </div>

                    <!-- Events -->
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold">Events</h3>
                            <i class="fas fa-calendar text-purple-500 text-2xl"></i>
                        </div>
                        <div class="text-3xl font-bold mb-2">
                            <?= number_format($stats['events']['upcoming']) ?>
                        </div>
                        <p class="text-gray-600">
                            Upcoming events
                        </p>
                    </div>

                    <!-- Grants -->
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold">Grants</h3>
                            <i class="fas fa-hand-holding-usd text-yellow-500 text-2xl"></i>
                        </div>
                        <div class="text-3xl font-bold mb-2">
                            <?= number_format($stats['grants']['open_grants']) ?>
                        </div>
                        <p class="text-gray-600">
                            Open grant applications
                        </p>
                    </div>
                </div>

                <!-- Charts -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                    <!-- Donations Chart -->
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <h3 class="text-lg font-semibold mb-4">Donation Trends</h3>
                        <canvas id="donationsChart"></canvas>
                    </div>

                    <!-- Campaign Progress -->
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <h3 class="text-lg font-semibold mb-4">Campaign Progress</h3>
                        <canvas id="campaignsChart"></canvas>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">Recent Activity</h3>
                    <div class="space-y-4">
                        <?php foreach ($stats['donations']['recent'] ?? [] as $donation): ?>
                            <div class="flex items-center justify-between border-b pb-4">
                                <div>
                                    <p class="font-medium"><?= htmlspecialchars($donation['donor_name']) ?></p>
                                    <p class="text-sm text-gray-600">
                                        Donated $<?= number_format($donation['amount'], 2) ?>
                                        <?php if ($donation['campaign_title']): ?>
                                            to <?= htmlspecialchars($donation['campaign_title']) ?>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <span class="text-sm text-gray-500">
                                    <?= Utility::timeAgo($donation['created_at']) ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Donations Chart
        const donationsCtx = document.getElementById('donationsChart').getContext('2d');
        new Chart(donationsCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode(array_keys($stats['donations']['monthly'])) ?>,
                datasets: [{
                    label: 'Monthly Donations',
                    data: <?= json_encode(array_values($stats['donations']['monthly'])) ?>,
                    borderColor: 'rgb(59, 130, 246)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                }
            }
        });

        // Campaigns Chart
        const campaignsCtx = document.getElementById('campaignsChart').getContext('2d');
        new Chart(campaignsCtx, {
            type: 'doughnut',
            data: {
                labels: ['Completed', 'Active', 'Upcoming'],
                datasets: [{
                    data: [
                        <?= $stats['campaigns']['completed_campaigns'] ?>,
                        <?= $stats['campaigns']['active_campaigns'] ?>,
                        <?= $stats['campaigns']['upcoming_campaigns'] ?>
                    ],
                    backgroundColor: [
                        'rgb(34, 197, 94)',
                        'rgb(59, 130, 246)',
                        'rgb(168, 85, 247)'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                }
            }
        });
    </script>
</body>
</html>