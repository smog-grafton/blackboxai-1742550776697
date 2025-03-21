<?php
require_once __DIR__ . '/Model.php';

class Grant extends Model {
    protected $table = 'grants';
    protected $fillable = [
        'title',
        'slug',
        'description',
        'amount',
        'deadline',
        'status'
    ];

    /**
     * Get open grants
     */
    public function getOpen($page = 1, $perPage = 10) {
        try {
            return $this->paginate($page, $perPage, [
                'status' => 'open',
                'deadline >= ?' => date('Y-m-d')
            ], 'deadline', 'ASC');
        } catch (Exception $e) {
            error_log("Get Open Grants Error: " . $e->getMessage());
            throw new Exception("Failed to get open grants");
        }
    }

    /**
     * Create a new grant
     */
    public function createGrant($data) {
        try {
            if (empty($data['slug'])) {
                $data['slug'] = Utility::generateSlug($data['title']);
            }

            // Validate amount
            if (!is_numeric($data['amount']) || $data['amount'] <= 0) {
                throw new Exception("Invalid grant amount");
            }

            // Validate deadline
            if (strtotime($data['deadline']) < strtotime(date('Y-m-d'))) {
                throw new Exception("Deadline cannot be in the past");
            }

            // Set default status if not provided
            if (empty($data['status'])) {
                $data['status'] = 'open';
            }

            return $this->create($data);
        } catch (Exception $e) {
            error_log("Create Grant Error: " . $e->getMessage());
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Update a grant
     */
    public function updateGrant($id, $data) {
        try {
            if (!empty($data['title'])) {
                $data['slug'] = Utility::generateSlug($data['title']);
            }

            // Validate amount if provided
            if (isset($data['amount']) && (!is_numeric($data['amount']) || $data['amount'] <= 0)) {
                throw new Exception("Invalid grant amount");
            }

            // Validate deadline if provided
            if (!empty($data['deadline'])) {
                $grant = $this->find($id);
                if ($grant['status'] === 'open' && strtotime($data['deadline']) < strtotime(date('Y-m-d'))) {
                    throw new Exception("Deadline cannot be in the past for open grants");
                }
            }

            return $this->update($id, $data);
        } catch (Exception $e) {
            error_log("Update Grant Error: " . $e->getMessage());
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Get grant with full details
     */
    public function getGrantWithDetails($id) {
        try {
            $sql = "SELECT g.*, 
                           COUNT(DISTINCT ga.id) as application_count,
                           COUNT(DISTINCT CASE WHEN ga.status = 'pending' THEN ga.id END) as pending_applications
                    FROM {$this->table} g
                    LEFT JOIN grant_applications ga ON g.id = ga.grant_id
                    WHERE g.id = ?
                    GROUP BY g.id";
            
            $this->db->query($sql, [$id]);
            return $this->db->findOne();
        } catch (Exception $e) {
            error_log("Get Grant Details Error: " . $e->getMessage());
            throw new Exception("Failed to get grant details");
        }
    }

    /**
     * Get grant by slug
     */
    public function getBySlug($slug) {
        try {
            return $this->findOneBy('slug', $slug);
        } catch (Exception $e) {
            error_log("Get Grant By Slug Error: " . $e->getMessage());
            throw new Exception("Failed to get grant by slug");
        }
    }

    /**
     * Close a grant
     */
    public function closeGrant($id) {
        try {
            return $this->update($id, ['status' => 'closed']);
        } catch (Exception $e) {
            error_log("Close Grant Error: " . $e->getMessage());
            throw new Exception("Failed to close grant");
        }
    }

    /**
     * Get grants by status
     */
    public function getByStatus($status, $page = 1, $perPage = 10) {
        try {
            return $this->paginate($page, $perPage, [
                'status' => $status
            ], 'created_at', 'DESC');
        } catch (Exception $e) {
            error_log("Get Grants By Status Error: " . $e->getMessage());
            throw new Exception("Failed to get grants by status");
        }
    }

    /**
     * Get grants statistics
     */
    public function getStatistics() {
        try {
            $stats = [
                'total' => $this->count(),
                'open' => $this->count(['status' => 'open']),
                'closed' => $this->count(['status' => 'closed']),
                'under_review' => $this->count(['status' => 'under_review'])
            ];

            // Get total grant amount
            $sql = "SELECT SUM(amount) as total_amount FROM {$this->table}";
            $this->db->query($sql);
            $result = $this->db->findOne();
            $stats['total_amount'] = $result['total_amount'] ?? 0;

            // Get applications statistics
            $sql = "SELECT g.status as grant_status, 
                           COUNT(DISTINCT ga.id) as application_count,
                           COUNT(DISTINCT CASE WHEN ga.status = 'approved' THEN ga.id END) as approved_count
                    FROM {$this->table} g
                    LEFT JOIN grant_applications ga ON g.id = ga.grant_id
                    GROUP BY g.status";
            
            $this->db->query($sql);
            $stats['applications'] = $this->db->findAll();

            return $stats;
        } catch (Exception $e) {
            error_log("Get Grant Statistics Error: " . $e->getMessage());
            throw new Exception("Failed to get grant statistics");
        }
    }

    /**
     * Search grants
     */
    public function searchGrants($searchTerm, $page = 1, $perPage = 10) {
        try {
            return $this->search(['title', 'description'], $searchTerm, $page, $perPage);
        } catch (Exception $e) {
            error_log("Search Grants Error: " . $e->getMessage());
            throw new Exception("Failed to search grants");
        }
    }

    /**
     * Get upcoming deadlines
     */
    public function getUpcomingDeadlines($limit = 5) {
        try {
            $sql = "SELECT * FROM {$this->table}
                    WHERE status = 'open'
                    AND deadline >= CURRENT_DATE
                    ORDER BY deadline ASC
                    LIMIT ?";
            
            $this->db->query($sql, [$limit]);
            return $this->db->findAll();
        } catch (Exception $e) {
            error_log("Get Upcoming Deadlines Error: " . $e->getMessage());
            throw new Exception("Failed to get upcoming deadlines");
        }
    }

    /**
     * Get grants by amount range
     */
    public function getByAmountRange($minAmount, $maxAmount, $page = 1, $perPage = 10) {
        try {
            $conditions = [
                'amount >= ?' => $minAmount,
                'amount <= ?' => $maxAmount,
                'status' => 'open'
            ];
            
            return $this->paginate($page, $perPage, $conditions, 'deadline', 'ASC');
        } catch (Exception $e) {
            error_log("Get Grants By Amount Range Error: " . $e->getMessage());
            throw new Exception("Failed to get grants by amount range");
        }
    }

    /**
     * Update grant statuses based on deadline
     */
    public function updateGrantStatuses() {
        try {
            $sql = "UPDATE {$this->table}
                    SET status = 'closed'
                    WHERE status = 'open'
                    AND deadline < CURRENT_DATE";
            
            $this->db->query($sql);
            return true;
        } catch (Exception $e) {
            error_log("Update Grant Statuses Error: " . $e->getMessage());
            throw new Exception("Failed to update grant statuses");
        }
    }

    /**
     * Get grant application statistics
     */
    public function getApplicationStatistics($grantId) {
        try {
            $sql = "SELECT status, COUNT(*) as count
                    FROM grant_applications
                    WHERE grant_id = ?
                    GROUP BY status";
            
            $this->db->query($sql, [$grantId]);
            return $this->db->findAll();
        } catch (Exception $e) {
            error_log("Get Grant Application Statistics Error: " . $e->getMessage());
            throw new Exception("Failed to get application statistics");
        }
    }
}