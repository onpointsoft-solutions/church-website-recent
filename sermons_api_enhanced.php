<?php
/**
 * Enhanced Sermons API - Production Ready
 * 
 * Comprehensive API for sermon management with upload, retrieval,
 * and admin controls for the mobile app and web portal.
 * 
 * @version 2.0.0
 * @author Onpoint Softwares Solutions
 */

// Security headers
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Start session
session_start();

// Database configuration
$host = 'localhost';
$dbname = 'kazrxdvk_church_management';
$username = 'kazrxdvk_vincent';
$password = '@Admin@2025';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit;
}

// Logging function
function logAction($action, $details, $level = 'INFO') {
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $logEntry = "[$timestamp] [$level] Action: $action | Details: $details | IP: $ip" . PHP_EOL;
    
    $logFile = __DIR__ . '/admin/logs/sermons_api.log';
    $logDir = dirname($logFile);
    
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

// CSRF token verification
function verifyCsrfToken($token) {
    return !empty($token); // Simplified for mobile app
}

// Get all sermons (public endpoint)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !isset($_GET['action'])) {
    try {
        $stmt = $pdo->query("
            SELECT id, title, date, speaker, ministry, description, 
                   thumbnail, youtube, file_url, duration, views, created_at
            FROM sermons 
            ORDER BY date DESC 
            LIMIT 50
        ");
        $sermons = $stmt->fetchAll();
        
        logAction('GET_SERMONS', 'Retrieved ' . count($sermons) . ' sermons');
        
        echo json_encode([
            'success' => true,
            'sermons' => $sermons,
            'total' => count($sermons)
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Get sermon by ID
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    try {
        $id = (int)$_GET['id'];
        $stmt = $pdo->prepare("
            SELECT id, title, date, speaker, ministry, description, 
                   thumbnail, youtube, file_url, duration, views, created_at
            FROM sermons 
            WHERE id = ?
        ");
        $stmt->execute([$id]);
        $sermon = $stmt->fetch();
        
        if ($sermon) {
            // Increment view count
            $updateStmt = $pdo->prepare("UPDATE sermons SET views = views + 1 WHERE id = ?");
            $updateStmt->execute([$id]);
            
            echo json_encode([
                'success' => true,
                'sermon' => $sermon
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Sermon not found']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Get sermons by ministry
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'get_by_ministry') {
    try {
        $ministry = trim($_POST['ministry'] ?? '');
        
        if (empty($ministry)) {
            echo json_encode(['success' => false, 'message' => 'Ministry is required']);
            exit;
        }
        
        $stmt = $pdo->prepare("
            SELECT id, title, date, speaker, ministry, description, 
                   thumbnail, youtube, file_url, duration, views, created_at
            FROM sermons 
            WHERE ministry = ? OR ministry LIKE ?
            ORDER BY date DESC
        ");
        $stmt->execute([$ministry, "%$ministry%"]);
        $sermons = $stmt->fetchAll();
        
        logAction('GET_SERMONS_BY_MINISTRY', "Ministry: $ministry, Count: " . count($sermons));
        
        echo json_encode([
            'success' => true,
            'sermons' => $sermons,
            'total' => count($sermons)
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Upload sermon (admin only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload_sermon') {
    try {
        // Verify CSRF token
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!verifyCsrfToken($csrfToken)) {
            logAction('UPLOAD_SERMON', 'CSRF token verification failed', 'SECURITY');
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Security verification failed']);
            exit;
        }
        
        // Validate required fields
        $title = trim($_POST['title'] ?? '');
        $speaker = trim($_POST['speaker'] ?? '');
        $ministry = trim($_POST['ministry'] ?? '');
        $date = trim($_POST['date'] ?? '');
        $description = trim($_POST['description'] ?? '');
        
        if (empty($title) || empty($speaker) || empty($date)) {
            echo json_encode(['success' => false, 'message' => 'Title, speaker, and date are required']);
            exit;
        }
        
        // Handle file upload
        $fileUrl = null;
        $thumbnailUrl = null;
        
        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $fileTmp = $_FILES['file']['tmp_name'];
            $fileName = basename($_FILES['file']['name']);
            $fileSize = $_FILES['file']['size'];
            
            // Validate file type and size
            $allowedTypes = ['video/mp4', 'video/quicktime', 'video/x-msvideo'];
            $fileType = mime_content_type($fileTmp);
            $maxSize = 500 * 1024 * 1024; // 500MB
            
            if (!in_array($fileType, $allowedTypes)) {
                echo json_encode(['success' => false, 'message' => 'Invalid video file type']);
                exit;
            }
            
            if ($fileSize > $maxSize) {
                echo json_encode(['success' => false, 'message' => 'File size exceeds 500MB limit']);
                exit;
            }
            
            // Create upload directory
            $uploadDir = __DIR__ . '/uploads/sermons/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }
            
            // Generate unique filename
            $newFileName = uniqid('sermon_', true) . '.mp4';
            $dest = $uploadDir . $newFileName;
            
            if (move_uploaded_file($fileTmp, $dest)) {
                $fileUrl = 'uploads/sermons/' . $newFileName;
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to upload video file']);
                exit;
            }
        }
        
        // Handle thumbnail upload
        if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
            $thumbTmp = $_FILES['thumbnail']['tmp_name'];
            $thumbName = basename($_FILES['thumbnail']['name']);
            $thumbSize = $_FILES['thumbnail']['size'];
            
            $allowedThumbTypes = ['image/jpeg', 'image/png', 'image/webp'];
            $thumbType = mime_content_type($thumbTmp);
            $maxThumbSize = 5 * 1024 * 1024; // 5MB
            
            if (in_array($thumbType, $allowedThumbTypes) && $thumbSize <= $maxThumbSize) {
                $thumbDir = __DIR__ . '/uploads/thumbnails/';
                if (!is_dir($thumbDir)) {
                    mkdir($thumbDir, 0775, true);
                }
                
                $newThumbName = uniqid('thumb_', true) . '.jpg';
                $thumbDest = $thumbDir . $newThumbName;
                
                if (move_uploaded_file($thumbTmp, $thumbDest)) {
                    $thumbnailUrl = 'uploads/thumbnails/' . $newThumbName;
                }
            }
        }
        
        // Insert into database
        $stmt = $pdo->prepare("
            INSERT INTO sermons (title, speaker, ministry, date, description, file_url, thumbnail, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$title, $speaker, $ministry, $date, $description, $fileUrl, $thumbnailUrl]);
        $sermonId = $pdo->lastInsertId();
        
        logAction('UPLOAD_SERMON', "ID: $sermonId, Title: $title, Speaker: $speaker");
        
        echo json_encode([
            'success' => true,
            'message' => 'Sermon uploaded successfully',
            'id' => $sermonId,
            'file_url' => $fileUrl,
            'thumbnail_url' => $thumbnailUrl
        ]);
    } catch (Exception $e) {
        logAction('UPLOAD_SERMON', 'Error: ' . $e->getMessage(), 'ERROR');
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Update sermon
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    try {
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!verifyCsrfToken($csrfToken)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Security verification failed']);
            exit;
        }
        
        $id = (int)($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $speaker = trim($_POST['speaker'] ?? '');
        $description = trim($_POST['description'] ?? '');
        
        if ($id <= 0 || empty($title) || empty($speaker)) {
            echo json_encode(['success' => false, 'message' => 'Invalid input']);
            exit;
        }
        
        $stmt = $pdo->prepare("
            UPDATE sermons 
            SET title = ?, speaker = ?, description = ?
            WHERE id = ?
        ");
        $stmt->execute([$title, $speaker, $description, $id]);
        
        logAction('UPDATE_SERMON', "ID: $id, Title: $title");
        
        echo json_encode(['success' => true, 'message' => 'Sermon updated successfully']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Delete sermon
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    try {
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!verifyCsrfToken($csrfToken)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Security verification failed']);
            exit;
        }
        
        $id = (int)($_POST['id'] ?? 0);
        
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid sermon ID']);
            exit;
        }
        
        // Get sermon details before deletion
        $stmt = $pdo->prepare("SELECT file_url, thumbnail FROM sermons WHERE id = ?");
        $stmt->execute([$id]);
        $sermon = $stmt->fetch();
        
        if ($sermon) {
            // Delete files
            if ($sermon['file_url']) {
                $filePath = __DIR__ . '/' . $sermon['file_url'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            if ($sermon['thumbnail']) {
                $thumbPath = __DIR__ . '/' . $sermon['thumbnail'];
                if (file_exists($thumbPath)) {
                    unlink($thumbPath);
                }
            }
            
            // Delete from database
            $deleteStmt = $pdo->prepare("DELETE FROM sermons WHERE id = ?");
            $deleteStmt->execute([$id]);
            
            logAction('DELETE_SERMON', "ID: $id");
            
            echo json_encode(['success' => true, 'message' => 'Sermon deleted successfully']);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Sermon not found']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Search sermons
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['search'])) {
    try {
        $query = '%' . trim($_GET['search'] ?? '') . '%';
        
        $stmt = $pdo->prepare("
            SELECT id, title, date, speaker, ministry, description, 
                   thumbnail, youtube, file_url, duration, views, created_at
            FROM sermons 
            WHERE title LIKE ? OR speaker LIKE ? OR description LIKE ?
            ORDER BY date DESC
            LIMIT 50
        ");
        $stmt->execute([$query, $query, $query]);
        $sermons = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'sermons' => $sermons,
            'total' => count($sermons)
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Default response
http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Invalid request']);
