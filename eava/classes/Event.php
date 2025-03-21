<?php
class Event extends Model {
    protected $table = 'events';
    protected $fillable = [
        'title',
        'slug',
        'description',
        'location',
        'start_date',
        'end_date',
        'featured_image',
        'status',
        'is_featured',
        'category_id',
        'organizer_id'
    ];

    /**
     * Get upcoming events
     */
    public function getUpcoming($limit = 5) {
        $sql = "SELECT e.*, c.name as category_name, u.full_name as organizer_name 
                FROM {$this->table} e 
                LEFT JOIN categories c ON e.category_id = c.id 
                LEFT JOIN users u ON e.organizer_id = u.id 
                WHERE e.status = 'published' 
                AND e.start_date >= CURRENT_DATE 
                ORDER BY e.start_date ASC 
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get featured events
     */
    public function getFeatured($limit = 3) {
        $sql = "SELECT e.*, c.name as category_name, u.full_name as organizer_name 
                FROM {$this->table} e 
                LEFT JOIN categories c ON e.category_id = c.id 
                LEFT JOIN users u ON e.organizer_id = u.id 
                WHERE e.status = 'published' 
                AND e.is_featured = 1 
                AND e.start_date >= CURRENT_DATE 
                ORDER BY e.start_date ASC 
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get events by category
     */
    public function getByCategory($categoryId, $page = 1, $perPage = 10) {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT e.*, c.name as category_name, u.full_name as organizer_name 
                FROM {$this->table} e 
                LEFT JOIN categories c ON e.category_id = c.id 
                LEFT JOIN users u ON e.organizer_id = u.id 
                WHERE e.status = 'published' 
                AND e.category_id = ? 
                AND e.start_date >= CURRENT_DATE 
                ORDER BY e.start_date ASC 
                LIMIT ? OFFSET ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$categoryId, $perPage, $offset]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get total count for pagination
        $countSql = "SELECT COUNT(*) as count 
                     FROM {$this->table} 
                     WHERE status = 'published' 
                     AND category_id = ? 
                     AND start_date >= CURRENT_DATE";
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
     * Get event by slug
     */
    public function getBySlug($slug) {
        $sql = "SELECT e.*, c.name as category_name, u.full_name as organizer_name 
                FROM {$this->table} e 
                LEFT JOIN categories c ON e.category_id = c.id 
                LEFT JOIN users u ON e.organizer_id = u.id 
                WHERE e.slug = ? AND e.status = 'published'";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$slug]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get events by date range
     */
    public function getByDateRange($startDate, $endDate) {
        $sql = "SELECT e.*, c.name as category_name, u.full_name as organizer_name 
                FROM {$this->table} e 
                LEFT JOIN categories c ON e.category_id = c.id 
                LEFT JOIN users u ON e.organizer_id = u.id 
                WHERE e.status = 'published' 
                AND e.start_date BETWEEN ? AND ? 
                ORDER BY e.start_date ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$startDate, $endDate]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get event statistics
     */
    public function getStatistics() {
        $stats = [
            'total' => 0,
            'upcoming' => 0,
            'ongoing' => 0,
            'completed' => 0,
            'by_category' => [],
            'by_month' => []
        ];

        // Get total counts by status
        $sql = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN start_date > CURRENT_TIMESTAMP THEN 1 ELSE 0 END) as upcoming,
                SUM(CASE WHEN start_date <= CURRENT_TIMESTAMP AND end_date >= CURRENT_TIMESTAMP THEN 1 ELSE 0 END) as ongoing,
                SUM(CASE WHEN end_date < CURRENT_TIMESTAMP THEN 1 ELSE 0 END) as completed
                FROM {$this->table} WHERE status = 'published'";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $counts = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $stats['total'] = $counts['total'];
        $stats['upcoming'] = $counts['upcoming'];
        $stats['ongoing'] = $counts['ongoing'];
        $stats['completed'] = $counts['completed'];

        // Get counts by category
        $sql = "SELECT c.name, COUNT(*) as count 
                FROM {$this->table} e 
                LEFT JOIN categories c ON e.category_id = c.id 
                WHERE e.status = 'published' 
                GROUP BY c.name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stats['by_category'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        // Get counts by month
        $sql = "SELECT DATE_FORMAT(start_date, '%Y-%m') as month, COUNT(*) as count 
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
     * Get event registrations
     */
    public function getRegistrations($eventId) {
        $sql = "SELECT r.*, u.full_name, u.email 
                FROM event_registrations r 
                LEFT JOIN users u ON r.user_id = u.id 
                WHERE r.event_id = ? 
                ORDER BY r.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$eventId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Register user for event
     */
    public function registerUser($eventId, $userId) {
        try {
            $sql = "INSERT INTO event_registrations (event_id, user_id, status) VALUES (?, ?, 'registered')";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$eventId, $userId]);
        } catch (Exception $e) {
            // Handle duplicate registration
            if ($e->getCode() == 23000) {
                throw new Exception('User already registered for this event');
            }
            throw $e;
        }
    }

    /**
     * Cancel registration
     */
    public function cancelRegistration($eventId, $userId) {
        $sql = "UPDATE event_registrations SET status = 'cancelled' WHERE event_id = ? AND user_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$eventId, $userId]);
    }

    /**
     * Check if event is full
     */
    public function isFull($eventId) {
        $sql = "SELECT COUNT(*) as count 
                FROM event_registrations 
                WHERE event_id = ? AND status = 'registered'";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$eventId]);
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        $event = $this->find($eventId);
        return $count >= $event['max_attendees'];
    }

    /**
     * Get registration count
     */
    public function getRegistrationCount($eventId) {
        $sql = "SELECT COUNT(*) as count 
                FROM event_registrations 
                WHERE event_id = ? AND status = 'registered'";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$eventId]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    }

    /**
     * Count events by status
     */
    public function countByStatus($status) {
        switch ($status) {
            case 'upcoming':
                $where = "start_date > CURRENT_TIMESTAMP";
                break;
            case 'ongoing':
                $where = "start_date <= CURRENT_TIMESTAMP AND end_date >= CURRENT_TIMESTAMP";
                break;
            case 'completed':
                $where = "end_date < CURRENT_TIMESTAMP";
                break;
            default:
                return 0;
        }

        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE status = 'published' AND $where";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    }
}