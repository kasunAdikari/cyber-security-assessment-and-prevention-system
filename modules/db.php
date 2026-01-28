<?php
// db.php
$dsn = 'mysql:host=localhost;dbname=cyber;charset=utf8mb4';
$user = 'root';
$pass = '';

$options = [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];
try {
  $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
  die("DB connection failed");
}
