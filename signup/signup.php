 <?php
session_start();
// Променлива, в която ще съхраняваме съобщението за грешка, ако данните са грешни
$wrong = "";

// Проверка дали формата е изпратена
if (isset($_GET['username']) && isset($_GET['password'])) {
    $username = $_GET['username'];
    $password = $_GET['password'];

    // За училищния проект: приемаме само този конкретен потребител
    if ($username == 'valentina22105' && $password == 'password') {
        // Пренасочваме към логин страницата с параметър за успех
        header("Location: ../login/login.php?registered=1");
        exit;
    } else {
        // Ако потребителят въведе нещо друго, показваме съобщение
        $wrong = 'Моля, използвайте валидни данни за регистрация (напр. valentina22105)';
    }
}
?>
<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация - Гатанки</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
<div class="site">
    <?php include '../include/header.php'; ?> 

    <main>
        <div class="level-card">
            <img src="../images/duck-talisman.png" class="talisman" alt="Пате">
            
            <h2>Регистрация</h2>
            
            <?php if ($wrong): ?>
                <div style="color: #ef4444; margin-bottom: 10px; font-weight: 600;">
                    <?php echo $wrong; ?>
                </div>
            <?php endif; ?>

            <form action="signup.php" method="get">
                <input type="text" name="username" placeholder="Избери потребителско име" required>
                <input type="password" name="password" placeholder="Избери парола" required>
                <button type="submit">Регистрирай се</button>
            </form>

            <div style="margin-top: 20px;">
                 <a href="../index.php" class="btn" style="background: #64748b;">Отказ</a>
                 <p style="margin-top: 15px; font-size: 0.9em; color: white;">
                    Вече имаш акаунт? <a href="../login/login.php" style="color: #2563eb;">Влез тук</a>
                 </p>
            </div>
        </div>
    </main>

    <?php include '../include/footer.php'; ?>
</div>
</body>
</html>