<?php
class Program extends Model {
    protected $table = 'programs';
    protected $fillable = [
        'title',
        'slug',
        'description',
        'goal_amount',
        'status',
        'category_id',
        'coordinator_id'
    ];

    /**
     * Get active programs
     */
    public function getActive($page = 1, $perPage = 10) {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT p.*, c.name as category_name, u.full_name as coordinator_name 
                FROM {$this->table} p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN users u ON p.coordinator_id = u.id 
                WHERE p.status = 'active' 
                ORDER BY p.created_at DESC 
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
     * Get program by slug
     */
    public function getBySlug($slug) {
        $sql = "SELECT p.*, c.name as category_name, u.full_name as coordinator_name 
                FROM {$this->table} p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN users u ON p.coordinator_id = u.id 
                WHERE p.slug = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$slug]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get program participants
     */
    public function getParticipants($programId) {
        $sql = "SELECT u.* 
                FROM program_participants pp 
                JOIN users u ON pp.user_id = u.id 
                WHERE pp.program_id = ? 
                ORDER BY pp.created_at ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$programId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Add participant to program
     */
    public function addParticipant($programId, $userId) {
        try {
            // Check if program is full
            if ($this->isFull($programId)) {
                throw new Exception('Program is at maximum capacity');
            }

            $sql = "INSERT INTO program_participants (program_id, user_id) VALUES (?, ?)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$programId, $userId]);
        } catch (Exception $e) {
            if ($e->getCode() == 23000) { // Duplicate entry
                throw new Exception('User is already enrolled in this program');
            }
            throw $e;
        }
    }

    /**
     * Remove participant from program
     */
    public function removeParticipant($programId, $userId) {
        $sql = "DELETE FROM program_participants WHERE program_id = ? AND user_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$programId, $userId]);
    }

    /**
     * Check if program is full
     */
    public function isFull($programId) {
        $program = $this->find($programId);
        if (!$program) {
            return true;
        }

        $sql = "SELECT COUNT(*) as count FROM program_participants WHERE program_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$programId]);
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        return $count >= $program['max_participants'];
    }

    /**
     * Get program statistics
     */
    public function getStatistics() {
        $stats = [
            'total_programs' => 0,
            'active_programs' => 0,
            'total_participants' => 0,
            'by_category' => [],
            'by_status' => [],
            'popular_programs' => []
        ];

        // Get basic counts
        $sql = "SELECT 
                COUNT(*) as total_programs,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_programs
                FROM {$this->table}";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $basic = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $stats['total_programs'] = $basic['total_programs'];
        $stats['active_programs'] = $basic['active_programs'];

        // Get total participants
        $sql = "SELECT COUNT(*) as count FROM program_participants";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stats['total_participants'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

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

        // Get most popular programs
        $sql = "SELECT p.*, COUNT(pp.user_id) as participant_count 
                FROM {$this->table} p 
                LEFT JOIN program_participants pp ON p.id = pp.program_id 
                GROUP BY p.id 
                ORDER BY participant_count DESC 
                LIMIT 5";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stats['popular_programs'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $stats;
    }

    /**
     * Get user programs
     */
    public function getUserPrograms($userId) {
        $sql = "SELECT p.*, c.name as category_name 
                FROM {$this->table} p 
                LEFT JOIN categories c ON p.category_id = c.id 
                JOIN program_participants pp ON p.id = pp.program_id 
                WHERE pp.user_id = ? 
                ORDER BY p.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get similar programs
     */
    public function getSimilar($programId, $limit = 3) {
        $program = $this->find($programId);
        if (!$program) {
            return [];
        }

        $sql = "SELECT p.*, c.name as category_name 
                FROM {$this->table} p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.id != ? 
                AND p.category_id = ? 
                AND p.status = 'active' 
                ORDER BY p.created_at DESC 
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$programId, $program['category_id'], $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get participant count
     */
    public function getParticipantCount($programId) {
        $sql = "SELECT COUNT(*) as count FROM program_participants WHERE program_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$programId]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    }

    /**
     * Check if user is enrolled
     */
    public function isUserEnrolled($programId, $userId) {
        $sql = "SELECT COUNT(*) as count 
                FROM program_participants 
                WHERE program_id = ? AND user_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$programId, $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0;
    }

    /**
     * Get program schedule
     */
    public function getSchedule($programId) {
        $sql = "SELECT * FROM program_schedule WHERE program_id = ? ORDER BY date, start_time";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$programId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Add schedule item
     */
    public function addScheduleItem($programId, $data) {
        $sql = "INSERT INTO program_schedule 
                (program_id, title, description, date, start_time, end_time, location) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $programId,
            $data['title'],
            $data['description'],
            $data['date'],
            $data['start_time'],
            $data['end_time'],
            $data['location']
        ]);
    }
}