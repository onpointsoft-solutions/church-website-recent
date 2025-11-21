<?php
$pageTitle = "Ushering Ministry | Christ Ekklesia Fellowship Chapel";
include dirname(__DIR__) . '/../includes/header.php';
?>

<style>
  :root {
    --primary-purple: #4c1d95;
    --secondary-purple: #6b21a8;
    --light-purple: #f3e8ff;
    --accent-purple: #8b5cf6;
    --accent-gold: #f59e0b;
    --dark-purple: #312e81;
    --text-dark: #1f2937;
    --text-light: #6b7280;
    --white: #ffffff;
    --off-white: #f9fafb;
    --gradient-primary: linear-gradient(135deg, var(--primary-purple) 0%, var(--secondary-purple) 100%);
    --gradient-secondary: linear-gradient(135deg, var(--accent-gold) 0%, var(--accent-purple) 100%);
    --shadow-lg: 0 8px 32px rgba(76, 29, 149, 0.15);
    --shadow-xl: 0 16px 48px rgba(76, 29, 149, 0.20);
}

    .hero-banner {
        background: var(--gradient-primary);
        color: white;
        padding: 80px 0;
        position: relative;
        overflow: hidden;
    }

    .hero-banner::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('https://res.cloudinary.com/dtpevimcr/image/upload/v1763730951/ushering_pyeaaw.jpg') center/cover;
        opacity: 0.3;
        z-index: 1;
    }

    .hero-content {
        position: relative;
        z-index: 2;
        text-align: center;
    }

    .hero-title {
        font-size: 3.5rem;
        font-weight: 700;
        margin-bottom: 1rem;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
    }

    .hero-subtitle {
        font-size: 1.4rem;
        font-weight: 300;
        opacity: 0.9;
    }

    .floating-card {
        background: white;
        border-radius: 20px;
        box-shadow: var(--shadow-medium);
        transition: all 0.3s ease;
        border: none;
        overflow: hidden;
    }

    .floating-card:hover {
        transform: translateY(-10px);
        box-shadow: var(--shadow-heavy);
    }

    .card-header-custom {
        background: var(--primary-gradient);
        color: white;
        border: none;
        padding: 1.5rem;
        font-weight: 600;
        font-size: 1.2rem;
    }

    .icon-badge {
        width: 60px;
        height: 60px;
        background: var(--gold-gradient);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
        margin-right: 1rem;
        box-shadow: var(--shadow-light);
    }

    .value-card {
        background: linear-gradient(135deg, #f8f9ff 0%, #ffffff 100%);
        border-radius: 15px;
        padding: 2rem;
        margin-bottom: 1.5rem;
        box-shadow: var(--shadow-light);
        border-left: 5px solid #667eea;
        transition: all 0.3s ease;
    }

    .value-card:hover {
        transform: translateX(10px);
        box-shadow: var(--shadow-medium);
    }

    .duty-section {
        background: white;
        border-radius: 15px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: var(--shadow-light);
        border-top: 4px solid #667eea;
    }

    .duty-header {
        color: #667eea;
        font-weight: 600;
        font-size: 1.3rem;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
    }

    .emergency-card {
        background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
        color: white;
        border-radius: 15px;
        padding: 2rem;
        box-shadow: var(--shadow-lg);
    }

    .emergency-card h4 {
        color: white;
        font-weight: 600;
    }

    .stats-container {
        background: var(--gradient-primary);
        color: white;
        border-radius: 20px;
        padding: 3rem 2rem;
        margin: 3rem 0;
        text-align: center;
    }

    .stat-item {
        text-align: center;
        padding: 1rem;
    }

    .stat-number {
        font-size: 3rem;
        font-weight: 700;
        display: block;
        color: #ffd700;
    }

    .stat-label {
        font-size: 1rem;
        opacity: 0.9;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .timeline {
        position: relative;
        padding: 2rem 0;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
        top: 0;
        bottom: 0;
        width: 4px;
        background: var(--gradient-primary);
    }

    .timeline-item {
        position: relative;
        margin-bottom: 3rem;
        width: 50%;
    }

    .timeline-item:nth-child(odd) {
        left: 0;
        padding-right: 3rem;
        text-align: right;
    }

    .timeline-item:nth-child(even) {
        left: 50%;
        padding-left: 3rem;
    }

    .timeline-marker {
        position: absolute;
        top: 0;
        width: 20px;
        height: 20px;
        background: #667eea;
        border-radius: 50%;
        right: -10px;
        box-shadow: 0 0 0 4px white, 0 0 0 8px #667eea;
    }

    .timeline-item:nth-child(even) .timeline-marker {
        left: -10px;
    }

    .cta-section {
        background: var(--secondary-gradient);
        color: white;
        border-radius: 20px;
        padding: 4rem 2rem;
        text-align: center;
        margin: 4rem 0;
    }

    .btn-custom {
        background: var(--gradient-secondary);
        border: none;
        color: white;
        padding: 12px 30px;
        border-radius: 25px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        transition: all 0.3s ease;
        box-shadow: var(--shadow-light);
    }

    .btn-custom:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-medium);
        color: white;
    }

    .qualification-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
        margin: 2rem 0;
    }

    .qualification-item {
        background: white;
        padding: 1.5rem;
        border-radius: 10px;
        box-shadow: var(--shadow-light);
        display: flex;
        align-items: center;
        transition: all 0.3s ease;
    }

    .qualification-item:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-medium);
    }

    .accordion-custom .accordion-button {
        background: var(--gradient-primary);
        color: white;
        border: none;
        font-weight: 600;
    }

    .accordion-custom .accordion-button:not(.collapsed) {
        background: var(--gradient-primary);
        color: white;
        box-shadow: none;
    }

    .accordion-custom .accordion-button:focus {
        box-shadow: none;
        border: none;
    }

    .ministry-metric {
        text-align: center;
        padding: 2rem;
        background: white;
        border-radius: 15px;
        box-shadow: var(--shadow-lg);
        transition: all 0.3s ease;
    }

    .ministry-metric:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-medium);
    }

    .metric-icon {
        font-size: 3rem;
        color: #667eea;
        margin-bottom: 1rem;
    }

    @media (max-width: 768px) {
        .hero-title {
            font-size: 2.5rem;
        }
        
        .timeline::before {
            left: 20px;
        }
        
        .timeline-item {
            width: 100%;
            left: 0 !important;
            padding-left: 3rem !important;
            padding-right: 1rem !important;
            text-align: left !important;
        }
        
        .timeline-marker {
            left: 10px !important;
            right: auto !important;
        }
    }
