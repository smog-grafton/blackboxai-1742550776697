<?php
class Modal {
    private $id;
    private $title;
    private $content;
    private $size;
    private $options;

    /**
     * Constructor
     */
    public function __construct($id, $title = '', $content = '', $size = 'md', $options = []) {
        $this->id = $id;
        $this->title = $title;
        $this->content = $content;
        $this->size = $size;
        $this->options = array_merge([
            'closeButton' => true,
            'backdrop' => true,
            'keyboard' => true,
            'centered' => true,
            'scrollable' => true
        ], $options);
    }

    /**
     * Get size classes
     */
    private function getSizeClasses() {
        return [
            'sm' => 'max-w-md',
            'md' => 'max-w-lg',
            'lg' => 'max-w-xl',
            'xl' => 'max-w-2xl',
            '2xl' => 'max-w-3xl',
            '3xl' => 'max-w-4xl',
            '4xl' => 'max-w-5xl',
            'full' => 'max-w-full'
        ][$this->size] ?? 'max-w-lg';
    }

    /**
     * Render modal
     */
    public function render() {
        $sizeClass = $this->getSizeClasses();
        ?>
        <div id="<?= $this->id ?>" 
             class="fixed inset-0 z-50 hidden"
             role="dialog"
             aria-labelledby="<?= $this->id ?>-title"
             aria-modal="true">
            
            <!-- Backdrop -->
            <?php if ($this->options['backdrop']): ?>
                <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"></div>
            <?php endif; ?>

            <!-- Modal Dialog -->
            <div class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                    <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 <?= $sizeClass ?> w-full">
                        <!-- Header -->
                        <div class="bg-white px-4 py-3 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-gray-900" id="<?= $this->id ?>-title">
                                    <?= htmlspecialchars($this->title) ?>
                                </h3>
                                <?php if ($this->options['closeButton']): ?>
                                    <button type="button" 
                                            class="text-gray-400 hover:text-gray-500"
                                            onclick="Modal.close('<?= $this->id ?>')">
                                        <span class="sr-only">Close</span>
                                        <i class="fas fa-times"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="bg-white px-4 py-4">
                            <?= $this->content ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Get modal JavaScript
     */
    public static function getJavaScript() {
        ?>
        <script>
            class ModalManager {
                constructor() {
                    this.modals = new Map();
                    this.activeModals = [];
                    this.setupKeyboardListener();
                }

                setupKeyboardListener() {
                    document.addEventListener('keydown', (e) => {
                        if (e.key === 'Escape' && this.activeModals.length > 0) {
                            const topModal = this.activeModals[this.activeModals.length - 1];
                            const modal = this.modals.get(topModal);
                            if (modal && modal.options.keyboard) {
                                this.close(topModal);
                            }
                        }
                    });
                }

                register(id, options = {}) {
                    this.modals.set(id, {
                        element: document.getElementById(id),
                        options: {
                            closeButton: true,
                            backdrop: true,
                            keyboard: true,
                            centered: true,
                            scrollable: true,
                            ...options
                        }
                    });
                }

                open(id) {
                    const modal = this.modals.get(id);
                    if (!modal) return;

                    // Show modal
                    modal.element.classList.remove('hidden');
                    document.body.classList.add('overflow-hidden');

                    // Add to active modals stack
                    this.activeModals.push(id);

                    // Focus first focusable element
                    const focusable = modal.element.querySelectorAll(
                        'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
                    );
                    if (focusable.length) {
                        focusable[0].focus();
                    }

                    // Trigger event
                    modal.element.dispatchEvent(new CustomEvent('modal:open'));
                }

                close(id) {
                    const modal = this.modals.get(id);
                    if (!modal) return;

                    // Hide modal
                    modal.element.classList.add('hidden');

                    // Remove from active modals stack
                    const index = this.activeModals.indexOf(id);
                    if (index > -1) {
                        this.activeModals.splice(index, 1);
                    }

                    // If no more active modals, restore body scroll
                    if (this.activeModals.length === 0) {
                        document.body.classList.remove('overflow-hidden');
                    }

                    // Trigger event
                    modal.element.dispatchEvent(new CustomEvent('modal:close'));
                }

                closeAll() {
                    [...this.activeModals].forEach(id => this.close(id));
                }

                confirm(options = {}) {
                    return new Promise((resolve) => {
                        const id = 'modal-confirm-' + Date.now();
                        const modal = document.createElement('div');
                        
                        modal.innerHTML = `
                            <div id="${id}" class="fixed inset-0 z-50 hidden">
                                <div class="fixed inset-0 bg-black bg-opacity-50"></div>
                                <div class="fixed inset-0 z-50 overflow-y-auto">
                                    <div class="flex min-h-full items-center justify-center p-4">
                                        <div class="relative bg-white rounded-lg max-w-md w-full">
                                            <div class="p-6">
                                                <h3 class="text-lg font-semibold mb-4">
                                                    ${options.title || 'Confirm'}
                                                </h3>
                                                <p class="text-gray-600 mb-6">
                                                    ${options.message || 'Are you sure?'}
                                                </p>
                                                <div class="flex justify-end space-x-3">
                                                    <button type="button"
                                                            class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200"
                                                            onclick="Modal.close('${id}'); document.getElementById('${id}').dispatchEvent(new CustomEvent('confirm', { detail: false }));">
                                                        ${options.cancelText || 'Cancel'}
                                                    </button>
                                                    <button type="button"
                                                            class="px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700"
                                                            onclick="Modal.close('${id}'); document.getElementById('${id}').dispatchEvent(new CustomEvent('confirm', { detail: true }));">
                                                        ${options.confirmText || 'Confirm'}
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;

                        document.body.appendChild(modal);
                        this.register(id);

                        modal.addEventListener('confirm', (e) => {
                            resolve(e.detail);
                            setTimeout(() => modal.remove(), 300);
                        });

                        this.open(id);
                    });
                }

                alert(message, title = 'Alert') {
                    return this.confirm({
                        title,
                        message,
                        confirmText: 'OK',
                        cancelText: null
                    });
                }
            }

            // Initialize global modal manager
            window.Modal = new ModalManager();
        </script>
        <?php
    }
}

// Usage example:
if (!isset($hideExample)) {
    // Basic modal
    /*
    $modal = new Modal(
        'example-modal',
        'Modal Title',
        '<p>Modal content goes here...</p>
         <div class="mt-4 flex justify-end">
             <button type="button" 
                     class="px-4 py-2 bg-blue-600 text-white rounded-lg"
                     onclick="Modal.close(\'example-modal\')">
                 Close
             </button>
         </div>'
    );
    $modal->render();
    */

    // JavaScript usage
    /*
    // Include this in your layout or header
    Modal::getJavaScript();

    // Then use in your JavaScript code:
    Modal.register('example-modal');
    Modal.open('example-modal');

    // Confirmation dialog
    Modal.confirm({
        title: 'Delete Item',
        message: 'Are you sure you want to delete this item?',
        confirmText: 'Delete',
        cancelText: 'Cancel'
    }).then(confirmed => {
        if (confirmed) {
            // Handle confirmation
        }
    });

    // Alert dialog
    Modal.alert('Operation completed successfully!', 'Success');
    */
}