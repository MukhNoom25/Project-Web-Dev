<?php
session_start();
include "config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$page_title = "Dashboard";
include "includes/header.php";
?>

<div class="container">
    <h2 class="page-title">Hi Welcome To JPPB SYSTEM , <?= htmlspecialchars($_SESSION['username']) ?></h2>
    
    <div class="card mb-4">
        <div class="card-body">
            <h3>Quick Actions</h3>
            <div class="row mt-4">
                <div class="col-md-4 mb-3">
                    <a href="clock.php" class="btn btn-primary w-100">Clock In/Out</a>
                </div>
                <div class="col-md-4 mb-3">
                    <a href="apply_cuti.php" class="btn btn-primary w-100">Apply Leave</a>
                </div>
                <div class="col-md-4 mb-3">
                    <a href="request_barang.php" class="btn btn-primary w-100">Request Items</a>
                </div>
                
            </div>
        </div>
    </div>

    <?php if ($_SESSION['role'] == 'hr'): ?>
    <div class="card mb-4">
        <div class="card-body">
            <h3>HR Management</h3>
            <div class="row mt-4">
                <div class="col-md-6 mb-3">
                    <a href="hr_dashboard.php" class="btn btn-primary w-100">HR Approval Panel</a>
                </div>
                <div class="col-md-6 mb-3">
                    <a href="kpi.php" class="btn btn-primary w-100">KPI Management</a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <a href="logout.php" class="btn btn-secondary">Logout</a>
</div>

<?php include "includes/footer.php"; ?>
