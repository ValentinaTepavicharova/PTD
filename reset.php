 <?php
session_start();
session_unset(); // Изчиства данните в сесията
session_destroy(); // Унищожава сесията
header("Location: index.php");
exit;
?>