<?php
require_once __DIR__ . '/../../classes/Donation.php';

$donationModel = new Donation();

// Get current page number for pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10; // Number of donations per page

// Get donations with pagination
$donations = $donationModel->paginate($page, $perPage);

// Get donation statistics
$stats = $donationModel->getStatistics();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donations - EAVA</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
    <script src="https://js.stripe.com/v3/"></script>
    <script src="https://checkout.flutterwave.com/v3.js"></script>
</head>
<body>
    <header>
        <h1>Support Our Cause</h1>
    </header>
    <main>
        <section class="donation-stats">
            <h2>Impact of Your Donations</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Donations</h3>
                    <p><?= $stats['total_count'] ?></p>
                </div>
                <div class="stat-card">
                    <h3>Total Amount</h3>
                    <p><?= Utility::formatCurrency($stats['total_amount']) ?></p>
                </div>
            </div>
        </section>

        <section class="donation-form">
            <h2>Make a Donation</h2>
            <form id="donation-form" action="/process-donation.php" method="POST">
                <div class="form-group">
                    <label for="amount">Donation Amount:</label>
                    <div class="amount-buttons">
                        <button type="button" class="amount-preset" data-amount="10">$10</button>
                        <button type="button" class="amount-preset" data-amount="25">$25</button>
                        <button type="button" class="amount-preset" data-amount="50">$50</button>
                        <button type="button" class="amount-preset" data-amount="100">$100</button>
                    </div>
                    <input type="number" id="amount" name="amount" min="1" required>
                </div>

                <div class="form-group">
                    <label for="donor_name">Name:</label>
                    <input type="text" id="donor_name" name="donor_name" required>
                </div>

                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="payment_method">Payment Method:</label>
                    <select id="payment_method" name="payment_method" required>
                        <option value="stripe">Credit Card (Stripe)</option>
                        <option value="flutterwave">Flutterwave</option>
                        <option value="paypal">PayPal</option>
                    </select>
                </div>

                <div id="card-element">
                    <!-- Stripe Elements Placeholder -->
                </div>

                <button type="submit" class="donate-button">Donate Now</button>
            </form>
        </section>

        <section class="recent-donations">
            <h2>Recent Donations</h2>
            <?php if ($donations['data']): ?>
                <ul class="donations-list">
                    <?php foreach ($donations['data'] as $donation): ?>
                        <li>
                            <span class="donor-name"><?= htmlspecialchars($donation['donor_name']) ?></span>
                            <span class="donation-amount"><?= Utility::formatCurrency($donation['amount']) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $donations['last_page']; $i++): ?>
                        <a href="?page=<?= $i ?>"><?= $i ?></a>
                    <?php endfor; ?>
                </div>
            <?php else: ?>
                <p>No donations yet. Be the first to donate!</p>
            <?php endif; ?>
        </section>
    </main>

    <script>
        // Initialize Stripe Elements
        const stripe = Stripe('your-publishable-key');
        const elements = stripe.elements();
        const card = elements.create('card');
        card.mount('#card-element');

        // Handle form submission
        document.getElementById('donation-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const form = e.target;
            const paymentMethod = form.payment_method.value;

            switch(paymentMethod) {
                case 'stripe':
                    handleStripePayment();
                    break;
                case 'flutterwave':
                    handleFlutterwavePayment();
                    break;
                case 'paypal':
                    handlePayPalPayment();
                    break;
            }
        });

        // Handle amount preset buttons
        document.querySelectorAll('.amount-preset').forEach(button => {
            button.addEventListener('click', () => {
                document.getElementById('amount').value = button.dataset.amount;
            });
        });

        // Payment handling functions
        async function handleStripePayment() {
            // Implement Stripe payment logic
        }

        function handleFlutterwavePayment() {
            // Implement Flutterwave payment logic
        }

        function handlePayPalPayment() {
            // Implement PayPal payment logic
        }
    </script>
    <footer>
        <p>&copy; 2023 EAVA. All rights reserved.</p>
    </footer>
</body>
</html>