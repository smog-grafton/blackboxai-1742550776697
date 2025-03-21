<?php
class User extends Model {
    protected $table = 'users';
    protected $fillable = [
        'username',
        'email',
        'password',
        'full_name',
        'role',
        'status',
        'remember_token',
        'last_login'
    ];

    protected $hidden = ['password', 'remember_token'];

    /**
     * Authenticate user
     */
    public function authenticate($username, $password) {
        $sql = "SELECT * FROM {$this->table} WHERE (username = ? OR email = ?) AND status = 'active'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Update last login
            $this->update($user['id'], [
                'last_login' => date('Y-m-d H:i:s')
            ]);

            return $this->hideFields($user);
        }

        return false;
    }

    /**
     * Create new user
     */
    public function create(array $data) {
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        return parent::create($data);
    }

    /**
     * Update user
     */
    public function update($id, array $data) {
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        return parent::update($id, $data);
    }

    /**
     * Set remember token
     */
    public function setRememberToken($userId, $token) {
        return $this->update($userId, [
            'remember_token' => $token
        ]);
    }

    /**
     * Clear remember token
     */
    public function clearRememberToken($userId) {
        return $this->update($userId, [
            'remember_token' => null
        ]);
    }

    /**
     * Get user by remember token
     */
    public function getUserByRememberToken($userId, $token) {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? AND remember_token = ? AND status = 'active'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ? $this->hideFields($user) : false;
    }

    /**
     * Get user by email
     */
    public function getByEmail($email) {
        $sql = "SELECT * FROM {$this->table} WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ? $this->hideFields($user) : false;
    }

    /**
     * Get user by username
     */
    public function getByUsername($username) {
        $sql = "SELECT * FROM {$this->table} WHERE username = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ? $this->hideFields($user) : false;
    }

    /**
     * Check if email exists
     */
    public function emailExists($email, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE email = ?";
        $params = [$email];

        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result['count'] > 0;
    }

    /**
     * Check if username exists
     */
    public function usernameExists($username, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE username = ?";
        $params = [$username];

        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result['count'] > 0;
    }

    /**
     * Get user statistics
     */
    public function getStatistics() {
        $stats = [
            'total' => 0,
            'active' => 0,
            'inactive' => 0,
            'banned' => 0,
            'by_role' => [],
            'recent_logins' => []
        ];

        // Get counts by status
        $sql = "SELECT status, COUNT(*) as count FROM {$this->table} GROUP BY status";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($results as $result) {
            $stats[$result['status']] = $result['count'];
            $stats['total'] += $result['count'];
        }

        // Get counts by role
        $sql = "SELECT role, COUNT(*) as count FROM {$this->table} GROUP BY role";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stats['by_role'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        // Get recent logins
        $sql = "SELECT id, username, full_name, last_login 
                FROM {$this->table} 
                WHERE last_login IS NOT NULL 
                ORDER BY last_login DESC 
                LIMIT 5";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stats['recent_logins'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $stats;
    }

    /**
     * Get users by role
     */
    public function getByRole($role) {
        $sql = "SELECT * FROM {$this->table} WHERE role = ? ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$role]);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map([$this, 'hideFields'], $users);
    }

    /**
     * Ban user
     */
    public function ban($userId) {
        return $this->update($userId, [
            'status' => 'banned'
        ]);
    }

    /**
     * Activate user
     */
    public function activate($userId) {
        return $this->update($userId, [
            'status' => 'active'
        ]);
    }

    /**
     * Deactivate user
     */
    public function deactivate($userId) {
        return $this->update($userId, [
            'status' => 'inactive'
        ]);
    }

    /**
     * Change password
     */
    public function changePassword($userId, $newPassword) {
        return $this->update($userId, [
            'password' => $newPassword
        ]);
    }

    /**
     * Get user's activity
     */
    public function getActivity($userId, $limit = 10) {
        $sql = "SELECT 'post' as type, title, created_at FROM posts WHERE author_id = ?
                UNION ALL
                SELECT 'event' as type, title, created_at FROM events WHERE organizer_id = ?
                UNION ALL
                SELECT 'donation' as type, amount as title, created_at FROM donations WHERE user_id = ?
                ORDER BY created_at DESC
                LIMIT ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $userId, $userId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}