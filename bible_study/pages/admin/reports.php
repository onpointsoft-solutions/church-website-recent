<?php
// CEFC Bible Study Management System
// File: pages/admin/reports.php
// Description: Reports and analytics page

require_once '../../config/config.php';
require_once '../../config/db.php';
require_once '../../includes/functions.php';
requireLogin();
requireRole(['admin']);

$pageTitle = 'Reports & Analytics';
$activePage = 'reports';

// Fetch data
$semesters = getAllSemesters($pdo);
$selectedSemesterId = (int)($_GET['semester_id'] ?? 0);

// Default to active semester if none selected
if (!$selectedSemesterId) {
    $activeSemester = getActiveSemester($pdo);
    $selectedSemesterId = $activeSemester ? $activeSemester['id'] : 0;
}

$selectedSemester = null;
$groupRankings = [];
$attendanceOverview = [];
$sessionHistory = [];
$topMembers = [];

// Certificate data for Section 5
$certificateStats = [];
$issuedCertificates = [];
$eligibleForCertificates = [];

if ($selectedSemesterId) {
    $stmt = $pdo->prepare("SELECT * FROM bs_semesters WHERE id = ?");
    $stmt->execute([$selectedSemesterId]);
    $selectedSemester = $stmt->fetch();
    
    // Group rankings
    $groupRankings = getGroupRankings($pdo, $selectedSemesterId);
    
    // Attendance overview
    $attendanceStmt = $pdo->prepare("
        SELECT g.name as group_name,
               COUNT(CASE WHEN a.status = 'present' THEN 1 END) as present,
               COUNT(CASE WHEN a.status = 'late' THEN 1 END) as late,
               COUNT(CASE WHEN a.status = 'absent' THEN 1 END) as absent,
               COUNT(CASE WHEN a.status = 'excused' THEN 1 END) as excused,
               COUNT(*) as total_sessions
        FROM bs_groups g
        LEFT JOIN bs_users u ON u.group_id = g.id
        LEFT JOIN bs_attendance a ON a.user_id = u.id
        LEFT JOIN bs_sessions s ON s.id = a.session_id AND s.semester_id = g.semester_id
        WHERE g.semester_id = ?
        GROUP BY g.id, g.name
        ORDER BY g.name
    ");
    $attendanceStmt->execute([$selectedSemesterId]);
    $attendanceData = $attendanceStmt->fetchAll();
    
    foreach ($attendanceData as $row) {
        $attended = $row['present'] + $row['late'];
        $percentage = $row['total_sessions'] > 0 ? round(($attended / $row['total_sessions']) * 100, 1) : 0;
        $attendanceOverview[] = [
            'group_name' => $row['group_name'],
            'present' => $row['present'],
            'late' => $row['late'],
            'absent' => $row['absent'],
            'excused' => $row['excused'],
            'attendance_percentage' => $percentage
        ];
    }
    
    // Session history
    $sessionHistoryStmt = $pdo->prepare("
        SELECT s.session_number, s.session_date, s.topic, s.book_reference, s.status,
               COUNT(DISTINCT sc.group_id) as groups_scored
        FROM bs_sessions s
        LEFT JOIN bs_scores sc ON sc.session_id = s.id
        WHERE s.semester_id = ?
        GROUP BY s.id
        ORDER BY s.session_date, s.session_number
    ");
    $sessionHistoryStmt->execute([$selectedSemesterId]);
    $sessionHistory = $sessionHistoryStmt->fetchAll();
    
    // Top members (based on attendance and achievements)
    $topMembersStmt = $pdo->prepare("
        SELECT u.name, g.name as group_name,
               COUNT(CASE WHEN a.status IN ('present', 'late') THEN 1 END) as attended,
               COUNT(*) as total_sessions
        FROM bs_users u
        LEFT JOIN bs_groups g ON g.id = u.group_id
        LEFT JOIN bs_attendance a ON a.user_id = u.id
        LEFT JOIN bs_sessions s ON s.id = a.session_id AND s.semester_id = ?
        WHERE u.role = 'member' AND g.semester_id = ?
        GROUP BY u.id, u.name, g.name
        HAVING total_sessions > 0
        ORDER BY (COUNT(CASE WHEN a.status IN ('present', 'late') THEN 1 END) / COUNT(*)) DESC, u.name
        LIMIT 10
    ");
    $topMembersStmt->execute([$selectedSemesterId, $selectedSemesterId]);
    $topMembersData = $topMembersStmt->fetchAll();
    
    foreach ($topMembersData as $index => $member) {
        $percentage = round(($member['attended'] / $member['total_sessions']) * 100, 1);
        $topMembers[] = [
            'rank' => $index + 1,
            'name' => $member['name'],
            'group_name' => $member['group_name'],
            'attendance_percentage' => $percentage,
            'achievements' => 0 // Placeholder for achievements count
        ];
    }
    
    // Certificate data for Section 5
    // Get certificate statistics
    $certStatsStmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_issued,
            COUNT(CASE WHEN certificate_type = 'Participation' THEN 1 END) as participation_count
        FROM bs_certificates 
        WHERE semester_id = ?
    ");
    $certStatsStmt->execute([$selectedSemesterId]);
    $certificateStats = $certStatsStmt->fetch();
    
    // Get issued certificates with member details
    $issuedCertsStmt = $pdo->prepare("
        SELECT 
            bc.id,
            bc.certificate_type,
            bc.issued_date,
            u.name as member_name,
            u.email as member_email,
            g.name as group_name
        FROM bs_certificates bc
        JOIN bs_users u ON u.id = bc.user_id
        LEFT JOIN bs_groups g ON g.id = u.group_id
        WHERE bc.semester_id = ?
        ORDER BY bc.issued_date DESC, u.name
    ");
    $issuedCertsStmt->execute([$selectedSemesterId]);
    $issuedCertificates = $issuedCertsStmt->fetchAll();
    
    // Get members eligible for certificates (attended at least 1 session)
    $eligibleStmt = $pdo->prepare("
        SELECT DISTINCT u.id, u.name, u.email, g.name as group_name
        FROM bs_users u
        JOIN bs_groups g ON g.id = u.group_id
        JOIN bs_attendance a ON a.user_id = u.id
        JOIN bs_sessions s ON s.id = a.session_id
        WHERE u.role = 'member' 
        AND g.semester_id = ?
        AND a.status IN ('present', 'late')
        AND u.id NOT IN (
            SELECT bc.user_id FROM bs_certificates bc 
            WHERE bc.semester_id = ? AND bc.certificate_type = 'Participation'
        )
        ORDER BY g.name, u.name
    ");
    $eligibleStmt->execute([$selectedSemesterId, $selectedSemesterId]);
    $eligibleForCertificates = $eligibleStmt->fetchAll();
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
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-3xl font-bold text-gray-900">Reports & Analytics</h1>
    <button onclick="window.print()" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
        <i class="fas fa-print mr-2"></i>Print Report
    </button>
</div>

<!-- Semester Filter -->
<div class="bg-white rounded-lg shadow p-4 mb-6">
    <form method="GET" class="flex items-center space-x-4">
        <label class="text-sm font-medium text-gray-700">Select Semester:</label>
        <select name="semester_id" onchange="this.form.submit()" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
            <?php foreach ($semesters as $semester): ?>
                <option value="<?= $semester['id'] ?>" <?= $selectedSemesterId == $semester['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($semester['name']) ?> (<?= formatDate($semester['start_date']) ?> - <?= formatDate($semester['end_date']) ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </form>
</div>

<?php if ($selectedSemester): ?>
    <!-- Group Rankings -->
    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Group Rankings</h2>
            <p class="text-sm text-gray-600"><?= htmlspecialchars($selectedSemester['name']) ?></p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rank</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Group Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Leader</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Points</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sessions Played</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($groupRankings)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                <i class="fas fa-trophy text-4xl text-gray-300 mb-2"></i>
                                <p>No group rankings available</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($groupRankings as $group): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <?php if ($group['rank'] == 1): ?>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-medal mr-1"></i>1st
                                        </span>
                                    <?php elseif ($group['rank'] == 2): ?>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            <i class="fas fa-medal mr-1"></i>2nd
                                        </span>
                                    <?php elseif ($group['rank'] == 3): ?>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                            <i class="fas fa-medal mr-1"></i>3rd
                                        </span>
                                    <?php else: ?>
                                        <span class="text-gray-600">#<?= $group['rank'] ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?= htmlspecialchars($group['name']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= htmlspecialchars($group['leader_name'] ?? 'Not assigned') ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-semibold">
                                    <?= number_format($group['total_points']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <!-- Sessions count would need to be calculated -->
                                    N/A
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Attendance Overview -->
    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Attendance Overview</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Group Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Present</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Late</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Absent</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Excused</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Attendance %</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($attendanceOverview)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                <i class="fas fa-clipboard text-4xl text-gray-300 mb-2"></i>
                                <p>No attendance data available</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($attendanceOverview as $attendance): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?= htmlspecialchars($attendance['group_name']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= number_format($attendance['present']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= number_format($attendance['late']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= number_format($attendance['absent']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= number_format($attendance['excused']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs rounded-full 
                                        <?= $attendance['attendance_percentage'] >= 80 ? 'bg-green-100 text-green-800' : 
                                           ($attendance['attendance_percentage'] >= 60 ? 'bg-amber-100 text-amber-800' : 
                                           'bg-red-100 text-red-800') ?>">
                                        <?= number_format($attendance['attendance_percentage'], 1) ?>%
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Session History -->
    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Session History</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Topic</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Book Reference</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Groups Scored</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($sessionHistory)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                <i class="fas fa-calendar text-4xl text-gray-300 mb-2"></i>
                                <p>No session history available</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($sessionHistory as $index => $session): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $session['session_number'] ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= formatDate($session['session_date']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= htmlspecialchars($session['topic'] ?? 'Not set') ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= htmlspecialchars($session['book_reference'] ?? 'Not set') ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs rounded-full 
                                        <?= $session['status'] === 'completed' ? 'bg-green-100 text-green-800' : 
                                           ($session['status'] === 'active' ? 'bg-blue-100 text-blue-800' : 
                                           'bg-gray-100 text-gray-800') ?>">
                                        <?= htmlspecialchars(ucfirst($session['status'])) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= number_format($session['groups_scored']) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Top Members -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Top Performing Members</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rank</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Group</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Attendance %</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Achievements</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($topMembers)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                <i class="fas fa-user text-4xl text-gray-300 mb-2"></i>
                                <p>No member data available</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($topMembers as $member): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">#<?= $member['rank'] ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?= htmlspecialchars($member['name']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= htmlspecialchars($member['group_name'] ?? 'Not assigned') ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs rounded-full 
                                        <?= $member['attendance_percentage'] >= 80 ? 'bg-green-100 text-green-800' : 
                                           ($member['attendance_percentage'] >= 60 ? 'bg-amber-100 text-amber-800' : 
                                           'bg-red-100 text-red-800') ?>">
                                        <?= number_format($member['attendance_percentage'], 1) ?>%
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= number_format($member['achievements']) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Section 5: Certificate Management -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Certificate Management</h2>
            <p class="text-sm text-gray-600"><?= htmlspecialchars($selectedSemester['name']) ?></p>
        </div>
        
        <!-- Certificate Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-6 border-b border-gray-200">
            <div class="text-center">
                <div class="text-2xl font-bold text-purple-600"><?= number_format($certificateStats['total_issued'] ?? 0) ?></div>
                <div class="text-sm text-gray-600">Total Certificates Issued</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-amber-600"><?= number_format($certificateStats['participation_count'] ?? 0) ?></div>
                <div class="text-sm text-gray-600">Participation Certificates</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-green-600"><?= number_format(count($eligibleForCertificates)) ?></div>
                <div class="text-sm text-gray-600">Eligible for Certificates</div>
            </div>
        </div>
        
        <!-- Bulk Issue Certificates -->
        <?php if (!empty($eligibleForCertificates)): ?>
            <div class="px-6 py-4 bg-amber-50 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-amber-900">Members Eligible for Participation Certificates</h3>
                        <p class="text-xs text-amber-700">These members attended at least one session but haven't received certificates yet.</p>
                    </div>
                    <button onclick="bulkIssueCertificates()" class="px-4 py-2 bg-amber-600 text-white text-sm rounded-lg hover:bg-amber-700 transition-colors">
                        <i class="fas fa-certificate mr-2"></i>Issue All Participation Certificates
                    </button>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Issued Certificates Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Group</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Certificate Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Issued Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($issuedCertificates)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                <i class="fas fa-certificate text-4xl text-gray-300 mb-2"></i>
                                <p>No certificates issued yet</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($issuedCertificates as $cert): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?= htmlspecialchars($cert['member_name']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= htmlspecialchars($cert['member_email']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= htmlspecialchars($cert['group_name'] ?? 'N/A') ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs rounded-full bg-purple-100 text-purple-800">
                                        <?= htmlspecialchars($cert['certificate_type']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= formatDate($cert['issued_date']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="../certificates/generate.php?cert_id=<?= $cert['id'] ?>" 
                                       target="_blank" 
                                       class="text-purple-600 hover:text-purple-900">
                                        <i class="fas fa-eye mr-1"></i>View
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php else: ?>
    <div class="bg-gray-50 border border-gray-200 rounded-lg p-8 text-center">
        <i class="fas fa-chart-bar text-4xl text-gray-300 mb-4"></i>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No Semester Selected</h3>
        <p class="text-gray-600">Select a semester from the dropdown above to view reports.</p>
    </div>
<?php endif; ?>

<!-- JavaScript for Certificate Management -->
<script>
function bulkIssueCertificates() {
    if (!confirm('Are you sure you want to issue participation certificates to all eligible members? This action cannot be undone.')) {
        return;
    }
    
    const button = event.target;
    const originalText = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Issuing...';
    
    fetch('../api/bs_api.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=bulk_issue_participation_certificates&semester_id=<?= $selectedSemesterId ?>`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`Successfully issued ${data.data.count} participation certificates!`);
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while issuing certificates.');
    })
    .finally(() => {
        button.disabled = false;
        button.innerHTML = originalText;
    });
}
</script>

<style>
@media print {
    /* Hide sidebar and header */
    .fixed, .lg\:ml-64, .pt-16 {
        position: static !important;
        margin-left: 0 !important;
        padding-top: 0 !important;
    }
    
    /* Hide print button and filters */
    button, form {
        display: none !important;
    }
    
    /* Ensure content takes full width */
    body {
        background: white !important;
    }
    
    .bg-gray-50 {
        background: white !important;
    }
    
    /* Improve table printing */
    table {
        page-break-inside: auto;
    }
    
    tr {
        page-break-inside: avoid;
        page-break-after: auto;
    }
    
    td, th {
        page-break-inside: avoid;
        page-break-after: auto;
    }
}
</style>

<?php
$pageContent = ob_get_clean();
require_once '../../includes/layout.php';
?>