<?php
// CEFC Bible Study Management System
// File: pages/coordinator/scoring.php
// Description: Enter weekly scores for each group

require_once '../../config/config.php';
require_once '../../config/db.php';
require_once '../../includes/functions.php';
requireLogin();
requireRole(['coordinator']);

$pageTitle = 'Score Entry';
$activePage = 'scoring';

// Run once: ALTER TABLE bs_scores ADD UNIQUE KEY unique_score (session_id, group_id, category_id);

// Fetch active semester first (needed for auto-save)
$activeSemester = getActiveSemester($pdo);

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] === 'save_scores') {
        $sessionId = (int)($_POST['session_id'] ?? 0);
        $scores = $_POST['scores'] ?? [];
        
        if (!$sessionId) {
            $_SESSION['bs_flash'] = ['type' => 'error', 'message' => 'Invalid session selected.'];
        } else {
            $pdo->beginTransaction();
            try {
                foreach ($scores as $groupId => $categories) {
                    foreach ($categories as $categoryId => $points) {
                        // Validate points: must be 0, 1, or 3 only
                        if (!in_array($points, [0, 1, 3])) {
                            throw new Exception('Invalid points value. Only 0, 1, or 3 are allowed.');
                        }
                        
                        // Insert or update score
                        $upsertStmt = $pdo->prepare("
                            INSERT INTO bs_scores (session_id, group_id, category_id, points) 
                            VALUES (?, ?, ?, ?)
                            ON DUPLICATE KEY UPDATE points = VALUES(points)
                        ");
                        $upsertStmt->execute([$sessionId, $groupId, $categoryId, $points]);
                    }
                    
                    // Update group total points
                    updateGroupTotalPoints($pdo, $groupId, $activeSemester['id']);
                }
                
                $pdo->commit();
                $_SESSION['bs_flash'] = ['type' => 'success', 'message' => 'Scores saved successfully.'];
            } catch (Exception $e) {
                $pdo->rollback();
                $_SESSION['bs_flash'] = ['type' => 'error', 'message' => 'Failed to save scores: ' . $e->getMessage()];
            }
        }
        header("Location: scoring.php?session_id=$sessionId");
        exit;
    }
    
    elseif ($_POST['action'] === 'save_single_score') {
        // AJAX handler for individual score auto-save
        $sessionId = (int)($_POST['session_id'] ?? 0);
        $groupId = (int)($_POST['group_id'] ?? 0);
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $points = (int)($_POST['points'] ?? 0);
        
        header('Content-Type: application/json');
        
        try {
            // Validate points
            if (!in_array($points, [0, 1, 3])) {
                throw new Exception('Invalid points value. Only 0, 1, or 3 are allowed.');
            }
            
            // Insert or update single score
            $upsertStmt = $pdo->prepare("
                INSERT INTO bs_scores (session_id, group_id, category_id, points) 
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE points = VALUES(points)
            ");
            $upsertStmt->execute([$sessionId, $groupId, $categoryId, $points]);
            
            // Update group total points
            updateGroupTotalPoints($pdo, $groupId, $activeSemester['id']);
            
            echo json_encode(['success' => true, 'message' => 'Score saved successfully']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
    
    elseif ($_POST['action'] === 'add_category') {
        $sessionId = (int)($_POST['session_id'] ?? 0);
        $categoryName = sanitize($_POST['category_name'] ?? '');
        $maxPoints = (int)($_POST['max_points'] ?? 3);
        
        if (!$sessionId || !$categoryName) {
            $_SESSION['bs_flash'] = ['type' => 'error', 'message' => 'Please fill in all required fields.'];
        } else {
            $insertStmt = $pdo->prepare("
                INSERT INTO bs_score_categories (session_id, name, max_points) 
                VALUES (?, ?, ?)
            ");
            
            if ($insertStmt->execute([$sessionId, $categoryName, $maxPoints])) {
                $_SESSION['bs_flash'] = ['type' => 'success', 'message' => 'Category added successfully.'];
            } else {
                $_SESSION['bs_flash'] = ['type' => 'error', 'message' => 'Failed to add category.'];
            }
        }
        header("Location: scoring.php?session_id=$sessionId");
        exit;
    }
}

// Fetch data
$publishedSessions = [];
$selectedSession = null;
$groups = [];
$categories = [];
$existingScores = [];

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
            // Get all active groups for selected semester
            $groupStmt = $pdo->prepare("
                SELECT g.*, u.name as leader_name 
                FROM bs_groups g
                LEFT JOIN bs_users u ON u.id = g.leader_id
                WHERE g.semester_id = ?
                ORDER BY g.name
            ");
            $groupStmt->execute([$activeSemester['id']]);
            $groups = $groupStmt->fetchAll();
            
            // Get score categories for selected session
            $categoryStmt = $pdo->prepare("
                SELECT * FROM bs_score_categories 
                WHERE session_id = ? 
                ORDER BY name
            ");
            $categoryStmt->execute([$selectedSessionId]);
            $categories = $categoryStmt->fetchAll();
            
            // Get existing scores for selected session
            $scoreStmt = $pdo->prepare("
                SELECT * FROM bs_scores 
                WHERE session_id = ?
            ");
            $scoreStmt->execute([$selectedSessionId]);
            $scoreData = $scoreStmt->fetchAll();
            
            // Organize scores by group_id and category_id
            foreach ($scoreData as $score) {
                $existingScores[$score['group_id']][$score['category_id']] = $score['points'];
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
    <h1 class="text-3xl font-bold text-gray-900">Score Entry</h1>
</div>

<?php if ($activeSemester && $selectedSession): ?>
    <!-- Session Selector -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
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
            <button onclick="openModal('addCategoryModal')" class="px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition-colors">
                <i class="fas fa-plus mr-2"></i>Add Custom Category
            </button>
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

    <!-- Scoring Grid -->
    <form method="POST" id="scoringForm">
        <input type="hidden" name="action" value="save_scores">
        <input type="hidden" name="session_id" value="<?= $selectedSession['id'] ?>">
        
        <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Scoring Grid</h2>
                <p class="text-sm text-gray-600">Click on point values to select scores for each group</p>
            </div>
            
            <?php if (!empty($groups) && !empty($categories)): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider sticky left-0 bg-gray-50">Group Name</th>
                                <?php foreach ($categories as $category): ?>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <?= htmlspecialchars($category['name']) ?>
                                        <br><span class="text-xs text-gray-400">(Max: <?= $category['max_points'] ?>)</span>
                                    </th>
                                <?php endforeach; ?>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Session Total</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($groups as $group): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap sticky left-0 bg-white">
                                        <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($group['name']) ?></div>
                                        <?php if ($group['leader_name']): ?>
                                            <div class="text-xs text-gray-500">Leader: <?= htmlspecialchars($group['leader_name']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <?php 
                                    $groupTotal = 0;
                                    foreach ($categories as $category): 
                                        $currentScore = $existingScores[$group['id']][$category['id']] ?? 0;
                                        $groupTotal += $currentScore;
                                    ?>
                                        <td class="px-6 py-4 text-center">
                                            <div class="flex justify-center space-x-1">
                                                <button type="button" 
                                                        class="score-btn w-8 h-8 text-xs rounded <?= $currentScore == 0 ? 'bg-purple-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' ?>"
                                                        onclick="selectScore(<?= $group['id'] ?>, <?= $category['id'] ?>, 0)">
                                                    0
                                                </button>
                                                <button type="button" 
                                                        class="score-btn w-8 h-8 text-xs rounded <?= $currentScore == 1 ? 'bg-purple-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' ?>"
                                                        onclick="selectScore(<?= $group['id'] ?>, <?= $category['id'] ?>, 1)">
                                                    1
                                                </button>
                                                <button type="button" 
                                                        class="score-btn w-8 h-8 text-xs rounded <?= $currentScore == 3 ? 'bg-purple-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' ?>"
                                                        onclick="selectScore(<?= $group['id'] ?>, <?= $category['id'] ?>, 3)">
                                                    3
                                                </button>
                                            </div>
                                            <input type="hidden" name="scores[<?= $group['id'] ?>][<?= $category['id'] ?>]" 
                                                   id="score_<?= $group['id'] ?>_<?= $category['id'] ?>" 
                                                   value="<?= $currentScore ?>">
                                        </td>
                                    <?php endforeach; ?>
                                    <td class="px-6 py-4 text-center">
                                        <span class="font-semibold text-purple-600" id="total_<?= $group['id'] ?>"><?= $groupTotal ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="px-6 py-8 text-center text-gray-500">
                    <i class="fas fa-table text-4xl text-gray-300 mb-2"></i>
                    <p><?= empty($groups) ? 'No groups found for this semester' : 'No scoring categories found. Add categories to start scoring.' ?></p>
                </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($groups) && !empty($categories)): ?>
            <div class="flex justify-center">
                <button type="submit" class="px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                    <i class="fas fa-save mr-2"></i>Save All Scores
                </button>
            </div>
        <?php endif; ?>
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
        <p class="text-gray-600">Please publish sessions before entering scores.</p>
    </div>
<?php endif; ?>

<!-- Add Category Modal -->
<div id="addCategoryModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-lg max-w-md w-full p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Add Custom Category</h3>
                <button onclick="closeModal('addCategoryModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add_category">
                <input type="hidden" name="session_id" value="<?= $selectedSession['id'] ?? '' ?>">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Category Name*</label>
                        <input type="text" name="category_name" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Max Points</label>
                        <input type="number" name="max_points" value="3" min="1" max="10"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" onclick="closeModal('addCategoryModal')" 
                            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
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

<script>
function openModal(modalId) {
    document.getElementById(modalId).classList.remove('hidden');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

function changeSession() {
    const sessionId = document.getElementById('sessionSelector').value;
    if (sessionId) {
        window.location.href = `scoring.php?session_id=${sessionId}`;
    }
}

function selectScore(groupId, categoryId, points) {
    // Update hidden input
    const input = document.getElementById(`score_${groupId}_${categoryId}`);
    input.value = points;
    
    // Update button styles
    const buttons = input.parentElement.querySelectorAll('.score-btn');
    buttons.forEach(btn => {
        btn.classList.remove('bg-purple-600', 'text-white');
        btn.classList.add('bg-gray-200', 'text-gray-700', 'hover:bg-gray-300');
    });
    
    // Highlight selected button
    const selectedIndex = points === 0 ? 0 : (points === 1 ? 1 : 2);
    buttons[selectedIndex].classList.remove('bg-gray-200', 'text-gray-700', 'hover:bg-gray-300');
    buttons[selectedIndex].classList.add('bg-purple-600', 'text-white');
    
    // Update group total
    updateGroupTotal(groupId);
    
    // Auto-save this score to database
    autoSaveScore(groupId, categoryId, points);
}

function autoSaveScore(groupId, categoryId, points) {
    const sessionId = document.querySelector('input[name="session_id"]').value;
    
    // Show saving indicator
    showSavingIndicator();
    
    // Send AJAX request to save individual score
    fetch('scoring.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            action: 'save_single_score',
            session_id: sessionId,
            group_id: groupId,
            category_id: categoryId,
            points: points
        })
    })
    .then(response => response.json())
    .then(data => {
        hideSavingIndicator();
        if (data.success) {
            // Show brief success feedback
            showQuickFeedback('Score saved', 'success');
        } else {
            // Show error feedback
            showQuickFeedback(data.message || 'Failed to save score', 'error');
        }
    })
    .catch(error => {
        hideSavingIndicator();
        showQuickFeedback('Network error. Please try again.', 'error');
    });
}

function showSavingIndicator() {
    // Create or update saving indicator
    let indicator = document.getElementById('savingIndicator');
    if (!indicator) {
        indicator = document.createElement('div');
        indicator.id = 'savingIndicator';
        indicator.className = 'fixed top-4 right-4 bg-blue-500 text-white px-3 py-2 rounded-lg shadow-lg z-50 flex items-center space-x-2';
        indicator.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Saving...</span>';
        document.body.appendChild(indicator);
    }
}

function hideSavingIndicator() {
    const indicator = document.getElementById('savingIndicator');
    if (indicator) {
        indicator.remove();
    }
}

function showQuickFeedback(message, type) {
    // Remove any existing feedback
    const existing = document.getElementById('quickFeedback');
    if (existing) {
        existing.remove();
    }
    
    // Create feedback element
    const feedback = document.createElement('div');
    feedback.id = 'quickFeedback';
    feedback.className = `fixed top-4 right-4 ${type === 'success' ? 'bg-green-500' : 'bg-red-500'} text-white px-4 py-2 rounded-lg shadow-lg z-50 flex items-center space-x-2`;
    feedback.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
        <span>${message}</span>
    `;
    
    document.body.appendChild(feedback);
    
    // Auto-remove after 2 seconds
    setTimeout(() => {
        if (feedback.parentNode) {
            feedback.parentNode.removeChild(feedback);
        }
    }, 2000);
}

function updateGroupTotal(groupId) {
    let total = 0;
    // Use attribute starts-with selector to match all category inputs for this group
    const inputs = document.querySelectorAll(`input[name^="scores[${groupId}]["]`);
    
    inputs.forEach(input => {
        total += parseInt(input.value) || 0;
    });
    
    document.getElementById(`total_${groupId}`).textContent = total;
}

// Close modals when clicking outside
document.querySelectorAll('[id$="Modal"]').forEach(modal => {
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.add('hidden');
        }
    });
});

// Confirm before saving if scores already exist
document.getElementById('scoringForm').addEventListener('submit', function(e) {
    const hasExistingScores = document.querySelectorAll('.score-btn.bg-purple-600').length > 0;
    
    if (hasExistingScores) {
        if (!confirm('Scores already entered for this session. Overwrite?')) {
            e.preventDefault();
        }
    }
});
</script>

<?php
$pageContent = ob_get_clean();
require_once '../../includes/layout.php';
?>