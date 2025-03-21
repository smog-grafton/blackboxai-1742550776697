<?php
require_once __DIR__ . '/../../classes/Settings.php';
$settings = new Settings();
?>

<section class="relative h-screen flex items-center justify-center overflow-hidden">
    <!-- Video Background -->
    <video autoplay muted loop playsinline class="absolute w-full h-full object-cover">
        <source src="<?= $settings->get('header_video') ?>" type="video/mp4">
    </video>

    <!-- Overlay -->
    <div class="absolute inset-0 bg-black" style="opacity: <?= $settings->get('header_overlay_opacity', 0.5) ?>;"></div>

    <!-- Content -->
    <div class="relative z-10 text-center text-white px-4 max-w-5xl mx-auto animate-fade-in">
        <h1 class="text-4xl md:text-6xl lg:text-7xl font-bold mb-6 leading-tight">
            Democracy and diversity are the cornerstone of our future
        </h1>
        <p class="text-xl md:text-2xl mb-12 max-w-3xl mx-auto">
            Join us in building a more inclusive and equitable society through art, education, and community engagement.
        </p>
        <div class="flex flex-col sm:flex-row items-center justify-center space-y-4 sm:space-y-0 sm:space-x-6">
            <a href="/donate" 
               class="btn btn-primary text-lg px-8 py-4 rounded-full transform hover:scale-105 transition-all duration-200 animate-slide-up"
               style="animation-delay: 0.2s;">
                <i class="fas fa-heart mr-2"></i>
                Donate Now
            </a>
            <a href="/programs" 
               class="btn btn-outline text-white border-2 border-white hover:bg-white hover:text-gray-900 text-lg px-8 py-4 rounded-full transform hover:scale-105 transition-all duration-200 animate-slide-up"
               style="animation-delay: 0.4s;">
                <i class="fas fa-info-circle mr-2"></i>
                Learn More
            </a>
        </div>
    </div>

    <!-- Scroll Indicator -->
    <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 animate-bounce">
        <a href="#content" 
           class="text-white opacity-75 hover:opacity-100 transition-opacity duration-200">
            <i class="fas fa-chevron-down text-3xl"></i>
            <span class="sr-only">Scroll down</span>
        </a>
    </div>

    <!-- Social Links -->
    <div class="absolute bottom-8 right-8 flex flex-col space-y-4">
        <?php if ($facebook = $settings->get('facebook_url')): ?>
            <a href="<?= htmlspecialchars($facebook) ?>" 
               target="_blank"
               class="text-white opacity-75 hover:opacity-100 transition-opacity duration-200 transform hover:scale-110">
                <i class="fab fa-facebook text-2xl"></i>
                <span class="sr-only">Facebook</span>
            </a>
        <?php endif; ?>

        <?php if ($twitter = $settings->get('twitter_url')): ?>
            <a href="<?= htmlspecialchars($twitter) ?>" 
               target="_blank"
               class="text-white opacity-75 hover:opacity-100 transition-opacity duration-200 transform hover:scale-110">
                <i class="fab fa-twitter text-2xl"></i>
                <span class="sr-only">Twitter</span>
            </a>
        <?php endif; ?>

        <?php if ($instagram = $settings->get('instagram_url')): ?>
            <a href="<?= htmlspecialchars($instagram) ?>" 
               target="_blank"
               class="text-white opacity-75 hover:opacity-100 transition-opacity duration-200 transform hover:scale-110">
                <i class="fab fa-instagram text-2xl"></i>
                <span class="sr-only">Instagram</span>
            </a>
        <?php endif; ?>

        <?php if ($youtube = $settings->get('youtube_url')): ?>
            <a href="<?= htmlspecialchars($youtube) ?>" 
               target="_blank"
               class="text-white opacity-75 hover:opacity-100 transition-opacity duration-200 transform hover:scale-110">
                <i class="fab fa-youtube text-2xl"></i>
                <span class="sr-only">YouTube</span>
            </a>
        <?php endif; ?>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Parallax effect on scroll
    window.addEventListener('scroll', () => {
        const scroll = window.pageYOffset;
        const video = document.querySelector('video');
        const overlay = document.querySelector('.absolute.inset-0.bg-black');
        
        // Move video up slightly on scroll
        if (video) {
            video.style.transform = `translateY(${scroll * 0.5}px)`;
        }

        // Adjust overlay opacity based on scroll
        if (overlay) {
            const baseOpacity = <?= $settings->get('header_overlay_opacity', 0.5) ?>;
            const scrollOpacity = Math.min(0.8, baseOpacity + (scroll / window.innerHeight * 0.3));
            overlay.style.opacity = scrollOpacity;
        }
    });

    // Smooth scroll for anchor links
    document.querySelector('a[href="#content"]').addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector('#content');
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });

    // Animate elements on scroll into view
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-fade-in');
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.1
    });

    document.querySelectorAll('.animate-on-scroll').forEach(el => observer.observe(el));
});
</script>