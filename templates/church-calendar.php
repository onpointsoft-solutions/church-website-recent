<?php 
/**
 * Church Calendar Page - Christ Ekklesia Fellowship Chapel
 * 
 * Production-ready calendar page with SEO optimization and enhanced content.
 */

// SEO Configuration
$pageTitle = "Events of the Year - Christ Ekklesia Fellowship Chapel | Year of Divine Encounters";
$pageDescription = "Discover our Events of the Year at Christ Ekklesia Fellowship Chapel. Join us for youth services, church cleaning days, outings, and special encounters in our Year of Divine Encounters.";
$pageKeywords = "church events, divine encounters, youth service, church calendar, ministry events, worship services, youth ministry, evangelism, community outreach, Christ Ekklesia events, Kabarak church calendar";
$pageType = "article";
$pageImage = "/assets/images/calendar-hero.jpg";

// Page-specific scripts
$pageScripts = [
    '/assets/js/calendar-page.js'
];

include dirname(__DIR__) . '/includes/header.php'; 
?>

<!-- Main Content -->
<main id="main-content" class="calendar-section container-fluid px-0">
    <!-- Calendar Hero Section -->
    <section class="hero-section position-relative mb-5" style="background: linear-gradient(rgba(96, 55, 158, 0.8), rgba(142, 68, 173, 0.8)), url('/assets/images/calendar-hero.jpg') center/cover no-repeat; min-height: 400px; display: flex; align-items: center; justify-content: center;">
        <div class="container text-center text-white py-5">
            <div class="hero-content animate-fade-in">
                <h1 class="display-4 fw-bold mb-3" style="text-shadow: 0 4px 20px rgba(0,0,0,0.3);">Events of the Year</h1>
                <p class="lead mb-4">Our Year of Divine Encounters</p>
                <div class="hero-breadcrumb">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb justify-content-center bg-transparent">
                            <li class="breadcrumb-item"><a href="/" class="text-white-50">Home</a></li>
                            <li class="breadcrumb-item active text-white" aria-current="page">Calendar 2025</li>
                        </ol>
                    </nav>
                </div>
                <div class="calendar-year-badge">
                    <span class="badge bg-light text-primary fs-6 px-4 py-2">Year of Divine Encounters</span>
                </div>
            </div>
        </div>
    </section>
    <div class="container" style="max-width: 1100px; margin-bottom: 40px;">
        <!-- Introduction Card -->
        <div class="card border-0 shadow-lg mb-5 animate-fade-in" style="border-radius: 15px; overflow: hidden;">
            <div class="card-body p-4">
                <div class="row align-items-center">
                    <div class="col-md-2 text-center">
                        <i class="fas fa-calendar-check" style="font-size: 4rem; color: var(--accent-gold);"></i>
                    </div>
                    <div class="col-md-10">
                        <h2 class="mb-2" style="font-family: 'Playfair Display', serif; color: var(--primary-purple);">Year of Divine Encounters</h2>
                        <p class="mb-0 text-muted">Join us throughout the year for special encounters with the divine through worship, fellowship, and service. Experience God's presence in every season.</p>
                    </div>
                </div>
            </div>
        </div>
        <!-- Events Calendar Section -->
        <div class="events-calendar">
            
            <!-- FEBRUARY -->
            <div class="calendar-section calendar-quarter animate-fade-in">
                <div class="row align-items-center mb-3">
                    <div class="col-md-4">
                        <img src="../assets/images/youths.jpg" alt="February Events" class="rounded shadow" style="width:100%; height:auto; object-fit:cover;">
                    </div>
                    <div class="col-md-8">
                        <div class="d-flex align-items-center mb-3">
                            <span class="badge bg-danger me-2" style="font-size: 1.2rem; padding: 0.5rem 1rem;">FEB</span>
                            <h3 class="mb-0"><span class="icon">❄️</span> FEBRUARY</h3>
                        </div>
                        <p class="text-muted">Youth Engagement & Service</p>
                    </div>
                </div>
                <div class="events-list">
                    <div class="event-item">
                        <div class="event-date"><strong>15th Feb (Sun)</strong></div>
                        <div class="event-details">
                            <h5>Youth Service</h5>
                            <p class="mb-0">Special youth-led worship service</p>
                        </div>
                    </div>
                    <div class="event-item">
                        <div class="event-date"><strong>21st Feb (Sat)</strong></div>
                        <div class="event-details">
                            <h5>Church Cleaning Day</h5>
                            <p class="mb-0"><i class="fas fa-clock me-1"></i>9:00 AM – 12:00 PM</p>
                            <p class="mb-0">Community service to prepare our worship space</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- MARCH -->
            <div class="calendar-section calendar-quarter animate-fade-in">
                <div class="row align-items-center mb-3">
                    <div class="col-md-4">
                        <img src="../assets/images/youths.jpg" alt="March Events" class="rounded shadow" style="width:100%; height:auto; object-fit:cover;">
                    </div>
                    <div class="col-md-8">
                        <div class="d-flex align-items-center mb-3">
                            <span class="badge bg-success me-2" style="font-size: 1.2rem; padding: 0.5rem 1rem;">MAR</span>
                            <h3 class="mb-0"><span class="icon">🌱</span> MARCH</h3>
                        </div>
                        <p class="text-muted">Youth Outings & Fellowships</p>
                    </div>
                </div>
                <div class="events-list">
                    <div class="event-item">
                        <div class="event-date"><strong>14th Mar (Sat)</strong></div>
                        <div class="event-details">
                            <h5>Youth Outing</h5>
                            <p class="mb-0"><i class="fas fa-money-bill-wave me-1"></i>Contribution: KSh 900–1,300 per person</p>
                            <p class="mb-0"><i class="fas fa-map-marker-alt me-1"></i>Venue: To be confirmed</p>
                        </div>
                    </div>
                    <div class="event-item">
                        <div class="event-date"><strong>21st Mar (Sat)</strong></div>
                        <div class="event-details">
                            <h5>Joint Fellowships</h5>
                            <p class="mb-0">Unity gathering with fellow ministries</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- APRIL -->
            <div class="calendar-section calendar-quarter animate-fade-in">
                <div class="row align-items-center mb-3">
                    <div class="col-md-4">
                        <img src="../assets/images/outreach.jpg" alt="April Events" class="rounded shadow" style="width:100%; height:auto; object-fit:cover;">
                    </div>
                    <div class="col-md-8">
                        <div class="d-flex align-items-center mb-3">
                            <span class="badge bg-warning me-2" style="font-size: 1.2rem; padding: 0.5rem 1rem;">APR</span>
                            <h3 class="mb-0"><span class="icon">🌸</span> APRIL</h3>
                        </div>
                        <p class="text-muted">Evangelism & Services</p>
                    </div>
                </div>
                <div class="events-list">
                    <div class="event-item">
                        <div class="event-date"><strong>To be announced</strong></div>
                        <div class="event-details">
                            <h5>Evangelism</h5>
                            <p class="mb-0">Community outreach and soul-winning</p>
                        </div>
                    </div>
                    <div class="event-item">
                        <div class="event-date"><strong>To be confirmed</strong></div>
                        <div class="event-details">
                            <h5>Men's Service</h5>
                            <p class="mb-0">Men's ministry special service</p>
                        </div>
                    </div>
                    <div class="event-item">
                        <div class="event-date"><strong>To be announced</strong></div>
                        <div class="event-details">
                            <h5>Constitution Meeting</h5>
                            <p class="mb-0">Leadership and governance session</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- JULY -->
            <div class="calendar-section calendar-quarter animate-fade-in">
                <div class="row align-items-center mb-3">
                    <div class="col-md-4">
                        <img src="../assets/images/women.jpg" alt="July Events" class="rounded shadow" style="width:100%; height:auto; object-fit:cover;">
                    </div>
                    <div class="col-md-8">
                        <div class="d-flex align-items-center mb-3">
                            <span class="badge bg-info me-2" style="font-size: 1.2rem; padding: 0.5rem 1rem;">JUL</span>
                            <h3 class="mb-0"><span class="icon">☀️</span> JULY</h3>
                        </div>
                        <p class="text-muted">Women's Ministry</p>
                    </div>
                </div>
                <div class="events-list">
                    <div class="event-item">
                        <div class="event-date"><strong>To be confirmed</strong></div>
                        <div class="event-details">
                            <h5>Ladies Service</h5>
                            <p class="mb-0">Women's ministry special gathering</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- AUGUST -->
            <div class="calendar-section calendar-quarter animate-fade-in">
                <div class="row align-items-center mb-3">
                    <div class="col-md-4">
                        <img src="../assets/images/sunday-school.jpg" alt="August Events" class="rounded shadow" style="width:100%; height:auto; object-fit:cover;">
                    </div>
                    <div class="col-md-8">
                        <div class="d-flex align-items-center mb-3">
                            <span class="badge bg-primary me-2" style="font-size: 1.2rem; padding: 0.5rem 1rem;">AUG</span>
                            <h3 class="mb-0"><span class="icon">🎒</span> AUGUST</h3>
                        </div>
                        <p class="text-muted">Children & Youth Ministry</p>
                    </div>
                </div>
                <div class="events-list">
                    <div class="event-item">
                        <div class="event-date"><strong>To be announced</strong></div>
                        <div class="event-details">
                            <h5>VBS for Sunday School & Teens</h5>
                            <p class="mb-0">Vacation Bible School program</p>
                        </div>
                    </div>
                    <div class="event-item">
                        <div class="event-date"><strong>To be announced</strong></div>
                        <div class="event-details">
                            <h5>Children's Crusade & Service</h5>
                            <p class="mb-0">Special children's ministry event</p>
                        </div>
                    </div>
                    <div class="event-item">
                        <div class="event-date"><strong>Saturday following VBS week</strong></div>
                        <div class="event-details">
                            <h5>Children's Outing</h5>
                            <p class="mb-0">Fun fellowship activity for children</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- OCTOBER -->
            <div class="calendar-section calendar-quarter animate-fade-in">
                <div class="row align-items-center mb-3">
                    <div class="col-md-4">
                        <img src="../assets/images/outdoor.jpg" alt="October Events" class="rounded shadow" style="width:100%; height:auto; object-fit:cover;">
                    </div>
                    <div class="col-md-8">
                        <div class="d-flex align-items-center mb-3">
                            <span class="badge bg-secondary me-2" style="font-size: 1.2rem; padding: 0.5rem 1rem;">OCT</span>
                            <h3 class="mb-0"><span class="icon">🍂</span> OCTOBER</h3>
                        </div>
                        <p class="text-muted">Divine Encounters</p>
                    </div>
                </div>
                <div class="events-list">
                    <div class="event-item">
                        <div class="event-date"><strong>To be announced</strong></div>
                        <div class="event-details">
                            <h5>Special Events</h5>
                            <p class="mb-0">Join us for special encounters with the divine</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <div class="admin-notes mb-5 animate-fade-in">
            <div class="d-flex align-items-center mb-3">
                <i class="fas fa-info-circle me-2" style="font-size: 2rem; color: var(--accent-gold);"></i>
                <h4 class="mb-0">Administrative Notes</h4>
            </div>
            <ol>
                <li><b>Church Registration:</b> Formally completed on April 9, 2025 (Reg. No. 32402, Republic of Kenya).</li>
                <li><b>Leadership Fasting:</b> Quarterly spiritual discipline (dates TBA).</li>
                <li><b>Financial Priorities:</b>
                    <ul>
                        <li>Clearance of outstanding debts</li>
                        <li>Management of recurrent expenditures</li>
                    </ul>
                </li>
            </ol>
            <div class="calendar-meta">All dates subject to confirmation. Watch bulletins for updates.</div>
        </div>
        <div class="text-center mb-4">
            <span class="text-muted">For details, contact: <a href="mailto:info@christekklesians.org">info@christekklesians.org</a>.</span>
        </div>
    </div>
    
</div>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
