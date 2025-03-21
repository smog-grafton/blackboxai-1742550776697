<?php
class Category extends Model {
    protected $table = 'categories';
    protected $fillable = [
        'name',
        'slug',
        'description',
        'module',
        'parent_id'
    ];

    /**
     * Get categories by module
     */
    public function getByModule($module) {
        $sql = "SELECT * FROM {$this->table} WHERE module = ? ORDER BY name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$module]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get category with its parent
     */
    public function getWithParent($id) {
        $sql = "SELECT c.*, p.name as parent_name 
                FROM {$this->table} c 
                LEFT JOIN {$this->table} p ON c.parent_id = p.id 
                WHERE c.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get category by slug
     */
    public function getBySlug($slug) {
        $sql = "SELECT * FROM {$this->table} WHERE slug = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$slug]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get child categories
     */
    public function getChildren($parentId) {
        $sql = "SELECT * FROM {$this->table} WHERE parent_id = ? ORDER BY name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$parentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get category tree
     */
    public function getTree($module = null) {
        $where = $module ? "WHERE module = ?" : "";
        $params = $module ? [$module] : [];
        
        $sql = "SELECT * FROM {$this->table} $where ORDER BY parent_id, name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $this->buildTree($categories);
    }

    /**
     * Build category tree from flat array
     */
    private function buildTree(array $categories, $parentId = null) {
        $branch = [];

        foreach ($categories as $category) {
            if ($category['parent_id'] === $parentId) {
                $children = $this->buildTree($categories, $category['id']);
                if ($children) {
                    $category['children'] = $children;
                }
                $branch[] = $category;
            }
        }

        return $branch;
    }

    /**
     * Get category path (breadcrumb)
     */
    public function getPath($id) {
        $path = [];
        $category = $this->find($id);

        while ($category) {
            array_unshift($path, $category);
            $category = $category['parent_id'] ? $this->find($category['parent_id']) : null;
        }

        return $path;
    }

    /**
     * Get category statistics
     */
    public function getStatistics() {
        $stats = [
            'total' => 0,
            'by_module' => [],
            'most_used' => []
        ];

        // Get total count
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stats['total'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        // Get counts by module
        $sql = "SELECT module, COUNT(*) as count FROM {$this->table} GROUP BY module";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stats['by_module'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        // Get most used categories (based on posts)
        $sql = "SELECT c.name, COUNT(p.id) as count 
                FROM {$this->table} c 
                LEFT JOIN posts p ON p.category_id = c.id 
                GROUP BY c.id 
                ORDER BY count DESC 
                LIMIT 5";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stats['most_used'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        return $stats;
    }

    /**
     * Check if category has children
     */
    public function hasChildren($id) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE parent_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0;
    }

    /**
     * Check if category is in use
     */
    public function isInUse($id) {
        $tables = ['posts', 'events', 'projects', 'programs'];
        
        foreach ($tables as $table) {
            $sql = "SELECT COUNT(*) as count FROM $table WHERE category_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            if ($stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Delete category and optionally move content to another category
     */
    public function deleteAndMove($id, $moveToId = null) {
        try {
            $this->db->beginTransaction();

            if ($moveToId) {
                $tables = ['posts', 'events', 'projects', 'programs'];
                foreach ($tables as $table) {
                    $sql = "UPDATE $table SET category_id = ? WHERE category_id = ?";
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute([$moveToId, $id]);
                }
            }

            // Update children categories
            $sql = "UPDATE {$this->table} SET parent_id = ? WHERE parent_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$moveToId, $id]);

            // Delete the category
            $this->delete($id);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Override parent create method to generate slug
     */
    public function create(array $data) {
        if (!isset($data['slug'])) {
            $data['slug'] = Utility::generateSlug($data['name']);
        }
        return parent::create($data);
    }

    /**
     * Override parent update method to generate slug
     */
    public function update($id, array $data) {
        if (isset($data['name']) && !isset($data['slug'])) {
            $data['slug'] = Utility::generateSlug($data['name']);
        }
        return parent::update($id, $data);
    }
}