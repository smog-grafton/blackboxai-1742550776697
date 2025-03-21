<?php
class Session {
    private static $instance = null;
    private $config;

    private function __construct() {
        $this->config = require __DIR__ . '/../config/config.php';
        $this->start();
    }

    /**
     * Get Session instance (Singleton)
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Start the session with secure configuration
     */
    private function start() {
        if (session_status() === PHP_SESSION_NONE) {
            // Set secure session parameters
            ini_set('session.cookie_httponly', $this->config['session_http_only']);
            ini_set('session.cookie_secure', $this->config['session_secure']);
            ini_set('session.gc_maxlifetime', $this->config['session_lifetime']);
            ini_set('session.use_strict_mode', 1);
            ini_set('session.cookie_samesite', 'Lax');

            session_start();

            // Regenerate session ID periodically
            if (!isset($_SESSION['last_regeneration'])) {
                $this->regenerateId();
            } else if (time() - $_SESSION['last_regeneration'] > 1800) { // 30 minutes
                $this->regenerateId();
            }
        }
    }

    /**
     * Regenerate session ID
     */
    public function regenerateId() {
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }

    /**
     * Set a session value
     */
    public function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    /**
     * Get a session value
     */
    public function get($key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Remove a session value
     */
    public function remove($key) {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
            return true;
        }
        return false;
    }

    /**
     * Check if a session value exists
     */
    public function has($key) {
        return isset($_SESSION[$key]);
    }

    /**
     * Clear all session data
     */
    public function clear() {
        session_unset();
        session_destroy();
        setcookie(session_name(), '', time() - 3600, '/');
    }

    /**
     * Set flash message
     */
    public function setFlash($key, $message) {
        $_SESSION['flash_messages'][$key] = [
            'message' => $message,
            'displayed' => false
        ];
    }

    /**
     * Get flash message
     */
    public function getFlash($key) {
        if (isset($_SESSION['flash_messages'][$key])) {
            $flash = $_SESSION['flash_messages'][$key];
            if (!$flash['displayed']) {
                $_SESSION['flash_messages'][$key]['displayed'] = true;
                return $flash['message'];
            }
        }
        return null;
    }

    /**
     * Clear displayed flash messages
     */
    public function clearDisplayedFlash() {
        if (isset($_SESSION['flash_messages'])) {
            foreach ($_SESSION['flash_messages'] as $key => $flash) {
                if ($flash['displayed']) {
                    unset($_SESSION['flash_messages'][$key]);
                }
            }
        }
    }

    /**
     * Set user data in session
     */
    public function setUser($user) {
        $this->set('user', $user);
        $this->set('user_id', $user['id']);
        $this->set('last_activity', time());
    }

    /**
     * Get logged in user data
     */
    public function getUser() {
        return $this->get('user');
    }

    /**
     * Check if user is logged in
     */
    public function isLoggedIn() {
        return $this->has('user') && $this->has('user_id');
    }

    /**
     * Set remember me token
     */
    public function setRememberToken($userId, $token) {
        $this->set('remember_token', [
            'user_id' => $userId,
            'token' => $token,
            'created_at' => time()
        ]);
    }

    /**
     * Get remember me token
     */
    public function getRememberToken() {
        return $this->get('remember_token');
    }

    /**
     * Check session timeout
     */
    public function checkTimeout() {
        if ($this->isLoggedIn()) {
            $lastActivity = $this->get('last_activity');
            if (time() - $lastActivity > $this->config['session_lifetime']) {
                $this->clear();
                return true;
            }
            $this->set('last_activity', time());
        }
        return false;
    }

    /**
     * Get CSRF token
     */
    public function getCsrfToken() {
        if (!$this->has('csrf_token')) {
            $this->set('csrf_token', bin2hex(random_bytes(32)));
        }
        return $this->get('csrf_token');
    }

    /**
     * Verify CSRF token
     */
    public function verifyCsrfToken($token) {
        return hash_equals($this->getCsrfToken(), $token);
    }

    /**
     * Prevent cloning of the instance (Singleton)
     */
    private function __clone() {}

    /**
     * Prevent unserializing of the instance (Singleton)
     */
    private function __wakeup() {}
}