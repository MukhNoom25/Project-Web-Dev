<?php

session_start();
include "config.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'hr') {
    header("Location: index.php");
    exit;
}

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = $_POST['request_id'];
    $status = $_POST['status'];
    $notes = $_POST['notes'] ?? '';

    $stmt = $conn->prepare("UPDATE cuti_requests SET status = ?, notes = ? WHERE id = ?");
    $stmt->bind_param("ssi", $status, $notes, $request_id);
    $stmt->execute();
}

// Get all pending leave requests with user information
$requests = $conn->query("
    SELECT cr.*, u.nombor_badan AS username, u.department 
    FROM cuti_requests cr 
    JOIN users u ON cr.user_id = u.id 
    WHERE cr.status = 'pending' 
    ORDER BY cr.created_at DESC
");

$page_title = "Leave Approvals";
include "includes/header.php";
?>

<div class="container">
    <h2 class="page-title">Leave Request Approvals</h2>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Department</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Reason</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $requests->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['username']) ?></td>
                            <td><?= htmlspecialchars($row['department']) ?></td>
                            <td><?= htmlspecialchars($row['start_date']) ?></td>
                            <td><?= htmlspecialchars($row['end_date']) ?></td>
                            <td><?= htmlspecialchars($row['reason']) ?></td>
                            <td>
                                <form method="post" class="d-inline">
                                    <input type="hidden" name="request_id" value="<?= $row['id'] ?>">
                                    <input type="text" name="notes" placeholder="Notes" class="form-control form-control-sm mb-2">
                                    <button type="submit" name="status" value="approved" class="btn btn-success btn-sm">Approve</button>
                                    <button type="submit" name="status" value="rejected" class="btn btn-danger btn-sm">Reject</button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <a href="hr_dashboard.php" class="btn btn-secondary">Back to HR Dashboard</a>
    </div>
</div>

<?php include "includes/footer.php"; ?>