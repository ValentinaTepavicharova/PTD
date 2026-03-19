 <?php
session_start(); // Винаги започваме сесията най-отгоре
$wrong = "";
$success = "";

if (isset($_POST['registered']) && $_POST['registered'] == '1') {
    $success = 'Регистрацията е успешна! Моля, влезте.';
}

if (isset($_POST['username']) && isset($_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Твоята проверка (училищна симулация)
    if ($username == 'valentina22105' && $password == 'password') {
        $_SESSION['user'] = $username; // Записваме потребителя в сесията
        // Позволяваме достъп до колелото (чрез cookie)
        setcookie('wheel_allowed', '1', time() + 60 * 60 * 24, '/');
        header("Location: ../profile.php");
        exit;
    } else {
        $wrong = 'Грешно потребителско име или парола';
    }
}
?>
<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход - Гатанки</title>
    <link rel="stylesheet" href="../styles.css"> </head>
<body>
<div class="site">
    <?php include '../include/header.php'; ?> 

    <main>
        <div class="level-card">
            <img src="../images/duck-talisman.png" class="talisman" alt="Пате">
            
            <h2>Вход</h2>
            
            <?php if ($success): ?>
                <div style="color: #10b981; margin-bottom: 10px; font-weight: 600;"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if ($wrong): ?>
                <div style="color: #ef4444; margin-bottom: 10px; font-weight: 600;"><?php echo $wrong; ?></div>
            <?php endif; ?>
        <form action="login.php" method="post">
             <input type="text" name="username" placeholder="Потребителско име" required>
             <input type="password" name="password" placeholder="Парола" required>
            <button type="submit">Влез</button>
        </form>

            <div style="margin-top: 20px;">
                 <a href="../index.php" class="btn" style="background: #64748b;">Отказ</a>
                 <p style="margin-top: 15px; font-size: 0.9em; color: white;">
                    Нямаш акаунт? <a href="../signup/signup.php" style="color: #2563eb;">Регистрирай се</a>
                 </p>
            </div>
        </div>
    </main>

    <?php include '../include/footer.php'; ?>
</div>
</body>
</html>