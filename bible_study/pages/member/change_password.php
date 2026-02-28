<?php
// CEFC Bible Study Management System
// File: pages/member/change_password.php
// Description: Change password for member users

require_once '../../config/config.php';
require_once '../../config/db.php';
require_once '../../includes/functions.php';
requireLogin();
requireRole(['member']);

$pageTitle = 'Change Password';
$activePage = 'change_password';

// Handle POST action
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Validate input
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $_SESSION['bs_flash'] = ['type' => 'error', 'message' => 'All fields are required.'];
    } elseif ($newPassword !== $confirmPassword) {
        $_SESSION['bs_flash'] = ['type' => 'error', 'message' => 'New passwords do not match.'];
    } elseif (strlen($newPassword) < 6) {
        $_SESSION['bs_flash'] = ['type' => 'error', 'message' => 'Password must be at least 6 characters long.'];
    } else {
        // Verify current password
        $stmt = $pdo->prepare("SELECT password FROM bs_users WHERE id = ?");
        $stmt->execute([$_SESSION['bs_user']['id']]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($currentPassword, $user['password'])) {
            // Update password
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
            $updateStmt = $pdo->prepare("UPDATE bs_users SET password = ? WHERE id = ?");
            $updateStmt->execute([$hashedPassword, $_SESSION['bs_user']['id']]);
            
            $_SESSION['bs_flash'] = ['type' => 'success', 'message' => 'Password changed successfully.'];
        } else {
            $_SESSION['bs_flash'] = ['type' => 'error', 'message' => 'Current password is incorrect.'];
        }
    }
    
    header("Location: change_password.php");
    exit;
}

ob_start();
?>

<!-- Flash Messages -->
<?php if (isset($_SESSION['bs_flash'])): ?>
    <div class="mb-6">
        <div class="bg-<?= $_SESSION['bs_flash']['type'] === 'success' ? 'green' : 'red' ?>-50 border border-<?= $_SESSION['bs_flash']['type'] === 'success' ? 'green' : 'red' ?>-200 text-<?= $_SESSION['bs_flash']['type'] === 'success' ? 'green' : 'red' ?>-800 px-4 py-3 rounded-lg flex items-center justify-between">
            <div class="flex items-center">
                <i class="fas fa-<?= $_SESSION['bs_flash']['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?> mr-2"></i>
                <span><?= htmlspecialchars($_SESSION['bs_flash']['message']) ?></span>
            </div>
            <button onclick="this.parentElement.remove()" class="ml-4 text-<?= $_SESSION['bs_flash']['type'] === 'success' ? 'green' : 'red' ?>-600 hover:text-<?= $_SESSION['bs_flash']['type'] === 'success' ? 'green' : 'red' ?>-800">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
    <?php unset($_SESSION['bs_flash']); ?>
<?php endif; ?>

<!-- Page Header -->
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900">Change Password</h1>
    <p class="text-gray-600 mt-2">Update your account password for better security.</p>
</div>

<!-- Change Password Form -->
<div class="bg-white rounded-lg shadow p-6 max-w-md mx-auto">
    <form method="POST">
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                <input type="password" name="current_password" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                       placeholder="Enter your current password">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                <input type="password" name="new_password" required minlength="6"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                       placeholder="Enter your new password">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                <input type="password" name="confirm_password" required minlength="6"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                       placeholder="Confirm your new password">
            </div>
        </div>
        
        <div class="mt-6">
            <button type="submit" class="w-full px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                <i class="fas fa-key mr-2"></i>Change Password
            </button>
        </div>
    </form>
</div>

<?php
$pageContent = ob_get_clean();
require_once '../../includes/layout.php';
?>
