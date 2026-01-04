<?php include 'guard.php'; require_once __DIR__ . '/../config.php'; ?>
<!doctype html><html><head><meta charset="utf-8"><title>Servis</title>
<link rel="stylesheet" href="../assets/css/style.css"></head>
<body><header><h2>Servis</h2></header><div class="container"><div class="card">
<form method="post">
  <div class="grid">
    <input name="name" placeholder="Nama servis" required>
    <input type="number" name="duration_min" placeholder="Durasi (min)" value="30" required>
    <input type="number" name="price_cents" placeholder="Harga (sen, cth 1500=RM15.00)" required>
  </div><br><button class="btn-primary">Tambah</button>
</form>
<?php
if($_SERVER['REQUEST_METHOD']==='POST'){
  $stmt=$pdo->prepare("INSERT INTO services (name,duration_min,price_cents) VALUES (?,?,?)");
  $stmt->execute([$_POST['name'], intval($_POST['duration_min']), intval($_POST['price_cents'])]);
}
$rows=$pdo->query("SELECT * FROM services ORDER BY id DESC")->fetchAll();
echo '<table><tr><th>Nama</th><th>Durasi</th><th>Harga</th><th>Status</th></tr>';
foreach($rows as $r){
  echo '<tr><td>'.htmlspecialchars($r['name']).'</td><td>'.$r['duration_min'].' min</td><td>'.money_format_rm($r['price_cents']).'</td><td>'.($r['active']?'Aktif':'Nyahaktif').'</td></tr>';
}
echo '</table>';
?>
</div></div></body></html>
