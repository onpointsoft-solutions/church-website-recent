<?php
// CEFC Bible Study Management System
// File: pages/coordinator/sessions.php
// Description: Manage weekly Bible study sessions

require_once '../../config/config.php';
require_once '../../config/db.php';
require_once '../../includes/functions.php';
requireLogin();
requireRole(['coordinator']);

$pageTitle = 'Weekly Sessions';
$activePage = 'sessions';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] === 'create_session') {
        $semesterId = (int)($_POST['semester_id'] ?? 0);
        $sessionDate = sanitize($_POST['session_date'] ?? '');
        $sessionNumber = (int)($_POST['session_number'] ?? 0);
        $topic = sanitize($_POST['topic'] ?? '');
        $bookReference = sanitize($_POST['book_reference'] ?? '');
        $status = sanitize($_POST['status'] ?? 'draft');
        
        // Validate required fields
        if (!$semesterId || !$sessionDate || !$sessionNumber || !$topic) {
            $_SESSION['bs_flash'] = ['type' => 'error', 'message' => 'Please fill in all required fields.'];
        } else {
            // Check for duplicate session number in same semester
            $checkStmt = $pdo->prepare("SELECT id FROM bs_sessions WHERE semester_id = ? AND session_number = ?");
            $checkStmt->execute([$semesterId, $sessionNumber]);
            
            if ($checkStmt->fetch()) {
                $_SESSION['bs_flash'] = ['type' => 'error', 'message' => 'Session number already exists in this semester.'];
            } else {
                $insertStmt = $pdo->prepare("
                    INSERT INTO bs_sessions (semester_id, session_date, session_number, topic, book_reference, status) 
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                
                if ($insertStmt->execute([$semesterId, $sessionDate, $sessionNumber, $topic, $bookReference, $status])) {
                    $_SESSION['bs_flash'] = ['type' => 'success', 'message' => 'Session created successfully.'];
                } else {
                    $_SESSION['bs_flash'] = ['type' => 'error', 'message' => 'Failed to create session.'];
                }
            }
        }
        header('Location: sessions.php');
        exit;
    }
    
    elseif ($_POST['action'] === 'update_session') {
        $id = (int)($_POST['id'] ?? 0);
        $topic = sanitize($_POST['topic'] ?? '');
        $bookReference = sanitize($_POST['book_reference'] ?? '');
        
        if (!$id || !$topic) {
            $_SESSION['bs_flash'] = ['type' => 'error', 'message' => 'Please fill in all required fields.'];
        } else {
            $updateStmt = $pdo->prepare("
                UPDATE bs_sessions SET topic = ?, book_reference = ? WHERE id = ?
            ");
            
            if ($updateStmt->execute([$topic, $bookReference, $id])) {
                $_SESSION['bs_flash'] = ['type' => 'success', 'message' => 'Session updated successfully.'];
            } else {
                $_SESSION['bs_flash'] = ['type' => 'error', 'message' => 'Failed to update session.'];
            }
        }
        header('Location: sessions.php');
        exit;
    }
    
    elseif ($_POST['action'] === 'publish_session') {
        $id = (int)($_POST['id'] ?? 0);
        
        if ($id) {
            $updateStmt = $pdo->prepare("UPDATE bs_sessions SET status = 'published' WHERE id = ?");
            
            if ($updateStmt->execute([$id])) {
                $_SESSION['bs_flash'] = ['type' => 'success', 'message' => 'Session published successfully.'];
            } else {
                $_SESSION['bs_flash'] = ['type' => 'error', 'message' => 'Failed to publish session.'];
            }
        }
        header('Location: sessions.php');
        exit;
    }
    
    elseif ($_POST['action'] === 'delete_session') {
        $id = (int)($_POST['id'] ?? 0);
        
        if ($id) {
            // Check for existing scores or attendance
            $checkStmt = $pdo->prepare("
                SELECT COUNT(*) as count FROM (
                    SELECT id FROM bs_scores WHERE session_id = ?
                    UNION ALL
                    SELECT id FROM bs_attendance WHERE session_id = ?
                ) combined
            ");
            $checkStmt->execute([$id, $id]);
            $hasData = $checkStmt->fetch()['count'] > 0;
            
            if ($hasData) {
                $_SESSION['bs_flash'] = ['type' => 'error', 'message' => 'Cannot delete session with recorded data.'];
            } else {
                $deleteStmt = $pdo->prepare("DELETE FROM bs_sessions WHERE id = ?");
                
                if ($deleteStmt->execute([$id])) {
                    $_SESSION['bs_flash'] = ['type' => 'success', 'message' => 'Session deleted successfully.'];
                } else {
                    $_SESSION['bs_flash'] = ['type' => 'error', 'message' => 'Failed to delete session.'];
                }
            }
        }
        header('Location: sessions.php');
        exit;
    }
}

// Fetch data
$activeSemester = getActiveSemester($pdo);
$sessions = [];
$nextSessionNumber = 1;

if ($activeSemester) {
    // Get all sessions for active semester
    $sessionStmt = $pdo->prepare("
        SELECT s.*, 
               COUNT(DISTINCT sc.id) as score_count,
               COUNT(DISTINCT a.id) as attendance_count
        FROM bs_sessions s
        LEFT JOIN bs_scores sc ON sc.session_id = s.id
        LEFT JOIN bs_attendance a ON a.session_id = s.id
        WHERE s.semester_id = ?
        GROUP BY s.id
        ORDER BY s.session_number ASC
    ");
    $sessionStmt->execute([$activeSemester['id']]);
    $sessions = $sessionStmt->fetchAll();
    
    // Get next session number
    $maxNumberStmt = $pdo->prepare("SELECT MAX(session_number) as max_num FROM bs_sessions WHERE semester_id = ?");
    $maxNumberStmt->execute([$activeSemester['id']]);
    $maxNumber = $maxNumberStmt->fetch()['max_num'];
    $nextSessionNumber = ($maxNumber ? $maxNumber + 1 : 1);
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
    <h1 class="text-3xl font-bold text-gray-900">Weekly Sessions</h1>
    <?php if ($activeSemester): ?>
        <button onclick="openModal('createSessionModal')" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
            <i class="fas fa-plus mr-2"></i>Create New Session
        </button>
    <?php endif; ?>
</div>

<?php if ($activeSemester): ?>
    <!-- Active Semester Info Banner -->
    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900"><?= htmlspecialchars($activeSemester['name']) ?></h2>
                <p class="text-sm text-gray-600">
                    <?= formatDate($activeSemester['start_date']) ?> - <?= formatDate($activeSemester['end_date']) ?>
                </p>
            </div>
            <span class="px-3 py-1 text-sm rounded-full bg-amber-100 text-amber-800">Active</span>
        </div>
    </div>

    <!-- Sessions Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Session No.</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Topic</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Book Reference</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Scores Entered</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Attendance</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($sessions)): ?>
                        <tr>
                            <td colspan="9" class="px-6 py-8 text-center text-gray-500">
                                <i class="fas fa-calendar text-4xl text-gray-300 mb-2"></i>
                                <p>No sessions created yet</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($sessions as $index => $session): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $index + 1 ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= $session['session_number'] ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= formatDate($session['session_date']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($session['topic']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($session['book_reference'] ?? '-') ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= number_format($session['score_count']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= number_format($session['attendance_count']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs rounded-full 
                                        <?= $session['status'] === 'completed' ? 'bg-green-100 text-green-800' : 
                                           ($session['status'] === 'published' ? 'bg-blue-100 text-blue-800' : 
                                           'bg-gray-100 text-gray-800') ?>">
                                        <?= htmlspecialchars(ucfirst($session['status'])) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <button onclick="openEditModal(<?= htmlspecialchars(json_encode($session)) ?>)" class="text-amber-600 hover:text-amber-900">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <?php if ($session['status'] === 'draft'): ?>
                                            <button onclick="openPublishModal(<?= $session['id'] ?>)" class="text-green-600 hover:text-green-900">
                                                <i class="fas fa-check-circle"></i>
                                            </button>
                                        <?php endif; ?>
                                        <button onclick="openDeleteModal(<?= $session['id'] ?>)" class="text-red-600 hover:text-red-900">
                                            <i class="fas fa-trash"></i>
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

<?php else: ?>
    <!-- No Active Semester -->
    <div class="bg-gray-50 border border-gray-200 rounded-lg p-8 text-center">
        <i class="fas fa-calendar-times text-4xl text-gray-300 mb-4"></i>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No Active Semester</h3>
        <p class="text-gray-600">Please contact an administrator to activate a semester.</p>
    </div>
<?php endif; ?>

<!-- Create Session Modal -->
<div id="createSessionModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-lg max-w-md w-full p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Create New Session</h3>
                <button onclick="closeModal('createSessionModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="create_session">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Semester</label>
                        <input type="text" value="<?= $activeSemester ? htmlspecialchars($activeSemester['name']) : 'No active semester' ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50" readonly>
                        <input type="hidden" name="semester_id" value="<?= $activeSemester ? $activeSemester['id'] : '' ?>">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Session Number*</label>
                        <input type="number" name="session_number" value="<?= $nextSessionNumber ?>" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Session Date*</label>
                        <input type="date" name="session_date" required
                               value="<?= date('Y-m-d', strtotime('next saturday')) ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Topic*</label>
                        <input type="text" name="topic" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Book Reference</label>
                        <input type="text" name="book_reference" placeholder="e.g. John 3:1-21"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            <option value="draft">Draft</option>
                            <option value="published">Published</option>
                        </select>
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" onclick="closeModal('createSessionModal')" 
                            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                        Create Session
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Session Modal -->
<div id="editSessionModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-lg max-w-md w-full p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Edit Session</h3>
                <button onclick="closeModal('editSessionModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="update_session">
                <input type="hidden" id="editSessionId" name="id">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Topic*</label>
                        <input type="text" id="editTopic" name="topic" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Book Reference</label>
                        <input type="text" id="editBookReference" name="book_reference"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" onclick="closeModal('editSessionModal')" 
                            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                        Update Session
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Publish Confirmation Modal -->
<div id="publishModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-lg max-w-md w-full p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Publish Session</h3>
                <button onclick="closeModal('publishModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <p class="text-gray-600 mb-6">Publishing will make this session visible to all users.</p>
            <form method="POST">
                <input type="hidden" name="action" value="publish_session">
                <input type="hidden" id="publishSessionId" name="id">
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeModal('publishModal')" 
                            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        Publish Session
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
                <h3 class="text-lg font-semibold text-gray-900">Delete Session</h3>
                <button onclick="closeModal('deleteModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <p class="text-gray-600 mb-6">Are you sure you want to delete this session? This action cannot be undone.</p>
            <form method="POST">
                <input type="hidden" name="action" value="delete_session">
                <input type="hidden" id="deleteSessionId" name="id">
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeModal('deleteModal')" 
                            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                        Delete Session
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

function openEditModal(session) {
    document.getElementById('editSessionId').value = session.id;
    document.getElementById('editTopic').value = session.topic;
    document.getElementById('editBookReference').value = session.book_reference || '';
    openModal('editSessionModal');
}

function openPublishModal(sessionId) {
    document.getElementById('publishSessionId').value = sessionId;
    openModal('publishModal');
}

function openDeleteModal(sessionId) {
    document.getElementById('deleteSessionId').value = sessionId;
    openModal('deleteModal');
}

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
