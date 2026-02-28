<?php
// CEFC Bible Study Management System
// File: pages/coordinator/dashboard.php
// Description: Coordinator overview dashboard

require_once '../../config/config.php';
require_once '../../config/db.php';
require_once '../../includes/functions.php';
requireLogin();
requireRole(['coordinator']);

$pageTitle = 'Coordinator Dashboard';
$activePage = 'dashboard';

// Fetch data
$activeSemester = getActiveSemester($pdo);
$totalSessions = 0;
$latestSession = null;
$totalGroups = 0;
$totalMembers = 0;
$attendanceRate = 0;
$groupsPendingScoring = [];
$upcomingSession = null;

if ($activeSemester) {
    // Total sessions in active semester
    $sessionStmt = $pdo->prepare("SELECT COUNT(*) as total FROM bs_sessions WHERE semester_id = ?");
    $sessionStmt->execute([$activeSemester['id']]);
    $totalSessions = $sessionStmt->fetch()['total'];
    
    // Latest session
    $latestSessionStmt = $pdo->prepare("
        SELECT * FROM bs_sessions 
        WHERE semester_id = ? 
        ORDER BY session_date DESC, session_number DESC 
        LIMIT 1
    ");
    $latestSessionStmt->execute([$activeSemester['id']]);
    $latestSession = $latestSessionStmt->fetch();
    
    // Total groups in active semester
    $groupStmt = $pdo->prepare("SELECT COUNT(*) as total FROM bs_groups WHERE semester_id = ?");
    $groupStmt->execute([$activeSemester['id']]);
    $totalGroups = $groupStmt->fetch()['total'];
    
    // Total members
    $memberStmt = $pdo->prepare("SELECT COUNT(*) as total FROM bs_users WHERE role = 'member'");
    $memberStmt->execute();
    $totalMembers = $memberStmt->fetch()['total'];
    
    // Attendance rate for latest session
    if ($latestSession) {
        $attendanceStmt = $pdo->prepare("
            SELECT 
                COUNT(CASE WHEN a.status IN ('present', 'late') THEN 1 END) as attended,
                COUNT(*) as total
            FROM bs_attendance a
            WHERE a.session_id = ?
        ");
        $attendanceStmt->execute([$latestSession['id']]);
        $attendanceData = $attendanceStmt->fetch();
        
        if ($attendanceData['total'] > 0) {
            $attendanceRate = round(($attendanceData['attended'] / $attendanceData['total']) * 100, 1);
        }
        
        // Groups not yet scored in latest session
        $pendingStmt = $pdo->prepare("
            SELECT g.id, g.name 
            FROM bs_groups g
            WHERE g.semester_id = ?
            AND g.id NOT IN (
                SELECT DISTINCT sc.group_id 
                FROM bs_scores sc 
                WHERE sc.session_id = ?
            )
        ");
        $pendingStmt->execute([$activeSemester['id'], $latestSession['id']]);
        $groupsPendingScoring = $pendingStmt->fetchAll();
    }
    
    // Upcoming session (next Saturday from today)
    $nextSaturday = date('Y-m-d', strtotime('next saturday'));
    $upcomingSession = [
        'date' => $nextSaturday,
        'days_until' => max(0, (strtotime($nextSaturday) - strtotime(date('Y-m-d'))) / (60 * 60 * 24))
    ];
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
    <h1 class="text-3xl font-bold text-gray-900">Coordinator Dashboard</h1>
    <p class="text-gray-600 mt-1">
        <?= $activeSemester ? htmlspecialchars($activeSemester['name']) : 'No active semester' ?>
    </p>
</div>

<?php if ($activeSemester): ?>
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <!-- Active Semester Sessions -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 bg-purple-100 rounded-full">
                    <i class="fas fa-book-open text-purple-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Active Sessions</p>
                    <p class="text-2xl font-bold text-gray-900"><?= number_format($totalSessions) ?></p>
                </div>
            </div>
        </div>

        <!-- Total Groups -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 bg-amber-100 rounded-full">
                    <i class="fas fa-layer-group text-amber-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Groups to Manage</p>
                    <p class="text-2xl font-bold text-gray-900"><?= number_format($totalGroups) ?></p>
                </div>
            </div>
        </div>

        <!-- Latest Session Attendance -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 bg-green-100 rounded-full">
                    <i class="fas fa-clipboard-check text-green-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Latest Attendance</p>
                    <p class="text-2xl font-bold text-gray-900"><?= number_format($attendanceRate, 1) ?>%</p>
                </div>
            </div>
        </div>

        <!-- Groups Pending Scoring -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 bg-red-100 rounded-full">
                    <i class="fas fa-hourglass-half text-red-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Pending Scoring</p>
                    <p class="text-2xl font-bold text-gray-900"><?= count($groupsPendingScoring) ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Semester Info -->
    <div class="bg-amber-50 border border-amber-200 rounded-lg p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Active Semester Information</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <p class="text-sm text-gray-600">Semester Name</p>
                <p class="font-medium text-gray-900"><?= htmlspecialchars($activeSemester['name']) ?></p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Duration</p>
                <p class="font-medium text-gray-900">
                    <?= formatDate($activeSemester['start_date']) ?> - <?= formatDate($activeSemester['end_date']) ?>
                </p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Progress</p>
                <div class="mt-1">
                    <?php
                    $totalWeeks = ceil((strtotime($activeSemester['end_date']) - strtotime($activeSemester['start_date'])) / (7 * 24 * 60 * 60));
                    $weeksCompleted = min($totalSessions, $totalWeeks);
                    $progressPercent = $totalWeeks > 0 ? round(($weeksCompleted / $totalWeeks) * 100) : 0;
                    ?>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-amber-600 h-2 rounded-full" style="width: <?= $progressPercent ?>%"></div>
                    </div>
                    <p class="text-xs text-gray-600 mt-1"><?= $weeksCompleted ?>/<?= $totalWeeks ?> weeks</p>
                </div>
            </div>
        </div>
    </div>

    <?php if ($latestSession): ?>
        <!-- Latest Session Card -->
        <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Latest Session</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                    <div>
                        <p class="text-sm text-gray-600">Session Number</p>
                        <p class="font-medium text-gray-900"><?= $latestSession['session_number'] ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Date</p>
                        <p class="font-medium text-gray-900"><?= formatDate($latestSession['session_date']) ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Topic</p>
                        <p class="font-medium text-gray-900"><?= htmlspecialchars($latestSession['topic'] ?? 'Not set') ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Status</p>
                        <span class="px-2 py-1 text-xs rounded-full 
                            <?= $latestSession['status'] === 'completed' ? 'bg-green-100 text-green-800' : 
                               ($latestSession['status'] === 'published' ? 'bg-blue-100 text-blue-800' : 
                               'bg-gray-100 text-gray-800') ?>">
                            <?= htmlspecialchars(ucfirst($latestSession['status'])) ?>
                        </span>
                    </div>
                </div>
                <?php if ($latestSession['book_reference']): ?>
                    <div class="mb-4">
                        <p class="text-sm text-gray-600">Book Reference</p>
                        <p class="font-medium text-gray-900"><?= htmlspecialchars($latestSession['book_reference']) ?></p>
                    </div>
                <?php endif; ?>
                <div class="flex flex-wrap gap-3">
                    <a href="scoring.php?session_id=<?= $latestSession['id'] ?>" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                        <i class="fas fa-star mr-2"></i>Enter Scores
                    </a>
                    <a href="attendance.php?session_id=<?= $latestSession['id'] ?>" class="px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition-colors">
                        <i class="fas fa-user-check mr-2"></i>Record Attendance
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Groups Scoring Status Table -->
    <?php if ($latestSession && !empty($groupsPendingScoring)): ?>
        <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Latest Session Scoring Status</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Group Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Leader</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Score Entered</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Points</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($groupsPendingScoring as $group): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?= htmlspecialchars($group['name']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <!-- Leader name would need to be fetched -->
                                    Not assigned
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">No</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    0
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <!-- Quick Action Buttons -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <a href="sessions.php" class="px-4 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors text-center">
            <i class="fas fa-plus-circle mr-2"></i>New Session
        </a>
        <a href="scoring.php" class="px-4 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors text-center">
            <i class="fas fa-star mr-2"></i>Enter Scores
        </a>
        <a href="attendance.php" class="px-4 py-3 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition-colors text-center">
            <i class="fas fa-user-check mr-2"></i>Record Attendance
        </a>
        <a href="notifications.php" class="px-4 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors text-center">
            <i class="fas fa-bell mr-2"></i>Send Notifications
        </a>
    </div>

    <?php if ($upcomingSession): ?>
        <!-- Upcoming Session Reminder -->
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-center">
                <i class="fas fa-calendar-alt text-blue-600 text-xl mr-3"></i>
                <div>
                    <p class="text-sm font-medium text-blue-900">Upcoming Session</p>
                    <p class="text-sm text-blue-700">
                        Next session on <?= formatDate($upcomingSession['date']) ?> 
                        (<?= round($upcomingSession['days_until']) ?> days from now)
                    </p>
                </div>
            </div>
        </div>
    <?php endif; ?>

<?php else: ?>
    <!-- No Active Semester -->
    <div class="bg-gray-50 border border-gray-200 rounded-lg p-8 text-center">
        <i class="fas fa-calendar-times text-4xl text-gray-300 mb-4"></i>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No Active Semester</h3>
        <p class="text-gray-600">Please contact an administrator to activate a semester.</p>
    </div>
<?php endif; ?>

<?php
$pageContent = ob_get_clean();
require_once '../../includes/layout.php';
?>