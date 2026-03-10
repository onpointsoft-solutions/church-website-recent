<?php 
/**
 * Home Page Template - Christ Ekklesia Fellowship Chapel
 * 
 * Production-ready homepage with SEO optimization, performance enhancements,
 * and comprehensive content sections.
 */

// SEO Configuration
$pageTitle = "Christ Ekklesia Fellowship Chapel - Where Christ Takes Preeminence";
$pageDescription = "Join Christ Ekklesia Fellowship Chapel in Kabarak, Nakuru for authentic worship, biblical teaching, and vibrant Christian fellowship. Experience God's love in our welcoming community.";
$pageKeywords = "Christ Ekklesia Fellowship Chapel, church Nakuru, Kabarak church, Christian fellowship, worship service, bible study, ministry, Kenya church";
$pageType = "website";
$pageImage = "https://res.cloudinary.com/dtpevimcr/image/upload/v1763730951/img1_gqptig.jpg";

// Page-specific scripts
$pageScripts = [
    '/assets/js/home-animations.js',
    '/assets/js/contact-form.js'
];

include dirname(__DIR__) . '/includes/header.php'; 
?>

<!-- Main Content -->
<main id="main-content" class="home-section container-fluid px-0">
   <!-- Hero Section -->
   <section id="home" class="hero-section" aria-label="Welcome to Christ Ekklesia Fellowship Chapel">
        <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="8000" aria-label="Church highlights carousel">
            <!-- Indicators -->
            <div class="carousel-indicators">
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1: Welcome to our church"></button>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1" aria-label="Slide 2: Community of faith"></button>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2" aria-label="Slide 3: Ministries and service"></button>
            </div>

            <!-- Slides -->
            <div class="carousel-inner">
                <!-- Slide 1: Welcome -->
                <div class="carousel-item active">
                    <div class="d-flex align-items-center min-vh-100">
                        <div class="container">
                            <div class="row align-items-center">
                                <div class="col-lg-8 hero-content animate-fade-in">
                                    <h1 class="hero-title">Christ Ekklesia Fellowship Chapel</h1>
                                    <p class="hero-subtitle">Where Christ Takes the Preeminence of Our Worship</p>
                                    <p class="lead mb-4">Join us in celebrating the supremacy of Christ in all things. Experience authentic worship, biblical teaching, and fellowship rooted in God's love in Kabarak, Nakuru.</p>
                                                                        <div class="d-flex flex-wrap gap-3">
                                        <a href="#services" class="btn btn-primary btn-lg">Join Our Worship</a>
                                        <a href="#about" class="btn btn-outline-light btn-lg">Learn More</a>
                                    </div>
                                </div>
                                <div class="col-lg-4 text-center">
                                    <div class="hero-logo-container">
                                        <img src="./assets/images/logo.png" alt="Christ Ekklesia Fellowship Chapel Logo" class="hero-logo img-fluid" loading="eager">
                                    </div>
                                </div>
                            
</div>
                        </div>
                    </div>
                </div>

                <!-- Slide 2: Community -->
                <div class="carousel-item">
                    <div class="d-flex align-items-center min-vh-100">
                        <div class="container">
                            <div class="row align-items-center">
                                <div class="col-lg-8 hero-content animate-fade-in">
                                    <h1 class="hero-title">Community of Faith</h1>
                                    <p class="hero-subtitle">Growing Together in Christ</p>
                                    <p class="lead mb-4">Join our vibrant community of believers who are passionate about knowing and following Jesus. Experience the warmth of Christian fellowship in Kabarak, Nakuru.</p>
                                    <div class="hero-features mb-4">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <div class="feature-item d-flex align-items-center">
                                                    <i class="fas fa-users feature-icon me-3"></i>
                                                    <div>
                                                        <h5 class="mb-1">Fellowship</h5>
                                                        <p class="mb-0 small">Authentic Christian community</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <div class="feature-item d-flex align-items-center">
                                                    <i class="fas fa-book-open feature-icon me-3"></i>
                                                    <div>
                                                        <h5 class="mb-1">Bible Study</h5>
                                                        <p class="mb-0 small">Weekly spiritual growth</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <div class="feature-item d-flex align-items-center">
                                                    <i class="fas fa-pray feature-icon me-3"></i>
                                                    <div>
                                                        <h5 class="mb-1">Prayer</h5>
                                                        <p class="mb-0 small">Powerful prayer meetings</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <div class="feature-item d-flex align-items-center">
                                                    <i class="fas fa-heart feature-icon me-3"></i>
                                                    <div>
                                                        <h5 class="mb-1">Care</h5>
                                                        <p class="mb-0 small">Supporting one another</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-wrap gap-3">
                                        <a href="/volunteers" class="btn btn-primary btn-lg">Get Involved</a>
                                        <a href="#ministries" class="btn btn-outline-light btn-lg">Our Ministries</a>
                                    </div>
                                </div>
                                <div class="col-lg-4 text-center">
                                    <div class="community-visual">
                                        <div class="community-icons">
                                            <i class="fas fa-church community-icon"></i>
                                            <i class="fas fa-hands-praying community-icon"></i>
                                            <i class="fas fa-cross community-icon"></i>
                                        </div>
                                    </div>
                                </div>
