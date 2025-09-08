<?php
require 'includes/db.php';
require 'includes/functions.php';
require_login();

$userId = $_SESSION['user_id'];

// Fetch wallet
$stmt = $conn->prepare("SELECT id, balance FROM wallets WHERE user_id = ?");
$stmt->bind_param('i', $userId);
$stmt->execute();
$wallet = $stmt->get_result()->fetch_assoc();
$walletId = $wallet['id'];

// Fetch merchants
$merchantStmt = $conn->prepare("SELECT id, name, business_type FROM merchants ORDER BY name");
$merchantStmt->execute();
$merchants = $merchantStmt->get_result();

// Handle transaction POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['merchant_id'])) {
    $merchant_id = intval($_POST['merchant_id']);
    $type = $_POST['type'] === 'Credit' ? 'Credit' : 'Debit';
    $amount = floatval($_POST['amount']);
    $description = trim($_POST['description'] ?? '');

    if ($amount <= 0) {
        set_flash("Enter a valid amount.", "danger");
        header('Location: transaction.php'); exit;
    }

    // Check merchant exists
    $s = $conn->prepare("SELECT id FROM merchants WHERE id=?");
    $s->bind_param('i',$merchant_id);
    $s->execute();
    if ($s->get_result()->num_rows === 0) {
        set_flash("Invalid merchant selected.", "danger");
        header('Location: transaction.php'); exit;
    }

    // Perform DB transaction with row lock
    $conn->begin_transaction();
    try {
        // Lock wallet row for update
        $s2 = $conn->prepare("SELECT balance FROM wallets WHERE id = ? FOR UPDATE");
        $s2->bind_param('i', $walletId);
        $s2->execute();
        $row = $s2->get_result()->fetch_assoc();
        $currentBalance = floatval($row['balance']);

        if ($type === 'Debit' && $amount > $currentBalance) {
            $conn->rollback();
            set_flash("Insufficient balance.", "danger");
            header('Location: transaction.php'); exit;
        }

        // Insert transaction
        $s3 = $conn->prepare("INSERT INTO transactions (wallet_id, merchant_id, amount, type, description) VALUES (?, ?, ?, ?, ?)");
        $s3->bind_param('iidds', $walletId, $merchant_id, $amount, $type, $description); // types: i i d s s -> 'iidss' (note below)
        // Correction of types: wallet_id (i), merchant_id (i), amount (d), type (s), description (s) => 'iidss'
        // But PHP accepts the string, so ensure 'iidss' is used instead of 'iidds'. We will call with 'iidss' below.
        // Unfortunately `bind_param` above used wrong type string. We'll create correct bind below properly.
        // To keep it simple and robust we will re-prepare and bind correctly:
        $s3 = $conn->prepare("INSERT INTO transactions (wallet_id, merchant_id, amount, type, description) VALUES (?, ?, ?, ?, ?)");
        $s3->bind_param('iidss', $walletId, $merchant_id, $amount, $type, $description);

        $s3->execute();

        // Update wallet balance
        if ($type === 'Debit') {
            $newBalance = $currentBalance - $amount;
        } else {
            $newBalance = $currentBalance + $amount;
        }
        $s4 = $conn->prepare("UPDATE wallets SET balance = ? WHERE id = ?");
        $s4->bind_param('di', $newBalance, $walletId);
        $s4->execute();

        $conn->commit();
        set_flash("Transaction successful.", "success");
        header('Location: transaction.php'); exit;
    } catch (Exception $ex) {
        $conn->rollback();
        set_flash("Transaction failed: " . $ex->getMessage(), "danger");
        header('Location: transaction.php'); exit;
    }
}

// Filters: type & date (GET)
$typeFilter = $_GET['type'] ?? '';
$dateFilter = $_GET['date'] ?? '';

