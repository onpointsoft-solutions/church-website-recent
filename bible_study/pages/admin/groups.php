<?php
// CEFC Bible Study Management System
// File: pages/admin/groups.php
// Description: Group management page for admin

require_once '../../config/config.php';
require_once '../../config/db.php';
require_once '../../includes/functions.php';
requireLogin();
requireRole(['admin']);

// Handle AJAX request for group members
if (isset($_GET['action']) && $_GET['action'] === 'get_group_members') {
    header('Content-Type: application/json');
    $groupId = (int)($_GET['group_id'] ?? 0);
    if ($groupId > 0) {
        $stmt = $pdo->prepare("
            SELECT id, name, email, role 
            FROM bs_users 
            WHERE group_id = ? AND status = 'active'
            ORDER BY name ASC
        ");
        $stmt->execute([$groupId]);
        $members = $stmt->fetchAll();
        echo json_encode(['success' => true, 'members' => $members]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid group ID']);
    }
    exit;
}

// Handle AJAX request for available members (for leader assignment)
if (isset($_GET['action']) && $_GET['action'] === 'get_available_members') {
    header('Content-Type: application/json');
    $groupId = (int)($_GET['group_id'] ?? 0);
    
    if ($groupId > 0) {
        // Get the semester_id for this group
        $stmt = $pdo->prepare("SELECT semester_id FROM bs_groups WHERE id = ?");
        $stmt->execute([$groupId]);
        $group = $stmt->fetch();
        
        if ($group) {
            // Get all active members who can be leaders (not already leaders of other groups)
            $stmt = $pdo->prepare("
                SELECT u.* 
                FROM bs_users u 
                WHERE u.role IN ('member', 'leader') 
                AND u.status = 'active'
                AND u.id NOT IN (
                    SELECT g.leader_id FROM bs_groups g WHERE g.leader_id IS NOT NULL AND g.id != ?
                )
                ORDER BY u.name ASC
            ");
            $stmt->execute([$groupId]);
            $members = $stmt->fetchAll();
            echo json_encode(['success' => true, 'members' => $members]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Group not found']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid group ID']);
    }
    exit;
}

// Handle AJAX request for unassigned members
if (isset($_GET['action']) && $_GET['action'] === 'get_unassigned_members') {
    header('Content-Type: application/json');
    $groupId = (int)($_GET['group_id'] ?? 0);
    
    if ($groupId > 0) {
        // Get the semester_id for this group
        $stmt = $pdo->prepare("SELECT semester_id FROM bs_groups WHERE id = ?");
        $stmt->execute([$groupId]);
        $group = $stmt->fetch();
        
        if ($group) {
            $unassignedMembers = getUnassignedMembers($pdo, $group['semester_id']);
            echo json_encode(['success' => true, 'members' => $unassignedMembers]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Group not found']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid group ID']);
    }
    exit;
}

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'create_group':
            $name        = trim($_POST['name'] ?? '');
            $semester_id = (int)($_POST['semester_id'] ?? 0);
            $leader_id   = !empty($_POST['leader_id']) ? (int)$_POST['leader_id'] : null;
            $status      = $_POST['status'] ?? 'active';

            if (empty($name) || empty($semester_id)) {
                $_SESSION['bs_flash'] = [
                    'type'    => 'error',
                    'message' => 'Group name and semester are required.'
                ];
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO bs_groups (name, semester_id, leader_id, status)
                    VALUES (?, ?, ?, ?)
                ");
                $ok = $stmt->execute([$name, $semester_id, $leader_id, $status]);
                $_SESSION['bs_flash'] = $ok
                    ? ['type' => 'success', 'message' => 'Group created successfully.']
                    : ['type' => 'error',   'message' => 'Failed to create group.'];
            }
            break;

        case 'update_group':
            $id          = (int)($_POST['group_id'] ?? 0);
            $name        = trim($_POST['name'] ?? '');
            $semester_id = (int)($_POST['semester_id'] ?? 0);
            $leader_id   = !empty($_POST['leader_id']) ? (int)$_POST['leader_id'] : null;
            $status      = $_POST['status'] ?? 'active';

            if (empty($id) || empty($name)) {
                $_SESSION['bs_flash'] = [
                    'type'    => 'error',
                    'message' => 'Group ID and name are required.'
                ];
            } else {
                $stmt = $pdo->prepare("
                    UPDATE bs_groups 
                    SET name=?, semester_id=?, leader_id=?, status=?
                    WHERE id=?
                ");
                $ok = $stmt->execute([$name, $semester_id, $leader_id, $status, $id]);
                $_SESSION['bs_flash'] = $ok
                    ? ['type' => 'success', 'message' => 'Group updated successfully.']
                    : ['type' => 'error',   'message' => 'Failed to update group.'];
            }
            break;

        case 'delete_group':
            $id = (int)($_POST['group_id'] ?? 0);
            if (empty($id)) {
                $_SESSION['bs_flash'] = [
                    'type'    => 'error',
                    'message' => 'Group ID is required.'
                ];
            } else {
                $chk = $pdo->prepare("SELECT COUNT(*) FROM bs_users WHERE group_id = ?");
                $chk->execute([$id]);
                $memberCount = (int)$chk->fetchColumn();

                if ($memberCount > 0) {
                    $_SESSION['bs_flash'] = [
                        'type'    => 'error',
                        'message' => 'Cannot delete a group that still has members.'
                    ];
                } else {
                    $stmt = $pdo->prepare("DELETE FROM bs_groups WHERE id = ?");
                    $ok   = $stmt->execute([$id]);
                    $_SESSION['bs_flash'] = $ok
                        ? ['type' => 'success', 'message' => 'Group deleted successfully.']
                        : ['type' => 'error',   'message' => 'Failed to delete group.'];
                }
            }
            break;

        case 'assign_leader':
            $group_id  = (int)($_POST['group_id'] ?? 0);
            $leader_id = !empty($_POST['leader_id']) ? (int)$_POST['leader_id'] : null;

            if (empty($group_id)) {
                $_SESSION['bs_flash'] = [
                    'type'    => 'error',
                    'message' => 'Group ID is required.'
                ];
            } elseif ($leader_id) {
                if (assignGroupLeader($pdo, $group_id, $leader_id)) {
                    $_SESSION['bs_flash'] = [
                        'type'    => 'success',
                        'message' => 'Group leader assigned successfully.'
                    ];
                } else {
                    $_SESSION['bs_flash'] = [
                        'type'    => 'error',
                        'message' => 'Failed to assign group leader.'
                    ];
                }
            } else {
                $_SESSION['bs_flash'] = [
                    'type'    => 'error',
                    'message' => 'Please select a leader.'
                ];
            }
            break;
    }

    header('Location: groups.php');
    exit;
}

// Fetch all groups with related data
$groups = $pdo->query("
    SELECT g.*,
           u.name  AS leader_name,
           s.name  AS semester_name,
           (SELECT COUNT(*) FROM bs_users WHERE group_id = g.id) AS member_count
    FROM bs_groups g
    LEFT JOIN bs_users     u ON g.leader_id  = u.id
    LEFT JOIN bs_semesters s ON g.semester_id = s.id
    ORDER BY g.name ASC
")->fetchAll();

// Fetch semesters for dropdowns
$semesters = $pdo->query("
    SELECT id, name FROM bs_semesters ORDER BY created_at DESC
")->fetchAll();

$pageTitle  = 'Group Management';
$activePage = 'groups';
ob_start();
?>

<!-- Flash Message -->
<?php if (!empty($_SESSION['bs_flash'])): ?>
    <div class="mb-6 p-4 rounded-lg flex items-center justify-between
        <?= $_SESSION['bs_flash']['type'] === 'success'
            ? 'bg-green-50 border border-green-200 text-green-800'
            : 'bg-red-50 border border-red-200 text-red-800' ?>">
        <div class="flex items-center gap-2">
            <i class="fa-solid fa-<?= $_SESSION['bs_flash']['type'] === 'success'
                ? 'circle-check' : 'circle-xmark' ?>"></i>
            <span><?= htmlspecialchars($_SESSION['bs_flash']['message']) ?></span>
        </div>
        <button onclick="this.parentElement.remove()">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>
    <?php unset($_SESSION['bs_flash']); ?>
<?php endif; ?>

<!-- Page Header -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-purple-900">
            <i class="fa-solid fa-layer-group mr-2 text-amber-500"></i>
            Group Management
        </h1>
        <p class="text-gray-500 text-sm mt-1">
            Create and manage Bible Study groups
        </p>
    </div>
    <button onclick="openModal('addGroupModal')"
            class="bg-purple-700 hover:bg-purple-800 text-white 
                   px-4 py-2 rounded-lg font-medium transition-colors">
        <i class="fa-solid fa-plus mr-2"></i>Add New Group
    </button>
</div>

<!-- Groups Table -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">#</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Group Name</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Semester</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Leader</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Members</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Points</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Status</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($groups)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-12 text-gray-400">
                            <i class="fa-solid fa-layer-group text-4xl mb-3 block"></i>
                            No groups found. Add your first group above.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($groups as $i => $group): ?>
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-400"><?= $i + 1 ?></td>
                            <td class="px-4 py-3 font-semibold text-gray-800">
                                <?= htmlspecialchars($group['name']) ?>
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                <?= htmlspecialchars($group['semester_name'] ?? 'N/A') ?>
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                <?php if ($group['leader_name']): ?>
                                    <span class="flex items-center gap-1">
                                        <i class="fa-solid fa-user-tie text-purple-500 text-xs"></i>
                                        <?= htmlspecialchars($group['leader_name']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-gray-400 italic text-xs">Not assigned</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3">
                                <span class="bg-blue-100 text-blue-700 text-xs 
                                             font-medium px-2 py-1 rounded-full">
                                    <?= $group['member_count'] ?> members
                                </span>
                            </td>
                            <td class="px-4 py-3 font-semibold text-purple-700">
                                <?= number_format($group['total_points']) ?>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-xs font-medium px-2 py-1 rounded-full
                                    <?= $group['status'] === 'active'
                                        ? 'bg-green-100 text-green-700'
                                        : 'bg-gray-100 text-gray-500' ?>">
                                    <?= ucfirst($group['status']) ?>
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <button title="Edit Group"
                                        onclick='openEditModal(<?= json_encode($group) ?>)'
                                        class="text-purple-600 hover:text-purple-900 
                                               p-1 rounded hover:bg-purple-50">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <button title="Assign Leader"
                                        onclick="openAssignLeaderModal(
                                            <?= $group['id'] ?>, 
                                            '<?= htmlspecialchars($group['name'], ENT_QUOTES) ?>',
                                            <?= $group['leader_id'] ?? 'null' ?>
                                        )"
                                        class="text-blue-600 hover:text-blue-900 
                                               p-1 rounded hover:bg-blue-50">
                                        <i class="fa-solid fa-user-tie"></i>
                                    </button>
                                    <button title="Delete Group"
                                        onclick="openDeleteModal(
                                            <?= $group['id'] ?>, 
                                            '<?= htmlspecialchars($group['name'], ENT_QUOTES) ?>',
                                            <?= $group['member_count'] ?>
                                        )"
                                        class="text-red-500 hover:text-red-700 
                                               p-1 rounded hover:bg-red-50">
                                        <i class="fa-solid fa-trash"></i>
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

<!-- MODAL: Add Group -->
<div id="addGroupModal" 
     class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 
            flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md">
        <div class="flex items-center justify-between p-6 border-b">
            <h3 class="text-lg font-semibold text-purple-900">
                <i class="fa-solid fa-plus mr-2 text-amber-500"></i>
                Add New Group
            </h3>
            <button onclick="closeModal('addGroupModal')"
                    class="text-gray-400 hover:text-gray-600">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>
        <form method="POST" class="p-6 space-y-4">
            <input type="hidden" name="action" value="create_group">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Group Name <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name" required
                       placeholder="e.g. Group Alpha"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 
                              text-sm focus:outline-none focus:ring-2 
                              focus:ring-purple-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Semester <span class="text-red-500">*</span>
                </label>
                <select name="semester_id" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 
                               text-sm focus:outline-none focus:ring-2 
                               focus:ring-purple-500">
                    <option value="">-- Select Semester --</option>
                    <?php foreach ($semesters as $sem): ?>
                        <option value="<?= $sem['id'] ?>">
                            <?= htmlspecialchars($sem['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 
                               text-sm focus:outline-none focus:ring-2 
                               focus:ring-purple-500">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <p class="text-xs text-gray-400 bg-blue-50 p-3 rounded-lg">
                <i class="fa-solid fa-circle-info mr-1 text-blue-400"></i>
                After creating the group, use the leader button to assign a group leader.
                The leader will then manage their own members from their dashboard.
            </p>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closeModal('addGroupModal')"
                        class="px-4 py-2 text-gray-600 bg-gray-100 
                               rounded-lg hover:bg-gray-200 text-sm">
                    Cancel
                </button>
                <button type="submit"
                        class="px-4 py-2 bg-purple-700 text-white 
                               rounded-lg hover:bg-purple-800 text-sm font-medium">
                    <i class="fa-solid fa-save mr-1"></i>Save Group
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: Edit Group -->
<div id="editGroupModal"
     class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 
            flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md">
        <div class="flex items-center justify-between p-6 border-b">
            <h3 class="text-lg font-semibold text-purple-900">
                <i class="fa-solid fa-pen-to-square mr-2 text-amber-500"></i>
                Edit Group
            </h3>
            <button onclick="closeModal('editGroupModal')"
                    class="text-gray-400 hover:text-gray-600">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>
        <form method="POST" class="p-6 space-y-4">
            <input type="hidden" name="action" value="update_group">
            <input type="hidden" name="group_id" id="editGroupId">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Group Name <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name" id="editGroupName" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 
                              text-sm focus:outline-none focus:ring-2 
                              focus:ring-purple-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Semester <span class="text-red-500">*</span>
                </label>
                <select name="semester_id" id="editGroupSemester" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 
                               text-sm focus:outline-none focus:ring-2 
                               focus:ring-purple-500">
                    <option value="">-- Select Semester --</option>
                    <?php foreach ($semesters as $sem): ?>
                        <option value="<?= $sem['id'] ?>">
                            <?= htmlspecialchars($sem['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" id="editGroupStatus"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 
                               text-sm focus:outline-none focus:ring-2 
                               focus:ring-purple-500">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closeModal('editGroupModal')"
                        class="px-4 py-2 text-gray-600 bg-gray-100 
                               rounded-lg hover:bg-gray-200 text-sm">
                    Cancel
                </button>
                <button type="submit"
                        class="px-4 py-2 bg-purple-700 text-white 
                               rounded-lg hover:bg-purple-800 text-sm font-medium">
                    <i class="fa-solid fa-save mr-1"></i>Update Group
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: Assign Leader -->
<div id="assignLeaderModal"
     class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 
            flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md">
        <div class="flex items-center justify-between p-6 border-b">
            <h3 class="text-lg font-semibold text-purple-900">
                <i class="fa-solid fa-user-tie mr-2 text-blue-500"></i>
                Assign Group Leader
            </h3>
            <button onclick="closeModal('assignLeaderModal')"
                    class="text-gray-400 hover:text-gray-600">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>
        <form method="POST" class="p-6 space-y-4">
            <input type="hidden" name="action" value="assign_leader">
            <input type="hidden" name="group_id" id="assignGroupId">
            <p class="text-sm text-gray-600">
                Assigning leader for group:
                <strong id="assignGroupName" class="text-purple-800"></strong>
            </p>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Select Leader from Available Members
                </label>
                <select name="leader_id" id="assignLeaderSelect"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 
                               text-sm focus:outline-none focus:ring-2 
                               focus:ring-purple-500">
                    <option value="">Loading members...</option>
                </select>
                <p class="text-xs text-gray-400 mt-1">
                    Select any available member to become the leader of this group.
                    The selected member will be automatically assigned to this group.
                </p>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closeModal('assignLeaderModal')"
                        class="px-4 py-2 text-gray-600 bg-gray-100 
                               rounded-lg hover:bg-gray-200 text-sm">
                    Cancel
                </button>
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white 
                               rounded-lg hover:bg-blue-700 text-sm font-medium">
                    <i class="fa-solid fa-user-check mr-1"></i>Assign Leader
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: Delete Confirmation -->
<div id="deleteGroupModal"
     class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 
            flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-sm">
        <div class="p-6 text-center">
            <div class="bg-red-100 rounded-full w-16 h-16 flex items-center 
                        justify-center mx-auto mb-4">
                <i class="fa-solid fa-trash text-red-500 text-2xl"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Delete Group</h3>
            <p class="text-gray-500 text-sm mb-2">
                Are you sure you want to delete
                <strong id="deleteGroupName" class="text-gray-800"></strong>?
            </p>
            <p id="deleteGroupWarning"
               class="text-red-600 text-xs bg-red-50 p-2 rounded-lg mb-4 hidden">
            </p>
        </div>
        <form method="POST" class="px-6 pb-6">
            <input type="hidden" name="action" value="delete_group">
            <input type="hidden" name="group_id" id="deleteGroupId">
            <div class="flex justify-center gap-3">
                <button type="button" onclick="closeModal('deleteGroupModal')"
                        class="px-4 py-2 text-gray-600 bg-gray-100 
                               rounded-lg hover:bg-gray-200 text-sm">
                    Cancel
                </button>
                <button type="submit" id="deleteGroupBtn"
                        class="px-4 py-2 bg-red-600 text-white 
                               rounded-lg hover:bg-red-700 text-sm font-medium">
                    <i class="fa-solid fa-trash mr-1"></i>Delete
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id) {
    document.getElementById(id).classList.remove('hidden');
}

function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
}

// Close on backdrop click
document.querySelectorAll('[id$="Modal"]').forEach(modal => {
    modal.addEventListener('click', function(e) {
        if (e.target === modal) closeModal(modal.id);
    });
});

// Edit Group Modal
function openEditModal(group) {
    document.getElementById('editGroupId').value       = group.id;
    document.getElementById('editGroupName').value     = group.name;
    document.getElementById('editGroupSemester').value = group.semester_id;
    document.getElementById('editGroupStatus').value   = group.status;
    openModal('editGroupModal');
}

// Assign Leader Modal
function openAssignLeaderModal(groupId, groupName, currentLeaderId) {
    document.getElementById('assignGroupId').value         = groupId;
    document.getElementById('assignGroupName').textContent = groupName;

    const select = document.getElementById('assignLeaderSelect');
    select.innerHTML = '<option value="">Loading members...</option>';
    openModal('assignLeaderModal');

    fetch('groups.php?action=get_available_members&group_id=' + groupId)
        .then(r => r.json())
        .then(data => {
            select.innerHTML = '<option value="">-- Select a Leader --</option>';
            if (data.success && data.members.length > 0) {
                data.members.forEach(m => {
                    const opt       = document.createElement('option');
                    opt.value       = m.id;
                    opt.textContent = m.name + ' (' + m.email + ')';
                    if (m.id == currentLeaderId) opt.selected = true;
                    select.appendChild(opt);
                });
            } else {
                const opt       = document.createElement('option');
                opt.disabled    = true;
                opt.textContent = 'No available members found.';
                select.appendChild(opt);
            }
        })
        .catch(() => {
            select.innerHTML =
                '<option disabled>Failed to load members. Please try again.</option>';
        });
}

// Delete Group Modal
function openDeleteModal(groupId, groupName, memberCount) {
    document.getElementById('deleteGroupId').value         = groupId;
    document.getElementById('deleteGroupName').textContent = groupName;

    const warning = document.getElementById('deleteGroupWarning');
    const btn     = document.getElementById('deleteGroupBtn');

    if (memberCount > 0) {
        warning.textContent =
            'This group has ' + memberCount +
            ' member(s). Please remove all members before deleting.';
        warning.classList.remove('hidden');
        btn.disabled = true;
        btn.classList.add('opacity-50', 'cursor-not-allowed');
    } else {
        warning.classList.add('hidden');
        btn.disabled = false;
        btn.classList.remove('opacity-50', 'cursor-not-allowed');
    }

    openModal('deleteGroupModal');
}
</script>

<?php
$pageContent = ob_get_clean();
require_once '../../includes/layout.php';