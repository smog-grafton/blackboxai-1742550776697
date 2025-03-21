<?php
class Cache {
    private static $instance = null;
    private $driver;
    private $config;

    /**
     * Constructor
     */
    private function __construct() {
        $this->config = require __DIR__ . '/../config/config.php';
        $this->initializeDriver();
    }

    /**
     * Get Cache instance (Singleton)
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialize cache driver
     */
    private function initializeDriver() {
        $driver = $this->config['cache_driver'] ?? 'file';
        
        switch ($driver) {
            case 'redis':
                $this->driver = new RedisCacheDriver($this->config);
                break;
            case 'memcached':
                $this->driver = new MemcachedCacheDriver($this->config);
                break;
            case 'file':
            default:
                $this->driver = new FileCacheDriver($this->config);
                break;
        }
    }

    /**
     * Get item from cache
     */
    public function get($key, $default = null) {
        return $this->driver->get($key) ?? $default;
    }

    /**
     * Store item in cache
     */
    public function set($key, $value, $ttl = null) {
        return $this->driver->set($key, $value, $ttl);
    }

    /**
     * Remove item from cache
     */
    public function delete($key) {
        return $this->driver->delete($key);
    }

    /**
     * Clear entire cache
     */
    public function clear() {
        return $this->driver->clear();
    }

    /**
     * Check if item exists in cache
     */
    public function has($key) {
        return $this->driver->has($key);
    }

    /**
     * Get multiple items from cache
     */
    public function getMultiple($keys, $default = null) {
        return $this->driver->getMultiple($keys, $default);
    }

    /**
     * Store multiple items in cache
     */
    public function setMultiple($values, $ttl = null) {
        return $this->driver->setMultiple($values, $ttl);
    }

    /**
     * Remove multiple items from cache
     */
    public function deleteMultiple($keys) {
        return $this->driver->deleteMultiple($keys);
    }

    /**
     * Get or set cache item
     */
    public function remember($key, $ttl, $callback) {
        $value = $this->get($key);

        if ($value !== null) {
            return $value;
        }

        $value = $callback();
        $this->set($key, $value, $ttl);

        return $value;
    }

    /**
     * Get cache statistics
     */
    public function getStats() {
        return $this->driver->getStats();
    }
}

/**
 * File Cache Driver
 */
class FileCacheDriver {
    private $directory;
    private $extension = '.cache';

    public function __construct($config) {
        $this->directory = $config['cache_file_path'] ?? __DIR__ . '/../storage/cache';
        if (!file_exists($this->directory)) {
            mkdir($this->directory, 0777, true);
        }
    }

    public function get($key) {
        $path = $this->getPath($key);
        if (!file_exists($path)) {
            return null;
        }

        $data = unserialize(file_get_contents($path));
        if ($data === false) {
            return null;
        }

        if (isset($data['ttl']) && time() > $data['ttl']) {
            $this->delete($key);
            return null;
        }

        return $data['value'];
    }

    public function set($key, $value, $ttl = null) {
        $path = $this->getPath($key);
        $data = [
            'value' => $value,
            'ttl' => $ttl ? time() + $ttl : null
        ];

        return file_put_contents($path, serialize($data)) !== false;
    }

    public function delete($key) {
        $path = $this->getPath($key);
        if (file_exists($path)) {
            return unlink($path);
        }
        return true;
    }

    public function clear() {
        $files = glob($this->directory . '/*' . $this->extension);
        foreach ($files as $file) {
            unlink($file);
        }
        return true;
    }

    public function has($key) {
        return $this->get($key) !== null;
    }

    public function getMultiple($keys, $default = null) {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->get($key) ?? $default;
        }
        return $result;
    }

    public function setMultiple($values, $ttl = null) {
        $success = true;
        foreach ($values as $key => $value) {
            $success = $success && $this->set($key, $value, $ttl);
        }
        return $success;
    }

    public function deleteMultiple($keys) {
        $success = true;
        foreach ($keys as $key) {
            $success = $success && $this->delete($key);
        }
        return $success;
    }

    public function getStats() {
        $files = glob($this->directory . '/*' . $this->extension);
        $size = 0;
        foreach ($files as $file) {
            $size += filesize($file);
        }

        return [
            'driver' => 'file',
            'items' => count($files),
            'size' => $size,
            'directory' => $this->directory
        ];
    }

    private function getPath($key) {
        return $this->directory . '/' . md5($key) . $this->extension;
    }
}

