<?php
class Logger {
    private static $instance = null;
    private $logPath;
    private $logLevel;

    // Log levels
    const EMERGENCY = 'emergency';
    const ALERT     = 'alert';
    const CRITICAL  = 'critical';
    const ERROR     = 'error';
    const WARNING   = 'warning';
    const NOTICE    = 'notice';
    const INFO      = 'info';
    const DEBUG     = 'debug';

    private $logLevels = [
        self::EMERGENCY => 0,
        self::ALERT     => 1,
        self::CRITICAL  => 2,
        self::ERROR     => 3,
        self::WARNING   => 4,
        self::NOTICE    => 5,
        self::INFO      => 6,
        self::DEBUG     => 7
    ];

    private function __construct() {
        $this->logPath = __DIR__ . '/../logs/';
        $this->logLevel = self::DEBUG; // Default to most verbose logging

        // Create logs directory if it doesn't exist
        if (!is_dir($this->logPath)) {
            mkdir($this->logPath, 0755, true);
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Set the minimum log level
     */
    public function setLogLevel($level) {
        if (!isset($this->logLevels[$level])) {
            throw new Exception("Invalid log level");
        }
        $this->logLevel = $level;
    }

    /**
     * Log a message
     */
    public function log($level, $message, array $context = []) {
        if (!isset($this->logLevels[$level])) {
            throw new Exception("Invalid log level");
        }

        // Check if this log level should be recorded
        if ($this->logLevels[$level] > $this->logLevels[$this->logLevel]) {
            return false;
        }

        // Format the log entry
        $logEntry = $this->formatLogEntry($level, $message, $context);

        // Write to appropriate log file
        $logFile = $this->logPath . date('Y-m-d') . '.log';
        
        return file_put_contents(
            $logFile,
            $logEntry . PHP_EOL,
            FILE_APPEND | LOCK_EX
        );
    }

    /**
     * Format a log entry
     */
    private function formatLogEntry($level, $message, array $context = []) {
        $timestamp = date('Y-m-d H:i:s');
        $message = $this->interpolate($message, $context);

        return sprintf(
            '[%s] %s: %s',
            $timestamp,
            strtoupper($level),
            $message
        );
    }

    /**
     * Interpolate context values into message placeholders
     */
    private function interpolate($message, array $context = []) {
        $replace = [];
        foreach ($context as $key => $val) {
            if (is_string($val) || method_exists($val, '__toString')) {
                $replace['{' . $key . '}'] = $val;
            }
        }

        return strtr($message, $replace);
    }

    /**
     * Emergency log
     */
    public function emergency($message, array $context = []) {
        $this->log(self::EMERGENCY, $message, $context);
    }

    /**
     * Alert log
     */
    public function alert($message, array $context = []) {
        $this->log(self::ALERT, $message, $context);
    }

    /**
     * Critical log
     */
    public function critical($message, array $context = []) {
        $this->log(self::CRITICAL, $message, $context);
    }

    /**
     * Error log
     */
    public function error($message, array $context = []) {
        $this->log(self::ERROR, $message, $context);
    }

    /**
     * Warning log
     */
    public function warning($message, array $context = []) {
        $this->log(self::WARNING, $message, $context);
    }

    /**
     * Notice log
     */
    public function notice($message, array $context = []) {
        $this->log(self::NOTICE, $message, $context);
    }

    /**
     * Info log
     */
    public function info($message, array $context = []) {
        $this->log(self::INFO, $message, $context);
    }

    /**
     * Debug log
     */
    public function debug($message, array $context = []) {
        $this->log(self::DEBUG, $message, $context);
    }

    /**
     * Get logs by date
     */
    public function getLogsByDate($date) {
        $logFile = $this->logPath . $date . '.log';
        if (!file_exists($logFile)) {
            return [];
        }

        $logs = [];
        $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            if (preg_match('/\[(.*?)\] (\w+): (.*)/', $line, $matches)) {
                $logs[] = [
                    'timestamp' => $matches[1],
                    'level' => strtolower($matches[2]),
                    'message' => $matches[3]
                ];
            }
        }

        return $logs;
    }

    /**
     * Get logs by level
     */
    public function getLogsByLevel($level, $date = null) {
        if (!isset($this->logLevels[$level])) {
            throw new Exception("Invalid log level");
        }

        $date = $date ?? date('Y-m-d');
        $logs = $this->getLogsByDate($date);
        
        return array_filter($logs, function($log) use ($level) {
            return $log['level'] === strtolower($level);
        });
    }

    /**
     * Clear old logs
     */
    public function clearOldLogs($daysToKeep = 30) {
        $files = glob($this->logPath . '*.log');
        $now = time();

        foreach ($files as $file) {
            if (is_file($file)) {
                if ($now - filemtime($file) >= 60 * 60 * 24 * $daysToKeep) {
                    unlink($file);
                }
            }
        }
    }

    /**
     * Get available log dates
     */
    public function getAvailableLogDates() {
        $files = glob($this->logPath . '*.log');
        $dates = [];

        foreach ($files as $file) {
            $dates[] = basename($file, '.log');
        }

        sort($dates);
        return $dates;
    }

    /**
     * Search logs
     */
    public function searchLogs($searchTerm, $date = null, $level = null) {
        $date = $date ?? date('Y-m-d');
        $logs = $this->getLogsByDate($date);

        return array_filter($logs, function($log) use ($searchTerm, $level) {
            $matchesLevel = $level ? $log['level'] === strtolower($level) : true;
            $matchesTerm = stripos($log['message'], $searchTerm) !== false;
            return $matchesLevel && $matchesTerm;
        });
    }

    /**
     * Get log statistics
     */
    public function getStatistics($date = null) {
        $date = $date ?? date('Y-m-d');
        $logs = $this->getLogsByDate($date);

        $stats = [
            'total' => count($logs),
            'by_level' => []
        ];

        foreach ($this->logLevels as $level => $priority) {
            $stats['by_level'][$level] = count(array_filter($logs, function($log) use ($level) {
                return $log['level'] === $level;
            }));
        }

        return $stats;
    }
}