// Build query with safe escaping for filters
$sql = "SELECT t.id, t.amount, t.type, t.description, t.created_at, m.name AS merchant_name
        FROM transactions t
        JOIN merchants m ON t.merchant_id = m.id
        WHERE t.wallet_id = ? ";
$params = [$walletId];
$types = "i";

if ($typeFilter === 'Debit' || $typeFilter === 'Credit') {
    $sql .= " AND t.type = ? ";
    $types .= "s";
    $params[] = $typeFilter;
}
if (!empty($dateFilter)) { // expect YYYY-MM-DD
    $sql .= " AND DATE(t.created_at) = ? ";
    $types .= "s";
    $params[] = $dateFilter;
}
$sql .= " ORDER BY t.created_at DESC";

$stmt = $conn->prepare($sql);
if ($types === "i") {
    $stmt->bind_param('i', $params[0]);
} else {
    // dynamic bind
    $bind_names[] = $types;
    for ($i=0; $i<count($params); $i++) {
        $bind_name = 'bind' . $i;
        $$bind_name = $params[$i];
        $bind_names[] = &$$bind_name;
    }
    call_user_func_array([$stmt, 'bind_param'], $bind_names);
}
$stmt->execute();
$transactions = $stmt->get_result();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Transactions</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<?php include 'includes/navbar.php'; ?>
<div class="container mt-4">
  <?php if($f=get_flash()): ?><div class="alert alert-<?= e($f['type']) ?>"><?= e($f['msg']) ?></div><?php endif; ?>

  <div class="card p-3 mb-4">
    <h4>Make Transaction</h4>
    <form method="post" class="row g-2">
      <div class="col-md-4">
        <select class="form-control" name="merchant_id" required>
          <?php while($m = $merchants->fetch_assoc()): ?>
            <option value="<?= e($m['id']) ?>"><?= e($m['name']) ?> (<?= e($m['business_type']) ?>)</option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="col-md-2">
        <select class="form-control" name="type" required>
          <option value="Debit">Debit</option>
          <option value="Credit">Credit</option>
        </select>
      </div>
      <div class="col-md-3"><input class="form-control" type="number" min="1" step="0.01" name="amount" placeholder="Amount" required></div>
      <div class="col-md-3"><input class="form-control" name="description" placeholder="Description (optional)"></div>
      <div class="col-md-12 mt-2"><button class="btn btn-custom">Submit Transaction</button></div>
    </form>
  </div>

  <div class="card p-3 mb-3">
    <h4>Filters</h4>
    <form method="get" class="row g-2">
      <div class="col-md-3">
        <select name="type" class="form-control">
          <option value="">All Types</option>
          <option value="Debit" <?= $typeFilter === 'Debit' ? 'selected' : '' ?>>Debit</option>
          <option value="Credit" <?= $typeFilter === 'Credit' ? 'selected' : '' ?>>Credit</option>
        </select>
      </div>
      <div class="col-md-3"><input type="date" name="date" class="form-control" value="<?= e($dateFilter) ?>"></div>
      <div class="col-auto"><button class="btn btn-dark">Apply</button></div>
    </form>
  </div>

  <div class="card p-3">
    <h4>Transaction History (Wallet #<?= e($walletId) ?>)</h4>
    <table class="table">
      <thead>
        <tr><th>ID</th><th>Merchant</th><th>Amount</th><th>Type</th><th>Description</th><th>Date</th></tr>
      </thead>
      <tbody>
        <?php while($t = $transactions->fetch_assoc()): ?>
          <tr>
            <td><?= e($t['id']) ?></td>
            <td><?= e($t['merchant_name']) ?></td>
            <td>₹<?= number_format($t['amount'],2) ?></td>
            <td><?= $t['type'] === 'Debit' ? "<span class='badge-debit'>Debit</span>" : "<span class='badge-credit'>Credit</span>" ?></td>
            <td><?= e($t['description']) ?></td>
            <td><?= e($t['created_at']) ?></td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

</div>
</body>
</html>
