<?php
class Pagination {
    private $currentPage;
    private $totalPages;
    private $baseUrl;
    private $queryParams;
    private $maxVisiblePages = 5;

    /**
     * Constructor
     */
    public function __construct($currentPage, $totalPages, $baseUrl = null, $queryParams = []) {
        $this->currentPage = max(1, (int)$currentPage);
        $this->totalPages = max(1, (int)$totalPages);
        $this->baseUrl = $baseUrl ?? $this->getCurrentUrl();
        $this->queryParams = $queryParams;
    }

    /**
     * Render pagination
     */
    public function render() {
        if ($this->totalPages <= 1) {
            return;
        }
        ?>
        <nav class="flex justify-center my-8" aria-label="Pagination">
            <ul class="flex items-center -space-x-px">
                <!-- Previous Page -->
                <?php if ($this->currentPage > 1): ?>
                    <li>
                        <a href="<?= $this->getPageUrl($this->currentPage - 1) ?>" 
                           class="block px-3 py-2 ml-0 leading-tight text-gray-500 bg-white border border-gray-300 rounded-l-lg hover:bg-gray-100 hover:text-gray-700">
                            <span class="sr-only">Previous</span>
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>
                <?php else: ?>
                    <li>
                        <span class="block px-3 py-2 ml-0 leading-tight text-gray-400 bg-gray-100 border border-gray-300 rounded-l-lg cursor-not-allowed">
                            <span class="sr-only">Previous</span>
                            <i class="fas fa-chevron-left"></i>
                        </span>
                    </li>
                <?php endif; ?>

                <!-- First Page -->
                <?php if ($this->currentPage > $this->maxVisiblePages): ?>
                    <li>
                        <a href="<?= $this->getPageUrl(1) ?>" 
                           class="px-3 py-2 leading-tight text-gray-500 bg-white border border-gray-300 hover:bg-gray-100 hover:text-gray-700">
                            1
                        </a>
                    </li>
                    <?php if ($this->currentPage > $this->maxVisiblePages + 1): ?>
                        <li>
                            <span class="px-3 py-2 leading-tight text-gray-500 bg-white border border-gray-300">
                                ...
                            </span>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>

                <!-- Page Numbers -->
                <?php
                $start = max(1, min($this->currentPage - floor($this->maxVisiblePages / 2), $this->totalPages - $this->maxVisiblePages + 1));
                $end = min($start + $this->maxVisiblePages - 1, $this->totalPages);
                
                for ($i = $start; $i <= $end; $i++):
                ?>
                    <li>
                        <?php if ($i === $this->currentPage): ?>
                            <span class="z-10 px-3 py-2 leading-tight text-blue-600 border border-blue-300 bg-blue-50">
                                <?= $i ?>
                            </span>
                        <?php else: ?>
                            <a href="<?= $this->getPageUrl($i) ?>" 
                               class="px-3 py-2 leading-tight text-gray-500 bg-white border border-gray-300 hover:bg-gray-100 hover:text-gray-700">
                                <?= $i ?>
                            </a>
                        <?php endif; ?>
                    </li>
                <?php endfor; ?>

                <!-- Last Page -->
                <?php if ($end < $this->totalPages): ?>
                    <?php if ($end < $this->totalPages - 1): ?>
                        <li>
                            <span class="px-3 py-2 leading-tight text-gray-500 bg-white border border-gray-300">
                                ...
                            </span>
                        </li>
                    <?php endif; ?>
                    <li>
                        <a href="<?= $this->getPageUrl($this->totalPages) ?>" 
                           class="px-3 py-2 leading-tight text-gray-500 bg-white border border-gray-300 hover:bg-gray-100 hover:text-gray-700">
                            <?= $this->totalPages ?>
                        </a>
                    </li>
                <?php endif; ?>

                <!-- Next Page -->
                <?php if ($this->currentPage < $this->totalPages): ?>
                    <li>
                        <a href="<?= $this->getPageUrl($this->currentPage + 1) ?>" 
                           class="block px-3 py-2 leading-tight text-gray-500 bg-white border border-gray-300 rounded-r-lg hover:bg-gray-100 hover:text-gray-700">
                            <span class="sr-only">Next</span>
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                <?php else: ?>
                    <li>
                        <span class="block px-3 py-2 leading-tight text-gray-400 bg-gray-100 border border-gray-300 rounded-r-lg cursor-not-allowed">
                            <span class="sr-only">Next</span>
                            <i class="fas fa-chevron-right"></i>
                        </span>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>

        <!-- Page Info -->
        <div class="text-center text-sm text-gray-500 mb-8">
            Page <?= $this->currentPage ?> of <?= $this->totalPages ?>
        </div>
        <?php
    }

    /**
     * Get URL for a specific page
     */
    private function getPageUrl($page) {
        $params = $this->queryParams;
        $params['page'] = $page;
        
        $query = http_build_query($params);
        return $this->baseUrl . ($query ? '?' . $query : '');
    }

    /**
     * Get current URL without query string
     */
    private function getCurrentUrl() {
        $url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        return rtrim($url, '/');
    }

    /**
     * Set maximum visible pages
     */
    public function setMaxVisiblePages($count) {
        $this->maxVisiblePages = max(3, (int)$count);
    }

    /**
     * Get pagination info
     */
    public function getInfo() {
        return [
            'current_page' => $this->currentPage,
            'total_pages' => $this->totalPages,
            'has_previous' => $this->currentPage > 1,
            'has_next' => $this->currentPage < $this->totalPages,
            'previous_page' => max(1, $this->currentPage - 1),
            'next_page' => min($this->totalPages, $this->currentPage + 1)
        ];
    }
}

// Usage example:
if (!isset($hideExample)) {
    // Example usage in a page:
    /*
    $pagination = new Pagination(
        $currentPage,    // Current page number
        $totalPages,     // Total number of pages
        '/blog',         // Base URL (optional)
        ['category' => 'news']  // Additional query parameters (optional)
    );
    $pagination->render();
    */
}