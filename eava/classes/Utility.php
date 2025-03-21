<?php
class Utility {
    /**
     * Generate a URL-friendly slug from a string
     */
    public static function generateSlug($string) {
        // Convert to lowercase and remove accents
        $string = strtolower(trim(strip_tags($string)));
        $string = iconv('UTF-8', 'ASCII//TRANSLIT', $string);
        
        // Replace non-alphanumeric characters with hyphens
        $string = preg_replace('/[^a-z0-9-]/', '-', $string);
        // Replace multiple hyphens with single hyphen
        $string = preg_replace('/-+/', '-', $string);
        // Remove leading and trailing hyphens
        $string = trim($string, '-');
        
        return $string;
    }

    /**
     * Handle file upload with security checks
     */
    public static function handleFileUpload($file, $allowedTypes, $uploadDir, $maxSize = 5242880) { // 5MB default
        try {
            if (!isset($file['error']) || is_array($file['error'])) {
                throw new Exception('Invalid file parameters');
            }

            switch ($file['error']) {
                case UPLOAD_ERR_OK:
                    break;
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    throw new Exception('File size exceeds limit');
                case UPLOAD_ERR_NO_FILE:
                    throw new Exception('No file uploaded');
                default:
                    throw new Exception('Unknown file upload error');
            }

            if ($file['size'] > $maxSize) {
                throw new Exception('File size exceeds limit');
            }

            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($file['tmp_name']);

            if (!in_array($mimeType, $allowedTypes)) {
                throw new Exception('Invalid file type');
            }

            // Create unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '_' . time() . '.' . $extension;
            $filepath = $uploadDir . $filename;

            if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                throw new Exception('Failed to move uploaded file');
            }

            return [
                'filename' => $filename,
                'filepath' => $filepath,
                'mime_type' => $mimeType,
                'size' => $file['size']
            ];
        } catch (Exception $e) {
            error_log("File Upload Error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Format date for display
     */
    public static function formatDate($date, $format = 'M j, Y') {
        return date($format, strtotime($date));
    }

    /**
     * Format currency amount
     */
    public static function formatCurrency($amount, $currency = 'USD') {
        $currencies = [
            'USD' => ['symbol' => '$', 'decimals' => 2],
            'EUR' => ['symbol' => '€', 'decimals' => 2],
            'GBP' => ['symbol' => '£', 'decimals' => 2],
            'KES' => ['symbol' => 'KSh', 'decimals' => 2],
            'UGX' => ['symbol' => 'USh', 'decimals' => 0],
            'TZS' => ['symbol' => 'TSh', 'decimals' => 0]
        ];

        $currencyInfo = $currencies[$currency] ?? ['symbol' => '$', 'decimals' => 2];
        return $currencyInfo['symbol'] . number_format($amount, $currencyInfo['decimals']);
    }

    /**
     * Truncate text to specified length
     */
    public static function truncateText($text, $length = 100, $append = '...') {
        if (strlen($text) <= $length) {
            return $text;
        }
        
        $truncated = substr($text, 0, $length);
        // Ensure we don't cut in the middle of a word
        $lastSpace = strrpos($truncated, ' ');
        
        return substr($truncated, 0, $lastSpace) . $append;
    }

    /**
     * Generate random string
     */
    public static function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        
        return $randomString;
    }

    /**
     * Clean input data
     */
    public static function cleanInput($data) {
        if (is_array($data)) {
            return array_map([self::class, 'cleanInput'], $data);
        }
        
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        return $data;
    }

    /**
     * Validate email address
     */
    public static function isValidEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate URL
     */
    public static function isValidUrl($url) {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Generate breadcrumbs
     */
    public static function generateBreadcrumbs($items) {
        $html = '<nav class="text-gray-500 py-2">';
        $html .= '<ol class="list-none p-0 inline-flex">';
        
        $lastKey = array_key_last($items);
        foreach ($items as $key => $item) {
            if ($key === $lastKey) {
                $html .= '<li class="text-gray-700">' . htmlspecialchars($item['label']) . '</li>';
            } else {
                $html .= '<li>';
                $html .= '<a href="' . htmlspecialchars($item['url']) . '" class="text-indigo-600 hover:text-indigo-800">';
                $html .= htmlspecialchars($item['label']);
                $html .= '</a>';
                $html .= '<span class="mx-2">/</span>';
                $html .= '</li>';
            }
        }
        
        $html .= '</ol>';
        $html .= '</nav>';
        
        return $html;
    }

    /**
     * Get file extension from mime type
     */
    public static function getExtensionFromMime($mimeType) {
        $mimeMap = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'application/pdf' => 'pdf',
            'video/mp4' => 'mp4',
            'video/quicktime' => 'mov',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'application/vnd.ms-excel' => 'xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx'
        ];

        return $mimeMap[$mimeType] ?? '';
    }

    /**
     * Get human readable file size
     */
    public static function formatFileSize($bytes) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Generate pagination HTML
     */
    public static function generatePagination($currentPage, $totalPages, $urlPattern) {
        if ($totalPages <= 1) return '';

        $html = '<div class="flex items-center justify-center mt-8">';
        $html .= '<nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">';

        // Previous page
        if ($currentPage > 1) {
            $prevUrl = str_replace('{page}', $currentPage - 1, $urlPattern);
            $html .= '<a href="' . $prevUrl . '" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">';
            $html .= '<span class="sr-only">Previous</span>';
            $html .= '<i class="fas fa-chevron-left"></i>';
            $html .= '</a>';
        }

        // Page numbers
        for ($i = 1; $i <= $totalPages; $i++) {
            if ($i === $currentPage) {
                $html .= '<span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-indigo-50 text-sm font-medium text-indigo-600">' . $i . '</span>';
            } else {
                $pageUrl = str_replace('{page}', $i, $urlPattern);
                $html .= '<a href="' . $pageUrl . '" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">' . $i . '</a>';
            }
        }

        // Next page
        if ($currentPage < $totalPages) {
            $nextUrl = str_replace('{page}', $currentPage + 1, $urlPattern);
            $html .= '<a href="' . $nextUrl . '" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">';
            $html .= '<span class="sr-only">Next</span>';
            $html .= '<i class="fas fa-chevron-right"></i>';
            $html .= '</a>';
        }

        $html .= '</nav>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Generate meta tags for SEO
     */
    public static function generateMetaTags($data) {
        $meta = '';
        
        // Basic meta tags
        if (!empty($data['title'])) {
            $meta .= '<title>' . htmlspecialchars($data['title']) . '</title>';
            $meta .= '<meta property="og:title" content="' . htmlspecialchars($data['title']) . '">';
        }
        
        if (!empty($data['description'])) {
            $meta .= '<meta name="description" content="' . htmlspecialchars($data['description']) . '">';
            $meta .= '<meta property="og:description" content="' . htmlspecialchars($data['description']) . '">';
        }
        
        if (!empty($data['keywords'])) {
            $meta .= '<meta name="keywords" content="' . htmlspecialchars($data['keywords']) . '">';
        }
        
        // Open Graph tags
        if (!empty($data['image'])) {
            $meta .= '<meta property="og:image" content="' . htmlspecialchars($data['image']) . '">';
        }
        
        if (!empty($data['url'])) {
            $meta .= '<meta property="og:url" content="' . htmlspecialchars($data['url']) . '">';
        }
        
        $meta .= '<meta property="og:type" content="' . (isset($data['type']) ? htmlspecialchars($data['type']) : 'website') . '">';
        
        // Twitter Card tags
        $meta .= '<meta name="twitter:card" content="summary_large_image">';
        
        if (!empty($data['twitter_handle'])) {
            $meta .= '<meta name="twitter:site" content="@' . htmlspecialchars($data['twitter_handle']) . '">';
        }
        
        return $meta;
    }
}