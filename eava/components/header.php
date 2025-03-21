<?php
require_once __DIR__ . '/../classes/Session.php';
require_once __DIR__ . '/../classes/Settings.php';

$session = Session::getInstance();
$settings = new Settings();
$user = $session->getUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $settings->get('site_name', 'EAVA') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body class="font-sans">
    <!-- Top Bar -->
    <div class="bg-blue-900 text-white py-2">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4 text-sm">
                    <a href="mailto:<?= $settings->get('site_email') ?>" class="hover:text-blue-200">
                        <i class="fas fa-envelope mr-1"></i><?= $settings->get('site_email') ?>
                    </a>
                    <span class="hidden md:inline">|</span>
                    <a href="tel:<?= $settings->get('site_phone') ?>" class="hidden md:inline hover:text-blue-200">
                        <i class="fas fa-phone mr-1"></i><?= $settings->get('site_phone') ?>
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <?php if ($settings->get('facebook_url')): ?>
                        <a href="<?= $settings->get('facebook_url') ?>" target="_blank" class="hover:text-blue-200">
                            <i class="fab fa-facebook"></i>
                        </a>
                    <?php endif; ?>
                    <?php if ($settings->get('twitter_url')): ?>
                        <a href="<?= $settings->get('twitter_url') ?>" target="_blank" class="hover:text-blue-200">
                            <i class="fab fa-twitter"></i>
                        </a>
                    <?php endif; ?>
                    <?php if ($settings->get('instagram_url')): ?>
                        <a href="<?= $settings->get('instagram_url') ?>" target="_blank" class="hover:text-blue-200">
                            <i class="fab fa-instagram"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Navigation -->
    <header class="bg-white shadow-md">
        <nav class="container mx-auto px-4">
            <div class="flex justify-between items-center h-20">
                <!-- Logo -->
                <a href="/" class="flex items-center">
                    <img src="/assets/images/logo.png" alt="EAVA" class="h-12">
                </a>

                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="/about" class="text-gray-700 hover:text-blue-600">About</a>
                    <div class="relative group">
                        <button class="text-gray-700 hover:text-blue-600 flex items-center">
                            Programs <i class="fas fa-chevron-down ml-1 text-xs"></i>
                        </button>
                        <div class="absolute left-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 hidden group-hover:block">
                            <a href="/programs" class="block px-4 py-2 text-gray-700 hover:bg-blue-50">All Programs</a>
                            <a href="/events" class="block px-4 py-2 text-gray-700 hover:bg-blue-50">Events</a>
                            <a href="/projects" class="block px-4 py-2 text-gray-700 hover:bg-blue-50">Projects</a>
                            <a href="/festival" class="block px-4 py-2 text-gray-700 hover:bg-blue-50">Festival</a>
                        </div>
                    </div>
                    <div class="relative group">
                        <button class="text-gray-700 hover:text-blue-600 flex items-center">
                            Support <i class="fas fa-chevron-down ml-1 text-xs"></i>
                        </button>
                        <div class="absolute left-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 hidden group-hover:block">
                            <a href="/donate" class="block px-4 py-2 text-gray-700 hover:bg-blue-50">Donate</a>
                            <a href="/campaigns" class="block px-4 py-2 text-gray-700 hover:bg-blue-50">Campaigns</a>
                            <a href="/grants" class="block px-4 py-2 text-gray-700 hover:bg-blue-50">Grants</a>
                        </div>
                    </div>
                    <a href="/blog" class="text-gray-700 hover:text-blue-600">Blog</a>
                    <a href="/contact" class="text-gray-700 hover:text-blue-600">Contact</a>
                </div>

                <!-- User Menu / CTA -->
                <div class="flex items-center space-x-4">
                    <?php if ($user): ?>
                        <div class="relative group">
                            <button class="flex items-center space-x-2 text-gray-700 hover:text-blue-600">
                                <img src="<?= Utility::getGravatar($user['email'], 32) ?>" 
                                     alt="<?= htmlspecialchars($user['full_name']) ?>" 
                                     class="w-8 h-8 rounded-full">
                                <span class="hidden md:inline"><?= htmlspecialchars($user['full_name']) ?></span>
                                <i class="fas fa-chevron-down text-xs"></i>
                            </button>
                            <div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 hidden group-hover:block">
                                <?php if ($user['role'] === 'admin'): ?>
                                    <a href="/admin/dashboard" class="block px-4 py-2 text-gray-700 hover:bg-blue-50">
                                        <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                                    </a>
                                <?php endif; ?>
                                <a href="/profile" class="block px-4 py-2 text-gray-700 hover:bg-blue-50">
                                    <i class="fas fa-user mr-2"></i>Profile
                                </a>
                                <a href="/settings" class="block px-4 py-2 text-gray-700 hover:bg-blue-50">
                                    <i class="fas fa-cog mr-2"></i>Settings
                                </a>
                                <div class="border-t border-gray-100 my-2"></div>
                                <a href="/logout" class="block px-4 py-2 text-red-600 hover:bg-red-50">
                                    <i class="fas fa-sign-out-alt mr-2"></i>Logout
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="/login" class="text-gray-700 hover:text-blue-600">Login</a>
                        <a href="/donate" class="bg-blue-600 text-white px-6 py-2 rounded-full hover:bg-blue-700">
                            Donate Now
                        </a>
                    <?php endif; ?>

                    <!-- Mobile Menu Button -->
                    <button class="md:hidden text-gray-700 hover:text-blue-600" onclick="toggleMobileMenu()">
                        <i class="fas fa-bars text-2xl"></i>
                    </button>
                </div>
            </div>

            <!-- Mobile Navigation -->
            <div id="mobileMenu" class="md:hidden hidden pb-6">
                <a href="/about" class="block py-2 text-gray-700 hover:text-blue-600">About</a>
                <div class="py-2">
                    <button onclick="toggleMobileSubmenu('programsSubmenu')" 
                            class="flex items-center justify-between w-full text-gray-700 hover:text-blue-600">
                        <span>Programs</span>
                        <i class="fas fa-chevron-down text-xs"></i>
                    </button>
                    <div id="programsSubmenu" class="hidden pl-4 mt-2 space-y-2">
                        <a href="/programs" class="block text-gray-700 hover:text-blue-600">All Programs</a>
                        <a href="/events" class="block text-gray-700 hover:text-blue-600">Events</a>
                        <a href="/projects" class="block text-gray-700 hover:text-blue-600">Projects</a>
                        <a href="/festival" class="block text-gray-700 hover:text-blue-600">Festival</a>
                    </div>
                </div>
                <div class="py-2">
                    <button onclick="toggleMobileSubmenu('supportSubmenu')" 
                            class="flex items-center justify-between w-full text-gray-700 hover:text-blue-600">
                        <span>Support</span>
                        <i class="fas fa-chevron-down text-xs"></i>
                    </button>
                    <div id="supportSubmenu" class="hidden pl-4 mt-2 space-y-2">
                        <a href="/donate" class="block text-gray-700 hover:text-blue-600">Donate</a>
                        <a href="/campaigns" class="block text-gray-700 hover:text-blue-600">Campaigns</a>
                        <a href="/grants" class="block text-gray-700 hover:text-blue-600">Grants</a>
                    </div>
                </div>
                <a href="/blog" class="block py-2 text-gray-700 hover:text-blue-600">Blog</a>
                <a href="/contact" class="block py-2 text-gray-700 hover:text-blue-600">Contact</a>
            </div>
        </nav>
    </header>

    <script>
        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            menu.classList.toggle('hidden');
        }

        function toggleMobileSubmenu(id) {
            const submenu = document.getElementById(id);
            submenu.classList.toggle('hidden');
        }

        // Close mobile menu on window resize
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 768) {
                const menu = document.getElementById('mobileMenu');
                menu.classList.add('hidden');
            }
        });
    </script>
</body>
</html>