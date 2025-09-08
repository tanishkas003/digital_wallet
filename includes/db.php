<?php
// includes/db.php
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = ''; // XAMPP default
$DB_NAME = 'digital_wallet';

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) {
    die("DB Connection error: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
?>