</div>
                        </div>
                    </div>
                </div>

                <!-- Slide 3: Service Times -->
                <div class="carousel-item">
                    <div class="d-flex align-items-center min-vh-100">
                        <div class="container">
                            <div class="row align-items-center">
                                <div class="col-lg-8 hero-content animate-fade-in">
                                    <h1 class="hero-title">Join Us for Worship</h1>
                                    <p class="hero-subtitle">Experience God's Presence with Us</p>
                                    <p class="lead mb-4">Come and worship with us every week. All are welcome to experience God's love and grace in our services.</p>
                                    <div class="service-times mb-4">
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <div class="service-card text-center">
                                                    <div class="service-icon mb-2">
                                                        <i class="fas fa-sun"></i>
                                                    </div>
                                                    <h5 class="service-title">Sunday Worship</h5>
                                                    <p class="service-time">10:00 AM</p>
                                                    <p class="service-desc small">Main worship service</p>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="service-card text-center">
                                                    <div class="service-icon mb-2">
                                                        <i class="fas fa-book-open"></i>
                                                    </div>
                                                    <h5 class="service-title">Bible Study</h5>
                                                    <p class="service-time">Wednesday 7:00 PM</p>
                                                    <p class="service-desc small">Midweek study</p>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="service-card text-center">
                                                    <div class="service-icon mb-2">
                                                        <i class="fas fa-praying-hands"></i>
                                                    </div>
                                                    <h5 class="service-title">Prayer Meeting</h5>
                                                    <p class="service-time">Friday 6:30 PM</p>
                                                    <p class="service-desc small">Corporate prayer</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-wrap gap-3">
                                        <a href="/calendar" class="btn btn-primary btn-lg">View Calendar</a>
                                        <a href="#contact" class="btn btn-outline-light btn-lg">Get Directions</a>
                                    </div>
                                </div>
                                <div class="col-lg-4 text-center">
                                    <div class="location-info">
                                        <div class="location-card">
                                            <i class="fas fa-map-marker-alt location-icon mb-3"></i>
                                            <h5>Find Us</h5>
                                            <p class="mb-2"><strong>Kabarak, Nakuru</strong></p>
                                            <p class="small text-light">Kenya</p>
                                            <a href="#contact" class="btn btn-outline-light btn-sm mt-2">Get Directions</a>
                                        </div>
                                    </div>
                                </div>
</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Controls -->
            <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    </section>

    <!-- Worship Emphasis Section -->
    <section class="worship-emphasis">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <h2>"And he is the head of the body, the church: who is the beginning, the firstborn from the dead; that in all things he might have the preeminence."</h2>
                    <p class="lead">Colossians 1:18</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Values, Vision, and Mission Section -->
    <section id="values" class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <h2 class="section-title">Our Values, Vision, and Mission</h2>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="values-container">
                        <div class="values-card">
                            <h3>Our Values</h3>
                            <div class="values-grid">
                                <div class="value-item">
                                    <i class="fas fa-music value-icon"></i>
                                    <span>Holy Spirit anointed worship</span>
                                </div>
                                <div class="value-item">
                                    <i class="fas fa-hands-praying value-icon"></i>
                                    <span>Prayers and intercession</span>
                                </div>
                                <div class="value-item">
                                    <i class="fas fa-book value-icon"></i>
                                    <span>Holy Spirit anointed teachings</span>
                                </div>
                                <div class="value-item">
                                    <i class="fas fa-seedling value-icon"></i>
                                    <span>Evangelism and Discipleship</span>
                                </div>
                                <div class="value-item">
                                    <i class="fas fa-child value-icon"></i>
                                    <span>Youth and Teens in the Ministry</span>
                                </div>
                                <div class="value-item">
                                    <i class="fas fa-shield-alt value-icon"></i>
                                    <span>Children security in Christ</span>
                                </div>
                                <div class="value-item">
                                    <i class="fas fa-heart value-icon"></i>
                                    <span>Godly marriages and Families</span>
                                </div>
                                <a href="#ministries" class="btn btn-outline-light mt-3 learn-more-btn">Learn More</a>
