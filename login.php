<?php
require 'includes/db.php';
require 'includes/functions.php';
if (is_logged_in()) { header('Location: wallet.php'); exit; }

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $err = "Email and password required.";
    } else {
        $stmt = $conn->prepare("SELECT id, name, password_hash FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows === 1) {
            $user = $res->fetch_assoc();
            if (password_verify($password, $user['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                header('Location: wallet.php'); exit;
            } else {
                $err = "Invalid credentials.";
            }
        } else {
            $err = "Invalid credentials.";
        }
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Login - Digital Wallet</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
  <div class="container mt-5">
    <div class="card mx-auto" style="max-width:520px;">
      <div class="p-4">
        <h3>Login</h3>
        <?php if ($err): ?><div class="alert alert-danger"><?= e($err) ?></div><?php endif; ?>
        <?php if($f = get_flash()): ?><div class="alert alert-<?= e($f['type']) ?>"><?= e($f['msg']) ?></div><?php endif; ?>
        <form method="post">
          <div class="mb-2"><input class="form-control" name="email" placeholder="Email" type="email" required></div>
          <div class="mb-2"><input class="form-control" name="password" placeholder="Password" type="password" required></div>
          <button class="btn btn-custom">Login</button>
          <a class="btn btn-outline ms-2" href="register.php">Create account</a>
        </form>
      </div>
    </div>
  </div>
</body>
</html>
