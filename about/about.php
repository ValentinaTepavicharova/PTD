 <!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles.css"> 
    <title>За проекта - Гатанки</title>
</head>
<body>
<div class="site">
    <?php include '../include/header.php'; ?> 

    <main>
        <div class="level-card">
            <img src="../images/duck-talisman.png" class="talisman" alt="Пате">

            <h2 style="color: #ffffff; text-shadow: 0 2px 4px rgba(0,0,0,0.2);">За проекта</h2>
            
            <p style="margin: 20px 0; font-size: 18px; line-height: 1.6; color: #ffffff;">
                Проектът представлява интерактивна уеб игра, в която потребителят 
                отговаря на гатанки и получава точки за верни отговори.
            </p>
            
            <p style="color: rgba(255,255,255,0.8); font-size: 14px; margin-bottom: 30px;">
                Това е училищен проект. Основният потребител е ученик или любител на загадки, 
                който иска да се забавлява.
            </p>

            <a href="../index.php" class="btn" style="background: rgba(255,255,255,0.2); backdrop-filter: blur(5px); border: 1px solid rgba(255,255,255,0.3); padding: 12px 25px; color: white; border-radius: 15px; text-decoration: none; font-weight: bold; transition: 0.3s;">
                🏠 Към началото
            </a>
        </div>
    </main>

    <?php include '../include/footer.php'; ?>
</div>
</body>
</html>