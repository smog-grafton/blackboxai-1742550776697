<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Utility.php';

abstract class Model {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    protected $timestamps = true;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Find a record by ID
     */
    public function find($id) {
        try {
            $this->db->query("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?", [$id]);
            return $this->db->findOne();
        } catch (Exception $e) {
            error_log("Find Error: " . $e->getMessage());
            throw new Exception("Failed to find record");
        }
    }

    /**
     * Get all records
     */
    public function all($orderBy = null, $order = 'ASC') {
        try {
            $sql = "SELECT * FROM {$this->table}";
            if ($orderBy) {
                $sql .= " ORDER BY {$orderBy} {$order}";
            }
            $this->db->query($sql);
            return $this->db->findAll();
        } catch (Exception $e) {
            error_log("Get All Error: " . $e->getMessage());
            throw new Exception("Failed to get records");
        }
    }

    /**
     * Create a new record
     */
    public function create($data) {
        try {
            $data = array_intersect_key($data, array_flip($this->fillable));
            
            if ($this->timestamps) {
                $data['created_at'] = date('Y-m-d H:i:s');
                $data['updated_at'] = date('Y-m-d H:i:s');
            }

            $columns = implode(', ', array_keys($data));
            $values = implode(', ', array_fill(0, count($data), '?'));
            
            $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$values})";
            
            $this->db->query($sql, array_values($data));
            return $this->db->lastInsertId();
        } catch (Exception $e) {
            error_log("Create Error: " . $e->getMessage());
            throw new Exception("Failed to create record");
        }
    }

    /**
     * Update a record
     */
    public function update($id, $data) {
        try {
            $data = array_intersect_key($data, array_flip($this->fillable));
            
            if ($this->timestamps) {
                $data['updated_at'] = date('Y-m-d H:i:s');
            }

            $setStatements = [];
            foreach ($data as $key => $value) {
                $setStatements[] = "{$key} = ?";
            }
            
            $sql = "UPDATE {$this->table} SET " . implode(', ', $setStatements) . 
                   " WHERE {$this->primaryKey} = ?";
            
            $values = array_values($data);
            $values[] = $id;
            
            $this->db->query($sql, $values);
            return true;
        } catch (Exception $e) {
            error_log("Update Error: " . $e->getMessage());
            throw new Exception("Failed to update record");
        }
    }

    /**
     * Delete a record
     */
    public function delete($id) {
        try {
            $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
            $this->db->query($sql, [$id]);
            return true;
        } catch (Exception $e) {
            error_log("Delete Error: " . $e->getMessage());
            throw new Exception("Failed to delete record");
        }
    }

    /**
     * Find records by field value
     */
    public function findBy($field, $value) {
        try {
            $this->db->query("SELECT * FROM {$this->table} WHERE {$field} = ?", [$value]);
            return $this->db->findAll();
        } catch (Exception $e) {
            error_log("FindBy Error: " . $e->getMessage());
            throw new Exception("Failed to find records");
        }
    }

    /**
     * Find one record by field value
     */
    public function findOneBy($field, $value) {
        try {
            $this->db->query("SELECT * FROM {$this->table} WHERE {$field} = ?", [$value]);
            return $this->db->findOne();
        } catch (Exception $e) {
            error_log("FindOneBy Error: " . $e->getMessage());
            throw new Exception("Failed to find record");
        }
    }

    /**
     * Get paginated results
     */
    public function paginate($page = 1, $perPage = 10, $conditions = [], $orderBy = null, $order = 'ASC') {
        try {
            // Build WHERE clause
            $where = '';
            $params = [];
            if (!empty($conditions)) {
                $whereClauses = [];
                foreach ($conditions as $field => $value) {
                    $whereClauses[] = "{$field} = ?";
                    $params[] = $value;
                }
                $where = "WHERE " . implode(' AND ', $whereClauses);
            }

            // Get total count
            $countSql = "SELECT COUNT(*) as total FROM {$this->table} {$where}";
            $this->db->query($countSql, $params);
            $total = $this->db->findOne()['total'];

            // Calculate offset
            $offset = ($page - 1) * $perPage;

            // Build query
            $sql = "SELECT * FROM {$this->table} {$where}";
            if ($orderBy) {
                $sql .= " ORDER BY {$orderBy} {$order}";
            }
            $sql .= " LIMIT ? OFFSET ?";

            // Add pagination parameters
            $params[] = $perPage;
            $params[] = $offset;

            // Get results
            $this->db->query($sql, $params);
            $results = $this->db->findAll();

            return [
                'data' => $results,
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => ceil($total / $perPage)
            ];
        } catch (Exception $e) {
            error_log("Pagination Error: " . $e->getMessage());
            throw new Exception("Failed to get paginated results");
        }
    }

    /**
     * Search records
     */
    public function search($searchFields, $searchTerm, $page = 1, $perPage = 10) {
        try {
            $whereClauses = [];
            $params = [];
            
            foreach ($searchFields as $field) {
                $whereClauses[] = "{$field} LIKE ?";
                $params[] = "%{$searchTerm}%";
            }
            
            $where = "WHERE " . implode(' OR ', $whereClauses);
            
            // Get total count
            $countSql = "SELECT COUNT(*) as total FROM {$this->table} {$where}";
            $this->db->query($countSql, $params);
            $total = $this->db->findOne()['total'];
            
            // Calculate offset
            $offset = ($page - 1) * $perPage;
            
            // Get results
            $sql = "SELECT * FROM {$this->table} {$where} LIMIT ? OFFSET ?";
            $params[] = $perPage;
            $params[] = $offset;
            
            $this->db->query($sql, $params);
            $results = $this->db->findAll();
            
            return [
                'data' => $results,
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => ceil($total / $perPage)
            ];
        } catch (Exception $e) {
            error_log("Search Error: " . $e->getMessage());
            throw new Exception("Failed to search records");
        }
    }

    /**
     * Count records
     */
    public function count($conditions = []) {
        try {
            $where = '';
            $params = [];
            
            if (!empty($conditions)) {
                $whereClauses = [];
                foreach ($conditions as $field => $value) {
                    $whereClauses[] = "{$field} = ?";
                    $params[] = $value;
                }
                $where = "WHERE " . implode(' AND ', $whereClauses);
            }
            
            $sql = "SELECT COUNT(*) as total FROM {$this->table} {$where}";
            $this->db->query($sql, $params);
            return $this->db->findOne()['total'];
        } catch (Exception $e) {
            error_log("Count Error: " . $e->getMessage());
            throw new Exception("Failed to count records");
        }
    }

    /**
     * Check if a record exists
     */
    public function exists($conditions) {
        try {
            $whereClauses = [];
            $params = [];
            
            foreach ($conditions as $field => $value) {
                $whereClauses[] = "{$field} = ?";
                $params[] = $value;
            }
            
            $where = "WHERE " . implode(' AND ', $whereClauses);
            $sql = "SELECT COUNT(*) as count FROM {$this->table} {$where}";
            
            $this->db->query($sql, $params);
            return $this->db->findOne()['count'] > 0;
        } catch (Exception $e) {
            error_log("Exists Error: " . $e->getMessage());
            throw new Exception("Failed to check record existence");
        }
    }

    /**
     * Begin a database transaction
     */
    public function beginTransaction() {
        return $this->db->beginTransaction();
    }

    /**
     * Commit a database transaction
     */
    public function commit() {
        return $this->db->commit();
    }

    /**
     * Rollback a database transaction
     */
    public function rollBack() {
        return $this->db->rollBack();
    }
}