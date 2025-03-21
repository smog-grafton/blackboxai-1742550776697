<?php
require_once __DIR__ . '/../../classes/Settings.php';
$settings = new Settings();
?>
<footer class="bg-gray-900 text-white pt-16 pb-8">
    <div class="container mx-auto px-4">
        <!-- Main Footer -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-12">
            <!-- About Section -->
            <div>
                <h3 class="text-xl font-bold mb-4">About Us</h3>
                <p class="text-gray-400 mb-4">
                    <?= htmlspecialchars($settings->get('site_description')) ?>
                </p>
                <div class="flex space-x-4">
                    <?php if ($facebook = $settings->get('facebook_url')): ?>
                        <a href="<?= htmlspecialchars($facebook) ?>" 
                           target="_blank"
                           class="text-gray-400 hover:text-white transition-colors">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($twitter = $settings->get('twitter_url')): ?>
                        <a href="<?= htmlspecialchars($twitter) ?>" 
                           target="_blank"
                           class="text-gray-400 hover:text-white transition-colors">
                            <i class="fab fa-twitter"></i>
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($instagram = $settings->get('instagram_url')): ?>
                        <a href="<?= htmlspecialchars($instagram) ?>" 
                           target="_blank"
                           class="text-gray-400 hover:text-white transition-colors">
                            <i class="fab fa-instagram"></i>
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($youtube = $settings->get('youtube_url')): ?>
                        <a href="<?= htmlspecialchars($youtube) ?>" 
                           target="_blank"
                           class="text-gray-400 hover:text-white transition-colors">
                            <i class="fab fa-youtube"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Links -->
            <div>
                <h3 class="text-xl font-bold mb-4">Quick Links</h3>
                <ul class="space-y-2">
                    <li>
                        <a href="/about" class="text-gray-400 hover:text-white transition-colors">
                            About Us
                        </a>
                    </li>
                    <li>
                        <a href="/programs" class="text-gray-400 hover:text-white transition-colors">
                            Programs
                        </a>
                    </li>
                    <li>
                        <a href="/events" class="text-gray-400 hover:text-white transition-colors">
                            Events
                        </a>
                    </li>
                    <li>
                        <a href="/resources" class="text-gray-400 hover:text-white transition-colors">
                            Resources
                        </a>
                    </li>
                    <li>
                        <a href="/contact" class="text-gray-400 hover:text-white transition-colors">
                            Contact Us
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Contact Info -->
            <div>
                <h3 class="text-xl font-bold mb-4">Contact Us</h3>
                <ul class="space-y-2">
                    <?php if ($address = $settings->get('site_address')): ?>
                        <li class="flex items-start">
                            <i class="fas fa-map-marker-alt mt-1.5 mr-3 text-gray-400"></i>
                            <span class="text-gray-400">
                                <?= nl2br(htmlspecialchars($address)) ?>
                            </span>
                        </li>
                    <?php endif; ?>
                    
                    <?php if ($phone = $settings->get('site_phone')): ?>
                        <li>
                            <a href="tel:<?= htmlspecialchars($phone) ?>" 
                               class="flex items-center text-gray-400 hover:text-white transition-colors">
                                <i class="fas fa-phone mr-3"></i>
                                <?= htmlspecialchars($phone) ?>
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php if ($email = $settings->get('site_email')): ?>
                        <li>
                            <a href="mailto:<?= htmlspecialchars($email) ?>" 
                               class="flex items-center text-gray-400 hover:text-white transition-colors">
                                <i class="fas fa-envelope mr-3"></i>
                                <?= htmlspecialchars($email) ?>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- Newsletter -->
            <div>
                <h3 class="text-xl font-bold mb-4">Newsletter</h3>
                <p class="text-gray-400 mb-4">
                    Subscribe to our newsletter to receive updates about our programs and events.
                </p>
                <form action="/api/newsletter/subscribe" method="POST" class="space-y-4">
                    <div>
                        <input type="email" 
                               name="email" 
                               required
                               placeholder="Enter your email"
                               class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg focus:outline-none focus:border-blue-500 text-white placeholder-gray-500">
                    </div>
                    <button type="submit" 
                            class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        Subscribe
                    </button>
                </form>
            </div>
        </div>

        <!-- Bottom Footer -->
        <div class="border-t border-gray-800 pt-8">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="text-gray-400 text-sm mb-4 md:mb-0">
                    &copy; <?= date('Y') ?> <?= htmlspecialchars($settings->get('site_name')) ?>. 
                    All rights reserved.
                </div>
                <div class="flex space-x-4 text-sm">
                    <a href="/privacy-policy" class="text-gray-400 hover:text-white transition-colors">
                        Privacy Policy
                    </a>
                    <a href="/terms-of-service" class="text-gray-400 hover:text-white transition-colors">
                        Terms of Service
                    </a>
                    <a href="/sitemap" class="text-gray-400 hover:text-white transition-colors">
                        Sitemap
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Back to Top Button -->
    <button id="backToTop" 
            onclick="scrollToTop()"
            class="fixed bottom-8 right-8 bg-blue-600 text-white rounded-full p-3 hidden shadow-lg hover:bg-blue-700 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
        <i class="fas fa-arrow-up"></i>
    </button>
</footer>

<script>
    // Newsletter Form
    document.querySelector('form').addEventListener('submit', async function(e) {
        e.preventDefault();
        const form = this;
        const button = form.querySelector('button');
        const originalText = button.textContent;
        
        try {
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Subscribing...';
            
            const response = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const data = await response.json();
            
            if (response.ok) {
                showNotification('success', data.message || 'Successfully subscribed to newsletter!');
                form.reset();
            } else {
                throw new Error(data.message || 'Failed to subscribe. Please try again.');
            }
        } catch (error) {
            showNotification('error', error.message);
        } finally {
            button.disabled = false;
            button.textContent = originalText;
        }
    });

    // Back to Top Button
    const backToTop = document.getElementById('backToTop');
    
    window.addEventListener('scroll', () => {
        if (window.pageYOffset > 300) {
            backToTop.classList.remove('hidden');
        } else {
            backToTop.classList.add('hidden');
        }
    });

    function scrollToTop() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }

    // Show notification
    function showNotification(type, message) {
        const notification = document.createElement('div');
        notification.className = `fixed bottom-4 right-4 bg-${type === 'success' ? 'green' : 'red'}-600 text-white px-6 py-3 rounded-lg shadow-lg z-50`;
        notification.innerHTML = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 5000);
    }
</script>