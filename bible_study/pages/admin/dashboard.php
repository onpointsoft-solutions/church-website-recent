<?php
// CEFC Bible Study Management System
// File: pages/admin/dashboard.php
// Description: Admin overview stats dashboard

require_once '../../config/config.php';
require_once '../../config/db.php';
require_once '../../includes/functions.php';
requireLogin();
requireRole(['admin']);

$pageTitle = 'Admin Dashboard';
$activePage = 'dashboard';

// Fetch dashboard statistics
$totalUsers = $pdo->query("SELECT COUNT(*) FROM bs_users")->fetchColumn();
$usersByRole = $pdo->query("SELECT role, COUNT(*) as count FROM bs_users GROUP BY role")->fetchAll(PDO::FETCH_KEY_PAIR);
$totalGroups = $pdo->query("SELECT COUNT(*) FROM bs_groups")->fetchColumn();
$activeSemester = getActiveSemester($pdo);
$totalSessions = 0;
$topGroup = null;
$recentUsers = [];

if ($activeSemester) {
    $totalSessions = $pdo->prepare("SELECT COUNT(*) FROM bs_sessions WHERE semester_id = ?")->execute([$activeSemester['id']]) ? 
                    $pdo->query("SELECT FOUND_ROWS()")->fetchColumn() : 0;
    
    $topGroup = $pdo->prepare("
        SELECT g.name, g.total_points, u.name as leader_name 
        FROM bs_groups g 
        LEFT JOIN bs_users u ON g.leader_id = u.id 
        WHERE g.semester_id = ? 
        ORDER BY g.total_points DESC 
        LIMIT 1
    ");
    $topGroup->execute([$activeSemester['id']]);
    $topGroup = $topGroup->fetch();
}

$recentUsers = $pdo->query("
    SELECT name, email, role, age_group, created_at, status 
    FROM bs_users 
    ORDER BY created_at DESC 
    LIMIT 5
")->fetchAll();

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
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900">Admin Dashboard</h1>
    <p class="text-gray-600 mt-2"><?= date('l, F j, Y') ?></p>
</div>

<!-- Stats Cards Row 1 -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Total Users -->
    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-purple-600">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Total Users</p>
                <p class="text-2xl font-bold text-gray-900"><?= number_format($totalUsers) ?></p>
            </div>
            <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                <i class="fas fa-users text-purple-600 text-xl"></i>
            </div>
        </div>
    </div>

    <!-- Active Groups -->
    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-amber-600">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Active Groups</p>
                <p class="text-2xl font-bold text-gray-900"><?= number_format($totalGroups) ?></p>
            </div>
            <div class="w-12 h-12 bg-amber-100 rounded-full flex items-center justify-center">
                <i class="fas fa-layer-group text-amber-600 text-xl"></i>
            </div>
        </div>
    </div>

    <!-- Active Semester -->
    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-600">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Active Semester</p>
                <p class="text-lg font-bold text-gray-900"><?= $activeSemester ? htmlspecialchars($activeSemester['name']) : 'None' ?></p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                <i class="fas fa-calendar text-green-600 text-xl"></i>
            </div>
        </div>
    </div>

    <!-- Total Sessions -->
    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-600">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Total Sessions</p>
                <p class="text-2xl font-bold text-gray-900"><?= number_format($totalSessions) ?></p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                <i class="fas fa-book-open text-blue-600 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Stats Cards Row 2 -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Top Ranked Group -->
    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-amber-500">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Top Ranked Group</h3>
            <div class="w-10 h-10 bg-amber-100 rounded-full flex items-center justify-center">
                <i class="fas fa-trophy text-amber-600"></i>
            </div>
        </div>
        <?php if ($topGroup): ?>
            <div>
                <p class="text-xl font-bold text-gray-900"><?= htmlspecialchars($topGroup['name']) ?></p>
                <p class="text-sm text-gray-600">Leader: <?= htmlspecialchars($topGroup['leader_name'] ?? 'Not assigned') ?></p>
                <p class="text-lg font-semibold text-amber-600 mt-2"><?= number_format($topGroup['total_points']) ?> points</p>
            </div>
        <?php else: ?>
            <p class="text-gray-500">No groups ranked yet</p>
        <?php endif; ?>
    </div>

    <!-- Users by Role -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Users by Role</h3>
        <div class="space-y-2">
            <?php foreach (['admin' => 'Admin', 'coordinator' => 'Coordinator', 'leader' => 'Leader', 'member' => 'Member'] as $role => $label): ?>
                <div class="flex items-center justify-between">
                    <span class="text-gray-700"><?= $label ?></span>
                    <span class="px-2 py-1 text-xs rounded-full <?= getRoleBadgeColor($role) ?>">
                        <?= number_format($usersByRole[$role] ?? 0) ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Recent Registrations -->
<div class="bg-white rounded-lg shadow overflow-hidden mb-8">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">Recent Registrations</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Age Group</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($recentUsers)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">No recent registrations</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($recentUsers as $user): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <?= htmlspecialchars($user['name']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= htmlspecialchars($user['email']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs rounded-full <?= getRoleBadgeColor($user['role']) ?>">
                                    <?= htmlspecialchars(ucfirst($user['role'])) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= htmlspecialchars(ucfirst($user['age_group'])) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= formatDate($user['created_at']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs rounded-full <?= $user['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' ?>">
                                    <?= htmlspecialchars(ucfirst($user['status'])) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Quick Links -->
<div class="flex flex-wrap gap-4">
    <a href="users.php" class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
        <i class="fas fa-users mr-2"></i>
        Manage Users
    </a>
    <a href="groups.php" class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
        <i class="fas fa-layer-group mr-2"></i>
        Manage Groups
    </a>
    <a href="semesters.php" class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
        <i class="fas fa-calendar mr-2"></i>
        Manage Semesters
    </a>
    <a href="reports.php" class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
        <i class="fas fa-chart-bar mr-2"></i>
        View Reports
    </a>
</div>

<?php
$pageContent = ob_get_clean();
require_once '../../includes/layout.php';
?>