/**
 * Redis Cache Driver
 */
class RedisCacheDriver {
    private $redis;

    public function __construct($config) {
        $this->redis = new Redis();
        $this->redis->connect(
            $config['redis_host'] ?? '127.0.0.1',
            $config['redis_port'] ?? 6379
        );

        if (isset($config['redis_password'])) {
            $this->redis->auth($config['redis_password']);
        }

        if (isset($config['redis_database'])) {
            $this->redis->select($config['redis_database']);
        }
    }

    public function get($key) {
        $value = $this->redis->get($key);
        return $value !== false ? unserialize($value) : null;
    }

    public function set($key, $value, $ttl = null) {
        if ($ttl) {
            return $this->redis->setex($key, $ttl, serialize($value));
        }
        return $this->redis->set($key, serialize($value));
    }

    public function delete($key) {
        return $this->redis->del($key) > 0;
    }

    public function clear() {
        return $this->redis->flushDB();
    }

    public function has($key) {
        return $this->redis->exists($key);
    }

    public function getMultiple($keys, $default = null) {
        $values = $this->redis->mGet($keys);
        $result = [];
        foreach ($keys as $i => $key) {
            $value = $values[$i];
            $result[$key] = $value !== false ? unserialize($value) : $default;
        }
        return $result;
    }

    public function setMultiple($values, $ttl = null) {
        if ($ttl) {
            $success = true;
            foreach ($values as $key => $value) {
                $success = $success && $this->set($key, $value, $ttl);
            }
            return $success;
        }

        $serialized = [];
        foreach ($values as $key => $value) {
            $serialized[$key] = serialize($value);
        }
        return $this->redis->mSet($serialized);
    }

    public function deleteMultiple($keys) {
        return $this->redis->del($keys) > 0;
    }

    public function getStats() {
        $info = $this->redis->info();
        return [
            'driver' => 'redis',
            'version' => $info['redis_version'],
            'used_memory' => $info['used_memory'],
            'connected_clients' => $info['connected_clients'],
            'last_save_time' => $info['last_save_time'],
            'total_connections_received' => $info['total_connections_received'],
            'total_commands_processed' => $info['total_commands_processed']
        ];
    }
}

/**
 * Memcached Cache Driver
 */
class MemcachedCacheDriver {
    private $memcached;

    public function __construct($config) {
        $this->memcached = new Memcached();
        $this->memcached->addServer(
            $config['memcached_host'] ?? '127.0.0.1',
            $config['memcached_port'] ?? 11211
        );
    }

    public function get($key) {
        $value = $this->memcached->get($key);
        return $this->memcached->getResultCode() === Memcached::RES_SUCCESS ? $value : null;
    }

    public function set($key, $value, $ttl = null) {
        return $this->memcached->set($key, $value, $ttl ?? 0);
    }

    public function delete($key) {
        return $this->memcached->delete($key);
    }

    public function clear() {
        return $this->memcached->flush();
    }

    public function has($key) {
        $this->memcached->get($key);
        return $this->memcached->getResultCode() === Memcached::RES_SUCCESS;
    }

    public function getMultiple($keys, $default = null) {
        $values = $this->memcached->getMulti($keys);
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = isset($values[$key]) ? $values[$key] : $default;
        }
        return $result;
    }

    public function setMultiple($values, $ttl = null) {
        return $this->memcached->setMulti($values, $ttl ?? 0);
    }

    public function deleteMultiple($keys) {
        return $this->memcached->deleteMulti($keys);
    }

    public function getStats() {
        $stats = $this->memcached->getStats();
        $server = key($stats);
        $stats = $stats[$server];

        return [
            'driver' => 'memcached',
            'version' => $stats['version'],
            'curr_items' => $stats['curr_items'],
            'bytes' => $stats['bytes'],
            'get_hits' => $stats['get_hits'],
            'get_misses' => $stats['get_misses'],
            'limit_maxbytes' => $stats['limit_maxbytes'],
            'uptime' => $stats['uptime']
        ];
    }
}