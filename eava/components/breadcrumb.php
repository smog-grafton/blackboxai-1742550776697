<?php
class Breadcrumb {
    private $items = [];
    private $currentItem = '';

    /**
     * Add item to breadcrumb
     */
    public function add($label, $url = null) {
        if ($url) {
            $this->items[] = [
                'label' => $label,
                'url' => $url
            ];
        } else {
            $this->currentItem = $label;
        }
    }

    /**
     * Render breadcrumb
     */
    public function render() {
        ?>
        <nav class="bg-gray-100 py-3 mb-6">
            <div class="container mx-auto px-4">
                <ol class="flex flex-wrap items-center text-sm">
                    <!-- Home -->
                    <li>
                        <a href="/" class="text-blue-600 hover:text-blue-800">
                            <i class="fas fa-home"></i>
                            <span class="sr-only">Home</span>
                        </a>
                    </li>

                    <!-- Separator -->
                    <?php if (!empty($this->items) || $this->currentItem): ?>
                        <li class="mx-2 text-gray-500">
                            <i class="fas fa-chevron-right text-xs"></i>
                        </li>
                    <?php endif; ?>

                    <!-- Navigation Items -->
                    <?php foreach ($this->items as $index => $item): ?>
                        <li>
                            <a href="<?= htmlspecialchars($item['url']) ?>" 
                               class="text-blue-600 hover:text-blue-800">
                                <?= htmlspecialchars($item['label']) ?>
                            </a>
                        </li>
                        <li class="mx-2 text-gray-500">
                            <i class="fas fa-chevron-right text-xs"></i>
                        </li>
                    <?php endforeach; ?>

                    <!-- Current Page -->
                    <?php if ($this->currentItem): ?>
                        <li class="text-gray-600 font-medium">
                            <?= htmlspecialchars($this->currentItem) ?>
                        </li>
                    <?php endif; ?>
                </ol>

                <!-- Schema.org Breadcrumb Markup -->
                <script type="application/ld+json">
                {
                    "@context": "https://schema.org",
                    "@type": "BreadcrumbList",
                    "itemListElement": [
                        {
                            "@type": "ListItem",
                            "position": 1,
                            "name": "Home",
                            "item": "<?= htmlspecialchars(rtrim($this->getBaseUrl(), '/')) ?>"
                        }
                        <?php
                        $position = 2;
                        foreach ($this->items as $item) {
                            echo ",\n";
                            ?>
                            {
                                "@type": "ListItem",
                                "position": <?= $position++ ?>,
                                "name": "<?= htmlspecialchars($item['label']) ?>",
                                "item": "<?= htmlspecialchars($this->getBaseUrl() . ltrim($item['url'], '/')) ?>"
                            }
                            <?php
                        }
                        if ($this->currentItem) {
                            echo ",\n";
                            ?>
                            {
                                "@type": "ListItem",
                                "position": <?= $position ?>,
                                "name": "<?= htmlspecialchars($this->currentItem) ?>"
                            }
                            <?php
                        }
                        ?>
                    ]
                }
                </script>
            </div>
        </nav>
        <?php
    }

    /**
     * Get base URL
     */
    private function getBaseUrl() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
        return $protocol . $_SERVER['HTTP_HOST'] . '/';
    }
}

// Usage example:
if (!isset($hideExample)) {
    // Example usage in a page:
    /*
    $breadcrumb = new Breadcrumb();
    $breadcrumb->add('Programs', '/programs');
    $breadcrumb->add('Events', '/programs/events');
    $breadcrumb->add('Summer Art Festival 2024');
    $breadcrumb->render();
    */
}