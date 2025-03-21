// Main JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Initialize components
    initNavigation();
    initVideoHeader();
    initTabs();
    initAnimations();
    initForms();
    initSocialWall();
});

// Navigation
function initNavigation() {
    const menuButton = document.querySelector('.menu-button');
    const mobileMenu = document.querySelector('.mobile-menu');

    if (menuButton && mobileMenu) {
        menuButton.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
            document.body.classList.toggle('overflow-hidden');
        });

        // Close menu on outside click
        document.addEventListener('click', (e) => {
            if (!mobileMenu.contains(e.target) && !menuButton.contains(e.target)) {
                mobileMenu.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }
        });
    }

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

// Video Header
function initVideoHeader() {
    const video = document.querySelector('.video-header video');
    const overlay = document.querySelector('.video-overlay');

    if (video && overlay) {
        // Parallax effect on scroll
        window.addEventListener('scroll', () => {
            const scroll = window.pageYOffset;
            video.style.transform = `translateY(${scroll * 0.5}px)`;
        });

        // Adjust overlay opacity based on scroll
        window.addEventListener('scroll', () => {
            const scroll = window.pageYOffset;
            const maxScroll = window.innerHeight;
            const opacity = Math.min(0.8, 0.5 + (scroll / maxScroll * 0.3));
            overlay.style.backgroundColor = `rgba(0, 0, 0, ${opacity})`;
        });
    }
}

// Tabs
function initTabs() {
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');

    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            const tabId = button.getAttribute('data-tab');

            // Update buttons
            tabButtons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');

            // Update content
            tabContents.forEach(content => {
                content.classList.remove('active');
                if (content.id === tabId) {
                    content.classList.add('active');
                }
            });
        });
    });
}

// Animations
function initAnimations() {
    // Intersection Observer for fade-in animations
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

    // Observe elements with animation classes
    document.querySelectorAll('.fade-in').forEach(el => observer.observe(el));
    document.querySelectorAll('.slide-up').forEach(el => observer.observe(el));
    document.querySelectorAll('.slide-down').forEach(el => observer.observe(el));
}

// Forms
function initForms() {
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            const submitButton = form.querySelector('[type="submit"]');
            const originalText = submitButton.textContent;
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';

            try {
                const formData = new FormData(form);
                const response = await fetch(form.action, {
                    method: form.method,
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await response.json();

                if (response.ok) {
                    showNotification('success', data.message || 'Form submitted successfully!');
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    } else {
                        form.reset();
                    }
                } else {
                    throw new Error(data.message || 'Form submission failed.');
                }
            } catch (error) {
                showNotification('error', error.message);
            } finally {
                submitButton.disabled = false;
                submitButton.textContent = originalText;
            }
        });
    });
}

// Social Wall
function initSocialWall() {
    const socialWall = document.querySelector('.social-wall');
    if (!socialWall) return;

    // Initialize Masonry layout
    const grid = new Masonry(socialWall, {
        itemSelector: '.social-post',
        columnWidth: '.social-post',
        gutter: 20,
        percentPosition: true
    });

    // Load more posts on scroll
    let loading = false;
    let page = 1;

    window.addEventListener('scroll', () => {
        if (loading) return;

        const lastPost = socialWall.querySelector('.social-post:last-child');
        if (!lastPost) return;

        const lastPostOffset = lastPost.offsetTop + lastPost.clientHeight;
        const pageOffset = window.pageYOffset + window.innerHeight;

        if (pageOffset > lastPostOffset - 100) {
            loading = true;
            loadMorePosts();
        }
    });

    async function loadMorePosts() {
        try {
            const response = await fetch(`/api/social-posts?page=${++page}`);
            const data = await response.json();

            if (data.posts.length > 0) {
                const fragment = document.createDocumentFragment();
                data.posts.forEach(post => {
                    const element = createSocialPost(post);
                    fragment.appendChild(element);
                });

                socialWall.appendChild(fragment);
                grid.appended(fragment.children);
            }
        } catch (error) {
            console.error('Failed to load more posts:', error);
        } finally {
            loading = false;
        }
    }
}

// Utility Functions
function showNotification(type, message) {
    const notification = document.createElement('div');
    notification.className = `notification ${type} animate-slide-down`;
    notification.innerHTML = `
        <div class="flex items-center">
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} mr-3"></i>
            <p>${message}</p>
        </div>
        <button onclick="this.parentElement.remove()" class="ml-4">
            <i class="fas fa-times"></i>
        </button>
    `;

    document.body.appendChild(notification);
    setTimeout(() => notification.remove(), 5000);
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function throttle(func, limit) {
    let inThrottle;
    return function(...args) {
        if (!inThrottle) {
            func.apply(this, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}