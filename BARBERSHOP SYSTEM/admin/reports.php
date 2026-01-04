<?php include 'guard.php'; require_once __DIR__ . '/../config.php'; ?>
<!doctype html><html><head><meta charset="utf-8"><title>Laporan</title>
<link rel="stylesheet" href="../assets/css/style.css"></head>
<body><header><h2>Laporan</h2></header><div class="container"><div class="card">
<form method="get" class="grid">
  <div><label>Dari</label><input type="date" name="from" required value="<?php echo htmlspecialchars($_GET['from'] ?? date('Y-m-01')); ?>"></div>
  <div><label>Hingga</label><input type="date" name="to" required value="<?php echo htmlspecialchars($_GET['to'] ?? date('Y-m-d')); ?>"></div>
  <div><label>&nbsp;</label><button class="btn">Jana</button></div>
</form>
<?php
$from = $_GET['from'] ?? date('Y-m-01');
$to = $_GET['to'] ?? date('Y-m-d');
$stmt = $pdo->prepare("SELECT DATE(order_time) d, SUM(total_cents) total FROM orders WHERE DATE(order_time) BETWEEN ? AND ? AND status='paid' GROUP BY DATE(order_time) ORDER BY d");
$stmt->execute([$from, $to]);
$rows = $stmt->fetchAll();
echo '<table><tr><th>Tarikh</th><th>Jumlah</th></tr>';
$grand=0;
foreach($rows as $r){ $grand += $r['total']; echo '<tr><td>'.$r['d'].'</td><td>'.money_format_rm($r['total']).'</td></tr>'; }
echo '<tr><th>Jumlah Keseluruhan</th><th>'.money_format_rm($grand).'</th></tr>';
echo '</table>';
?>
</div></div></body></html>
