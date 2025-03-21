<?php
require_once __DIR__ . '/../../classes/Settings.php';
$settings = new Settings();

// Set meta tags
$meta = [
    'title' => 'Donate - ' . $settings->get('site_name'),
    'description' => 'Support our mission by making a donation. Your contribution helps us create positive change in our community.',
    'type' => 'website'
];

// Set breadcrumbs
$breadcrumbs = [
    'Donate' => null
];

ob_start();
?>

<!-- Donation Header -->
<section class="bg-blue-600 text-white py-16">
    <div class="container mx-auto px-4">
        <div class="max-w-3xl mx-auto text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-6">Make a Difference Today</h1>
            <p class="text-xl mb-8">
                Your support enables us to continue our vital work in promoting democracy and diversity through art, education, and community engagement.
            </p>
        </div>
    </div>
</section>

<!-- Donation Options -->
<section class="py-16">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto">
            <!-- Donation Tabs -->
            <div class="mb-12">
                <div class="flex justify-center space-x-4">
                    <button onclick="switchDonationType('one-time')" 
                            class="donation-type-btn active px-6 py-3 rounded-full border-2 border-blue-600 text-blue-600 font-medium hover:bg-blue-50 transition-colors">
                        One-time Donation
                    </button>
                    <button onclick="switchDonationType('monthly')" 
                            class="donation-type-btn px-6 py-3 rounded-full border-2 border-gray-300 text-gray-700 font-medium hover:border-blue-600 hover:text-blue-600 transition-colors">
                        Monthly Donation
                    </button>
                </div>
            </div>

            <!-- Donation Form -->
            <form id="donationForm" 
                  action="/api/donations/process" 
                  method="POST" 
                  class="bg-white rounded-lg shadow-lg p-8"
                  data-ajax>
                <input type="hidden" name="donation_type" id="donationType" value="one-time">

                <!-- Amount Selection -->
                <div class="mb-8">
                    <label class="block text-lg font-semibold mb-4">Select Amount</label>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <?php
                        $amounts = [25, 50, 100, 250, 500, 1000];
                        foreach ($amounts as $amount):
                        ?>
                            <button type="button"
                                    onclick="selectAmount(<?= $amount ?>)"
                                    class="amount-btn px-6 py-4 rounded-lg border-2 border-gray-300 text-center hover:border-blue-600 hover:text-blue-600 transition-colors">
                                $<?= number_format($amount) ?>
                            </button>
                        <?php endforeach; ?>
                        <div class="col-span-2">
                            <input type="number"
                                   id="customAmount"
                                   name="amount"
                                   min="1"
                                   step="0.01"
                                   placeholder="Custom Amount"
                                   class="w-full px-6 py-4 rounded-lg border-2 border-gray-300 focus:border-blue-600 focus:ring-blue-600">
                        </div>
                    </div>
                </div>

                <!-- Personal Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                        <input type="text"
                               name="first_name"
                               required
                               class="w-full px-4 py-2 rounded-lg border-gray-300 focus:border-blue-600 focus:ring-blue-600">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                        <input type="text"
                               name="last_name"
                               required
                               class="w-full px-4 py-2 rounded-lg border-gray-300 focus:border-blue-600 focus:ring-blue-600">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                        <input type="email"
                               name="email"
                               required
                               class="w-full px-4 py-2 rounded-lg border-gray-300 focus:border-blue-600 focus:ring-blue-600">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                        <input type="tel"
                               name="phone"
                               class="w-full px-4 py-2 rounded-lg border-gray-300 focus:border-blue-600 focus:ring-blue-600">
                    </div>
                </div>

                <!-- Payment Information -->
                <div class="mb-8">
                    <label class="block text-lg font-semibold mb-4">Payment Method</label>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <button type="button"
                                onclick="selectPaymentMethod('credit-card')"
                                class="payment-method-btn active flex items-center justify-center px-6 py-4 rounded-lg border-2 border-blue-600 text-blue-600 hover:bg-blue-50 transition-colors">
                            <i class="far fa-credit-card mr-2"></i>
                            Credit Card
                        </button>
                        <button type="button"
                                onclick="selectPaymentMethod('paypal')"
                                class="payment-method-btn flex items-center justify-center px-6 py-4 rounded-lg border-2 border-gray-300 hover:border-blue-600 hover:text-blue-600 transition-colors">
                            <i class="fab fa-paypal mr-2"></i>
                            PayPal
                        </button>
                        <button type="button"
                                onclick="selectPaymentMethod('bank')"
                                class="payment-method-btn flex items-center justify-center px-6 py-4 rounded-lg border-2 border-gray-300 hover:border-blue-600 hover:text-blue-600 transition-colors">
                            <i class="fas fa-university mr-2"></i>
                            Bank Transfer
                        </button>
                    </div>
                </div>

                <!-- Credit Card Form -->
                <div id="creditCardForm" class="mb-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Card Number</label>
                            <div class="relative">
                                <input type="text"
                                       id="cardNumber"
                                       required
                                       placeholder="1234 5678 9012 3456"
                                       class="w-full px-4 py-2 rounded-lg border-gray-300 focus:border-blue-600 focus:ring-blue-600">
                                <div class="absolute right-4 top-1/2 transform -translate-y-1/2">
                                    <i class="fab fa-cc-visa text-gray-400 mr-1"></i>
                                    <i class="fab fa-cc-mastercard text-gray-400 mr-1"></i>
                                    <i class="fab fa-cc-amex text-gray-400"></i>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Expiration Date</label>
                            <input type="text"
                                   id="expiryDate"
                                   required
                                   placeholder="MM/YY"
                                   class="w-full px-4 py-2 rounded-lg border-gray-300 focus:border-blue-600 focus:ring-blue-600">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">CVV</label>
                            <input type="text"
                                   id="cvv"
                                   required
                                   placeholder="123"
                                   class="w-full px-4 py-2 rounded-lg border-gray-300 focus:border-blue-600 focus:ring-blue-600">
                        </div>
                    </div>
                </div>

                <!-- Additional Options -->
                <div class="mb-8">
                    <div class="space-y-4">
                        <label class="flex items-center">
                            <input type="checkbox"
                                   name="cover_fees"
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <span class="ml-2 text-gray-700">
                                Cover transaction fees (3%)
                            </span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox"
                                   name="anonymous"
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <span class="ml-2 text-gray-700">
                                Make this donation anonymous
                            </span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox"
                                   name="newsletter"
                                   checked
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <span class="ml-2 text-gray-700">
                                Subscribe to our newsletter
                            </span>
                        </label>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit"
                        class="w-full bg-blue-600 text-white py-4 px-6 rounded-lg text-lg font-semibold hover:bg-blue-700 transition-colors">
                    Complete Donation
                </button>

                <!-- Security Notice -->
                <p class="text-center text-sm text-gray-500 mt-4">
                    <i class="fas fa-lock mr-1"></i>
                    Your payment information is securely processed through Stripe.
                </p>
            </form>
        </div>
    </div>
