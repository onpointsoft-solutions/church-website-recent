<?php
/**
 * SEO-Optimized Header for Christ Ekklesia Fellowship Chapel
 * 
 * Enhanced with comprehensive SEO meta tags, structured data,
 * and performance optimizations.
 */

// Include SEO configuration
require_once __DIR__ . '/seo-config.php';

// Get current page SEO data
$currentPath = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
if (empty($currentPath)) $currentPath = 'index';

$seoData = getCurrentPageSEO();
$canonicalUrl = getCanonicalUrl($currentPath);
$robotsContent = getRobotsContent($currentPath);
$organizationData = getOrganizationStructuredData();
$breadcrumbData = getBreadcrumbStructuredData($currentPath);
?>
<!DOCTYPE html>
<html lang="<?= $seoData['language'] ?>" prefix="og: https://ogp.me/ns#">
<head>
    <!-- Basic Meta Tags -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
    <!-- SEO Meta Tags -->
    <title><?= htmlspecialchars($seoData['title']) ?></title>
    <meta name="description" content="<?= htmlspecialchars($seoData['description']) ?>">
    <meta name="keywords" content="<?= htmlspecialchars($seoData['keywords']) ?>">
    <meta name="author" content="<?= htmlspecialchars($seoData['siteName']) ?>">
    <meta name="robots" content="<?= $robotsContent ?>">
    <meta name="googlebot" content="<?= $robotsContent ?>">
    <meta name="bingbot" content="<?= $robotsContent ?>">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="<?= htmlspecialchars($canonicalUrl) ?>">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:type" content="<?= htmlspecialchars($seoData['type']) ?>">
    <meta property="og:title" content="<?= htmlspecialchars($seoData['title']) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($seoData['description']) ?>">
    <meta property="og:url" content="<?= htmlspecialchars($canonicalUrl) ?>">
    <meta property="og:site_name" content="<?= htmlspecialchars($seoData['siteName']) ?>">
    <meta property="og:locale" content="<?= htmlspecialchars($seoData['locale']) ?>">
    <?php if (isset($seoData['image'])): ?>
    <meta property="og:image" content="<?= htmlspecialchars($seoData['image']) ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="<?= htmlspecialchars($seoData['title']) ?>">
    <?php endif; ?>
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($seoData['title']) ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($seoData['description']) ?>">
    <?php if (isset($seoData['image'])): ?>
    <meta name="twitter:image" content="<?= htmlspecialchars($seoData['image']) ?>">
    <meta name="twitter:image:alt" content="<?= htmlspecialchars($seoData['title']) ?>">
    <?php endif; ?>
    
    <!-- Favicon and App Icons -->
    <link rel="icon" type="image/png" href="/assets/images/logo.png" sizes="32x32">
    <link rel="icon" type="image/png" href="/assets/images/logo.png" sizes="16x16">
    <link rel="apple-touch-icon" href="/assets/images/logo.png">
    <link rel="manifest" href="/assets/manifest.json">
    <meta name="theme-color" content="#60379e">
    <meta name="msapplication-TileColor" content="#60379e">
    
    <!-- DNS Prefetch and Preconnect -->
    <link rel="dns-prefetch" href="//fonts.googleapis.com">
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link rel="dns-prefetch" href="//cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!-- Stylesheets with Performance Optimization -->
    <link href="/assets/vendor/bootstrap/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="/style.css?v=<?= filemtime(__DIR__ . '/../style.css') ?>" rel="stylesheet">
    
    <!-- Structured Data -->
    <script type="application/ld+json">
    <?= json_encode($organizationData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?>
    </script>
    
    <script type="application/ld+json">
    <?= json_encode($breadcrumbData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?>
    </script>
    
    <!-- Performance and Security -->
    <meta http-equiv="Referrer-Policy" content="strict-origin-when-cross-origin">
    
    <!-- Preload Critical Resources -->
    <link rel="preload" href="/style.css?v=<?= filemtime(__DIR__ . '/../style.css') ?>" as="style">
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" as="style">
</head>
<body>
    <!-- Google Tag Manager (noscript) - Add your GTM ID here -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-XXXXXXX"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
    
    <!-- Skip to main content for accessibility -->
    <a class="visually-hidden-focusable" href="#main-content">Skip to main content</a>
<nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#home">
                <img src="../assets/images/logo.png" alt="Christ Ekklesians Fellowship Chapel Logo" class="navbar-logo me-2">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link active" href="/index#home">Home</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#about" id="aboutDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">About</a>
                        <ul class="dropdown-menu" aria-labelledby="aboutDropdown">
                            <li><a class="dropdown-item" href="/about">About Us</a></li>
                            <li><a class="dropdown-item" href="/constitution">Constitution</a></li>
                            <li><a class="dropdown-item" href="/church-calendar">Calendar</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index#services">Services</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#ministries" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Ministries
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="/ministries/childrens-ministry">Children's Ministry</a></li>
                           <!-- <li><a class="dropdown-item" href="/ministries/youth-ministry">Youth Ministry</a></li> -->
                            <li><a class="dropdown-item" href="/ministries/worship-team">Worship Team</a></li>
                            <!-- <li><a class="dropdown-item" href="/ministries/prayer-ministry">Prayer & Intercession Ministry</a></li> -->
                            <li><a class="dropdown-item" href="/ministries/sound-media-ministry">Sound & Media Ministry</a></li>
                            <li><a class="dropdown-item" href="/ministries/ushering-ministry">Ushering Ministry</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/index#contact">Contact</a>
                    </li>
                    <li class="nav-item d-none d-lg-block ms-2"> <a class="btn btn-outline-light px-4" href="/giving.php">
                            <i class="fas fa-hands-helping me-1"></i> Give now
                        </a></li>
                    <li class="nav-item d-lg-none">
                        <a class="nav-link" href="giving.php">
                            <i class="fas fa-hands-helping me-1"></i> Give now
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
<div style="height: 80px;"></div> <!-- Spacer for fixed navbar -->
