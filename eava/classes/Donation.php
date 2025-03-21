<?php
require_once __DIR__ . '/Model.php';

class Donation extends Model {
    protected $table = 'donations';
    protected $fillable = [
        'donor_name',
        'email',
        'amount',
        'currency',
        'payment_method',
        'transaction_id',
        'status'
    ];

    // Payment gateway constants
    const PAYMENT_METHOD_FLUTTERWAVE = 'flutterwave';
    const PAYMENT_METHOD_PAYPAL = 'paypal';
    const PAYMENT_METHOD_STRIPE = 'stripe';

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';

    /**
     * Create a new donation
     */
    public function createDonation($data) {
        try {
            // Validate amount
            if (!is_numeric($data['amount']) || $data['amount'] <= 0) {
                throw new Exception("Invalid donation amount");
            }

            // Validate email
            if (!Utility::isValidEmail($data['email'])) {
                throw new Exception("Invalid email address");
            }

            // Set default status
            if (empty($data['status'])) {
                $data['status'] = self::STATUS_PENDING;
            }

            // Set default currency if not provided
            if (empty($data['currency'])) {
                $data['currency'] = 'USD';
            }

            return $this->create($data);
        } catch (Exception $e) {
            error_log("Create Donation Error: " . $e->getMessage());
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Process Flutterwave payment
     */
    public function processFlutterwavePayment($donationId, $transactionData) {
        try {
            $this->db->beginTransaction();

            $donation = $this->find($donationId);
            if (!$donation) {
                throw new Exception("Donation not found");
            }

            // Verify transaction with Flutterwave
            $verified = $this->verifyFlutterwaveTransaction($transactionData
