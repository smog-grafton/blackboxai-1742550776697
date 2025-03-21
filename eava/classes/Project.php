<?php
require_once __DIR__ . '/Model.php';

class Project extends Model {
    protected $table = 'projects';
    protected $fillable = [
        'title',
        'slug',
        'description',
        'featured_image',
        'status',
        'start_date',
        'end_date',
        'category_id'
    ];

    /**
     * Get active projects
     */
    public function getActive($page = 1, $perPage = 10) {
        try {
            return $this->paginate($page, $perPage, [
                'status' => 'active'
            ], 'start_date', 'DESC');
        } catch (Exception $e) {
            error_log("Get Active Projects Error: " . $e->getMessage());
            throw new Exception("Failed to get active projects");
        }
    }

    /**
     * Get completed projects
     */
    public function getCompleted($page = 1, $perPage = 10) {
        try {
            return $this->paginate($page, $perPage, [
                'status' => 'completed'
            ], 'end_date', 'DESC');
        } catch (Exception $e) {
            error_log("Get Completed Projects Error: " . $e->getMessage());
            throw new Exception("Failed to get completed projects");
        }
    }

    /**
     * Get upcoming projects
     */
    public function getUpcoming($page = 1, $perPage = 10) {
        try {
            return $this->paginate($page, $perPage, [
                'status' => 'upcoming'
            ], 'start_date', 'ASC');
        } catch (Exception $e) {
            error_log("Get Upcoming Projects Error: " . $e->getMessage());
            throw new Exception("Failed to get upcoming projects");
        }
    }

