<?php
// CEFC Bible Study Management System
// File: pages/leader/change_password.php
// Description: Allow leaders to change their password

require_once '../../config/config.php';
require_once '../../config/db.php';
require_once '../../includes/functions.php';
requireLogin();

// Allow both 'leader' and 'member' roles, but verify user is actually a group leader
$currentUserRole = $_SESSION['bs_user_role'];
if (!in_array($currentUserRole, ['leader', 'member'])) {
    requireRole(['leader']); // This will show access denied for other roles
}

// Verify this user is actually a leader of a group
$leaderId = $_SESSION['bs_user_id'];
$groupStmt = $pdo->prepare("SELECT * FROM bs_groups WHERE leader_id = ?");
$groupStmt->execute([$leaderId]);
$group = $groupStmt->fetch();

if (!$group) {
    http_response_code(403);
    echo '<div class="flex items-center justify-center min-h-screen bg-gray-50">
            <div class="text-center">
                <i class="fas fa-user-shield text-6xl text-amber-500 mb-4"></i>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Leader Access Required</h1>
                <p class="text-gray-600">You must be assigned as a group leader to access this page.</p>
                <a href="../member/dashboard.php" class="mt-4 inline-block bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700">Back to Member Dashboard</a>
            </div>
          </div>';
    exit;
}

$pageTitle = 'Change Password';
$activePage = 'change_password';

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate input
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'All fields are required.';
    } elseif (strlen($new_password) < 6) {
        $error = 'New password must be at least 6 characters long.';
    } elseif ($new_password !== $confirm_password) {
        $error = 'New passwords do not match.';
    } else {
        // Get current user data
        $user_id = $_SESSION['bs_user_id'];
        $stmt = $pdo->prepare("SELECT password FROM bs_users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($current_password, $user['password'])) {
            // Update password
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
            $update_stmt = $pdo->prepare("UPDATE bs_users SET password = ? WHERE id = ?");
            
            if ($update_stmt->execute([$hashed_password, $user_id])) {
                $success = 'Password changed successfully!';
                
                // Log the password change (optional security measure)
                error_log("Password changed for leader ID: $user_id at " . date('Y-m-d H:i:s'));
            } else {
                $error = 'Failed to update password. Please try again.';
            }
        } else {
            $error = 'Current password is incorrect.';
        }
    }
}

ob_start();
?>

<!-- Page Header -->
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900">Change Password</h1>
    <p class="text-gray-600 mt-1">Update your account password</p>
</div>

<!-- Flash Messages -->
<?php if ($error): ?>
    <div class="mb-6">
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg flex items-center justify-between">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
            <button onclick="this.parentElement.remove()" class="ml-4 text-red-600 hover:text-red-800">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="mb-6">
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg flex items-center justify-between">
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                <span><?= htmlspecialchars($success) ?></span>
            </div>
            <button onclick="this.parentElement.remove()" class="ml-4 text-green-600 hover:text-green-800">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
<?php endif; ?>

<!-- Change Password Form -->
<div class="max-w-md">
    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="" class="space-y-6">
            <!-- Current Password -->
            <div>
                <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">
                    Current Password <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input type="password" 
                           id="current_password" 
                           name="current_password" 
                           required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                           placeholder="Enter your current password">
                    <button type="button" 
                            onclick="togglePassword('current_password', this)"
                            class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-600">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <!-- New Password -->
            <div>
                <label for="new_password" class="block text-sm font-medium text-gray-700 mb-2">
                    New Password <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input type="password" 
                           id="new_password" 
                           name="new_password" 
                           required
                           minlength="6"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                           placeholder="Enter your new password (min 6 characters)">
                    <button type="button" 
                            onclick="togglePassword('new_password', this)"
                            class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-600">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <div class="mt-2">
                    <div class="text-xs text-gray-500">Password must:</div>
                    <ul class="text-xs text-gray-500 mt-1 space-y-1">
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-1" id="length-check"></i>
                            Be at least 6 characters long
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Confirm New Password -->
            <div>
                <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-2">
                    Confirm New Password <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input type="password" 
                           id="confirm_password" 
                           name="confirm_password" 
                           required
                           minlength="6"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                           placeholder="Confirm your new password">
                    <button type="button" 
                            onclick="togglePassword('confirm_password', this)"
                            class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-600">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <div class="mt-2">
                    <div id="match-check" class="text-xs"></div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-4">
                <button type="submit" 
                        class="w-full bg-purple-600 text-white py-2 px-4 rounded-lg hover:bg-purple-700 transition-colors font-medium">
                    <i class="fas fa-key mr-2"></i>
                    Change Password
                </button>
            </div>
        </form>

        <!-- Security Tips -->
        <div class="mt-6 p-4 bg-blue-50 rounded-lg">
            <h3 class="text-sm font-semibold text-blue-900 mb-2">
                <i class="fas fa-shield-alt mr-2"></i>Security Tips
            </h3>
            <ul class="text-xs text-blue-800 space-y-1">
                <li>• Use a combination of letters, numbers, and symbols</li>
                <li>• Avoid using personal information or common words</li>
                <li>• Don't reuse passwords from other accounts</li>
                <li>• Change your password regularly</li>
            </ul>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script>
// Toggle password visibility
function togglePassword(inputId, button) {
    const input = document.getElementById(inputId);
    const icon = button.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Password strength validation
const newPassword = document.getElementById('new_password');
const confirmPassword = document.getElementById('confirm_password');
const lengthCheck = document.getElementById('length-check');
const matchCheck = document.getElementById('match-check');

function validatePasswords() {
    // Check length
    if (newPassword.value.length >= 6) {
        lengthCheck.classList.remove('text-gray-400');
        lengthCheck.classList.add('text-green-500');
    } else {
        lengthCheck.classList.remove('text-green-500');
        lengthCheck.classList.add('text-gray-400');
    }
    
    // Check match
    if (confirmPassword.value.length > 0) {
        if (newPassword.value === confirmPassword.value) {
            matchCheck.innerHTML = '<i class="fas fa-check text-green-500 mr-1"></i>Passwords match';
            matchCheck.className = 'text-xs text-green-600';
        } else {
            matchCheck.innerHTML = '<i class="fas fa-times text-red-500 mr-1"></i>Passwords do not match';
            matchCheck.className = 'text-xs text-red-600';
        }
    } else {
        matchCheck.innerHTML = '';
    }
}

newPassword.addEventListener('input', validatePasswords);
confirmPassword.addEventListener('input', validatePasswords);

// Clear success message after 5 seconds
<?php if ($success): ?>
setTimeout(() => {
    const successMsg = document.querySelector('.bg-green-50');
    if (successMsg) {
        successMsg.remove();
    }
}, 5000);
<?php endif; ?>
</script>

<?php
$pageContent = ob_get_clean();
require_once '../../includes/layout.php';
?>
