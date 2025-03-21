<?php
class Campaign extends Model {
    protected $table = 'campaigns';
    protected $fillable = [
        'title',
        'slug',
        'description',
        'goal_amount',
        'current_amount',
        'start_date',
        'end_date',
        'featured_image',
        'status',
        'category_id',
        'created_by'
    ];

    /**
     * Get active campaigns
     */
    public function getActive($page = 1, $perPage = 10) {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT c.*, cat.name as category_name, u.full_name as creator_name 
                FROM {$this->table} c 
                LEFT JOIN categories cat ON c.category_id = cat.id 
                LEFT JOIN users u ON c.created_by = u.id 
                WHERE c.status = 'active' 
                AND c.end_date >= CURRENT_DATE 
                ORDER BY c.created_at DESC 
                LIMIT ? OFFSET ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$perPage, $offset]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get total count for pagination
        $countSql = "SELECT COUNT(*) as count 
                     FROM {$this->table} 
                     WHERE status = 'active' 
                     AND end_date >= CURRENT_DATE";
        $stmt = $this->db->prepare($countSql);
        $stmt->execute();
        $total = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        return [
            'data' => $data,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage)
        ];
    }

    /**
     * Get featured campaigns
     */
    public function getFeatured($limit = 3) {
        $sql = "SELECT c.*, cat.name as category_name, u.full_name as creator_name 
                FROM {$this->table} c 
                LEFT JOIN categories cat ON c.category_id = cat.id 
                LEFT JOIN users u ON c.created_by = u.id 
                WHERE c.status = 'active' 
                AND c.end_date >= CURRENT_DATE 
                AND c.is_featured = 1 
                ORDER BY c.created_at DESC 
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get campaign by slug
     */
    public function getBySlug($slug) {
        $sql = "SELECT c.*, cat.name as category_name, u.full_name as creator_name 
                FROM {$this->table} c 
                LEFT JOIN categories cat ON c.category_id = cat.id 
                LEFT JOIN users u ON c.created_by = u.id 
                WHERE c.slug = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$slug]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Update campaign amount
     */
    public function updateAmount($id, $amount) {
        $sql = "UPDATE {$this->table} 
                SET current_amount = current_amount + ? 
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$amount, $id]);
    }

    /**
     * Get campaign statistics
     */
    public function getStatistics() {
        $stats = [
            'total_campaigns' => 0,
            'active_campaigns' => 0,
            'total_raised' => 0,
            'average_donation' => 0,
            'by_category' => [],
            'by_month' => [],
            'success_rate' => 0
        ];

        // Get basic counts
        $sql = "SELECT 
                COUNT(*) as total_campaigns,
                SUM(CASE WHEN status = 'active' AND end_date >= CURRENT_DATE THEN 1 ELSE 0 END) as active_campaigns,
                SUM(current_amount) as total_raised,
                SUM(CASE WHEN current_amount >= goal_amount THEN 1 ELSE 0 END) as successful_campaigns
                FROM {$this->table}";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $basic = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $stats['total_campaigns'] = $basic['total_campaigns'];
        $stats['active_campaigns'] = $basic['active_campaigns'];
        $stats['total_raised'] = $basic['total_raised'];
        $stats['success_rate'] = $basic['total_campaigns'] > 0 
            ? ($basic['successful_campaigns'] / $basic['total_campaigns']) * 100 
            : 0;

        // Get average donation
        $sql = "SELECT AVG(amount) as average 
                FROM donations d 
                JOIN {$this->table} c ON d.campaign_id = c.id 
                WHERE d.status = 'completed'";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stats['average_donation'] = $stmt->fetch(PDO::FETCH_ASSOC)['average'] ?? 0;

        // Get counts by category
        $sql = "SELECT cat.name, COUNT(*) as count, SUM(c.current_amount) as amount 
                FROM {$this->table} c 
                LEFT JOIN categories cat ON c.category_id = cat.id 
                GROUP BY cat.name";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stats['by_category'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get amounts by month
        $sql = "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, 
                COUNT(*) as campaigns,
                SUM(current_amount) as amount 
                FROM {$this->table} 
                GROUP BY month 
                ORDER BY month DESC 
                LIMIT 12";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stats['by_month'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $stats;
    }

    /**
     * Get campaign donors
     */
    public function getDonors($campaignId, $limit = null) {
        $sql = "SELECT d.*, u.full_name, u.email 
                FROM donations d 
                LEFT JOIN users u ON d.user_id = u.id 
                WHERE d.campaign_id = ? AND d.status = 'completed' 
                ORDER BY d.amount DESC";
        
        if ($limit) {
            $sql .= " LIMIT ?";
        }

        $stmt = $this->db->prepare($sql);
        $params = [$campaignId];
        if ($limit) {
            $params[] = $limit;
        }
        
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get campaign progress
     */
    public function getProgress($campaignId) {
        $campaign = $this->find($campaignId);
        if (!$campaign) {
            return 0;
        }

        return [
            'current' => $campaign['current_amount'],
            'goal' => $campaign['goal_amount'],
            'percentage' => ($campaign['current_amount'] / $campaign['goal_amount']) * 100,
            'remaining' => $campaign['goal_amount'] - $campaign['current_amount'],
            'days_left' => max(0, ceil((strtotime($campaign['end_date']) - time()) / 86400))
        ];
    }

    /**
     * Get similar campaigns
     */
    public function getSimilar($campaignId, $limit = 3) {
        $campaign = $this->find($campaignId);
        if (!$campaign) {
            return [];
        }

        $sql = "SELECT c.*, cat.name as category_name 
                FROM {$this->table} c 
                LEFT JOIN categories cat ON c.category_id = cat.id 
                WHERE c.id != ? 
                AND c.category_id = ? 
                AND c.status = 'active' 
                AND c.end_date >= CURRENT_DATE 
                ORDER BY c.created_at DESC 
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$campaignId, $campaign['category_id'], $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Check if campaign is active
     */
    public function isActive($campaignId) {
        $campaign = $this->find($campaignId);
        if (!$campaign) {
            return false;
        }

        return $campaign['status'] === 'active' && 
               strtotime($campaign['end_date']) >= time();
    }

    /**
     * Get top campaigns
     */
    public function getTopCampaigns($limit = 5) {
        $sql = "SELECT c.*, cat.name as category_name,
                (c.current_amount / c.goal_amount * 100) as progress 
                FROM {$this->table} c 
                LEFT JOIN categories cat ON c.category_id = cat.id 
                WHERE c.status = 'active' 
                AND c.end_date >= CURRENT_DATE 
                ORDER BY progress DESC 
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}