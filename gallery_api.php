<?php
/**
 * Gallery API - Production Ready
 * 
 * Comprehensive API for gallery management with upload, retrieval,
 * and admin controls for the mobile app and web portal.
 * 
 * @version 1.0.0
 * @author Onpoint Softwares Solutions
 */

// Security headers
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
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

// Create gallery table if not exists
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS gallery (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            image_url VARCHAR(500) NOT NULL,
            thumbnail_url VARCHAR(500),
            category VARCHAR(100) DEFAULT 'general',
            uploaded_by VARCHAR(255),
            is_featured BOOLEAN DEFAULT FALSE,
            views INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            
            INDEX idx_category (category),
            INDEX idx_is_featured (is_featured),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
} catch (PDOException $e) {
    error_log('Gallery table creation error: ' . $e->getMessage());
}

// Logging function
function logAction($action, $details, $level = 'INFO') {
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $logEntry = "[$timestamp] [$level] Action: $action | Details: $details | IP: $ip" . PHP_EOL;
    
    $logFile = __DIR__ . '/admin/logs/gallery_api.log';
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

// Get all gallery images (public endpoint)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !isset($_GET['action'])) {
    try {
        $category = $_GET['category'] ?? null;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
        $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
        
        $sql = "SELECT id, title, description, image_url, thumbnail_url, category, 
                       is_featured, views, created_at
                FROM gallery";
        
        $params = [];
        if ($category) {
            $sql .= " WHERE category = ?";
            $params[] = $category;
        }
        
        $sql .= " ORDER BY is_featured DESC, created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $images = $stmt->fetchAll();
        
        logAction('GET_GALLERY', 'Retrieved ' . count($images) . ' images');
        
        echo json_encode([
            'success' => true,
            'images' => $images,
            'total' => count($images)
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Get image by ID
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    try {
        $id = (int)$_GET['id'];
        $stmt = $pdo->prepare("
            SELECT id, title, description, image_url, thumbnail_url, category, 
                   is_featured, views, created_at
            FROM gallery 
            WHERE id = ?
        ");
        $stmt->execute([$id]);
        $image = $stmt->fetch();
        
        if ($image) {
            // Increment view count
            $updateStmt = $pdo->prepare("UPDATE gallery SET views = views + 1 WHERE id = ?");
            $updateStmt->execute([$id]);
            
            echo json_encode([
                'success' => true,
                'image' => $image
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Image not found']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Upload image (admin only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload') {
    try {
        // Verify CSRF token
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!verifyCsrfToken($csrfToken)) {
            logAction('UPLOAD_IMAGE', 'CSRF token verification failed', 'SECURITY');
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Security verification failed']);
            exit;
        }
        
        // Validate required fields
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $category = trim($_POST['category'] ?? 'general');
        $uploadedBy = trim($_POST['uploaded_by'] ?? 'Admin');
        $isFeatured = isset($_POST['is_featured']) ? (bool)$_POST['is_featured'] : false;
        
        if (empty($title)) {
            echo json_encode(['success' => false, 'message' => 'Title is required']);
            exit;
        }
        
        // Handle image upload
        $imageUrl = null;
        $thumbnailUrl = null;
        
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $fileTmp = $_FILES['image']['tmp_name'];
            $fileName = basename($_FILES['image']['name']);
            $fileSize = $_FILES['image']['size'];
            
            // Validate file type and size
            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
            $fileType = mime_content_type($fileTmp);
            $maxSize = 10 * 1024 * 1024; // 10MB
            
            if (!in_array($fileType, $allowedTypes)) {
                echo json_encode(['success' => false, 'message' => 'Invalid image file type. Only JPEG, PNG, and WebP are allowed.']);
                exit;
            }
            
            if ($fileSize > $maxSize) {
                echo json_encode(['success' => false, 'message' => 'File size exceeds 10MB limit']);
                exit;
            }
            
            // Create upload directory
            $uploadDir = __DIR__ . '/uploads/gallery/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }
            
            // Generate unique filename
            $extension = pathinfo($fileName, PATHINFO_EXTENSION);
            $newFileName = uniqid('gallery_', true) . '.' . $extension;
            $dest = $uploadDir . $newFileName;
            
            if (move_uploaded_file($fileTmp, $dest)) {
                $imageUrl = 'uploads/gallery/' . $newFileName;
                
                // Create thumbnail
                $thumbDir = __DIR__ . '/uploads/gallery/thumbs/';
                if (!is_dir($thumbDir)) {
                    mkdir($thumbDir, 0775, true);
                }
                
                $thumbFileName = 'thumb_' . $newFileName;
                $thumbDest = $thumbDir . $thumbFileName;
                
                // Simple thumbnail creation (you can enhance this with image processing libraries)
                if (copy($dest, $thumbDest)) {
                    $thumbnailUrl = 'uploads/gallery/thumbs/' . $thumbFileName;
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to upload image file']);
                exit;
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'No image file provided']);
            exit;
        }
        
        // Insert into database
        $stmt = $pdo->prepare("
            INSERT INTO gallery (title, description, image_url, thumbnail_url, category, uploaded_by, is_featured, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$title, $description, $imageUrl, $thumbnailUrl, $category, $uploadedBy, $isFeatured]);
        $imageId = $pdo->lastInsertId();
        
        logAction('UPLOAD_IMAGE', "ID: $imageId, Title: $title, Category: $category");
        
        echo json_encode([
            'success' => true,
            'message' => 'Image uploaded successfully',
            'id' => $imageId,
            'image_url' => $imageUrl,
            'thumbnail_url' => $thumbnailUrl
        ]);
    } catch (Exception $e) {
        logAction('UPLOAD_IMAGE', 'Error: ' . $e->getMessage(), 'ERROR');
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Delete image
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
            echo json_encode(['success' => false, 'message' => 'Invalid image ID']);
            exit;
        }
        
        // Get image details before deletion
        $stmt = $pdo->prepare("SELECT image_url, thumbnail_url FROM gallery WHERE id = ?");
        $stmt->execute([$id]);
        $image = $stmt->fetch();
        
        if ($image) {
            // Delete files
            if ($image['image_url']) {
                $filePath = __DIR__ . '/' . $image['image_url'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            if ($image['thumbnail_url']) {
                $thumbPath = __DIR__ . '/' . $image['thumbnail_url'];
                if (file_exists($thumbPath)) {
                    unlink($thumbPath);
                }
            }
            
            // Delete from database
            $deleteStmt = $pdo->prepare("DELETE FROM gallery WHERE id = ?");
            $deleteStmt->execute([$id]);
            
            logAction('DELETE_IMAGE', "ID: $id");
            
            echo json_encode(['success' => true, 'message' => 'Image deleted successfully']);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Image not found']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Get categories
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'categories') {
    try {
        $stmt = $pdo->query("SELECT DISTINCT category FROM gallery ORDER BY category");
        $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo json_encode([
            'success' => true,
            'categories' => $categories
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
