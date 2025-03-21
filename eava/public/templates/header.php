<?php
require_once __DIR__ . '/../../classes/Settings.php';
$settings = new Settings();
?>
<header class="relative">
    <!-- Top Bar -->
    <div class="bg-gray-900 text-white py-2">
        <div class="container mx-auto px-4 flex justify-between items-center text-sm">
            <div class="flex items-center space-x-4">
                <?php if ($phone = $settings->get('site_phone')): ?>
                    <a href="tel:<?= htmlspecialchars($phone) ?>" class="hover:text-gray-300">
                        <i class="fas fa-phone mr-1"></i>
                        <?= htmlspecialchars($phone) ?>
                    </a>
                <?php endif; ?>
                
                <?php if ($email = $settings->get('site_email')): ?>
                    <a href="mailto:<?= htmlspecialchars($email) ?>" class="hover:text-gray-300">
                        <i class="fas fa-envelope mr-1"></i>
                        <?= htmlspecialchars($email) ?>
                    </a>
                <?php endif; ?>
            </div>
            
            <div class="flex items-center space-x-4">
                <?php if ($facebook = $settings->get('facebook_url')): ?>
                    <a href="<?= htmlspecialchars($facebook) ?>" target="_blank" class="hover:text-gray-300">
                        <i class="fab fa-facebook"></i>
                    </a>
                <?php endif; ?>
                
                <?php if ($twitter = $settings->get('twitter_url')): ?>
                    <a href="<?= htmlspecialchars($twitter) ?>" target="_blank" class="hover:text-gray-300">
                        <i class="fab fa-twitter"></i>
                    </a>
                <?php endif; ?>
                
                <?php if ($instagram = $settings->get('instagram_url')): ?>
                    <a href="<?= htmlspecialchars($instagram) ?>" target="_blank" class="hover:text-gray-300">
                        <i class="fab fa-instagram"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Main Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center h-20">
                <!-- Logo -->
                <a href="/" class="flex items-center">
                    <?php if ($logo = $settings->get('site_logo')): ?>
                        <img src="<?= htmlspecialchars($logo) ?>" 
                             alt="<?= htmlspecialchars($settings->get('site_name')) ?>" 
                             class="h-12">
                    <?php else: ?>
                        <span class="text-2xl font-bold text-gray-900">
                            <?= htmlspecialchars($settings->get('site_name')) ?>
                        </span>
                    <?php endif; ?>
                </a>

                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="/about" class="nav-link text-gray-700 hover:text-blue-600">About</a>
                    <a href="/programs" class="nav-link text-gray-700 hover:text-blue-600">Programs</a>
                    <a href="/events" class="nav-link text-gray-700 hover:text-blue-600">Events</a>
                    <a href="/resources" class="nav-link text-gray-700 hover:text-blue-600">Resources</a>
                    <a href="/contact" class="nav-link text-gray-700 hover:text-blue-600">Contact</a>
                    <a href="/donate" class="btn btn-primary">Donate Now</a>
                </div>

                <!-- Mobile Menu Button -->
                <button type="button" 
                        class="md:hidden text-gray-500 hover:text-gray-600 focus:outline-none focus:text-gray-600"
                        aria-label="Toggle menu"
                        onclick="toggleMobileMenu()">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" 
                              stroke-linejoin="round" 
                              stroke-width="2" 
                              d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Navigation -->
        <div id="mobileMenu" 
             class="hidden md:hidden bg-white border-t border-gray-200 absolute w-full z-50">
            <div class="container mx-auto px-4 py-4 space-y-4">
                <a href="/about" class="block text-gray-700 hover:text-blue-600">About</a>
                <a href="/programs" class="block text-gray-700 hover:text-blue-600">Programs</a>
                <a href="/events" class="block text-gray-700 hover:text-blue-600">Events</a>
                <a href="/resources" class="block text-gray-700 hover:text-blue-600">Resources</a>
                <a href="/contact" class="block text-gray-700 hover:text-blue-600">Contact</a>
                <a href="/donate" class="btn btn-primary w-full text-center">Donate Now</a>
            </div>
        </div>
    </nav>
</header>

<script>
    function toggleMobileMenu() {
        const menu = document.getElementById('mobileMenu');
        menu.classList.toggle('hidden');
    }

    // Close mobile menu on window resize
    window.addEventListener('resize', () => {
        if (window.innerWidth >= 768) {
            document.getElementById('mobileMenu').classList.add('hidden');
        }
    });

    // Handle navigation highlighting
    document.addEventListener('DOMContentLoaded', () => {
        const currentPath = window.location.pathname;
        document.querySelectorAll('.nav-link').forEach(link => {
            if (link.getAttribute('href') === currentPath) {
                link.classList.add('text-blue-600');
            }
        });
    });
</script>