</div>
                        </div>
                        <div class="values-card">
                            <h3>Our Vision</h3>
                            <p class="vision-text">To reach every corner of the world with good news of Jesus Christ the Son of God.</p>
                        </div>
                        <div class="values-card">
                            <h3>Our Mission</h3>
                            <p class="mission-text">To make and equip disciples for the Kingdom's great commission.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Upcoming Events Section -->
    <section id="events" class="py-5">
      <div class="container">
        <div class="row">
          <div class="col-lg-12 text-center">
            <h2 class="section-title">Upcoming Events</h2>
          </div>
        </div>
        <div class="row" id="events-list">
          <!-- Events will be dynamically loaded here -->
        </div>
      </div>
    </section>
    <!-- Gallery Section -->
    <section class="gallery-section">

        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <h2 class="section-title">Our Fellowship</h2>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-4 col-md-6">
                    <div class="gallery-item">
                        <img src="https://res.cloudinary.com/dtpevimcr/image/upload/v1763730951/img1_gqptig.jpg" alt="Worship Service">
                        <div class="gallery-overlay">
                            <span>Sunday Worship</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="gallery-item">
                        <img src="https://res.cloudinary.com/dtpevimcr/image/upload/v1763730950/ministers_lytcuc.jpg" alt="Worship Ministry">
                        <div class="gallery-overlay">
                            <span>Ministers</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="gallery-item">
                        <img src="./assets/images/bible-study.jpg" alt="Bible Study">
                        <div class="gallery-overlay">
                            <span>Bible Study</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="gallery-item">
                        <img src="./assets/images/youths.jpg" alt="Youth Ministry">
                        <div class="gallery-overlay">
                            <span>Youth Ministry</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="gallery-item">
                        <img src="./assets/images/sunday-school.jpg" alt="Children's Ministry">
                        <div class="gallery-overlay">
                            <span>Children's Ministry</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Sermons Section -->
    <section id="sermons" class="py-5 bg-light">
      <div class="container">
        <div class="row">
          <div class="col-lg-12 text-center">
            <h2 class="section-title">Latest Sermons</h2>
          </div>
        </div>
        <div class="row" id="sermons-list">
          <!-- Sermons will be dynamically loaded here -->
        </div>
      </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <h2 class="section-title">Worship Services</h2>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <div class="card">
                        <div class="card-header text-center">
                            <i class="fas fa-clock mb-2" style="font-size: 2rem;"></i>
                            <h4>Wednesday Prayer & Intercession</h4>
                        </div>
                        <div class="card-body">
                            <p><strong>Time:</strong> 5:30 PM - 6:30 PM</p>
                            <p><strong>Focus:</strong> Prayer and intercession</p>
                            <p>A dedicated time for seeking God's presence through focused prayer and intercession for our community and world.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mb-4">
                    <div class="card">
                        <div class="card-header text-center">
                            <i class="fas fa-sun mb-2" style="font-size: 2rem;"></i>
                            <h4>Sunday Morning Services</h4>
                        </div>
                        <div class="card-body">
                            <p><strong>First Service:</strong> 8:00 AM - 9:30 AM</p>
                            <p><strong>Second Service:</strong> 10:30 AM - 12:30 PM</p>
                            <p><strong>Focus:</strong> Worship, teaching, and fellowship</p>
                            <p>Celebrate Christ's presence through worship, biblical teaching, and community.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mb-4">
                    <div class="card">
                        <div class="card-header text-center">
                            <i class="fas fa-graduation-cap mb-2" style="font-size: 2rem;"></i>
                            <h4>Youth-led Bible Study</h4>
                        </div>
                        <div class="card-body">
                            <p><strong>Day:</strong> Saturday</p>
                            <p><strong>Time:</strong> 2:00 PM - 4:00 PM</p>
                            <p><strong>Focus:</strong> Youth-led Bible study and discussion</p>
                            <p>A dynamic time of learning and fellowship led by our youth, exploring God's Word together.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mb-4">
                    <div class="card">
                        <div class="card-header text-center">
                            <i class="fas fa-utensils mb-2" style="font-size: 2rem;"></i>
                            <h4>Lunch Hour Services</h4>
                        </div>
                        <div class="card-body">
                            <p><strong>Days:</strong> Wednesday and Friday</p>
                            <p><strong>Time:</strong> 12:30 PM - 1:30 PM</p>
                            <p><strong>Focus:</strong> Midday worship and encouragement</p>
                            <p>A refreshing break in your day to connect with God and fellow believers.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Sermons Section 
    <section id="sermons" class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <h2 class="section-title">Recent Sermons</h2>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="sermon-card">
                        <div class="sermon-thumbnail">
                            <img src="./assets/images/13th-july-2025.jpg" alt="Sermon Thumbnail" class="sermon-thumb">
                            <a href="https://www.youtube.com/watch?v=5010AvX8wuQ" class="youtube-link" target="_blank">
                                <i class="fab fa-youtube"></i>
                            </a>
                        </div>
                        <div class="sermon-header">
                            <i class="fas fa-microphone-alt sermon-icon"></i>
                        </div>
                        <div class="sermon-content">
                            <h4>Access granted through legislation</h4>
                            <p class="sermon-meta">Preached by Mr Malanda • July 13, 2025</p>
                            <p class="sermon-description"></p>
                            <div class="sermon-actions">
                                <button class="btn btn-outline-primary btn-sm me-2"><i class="fas fa-play"></i> Listen Now</button>
                                <button class="btn btn-outline-secondary btn-sm"><i class="fas fa-download"></i> Download</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="sermon-card">
                        <div class="sermon-thumbnail">
                            <img src="./assets/images/22-june-2025.jpg" alt="Sermon Thumbnail" class="sermon-thumb">
                            <a href="https://www.youtube.com/watch?v=lNh9PMu1JAw" class="youtube-link" target="_blank">
                                <i class="fab fa-youtube"></i>
                            </a>
                        </div>
                        <div class="sermon-header">
                            <i class="fas fa-hands-praying sermon-icon"></i>
                        </div>
                        <div class="sermon-content">
                            <h4>The Presence</h4>
                            <p class="sermon-meta">Preached by Pastor David Kituyi • June 22nd, 2025</p>
                            <p class="sermon-description"></p>
                            <div class="sermon-actions">
                                <button class="btn btn-outline-primary btn-sm me-2"><i class="fas fa-play"></i> Listen Now</button>
                                <button class="btn btn-outline-secondary btn-sm"><i class="fas fa-download"></i> Download</button>
                            </div>
                    
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="sermon-card">
                        <div class="sermon-thumbnail">
                            <img src="https://img.youtube.com/vi/5678901234/0.jpg" alt="Sermon Thumbnail" class="sermon-thumb">
                            <a href="https://www.youtube.com/watch?v=zMXhJT8gPHg" class="youtube-link" target="_blank">
                                <i class="fab fa-youtube"></i>
                            </a>
                        </div>
                        <div class="sermon-header">
                            <i class="fas fa-heart sermon-icon"></i>
                        </div>
                        <div class="sermon-content">
                            <h4>Stay</h4>
                            <p class="sermon-meta">Preached by Pastor David Kituyi • June 2nd, 2025</p>
                            <p class="sermon-description"></p>
                            <div class="sermon-actions">
                                <button class="btn btn-outline-primary btn-sm me-2"><i class="fas fa-play"></i> Listen Now</button>
                                <button class="btn btn-outline-secondary btn-sm"><i class="fas fa-download"></i> Download</button>
                            </div>
                        --  
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>-->

    <!-- Upcoming Events Section -->
    <!--
    <section id="events" class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <h2 class="section-title">Upcoming Events</h2>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="event-card">
                        <div class="event-banner" style="background-image: url('./assets/images/conference.jpg');">
                            <div class="event-date">
                                <span class="event-day">20</span>
                                <span class="event-month">Jul</span>
                                <a href="#ministries" class="btn btn-outline-light mt-3 learn-more-btn">Learn More</a>