</section>

<!-- Impact Section -->
<section class="bg-gray-100 py-16">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto">
            <h2 class="text-3xl font-bold text-center mb-12">Your Impact</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-graduation-cap text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Education</h3>
                    <p class="text-gray-600">
                        Support educational programs that empower future leaders.
                    </p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-paint-brush text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Arts</h3>
                    <p class="text-gray-600">
                        Fund artistic initiatives that celebrate diversity.
                    </p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 bg-purple-100 text-purple-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-users text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Community</h3>
                    <p class="text-gray-600">
                        Build stronger, more inclusive communities.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    // Initialize form handling
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize card validation
        const cardNumber = document.getElementById('cardNumber');
        const expiryDate = document.getElementById('expiryDate');
        const cvv = document.getElementById('cvv');

        // Format card number
        cardNumber.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            value = value.replace(/(\d{4})/g, '$1 ').trim();
            e.target.value = value.substring(0, 19);
        });

        // Format expiry date
        expiryDate.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.substring(0, 2) + '/' + value.substring(2);
            }
            e.target.value = value.substring(0, 5);
        });

        // Format CVV
        cvv.addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/\D/g, '').substring(0, 4);
        });
    });

    // Handle donation type selection
    function switchDonationType(type) {
        document.getElementById('donationType').value = type;
        document.querySelectorAll('.donation-type-btn').forEach(btn => {
            btn.classList.remove('active', 'border-blue-600', 'text-blue-600');
            btn.classList.add('border-gray-300', 'text-gray-700');
        });
        event.target.classList.add('active', 'border-blue-600', 'text-blue-600');
        event.target.classList.remove('border-gray-300', 'text-gray-700');
    }

    // Handle amount selection
    function selectAmount(amount) {
        document.getElementById('customAmount').value = amount;
        document.querySelectorAll('.amount-btn').forEach(btn => {
            btn.classList.remove('active', 'border-blue-600', 'text-blue-600');
            btn.classList.add('border-gray-300', 'text-gray-700');
        });
        event.target.classList.add('active', 'border-blue-600', 'text-blue-600');
        event.target.classList.remove('border-gray-300', 'text-gray-700');
    }

    // Handle payment method selection
    function selectPaymentMethod(method) {
        document.querySelectorAll('.payment-method-btn').forEach(btn => {
            btn.classList.remove('active', 'border-blue-600', 'text-blue-600');
            btn.classList.add('border-gray-300', 'text-gray-700');
        });
        event.target.classList.add('active', 'border-blue-600', 'text-blue-600');
        event.target.classList.remove('border-gray-300', 'text-gray-700');

        // Show/hide credit card form
        document.getElementById('creditCardForm').style.display = 
            method === 'credit-card' ? 'block' : 'none';
    }
</script>

<?php
$content = ob_get_clean();

// Include layout
include __DIR__ . '/layout.php';
?>