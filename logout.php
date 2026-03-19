<?php
session_start();
// Изчистваме сесията и правим колелото недостъпно
session_unset();
session_destroy();
setcookie('wheel_allowed', '', time() - 3600, '/');
header('Location: index.php');
exit;
