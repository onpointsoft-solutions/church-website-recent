<?php
// CEFC Bible Study Management System
// File: auth/login.php
// Description: User authentication login page

require_once '../config/config.php';
require_once '../config/db.php';

// Check if already logged in
if (isset($_SESSION['bs_user_id'])) {
    $role = $_SESSION['bs_user_role'];
    switch ($role) {
        case ROLE_ADMIN:
            header('Location: ../pages/admin/dashboard.php');
            break;
        case ROLE_COORDINATOR:
            header('Location: ../pages/coordinator/dashboard.php');
            break;
        case ROLE_LEADER:
            header('Location: ../pages/leader/dashboard.php');
            break;
        case ROLE_MEMBER:
            header('Location: ../pages/member/dashboard.php');
            break;
        default:
            header('Location: ../pages/member/dashboard.php');
    }
    exit;
}

$error = '';
$success = '';
$show_otp_form = false;
$user_email = '';

// Handle OTP verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'verify_otp') {
    $email = trim($_POST['email'] ?? '');
    $otp = trim($_POST['otp'] ?? '');
    
    if (empty($email) || empty($otp)) {
        $error = 'Email and OTP are required.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, otp FROM bs_users WHERE email = ? AND verified = 0");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && $otp === $user['otp']) {
                // Mark account as verified
                $stmt = $pdo->prepare("UPDATE bs_users SET verified = 1, otp = NULL WHERE id = ?");
                $stmt->execute([$user['id']]);
                $success = 'Account verified! You can now log in.';
            } else {
                $error = 'Invalid OTP. Please check your email and try again.';
            }
        } catch (PDOException $e) {
            $error = 'Verification failed. Please try again.';
        }
    }
}

// Handle success messages from URL
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'verified':
            $success = 'Account verified! You can now log in.';
            break;
        case 'logged_out':
            $success = 'You have been logged out successfully.';
            break;
        case 'already_verified':
            $success = 'Account already verified. Please log in.';
            break;
    }
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validate input
    if (empty($email) || empty($password)) {
        $error = 'Invalid email or password.';
    } else {
        try {
            // Query user from database
            $stmt = $pdo->prepare("SELECT * FROM bs_users WHERE email = ? AND status = 'active'");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Check if account is verified
                if ($user['verified'] == 0) {
                    $error = 'Account not yet verified. Check your email for your OTP.';
                    $show_otp_form = true;
                    $user_email = $email;
                } else {
                    // Set session variables
                    $_SESSION['bs_user_id'] = $user['id'];
                    $_SESSION['bs_user_name'] = $user['name'];
                    $_SESSION['bs_user_role'] = $user['role'];
                    $_SESSION['bs_user_email'] = $user['email'];
                    $_SESSION['bs_login_time'] = time();
                    
                    // Redirect based on role
                    switch ($user['role']) {
                        case ROLE_ADMIN:
                            header('Location: ../pages/admin/dashboard.php');
                            break;
                        case ROLE_COORDINATOR:
                            header('Location: ../pages/coordinator/dashboard.php');
                            break;
                        case ROLE_LEADER:
                            header('Location: ../pages/leader/dashboard.php');
                            break;
                        case ROLE_MEMBER:
                            header('Location: ../pages/member/dashboard.php');
                            break;
                        default:
                            header('Location: ../pages/member/dashboard.php');
                    }
                    exit;
                }
            } else {
                $error = 'Invalid email or password.';
            }
        } catch (PDOException $e) {
            $error = 'Login failed. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - CEFC Bible Study</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="min-h-screen bg-gradient-to-br from-purple-900 to-purple-700 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-purple-100 rounded-full mb-4">
                <i class="fas fa-cross text-purple-600 text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">CEFC Bible Study</h1>
            <p class="text-gray-600 mt-2">Sign in to your account</p>
        </div>

        <!-- Success Message -->
        <?php if ($success): ?>
            <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6">
                <i class="fas fa-check-circle mr-2"></i>
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <!-- Error Message -->
        <?php if ($error): ?>
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- OTP Verification Form (shown when account is not verified) -->
        <?php if ($show_otp_form): ?>
            <div class="bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded-lg mb-6">
                <i class="fas fa-key mr-2"></i>
                <strong>Verify Your Account:</strong> Enter the 6-digit OTP sent to your email.
            </div>
            
            <form method="POST" class="space-y-4 mb-6">
                <input type="hidden" name="action" value="verify_otp">
                <input type="hidden" name="email" value="<?php echo htmlspecialchars($user_email); ?>">
                
                <!-- OTP Field -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Enter OTP Code</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-key text-gray-400"></i>
                        </div>
                        <input type="text" name="otp" required maxlength="6" pattern="[0-9]{6}"
                               class="pl-10 w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent text-center text-lg font-mono"
                               placeholder="000000">
                    </div>
                </div>

                <!-- Verify OTP Button -->
                <button type="submit"
                        class="w-full bg-amber-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-amber-700 transition-colors">
                    <i class="fas fa-check-circle mr-2"></i>
                    Verify OTP
                </button>
            </form>
            
            <div class="text-center text-sm text-gray-600 mb-6">
                <p>Didn't receive the OTP? Check your spam folder or <a href="register.php" class="text-purple-600 hover:text-purple-700">register again</a>.</p>
            </div>
            
            <div class="border-t pt-4">
                <p class="text-center text-sm text-gray-500 mb-3">Or try logging in again:</p>
            </div>
        <?php endif; ?>

        <!-- Login Form -->
        <form method="POST" class="space-y-6">
            <!-- Email Field -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-envelope text-gray-400"></i>
                    </div>
                    <input type="email" name="email" required
                           class="pl-10 w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                           placeholder="your@email.com"
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>
            </div>

            <!-- Password Field -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-lock text-gray-400"></i>
                    </div>
                    <input type="password" name="password" id="password" required
                           class="pl-10 pr-10 w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                           placeholder="Enter your password">
                    <button type="button" onclick="togglePassword()"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center">
                        <i id="passwordToggle" class="fas fa-eye text-gray-400 hover:text-gray-600"></i>
                    </button>
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit"
                    class="w-full bg-purple-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-purple-700 transition-colors">
                <i class="fas fa-sign-in-alt mr-2"></i>
                Sign In
            </button>
        </form>

        <!-- Divider -->
        <div class="relative my-6">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-gray-300"></div>
            </div>
            <div class="relative flex justify-center text-sm">
                <span class="px-2 bg-white text-gray-500">New to CEFC Bible Study?</span>
            </div>
        </div>

        <!-- Register Link -->
        <div class="text-center">
            <a href="register.php" class="text-purple-600 hover:text-purple-700 font-medium">
                <i class="fas fa-user-plus mr-1"></i>
                New member? Register here
            </a>
        </div>

        <!-- Footer -->
        <div class="text-center mt-8 text-sm text-gray-500">
            © CEFC Bible Study Management System
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('passwordToggle');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>