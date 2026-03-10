/**
 * Home Page Animations - Christ Ekklesia Fellowship Chapel
 * 
 * Enhanced animations and interactions for the homepage including
 * carousel enhancements, scroll animations, and interactive elements.
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Initialize home page functionality
    initHeroAnimations();
    initCounterAnimations();
    initCarouselEnhancements();
    initScrollAnimations();
    initParallaxEffects();
    
    /**
     * Initialize hero section animations
     */
    function initHeroAnimations() {
        const heroContent = document.querySelectorAll('.hero-content');
        
        heroContent.forEach((content, index) => {
            // Stagger animation for hero elements
            const elements = content.querySelectorAll('h1, p, .btn, .hero-features');
            elements.forEach((element, i) => {
                element.style.opacity = '0';
                element.style.transform = 'translateY(30px)';
                
                setTimeout(() => {
                    element.style.transition = 'opacity 0.8s ease, transform 0.8s ease';
                    element.style.opacity = '1';
                    element.style.transform = 'translateY(0)';
                }, (i * 200) + (index * 100));
            });
        });
    }
    
    /**
     * Initialize counter animations for statistics
     */
    function initCounterAnimations() {
        const counters = document.querySelectorAll('.stat-number');
        
        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        animateCounter(entry.target);
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.5 });
            
            counters.forEach(counter => {
                observer.observe(counter);
            });
        }
    }
    
    /**
     * Animate counter numbers
     */
    function animateCounter(element) {
        const target = parseInt(element.textContent.replace(/[^0-9]/g, ''));
        const suffix = element.textContent.replace(/[0-9]/g, '');
        let current = 0;
        const increment = target / 50;
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            element.textContent = Math.floor(current) + suffix;
        }, 50);
    }
    
    /**
     * Enhance carousel functionality
     */
    function initCarouselEnhancements() {
        const carousel = document.getElementById('heroCarousel');
        if (!carousel) return;
        
        // Add keyboard navigation
        document.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowLeft') {
                bootstrap.Carousel.getInstance(carousel)?.prev();
            } else if (e.key === 'ArrowRight') {
                bootstrap.Carousel.getInstance(carousel)?.next();
            }
        });
        
        // Pause carousel on hover
        carousel.addEventListener('mouseenter', function() {
            bootstrap.Carousel.getInstance(carousel)?.pause();
        });
        
        carousel.addEventListener('mouseleave', function() {
            bootstrap.Carousel.getInstance(carousel)?.cycle();
        });
        
        // Add slide change animations
        carousel.addEventListener('slide.bs.carousel', function(e) {
            const activeSlide = e.relatedTarget;
            const content = activeSlide.querySelector('.hero-content');
            
            if (content) {
                // Reset animations
                const elements = content.querySelectorAll('h1, p, .btn, .hero-features');
                elements.forEach(element => {
                    element.style.opacity = '0';
                    element.style.transform = 'translateY(30px)';
                });
                
                // Animate in
                setTimeout(() => {
                    elements.forEach((element, i) => {
                        setTimeout(() => {
                            element.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                            element.style.opacity = '1';
                            element.style.transform = 'translateY(0)';
                        }, i * 150);
                    });
                }, 300);
            }
        });
    }
    
    /**
     * Initialize scroll-triggered animations
     */
    function initScrollAnimations() {
        if ('IntersectionObserver' in window) {
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animate-in');
                        
                        // Special animations for specific sections
                        if (entry.target.classList.contains('ministry-grid')) {
                            entry.target.classList.add('animate-in');
                            animateMinistryCards(entry.target);
                        }
                        
                        if (entry.target.classList.contains('values-grid')) {
                            animateValueItems(entry.target);
                        }
                        
                        observer.unobserve(entry.target);
                    }
                });
            }, observerOptions);
            
            // Observe sections for animation
            document.querySelectorAll('.section-animate, .ministry-grid, .values-grid, .testimonial-card').forEach(section => {
                observer.observe(section);
            });
        }
    }
    
    /**
     * Animate ministry cards
     */
    function animateMinistryCards(container) {
        const cards = container.querySelectorAll('.ministry-card');
        
        if (cards.length === 0) {
            console.warn('No ministry cards found in container');
            return;
        }
        
        cards.forEach((card, index) => {
            // Ensure card is initially hidden for animation
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px) scale(0.95)';
            
            setTimeout(() => {
                card.style.opacity = '1';
                card.style.transform = 'translateY(0) scale(1)';
            }, index * 200);
        });
        
        // Fallback: ensure all cards are visible after animation completes
        setTimeout(() => {
            cards.forEach(card => {
                card.style.opacity = '1';
                card.style.transform = 'translateY(0) scale(1)';
            });
        }, cards.length * 200 + 1000);
    }
    
    /**
     * Animate value items
     */
    function animateValueItems(container) {
        const items = container.querySelectorAll('.value-item');
        items.forEach((item, index) => {
            setTimeout(() => {
                item.style.opacity = '1';
                item.style.transform = 'translateX(0)';
            }, index * 100);
        });
    }
    
    /**
     * Initialize parallax effects
     */
    function initParallaxEffects() {
        const parallaxElements = document.querySelectorAll('.parallax-bg');
        
        if (parallaxElements.length > 0) {
            window.addEventListener('scroll', function() {
                const scrolled = window.pageYOffset;
                const rate = scrolled * -0.5;
                
                parallaxElements.forEach(element => {
                    element.style.transform = `translateY(${rate}px)`;
                });
            }, { passive: true });
        }
    }
    
    /**
     * Initialize interactive elements
     */
    function initInteractiveElements() {
        // Add hover effects to buttons
        const buttons = document.querySelectorAll('.btn');
        buttons.forEach(btn => {
            btn.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px)';
                this.style.boxShadow = '0 8px 25px rgba(0,0,0,0.15)';
            });
            
            btn.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = '';
            });
        });
        
        // Add interactive ministry cards
        const ministryCards = document.querySelectorAll('.ministry-card');
        ministryCards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-10px) scale(1.02)';
                this.style.boxShadow = '0 15px 35px rgba(0,0,0,0.1)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
                this.style.boxShadow = '';
            });
        });
    }
    
    /**
     * Initialize typing animation for hero text
     */
    function initTypingAnimation() {
        const typingElements = document.querySelectorAll('.typing-text');
        
        typingElements.forEach(element => {
            const text = element.textContent;
            element.textContent = '';
            element.style.borderRight = '2px solid';
            element.style.animation = 'blink 1s infinite';
            
            let i = 0;
            const typeWriter = () => {
                if (i < text.length) {
                    element.textContent += text.charAt(i);
                    i++;
                    setTimeout(typeWriter, 100);
                } else {
                    element.style.borderRight = 'none';
                    element.style.animation = 'none';
                }
            };
            
            setTimeout(typeWriter, 1000);
        });
    }
    
    // Initialize all interactive elements
    initInteractiveElements();
    
    // Add CSS for animations
    const style = document.createElement('style');
    style.textContent = `
        .animate-in {
            opacity: 1 !important;
            transform: translateY(0) !important;
        }
        
        .section-animate {
            opacity: 0;
            transform: translateY(50px);
            transition: opacity 0.8s ease, transform 0.8s ease;
        }
        
        .ministry-grid .ministry-card {
            opacity: 0;
            transform: translateY(30px) scale(0.95);
            transition: all 0.6s ease;
        }
        
        .ministry-card {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
        
        .value-item {
            opacity: 0;
            transform: translateX(-30px);
            transition: all 0.6s ease;
        }
        
        .btn {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .parallax-bg {
            transition: transform 0.1s ease-out;
        }
        
        @keyframes blink {
            0%, 50% { border-color: transparent; }
            51%, 100% { border-color: currentColor; }
        }
        
        .hero-features .feature-item {
            transition: all 0.6s ease;
        }
        
        .service-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        .community-icons .community-icon {
            animation: float 3s ease-in-out infinite;
        }
        
        .community-icons .community-icon:nth-child(2) {
            animation-delay: 1s;
        }
        
        .community-icons .community-icon:nth-child(3) {
            animation-delay: 2s;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        
        .location-card {
            transition: transform 0.3s ease;
        }
        
        .location-card:hover {
            transform: scale(1.05);
        }
    `;
    document.head.appendChild(style);
    
    // Fallback: Ensure ministry cards are visible after page load
    setTimeout(() => {
        const ministryCards = document.querySelectorAll('.ministry-card');
        ministryCards.forEach(card => {
            if (card.style.opacity === '0' || !card.style.opacity) {
                card.style.opacity = '1';
                card.style.transform = 'translateY(0) scale(1)';
                card.style.transition = 'all 0.6s ease';
            }
        });
    }, 2000); // Wait 2 seconds after page load
    
    // Additional fallback on window load
    window.addEventListener('load', () => {
        setTimeout(() => {
            const hiddenCards = document.querySelectorAll('.ministry-card[style*="opacity: 0"]');
            hiddenCards.forEach(card => {
                card.style.opacity = '1';
                card.style.transform = 'translateY(0) scale(1)';
            });
        }, 500);
    });
});

// Export for potential use in other scripts
window.HomeAnimations = {
    init: function() {
        document.dispatchEvent(new Event('DOMContentLoaded'));
    }
};
