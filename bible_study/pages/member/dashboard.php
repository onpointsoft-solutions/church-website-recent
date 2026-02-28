<?php
// CEFC Bible Study Management System
// File: pages/member/dashboard.php
// Description: Personal progress and ranking for logged-in member

require_once '../../config/config.php';
require_once '../../config/db.php';
require_once '../../includes/functions.php';
requireLogin();
requireRole(['member']);

$pageTitle = 'My Progress';
$activePage = 'dashboard';

// Get current member's user ID
$memberId = $_SESSION['bs_user_id'];

// Fetch member's group
$group = null;
$groupStmt = $pdo->prepare("
    SELECT g.* FROM bs_groups g 
    JOIN bs_users u ON u.group_id = g.id 
    WHERE u.id = ?
");
$groupStmt->execute([$memberId]);
$group = $groupStmt->fetch();

// Initialize variables
$activeSemester = null;
$attendanceStats = [];
$groupRank = 0;
$allGroups = [];
$memberAchievements = [];
$recentSessions = [];
$scripture = '';

if ($group) {
    // Get active semester
    $activeSemester = getActiveSemester($pdo);
    
    if ($activeSemester) {
        // Get member's attendance stats
        $attendanceStmt = $pdo->prepare("
            SELECT 
                COUNT(CASE WHEN a.status = 'present' THEN 1 END) as present,
                COUNT(CASE WHEN a.status = 'late' THEN 1 END) as late,
                COUNT(CASE WHEN a.status = 'absent' THEN 1 END) as absent,
                COUNT(CASE WHEN a.status = 'excused' THEN 1 END) as excused,
                COUNT(*) as total
            FROM bs_attendance a
            WHERE a.user_id = ?
            AND a.session_id IN (SELECT id FROM bs_sessions WHERE semester_id = ?)
        ");
        $attendanceStmt->execute([$memberId, $activeSemester['id']]);
        $attendanceStats = $attendanceStmt->fetch();
        
        // Calculate attendance percentage
        $attendancePercentage = $attendanceStats['total'] > 0 ? 
            round((($attendanceStats['present'] + $attendanceStats['late']) / $attendanceStats['total']) * 100, 1) : 0;
        
        // Get group's total points and rank
        $pointsStmt = $pdo->prepare("SELECT total_points FROM bs_groups WHERE id = ?");
        $pointsStmt->execute([$group['id']]);
        $groupPoints = $pointsStmt->fetch()['total_points'] ?? 0;
        
        $rankStmt = $pdo->prepare("
            SELECT COUNT(*) + 1 as rank 
            FROM bs_groups 
            WHERE semester_id = ? AND total_points > ?
        ");
        $rankStmt->execute([$activeSemester['id'], $groupPoints]);
        $groupRank = $rankStmt->fetch()['rank'];
        
        // Get all groups for leaderboard
        $allGroupsStmt = $pdo->prepare("
            SELECT g.*, u.name as leader_name 
            FROM bs_groups g
            LEFT JOIN bs_users u ON u.id = g.leader_id
            WHERE g.semester_id = ?
            ORDER BY g.total_points DESC
        ");
        $allGroupsStmt->execute([$activeSemester['id']]);
        $allGroups = $allGroupsStmt->fetchAll();
        
        // Get member's achievements
        $achievementStmt = $pdo->prepare("
            SELECT a.*, s.session_number 
            FROM bs_achievements a
            JOIN bs_sessions s ON s.id = a.session_id
            WHERE a.user_id = ?
            AND s.semester_id = ?
            ORDER BY a.created_at DESC
        ");
        $achievementStmt->execute([$memberId, $activeSemester['id']]);
        $memberAchievements = $achievementStmt->fetchAll();
        
        // Get recent sessions (last 5)
        $recentSessionsStmt = $pdo->prepare("
            SELECT s.session_number, s.session_date, s.topic,
                   a.status as attendance_status,
                   COALESCE(SUM(sc.points), 0) as group_score
            FROM bs_sessions s
            LEFT JOIN bs_attendance a ON a.session_id = s.id AND a.user_id = ?
            LEFT JOIN bs_scores sc ON sc.session_id = s.id AND sc.group_id = ?
            WHERE s.semester_id = ?
            GROUP BY s.id
            ORDER BY s.session_date DESC, s.session_number DESC
            LIMIT 5
        ");
        $recentSessionsStmt->execute([$memberId, $group['id'], $activeSemester['id']]);
        $recentSessions = $recentSessionsStmt->fetchAll();
    }
}

// Scripture of the day
$scriptures = [
    "Study to show yourself approved - 2 Timothy 2:15",
    "Your word is a lamp to my feet - Psalm 119:105",
    "Let the word of Christ dwell in you richly - Colossians 3:16",
    "All Scripture is God-breathed - 2 Timothy 3:16",
    "Blessed is the one who reads - Revelation 1:3"
];
$scripture = $scriptures[date('N') % 5];

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

<?php if ($group && $activeSemester): ?>
    <!-- Page Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">My Progress</h1>
        <p class="text-gray-600 mt-1">Welcome, <?= htmlspecialchars($_SESSION['bs_user_name']) ?>! 🙏</p>
    </div>

    <!-- Personal Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <!-- My Attendance % -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 bg-green-100 rounded-full">
                    <i class="fas fa-clipboard-check text-green-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">My Attendance</p>
                    <p class="text-2xl font-bold text-gray-900"><?= number_format($attendancePercentage, 1) ?>%</p>
                </div>
            </div>
        </div>

        <!-- My Group Rank -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 bg-amber-100 rounded-full">
                    <i class="fas fa-ranking-star text-amber-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">My Group Rank</p>
                    <p class="text-2xl font-bold text-gray-900">#<?= $groupRank ?> of <?= count($allGroups) ?></p>
                </div>
            </div>
        </div>

        <!-- My Group Points -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 bg-purple-100 rounded-full">
                    <i class="fas fa-star text-purple-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">My Group Points</p>
                    <p class="text-2xl font-bold text-gray-900"><?= number_format($groupPoints) ?></p>
                </div>
            </div>
        </div>

        <!-- My Achievements -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 bg-yellow-100 rounded-full">
                    <i class="fas fa-trophy text-yellow-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">My Achievements</p>
                    <p class="text-2xl font-bold text-gray-900"><?= count($memberAchievements) ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripture of the Day Card -->
    <div class="bg-amber-50 border border-amber-200 rounded-lg p-6 mb-6">
        <div class="flex items-start">
            <i class="fas fa-book-bible text-amber-600 text-xl mr-4 mt-1"></i>
            <div>
                <h3 class="text-lg font-semibold text-amber-900 mb-2">Scripture of the Day</h3>
                <p class="text-amber-800 italic">"<?= htmlspecialchars($scripture) ?>"</p>
            </div>
        </div>
    </div>

    <!-- My Group's Leaderboard Card -->
    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Group Leaderboard</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rank</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Group Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Points</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($allGroups as $index => $rankGroup): ?>
                        <tr class="<?= $rankGroup['id'] == $group['id'] ? 'bg-purple-50' : 'hover:bg-gray-50' ?>">
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <?php if ($index == 0): ?>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <i class="fas fa-medal mr-1"></i>1st
                                    </span>
                                <?php elseif ($index == 1): ?>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        <i class="fas fa-medal mr-1"></i>2nd
                                    </span>
                                <?php elseif ($index == 2): ?>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                        <i class="fas fa-medal mr-1"></i>3rd
                                    </span>
                                <?php else: ?>
                                    <span class="text-gray-600">#<?= $index + 1 ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <?= htmlspecialchars($rankGroup['name']) ?>
                                <?php if ($rankGroup['id'] == $group['id']): ?>
                                    <span class="ml-2 px-2 py-1 text-xs rounded-full bg-purple-100 text-purple-800">My Group</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-semibold">
                                <?= number_format($rankGroup['total_points']) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- My Attendance History Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">My Recent Sessions</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Session #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Topic</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">My Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Group Score</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($recentSessions)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                <i class="fas fa-calendar text-4xl text-gray-300 mb-2"></i>
                                <p>No sessions attended yet</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentSessions as $session): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $session['session_number'] ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= formatDate($session['session_date']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($session['topic']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs rounded-full 
                                        <?= $session['attendance_status'] === 'present' ? 'bg-green-100 text-green-800' : 
                                           ($session['attendance_status'] === 'late' ? 'bg-amber-100 text-amber-800' : 
                                           ($session['attendance_status'] === 'absent' ? 'bg-red-100 text-red-800' : 
                                           ($session['attendance_status'] === 'excused' ? 'bg-blue-100 text-blue-800' : 
                                           'bg-gray-100 text-gray-800'))) ?>">
                                        <?= htmlspecialchars(ucfirst($session['attendance_status'] ?? 'Not recorded')) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-semibold">
                                    <?= $session['group_score'] ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- My Achievements Section -->
    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">My Achievements</h2>
        </div>
        <?php if (!empty($memberAchievements)): ?>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php foreach ($memberAchievements as $achievement): ?>
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex items-start justify-between mb-2">
                                <h4 class="font-semibold text-gray-900"><?= htmlspecialchars($achievement['achievement_type']) ?></h4>
                                <?php if ($achievement['points_awarded'] > 0): ?>
                                    <span class="px-2 py-1 text-xs rounded-full bg-amber-100 text-amber-800">
                                        +<?= $achievement['points_awarded'] ?> pts
                                    </span>
                                <?php endif; ?>
                            </div>
                            <?php if ($achievement['description']): ?>
                                <p class="text-sm text-gray-600 mb-2"><?= htmlspecialchars($achievement['description']) ?></p>
                            <?php endif; ?>
                            <p class="text-xs text-gray-500">Session <?= $achievement['session_number'] ?> • <?= formatDate($achievement['created_at']) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="px-6 py-8 text-center text-gray-500">
                <i class="fas fa-trophy text-4xl text-gray-300 mb-2"></i>
                <p>No achievements yet. Keep participating!</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Motivation Banner -->
    <div class="mb-6">
        <?php if ($attendancePercentage >= 80): ?>
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-center">
                    <span class="text-2xl mr-3">🌟</span>
                    <p class="text-green-800 font-medium">Excellent attendance! You're a star member!</p>
                </div>
            </div>
        <?php elseif ($attendancePercentage >= 60): ?>
            <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                <div class="flex items-center">
                    <span class="text-2xl mr-3">📖</span>
                    <p class="text-amber-800 font-medium">Good effort! Try not to miss any more sessions.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex items-center">
                    <span class="text-2xl mr-3">🙏</span>
                    <p class="text-red-800 font-medium">We miss you! Please attend more sessions.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>

<?php else: ?>
    <!-- No Group Assigned -->
    <div class="bg-gray-50 border border-gray-200 rounded-lg p-8 text-center">
        <i class="fas fa-users text-4xl text-gray-300 mb-4"></i>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No Group Assigned</h3>
        <p class="text-gray-600">You have not been assigned to a group yet.</p>
    </div>
<?php endif; ?>

<?php
$pageContent = ob_get_clean();
require_once '../../includes/layout.php';
?>