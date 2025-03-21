<?php
class Post extends Model {
    protected $table = 'posts';
    protected $fillable = [
        'title',
        'slug',
        'content',
        'excerpt',
        'featured_image',
        'status',
        'category_id',
        'author_id',
        'is_featured',
        'published_at'
    ];

    /**
     * Get featured posts
     */
    public function getFeatured($limit = 3) {
        $sql = "SELECT p.*, c.name as category_name, u.full_name as author_name 
                FROM {$this->table} p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN users u ON p.author_id = u.id 
                WHERE p.status = 'published' AND p.is_featured = 1 
                ORDER BY p.published_at DESC 
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get recent posts
     */
    public function getRecent($limit = 5) {
        $sql = "SELECT p.*, c.name as category_name, u.full_name as author_name 
                FROM {$this->table} p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN users u ON p.author_id = u.id 
                WHERE p.status = 'published' 
                ORDER BY p.published_at DESC 
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get posts by category
     */
    public function getByCategory($categoryId, $page = 1, $perPage = 10) {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT p.*, c.name as category_name, u.full_name as author_name 
                FROM {$this->table} p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN users u ON p.author_id = u.id 
                WHERE p.status = 'published' AND p.category_id = ? 
                ORDER BY p.published_at DESC 
                LIMIT ? OFFSET ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$categoryId, $perPage, $offset]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get total count for pagination
        $countSql = "SELECT COUNT(*) as count FROM {$this->table} WHERE status = 'published' AND category_id = ?";
        $stmt = $this->db->prepare($countSql);
        $stmt->execute([$categoryId]);
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
     * Search posts
     */
    public function search($query, $page = 1, $perPage = 10) {
        $offset = ($page - 1) * $perPage;
        $searchTerm = "%$query%";
        
        $sql = "SELECT p.*, c.name as category_name, u.full_name as author_name 
                FROM {$this->table} p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN users u ON p.author_id = u.id 
                WHERE p.status = 'published' 
                AND (p.title LIKE ? OR p.content LIKE ? OR p.excerpt LIKE ?) 
                ORDER BY p.published_at DESC 
                LIMIT ? OFFSET ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $perPage, $offset]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get total count for pagination
        $countSql = "SELECT COUNT(*) as count FROM {$this->table} 
                     WHERE status = 'published' 
                     AND (title LIKE ? OR content LIKE ? OR excerpt LIKE ?)";
        $stmt = $this->db->prepare($countSql);
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
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
     * Get post by slug
     */
    public function getBySlug($slug) {
        $sql = "SELECT p.*, c.name as category_name, u.full_name as author_name 
                FROM {$this->table} p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN users u ON p.author_id = u.id 
                WHERE p.slug = ? AND p.status = 'published'";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$slug]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get related posts
     */
    public function getRelated($categoryId, $postId, $limit = 3) {
        $sql = "SELECT p.*, c.name as category_name, u.full_name as author_name 
                FROM {$this->table} p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN users u ON p.author_id = u.id 
                WHERE p.status = 'published' 
                AND p.category_id = ? 
                AND p.id != ? 
                ORDER BY p.published_at DESC 
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$categoryId, $postId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get posts by author
     */
    public function getByAuthor($authorId, $page = 1, $perPage = 10) {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT p.*, c.name as category_name, u.full_name as author_name 
                FROM {$this->table} p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN users u ON p.author_id = u.id 
                WHERE p.status = 'published' AND p.author_id = ? 
                ORDER BY p.published_at DESC 
                LIMIT ? OFFSET ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$authorId, $perPage, $offset]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get total count for pagination
        $countSql = "SELECT COUNT(*) as count FROM {$this->table} WHERE status = 'published' AND author_id = ?";
        $stmt = $this->db->prepare($countSql);
        $stmt->execute([$authorId]);
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
     * Get post statistics
     */
    public function getStatistics() {
        $stats = [
            'total' => 0,
            'published' => 0,
            'draft' => 0,
            'by_category' => [],
            'by_month' => []
        ];

        // Get total counts
        $sql = "SELECT status, COUNT(*) as count FROM {$this->table} GROUP BY status";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($results as $result) {
            $stats[$result['status']] = $result['count'];
            $stats['total'] += $result['count'];
        }

        // Get counts by category
        $sql = "SELECT c.name, COUNT(*) as count 
                FROM {$this->table} p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.status = 'published' 
                GROUP BY c.name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stats['by_category'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        // Get counts by month
        $sql = "SELECT DATE_FORMAT(published_at, '%Y-%m') as month, COUNT(*) as count 
                FROM {$this->table} 
                WHERE status = 'published' 
                GROUP BY month 
                ORDER BY month DESC 
                LIMIT 12";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stats['by_month'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        return $stats;
    }

    /**
     * Override parent paginate method to include joins
     */
    public function paginate($page = 1, $perPage = 10, $conditions = []) {
        $offset = ($page - 1) * $perPage;
        
        // Build WHERE clause
        $where = '';
        $params = [];
        if (!empty($conditions)) {
            $whereClauses = [];
            foreach ($conditions as $field => $value) {
                $whereClauses[] = "p.$field = ?";
                $params[] = $value;
            }
            $where = 'WHERE ' . implode(' AND ', $whereClauses);
        }

        $sql = "SELECT p.*, c.name as category_name, u.full_name as author_name 
                FROM {$this->table} p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN users u ON p.author_id = u.id 
                $where 
                ORDER BY p.created_at DESC 
                LIMIT ? OFFSET ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([...$params, $perPage, $offset]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get total count for pagination
        $countSql = "SELECT COUNT(*) as count FROM {$this->table} p $where";
        $stmt = $this->db->prepare($countSql);
        $stmt->execute($params);
        $total = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        return [
            'data' => $data,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage)
        ];
    }
}