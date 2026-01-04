<?php
include 'guard.php';
require_once __DIR__ . '/../config.php';

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

if (isset($_POST['sync_cart'])) {
  $_SESSION['cart'] = json_decode($_POST['cart'], true) ?? [];
  exit;
}

if (isset($_POST['checkout'])) {
  $method = $_POST['payment_method'] ?? 'cash';
  $cartData = json_decode($_POST['cart'], true) ?? [];
  $total = 0;
  foreach ($cartData as $it) { $total += $it['qty'] * $it['price']; }
  $pdo->beginTransaction();
  $ins = $pdo->prepare("INSERT INTO orders (total_cents, payment_method, status, created_by) VALUES (?, ?, 'paid', ?)");
  $ins->execute([$total, $method, $_SESSION['uid'] ?? null]);
  $order_id = $pdo->lastInsertId();
  $insItem = $pdo->prepare("INSERT INTO order_items (order_id, item_type, item_id, name, qty, unit_price_cents, line_total_cents) VALUES (?, ?, ?, ?, ?, ?, ?)");
  foreach ($cartData as $it) {
    $insItem->execute([$order_id, $it['type'], $it['id'], $it['name'], $it['qty'], $it['price'], $it['qty'] * $it['price']]);
  }
  $pdo->commit();
  $_SESSION['cart'] = [];
  echo json_encode(['success' => true, 'order_id' => $order_id]);
  exit;
}
?>
<!doctype html>
<html>
<head>
<title>POS</title>
<link rel="stylesheet" href="../assets/css/style.css">
<script src="../assets/js/main.js"></script>
<script>
// Initialize cart from session if available
<?php
if (isset($_SESSION['cart'])) {
  echo 'cart = ' . json_encode($_SESSION['cart']) . ';';
  echo 'updateCartDisplay();';
}
?>
</script>
</head>
<body>
<header><h2>Point of Sale</h2></header>
<div class="container">
  <div class="grid">
    <div class="card">
      <h3>Servis</h3>
      <table>
        <tr><th>Nama</th><th>Harga</th><th></th></tr>
        <?php
          $services = $pdo->query("SELECT * FROM services WHERE active=1")->fetchAll();
          foreach($services as $s){
            echo '<tr><td>'.htmlspecialchars($s['name']).'</td><td>'.money_format_rm($s['price_cents']).'</td>
            <td><button class="btn" onclick="addServiceToCart('.$s['id'].', \''.htmlspecialchars($s['name']).'\', '.$s['price_cents'].')">Tambah</button></td></tr>';
          }
        ?>
      </table>
    </div>
    <div class="card">
      <h3>Produk</h3>
      <table>
        <tr><th>Nama</th><th>Harga</th><th>Stok</th><th></th></tr>
        <?php
          $products = $pdo->query("SELECT * FROM products WHERE active=1")->fetchAll();
          foreach($products as $p){
            echo '<tr><td>'.htmlspecialchars($p['name']).'</td><td>'.money_format_rm($p['price_cents']).'</td><td>'.$p['stock_qty'].'</td>
            <td><button class="btn" onclick="addProductToCart('.$p['id'].', \''.htmlspecialchars($p['name']).'\', '.$p['price_cents'].')">Tambah</button></td></tr>';
          }
        ?>
          }
        ?>
      </table>
    </div>
    <div class="card">
      <h3>Troli</h3>
      <table id="cart-table">
        <thead><tr><th>Item</th><th>Qty</th><th>Harga</th><th></th></tr></thead>
        <tbody>
          <!-- Cart items will be populated by JS -->
        </tbody>
        <tfoot><tr><th colspan="3" style="text-align:right;">Total</th><th id="cart-total">RM 0.00</th></tr></tfoot>
      </table>
      <form method="post" style="margin-top:12px;">
        <label>Kaedah Bayaran</label>
        <select name="payment_method">
          <option value="cash">Tunai</option>
          <option value="card">Kad</option>
          <option value="ewallet">E-Wallet/QR</option>
          <option value="transfer">Bank Transfer</option>
        </select>
        <br><br>
        <button class="btn-primary" id="checkout-btn">Checkout</button>
        <button class="btn" onclick="clearCart()">Clear</button>
      </form>
    </div>
  </div>
</div>
<script>
function addServiceToCart(id, name, price) {
  addToCart({id: id, type: "service", name: name, price: price});
  syncCartToServer();
}

function addProductToCart(id, name, price) {
  addToCart({id: id, type: "product", name: name, price: price});
  syncCartToServer();
}

function clearCart() {
  cart = [];
  updateCartDisplay();
  syncCartToServer();
}

function syncCartToServer() {
  ajaxPost('', { 'sync_cart': 1, 'cart': JSON.stringify(cart) }, function(err, response) {
    if (err) {
      showAlert('Error syncing cart.', 'error');
    }
  });
}

document.getElementById('checkout-btn').addEventListener('click', function(e) {
  e.preventDefault();
  if (cart.length === 0) {
    showAlert('Cart is empty.', 'error');
    return;
  }
  const paymentMethod = document.querySelector('select[name="payment_method"]').value;
  showLoading(this);
  ajaxPost('', { 'checkout': 1, 'payment_method': paymentMethod, 'cart': JSON.stringify(cart) }, function(err, response) {
    hideLoading(document.getElementById('checkout-btn'));
    if (err) {
      showAlert('Error during checkout.', 'error');
    } else {
      showAlert('Checkout successful!', 'success');
      cart = [];
      updateCartDisplay();
      syncCartToServer();
    }
  });
});
</script>
</body></html>
