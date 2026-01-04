<?php
session_start();
include "config.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'hr') {
    header('Location: index.php');
    exit;
}

// Fetch pending KPI records
$reviews = $conn->query("
    SELECT k.*, u.nombor_badan AS user_code
    FROM kpi k
    JOIN users u ON k.user_id = u.id
    WHERE k.status = 'pending'
    ORDER BY k.year DESC, k.quarter DESC, k.created_at DESC
");

include "includes/header.php";
?>

<div class="container">
    <h2 class="page-title">KPI Reviews (Pending)</h2>

    <div class="card">
        <div class="card-body">
            <?php if ($reviews && $reviews->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Department</th>
                                <th>Year</th>
                                <th>Quarter</th>
                                <th>Target</th>
                                <th>Achievement</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($r = $reviews->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($r['user_code']) ?></td>
                                    <td><?= htmlspecialchars($r['department'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($r['year']) ?></td>
                                    <td>Q<?= htmlspecialchars($r['quarter']) ?></td>
                                    <td><?= nl2br(htmlspecialchars($r['target'])) ?></td>
                                    <td><?= nl2br(htmlspecialchars($r['achievement'] ?? '-')) ?></td>
                                    <td style="min-width:260px;">
                                        <form method="post" class="d-flex gap-2">
                                            <input type="hidden" name="kpi_id" value="<?= $r['id'] ?>">
                                            <input type="number" name="score" step="0.01" min="0" max="100" class="form-control form-control-sm" placeholder="Score (0-100)">
                                            <input type="text" name="notes" class="form-control form-control-sm" placeholder="Notes (optional)">
                                            <button type="submit" name="action" value="approve" class="btn btn-success btn-sm">Approve</button>
                                            <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm">Reject</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted">No pending KPI reviews.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="mt-3">
        <a href="hr_dashboard.php" class="btn btn-secondary">Back to HR Dashboard</a>
    </div>
</div>

<?php include "includes/footer.php"; ?>