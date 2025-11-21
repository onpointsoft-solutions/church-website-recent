<?php 
/**
 * Youth Ministry Page - Christ Ekklesia Fellowship Chapel
 * 
 * Production-ready youth ministry page with SEO optimization and enhanced content.
 */

// SEO Configuration
$pageTitle = "Youth Ministry - Christ Ekklesia Fellowship Chapel | Empowering Young Leaders";
$pageDescription = "Join our vibrant Youth Ministry at Christ Ekklesia Fellowship Chapel. Raising a Christ-centered generation equipped to live and lead for Jesus in Kabarak, Nakuru.";
$pageKeywords = "youth ministry, young adults, teenagers, Christian youth, discipleship, Kabarak youth, Christ Ekklesia youth, church youth group";
$pageType = "article";
$pageImage = "https://res.cloudinary.com/dtpevimcr/image/upload/v1763730953/hero_af9pf9.jpg";

// Page-specific scripts
$pageScripts = [
    '/assets/js/ministry-page.js'
];

include dirname(__DIR__) . '/../includes/header.php'; 
?>

<!-- Main Content -->
<main id="main-content" class="ministry-section container-fluid px-0">
    <!-- Ministry Hero Section -->
    <section class="ministry-hero-section position-relative mb-5" style="background: linear-gradient(rgba(96, 55, 158, 0.8), rgba(142, 68, 173, 0.8)), url('https://res.cloudinary.com/dtpevimcr/image/upload/v1763730953/hero_af9pf9.jpg') center/cover no-repeat; min-height: 400px; display: flex; align-items: center; justify-content: center;">
        <div class="container text-center text-white py-5">
            <div class="hero-content animate-fade-in">
                <h1 class="display-4 fw-bold mb-3" style="text-shadow: 0 4px 20px rgba(0,0,0,0.3);">Youth Ministry</h1>
                <p class="lead mb-4">Raising a Christ-centered generation equipped to live and lead for Jesus</p>
                <div class="hero-breadcrumb">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb justify-content-center bg-transparent">
                            <li class="breadcrumb-item"><a href="/" class="text-white-50">Home</a></li>
                            <li class="breadcrumb-item"><a href="/#ministries" class="text-white-50">Ministries</a></li>
                            <li class="breadcrumb-item active text-white" aria-current="page">Youth Ministry</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </section>

    <!-- Ministry Content -->
    <div class="container py-5">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="ministry-intro mb-5 text-center">
                    <img src="https://res.cloudinary.com/dtpevimcr/image/upload/v1763730953/hero_af9pf9.jpg" alt="Youth Ministry" class="img-fluid rounded shadow mb-4" loading="lazy">
                    <p class="lead">Our Youth Ministry is passionate about empowering the next generation to know God deeply, live boldly for Christ, and impact their world with the Gospel. We believe young people are not just the church of tomorrow, but the church of today.</p>
                </div>

                <!-- Ministry Overview -->
                <div class="ministry-overview mb-5">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="mb-3">Our Mission</h2>
                            <p class="mb-3">To disciple teenagers and young adults to know God deeply, walk in holiness, and serve their church and community with boldness and love. We create an environment where young people can grow spiritually, build meaningful relationships, and discover their God-given purpose.</p>
                            <div class="ministry-stats">
                                <div class="row text-center">
                                    <div class="col-4">
                                        <div class="stat-item">
                                            <h4 class="stat-number text-primary">50+</h4>
                                            <p class="stat-label small">Active Youth</p>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="stat-item">
                                            <h4 class="stat-number text-primary">12-25</h4>
                                            <p class="stat-label small">Age Range</p>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="stat-item">
                                            <h4 class="stat-number text-primary">Weekly</h4>
                                            <p class="stat-label small">Meetings</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="ministry-icon-large">
                                <i class="fas fa-users" style="font-size: 4rem; color: var(--primary-color);"></i>
                            </div>
                        </div>
                    </div>
                </div>

                    <div class="ministry-section mt-5">
                        <h3 class="mb-4" style="color: var(--primary-color); font-family: 'Playfair Display', serif;">Our Vision</h3>
                        <p>To disciple teenagers and young adults to know God deeply, walk in holiness, and serve their church and community with boldness and love.</p>
                    </div>

                    <div class="ministry-section mt-5">
                        <h3 class="mb-4" style="color: var(--primary-color); font-family: 'Playfair Display', serif;">Core Pillars</h3>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="guideline-card"><div class="card-mask"></div>
                                    <h4>Discipleship</h4>
                                    <p>Grounding youths in Scripture through Bible study, mentorship, and accountability groups.</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="guideline-card"><div class="card-mask"></div>
                                    <h4>Worship & Prayer</h4>
                                    <p>Cultivating a lifestyle of worship and intercession that ignites personal and corporate revival.</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="guideline-card"><div class="card-mask"></div>
                                    <h4>Fellowship</h4>
                                    <p>Building Christ-centered relationships through small groups, events, and service opportunities.</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="guideline-card"><div class="card-mask"></div>
                                    <h4>Mission</h4>
                                    <p>Empowering youths to share the Gospel and serve their communities through outreaches and acts of mercy.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="ministry-section mt-5">
                        <h3 class="mb-4" style="color: var(--primary-color); font-family: 'Playfair Display', serif;">Programs</h3>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check-circle me-2"></i> Weekly Bible Study & Small Groups</li>
                            <li><i class="fas fa-check-circle me-2"></i> Youth Worship Nights</li>
                            <li><i class="fas fa-check-circle me-2"></i> Leadership Development & Mentorship</li>
                            <li><i class="fas fa-check-circle me-2"></i> Community Outreach & Missions</li>
                            <li><i class="fas fa-check-circle me-2"></i> Sports, Arts, and Talent Development</li>
                        </ul>
                    </div>

                    <div class="ministry-section mt-5">
                        <h3 class="mb-4" style="color: var(--primary-color); font-family: 'Playfair Display', serif;">Meeting Times</h3>
                        <div class="rehearsal-info">
                            <p><i class="fas fa-calendar me-2"></i> Youth Service: Every Sunday, 9:00 AM – 10:30 AM</p>
                            <p><i class="fas fa-users me-2"></i> Small Groups: Midweek (days vary by group)</p>
                            <p><i class="fas fa-map-marker-alt me-2"></i> Venue: Church Youth Hall</p>
                        </div>
                    </div>

                    <div class="ministry-section mt-5">
                        <h3 class="mb-4" style="color: var(--primary-color); font-family: 'Playfair Display', serif;">Get Involved</h3>
                        <p>We welcome teenagers and young adults to grow with us. Whether you’re passionate about worship, discipleship, media, or outreach—we have a place for you.</p>
                        <a href="/volunteers.php" class="btn btn-primary">Volunteer</a>
                    </div>

                    <div class="ministry-section mt-5">
                        <h3 class="mb-4" style="color: var(--primary-color); font-family: 'Playfair Display', serif;">Contact</h3>
                        <div class="contact-info">
                            <p><i class="fas fa-envelope me-2"></i>Email: youth@christekklesians.org</p>
                            <p><i class="fas fa-phone me-2"></i>Phone: (254) 700000000</p>
                            <p><i class="fas fa-user me-2"></i>Youth Leader: To be announced</p>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<?php include dirname(__DIR__) . '/../includes/footer.php'; ?>
