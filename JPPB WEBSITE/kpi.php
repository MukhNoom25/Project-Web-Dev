<?php
session_start();
include "config.php";

// If HR visits the KPI page, redirect to HR review page
if (isset($_SESSION['role']) && $_SESSION['role'] === 'hr') {
    header('Location: hr_kpi_review.php');
    exit;
}

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$page_title = "KPI - Submit Target";

// Handle new KPI submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_kpi') {
    $year = (int) $_POST['year'];
    $quarter = (int) $_POST['quarter'];
    $target = trim($_POST['target']);

    $stmt = $conn->prepare("INSERT INTO kpi (user_id, year, quarter, target, status) VALUES (?, ?, ?, ?, 'pending')");
    $stmt->bind_param('iiis', $user_id, $year, $quarter, $target);
    $stmt->execute();
    $stmt->close();
    $message = "KPI target submitted and awaiting HR review.";
}

// Fetch user's KPI records
$stmt = $conn->prepare("SELECT id, year, quarter, target, achievement, score, status, notes, created_at FROM kpi WHERE user_id = ? ORDER BY year DESC, quarter DESC, created_at DESC");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

include "includes/header.php";
?>

<div class="container">
  <h2 class="page-title">Submit KPI Target</h2>
  <?php if(!empty($message)): ?><div class="alert alert-success"><?=htmlspecialchars($message)?></div><?php endif;?>
  <div class="card mb-4"><div class="card-body">
    <form method="post">
      <input type="hidden" name="action" value="submit_kpi">
      <div class="row">
        <div class="col-md-3 mb-3">
          <label>Year</label>
          <select name="year" class="form-control"><?php $y=date('Y'); for($i=$y;$i>=$y-1;$i--){ echo "<option>$i</option>"; } ?></select>
        </div>
        <div class="col-md-3 mb-3">
          <label>Quarter</label>
          <select name="quarter" class="form-control">
            <option value="1">Q1</option>
            <option value="2">Q2</option>
            <option value="3">Q3</option>
            <option value="4">Q4</option>
          </select>
        </div>
      </div>
      <div class="mb-3">
        <label>Target</label>
        <textarea name="target" class="form-control" rows="4" required></textarea>
      </div>
      <div class="d-flex gap-2 mt-3">
        <button type="submit" name="submit_kpi" value="1" class="btn btn-primary">Submit KPI Target</button>
        <a href="dashboard.php" class="btn btn-secondary">Back</a>
      </div>
    </form>
  </div></div>

  <h4>My KPI History</h4>
  <div class="table-responsive">
    <table class="table table-hover">
      <thead><tr><th>Year</th><th>Q</th><th>Target</th><th>Achievement</th><th>Score</th><th>Status</th><th>HR Notes</th><th>Submitted</th></tr></thead>
      <tbody>
        <?php while($r=$result->fetch_assoc()): ?>
          <tr>
            <td><?=htmlspecialchars($r['year'])?></td>
            <td>Q<?=htmlspecialchars($r['quarter'])?></td>
            <td><?=nl2br(htmlspecialchars($r['target']))?></td>
            <td><?=nl2br(htmlspecialchars($r['achievement'] ?? '-'))?></td>
            <td><?= $r['score']!==null?htmlspecialchars($r['score']):'-' ?></td>
            <td><?=htmlspecialchars(ucfirst($r['status']))?></td>
            <td><?=nl2br(htmlspecialchars($r['notes'] ?? '-'))?></td>
            <td><?=date('d M Y H:i',strtotime($r['created_at']))?></td>
          </tr>
        <?php endwhile;?>
      </tbody>
    </table>
  </div>
</div>
<?php $stmt->close(); include "includes/footer.php"; ?>
