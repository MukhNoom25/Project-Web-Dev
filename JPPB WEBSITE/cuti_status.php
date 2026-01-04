<?php
session_start();
include "config.php";
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT start_date, end_date, reason, status FROM cuti_requests WHERE user_id=? ORDER BY id DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
<title>My Leave Status</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<h2>My Leave Requests</h2>
<table border="1" cellpadding="5">
    <tr>
        <th>Start Date</th>
        <th>End Date</th>
        <th>Reason</th>
        <th>Status</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()) { ?>
        <tr>
            <td><?= $row['start_date'] ?></td>
            <td><?= $row['end_date'] ?></td>
            <td><?= htmlspecialchars($row['reason']) ?></td>
            <td><?= ucfirst($row['status']) ?></td>
        </tr>
    <?php } ?>
</table>
<p><a href="dashboard.php">Back to Dashboard</a></p>
</body>
</html>
