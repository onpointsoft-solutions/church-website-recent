<?php
// CEFC Bible Study Management System
// File: api/bs_api.php
// Description: Single AJAX endpoint for all dynamic actions

require_once '../config/config.php';
require_once '../config/db.php';
require_once '../includes/functions.php';
require_once '../vendor/autoload.php';

// Set JSON response header
header('Content-Type: application/json');

// Check if session is active and user is logged in
if (!isset($_SESSION['bs_user_id']) || !isset($_SESSION['bs_user_role'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized'
    ]);
    exit;
}

// Get action from POST
$action = $_POST['action'] ?? '';

// Helper functions
function jsonResponse($success, $message, $data = []) {
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

function requireApiRole($allowed_roles) {
    if (!in_array($_SESSION['bs_user_role'], $allowed_roles)) {
        jsonResponse(false, 'Access denied');
    }
}

// Route to appropriate handler
try {
    switch ($action) {
        // SECTION 1: USER ACTIONS
        case 'get_user':
            requireApiRole(['admin']);
            $userId = (int)($_POST['id'] ?? 0);
            $stmt = $pdo->prepare("SELECT id, name, email, age_group, role, status FROM bs_users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            if ($user) {
                jsonResponse(true, 'ok', $user);
            } else {
                jsonResponse(false, 'User not found');
            }
            break;

        case 'toggle_user_status':
            requireApiRole(['admin']);
            $userId = (int)($_POST['id'] ?? 0);
            $stmt = $pdo->prepare("SELECT status FROM bs_users WHERE id = ?");
            $stmt->execute([$userId]);
            $currentUser = $stmt->fetch();
            
            if ($currentUser) {
                $newStatus = $currentUser['status'] === 'active' ? 'inactive' : 'active';
                $updateStmt = $pdo->prepare("UPDATE bs_users SET status = ? WHERE id = ?");
                $updateStmt->execute([$newStatus, $userId]);
                jsonResponse(true, 'Status updated', ['new_status' => $newStatus]);
            } else {
                jsonResponse(false, 'User not found');
            }
            break;

        case 'get_users_by_group':
            requireApiRole(['admin', 'coordinator', 'leader']);
            $groupId = (int)($_POST['group_id'] ?? 0);
            $stmt = $pdo->prepare("SELECT id, name, email, age_group, status FROM bs_users WHERE group_id = ? ORDER BY name");
            $stmt->execute([$groupId]);
            $users = $stmt->fetchAll();
            jsonResponse(true, 'ok', $users);
            break;

        case 'assign_user_to_group':
            requireApiRole(['admin']);
            $userId = (int)($_POST['user_id'] ?? 0);
            $groupId = (int)($_POST['group_id'] ?? 0);
            $stmt = $pdo->prepare("UPDATE bs_users SET group_id = ? WHERE id = ?");
            $stmt->execute([$groupId, $userId]);
            jsonResponse(true, 'Member assigned to group');
            break;

        // SECTION 2: GROUP ACTIONS
        case 'get_group':
            requireApiRole(['admin', 'coordinator', 'leader']);
            $groupId = (int)($_POST['id'] ?? 0);
            $stmt = $pdo->prepare("
                SELECT g.*, u.name as leader_name 
                FROM bs_groups g
                LEFT JOIN bs_users u ON u.id = g.leader_id
                WHERE g.id = ?
            ");
            $stmt->execute([$groupId]);
            $group = $stmt->fetch();
            if ($group) {
                jsonResponse(true, 'ok', $group);
            } else {
                jsonResponse(false, 'Group not found');
            }
            break;

        case 'get_group_members':
            requireApiRole(['admin', 'coordinator', 'leader']);
            $groupId = (int)($_POST['group_id'] ?? 0);
            $stmt = $pdo->prepare("
                SELECT id, name, email, phone, age_group, status 
                FROM bs_users 
                WHERE group_id = ? 
                ORDER BY name
            ");
            $stmt->execute([$groupId]);
            $members = $stmt->fetchAll();
            jsonResponse(true, 'ok', $members);
            break;

        case 'get_group_rankings':
            requireApiRole(['admin', 'coordinator', 'leader', 'member']);
            $semesterId = (int)($_POST['semester_id'] ?? 0);
            $stmt = $pdo->prepare("
                SELECT g.id, g.name, g.total_points,
                       u.name as leader_name
                FROM bs_groups g
                LEFT JOIN bs_users u ON u.id = g.leader_id
                WHERE g.semester_id = ?
                ORDER BY g.total_points DESC
            ");
            $stmt->execute([$semesterId]);
            $groups = $stmt->fetchAll();
            
            // Add rank numbers
            foreach ($groups as $index => &$group) {
                $group['rank'] = $index + 1;
            }
            
            jsonResponse(true, 'ok', $groups);
            break;

        case 'split_group':
            requireApiRole(['admin']);
            $groupId = (int)($_POST['group_id'] ?? 0);
            
            // Get current members
            $membersStmt = $pdo->prepare("SELECT * FROM bs_users WHERE group_id = ?");
            $membersStmt->execute([$groupId]);
            $members = $membersStmt->fetchAll();
            
            if (count($members) <= MAX_GROUP_SIZE) {
                jsonResponse(false, 'Group does not need splitting');
                break;
            }
            
            // Get group details
            $groupStmt = $pdo->prepare("SELECT * FROM bs_groups WHERE id = ?");
            $groupStmt->execute([$groupId]);
            $group = $groupStmt->fetch();
            
            if (!$group) {
                jsonResponse(false, 'Group not found');
                break;
            }
            
            $pdo->beginTransaction();
            try {
                // Create new group
                $newGroupName = $group['name'] . ' B';
                $insertStmt = $pdo->prepare("
                    INSERT INTO bs_groups (name, semester_id, leader_id, created_at) 
                    VALUES (?, ?, ?, NOW())
                ");
                $insertStmt->execute([$newGroupName, $group['semester_id'], $group['leader_id']]);
                $newGroupId = $pdo->lastInsertId();
                
                // Split members
                $half = ceil(count($members) / 2);
                $secondHalf = array_slice($members, $half);
                
                foreach ($secondHalf as $member) {
                    $updateStmt = $pdo->prepare("UPDATE bs_users SET group_id = ? WHERE id = ?");
                    $updateStmt->execute([$newGroupId, $member['id']]);
                }
                
                // Share total points equally
                $pointsPerGroup = floor($group['total_points'] / 2);
                $updatePointsStmt = $pdo->prepare("UPDATE bs_groups SET total_points = ? WHERE id = ?");
                $updatePointsStmt->execute([$pointsPerGroup, $groupId]);
                $updatePointsStmt->execute([$pointsPerGroup, $newGroupId]);
                
                // Log action
                logNotification("Group {$group['name']} split into two groups", 'system');
                
                $pdo->commit();
                jsonResponse(true, 'Group split successfully', ['new_group_id' => $newGroupId]);
            } catch (Exception $e) {
                $pdo->rollback();
                jsonResponse(false, 'Failed to split group: ' . $e->getMessage());
            }
            break;

        // SECTION 3: SESSION ACTIONS
        case 'get_session':
            requireApiRole(['admin', 'coordinator']);
            $sessionId = (int)($_POST['id'] ?? 0);
            $stmt = $pdo->prepare("
                SELECT s.*, sem.name as semester_name 
                FROM bs_sessions s
                JOIN bs_semesters sem ON sem.id = s.semester_id
                WHERE s.id = ?
            ");
            $stmt->execute([$sessionId]);
            $session = $stmt->fetch();
            if ($session) {
                jsonResponse(true, 'ok', $session);
            } else {
                jsonResponse(false, 'Session not found');
            }
            break;

        case 'get_session_categories':
            requireApiRole(['admin', 'coordinator']);
            $sessionId = (int)($_POST['session_id'] ?? 0);
            $stmt = $pdo->prepare("SELECT * FROM bs_score_categories WHERE session_id = ? ORDER BY name");
            $stmt->execute([$sessionId]);
            $categories = $stmt->fetchAll();
            jsonResponse(true, 'ok', $categories);
            break;

        case 'get_session_scores':
            requireApiRole(['admin', 'coordinator', 'leader']);
            $sessionId = (int)($_POST['session_id'] ?? 0);
            $stmt = $pdo->prepare("
                SELECT sc.*, g.name as group_name, cat.name as category_name
                FROM bs_scores sc
                JOIN bs_groups g ON g.id = sc.group_id
                JOIN bs_score_categories cat ON cat.id = sc.category_id
                WHERE sc.session_id = ?
                ORDER BY g.name, cat.name
            ");
            $stmt->execute([$sessionId]);
            $scores = $stmt->fetchAll();
            jsonResponse(true, 'ok', $scores);
            break;

        case 'get_sessions_for_semester':
            requireApiRole(['admin', 'coordinator', 'leader', 'member']);
            $semesterId = (int)($_POST['semester_id'] ?? 0);
            $stmt = $pdo->prepare("SELECT * FROM bs_sessions WHERE semester_id = ? ORDER BY session_number ASC");
            $stmt->execute([$semesterId]);
            $sessions = $stmt->fetchAll();
            jsonResponse(true, 'ok', $sessions);
            break;

        // SECTION 4: SCORING ACTIONS
        case 'get_existing_scores':
            requireApiRole(['admin', 'coordinator']);
            $sessionId = (int)($_POST['session_id'] ?? 0);
            $stmt = $pdo->prepare("
                SELECT session_id, group_id, category_id, points 
                FROM bs_scores 
                WHERE session_id = ?
            ");
            $stmt->execute([$sessionId]);
            $scores = $stmt->fetchAll();
            
            // Organize as keyed array
            $keyedScores = [];
            foreach ($scores as $score) {
                $keyedScores[$score['group_id']][$score['category_id']] = $score['points'];
            }
            
            jsonResponse(true, 'ok', $keyedScores);
            break;

        case 'recalculate_rankings':
            requireApiRole(['admin', 'coordinator']);
            $semesterId = (int)($_POST['semester_id'] ?? 0);
            
            // Get all groups in semester
            $groupsStmt = $pdo->prepare("SELECT id FROM bs_groups WHERE semester_id = ?");
            $groupsStmt->execute([$semesterId]);
            $groups = $groupsStmt->fetchAll();
            
            foreach ($groups as $group) {
                // Sum all points for this group
                $scoreStmt = $pdo->prepare("
                    SELECT COALESCE(SUM(points), 0) as total 
                    FROM bs_scores sc
                    JOIN bs_sessions s ON s.id = sc.session_id
                    WHERE sc.group_id = ? AND s.semester_id = ?
                ");
                $scoreStmt->execute([$group['id'], $semesterId]);
                $totalPoints = $scoreStmt->fetch()['total'];
                
                // Update group total points
                $updateStmt = $pdo->prepare("UPDATE bs_groups SET total_points = ? WHERE id = ?");
                $updateStmt->execute([$totalPoints, $group['id']]);
            }
            
            jsonResponse(true, 'Rankings recalculated');
            break;

        // SECTION 5: ATTENDANCE ACTIONS
        case 'get_session_attendance':
            requireApiRole(['admin', 'coordinator', 'leader']);
            $sessionId = (int)($_POST['session_id'] ?? 0);
            $stmt = $pdo->prepare("
                SELECT a.*, u.name as user_name, g.name as group_name
                FROM bs_attendance a
                JOIN bs_users u ON u.id = a.user_id
                JOIN bs_groups g ON g.id = u.group_id
                WHERE a.session_id = ?
                ORDER BY g.name, u.name
            ");
            $stmt->execute([$sessionId]);
            $attendance = $stmt->fetchAll();
            
            // Organize as keyed array
            $keyedAttendance = [];
            foreach ($attendance as $att) {
                $keyedAttendance[$att['user_id']] = $att['status'];
            }
            
            jsonResponse(true, 'ok', $keyedAttendance);
            break;

        case 'get_member_attendance_summary':
            requireApiRole(['admin', 'coordinator', 'leader', 'member']);
            $userId = (int)($_POST['user_id'] ?? 0);
            $semesterId = (int)($_POST['semester_id'] ?? 0);
            
            $stmt = $pdo->prepare("
                SELECT 
                    COUNT(CASE WHEN a.status = 'present' THEN 1 END) as present,
                    COUNT(CASE WHEN a.status = 'late' THEN 1 END) as late,
                    COUNT(CASE WHEN a.status = 'absent' THEN 1 END) as absent,
                    COUNT(CASE WHEN a.status = 'excused' THEN 1 END) as excused,
                    COUNT(*) as total_sessions
                FROM bs_attendance a
                JOIN bs_sessions s ON s.id = a.session_id
                WHERE a.user_id = ? AND s.semester_id = ?
            ");
            $stmt->execute([$userId, $semesterId]);
            $stats = $stmt->fetch();
            
            $percentage = $stats['total_sessions'] > 0 ? 
                round((($stats['present'] + $stats['late']) / $stats['total_sessions']) * 100, 1) : 0;
            
            jsonResponse(true, 'ok', [
                'present' => (int)$stats['present'],
                'late' => (int)$stats['late'],
                'absent' => (int)$stats['absent'],
                'excused' => (int)$stats['excused'],
                'percentage' => $percentage
            ]);
            break;

        // SECTION 6: NOTIFICATION ACTIONS
        case 'send_session_results':
            requireApiRole(['admin', 'coordinator']);
            $sessionId = (int)($_POST['session_id'] ?? 0);
            
            // Get session details
            $sessionStmt = $pdo->prepare("
                SELECT s.*, sem.name as semester_name 
                FROM bs_sessions s
                JOIN bs_semesters sem ON sem.id = s.semester_id
                WHERE s.id = ?
            ");
            $sessionStmt->execute([$sessionId]);
            $session = $sessionStmt->fetch();
            
            if (!$session) {
                jsonResponse(false, 'Session not found');
                break;
            }
            
            // Get all groups with their scores for this session
            $scoresStmt = $pdo->prepare("
                SELECT g.id, g.name, g.total_points, sc.points as session_score
                FROM bs_groups g
                LEFT JOIN bs_scores sc ON sc.group_id = g.id AND sc.session_id = ?
                WHERE g.semester_id = ?
                ORDER BY g.total_points DESC
            ");
            $scoresStmt->execute([$sessionId, $session['semester_id']]);
            $groupScores = $scoresStmt->fetchAll();
            
            // Get group rankings
            $rankingsStmt = $pdo->prepare("
                SELECT g.id, g.name, g.total_points
                FROM bs_groups g
                WHERE g.semester_id = ?
                ORDER BY g.total_points DESC
            ");
            $rankingsStmt->execute([$session['semester_id']]);
            $rankings = $rankingsStmt->fetchAll();
            
            // Get all active members with emails
            $membersStmt = $pdo->prepare("
                SELECT u.id, u.name, u.email, g.name as group_name
                FROM bs_users u
                JOIN bs_groups g ON g.id = u.group_id
                WHERE u.role = 'member' AND u.status = 'active' AND u.verified = 1 AND u.email IS NOT NULL
                AND g.semester_id = ?
                ORDER BY g.name, u.name
            ");
            $membersStmt->execute([$session['semester_id']]);
            $members = $membersStmt->fetchAll();
            
            // Scriptures array
            $scriptures = [
                "Study to show yourself approved - 2 Timothy 2:15",
                "Your word is a lamp to my feet - Psalm 119:105",
                "Let the word of Christ dwell in you richly - Colossians 3:16",
                "All Scripture is God-breathed - 2 Timothy 3:16",
                "Blessed is the one who reads - Revelation 1:3"
            ];
            
            $sentCount = 0;
            $failedCount = 0;
            
            try {
                // Initialize PHPMailer
                $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                
                // SMTP settings from config
                $mail->isSMTP();
                $mail->Host = MAIL_HOST;
                $mail->SMTPAuth = true;
                $mail->Username = MAIL_USERNAME;
                $mail->Password = MAIL_PASSWORD;
                $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = MAIL_PORT;
                
                foreach ($members as $member) {
                    try {
                        // Find member's group score and rank
                        $memberGroupScore = 0;
                        $memberGroupRank = 0;
                        
                        foreach ($groupScores as $index => $gs) {
                            if ($gs['id'] == $member['group_name']) {
                                $memberGroupScore = $gs['session_score'];
                                break;
                            }
                        }
                        
                        foreach ($rankings as $index => $rank) {
                            if ($rank['id'] == $member['group_name']) {
                                $memberGroupRank = $index + 1;
                                break;
                            }
                        }
                        
                        // Build email HTML
                        $subject = "CEFC Bible Study - Session {$session['session_number']} Results 📖";
                        
                        $html = "
                        <html>
                        <head>
                            <style>
                                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                                .header { background: linear-gradient(135deg, #6B21A8, #9333EA); color: white; padding: 20px; text-align: center; }
                                .content { padding: 20px; background: #f9f9f9; }
                                .section { margin: 20px 0; padding: 15px; background: white; border-radius: 8px; }
                                .group-highlight { background: #f3e8ff; padding: 10px; border-radius: 5px; margin: 10px 0; }
                                .table { width: 100%; border-collapse: collapse; margin: 10px 0; }
                                .table th, .table td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
                                .table th { background: #6B21A8; color: white; }
                                .footer { background: #333; color: white; padding: 20px; text-align: center; font-size: 12px; }
                            </style>
                        </head>
                        <body>
                            <div class='header'>
                                <h1>📖 CEFC Bible Study</h1>
                                <h2>Session {$session['session_number']} Results</h2>
                            </div>
                            
                            <div class='content'>
                                <p>Dear {$member['name']},</p>
                                <p>Here are the results for Session {$session['session_number']} - {$session['session_date']}</p>
                                
                                <div class='section'>
                                    <h3>🏆 Your Group Performance</h3>
                                    <div class='group-highlight'>
                                        <strong>Group:</strong> {$member['group_name']}<br>
                                        <strong>Score this session:</strong> {$memberGroupScore}<br>
                                        <strong>Total semester points:</strong> " . (array_search($member['group_name'], array_column($rankings, 'id')) !== false ? $rankings[array_search($member['group_name'], array_column($rankings, 'id'))]['total_points'] : 0) . "
                                    </div>
                                </div>
                                
                                <div class='section'>
                                    <h3>📊 Current Rankings</h3>
                                    <table class='table'>
                                        <tr><th>Rank</th><th>Group</th><th>Points</th></tr>";
                        
                        foreach ($rankings as $index => $rank) {
                            $highlight = $rank['id'] == $member['group_name'] ? 'background: #f3e8ff;' : '';
                            $rankNum = $index + 1;
                            $html .= "<tr style='" . $highlight . "'><td>#{$rankNum}</td><td>" . $rank['name'] . "</td><td>" . $rank['total_points'] . "</td></tr>";
                        }
                        
                        $html .= "
                                    </table>
                                </div>
                                
                                <div class='section'>
                                    <h3>🙏 Encouragement Scripture</h3>
                                    <p><em>\"{$scriptures[array_rand($scriptures)]}\"</em></p>
                                </div>
                            </div>
                            
                            <div class='footer'>
                                <p>See you next Saturday! 2:00 PM - 4:00 PM</p>
                                <p>CEFC Bible Study Team</p>
                                <p><small>To unsubscribe from these emails, please contact the church office.</small></p>
                            </div>
                        </body>
                        </html>";
                        
                        $mail->setFrom(MAIL_USERNAME, MAIL_FROM_NAME);
                        $mail->addAddress($member['email'], $member['name']);
                        $mail->Subject = $subject;
                        $mail->isHTML(true);
                        $mail->Body = $html;
                        
                        $mail->send();
                        $sentCount++;
                        
                        // Log successful send
                        $logStmt = $pdo->prepare("
                            INSERT INTO bs_notifications_log (session_id, user_id, email_address, subject, status, sent_at) 
                            VALUES (?, ?, ?, ?, 'sent', NOW())
                        ");
                        $logStmt->execute([$sessionId, $member['id'], $member['email'], $subject]);
                        
                    } catch (Exception $e) {
                        $failedCount++;
                        
                        // Log failed send
                        $logStmt = $pdo->prepare("
                            INSERT INTO bs_notifications_log (session_id, user_id, email_address, subject, status, error_message, sent_at) 
                            VALUES (?, ?, ?, ?, 'failed', ?, NOW())
                        ");
                        $logStmt->execute([$sessionId, $member['id'], $member['email'], $subject, $e->getMessage()]);
                    }
                }
                
                jsonResponse(true, 'Notifications sent', [
                    'sent' => $sentCount,
                    'failed' => $failedCount,
                    'total' => count($members)
                ]);
                
            } catch (Exception $e) {
                jsonResponse(false, 'Failed to send notifications: ' . $e->getMessage());
            }
            break;

        case 'get_notification_history':
            requireApiRole(['admin', 'coordinator']);
            $sessionId = (int)($_POST['session_id'] ?? 0);
            
            $whereClause = $sessionId ? "WHERE nl.session_id = ?" : "";
            $params = $sessionId ? [$sessionId] : [];
            
            $stmt = $pdo->prepare("
                SELECT nl.*, u.name as user_name
                FROM bs_notifications_log nl
                LEFT JOIN bs_users u ON u.id = nl.user_id
                {$whereClause}
                ORDER BY nl.sent_at DESC
                LIMIT 50
            ");
            $stmt->execute($params);
            $logs = $stmt->fetchAll();
            
            jsonResponse(true, 'ok', $logs);
            break;

        case 'send_test_email':
            requireApiRole(['admin', 'coordinator']);
            
            try {
                $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                $mail->isSMTP();
                $mail->Host = MAIL_HOST;
                $mail->SMTPAuth = true;
                $mail->Username = MAIL_USERNAME;
                $mail->Password = MAIL_PASSWORD;
                $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = MAIL_PORT;
                
                $mail->setFrom(MAIL_USERNAME, MAIL_FROM_NAME);
                $mail->addAddress($_SESSION['bs_user_email'], $_SESSION['bs_user_name']);
                $mail->Subject = 'Test Email - CEFC Bible Study System';
                $mail->isHTML(true);
                $mail->Body = '
                    <h2>Test Email Successful</h2>
                    <p>This is a test email from the CEFC Bible Study Management System.</p>
                    <p>If you received this, your email configuration is working correctly.</p>
                    <p>Best regards,<br>CEFC Bible Study Team</p>
                ';
                
                $mail->send();
                jsonResponse(true, 'Test email sent successfully');
                
            } catch (Exception $e) {
                jsonResponse(false, 'Test email failed: ' . $e->getMessage());
            }
            break;

        // SECTION 7: CERTIFICATE ACTIONS
        case 'issue_certificate':
            requireApiRole(['admin']);
            $semesterId = (int)($_POST['semester_id'] ?? 0);
            $userId = (int)($_POST['user_id'] ?? 0);
            $certificateType = sanitize($_POST['certificate_type'] ?? '');
            
            // Check if already issued
            $checkStmt = $pdo->prepare("
                SELECT id FROM bs_certificates 
                WHERE semester_id = ? AND user_id = ? AND certificate_type = ?
            ");
            $checkStmt->execute([$semesterId, $userId, $certificateType]);
            
            if ($checkStmt->fetch()) {
                jsonResponse(false, 'Certificate already issued');
                break;
            }
            
            // Issue certificate
            $insertStmt = $pdo->prepare("
                INSERT INTO bs_certificates (semester_id, user_id, certificate_type, issued_date) 
                VALUES (?, ?, ?, CURDATE())
            ");
            $insertStmt->execute([$semesterId, $userId, $certificateType]);
            
            jsonResponse(true, 'Certificate issued', ['cert_id' => $pdo->lastInsertId()]);
            break;

        case 'get_member_certificates':
            requireApiRole(['admin', 'member']);
            $userId = (int)($_POST['user_id'] ?? 0);
            
            // If member role, force user_id to current user
            if ($_SESSION['bs_user_role'] === 'member') {
                $userId = $_SESSION['bs_user_id'];
            }
            
            $stmt = $pdo->prepare("
                SELECT c.*, s.name as semester_name 
                FROM bs_certificates c
                JOIN bs_semesters s ON s.id = c.semester_id
                WHERE c.user_id = ?
                ORDER BY c.issued_date DESC
            ");
            $stmt->execute([$userId]);
            $certificates = $stmt->fetchAll();
            
            jsonResponse(true, 'ok', $certificates);
            break;

        case 'bulk_issue_participation_certificates':
            requireApiRole(['admin']);
            $semesterId = (int)($_POST['semester_id'] ?? 0);
            
            // Find all members who attended at least 1 session
            $membersStmt = $pdo->prepare("
                SELECT DISTINCT u.id
                FROM bs_users u
                JOIN bs_attendance a ON a.user_id = u.id
                JOIN bs_sessions s ON s.id = a.session_id
                WHERE u.role = 'member' AND s.semester_id = ?
                AND a.status IN ('present', 'late')
            ");
            $membersStmt->execute([$semesterId]);
            $memberIds = $membersStmt->fetchAll(PDO::FETCH_COLUMN);
            
            $issuedCount = 0;
            foreach ($memberIds as $memberId) {
                // Check if certificate already issued
                $checkStmt = $pdo->prepare("
                    SELECT id FROM bs_certificates 
                    WHERE semester_id = ? AND user_id = ? AND certificate_type = 'Participation'
                ");
                $checkStmt->execute([$semesterId, $memberId]);
                
                if (!$checkStmt->fetch()) {
                    $insertStmt = $pdo->prepare("
                        INSERT INTO bs_certificates (semester_id, user_id, certificate_type, issued_date) 
                        VALUES (?, ?, 'Participation', CURDATE())
                    ");
                    $insertStmt->execute([$semesterId, $memberId]);
                    $issuedCount++;
                }
            }
            
            jsonResponse(true, 'Certificates issued', ['count' => $issuedCount]);
            break;

        // SECTION 8: DASHBOARD STATS ACTIONS
        case 'get_dashboard_stats':
            requireApiRole(['admin', 'coordinator']);
            
            $activeSemester = getActiveSemester($pdo);
            
            $stats = [];
            
            // Total members
            $memberStmt = $pdo->prepare("SELECT COUNT(*) as count FROM bs_users WHERE role = 'member'");
            $memberStmt->execute();
            $stats['total_members'] = $memberStmt->fetch()['count'];
            
            // Total groups
            $groupStmt = $pdo->prepare("SELECT COUNT(*) as count FROM bs_groups");
            $groupStmt->execute();
            $stats['total_groups'] = $groupStmt->fetch()['count'];
            
            // Active semester
            $stats['active_semester'] = $activeSemester;
            
            if ($activeSemester) {
                // Total sessions in active semester
                $sessionStmt = $pdo->prepare("SELECT COUNT(*) as count FROM bs_sessions WHERE semester_id = ?");
                $sessionStmt->execute([$activeSemester['id']]);
                $stats['total_sessions'] = $sessionStmt->fetch()['count'];
                
                // Top group
                $topGroupStmt = $pdo->prepare("
                    SELECT g.*, u.name as leader_name 
                    FROM bs_groups g
                    LEFT JOIN bs_users u ON u.id = g.leader_id
                    WHERE g.semester_id = ?
                    ORDER BY g.total_points DESC
                    LIMIT 1
                ");
                $topGroupStmt->execute([$activeSemester['id']]);
                $stats['top_group'] = $topGroupStmt->fetch();
                
                // Groups pending scoring (for latest session)
                $latestSessionStmt = $pdo->prepare("
                    SELECT id FROM bs_sessions 
                    WHERE semester_id = ? 
                    ORDER BY session_date DESC, session_number DESC
                    LIMIT 1
                ");
                $latestSessionStmt->execute([$activeSemester['id']]);
                $latestSession = $latestSessionStmt->fetch();
                
                if ($latestSession) {
                    $pendingStmt = $pdo->prepare("
                        SELECT COUNT(*) as count 
                        FROM bs_groups g
                        WHERE g.semester_id = ?
                        AND g.id NOT IN (
                            SELECT DISTINCT sc.group_id 
                            FROM bs_scores sc 
                            WHERE sc.session_id = ?
                        )
                    ");
                    $pendingStmt->execute([$activeSemester['id'], $latestSession['id']]);
                    $stats['pending_scoring'] = $pendingStmt->fetch()['count'];
                } else {
                    $stats['pending_scoring'] = 0;
                }
            } else {
                $stats['total_sessions'] = 0;
                $stats['top_group'] = null;
                $stats['pending_scoring'] = 0;
            }
            
            jsonResponse(true, 'ok', $stats);
            break;

        default:
            jsonResponse(false, 'Invalid action');
            break;
    }
} catch (Exception $e) {
    // Log error to file
    error_log("API Error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
    
    jsonResponse(false, 'Operation failed');
}
?>
