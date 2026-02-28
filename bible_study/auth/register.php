<?php
// CEFC Bible Study Management System
// File: auth/register.php
// Description: Member registration page with OTP verification

require_once '../config/config.php';
require_once '../config/db.php';

// Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

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

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $age_group = $_POST['age_group'] ?? '';
    
    // Validate input
    if (empty($name) || empty($email) || empty($phone) || empty($password) || empty($confirm_password) || empty($age_group)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } else {
        try {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM bs_users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = 'Email already registered.';
            } else {
                // Generate OTP
                $otp = strval(rand(100000, 999999));
                
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                
                // Insert new user
                $stmt = $pdo->prepare("
                    INSERT INTO bs_users (name, email, phone, password, age_group, role, status, verified, otp)
                    VALUES (?, ?, ?, ?, ?, 'member', 'active', 0, ?)
                ");
                $stmt->execute([$name, $email, $phone, $hashed_password, $age_group, $otp]);
                
                // Send OTP email using PHPMailer
                try {
                    require_once VENDOR_PATH;
                    
                    $mail = new PHPMailer(true);
                    
                    // Enable debugging
                    $mail->SMTPDebug = 2; // Enable verbose debug output
                    $mail->Debugoutput = 'error_log'; // Send debug to error log
                    
                    // Server settings
                    $mail->isSMTP();
                    $mail->Host = MAIL_HOST;
                    $mail->SMTPAuth = true;
                    $mail->Username = MAIL_USERNAME;
                    $mail->Password = MAIL_PASSWORD;
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = MAIL_PORT;
                    
                    // Recipients
                    $mail->setFrom(MAIL_USERNAME, MAIL_FROM_NAME);
                    $mail->addAddress($email, $name);
                    
                    // Content
                    $mail->isHTML(true);
                    $mail->Subject = 'CEFC Bible Study - Verify Your Account';
                    $mail->Body = "
                        <h2>Hello $name,</h2>
                        <p>Your OTP verification code is: <strong style='font-size: 24px; color: #6B46C1;'>$otp</strong></p>
                        <p>Enter this code to activate your account.</p>
                        <p>This code expires in 24 hours.</p>
                        <br>
                        <p>- CEFC Bible Study Team</p>
                    ";
                    
                    // Debug: Log email attempt
                    error_log("Attempting to send email to: $email");
                    error_log("SMTP Host: " . MAIL_HOST);
                    error_log("SMTP Username: " . MAIL_USERNAME);
                    error_log("SMTP Port: " . MAIL_PORT);
                    error_log("OTP Code: $otp");
                    
                    $mail->send();
                    
                    // Debug: Log success
                    error_log("Email sent successfully to: $email");
                    
                } catch (Exception $e) {
                    // Log email error but continue registration
                    error_log("Email sending failed: " . $e->getMessage());
                    error_log("PHPMailer Error: " . $mail->ErrorInfo);
                    // For testing: Show OTP in error message
                    $error = "Email Error: " . $e->getMessage() . "<br>Your OTP is: <strong>$otp</strong>";
                }
                
                // Redirect to verification page
                header('Location: verify.php?email=' . urlencode($email));
                exit;
            }
        } catch (PDOException $e) {
            $error = 'Registration failed. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - CEFC Bible Study</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="min-h-screen bg-gradient-to-br from-purple-900 to-purple-700 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-purple-100 rounded-full mb-4">
                <i class="fas fa-user-plus text-purple-600 text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Create Your Account</h1>
            <p class="text-gray-600 mt-2">Members registration</p>
        </div>

        <!-- Error Message -->
        <?php if ($error): ?>
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Registration Form -->
        <form method="POST" class="space-y-4">
            <!-- Full Name Field -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-user text-gray-400"></i>
                    </div>
                    <input type="text" name="name" required
                           class="pl-10 w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                           placeholder="Enter your full name"
                           value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                </div>
            </div>

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

            <!-- Phone Field -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-phone text-gray-400"></i>
                    </div>
                    <input type="tel" name="phone" required
                           class="pl-10 w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                           placeholder="+254 700 000 000"
                           value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                </div>
            </div>

            <!-- Age Group Field -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Age Group</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-users text-gray-400"></i>
                    </div>
                    <select name="age_group" required
                            class="pl-10 w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent appearance-none">
                        <option value="">Select Age Group</option>
                        <option value="youth" <?php echo ($_POST['age_group'] ?? '') === 'youth' ? 'selected' : ''; ?>>Youth</option>
                        <option value="young_adult" <?php echo ($_POST['age_group'] ?? '') === 'young_adult' ? 'selected' : ''; ?>>Young Adult</option>
                        <option value="adult" <?php echo ($_POST['age_group'] ?? '') === 'adult' ? 'selected' : ''; ?>>Adult</option>
                        <option value="senior" <?php echo ($_POST['age_group'] ?? '') === 'senior' ? 'selected' : ''; ?>>Senior</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <i class="fas fa-chevron-down text-gray-400"></i>
                    </div>
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
                           placeholder="Min. 6 characters">
                    <button type="button" onclick="togglePassword()"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center">
                        <i id="passwordToggle" class="fas fa-eye text-gray-400 hover:text-gray-600"></i>
                    </button>
                </div>
            </div>

            <!-- Confirm Password Field -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Confirm Password</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-lock text-gray-400"></i>
                    </div>
                    <input type="password" name="confirm_password" id="confirm_password" required
                           class="pl-10 pr-10 w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                           placeholder="Re-enter password">
                    <button type="button" onclick="toggleConfirmPassword()"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center">
                        <i id="confirmPasswordToggle" class="fas fa-eye text-gray-400 hover:text-gray-600"></i>
                    </button>
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit"
                    class="w-full bg-purple-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-purple-700 transition-colors">
                <i class="fas fa-user-plus mr-2"></i>
                Create Account
            </button>
        </form>

        <!-- Divider -->
        <div class="relative my-6">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-gray-300"></div>
            </div>
            <div class="relative flex justify-center text-sm">
                <span class="px-2 bg-white text-gray-500">Already have an account?</span>
            </div>
        </div>

        <!-- Login Link -->
        <div class="text-center">
            <a href="login.php" class="text-purple-600 hover:text-purple-700 font-medium">
                <i class="fas fa-sign-in-alt mr-1"></i>
                Already have an account? Sign in
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

        function toggleConfirmPassword() {
            const confirmPasswordInput = document.getElementById('confirm_password');
            const toggleIcon = document.getElementById('confirmPasswordToggle');
            
            if (confirmPasswordInput.type === 'password') {
                confirmPasswordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                confirmPasswordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>