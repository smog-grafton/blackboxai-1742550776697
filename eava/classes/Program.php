<?php
require_once __DIR__ . '/Model.php';

class Program extends Model {
    protected $table = 'programs';
    protected $fillable = [
        'title',
        'slug',
        'description',
        'featured_image',
        'status',
        'category_id'
    ];

    /**
     * Get active programs
     */
    public function getActive($page = 1, $perPage = 10) {
        try {
            return $this->paginate($page, $perPage, [
                'status' => 'active'
            ], 'created_at', 'DESC');
        } catch (Exception $e) {
            error_log("Get Active Programs Error: " . $e->getMessage());
            throw new Exception("Failed to get active programs");
        }
    }

    /**
     * Create a new program
     */
    public function createProgram($data) {
        try {
            if (empty($data['slug'])) {
                $data['slug'] = Utility::generateSlug($data['title']);
            }

            // Verify unique slug
            if ($this->exists(['slug' => $data['slug']])) {
                throw new Exception("Program with this slug already exists");
            }

            // Set default status if not provided
            if (empty($data['status'])) {
                $data['status'] = 'active';
            }

            return $this->create($data);
        } catch (Exception $e) {
            error_log("Create Program Error: " . $e->getMessage());
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Update a program
     */
    public function updateProgram($id, $data) {
        try {
            if (!empty($data['title'])) {
                $data['slug'] = Utility::generateSlug($data['title']);

                // Verify unique slug
                $existing = $this->findOneBy('slug', $data['slug']);
                if ($existing && $existing['id'] != $id) {
                    throw new Exception("Program with this slug already exists");
                }
            }

            return $this->update($id, $data);
        } catch (Exception $e) {
            error_log("Update Program Error: " . $e->getMessage());
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Get program with full details
     */
    public function getProgramWithDetails($id) {
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
            error_log("Get Program Details Error: " . $e->getMessage());
            throw new Exception("Failed to get program details");
        }
    }

    /**
     * Get programs by category
     */
    public function getByCategory($categoryId, $page = 1, $perPage = 10) {
        try {
            return $this->paginate($page, $perPage, [
                'category_id' => $categoryId,
                'status' => 'active'
            ], 'created_at', 'DESC');
        } catch (Exception $e) {
            error_log("Get Programs By Category Error: " . $e->getMessage());
            throw new Exception("Failed to get programs by category");
        }
    }

    /**
     * Get program by slug
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
            error_log("Get Program By Slug Error: " . $e->getMessage());
            throw new Exception("Failed to get program by slug");
        }
    }

    /**
     * Get related programs
     */
    public function getRelated($programId, $categoryId, $limit = 3) {
        try {
            $sql = "SELECT p.*, c.name as category_name 
                    FROM {$this->table} p
                    LEFT JOIN categories c ON p.category_id = c.id
                    WHERE p.id != ? 
                    AND p.category_id = ?
                    AND p.status = 'active'
                    ORDER BY p.created_at DESC
                    LIMIT ?";
            
            $this->db->query($sql, [$programId, $categoryId, $limit]);
            return $this->db->findAll();
        } catch (Exception $e) {
            error_log("Get Related Programs Error: " . $e->getMessage());
            throw new Exception("Failed to get related programs");
        }
    }

    /**
     * Search programs
     */
    public function searchPrograms($searchTerm, $page = 1, $perPage = 10) {
        try {
            return $this->search(['title', 'description'], $searchTerm, $page, $perPage);
        } catch (Exception $e) {
            error_log("Search Programs Error: " . $e->getMessage());
            throw new Exception("Failed to search programs");
        }
    }

    /**
     * Get featured programs
     */
    public function getFeatured($limit = 3) {
        try {
            $sql = "SELECT p.*, c.name as category_name 
                    FROM {$this->table} p
                    LEFT JOIN categories c ON p.category_id = c.id
                    WHERE p.status = 'active'
                    AND p.featured_image IS NOT NULL
                    ORDER BY p.created_at DESC
                    LIMIT ?";
            
            $this->db->query($sql, [$limit]);
            return $this->db->findAll();
        } catch (Exception $e) {
            error_log("Get Featured Programs Error: " . $e->getMessage());
            throw new Exception("Failed to get featured programs");
        }
    }

    /**
     * Get program statistics
     */
    public function getStatistics() {
        try {
            $stats = [
                'total' => $this->count(),
                'active' => $this->count(['status' => 'active']),
                'inactive' => $this->count(['status' => 'inactive'])
            ];

            // Get programs by category
            $sql = "SELECT c.name, COUNT(*) as count 
                    FROM {$this->table} p
                    LEFT JOIN categories c ON p.category_id = c.id
                    WHERE p.status = 'active'
                    GROUP BY p.category_id";
            
            $this->db->query($sql);
            $stats['by_category'] = $this->db->findAll();

            return $stats;
        } catch (Exception $e) {
            error_log("Get Program Statistics Error: " . $e->getMessage());
            throw new Exception("Failed to get program statistics");
        }
    }

    /**
     * Get latest programs
     */
    public function getLatest($limit = 5) {
        try {
            $sql = "SELECT p.*, c.name as category_name 
                    FROM {$this->table} p
                    LEFT JOIN categories c ON p.category_id = c.id
                    WHERE p.status = 'active'
                    ORDER BY p.created_at DESC
                    LIMIT ?";
            
            $this->db->query($sql, [$limit]);
            return $this->db->findAll();
        } catch (Exception $e) {
            error_log("Get Latest Programs Error: " . $e->getMessage());
            throw new Exception("Failed to get latest programs");
        }
    }

    /**
     * Get program categories with counts
     */
    public function getCategoriesWithCounts() {
        try {
            $sql = "SELECT c.*, COUNT(p.id) as program_count 
                    FROM categories c
                    LEFT JOIN {$this->table} p ON c.id = p.category_id AND p.status = 'active'
                    WHERE c.module = 'programs'
                    GROUP BY c.id
                    ORDER BY c.name ASC";
            
            $this->db->query($sql);
            return $this->db->findAll();
        } catch (Exception $e) {
            error_log("Get Categories With Counts Error: " . $e->getMessage());
            throw new Exception("Failed to get categories with counts");
        }
    }

    /**
     * Toggle program status
     */
    public function toggleStatus($id) {
        try {
            $program = $this->find($id);
            if (!$program) {
                throw new Exception("Program not found");
            }

            $newStatus = $program['status'] === 'active' ? 'inactive' : 'active';
            return $this->update($id, ['status' => $newStatus]);
        } catch (Exception $e) {
            error_log("Toggle Program Status Error: " . $e->getMessage());
            throw new Exception("Failed to toggle program status");
        }
    }

    /**
     * Get program schedule
     * This assumes you have a program_schedule table relating programs to events/dates
     */
    public function getSchedule($programId) {
        try {
            $sql = "SELECT ps.*, e.title as event_title, e.location 
                    FROM program_schedule ps
                    LEFT JOIN events e ON ps.event_id = e.id
                    WHERE ps.program_id = ?
                    ORDER BY ps.start_date ASC";
            
            $this->db->query($sql, [$programId]);
            return $this->db->findAll();
        } catch (Exception $e) {
            error_log("Get Program Schedule Error: " . $e->getMessage());
            throw new Exception("Failed to get program schedule");
        }
    }
}