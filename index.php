<?php
require 'includes/functions.php';
if (is_logged_in()) {
    header('Location: wallet.php'); exit;
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Digital Wallet - Welcome</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
  <div class="container header-hero">
    <div class="card mx-auto" style="max-width:820px;">
      <div class="row g-0 align-items-center">
        <div class="col-md-6 p-4">
          <h1 style="color:var(--accent)">Digital Wallet</h1>
          <p>Fast, simple & secure wallet to track your transactions and merchants.</p>
          <a href="register.php" class="btn btn-custom me-2">Create Account</a>
          <a href="login.php" class="btn btn-outline">Login</a>
        </div>
        <div class="col-md-6 p-4">
          <img src="https://images.unsplash.com/photo-1545239351-1141bd82e8a6?w=800&q=60" alt="wallet" style="width:100%; border-radius:8px;">
        </div>
      </div>
    </div>
  </div>
</body>
</html>
