<?php
session_start();
include "config.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'hr') {
    header("Location: index.php");
    exit;
}

$page_title = "HR Dashboard";

// Get pending requests counts
$leave_pending = $conn->query("SELECT COUNT(*) as count FROM cuti_requests WHERE status = 'pending'")->fetch_assoc()['count'];
$pending_items = $conn->query("
    SELECT COUNT(*) as count 
    FROM barang_requests 
    WHERE status = 'pending' OR status IS NULL
");
$item_pending = $pending_items->fetch_assoc()['count'];

// Add debug logging
error_log("HR Dashboard - Found {$item_pending} pending requests");

$kpi_pending = $conn->query("SELECT COUNT(*) as count FROM kpi WHERE status = 'pending'")->fetch_assoc()['count'];

// Debug: show DB errors and enable reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// fetch pending item count (treat NULL/empty as pending)
$item_query = "
    SELECT COUNT(*) AS cnt
    FROM barang_requests
    WHERE status IS NULL OR status = '' OR LOWER(status) = 'pending'
";
$item_result = $conn->query($item_query);
if (! $item_result) {
    error_log('Item count query failed: ' . $conn->error);
    $item_pending = 0;
} else {
    $item_pending = (int) $item_result->fetch_assoc()['cnt'];
}
error_log("Debug: item_pending = $item_pending");

// fetch recent pending item requests (limit 10)
$recent_items_query = "
    SELECT br.id, br.user_id, br.item_name, br.quantity, COALESCE(br.status,'pending') AS status,
           br.created_at, u.nombor_badan AS user_code
    FROM barang_requests br
    LEFT JOIN users u ON br.user_id = u.id
    WHERE br.status IS NULL OR br.status = '' OR LOWER(br.status) = 'pending'
    ORDER BY br.created_at DESC
    LIMIT 10
";
$recent_items = $conn->query($recent_items_query);
if (! $recent_items) {
    error_log('Recent items query failed: ' . $conn->error);
}

// debug comments visible in page source
echo "<!-- Debug: item_pending={$item_pending} recent_rows=" . ($recent_items ? $recent_items->num_rows : 'err') . " -->";

include "includes/header.php";
?>

<div class="container">
    <h2 class="page-title">HR Dashboard</h2>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h3 class="card-title">Leave Requests</h3>
                    <p class="display-4"><?= $leave_pending ?></p>
                    <p class="mb-0">Pending Approvals</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h3 class="card-title">Item Requests</h3>
                    <p class="display-4"><?= $item_pending ?></p>
                    <p class="mb-0">Pending Approvals</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h3 class="card-title">KPI Reviews</h3>
                    <p class="display-4"><?= $kpi_pending ?></p>
                    <p class="mb-0">Pending Reviews</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h3 class="mb-0">Quick Actions</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <a href="hr_leave_approval.php" class="btn btn-outline-primary w-100 mb-3">
                        <i class="fas fa-calendar-check"></i> Leave Approvals
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="hr_item_approval.php" class="btn btn-outline-success w-100 mb-3">
                        <i class="fas fa-box-open"></i> Item Approvals
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="hr_kpi_review.php" class="btn btn-outline-info w-100 mb-3">
                        <i class="fas fa-chart-line"></i> KPI Reviews
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h3 class="mb-0">Recent Activities</h3>
        </div>
        <div class="card-body">
            <ul class="list-group">
                <?php
                // Update the recent activities query
                $recent_requests = $conn->query("
                    SELECT br.*, u.nombor_badan 
                    FROM barang_requests br
                    JOIN users u ON br.user_id = u.id
                    WHERE br.status = 'pending'
                    ORDER BY br.created_at DESC 
                    LIMIT 5
                ");
                while ($activity = $recent_requests->fetch_assoc()):
                    $type_text = [
                        'leave' => 'Leave Request',
                        'item' => 'Item Request',
                        'kpi' => 'KPI Submission'
                    ][$activity['type']];
                ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <strong><?= $type_text ?></strong>
                        <span class="text-muted ms-2"><?= date('d M Y H:i', strtotime($activity['created_at'])) ?></span>
                    </div>
                    <span class="badge bg-<?= $activity['status'] === 'pending' ? 'warning' : 
                                           ($activity['status'] === 'approved' ? 'success' : 'danger') ?>">
                        <?= ucfirst($activity['status']) ?>
                    </span>
                </li>
                <?php endwhile; ?>
            </ul>
        </div>
    </div>

    <!-- Staff Overview -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h3 class="mb-0">Staff Overview</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Department</th>
                            <th>Total Staff</th>
                            <th>Present Today</th>
                            <th>On Leave</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $departments = $conn->query("SELECT COALESCE(department, 'Unassigned') as department, 
                                              COUNT(*) as total 
                                              FROM users 
                                              GROUP BY department");
                        
                        while ($dept = $departments->fetch_assoc()):
                            // Get present count
                            $present = $conn->query("SELECT COUNT(DISTINCT c.user_id) as count 
                                                   FROM clock c 
                                                   JOIN users u ON c.user_id = u.id 
                                                   WHERE c.date = CURDATE() 
                                                   AND u.department = '{$dept['department']}'"
                                                 )->fetch_assoc()['count'];
                            
                            // Get on leave count
                            $on_leave = $conn->query("SELECT COUNT(DISTINCT cr.user_id) as count 
                                                    FROM cuti_requests cr 
                                                    JOIN users u ON cr.user_id = u.id 
                                                    WHERE cr.status = 'approved' 
                                                    AND CURDATE() BETWEEN cr.start_date AND cr.end_date 
                                                    AND u.department = '{$dept['department']}'"
                                                  )->fetch_assoc()['count'];
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($dept['department']) ?></td>
                            <td><?= $dept['total'] ?></td>
                            <td><?= $present ?></td>
                            <td><?= $on_leave ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <a href="dashboard.php" class="btn btn-secondary">Back to Main Dashboard</a>
</div>

<?php include "includes/footer.php"; ?>
