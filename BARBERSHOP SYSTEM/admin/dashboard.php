<?php include 'guard.php'; require_once __DIR__ . '/../config.php'; ?>
<!doctype html><html><head><meta charset="utf-8"><title>Dashboard</title>
<link rel="stylesheet" href="../assets/css/style.css"></head>
<body>
<header><h2>Dashboard — Hai, <?php echo htmlspecialchars($_SESSION['name']); ?></h2></header>
<div class="container">
  <div class="grid">
    <div class="card">
      <h3>Tempahan Hari Ini</h3>
      <?php
      $rows = $pdo->prepare("SELECT b.*, s.name AS service_name, st.name AS staff_name FROM bookings b 
        LEFT JOIN services s ON s.id=b.service_id 
        LEFT JOIN staff st ON st.id=b.staff_id 
        WHERE DATE(b.start_time)=CURDATE() ORDER BY b.start_time ASC");
      $rows->execute();
      $rows = $rows->fetchAll();
      if(!$rows){ echo "<p>Tiada tempahan.</p>"; }
      else {
        echo "<table><tr><th>Masa</th><th>Pelanggan</th><th>Barber</th><th>Servis</th><th>Status</th></tr>";
        foreach($rows as $r){
          echo "<tr><td>{$r['start_time']}</td><td>".htmlspecialchars($r['customer_name'])."</td><td>".htmlspecialchars($r['staff_name'])."</td><td>".htmlspecialchars($r['service_name'])."</td><td>{$r['status']}</td></tr>";
        }
        echo "</table>";
      }
      ?>
    </div>
    <div class="card">
      <h3>Jualan Hari Ini</h3>
      <?php
      $sum = $pdo->query("SELECT COALESCE(SUM(total_cents),0) AS total FROM orders WHERE DATE(order_time)=CURDATE() AND status='paid'")->fetch();
      echo "<p><strong>Jumlah:</strong> " . money_format_rm($sum['total']) . "</p>";
      ?>
      <a class="btn" href="pos.php">Buka POS</a>
    </div>
  </div>

  <div class="card">
    <h3>Pintasan</h3>
    <a class="btn" href="bookings.php">Tempahan</a>
    <a class="btn" href="services.php">Servis</a>
    <a class="btn" href="staff.php">Kakitangan</a>
    <a class="btn" href="products.php">Produk</a>
    <a class="btn" href="reports.php">Laporan</a>
  </div>
</div>
</body></html>
