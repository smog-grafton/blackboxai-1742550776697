<?php
require_once __DIR__ . '/../../classes/Settings.php';
$settings = new Settings();

// Default meta tags
$meta = array_merge([
    'title' => $settings->get('site_name'),
    'description' => $settings->get('site_description'),
    'keywords' => $settings->get('site_keywords'),
    'image' => $settings->get('site_logo'),
    'type' => 'website',
    'robots' => 'index, follow'
], $meta ?? []);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Primary Meta Tags -->
    <title><?= htmlspecialchars($meta['title']) ?></title>
    <meta name="description" content="<?= htmlspecialchars($meta['description']) ?>">
    <meta name="keywords" content="<?= htmlspecialchars($meta['keywords']) ?>">
    <meta name="robots" content="<?= htmlspecialchars($meta['robots']) ?>">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="<?= htmlspecialchars($meta['type']) ?>">
    <meta property="og:url" content="<?= htmlspecialchars('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>">
    <meta property="og:title" content="<?= htmlspecialchars($meta['title']) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($meta['description']) ?>">
    <meta property="og:image" content="<?= htmlspecialchars($meta['image']) ?>">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="<?= htmlspecialchars('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>">
    <meta property="twitter:title" content="<?= htmlspecialchars($meta['title']) ?>">
    <meta property="twitter:description" content="<?= htmlspecialchars($meta['description']) ?>">
    <meta property="twitter:image" content="<?= htmlspecialchars($meta['image']) ?>">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= $settings->get('site_favicon') ?>">
    
    <!-- Styles -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/public/assets/css/main.css">

    <!-- Custom Styles -->
    <style>
        :root {
            --primary-color: <?= $settings->get('primary_color', '#3B82F6') ?>;
            --secondary-color: <?= $settings->get('secondary_color', '#1F2937') ?>;
            --accent-color: <?= $settings->get('accent_color', '#10B981') ?>;
        }
    </style>

    <!-- Scripts -->
    <script src="https://unpkg.com/masonry-layout@4/dist/masonry.pkgd.min.js"></script>
    <script src="https://unpkg.com/imagesloaded@5/imagesloaded.pkgd.min.js"></script>
    <script src="/public/assets/js/main.js" defer></script>

    <!-- Google Analytics -->
    <?php if ($ga_id = $settings->get('google_analytics_id')): ?>
        <script async src="https://www.googletagmanager.com/gtag/js?id=<?= htmlspecialchars($ga_id) ?>"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '<?= htmlspecialchars($ga_id) ?>');
        </script>
    <?php endif; ?>

    <!-- Custom Head Content -->
    <?php if (isset($head_content)) echo $head_content; ?>
</head>
<body class="min-h-screen flex flex-col">
    <!-- Skip to main content -->
    <a href="#main-content" 
       class="sr-only focus:not-sr-only focus:absolute focus:top-0 focus:left-0 bg-blue-600 text-white px-4 py-2">
        Skip to main content
    </a>

    <!-- Header -->
    <?php include __DIR__ . '/header.php'; ?>

    <!-- Main Content -->
    <main id="main-content" class="flex-grow">
        <?php if (isset($breadcrumbs)): ?>
            <div class="bg-gray-100 py-4">
                <div class="container mx-auto px-4">
                    <nav class="flex" aria-label="Breadcrumb">
                        <ol class="flex items-center space-x-2">
                            <li>
                                <a href="/" class="text-gray-500 hover:text-gray-700">
                                    <i class="fas fa-home"></i>
                                    <span class="sr-only">Home</span>
                                </a>
                            </li>
                            <?php foreach ($breadcrumbs as $label => $url): ?>
                                <li>
                                    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                                    <?php if ($url): ?>
                                        <a href="<?= htmlspecialchars($url) ?>" 
                                           class="text-gray-500 hover:text-gray-700">
                                            <?= htmlspecialchars($label) ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-gray-900">
                                            <?= htmlspecialchars($label) ?>
                                        </span>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ol>
                    </nav>
                </div>
            </div>
        <?php endif; ?>

        <!-- Page Content -->
        <?php if (isset($content)) echo $content; ?>
    </main>

    <!-- Footer -->
    <?php include __DIR__ . '/footer.php'; ?>

    <!-- Notifications Container -->
    <div id="notifications" class="fixed bottom-4 right-4 z-50 space-y-4"></div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" 
         class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg p-8">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
            <p class="mt-4 text-center text-white">Loading...</p>
        </div>
    </div>

    <!-- Custom Scripts -->
    <?php if (isset($scripts)) echo $scripts; ?>

    <script>
        // Show/hide loading overlay
        function showLoading() {
            document.getElementById('loadingOverlay').classList.remove('hidden');
        }

        function hideLoading() {
            document.getElementById('loadingOverlay').classList.add('hidden');
        }

        // Show notification
        function showNotification(type, message, duration = 5000) {
            const container = document.getElementById('notifications');
            const notification = document.createElement('div');
            
            notification.className = `
                max-w-sm w-full bg-white rounded-lg shadow-lg border-l-4 
                ${type === 'success' ? 'border-green-500' : 'border-red-500'}
                p-4 transform transition-all duration-300 translate-x-full
            `;
            
            notification.innerHTML = `
                <div class="flex items-center">
                    <i class="fas fa-${type === 'success' ? 'check-circle text-green-500' : 'exclamation-circle text-red-500'} mr-3"></i>
                    <p class="text-gray-800">${message}</p>
                </div>
            `;

            container.appendChild(notification);
            
            // Trigger animation
            requestAnimationFrame(() => {
                notification.classList.remove('translate-x-full');
            });

            // Remove notification after duration
            setTimeout(() => {
                notification.classList.add('translate-x-full');
                setTimeout(() => {
                    notification.remove();
                }, 300);
            }, duration);
        }

        // Handle form submissions
        document.addEventListener('submit', async function(e) {
            const form = e.target;
            if (!form.hasAttribute('data-ajax')) return;

            e.preventDefault();
            const submitButton = form.querySelector('[type="submit"]');
            const originalText = submitButton.textContent;

            try {
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';

                const response = await fetch(form.action, {
                    method: form.method,
                    body: new FormData(form),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await response.json();

                if (response.ok) {
                    showNotification('success', data.message);
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    } else {
                        form.reset();
                    }
                } else {
                    throw new Error(data.message || 'Form submission failed');
                }
            } catch (error) {
                showNotification('error', error.message);
            } finally {
                submitButton.disabled = false;
                submitButton.textContent = originalText;
            }
        });
    </script>
</body>
</html>