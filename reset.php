<?php
session_start();
require_once 'include/db.php';

if (isset($_SESSION['user'])) {
    $stmt = $pdo->prepare("UPDATE Users SET Current_level = 0, Stars = 0, Hints_used = 0, Bonus_hints = 0 WHERE Username = ?");
    $stmt->execute([$_SESSION['user']]);
}
session_unset(); // Изчища данните в сесията
session_destroy(); // Унищожава сесията
header("Location: index.php");
exit;
?>