</div>
                        </div>
                        <div class="event-content">
                            <h4>Summer Bible Conference</h4>
                            <div class="event-meta">
                                <div class="event-time">
                                    <i class="fas fa-clock"></i>
                                    <span>9:00 AM - 4:00 PM</span>
                                </div>
                                <div class="event-location">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span>Church Auditorium</span>
                                </div>
                            </div>
                            <p class="event-description">Join us for a day of biblical teaching, fellowship, and worship. Guest speakers will lead sessions on various topics. Special focus on Christ's preeminence in our lives and ministries.</p>
                            <div class="event-details">
                                <h5>Speakers:</h5>
                                <ul class="event-speakers">
                                    <li>Dr. John Smith (Keynote)</li>
                                    <li>Elder Mary Johnson</li>
                                    <li>Brother James Wilson</li>
                                </ul>
                                <h5>Topics:</h5>
                                <ul class="event-topics">
                                    <li>The Supremacy of Christ</li>
                                    <li>Practical Discipleship</li>
                                    <li>Living by Faith</li>
                                </ul>
                            </div>
                            <div class="event-actions">
                                <button class="btn btn-primary btn-sm me-2">Learn More</button>
                                <button class="btn btn-outline-primary btn-sm">Register Now</button>
                                <a href="#ministries" class="btn btn-outline-light mt-3 learn-more-btn">Learn More</a>
