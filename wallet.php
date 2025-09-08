<?php
require 'includes/db.php';
require 'includes/functions.php';
require_login();

$userId = $_SESSION['user_id'];

// Fetch wallet
$stmt = $conn->prepare("SELECT id, balance, status FROM wallets WHERE user_id = ?");
$stmt->bind_param('i', $userId);
$stmt->execute();
$wallet = $stmt->get_result()->fetch_assoc();

// Handle add money
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_amount'])) {
    $add = floatval($_POST['add_amount']);
    if ($add <= 0) {
        set_flash("Invalid amount.", "danger");
        header('Location: wallet.php'); exit;
    }
    $stmt2 = $conn->prepare("UPDATE wallets SET balance = balance + ? WHERE id = ?");
    $stmt2->bind_param('di', $add, $wallet['id']);
    if ($stmt2->execute()) {
        set_flash("₹" . number_format($add,2) . " added to wallet.", "success");
    } else {
        set_flash("Failed to add amount.", "danger");
    }
    header('Location: wallet.php'); exit;
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>My Wallet</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<?php include 'includes/navbar.php'; ?>
<div class="container mt-4">
  <?php if($f=get_flash()): ?><div class="alert alert-<?= e($f['type']) ?>"><?= e($f['msg']) ?></div><?php endif; ?>
  <div class="card p-4 mb-4">
    <h3>Hello, <?= e($_SESSION['user_name']) ?></h3>
    <p><strong>Balance:</strong> ₹<?= number_format($wallet['balance'],2) ?> <span style="float:right">Status: <?= e($wallet['status']) ?></span></p>
    <form method="post" class="row g-2">
      <div class="col-md-3"><input class="form-control" name="add_amount" type="number" step="0.01" min="1" placeholder="Enter amount"></div>
      <div class="col-auto"><button class="btn btn-success">Add Money</button></div>
    </form>
  </div>
  <div class="card p-4">
    <h4>Quick actions</h4>
    <a class="btn btn-custom me-2" href="transaction.php">Make Transaction</a>
    <a class="btn btn-outline" href="report.php">Spending Report</a>
  </div>
</div>
</body>
</html>
