<?php
class Donation extends Model {
    protected $table = 'donations';
    protected $fillable = [
        'amount',
        'currency',
        'donor_name',
        'email',
        'phone',
        'transaction_id',
        'payment_method',
        'status',
        'campaign_id'
    ];

    /**
     * Create a new donation
     */
    public function createDonation($data) {
        try {
            $this->db->beginTransaction();

            // Create donation record
            $donation = $this->create($data);

            // Update campaign amount if donation is for a campaign
            if ($data['campaign_id'] && $data['status'] === 'completed') {
                $campaignModel = new Campaign();
                $campaignModel->updateAmount($data['campaign_id'], $data['amount']);
            }

            $this->db->commit();
            return $donation;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Update donation status
     */
    public function updateStatus($id, $status) {
        try {
            $this->db->beginTransaction();

            $donation = $this->find($id);
            if (!$donation) {
                throw new Exception('Donation not found');
            }

            // If status is changing to completed, update campaign amount
            if ($status === 'completed' && $donation['status'] !== 'completed' && $donation['campaign_id']) {
                $campaignModel = new Campaign();
                $campaignModel->updateAmount($donation['campaign_id'], $donation['amount']);
            }

            // If status is changing from completed, subtract from campaign amount
            if ($donation['status'] === 'completed' && $status !== 'completed' && $donation['campaign_id']) {
                $campaignModel = new Campaign();
                $campaignModel->updateAmount($donation['campaign_id'], -$donation['amount']);
            }

            $this->update($id, ['status' => $status]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Get recent donations
     */
    public function getRecent($limit = 5) {
        $sql = "SELECT d.*, c.title as campaign_title 
                FROM {$this->table} d 
                LEFT JOIN campaigns c ON d.campaign_id = c.id 
                WHERE d.status = 'completed' 
                ORDER BY d.created_at DESC 
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get donation statistics
     */
    public function getStatistics() {
        $stats = [
            'total_amount' => 0,
            'total_donations' => 0,
            'average_donation' => 0,
            'by_status' => [],
            'by_payment_method' => [],
            'monthly' => [],
            'campaign_totals' => []
        ];

        // Get basic totals
        $sql = "SELECT 
                COUNT(*) as total_donations,
                SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as total_amount,
                AVG(CASE WHEN status = 'completed' THEN amount ELSE NULL END) as average_donation
                FROM {$this->table}";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $basic = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $stats['total_donations'] = $basic['total_donations'];
        $stats['total_amount'] = $basic['total_amount'];
        $stats['average_donation'] = $basic['average_donation'];

        // Get counts by status
        $sql = "SELECT status, COUNT(*) as count, SUM(amount) as total 
                FROM {$this->table} 
                GROUP BY status";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stats['by_status'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get counts by payment method
        $sql = "SELECT payment_method, COUNT(*) as count, SUM(amount) as total 
                FROM {$this->table} 
                WHERE status = 'completed' 
                GROUP BY payment_method";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stats['by_payment_method'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get monthly totals
        $sql = "SELECT 
                DATE_FORMAT(created_at, '%Y-%m') as month,
                COUNT(*) as count,
                SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as total
                FROM {$this->table} 
                GROUP BY month 
                ORDER BY month DESC 
                LIMIT 12";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stats['monthly'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get campaign totals
        $sql = "SELECT 
                c.title as campaign_name,
                COUNT(d.id) as donation_count,
                SUM(d.amount) as total_amount
                FROM campaigns c 
                LEFT JOIN {$this->table} d ON c.id = d.campaign_id 
                WHERE d.status = 'completed' 
                GROUP BY c.id 
                ORDER BY total_amount DESC 
                LIMIT 10";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stats['campaign_totals'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $stats;
    }

    /**
     * Get donations by campaign
     */
    public function getByCampaign($campaignId, $page = 1, $perPage = 20) {
        return $this->paginate($page, $perPage, [
            'campaign_id' => $campaignId,
            'status' => 'completed'
        ]);
    }

    /**
     * Get donations by email
     */
    public function getByEmail($email) {
        $sql = "SELECT d.*, c.title as campaign_title 
                FROM {$this->table} d 
                LEFT JOIN campaigns c ON d.campaign_id = c.id 
                WHERE d.email = ? 
                ORDER BY d.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get top donors
     */
    public function getTopDonors($limit = 10) {
        $sql = "SELECT 
                email,
                COUNT(*) as donation_count,
                SUM(amount) as total_amount 
                FROM {$this->table} 
                WHERE status = 'completed' 
                GROUP BY email 
                ORDER BY total_amount DESC 
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Generate donation receipt
     */
    public function generateReceipt($donationId) {
        $donation = $this->find($donationId);
        if (!$donation || $donation['status'] !== 'completed') {
            throw new Exception('Invalid donation for receipt generation');
        }

        // Get campaign details if donation was for a campaign
        $campaignTitle = '';
        if ($donation['campaign_id']) {
            $campaignModel = new Campaign();
            $campaign = $campaignModel->find($donation['campaign_id']);
            $campaignTitle = $campaign ? $campaign['title'] : '';
        }

        return [
            'receipt_number' => 'RCP-' . str_pad($donationId, 8, '0', STR_PAD_LEFT),
            'date' => $donation['created_at'],
            'donor_name' => $donation['donor_name'],
            'email' => $donation['email'],
            'amount' => $donation['amount'],
            'currency' => $donation['currency'],
            'payment_method' => $donation['payment_method'],
            'transaction_id' => $donation['transaction_id'],
            'campaign_title' => $campaignTitle
        ];
    }

    /**
     * Get donation summary by date range
     */
    public function getSummaryByDateRange($startDate, $endDate) {
        $sql = "SELECT 
                DATE(created_at) as date,
                COUNT(*) as count,
                SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as total_amount
                FROM {$this->table} 
                WHERE created_at BETWEEN ? AND ? 
                GROUP BY date 
                ORDER BY date";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$startDate, $endDate]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}