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
                $data['status'] = 'pending';
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
     * Update donation status
     */
    public function updateStatus($id, $status, $transactionId = null) {
        try {
            $data = ['status' => $status];
            if ($transactionId) {
                $data['transaction_id'] = $transactionId;
            }
            return $this->update($id, $data);
        } catch (Exception $e) {
            error_log("Update Donation Status Error: " . $e->getMessage());
            throw new Exception("Failed to update donation status");
        }
    }

    /**
     * Get donation statistics
     */
    public function getStatistics() {
        try {
            $stats = [
                'total_count' => $this->count(['status' => 'completed']),
                'pending_count' => $this->count(['status' => 'pending']),
                'failed_count' => $this->count(['status' => 'failed'])
            ];

            // Get total amount by currency
            $sql = "SELECT currency, SUM(amount) as total_amount, COUNT(*) as count 
                    FROM {$this->table} 
                    WHERE status = 'completed' 
                    GROUP BY currency";
            
            $this->db->query($sql);
            $stats['by_currency'] = $this->db->findAll();

            // Get monthly totals for current year
            $sql = "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, 
                           currency, 
                           SUM(amount) as total_amount, 
                           COUNT(*) as count
                    FROM {$this->table}
                    WHERE status = 'completed'
                    AND YEAR(created_at) = YEAR(CURRENT_DATE)
                    GROUP BY month, currency
                    ORDER BY month DESC";
            
            $this->db->query($sql);
            $stats['monthly'] = $this->db->findAll();

            return $stats;
        } catch (Exception $e) {
            error_log("Get Donation Statistics Error: " . $e->getMessage());
            throw new Exception("Failed to get donation statistics");
        }
    }

    /**
     * Get recent donations
     */
    public function getRecent($limit = 10) {
        try {
            $sql = "SELECT * FROM {$this->table} 
                    ORDER BY created_at DESC 
                    LIMIT ?";
            
            $this->db->query($sql, [$limit]);
            return $this->db->findAll();
        } catch (Exception $e) {
            error_log("Get Recent Donations Error: " . $e->getMessage());
            throw new Exception("Failed to get recent donations");
        }
    }

    /**
     * Get donations by date range
     */
    public function getByDateRange($startDate, $endDate, $status = 'completed') {
        try {
            $sql = "SELECT * FROM {$this->table} 
                    WHERE created_at BETWEEN ? AND ? 
                    AND status = ?
                    ORDER BY created_at DESC";
            
            $this->db->query($sql, [$startDate, $endDate, $status]);
            return $this->db->findAll();
        } catch (Exception $e) {
            error_log("Get Donations By Date Range Error: " . $e->getMessage());
            throw new Exception("Failed to get donations by date range");
        }
    }

    /**
     * Get total donations amount
     */
    public function getTotalAmount($currency = 'USD', $status = 'completed') {
        try {
            $sql = "SELECT SUM(amount) as total 
                    FROM {$this->table} 
                    WHERE currency = ? 
                    AND status = ?";
            
            $this->db->query($sql, [$currency, $status]);
            $result = $this->db->findOne();
            return $result['total'] ?? 0;
        } catch (Exception $e) {
            error_log("Get Total Donations Amount Error: " . $e->getMessage());
            throw new Exception("Failed to get total donations amount");
        }
    }

    /**
     * Process Flutterwave payment
     */
    public function processFlutterwavePayment($donationId, $transactionData) {
        try {
            $this->db->beginTransaction();

            // Verify transaction with Flutterwave API
            $verified = $this->verifyFlutterwaveTransaction($transactionData['transaction_id']);
            
            if ($verified) {
                $this->updateStatus($donationId, 'completed', $transactionData['transaction_id']);
                $this->db->commit();
                return true;
            } else {
                $this->updateStatus($donationId, 'failed');
                $this->db->commit();
                return false;
            }
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Process Flutterwave Payment Error: " . $e->getMessage());
            throw new Exception("Failed to process payment");
        }
    }

    /**
     * Process PayPal payment
     */
    public function processPayPalPayment($donationId, $transactionData) {
        try {
            $this->db->beginTransaction();

            // Verify transaction with PayPal API
            $verified = $this->verifyPayPalTransaction($transactionData['transaction_id']);
            
            if ($verified) {
                $this->updateStatus($donationId, 'completed', $transactionData['transaction_id']);
                $this->db->commit();
                return true;
            } else {
                $this->updateStatus($donationId, 'failed');
                $this->db->commit();
                return false;
            }
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Process PayPal Payment Error: " . $e->getMessage());
            throw new Exception("Failed to process payment");
        }
    }

    /**
     * Process Stripe payment
     */
    public function processStripePayment($donationId, $transactionData) {
        try {
            $this->db->beginTransaction();

            // Verify transaction with Stripe API
            $verified = $this->verifyStripeTransaction($transactionData['transaction_id']);
            
            if ($verified) {
                $this->updateStatus($donationId, 'completed', $transactionData['transaction_id']);
                $this->db->commit();
                return true;
            } else {
                $this->updateStatus($donationId, 'failed');
                $this->db->commit();
                return false;
            }
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Process Stripe Payment Error: " . $e->getMessage());
            throw new Exception("Failed to process payment");
        }
    }

    /**
     * Verify Flutterwave transaction
     */
    private function verifyFlutterwaveTransaction($transactionId) {
        // Implementation would include actual API call to Flutterwave
        // This is a placeholder
        return true;
    }

    /**
     * Verify PayPal transaction
     */
    private function verifyPayPalTransaction($transactionId) {
        // Implementation would include actual API call to PayPal
        // This is a placeholder
        return true;
    }

    /**
     * Verify Stripe transaction
     */
    private function verifyStripeTransaction($transactionId) {
        // Implementation would include actual API call to Stripe
        // This is a placeholder
        return true;
    }

    /**
     * Get donation by transaction ID
     */
    public function getByTransactionId($transactionId) {
        try {
            return $this->findOneBy('transaction_id', $transactionId);
        } catch (Exception $e) {
            error_log("Get Donation By Transaction ID Error: " . $e->getMessage());
            throw new Exception("Failed to get donation by transaction ID");
        }
    }

    /**
     * Get donations by email
     */
    public function getByEmail($email, $page = 1, $perPage = 10) {
        try {
            return $this->paginate($page, $perPage, [
                'email' => $email,
                'status' => 'completed'
            ], 'created_at', 'DESC');
        } catch (Exception $e) {
            error_log("Get Donations By Email Error: " . $e->getMessage());
            throw new Exception("Failed to get donations by email");
        }
    }
}
