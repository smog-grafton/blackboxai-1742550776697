<?php
require_once __DIR__ . '/../../classes/Settings.php';
$settings = new Settings();

// Get social media feeds
$facebook_feed = json_decode(file_get_contents("https://graph.facebook.com/v12.0/{$settings->get('facebook_page_id')}/posts?access_token={$settings->get('facebook_access_token')}"), true);
$twitter_feed = json_decode(file_get_contents("https://api.twitter.com/2/users/{$settings->get('twitter_user_id')}/tweets"), true);
$instagram_feed = json_decode(file_get_contents("https://graph.instagram.com/me/media?access_token={$settings->get('instagram_access_token')}"), true);
?>

<div class="social-wall">
    <!-- Social Media Filters -->
    <div class="flex justify-center mb-8 space-x-4">
        <button onclick="filterSocialPosts('all')" 
                class="social-filter active px-6 py-2 rounded-full border-2 border-gray-300 hover:border-blue-500 transition-colors">
            All
        </button>
        <button onclick="filterSocialPosts('facebook')" 
                class="social-filter px-6 py-2 rounded-full border-2 border-gray-300 hover:border-blue-500 transition-colors">
            <i class="fab fa-facebook-f mr-2"></i>Facebook
        </button>
        <button onclick="filterSocialPosts('twitter')" 
                class="social-filter px-6 py-2 rounded-full border-2 border-gray-300 hover:border-blue-500 transition-colors">
            <i class="fab fa-twitter mr-2"></i>Twitter
        </button>
        <button onclick="filterSocialPosts('instagram')" 
                class="social-filter px-6 py-2 rounded-full border-2 border-gray-300 hover:border-blue-500 transition-colors">
            <i class="fab fa-instagram mr-2"></i>Instagram
        </button>
    </div>

    <!-- Social Posts Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Facebook Posts -->
        <?php foreach ($facebook_feed['data'] as $post): ?>
            <div class="social-post facebook animate-fade-in bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center mb-4">
                        <img src="<?= $settings->get('facebook_page_avatar') ?>" 
                             alt="Facebook Page" 
                             class="w-10 h-10 rounded-full">
                        <div class="ml-3">
                            <h4 class="font-semibold"><?= htmlspecialchars($settings->get('facebook_page_name')) ?></h4>
                            <p class="text-sm text-gray-500">
                                <?= Utility::timeAgo($post['created_time']) ?>
                            </p>
                        </div>
                        <a href="https://facebook.com/<?= $post['id'] ?>" 
                           target="_blank"
                           class="ml-auto text-blue-600 hover:text-blue-800">
                            <i class="fab fa-facebook"></i>
                        </a>
                    </div>
                    <?php if (isset($post['message'])): ?>
                        <p class="text-gray-700 mb-4">
                            <?= nl2br(htmlspecialchars($post['message'])) ?>
                        </p>
                    <?php endif; ?>
                    <?php if (isset($post['full_picture'])): ?>
                        <img src="<?= htmlspecialchars($post['full_picture']) ?>" 
                             alt="Post image"
                             class="w-full rounded-lg mb-4">
                    <?php endif; ?>
                    <div class="flex items-center text-gray-500 text-sm">
                        <span class="mr-4">
                            <i class="far fa-heart mr-1"></i>
                            <?= number_format($post['likes']['summary']['total_count']) ?>
                        </span>
                        <span>
                            <i class="far fa-comment mr-1"></i>
                            <?= number_format($post['comments']['summary']['total_count']) ?>
                        </span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- Twitter Posts -->
        <?php foreach ($twitter_feed['data'] as $tweet): ?>
            <div class="social-post twitter animate-fade-in bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center mb-4">
                        <img src="<?= $settings->get('twitter_profile_image') ?>" 
                             alt="Twitter Profile" 
                             class="w-10 h-10 rounded-full">
                        <div class="ml-3">
                            <h4 class="font-semibold"><?= htmlspecialchars($settings->get('twitter_name')) ?></h4>
                            <p class="text-sm text-gray-500">@<?= htmlspecialchars($settings->get('twitter_username')) ?></p>
                        </div>
                        <a href="https://twitter.com/user/status/<?= $tweet['id'] ?>" 
                           target="_blank"
                           class="ml-auto text-blue-400 hover:text-blue-600">
                            <i class="fab fa-twitter"></i>
                        </a>
                    </div>
                    <p class="text-gray-700 mb-4">
                        <?= nl2br(htmlspecialchars($tweet['text'])) ?>
                    </p>
                    <?php if (isset($tweet['entities']['media'])): ?>
                        <img src="<?= htmlspecialchars($tweet['entities']['media'][0]['media_url_https']) ?>" 
                             alt="Tweet image"
                             class="w-full rounded-lg mb-4">
                    <?php endif; ?>
                    <div class="flex items-center text-gray-500 text-sm">
                        <span class="mr-4">
                            <i class="far fa-heart mr-1"></i>
                            <?= number_format($tweet['public_metrics']['like_count']) ?>
                        </span>
                        <span class="mr-4">
                            <i class="fas fa-retweet mr-1"></i>
                            <?= number_format($tweet['public_metrics']['retweet_count']) ?>
                        </span>
                        <span>
                            <i class="far fa-comment mr-1"></i>
                            <?= number_format($tweet['public_metrics']['reply_count']) ?>
                        </span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- Instagram Posts -->
        <?php foreach ($instagram_feed['data'] as $post): ?>
            <div class="social-post instagram animate-fade-in bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center mb-4">
                        <img src="<?= $settings->get('instagram_profile_image') ?>" 
                             alt="Instagram Profile" 
                             class="w-10 h-10 rounded-full">
                        <div class="ml-3">
                            <h4 class="font-semibold"><?= htmlspecialchars($settings->get('instagram_username')) ?></h4>
                            <p class="text-sm text-gray-500">
                                <?= Utility::timeAgo($post['timestamp']) ?>
                            </p>
                        </div>
                        <a href="<?= $post['permalink'] ?>" 
                           target="_blank"
                           class="ml-auto text-pink-600 hover:text-pink-800">
                            <i class="fab fa-instagram"></i>
                        </a>
                    </div>
                    <?php if ($post['media_type'] === 'IMAGE' || $post['media_type'] === 'CAROUSEL_ALBUM'): ?>
                        <img src="<?= htmlspecialchars($post['media_url']) ?>" 
                             alt="Instagram post"
                             class="w-full rounded-lg mb-4">
                    <?php endif; ?>
                    <?php if (isset($post['caption'])): ?>
                        <p class="text-gray-700 mb-4">
                            <?= nl2br(htmlspecialchars($post['caption'])) ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Load More Button -->
    <div class="text-center mt-8">
        <button onclick="loadMorePosts()" 
                class="px-6 py-2 bg-blue-600 text-white rounded-full hover:bg-blue-700 transition-colors">
            Load More
        </button>
    </div>
