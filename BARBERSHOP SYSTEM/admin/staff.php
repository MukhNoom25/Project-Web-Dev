<?php include 'guard.php'; require_once __DIR__ . '/../config.php'; ?>
<!doctype html><html><head><meta charset="utf-8"><title>Staff</title>
<link rel="stylesheet" href="../assets/css/style.css"></head>
<body><header><h2>Kakitangan</h2></header><div class="container"><div class="card">
<form method="post">
  <div class="grid">
    <input name="name" placeholder="Nama" required>
    <input name="phone" placeholder="No. Telefon">
  </div><br><button class="btn-primary">Tambah</button>
</form>
<?php
if($_SERVER['REQUEST_METHOD']==='POST'){
  $stmt=$pdo->prepare("INSERT INTO staff (name,phone) VALUES (?,?)");
  $stmt->execute([$_POST['name'], $_POST['phone']]);
}
$rows=$pdo->query("SELECT * FROM staff ORDER BY id DESC")->fetchAll();
echo '<table><tr><th>Nama</th><th>Telefon</th><th>Status</th></tr>';
foreach($rows as $r){
  echo '<tr><td>'.htmlspecialchars($r['name']).'</td><td>'.htmlspecialchars($r['phone']).'</td><td>'.($r['active']?'Aktif':'Nyahaktif').'</td></tr>';
}
echo '</table>';
?>
</div></div></body></html>
