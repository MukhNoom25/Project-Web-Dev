<?php
session_start();
include "config.php";

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$message = '';
$user_id = $_SESSION['user_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get form data
        $user_id = $_SESSION['user_id'];
        $item_name = $_POST['item_name'];
        $quantity = (int)$_POST['quantity'];
        $description = $_POST['description'] ?? '';
        
        $user_id = (int) $_SESSION['user_id'];
        $stmt = $conn->prepare("INSERT INTO barang_requests (user_id, item_name, quantity, description, status, created_at) VALUES (?, ?, ?, ?, 'pending', NOW())");
        $stmt->bind_param('isis', $user_id, $item_name, $quantity, $description);
        
        if ($stmt->execute()) {
            $message = "Request submitted successfully!";
        } else {
            $message = "Error submitting request";
        }
    } catch (Exception $e) {
        error_log("Request error: " . $e->getMessage());
        $message = "System error occurred";
    }
}

$page_title = "Request Item";
include "includes/header.php";
?>

<div class="container">
    <h2 class="page-title">Request Item</h2>

    <?php if ($message): ?>
        <div class="alert <?= strpos($message, 'successfully') !== false ? 'alert-success' : 'alert-danger' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="post" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label for="item_name" class="form-label">Item Name *</label>
                    <input type="text" class="form-control" id="item_name" name="item_name" required>
                </div>

                <div class="mb-3">
                    <label for="quantity" class="form-label">Quantity *</label>
                    <input type="number" class="form-control" id="quantity" name="quantity" min="1" required>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Submit Request</button>
                    <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Show existing requests -->
    <div class="card mt-4">
        <div class="card-header">
            <h3 class="card-title h5 mb-0">My Request History</h3>
        </div>
        <div class="card-body">
            <?php
            $requests = $conn->query("SELECT * FROM barang_requests WHERE user_id = $user_id ORDER BY created_at DESC");
            if ($requests && $requests->num_rows > 0):
            ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Quantity</th>
                                <th>Status</th>
                                <th>Requested On</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $requests->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['item_name']) ?></td>
                                    <td><?= htmlspecialchars($row['quantity']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $row['status'] === 'pending' ? 'warning' : 
                                            ($row['status'] === 'approved' ? 'success' : 'danger') ?>">
                                            <?= ucfirst(htmlspecialchars($row['status'])) ?>
                                        </span>
                                    </td>
                                    <td><?= date('d M Y H:i', strtotime($row['created_at'])) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted mb-0">No requests found</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include "includes/footer.php"; ?>
