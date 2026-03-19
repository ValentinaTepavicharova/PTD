 <?php
session_start();
$_SESSION['level'] = 0;   // Връщаме играта отначало
$_SESSION['started'] = false; // Показваме стартовия екран
// НЕ трием $_SESSION['stars'], за да си останат спечелените точки!
header("Location: index.php");
exit;
?>