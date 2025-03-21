<?php
class Model {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    protected $hidden = [];

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Find a record by ID
     */
    public function find($id) {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get all records
     */
    public function all() {
        $sql = "SELECT * FROM {$this->table}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Create a new record
     */
    public function create(array $data) {
        $data = $this->filterFillable($data);
        $fields = array_keys($data);
        $placeholders = str_repeat('?,', count($fields) - 1) . '?';
        
        $sql = "INSERT INTO {$this->table} (" . implode(',', $fields) . ") VALUES ($placeholders)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_values($data));
        
        return $this->find($this->db->lastInsertId());
    }

    /**
     * Update a record
     */
    public function update($id, array $data) {
        $data = $this->filterFillable($data);
        $fields = array_keys($data);
        $set = implode('=?,', $fields) . '=?';
        
        $sql = "UPDATE {$this->table} SET $set WHERE {$this->primaryKey} = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([...array_values($data), $id]);
        
        return $this->find($id);
    }

    /**
     * Delete a record
     */
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    /**
     * Count total records
     */
    public function count() {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) $result['count'];
    }

    /**
     * Paginate records
     */
    public function paginate($page = 1, $perPage = 10, $conditions = []) {
        $offset = ($page - 1) * $perPage;
        
        // Build WHERE clause if conditions exist
        $where = '';
        $params = [];
        if (!empty($conditions)) {
            $whereClauses = [];
            foreach ($conditions as $field => $value) {
                $whereClauses[] = "$field = ?";
                $params[] = $value;
            }
            $where = 'WHERE ' . implode(' AND ', $whereClauses);
        }

        // Get total count for pagination
        $countSql = "SELECT COUNT(*) as count FROM {$this->table} $where";
        $stmt = $this->db->prepare($countSql);
        $stmt->execute($params);
        $total = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        // Get paginated data
        $sql = "SELECT * FROM {$this->table} $where LIMIT ? OFFSET ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([...$params, $perPage, $offset]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'data' => $data,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage)
        ];
    }

    /**
     * Filter data to only include fillable fields
     */
    protected function filterFillable(array $data) {
        if (empty($this->fillable)) {
            return $data;
        }
        return array_intersect_key($data, array_flip($this->fillable));
    }

    /**
     * Hide specified fields from array
     */
    protected function hideFields(array $data) {
        if (empty($this->hidden)) {
            return $data;
        }
        return array_diff_key($data, array_flip($this->hidden));
    }

    /**
     * Begin a transaction
     */
    public function beginTransaction() {
        return $this->db->beginTransaction();
    }

    /**
     * Commit a transaction
     */
    public function commit() {
        return $this->db->commit();
    }

    /**
     * Rollback a transaction
     */
    public function rollback() {
        return $this->db->rollBack();
    }

    /**
     * Execute a raw SQL query
     */
    protected function raw($sql, $params = []) {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Get records by a specific field value
     */
    public function getBy($field, $value) {
        $sql = "SELECT * FROM {$this->table} WHERE $field = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$value]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Find a single record by a specific field value
     */
    public function findBy($field, $value) {
        $sql = "SELECT * FROM {$this->table} WHERE $field = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$value]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Check if a record exists
     */
    public function exists($id) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) $result['count'] > 0;
    }

    /**
     * Get the latest records
     */
    public function getLatest($limit = 5) {
        $sql = "SELECT * FROM {$this->table} ORDER BY created_at DESC LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get records with custom ordering
     */
    public function getOrdered($orderBy = 'id', $direction = 'ASC') {
        $sql = "SELECT * FROM {$this->table} ORDER BY $orderBy $direction";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Search records by a field
     */
    public function search($field, $query) {
        $sql = "SELECT * FROM {$this->table} WHERE $field LIKE ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(["%$query%"]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}