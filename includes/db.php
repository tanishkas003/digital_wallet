<?php
$DB_HOST = 'sql109.infinityfree.com';
$DB_USER = 'if0_39893542';
$DB_PASS = 'ilovepomerian'; 
$DB_NAME = 'if0_39893542_digital_wallet';

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) {
    die("DB Connection error: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
?>
