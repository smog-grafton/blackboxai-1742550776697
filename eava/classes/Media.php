<?php
require_once __DIR__ . '/Model.php';

class Media extends Model {
    protected $table = 'media';
    protected $fillable = [
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'uploaded_by'
    ];

    // Allowed file types and their MIME types
    private $allowedTypes = [
        'image' => ['image/jpeg', 'image/png', 'image/gif'],
        'video' => ['video/mp4', 'video/quicktime'],
        'document' => [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ]
    ];

    /**
     * Upload a new media file
     */
    public function upload($file, $uploadedBy) {
        try {
            // Validate file
            $this->validateFile($file);

            // Generate unique filename
            $fileName = $this->generateUniqueFilename($file['name']);
            
            // Determine upload directory based on file type
            $uploadDir = $this->getUploadDirectory($file['type']);
            
            // Create full file path
            $filePath = $uploadDir . $fileName;

            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $filePath)) {
                throw new Exception("Failed to move uploaded file");
            }

            // Create media record
            $data = [
                'file_name' => $fileName,
                'file_path' => str_replace($_SERVER['DOCUMENT_ROOT'], '', $filePath),
                'file_type' => $file['type'],
                'file_size' => $file['size'],
                'uploaded_by' => $uploadedBy
            ];

            return $this->create($data);
        } catch (Exception $e) {
            error_log("Media Upload Error: " . $e->getMessage());
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Validate uploaded file
     */
    private function validateFile($file) {
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("File upload failed with error code: " . $file['error']);
        }

        // Check file type
        $allowedMimeTypes = array_merge(...array_values($this->allowedTypes));
        if (!in_array($file['type'], $allowedMimeTypes)) {
            throw new Exception("File type not allowed");
        }

        // Check file size (max 10MB)
        $maxSize = 10 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            throw new Exception("File size exceeds limit");
        }

        return true;
    }

    /**
     * Generate unique filename
     */
    private function generateUniqueFilename($originalName) {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        return uniqid() . '_' . time() . '.' . $extension;
    }

    /**
     * Get appropriate upload directory based on file type
     */
    private function getUploadDirectory($mimeType) {
        $baseDir = __DIR__ . '/../uploads/media/';
        
        foreach ($this->allowedTypes as $type => $mimeTypes) {
            if (in_array($mimeType, $mimeTypes)) {
                $dir = $baseDir . $type . '/';
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }
                return $dir;
            }
        }
        
        throw new Exception("Invalid file type");
    }

    /**
     * Delete media file
     */
    public function deleteMedia($id) {
        try {
            $media = $this->find($id);
            if (!$media) {
                throw new Exception("Media not found");
            }

            // Delete physical file
            $filePath = $_SERVER['DOCUMENT_ROOT'] . $media['file_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            // Delete database record
            return $this->delete($id);
        } catch (Exception $e) {
            error_log("Media Delete Error: " . $e->getMessage());
            throw new Exception("Failed to delete media");
        }
    }

    /**
     * Get media by type
     */
    public function getByType($type, $page = 1, $perPage = 20) {
        try {
            $mimeTypes = $this->allowedTypes[$type] ?? [];
            if (empty($mimeTypes)) {
                throw new Exception("Invalid media type");
            }

            $placeholders = str_repeat('?,', count($mimeTypes) - 1) . '?';
            $sql = "SELECT m.*, u.username as uploader_name 
                    FROM {$this->table} m
                    LEFT JOIN users u ON m.uploaded_by = u.id
                    WHERE m.file_type IN ($placeholders)
                    ORDER BY m.created_at DESC
                    LIMIT ? OFFSET ?";

            $params = array_merge($mimeTypes, [$perPage, ($page - 1) * $perPage]);
            
            $this->db->query($sql, $params);
            return $this->db->findAll();
        } catch (Exception $e) {
            error_log("Get Media By Type Error: " . $e->getMessage());
            throw new Exception("Failed to get media by type");
        }
    }

    /**
     * Get media statistics
     */
    public function getStatistics() {
        try {
            $stats = [];

            // Get counts by type
            foreach ($this->allowedTypes as $type => $mimeTypes) {
                $placeholders = str_repeat('?,', count($mimeTypes) - 1) . '?';
                $sql = "SELECT COUNT(*) as count, SUM(file_size) as total_size 
                        FROM {$this->table} 
                        WHERE file_type IN ($placeholders)";
                
                $this->db->query($sql, $mimeTypes);
                $result = $this->db->findOne();
                
                $stats[$type] = [
                    'count' => $result['count'] ?? 0,
                    'total_size' => $result['total_size'] ?? 0
                ];
            }

            // Get total statistics
            $stats['total'] = [
                'count' => array_sum(array_column($stats, 'count')),
                'total_size' => array_sum(array_column($stats, 'total_size'))
            ];

            return $stats;
        } catch (Exception $e) {
            error_log("Get Media Statistics Error: " . $e->getMessage());
            throw new Exception("Failed to get media statistics");
        }
    }

    /**
     * Search media
     */
    public function searchMedia($searchTerm, $type = null, $page = 1, $perPage = 20) {
        try {
            $conditions = ["file_name LIKE ?"];
            $params = ["%{$searchTerm}%"];

            if ($type && isset($this->allowedTypes[$type])) {
                $mimeTypes = $this->allowedTypes[$type];
                $placeholders = str_repeat('?,', count($mimeTypes) - 1) . '?';
                $conditions[] = "file_type IN ($placeholders)";
                $params = array_merge($params, $mimeTypes);
            }

            $where = "WHERE " . implode(" AND ", $conditions);
            
            $sql = "SELECT m.*, u.username as uploader_name 
                    FROM {$this->table} m
                    LEFT JOIN users u ON m.uploaded_by = u.id
                    {$where}
                    ORDER BY m.created_at DESC
                    LIMIT ? OFFSET ?";

            $params[] = $perPage;
            $params[] = ($page - 1) * $perPage;
            
            $this->db->query($sql, $params);
            return $this->db->findAll();
        } catch (Exception $e) {
            error_log("Search Media Error: " . $e->getMessage());
            throw new Exception("Failed to search media");
        }
    }

    /**
     * Get recent uploads
     */
    public function getRecent($limit = 10) {
        try {
            $sql = "SELECT m.*, u.username as uploader_name 
                    FROM {$this->table} m
                    LEFT JOIN users u ON m.uploaded_by = u.id
                    ORDER BY m.created_at DESC
                    LIMIT ?";
            
            $this->db->query($sql, [$limit]);
            return $this->db->findAll();
        } catch (Exception $e) {
            error_log("Get Recent Media Error: " . $e->getMessage());
            throw new Exception("Failed to get recent media");
        }
    }

    /**
     * Get media by user
     */
    public function getByUser($userId, $page = 1, $perPage = 20) {
        try {
            return $this->paginate($page, $perPage, [
                'uploaded_by' => $userId
            ], 'created_at', 'DESC');
        } catch (Exception $e) {
            error_log("Get Media By User Error: " . $e->getMessage());
            throw new Exception("Failed to get media by user");
        }
    }

    /**
     * Check if file type is allowed
     */
    public function isAllowedType($mimeType) {
        $allowedMimeTypes = array_merge(...array_values($this->allowedTypes));
        return in_array($mimeType, $allowedMimeTypes);
    }

    /**
     * Get allowed file types
     */
    public function getAllowedTypes() {
        return $this->allowedTypes;
    }
}