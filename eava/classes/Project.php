<?php
class Project extends Model {
    protected $table = 'projects';
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
        'manager_id'
    ];

    /**
     * Get active projects
     */
    public function getActive($page = 1, $perPage = 10) {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT p.*, c.name as category_name, u.full_name as manager_name 
                FROM {$this->table} p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN users u ON p.manager_id = u.id 
                WHERE p.status = 'active' 
                ORDER BY p.start_date ASC 
                LIMIT ? OFFSET ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$perPage, $offset]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get total count for pagination
        $countSql = "SELECT COUNT(*) as count FROM {$this->table} WHERE status = 'active'";
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
     * Get project by slug
     */
    public function getBySlug($slug) {
        $sql = "SELECT p.*, c.name as category_name, u.full_name as manager_name 
                FROM {$this->table} p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN users u ON p.manager_id = u.id 
                WHERE p.slug = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$slug]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get project team members
     */
    public function getTeamMembers($projectId) {
        $sql = "SELECT u.*, pt.role 
                FROM project_team pt 
                JOIN users u ON pt.user_id = u.id 
                WHERE pt.project_id = ? 
                ORDER BY pt.created_at ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$projectId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Add team member
     */
    public function addTeamMember($projectId, $userId, $role) {
        try {
            $sql = "INSERT INTO project_team (project_id, user_id, role) VALUES (?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$projectId, $userId, $role]);
        } catch (Exception $e) {
            if ($e->getCode() == 23000) {
                throw new Exception('User is already a team member');
            }
            throw $e;
        }
    }

    /**
     * Remove team member
     */
    public function removeTeamMember($projectId, $userId) {
        $sql = "DELETE FROM project_team WHERE project_id = ? AND user_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$projectId, $userId]);
    }

    /**
     * Update project progress
     */
    public function updateProgress($projectId, $amount) {
        $sql = "UPDATE {$this->table} 
                SET current_amount = current_amount + ? 
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$amount, $projectId]);
    }

    /**
     * Get project milestones
     */
    public function getMilestones($projectId) {
        $sql = "SELECT * FROM project_milestones 
                WHERE project_id = ? 
                ORDER BY due_date ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$projectId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Add milestone
     */
    public function addMilestone($projectId, $data) {
        $sql = "INSERT INTO project_milestones 
                (project_id, title, description, due_date, status) 
                VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $projectId,
            $data['title'],
            $data['description'],
            $data['due_date'],
            $data['status'] ?? 'pending'
        ]);
    }

    /**
     * Update milestone status
     */
    public function updateMilestoneStatus($milestoneId, $status) {
        $sql = "UPDATE project_milestones SET status = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$status, $milestoneId]);
    }

    /**
     * Get project statistics
     */
    public function getStatistics() {
        $stats = [
            'total_projects' => 0,
            'active_projects' => 0,
            'completed_projects' => 0,
            'total_funding' => 0,
            'by_category' => [],
            'by_status' => [],
            'recent_milestones' => []
        ];

        // Get basic counts
        $sql = "SELECT 
                COUNT(*) as total_projects,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_projects,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_projects,
                SUM(current_amount) as total_funding
                FROM {$this->table}";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $basic = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $stats['total_projects'] = $basic['total_projects'];
        $stats['active_projects'] = $basic['active_projects'];
        $stats['completed_projects'] = $basic['completed_projects'];
        $stats['total_funding'] = $basic['total_funding'];

        // Get counts by category
        $sql = "SELECT c.name, COUNT(*) as count 
                FROM {$this->table} p 
                LEFT JOIN categories c ON p.category_id = c.id 
                GROUP BY c.name";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stats['by_category'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        // Get counts by status
        $sql = "SELECT status, COUNT(*) as count FROM {$this->table} GROUP BY status";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stats['by_status'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        // Get recent milestones
        $sql = "SELECT pm.*, p.title as project_title 
                FROM project_milestones pm 
                JOIN {$this->table} p ON pm.project_id = p.id 
                WHERE pm.due_date >= CURRENT_DATE 
                ORDER BY pm.due_date ASC 
                LIMIT 5";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stats['recent_milestones'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $stats;
    }

    /**
     * Get user projects
     */
    public function getUserProjects($userId) {
        $sql = "SELECT p.*, c.name as category_name 
                FROM {$this->table} p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.manager_id = ? OR EXISTS (
                    SELECT 1 FROM project_team pt WHERE pt.project_id = p.id AND pt.user_id = ?
                ) 
                ORDER BY p.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get similar projects
     */
    public function getSimilar($projectId, $limit = 3) {
        $project = $this->find($projectId);
        if (!$project) {
            return [];
        }

        $sql = "SELECT p.*, c.name as category_name 
                FROM {$this->table} p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.id != ? 
                AND p.category_id = ? 
                AND p.status = 'active' 
                ORDER BY p.start_date ASC 
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$projectId, $project['category_id'], $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get project progress
     */
    public function getProgress($projectId) {
        $project = $this->find($projectId);
        if (!$project) {
            return 0;
        }

        return [
            'current' => $project['current_amount'],
            'goal' => $project['goal_amount'],
            'percentage' => ($project['current_amount'] / $project['goal_amount']) * 100,
            'remaining' => $project['goal_amount'] - $project['current_amount'],
            'days_left' => max(0, ceil((strtotime($project['end_date']) - time()) / 86400))
        ];
    }

    /**
     * Check if user is team member
     */
    public function isTeamMember($projectId, $userId) {
        $sql = "SELECT COUNT(*) as count 
                FROM project_team 
                WHERE project_id = ? AND user_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$projectId, $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0;
    }
}