<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/Database.php';

class Settings {
    private $db;
    private static $instance = null;
    private $settings = [];
    private $modified = false;

    private function __construct() {
        $this->db = Database::getInstance();
        $this->loadSettings();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function loadSettings() {
        try {
            $this->db->query("SELECT setting_key, setting_value, setting_group FROM settings");
            $results = $this->db->findAll();
            
            foreach ($results as $row) {
                $this->settings[$row['setting_group']][$row['setting_key']] = $row['setting_value'];
            }
        } catch (Exception $e) {
            error_log("Settings Load Error: " . $e->getMessage());
            throw new Exception("Failed to load settings");
        }
    }

    public function get($key, $group = 'general', $default = null) {
        return $this->settings[$group][$key] ?? $default;
    }

    public function getGroup($group) {
        return $this->settings[$group] ?? [];
    }

    public function set($key, $value, $group = 'general', $isPublic = true) {
        try {
            // Check if setting exists
            $this->db->query(
                "SELECT id FROM settings WHERE setting_key = ? AND setting_group = ?",
                [$key, $group]
            );
            $existing = $this->db->findOne();

            if ($existing) {
                // Update existing setting
                $this->db->query(
                    "UPDATE settings SET setting_value = ?, is_public = ? WHERE setting_key = ? AND setting_group = ?",
                    [$value, $isPublic, $key, $group]
                );
            } else {
                // Insert new setting
                $this->db->query(
                    "INSERT INTO settings (setting_key, setting_value, setting_group, is_public) VALUES (?, ?, ?, ?)",
                    [$key, $value, $group, $isPublic]
                );
            }

            // Update local cache
            $this->settings[$group][$key] = $value;
            $this->modified = true;

            return true;
        } catch (Exception $e) {
            error_log("Settings Update Error: " . $e->getMessage());
            throw new Exception("Failed to update setting");
        }
    }

    public function setMultiple($settings) {
        try {
            $this->db->beginTransaction();

            foreach ($settings as $group => $groupSettings) {
                foreach ($groupSettings as $key => $value) {
                    $this->set($key, $value, $group);
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Multiple Settings Update Error: " . $e->getMessage());
            throw new Exception("Failed to update multiple settings");
        }
    }

    public function delete($key, $group = 'general') {
        try {
            $this->db->query(
                "DELETE FROM settings WHERE setting_key = ? AND setting_group = ?",
                [$key, $group]
            );

            unset($this->settings[$group][$key]);
            $this->modified = true;

            return true;
        } catch (Exception $e) {
            error_log("Setting Delete Error: " . $e->getMessage());
            throw new Exception("Failed to delete setting");
        }
    }

    public function getAllGroups() {
        return array_keys($this->settings);
    }

    public function getPublicSettings() {
        try {
            $this->db->query("SELECT setting_key, setting_value, setting_group FROM settings WHERE is_public = 1");
            $results = $this->db->findAll();
            
            $publicSettings = [];
            foreach ($results as $row) {
                $publicSettings[$row['setting_group']][$row['setting_key']] = $row['setting_value'];
            }
            
            return $publicSettings;
        } catch (Exception $e) {
            error_log("Public Settings Load Error: " . $e->getMessage());
            throw new Exception("Failed to load public settings");
        }
    }

    public function getSocialMediaSettings() {
        return $this->getGroup('social_media') ?? [];
    }

    public function getPaymentSettings() {
        return $this->getGroup('payment') ?? [];
    }

    public function getThemeSettings() {
        return $this->getGroup('theme') ?? [];
    }

    public function updateSocialMediaSettings($settings) {
        return $this->setMultiple(['social_media' => $settings]);
    }

    public function updatePaymentSettings($settings) {
        return $this->setMultiple(['payment' => $settings]);
    }

    public function updateThemeSettings($settings) {
        return $this->setMultiple(['theme' => $settings]);
    }

    public function clearCache() {
        $this->settings = [];
        $this->loadSettings();
    }

    public function isModified() {
        return $this->modified;
    }

    public function validateSettingValue($key, $value, $group = 'general') {
        // Add validation rules based on setting type
        switch ($group) {
            case 'theme':
                if (strpos($key, 'color') !== false) {
                    return preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $value);
                }
                break;
            case 'social_media':
                if (strpos($key, 'url') !== false) {
                    return filter_var($value, FILTER_VALIDATE_URL);
                }
                break;
            case 'payment':
                if (strpos($key, 'key') !== false) {
                    return !empty($value) && strlen($value) >= 16;
                }
                break;
        }
        return true;
    }
}