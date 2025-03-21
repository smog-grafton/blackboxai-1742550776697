<?php
class Notification {
    private static $types = [
        'success' => [
            'bg' => 'bg-green-50',
            'border' => 'border-green-400',
            'text' => 'text-green-700',
            'icon' => 'fa-check-circle',
            'title' => 'Success'
        ],
        'error' => [
            'bg' => 'bg-red-50',
            'border' => 'border-red-400',
            'text' => 'text-red-700',
            'icon' => 'fa-times-circle',
            'title' => 'Error'
        ],
        'warning' => [
            'bg' => 'bg-yellow-50',
            'border' => 'border-yellow-400',
            'text' => 'text-yellow-700',
            'icon' => 'fa-exclamation-circle',
            'title' => 'Warning'
        ],
        'info' => [
            'bg' => 'bg-blue-50',
            'border' => 'border-blue-400',
            'text' => 'text-blue-700',
            'icon' => 'fa-info-circle',
            'title' => 'Information'
        ]
    ];

    /**
     * Render notification
     */
    public static function render($message, $type = 'info', $options = []) {
        $config = self::$types[$type] ?? self::$types['info'];
        $dismissible = $options['dismissible'] ?? true;
        $duration = $options['duration'] ?? null;
        $id = 'notification-' . uniqid();
        ?>
        <div id="<?= $id ?>" 
             class="rounded-lg p-4 mb-4 border <?= $config['bg'] ?> <?= $config['border'] ?> <?= $config['text'] ?>"
             role="alert">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas <?= $config['icon'] ?> mt-0.5"></i>
                </div>
                <div class="ml-3">
                    <p class="font-medium"><?= $config['title'] ?></p>
                    <p class="mt-1"><?= htmlspecialchars($message) ?></p>
                </div>
                <?php if ($dismissible): ?>
                    <div class="ml-auto pl-3">
                        <button type="button" 
                                class="inline-flex rounded-md p-1.5 focus:outline-none focus:ring-2 focus:ring-offset-2 <?= $config['text'] ?> hover:bg-opacity-20 hover:bg-gray-900"
                                onclick="dismissNotification('<?= $id ?>')">
                            <span class="sr-only">Dismiss</span>
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($duration): ?>
            <script>
                setTimeout(() => {
                    dismissNotification('<?= $id ?>');
                }, <?= $duration * 1000 ?>);
            </script>
        <?php endif;
    }

    /**
     * Get notification JavaScript
     */
    public static function getJavaScript() {
        ?>
        <script>
            class NotificationManager {
                constructor() {
                    this.container = this.createContainer();
                }

                createContainer() {
                    let container = document.getElementById('notification-container');
                    if (!container) {
                        container = document.createElement('div');
                        container.id = 'notification-container';
                        container.className = 'fixed top-4 right-4 z-50 w-96 space-y-4';
                        document.body.appendChild(container);
                    }
                    return container;
                }

                show(message, type = 'info', options = {}) {
                    const id = 'notification-' + Date.now();
                    const config = <?= json_encode(self::$types) ?>[type] || <?= json_encode(self::$types) ?>['info'];
                    
                    const notification = document.createElement('div');
                    notification.id = id;
                    notification.className = `transform transition-all duration-300 translate-x-full`;
                    notification.innerHTML = `
                        <div class="rounded-lg p-4 border ${config.bg} ${config.border} ${config.text}" role="alert">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas ${config.icon} mt-0.5"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="font-medium">${config.title}</p>
                                    <p class="mt-1">${message}</p>
                                </div>
                                ${options.dismissible !== false ? `
                                    <div class="ml-auto pl-3">
                                        <button type="button" 
                                                class="inline-flex rounded-md p-1.5 focus:outline-none focus:ring-2 focus:ring-offset-2 ${config.text} hover:bg-opacity-20 hover:bg-gray-900"
                                                onclick="dismissNotification('${id}')">
                                            <span class="sr-only">Dismiss</span>
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                ` : ''}
                            </div>
                        </div>
                    `;

                    this.container.appendChild(notification);
                    
                    // Trigger animation
                    requestAnimationFrame(() => {
                        notification.classList.remove('translate-x-full');
                    });

                    if (options.duration) {
                        setTimeout(() => {
                            this.dismiss(id);
                        }, options.duration * 1000);
                    }

                    return id;
                }

                dismiss(id) {
                    const notification = document.getElementById(id);
                    if (notification) {
                        notification.classList.add('translate-x-full');
                        setTimeout(() => {
                            notification.remove();
                        }, 300);
                    }
                }

                success(message, options = {}) {
                    return this.show(message, 'success', options);
                }

                error(message, options = {}) {
                    return this.show(message, 'error', options);
                }

                warning(message, options = {}) {
                    return this.show(message, 'warning', options);
                }

                info(message, options = {}) {
                    return this.show(message, 'info', options);
                }
            }

            // Initialize global notification manager
            window.notifications = new NotificationManager();

            function dismissNotification(id) {
                window.notifications.dismiss(id);
            }
        </script>
        <?php
    }
}

// Usage example:
if (!isset($hideExample)) {
    // Server-side rendering
    /*
    Notification::render(
        'Your changes have been saved successfully!',
        'success',
        [
            'dismissible' => true,
            'duration' => 5 // Auto-dismiss after 5 seconds
        ]
    );
    */

    // JavaScript usage
    /*
    // Include this in your layout or header
    Notification::getJavaScript();

    // Then use in your JavaScript code:
    notifications.success('Changes saved successfully!', { duration: 5 });
    notifications.error('An error occurred. Please try again.');
    notifications.warning('Your session will expire soon.');
    notifications.info('New updates are available.');
    */
}