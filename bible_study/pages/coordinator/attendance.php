<?php
// CEFC Bible Study Management System
// File: pages/coordinator/attendance.php
// Description: Record attendance for each session

require_once '../../config/config.php';
require_once '../../config/db.php';
require_once '../../includes/functions.php';
requireLogin();
requireRole(['coordinator']);

$pageTitle = 'Attendance Recording';
$activePage = 'attendance';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] === 'save_attendance') {
        $sessionId = (int)($_POST['session_id'] ?? 0);
        $attendance = $_POST['attendance'] ?? [];
        
        if (!$sessionId) {
            $_SESSION['bs_flash'] = ['type' => 'error', 'message' => 'Invalid session selected.'];
        } else {
            $pdo->beginTransaction();
            try {
                foreach ($attendance as $userId => $status) {
                    // Get user's group_id from database
                    $userStmt = $pdo->prepare("
                        SELECT group_id FROM bs_users 
                        WHERE id = ? AND role = 'member'
                    ");
                    $userStmt->execute([$userId]);
                    $user = $userStmt->fetch();
                    
                    if (!$user) {
                        throw new Exception("Invalid user ID: $userId. User does not exist or is not a member.");
                    }
                    
                    $groupId = $user['group_id'];
                    
                    // Check if group exists (don't restrict to current semester - users might be in groups from other semesters)
                    if ($groupId) {
                        $groupCheckStmt = $pdo->prepare("
                            SELECT id FROM bs_groups 
                            WHERE id = ?
                        ");
                        $groupCheckStmt->execute([$groupId]);
                        if (!$groupCheckStmt->fetch()) {
                            throw new Exception("Invalid group ID: $groupId for user $userId. Group does not exist.");
                        }
                    } else {
                        // User has no group assigned - skip or handle as needed
                        continue;
                    }
                    
                    // Validate status
                    if (!in_array($status, ['present', 'late', 'absent', 'excused'])) {
                        throw new Exception('Invalid attendance status.');
                    }
                    
                    // Insert or update attendance with validated group_id
                    $upsertStmt = $pdo->prepare("
                        INSERT INTO bs_attendance (session_id, user_id, group_id, status) 
                        VALUES (?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE status = VALUES(status)
                    ");
                    $upsertStmt->execute([$sessionId, $userId, $groupId, $status]);
                }
                
                $pdo->commit();
                $_SESSION['bs_flash'] = ['type' => 'success', 'message' => 'Attendance recorded successfully.'];
            } catch (Exception $e) {
                $pdo->rollback();
                $_SESSION['bs_flash'] = ['type' => 'error', 'message' => 'Failed to save attendance: ' . $e->getMessage()];
            }
        }
        header("Location: attendance.php?session_id=$sessionId");
        exit;
    }
    
    elseif ($_POST['action'] === 'mark_all') {
        $sessionId = (int)($_POST['session_id'] ?? 0);
        $groupId = (int)($_POST['group_id'] ?? 0);
        $status = sanitize($_POST['status'] ?? '');
        
        if (!$sessionId || !$groupId || !$status) {
            $_SESSION['bs_flash'] = ['type' => 'error', 'message' => 'Invalid parameters.'];
        } else {
            // Get all members of the group
            $membersStmt = $pdo->prepare("
                SELECT u.id FROM bs_users u 
                WHERE u.group_id = ? AND u.role = 'member'
            ");
            $membersStmt->execute([$groupId]);
            $members = $membersStmt->fetchAll();
            
            $pdo->beginTransaction();
            try {
                foreach ($members as $member) {
                    $upsertStmt = $pdo->prepare("
                        INSERT INTO bs_attendance (session_id, user_id, group_id, status) 
                        VALUES (?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE status = VALUES(status)
                    ");
                    $upsertStmt->execute([$sessionId, $member['id'], $groupId, $status]);
                }
                
                $pdo->commit();
                $_SESSION['bs_flash'] = ['type' => 'success', 'message' => 'All group members marked as ' . $status . '.'];
            } catch (Exception $e) {
                $pdo->rollback();
                $_SESSION['bs_flash'] = ['type' => 'error', 'message' => 'Failed to mark all members.'];
            }
        }
        header("Location: attendance.php?session_id=$sessionId");
        exit;
    }
}

// Fetch data
$activeSemester = getActiveSemester($pdo);
$publishedSessions = [];
$selectedSession = null;
$groups = [];

if ($activeSemester) {
    // Get all published sessions for active semester
    $sessionStmt = $pdo->prepare("
        SELECT * FROM bs_sessions 
        WHERE semester_id = ? AND status = 'published' 
        ORDER BY session_date ASC, session_number ASC
    ");
    $sessionStmt->execute([$activeSemester['id']]);
    $publishedSessions = $sessionStmt->fetchAll();
    
    // Get selected session
    $selectedSessionId = (int)($_GET['session_id'] ?? 0);
    if (!$selectedSessionId && !empty($publishedSessions)) {
        // Default to latest published session
        $selectedSessionId = end($publishedSessions)['id'];
    }
    
    if ($selectedSessionId) {
        $sessionStmt = $pdo->prepare("SELECT * FROM bs_sessions WHERE id = ?");
        $sessionStmt->execute([$selectedSessionId]);
        $selectedSession = $sessionStmt->fetch();
        
        if ($selectedSession) {
            // Get all groups with their members for selected semester
            $groupStmt = $pdo->prepare("
                SELECT g.*, u.name as leader_name,
                       COUNT(u2.id) as member_count
                FROM bs_groups g
                LEFT JOIN bs_users u ON u.id = g.leader_id
                LEFT JOIN bs_users u2 ON u2.group_id = g.id AND u2.role = 'member'
                WHERE g.semester_id = ?
                GROUP BY g.id
                ORDER BY g.name
            ");
            $groupStmt->execute([$activeSemester['id']]);
            $groups = $groupStmt->fetchAll();
            
            // Get members for each group
            foreach ($groups as &$group) {
                $memberStmt = $pdo->prepare("
                    SELECT u.*, a.status as attendance_status
                    FROM bs_users u
                    LEFT JOIN bs_attendance a ON a.user_id = u.id AND a.session_id = ?
                    WHERE u.group_id = ? AND u.role = 'member'
                    ORDER BY u.name
                ");
                $memberStmt->execute([$selectedSessionId, $group['id']]);
                $group['members'] = $memberStmt->fetchAll();
            }
        }
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

<!-- Page Header -->
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900">Attendance Recording</h1>
</div>

<?php if ($activeSemester && $selectedSession): ?>
    <!-- Session Selector -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Select Session</label>
            <select id="sessionSelector" onchange="changeSession()" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                <?php foreach ($publishedSessions as $session): ?>
                    <option value="<?= $session['id'] ?>" <?= $selectedSession['id'] == $session['id'] ? 'selected' : '' ?>>
                        Session <?= $session['session_number'] ?> - <?= formatDate($session['session_date']) ?> - <?= htmlspecialchars($session['topic']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <!-- Selected Session Info Card -->
    <div class="bg-purple-50 border border-purple-200 rounded-lg p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Session Information</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <p class="text-sm text-gray-600">Session Number</p>
                <p class="font-medium text-gray-900"><?= $selectedSession['session_number'] ?></p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Date</p>
                <p class="font-medium text-gray-900"><?= formatDate($selectedSession['session_date']) ?></p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Topic</p>
                <p class="font-medium text-gray-900"><?= htmlspecialchars($selectedSession['topic']) ?></p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Status</p>
                <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                    <?= htmlspecialchars(ucfirst($selectedSession['status'])) ?>
                </span>
            </div>
        </div>
        <?php if ($selectedSession['book_reference']): ?>
            <div class="mt-4">
                <p class="text-sm text-gray-600">Book Reference</p>
                <p class="font-medium text-gray-900"><?= htmlspecialchars($selectedSession['book_reference']) ?></p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Attendance Summary -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <div class="flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-900">Attendance Summary</h3>
            <div id="attendanceCounts" class="text-sm space-x-4">
                <span class="text-green-600">Present: <span id="presentCount">0</span></span>
                <span class="text-amber-600">Late: <span id="lateCount">0</span></span>
                <span class="text-red-600">Absent: <span id="absentCount">0</span></span>
                <span class="text-blue-600">Excused: <span id="excusedCount">0</span></span>
            </div>
        </div>
    </div>

    <!-- Attendance Grid -->
    <form method="POST" id="attendanceForm">
        <input type="hidden" name="action" value="save_attendance">
        <input type="hidden" name="session_id" value="<?= $selectedSession['id'] ?>">
        
        <?php foreach ($groups as $group): ?>
            <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
                <!-- Group Header -->
                <div class="bg-purple-600 text-white px-6 py-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-semibold"><?= htmlspecialchars($group['name']) ?></h3>
                            <p class="text-sm text-purple-200">
                                <?= $group['member_count'] ?> member<?= $group['member_count'] != 1 ? 's' : '' ?>
                                <?php if ($group['leader_name']): ?>
                                    | Leader: <?= htmlspecialchars($group['leader_name']) ?>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="flex space-x-2">
                            <button type="button" onclick="markAllPresent(<?= $group['id'] ?>)" 
                                    class="px-3 py-1 bg-green-500 text-white text-sm rounded hover:bg-green-600 transition-colors">
                                <i class="fas fa-check mr-1"></i>All Present
                            </button>
                            <button type="button" onclick="markAllAbsent(<?= $group['id'] ?>)" 
                                    class="px-3 py-1 bg-red-500 text-white text-sm rounded hover:bg-red-600 transition-colors">
                                <i class="fas fa-times mr-1"></i>All Absent
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Members Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Age Group</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($group['members'] as $member): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <?= htmlspecialchars($member['name']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= htmlspecialchars($member['age_group'] ?? 'Not specified') ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <div class="flex justify-center space-x-2">
                                            <button type="button" 
                                                    class="status-btn px-3 py-1 text-xs rounded-full 
                                                        <?= ($member['attendance_status'] ?? 'absent') === 'present' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600 hover:bg-green-50' ?>"
                                                    onclick="setStatus(<?= $member['id'] ?>, 'present')">
                                                <i class="fas fa-check mr-1"></i>Present
                                            </button>
                                            <button type="button" 
                                                    class="status-btn px-3 py-1 text-xs rounded-full 
                                                        <?= ($member['attendance_status'] ?? 'absent') === 'late' ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-600 hover:bg-amber-50' ?>"
                                                    onclick="setStatus(<?= $member['id'] ?>, 'late')">
                                                <i class="fas fa-clock mr-1"></i>Late
                                            </button>
                                            <button type="button" 
                                                    class="status-btn px-3 py-1 text-xs rounded-full 
                                                        <?= ($member['attendance_status'] ?? 'absent') === 'absent' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-600 hover:bg-red-50' ?>"
                                                    onclick="setStatus(<?= $member['id'] ?>, 'absent')">
                                                <i class="fas fa-times mr-1"></i>Absent
                                            </button>
                                            <button type="button" 
                                                    class="status-btn px-3 py-1 text-xs rounded-full 
                                                        <?= ($member['attendance_status'] ?? 'absent') === 'excused' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-600 hover:bg-blue-50' ?>"
                                                    onclick="setStatus(<?= $member['id'] ?>, 'excused')">
                                                <i class="fas fa-file-alt mr-1"></i>Excused
                                            </button>
                                        </div>
                                        <input type="hidden" name="attendance[<?= $member['id'] ?>]" 
                                               id="attendance_<?= $member['id'] ?>" 
                                               value="<?= $member['attendance_status'] ?? 'absent' ?>">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- Save Button -->
        <div class="flex justify-center mb-8">
            <button type="submit" class="px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                <i class="fas fa-save mr-2"></i>Save Attendance
            </button>
        </div>
    </form>

<?php elseif (!$activeSemester): ?>
    <!-- No Active Semester -->
    <div class="bg-gray-50 border border-gray-200 rounded-lg p-8 text-center">
        <i class="fas fa-calendar-times text-4xl text-gray-300 mb-4"></i>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No Active Semester</h3>
        <p class="text-gray-600">Please contact an administrator to activate a semester.</p>
    </div>
<?php elseif (empty($publishedSessions)): ?>
    <!-- No Published Sessions -->
    <div class="bg-gray-50 border border-gray-200 rounded-lg p-8 text-center">
        <i class="fas fa-calendar-check text-4xl text-gray-300 mb-4"></i>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No Published Sessions</h3>
        <p class="text-gray-600">Please publish sessions before recording attendance.</p>
    </div>
<?php endif; ?>

<!-- Mark All Forms (hidden) -->
<form id="markAllForm" method="POST" style="display: none;">
    <input type="hidden" name="action" value="mark_all">
    <input type="hidden" name="session_id" value="<?= $selectedSession['id'] ?? '' ?>">
    <input type="hidden" name="group_id" id="markAllGroupId">
    <input type="hidden" name="status" id="markAllStatus">
</form>

<script>
function changeSession() {
    const sessionId = document.getElementById('sessionSelector').value;
    if (sessionId) {
        window.location.href = `attendance.php?session_id=${sessionId}`;
    }
}

function setStatus(userId, status) {
    // Update hidden input
    const input = document.getElementById(`attendance_${userId}`);
    input.value = status;
    
    // Update button styles
    const buttons = input.parentElement.querySelectorAll('.status-btn');
    buttons.forEach(btn => {
        btn.classList.remove('bg-green-100', 'text-green-800', 'bg-amber-100', 'text-amber-800', 
                            'bg-red-100', 'text-red-800', 'bg-blue-100', 'text-blue-800');
        btn.classList.add('bg-gray-100', 'text-gray-600');
    });
    
    // Highlight selected button
    const statusColors = {
        'present': ['bg-green-100', 'text-green-800'],
        'late': ['bg-amber-100', 'text-amber-800'],
        'absent': ['bg-red-100', 'text-red-800'],
        'excused': ['bg-blue-100', 'text-blue-800']
    };
    
    const selectedBtn = Array.from(buttons).find(btn => btn.textContent.toLowerCase().includes(status));
    if (selectedBtn) {
        selectedBtn.classList.remove('bg-gray-100', 'text-gray-600');
        selectedBtn.classList.add(...statusColors[status]);
    }
    
    // Update attendance counts
    updateAttendanceCounts();
}

function markAllPresent(groupId) {
    document.getElementById('markAllGroupId').value = groupId;
    document.getElementById('markAllStatus').value = 'present';
    document.getElementById('markAllForm').submit();
}

function markAllAbsent(groupId) {
    document.getElementById('markAllGroupId').value = groupId;
    document.getElementById('markAllStatus').value = 'absent';
    document.getElementById('markAllForm').submit();
}

function updateAttendanceCounts() {
    const counts = {
        present: 0,
        late: 0,
        absent: 0,
        excused: 0
    };
    
    document.querySelectorAll('input[name^="attendance["]').forEach(input => {
        const status = input.value;
        if (counts.hasOwnProperty(status)) {
            counts[status]++;
        }
    });
    
    document.getElementById('presentCount').textContent = counts.present;
    document.getElementById('lateCount').textContent = counts.late;
    document.getElementById('absentCount').textContent = counts.absent;
    document.getElementById('excusedCount').textContent = counts.excused;
}

// Initialize counts on page load
document.addEventListener('DOMContentLoaded', function() {
    updateAttendanceCounts();
});
</script>

<?php
$pageContent = ob_get_clean();
require_once '../../includes/layout.php';
?>