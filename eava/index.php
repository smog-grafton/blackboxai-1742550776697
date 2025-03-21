<?php
require_once __DIR__ . '/classes/Session.php';
require_once __DIR__ . '/classes/Settings.php';
require_once __DIR__ . '/classes/Post.php';
require_once __DIR__ . '/classes/Program.php';
require_once __DIR__ . '/classes/Project.php';

$session = Session::getInstance();
$settings = new Settings();

// Get latest content
$posts = (new Post())->getLatest(6);
$programs = (new Program())->getActive();
$projects = (new Project())->getFeatured();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($settings->get('site_name')) ?></title>
    
    <!-- Styles -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/styles.css">

    <style>
        .video-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, <?= $settings->get('header_overlay_opacity', 0.5) ?>);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .social-wall {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
        }

        .social-post {
            transform: translateY(0);
            transition: transform 0.3s ease;
        }

        .social-post:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="relative h-screen flex items-center justify-center overflow-hidden">
        <!-- Video Background -->
        <video autoplay muted loop playsinline class="absolute w-full h-full object-cover">
            <source src="<?= $settings->get('header_video') ?>" type="video/mp4">
        </video>
        
        <!-- Overlay -->
        <div class="video-overlay"></div>

        <!-- Content -->
        <div class="relative z-10 text-center text-white px-4">
            <h1 class="text-4xl md:text-6xl font-bold mb-6 leading-tight max-w-4xl mx-auto">
                Democracy and diversity are the cornerstone of our future
            </h1>
            <p class="text-xl md:text-2xl mb-8 max-w-2xl mx-auto">
                Join us in building a more inclusive and equitable society through art, education, and community engagement.
            </p>
            <div class="flex flex-col sm:flex-row items-center justify-center space-y-4 sm:space-y-0 sm:space-x-6">
                <a href="/donate" class="btn btn-primary text-lg px-8 py-3">
                    Donate Now
                </a>
                <a href="/about" class="btn btn-outline text-white border-white hover:bg-white hover:text-gray-900 text-lg px-8 py-3">
                    Learn More
                </a>
            </div>
        </div>

        <!-- Scroll Indicator -->
        <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 animate-bounce">
            <i class="fas fa-chevron-down text-white text-2xl"></i>
        </div>
    </header>

    <!-- Tabbed Section -->
    <section class="py-16 px-4">
        <div class="container mx-auto">
            <div class="flex flex-wrap justify-center mb-12">
                <button onclick="switchTab('news')" 
                        class="tab-button active px-6 py-3 text-lg font-medium">
                    Latest News
                </button>
                <button onclick="switchTab('programs')" 
                        class="tab-button px-6 py-3 text-lg font-medium">
                    Programs
                </button>
                <button onclick="switchTab('projects')" 
                        class="tab-button px-6 py-3 text-lg font-medium">
                    Projects
                </button>
            </div>

            <!-- News Tab -->
            <div id="news" class="tab-content active">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <?php foreach ($posts as $post): ?>
                        <article class="bg-white rounded-lg shadow-lg overflow-hidden">
                            <?php if ($post['image']): ?>
                                <img src="<?= htmlspecialchars($post['image']) ?>" 
                                     alt="<?= htmlspecialchars($post['title']) ?>"
                                     class="w-full h-48 object-cover">
                            <?php endif; ?>
                            <div class="p-6">
                                <h3 class="text-xl font-semibold mb-2">
                                    <a href="/blog/<?= $post['slug'] ?>" class="hover:text-blue-600">
                                        <?= htmlspecialchars($post['title']) ?>
                                    </a>
                                </h3>
                                <p class="text-gray-600 mb-4">
                                    <?= htmlspecialchars(substr($post['excerpt'], 0, 150)) ?>...
                                </p>
                                <div class="flex items-center text-sm text-gray-500">
                                    <i class="far fa-calendar mr-2"></i>
                                    <?= date('F j, Y', strtotime($post['created_at'])) ?>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Programs Tab -->
            <div id="programs" class="tab-content">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <?php foreach ($programs as $program): ?>
                        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                            <?php if ($program['image']): ?>
                                <img src="<?= htmlspecialchars($program['image']) ?>" 
                                     alt="<?= htmlspecialchars($program['title']) ?>"
                                     class="w-full h-48 object-cover">
                            <?php endif; ?>
                            <div class="p-6">
                                <h3 class="text-xl font-semibold mb-2">
                                    <a href="/programs/<?= $program['slug'] ?>" class="hover:text-blue-600">
                                        <?= htmlspecialchars($program['title']) ?>
                                    </a>
                                </h3>
                                <p class="text-gray-600 mb-4">
                                    <?= htmlspecialchars(substr($program['description'], 0, 150)) ?>...
                                </p>
                                <a href="/programs/<?= $program['slug'] ?>" 
                                   class="inline-flex items-center text-blue-600 hover:text-blue-800">
                                    Learn More
                                    <i class="fas fa-arrow-right ml-2"></i>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Projects Tab -->
            <div id="projects" class="tab-content">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <?php foreach ($projects as $project): ?>
                        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                            <?php if ($project['image']): ?>
                                <img src="<?= htmlspecialchars($project['image']) ?>" 
                                     alt="<?= htmlspecialchars($project['title']) ?>"
                                     class="w-full h-48 object-cover">
                            <?php endif; ?>
                            <div class="p-6">
                                <h3 class="text-xl font-semibold mb-2">
                                    <a href="/projects/<?= $project['slug'] ?>" class="hover:text-blue-600">
                                        <?= htmlspecialchars($project['title']) ?>
                                    </a>
                                </h3>
                                <p class="text-gray-600 mb-4">
                                    <?= htmlspecialchars(substr($project['description'], 0, 150)) ?>...
                                </p>
                                <div class="flex items-center justify-between">
                                    <div class="text-sm text-gray-500">
                                        <i class="far fa-calendar mr-2"></i>
                                        <?= date('F j, Y', strtotime($project['start_date'])) ?>
                                    </div>
                                    <a href="/projects/<?= $project['slug'] ?>" 
                                       class="inline-flex items-center text-blue-600 hover:text-blue-800">
                                        View Project
                                        <i class="fas fa-arrow-right ml-2"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Social Media Wall -->
    <section class="bg-gray-100 py-16 px-4">
        <div class="container mx-auto">
            <h2 class="text-3xl font-bold text-center mb-12">Connect With Us</h2>
            <?php include 'components/social_wall.php'; ?>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'components/footer.php'; ?>

    <script>
        // Tab switching
        function switchTab(tabId) {
            // Update tab buttons
            document.querySelectorAll('.tab-button').forEach(button => {
                button.classList.remove('active', 'text-blue-600', 'border-b-2', 'border-blue-600');
                button.classList.add('text-gray-600', 'hover:text-blue-600');
            });
            
            const activeButton = document.querySelector(`[onclick="switchTab('${tabId}')"]`);
            activeButton.classList.add('active', 'text-blue-600', 'border-b-2', 'border-blue-600');
            activeButton.classList.remove('text-gray-600', 'hover:text-blue-600');

            // Update tab content
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            document.getElementById(tabId).classList.add('active');
        }

        // Smooth scroll
        document.querySelector('.fa-chevron-down').addEventListener('click', () => {
            window.scrollTo({
                top: window.innerHeight,
                behavior: 'smooth'
            });
        });

        // Parallax effect on header
        window.addEventListener('scroll', () => {
            const scroll = window.pageYOffset;
            document.querySelector('video').style.transform = `translateY(${scroll * 0.5}px)`;
        });

        // Initialize social wall
        document.addEventListener('DOMContentLoaded', () => {
            const socialWall = new SocialWall();
            socialWall.init();
        });
    </script>
</body>
</html>