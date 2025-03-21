<?php
require_once __DIR__ . '/../classes/Session.php';
require_once __DIR__ . '/../classes/Settings.php';

$session = Session::getInstance();
$settings = new Settings();

// Get page title and description from parameters
$pageTitle = $pageTitle ?? $settings->get('site_name', 'EAVA');
if (isset($pageSubtitle)) {
    $pageTitle .= ' - ' . $pageSubtitle;
}
$pageDescription = $pageDescription ?? $settings->get('site_description', '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= htmlspecialchars($pageDescription) ?>">
    
    <title><?= htmlspecialchars($pageTitle) ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/assets/images/favicon.png">
    
    <!-- Styles -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/styles.css">

    <!-- Open Graph Tags -->
    <meta property="og:title" content="<?= htmlspecialchars($pageTitle) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($pageDescription) ?>">
    <meta property="og:image" content="<?= $settings->get('site_url') ?>/assets/images/og-image.jpg">
    <meta property="og:url" content="<?= $settings->get('site_url') . $_SERVER['REQUEST_URI'] ?>">
    
    <!-- Twitter Card Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($pageTitle) ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($pageDescription) ?>">
    <meta name="twitter:image" content="<?= $settings->get('site_url') ?>/assets/images/twitter-card.jpg">

    <!-- Custom Page Styles -->
    <?php if (isset($pageStyles)): ?>
        <style>
            <?= $pageStyles ?>
        </style>
    <?php endif; ?>

    <!-- Custom Page Head Content -->
    <?php if (isset($pageHead)) echo $pageHead; ?>
</head>
<body class="flex flex-col min-h-screen bg-gray-50">
    <!-- Flash Messages -->
    <?php if ($message = $session->getFlash('success')): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline"><?= htmlspecialchars($message) ?></span>
            <button class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    <?php endif; ?>

    <?php if ($message = $session->getFlash('error')): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline"><?= htmlspecialchars($message) ?></span>
            <button class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    <?php endif; ?>

    <!-- Header -->
    <?php include 'header.php'; ?>

    <!-- Main Content -->
    <main class="flex-grow">
        <?php if (isset($pageContent)) echo $pageContent; ?>
    </main>

    <!-- Footer -->
    <?php include 'footer.php'; ?>

    <!-- Scripts -->
    <script src="/assets/js/scripts.js"></script>
    
    <!-- Custom Page Scripts -->
    <?php if (isset($pageScripts)): ?>
        <script>
            <?= $pageScripts ?>
        </script>
    <?php endif; ?>

    <!-- Google Analytics -->
    <?php if ($gaId = $settings->get('google_analytics_id')): ?>
        <script async src="https://www.googletagmanager.com/gtag/js?id=<?= $gaId ?>"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '<?= $gaId ?>');
        </script>
    <?php endif; ?>

    <!-- Custom Page Footer Content -->
    <?php if (isset($pageFooter)) echo $pageFooter; ?>
</body>
</html>