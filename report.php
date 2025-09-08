<?php
require 'includes/db.php';
require 'includes/functions.php';
require_login();
$userId = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT w.id AS wallet_id FROM wallets w WHERE w.user_id = ?");
$stmt->bind_param('i', $userId);
$stmt->execute();
$walletRow = $stmt->get_result()->fetch_assoc();
$walletId = $walletRow['wallet_id'] ?? null;

$labels = []; $values = [];
if ($walletId) {
    $q = $conn->prepare("
      SELECT m.name, SUM(t.amount) AS total_spent
      FROM transactions t
      JOIN merchants m ON t.merchant_id = m.id
      WHERE t.wallet_id = ? AND t.type = 'Debit'
      GROUP BY m.name
      ORDER BY total_spent DESC
    ");
    $q->bind_param('i', $walletId);
    $q->execute();
    $res = $q->get_result();
    while($r = $res->fetch_assoc()) {
        $labels[] = $r['name'];
        $values[] = (float)$r['total_spent'];
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Spending Report</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<?php include 'includes/navbar.php'; ?>
<div class="container mt-4">
  <div class="card p-3">
    <h4>Spending Report (Debit by Merchant)</h4>
    <canvas id="spendChart"></canvas>
  </div>
</div>
<script>
const labels = <?= json_encode($labels) ?>;
const data = <?= json_encode($values) ?>;
const ctx = document.getElementById('spendChart').getContext('2d');
new Chart(ctx, {
    type: 'pie',
    data: {
        labels: labels,
        datasets: [{
            data: data,
        }]
    },
    options: { responsive: true }
});
</script>
</body>
</html>
