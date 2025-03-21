<?php
require_once __DIR__ . '/../classes/Settings.php';
require_once __DIR__ . '/../classes/Cache.php';

class SocialWall {
    private $settings;
    private $cache;
    private $refreshInterval;
    private $cacheTime;
    private $itemsPerNetwork;

    public function __construct() {
        $this->settings = new Settings();
        $this->cache = new Cache();
        $this->refreshInterval = $this->settings->get('social_wall_refresh_interval', 300); // 5 minutes
        $this->cacheTime = $this->settings->get('social_wall_cache_time', 3600); // 1 hour
        $this->itemsPerNetwork = $this->settings->get('social_wall_items_per_network', 5);
    }

    /**
     * Get combined social media feed
     */
    public function getFeed() {
        $feed = [];

        // Try to get from cache first
        $cachedFeed = $this->cache->get('social_wall_feed');
        if ($cachedFeed) {
            return $cachedFeed;
        }

        // Fetch from each network
        if ($this->settings->get('facebook_api_key')) {
            $feed = array_merge($feed, $this->getFacebookPosts());
        }
        if ($this->settings->get('twitter_api_key')) {
            $feed = array_merge($feed, $this->getTwitterPosts());
        }
        if ($this->settings->get('instagram_api_key')) {
            $feed = array_merge($feed, $this->getInstagramPosts());
        }

        // Sort by date
        usort($feed, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });

        // Cache the result
        $this->cache->set('social_wall_feed', $feed, $this->cacheTime);

        return $feed;
    }

    /**
     * Render the social wall
     */
    public function render() {
        $feed = $this->getFeed();
        ?>
        <div class="bg-white py-12">
            <div class="container mx-auto px-4">
                <h2 class="text-3xl font-bold text-center mb-8">Social Media Feed</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($feed as $item): ?>
                        <div class="bg-white rounded-lg shadow-lg overflow-hidden border border-gray-200">
                            <?php if (isset($item['image'])): ?>
                                <img src="<?= htmlspecialchars($item['image']) ?>" 
                                     alt="Social media post" 
                                     class="w-full h-48 object-cover">
                            <?php endif; ?>
                            
                            <div class="p-6">
                                <div class="flex items-center mb-4">
                                    <div class="w-10 h-10 rounded-full overflow-hidden mr-4">
                                        <img src="<?= htmlspecialchars($item['profile_image']) ?>" 
                                             alt="<?= htmlspecialchars($item['username']) ?>"
                                             class="w-full h-full object-cover">
                                    </div>
                                    <div>
                                        <div class="font-semibold"><?= htmlspecialchars($item['username']) ?></div>
                                        <div class="text-sm text-gray-500">
                                            <?= $this->getTimeAgo($item['date']) ?>
                                            <i class="fab fa-<?= $item['network'] ?> ml-2"></i>
                                        </div>
                                    </div>
                                </div>

                                <p class="text-gray-700 mb-4"><?= $this->formatText($item['text']) ?></p>

                                <div class="flex items-center text-gray-500 text-sm">
                                    <?php if (isset($item['likes'])): ?>
                                        <span class="mr-4">
                                            <i class="far fa-heart mr-1"></i>
                                            <?= number_format($item['likes']) ?>
                                        </span>
                                    <?php endif; ?>
                                    
                                    <?php if (isset($item['comments'])): ?>
                                        <span class="mr-4">
                                            <i class="far fa-comment mr-1"></i>
                                            <?= number_format($item['comments']) ?>
                                        </span>
                                    <?php endif; ?>
                                    
                                    <?php if (isset($item['shares'])): ?>
                                        <span>
                                            <i class="far fa-share-square mr-1"></i>
                                            <?= number_format($item['shares']) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <?php if (isset($item['link'])): ?>
                                    <a href="<?= htmlspecialchars($item['link']) ?>" 
                                       target="_blank" 
                                       class="inline-block mt-4 text-blue-600 hover:text-blue-800">
                                        View Post <i class="fas fa-external-link-alt ml-1"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="text-center mt-8">
                    <button onclick="refreshSocialWall()" 
                            class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <i class="fas fa-sync-alt mr-2"></i>Refresh Feed
                    </button>
                </div>
            </div>
        </div>

        <script>
            let lastRefresh = <?= time() ?>;
            const refreshInterval = <?= $this->refreshInterval ?>;

            function refreshSocialWall() {
                const now = Math.floor(Date.now() / 1000);
                if (now - lastRefresh < refreshInterval) {
                    alert(`Please wait ${refreshInterval - (now - lastRefresh)} seconds before refreshing again.`);
                    return;
                }

                fetch('/api/social-wall/refresh', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Failed to refresh feed. Please try again later.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while refreshing the feed.');
                });
            }
        </script>
        <?php
    }

    /**
     * Format text with links and hashtags
     */
    private function formatText($text) {
        // Convert URLs to links
        $text = preg_replace(
            '/(https?:\/\/[^\s]+)/',
            '<a href="$1" target="_blank" class="text-blue-600 hover:text-blue-800">$1</a>',
            $text
        );

        // Convert @mentions to links
        $text = preg_replace(
            '/@(\w+)/',
            '<a href="https://twitter.com/$1" target="_blank" class="text-blue-600 hover:text-blue-800">@$1</a>',
            $text
        );

        // Convert #hashtags to links
        $text = preg_replace(
            '/#(\w+)/',
            '<a href="https://twitter.com/hashtag/$1" target="_blank" class="text-blue-600 hover:text-blue-800">#$1</a>',
            $text
        );

        return $text;
    }

    /**
     * Get time ago string
     */
    private function getTimeAgo($date) {
        $timestamp = strtotime($date);
        $now = time();
        $diff = $now - $timestamp;

        if ($diff < 60) {
            return 'just now';
        } elseif ($diff < 3600) {
            $mins = floor($diff / 60);
            return $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
        } else {
            return date('M j, Y', $timestamp);
        }
    }

    /**
     * Get Facebook posts
     */
    private function getFacebookPosts() {
        // Implementation would use Facebook Graph API
        return [];
    }

    /**
     * Get Twitter posts
     */
    private function getTwitterPosts() {
        // Implementation would use Twitter API v2
        return [];
    }

    /**
     * Get Instagram posts
     */
    private function getInstagramPosts() {
        // Implementation would use Instagram Graph API
        return [];
    }
}

// Usage example:
if (!isset($hideRender)) {
    $socialWall = new SocialWall();
    $socialWall->render();
}