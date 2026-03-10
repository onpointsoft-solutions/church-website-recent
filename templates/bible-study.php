<?php
/**
 * Bible Study Page - Christ Ekklesia Fellowship Chapel
 * 
 * Dedicated page for Bible study programs, schedules, and resources.
 */

$pageTitle = "Bible Study - Christ Ekklesia Fellowship Chapel";
$pageDescription = "Join our Bible study programs for spiritual growth and fellowship. Explore our weekly Bible study sessions, group discussions, and resources for deeper understanding of God's Word.";
$pageKeywords = "bible study, Bible fellowship, Christian education, spiritual growth, Bible study groups, Christ Ekklesia";
$pageType = "article";
$pageImage = "/assets/images/bible-study.jpg";

include dirname(__DIR__) . '/../includes/header.php';
?>

<!-- Main Content -->
<main id="main-content" class="bible-study-section container-fluid px-0">
    <!-- Hero Section -->
    <section class="hero-section" style="background: linear-gradient(135deg, var(--primary-purple), var(--secondary-purple)), url('/assets/images/bible-study.jpg') center/cover no-repeat; min-height: 500px; display: flex; align-items: center; position: relative;">
        <div class="container text-center text-white py-5">
            <div class="hero-content animate-fade-in">
                <h1 class="display-4 fw-bold mb-3">Bible Study</h1>
                <p class="lead mb-4">Growing Together in God's Word</p>
                <p class="mb-4">Join us for meaningful Bible study, fellowship, and spiritual growth as we explore the depths of God's Word together.</p>
                <div class="hero-breadcrumb">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb justify-content-center bg-transparent">
                            <li class="breadcrumb-item"><a href="/" class="text-white-50">Home</a></li>
                            <li class="breadcrumb-item active text-white" aria-current="page">Bible Study</li>
                        </ol>
                    </nav>
                </div>
                <div class="mt-3">
                    <a href="/bible-study.php/auth/login.php" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-sign-in-alt me-2"></i>
                        Member Login
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="container" style="max-width: 1200px; margin-bottom: 40px;">
        <!-- Bible Study Schedule -->
        <div class="card border-0 shadow-lg mb-5">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title mb-0">
                    <i class="fas fa-calendar-alt me-2"></i>
                    Weekly Bible Study Schedule
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="d-flex align-items-center">
                            <div class="study-day-icon">
                                <i class="fas fa-sun"></i>
                            </div>
                            <div>
                                <h5>Saturday Bible Study</h5>
                                <p class="text-muted mb-2">Every Saturday</p>
                                <p class="fw-bold">2:00 PM - 4:00 PM</p>
                                <p class="small">Join us for in-depth study and discussion</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="d-flex align-items-center">
                            <div class="study-day-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div>
                                <h5>Midweek Groups</h5>
                                <p class="text-muted mb-2">Various Days</p>
                                <p class="fw-bold">Contact for schedule</p>
                                <p class="small">Small group studies throughout the week</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Study Programs -->
        <div class="row mb-5">
            <div class="col-lg-4 mb-4">
                <div class="card h-100 border-0 shadow">
                    <div class="card-body text-center">
                        <div class="program-icon mb-3">
                            <i class="fas fa-book-open" style="font-size: 3rem; color: var(--primary-purple);"></i>
                        </div>
                        <h4 class="card-title">Foundational Studies</h4>
                        <p class="card-text">Explore the foundations of faith through systematic Bible study</p>
                        <a href="/contact" class="btn btn-primary mt-3">Get Started</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb-4">
                <div class="card h-100 border-0 shadow">
                    <div class="card-body text-center">
                        <div class="program-icon mb-3">
                            <i class="fas fa-comments" style="font-size: 3rem; color: var(--accent-gold);"></i>
                        </div>
                        <h4 class="card-title">Group Discussions</h4>
                        <p class="card-text">Interactive Bible study with fellowship and sharing</p>
                        <a href="/contact" class="btn btn-primary mt-3">Join a Group</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb-4">
                <div class="card h-100 border-0 shadow">
                    <div class="card-body text-center">
                        <div class="program-icon mb-3">
                            <i class="fas fa-graduation-cap" style="font-size: 3rem; color: var(--accent-gold);"></i>
                        </div>
                        <h4 class="card-title">Youth Programs</h4>
                        <p class="card-text">Age-appropriate Bible study for young people</p>
                        <a href="/contact" class="btn btn-primary mt-3">Youth Programs</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resources Section -->
        <div class="card border-0 shadow-lg mb-5">
            <div class="card-header bg-secondary text-white">
                <h3 class="card-title mb-0">
                    <i class="fas fa-book me-2"></i>
                    Study Resources
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <h5><i class="fas fa-download me-2"></i> Study Materials</h5>
                        <p>Access downloadable study guides and materials</p>
                        <ul class="list-unstyled">
                            <li>Weekly study outlines</li>
                            <li>Discussion questions</li>
                            <li>Prayer guides</li>
                        </ul>
                    </div>
                    <div class="col-md-6 mb-3">
                        <h5><i class="fas fa-video me-2"></i> Online Resources</h5>
                        <p>Video teachings and recorded sessions</p>
                        <ul class="list-unstyled">
                            <li>Sermon archives</li>
                            <li>Study playlists</li>
                            <li>Live stream options</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Call to Action -->
        <div class="card border-0 shadow-lg mt-5">
            <div class="card-body text-center">
                <h4 class="mb-3">Ready to Deepen Your Faith?</h4>
                <p class="mb-4">Join our Bible study community and grow in your understanding of God's Word. Whether you're new to Bible study or have been studying for years, there's a place for you to learn, share, and grow together.</p>
                <div class="d-flex gap-3 justify-content-center">
                    <a href="/contact" class="btn btn-primary btn-lg me-2">
                        <i class="fas fa-praying-hands me-2"></i>
                        Join Bible Study
                    </a>
                    <a href="/bible-study.php/auth/login.php" class="btn btn-outline-primary btn-lg">
                        <i class="fas fa-sign-in-alt me-2"></i>
                        Member Login
                    </a>
                    <a href="/volunteers" class="btn btn-outline-primary btn-lg">
                        <i class="fas fa-hands-helping me-2"></i>
                        Volunteer to Lead
                    </a>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
.bible-study-section {
    padding-top: 0;
}

.hero-section {
    position: relative;
    overflow: hidden;
}

.hero-content {
    position: relative;
    z-index: 2;
}

.study-day-icon {
    width: 60px;
    height: 60px;
    background: var(--accent-gold);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    flex-shrink: 0;
}

.study-day-icon i {
    font-size: 1.5rem;
    color: white;
}

.program-icon {
    width: 80px;
    height: 80px;
    background: var(--accent-gold);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    flex-shrink: 0;
}

.program-icon i {
    font-size: 2.5rem;
    color: white;
}

.card-title {
    color: var(--primary-purple);
    font-weight: 600;
    margin-bottom: 1rem;
}

.card-text {
    color: var(--text-light);
    line-height: 1.6;
}

@media (max-width: 768px) {
    .study-day-icon,
    .program-icon {
        width: 50px;
        height: 50px;
        margin-right: 0.5rem;
    }
    
    .study-day-icon i,
    .program-icon i {
        font-size: 1.2rem;
    }
}
</style>

<?php include dirname(__DIR__) . '/../includes/footer.php'; ?>
