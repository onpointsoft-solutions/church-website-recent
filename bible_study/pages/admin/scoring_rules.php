<?php
// CEFC Bible Study Management System
// File: pages/admin/scoring_rules.php
// Description: Configure scoring categories per session

require_once '../../config/config.php';
require_once '../../config/db.php';
require_once '../../includes/functions.php';
requireLogin();
requireRole(['admin']);

$pageTitle = 'Scoring Rules Configuration';
$activePage = 'scoring_rules';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add_category':
            $session_id = (int)($_POST['session_id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $max_points = (int)($_POST['max_points'] ?? 3);
            $is_custom = isset($_POST['is_custom']) ? 1 : 0;
            
            if (empty($session_id) || empty($name)) {
                $_SESSION['bs_flash'] = ['type' => 'error', 'message' => 'Session and category name are required'];
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO bs_score_categories (session_id, name, max_points, is_custom)
                    VALUES (?, ?, ?, ?)
                ");
                if ($stmt->execute([$session_id, $name, $max_points, $is_custom])) {
                    $_SESSION['bs_flash'] = ['type' => 'success', 'message' => 'Category added successfully'];
                } else {
                    $_SESSION['bs_flash'] = ['type' => 'error', 'message' => 'Failed to add category'];
                }
            }
            break;
            
        case 'update_category':
            $id = (int)($_POST['category_id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $max_points = (int)($_POST['max_points'] ?? 3);
            $is_custom = isset($_POST['is_custom']) ? 1 : 0;
            
            if (empty($id) || empty($name)) {
                $_SESSION['bs_flash'] = ['type' => 'error', 'message' => 'ID and category name are required'];
            } else {
                $stmt = $pdo->prepare("
                    UPDATE bs_score_categories 
                    SET name = ?, max_points = ?, is_custom = ? 
                    WHERE id = ?
                ");
                if ($stmt->execute([$name, $max_points, $is_custom, $id])) {
                    $_SESSION['bs_flash'] = ['type' => 'success', 'message' => 'Category updated successfully'];
                } else {
                    $_SESSION['bs_flash'] = ['type' => 'error', 'message' => 'Failed to update category'];
                }
            }
            break;
            
        case 'delete_category':
            $id = (int)($_POST['category_id'] ?? 0);
            if (empty($id)) {
                $_SESSION['bs_flash'] = ['type' => 'error', 'message' => 'Category ID is required'];
            } else {
                // Check if scores exist for this category
                $scoreCount = $pdo->prepare("SELECT COUNT(*) FROM bs_scores WHERE category_id = ?")->execute([$id]) ? 
                              $pdo->fetchColumn() : 0;
                
                if ($scoreCount > 0) {
                    $_SESSION['bs_flash'] = ['type' => 'error', 'message' => 'Cannot delete category with scores'];
                } else {
                    $stmt = $pdo->prepare("DELETE FROM bs_score_categories WHERE id = ?");
                    if ($stmt->execute([$id])) {
                        $_SESSION['bs_flash'] = ['type' => 'success', 'message' => 'Category deleted successfully'];
                    } else {
                        $_SESSION['bs_flash'] = ['type' => 'error', 'message' => 'Failed to delete category'];
                    }
                }
            }
            break;
            
        case 'add_default_categories':
            $session_id = (int)($_POST['session_id'] ?? 0);
            if (empty($session_id)) {
                $_SESSION['bs_flash'] = ['type' => 'error', 'message' => 'Session ID is required'];
            } else {
                $defaultCategories = [
                    ['name' => 'Time Keeping', 'max_points' => 3],
                    ['name' => 'Notebook Present', 'max_points' => 3],
                    ['name' => 'Bible Present', 'max_points' => 3],
                    ['name' => 'Participation', 'max_points' => 3],
                    ['name' => 'Team Growth', 'max_points' => 3],
                    ['name' => 'Verse Memorization', 'max_points' => 3],
                    ['name' => 'Judge\'s Bonus', 'max_points' => 3]
                ];
                
                $success = true;
                foreach ($defaultCategories as $category) {
                    $stmt = $pdo->prepare("
                        INSERT INTO bs_score_categories (session_id, name, max_points, is_custom)
                        VALUES (?, ?, ?, 0)
                    ");
                    if (!$stmt->execute([$session_id, $category['name'], $category['max_points']])) {
                        $success = false;
                    }
                }
                
                if ($success) {
                    $_SESSION['bs_flash'] = ['type' => 'success', 'message' => 'Default categories loaded successfully'];
                } else {
                    $_SESSION['bs_flash'] = ['type' => 'error', 'message' => 'Failed to load default categories'];
                }
            }
            break;
    }
    
    header('Location: scoring_rules.php' . (!empty($_GET['session_id']) ? '?session_id=' . $_GET['session_id'] : ''));
    exit;
}

// Fetch data
$sessions = $pdo->query("
    SELECT s.*, sem.name as semester_name 
    FROM bs_sessions s 
    LEFT JOIN bs_semesters sem ON s.semester_id = sem.id 
    ORDER BY s.session_date ASC, s.session_number ASC
")->fetchAll();

$selectedSessionId = (int)($_GET['session_id'] ?? 0);
$categories = [];
$selectedSession = null;

if ($selectedSessionId) {
    $categories = getScoreCategories($pdo, $selectedSessionId);
    $selectedSession = bsGetSessionById($pdo, $selectedSessionId);
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
    <h1 class="text-3xl font-bold text-gray-900">Scoring Rules Configuration</h1>
</div>

<!-- Session Selector -->
<div class="bg-white rounded-lg shadow p-4 mb-6">
    <div class="flex items-center space-x-4">
        <label class="text-sm font-medium text-gray-700">Select Session:</label>
        <select id="sessionSelector" onchange="changeSession()" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
            <option value="">Select a session to manage categories</option>
            <?php foreach ($sessions as $session): ?>
                <option value="<?= $session['id'] ?>" <?= $selectedSessionId == $session['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($session['semester_name']) ?> - Session <?= $session['session_number'] ?> (<?= formatDate($session['session_date']) ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<!-- Session Info Card -->
<?php if ($selectedSession): ?>
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
        <h3 class="text-lg font-semibold text-blue-900 mb-2">Session Information</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <span class="text-sm text-gray-600">Date:</span>
                <span class="font-medium"><?= formatDate($selectedSession['session_date']) ?></span>
            </div>
            <div>
                <span class="text-sm text-gray-600">Topic:</span>
                <span class="font-medium"><?= htmlspecialchars($selectedSession['topic'] ?? 'Not set') ?></span>
            </div>
            <div>
                <span class="text-sm text-gray-600">Book Reference:</span>
                <span class="font-medium"><?= htmlspecialchars($selectedSession['book_reference'] ?? 'Not set') ?></span>
            </div>
        </div>
    </div>
    
    <!-- Action Buttons -->
    <div class="flex space-x-4 mb-6">
        <button onclick="loadDefaultCategories()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
            <i class="fas fa-download mr-2"></i>Load Default Categories
        </button>
        <button onclick="openAddCategoryModal()" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
            <i class="fas fa-plus mr-2"></i>Add Custom Category
        </button>
    </div>
    
    <!-- Categories Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Max Points</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Custom</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($categories)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                <i class="fas fa-sliders text-4xl text-gray-300 mb-2"></i>
                                <p>No categories found. Load default categories or add custom ones above.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($categories as $index => $category): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $index + 1 ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?= htmlspecialchars($category['name']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= number_format($category['max_points']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs rounded-full <?= $category['is_custom'] ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800' ?>">
                                        <?= $category['is_custom'] ? 'Yes' : 'No' ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button onclick="openEditCategoryModal(<?= htmlspecialchars(json_encode($category)) ?>)" 
                                            class="text-purple-600 hover:text-purple-900 mr-3">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="openDeleteModal(<?= $category['id'] ?>, '<?= htmlspecialchars($category['name']) ?>')" 
                                            class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-trash"></i>
                                    </button>
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
        <i class="fas fa-sliders text-4xl text-gray-300 mb-4"></i>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No Session Selected</h3>
        <p class="text-gray-600">Select a session from the dropdown above to manage its scoring categories.</p>
    </div>
<?php endif; ?>

<!-- Add Category Modal -->
<div id="addCategoryModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Add Custom Category</h3>
            <form method="POST">
                <input type="hidden" name="action" value="add_category">
                <input type="hidden" id="addSessionId" name="session_id">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Category Name *</label>
                        <input type="text" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Max Points</label>
                        <input type="number" name="max_points" value="3" min="1" max="10" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" id="addIsCustom" name="is_custom" class="mr-2">
                        <label for="addIsCustom" class="text-sm text-gray-700">Is Custom Category</label>
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeModal('addCategoryModal')" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                        Add Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div id="editCategoryModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Edit Category</h3>
            <form method="POST">
                <input type="hidden" name="action" value="update_category">
                <input type="hidden" id="editCategoryId" name="category_id">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Category Name *</label>
                        <input type="text" id="editName" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Max Points</label>
                        <input type="number" id="editMaxPoints" name="max_points" min="1" max="10" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" id="editIsCustom" name="is_custom" class="mr-2">
                        <label for="editIsCustom" class="text-sm text-gray-700">Is Custom Category</label>
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeModal('editCategoryModal')" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                        Update Category
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
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Delete Category</h3>
                <p class="text-gray-600 mb-6">Are you sure you want to delete <span id="deleteCategoryName" class="font-semibold"></span>?</p>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="delete_category">
                <input type="hidden" id="deleteCategoryId" name="category_id">
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
function changeSession() {
    const sessionId = document.getElementById('sessionSelector').value;
    if (sessionId) {
        window.location.href = 'scoring_rules.php?session_id=' + sessionId;
    }
}

function loadDefaultCategories() {
    const sessionId = document.getElementById('sessionSelector').value;
    if (!sessionId) {
        showMessage('Please select a session first', 'error');
        return;
    }
    
    if (confirm('This will replace all existing categories with the 7 default categories. Continue?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="add_default_categories">
            <input type="hidden" name="session_id" value="${sessionId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function openAddCategoryModal() {
    const sessionId = document.getElementById('sessionSelector').value;
    if (!sessionId) {
        showMessage('Please select a session first', 'error');
        return;
    }
    document.getElementById('addSessionId').value = sessionId;
    document.getElementById('addCategoryModal').classList.remove('hidden');
}

function openEditCategoryModal(category) {
    document.getElementById('editCategoryId').value = category.id;
    document.getElementById('editName').value = category.name;
    document.getElementById('editMaxPoints').value = category.max_points;
    document.getElementById('editIsCustom').checked = category.is_custom == 1;
    document.getElementById('editCategoryModal').classList.remove('hidden');
}

function openDeleteModal(categoryId, categoryName) {
    document.getElementById('deleteCategoryId').value = categoryId;
    document.getElementById('deleteCategoryName').textContent = categoryName;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

function showMessage(message, type) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `bg-${type === 'error' ? 'red' : 'green'}-50 border border-${type === 'error' ? 'red' : 'green'}-200 text-${type === 'error' ? 'red' : 'green'}-800 px-4 py-3 rounded-lg mb-6`;
    alertDiv.innerHTML = `
        <div class="flex items-center">
            <i class="fas fa-${type === 'error' ? 'exclamation-circle' : 'check-circle'} mr-2"></i>
            <span>${message}</span>
        </div>
        <button onclick="this.parentElement.remove()" class="ml-4 text-${type === 'error' ? 'red' : 'green'}-600 hover:text-${type === 'error' ? 'red' : 'green'}-800">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    const container = document.querySelector('.mb-6');
    if (container) {
        container.after(alertDiv);
    } else {
        document.querySelector('h1').after(alertDiv);
    }
    
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.parentNode.removeChild(alertDiv);
        }
    }, 5000);
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