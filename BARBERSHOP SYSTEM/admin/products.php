<?php include 'guard.php'; require_once __DIR__ . '/../config.php'; ?>
<!doctype html><html><head><meta charset="utf-8"><title>Produk</title>
<link rel="stylesheet" href="../assets/css/style.css"></head>
<body><header><h2>Produk</h2></header><div class="container"><div class="card">
<form method="post">
  <div class="grid">
    <input name="name" placeholder="Nama produk" required>
    <input name="sku" placeholder="SKU">
    <input type="number" name="price_cents" placeholder="Harga (sen)">
    <input type="number" name="stock_qty" placeholder="Stok">
  </div><br><button class="btn-primary">Tambah</button>
</form>
<?php
if($_SERVER['REQUEST_METHOD']==='POST'){
  $stmt=$pdo->prepare("INSERT INTO products (name,sku,price_cents,stock_qty) VALUES (?,?,?,?)");
  $stmt->execute([$_POST['name'], $_POST['sku'], intval($_POST['price_cents']), intval($_POST['stock_qty'])]);
}
$rows=$pdo->query("SELECT * FROM products ORDER BY id DESC")->fetchAll();
echo '<table><tr><th>Nama</th><th>SKU</th><th>Harga</th><th>Stok</th></tr>';
foreach($rows as $r){
  echo '<tr><td>'.htmlspecialchars($r['name']).'</td><td>'.htmlspecialchars($r['sku']).'</td><td>'.money_format_rm($r['price_cents']).'</td><td>'.$r['stock_qty'].'</td></tr>';
}
echo '</table>';
?>
</div></div></body></html>
