<?php
class Session {
    private static $instance = null;
    private $logger;

    private function __construct() {
        $this->logger = Logger::getInstance();
        $this->start();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Start the session securely
     */
    private function start() {
        if (session_status() === PHP_SESSION_NONE) {
            // Set secure session parameters
            ini_set('session.cookie_httponly', 1);
            ini_set('session.use_only_cookies', 1);
            ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
            ini_set('session.cookie_samesite', 'Strict');
            ini_set('session.gc_maxlifetime', 3600); // 1 hour
            ini_set('session.cookie_lifetime', 0); // Until browser closes

            // Start session
            session_start();

            // Regenerate session ID periodically
            if (!isset($_SESSION['last_regeneration'])) {
                $this->regenerate();
            } else {
                // Regenerate session ID every 30 minutes
                $regeneration_time = 30 * 60;
                if (time() - $_SESSION['last_regeneration'] > $regeneration_time) {
                    $this->regenerate();
                }
            }
        }
    }

    /**
     * Regenerate session ID
     */
    public function regenerate() {
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
     * Check if a session key exists
     */
    public function has($key) {
        return isset($_SESSION[$key]);
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
     * Clear all session data
     */
    public function clear() {
        session_unset();
        session_destroy();
        $this->start();
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
    public function setUser($userData) {
        $this->set('user', $userData);
        $this->set('user_ip', $_SERVER['REMOTE_ADDR']);
        $this->set('user_agent', $_SERVER['HTTP_USER_AGENT']);
        $this->logger->info('User session created', ['user_id' => $userData['id']]);
    }

    /**
     * Get user data from session
     */
    public function getUser() {
        return $this->get('user');
    }

    /**
     * Check if user is logged in
     */
    public function isLoggedIn() {
        if (!$this->has('user')) {
            return false;
        }

        // Verify IP and user agent haven't changed
        if ($this->get('user_ip') !== $_SERVER['REMOTE_ADDR'] ||
            $this->get('user_agent') !== $_SERVER['HTTP_USER_AGENT']) {
            $this->logger->warning('Session security check failed', [
                'user_id' => $this->get('user')['id'],
                'ip_mismatch' => $this->get('user_ip') !== $_SERVER['REMOTE_ADDR'],
                'agent_mismatch' => $this->get('user_agent') !== $_SERVER['HTTP_USER_AGENT']
            ]);
            $this->clear();
            return false;
        }

        return true;
    }

    /**
     * Log out user
     */
    public function logout() {
        if ($this->has('user')) {
            $userId = $this->get('user')['id'];
            $this->clear();
            $this->logger->info('User logged out', ['user_id' => $userId]);
        }
    }

    /**
     * Set CSRF token
     */
    public function setCsrfToken() {
        $token = bin2hex(random_bytes(32));
        $this->set('csrf_token', $token);
        return $token;
    }

    /**
     * Verify CSRF token
     */
    public function verifyCsrfToken($token) {
        return hash_equals($this->get('csrf_token', ''), $token);
    }

    /**
     * Set remember me token
     */
    public function setRememberToken($userId, $token) {
        $this->set('remember_token', [
            'user_id' => $userId,
            'token' => $token,
            'expiry' => time() + (30 * 24 * 60 * 60) // 30 days
        ]);
    }

    /**
     * Get remember me token
     */
    public function getRememberToken() {
        $token = $this->get('remember_token');
        if ($token && time() > $token['expiry']) {
            $this->remove('remember_token');
            return null;
        }
        return $token;
    }

    /**
     * Set session timeout
     */
    public function setTimeout($minutes) {
        ini_set('session.gc_maxlifetime', $minutes * 60);
        session_set_cookie_params($minutes * 60);
    }

    /**
     * Get session ID
     */
    public function getId() {
        return session_id();
    }

    /**
     * Get all session data
     */
    public function all() {
        return $_SESSION;
    }

    /**
     * Check session validity
     */
    public function isValid() {
        return session_status() === PHP_SESSION_ACTIVE;
    }
}