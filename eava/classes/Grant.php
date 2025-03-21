<?php
class Grant extends Model {
    protected $table = 'grants';
    protected $fillable = [
        'title',
        'slug',
        'description',
        'amount',
        'deadline',
        'status',
        'category_id',
        'created_by'
    ];

    /**
     * Get open grants
     */
    public function getOpen($page = 1, $perPage = 10) {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT g.*, c.name as category_name, u.full_name as creator_name 
                FROM {$this->table} g 
                LEFT JOIN categories c ON g.category_id = c.id 
                LEFT JOIN users u ON g.created_by = u.id 
                WHERE g.status = 'open' 
                AND g.deadline >= CURRENT_DATE 
                ORDER BY g.deadline ASC 
                LIMIT ? OFFSET ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$perPage, $offset]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get total count for pagination
        $countSql = "SELECT COUNT(*) as count 
                     FROM {$this->table} 
                     WHERE status = 'open' 
                     AND deadline >= CURRENT_DATE";
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
     * Get grant by slug
     */
    public function getBySlug($slug) {
        $sql = "SELECT g.*, c.name as category_name, u.full_name as creator_name 
                FROM {$this->table} g 
                LEFT JOIN categories c ON g.category_id = c.id 
                LEFT JOIN users u ON g.created_by = u.id 
                WHERE g.slug = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$slug]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Submit grant application
     */
    public function submitApplication($grantId, $userId, $data) {
        try {
            $this->db->beginTransaction();

            // Check if grant is still open
            $grant = $this->find($grantId);
            if (!$grant || $grant['status'] !== 'open' || strtotime($grant['deadline']) < time()) {
                throw new Exception('Grant is not available for applications');
            }

            // Check if user has already applied
            $sql = "SELECT COUNT(*) as count FROM grant_applications 
                    WHERE grant_id = ? AND user_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$grantId, $userId]);
            if ($stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0) {
                throw new Exception('You have already applied for this grant');
            }

            // Create application
            $sql = "INSERT INTO grant_applications 
                    (grant_id, user_id, content, status) 
                    VALUES (?, ?, ?, 'submitted')";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$grantId, $userId, $data['content']]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Get grant applications
     */
    public function getApplications($grantId, $status = null) {
        $sql = "SELECT a.*, u.full_name, u.email 
                FROM grant_applications a 
                LEFT JOIN users u ON a.user_id = u.id 
                WHERE a.grant_id = ?";
        
        $params = [$grantId];
        if ($status) {
            $sql .= " AND a.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY a.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Update application status
     */
    public function updateApplicationStatus($applicationId, $status, $feedback = null) {
        $sql = "UPDATE grant_applications 
                SET status = ?, feedback = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$status, $feedback, $applicationId]);
    }

    /**
     * Get grant statistics
     */
    public function getStatistics() {
        $stats = [
            'total_grants' => 0,
            'open_grants' => 0,
            'total_amount' => 0,
            'total_applications' => 0,
            'by_category' => [],
            'by_status' => [],
            'application_stats' => []
        ];

        // Get basic counts
        $sql = "SELECT 
                COUNT(*) as total_grants,
                SUM(CASE WHEN status = 'open' AND deadline >= CURRENT_DATE THEN 1 ELSE 0 END) as open_grants,
                SUM(amount) as total_amount
                FROM {$this->table}";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $basic = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $stats['total_grants'] = $basic['total_grants'];
        $stats['open_grants'] = $basic['open_grants'];
        $stats['total_amount'] = $basic['total_amount'];

        // Get counts by category
        $sql = "SELECT c.name, COUNT(*) as count, SUM(g.amount) as amount 
                FROM {$this->table} g 
                LEFT JOIN categories c ON g.category_id = c.id 
                GROUP BY c.name";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stats['by_category'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get counts by status
        $sql = "SELECT status, COUNT(*) as count 
                FROM {$this->table} 
                GROUP BY status";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stats['by_status'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        // Get application statistics
        $sql = "SELECT 
                COUNT(*) as total_applications,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                SUM(CASE WHEN status = 'under_review' THEN 1 ELSE 0 END) as under_review
                FROM grant_applications";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stats['application_stats'] = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['total_applications'] = $stats['application_stats']['total_applications'];

        return $stats;
    }

    /**
     * Get user applications
     */
    public function getUserApplications($userId) {
        $sql = "SELECT a.*, g.title as grant_title, g.amount 
                FROM grant_applications a 
                JOIN {$this->table} g ON a.grant_id = g.id 
                WHERE a.user_id = ? 
                ORDER BY a.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Check if grant is open
     */
    public function isOpen($grantId) {
        $grant = $this->find($grantId);
        if (!$grant) {
            return false;
        }

        return $grant['status'] === 'open' && 
               strtotime($grant['deadline']) >= time();
    }

    /**
     * Get similar grants
     */
    public function getSimilar($grantId, $limit = 3) {
        $grant = $this->find($grantId);
        if (!$grant) {
            return [];
        }

        $sql = "SELECT g.*, c.name as category_name 
                FROM {$this->table} g 
                LEFT JOIN categories c ON g.category_id = c.id 
                WHERE g.id != ? 
                AND g.category_id = ? 
                AND g.status = 'open' 
                AND g.deadline >= CURRENT_DATE 
                ORDER BY g.deadline ASC 
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$grantId, $grant['category_id'], $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get application count
     */
    public function getApplicationCount($grantId) {
        $sql = "SELECT COUNT(*) as count FROM grant_applications WHERE grant_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$grantId]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    }
}