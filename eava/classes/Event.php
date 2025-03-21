<?php
require_once __DIR__ . '/Model.php';

class Event extends Model {
    protected $table = 'events';
    protected $fillable = [
        'title',
        'slug',
        'description',
        'start_date',
        'end_date',
        'location',
        'featured_image',
        'status',
        'category_id',
        'organizer_id'
    ];

    /**
     * Get upcoming events
     */
    public function getUpcoming($limit = null, $categoryId = null) {
        try {
            $sql = "SELECT e.*, c.name as category_name, u.full_name as organizer_name 
                    FROM {$this->table} e 
                    LEFT JOIN categories c ON e.category_id = c.id 
                    LEFT JOIN users u ON e.organizer_id = u.id 
                    WHERE e.start_date >= ? 
                    AND e.status = 'upcoming'";
            
            $params = [date('Y-m-d H:i:s')];
            
            if ($categoryId) {
                $sql .= " AND e.category_id = ?";
                $params[] = $categoryId;
            }
            
            $sql .= " ORDER BY e.start_date ASC";
            
            if ($limit) {
                $sql .= " LIMIT ?";
                $params[] = $limit;
            }
            
            $this->db->query($sql, $params);
            return $this->db->findAll();
        } catch (Exception $e) {
            error_log("Get Upcoming Events Error: " . $e->getMessage());
            throw new Exception("Failed to get upcoming events");
        }
    }

    /**
     * Get ongoing events
     */
    public function getOngoing() {
        try {
            $now = date('Y-m-d H:i:s');
            $sql = "SELECT e.*, c.name as category_name, u.full_name as organizer_name 
                    FROM {$this->table} e 
                    LEFT JOIN categories c ON e.category_id = c.id 
                    LEFT JOIN users u ON e.organizer_id = u.id 
                    WHERE e.start_date <= ? 
                    AND e.end_date >= ? 
                    AND e.status = 'ongoing'
                    ORDER BY e.start_date ASC";
            
            $this->db->query($sql, [$now, $now]);
            return $this->db->findAll();
        } catch (Exception $e) {
            error_log("Get Ongoing Events Error: " . $e->getMessage());
            throw new Exception("Failed to get ongoing events");
        }
    }

    /**
     * Get past events
     */
    public function getPast($page = 1, $perPage = 10) {
        try {
            return $this->paginate($page, $perPage, [
                'end_date < ?' => date('Y-m-d H:i:s'),
                'status' => 'completed'
            ], 'end_date', 'DESC');
        } catch (Exception $e) {
            error_log("Get Past Events Error: " . $e->getMessage());
            throw new Exception("Failed to get past events");
        }
    }

