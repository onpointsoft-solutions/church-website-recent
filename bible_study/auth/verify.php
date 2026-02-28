<?php
// CEFC Bible Study Management System
// File: auth/verify.php
// Description: OTP verification page for account activation

require_once '../config/config.php';
require_once '../config/db.php';

// Get email from URL
$email = isset($_GET['email']) ? urldecode(trim($_GET['email'])) : '';

if (empty($email)) {
    header('Location: register.php');
    exit;
}

$error = '';

// Handle OTP verification form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submitted_otp = trim($_POST['otp'] ?? '');
    
    if (empty($submitted_otp)) {
        $error = 'Please enter the OTP code.';
    } else {
        try {
            // Query user from database
            $stmt = $pdo->prepare("SELECT id, name, otp, verified FROM bs_users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if (!$user) {
                $error = 'Account not found.';
            } elseif ($user['verified'] == 1) {
                // Already verified, redirect to login
                header('Location: login.php?success=already_verified');
                exit;
            } elseif ($submitted_otp === $user['otp']) {
                // OTP matches, verify account
                $stmt = $pdo->prepare("UPDATE bs_users SET verified = 1, otp = NULL WHERE email = ?");
                $stmt->execute([$email]);
                
                header('Location: login.php?success=verified');
                exit;
            } else {
                $error = 'Invalid OTP. Please try again.';
            }
        } catch (PDOException $e) {
            $error = 'Verification failed. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Account - CEFC Bible Study</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="min-h-screen bg-gradient-to-br from-purple-900 to-purple-700 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-purple-100 rounded-full mb-4">
                <i class="fas fa-shield-alt text-purple-600 text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Verify Your Account</h1>
            <p class="text-gray-600 mt-2">Enter the 6-digit code sent to your email</p>
        </div>

        <!-- Info Box -->
        <div class="bg-indigo-50 border border-indigo-200 text-indigo-800 px-4 py-3 rounded-lg mb-6">
            <i class="fas fa-info-circle mr-2"></i>
            We sent a 6-digit OTP to <strong><?php echo htmlspecialchars($email); ?></strong>
        </div>

        <!-- Error Message -->
        <?php if ($error): ?>
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- OTP Verification Form -->
        <form method="POST" class="space-y-6">
            <!-- OTP Input -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Verification Code</label>
                <input type="text" name="otp" required
                       class="w-full px-4 py-4 text-center text-2xl font-mono border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                       placeholder="000000"
                       maxlength="6"
                       inputmode="numeric"
                       pattern="[0-9]{6}"
                       autocomplete="one-time-code">
                <p class="text-sm text-gray-500 mt-2">Enter the 6-digit code from your email</p>
            </div>

            <!-- Submit Button -->
            <button type="submit"
                    class="w-full bg-purple-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-purple-700 transition-colors">
                <i class="fas fa-check-circle mr-2"></i>
                Verify Account
            </button>
        </form>

        <!-- Links -->
        <div class="text-center mt-6 space-y-2">
            <div>
                <a href="login.php" class="text-purple-600 hover:text-purple-700 font-medium text-sm">
                    <i class="fas fa-arrow-left mr-1"></i>
                    Back to Login
                </a>
            </div>
            <div>
                <a href="register.php" class="text-purple-600 hover:text-purple-700 font-medium text-sm">
                    <i class="fas fa-redo mr-1"></i>
                    Wrong email? Register again
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-8 text-sm text-gray-500">
            © CEFC Bible Study Management System
        </div>
    </div>

    <script>
        // Auto-focus OTP input and move cursor to end
        document.addEventListener('DOMContentLoaded', function() {
            const otpInput = document.querySelector('input[name="otp"]');
            if (otpInput) {
                otpInput.focus();
                otpInput.setSelectionRange(6, 6); // Move cursor to end
            }
        });

        // Only allow numeric input
        document.querySelector('input[name="otp"]').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    </script>
</body>
</html>
