<?php
require_once __DIR__ . '/Model.php';

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
        try {
            return $this->paginate($page, $perPage, [
                'status' => 'active',
                'end_date >= ?' => date('Y-m-d')
            ], 'end_date', 'ASC');
        } catch (Exception $e) {
            error_log("Get Active Campaigns Error: " . $e->getMessage());
            throw new Exception("Failed to get active campaigns");
        }
    }

    /**
     * Create a new campaign
     */
    public function createCampaign($data) {
        try {
            if (empty($data['slug'])) {
                $data['slug'] = Utility::generateSlug($data['title']);
            }

            // Validate amounts
            if (!is_numeric($data['goal_amount']) || $data['goal_amount'] <= 0) {
                throw new Exception("Invalid goal amount");
            }

            // Set initial current amount
            $data['current_amount'] = 0;

            // Validate dates
            if (strtotime($data['end_date']) < strtotime($data['start_date'])) {
                throw new Exception("End date cannot be before start date");
            }

            // Set initial status
            if (empty($data['status'])) {
                $now = time();
                $startTime = strtotime($data['start_date']);
                
                if ($now < $startTime) {
                    $data['status'] = 'pending';
                } else {
                    $data['status'] = 'active';
                }
            }

            return $this->create($data);
        } catch (Exception $e) {
            error_log("Create Campaign Error: " . $e->getMessage());
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Update a campaign
     */
    public function updateCampaign($id, $data) {
        try {
            if (!empty($data['title'])) {
                $data['slug'] = Utility::generateSlug($data['title']);
            }

            // Validate goal amount if provided
            if (isset($data['goal_amount']) && (!is_numeric($data['goal_amount']) || $data['goal_amount'] <= 0)) {
                throw new Exception("Invalid goal amount");
            }

            // Validate dates if both are provided
            if (!empty($data['start_date']) && !empty($data['end_date'])) {
                if (strtotime($data['end_date']) < strtotime($data['start_date'])) {
                    throw new Exception("End date cannot be before start date");
                }
            }

            return $this->update($id, $data);
        } catch (Exception $e) {
            error_log("Update Campaign Error: " . $e->getMessage());
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Update campaign amount
     */
    public function updateAmount($id, $amount) {
        try {
            $campaign = $this->find($id);
            if (!$campaign) {
                throw new Exception("Campaign not found");
            }

            $newAmount = $campaign['current_amount'] + $amount;
            return $this->update($id, ['current_amount' => $newAmount]);
        } catch (Exception $e) {
            error_log("Update Campaign Amount Error: " . $e->getMessage());
            throw new Exception("Failed to update campaign amount");
        }
    }

    /**
     * Get campaign with full details
     */
    public function getCampaignWithDetails($id) {
        try {
            $sql = "SELECT c.*, 
                           cat.name as category_name,
                           cat.slug as category_slug,
                           u.username as creator_name,
                           u.email as creator_email
                    FROM {$this->table} c
                    LEFT JOIN categories cat ON c.category_id = cat.id
                    LEFT JOIN users u ON c.created_by = u.id
                    WHERE c.id = ?";
            
            $this->db->query($sql, [$id]);
            return $this->db->findOne();
        } catch (Exception $e) {
            error_log("Get Campaign Details Error: " . $e->getMessage());
            throw new Exception("Failed to get campaign details");
        }
    }

    /**
     * Get campaign by slug
     */
    public function getBySlug($slug) {
        try {
            $sql = "SELECT c.*, 
                           cat.name as category_name,
                           cat.slug as category_slug,
                           u.username as creator_name
                    FROM {$this->table} c
                    LEFT JOIN categories cat ON c.category_id = cat.id
                    LEFT JOIN users u ON c.created_by = u.id
                    WHERE c.slug = ?";
            
            $this->db->query($sql, [$slug]);
            return $this->db->findOne();
        } catch (Exception $e) {
            error_log("Get Campaign By Slug Error: " . $e->getMessage());
            throw new Exception("Failed to get campaign by slug");
        }
    }

    /**
     * Get campaigns by category
     */
    public function getByCategory($categoryId, $page = 1, $perPage = 10) {
        try {
            return $this->paginate($page, $perPage, [
                'category_id' => $categoryId,
                'status' => 'active',
                'end_date >= ?' => date('Y-m-d')
            ], 'end_date', 'ASC');
        } catch (Exception $e) {
            error_log("Get Campaigns By Category Error: " . $e->getMessage());
            throw new Exception("Failed to get campaigns by category");
        }
    }

    /**
     * Get campaign statistics
     */
    public function getStatistics() {
        try {
            $stats = [
                'total' => $this->count(),
                'active' => $this->count([
                    'status' => 'active',
                    'end_date >= ?' => date('Y-m-d')
                ]),
                'completed' => $this->count([
                    'status' => 'completed'
                ]),
                'pending' => $this->count([
                    'status' => 'pending'
                ])
            ];

            // Get total amounts
            $sql = "SELECT 
                        SUM(goal_amount) as total_goal,
                        SUM(current_amount) as total_raised
                    FROM {$this->table}";
            
            $this->db->query($sql);
            $amounts = $this->db->findOne();
            $stats['total_goal'] = $amounts['total_goal'] ?? 0;
            $stats['total_raised'] = $amounts['total_raised'] ?? 0;

            // Get campaigns by category
            $sql = "SELECT c.name, 
                           COUNT(*) as campaign_count,
                           SUM(cam.current_amount) as amount_raised
                    FROM categories c
                    LEFT JOIN {$this->table} cam ON c.id = cam.category_id
                    WHERE c.module = 'campaigns'
                    GROUP BY c.id";
            
            $this->db->query($sql);
            $stats['by_category'] = $this->db->findAll();

            return $stats;
        } catch (Exception $e) {
            error_log("Get Campaign Statistics Error: " . $e->getMessage());
            throw new Exception("Failed to get campaign statistics");
        }
    }

    /**
     * Search campaigns
     */
    public function searchCampaigns($searchTerm, $page = 1, $perPage = 10) {
        try {
            return $this->search(['title', 'description'], $searchTerm, $page, $perPage);
        } catch (Exception $e) {
            error_log("Search Campaigns Error: " . $e->getMessage());
            throw new Exception("Failed to search campaigns");
        }
    }

    /**
     * Get featured campaigns
     */
    public function getFeatured($limit = 3) {
        try {
            $sql = "SELECT c.*, 
                           cat.name as category_name,
                           (c.current_amount / c.goal_amount * 100) as progress
                    FROM {$this->table} c
                    LEFT JOIN categories cat ON c.category_id = cat.id
                    WHERE c.status = 'active'
                    AND c.end_date >= CURRENT_DATE
                    AND c.featured_image IS NOT NULL
                    ORDER BY progress DESC
                    LIMIT ?";
            
            $this->db->query($sql, [$limit]);
            return $this->db->findAll();
        } catch (Exception $e) {
            error_log("Get Featured Campaigns Error: " . $e->getMessage());
            throw new Exception("Failed to get featured campaigns");
        }
    }

    /**
     * Update campaign statuses
     */
    public function updateCampaignStatuses() {
        try {
            $now = date('Y-m-d');
            
            // Update to active
            $sql = "UPDATE {$this->table} 
                    SET status = 'active' 
                    WHERE status = 'pending' 
                    AND start_date <= ?";
            $this->db->query($sql, [$now]);
            
            // Update to completed
            $sql = "UPDATE {$this->table} 
                    SET status = 'completed' 
                    WHERE status = 'active' 
                    AND (end_date < ? OR current_amount >= goal_amount)";
            $this->db->query($sql, [$now]);
            
            return true;
        } catch (Exception $e) {
            error_log("Update Campaign Statuses Error: " . $e->getMessage());
            throw new Exception("Failed to update campaign statuses");
        }
    }
}