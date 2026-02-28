<?php
// CEFC Bible Study Management System
// File: includes/layout.php
// Description: Master layout shell for all dashboard pages

// Ensure user is logged in
if (!isset($_SESSION['bs_user_id'])) {
    header('Location: ../../auth/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> | <?= htmlspecialchars($pageTitle) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        ::-webkit-scrollbar-thumb {
            background: #6B21A8;
            border-radius: 3px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #5B1A96;
        }
        
        /* Sidebar transition */
        .sidebar-transition {
            transition: transform 0.3s ease-in-out;
        }
        
        /* Active nav link */
        .nav-active {
            background-color: #6B21A8;
            border-left: 4px solid #D97706;
            color: white;
            font-weight: 600;
        }
        
        /* Modal improvements */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        
        .modal-content {
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            max-width: 90vw;
            max-height: 90vh;
            width: 100%;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        
        .modal-header {
            padding: 1.5rem;
            border-bottom: 1px solid #e5e7eb;
            flex-shrink: 0;
        }
        
        .modal-body {
            padding: 1.5rem;
            overflow-y: auto;
            flex: 1;
            min-height: 0;
        }
        
        .modal-footer {
            padding: 1.5rem;
            border-top: 1px solid #e5e7eb;
            flex-shrink: 0;
        }
        
        /* Responsive modal sizes */
        .modal-sm {
            max-width: 400px;
        }
        
        .modal-md {
            max-width: 600px;
        }
        
        .modal-lg {
            max-width: 800px;
        }
        
        .modal-xl {
            max-width: 1200px;
        }
        
        /* Form improvements */
        .form-scrollable {
            max-height: 60vh;
            overflow-y: auto;
            padding-right: 0.5rem;
        }
        
        .form-scrollable::-webkit-scrollbar {
            width: 6px;
        }
        
        .form-scrollable::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }
        
        .form-scrollable::-webkit-scrollbar-thumb {
            background: #6B21A8;
            border-radius: 3px;
        }
        
        .form-scrollable::-webkit-scrollbar-thumb:hover {
            background: #5B1A96;
        }
        
        /* Better mobile responsiveness */
        @media (max-width: 768px) {
            .modal-content {
                margin: 0.5rem;
                max-height: 95vh;
            }
            
            .modal-header,
            .modal-body,
            .modal-footer {
                padding: 1rem;
            }
        }
        
        /* Fix content overflow and right margin issues */
        .main-content-wrapper {
            width: 100%;
            max-width: 100%;
            overflow-x: hidden;
        }
        
        .page-content-container {
            padding: 1rem;
            max-width: 100%;
            overflow-x: auto;
        }
        
        @media (min-width: 1024px) {
            .page-content-container {
                padding: 1.5rem;
            }
        }
        
        /* Ensure tables don't overflow */
        .table-container {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        /* Fix header positioning */
        .header-fixed {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 40;
        }
        
        @media (min-width: 1024px) {
            .header-fixed {
                left: 16rem; /* w-64 = 16rem */
            }
        }
        
        /* Main content area with proper spacing */
        .content-area {
            padding-top: 4rem; /* Space for fixed header */
            min-height: 100vh;
            overflow-x: hidden;
        }
        
        /* Fix sidebar positioning */
        @media (min-width: 1024px) {
            .main-content-wrapper {
                margin-left: 16rem; /* w-64 = 16rem for sidebar width */
            }
        }
        
        /* Sticky form actions */
        .form-actions-sticky {
            position: sticky;
            bottom: 0;
            background: white;
            border-top: 1px solid #e5e7eb;
            padding: 1rem;
            margin: 0 -1.5rem -1.5rem;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside id="sidebar" class="sidebar-transition fixed left-0 top-0 h-full w-64 bg-indigo-950 text-white z-30 transform -translate-x-full lg:translate-x-0">
            <div class="flex flex-col h-full">
                <!-- Logo Area -->
                <div class="p-6 border-b border-indigo-800">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-amber-500 rounded-full flex items-center justify-center">
                            <i class="fas fa-cross text-white text-lg"></i>
                        </div>
                        <div>
                            <h1 class="text-xl font-bold text-white">CEFC</h1>
                            <p class="text-xs text-amber-400">Bible Study</p>
                        </div>
                    </div>
                </div>
                
                <!-- User Info -->
                <div class="p-4 border-b border-indigo-800">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-purple-600 rounded-full flex items-center justify-center">
                            <span class="text-white font-semibold"><?= substr($_SESSION['bs_user_name'], 0, 1) ?></span>
                        </div>
                        <div class="flex-1">
                            <p class="text-white font-medium"><?= htmlspecialchars($_SESSION['bs_user_name']) ?></p>
                            <span class="inline-block px-2 py-1 text-xs rounded-full <?= getRoleBadgeColor($_SESSION['bs_user_role']) ?>">
                                <?= htmlspecialchars(ucfirst($_SESSION['bs_user_role'])) ?>
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Navigation -->
                <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
                    <?php 
                    $currentRole = $_SESSION['bs_user_role'];
                    
                    // Detect if we're in a leader page
                    $isLeaderPage = strpos($_SERVER['REQUEST_URI'], '/leader/') !== false;
                    
                    // Check if user is actually a leader (has a group assigned as leader)
                    $isActuallyLeader = false;
                    try {
                        $stmt = $pdo->prepare("SELECT g.id, g.name FROM bs_groups g WHERE g.leader_id = ?");
                        $stmt->execute([$_SESSION['bs_user_id']]);
                        $leaderGroup = $stmt->fetch();
                        $isActuallyLeader = $leaderGroup !== false;
                    } catch (Exception $e) {
                        $isActuallyLeader = false;
                    }
                    ?>
                    
                    <?php if ($currentRole === 'admin'): ?>
                        <!-- Admin Navigation -->
                        <a href="dashboard.php" data-page="dashboard" 
                           class="nav-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-indigo-800 hover:text-white transition-colors">
                            <i class="fas fa-gauge w-5"></i>
                            <span>Dashboard</span>
                        </a>
                        <a href="users.php" data-page="users"
                           class="nav-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-indigo-800 hover:text-white transition-colors">
                            <i class="fas fa-users w-5"></i>
                            <span>Users</span>
                        </a>
                        <a href="groups.php" data-page="groups"
                           class="nav-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-indigo-800 hover:text-white transition-colors">
                            <i class="fas fa-layer-group w-5"></i>
                            <span>Groups</span>
                        </a>
                        <a href="semesters.php" data-page="semesters"
                           class="nav-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-indigo-800 hover:text-white transition-colors">
                            <i class="fas fa-calendar w-5"></i>
                            <span>Semesters</span>
                        </a>
                        <a href="scoring_rules.php" data-page="scoring_rules"
                           class="nav-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-indigo-800 hover:text-white transition-colors">
                            <i class="fas fa-sliders w-5"></i>
                            <span>Scoring Rules</span>
                        </a>
                        <a href="reports.php" data-page="reports"
                           class="nav-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-indigo-800 hover:text-white transition-colors">
                            <i class="fas fa-chart-bar w-5"></i>
                            <span>Reports</span>
                        </a>
                        
                    <?php elseif ($currentRole === 'coordinator'): ?>
                        <!-- Coordinator Navigation -->
                        <a href="dashboard.php" data-page="dashboard"
                           class="nav-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-indigo-800 hover:text-white transition-colors">
                            <i class="fas fa-gauge w-5"></i>
                            <span>Dashboard</span>
                        </a>
                        <a href="scoring.php" data-page="scoring"
                           class="nav-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-indigo-800 hover:text-white transition-colors">
                            <i class="fas fa-star w-5"></i>
                            <span>Scoring</span>
                        </a>
                        <a href="attendance.php" data-page="attendance"
                           class="nav-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-indigo-800 hover:text-white transition-colors">
                            <i class="fas fa-clipboard w-5"></i>
                            <span>Attendance</span>
                        </a>
                        <a href="achievements.php" data-page="achievements"
                           class="nav-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-indigo-800 hover:text-white transition-colors">
                            <i class="fas fa-trophy w-5"></i>
                            <span>Achievements</span>
                        </a>
                        <a href="notifications.php" data-page="notifications"
                           class="nav-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-indigo-800 hover:text-white transition-colors">
                            <i class="fas fa-bell w-5"></i>
                            <span>Notifications</span>
                        </a>
                        
                        <!-- Account Settings -->
                        <div class="border-t border-indigo-800 pt-2 mt-2">
                            <p class="text-xs text-amber-400 px-4 mb-2">Account Settings</p>
                            <a href="change_password.php" data-page="change_password"
                               class="nav-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-indigo-800 hover:text-white transition-colors">
                                <i class="fas fa-key w-5 text-amber-400"></i>
                                <span>Change Password</span>
                            </a>
                        </div>
                        
                    <?php elseif (($currentRole === 'leader') || ($isLeaderPage && $isActuallyLeader)): ?>
                        <!-- Leader Navigation -->
                        <a href="dashboard.php" data-page="dashboard"
                           class="nav-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-indigo-800 hover:text-white transition-colors">
                            <i class="fas fa-gauge w-5"></i>
                            <span>Leadership Dashboard</span>
                        </a>
                        <a href="members.php" data-page="members"
                           class="nav-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-indigo-800 hover:text-white transition-colors">
                            <i class="fas fa-people-group w-5"></i>
                            <span>My Group Members</span>
                        </a>
                        
                        <!-- Member Tools (for leaders who are also members) -->
                        <div class="border-t border-indigo-800 pt-2 mt-2">
                            <p class="text-xs text-amber-400 px-4 mb-2">Member Tools</p>
                            <a href="../member/dashboard.php" data-page="member_dashboard"
                               class="nav-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-indigo-800 hover:text-white transition-colors">
                                <i class="fas fa-user w-5 text-amber-400"></i>
                                <span>My Dashboard</span>
                            </a>
                            <a href="../member/certificates.php" data-page="member_certificates"
                               class="nav-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-indigo-800 hover:text-white transition-colors">
                                <i class="fas fa-certificate w-5 text-amber-400"></i>
                                <span>My Certificates</span>
                            </a>
                        </div>
                        
                        <!-- Account Settings -->
                        <div class="border-t border-indigo-800 pt-2 mt-2">
                            <p class="text-xs text-amber-400 px-4 mb-2">Account Settings</p>
                            <a href="change_password.php" data-page="change_password"
                               class="nav-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-indigo-800 hover:text-white transition-colors">
                                <i class="fas fa-key w-5 text-amber-400"></i>
                                <span>Change Password</span>
                            </a>
                        </div>
                        
                    <?php elseif ($currentRole === 'member'): ?>
                        <!-- Member Navigation -->
                        <a href="dashboard.php" data-page="dashboard"
                           class="nav-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-indigo-800 hover:text-white transition-colors">
                            <i class="fas fa-gauge w-5"></i>
                            <span>My Dashboard</span>
                        </a>
                        
                        <!-- Leader Navigation (for members who are also leaders) -->
                        <?php if ($isActuallyLeader): ?>
                        <div class="border-t border-indigo-800 pt-2 mt-2">
                            <p class="text-xs text-amber-400 px-4 mb-2">Leader Tools</p>
                            <a href="../leader/dashboard.php" data-page="leader_dashboard"
                               class="nav-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-indigo-800 hover:text-white transition-colors">
                                <i class="fas fa-user-tie w-5 text-amber-400"></i>
                                <span>Leader Dashboard</span>
                            </a>
                            <a href="../leader/members.php" data-page="leader_members"
                               class="nav-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-indigo-800 hover:text-white transition-colors">
                                <i class="fas fa-people-group w-5 text-amber-400"></i>
                                <span>My Group Members</span>
                            </a>
                        </div>
                        <?php endif; ?>
                        
                        <a href="certificates.php" data-page="certificates"
                           class="nav-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-indigo-800 hover:text-white transition-colors">
                            <i class="fas fa-certificate w-5"></i>
                            <span>My Certificates</span>
                        </a>
                        
                        <!-- Account Settings -->
                        <div class="border-t border-indigo-800 pt-2 mt-2">
                            <p class="text-xs text-amber-400 px-4 mb-2">Account Settings</p>
                            <a href="change_password.php" data-page="change_password"
                               class="nav-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-indigo-800 hover:text-white transition-colors">
                                <i class="fas fa-key w-5 text-amber-400"></i>
                                <span>Change Password</span>
                            </a>
                        </div>
                    <?php endif; ?>
                </nav>
                
                <!-- Sidebar Bottom -->
                <div class="p-4 border-t border-indigo-800">
                    <a href="../../auth/logout.php" 
                       class="flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-indigo-800 hover:text-white transition-colors mb-2">
                        <i class="fas fa-right-from-bracket w-5"></i>
                        <span>Logout</span>
                    </a>
                    <p class="text-xs text-gray-400 text-center">CEFC Bible Study</p>
                </div>
            </div>
        </aside>
        
        <!-- Main Content -->
        <div class="flex-1 main-content-wrapper">
            <!-- Top Header -->
            <header class="bg-white shadow-sm header-fixed">
                <div class="flex items-center justify-between px-6 py-4 bg-white border-b border-gray-200">
                    <div class="flex items-center space-x-4">
                        <button id="sidebarToggle" class="lg:hidden text-gray-600 hover:text-gray-900">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                        <!-- Time-based Greeting with Dashboard Description -->
                        <div class="flex-1 text-center lg:text-left">
                            <?php
                            $hour = date('H');
                            $greeting = 'Good evening';
                            if ($hour < 12) $greeting = 'Good morning';
                            elseif ($hour < 17) $greeting = 'Good afternoon';
                            ?>
                            <h1 class="text-xl font-bold text-purple-800"><?= $greeting ?>, <?= htmlspecialchars($_SESSION['bs_user_name']) ?>!</h1>
                            <p class="text-sm text-gray-600">Manage your Bible Study journey</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-2">
                        <!-- Notifications Dropdown -->
                        <div class="relative">
                            <button id="notificationBtn" class="relative text-amber-600 hover:text-amber-700 p-2 rounded-lg hover:bg-amber-50 transition-colors">
                                <i class="fas fa-bell text-xl"></i>
                                <span class="absolute -top-1 -right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                            </button>
                            
                            <!-- Notification Dropdown -->
                            <div id="notificationDropdown" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                                <div class="p-4 border-b border-gray-200">
                                    <h3 class="font-semibold text-gray-900">Notifications</h3>
                                </div>
                                <div class="max-h-96 overflow-y-auto">
                                    <div class="p-4 hover:bg-gray-50 border-b border-gray-100">
                                        <div class="flex items-start space-x-3">
                                            <div class="w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
                                            <div class="flex-1">
                                                <p class="text-sm text-gray-900">New Bible Study session starts this Saturday</p>
                                                <p class="text-xs text-gray-500 mt-1">2 hours ago</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="p-4 hover:bg-gray-50 border-b border-gray-100">
                                        <div class="flex items-start space-x-3">
                                            <div class="w-2 h-2 bg-green-500 rounded-full mt-2"></div>
                                            <div class="flex-1">
                                                <p class="text-sm text-gray-900">Your attendance has been recorded</p>
                                                <p class="text-xs text-gray-500 mt-1">Yesterday</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="p-4 hover:bg-gray-50">
                                        <div class="flex items-start space-x-3">
                                            <div class="w-2 h-2 bg-amber-500 rounded-full mt-2"></div>
                                            <div class="flex-1">
                                                <p class="text-sm text-gray-900">Certificate available for download</p>
                                                <p class="text-xs text-gray-500 mt-1">3 days ago</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="p-3 border-t border-gray-200 text-center">
                                    <button class="text-sm text-purple-600 hover:text-purple-700 font-medium">View all notifications</button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Logout Button -->
                        <a href="../../auth/logout.php" 
                           class="text-red-600 hover:text-red-700 p-2 rounded-lg hover:bg-red-50 transition-colors"
                           title="Logout">
                            <i class="fas fa-sign-out-alt text-xl"></i>
                        </a>
                    </div>
                </div>
            </header>
            
            <!-- Page Content -->
            <main class="content-area bg-gray-50">
                <div class="page-content-container">
                    <?= $pageContent ?>
                    
                    <!-- Footer -->
                    <footer class="mt-12 text-center text-sm text-gray-400">
                        CEFC Bible Study Management System v1.0
                    </footer>
                </div>
            </main>
        </div>
    </div>
    
    <!-- JavaScript -->
    <script>
        // Notification dropdown functionality
        const notificationBtn = document.getElementById('notificationBtn');
        const notificationDropdown = document.getElementById('notificationDropdown');
        
        if (notificationBtn && notificationDropdown) {
            notificationBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                notificationDropdown.classList.toggle('hidden');
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', (e) => {
                if (!notificationBtn.contains(e.target) && !notificationDropdown.contains(e.target)) {
                    notificationDropdown.classList.add('hidden');
                }
            });
        }
        
        // Sidebar toggle for mobile
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('-translate-x-full');
        });
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', (e) => {
            if (window.innerWidth < 1024) {
                if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                    sidebar.classList.add('-translate-x-full');
                }
            }
        });
        
        // Highlight active nav link
        const activePage = '<?= $activePage ?>';
        const navLinks = document.querySelectorAll('.nav-link');
        
        navLinks.forEach(link => {
            const page = link.getAttribute('data-page');
            if (page === activePage) {
                link.classList.add('nav-active');
                link.classList.remove('text-gray-300', 'hover:bg-indigo-800', 'hover:text-white');
            }
        });
        
        // Session timeout warning
        const sessionTimeout = 50 * 60 * 1000; // 50 minutes
        setTimeout(() => {
            showSessionWarning();
        }, sessionTimeout);
        
        function showSessionWarning() {
            const warning = document.createElement('div');
            warning.className = 'fixed bottom-4 right-4 bg-amber-100 border border-amber-400 text-amber-800 px-4 py-3 rounded-lg shadow-lg z-50';
            warning.innerHTML = `
                <div class="flex items-center space-x-2">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>Your session will expire in 10 minutes</span>
                </div>
            `;
            document.body.appendChild(warning);
            
            // Auto-remove after 10 seconds
            setTimeout(() => {
                if (warning.parentNode) {
                    warning.parentNode.removeChild(warning);
                }
            }, 10000);
        }
        
        // Helper function for role badge colors (mirrors PHP function)
        function getRoleBadgeColor(role) {
            const colors = {
                'admin': 'bg-red-100 text-red-800',
                'coordinator': 'bg-blue-100 text-blue-800',
                'leader': 'bg-green-100 text-green-800',
                'member': 'bg-amber-100 text-amber-800'
            };
            return colors[role] || 'bg-gray-100 text-gray-800';
        }
    </script>
</body>
</html>