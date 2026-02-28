<?php
// CEFC Bible Study Management System
// File: pages/admin/users.php
// Description: Full user management page

require_once '../../config/config.php';
require_once '../../config/db.php';
require_once '../../includes/functions.php';
requireLogin();
requireRole(['admin']);

$pageTitle = 'User Management';
$activePage = 'users';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create_user':
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            $role = $_POST['role'] ?? '';
            $age_group = $_POST['age_group'] ?? '';
            $group_id = !empty($_POST['group_id']) ? (int)$_POST['group_id'] : null;
            $status = $_POST['status'] ?? 'active';
            
            if (empty($name) || empty($email) || empty($password) || empty($role)) {
                $_SESSION['bs_flash'] = ['type' => 'error', 'message' => 'Name, email, password, and role are required'];
            } elseif ($password !== $confirm_password) {
                $_SESSION['bs_flash'] = ['type' => 'error', 'message' => 'Passwords do not match'];
            } elseif (strlen($password) < 6) {
                $_SESSION['bs_flash'] = ['type' => 'error', 'message' => 'Password must be at least 6 characters'];
            } else {
                $stmt = $pdo->prepare("SELECT id FROM bs_users WHERE email = ?");
                $stmt->execute([$email]);
                $existing = $stmt->fetch();
                if ($existing) {
                    $_SESSION['bs_flash'] = ['type' => 'error', 'message' => 'Email already registered'];
                } else {
                    $data = [
                        'name' => $name,
                        'email' => $email,
                        'phone' => $phone,
                        'password' => $password,
                        'role' => $role,
                        'age_group' => $age_group,
                        'group_id' => $group_id,
                        'status' => $status
                    ];
                    if (createUser($pdo, $data)) {
                        $_SESSION['bs_flash'] = ['type' => 'success', 'message' => 'User created successfully'];
                    } else {
                        $_SESSION['bs_flash'] = ['type' => 'error', 'message' => 'Failed to create user'];
                    }
                }
            }
            break;
            
        case 'update_user':
            $id = (int)($_POST['user_id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $password = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? '';
            $age_group = $_POST['age_group'] ?? '';
            $group_id = !empty($_POST['group_id']) ? (int)$_POST['group_id'] : null;
            $status = $_POST['status'] ?? 'active';
            
            if (empty($id) || empty($name) || empty($email) || empty($role)) {
                $_SESSION['bs_flash'] = ['type' => 'error', 'message' => 'ID, name, email, and role are required'];
            } else {
                $data = [
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'role' => $role,
                    'age_group' => $age_group,
                    'group_id' => $group_id,
                    'status' => $status
                ];
                
                if (!empty($password)) {
                    if (strlen($password) < 6) {
                        $_SESSION['bs_flash'] = ['type' => 'error', 'message' => 'Password must be at least 6 characters'];
                    } else {
                        $data['password'] = $password;
                    }
                }
                
                if (updateUser($pdo, $id, $data)) {
                    $_SESSION['bs_flash'] = ['type' => 'success', 'message' => 'User updated successfully'];
                } else {
                    $_SESSION['bs_flash'] = ['type' => 'error', 'message' => 'Failed to update user'];
                }
            }
            break;
            
        case 'delete_user':
            $id = (int)($_POST['user_id'] ?? 0);
            if (empty($id)) {
                $_SESSION['bs_flash'] = ['type' => 'error', 'message' => 'User ID is required'];
            } elseif ($id == $_SESSION['bs_user_id']) {
                $_SESSION['bs_flash'] = ['type' => 'error', 'message' => 'Cannot delete your own account'];
            } else {
                if (deleteUser($pdo, $id)) {
                    $_SESSION['bs_flash'] = ['type' => 'success', 'message' => 'User deleted successfully'];
                } else {
                    $_SESSION['bs_flash'] = ['type' => 'error', 'message' => 'Failed to delete user'];
                }
            }
            break;
            
        case 'toggle_status':
            $id = (int)($_POST['user_id'] ?? 0);
            $user = getUserById($pdo, $id);
            if ($user) {
                $newStatus = $user['status'] === 'active' ? 'inactive' : 'active';
                if (updateUser($pdo, $id, ['status' => $newStatus])) {
                    $_SESSION['bs_flash'] = ['type' => 'success', 'message' => 'User status updated'];
                } else {
                    $_SESSION['bs_flash'] = ['type' => 'error', 'message' => 'Failed to update status'];
                }
            }
            break;
    }
    
    header('Location: users.php');
    exit;
}

