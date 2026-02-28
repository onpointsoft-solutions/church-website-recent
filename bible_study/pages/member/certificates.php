<?php
// CEFC Bible Study Management System
// File: pages/member/certificates.php
// Description: View and download certificates

require_once '../../config/config.php';
require_once '../../config/db.php';
require_once '../../includes/functions.php';
requireLogin();
requireRole(['member']);

$pageTitle = 'My Certificates';
$activePage = 'certificates';

// Get current member's user ID
$memberId = $_SESSION['bs_user_id'];

// Fetch member's certificates
$certificates = [];
$activeSemester = null;

// Get active semester
$activeSemester = getActiveSemester($pdo);

if ($activeSemester) {
    // Get certificates for this member
    $certStmt = $pdo->prepare("
        SELECT c.*, s.name as semester_name 
        FROM bs_certificates c
        JOIN bs_semesters s ON s.id = c.semester_id
        WHERE c.user_id = ?
        ORDER BY c.issued_date DESC
    ");
    $certStmt->execute([$memberId]);
    $certificates = $certStmt->fetchAll();
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
    <h1 class="text-3xl font-bold text-gray-900">My Certificates</h1>
    <p class="text-gray-600 mt-1">Download and print your earned certificates</p>
</div>

<?php if (empty($certificates)): ?>
    <!-- No Certificates Yet -->
    <div class="bg-gray-50 border border-gray-200 rounded-lg p-8 text-center">
        <i class="fas fa-certificate text-4xl text-gray-300 mb-4"></i>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No Certificates Yet</h3>
        <p class="text-gray-600 mb-4">Certificates are issued at the end of each semester to members who participated in the Bible Study program.</p>
        <p class="text-sm text-gray-500">Keep attending and participating every Saturday to earn your certificate at the end of the semester.</p>
        
        <div class="mt-6 max-w-md mx-auto">
            <div class="bg-white rounded-lg p-4 border border-gray-200">
                <h4 class="font-semibold text-gray-900 mb-3">Certificate Types</h4>
                <div class="space-y-2 text-sm text-gray-600">
                    <div class="flex items-center">
                        <span class="mr-2">🎓</span>
                        <span><strong>Participation</strong> — Awarded for participating in the Bible Study program during that semester.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php else: ?>
    <!-- Certificates Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <?php foreach ($certificates as $certificate): ?>
            <div class="bg-white rounded-lg shadow-lg overflow-hidden border border-amber-200">
                <!-- Top Accent Bar -->
                <div class="bg-amber-500 h-2"></div>
                
                <div class="p-6">
                    <!-- Certificate Icon and Type -->
                    <div class="flex items-center mb-4">
                        <div class="p-3 bg-amber-100 rounded-full mr-4">
                            <i class="fas fa-certificate text-amber-600 text-2xl"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-purple-900"><?= htmlspecialchars($certificate['certificate_type']) ?></h3>
                            <p class="text-sm text-gray-600"><?= htmlspecialchars($certificate['semester_name']) ?></p>
                        </div>
                    </div>
                    
                    <!-- Certificate Details -->
                    <div class="space-y-3 mb-6">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Issued Date:</span>
                            <span class="text-sm font-medium text-gray-900"><?= formatDate($certificate['issued_date']) ?></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Status:</span>
                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Available</span>
                        </div>
                        <?php if (!empty($certificate['description'])): ?>
                            <div class="pt-2 border-t border-gray-100">
                                <p class="text-sm text-gray-600"><?= htmlspecialchars($certificate['description']) ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex space-x-3">
                        <a href="../../certificates/generate.php?cert_id=<?= $certificate['id'] ?>" 
                           class="flex-1 px-4 py-2 bg-purple-600 text-white text-center rounded-lg hover:bg-purple-700 transition-colors">
                            <i class="fas fa-download mr-2"></i>Download PDF
                        </a>
                        <button onclick="window.open('../../certificates/generate.php?cert_id=<?= $certificate['id'] ?>', '_blank')" 
                                class="px-4 py-2 border border-amber-600 text-amber-600 rounded-lg hover:bg-amber-50 transition-colors">
                            <i class="fas fa-print mr-2"></i>Print
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Certificate Type Info Section -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-8">
        <h3 class="text-lg font-semibold text-blue-900 mb-4">
            <i class="fas fa-info-circle mr-2"></i>Certificate Types
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-lg p-4 border border-blue-100">
                <div class="text-2xl mb-2">🎓</div>
                <h4 class="font-semibold text-gray-900 mb-1">Participation</h4>
                <p class="text-sm text-gray-600">Awarded for participating in the Bible Study program during that semester.</p>
            </div>
        </div>
    </div>

    <!-- Semester History Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Semester History</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Semester</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Certificate Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Issued Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($certificates as $certificate): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= htmlspecialchars($certificate['semester_name']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= htmlspecialchars($certificate['certificate_type']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= formatDate($certificate['issued_date']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <a href="../../certificates/generate.php?cert_id=<?= $certificate['id'] ?>" 
                                       class="text-purple-600 hover:text-purple-900">
                                        <i class="fas fa-download mr-1"></i>Download
                                    </a>
                                    <button onclick="window.open('../../certificates/generate.php?cert_id=<?= $certificate['id'] ?>', '_blank')" 
                                            class="text-amber-600 hover:text-amber-900">
                                        <i class="fas fa-print mr-1"></i>Print
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php
$pageContent = ob_get_clean();
require_once '../../includes/layout.php';
?>