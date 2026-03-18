<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<header class="custom-header">
  <div class="nav-flex">
    <a href="index.php" class="logo">
       <img src="/PTD/images/logo.png" alt="Logo" style="height: 40px;">
      <span>Гатанки</span>
    </a>
     <ul class="nav">
      <li><a class="nav-link" href="/PTD/index.php">Начало</a></li>
      <li><a class="nav-link" href="/PTD/about/about.php">За проекта</a></li>
      <?php if (isset($_SESSION['user'])): ?>
        <li><a class="nav-link" href="/PTD/profile.php">Профил</a></li>
        <li><a class="nav-link" href="/PTD/logout.php">Изход</a></li>
      <?php else: ?>
        <li><a class="nav-link" href="/PTD/login/login.php">Вход</a></li>
        <li><a class="nav-link" href="/PTD/signup/signup.php">Регистрация</a></li>
      <?php endif; ?>
    </ul>
  </div>
</header>