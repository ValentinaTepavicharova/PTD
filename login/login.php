 <?php
session_start(); // Винаги започваме сесията най-отгоре
require_once '../include/db.php'; // Включваме връзката с базата данни

$wrong = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM Users WHERE Username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['Password'])) {
        $_SESSION['user'] = $user['Username'];
        $_SESSION['stars'] = (int)$user['Stars'];
        $_SESSION['last_spin_date'] = $user['Last_spin_date'] ?? '';
        
        setcookie('wheel_allowed', '1', time() + 86400, '/');
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
    <link rel="stylesheet" href="../styles.css">
    <link rel="icon" type="image/x-icon" href="../images/logo.png">
</head>
<body>
<div class="site">
    <?php include '../include/header.php'; ?> 

    <main>
        <div class="level-card">
            <img src="../images/duck-talisman.png" class="talisman" alt="Пате">
            
            <h2>Вход</h2>
            
            <?php if ($wrong): ?>
                <div style="color: #ef4444; margin-bottom: 10px; font-weight: 600;"><?php echo $wrong; ?></div>
            <?php endif; ?>
            <form action="login.php" method="POST">
                <input type="text" name="username" placeholder="Потребителско име" required>
                <div style="position: relative;">
                    <input type="password" id="password" name="password" placeholder="Парола" required>
                    <span onclick="togglePassword()" style="position:absolute; right:15px; top:50%; transform:translateY(-50%); cursor:pointer; color:#2563eb;">👁</span>
                </div>
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
<script>
    function togglePassword() {
        const pwd = document.getElementById('password');
        pwd.type = pwd.type === 'password' ? 'text' : 'password';
    }
</script>
</body>
</html>