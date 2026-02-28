<?php
// CEFC Bible Study Management System
// File: pages/leader/members.php
// Description: Track and view group member participation

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

// Handle AJAX requests for CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'add_member':
            $memberId = (int)($_POST['member_id'] ?? 0);
            if ($memberId > 0) {
                if (assignMemberToGroup($pdo, $memberId, $group['id'])) {
                    echo json_encode(['success' => true, 'message' => 'Member added to group successfully']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to add member to group']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid member ID']);
            }
            exit;
            
        case 'update_member':
            $memberId = (int)($_POST['member_id'] ?? 0);
            $updateData = [
                'name' => $_POST['name'] ?? '',
                'email' => $_POST['email'] ?? '',
                'phone' => $_POST['phone'] ?? '',
                'age_group' => $_POST['age_group'] ?? ''
            ];
            
            if ($memberId > 0 && !empty($updateData['name'])) {
                if (updateUser($pdo, $memberId, $updateData)) {
                    echo json_encode(['success' => true, 'message' => 'Member updated successfully']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to update member']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid member data']);
            }
            exit;
            
        case 'remove_member':
            $memberId = (int)($_POST['member_id'] ?? 0);
            if ($memberId > 0) {
                if (removeMemberFromGroup($pdo, $memberId)) {
                    echo json_encode(['success' => true, 'message' => 'Member removed from group successfully']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to remove member from group']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid member ID']);
            }
            exit;
    }
}

// Handle GET request for available members
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_available_members') {
    header('Content-Type: application/json');
    
    // Get members who are not in any group yet
    $stmt = $pdo->prepare("
        SELECT u.* 
        FROM bs_users u 
        WHERE u.role = 'member' 
        AND u.status = 'active'
        AND (u.group_id IS NULL OR u.group_id = 0)
        AND u.id != ?
        ORDER BY u.name ASC
    ");
    $stmt->execute([$leaderId]); // Exclude the leader themselves
    $availableMembers = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'members' => $availableMembers]);
    exit;
}

$pageTitle = 'Group Members';
$activePage = 'members';

$members = [];
$activeSemester = null;
$groupStats = [];

