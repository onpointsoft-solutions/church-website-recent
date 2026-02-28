<?php
// CEFC Bible Study Management System
// File: pages/admin/semesters.php
// Description: Semester management page

require_once '../../config/config.php';
require_once '../../config/db.php';
require_once '../../includes/functions.php';
requireLogin();
requireRole(['admin']);

$pageTitle = 'Semester Management';
$activePage = 'semesters';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create_semester':
            $name = trim($_POST['name'] ?? '');
            $start_date = $_POST['start_date'] ?? '';
            $end_date = $_POST['end_date'] ?? '';
            
            if (empty($name) || empty($start_date) || empty($end_date)) {
                $_SESSION['bs_flash'] = ['type' => 'error', 'message' => 'Name, start date, and end date are required'];
            } elseif (strtotime($start_date) >= strtotime($end_date)) {
                $_SESSION['bs_flash'] = ['type' => 'error', 'message' => 'Start date must be before end date'];
            } else {
                $data = [
                    'name' => $name,
                    'start_date' => $start_date,
                    'end_date' => $end_date,
                    'status' => 'upcoming',
                    'created_by' => $_SESSION['bs_user_id']
                ];
                if (createSemester($pdo, $data)) {
                    $_SESSION['bs_flash'] = ['type' => 'success', 'message' => 'Semester created successfully'];
                } else {
                    $_SESSION['bs_flash'] = ['type' => 'error', 'message' => 'Failed to create semester'];
                }
            }
            break;
            
        case 'update_semester':
            $id = (int)($_POST['semester_id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $start_date = $_POST['start_date'] ?? '';
            $end_date = $_POST['end_date'] ?? '';
            
            if (empty($id) || empty($name) || empty($start_date) || empty($end_date)) {
                $_SESSION['bs_flash'] = ['type' => 'error', 'message' => 'All fields are required'];
            } elseif (strtotime($start_date) >= strtotime($end_date)) {
                $_SESSION['bs_flash'] = ['type' => 'error', 'message' => 'Start date must be before end date'];
            } else {
                $data = [
                    'name' => $name,
                    'start_date' => $start_date,
                    'end_date' => $end_date
                ];
                if (updateSemester($pdo, $id, $data)) {
                    $_SESSION['bs_flash'] = ['type' => 'success', 'message' => 'Semester updated successfully'];
                } else {
                    $_SESSION['bs_flash'] = ['type' => 'error', 'message' => 'Failed to update semester'];
                }
            }
            break;
            
        case 'activate_semester':
            $id = (int)($_POST['semester_id'] ?? 0);
            if (empty($id)) {
                $_SESSION['bs_flash'] = ['type' => 'error', 'message' => 'Semester ID is required'];
            } else {
                try {
                    $pdo->beginTransaction();
                    
                    // Complete current active semester
                    $stmt = $pdo->prepare("UPDATE bs_semesters SET status = 'completed' WHERE status = 'active'");
                    $stmt->execute();
                    
                    // Activate new semester
                    if (updateSemesterStatus($pdo, $id, 'active')) {
                        $pdo->commit();
                        $_SESSION['bs_flash'] = ['type' => 'success', 'message' => 'Semester activated. Previous semester closed.'];
                    } else {
                        $pdo->rollBack();
                        $_SESSION['bs_flash'] = ['type' => 'error', 'message' => 'Failed to activate semester'];
                    }
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $_SESSION['bs_flash'] = ['type' => 'error', 'message' => 'Failed to activate semester'];
                }
            }
            break;
            
        case 'complete_semester':
            $id = (int)($_POST['semester_id'] ?? 0);
            if (empty($id)) {
                $_SESSION['bs_flash'] = ['type' => 'error', 'message' => 'Semester ID is required'];
            } else {
                if (updateSemesterStatus($pdo, $id, 'completed')) {
                    $_SESSION['bs_flash'] = ['type' => 'success', 'message' => 'Semester completed successfully'];
                } else {
                    $_SESSION['bs_flash'] = ['type' => 'error', 'message' => 'Failed to complete semester'];
                }
            }
            break;
            
        case 'delete_semester':
            $id = (int)($_POST['semester_id'] ?? 0);
            if (empty($id)) {
                $_SESSION['bs_flash'] = ['type' => 'error', 'message' => 'Semester ID is required'];
            } else {
                $semester = $pdo->prepare("SELECT status FROM bs_semesters WHERE id = ?")->execute([$id]) ? 
                          $pdo->fetch() : null;
                
                if (!$semester) {
                    $_SESSION['bs_flash'] = ['type' => 'error', 'message' => 'Semester not found'];
                } elseif ($semester['status'] !== 'upcoming') {
                    $_SESSION['bs_flash'] = ['type' => 'error', 'message' => 'Can only delete upcoming semesters'];
                } else {
                    $sessionCount = $pdo->prepare("SELECT COUNT(*) FROM bs_sessions WHERE semester_id = ?")->execute([$id]) ? 
                                  $pdo->fetchColumn() : 0;
                    
                    if ($sessionCount > 0) {
                        $_SESSION['bs_flash'] = ['type' => 'error', 'message' => 'Cannot delete semester with sessions'];
                    } else {
                        $stmt = $pdo->prepare("DELETE FROM bs_semesters WHERE id = ?");
                        if ($stmt->execute([$id])) {
                            $_SESSION['bs_flash'] = ['type' => 'success', 'message' => 'Semester deleted successfully'];
                        } else {
                            $_SESSION['bs_flash'] = ['type' => 'error', 'message' => 'Failed to delete semester'];
                        }
                    }
                }
            }
            break;
    }
    
    header('Location: semesters.php');
    exit;
}

