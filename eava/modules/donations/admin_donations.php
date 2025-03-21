<?php
require_once __DIR__ . '/../../classes/Donation.php';

$donationModel = new Donation();

// Get current page number for pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 20; // Number of donations per page

// Get filter parameters
$status = isset($_GET['status']) ? $_GET['status'] : null;
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : null;
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : null;

// Get donations with filters and pagination
$conditions = [];
if ($status) {
    $conditions['status'] = $status;
}
if ($startDate && $endDate) {
    $donations = $donationModel->getByDateRange($startDate, $endDate, $status);
} else {
    $donations = $donationModel->paginate($page, $perPage, $conditions);
}

// Get donation statistics
$stats = $donationModel->getStatistics();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Donations - EAVA Admin</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <header>
        <h1>Manage Donations</h1>
    </header>
    <main>
        <section class="donation-stats">
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Donations</h3>
                    <p><?= $stats['total_count'] ?></p>
                </div>
                <div class="stat-card">
                    <h3>Pending Donations</h3>
                    <p><?= $stats['pending_count'] ?></p>
                </div>
                <div class="stat-card">
                    <h3>Failed Donations</h3>
                    <p><?= $stats['failed_count'] ?></p>
                </div>
            </div>

            <div class="chart-container">
                <canvas id="donationsChart"></canvas>
            </div>
        </section>

        <section class="donation-filters">
            <form action="" method="GET">
                <div class="form-group">
                    <label for="status">Status:</label>
                    <select name="status" id="status">
                        <option value="">All</option>
                        <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="failed" <?= $status === 'failed' ? 'selected' : '' ?>>Failed</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="start_date">Start Date:</label>
                    <input type="date" name="start_date" id="start_date" value="<?= $startDate ?>">
                </div>

                <div class="form-group">
                    <label for="end_date">End Date:</label>
                    <input type="date" name="end_date" id="end_date" value="<?= $endDate ?>">
                </div>

                <button type="submit">Filter</button>
            </form>
        </section>

        <section class="donations-list">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Donor Name</th>
                        <th>Email</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($donations['data'] as $donation): ?>
                        <tr>
                            <td><?= $donation['id'] ?></td>
                            <td><?= htmlspecialchars($donation['donor_name']) ?></td>
                            <td><?= htmlspecialchars($donation['email']) ?></td>
                            <td><?= Utility::formatCurrency($donation['amount'], $donation['currency']) ?></td>
                            <td>
                                <span class="status-badge status-<?= $donation['status'] ?>">
                                    <?= ucfirst($donation['status']) ?>
                                </span>
                            </td>
                            <td><?= date('Y-m-d H:i', strtotime($donation['created_at'])) ?></td>
                            <td>
                                <button onclick="viewDonationDetails(<?= $donation['id'] ?>)">View</button>
                                <?php if ($donation['status'] === 'pending'): ?>
                                    <button onclick="updateDonationStatus(<?= $donation['id'] ?>, 'completed')">Mark Complete</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="pagination">
                <?php for ($i = 1; $i <= $donations['last_page']; $i++): ?>
                    <a href="?page=<?= $i ?>&status=<?= $status ?>&start_date=<?= $startDate ?>&end_date=<?= $endDate ?>" 
                       class="<?= $i === $page ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
        </section>
    </main>

    <script>
        // Initialize donations chart
        const ctx = document.getElementById('donationsChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode(array_column($stats['monthly'], 'month')) ?>,
                datasets: [{
                    label: 'Monthly Donations',
                    data: <?= json_encode(array_column($stats['monthly'], 'total_amount')) ?>,
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // View donation details
        function viewDonationDetails(id) {
            // Implement view details logic
        }

        // Update donation status
        async function updateDonationStatus(id, status) {
            try {
                const response = await fetch('/api/donations/update-status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id, status })
                });

                if (response.ok) {
                    location.reload();
                } else {
                    alert('Failed to update donation status');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred while updating the donation status');
            }
        }
    </script>
    <footer>
        <p>&copy; 2023 EAVA. All rights reserved.</p>
    </footer>
</body>
</html>