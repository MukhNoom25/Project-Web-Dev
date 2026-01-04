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

    $stmt = $conn->prepare("UPDATE barang_requests SET status = ?, notes = ? WHERE id = ?");
    $stmt->bind_param("ssi", $status, $notes, $request_id);
    if ($stmt->execute()) {
        $message = "Request has been " . $status;
    } else {
        $message = "Error updating request";
    }
}

// Get all pending item requests
$requests = $conn->query("
    SELECT br.*, u.nombor_badan AS username, u.department 
    FROM barang_requests br 
    JOIN users u ON br.user_id = u.id 
    WHERE br.status = 'pending' OR br.status IS NULL
    ORDER BY br.created_at DESC
");

$page_title = "Item Request Approvals";
include "includes/header.php";
?>

<div class="container">
    <h2 class="page-title">Item Request Approvals</h2>

    <?php if (isset($message)): ?>
        <div class="alert <?= strpos($message, 'Error') !== false ? 'alert-danger' : 'alert-success' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Employee ID</th>
                            <th>Department</th>
                            <th>Item Name</th>
                            <th>Quantity</th>
                            <th>Request Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($requests && $requests->num_rows > 0): ?>
                            <?php while ($row = $requests->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['username']) ?></td>
                                    <td><?= htmlspecialchars($row['department']) ?></td>
                                    <td><?= htmlspecialchars($row['item_name']) ?></td>
                                    <td><?= htmlspecialchars($row['quantity']) ?></td>
                                    <td><?= date('d M Y H:i', strtotime($row['created_at'])) ?></td>
                                    <td>
                                        <form method="post" class="d-inline">
                                            <input type="hidden" name="request_id" value="<?= $row['id'] ?>">
                                            <div class="mb-2">
                                                <input type="text" name="notes" class="form-control form-control-sm" 
                                                       placeholder="Add notes (optional)">
                                            </div>
                                            <button type="submit" name="status" value="approved" 
                                                    class="btn btn-success btn-sm">Approve</button>
                                            <button type="submit" name="status" value="rejected" 
                                                    class="btn btn-danger btn-sm">Reject</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">No pending requests found</td>
                            </tr>
                        <?php endif; ?>
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