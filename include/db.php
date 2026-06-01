<?php
$host = 'localhost';
$db   = 'ptd_db'; 
$user = 'root';
$pass = '1234'; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Грешка при връзка с БД: " . $e->getMessage());
}
?>