if ($group) {
    // Get active semester
    $activeSemester = getActiveSemester($pdo);
    
    if ($activeSemester) {
        // Get all members of this group with their stats
        $memberStmt = $pdo->prepare("
            SELECT u.*,
                   COUNT(CASE WHEN a.status = 'present' THEN 1 END) as present_count,
                   COUNT(CASE WHEN a.status = 'late' THEN 1 END) as late_count,
                   COUNT(CASE WHEN a.status = 'absent' THEN 1 END) as absent_count,
                   COUNT(CASE WHEN a.status = 'excused' THEN 1 END) as excused_count,
                   COUNT(a.id) as total_sessions,
                   COUNT(CASE WHEN a.status IN ('present', 'late') THEN 1 END) / COUNT(a.id) * 100 as attendance_percentage,
                   COUNT(ach.id) as achievements_count
            FROM bs_users u
            LEFT JOIN bs_attendance a ON a.user_id = u.id 
                AND a.session_id IN (SELECT id FROM bs_sessions WHERE semester_id = ?)
            LEFT JOIN bs_achievements ach ON ach.user_id = u.id
                AND ach.session_id IN (SELECT id FROM bs_sessions WHERE semester_id = ?)
            WHERE u.group_id = ? AND u.role = 'member'
            GROUP BY u.id
            ORDER BY u.name
        ");
        $memberStmt->execute([$activeSemester['id'], $activeSemester['id'], $group['id']]);
        $members = $memberStmt->fetchAll();
        
        // Calculate group stats
        $totalMembers = count($members);
        $avgAttendance = $totalMembers > 0 ? array_sum(array_column($members, 'attendance_percentage')) / $totalMembers : 0;
        $totalAchievements = array_sum(array_column($members, 'achievements_count'));
        
        $groupStats = [
            'total_members' => $totalMembers,
            'avg_attendance' => round($avgAttendance, 1),
            'total_achievements' => $totalAchievements
        ];
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
        <h1 class="text-3xl font-bold text-gray-900">Group Members</h1>
        <p class="text-gray-600 mt-1">
            <?= htmlspecialchars($group['name']) ?>
            <span class="ml-2 px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                <?= $groupStats['total_members'] ?> members
            </span>
        </p>
    </div>

    <!-- Group Summary Bar -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-4">
                <div class="flex items-center">
                    <i class="fas fa-users text-blue-500 mr-2"></i>
                    <span class="text-sm text-gray-600">Total members:</span>
                    <span class="ml-1 font-semibold text-gray-900"><?= $groupStats['total_members'] ?></span>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-chart-line text-green-500 mr-2"></i>
                    <span class="text-sm text-gray-600">Avg attendance:</span>
                    <span class="ml-1 font-semibold text-gray-900"><?= number_format($groupStats['avg_attendance'], 1) ?>%</span>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-trophy text-amber-500 mr-2"></i>
                    <span class="text-sm text-gray-600">Total achievements:</span>
                    <span class="ml-1 font-semibold text-gray-900"><?= $groupStats['total_achievements'] ?></span>
                </div>
            </div>
            <button onclick="openAddMemberModal()" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors">
                <i class="fas fa-user-plus mr-2"></i>Add Member
            </button>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <div class="relative">
            <input type="text" id="searchInput" placeholder="Search members by name..." 
                   class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
        </div>
    </div>

    <!-- Members Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Member Details</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200" id="membersTable">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Age Group</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Present</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Late</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Absent</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Excused</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Attendance %</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Achievements</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($members)): ?>
                        <tr>
                            <td colspan="11" class="px-6 py-8 text-center text-gray-500">
                                <i class="fas fa-users text-4xl text-gray-300 mb-2"></i>
                                <p>No members in your group yet.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($members as $index => $member): ?>
                            <tr class="hover:bg-gray-50 member-row" data-name="<?= strtolower(htmlspecialchars($member['name'])) ?>">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $index + 1 ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?= htmlspecialchars($member['name']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= htmlspecialchars($member['age_group'] ?? 'Not specified') ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= htmlspecialchars($member['phone'] ?? 'Not provided') ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900">
                                    <?= $member['present_count'] ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900">
                                    <?= $member['late_count'] ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900">
                                    <?= $member['absent_count'] ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900">
                                    <?= $member['excused_count'] ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="px-2 py-1 text-xs rounded-full 
                                        <?= $member['attendance_percentage'] >= 80 ? 'bg-green-100 text-green-800' : 
                                           ($member['attendance_percentage'] >= 60 ? 'bg-amber-100 text-amber-800' : 
                                           'bg-red-100 text-red-800') ?>">
                                        <?= number_format($member['attendance_percentage'], 1) ?>%
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-gray-900">
                                    <?= $member['achievements_count'] ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <button onclick="showMemberDetails(<?= htmlspecialchars(json_encode($member)) ?>)" 
                                                class="text-blue-600 hover:text-blue-900" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button onclick="openEditMemberModal(<?= htmlspecialchars(json_encode($member)) ?>)" 
                                                class="text-amber-600 hover:text-amber-900" title="Edit Member">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="confirmRemoveMember(<?= $member['id'] ?>, '<?= htmlspecialchars($member['name']) ?>')" 
                                                class="text-red-600 hover:text-red-900" title="Remove from Group">
                                            <i class="fas fa-user-minus"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Leadership Tips Section -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-blue-900 mb-4">
            <i class="fas fa-lightbulb mr-2"></i>Leadership Tips
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2">
                <div class="flex items-start">
                    <i class="fas fa-user-clock text-blue-600 mt-0.5 mr-3"></i>
                    <p class="text-sm text-blue-800">Members with &lt;60% attendance need personal follow-up</p>
                </div>
                <div class="flex items-start">
                    <i class="fas fa-bullhorn text-blue-600 mt-0.5 mr-3"></i>
                    <p class="text-sm text-blue-800">Celebrate achievements publicly to boost morale</p>
                </div>
            </div>
            <div class="space-y-2">
                <div class="flex items-start">
                    <i class="fas fa-users-growth text-blue-600 mt-0.5 mr-3"></i>
                    <p class="text-sm text-blue-800">Team growth earns your group extra points!</p>
                </div>
                <div class="flex items-start">
                    <i class="fas fa-book-bible text-blue-600 mt-0.5 mr-3"></i>
                    <p class="text-sm text-blue-800">Remind members: Bible + Notebook every Saturday</p>
                </div>
            </div>
        </div>
    </div>

<?php else: ?>
    <!-- No Group Assigned -->
    <div class="bg-gray-50 border border-gray-200 rounded-lg p-8 text-center">
        <i class="fas fa-users text-4xl text-gray-300 mb-4"></i>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No Group Assigned</h3>
        <p class="text-gray-600">You have not been assigned a group yet. Please contact the admin.</p>
    </div>
<?php endif; ?>

<!-- Member Detail Modal -->
<div id="memberDetailModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-semibold text-gray-900">Member Details</h3>
                <button onclick="closeModal('memberDetailModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <div id="memberDetailContent">
                <!-- Content will be populated by JavaScript -->
            </div>
        </div>
    </div>
</div>

<!-- Add Member Modal -->
<div id="addMemberModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-lg max-w-md w-full p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-semibold text-gray-900">Add Member to Group</h3>
                <button onclick="closeModal('addMemberModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form id="addMemberForm" onsubmit="addMember(event)">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Member</label>
                        <select name="member_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            <option value="">Choose a member...</option>
                        </select>
                    </div>
                </div>
                
                <div class="mt-6 flex space-x-3">
                    <button type="submit" class="flex-1 bg-purple-600 text-white py-2 px-4 rounded-lg hover:bg-purple-700 transition-colors">
                        <i class="fas fa-user-plus mr-2"></i>Add to Group
                    </button>
                    <button type="button" onclick="closeModal('addMemberModal')" class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-400 transition-colors">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Member Modal -->
<div id="editMemberModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-lg max-w-md w-full p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-semibold text-gray-900">Edit Member</h3>
                <button onclick="closeModal('editMemberModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form id="editMemberForm" onsubmit="updateMember(event)">
                <input type="hidden" name="member_id" id="editMemberId">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Name</label>
                        <input type="text" name="name" id="editMemberName" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input type="email" name="email" id="editMemberEmail" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                        <input type="tel" name="phone" id="editMemberPhone" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Age Group</label>
                        <select name="age_group" id="editMemberAgeGroup" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            <option value="">Select age group...</option>
                            <option value="children">Children</option>
                            <option value="youth">Youth</option>
                            <option value="young_adults">Young Adults</option>
                            <option value="adults">Adults</option>
                        </select>
                    </div>
                </div>
                
                <div class="mt-6 flex space-x-3">
                    <button type="submit" class="flex-1 bg-amber-600 text-white py-2 px-4 rounded-lg hover:bg-amber-700 transition-colors">
                        <i class="fas fa-save mr-2"></i>Update Member
                    </button>
                    <button type="button" onclick="closeModal('editMemberModal')" class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-400 transition-colors">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Confirm Remove Modal -->
<div id="confirmRemoveModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-lg max-w-md w-full p-6">
            <div class="flex items-center mb-4">
                <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mr-4">
                    <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Remove Member</h3>
                    <p class="text-sm text-gray-600">This action will remove the member from your group.</p>
                </div>
            </div>
            
            <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 mb-4">
                <p class="text-sm text-amber-800">
                    <strong>Member:</strong> <span id="removeMemberName"></span>
                </p>
            </div>
            
            <form id="removeMemberForm" onsubmit="removeMember(event)">
                <input type="hidden" name="member_id" id="removeMemberId">
                <div class="flex space-x-3">
                    <button type="submit" class="flex-1 bg-red-600 text-white py-2 px-4 rounded-lg hover:bg-red-700 transition-colors">
                        <i class="fas fa-user-minus mr-2"></i>Remove from Group
                    </button>
                    <button type="button" onclick="closeModal('confirmRemoveModal')" class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-400 transition-colors">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showMemberDetails(member) {
    const content = document.getElementById('memberDetailContent');
    
    // Calculate attendance percentage
    const attendancePercentage = member.total_sessions > 0 ? 
        ((member.present_count + member.late_count) / member.total_sessions * 100).toFixed(1) : 0;
    
    content.innerHTML = `
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Basic Info -->
            <div class="space-y-4">
                <h4 class="font-semibold text-gray-900 text-lg">Basic Information</h4>
                <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                    <div>
                        <p class="text-sm text-gray-600">Name</p>
                        <p class="font-medium text-gray-900">${member.name}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Email</p>
                        <p class="font-medium text-gray-900">${member.email || 'Not provided'}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Phone</p>
                        <p class="font-medium text-gray-900">${member.phone || 'Not provided'}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Age Group</p>
                        <p class="font-medium text-gray-900">${member.age_group || 'Not specified'}</p>
                    </div>
                </div>
            </div>
            
            <!-- Attendance Stats -->
            <div class="space-y-4">
                <h4 class="font-semibold text-gray-900 text-lg">Attendance Statistics</h4>
                <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <p class="text-sm text-gray-600">Attendance Rate</p>
                            <span class="px-2 py-1 text-xs rounded-full ${
                                attendancePercentage >= 80 ? 'bg-green-100 text-green-800' : 
                                (attendancePercentage >= 60 ? 'bg-amber-100 text-amber-800' : 
                                'bg-red-100 text-red-800')
                            }">${attendancePercentage}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-purple-600 h-2 rounded-full" style="width: ${attendancePercentage}%"></div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="text-center">
                            <p class="text-2xl font-bold text-green-600">${member.present_count}</p>
                            <p class="text-xs text-gray-600">Present</p>
                        </div>
                        <div class="text-center">
                            <p class="text-2xl font-bold text-amber-600">${member.late_count}</p>
                            <p class="text-xs text-gray-600">Late</p>
                        </div>
                        <div class="text-center">
                            <p class="text-2xl font-bold text-red-600">${member.absent_count}</p>
                            <p class="text-xs text-gray-600">Absent</p>
                        </div>
                        <div class="text-center">
                            <p class="text-2xl font-bold text-blue-600">${member.excused_count}</p>
                            <p class="text-xs text-gray-600">Excused</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Session History -->
        <div class="mt-6">
            <h4 class="font-semibold text-gray-900 text-lg mb-4">Session History</h4>
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-sm text-gray-600">Session attendance details will be available here.</p>
                <p class="text-xs text-gray-500 mt-2">This feature requires additional data loading from the server.</p>
            </div>
        </div>
        
        <!-- Achievements -->
        <div class="mt-6">
            <h4 class="font-semibold text-gray-900 text-lg mb-4">Achievements</h4>
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="flex items-center">
                    <i class="fas fa-trophy text-amber-500 text-2xl mr-3"></i>
                    <div>
                        <p class="font-medium text-gray-900">${member.achievements_count} Achievements</p>
                        <p class="text-sm text-gray-600">Total achievements earned this semester</p>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    openModal('memberDetailModal');
}

function openModal(modalId) {
    document.getElementById(modalId).classList.remove('hidden');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

// Search functionality
document.getElementById('searchInput').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('#membersTable .member-row');
    
    rows.forEach(row => {
        const memberName = row.getAttribute('data-name');
        if (memberName.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

// Close modal when clicking outside
document.getElementById('memberDetailModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal('memberDetailModal');
    }
});

// CRUD Functions
function openAddMemberModal() {
    // Load available members
    fetch('members.php?action=get_available_members')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const select = document.querySelector('#addMemberForm select[name="member_id"]');
                select.innerHTML = '<option value="">Choose a member...</option>';
                
                data.members.forEach(member => {
                    const option = document.createElement('option');
                    option.value = member.id;
                    option.textContent = `${member.name} (${member.email})`;
                    select.appendChild(option);
                });
                
                openModal('addMemberModal');
            } else {
                alert('Error loading available members: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading available members');
        });
}

function openEditMemberModal(member) {
    document.getElementById('editMemberId').value = member.id;
    document.getElementById('editMemberName').value = member.name;
    document.getElementById('editMemberEmail').value = member.email || '';
    document.getElementById('editMemberPhone').value = member.phone || '';
    document.getElementById('editMemberAgeGroup').value = member.age_group || '';
    
    openModal('editMemberModal');
}

function confirmRemoveMember(memberId, memberName) {
    document.getElementById('removeMemberId').value = memberId;
    document.getElementById('removeMemberName').textContent = memberName;
    
    openModal('confirmRemoveModal');
}

function addMember(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    formData.append('action', 'add_member');
    
    fetch('members.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeModal('addMemberModal');
            location.reload(); // Refresh to show new member
        } else {
            alert('Error adding member: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error adding member');
    });
}

function updateMember(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    formData.append('action', 'update_member');
    
    fetch('members.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeModal('editMemberModal');
            location.reload(); // Refresh to show updated member
        } else {
            alert('Error updating member: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating member');
    });
}

function removeMember(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    formData.append('action', 'remove_member');
    
    fetch('members.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeModal('confirmRemoveModal');
            location.reload(); // Refresh to show updated member list
        } else {
            alert('Error removing member: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error removing member');
    });
}
</script>

<?php
$pageContent = ob_get_clean();
require_once '../../includes/layout.php';
?>