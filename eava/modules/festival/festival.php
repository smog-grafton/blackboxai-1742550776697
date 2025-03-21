<?php
require_once __DIR__ . '/../../classes/Event.php';
require_once __DIR__ . '/../../classes/Category.php';

$eventModel = new Event();
$categoryModel = new Category();

// Get festival events
$upcomingEvents = $eventModel->getUpcoming(5);
$featuredEvents = $eventModel->getFeatured(3);

// Get categories
$categories = $categoryModel->all();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Festival - EAVA</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100">
    <!-- Hero Section -->
    <section class="relative h-screen bg-cover bg-center" style="background-image: url('/assets/images/festival-hero.jpg');">
        <div class="absolute inset-0 bg-black bg-opacity-50"></div>
        <div class="relative container mx-auto px-4 h-full flex items-center">
            <div class="text-white max-w-2xl">
                <h1 class="text-5xl font-bold mb-4">EAVA Festival 2023</h1>
                <p class="text-xl mb-8">Join us for a celebration of art, culture, and community. Experience performances, workshops, and exhibitions that showcase the best of our creative community.</p>
                <a href="#events" class="bg-blue-500 text-white px-8 py-3 rounded-full text-lg hover:bg-blue-600 transition duration-300">
                    View Events
                </a>
            </div>
        </div>
    </section>

    <!-- Featured Events -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold mb-8">Featured Events</h2>
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
                                <span><i class="fas fa-map-marker-alt mr-2"></i><?= htmlspecialchars($event['location']) ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Schedule -->
    <section id="events" class="py-16 bg-gray-100">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold mb-8">Event Schedule</h2>
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <?php foreach ($upcomingEvents as $event): ?>
                    <div class="flex items-center border-b p-6 hover:bg-gray-50">
                        <div class="w-32 text-center">
                            <div class="text-2xl font-bold text-blue-500"><?= date('d', strtotime($event['start_date'])) ?></div>
                            <div class="text-gray-500"><?= date('M', strtotime($event['start_date'])) ?></div>
                        </div>
                        <div class="flex-1 ml-8">
                            <h3 class="text-xl font-semibold mb-2">
                                <a href="/event/<?= $event['slug'] ?>" class="text-blue-600 hover:text-blue-800">
                                    <?= htmlspecialchars($event['title']) ?>
                                </a>
                            </h3>
                            <p class="text-gray-600 mb-2"><?= htmlspecialchars($event['description']) ?></p>
                            <div class="flex items-center text-sm text-gray-500">
                                <span class="mr-4"><i class="fas fa-clock mr-2"></i><?= date('g:i A', strtotime($event['start_date'])) ?></span>
                                <span><i class="fas fa-map-marker-alt mr-2"></i><?= htmlspecialchars($event['location']) ?></span>
                            </div>
                        </div>
                        <div class="ml-8">
                            <a href="/event/<?= $event['slug'] ?>" 
                               class="bg-blue-500 text-white px-6 py-2 rounded-full hover:bg-blue-600 transition duration-300">
                                Learn More
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Registration -->
    <section class="py-16 bg-blue-600 text-white">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl font-bold mb-4">Register for the Festival</h2>
            <p class="text-xl mb-8">Secure your spot at EAVA Festival 2023 and be part of this amazing celebration.</p>
            <a href="/register" class="bg-white text-blue-600 px-8 py-3 rounded-full text-lg hover:bg-gray-100 transition duration-300">
                Register Now
            </a>
        </div>
    </section>

    <!-- Location -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold mb-8">Festival Location</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <div class="aspect-w-16 aspect-h-9">
                        <!-- Add your map embed code here -->
                        <div class="bg-gray-200 w-full h-96 rounded-lg"></div>
                    </div>
                </div>
                <div>
                    <h3 class="text-2xl font-semibold mb-4">Venue Details</h3>
                    <p class="text-gray-600 mb-4">
                        Join us at our main venue for an unforgettable experience. The venue is easily accessible and offers
                        ample parking for all attendees.
                    </p>
                    <div class="space-y-4">
                        <div class="flex items-start">
                            <i class="fas fa-map-marker-alt text-blue-500 mt-1 mr-4"></i>
                            <div>
                                <h4 class="font-semibold">Address</h4>
                                <p class="text-gray-600">123 Festival Street<br>City, State 12345</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <i class="fas fa-parking text-blue-500 mt-1 mr-4"></i>
                            <div>
                                <h4 class="font-semibold">Parking</h4>
                                <p class="text-gray-600">Free parking available on-site</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <i class="fas fa-bus text-blue-500 mt-1 mr-4"></i>
                            <div>
                                <h4 class="font-semibold">Public Transport</h4>
                                <p class="text-gray-600">Bus routes 10, 15, 20 stop nearby</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="bg-gray-800 text-white py-8">
        <div class="container mx-auto px-4 text-center">
            <p>&copy; 2023 EAVA Festival. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>