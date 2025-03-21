<?php
require_once __DIR__ . '/../classes/Settings.php';
$settings = new Settings();
?>
<footer class="bg-gray-900 text-white pt-16 pb-8">
    <div class="container mx-auto px-4">
        <!-- Footer Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-12">
            <!-- About Section -->
            <div>
                <h3 class="text-xl font-bold mb-4">About EAVA</h3>
                <p class="text-gray-400 mb-4">
                    <?= $settings->get('site_description', 'Empowering artists and fostering creativity through education, support, and community engagement.') ?>
                </p>
                <div class="flex space-x-4">
                    <?php if ($settings->get('facebook_url')): ?>
                        <a href="<?= $settings->get('facebook_url') ?>" target="_blank" class="text-gray-400 hover:text-white">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                    <?php endif; ?>
                    <?php if ($settings->get('twitter_url')): ?>
                        <a href="<?= $settings->get('twitter_url') ?>" target="_blank" class="text-gray-400 hover:text-white">
                            <i class="fab fa-twitter"></i>
                        </a>
                    <?php endif; ?>
                    <?php if ($settings->get('instagram_url')): ?>
                        <a href="<?= $settings->get('instagram_url') ?>" target="_blank" class="text-gray-400 hover:text-white">
                            <i class="fab fa-instagram"></i>
                        </a>
                    <?php endif; ?>
                    <?php if ($settings->get('linkedin_url')): ?>
                        <a href="<?= $settings->get('linkedin_url') ?>" target="_blank" class="text-gray-400 hover:text-white">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Links -->
            <div>
                <h3 class="text-xl font-bold mb-4">Quick Links</h3>
                <ul class="space-y-2">
                    <li>
                        <a href="/about" class="text-gray-400 hover:text-white">About Us</a>
                    </li>
                    <li>
                        <a href="/programs" class="text-gray-400 hover:text-white">Programs</a>
                    </li>
                    <li>
                        <a href="/events" class="text-gray-400 hover:text-white">Events</a>
                    </li>
                    <li>
                        <a href="/projects" class="text-gray-400 hover:text-white">Projects</a>
                    </li>
                    <li>
                        <a href="/blog" class="text-gray-400 hover:text-white">Blog</a>
                    </li>
                    <li>
                        <a href="/contact" class="text-gray-400 hover:text-white">Contact</a>
                    </li>
                </ul>
            </div>

            <!-- Support -->
            <div>
                <h3 class="text-xl font-bold mb-4">Support</h3>
                <ul class="space-y-2">
                    <li>
                        <a href="/donate" class="text-gray-400 hover:text-white">Donate</a>
                    </li>
                    <li>
                        <a href="/campaigns" class="text-gray-400 hover:text-white">Campaigns</a>
                    </li>
                    <li>
                        <a href="/grants" class="text-gray-400 hover:text-white">Grants</a>
                    </li>
                    <li>
                        <a href="/volunteer" class="text-gray-400 hover:text-white">Volunteer</a>
                    </li>
                    <li>
                        <a href="/sponsors" class="text-gray-400 hover:text-white">Sponsors</a>
                    </li>
                </ul>
            </div>

            <!-- Contact Info -->
            <div>
                <h3 class="text-xl font-bold mb-4">Contact Us</h3>
                <ul class="space-y-2">
                    <li class="flex items-start">
                        <i class="fas fa-map-marker-alt mt-1.5 mr-3 text-gray-400"></i>
                        <span class="text-gray-400">
                            <?= nl2br(htmlspecialchars($settings->get('site_address', ''))) ?>
                        </span>
                    </li>
                    <li>
                        <a href="tel:<?= $settings->get('site_phone') ?>" class="flex items-center text-gray-400 hover:text-white">
                            <i class="fas fa-phone mr-3"></i>
                            <?= $settings->get('site_phone') ?>
                        </a>
                    </li>
                    <li>
                        <a href="mailto:<?= $settings->get('site_email') ?>" class="flex items-center text-gray-400 hover:text-white">
                            <i class="fas fa-envelope mr-3"></i>
                            <?= $settings->get('site_email') ?>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Newsletter -->
        <div class="border-t border-gray-800 pt-8 pb-12">
            <div class="max-w-xl mx-auto text-center">
                <h3 class="text-xl font-bold mb-2">Subscribe to Our Newsletter</h3>
                <p class="text-gray-400 mb-4">Stay updated with our latest news and events</p>
                <form action="/subscribe" method="POST" class="flex gap-2">
                    <input type="email" 
                           name="email" 
                           placeholder="Enter your email" 
                           class="flex-1 px-4 py-2 rounded-lg bg-gray-800 text-white border border-gray-700 focus:outline-none focus:border-blue-500"
                           required>
                    <button type="submit" 
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Subscribe
                    </button>
                </form>
            </div>
        </div>

        <!-- Bottom Bar -->
        <div class="border-t border-gray-800 pt-8">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="text-gray-400 text-sm mb-4 md:mb-0">
                    &copy; <?= date('Y') ?> <?= $settings->get('site_name', 'EAVA') ?>. All rights reserved.
                </div>
                <div class="flex space-x-6 text-sm">
                    <a href="/privacy-policy" class="text-gray-400 hover:text-white">Privacy Policy</a>
                    <a href="/terms-of-service" class="text-gray-400 hover:text-white">Terms of Service</a>
                    <a href="/sitemap" class="text-gray-400 hover:text-white">Sitemap</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Back to Top Button -->
    <button id="backToTop" 
            class="fixed bottom-8 right-8 bg-blue-600 text-white w-10 h-10 rounded-full hidden items-center justify-center hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
        <i class="fas fa-arrow-up"></i>
    </button>

    <script>
        // Back to Top Button
        const backToTopButton = document.getElementById('backToTop');

        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 300) {
                backToTopButton.classList.remove('hidden');
                backToTopButton.classList.add('flex');
            } else {
                backToTopButton.classList.remove('flex');
                backToTopButton.classList.add('hidden');
            }
        });

        backToTopButton.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });

        // Newsletter Form
        const newsletterForm = document.querySelector('form');
        newsletterForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const email = e.target.email.value;
            
            try {
                const response = await fetch('/api/subscribe', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ email })
                });

                if (response.ok) {
                    alert('Thank you for subscribing!');
                    e.target.reset();
                } else {
                    throw new Error('Failed to subscribe');
                }
            } catch (error) {
                alert('Sorry, there was an error. Please try again later.');
            }
        });
    </script>
</footer>