// Fetch data
$semesters = $pdo->query("
    SELECT s.*, 
           (SELECT COUNT(*) FROM bs_sessions WHERE semester_id = s.id) as session_count,
           (SELECT COUNT(*) FROM bs_groups WHERE semester_id = s.id) as group_count
    FROM bs_semesters s 
    ORDER BY s.created_at DESC
")->fetchAll();

$activeSemester = getActiveSemester($pdo);

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
    <h1 class="text-3xl font-bold text-gray-900">Semester Management</h1>
    <button onclick="openAddSemesterModal()" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
        <i class="fas fa-plus mr-2"></i>Create New Semester
    </button>
</div>

<!-- Active Semester Highlight -->
<?php if ($activeSemester): ?>
    <div class="bg-gradient-to-r from-amber-50 to-yellow-50 border-2 border-amber-300 rounded-lg p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center mb-2">
                    <i class="fas fa-star text-amber-500 text-xl mr-2"></i>
                    <h3 class="text-lg font-semibold text-gray-900">Active Semester</h3>
                </div>
                <p class="text-xl font-bold text-gray-900"><?= htmlspecialchars($activeSemester['name']) ?></p>
                <p class="text-gray-600">
                    <?= formatDate($activeSemester['start_date']) ?> - <?= formatDate($activeSemester['end_date']) ?>
                </p>
                <?php
                $daysLeft = ceil((strtotime($activeSemester['end_date']) - time()) / (60 * 60 * 24));
                if ($daysLeft > 0) {
                    echo '<p class="text-sm text-amber-600 mt-1">' . $daysLeft . ' days remaining</p>';
                } else {
                    echo '<p class="text-sm text-red-600 mt-1">Ended</p>';
                }
                ?>
            </div>
            <div class="text-right">
                <div class="text-3xl font-bold text-amber-600"><?= $activeSemester['status'] ?></div>
                <div class="text-sm text-gray-600">Status</div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Semesters Table -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">End Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sessions</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Groups</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($semesters)): ?>
                    <tr>
                        <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-calendar text-4xl text-gray-300 mb-2"></i>
                            <p>No semesters found. Create your first semester above.</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($semesters as $index => $semester): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $index + 1 ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <?= htmlspecialchars($semester['name']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= formatDate($semester['start_date']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= formatDate($semester['end_date']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= number_format($semester['session_count']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= number_format($semester['group_count']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs rounded-full 
                                    <?= $semester['status'] === 'upcoming' ? 'bg-gray-100 text-gray-800' : 
                                       ($semester['status'] === 'active' ? 'bg-green-100 text-green-800' : 
                                       'bg-purple-100 text-purple-800') ?>">
                                    <?= htmlspecialchars(ucfirst($semester['status'])) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button onclick="openEditSemesterModal(<?= htmlspecialchars(json_encode($semester)) ?>)" 
                                        class="text-purple-600 hover:text-purple-900 mr-3">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <?php if ($semester['status'] === 'upcoming'): ?>
                                    <button onclick="openActivateModal(<?= $semester['id'] ?>, '<?= htmlspecialchars($semester['name']) ?>')" 
                                            class="text-green-600 hover:text-green-900 mr-3">
                                        <i class="fas fa-play"></i>
                                    </button>
                                <?php elseif ($semester['status'] === 'active'): ?>
                                    <button onclick="openCompleteModal(<?= $semester['id'] ?>, '<?= htmlspecialchars($semester['name']) ?>')" 
                                            class="text-blue-600 hover:text-blue-900 mr-3">
                                        <i class="fas fa-check"></i>
                                    </button>
                                <?php endif; ?>
                                <?php if ($semester['status'] === 'upcoming'): ?>
                                    <button onclick="openDeleteModal(<?= $semester['id'] ?>, '<?= htmlspecialchars($semester['name']) ?>')" 
                                            class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Semester Modal -->
<div id="addSemesterModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Create New Semester</h3>
            <form method="POST">
                <input type="hidden" name="action" value="create_semester">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Semester Name *</label>
                        <input type="text" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Start Date *</label>
                        <input type="date" name="start_date" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">End Date *</label>
                        <input type="date" name="end_date" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeModal('addSemesterModal')" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                        Create Semester
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Semester Modal -->
<div id="editSemesterModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Edit Semester</h3>
            <form method="POST">
                <input type="hidden" name="action" value="update_semester">
                <input type="hidden" id="editSemesterId" name="semester_id">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Semester Name *</label>
                        <input type="text" id="editName" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Start Date *</label>
                        <input type="date" id="editStartDate" name="start_date" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">End Date *</label>
                        <input type="date" id="editEndDate" name="end_date" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeModal('editSemesterModal')" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                        Update Semester
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Activate Confirmation Modal -->
<div id="activateModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-sm w-full p-6">
            <div class="text-center">
                <i class="fas fa-exclamation-triangle text-4xl text-amber-500 mb-4"></i>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Activate Semester</h3>
                <p class="text-gray-600 mb-4">Are you sure you want to activate <span id="activateSemesterName" class="font-semibold"></span>?</p>
                <p class="text-sm text-amber-600 mb-4">This will complete the current active semester.</p>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="activate_semester">
                <input type="hidden" id="activateSemesterId" name="semester_id">
                <div class="flex justify-center space-x-3">
                    <button type="button" onclick="closeModal('activateModal')" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        Activate
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Complete Confirmation Modal -->
<div id="completeModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-sm w-full p-6">
            <div class="text-center">
                <i class="fas fa-check-circle text-4xl text-blue-500 mb-4"></i>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Complete Semester</h3>
                <p class="text-gray-600 mb-4">Are you sure you want to complete <span id="completeSemesterName" class="font-semibold"></span>?</p>
                <p class="text-sm text-blue-600 mb-4">This will close the semester. This cannot be undone.</p>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="complete_semester">
                <input type="hidden" id="completeSemesterId" name="semester_id">
                <div class="flex justify-center space-x-3">
                    <button type="button" onclick="closeModal('completeModal')" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Complete
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-sm w-full p-6">
            <div class="text-center">
                <i class="fas fa-exclamation-triangle text-4xl text-red-500 mb-4"></i>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Delete Semester</h3>
                <p class="text-gray-600 mb-6">Are you sure you want to delete <span id="deleteSemesterName" class="font-semibold"></span>?</p>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="delete_semester">
                <input type="hidden" id="deleteSemesterId" name="semester_id">
                <div class="flex justify-center space-x-3">
                    <button type="button" onclick="closeModal('deleteModal')" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                        Delete
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openAddSemesterModal() {
    document.getElementById('addSemesterModal').classList.remove('hidden');
}

function openEditSemesterModal(semester) {
    document.getElementById('editSemesterId').value = semester.id;
    document.getElementById('editName').value = semester.name;
    document.getElementById('editStartDate').value = semester.start_date;
    document.getElementById('editEndDate').value = semester.end_date;
    document.getElementById('editSemesterModal').classList.remove('hidden');
}

function openActivateModal(semesterId, semesterName) {
    document.getElementById('activateSemesterId').value = semesterId;
    document.getElementById('activateSemesterName').textContent = semesterName;
    document.getElementById('activateModal').classList.remove('hidden');
}

function openCompleteModal(semesterId, semesterName) {
    document.getElementById('completeSemesterId').value = semesterId;
    document.getElementById('completeSemesterName').textContent = semesterName;
    document.getElementById('completeModal').classList.remove('hidden');
}

function openDeleteModal(semesterId, semesterName) {
    document.getElementById('deleteSemesterId').value = semesterId;
    document.getElementById('deleteSemesterName').textContent = semesterName;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

// Close modals on backdrop click
document.querySelectorAll('[id$="Modal"]').forEach(modal => {
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.classList.add('hidden');
        }
    });
});
</script>

<?php
$pageContent = ob_get_clean();
require_once '../../includes/layout.php';
?>