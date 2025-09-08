<?php
require 'includes/db.php';
require 'includes/functions.php';
if (is_logged_in()) { header('Location: wallet.php'); exit; }

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $dob = $_POST['dob'] ?? null;

    if ($name === '' || $email === '' || $phone === '' || $password === '') {
        $errors[] = "All fields except DOB are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email address.";
    } else {
        // Check email or phone exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR phone = ?");
        $stmt->bind_param('ss', $email, $phone);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows > 0) {
            $errors[] = "Email or phone already registered.";
        } else {
            // Insert user + wallet in a DB transaction
            $passHash = password_hash($password, PASSWORD_DEFAULT);
            $conn->begin_transaction();
            try {
                $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password_hash, dob) VALUES (?,?,?,?,?)");
                $stmt->bind_param('sssss', $name, $email, $phone, $passHash, $dob);
                $stmt->execute();
                $userId = $conn->insert_id;

                $stmt2 = $conn->prepare("INSERT INTO wallets (user_id, balance, status) VALUES (?, 0.00, 'Active')");
                $stmt2->bind_param('i', $userId);
                $stmt2->execute();

                $conn->commit();
                set_flash("Registration successful, please log in.", "success");
                header('Location: login.php'); exit;
            } catch (Exception $e) {
                $conn->rollback();
                $errors[] = "Registration failed: " . $e->getMessage();
            }
        }
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Register - Digital Wallet</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
  <div class="container mt-5">
    <div class="card mx-auto" style="max-width:720px;">
      <div class="p-4">
        <h3>Create Account</h3>
        <?php if($err = get_flash()): ?>
          <div class="alert alert-<?= e($err['type']) ?>"><?= e($err['msg']) ?></div>
        <?php endif; ?>
        <?php if ($errors): ?>
          <div class="alert alert-danger">
            <?php foreach ($errors as $er) echo '<div>' . e($er) . '</div>'; ?>
          </div>
        <?php endif; ?>
        <form method="post" novalidate>
          <div class="row g-2">
            <div class="col-md-6"><input class="form-control" name="name" placeholder="Full name" required></div>
            <div class="col-md-6"><input class="form-control" name="email" placeholder="Email" type="email" required></div>
            <div class="col-md-6"><input class="form-control" name="phone" placeholder="Phone" required></div>
            <div class="col-md-6"><input class="form-control" name="password" placeholder="Password" type="password" required></div>
            <div class="col-md-6"><input class="form-control" name="dob" type="date" placeholder="DOB"></div>
          </div>
          <div class="mt-3">
            <button class="btn btn-custom">Register</button>
            <a href="login.php" class="btn btn-outline ms-2">Already have account? Login</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</body>
</html>
