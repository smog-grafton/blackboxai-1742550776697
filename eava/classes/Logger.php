<?php
class Logger {
    private static $instance = null;
    private $config;
    private $logFile;
    private $logPath;

    private function __construct() {
        $this->config = require __DIR__ . '/../config/config.php';
        $this->setupLogger();
    }

    /**
     * Get Logger instance (Singleton)
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Setup logger configuration
     */
    private function setupLogger() {
        $this->logPath = __DIR__ . '/../logs';
        
        // Create logs directory if it doesn't exist
        if (!file_exists($this->logPath)) {
            mkdir($this->logPath, 0755, true);
        }

        // Set log file based on channel configuration
        switch ($this->config['log_channel']) {
            case 'daily':
                $this->logFile = $this->logPath . '/eava-' . date('Y-m-d') . '.log';
                break;
            case 'single':
                $this->logFile = $this->logPath . '/eava.log';
                break;
            default:
                $this->logFile = $this->logPath . '/eava.log';
        }
    }

    /**
     * Write log entry
     */
    private function write($level, $message, array $context = []) {
        // Check if logging is enabled for this level
        if (!$this->isLevelEnabled($level)) {
            return false;
        }

        $timestamp = date('Y-m-d H:i:s');
        $contextStr = empty($context) ? '' : json_encode($context);
        
        $logEntry = sprintf(
            "[%s] %s: %s %s\n",
            $timestamp,
            strtoupper($level),
            $message,
            $contextStr
        );

        // Rotate log files if needed
        $this->rotateLogFiles();

        // Write to log file
        return file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }

    /**
     * Check if logging level is enabled
     */
    private function isLevelEnabled($level) {
        $levels = [
            'emergency' => 800,
            'alert'     => 700,
            'critical'  => 600,
            'error'     => 500,
            'warning'   => 400,
            'notice'    => 300,
            'info'      => 200,
            'debug'     => 100
        ];

        $configLevel = $this->config['log_level'];
        return $levels[$level] >= $levels[$configLevel];
    }

    /**
     * Rotate log files
     */
    private function rotateLogFiles() {
        if ($this->config['log_channel'] === 'daily') {
            $files = glob($this->logPath . '/eava-*.log');
            $maxFiles = $this->config['log_max_files'];

            if (count($files) > $maxFiles) {
                usort($files, function($a, $b) {
                    return filemtime($a) - filemtime($b);
                });

                $filesToDelete = array_slice($files, 0, count($files) - $maxFiles);
                foreach ($filesToDelete as $file) {
                    unlink($file);
                }
            }
        }
    }

    /**
     * Log debug message
     */
    public function debug($message, array $context = []) {
        return $this->write('debug', $message, $context);
    }

    /**
     * Log info message
     */
    public function info($message, array $context = []) {
        return $this->write('info', $message, $context);
    }

    /**
     * Log notice message
     */
    public function notice($message, array $context = []) {
        return $this->write('notice', $message, $context);
    }

    /**
     * Log warning message
     */
    public function warning($message, array $context = []) {
        return $this->write('warning', $message, $context);
    }

    /**
     * Log error message
     */
    public function error($message, array $context = []) {
        return $this->write('error', $message, $context);
    }

    /**
     * Log critical message
     */
    public function critical($message, array $context = []) {
        return $this->write('critical', $message, $context);
    }

    /**
     * Log alert message
     */
    public function alert($message, array $context = []) {
        return $this->write('alert', $message, $context);
    }

    /**
     * Log emergency message
     */
    public function emergency($message, array $context = []) {
        return $this->write('emergency', $message, $context);
    }

    /**
     * Get all logs for a specific date
     */
    public function getLogs($date = null) {
        if ($date === null) {
            $date = date('Y-m-d');
        }

        $logFile = $this->logPath . '/eava-' . $date . '.log';
        if (!file_exists($logFile)) {
            return [];
        }

        $logs = [];
        $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            if (preg_match('/\[(.*?)\] (\w+): (.*?) ({.*})?/', $line, $matches)) {
                $logs[] = [
                    'timestamp' => $matches[1],
                    'level' => strtolower($matches[2]),
                    'message' => $matches[3],
                    'context' => isset($matches[4]) ? json_decode($matches[4], true) : []
                ];
            }
        }

        return $logs;
    }

    /**
     * Clear logs
     */
    public function clear() {
        $files = glob($this->logPath . '/eava-*.log');
        foreach ($files as $file) {
            unlink($file);
        }
    }

    /**
     * Get log file size
     */
    public function getLogSize($date = null) {
        if ($date === null) {
            $date = date('Y-m-d');
        }

        $logFile = $this->logPath . '/eava-' . $date . '.log';
        if (!file_exists($logFile)) {
            return 0;
        }

        return filesize($logFile);
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