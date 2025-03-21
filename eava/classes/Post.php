<?php
require_once __DIR__ . '/Model.php';

class Post extends Model {
    protected $table = 'posts';
    protected $fillable = [
        'title',
        'slug',
        'content',
        'excerpt',
        'featured_image',
        'category_id',
        'author_id',
        'status',
        'published_at'
    ];

    /**
     * Get published posts
     */
    public function getPublished($page = 1, $perPage = 10) {
        try {
            return $this->paginate($page, $perPage, [
                'status' => 'published',
                'published_at <= ?' => date('Y-m-d H:i:s')
            ], 'published_at', 'DESC');
        } catch (Exception $e) {
            error_log("Get Published Posts Error: " . $e->getMessage());
            throw new Exception("Failed to get published posts");
        }
    }

    /**
     * Get posts by category
     */
    public function getByCategory($categoryId, $page = 1, $perPage = 10) {
        try {
            return $this->paginate($page, $perPage, [
                'category_id' => $categoryId,
                'status' => 'published',
                'published_at <= ?' => date('Y-m-d H:i:s')
            ], 'published_at', 'DESC');
        } catch (Exception $e) {
            error_log("Get Posts By Category Error: " . $e->getMessage());
            throw new Exception("Failed to get posts by category");
        }
    }

    /**
     * Get posts by author
     */
    public function getByAuthor($authorId, $page = 1, $perPage = 10) {
        try {
            return $this->paginate($page, $perPage, [
                'author_id' => $authorId,
                'status' => 'published',
                'published_at <= ?' => date('Y-m-d H:i:s')
            ], 'published_at', 'DESC');
        } catch (Exception $e) {
            error_log("Get Posts By Author Error: " . $e->getMessage());
            throw new Exception("Failed to get posts by author");
        }
    }

    /**
     * Search posts
     */
    public function searchPosts($searchTerm, $page = 1, $perPage = 10) {
        try {
            return $this->search(['title', 'content', 'excerpt'], $searchTerm, $page, $perPage);
        } catch (Exception $e) {
            error_log("Search Posts Error: " . $e->getMessage());
            throw new Exception("Failed to search posts");
        }
    }

    /**
     * Get related posts
     */
    public function getRelated($postId, $categoryId, $limit = 3) {
        try {
            $sql = "SELECT * FROM {$this->table} 
                    WHERE id != ? 
                    AND category_id = ? 
                    AND status = 'published' 
                    AND published_at <= ? 
                    ORDER BY published_at DESC 
                    LIMIT ?";
            
            $this->db->query($sql, [$postId, $categoryId, date('Y-m-d H:i:s'), $limit]);
            return $this->db->findAll();
        } catch (Exception $e) {
            error_log("Get Related Posts Error: " . $e->getMessage());
            throw new Exception("Failed to get related posts");
        }
    }

    /**
     * Get post by slug
     */
    public function getBySlug($slug) {
        try {
            return $this->findOneBy('slug', $slug);
        } catch (Exception $e) {
            error_log("Get Post By Slug Error: " . $e->getMessage());
            throw new Exception("Failed to get post by slug");
        }
    }

    /**
     * Create a new post
     */
    public function createPost($data) {
        try {
            // Generate slug if not provided
            if (empty($data['slug'])) {
                $data['slug'] = Utility::generateSlug($data['title']);
            }

            // Set published date if status is published
            if ($data['status'] === 'published' && empty($data['published_at'])) {
                $data['published_at'] = date('Y-m-d H:i:s');
            }

            // Generate excerpt if not provided
            if (empty($data['excerpt']) && !empty($data['content'])) {
                $data['excerpt'] = Utility::truncateText(strip_tags($data['content']), 160);
            }

            return $this->create($data);
        } catch (Exception $e) {
            error_log("Create Post Error: " . $e->getMessage());
            throw new Exception("Failed to create post");
        }
    }