</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="event-card">
                        <div class="event-banner" style="background-image: url('./assets/images/outreach.jpg');">
                            <div class="event-date">
                                <span class="event-day">25</span>
                                <span class="event-month">Jul</span>
                                <a href="#ministries" class="btn btn-outline-light mt-3 learn-more-btn">Learn More</a>
</div>
                        </div>
                        <div class="event-content">
                            <h4>Community Outreach</h4>
                            <div class="event-meta">
                                <div class="event-time">
                                    <i class="fas fa-clock"></i>
                                    <span>2:00 PM - 5:00 PM</span>
                                </div>
                                <div class="event-location">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span>Local Community Center</span>
                                </div>
                            </div>
                            <p class="event-description">Join our outreach team as we serve our community through practical acts of love and sharing the gospel. This event includes food distribution, prayer ministry, and community engagement.</p>
                            <div class="event-details">
                                <h5>Activities:</h5>
                                <ul class="event-activities">
                                    <li>Food Distribution</li>
                                    <li>Prayer Ministry</li>
                                    <li>Community Engagement</li>
                                </ul>
                                <h5>What to Bring:</h5>
                                <ul class="event-items">
                                    <li>Comfortable Shoes</li>
                                    <li>Water Bottle</li>
                                    <li>Willing Heart</li>
                                </ul>
                            </div>
                            <div class="event-actions">
                                <button class="btn btn-primary btn-sm me-2">Details</button>
                                <button class="btn btn-outline-primary btn-sm">Sign Up</button>
                                <a href="#ministries" class="btn btn-outline-light mt-3 learn-more-btn">Learn More</a>
</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="event-card">
                        <div class="event-banner" style="background-image: url('./assets/images/youth-retreat.jpg');">
                            <div class="event-date">
                                <span class="event-day">29</span>
                                <span class="event-month">Jul</span>
                                <a href="#ministries" class="btn btn-outline-light mt-3 learn-more-btn">Learn More</a>
</div>
                        </div>
                        <div class="event-content">
                            <h4>Youth Retreat</h4>
                            <div class="event-meta">
                                <div class="event-time">
                                    <i class="fas fa-clock"></i>
                                    <span>All Day</span>
                                </div>
                                <div class="event-location">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span>Church Retreat Center</span>
                                </div>
                            </div>
                            <p class="event-description">A special day of spiritual growth and fellowship for our youth group. Includes worship, teaching, and fun activities designed to strengthen their faith and community bonds.</p>
                            <div class="event-details">
                                <h5>Activities:</h5>
                                <ul class="event-activities">
                                    <li>Worship Sessions</li>
                                    <li>Bible Studies</li>
                                    <li>Team Building</li>
                                </ul>
                                <h5>Requirements:</h5>
                                <ul class="event-requirements">
                                    <li>Permission Slip</li>
                                    <li>Worship Attire</li>
                                    <li>Personal Items</li>
                                </ul>
                            </div>
                            <div class="event-actions">
                                <button class="btn btn-primary btn-sm me-2">Learn More</button>
                                <button class="btn btn-outline-primary btn-sm">Register</button>
                                <a href="#ministries" class="btn btn-outline-light mt-3 learn-more-btn">Learn More</a>
