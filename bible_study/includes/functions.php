<?php
// CEFC Bible Study Management System
// File: includes/functions.php
// Description: Core helper functions for the entire Bible Study system

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

// =================================================================
// AUTH FUNCTIONS
// =================================================================

function requireLogin() {
    if (!isset($_SESSION['bs_user_id'])) {
        header('Location: ../../auth/login.php');
        exit;
    }
}

function requireRole($allowed_roles) {
    if (!isset($_SESSION['bs_user_role']) || !in_array($_SESSION['bs_user_role'], $allowed_roles)) {
        http_response_code(403);
        echo '<div class="flex items-center justify-center min-h-screen bg-gray-50">
                <div class="text-center">
                    <i class="fas fa-lock text-6xl text-red-500 mb-4"></i>
                    <h1 class="text-2xl font-bold text-gray-900 mb-2">Access Denied</h1>
                    <p class="text-gray-600">You do not have permission to access this page.</p>
                </div>
              </div>';
        exit;
    }
}

function getCurrentUser() {
    return [
        'id' => $_SESSION['bs_user_id'] ?? null,
        'name' => $_SESSION['bs_user_name'] ?? null,
        'role' => $_SESSION['bs_user_role'] ?? null,
        'email' => $_SESSION['bs_user_email'] ?? null
    ];
}

function isLoggedIn() {
    return isset($_SESSION['bs_user_id']);
}

// =================================================================
// USER FUNCTIONS
// =================================================================

function getAllUsers($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM bs_users ORDER BY name ASC");
    $stmt->execute();
    return $stmt->fetchAll();
}

