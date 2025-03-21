<?php
require_once __DIR__ . '/../../classes/Post.php';
require_once __DIR__ . '/../../classes/Program.php';
require_once __DIR__ . '/../../classes/Project.php';

// Get latest content
$posts = (new Post())->getLatest(6);
$programs = (new Program())->getActive();
$projects = (new Project())->getFeatured();
?>

<section id="content" class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <!-- Tab Navigation -->
        <div class="flex flex-wrap justify-center mb-12">
            <button onclick="switchTab('news')" 
                    class="tab-button active px-6 py-3 text-lg font-medium relative group">
                <span class="relative z-10">Latest News</span>
                <span class="absolute bottom-0 left-0 w-full h-0.5 bg-blue-600 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-200 origin-left"></span>
            </button>
            <button onclick="switchTab('programs')" 
                    class="tab-button px-6 py-3 text-lg font-medium relative group">
                <span class="relative z-10">Programs</span>
                <span class="absolute bottom-0 left-0 w-full h-0.5 bg-blue-600 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-200 origin-left"></span>
            </button>
            <button onclick="switchTab('projects')" 
                    class="tab-button px-6 py-3 text-lg font-medium relative group">
                <span class="relative z-10">Projects</span>
                <span class="absolute bottom-0 left-0 w-full h-0.5 bg-blue-600 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-200 origin-left"></span>
            </button>
        </div>

        <!-- Tab Content -->
        <div class="tab-content-wrapper">
            <!-- News Tab -->
            <div id="news-tab" class="tab-content active">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <?php foreach ($posts as $post): ?>
                        <article class="bg-white rounded-lg shadow-lg overflow-hidden transform hover:-translate-y-1 transition-transform duration-200">
                            <?php if ($post['image']): ?>
                                <div class="relative h-48 overflow-hidden">
                                    <img src="<?= htmlspecialchars($post['image']) ?>" 
                                         alt="<?= htmlspecialchars($post['title']) ?>"
                                         class="w-full h-full object-cover transform hover:scale-110 transition-transform duration-200">
                                </div>
                            <?php endif; ?>
                            <div class="p-6">
                                <div class="flex items-center text-sm text-gray-500 mb-2">
                                    <i class="far fa-calendar mr-2"></i>
                                    <?= date('F j, Y', strtotime($post['created_at'])) ?>
                                    <?php if ($post['category']): ?>
                                        <span class="mx-2">•</span>
                                        <span class="text-blue-600"><?= htmlspecialchars($post['category']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <h3 class="text-xl font-semibold mb-2 hover:text-blue-600 transition-colors">
                                    <a href="/blog/<?= $post['slug'] ?>">
                                        <?= htmlspecialchars($post['title']) ?>
                                    </a>
                                </h3>
                                <p class="text-gray-600 mb-4">
                                    <?= htmlspecialchars(substr($post['excerpt'], 0, 150)) ?>...
                                </p>
                                <a href="/blog/<?= $post['slug'] ?>" 
                                   class="inline-flex items-center text-blue-600 hover:text-blue-800 transition-colors">
                                    Read More
                                    <i class="fas fa-arrow-right ml-2 transform group-hover:translate-x-1 transition-transform"></i>
                                </a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Programs Tab -->
            <div id="programs-tab" class="tab-content hidden">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <?php foreach ($programs as $program): ?>
                        <div class="bg-white rounded-lg shadow-lg overflow-hidden transform hover:-translate-y-1 transition-transform duration-200">
                            <?php if ($program['image']): ?>
                                <div class="relative h-48 overflow-hidden">
                                    <img src="<?= htmlspecialchars($program['image']) ?>" 
                                         alt="<?= htmlspecialchars($program['title']) ?>"
                                         class="w-full h-full object-cover transform hover:scale-110 transition-transform duration-200">
                                    <?php if ($program['status'] === 'active'): ?>
                                        <div class="absolute top-4 right-4 bg-green-500 text-white px-3 py-1 rounded-full text-sm">
                                            Active
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            <div class="p-6">
                                <h3 class="text-xl font-semibold mb-2 hover:text-blue-600 transition-colors">
                                    <a href="/programs/<?= $program['slug'] ?>">
                                        <?= htmlspecialchars($program['title']) ?>
                                    </a>
                                </h3>
                                <p class="text-gray-600 mb-4">
                                    <?= htmlspecialchars(substr($program['description'], 0, 150)) ?>...
                                </p>
                                <div class="flex items-center justify-between">
                                    <a href="/programs/<?= $program['slug'] ?>" 
                                       class="inline-flex items-center text-blue-600 hover:text-blue-800 transition-colors">
                                        Learn More
                                        <i class="fas fa-arrow-right ml-2"></i>
                                    </a>
                                    <?php if ($program['registration_open']): ?>
                                        <a href="/programs/<?= $program['slug'] ?>/register" 
                                           class="btn btn-primary">
                                            Register Now
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Projects Tab -->
            <div id="projects-tab" class="tab-content hidden">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <?php foreach ($projects as $project): ?>
                        <div class="bg-white rounded-lg shadow-lg overflow-hidden transform hover:-translate-y-1 transition-transform duration-200">
                            <?php if ($project['image']): ?>
                                <div class="relative h-48 overflow-hidden">
                                    <img src="<?= htmlspecialchars($project['image']) ?>" 
                                         alt="<?= htmlspecialchars($project['title']) ?>"
                                         class="w-full h-full object-cover transform hover:scale-110 transition-transform duration-200">
                                    <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black to-transparent p-4">
                                        <div class="flex items-center text-white text-sm">
                                            <i class="far fa-calendar mr-2"></i>
                                            <?= date('F j, Y', strtotime($project['start_date'])) ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <div class="p-6">
                                <h3 class="text-xl font-semibold mb-2 hover:text-blue-600 transition-colors">
                                    <a href="/projects/<?= $project['slug'] ?>">
                                        <?= htmlspecialchars($project['title']) ?>
                                    </a>
                                </h3>
                                <p class="text-gray-600 mb-4">
                                    <?= htmlspecialchars(substr($project['description'], 0, 150)) ?>...
                                </p>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <?php if ($project['status'] === 'completed'): ?>
                                            <span class="text-green-500">
                                                <i class="fas fa-check-circle mr-1"></i>
                                                Completed
                                            </span>
                                        <?php elseif ($project['status'] === 'in_progress'): ?>
                                            <span class="text-blue-500">
                                                <i class="fas fa-spinner fa-spin mr-1"></i>
                                                In Progress
                                            </span>
                                        <?php else: ?>
                                            <span class="text-yellow-500">
                                                <i class="fas fa-clock mr-1"></i>
                                                Upcoming
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <a href="/projects/<?= $project['slug'] ?>" 
                                       class="inline-flex items-center text-blue-600 hover:text-blue-800 transition-colors">
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
    </div>
</section>

<script>
function switchTab(tabId) {
    // Update tab buttons
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('active');
        button.querySelector('.absolute').classList.remove('scale-x-100');
    });
    
    const activeButton = document.querySelector(`[onclick="switchTab('${tabId}')"]`);
    activeButton.classList.add('active');
    activeButton.querySelector('.absolute').classList.add('scale-x-100');

    // Update tab content with animation
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
        content.classList.remove('active');
    });

    const activeContent = document.getElementById(`${tabId}-tab`);
    activeContent.classList.remove('hidden');
    
    // Trigger reflow for animation
    void activeContent.offsetWidth;
    
    activeContent.classList.add('active');

    // Initialize masonry layout if needed
    if (typeof Masonry !== 'undefined') {
        new Masonry(activeContent.querySelector('.grid'), {
            itemSelector: '.grid > *',
            percentPosition: true
        });
    }
}

// Initialize first tab
document.addEventListener('DOMContentLoaded', () => {
    // Show first tab
    document.querySelector('.tab-content').classList.remove('hidden');
    
    // Initialize masonry layout
    if (typeof Masonry !== 'undefined') {
        document.querySelectorAll('.grid').forEach(grid => {
            new Masonry(grid, {
                itemSelector: '.grid > *',
                percentPosition: true
            });
        });
    }

    // Initialize animations
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

    document.querySelectorAll('.grid > *').forEach(el => {
        el.classList.add('opacity-0');
        observer.observe(el);
    });
});
</script>