</div>
                        </div>
                    </div>
                    -->
                </div>
            </div>
        </div>
    </section>

    <!-- Ministries Section -->
<section id="ministries" class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <h2 class="section-title">Our Ministries</h2>
                </div>
            </div>
            <div class="row g-4 ministry-grid">
                <div class="col-lg-3 col-md-6">
                    <div class="ministry-card">
                        <img src="./assets/images/sunday-school.jpg" alt="Children's Ministry" class="ministry-card-image">
                        <div class="ministry-card-content">
                            <div class="ministry-card-title">
                                <i class="fas fa-child service-icon"></i>
                                <h5>Children's Ministry</h5>
                            </div>
                            <div class="ministry-card-description">
                                Building young hearts for Christ through age-appropriate worship and biblical teaching.
                            </div>
                            <div class="ministry-card-actions">
                                <a href="/ministries/childrens-ministry" class="btn btn-outline-light">Learn More</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="ministry-card">
                        <img src="./assets/images/youths.jpg" alt="Youth Ministry" class="ministry-card-image">
                        <div class="ministry-card-content">
                            <div class="ministry-card-title">
                                <i class="fas fa-graduation-cap service-icon"></i>
                                <h5>Youth Ministry</h5>
                            </div>
                            <div class="ministry-card-description">
                                Empowering teenagers to live for Christ in today's world through discipleship and fellowship.
                            </div>
                            <div class="ministry-card-actions">
                                <a href="/ministries/youth-ministry" class="btn btn-outline-light">Learn More</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="ministry-card">
                        <img src="https://res.cloudinary.com/dtpevimcr/image/upload/v1763730950/ministers_lytcuc.jpg" alt="Worship Ministry" class="ministry-card-image">
                        <div class="ministry-card-content">
                            <div class="ministry-card-title">
                                <i class="fas fa-music service-icon"></i>
                                <h5>Worship Ministry</h5>
                            </div>
                            <div class="ministry-card-description">
                                Leading the congregation in heartfelt worship through music, praise, and spiritual expression.
                            </div>
                            <div class="ministry-card-actions">
                                <a href="/ministries/worship-team" class="btn btn-outline-light">Learn More</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="ministry-card">
                        <img src="./assets/images/sound_and_media.jpg" alt="Sound & Media Ministry" class="ministry-card-image">
                        <div class="ministry-card-content">
                            <div class="ministry-card-title">
                                <i class="fas fa-microphone service-icon"></i>
                                <h5>Sound & Media Ministry</h5>
                            </div>
                            <div class="ministry-card-description">
                                Enhancing worship experiences through audio, video, and media technology.
                            </div>
                            <div class="ministry-card-actions">
                                <a href="/ministries/sound-media-ministry" class="btn btn-outline-light">Learn More</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="ministry-card">
                        <img src="./assets/images/ushering.jpg" alt="Ushering Ministry" class="ministry-card-image">
                        <div class="ministry-card-content">
                            <div class="ministry-card-title">
                                <i class="fas fa-handshake service-icon"></i>
                                <h5>Ushering Ministry</h5>
                            </div>
                            <div class="ministry-card-description">
                                Welcoming and serving our congregation with hospitality and care.
                            </div>
                            <div class="ministry-card-actions">
                                <a href="/ministries/ushering-ministry" class="btn btn-outline-light">Learn More</a>
                            </div>
                        </div>
                    </div>
                </div>
        
                <div class="col-lg-3 col-md-6">
                    <div class="ministry-card">
                        <img src="./assets/images/mens.jpg" alt="Men's Ministry" class="ministry-card-image">
                        <div class="ministry-card-content">
                            <div class="ministry-card-title">
                                <i class="fas fa-users service-icon"></i>
                                <h5>Men's Ministry</h5>
                            </div>
                            <div class="ministry-card-description">
                                Building godly men through fellowship, discipleship, and spiritual accountability.
                            </div>
                            <div class="ministry-card-actions">
                                <a href="#" class="btn btn-outline-light">Learn More</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="ministry-card">
                        <img src="./assets/images/women.jpg" alt="Women's Ministry" class="ministry-card-image">
                        <div class="ministry-card-content">
                            <div class="ministry-card-title">
                                <i class="fas fa-heart service-icon"></i>
                                <h5>Women's Ministry</h5>
                            </div>
                            <div class="ministry-card-description">
                                Empowering women to grow in faith through Bible study, prayer, and mutual support.
                            </div>
                            <div class="ministry-card-actions">
                                <a href="#" class="btn btn-outline-light">Learn More</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="ministry-card">
                        <img src="./assets/images/mercy-visits.jpg" alt="Mercy Ministry" class="ministry-card-image">
                        <div class="ministry-card-content">
                            <div class="ministry-card-title">
                                <i class="fas fa-hands-helping service-icon"></i>
                                <h5>Mercy Ministry</h5>
                            </div>
                            <div class="ministry-card-description">
                                Showing Christ's love through compassionate care and support for those in need.
                            </div>
                            <div class="ministry-card-actions">
                                <a href="#" class="btn btn-outline-light">Learn More</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="ministry-card">
                        <img src="https://res.cloudinary.com/dtpevimcr/image/upload/v1763732158/prayer_and_intercession_e87lug.jpg" alt="Prayer & Intercession" class="ministry-card-image">
                        <div class="ministry-card-content">
                            <div class="ministry-card-title">
                                <i class="fas fa-hands-praying service-icon"></i>
                                <h5>Prayer & Intercession</h5>
                            </div>
                            <div class="ministry-card-description">
                                Seeking God's presence through focused prayer and intercession for our community and world.
                            </div>
                            <div class="ministry-card-actions">
                                <a href="#" class="btn btn-outline-light">Learn More</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <h2 class="section-title">Contact Us</h2>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Get in Touch</h5>
                            <div class="mb-3">
                                <i class="fas fa-map-marker-alt me-2" style="color: var(--primary-purple);"></i>
                                <strong>Address:</strong> Kabarak, Nakuru.
                            </div>
                            <div class="mb-3">
                                <i class="fas fa-phone me-2" style="color: var(--primary-purple);"></i>
                                <strong>Phone:</strong> (254) 724740854
                            </div>
                            <div class="mb-3">
                                <i class="fas fa-envelope me-2" style="color: var(--primary-purple);"></i>
                                <strong>Email:</strong> info@christekklesians.org
                            </div>

                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Send Us a Message</h5>
                            <form id="contactForm" method="POST" action="/includes/contact_handler.php">
                                <div class="mb-3">
                                    <input type="text" name="name" class="form-control" placeholder="Your Name" required>
                                </div>
                                <div class="mb-3">
                                    <input type="email" name="email" class="form-control" placeholder="Your Email" required>
                                </div>
                                <div class="mb-3">
                                    <input type="text" name="subject" class="form-control" placeholder="Subject">
                                </div>
                                <div class="mb-3">
                                    <textarea name="message" class="form-control" rows="4" placeholder="Your Message" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary" id="submitBtn">Send Message</button>
                                <div id="formMessages" class="mt-3"></div>
                            </form>
                            <script>
                            document.getElementById('contactForm').addEventListener('submit', function(e) {
                                e.preventDefault();
                                
                                const form = e.target;
                                const formData = new FormData(form);
                                const submitBtn = document.getElementById('submitBtn');
                                const messagesDiv = document.getElementById('formMessages');
                                
                                // Disable submit button
                                submitBtn.disabled = true;
                                submitBtn.innerHTML = 'Sending...';
                                messagesDiv.innerHTML = '';
                                
                                // Submit form via AJAX
                                fetch(form.action, {
                                    method: 'POST',
                                    body: formData
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.status === 'success') {
                                        // Show success message
                                        messagesDiv.className = 'alert alert-success';
                                        messagesDiv.innerHTML = data.message;
                                        form.reset();
                                    } else {
                                        // Show error message(s)
                                        messagesDiv.className = 'alert alert-danger';
                                        let errorHtml = data.message;
                                        if (data.errors) {
                                            errorHtml += '<ul class="mb-0">';
                                            data.errors.forEach(error => {
                                                errorHtml += `<li>${error}</li>`;
                                            });
                                            errorHtml += '</ul>';
                                        }
                                        messagesDiv.innerHTML = errorHtml;
                                    }
                                })
                                .catch(error => {
                                    /*messagesDiv.className = 'alert alert-danger';
                                    messagesDiv.textContent = 'An error occurred. Please try again.';
                                    console.error('Error:', error);*/
                                })
                                .finally(() => {
                                    submitBtn.disabled = false;
                                    submitBtn.innerHTML = 'Send Message';
                                    
                                    // Scroll to messages
                                    messagesDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                                });
                            });
                            </script>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    