function getUserById($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM bs_users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function createUser($pdo, $data) {
    $hashed_password = password_hash($data['password'], PASSWORD_BCRYPT);
    
    // For admin and coordinator roles, auto-verify (no OTP required)
    // For leader and member roles, require OTP verification
    $verified = 1; // Default to verified
    $otp = null;
    
    if (!in_array($data['role'], ['admin', 'coordinator'])) {
        $verified = 0; // Require verification for leaders and members
        $otp = generateOTP(); // Generate 6-digit OTP
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO bs_users (name, email, phone, password, role, age_group, group_id, status, verified, otp)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $result = $stmt->execute([
        $data['name'],
        $data['email'],
        $data['phone'],
        $hashed_password,
        $data['role'],
        $data['age_group'],
        $data['group_id'] ?? null,
        $data['status'] ?? 'active',
        $verified,
        $otp
    ]);
    
    // Send OTP email only for non-admin, non-coordinator users
    if ($result && $otp && !in_array($data['role'], ['admin', 'coordinator'])) {
        $emailSent = sendOTPEmail($data['email'], $data['name'], $otp);
        
        // Log email sending result for debugging
        if ($emailSent) {
            error_log("OTP email sent successfully to {$data['email']} for user {$data['name']} (Role: {$data['role']})");
        } else {
            error_log("Failed to send OTP email to {$data['email']} for user {$data['name']} (Role: {$data['role']})");
        }
    }
    
    return $result;
}

function updateUser($pdo, $id, $data) {
    $fields = [];
    $values = [];
    
    foreach ($data as $key => $value) {
        if ($key !== 'password') {
            $fields[] = "$key = ?";
            $values[] = $value;
        } else {
            $fields[] = "password = ?";
            $values[] = password_hash($value, PASSWORD_BCRYPT);
        }
    }
    
    $values[] = $id;
    $sql = "UPDATE bs_users SET " . implode(', ', $fields) . " WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute($values);
}

function deleteUser($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM bs_users WHERE id = ?");
    return $stmt->execute([$id]);
}

function getUsersByRole($pdo, $role) {
    $stmt = $pdo->prepare("SELECT * FROM bs_users WHERE role = ? ORDER BY name ASC");
    $stmt->execute([$role]);
    return $stmt->fetchAll();
}

function getUsersByGroup($pdo, $group_id) {
    $stmt = $pdo->prepare("SELECT * FROM bs_users WHERE group_id = ? ORDER BY name ASC");
    $stmt->execute([$group_id]);
    return $stmt->fetchAll();
}

// =================================================================
// GROUP FUNCTIONS
// =================================================================

function getAllGroups($pdo) {
    $stmt = $pdo->prepare("
        SELECT g.*, u.name as leader_name 
        FROM bs_groups g 
        LEFT JOIN bs_users u ON g.leader_id = u.id 
        ORDER BY g.name ASC
    ");
    $stmt->execute();
    return $stmt->fetchAll();
}

function getGroupById($pdo, $id) {
    $stmt = $pdo->prepare("
        SELECT g.*, u.name as leader_name 
        FROM bs_groups g 
        LEFT JOIN bs_users u ON g.leader_id = u.id 
        WHERE g.id = ?
    ");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function createGroup($pdo, $data) {
    $stmt = $pdo->prepare("
        INSERT INTO bs_groups (name, semester_id, leader_id, status)
        VALUES (?, ?, ?, ?)
    ");
    return $stmt->execute([
        $data['name'],
        $data['semester_id'],
        $data['leader_id'],
        $data['status'] ?? 'active'
    ]);
}

function updateGroup($pdo, $id, $data) {
    $fields = [];
    $values = [];
    
    foreach ($data as $key => $value) {
        $fields[] = "$key = ?";
        $values[] = $value;
    }
    
    $values[] = $id;
    $sql = "UPDATE bs_groups SET " . implode(', ', $fields) . " WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute($values);
}

function deleteGroup($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM bs_groups WHERE id = ?");
    return $stmt->execute([$id]);
}

function getGroupMembers($pdo, $group_id) {
    $stmt = $pdo->prepare("SELECT * FROM bs_users WHERE group_id = ? ORDER BY name ASC");
    $stmt->execute([$group_id]);
    return $stmt->fetchAll();
}

function getGroupMemberCount($pdo, $group_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM bs_users WHERE group_id = ?");
    $stmt->execute([$group_id]);
    return $stmt->fetchColumn();
}

function getUnassignedMembers($pdo, $semester_id) {
    $stmt = $pdo->prepare("
        SELECT u.* 
        FROM bs_users u 
        WHERE u.role = 'member' 
        AND u.status = 'active'
        AND (u.group_id IS NULL OR u.group_id = 0)
        AND u.id NOT IN (
            SELECT g.leader_id FROM bs_groups g WHERE g.semester_id = ? AND g.leader_id IS NOT NULL
        )
        ORDER BY u.name ASC
    ");
    $stmt->execute([$semester_id]);
    return $stmt->fetchAll();
}

function assignMemberToGroup($pdo, $user_id, $group_id) {
    $stmt = $pdo->prepare("UPDATE bs_users SET group_id = ? WHERE id = ?");
    return $stmt->execute([$group_id, $user_id]);
}

function assignGroupLeader($pdo, $group_id, $leader_id) {
    $pdo->beginTransaction();
    try {
        // Update group leader
        $stmt = $pdo->prepare("UPDATE bs_groups SET leader_id = ? WHERE id = ?");
        $stmt->execute([$leader_id, $group_id]);
        
        // Assign leader to group (keep role as 'member')
        $stmt = $pdo->prepare("UPDATE bs_users SET group_id = ? WHERE id = ?");
        $stmt->execute([$group_id, $leader_id]);
        
        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollback();
        return false;
    }
}

function removeMemberFromGroup($pdo, $user_id) {
    $stmt = $pdo->prepare("UPDATE bs_users SET group_id = NULL WHERE id = ?");
    return $stmt->execute([$user_id]);
}

function getMemberTransferHistory($pdo, $user_id) {
    $stmt = $pdo->prepare("
        SELECT 
            g.name as group_name,
            'transferred' as action,
            'Unknown' as performed_by,
            CURRENT_DATE() as transfer_date
        FROM bs_users u
        LEFT JOIN bs_groups g ON g.id = u.group_id
        WHERE u.id = ? AND u.group_id IS NOT NULL
        ORDER BY u.id DESC
        LIMIT 10
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

// =================================================================
// SEMESTER FUNCTIONS
// =================================================================

function getAllSemesters($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM bs_semesters ORDER BY created_at DESC");
    $stmt->execute();
    return $stmt->fetchAll();
}

function getActiveSemester($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM bs_semesters WHERE status = 'active' LIMIT 1");
    $stmt->execute();
    return $stmt->fetch();
}

function createSemester($pdo, $data) {
    $stmt = $pdo->prepare("
        INSERT INTO bs_semesters (name, start_date, end_date, status, created_by)
        VALUES (?, ?, ?, ?, ?)
    ");
    return $stmt->execute([
        $data['name'],
        $data['start_date'],
        $data['end_date'],
        $data['status'] ?? 'upcoming',
        $data['created_by'] ?? null
    ]);
}

function updateSemesterStatus($pdo, $id, $status) {
    $stmt = $pdo->prepare("UPDATE bs_semesters SET status = ? WHERE id = ?");
    return $stmt->execute([$status, $id]);
}

function updateSemester($pdo, $id, $data) {
    $stmt = $pdo->prepare("
        UPDATE bs_semesters 
        SET name = ?, start_date = ?, end_date = ?
        WHERE id = ?
    ");
    return $stmt->execute([
        $data['name'],
        $data['start_date'],
        $data['end_date'],
        $id
    ]);
}

// =================================================================
// SESSION FUNCTIONS
// =================================================================

function bsGetAllSessions($pdo, $semester_id) {
    $stmt = $pdo->prepare("
        SELECT * FROM bs_sessions 
        WHERE semester_id = ? 
        ORDER BY session_date ASC, session_number ASC
    ");
    $stmt->execute([$semester_id]);
    return $stmt->fetchAll();
}

function bsGetSessionById($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM bs_sessions WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function bsCreateSession($pdo, $data) {
    $stmt = $pdo->prepare("
        INSERT INTO bs_sessions (semester_id, session_date, session_number, topic, book_reference, status)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    return $stmt->execute([
        $data['semester_id'],
        $data['session_date'],
        $data['session_number'],
        $data['topic'],
        $data['book_reference'],
        $data['status'] ?? 'draft'
    ]);
}

// =================================================================
// SCORING FUNCTIONS
// =================================================================

function getScoreCategories($pdo, $session_id) {
    $stmt = $pdo->prepare("
        SELECT * FROM bs_score_categories 
        WHERE session_id = ? 
        ORDER BY name ASC
    ");
    $stmt->execute([$session_id]);
    return $stmt->fetchAll();
}

function saveScore($pdo, $data) {
    // Check if score exists and update, otherwise insert
    $stmt = $pdo->prepare("
        SELECT id FROM bs_scores 
        WHERE session_id = ? AND group_id = ? AND category_id = ?
    ");
    $stmt->execute([$data['session_id'], $data['group_id'], $data['category_id']]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        $stmt = $pdo->prepare("
            UPDATE bs_scores 
            SET points = ?, entered_by = ? 
            WHERE session_id = ? AND group_id = ? AND category_id = ?
        ");
        return $stmt->execute([
            $data['points'],
            $data['entered_by'],
            $data['session_id'],
            $data['group_id'],
            $data['category_id']
        ]);
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO bs_scores (session_id, group_id, category_id, points, entered_by)
            VALUES (?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $data['session_id'],
            $data['group_id'],
            $data['category_id'],
            $data['points'],
            $data['entered_by']
        ]);
    }
}

function getGroupSessionScore($pdo, $group_id, $session_id) {
    $stmt = $pdo->prepare("
        SELECT SUM(points) as total_score 
        FROM bs_scores 
        WHERE session_id = ? AND group_id = ?
    ");
    $stmt->execute([$session_id, $group_id]);
    $result = $stmt->fetch();
    return $result['total_score'] ?? 0;
}

function getGroupTotalPoints($pdo, $group_id, $semester_id) {
    $stmt = $pdo->prepare("
        SELECT SUM(s.points) as total_points 
        FROM bs_scores s 
        INNER JOIN bs_sessions sess ON s.session_id = sess.id 
        WHERE s.group_id = ? AND sess.semester_id = ?
    ");
    $stmt->execute([$group_id, $semester_id]);
    $result = $stmt->fetch();
    return $result['total_points'] ?? 0;
}

function updateGroupTotalPoints($pdo, $group_id, $semester_id) {
    $total_points = getGroupTotalPoints($pdo, $group_id, $semester_id);
    $stmt = $pdo->prepare("UPDATE bs_groups SET total_points = ? WHERE id = ?");
    return $stmt->execute([$total_points, $group_id]);
}

// =================================================================
// RANKING FUNCTIONS
// =================================================================

function getGroupRankings($pdo, $semester_id) {
    $stmt = $pdo->prepare("
        SELECT g.*, u.name as leader_name,
               (SELECT SUM(s.points) 
                FROM bs_scores s 
                INNER JOIN bs_sessions sess ON s.session_id = sess.id 
                WHERE s.group_id = g.id AND sess.semester_id = ?) as total_points
        FROM bs_groups g 
        LEFT JOIN bs_users u ON g.leader_id = u.id 
        WHERE g.semester_id = ? 
        ORDER BY total_points DESC, g.name ASC
    ");
    $stmt->execute([$semester_id, $semester_id]);
    $groups = $stmt->fetchAll();
    
    // Add rank numbers
    $rank = 1;
    $prev_points = null;
    foreach ($groups as &$group) {
        if ($prev_points !== null && $group['total_points'] < $prev_points) {
            $rank++;
        }
        $group['rank'] = $rank;
        $prev_points = $group['total_points'];
    }
    
    return $groups;
}

function getGroupRank($pdo, $group_id, $semester_id) {
    $rankings = getGroupRankings($pdo, $semester_id);
    foreach ($rankings as $group) {
        if ($group['id'] == $group_id) {
            return $group['rank'];
        }
    }
    return null;
}

// =================================================================
// ATTENDANCE FUNCTIONS
// =================================================================

function recordAttendance($pdo, $data) {
    // Check if attendance exists and update, otherwise insert
    $stmt = $pdo->prepare("
        SELECT id FROM bs_attendance 
        WHERE session_id = ? AND user_id = ?
    ");
    $stmt->execute([$data['session_id'], $data['user_id']]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        $stmt = $pdo->prepare("
            UPDATE bs_attendance 
            SET group_id = ?, status = ? 
            WHERE session_id = ? AND user_id = ?
        ");
        return $stmt->execute([
            $data['group_id'],
            $data['status'],
            $data['session_id'],
            $data['user_id']
        ]);
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO bs_attendance (session_id, user_id, group_id, status)
            VALUES (?, ?, ?, ?)
        ");
        return $stmt->execute([
            $data['session_id'],
            $data['user_id'],
            $data['group_id'],
            $data['status']
        ]);
    }
}

function getSessionAttendance($pdo, $session_id) {
    $stmt = $pdo->prepare("
        SELECT a.*, u.name as user_name, g.name as group_name 
        FROM bs_attendance a 
        INNER JOIN bs_users u ON a.user_id = u.id 
        INNER JOIN bs_groups g ON a.group_id = g.id 
        WHERE a.session_id = ? 
        ORDER BY u.name ASC
    ");
    $stmt->execute([$session_id]);
    return $stmt->fetchAll();
}

function getMemberAttendanceStats($pdo, $user_id, $semester_id) {
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(CASE WHEN a.status = 'present' THEN 1 END) as present,
            COUNT(CASE WHEN a.status = 'late' THEN 1 END) as late,
            COUNT(CASE WHEN a.status = 'absent' THEN 1 END) as absent,
            COUNT(CASE WHEN a.status = 'excused' THEN 1 END) as excused,
            COUNT(*) as total_sessions
        FROM bs_attendance a 
        INNER JOIN bs_sessions s ON a.session_id = s.id 
        WHERE a.user_id = ? AND s.semester_id = ?
    ");
    $stmt->execute([$user_id, $semester_id]);
    $stats = $stmt->fetch();
    
    // Calculate attendance percentage (present + late count as attended)
    $attended = $stats['present'] + $stats['late'];
    $percentage = $stats['total_sessions'] > 0 ? round(($attended / $stats['total_sessions']) * 100, 1) : 0;
    $stats['attendance_percentage'] = $percentage;
    
    return $stats;
}

// =================================================================
// NOTIFICATION FUNCTIONS
// =================================================================

function logNotification($pdo, $data) {
    $stmt = $pdo->prepare("
        INSERT INTO bs_notifications_log (session_id, user_id, email_address, subject, status)
        VALUES (?, ?, ?, ?, ?)
    ");
    return $stmt->execute([
        $data['session_id'] ?? null,
        $data['user_id'] ?? null,
        $data['email_address'],
        $data['subject'] ?? '',
        $data['status'] ?? 'failed'
    ]);
}

// =================================================================
// UTILITY FUNCTIONS
// =================================================================

function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function formatDate($date) {
    return date('l, M j Y', strtotime($date));
}

function getRoleBadgeColor($role) {
    $colors = [
        'admin' => 'bg-red-100 text-red-800',
        'coordinator' => 'bg-blue-100 text-blue-800',
        'leader' => 'bg-green-100 text-green-800',
        'member' => 'bg-amber-100 text-amber-800'
    ];
    return $colors[$role] ?? 'bg-gray-100 text-gray-800';
}

function getAttendanceStatusColor($status) {
    $colors = [
        'present' => 'bg-green-100 text-green-800',
        'late' => 'bg-amber-100 text-amber-800',
        'absent' => 'bg-red-100 text-red-800',
        'excused' => 'bg-gray-100 text-gray-800'
    ];
    return $colors[$status] ?? 'bg-gray-100 text-gray-800';
}

function generateOTP() {
    return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}

function sendOTPEmail($email, $name, $otp) {
    $subject = "CEFC Bible Study - Your Verification Code";
    $message = "
    <html>
    <head>
        <title>CEFC Bible Study - Email Verification</title>
    </head>
    <body style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
        <div style='background: linear-gradient(135deg, #6B21A8 0%, #9333EA 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0;'>
            <h1 style='margin: 0; font-size: 28px;'>🙏 CEFC Bible Study</h1>
            <p style='margin: 10px 0 0 0; opacity: 0.9;'>Email Verification</p>
        </div>
        <div style='background: white; padding: 40px; border-radius: 0 0 10px 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);'>
            <h2 style='color: #6B21A8; margin-top: 0;'>Hello, " . htmlspecialchars($name) . "!</h2>
            <p style='color: #666; line-height: 1.6;'>Welcome to CEFC Bible Study Management System! Your account has been created by an administrator.</p>
            
            <div style='background: #F3F4F6; padding: 20px; border-radius: 8px; text-align: center; margin: 30px 0;'>
                <p style='margin: 0 0 10px 0; color: #666; font-weight: bold;'>Your Verification Code:</p>
                <div style='background: #6B21A8; color: white; font-size: 32px; font-weight: bold; padding: 15px 30px; border-radius: 8px; letter-spacing: 5px; display: inline-block;'>" . $otp . "</div>
            </div>
            
            <p style='color: #666; line-height: 1.6;'>Use this code to verify your email address and activate your account. This code will expire in 24 hours.</p>
            
            <div style='text-align: center; margin-top: 30px;'>
                <a href='http://localhost/church-website-recent/bible_study/auth/login.php' style='background: #6B21A8; color: white; padding: 12px 30px; text-decoration: none; border-radius: 6px; display: inline-block;'>Go to Login</a>
            </div>
            
            <hr style='border: none; border-top: 1px solid #E5E7EB; margin: 30px 0;'>
            <p style='color: #999; font-size: 12px; text-align: center;'>If you didn't request this account, please contact your administrator.</p>
        </div>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
    $headers .= "From: CEFC Bible Study <noreply@cefc.org>" . "\r\n";
    
    // Try to send email
    $emailSent = mail($email, $subject, $message, $headers);
    
    // If email fails, log the OTP for admin to see
    if (!$emailSent) {
        error_log("EMAIL FAILED - OTP for {$email}: {$otp}");
        
        // Store OTP in session for admin to see (fallback for development)
        if (!isset($_SESSION['admin_otp_log'])) {
            $_SESSION['admin_otp_log'] = [];
        }
        $_SESSION['admin_otp_log'][] = [
            'email' => $email,
            'name' => $name,
            'otp' => $otp,
            'time' => date('Y-m-d H:i:s')
        ];
    }
    
    return $emailSent;
}

function redirectTo($path) {
    header('Location: ' . $path);
    exit();
}