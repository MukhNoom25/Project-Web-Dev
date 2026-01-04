<?php
session_start();
include "config.php";
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }

$user_id = $_SESSION['user_id'];
$result = $conn->query("SELECT * FROM req_barang WHERE user_id=$user_id ORDER BY id DESC");
?>
<a href="dashboard.php">Back</a>
<h2>My Barang Request Status</h2>
<table>
<tr><th>Item</th><th>Quantity</th><th>Reason</th><th>Status</th></tr>
<?php while($row = $result->fetch_assoc()) { ?>
<tr>
<td><?= $row['item_name'] ?></td>
<td><?= $row['quantity'] ?></td>
<td><?= $row['reason'] ?></td>
<td><?= ucfirst($row['status']) ?></td>
</tr>
<?php } ?>
</table>