    /**
     * Create a new project
     */
    public function createProject($data) {
        try {
            if (empty($data['slug'])) {
                $data['slug'] = Utility::generateSlug($data['title']);
            }

            // Validate dates if provided
            if (!empty($data['start_date']) && !empty($data['end_date'])) {
                if (strtotime($data['end_date']) < strtotime($data['start_date'])) {
                    throw new Exception("End date cannot be before start date");
                }
            }

            // Set initial status if not provided
            if (empty($data['status'])) {
                $now = time();
                $startTime = !empty($data['start_date']) ? strtotime($data['start_date']) : $now;
                
                if ($now < $startTime) {
                    $data['status'] = 'upcoming';
                } else {
                    $data['status'] = 'active';
                }
            }

            return $this->create($data);
        } catch (Exception $e) {
            error_log("Create Project Error: " . $e->getMessage());
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Update a project
     */
    public function updateProject($id, $data) {
        try {
            if (!empty($data['title'])) {
                $data['slug'] = Utility::generateSlug($data['title']);
            }

            // Validate dates if both are provided
            if (!empty($data['start_date']) && !empty($data['end_date'])) {
                if (strtotime($data['end_date']) < strtotime($data['start_date'])) {
                    throw new Exception("End date cannot be before start date");
                }
            }

            // Update status based on dates if not explicitly set
            if (empty($data['status']) && (!empty($data['start_date']) || !empty($data['end_date']))) {
                $project = $this->find($id);
                $startDate = !empty($data['start_date']) ? $data['start_date'] : $project['start_date'];
                $endDate = !empty($data['end_date']) ? $data['end_date'] : $project['end_date'];
                
                $now = time();
                $startTime = strtotime($startDate);
                $endTime = $endDate ? strtotime($endDate) : null;

                if ($now < $startTime) {
                    $data['status'] = 'upcoming';
                } elseif (!$endTime || $now <= $endTime) {
                    $data['status'] = 'active';
                } else {
                    $data['status'] = 'completed';
                }
            }

            return $this->update($id, $data);
        } catch (Exception $e) {
            error_log("Update Project Error: " . $e->getMessage());
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Get project with full details
     */
    public function getProjectWithDetails($id) {
        try {
            $sql = "SELECT p.*, 
                           c.name as category_name,
                           c.slug as category_slug
                    FROM {$this->table} p
                    LEFT JOIN categories c ON p.category_id = c.id
                    WHERE p.id = ?";
            
            $this->db->query($sql, [$id]);
            return $this->db->findOne();
        } catch (Exception $e) {
            error_log("Get Project Details Error: " . $e->getMessage());
            throw new Exception("Failed to get project details");
        }
    }

    /**
     * Get projects by category
     */
    public function getByCategory($categoryId, $page = 1, $perPage = 10) {
        try {
            return $this->paginate($page, $perPage, [
                'category_id' => $categoryId
            ], 'start_date', 'DESC');
        } catch (Exception $e) {
            error_log("Get Projects By Category Error: " . $e->getMessage());
            throw new Exception("Failed to get projects by category");
        }
    }

    /**
     * Get project by slug
     */
    public function getBySlug($slug) {
        try {
            $sql = "SELECT p.*, 
                           c.name as category_name,
                           c.slug as category_slug
                    FROM {$this->table} p
                    LEFT JOIN categories c ON p.category_id = c.id
                    WHERE p.slug = ?";
            
            $this->db->query($sql, [$slug]);
            return $this->db->findOne();
        } catch (Exception $e) {
            error_log("Get Project By Slug Error: " . $e->getMessage());
            throw new Exception("Failed to get project by slug");
        }
    }

    /**
     * Get related projects
     */
    public function getRelated($projectId, $categoryId, $limit = 3) {
        try {
            $sql = "SELECT p.*, c.name as category_name 
                    FROM {$this->table} p
                    LEFT JOIN categories c ON p.category_id = c.id
                    WHERE p.id != ? 
                    AND p.category_id = ?
                    AND p.status != 'completed'
                    ORDER BY p.start_date DESC
                    LIMIT ?";
            
            $this->db->query($sql, [$projectId, $categoryId, $limit]);
            return $this->db->findAll();
        } catch (Exception $e) {
            error_log("Get Related Projects Error: " . $e->getMessage());
            throw new Exception("Failed to get related projects");
        }
    }

    /**
     * Search projects
     */
    public function searchProjects($searchTerm, $page = 1, $perPage = 10) {
        try {
            return $this->search(['title', 'description'], $searchTerm, $page, $perPage);
        } catch (Exception $e) {
            error_log("Search Projects Error: " . $e->getMessage());
            throw new Exception("Failed to search projects");
        }
    }

    /**
     * Get project statistics
     */
    public function getStatistics() {
        try {
            $stats = [
                'total' => $this->count(),
                'active' => $this->count(['status' => 'active']),
                'completed' => $this->count(['status' => 'completed']),
                'upcoming' => $this->count(['status' => 'upcoming'])
            ];

            // Get projects by category
            $sql = "SELECT c.name, COUNT(*) as count 
                    FROM {$this->table} p
                    LEFT JOIN categories c ON p.category_id = c.id
                    GROUP BY p.category_id";
            
            $this->db->query($sql);
            $stats['by_category'] = $this->db->findAll();

            return $stats;
        } catch (Exception $e) {
            error_log("Get Project Statistics Error: " . $e->getMessage());
            throw new Exception("Failed to get project statistics");
        }
    }

    /**
     * Update project statuses based on dates
     */
    public function updateProjectStatuses() {
        try {
            $now = date('Y-m-d');
            
            // Update to active
            $sql = "UPDATE {$this->table} 
                    SET status = 'active' 
                    WHERE start_date <= ? 
                    AND (end_date IS NULL OR end_date >= ?) 
                    AND status = 'upcoming'";
            $this->db->query($sql, [$now, $now]);
            
            // Update to completed
            $sql = "UPDATE {$this->table} 
                    SET status = 'completed' 
                    WHERE end_date < ? 
                    AND status != 'completed'";
            $this->db->query($sql, [$now]);
            
            return true;
        } catch (Exception $e) {
            error_log("Update Project Statuses Error: " . $e->getMessage());
            throw new Exception("Failed to update project statuses");
        }
    }

    /**
     * Get featured projects
     */
    public function getFeatured($limit = 3) {
        try {
            $sql = "SELECT p.*, c.name as category_name 
                    FROM {$this->table} p
                    LEFT JOIN categories c ON p.category_id = c.id
                    WHERE p.status = 'active'
                    AND p.featured_image IS NOT NULL
                    ORDER BY p.start_date DESC
                    LIMIT ?";
            
            $this->db->query($sql, [$limit]);
            return $this->db->findAll();
        } catch (Exception $e) {
            error_log("Get Featured Projects Error: " . $e->getMessage());
            throw new Exception("Failed to get featured projects");
        }
    }
}