    /**
     * Create a new event
     */
    public function createEvent($data) {
        try {
            if (empty($data['slug'])) {
                $data['slug'] = Utility::generateSlug($data['title']);
            }

            // Validate dates
            if (strtotime($data['end_date']) < strtotime($data['start_date'])) {
                throw new Exception("End date cannot be before start date");
            }

            // Set initial status
            if (empty($data['status'])) {
                $now = time();
                $startTime = strtotime($data['start_date']);
                $endTime = strtotime($data['end_date']);

                if ($now < $startTime) {
                    $data['status'] = 'upcoming';
                } elseif ($now >= $startTime && $now <= $endTime) {
                    $data['status'] = 'ongoing';
                } else {
                    $data['status'] = 'completed';
                }
            }

            return $this->create($data);
        } catch (Exception $e) {
            error_log("Create Event Error: " . $e->getMessage());
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Update an event
     */
    public function updateEvent($id, $data) {
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

            // Update status based on dates
            if (!empty($data['start_date']) || !empty($data['end_date'])) {
                $event = $this->find($id);
                $startDate = !empty($data['start_date']) ? $data['start_date'] : $event['start_date'];
                $endDate = !empty($data['end_date']) ? $data['end_date'] : $event['end_date'];
                
                $now = time();
                $startTime = strtotime($startDate);
                $endTime = strtotime($endDate);

                if ($now < $startTime) {
                    $data['status'] = 'upcoming';
                } elseif ($now >= $startTime && $now <= $endTime) {
                    $data['status'] = 'ongoing';
                } else {
                    $data['status'] = 'completed';
                }
            }

            return $this->update($id, $data);
        } catch (Exception $e) {
            error_log("Update Event Error: " . $e->getMessage());
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Get event with full details
     */
    public function getEventWithDetails($id) {
        try {
            $sql = "SELECT e.*, 
                           c.name as category_name,
                           c.slug as category_slug,
                           u.full_name as organizer_name,
                           u.email as organizer_email
                    FROM {$this->table} e
                    LEFT JOIN categories c ON e.category_id = c.id
                    LEFT JOIN users u ON e.organizer_id = u.id
                    WHERE e.id = ?";
            
            $this->db->query($sql, [$id]);
            return $this->db->findOne();
        } catch (Exception $e) {
            error_log("Get Event Details Error: " . $e->getMessage());
            throw new Exception("Failed to get event details");
        }
    }

    /**
     * Get events by date range
     */
    public function getByDateRange($startDate, $endDate, $categoryId = null) {
        try {
            $sql = "SELECT e.*, c.name as category_name 
                    FROM {$this->table} e
                    LEFT JOIN categories c ON e.category_id = c.id
                    WHERE ((e.start_date BETWEEN ? AND ?) 
                    OR (e.end_date BETWEEN ? AND ?))";
            
            $params = [$startDate, $endDate, $startDate, $endDate];
            
            if ($categoryId) {
                $sql .= " AND e.category_id = ?";
                $params[] = $categoryId;
            }
            
            $sql .= " ORDER BY e.start_date ASC";
            
            $this->db->query($sql, $params);
            return $this->db->findAll();
        } catch (Exception $e) {
            error_log("Get Events By Date Range Error: " . $e->getMessage());
            throw new Exception("Failed to get events by date range");
        }
    }

    /**
     * Get events for calendar
     */
    public function getCalendarEvents($year, $month) {
        try {
            $startDate = date('Y-m-d', mktime(0, 0, 0, $month, 1, $year));
            $endDate = date('Y-m-t', mktime(0, 0, 0, $month, 1, $year));
            
            $sql = "SELECT id, title, start_date, end_date, status, featured_image 
                    FROM {$this->table}
                    WHERE (start_date BETWEEN ? AND ?) 
                    OR (end_date BETWEEN ? AND ?)
                    OR (start_date <= ? AND end_date >= ?)
                    ORDER BY start_date ASC";
            
            $this->db->query($sql, [$startDate, $endDate, $startDate, $endDate, $startDate, $endDate]);
            return $this->db->findAll();
        } catch (Exception $e) {
            error_log("Get Calendar Events Error: " . $e->getMessage());
            throw new Exception("Failed to get calendar events");
        }
    }

    /**
     * Get event by slug
     */
    public function getBySlug($slug) {
        try {
            $sql = "SELECT e.*, 
                           c.name as category_name,
                           c.slug as category_slug,
                           u.full_name as organizer_name
                    FROM {$this->table} e
                    LEFT JOIN categories c ON e.category_id = c.id
                    LEFT JOIN users u ON e.organizer_id = u.id
                    WHERE e.slug = ?";
            
            $this->db->query($sql, [$slug]);
            return $this->db->findOne();
        } catch (Exception $e) {
            error_log("Get Event By Slug Error: " . $e->getMessage());
            throw new Exception("Failed to get event by slug");
        }
    }

    /**
     * Update event status based on dates
     */
    public function updateEventStatuses() {
        try {
            $now = date('Y-m-d H:i:s');
            
            // Update to ongoing
            $sql = "UPDATE {$this->table} 
                    SET status = 'ongoing' 
                    WHERE start_date <= ? 
                    AND end_date >= ? 
                    AND status = 'upcoming'";
            $this->db->query($sql, [$now, $now]);
            
            // Update to completed
            $sql = "UPDATE {$this->table} 
                    SET status = 'completed' 
                    WHERE end_date < ? 
                    AND status IN ('upcoming', 'ongoing')";
            $this->db->query($sql, [$now]);
            
            return true;
        } catch (Exception $e) {
            error_log("Update Event Statuses Error: " . $e->getMessage());
            throw new Exception("Failed to update event statuses");
        }
    }

    /**
     * Search events
     */
    public function searchEvents($searchTerm, $page = 1, $perPage = 10) {
        try {
            return $this->search(['title', 'description', 'location'], $searchTerm, $page, $perPage);
        } catch (Exception $e) {
            error_log("Search Events Error: " . $e->getMessage());
            throw new Exception("Failed to search events");
        }
    }
}