</style>

<!-- Hero Banner -->
<div class="hero-banner">
    <div class="container">
        <div class="hero-content">
            <h1 class="hero-title">Ushering Ministry</h1>
            <p class="hero-subtitle">Doorkeepers, Greeters, and Spiritual Ambassadors for Christ</p>
        </div>
    </div>
</div>

<div class="container my-5">
    <!-- Ministry Overview -->
    <div class="row mb-5">
        <div class="col-lg-8 mx-auto">
            <div class="floating-card p-4 mb-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="icon-badge">
                        <i class="fas fa-door-open"></i>
                    </div>
                    <div>
                        <h3 class="mb-1">Who is an Usher?</h3>
                        <p class="text-muted mb-0">Understanding the role and calling</p>
                    </div>
                </div>
                <p class="lead">An usher at Christ Fellowship Chapel is a dedicated individual who serves as a doorkeeper and plays a vital role within the church community. Their responsibilities go beyond opening doors—they provide first impressions, serve as spiritual ambassadors, and help set the tone for worship.</p>
                <blockquote class="blockquote text-center">
                    <p class="mb-0"><em>"Whatever you do, work at it with all your heart, as working for the Lord..."</em></p>
                    <footer class="blockquote-footer mt-2">Colossians 3:23-24</footer>
                </blockquote>
            </div>
        </div>
    </div>

    <!-- Ministry Statistics -->
    <div class="stats-container">
        <div class="row">
            <div class="col-md-4">
                <div class="stat-item">
                    <span class="stat-number">3</span>
                    <span class="stat-label">Month Probation</span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-item">
                    <span class="stat-number">30</span>
                    <span class="stat-label">Minutes Early Arrival</span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-item">
                    <span class="stat-number">8</span>
                    <span class="stat-label">Core Values</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Ministry Gallery -->
    <div class="enhanced-card fade-in">
        <div class="card-header">
            <div class="card-icon">
                <i class="fas fa-images"></i>
            </div>
            <h2 class="card-title">Ushering Ministry Gallery</h2>
        </div>
        <div class="gallery-grid">
            <div class="gallery-item">
                <img src="../../assets/images/ushering-in-action.jpg" alt="Ushering Team" class="gallery-image">
                <div class="gallery-overlay">
                    <h4>Ushering Team</h4>
                    <p>Our dedicated ushers welcoming and assisting the congregation with a smile.</p>
                </div>
            </div>
            <div class="gallery-item">
                <img src="../../assets/images/outdoor-ushering.jpg" alt="Outdoor Fellowship" class="gallery-image">
                <div class="gallery-overlay">
                    <h4>Outdoor Fellowship</h4>
                    <p>Ushers supporting special events and outdoor gatherings for the church community.</p>
                </div>
            </div>
            <div class="gallery-item">
                <img src="../../assets/images/prayer.jpg" alt="Prayer Support" class="gallery-image">
                <div class="gallery-overlay">
                    <h4>Prayer Support</h4>
                    <p>Ushering team members praying together before and after services.</p>
                </div>
            </div>
            <div class="gallery-item">
                <img src="../../assets/images/ushering-prayer.jpg" alt="Women Ushers" class="gallery-image">
                <div class="gallery-overlay">
                    <h4>Women Ushers</h4>
                    <p>Empowering women in ministry through service and hospitality.</p>
                </div>
            </div>
            <div class="gallery-item">
                <img src="../../assets/images/logo.jpg" alt="Church Logo" class="gallery-image">
                <div class="gallery-overlay">
                    <h4>Church Logo</h4>
                    <p>Representing the spirit and mission of Christ Ekklesia Fellowship Chapel.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Core Values Section -->
    <div class="row mb-5">
        <div class="col-12">
            <h2 class="text-center mb-4" style="color: var(--primary-color);">Our Core Values</h2>
            <div class="row">
                <div class="col-lg-6">
                    <div class="value-card">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-gem text-primary me-3" style="font-size: 1.5rem;"></i>
                            <div>
                                <h5 class="mb-1">Honesty</h5>
                                <p class="mb-0">Transparency and trust in all interactions.</p>
                            </div>
                        </div>
                    </div>
                    <div class="value-card">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-shield-alt text-primary me-3" style="font-size: 1.5rem;"></i>
                            <div>
                                <h5 class="mb-1">Integrity</h5>
                                <p class="mb-0">High moral and ethical principles, reflecting Christ.</p>
                            </div>
                        </div>
                    </div>
                    <div class="value-card">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-hourglass-half text-primary me-3" style="font-size: 1.5rem;"></i>
                            <div>
                                <h5 class="mb-1">Patience</h5>
                                <p class="mb-0">Calm and understanding, especially in diverse situations.</p>
                            </div>
                        </div>
                    </div>
                    <div class="value-card">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-tasks text-primary me-3" style="font-size: 1.5rem;"></i>
                            <div>
                                <h5 class="mb-1">Accountability</h5>
                                <p class="mb-0">Responsible for actions and duties.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="value-card">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-users text-primary me-3" style="font-size: 1.5rem;"></i>
                            <div>
                                <h5 class="mb-1">Teamwork</h5>
                                <p class="mb-0">Unity and cooperation to serve the congregation.</p>
                            </div>
                        </div>
                    </div>
                    <div class="value-card">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-hands text-primary me-3" style="font-size: 1.5rem;"></i>
                            <div>
                                <h5 class="mb-1">Humility</h5>
                                <p class="mb-0">Serving with a humble attitude.</p>
                            </div>
                        </div>
                    </div>
                    <div class="value-card">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-heartbeat text-primary me-3" style="font-size: 1.5rem;"></i>
                            <div>
                                <h5 class="mb-1">Compassion</h5>
                                <p class="mb-0">Care and empathy for all.</p>
                            </div>
                        </div>
                    </div>
                    <div class="value-card">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-cross text-primary me-3" style="font-size: 1.5rem;"></i>
                            <div>
                                <h5 class="mb-1">Godliness</h5>
                                <p class="mb-0">Aligning life with the teachings of Jesus Christ.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Qualifications Section -->
    <div class="row mb-5">
        <div class="col-12">
            <h2 class="text-center mb-4" style="color: var(--primary-color);">Characteristics & Qualifications</h2>
            <div class="qualification-grid">
                <div class="qualification-item">
                    <i class="fas fa-comments me-3 text-primary"></i>
                    <span>Good communication skills</span>
                </div>
                <div class="qualification-item">
                    <i class="fas fa-user-check me-3 text-primary"></i>
                    <span>High level of confidence</span>
                </div>
                <div class="qualification-item">
                    <i class="fas fa-clock me-3 text-primary"></i>
                    <span>Time management skills</span>
                </div>
                <div class="qualification-item">
                    <i class="fas fa-tasks me-3 text-primary"></i>
                    <span>Multitasking abilities</span>
                </div>
                <div class="qualification-item">
                    <i class="fas fa-praying-hands me-3 text-primary"></i>
                    <span>Patience</span>
                </div>
                <div class="qualification-item">
                    <i class="fas fa-dove me-3 text-primary"></i>
                    <span>Spiritual relationship with the Lord</span>
                </div>
                <div class="qualification-item">
                    <i class="fas fa-calendar-check me-3 text-primary"></i>
                    <span>Regular attendance at meetings</span>
                </div>
                <div class="qualification-item">
                    <i class="fas fa-smile me-3 text-primary"></i>
                    <span>Friendliness and humility</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Duties Timeline -->
    <div class="row mb-5">
        <div class="col-12">
            <h2 class="text-center mb-5" style="color: var(--primary-color);">Usher Duties Overview</h2>
            <div class="timeline">
                <div class="timeline-item">
                    <div class="timeline-marker"></div>
                    <div class="duty-section">
                        <div class="duty-header">
                            <i class="fas fa-sign-in-alt me-2"></i>
                            Arrival & Departure
                        </div>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-clock me-2 text-primary"></i>Arrive at least 30 minutes early to pray and prepare</li>
                            <li><i class="fas fa-user-plus me-2 text-primary"></i>Conduct orientation for first-time ushers</li>
                            <li><i class="fas fa-calendar-day me-2 text-primary"></i>Arrive earlier for special activities as required</li>
                        </ul>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-marker"></div>
                    <div class="duty-section">
                        <div class="duty-header">
                            <i class="fas fa-handshake me-2"></i>
                            Greeting
                        </div>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-smile me-2 text-primary"></i>Greet arrivals warmly and promptly</li>
                            <li><i class="fas fa-comments me-2 text-primary"></i>Avoid extended conversations that delay movement</li>
                            <li><i class="fas fa-user-friends me-2 text-primary"></i>Refer special requests to appropriate ushers</li>
                        </ul>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-marker"></div>
                    <div class="duty-section">
                        <div class="duty-header">
                            <i class="fas fa-chair me-2"></i>
                            Seating
                        </div>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-arrow-right me-2 text-primary"></i>Guide arrivals to seats near the front courteously</li>
                            <li><i class="fas fa-praying-hands me-2 text-primary"></i>Discourage movement during prayer or worship</li>
                            <li><i class="fas fa-user-clock me-2 text-primary"></i>Seat late arrivals at the back with minimal disturbance</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Emergency Procedures -->
    <div class="row mb-5">
        <div class="col-12">
            <h2 class="text-center mb-4" style="color: var(--primary-color);">Emergency & Safety Procedures</h2>
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="emergency-card h-100">
                        <h4><i class="fas fa-fire-extinguisher me-2"></i>Fire & Evacuation</h4>
                        <ul class="list-unstyled mt-3">
                            <li><i class="fas fa-phone me-2"></i>Call fire brigade immediately when in doubt</li>
                            <li><i class="fas fa-running me-2"></i>Safety comes first—exit if fire is too large</li>
                            <li><i class="fas fa-arrow-down me-2"></i>Start evacuation from last row</li>
                            <li><i class="fas fa-map-marker-alt me-2"></i>Gather at safe distance outside</li>
                            <li><i class="fas fa-search me-2"></i>Check all rooms for remaining people</li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-6 mb-4">
                    <div class="emergency-card h-100">
                        <h4><i class="fas fa-user-shield me-2"></i>Managing Disturbances</h4>
                        <ul class="list-unstyled mt-3">
                            <li><i class="fas fa-eye me-2"></i>Assess the situation with discernment</li>
                            <li><i class="fas fa-hand-paper me-2"></i>Gently but firmly address disruptions</li>
                            <li><i class="fas fa-user-tie me-2"></i>Involve elders or pastor when needed</li>
                            <li><i class="fas fa-heart me-2"></i>Show compassion for distressed individuals</li>
                            <li><i class="fas fa-shield-alt me-2"></i>Call authorities if situation escalates</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Call to Action -->
    <div class="cta-section">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h3 class="mb-2">Ready to Serve as an Usher?</h3>
                <p class="mb-0">Join our ministry and become a spiritual ambassador for Christ Fellowship Chapel</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <button class="btn btn-custom btn-lg" onclick="window.location.href='/volunteers.php'">
                    <i class="fas fa-hands me-2"></i>Get Involved
                </button>
            </div>
        </div>
    </div>

    <!-- Detailed Information Accordion -->
    <div class="accordion accordion-custom" id="usheringAccordion">
        <div class="accordion-item floating-card">
            <h2 class="accordion-header" id="detailedGuidelinesHeading">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                        data-bs-target="#detailedGuidelinesCollapse" aria-expanded="false" 
                        aria-controls="detailedGuidelinesCollapse">
                    <i class="fas fa-book-open me-2"></i>
                    Detailed Guidelines and Principles
                </button>
            </h2>
            <div id="detailedGuidelinesCollapse" class="accordion-collapse collapse" 
                 aria-labelledby="detailedGuidelinesHeading" data-bs-parent="#usheringAccordion">
                <div class="accordion-body">
                    <h4>Who is an usher at Christ Fellowship Chapel?</h4>
                    <p>An usher at Christ Fellowship Chapel is a dedicated individual who serves as a doorkeeper and plays a vital role within the church community. Although the term "usher" may imply simply opening doors, the responsibilities of an usher go far beyond that. Each usher at Christ Fellowship Chapel has the opportunity to make a significant impact on the lives of both new and existing individuals within the church. They are seen as an extension of the pastor's hand and are entrusted with providing the first impressions of the church and the ministry.</p>
                    
                    <p>The ministry of ushering holds great importance in the worship experience as it is one of the most visible ministries in the church. In Christ, we have received God's unconditional love, and as followers of Christ, we are called to extend that same love to others. Ushers play a major role in ensuring that people see and experience this love within the church community.</p>

                    <h5>Probationary period:</h5>
                    <p>Every new member will be subject to a probationary period. Ordinarily, there will be a three month probationary period during which the individual will attend practice sessions but will not be active in ministry with the team during services or other special functions. The purpose of this period is to allow the new member and the rest of the team to gel spiritually in a safe and comfortable environment and to learn their tasks. During this period, new members are also expected to purchase all uniforms that the team uses during ministration.</p>

                    <h5>Ministry time:</h5>
                    <p>All volunteers are expected to be in church on Saturday from 5pm to prepare adequately for the Sunday service. It is expected that every member should arrive earlier for services as guided by other parts of this document.</p>

                    <h4>Responsibilities of an Usher at Christ Fellowship Chapel:</h4>
                    <ol>
                        <li>Faithful attendance</li>
                        <li>Timely arrival</li>
                        <li>Appropriate attire</li>
                        <li>Follow instructions</li>
                        <li>Warm welcome</li>
                        <li>Alertness and attentiveness</li>
                        <li>Assistance to specific groups</li>
                        <li>Support for the elderly and handicapped</li>
                        <li>Duties during the service</li>
                        <li>Security check</li>
                        <li>Facility maintenance</li>
                        <li>Emergency assistance</li>
                    </ol>
                </div>
            </div>
        </div>

        <div class="accordion-item floating-card">
            <h2 class="accordion-header" id="emergencyDetailsHeading">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                        data-bs-target="#emergencyDetailsCollapse" aria-expanded="false" 
                        aria-controls="emergencyDetailsCollapse">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Detailed Emergency Procedures
                </button>
            </h2>
            <div id="emergencyDetailsCollapse" class="accordion-collapse collapse" 
                 aria-labelledby="emergencyDetailsHeading" data-bs-parent="#usheringAccordion">
                <div class="accordion-body">
                    <h4>In the event of an emergency, the safety of the church members is every usher's first responsibility.</h4>
                    <p>Most emergencies are for medical attention or some kind of disturbance with an individual. In these cases, please follow the instructions listed below.</p>
                    
                    <h5>General Emergency Response:</h5>
                    <ol>
                        <li>Clear the area to allow assistance.</li>
                        <li>Always assign one person to call the police, ambulance or fire brigade where need be.</li>
                        <li>Conduct a scene survey and casualty assessment.</li>
                        <li>Station one person to help emergency personnel to the person who needs help. Have another usher make sure the isles are clear for emergency personnel.</li>
                        <li>Keep either an usher or experienced medical person from the church, next to the afflicted person at all times.</li>
                        <li>Encourage others to pray.</li>
                        <li>Assign one person to remain close to the family members and friends.</li>
                        <li>Monitor and make sure the area is not crowded by on-lookers.</li>
                    </ol>

                    <h5>Evacuation Procedures</h5>
                    <p>Ushers should maintain order to the best of their ability in the event of any kind of evacuation. In this case, the ushers should start with the last pew or row in the sanctuary and procedure to usher people out to a nearby exit. If the church has front exits, an usher will escort people from the front rows while others are going out the back rows.</p>
                    
                    <p>Ushers should be assigned to help others in the building at the same time, such as Sunday school church, youth department, checking the washrooms and kitchen, etc. to ensure an evacuation is conducted in a safe and timely manner. It is good for all ushers to check remaining rooms to make sure everyone has evacuated. Once outside, the ushers can determine with others whether anyone is missing.</p>

                    <h5>Fire Safety</h5>
                    <p>In cases of fire, it is always best to call the fire brigade when in doubt. The longer the delay, there is a greater risk to the building and others. On a very small fire that can be put out with an extinguisher, put out the fire, and monitor it. Determine whether it was an extremely small fire to not disrupt the service but in most cases evacuations are the best solution. Again, safety comes first and it is always best to be safe.</p>
                    
                    <p><strong>WARNING:</strong> Do not place your safety or the safety of others at risk! If the fire grows too large or aggressive to control by an extinguisher, time and safety are compromised. Exit the building immediately.</p>

                    <h5>Managing Disturbances</h5>
                    <p>Disturbances by mentally ill, distraught persons, or people who wish to disturb the service require discernment on the part of the chief usher and other ushers present. In some cases, the pastor may deal with issues but for the most part, it will be the responsibility of the chief usher and other ushers to carry out the procedures.</p>
                    
                    <ol>
                        <li>Determine whether a person needs to be taken out of the sanctuary.</li>
                        <li>Determine how you wish to do this. Most of the times, it can be handled through a gentle but yet firm statement to come with the usher. If the disturbance is overbearing and causing further trouble, it may take a couple of ushers, one on each side, to gently encourage the person to another place. If they still refuse to be removed, then contact a senior elder or senior pastor for further assistance.</li>
                        <li>Determine a safe place to deal with this person and firmly state this type of behavior is not wanted or accepted in our services. Please distinguish between the person and the behavior. Note that everyone is welcome in church but not the disruptive behaviors.</li>
                        <li>Determine whether the person will stop or will have to be asked to leave the building. If they refuse, you may have to call the police department for help to remove this person.</li>
                        <li>If an individual is distraught due to grief, please ask them to step out and gently escort them out of the sanctuary. Ask the grieving person, "How can we help?" or "We have someone who will pray with you, please come with me." Please do this in a loving and tactful manner. Have another usher to get senior elders to come pray with them.</li>
                        <li>On disturbances of family battles where one person attends church and another comes into the church to see their children or spouse, please escort the troubling person out of the sanctuary or area where the spouse or children are. In today's times, there are so many legal issues of restraining orders, custody battles, etc. and unfortunately, there are times when others will try to take advantage of picking up a child they could not previously see or have a setting they think they can freely talk to their spouse.</li>
                        <li>Because of these and other issues that may arise in the course of the service, a good usher comes prepared mentally, physically, emotionally and spiritually.</li>
                    </ol>
                </div>
            </div>
        </div>

        <div class="accordion-item floating-card">
            <h2 class="accordion-header" id="conductHeading">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                        data-bs-target="#conductCollapse" aria-expanded="false" 
                        aria-controls="conductCollapse">
                    <i class="fas fa-user-tie me-2"></i>
                    Professional Conduct & Leadership Roles
                </button>
            </h2>
            <div id="conductCollapse" class="accordion-collapse collapse" 
                 aria-labelledby="conductHeading" data-bs-parent="#usheringAccordion">
                <div class="accordion-body">
                    <h4>Responsibilities of the Chief/Head Usher at Christ Fellowship Chapel:</h4>
                    <ol>
                        <li><strong>Special instructions:</strong> Provide clear guidance to the usher team for specific services or events</li>
                        <li><strong>Operations oversight:</strong> Ensure smooth coordination of all ushering activities</li>
                        <li><strong>Usher team management:</strong> Assign roles and responsibilities to team members</li>
                        <li><strong>Pre-service prayer:</strong> Lead the team in prayer and spiritual preparation</li>
                        <li><strong>Teaching and training:</strong> Mentor new ushers and provide ongoing development</li>
                        <li><strong>Communication:</strong> Serve as liaison between ushers and church leadership</li>
                        <li><strong>Rotation and involvement:</strong> Ensure fair distribution of duties among team members</li>
                        <li><strong>Overflow seating management:</strong> Coordinate additional seating arrangements when needed</li>
                        <li><strong>Record keeping:</strong> Maintain attendance and performance records</li>
                    </ol>

                    <h4>Conduct of Ushers at Christ Fellowship Chapel:</h4>
                    <ol>
                        <li><strong>Positive and warm demeanor:</strong> Always maintain a welcoming and friendly attitude</li>
                        <li><strong>Servant's heart:</strong> Approach all duties with humility and willingness to serve</li>
                        <li><strong>Balancing conversations:</strong> Be friendly but mindful of time and other responsibilities</li>
                        <li><strong>Respectful physical contact:</strong> Maintain appropriate boundaries while assisting others</li>
                        <li><strong>Godly conduct:</strong> Reflect Christian values in all interactions and behaviors</li>
                        <li><strong>Professional appearance:</strong> Dress appropriately and maintain neat appearance</li>
                    </ol>

                    <h4>Communication Guidelines:</h4>
                    <ul>
                        <li>Use proper language at all times</li>
                        <li>Speak clearly and confidently</li>
                        <li>Listen actively to congregants' needs</li>
                        <li>Provide accurate information about church services and facilities</li>
                        <li>Know when to refer questions to appropriate leadership</li>
                    </ul>

                    <h4>Team Collaboration:</h4>
                    <ul>
                        <li>Support fellow ushers in their duties</li>
                        <li>Communicate effectively with team members</li>
                        <li>Share responsibilities equitably</li>
                        <li>Participate actively in team meetings and training</li>
                        <li>Maintain unity and positive team spirit</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Ministry Metrics -->
    <div class="row mb-5 mt-5">
        <div class="col-12">
            <h2 class="text-center mb-5" style="color: var(--primary-color);">Ministry Impact</h2>
            <div class="row">
                <div class="col-md-3 mb-4">
                    <div class="ministry-metric">
                        <div class="metric-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h4>First Impressions</h4>
                        <p class="text-muted">Creating welcoming experiences for every visitor</p>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="ministry-metric">
                        <div class="metric-icon">
                            <i class="fas fa-heart"></i>
                        </div>
                        <h4>Spiritual Care</h4>
                        <p class="text-muted">Providing compassionate support during services</p>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="ministry-metric">
                        <div class="metric-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h4>Safety & Security</h4>
                        <p class="text-muted">Ensuring a safe worship environment for all</p>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="ministry-metric">
                        <div class="metric-icon">
                            <i class="fas fa-hands-helping"></i>
                        </div>
                        <h4>Service Excellence</h4>
                        <p class="text-muted">Facilitating smooth and orderly worship services</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact Section -->
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="floating-card p-4 text-center">
                <h3 class="mb-3">Join Our Ushering Ministry</h3>
                <p class="lead mb-4">Feel called to serve as a doorkeeper in the house of the Lord? We'd love to have you join our team of dedicated ushers.</p>
                <div class="row text-center">
                    <div class="col-md-4 mb-3">
                        <div class="d-flex flex-column align-items-center">
                            <div class="icon-badge mb-2">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <small class="text-muted">Email Us</small>
                            <span>info@christekklesians.org</span>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="d-flex flex-column align-items-center">
                            <div class="icon-badge mb-2">
                                <i class="fas fa-phone"></i>
                            </div>
                            <small class="text-muted">Volunteer</small>
                            <span>Click here to volunteer</span><button class="btn btn-custom btn-lg" onclick="window.location.href='/volunteers.php'">
                                <i class="fas fa-hands me-2"></i>Get Involved
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="d-flex flex-column align-items-center">
                            <div class="icon-badge mb-2">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <small class="text-muted">Visit Us</small>
                            <span>Sunday Services</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Add smooth scrolling and animation effects
    document.addEventListener('DOMContentLoaded', function() {
        // Animate cards on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Observe all floating cards and value cards
        document.querySelectorAll('.floating-card, .value-card, .ministry-metric, .qualification-item').forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(card);
        });

        // Add hover effects to timeline items
        document.querySelectorAll('.timeline-item').forEach(item => {
            item.addEventListener('mouseenter', function() {
                this.style.transform = 'scale(1.02)';
                this.style.transition = 'transform 0.3s ease';
            });
            
            item.addEventListener('mouseleave', function() {
                this.style.transform = 'scale(1)';
            });
        });

        // Animate statistics on scroll
        const statsObserver = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const statNumber = entry.target.querySelector('.stat-number');
                    if (statNumber) {
                        const finalNumber = parseInt(statNumber.textContent);
                        animateNumber(statNumber, 0, finalNumber, 2000);
                    }
                }
            });
        }, { threshold: 0.5 });

        document.querySelectorAll('.stat-item').forEach(stat => {
            statsObserver.observe(stat);
        });

        function animateNumber(element, start, end, duration) {
            const range = end - start;
            const increment = end > start ? 1 : -1;
            const stepTime = Math.abs(Math.floor(duration / range));
            let current = start;
            
            const timer = setInterval(() => {
                current += increment;
                element.textContent = current;
                if (current === end) {
                    clearInterval(timer);
                }
            }, stepTime);
        }
    });
</script>

<?php include dirname(__DIR__) . '/../includes/footer.php'; ?>