</main>
<!-- End Main Content -->

<!-- Load Sermons and Events -->
<script>
// Load sermons dynamically
fetch('/sermons_api_enhanced.php')
  .then(res => res.json())
  .then(data => {
    if (data.success && data.sermons && Array.isArray(data.sermons)) {
      const container = document.getElementById('sermons-list');
      if (!container) return;
      
      // Show only the latest 3 sermons
      const latestSermons = data.sermons.slice(0, 3);
      
      if (latestSermons.length === 0) {
        container.innerHTML = '<div class="col-12"><p class="text-center text-muted">No sermons available yet. Check back soon!</p></div>';
        return;
      }
      
      container.innerHTML = latestSermons.map(sermon => `
        <div class="col-lg-4 col-md-6 mb-4">
          <div class="sermon-card h-100">
            <div class="sermon-thumbnail">
              <img src="${sermon.thumbnail || 'assets/images/default-sermon.jpg'}" alt="${sermon.title}" class="sermon-thumb">
              ${sermon.youtube ? `<a href="${sermon.youtube}" class="youtube-link" target="_blank" rel="noopener noreferrer"><i class="fab fa-youtube"></i></a>` : ''}
            </div>
            <div class="sermon-content">
              <h5 class="mt-3 mb-2">${sermon.title}</h5>
              <div class="sermon-meta mb-2">
                <span><i class="fas fa-calendar-alt me-1"></i> ${new Date(sermon.date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })}</span><br>
                <span><i class="fas fa-user me-1"></i> ${sermon.speaker}</span>
              </div>
              <p class="sermon-description">${sermon.description ? sermon.description.substring(0, 100) + (sermon.description.length > 100 ? '...' : '') : ''}</p>
              ${sermon.file_url ? `<a href="${sermon.file_url}" class="btn btn-sm btn-outline-primary mt-2" target="_blank"><i class="fas fa-play"></i> Watch</a>` : ''}
            </div>
          </div>
        </div>
      `).join('');
    }
  })
  .catch(err => {
    console.error('Error loading sermons:', err);
    const container = document.getElementById('sermons-list');
    if (container) container.innerHTML = '<div class="col-12"><p class="text-center text-muted">Unable to load sermons at this time.</p></div>';
  });

