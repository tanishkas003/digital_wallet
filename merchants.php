<?php
require 'includes/db.php';
require 'includes/functions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mname'])) {
    $name = trim($_POST['mname']);
    $type = trim($_POST['mbtype']);
    if ($name !== '') {
        $stmt = $conn->prepare("INSERT INTO merchants (name, business_type) VALUES (?, ?)");
        $stmt->bind_param('ss', $name, $type);
        $stmt->execute();
        set_flash("Merchant added.", "success");
        header('Location: merchants.php'); exit;
    } else {
        set_flash("Merchant name required.", "danger");
        header('Location: merchants.php'); exit;
    }
}

$ms = $conn->query("SELECT * FROM merchants ORDER BY name");
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Merchants</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<?php include 'includes/navbar.php'; ?>
<div class="container mt-4">
  <?php if($f=get_flash()): ?><div class="alert alert-<?= e($f['type']) ?>"><?= e($f['msg']) ?></div><?php endif; ?>
  <div class="card p-3 mb-3">
    <h4>Add Merchant</h4>
    <form method="post" class="row g-2">
      <div class="col-md-5"><input class="form-control" name="mname" placeholder="Merchant name" required></div>
      <div class="col-md-5"><input class="form-control" name="mbtype" placeholder="Business type"></div>
      <div class="col-md-2"><button class="btn btn-custom">Add</button></div>
    </form>
  </div>

  <div class="card p-3">
    <h4>All Merchants</h4>
    <ul class="list-group">
      <?php while($m = $ms->fetch_assoc()): ?>
        <li class="list-group-item"><?= e($m['name']) ?> <small class="text-muted"> - <?= e($m['business_type']) ?></small></li>
      <?php endwhile; ?>
    </ul>
  </div>
</div>
</body>
</html>