// Fetch data
$roleFilter = $_GET['role'] ?? '';
$whereClause = '';
$params = [];
if (!empty($roleFilter)) {
    $whereClause = 'WHERE u.role = ?';
    $params[] = $roleFilter;
}

$sql = "
    SELECT u.*, g.name as group_name 
    FROM bs_users u 
    LEFT JOIN bs_groups g ON u.group_id = g.id 
    $whereClause
    ORDER BY u.name ASC
";

if (!empty($params)) {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $users = $stmt->fetchAll();
} else {
    $users = $pdo->query($sql)->fetchAll();
}

$groups = getAllGroups($pdo);
$roles = ['admin', 'coordinator', 'leader', 'member'];
$ageGroups = ['youth', 'young_adult', 'adult', 'senior'];

// Determine which columns to show based on current user role
$currentUserRole = $_SESSION['bs_user_role'];
$showGroupColumn = in_array($currentUserRole, ['admin']); // Only admin sees group column
$showAgeGroupColumn = in_array($currentUserRole, ['admin']); // Only admin sees age group column
$showPhoneColumn = in_array($currentUserRole, ['admin', 'coordinator']); // Admin and coordinator see phone
$showActions = in_array($currentUserRole, ['admin']); // Only admin can edit/delete users
$colspanCount = 4; // Base columns: #, Name, Email, Role
if ($showPhoneColumn) $colspanCount++;
if ($showAgeGroupColumn) $colspanCount++;
if ($showGroupColumn) $colspanCount++;
if ($showActions) $colspanCount++; // Status column always shown

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

<!-- OTP Codes Display (for development when email fails) -->
<?php if (isset($_SESSION['admin_otp_log']) && !empty($_SESSION['admin_otp_log'])): ?>
    <div class="mb-6">
        <div class="bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded-lg">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <span class="font-semibold">Email System Not Configured - OTP Codes Below</span>
                </div>
                <button onclick="this.parentElement.parentElement.remove()" class="text-amber-600 hover:text-amber-800">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <p class="text-sm mb-3">The email system is not working on this server. Please share these OTP codes with the users:</p>
            <div class="space-y-2">
                <?php foreach ($_SESSION['admin_otp_log'] as $otpInfo): ?>
                    <div class="bg-white p-2 rounded border border-amber-300">
                        <div class="flex justify-between items-center">
                            <div>
                                <strong><?= htmlspecialchars($otpInfo['name']) ?></strong> 
                                <span class="text-amber-600">(<?= htmlspecialchars($otpInfo['email']) ?>)</span>
                            </div>
                            <div class="text-right">
                                <div class="font-mono font-bold text-lg text-amber-700"><?= $otpInfo['otp'] ?></div>
                                <div class="text-xs text-gray-500"><?= $otpInfo['time'] ?></div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <p class="text-xs mt-3 text-amber-700">
                <i class="fas fa-info-circle mr-1"></i>
                These codes will be removed when you refresh the page. Copy them now.
            </p>
        </div>
    </div>
    <?php unset($_SESSION['admin_otp_log']); ?>
<?php endif; ?>

<!-- Page Header -->
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900">User Management</h1>
</div>

<!-- Search and Filters -->
<div class="bg-white rounded-lg shadow p-4 mb-6">
    <div class="flex flex-col md:flex-row gap-4">
        <div class="flex-1">
            <input type="text" id="searchInput" placeholder="Search by name or email..." 
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
        </div>
        <div class="md:w-48">
            <select id="roleFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                <option value="">All Roles</option>
                <?php foreach ($roles as $role): ?>
                    <option value="<?= $role ?>" <?= $roleFilter === $role ? 'selected' : '' ?>>
                        <?= ucfirst($role) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php if ($showActions): ?>
            <button onclick="openAddUserModal()" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                <i class="fas fa-plus mr-2"></i>Add New User
            </button>
        <?php endif; ?>
    </div>
</div>

