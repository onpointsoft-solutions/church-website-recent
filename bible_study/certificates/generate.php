<?php
// CEFC Bible Study Management System
// File: certificates/generate.php
// Description: Generate and display certificates

require_once '../config/config.php';
require_once '../config/db.php';
require_once '../includes/functions.php';
requireLogin();

// Get certificate ID
$certId = $_GET['cert_id'] ?? '';
if (empty($certId)) {
    header('Location: ../pages/member/certificates.php');
    exit;
}

// Query certificate with full details
$stmt = $pdo->prepare("
    SELECT 
        bc.id,
        bc.certificate_type,
        bc.issued_date,
        bc.semester_id,
        bs.name as semester_name,
        bs.start_date as semester_start,
        bs.end_date as semester_end,
        bu.id as user_id,
        bu.name as member_name,
        bu.email as member_email,
        bu.age_group,
        bg.name as group_name
    FROM bs_certificates bc
    JOIN bs_semesters bs ON bc.semester_id = bs.id
    JOIN bs_users bu ON bc.user_id = bu.id
    LEFT JOIN bs_groups bg ON bu.group_id = bg.id
    WHERE bc.id = ?
");
$stmt->execute([$certId]);
$cert = $stmt->fetch();

if (!$cert) {
    die('
        <div style="display: flex; justify-content: center; align-items: center; min-height: 100vh; font-family: Arial, sans-serif; background: #f3f4f6;">
            <div style="text-align: center; padding: 40px; background: white; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                <h2 style="color: #6B21A8; margin-bottom: 16px;">Certificate Not Found</h2>
                <p style="color: #6B7280; margin-bottom: 20px;">The requested certificate could not be found.</p>
                <a href="../pages/member/certificates.php" style="display: inline-block; padding: 10px 20px; background: #6B21A8; color: white; text-decoration: none; border-radius: 6px;">Back to Certificates</a>
            </div>
        </div>
    ');
}

// Security check
if ($_SESSION['bs_user_role'] === 'member') {
    if ($cert['user_id'] !== $_SESSION['bs_user_id']) {
        die('
            <div style="display: flex; justify-content: center; align-items: center; min-height: 100vh; font-family: Arial, sans-serif; background: #f3f4f6;">
                <div style="text-align: center; padding: 40px; background: white; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                    <h2 style="color: #DC2626; margin-bottom: 16px;">Unauthorized Access</h2>
                    <p style="color: #6B7280; margin-bottom: 20px;">You do not have permission to view this certificate.</p>
                    <a href="../pages/member/certificates.php" style="display: inline-block; padding: 10px 20px; background: #6B21A8; color: white; text-decoration: none; border-radius: 6px;">Back to Certificates</a>
                </div>
            </div>
        ');
    }
}

// Set display variables
$memberName = strtoupper($cert['member_name']);
$semesterName = $cert['semester_name'];
$groupName = $cert['group_name'] ?? 'N/A';
$issuedDate = date('F j, Y', strtotime($cert['issued_date']));
$semesterStart = date('F Y', strtotime($cert['semester_start']));
$semesterEnd = date('F Y', strtotime($cert['semester_end']));
$certNumber = 'CEFC-BS-' . str_pad($cert['id'], 4, '0', STR_PAD_LEFT);

// Certificate content (participation only)
$certTitle = "Certificate of Participation";
$certText = "This certifies that " . $memberName . " has successfully participated in the CEFC Bible Study Program during " . $semesterName . " (" . $semesterStart . " – " . $semesterEnd . "), demonstrating commitment to the study of God's Word and faithfulness to the body of Christ.";
$borderColor = "#6B21A8";
$accentColor = "#D97706";
$icon = "🎓";
$scripture = "Study to show yourself approved unto God, a workman that needeth not to be ashamed, rightly dividing the word of truth. — 2 Timothy 2:15";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $certTitle ?> - <?= $memberName ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #f3f4f6; font-family: Georgia, serif; }

        /* Action bar — hidden on print */
        .action-bar {
          position: fixed; top: 0; left: 0; right: 0;
          height: 60px; background: white;
          box-shadow: 0 2px 8px rgba(0,0,0,0.1);
          display: flex; align-items: center;
          justify-content: space-between;
          padding: 0 24px; z-index: 100;
        }
        .btn-back { color: <?= $borderColor ?>; text-decoration: none; font-size: 14px; }
        .btn-print {
          background: <?= $borderColor ?>; color: white;
          border: none; padding: 8px 20px;
          border-radius: 6px; cursor: pointer; font-size: 14px;
        }
        .action-bar-title { font-size: 15px; color: #374151; font-weight: bold; }

        /* Certificate wrapper */
        .page-wrapper {
          display: flex; justify-content: center; align-items: center;
          min-height: 100vh; padding: 80px 20px 20px;
        }

        /* Certificate — A4 Landscape */
        @page { size: A4 landscape; margin: 0; }

        .certificate {
          width: 297mm;
          height: 210mm;
          background: white;
          position: relative;
          overflow: hidden;
          display: flex;
          flex-direction: column;
          align-items: center;
          justify-content: center;
          box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }

        /* Outer border */
        .cert-outer-border {
          position: absolute;
          inset: 8px;
          border: 8px solid <?= $borderColor ?>;
        }

        /* Inner border */
        .cert-inner-border {
          position: absolute;
          inset: 22px;
          border: 2px solid <?= $accentColor ?>;
        }

        /* Corner ornaments */
        .corner {
          position: absolute;
          font-size: 28px;
          color: <?= $accentColor ?>;
          line-height: 1;
        }
        .corner-tl { top: 28px; left: 28px; }
        .corner-tr { top: 28px; right: 28px; }
        .corner-bl { bottom: 28px; left: 28px; }
        .corner-br { bottom: 28px; right: 28px; }

        /* Content area */
        .cert-content {
          position: relative;
          z-index: 2;
          text-align: center;
          padding: 0 60px;
          width: 100%;
        }

        .church-name {
          font-size: 13px;
          letter-spacing: 4px;
          color: <?= $borderColor ?>;
          font-weight: bold;
          text-transform: uppercase;
          margin-bottom: 4px;
        }

        .decorative-line {
          width: 200px;
          height: 2px;
          background: linear-gradient(to right, transparent, <?= $accentColor ?>, transparent);
          margin: 6px auto;
        }

        .program-name {
          font-size: 10px;
          letter-spacing: 6px;
          color: #9CA3AF;
          text-transform: uppercase;
          margin-bottom: 8px;
        }

        .cert-icon {
          font-size: 42px;
          margin-bottom: 6px;
        }

        .cert-of-text {
          font-size: 16px;
          font-style: italic;
          color: #6B7280;
          margin-bottom: 2px;
        }

        .cert-type-word {
          font-size: 40px;
          font-weight: bold;
          color: <?= $borderColor ?>;
          letter-spacing: 3px;
          margin-bottom: 8px;
        }

        .diamond-divider {
          color: <?= $accentColor ?>;
          font-size: 14px;
          margin-bottom: 8px;
          letter-spacing: 12px;
        }

        .certify-text {
          font-size: 12px;
          font-style: italic;
          color: #9CA3AF;
          margin-bottom: 4px;
        }

        .member-name {
          font-size: 28px;
          font-weight: bold;
          color: <?= $borderColor ?>;
          letter-spacing: 2px;
          margin-bottom: 4px;
        }

        .name-underline {
          width: 280px;
          height: 2px;
          background: <?= $accentColor ?>;
          margin: 0 auto 8px;
        }

        .cert-body-text {
          font-size: 11px;
          color: #6B7280;
          max-width: 65%;
          margin: 0 auto 6px;
          line-height: 1.7;
        }

        .group-semester-info {
          font-size: 10px;
          color: #9CA3AF;
          margin-bottom: 6px;
        }

        .scripture-text {
          font-size: 10px;
          font-style: italic;
          color: <?= $borderColor ?>;
          max-width: 60%;
          margin: 0 auto 10px;
          opacity: 0.8;
        }

        /* Footer */
        .cert-footer {
          position: absolute;
          bottom: 36px;
          left: 0; right: 0;
          display: flex;
          justify-content: space-around;
          align-items: flex-end;
          padding: 0 80px;
        }

        .footer-col {
          text-align: center;
          width: 180px;
        }

        .signature-line {
          width: 140px;
          height: 1px;
          background: #374151;
          margin: 0 auto 4px;
        }

        .footer-label {
          font-size: 9px;
          color: #6B7280;
          text-transform: uppercase;
          letter-spacing: 1px;
        }

        .footer-sub {
          font-size: 8px;
          color: #9CA3AF;
        }

        /* Official seal */
        .official-seal {
          width: 80px; height: 80px;
          border-radius: 50%;
          border: 2px solid <?= $borderColor ?>;
          display: flex; flex-direction: column;
          align-items: center; justify-content: center;
          margin: 0 auto 4px;
        }

        .seal-cefc {
          font-size: 18px;
          font-weight: bold;
          color: <?= $borderColor ?>;
        }

        .seal-text {
          font-size: 7px;
          letter-spacing: 1px;
          color: <?= $borderColor ?>;
          text-transform: uppercase;
        }

        .cert-number {
          font-size: 8px;
          color: #9CA3AF;
          margin-top: 2px;
        }

        /* Watermark */
        .watermark {
          position: absolute;
          top: 50%; left: 50%;
          transform: translate(-50%, -50%) rotate(-45deg);
          font-size: 180px;
          font-weight: bold;
          color: <?= $borderColor ?>;
          opacity: 0.03;
          pointer-events: none;
          user-select: none;
          z-index: 1;
          white-space: nowrap;
        }

        /* Print rules */
        @media print {
          .action-bar { display: none !important; }
          .page-wrapper { padding: 0; min-height: unset; background: white; }
          body { background: white; }
          .certificate { box-shadow: none; }
        }
    </style>
</head>
<body>
    <!-- Action Bar -->
    <div class="action-bar">
        <a href="../pages/member/certificates.php" class="btn-back">← Back to Certificates</a>
        <span class="action-bar-title"><?= $certTitle ?></span>
        <button onclick="window.print()" class="btn-print">🖨️ Print Certificate</button>
    </div>

    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <!-- Certificate -->
        <div class="certificate">
            <!-- Watermark -->
            <div class="watermark">CEFC</div>
            
            <!-- Borders -->
            <div class="cert-outer-border"></div>
            <div class="cert-inner-border"></div>
            
            <!-- Corner Ornaments -->
            <div class="corner corner-tl">✦</div>
            <div class="corner corner-tr">✦</div>
            <div class="corner corner-bl">✦</div>
            <div class="corner corner-br">✦</div>
            
            <!-- Content -->
            <div class="cert-content">
                <div class="church-name">CHRIST EKKLESIA FELLOWSHIP CHAPEL</div>
                <div class="decorative-line"></div>
                <div class="program-name">BIBLE STUDY PROGRAM</div>
                <div class="cert-icon"><?= $icon ?></div>
                
                <div class="cert-of-text">Certificate of</div>
                <div class="cert-type-word">Participation</div>
                <div class="diamond-divider">◆ ◆ ◆</div>
                
                <div class="certify-text">This is to certify that</div>
                <div class="member-name"><?= $memberName ?></div>
                <div class="name-underline"></div>
                
                <div class="cert-body-text"><?= $certText ?></div>
                
                <div class="group-semester-info">
                    Study Group: <?= $groupName ?> &nbsp;|&nbsp; Semester: <?= $semesterName ?>
                </div>
                
                <div class="scripture-text">"<?= $scripture ?>"</div>
            </div>
            
            <!-- Footer -->
            <div class="cert-footer">
                <!-- Left Column -->
                <div class="footer-col">
                    <div class="signature-line"></div>
                    <div class="footer-label">Pastor / Church Leadership</div>
                    <div class="footer-sub">Christ Ekklesia Fellowship Chapel</div>
                </div>
                
                <!-- Center Column -->
                <div class="footer-col">
                    <div class="official-seal">
                        <div class="seal-cefc">CEFC</div>
                        <div class="seal-text">Official Seal</div>
                    </div>
                    <div class="cert-number"><?= $certNumber ?></div>
                </div>
                
                <!-- Right Column -->
                <div class="footer-col">
                    <div class="signature-line"></div>
                    <div class="footer-label">Bible Study Coordinator</div>
                    <div class="footer-sub">Issued: <?= $issuedDate ?></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.onload = function() {
            setTimeout(() => window.print(), 800);
        }

        window.onafterprint = function() {
            // Show toast notification
            const toast = document.createElement('div');
            toast.style.cssText = `
              position: fixed; bottom: 24px; right: 24px;
              background: #065F46; color: white;
              padding: 12px 24px; border-radius: 8px;
              font-size: 14px; z-index: 9999;
              box-shadow: 0 4px 12px rgba(0,0,0,0.2);
              font-family: Arial, sans-serif;
            `;
            toast.textContent = '✅ Certificate printed/saved successfully!';
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 4000);
        }
    </script>
</body>
</html>