</div>

<script>
    let currentFilter = 'all';
    let page = 1;
    const postsPerPage = 9;

    function filterSocialPosts(filter) {
        currentFilter = filter;
        page = 1;

        // Update filter buttons
        document.querySelectorAll('.social-filter').forEach(button => {
            button.classList.remove('active', 'border-blue-500', 'text-blue-600');
            if (button.textContent.toLowerCase().includes(filter)) {
                button.classList.add('active', 'border-blue-500', 'text-blue-600');
            }
        });

        // Show/hide posts based on filter
        document.querySelectorAll('.social-post').forEach(post => {
            if (filter === 'all' || post.classList.contains(filter)) {
                post.style.display = '';
            } else {
                post.style.display = 'none';
            }
        });

        // Reset masonry layout
        if (typeof Masonry !== 'undefined') {
            const grid = document.querySelector('.social-wall');
            const masonry = new Masonry(grid, {
                itemSelector: '.social-post',
                columnWidth: '.social-post',
                percentPosition: true
            });
        }
    }

    async function loadMorePosts() {
        const button = event.target;
        const originalText = button.textContent;
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';

        try {
            const response = await fetch(`/api/social-posts?page=${++page}&filter=${currentFilter}`);
            const data = await response.json();

            if (data.posts.length > 0) {
                const container = document.querySelector('.social-wall .grid');
                data.posts.forEach(post => {
                    const element = createSocialPost(post);
                    container.appendChild(element);
                });

                // Update masonry layout
                if (typeof Masonry !== 'undefined') {
                    const masonry = new Masonry(container, {
                        itemSelector: '.social-post',
                        columnWidth: '.social-post',
                        percentPosition: true
                    });
                }
            } else {
                button.style.display = 'none';
            }
        } catch (error) {
            console.error('Failed to load more posts:', error);
            showNotification('error', 'Failed to load more posts. Please try again.');
        } finally {
            button.disabled = false;
            button.textContent = originalText;
        }
    }

    function createSocialPost(post) {
        // Create post element based on platform
        // Implementation depends on post data structure
    }

    // Initialize masonry layout
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof Masonry !== 'undefined') {
            const grid = document.querySelector('.social-wall .grid');
            const masonry = new Masonry(grid, {
                itemSelector: '.social-post',
                columnWidth: '.social-post',
                percentPosition: true
            });
        }
    });
</script>