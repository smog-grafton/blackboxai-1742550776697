<?php
class Database {
    private static $instance = null;
    private $connection;
    private $config;

    private function __construct() {
        $this->loadConfig();
        $this->connect();
    }

    /**
     * Get Database instance (Singleton)
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Load database configuration
     */
    private function loadConfig() {
        $configFile = __DIR__ . '/../config/config.php';
        if (!file_exists($configFile)) {
            throw new Exception('Database configuration file not found');
        }
        $this->config = require $configFile;
    }

    /**
     * Establish database connection
     */
    private function connect() {
        try {
            $dsn = "mysql:host={$this->config['db_host']};dbname={$this->config['db_name']};charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ];

            $this->connection = new PDO(
                $dsn,
                $this->config['db_user'],
                $this->config['db_pass'],
                $options
            );
        } catch (PDOException $e) {
            // Log the error
            error_log("Database Connection Error: " . $e->getMessage());
            throw new Exception('Failed to connect to database');
        }
    }

    /**
     * Get database connection
     */
    public function getConnection() {
        return $this->connection;
    }

    /**
     * Begin a transaction
     */
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }

    /**
     * Commit a transaction
     */
    public function commit() {
        return $this->connection->commit();
    }

    /**
     * Rollback a transaction
     */
    public function rollback() {
        return $this->connection->rollBack();
    }

    /**
     * Execute a query and return the statement
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            // Log the error
            error_log("Database Query Error: " . $e->getMessage());
            error_log("SQL: " . $sql);
            error_log("Parameters: " . print_r($params, true));
            throw new Exception('Database query failed');
        }
    }

    /**
     * Get the last inserted ID
     */
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }

    /**
     * Quote a string for use in a query
     */
    public function quote($string) {
        return $this->connection->quote($string);
    }

    /**
     * Check if a table exists
     */
    public function tableExists($table) {
        try {
            $result = $this->query("SHOW TABLES LIKE ?", [$table]);
            return $result->rowCount() > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get table columns
     */
    public function getTableColumns($table) {
        try {
            $stmt = $this->query("DESCRIBE $table");
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Execute multiple queries
     */
    public function executeMultiple($queries) {
        try {
            $this->beginTransaction();
            foreach ($queries as $query) {
                $this->query($query);
            }
            $this->commit();
            return true;
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * Import SQL file
     */
    public function importSQL($file) {
        if (!file_exists($file)) {
            throw new Exception('SQL file not found');
        }

        try {
            $sql = file_get_contents($file);
            $queries = array_filter(array_map('trim', explode(';', $sql)));
            return $this->executeMultiple($queries);
        } catch (Exception $e) {
            throw new Exception('Failed to import SQL file: ' . $e->getMessage());
        }
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