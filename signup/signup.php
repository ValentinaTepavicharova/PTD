 <?php
session_start(); // Винаги започваме сесията най-отгоре
require_once '../include/db.php';

$wrong = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (strlen($username) < 3 || strlen($password) < 4) {
        $wrong = "Потребителското име трябва да е поне 3 символа, а паролата - 4.";
    } else {
        // Проверка дали потребителят вече съществува
        $stmt = $pdo->prepare("SELECT Id FROM Users WHERE Username = ?");
        $stmt->execute([$username]);
        
        if ($stmt->rowCount() > 0) {
            $wrong = "Това потребителско име вече съществува!";
        } else {
            // Хеширане на паролата (много по-сигурно)
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("INSERT INTO Users (Username, Password, Stars, Last_spin_date, Hints_used, Bonus_hints, Skip_levels) VALUES (?, ?, 0, null, 0, 0, 0)");
            if ($stmt->execute([$username, $hashed])) {
                // Автоматично влизане след регистрация
                $_SESSION['user'] = $username;
                $_SESSION['stars'] = 0; // Новите потребители започват с 0 звезди  
                $_SESSION['last_spin_date'] = '';
                $_SESSION['hints_used'] = 0;
                $_SESSION['bonus_hints'] = 0;
                $_SESSION['skip_levels'] = 0;

                setcookie('wheel_allowed', '1', time() + 86400, '/');
                header("Location: ../profile.php");
                exit;
            } else {
                $wrong = "Моля въведете валидни данни.";
            }
        }
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
    <link rel="icon" type="image/x-icon" href="../images/logo.png"> 
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

            <form action="signup.php" method="POST">
                <input type="text" name="username" placeholder="Избери потребителско име" required>
                <div style="position: relative;">
                    <input type="password" id="password" name="password" placeholder="Избери парола" required>
                    <span onclick="togglePassword()" style="position:absolute; right:15px; top:50%; transform:translateY(-50%); cursor:pointer; color:#2563eb;">👁</span>
                </div>
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
<script>
    function togglePassword() {
        const pwd = document.getElementById('password');
        pwd.type = pwd.type === 'password' ? 'text' : 'password';
    }
</script>
</body>
</html>