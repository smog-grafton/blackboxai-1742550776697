<?php
class Tabs {
    private $id;
    private $tabs;
    private $activeTab;
    private $options;

    /**
     * Constructor
     */
    public function __construct($id, $tabs = [], $activeTab = null, $options = []) {
        $this->id = $id;
        $this->tabs = $tabs;
        $this->activeTab = $activeTab ?? array_key_first($tabs);
        $this->options = array_merge([
            'type' => 'default', // default, pills, underline
            'align' => 'left', // left, center, right, justify
            'size' => 'md', // sm, md, lg
            'vertical' => false,
            'animated' => true
        ], $options);
    }

    /**
     * Get tab type classes
     */
    private function getTypeClasses() {
        $base = 'focus:outline-none';
        $types = [
            'default' => [
                'nav' => 'border-b border-gray-200',
                'tab' => $base . ' px-4 py-2 border-b-2 border-transparent hover:border-gray-300',
                'active' => 'border-blue-500 text-blue-600'
            ],
            'pills' => [
                'nav' => '',
                'tab' => $base . ' px-4 py-2 rounded-lg hover:bg-gray-100',
                'active' => 'bg-blue-600 text-white hover:bg-blue-700'
            ],
            'underline' => [
                'nav' => '',
                'tab' => $base . ' px-4 py-2 border-b-2 border-transparent hover:text-gray-700',
                'active' => 'border-blue-500 text-blue-600'
            ]
        ];
        return $types[$this->options['type']] ?? $types['default'];
    }

    /**
     * Get alignment classes
     */
    private function getAlignmentClasses() {
        return [
            'left' => 'justify-start',
            'center' => 'justify-center',
            'right' => 'justify-end',
            'justify' => 'justify-between'
        ][$this->options['align']] ?? 'justify-start';
    }

    /**
     * Get size classes
     */
    private function getSizeClasses() {
        return [
            'sm' => 'text-sm',
            'md' => 'text-base',
            'lg' => 'text-lg'
        ][$this->options['size']] ?? 'text-base';
    }

    /**
     * Render tabs
     */
    public function render() {
        $typeClasses = $this->getTypeClasses();
        $alignmentClass = $this->getAlignmentClasses();
        $sizeClass = $this->getSizeClasses();
        ?>
        <div id="<?= $this->id ?>" 
             class="<?= $this->options['vertical'] ? 'flex space-x-6' : '' ?>"
             x-data="{ activeTab: '<?= $this->activeTab ?>' }">
            
            <!-- Tab Navigation -->
            <div class="<?= $this->options['vertical'] ? 'w-1/4' : '' ?>">
                <nav class="<?= $typeClasses['nav'] ?> <?= $sizeClass ?>">
                    <div class="flex <?= $this->options['vertical'] ? 'flex-col space-y-2' : $alignmentClass ?> <?= !$this->options['vertical'] ? 'space-x-4' : '' ?>">
                        <?php foreach ($this->tabs as $id => $tab): ?>
                            <button type="button"
                                    @click="activeTab = '<?= $id ?>'"
                                    :class="{ '<?= $typeClasses['active'] ?>': activeTab === '<?= $id ?>' }"
                                    class="<?= $typeClasses['tab'] ?> flex items-center"
                                    role="tab"
                                    aria-controls="<?= $this->id ?>-<?= $id ?>"
                                    :aria-selected="activeTab === '<?= $id ?>'">
                                <?php if (!empty($tab['icon'])): ?>
                                    <i class="<?= $tab['icon'] ?> <?= !empty($tab['label']) ? 'mr-2' : '' ?>"></i>
                                <?php endif; ?>
                                <?php if (!empty($tab['label'])): ?>
                                    <span><?= htmlspecialchars($tab['label']) ?></span>
                                <?php endif; ?>
                                <?php if (!empty($tab['badge'])): ?>
                                    <span class="ml-2 px-2 py-0.5 text-xs rounded-full <?= $tab['badge']['class'] ?? 'bg-gray-100' ?>">
                                        <?= htmlspecialchars($tab['badge']['text']) ?>
                                    </span>
                                <?php endif; ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </nav>
            </div>

            <!-- Tab Panels -->
            <div class="<?= $this->options['vertical'] ? 'w-3/4' : 'mt-4' ?>">
                <?php foreach ($this->tabs as $id => $tab): ?>
                    <div x-show="activeTab === '<?= $id ?>'"
                         id="<?= $this->id ?>-<?= $id ?>"
                         role="tabpanel"
                         <?php if ($this->options['animated']): ?>
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0"
                             x-transition:enter-end="opacity-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100"
                             x-transition:leave-end="opacity-0"
                         <?php endif; ?>>
                        <?= $tab['content'] ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Get tabs JavaScript
     */
    public static function getJavaScript() {
        ?>
        <script>
            class TabManager {
                constructor() {
                    this.tabs = new Map();
                }

                register(id, options = {}) {
                    this.tabs.set(id, {
                        element: document.getElementById(id),
                        activeTab: options.activeTab || null,
                        onChange: options.onChange || null
                    });

                    if (options.onChange) {
                        this.setupChangeListener(id);
                    }
                }

                setupChangeListener(id) {
                    const tab = this.tabs.get(id);
                    if (!tab || !tab.onChange) return;

                    const observer = new MutationObserver((mutations) => {
                        mutations.forEach((mutation) => {
                            if (mutation.type === 'attributes' && mutation.attributeName === 'aria-selected') {
                                const activeTab = Array.from(tab.element.querySelectorAll('[role="tab"]'))
                                    .find(tab => tab.getAttribute('aria-selected') === 'true');
                                
                                if (activeTab) {
                                    const tabId = activeTab.getAttribute('aria-controls').replace(`${id}-`, '');
                                    tab.onChange(tabId, activeTab);
                                }
                            }
                        });
                    });

                    observer.observe(tab.element, {
                        attributes: true,
                        subtree: true
                    });
                }

                activate(id, tabId) {
                    const tab = this.tabs.get(id);
                    if (!tab) return;

                    const alpineComponent = Alpine.$data(tab.element);
                    if (alpineComponent) {
                        alpineComponent.activeTab = tabId;
                    }
                }

                getActive(id) {
                    const tab = this.tabs.get(id);
                    if (!tab) return null;

                    const alpineComponent = Alpine.$data(tab.element);
                    return alpineComponent ? alpineComponent.activeTab : null;
                }
            }

            // Initialize global tab manager
            window.Tabs = new TabManager();
        </script>
        <?php
    }
}

// Usage example:
if (!isset($hideExample)) {
    // Basic tabs
    /*
    $tabs = new Tabs('example-tabs', [
        'tab1' => [
            'label' => 'Tab 1',
            'icon' => 'fas fa-home',
            'content' => '<p>Content for tab 1</p>'
        ],
        'tab2' => [
            'label' => 'Tab 2',
            'icon' => 'fas fa-user',
            'badge' => [
                'text' => 'New',
                'class' => 'bg-red-100 text-red-600'
            ],
            'content' => '<p>Content for tab 2</p>'
        ]
    ]);
    $tabs->render();
    */

    // JavaScript usage
    /*
    // Include this in your layout or header
    Tabs::getJavaScript();

    // Then use in your JavaScript code:
    Tabs.register('example-tabs', {
        onChange: (tabId, tabElement) => {
            console.log(`Tab changed to: ${tabId}`);
        }
    });

    // Activate a specific tab
    Tabs.activate('example-tabs', 'tab2');

    // Get active tab
    const activeTab = Tabs.getActive('example-tabs');
    */
}