// Load events dynamically
fetch('/admin/events_api.php')
  .then(res => res.json())
  .then(data => {
    if (data.events && Array.isArray(data.events)) {
      const container = document.getElementById('events-list');
      if (!container) return;
      
      // Show only upcoming events (limit to 3)
      const upcomingEvents = data.events.filter(event => new Date(event.event_date) >= new Date()).slice(0, 3);
      
      if (upcomingEvents.length === 0) {
        container.innerHTML = '<div class="col-12"><p class="text-center text-muted">No upcoming events at this time. Check back soon!</p></div>';
        return;
      }
      
      container.innerHTML = upcomingEvents.map(event => `
        <div class="col-lg-4 col-md-6 mb-4">
          <div class="event-card h-100">
            <div class="event-banner" style="background-image:url('${event.banner || 'assets/images/default-event.jpg'}')">
              <div class="event-date">
                <span class="event-day">${new Date(event.event_date).getDate()}</span>
                <span class="event-month">${new Date(event.event_date).toLocaleString('default', { month: 'short' })}</span>
              </div>
            </div>
            <div class="event-content">
              <h5 class="mt-3 mb-2">${event.title}</h5>
              <div class="event-meta mb-2">
                <div><i class="fas fa-calendar-alt me-1"></i> ${new Date(event.event_date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })}</div>
                ${event.event_time ? `<div><i class="fas fa-clock me-1"></i> ${event.event_time}</div>` : ''}
                ${event.location ? `<div><i class="fas fa-map-marker-alt me-1"></i> ${event.location}</div>` : ''}
              </div>
              <p class="event-description">${event.description ? event.description.substring(0, 100) + (event.description.length > 100 ? '...' : '') : ''}</p>
            </div>
          </div>
        </div>
      `).join('');
    }
  })
  .catch(err => {
    console.error('Error loading events:', err);
    const container = document.getElementById('events-list');
    if (container) container.innerHTML = '<div class="col-12"><p class="text-center text-muted">Unable to load events at this time.</p></div>';
  });
</script>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