<!-- Users Table -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="table-container">
        <table class="min-w-full divide-y divide-gray-200" id="usersTable">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <?php if ($showPhoneColumn): ?>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                    <?php endif; ?>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                    <?php if ($showAgeGroupColumn): ?>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Age Group</th>
                    <?php endif; ?>
                    <?php if ($showGroupColumn): ?>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Group</th>
                    <?php endif; ?>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <?php if ($showActions): ?>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="<?= $colspanCount ?>" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-users text-4xl text-gray-300 mb-2"></i>
                            <p>No users found. Add your first user above.</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $index => $user): ?>
                        <tr class="hover:bg-gray-50" data-name="<?= htmlspecialchars(strtolower($user['name'])) ?>" 
                            data-email="<?= htmlspecialchars(strtolower($user['email'])) ?>" data-role="<?= $user['role'] ?>">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $index + 1 ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <?= htmlspecialchars($user['name']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= htmlspecialchars($user['email']) ?>
                            </td>
                            <?php if ($showPhoneColumn): ?>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= htmlspecialchars($user['phone'] ?? 'N/A') ?>
                                </td>
                            <?php endif; ?>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs rounded-full <?= getRoleBadgeColor($user['role']) ?>">
                                    <?= htmlspecialchars(ucfirst($user['role'])) ?>
                                </span>
                            </td>
                            <?php if ($showAgeGroupColumn): ?>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= htmlspecialchars(ucfirst($user['age_group'])) ?>
                                </td>
                            <?php endif; ?>
                            <?php if ($showGroupColumn): ?>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= htmlspecialchars($user['group_name'] ?? 'Not assigned') ?>
                                </td>
                            <?php endif; ?>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs rounded-full <?= $user['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' ?>">
                                    <?= htmlspecialchars(ucfirst($user['status'])) ?>
                                </span>
                            </td>
                            <?php if ($showActions): ?>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button onclick="openEditUserModal(<?= htmlspecialchars(json_encode($user)) ?>)" 
                                            class="text-purple-600 hover:text-purple-900 mr-3">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="openDeleteModal(<?= $user['id'] ?>, '<?= htmlspecialchars($user['name']) ?>')" 
                                            class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add User Modal -->
<div id="addUserModal" class="modal-overlay hidden">
    <div class="modal-content modal-md">
        <div class="modal-header">
            <h3 class="text-lg font-semibold text-gray-900">Add New User</h3>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="create_user">
            <div class="modal-body">
                <div class="form-scrollable space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                        <input type="text" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                        <input type="email" name="email" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Role *</label>
                        <select id="roleSelect" name="role" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" onchange="toggleRoleFields()">
                            <option value="">Select Role</option>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= $role ?>"><?= ucfirst($role) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div id="phoneField" class="role-field" data-roles="admin,coordinator">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                        <input type="tel" name="phone" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    </div>
                    <div id="ageGroupField" class="role-field" data-roles="admin,member">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Age Group</label>
                        <select name="age_group" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            <option value="">Select Age Group</option>
                            <?php foreach ($ageGroups as $ageGroup): ?>
                                <option value="<?= $ageGroup ?>"><?= ucfirst($ageGroup) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div id="groupField" class="role-field" data-roles="admin,leader,member">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Group Assignment</label>
                        <select name="group_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            <option value="">No Group</option>
                            <?php foreach ($groups as $group): ?>
                                <option value="<?= $group['id'] ?>"><?= htmlspecialchars($group['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password *</label>
                        <input type="password" name="password" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password *</label>
                        <input type="password" name="confirm_password" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeModal('addUserModal')" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                        Save User
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Edit User Modal -->
<div id="editUserModal" class="modal-overlay hidden">
    <div class="modal-content modal-md">
        <div class="modal-header">
            <h3 class="text-lg font-semibold text-gray-900">Edit User</h3>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="update_user">
            <input type="hidden" id="editUserId" name="user_id">
            <div class="modal-body">
                <div class="form-scrollable space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                        <input type="text" id="editName" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                        <input type="email" id="editEmail" name="email" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Role *</label>
                        <select id="editRoleSelect" name="role" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" onchange="toggleEditRoleFields()">
                            <option value="">Select Role</option>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= $role ?>"><?= ucfirst($role) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div id="editPhoneField" class="role-field" data-roles="admin,coordinator">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                        <input type="tel" id="editPhone" name="phone" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    </div>
                    <div id="editAgeGroupField" class="role-field" data-roles="admin,member">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Age Group</label>
                        <select id="editAgeGroup" name="age_group" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            <option value="">Select Age Group</option>
                            <?php foreach ($ageGroups as $ageGroup): ?>
                                <option value="<?= $ageGroup ?>"><?= ucfirst($ageGroup) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div id="editGroupField" class="role-field" data-roles="admin,leader,member">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Group Assignment</label>
                        <select id="editGroupId" name="group_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            <option value="">No Group</option>
                            <?php foreach ($groups as $group): ?>
                                <option value="<?= $group['id'] ?>"><?= htmlspecialchars($group['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <input type="password" id="editPassword" name="password" placeholder="Leave blank to keep current" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select id="editStatus" name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeModal('editUserModal')" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                        Update User
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal-overlay hidden">
    <div class="modal-content modal-sm">
        <div class="modal-header">
            <h3 class="text-lg font-semibold text-gray-900">Delete User</h3>
        </div>
        <div class="modal-body">
            <div class="text-center">
                <i class="fas fa-exclamation-triangle text-4xl text-red-500 mb-4"></i>
                <p class="text-gray-600">Are you sure you want to delete <span id="deleteUserName" class="font-semibold"></span>?</p>
            </div>
        </div>
        <div class="modal-footer">
            <form method="POST">
                <input type="hidden" name="action" value="delete_user">
                <input type="hidden" id="deleteUserId" name="user_id">
                <div class="flex justify-center space-x-3">
                    <button type="button" onclick="closeModal('deleteModal')" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                        Delete
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openAddUserModal() {
    const modal = document.getElementById('addUserModal');
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function openEditUserModal(user) {
    document.getElementById('editUserId').value = user.id;
    document.getElementById('editName').value = user.name;
    document.getElementById('editEmail').value = user.email;
    document.getElementById('editPhone').value = user.phone || '';
    document.getElementById('editRoleSelect').value = user.role;
    document.getElementById('editAgeGroup').value = user.age_group;
    document.getElementById('editGroupId').value = user.group_id || '';
    document.getElementById('editStatus').value = user.status;
    document.getElementById('editPassword').value = '';
    
    // Toggle fields based on selected role
    toggleEditRoleFields();
    
    const modal = document.getElementById('editUserModal');
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function toggleRoleFields() {
    const selectedRole = document.getElementById('roleSelect').value;
    const roleFields = document.querySelectorAll('#addUserModal .role-field');
    
    roleFields.forEach(field => {
        const allowedRoles = field.dataset.roles.split(',');
        if (allowedRoles.includes(selectedRole)) {
            field.style.display = 'block';
        } else {
            field.style.display = 'none';
            // Clear the field values when hidden
            const inputs = field.querySelectorAll('input, select');
            inputs.forEach(input => input.value = '');
        }
    });
}

function toggleEditRoleFields() {
    const selectedRole = document.getElementById('editRoleSelect').value;
    const roleFields = document.querySelectorAll('#editUserModal .role-field');
    
    roleFields.forEach(field => {
        const allowedRoles = field.dataset.roles.split(',');
        if (allowedRoles.includes(selectedRole)) {
            field.style.display = 'block';
        } else {
            field.style.display = 'none';
        }
    });
}

function openDeleteModal(userId, userName) {
    document.getElementById('deleteUserId').value = userId;
    document.getElementById('deleteUserName').textContent = userName;
    const modal = document.getElementById('deleteModal');
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Close modals on backdrop click
document.querySelectorAll('.modal-overlay').forEach(modal => {
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeModal(modal.id);
        }
    });
});

// Close modals on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay:not(.hidden)').forEach(modal => {
            closeModal(modal.id);
        });
    }
});

// Search functionality
document.getElementById('searchInput').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('#usersTable tbody tr');
    
    rows.forEach(row => {
        const name = row.dataset.name || '';
        const email = row.dataset.email || '';
        const matches = name.includes(searchTerm) || email.includes(searchTerm);
        row.style.display = matches ? '' : 'none';
    });
});

// Role filter
document.getElementById('roleFilter').addEventListener('change', function(e) {
    const role = e.target.value;
    const rows = document.querySelectorAll('#usersTable tbody tr');
    
    rows.forEach(row => {
        const rowRole = row.dataset.role || '';
        const matches = !role || rowRole === role;
        row.style.display = matches ? '' : 'none';
    });
});
</script>

<?php
$pageContent = ob_get_clean();
require_once '../../includes/layout.php';
?>