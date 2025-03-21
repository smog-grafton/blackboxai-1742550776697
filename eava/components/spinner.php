<?php
class Spinner {
    private $size;
    private $color;
    private $text;
    private $overlay;

    /**
     * Constructor
     */
    public function __construct($options = []) {
        $this->size = $options['size'] ?? 'md';
        $this->color = $options['color'] ?? 'blue';
        $this->text = $options['text'] ?? 'Loading...';
        $this->overlay = $options['overlay'] ?? false;
    }

    /**
     * Get size classes
     */
    private function getSizeClasses() {
        return [
            'sm' => 'w-6 h-6',
            'md' => 'w-8 h-8',
            'lg' => 'w-12 h-12',
            'xl' => 'w-16 h-16'
        ][$this->size] ?? 'w-8 h-8';
    }

    /**
     * Get color classes
     */
    private function getColorClasses() {
        return [
            'blue' => 'text-blue-600',
            'red' => 'text-red-600',
            'green' => 'text-green-600',
            'yellow' => 'text-yellow-600',
            'purple' => 'text-purple-600',
            'gray' => 'text-gray-600',
            'white' => 'text-white'
        ][$this->color] ?? 'text-blue-600';
    }

    /**
     * Render spinner
     */
    public function render() {
        $sizeClasses = $this->getSizeClasses();
        $colorClasses = $this->getColorClasses();
        ?>
        <?php if ($this->overlay): ?>
            <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <?php endif; ?>

        <div class="flex flex-col items-center justify-center">
            <!-- Spinner SVG -->
            <svg class="animate-spin <?= $sizeClasses ?> <?= $colorClasses ?>" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            
            <?php if ($this->text): ?>
                <span class="mt-2 <?= $this->overlay ? 'text-white' : 'text-gray-700' ?>">
                    <?= htmlspecialchars($this->text) ?>
                </span>
            <?php endif; ?>
        </div>

        <?php if ($this->overlay): ?>
            </div>
        <?php endif; ?>

        <?php
    }

    /**
     * Get spinner HTML
     */
    public static function html($options = []) {
        ob_start();
        $spinner = new self($options);
        $spinner->render();
        return ob_get_clean();
    }

    /**
     * Get spinner JavaScript
     */
    public static function getJavaScript() {
        ?>
        <script>
            class SpinnerManager {
                constructor() {
                    this.spinners = new Map();
                }

                show(target, options = {}) {
                    if (typeof target === 'string') {
                        target = document.querySelector(target);
                    }

                    if (!target) return;

                    // Create spinner element
                    const spinner = document.createElement('div');
                    spinner.innerHTML = <?= json_encode(self::html()) ?>;
                    
                    // Store original position if not static
                    const originalPosition = window.getComputedStyle(target).position;
                    if (originalPosition === 'static') {
                        target.style.position = 'relative';
                    }

                    // Position spinner
                    const spinnerElement = spinner.firstElementChild;
                    spinnerElement.style.position = 'absolute';
                    spinnerElement.style.top = '50%';
                    spinnerElement.style.left = '50%';
                    spinnerElement.style.transform = 'translate(-50%, -50%)';

                    // Add spinner to target
                    target.appendChild(spinnerElement);
                    this.spinners.set(target, {
                        element: spinnerElement,
                        originalPosition
                    });
                }

                hide(target) {
                    if (typeof target === 'string') {
                        target = document.querySelector(target);
                    }

                    if (!target) return;

                    const spinner = this.spinners.get(target);
                    if (spinner) {
                        spinner.element.remove();
                        if (spinner.originalPosition === 'static') {
                            target.style.position = 'static';
                        }
                        this.spinners.delete(target);
                    }
                }

                showOverlay(text = 'Loading...') {
                    const overlay = document.createElement('div');
                    overlay.innerHTML = <?= json_encode(self::html(['overlay' => true])) ?>;
                    document.body.appendChild(overlay.firstElementChild);
                }

                hideOverlay() {
                    const overlay = document.querySelector('.fixed.inset-0.bg-black.bg-opacity-50');
                    if (overlay) {
                        overlay.remove();
                    }
                }
            }

            // Initialize global spinner manager
            window.spinner = new SpinnerManager();
        </script>
        <?php
    }
}

// Usage example:
if (!isset($hideExample)) {
    // Basic spinner
    /*
    $spinner = new Spinner([
        'size' => 'lg',
        'color' => 'blue',
        'text' => 'Loading...'
    ]);
    $spinner->render();
    */

    // Overlay spinner
    /*
    $spinner = new Spinner([
        'size' => 'xl',
        'color' => 'white',
        'text' => 'Processing...',
        'overlay' => true
    ]);
    $spinner->render();
    */

    // JavaScript usage
    /*
    // Include this in your layout or header
    Spinner::getJavaScript();

    // Then use in your JavaScript code:
    spinner.show('#content');
    spinner.showOverlay('Processing...');
    
    // Later:
    spinner.hide('#content');
    spinner.hideOverlay();
    */
}