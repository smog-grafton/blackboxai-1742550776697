<?php
require_once __DIR__ . '/classes/Event.php';
require_once __DIR__ . '/classes/Campaign.php';
require_once __DIR__ . '/classes/Post.php';
require_once __DIR__ . '/components/social_wall.php';

$eventModel = new Event();
$campaignModel = new Campaign();
$postModel = new Post();
$socialWall = new SocialWall();

// Get featured content
$featuredEvents = $eventModel->getFeatured(3);
$featuredCampaigns = $campaignModel->getFeatured(3);
$latestPosts = $postModel->getLatest(3);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EAVA - Empowering Arts and Visual Achievement</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100">
    <!-- Hero Section -->
    <section class="relative h-screen bg-cover bg-center" style="background-image: url('/assets/images/hero-bg.jpg');">
        <div class="absolute inset-0 bg-black bg-opacity-50"></div>
        <div class="relative container mx-auto px-4 h-full flex items-center">
            <div class="text-white max-w-3xl">
                <h1 class="text-5xl md:text-6xl font-bold mb-6">Empowering Arts and Visual Achievement</h1>
                <p class="text-xl mb-8">Join our community of artists, creators, and visionaries. Together, we're shaping the future of visual arts.</p>
                <div class="flex flex-wrap gap-4">
                    <a href="/modules/programs/programs.php" 
                       class="bg-blue-500 text-white px-8 py-3 rounded-full text-lg hover:bg-blue-600 transition duration-300">
                        Explore Programs
                    </a>
                    <a href="/modules/donations/donations.php" 
                       class="bg-white text-blue-500 px-8 py-3 rounded-full text-lg hover:bg-gray-100 transition duration-300">
                        Support Us
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Events -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold mb-8">Upcoming Events</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <?php foreach ($featuredEvents as $event): ?>
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                        <?php if ($event['featured_image']): ?>
                            <img src="<?= $event['featured_image'] ?>" 
                                 alt="<?= htmlspecialchars($event['title']) ?>"
                                 class="w-full h-48 object-cover">
                        <?php endif; ?>
                        <div class="p-6">
                            <h3 class="text-xl font-semibold mb-2">
                                <a href="/event/<?= $event['slug'] ?>" class="text-blue-600 hover:text-blue-800">
                                    <?= htmlspecialchars($event['title']) ?>
                                </a>
                            </h3>
                            <p class="text-gray-600 mb-4"><?= htmlspecialchars($event['description']) ?></p>
                            <div class="flex items-center justify-between text-sm text-gray-500">
                                <span><i class="fas fa-calendar mr-2"></i><?= date('F j, Y', strtotime($event['start_date'])) ?></span>
                                <a href="/event/<?= $event['slug'] ?>" class="text-blue-500 hover:text-blue-700">
                                    Learn More <i class="fas fa-arrow-right ml-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Featured Campaigns -->
    <section class="py-16 bg-gray-100">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold mb-8">Current Campaigns</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <?php foreach ($featuredCampaigns as $campaign): ?>
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                        <?php if ($campaign['featured_image']): ?>
                            <img src="<?= $campaign['featured_image'] ?>" 
                                 alt="<?= htmlspecialchars($campaign['title']) ?>"
                                 class="w-full h-48 object-cover">
                        <?php endif; ?>
                        <div class="p-6">
                            <h3 class="text-xl font-semibold mb-2">
                                <a href="/campaign/<?= $campaign['slug'] ?>" class="text-blue-600 hover:text-blue-800">
                                    <?= htmlspecialchars($campaign['title']) ?>
                                </a>
                            </h3>
                            <div class="mb-4">
                                <div class="w-full bg-gray-200 rounded-full h-2.5">
                                    <div class="bg-blue-600 h-2.5 rounded-full" 
                                         style="width: <?= ($campaign['current_amount'] / $campaign['goal_amount']) * 100 ?>%">
                                    </div>
                                </div>
                                <div class="flex justify-between mt-2 text-sm text-gray-600">
                                    <span><?= Utility::formatCurrency($campaign['current_amount']) ?> raised</span>
                                    <span><?= round(($campaign['current_amount'] / $campaign['goal_amount']) * 100) ?>% of <?= Utility::formatCurrency($campaign['goal_amount']) ?></span>
                                </div>
                            </div>
                            <a href="/campaign/<?= $campaign['slug'] ?>" 
                               class="block w-full text-center bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600">
                                Support This Campaign
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Latest Blog Posts -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold mb-8">Latest News</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <?php foreach ($latestPosts as $post): ?>
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                        <?php if ($post['featured_image']): ?>
                            <img src="<?= $post['featured_image'] ?>" 
                                 alt="<?= htmlspecialchars($post['title']) ?>"
                                 class="w-full h-48 object-cover">
                        <?php endif; ?>
                        <div class="p-6">
                            <h3 class="text-xl font-semibold mb-2">
                                <a href="/post/<?= $post['slug'] ?>" class="text-blue-600 hover:text-blue-800">
                                    <?= htmlspecialchars($post['title']) ?>
                                </a>
                            </h3>
                            <p class="text-gray-600 mb-4"><?= htmlspecialchars($post['excerpt']) ?></p>
                            <div class="flex items-center justify-between text-sm text-gray-500">
                                <span><?= date('F j, Y', strtotime($post['created_at'])) ?></span>
                                <a href="/post/<?= $post['slug'] ?>" class="text-blue-500 hover:text-blue-700">
                                    Read More <i class="fas fa-arrow-right ml-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Social Media Wall -->
    <section class="py-16 bg-gray-100">
        <div class="container mx-auto px-4">
            <?php $socialWall->render(); ?>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="py-16 bg-blue-600 text-white">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl font-bold mb-4">Join Our Community</h2>
            <p class="text-xl mb-8">Be part of our growing community of artists and supporters.</p>
            <div class="flex justify-center gap-4">
                <a href="/register" class="bg-white text-blue-600 px-8 py-3 rounded-full text-lg hover:bg-gray-100">
                    Register Now
                </a>
                <a href="/contact" class="border-2 border-white text-white px-8 py-3 rounded-full text-lg hover:bg-blue-700">
                    Contact Us
                </a>
            </div>
        </div>
    </section>

    <footer class="bg-gray-800 text-white py-12">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-xl font-semibold mb-4">About EAVA</h3>
                    <p class="text-gray-400">Empowering Arts and Visual Achievement is dedicated to supporting and promoting visual arts in our community.</p>
                </div>
                <div>
                    <h3 class="text-xl font-semibold mb-4">Quick Links</h3>
                    <ul class="space-y-2">
                        <li><a href="/programs" class="text-gray-400 hover:text-white">Programs</a></li>
                        <li><a href="/events" class="text-gray-400 hover:text-white">Events</a></li>
                        <li><a href="/blog" class="text-gray-400 hover:text-white">Blog</a></li>
                        <li><a href="/contact" class="text-gray-400 hover:text-white">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-xl font-semibold mb-4">Connect</h3>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white text-2xl"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white text-2xl"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white text-2xl"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white text-2xl"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
                <div>
                    <h3 class="text-xl font-semibold mb-4">Newsletter</h3>
                    <form class="space-y-4">
                        <input type="email" placeholder="Your email" 
                               class="w-full px-4 py-2 rounded-lg bg-gray-700 text-white border-none focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <button type="submit" 
                                class="w-full bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
                            Subscribe
                        </button>
                    </form>
                </div>
            </div>
            <div class="mt-8 pt-8 border-t border-gray-700 text-center">
                <p class="text-gray-400">&copy; 2023 EAVA. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>