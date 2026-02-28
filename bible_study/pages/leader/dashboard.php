<?php
// CEFC Bible Study Management System
// File: pages/leader/dashboard.php
// Description: Group stats overview for logged-in leader

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

$pageTitle = 'My Group Dashboard';
$activePage = 'dashboard';

// Get current leader's user ID
$leaderId = $_SESSION['bs_user_id'];

// Initialize variables
$activeSemester = null;
$totalPoints = 0;
$currentRank = 0;
$memberCount = 0;
$attendanceRate = 0;
$achievementsCount = 0;
$sessionScores = [];
$latestSession = null;
$allGroups = [];

if ($group) {
    // Get active semester
    $activeSemester = getActiveSemester($pdo);
    
    if ($activeSemester) {
        // Get group's total points
        $pointsStmt = $pdo->prepare("SELECT total_points FROM bs_groups WHERE id = ?");
        $pointsStmt->execute([$group['id']]);
        $totalPoints = $pointsStmt->fetch()['total_points'] ?? 0;
        
        // Get group's current rank
        $rankStmt = $pdo->prepare("
            SELECT COUNT(*) + 1 as rank 
            FROM bs_groups 
            WHERE semester_id = ? AND total_points > ?
        ");
        $rankStmt->execute([$activeSemester['id'], $totalPoints]);
        $currentRank = $rankStmt->fetch()['rank'];
        
        // Get all groups for rankings
        $allGroupsStmt = $pdo->prepare("
            SELECT g.*, u.name as leader_name 
            FROM bs_groups g
            LEFT JOIN bs_users u ON u.id = g.leader_id
            WHERE g.semester_id = ?
            ORDER BY g.total_points DESC
        ");
        $allGroupsStmt->execute([$activeSemester['id']]);
        $allGroups = $allGroupsStmt->fetchAll();
        
        // Get member count
        $memberStmt = $pdo->prepare("SELECT COUNT(*) as count FROM bs_users WHERE group_id = ? AND role = 'member'");
        $memberStmt->execute([$group['id']]);
        $memberCount = $memberStmt->fetch()['count'];
        
        // Get session scores for this group
        $sessionScoresStmt = $pdo->prepare("
            SELECT s.session_number, s.session_date, s.topic,
                   COALESCE(SUM(sc.points), 0) as group_score
            FROM bs_sessions s
            LEFT JOIN bs_scores sc ON sc.session_id = s.id AND sc.group_id = ?
            WHERE s.semester_id = ?
            GROUP BY s.id
            ORDER BY s.session_number
        ");
        $sessionScoresStmt->execute([$group['id'], $activeSemester['id']]);
        $sessionScores = $sessionScoresStmt->fetchAll();
        
        // Get latest session
        $latestSessionStmt = $pdo->prepare("
            SELECT s.*, COALESCE(SUM(sc.points), 0) as group_score
            FROM bs_sessions s
            LEFT JOIN bs_scores sc ON sc.session_id = s.id AND sc.group_id = ?
            WHERE s.semester_id = ?
            GROUP BY s.id
            ORDER BY s.session_date DESC, s.session_number DESC
            LIMIT 1
        ");
        $latestSessionStmt->execute([$group['id'], $activeSemester['id']]);
        $latestSession = $latestSessionStmt->fetch();
        
        // Calculate attendance rate
        $attendanceStmt = $pdo->prepare("
            SELECT 
                COUNT(CASE WHEN a.status IN ('present', 'late') THEN 1 END) as attended,
                COUNT(*) as total
            FROM bs_attendance a
            JOIN bs_users u ON u.id = a.user_id
            WHERE u.group_id = ? AND u.role = 'member'
            AND a.session_id IN (
                SELECT id FROM bs_sessions WHERE semester_id = ?
            )
        ");
        $attendanceStmt->execute([$group['id'], $activeSemester['id']]);
        $attendanceData = $attendanceStmt->fetch();
        
        if ($attendanceData['total'] > 0) {
            $attendanceRate = round(($attendanceData['attended'] / $attendanceData['total']) * 100, 1);
        }
        
        // Get achievements count
        $achievementStmt = $pdo->prepare("
            SELECT COUNT(*) as count 
            FROM bs_achievements a
            JOIN bs_users u ON u.id = a.user_id
            WHERE u.group_id = ?
            AND a.session_id IN (
                SELECT id FROM bs_sessions WHERE semester_id = ?
            )
        ");
        $achievementStmt->execute([$group['id'], $activeSemester['id']]);
        $achievementsCount = $achievementStmt->fetch()['count'];
    }
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

<?php if ($group && $activeSemester): ?>
    <!-- Page Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">My Group Dashboard</h1>
        <p class="text-gray-600 mt-1">
            <?= htmlspecialchars($group['name']) ?>
            <span class="ml-2 px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Active</span>
        </p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <!-- Current Rank -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 bg-amber-100 rounded-full">
                    <i class="fas fa-ranking-star text-amber-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Current Rank</p>
                    <p class="text-2xl font-bold text-gray-900">#<?= $currentRank ?> of <?= count($allGroups) ?></p>
                </div>
            </div>
        </div>

        <!-- Total Points -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 bg-purple-100 rounded-full">
                    <i class="fas fa-star text-purple-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Points</p>
                    <p class="text-2xl font-bold text-gray-900"><?= number_format($totalPoints) ?></p>
                </div>
            </div>
        </div>

        <!-- Group Members -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 bg-blue-100 rounded-full">
                    <i class="fas fa-users text-blue-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Group Members</p>
                    <p class="text-2xl font-bold text-gray-900"><?= $memberCount ?></p>
                </div>
            </div>
        </div>

        <!-- Attendance Rate -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 bg-green-100 rounded-full">
                    <i class="fas fa-clipboard-check text-green-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Attendance Rate</p>
                    <p class="text-2xl font-bold text-gray-900"><?= number_format($attendanceRate, 1) ?>%</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Group Info Card -->
    <div class="bg-amber-50 border border-amber-200 rounded-lg p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Group Information</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <p class="text-sm text-gray-600">Group Name</p>
                <p class="font-medium text-gray-900"><?= htmlspecialchars($group['name']) ?></p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Semester</p>
                <p class="font-medium text-gray-900"><?= htmlspecialchars($activeSemester['name']) ?></p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Leader</p>
                <p class="font-medium text-gray-900">You</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Semester Started</p>
                <p class="font-medium text-gray-900"><?= formatDate($activeSemester['start_date']) ?></p>
            </div>
        </div>
        <div class="mt-4">
            <p class="text-sm text-gray-600">Sessions Progress</p>
            <p class="font-medium text-gray-900">
                <?= count($sessionScores) ?> sessions played out of 
                <?= count(array_filter($sessionScores, fn($s) => $s['group_score'] > 0)) ?> scored
            </p>
        </div>
    </div>

    <!-- Encouragement Banner -->
    <div class="mb-6">
        <?php if ($currentRank == 1): ?>
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-center">
                    <span class="text-2xl mr-3">🏆</span>
                    <p class="text-green-800 font-medium">Your group is leading! Keep it up!</p>
                </div>
            </div>
        <?php elseif ($currentRank <= 3): ?>
            <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                <div class="flex items-center">
                    <span class="text-2xl mr-3">⭐</span>
                    <p class="text-amber-800 font-medium">You're in the top 3! Push harder!</p>
                </div>
            </div>
        <?php else: ?>
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-center">
                    <span class="text-2xl mr-3">💪</span>
                    <p class="text-blue-800 font-medium">Keep going! Every session is a new opportunity!</p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Rank Overview Card -->
    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Current Standings</h2>
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
                                    <span class="ml-2 px-2 py-1 text-xs rounded-full bg-purple-100 text-purple-800">Your Group</span>
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

    <!-- Session Performance Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Session by Session Performance</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Session #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Topic</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Group Score</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rank That Session</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($sessionScores)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                <i class="fas fa-calendar text-4xl text-gray-300 mb-2"></i>
                                <p>No sessions played yet</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($sessionScores as $index => $session): ?>
                            <tr class="<?= $latestSession && $session['session_number'] == $latestSession['session_number'] ? 'bg-purple-50' : 'hover:bg-gray-50' ?>">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $session['session_number'] ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= formatDate($session['session_date']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($session['topic']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="px-2 py-1 text-xs rounded-full font-semibold
                                        <?= $session['group_score'] > 15 ? 'bg-green-100 text-green-800' : 
                                           ($session['group_score'] >= 8 ? 'bg-amber-100 text-amber-800' : 
                                           'bg-red-100 text-red-800') ?>">
                                        <?= $session['group_score'] ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <!-- Session rank would need complex calculation - placeholder -->
                                    N/A
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Achievements Section -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Group Achievements</h2>
            <p class="text-sm text-gray-600">Total: <?= $achievementsCount ?> achievements</p>
        </div>
        <?php if ($achievementsCount > 0): ?>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php
                    // Get achievements for display
                    $achievementListStmt = $pdo->prepare("
                        SELECT a.*, u.name as member_name, s.session_number
                        FROM bs_achievements a
                        JOIN bs_users u ON u.id = a.user_id
                        JOIN bs_sessions s ON s.id = a.session_id
                        WHERE u.group_id = ?
                        ORDER BY a.created_at DESC
                        LIMIT 6
                    ");
                    $achievementListStmt->execute([$group['id']]);
                    $achievementList = $achievementListStmt->fetchAll();
                    
                    foreach ($achievementList as $achievement):
                    ?>
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <p class="font-medium text-gray-900"><?= htmlspecialchars($achievement['member_name']) ?></p>
                                    <p class="text-sm text-gray-600"><?= htmlspecialchars($achievement['achievement_type']) ?></p>
                                    <p class="text-xs text-gray-500 mt-1">Session <?= $achievement['session_number'] ?></p>
                                </div>
                                <?php if ($achievement['points_awarded'] > 0): ?>
                                    <span class="ml-2 px-2 py-1 text-xs rounded-full bg-amber-100 text-amber-800">
                                        +<?= $achievement['points_awarded'] ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="px-6 py-8 text-center text-gray-500">
                <i class="fas fa-trophy text-4xl text-gray-300 mb-2"></i>
                <p>No achievements earned yet</p>
            </div>
        <?php endif; ?>
    </div>

<?php else: ?>
    <!-- No Group Assigned -->
    <div class="bg-gray-50 border border-gray-200 rounded-lg p-8 text-center">
        <i class="fas fa-users text-4xl text-gray-300 mb-4"></i>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No Group Assigned</h3>
        <p class="text-gray-600">You have not been assigned a group yet. Please contact the admin.</p>
    </div>
<?php endif; ?>

<?php
$pageContent = ob_get_clean();
require_once '../../includes/layout.php';
?>