<?php
// CEFC Bible Study Management System
// File: pages/coordinator/achievements.php
// Description: Record special achievements for members

require_once '../../config/config.php';
require_once '../../config/db.php';
require_once '../../includes/functions.php';
requireLogin();
requireRole(['coordinator']);

$pageTitle = 'Special Achievements';
$activePage = 'achievements';

// Achievement types
$achievementTypes = [
    'Verse Memorization Champion',
    'Most Improved Member',
    'Perfect Attendance',
    'Best Team Player',
    'Outstanding Participation',
    'First to Answer',
    'Custom'
];

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] === 'record_achievement') {
        $sessionId = (int)($_POST['session_id'] ?? 0);
        $userId = (int)($_POST['user_id'] ?? 0);
        $groupId = (int)($_POST['group_id'] ?? 0);
        $achievementType = sanitize($_POST['achievement_type'] ?? '');
        $customType = sanitize($_POST['custom_type'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        $pointsAwarded = (int)($_POST['points_awarded'] ?? 0);
        
        // Validate required fields
        if (!$sessionId || !$userId || !$groupId || !$achievementType) {
            $_SESSION['bs_flash'] = ['type' => 'error', 'message' => 'Please fill in all required fields.'];
        } elseif ($achievementType === 'Custom' && !$customType) {
            $_SESSION['bs_flash'] = ['type' => 'error', 'message' => 'Please provide a custom achievement type.'];
        } else {
            $finalAchievementType = $achievementType === 'Custom' ? $customType : $achievementType;
            
            $pdo->beginTransaction();
            try {
                // Insert achievement
                $insertStmt = $pdo->prepare("
                    INSERT INTO bs_achievements (session_id, user_id, group_id, achievement_type, description, points_awarded, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW())
                ");
                $insertStmt->execute([$sessionId, $userId, $groupId, $finalAchievementType, $description, $pointsAwarded]);
                
                // If points awarded > 0, also add as bonus score
                if ($pointsAwarded > 0) {
                    // Get or create a bonus category for this session
                    $categoryStmt = $pdo->prepare("
                        SELECT id FROM bs_score_categories 
                        WHERE session_id = ? AND name = 'Bonus Points'
                    ");
                    $categoryStmt->execute([$sessionId]);
                    $categoryId = $categoryStmt->fetchColumn();
                    
                    if (!$categoryId) {
                        // Create bonus category
                        $createCategoryStmt = $pdo->prepare("
                            INSERT INTO bs_score_categories (session_id, name, max_points) 
                            VALUES (?, 'Bonus Points', 10)
                        ");
                        $createCategoryStmt->execute([$sessionId]);
                        $categoryId = $pdo->lastInsertId();
                    }
                    
                    // Insert bonus score
                    $scoreStmt = $pdo->prepare("
                        INSERT INTO bs_scores (session_id, group_id, category_id, points) 
                        VALUES (?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE points = points + VALUES(points)
                    ");
                    $scoreStmt->execute([$sessionId, $groupId, $categoryId, $pointsAwarded]);
                    
                    // Update group total points
                    updateGroupTotalPoints($pdo, $groupId, $activeSemester['id']);
                }
                
                $pdo->commit();
                $_SESSION['bs_flash'] = ['type' => 'success', 'message' => 'Achievement recorded successfully.'];
            } catch (Exception $e) {
                $pdo->rollback();
                $_SESSION['bs_flash'] = ['type' => 'error', 'message' => 'Failed to record achievement: ' . $e->getMessage()];
            }
        }
        header('Location: achievements.php');
        exit;
    }
    
    elseif ($_POST['action'] === 'delete_achievement') {
        $id = (int)($_POST['id'] ?? 0);
        
        if ($id) {
            $deleteStmt = $pdo->prepare("DELETE FROM bs_achievements WHERE id = ?");
            
            if ($deleteStmt->execute([$id])) {
                $_SESSION['bs_flash'] = ['type' => 'success', 'message' => 'Achievement deleted successfully.'];
            } else {
                $_SESSION['bs_flash'] = ['type' => 'error', 'message' => 'Failed to delete achievement.'];
            }
        }
        header('Location: achievements.php');
        exit;
    }
}

// Fetch data
$activeSemester = getActiveSemester($pdo);
$publishedSessions = [];
$members = [];
$achievements = [];

if ($activeSemester) {
    // Get all published sessions for active semester
    $sessionStmt = $pdo->prepare("
        SELECT * FROM bs_sessions 
        WHERE semester_id = ? AND status = 'published' 
        ORDER BY session_date ASC, session_number ASC
    ");
    $sessionStmt->execute([$activeSemester['id']]);
    $publishedSessions = $sessionStmt->fetchAll();
    
    // Get all members with group info
    $memberStmt = $pdo->prepare("
        SELECT u.id, u.name, g.id as group_id, g.name as group_name
        FROM bs_users u
        LEFT JOIN bs_groups g ON g.id = u.group_id
        WHERE u.role = 'member' AND g.semester_id = ?
        ORDER BY g.name, u.name
    ");
    $memberStmt->execute([$activeSemester['id']]);
    $members = $memberStmt->fetchAll();
    
    // Get all achievements for active semester
    $achievementStmt = $pdo->prepare("
        SELECT a.*, u.name as member_name, g.name as group_name, s.session_number, s.session_date
        FROM bs_achievements a
        JOIN bs_users u ON u.id = a.user_id
        JOIN bs_groups g ON g.id = a.group_id
        JOIN bs_sessions s ON s.id = a.session_id
        WHERE s.semester_id = ?
        ORDER BY a.created_at DESC
    ");
    $achievementStmt->execute([$activeSemester['id']]);
    $achievements = $achievementStmt->fetchAll();
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
    <h1 class="text-3xl font-bold text-gray-900">Special Achievements</h1>
    <?php if ($activeSemester && !empty($publishedSessions) && !empty($members)): ?>
        <button onclick="openModal('recordAchievementModal')" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
            <i class="fas fa-trophy mr-2"></i>Record Achievement
        </button>
    <?php endif; ?>
</div>

<?php if ($activeSemester): ?>
    <!-- Achievements Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Achievement Records</h2>
            <p class="text-sm text-gray-600"><?= htmlspecialchars($activeSemester['name']) ?></p>
        </div>
        
        <?php if (!empty($achievements)): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Group</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Achievement Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Points Awarded</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Session</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($achievements as $achievement): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?= htmlspecialchars($achievement['member_name']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= htmlspecialchars($achievement['group_name']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= htmlspecialchars($achievement['achievement_type']) ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <?= htmlspecialchars($achievement['description'] ?? '-') ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php if ($achievement['points_awarded'] > 0): ?>
                                        <span class="px-2 py-1 text-xs rounded-full bg-amber-100 text-amber-800">
                                            +<?= $achievement['points_awarded'] ?> pts
                                        </span>
                                    <?php else: ?>
                                        <span class="text-gray-400">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    Session <?= $achievement['session_number'] ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= formatDate($achievement['created_at']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button onclick="openDeleteModal(<?= $achievement['id'] ?>)" class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="px-6 py-8 text-center text-gray-500">
                <i class="fas fa-trophy text-4xl text-gray-300 mb-2"></i>
                <p>No achievements recorded yet</p>
                <?php if (empty($publishedSessions) || empty($members)): ?>
                    <p class="text-sm mt-2">
                        <?= empty($publishedSessions) ? 'Publish sessions first' : 'Add members to groups first' ?>
                    </p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

<?php else: ?>
    <!-- No Active Semester -->
    <div class="bg-gray-50 border border-gray-200 rounded-lg p-8 text-center">
        <i class="fas fa-calendar-times text-4xl text-gray-300 mb-4"></i>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No Active Semester</h3>
        <p class="text-gray-600">Please contact an administrator to activate a semester.</p>
    </div>
<?php endif; ?>

<!-- Record Achievement Modal -->
<div id="recordAchievementModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-lg max-w-md w-full p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Record Achievement</h3>
                <button onclick="closeModal('recordAchievementModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="record_achievement">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Session*</label>
                        <select name="session_id" required onchange="updateGroupOptions()" id="sessionSelect"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            <option value="">Select a session</option>
                            <?php foreach ($publishedSessions as $session): ?>
                                <option value="<?= $session['id'] ?>">
                                    Session <?= $session['session_number'] ?> - <?= formatDate($session['session_date']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Member*</label>
                        <select name="user_id" required id="memberSelect"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            <option value="">Select a member</option>
                            <?php foreach ($members as $member): ?>
                                <option value="<?= $member['id'] ?>" data-group="<?= $member['group_id'] ?>">
                                    <?= htmlspecialchars($member['name']) ?> (<?= htmlspecialchars($member['group_name']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" name="group_id" id="groupIdInput">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Achievement Type*</label>
                        <select name="achievement_type" required onchange="toggleCustomType()" id="achievementTypeSelect"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            <?php foreach ($achievementTypes as $type): ?>
                                <option value="<?= $type ?>"><?= $type ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div id="customTypeDiv" class="hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Custom Type*</label>
                        <input type="text" name="custom_type" id="customTypeInput"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea name="description" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Points Awarded</label>
                        <input type="number" name="points_awarded" value="0" min="0" max="10"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <p class="text-xs text-gray-500 mt-1">Points will be added as bonus to the group's score</p>
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" onclick="closeModal('recordAchievementModal')" 
                            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                        Record Achievement
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-lg max-w-md w-full p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Delete Achievement</h3>
                <button onclick="closeModal('deleteModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <p class="text-gray-600 mb-6">Are you sure you want to delete this achievement? This action cannot be undone.</p>
            <form method="POST">
                <input type="hidden" name="action" value="delete_achievement">
                <input type="hidden" id="deleteAchievementId" name="id">
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeModal('deleteModal')" 
                            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                        Delete Achievement
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openModal(modalId) {
    document.getElementById(modalId).classList.remove('hidden');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

function openDeleteModal(achievementId) {
    document.getElementById('deleteAchievementId').value = achievementId;
    openModal('deleteModal');
}

function toggleCustomType() {
    const select = document.getElementById('achievementTypeSelect');
    const customDiv = document.getElementById('customTypeDiv');
    const customInput = document.getElementById('customTypeInput');
    
    if (select.value === 'Custom') {
        customDiv.classList.remove('hidden');
        customInput.required = true;
    } else {
        customDiv.classList.add('hidden');
        customInput.required = false;
        customInput.value = '';
    }
}

function updateGroupOptions() {
    const sessionId = document.getElementById('sessionSelect').value;
    const memberSelect = document.getElementById('memberSelect');
    const groupIdInput = document.getElementById('groupIdInput');
    
    // Reset member selection
    memberSelect.value = '';
    groupIdInput.value = '';
    
    // Filter members by session (all members belong to the same semester as sessions)
    // For now, we'll keep all members since they're already filtered by active semester
}

// Auto-fill group ID when member is selected
document.getElementById('memberSelect').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const groupId = selectedOption.getAttribute('data-group');
    document.getElementById('groupIdInput').value = groupId || '';
});

// Close modals when clicking outside
document.querySelectorAll('[id$="Modal"]').forEach(modal => {
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.add('hidden');
        }
    });
});
</script>

<?php
$pageContent = ob_get_clean();
require_once '../../includes/layout.php';
?>