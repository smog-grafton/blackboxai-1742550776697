<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/Database.php';

class User {
    private $db;
    private $data;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function login($username, $password) {
        try {
            $this->db->query("SELECT * FROM users WHERE (username = ? OR email = ?) AND status = 'active'", 
                [$username, $username]);
            $user = $this->db->findOne();

            if ($user && password_verify($password, $user['password'])) {
                // Update last login
                $this->db->query("UPDATE users SET last_login = NOW() WHERE id = ?", [$user['id']]);
                
                // Set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['full_name'] = $user['full_name'];
                
                // Set CSRF token
                $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
                
                return true;
            }
            return false;
        } catch (Exception $e) {
            error_log("Login Error: " . $e->getMessage());
            return false;
        }
    }

    public function logout() {
        session_unset();
        session_destroy();
        return true;
    }

    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public function isAdmin() {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }

    public function isEditor() {
        return isset($_SESSION['role']) && ($_SESSION['role'] === 'editor' || $_SESSION['role'] === 'admin');
    }

    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }

        if (!$this->data) {
            $this->db->query("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
            $this->data = $this->db->findOne();
        }

        return $this->data;
    }

    public function create($userData) {
        try {
            // Validate required fields
            $requiredFields = ['username', 'password', 'email', 'full_name'];
            foreach ($requiredFields as $field) {
                if (empty($userData[$field])) {
                    throw new Exception("$field is required");
                }
            }

            // Check if username or email already exists
            $this->db->query("SELECT id FROM users WHERE username = ? OR email = ?", 
                [$userData['username'], $userData['email']]);
            if ($this->db->count() > 0) {
                throw new Exception("Username or email already exists");
            }

            // Hash password
            $userData['password'] = password_hash($userData['password'], PASSWORD_DEFAULT);
            
            $sql = "INSERT INTO users (username, password, email, full_name, role, status) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            
            $this->db->query($sql, [
                $userData['username'],
                $userData['password'],
                $userData['email'],
                $userData['full_name'],
                $userData['role'] ?? 'user',
                $userData['status'] ?? 'active'
            ]);

            return $this->db->lastInsertId();
        } catch (Exception $e) {
            error_log("User Creation Error: " . $e->getMessage());
            throw new Exception($e->getMessage());
        }
    }

    public function update($userId, $userData) {
        try {
            $updates = [];
            $params = [];

            // Build update query dynamically
            foreach ($userData as $key => $value) {
                if ($key === 'password' && !empty($value)) {
                    $value = password_hash($value, PASSWORD_DEFAULT);
                }
                if ($value !== null) {
                    $updates[] = "$key = ?";
                    $params[] = $value;
                }
            }

            if (empty($updates)) {
                return true; // Nothing to update
            }

            $params[] = $userId;
            $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
            
            $this->db->query($sql, $params);
            return true;
        } catch (Exception $e) {
            error_log("User Update Error: " . $e->getMessage());
            throw new Exception("Failed to update user");
        }
    }

    public function delete($userId) {
        try {
            // Check if user exists
            $this->db->query("SELECT role FROM users WHERE id = ?", [$userId]);
            $user = $this->db->findOne();
            
            if (!$user) {
                throw new Exception("User not found");
            }

            // Prevent deletion of last admin
            if ($user['role'] === 'admin') {
                $this->db->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
                $adminCount = $this->db->findOne()['count'];
                if ($adminCount <= 1) {
                    throw new Exception("Cannot delete the last admin user");
                }
            }

            $this->db->query("DELETE FROM users WHERE id = ?", [$userId]);
            return true;
        } catch (Exception $e) {
            error_log("User Deletion Error: " . $e->getMessage());
            throw new Exception($e->getMessage());
        }
    }

    public function getById($userId) {
        $this->db->query("SELECT id, username, email, full_name, role, status, last_login, created_at 
            FROM users WHERE id = ?", [$userId]);
        return $this->db->findOne();
    }

    public function getAll($params = []) {
        $conditions = [];
        $queryParams = [];
        $limit = $params['limit'] ?? 10;
        $offset = $params['offset'] ?? 0;

        if (!empty($params['search'])) {
            $conditions[] = "(username LIKE ? OR email LIKE ? OR full_name LIKE ?)";
            $search = "%{$params['search']}%";
            array_push($queryParams, $search, $search, $search);
        }

        if (!empty($params['role'])) {
            $conditions[] = "role = ?";
            $queryParams[] = $params['role'];
        }

        if (!empty($params['status'])) {
            $conditions[] = "status = ?";
            $queryParams[] = $params['status'];
        }

        $whereClause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";

        $sql = "SELECT id, username, email, full_name, role, status, last_login, created_at 
                FROM users 
                $whereClause 
                ORDER BY created_at DESC 
                LIMIT ? OFFSET ?";

        array_push($queryParams, $limit, $offset);

        $this->db->query($sql, $queryParams);
        return $this->db->findAll();
    }

    public function count($params = []) {
        $conditions = [];
        $queryParams = [];

        if (!empty($params['search'])) {
            $conditions[] = "(username LIKE ? OR email LIKE ? OR full_name LIKE ?)";
            $search = "%{$params['search']}%";
            array_push($queryParams, $search, $search, $search);
        }

        if (!empty($params['role'])) {
            $conditions[] = "role = ?";
            $queryParams[] = $params['role'];
        }

        if (!empty($params['status'])) {
            $conditions[] = "status = ?";
            $queryParams[] = $params['status'];
        }

        $whereClause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";

        $sql = "SELECT COUNT(*) as total FROM users $whereClause";
        $this->db->query($sql, $queryParams);
        return $this->db->findOne()['total'];
    }

    public function validateCSRFToken($token) {
        return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
    }

    public function generateCSRFToken() {
        if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
            $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
        }
        return $_SESSION[CSRF_TOKEN_NAME];
    }
}