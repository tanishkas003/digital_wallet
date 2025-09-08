<?php
// includes/navbar.php
if(session_status() == PHP_SESSION_NONE) session_start();
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand" href="wallet.php">Digital Wallet</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
            data-bs-target="#navMenu" aria-controls="navMenu" aria-expanded="false">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMenu">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="wallet.php">Wallet</a></li>
        <li class="nav-item"><a class="nav-link" href="transaction.php">Transactions</a></li>
        <li class="nav-item"><a class="nav-link" href="report.php">Report</a></li>
        <li class="nav-item"><a class="nav-link" href="merchants.php">Merchants</a></li>
        <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
      </ul>
    </div>
  </div>
</nav>
