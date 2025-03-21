<?php
class Settings extends Model {
    protected $table = 'settings';
    protected $fillable = [
        'name',
        'value',
        'type'
    ];

    private $cache = [];

    /**
     * Get a setting value
     */
    public function get($name, $default = null) {
        if (isset($this->cache[$name])) {
            return $this->cache[$name];
        }

        $sql = "SELECT value, type FROM {$this->table} WHERE name = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$name]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            return $default;
        }

        $value = $this->castValue($result['value'], $result['type']);
        $this->cache[$name] = $value;

        return $value;
    }

    /**
     * Set a setting value
     */
    public function set($name, $value, $type = null) {
        if ($type === null) {
            $type = $this->determineType($value);
        }

        $value = $this->prepareValue($value);

        $sql = "INSERT INTO {$this->table} (name, value, type) 
                VALUES (?, ?, ?) 
                ON DUPLICATE KEY UPDATE value = ?, type = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$name, $value, $type, $value, $type]);

        $this->cache[$name] = $this->castValue($value, $type);

        return true;
    }

    /**
     * Get multiple settings at once
     */
    public function getMany(array $names, $default = null) {
        $placeholders = str_repeat('?,', count($names) - 1) . '?';
        $sql = "SELECT name, value, type FROM {$this->table} WHERE name IN ($placeholders)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($names);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $settings = [];
        foreach ($names as $name) {
            $settings[$name] = $default;
        }

        foreach ($results as $result) {
            $settings[$result['name']] = $this->castValue($result['value'], $result['type']);
            $this->cache[$result['name']] = $settings[$result['name']];
        }

        return $settings;
    }

    /**
     * Set multiple settings at once
     */
    public function setMany(array $settings) {
        try {
            $this->db->beginTransaction();

            foreach ($settings as $name => $value) {
                $this->set($name, $value);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Delete a setting
     */
    public function delete($name) {
        $sql = "DELETE FROM {$this->table} WHERE name = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$name]);

        unset($this->cache[$name]);

        return true;
    }

    /**
     * Get all settings
     */
    public function getAll() {
        $sql = "SELECT name, value, type FROM {$this->table}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $settings = [];
        foreach ($results as $result) {
            $value = $this->castValue($result['value'], $result['type']);
            $settings[$result['name']] = $value;
            $this->cache[$result['name']] = $value;
        }

        return $settings;
    }

    /**
     * Clear settings cache
     */
    public function clearCache() {
        $this->cache = [];
    }

    /**
     * Determine value type
     */
    private function determineType($value) {
        if (is_bool($value)) {
            return 'boolean';
        } elseif (is_int($value)) {
            return 'integer';
        } elseif (is_float($value)) {
            return 'float';
        } elseif (is_array($value)) {
            return 'array';
        } elseif (is_object($value)) {
            return 'object';
        } else {
            return 'string';
        }
    }

    /**
     * Prepare value for storage
     */
    private function prepareValue($value) {
        if (is_bool($value)) {
            return $value ? '1' : '0';
        } elseif (is_array($value) || is_object($value)) {
            return json_encode($value);
        } else {
            return (string)$value;
        }
    }

    /**
     * Cast value to its proper type
     */
    private function castValue($value, $type) {
        switch ($type) {
            case 'boolean':
                return (bool)$value;
            case 'integer':
                return (int)$value;
            case 'float':
                return (float)$value;
            case 'array':
            case 'object':
                return json_decode($value, $type === 'array');
            default:
                return $value;
        }
    }

    /**
     * Get settings by prefix
     */
    public function getByPrefix($prefix) {
        $sql = "SELECT name, value, type FROM {$this->table} WHERE name LIKE ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$prefix . '%']);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $settings = [];
        foreach ($results as $result) {
            $name = str_replace($prefix, '', $result['name']);
            $value = $this->castValue($result['value'], $result['type']);
            $settings[$name] = $value;
            $this->cache[$result['name']] = $value;
        }

        return $settings;
    }

    /**
     * Check if setting exists
     */
    public function has($name) {
        if (isset($this->cache[$name])) {
            return true;
        }

        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE name = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$name]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result['count'] > 0;
    }

    /**
     * Increment a numeric setting
     */
    public function increment($name, $amount = 1) {
        $sql = "UPDATE {$this->table} SET value = value + ? WHERE name = ? AND (type = 'integer' OR type = 'float')";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$amount, $name]);

        unset($this->cache[$name]);

        return $this->get($name);
    }

    /**
     * Decrement a numeric setting
     */
    public function decrement($name, $amount = 1) {
        return $this->increment($name, -$amount);
    }

    /**
     * Get settings for module
     */
    public function getModuleSettings($module) {
        return $this->getByPrefix("module_{$module}_");
    }

    /**
     * Set settings for module
     */
    public function setModuleSettings($module, array $settings) {
        $prefix = "module_{$module}_";
        $prefixedSettings = [];

        foreach ($settings as $key => $value) {
            $prefixedSettings[$prefix . $key] = $value;
        }

        return $this->setMany($prefixedSettings);
    }
}