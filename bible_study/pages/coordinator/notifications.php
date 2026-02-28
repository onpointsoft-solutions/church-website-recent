<?php
// CEFC Bible Study Management System
// File: pages/coordinator/notifications.php
// Description: Send session result notifications to all members

require_once '../../config/config.php';
require_once '../../config/db.php';
require_once '../../includes/functions.php';
requireLogin();
requireRole(['coordinator']);

// Fetch active semester
$activeSemester = getActiveSemester($pdo);

// Fetch published sessions for active semester
$sessions = [];
if ($activeSemester) {
    $stmt = $pdo->prepare("
        SELECT bs.*, 
               COUNT(DISTINCT sc.group_id) as groups_scored,
               COUNT(DISTINCT ba.user_id) as members_attended
        FROM bs_sessions bs
        LEFT JOIN bs_scores sc ON sc.session_id = bs.id
        LEFT JOIN bs_attendance ba ON ba.session_id = bs.id
        WHERE bs.semester_id = ? AND bs.status = 'published'
        ORDER BY bs.session_number DESC
    ");
    $stmt->execute([$activeSemester['id']]);
    $sessions = $stmt->fetchAll();
}

// Fetch recent notification history (last 20)
$notificationHistory = [];
$stmt = $pdo->prepare("
    SELECT nl.*, bu.name as member_name
    FROM bs_notifications_log nl
    LEFT JOIN bs_users bu ON nl.user_id = bu.id
    ORDER BY nl.sent_at DESC
    LIMIT 20
");
$stmt->execute();
$notificationHistory = $stmt->fetchAll();

// Count total active verified members
$totalMembers = 0;
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM bs_users 
    WHERE role = 'member' AND status = 'active' AND verified = 1
");
$stmt->execute();
$totalMembers = $stmt->fetchColumn();

$pageTitle  = 'Send Notifications';
$activePage = 'notifications';
ob_start();
?>

<!-- Flash Message -->
<?php if (!empty($_SESSION['bs_flash'])): ?>
    <div class="mb-4 p-4 rounded-lg <?= $_SESSION['bs_flash']['type'] === 'success' 
        ? 'bg-green-50 border border-green-200 text-green-800' 
        : 'bg-red-50 border border-red-200 text-red-800' ?>">
        <?= htmlspecialchars($_SESSION['bs_flash']['message']) ?>
    </div>
    <?php unset($_SESSION['bs_flash']); ?>
<?php endif; ?>

<!-- Page Header -->
<div class="mb-6">
    <h1 class="text-2xl font-bold text-purple-900">
        <i class="fa-solid fa-bell mr-2 text-amber-500"></i>
        Send Notifications
    </h1>
    <p class="text-gray-500 mt-1">
        Email session results to all active members
    </p>
</div>

<?php if (!$activeSemester): ?>
<!-- No Active Semester -->
<div class="bg-amber-50 border border-amber-200 rounded-xl p-6 text-center">
    <i class="fa-solid fa-triangle-exclamation text-amber-500 text-4xl mb-3"></i>
    <h3 class="text-lg font-semibold text-amber-800">No Active Semester</h3>
    <p class="text-amber-600 mt-1">
        Please ask the admin to activate a semester before sending notifications.
    </p>
</div>

<?php else: ?>

<!-- Stats Row -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex items-center gap-4">
        <div class="bg-purple-100 rounded-full p-3">
            <i class="fa-solid fa-users text-purple-700 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Total Recipients</p>
            <p class="text-2xl font-bold text-purple-900"><?= $totalMembers ?></p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex items-center gap-4">
        <div class="bg-amber-100 rounded-full p-3">
            <i class="fa-solid fa-book-open text-amber-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Published Sessions</p>
            <p class="text-2xl font-bold text-purple-900"><?= count($sessions) ?></p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex items-center gap-4">
        <div class="bg-green-100 rounded-full p-3">
            <i class="fa-solid fa-envelope-circle-check text-green-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Emails Sent (All Time)</p>
            <p class="text-2xl font-bold text-purple-900"><?= count($notificationHistory) ?>+</p>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

    <!-- Send Results Card -->
    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-semibold text-purple-900 mb-1">
            <i class="fa-solid fa-paper-plane mr-2 text-purple-600"></i>
            Send Session Results
        </h2>
        <p class="text-sm text-gray-500 mb-4">
            Select a session and send results to all 
            <strong><?= $totalMembers ?></strong> active verified members.
        </p>

        <?php if (empty($sessions)): ?>
            <div class="bg-gray-50 rounded-lg p-4 text-center text-gray-500">
                <i class="fa-solid fa-circle-info mr-1"></i>
                No published sessions available yet.
            </div>
        <?php else: ?>

            <!-- Session Selector -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Select Session
                </label>
                <select id="sessionSelector" 
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 
                           text-sm focus:outline-none focus:ring-2 
                           focus:ring-purple-500">
                    <option value="">-- Choose a session --</option>
                    <?php foreach ($sessions as $session): ?>
                        <option value="<?= $session['id'] ?>"
                            data-date="<?= date('M j, Y', 
                                strtotime($session['session_date'])) ?>"
                            data-topic="<?= htmlspecialchars($session['topic'] ?? 'N/A') ?>"
                            data-scored="<?= $session['groups_scored'] ?>"
                            data-attended="<?= $session['members_attended'] ?>">
                            Session <?= $session['session_number'] ?> — 
                            <?= date('M j, Y', strtotime($session['session_date'])) ?>
                            <?= $session['topic'] ? '— ' . 
                                htmlspecialchars($session['topic']) : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Session Info Card (shown after selection) -->
            <div id="sessionInfoCard" 
                 class="hidden bg-purple-50 border border-purple-200 
                        rounded-lg p-4 mb-4">
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div>
                        <span class="text-gray-500">Date:</span>
                        <span id="infoDate" class="font-medium text-purple-900 ml-1"></span>
                    </div>
                    <div>
                        <span class="text-gray-500">Topic:</span>
                        <span id="infoTopic" class="font-medium text-purple-900 ml-1"></span>
                    </div>
                    <div>
                        <span class="text-gray-500">Groups Scored:</span>
                        <span id="infoScored" class="font-medium text-purple-900 ml-1"></span>
                    </div>
                    <div>
                        <span class="text-gray-500">Members Attended:</span>
                        <span id="infoAttended" class="font-medium text-purple-900 ml-1"></span>
                    </div>
                </div>
            </div>

            <!-- Warning -->
            <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 mb-4">
                <p class="text-xs text-amber-700">
                    <i class="fa-solid fa-triangle-exclamation mr-1"></i>
                    This will send an email to all 
                    <strong><?= $totalMembers ?></strong> 
                    active verified members. 
                    Make sure scores are entered before sending.
                </p>
            </div>

            <!-- Send Button -->
            <button id="sendNotificationsBtn" 
                    onclick="sendSessionResults()"
                    class="w-full bg-purple-700 hover:bg-purple-800 
                           text-white font-semibold py-3 px-6 rounded-lg 
                           transition-colors duration-200 
                           disabled:opacity-50 disabled:cursor-not-allowed">
                <i class="fa-solid fa-paper-plane mr-2"></i>
                Send Session Results to All Members
            </button>

            <!-- Result Box -->
            <div id="sendResult" class="hidden mt-4 p-4 rounded-lg"></div>

        <?php endif; ?>
    </div>

    <!-- Test Email Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-semibold text-purple-900 mb-1">
            <i class="fa-solid fa-vial mr-2 text-blue-500"></i>
            Test Email
        </h2>
        <p class="text-sm text-gray-500 mb-4">
            Send a test email to verify your email configuration is working.
        </p>
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4">
            <p class="text-xs text-blue-700">
                <i class="fa-solid fa-circle-info mr-1"></i>
                Test email will be sent to your account email:
                <strong><?= htmlspecialchars($_SESSION['bs_user_email'] ?? 'N/A') ?></strong>
            </p>
        </div>
        <button onclick="sendTestEmail()"
                id="testEmailBtn"
                class="w-full bg-blue-600 hover:bg-blue-700 
                       text-white font-semibold py-2 px-4 rounded-lg 
                       transition-colors duration-200">
            <i class="fa-solid fa-paper-plane mr-2"></i>
            Send Test Email
        </button>
        <div id="testResult" class="hidden mt-3 p-3 rounded-lg text-sm"></div>
    </div>

</div>

<!-- Notification History Table -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <h2 class="text-lg font-semibold text-purple-900 mb-4">
        <i class="fa-solid fa-clock-rotate-left mr-2 text-gray-400"></i>
        Recent Notification History
        <span class="text-sm font-normal text-gray-400 ml-2">(Last 20)</span>
    </h2>

    <?php if (empty($notificationHistory)): ?>
        <div class="text-center py-8 text-gray-400">
            <i class="fa-solid fa-bell-slash text-4xl mb-3"></i>
            <p>No notifications sent yet.</p>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="text-left px-4 py-3 font-semibold text-gray-600">#</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600">
                            Member
                        </th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600">
                            Email
                        </th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600">
                            Subject
                        </th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600">
                            Status
                        </th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600">
                            Sent At
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($notificationHistory as $i => $log): ?>
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-400">
                                <?= $i + 1 ?>
                            </td>
                            <td class="px-4 py-3 font-medium text-gray-800">
                                <?= htmlspecialchars($log['member_name'] ?? 'N/A') ?>
                            </td>
                            <td class="px-4 py-3 text-gray-500 text-xs">
                                <?= htmlspecialchars($log['email_address']) ?>
                            </td>
                            <td class="px-4 py-3 text-gray-600 text-xs max-w-xs truncate">
                                <?= htmlspecialchars($log['subject'] ?? 'N/A') ?>
                            </td>
                            <td class="px-4 py-3">
                                <?php if ($log['status'] === 'sent'): ?>
                                    <span class="bg-green-100 text-green-700 
                                                 text-xs font-medium px-2 py-1 rounded-full">
                                        <i class="fa-solid fa-check mr-1"></i>Sent
                                    </span>
                                <?php else: ?>
                                    <span class="bg-red-100 text-red-700 
                                                 text-xs font-medium px-2 py-1 rounded-full">
                                        <i class="fa-solid fa-xmark mr-1"></i>Failed
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-gray-400 text-xs">
                                <?= date('M j, Y g:i A', strtotime($log['sent_at'])) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php endif; ?>

<!-- JavaScript -->
<script>
// Show session info when session is selected
document.getElementById('sessionSelector')
    ?.addEventListener('change', function() {
        const option = this.options[this.selectedIndex];
        const card = document.getElementById('sessionInfoCard');
        
        if (this.value) {
            document.getElementById('infoDate').textContent = 
                option.dataset.date;
            document.getElementById('infoTopic').textContent = 
                option.dataset.topic;
            document.getElementById('infoScored').textContent = 
                option.dataset.scored + ' groups';
            document.getElementById('infoAttended').textContent = 
                option.dataset.attended + ' members';
            card.classList.remove('hidden');
        } else {
            card.classList.add('hidden');
        }
    });

// Send session results
function sendSessionResults() {
    const sessionId = document.getElementById('sessionSelector')?.value;
    
    if (!sessionId) {
        alert('Please select a session first.');
        return;
    }
    
    if (!confirm('Send results to all active members? This cannot be undone.')) {
        return;
    }
    
    const btn = document.getElementById('sendNotificationsBtn');
    const result = document.getElementById('sendResult');
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i>Sending...';
    
    fetch('../../api/bs_api.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=send_session_results&session_id=' + sessionId
    })
    .then(r => r.json())
    .then(data => {
        result.classList.remove('hidden');
        if (data.success) {
            result.className = 'mt-4 p-4 rounded-lg bg-green-50 ' +
                'border border-green-200 text-green-800';
            result.innerHTML = 
                '<i class="fa-solid fa-circle-check mr-2"></i>' +
                '<strong>Notifications sent!</strong><br>' +
                '✅ Sent: ' + data.data.sent + 
                ' &nbsp;|&nbsp; ❌ Failed: ' + data.data.failed + 
                ' &nbsp;|&nbsp; 📧 Total: ' + data.data.total;
            setTimeout(() => location.reload(), 3000);
        } else {
            result.className = 'mt-4 p-4 rounded-lg bg-red-50 ' +
                'border border-red-200 text-red-800';
            result.innerHTML = 
                '<i class="fa-solid fa-circle-xmark mr-2"></i>' + 
                data.message;
        }
    })
    .catch(() => {
        result.classList.remove('hidden');
        result.className = 'mt-4 p-4 rounded-lg bg-red-50 ' +
            'border border-red-200 text-red-800';
        result.textContent = 'Request failed. Check your connection.';
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = 
            '<i class="fa-solid fa-paper-plane mr-2"></i>' +
            'Send Session Results to All Members';
    });
}

// Send test email
function sendTestEmail() {
    const btn = document.getElementById('testEmailBtn');
    const result = document.getElementById('testResult');
    
    btn.disabled = true;
    btn.innerHTML = 
        '<i class="fa-solid fa-spinner fa-spin mr-2"></i>Sending...';
    
    fetch('../../api/bs_api.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=send_test_email'
    })
    .then(r => r.json())
    .then(data => {
        result.classList.remove('hidden');
        if (data.success) {
            result.className = 'mt-3 p-3 rounded-lg text-sm ' +
                'bg-green-50 border border-green-200 text-green-700';
            result.innerHTML = 
                '<i class="fa-solid fa-check mr-1"></i>' + data.message;
        } else {
            result.className = 'mt-3 p-3 rounded-lg text-sm ' +
                'bg-red-50 border border-red-200 text-red-700';
            result.innerHTML = 
                '<i class="fa-solid fa-xmark mr-1"></i>' + data.message;
        }
    })
    .catch(() => {
        result.classList.remove('hidden');
        result.textContent = 'Request failed.';
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = 
            '<i class="fa-solid fa-paper-plane mr-2"></i>Send Test Email';
    });
}
</script>

<?php
$pageContent = ob_get_clean();
require_once '../../includes/layout.php';
?>