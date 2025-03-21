<?php
class Media extends Model {
    protected $table = 'media';
    protected $fillable = [
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'uploaded_by'
    ];

    private $config;
    private $allowedTypes;
    private $maxFileSize;
    private $uploadPath;

    public function __construct() {
        parent::__construct();
        $this->config = require __DIR__ . '/../config/config.php';
        $this->allowedTypes = $this->config['allowed_file_types'];
        $this->maxFileSize = $this->config['upload_max_size'];
        $this->uploadPath = $this->config['media_library_path'];
    }

    /**
     * Upload a file
     */
    public function upload($file, $userId) {
        // Validate file
        $this->validateFile($file);

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '_' . time() . '.' . $extension;
        $relativePath = date('Y/m/') . $filename;
        $fullPath = $this->uploadPath . '/' . $relativePath;

        // Create directory if it doesn't exist
        $directory = dirname($fullPath);
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
            throw new Exception('Failed to move uploaded file');
        }

        // Create media record
        $data = [
            'file_name' => $file['name'],
            'file_path' => '/media/' . $relativePath,
            'file_type' => $this->getFileType($extension),
            'file_size' => $file['size'],
            'uploaded_by' => $userId
        ];

        return $this->create($data);
    }

    /**
     * Validate uploaded file
     */
    private function validateFile($file) {
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('File upload failed with error code: ' . $file['error']);
        }

        // Check file size
        if ($file['size'] > $this->maxFileSize) {
            throw new Exception('File size exceeds maximum allowed size');
        }

        // Check file type
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $isAllowed = false;
        foreach ($this->allowedTypes as $type => $extensions) {
            if (in_array($extension, $extensions)) {
                $isAllowed = true;
                break;
            }
        }

        if (!$isAllowed) {
            throw new Exception('File type not allowed');
        }
    }

    /**
     * Get file type based on extension
     */
    private function getFileType($extension) {
        foreach ($this->allowedTypes as $type => $extensions) {
            if (in_array(strtolower($extension), $extensions)) {
                return $type;
            }
        }
        return 'other';
    }

    /**
     * Get files by type
     */
    public function getByType($type, $page = 1, $perPage = 20) {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT m.*, u.username as uploader_name 
                FROM {$this->table} m 
                LEFT JOIN users u ON m.uploaded_by = u.id 
                WHERE m.file_type = ? 
                ORDER BY m.created_at DESC 
                LIMIT ? OFFSET ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$type, $perPage, $offset]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get total count for pagination
        $countSql = "SELECT COUNT(*) as count FROM {$this->table} WHERE file_type = ?";
        $stmt = $this->db->prepare($countSql);
        $stmt->execute([$type]);
        $total = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        return [
            'data' => $data,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage)
        ];
    }

    /**
     * Delete file
     */
    public function delete($id) {
        $media = $this->find($id);
        if (!$media) {
            return false;
        }

        // Delete physical file
        $fullPath = $this->uploadPath . str_replace('/media/', '/', $media['file_path']);
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }

        // Delete database record
        return parent::delete($id);
    }

    /**
     * Get media statistics
     */
    public function getStatistics() {
        $stats = [
            'total_files' => 0,
            'total_size' => 0,
            'by_type' => []
        ];

        // Get counts and sizes by type
        $sql = "SELECT file_type, COUNT(*) as count, SUM(file_size) as total_size 
                FROM {$this->table} 
                GROUP BY file_type";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($results as $result) {
            $stats['by_type'][$result['file_type']] = [
                'count' => $result['count'],
                'total_size' => $result['total_size']
            ];
            $stats['total_files'] += $result['count'];
            $stats['total_size'] += $result['total_size'];
        }

        return $stats;
    }

    /**
     * Search media files
     */
    public function search($query) {
        $sql = "SELECT m.*, u.username as uploader_name 
                FROM {$this->table} m 
                LEFT JOIN users u ON m.uploaded_by = u.id 
                WHERE m.file_name LIKE ? 
                ORDER BY m.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['%' . $query . '%']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get recent uploads
     */
    public function getRecent($limit = 10) {
        $sql = "SELECT m.*, u.username as uploader_name 
                FROM {$this->table} m 
                LEFT JOIN users u ON m.uploaded_by = u.id 
                ORDER BY m.created_at DESC 
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get user uploads
     */
    public function getUserUploads($userId, $page = 1, $perPage = 20) {
        return $this->paginate($page, $perPage, ['uploaded_by' => $userId]);
    }

    /**
     * Create image thumbnails
     */
    public function createThumbnails($mediaId) {
        $media = $this->find($mediaId);
        if (!$media || $media['file_type'] !== 'image') {
            return false;
        }

        $sourcePath = $this->uploadPath . str_replace('/media/', '/', $media['file_path']);
        $sourceInfo = pathinfo($sourcePath);
        $thumbnailSizes = $this->config['image_thumbnail_sizes'];

        foreach ($thumbnailSizes as $size => list($width, $height)) {
            $thumbnailPath = $sourceInfo['dirname'] . '/' . 
                            $sourceInfo['filename'] . '_' . $size . '.' . 
                            $sourceInfo['extension'];

            $this->createThumbnail($sourcePath, $thumbnailPath, $width, $height);
        }

        return true;
    }

    /**
     * Create single thumbnail
     */
    private function createThumbnail($source, $destination, $width, $height) {
        list($sourceWidth, $sourceHeight) = getimagesize($source);
        
        $ratio = min($width / $sourceWidth, $height / $sourceHeight);
        $newWidth = round($sourceWidth * $ratio);
        $newHeight = round($sourceHeight * $ratio);

        $sourceImage = imagecreatefromstring(file_get_contents($source));
        $destinationImage = imagecreatetruecolor($newWidth, $newHeight);

        imagecopyresampled(
            $destinationImage, $sourceImage,
            0, 0, 0, 0,
            $newWidth, $newHeight, $sourceWidth, $sourceHeight
        );

        imagejpeg($destinationImage, $destination, 90);
        
        imagedestroy($sourceImage);
        imagedestroy($destinationImage);
    }
}