    /**
     * Update a post
     */
    public function updatePost($id, $data) {
        try {
            // Generate slug if title changed
            if (!empty($data['title'])) {
                $data['slug'] = Utility::generateSlug($data['title']);
            }

            // Update published date if status changed to published
            if (isset($data['status']) && $data['status'] === 'published') {
                $current = $this->find($id);
                if ($current['status'] !== 'published') {
                    $data['published_at'] = date('Y-m-d H:i:s');
                }
            }

            // Update excerpt if content changed
            if (!empty($data['content']) && empty($data['excerpt'])) {
                $data['excerpt'] = Utility::truncateText(strip_tags($data['content']), 160);
            }

            return $this->update($id, $data);
        } catch (Exception $e) {
            error_log("Update Post Error: " . $e->getMessage());
            throw new Exception("Failed to update post");
        }
    }

    /**
     * Get post with author and category details
     */
    public function getPostWithDetails($id) {
        try {
            $sql = "SELECT p.*, 
                           u.username as author_name, 
                           u.email as author_email,
                           c.name as category_name,
                           c.slug as category_slug
                    FROM {$this->table} p
                    LEFT JOIN users u ON p.author_id = u.id
                    LEFT JOIN categories c ON p.category_id = c.id
                    WHERE p.id = ?";
            
            $this->db->query($sql, [$id]);
            return $this->db->findOne();
        } catch (Exception $e) {
            error_log("Get Post With Details Error: " . $e->getMessage());
            throw new Exception("Failed to get post details");
        }
    }

    /**
     * Get archive data (posts grouped by month)
     */
    public function getArchiveData() {
        try {
            $sql = "SELECT 
                        DATE_FORMAT(published_at, '%Y-%m') as month,
                        COUNT(*) as post_count
                    FROM {$this->table}
                    WHERE status = 'published'
                    AND published_at <= ?
                    GROUP BY month
                    ORDER BY month DESC";
            
            $this->db->query($sql, [date('Y-m-d H:i:s')]);
            return $this->db->findAll();
        } catch (Exception $e) {
            error_log("Get Archive Data Error: " . $e->getMessage());
            throw new Exception("Failed to get archive data");
        }
    }

    /**
     * Get posts by month
     */
    public function getPostsByMonth($year, $month, $page = 1, $perPage = 10) {
        try {
            $startDate = "{$year}-{$month}-01 00:00:00";
            $endDate = date('Y-m-t 23:59:59', strtotime($startDate));
            
            $sql = "SELECT * FROM {$this->table}
                    WHERE status = 'published'
                    AND published_at BETWEEN ? AND ?
                    ORDER BY published_at DESC
                    LIMIT ? OFFSET ?";
            
            $offset = ($page - 1) * $perPage;
            $this->db->query($sql, [$startDate, $endDate, $perPage, $offset]);
            
            return [
                'data' => $this->db->findAll(),
                'total' => $this->count([
                    'status' => 'published',
                    'published_at >= ?' => $startDate,
                    'published_at <= ?' => $endDate
                ])
            ];
        } catch (Exception $e) {
            error_log("Get Posts By Month Error: " . $e->getMessage());
            throw new Exception("Failed to get posts by month");
        }
    }

    /**
     * Get popular posts
     */
    public function getPopularPosts($limit = 5) {
        try {
            // This is a basic implementation. In a real application,
            // you might want to track views or implement a more sophisticated
            // popularity algorithm
            $sql = "SELECT * FROM {$this->table}
                    WHERE status = 'published'
                    AND published_at <= ?
                    ORDER BY published_at DESC
                    LIMIT ?";
            
            $this->db->query($sql, [date('Y-m-d H:i:s'), $limit]);
            return $this->db->findAll();
        } catch (Exception $e) {
            error_log("Get Popular Posts Error: " . $e->getMessage());
            throw new Exception("Failed to get popular posts");
        }
    }
}