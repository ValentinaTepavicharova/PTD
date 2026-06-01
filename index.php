 <?php
 // Стартираме сесията, за да можем да помним точките и нивата на играча, докато той превключва страниците
session_start();
require_once 'include/db.php';

// Зареждане на данни от базата, ако потребителят е влязъл
if (isset($_SESSION['user'])) {
    $stmt = $pdo->prepare("SELECT Current_level, Stars, Skip_levels, Hints_used, Bonus_hints FROM Users WHERE Username = ?");
    $stmt->execute([$_SESSION['user']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        $_SESSION['level'] = (int)$user['Current_level'];
        $_SESSION['stars'] = (int)$user['Stars'];
        $_SESSION['skip_levels'] = (int)$user['Skip_levels'];
        $_SESSION['hints_used'] = (int)$user['Hints_used'];
        $_SESSION['bonus_hints'] = (int)$user['Bonus_hints'];
    }
}

// --- ЛОГИКА ЗА ТОЧКИ И ЖОКЕРИ ---

if (isset($_SESSION['user'])) {
    $_SESSION['started'] = true; // влезлите потребители винаги виждат играта
} elseif (!isset($_SESSION['started'])) {
    $_SESSION['started'] = false; // гости: само ако не са стартирали играта, показваме началния екран
}


if (!isset($_SESSION['level'])) {
    $_SESSION['level'] = 0; // Започваме от първото ниво (индекс 0)
}

// Ако потребителят не е влязъл, даваме 0 точки по подразбиране
if (!isset($_SESSION['stars'])) {
    $_SESSION['stars'] = 0;
}
// Нов брояч: колко пъти общо са използвани жокери в цялата игра
if (!isset($_SESSION['hints_used'])) {
    $_SESSION['hints_used'] = 0;
}

if (!isset($_SESSION['skip_levels'])) {
    $_SESSION['skip_levels']= 0;
}

if (!isset($_SESSION['bonus_hints'])) {
    $_SESSION['bonus_hints']= 0;
}
 
// Проверяваме дали нивото в сесията отговаря на това, за което е бил активиран жокера
if (!isset($_SESSION['hint_active_for_level']) || $_SESSION['hint_active_for_level'] != $_SESSION['level']) {
    $_SESSION['hint_active'] = false;
    $_SESSION['hint_active_for_level'] = null;
}

require_once 'include/riddles.php';


$level = $_SESSION['level'];
$result = '';// Пази съобщението за верен/грешен отговор
$hintError = ''; // Променлива за грешка, ако няма точки за жокер

//Функция за синхронизиране с базата
function syncWithDatabase($pdo) {
    if (isset($_SESSION['user'])) {
        $stmt = $pdo->prepare("UPDATE Users SET Current_level = ?, Stars = ?, Skip_levels = ?, Hints_used = ?, Bonus_hints = ? WHERE Username = ?");
        $stmt->execute([
            $_SESSION['level'],
            $_SESSION['stars'],
            $_SESSION['skip_levels'],
            $_SESSION['hints_used'],
            $_SESSION['bonus_hints'],
            $_SESSION['user']
        ]);
    }
}

// Логика за старт на играта
if (isset($_POST['start'])) {
    $_SESSION['started'] = true;
    $_SESSION['level'] = 0;
    $_SESSION['hints_used'] = 0;
    $_SESSION['bonus_hints'] = 0;
    $_SESSION['hint_active'] = false;
    $_SESSION['stars'] = isset($_SESSION['user']) ? $_SESSION['stars'] : 0; // Ако е гост, започваме с 0 точки
    $_SESSION['hint_active_for_level'] = null;

    if (isset($_SESSION['user'])) {
        $stmt= $pdo->prepare("UPDATE Users SET Current_level = ?, Hints_used = ?, Bonus_hints = ? WHERE Username = ?");
        $stmt->execute([$_SESSION['user']]);
    }else {
        // За гости: нулираме точките и нивото
        $_SESSION['stars'] = 0;
        $_SESSION['level'] = 0;
    }
    header("Location: index.php");
    exit;
}
// Проверка дали сме минали всички гатанки
$finished = $level >= count($riddles);

// ПРОПУСК НА НИВО 
if (!$finished && isset($_POST['skip'])) {
    // Проверка за пропуски (от колелото)
    if ($_SESSION['skip_levels'] > 0) {
        $_SESSION['skip_levels']--;
        $_SESSION['level']++;
        $_SESSION['hint_active'] = false; // Деактивираме жокера при пропуск
        $_SESSION['hint_active_for_level'] = null;
        $level = $_SESSION['level'];
        syncWithDatabase($pdo); // Синхронизираме с базата след пропуск
        header("Location: index.php");
        exit;
    } else {
        $result = '⚠️ Нямате налични пропуски! Спечелете от колелото на профила.';
    }
}

// ЖОКЕР 
$showHint = false;

if (isset($_GET['hint']) && !$_SESSION['hint_active']) {
    $totalFreeHints = 3 + ($_SESSION['bonus_hints']);
    // Проверяваме дали вече сме използвали безплатните (3 броя)
    if ($_SESSION['hints_used'] < $totalFreeHints) {
        $_SESSION['hints_used']++;
        $_SESSION['hint_active'] = true; // Активираме жокера за текущото ниво
        $_SESSION['hint_active_for_level'] = $_SESSION['level']; // Запомняме за кое ниво е активиран жокера
        $showHint = true;
        syncWithDatabase($pdo); // Синхронизираме с базата след използване на жокер
    } else {
        // Ако са свършили безплатните, проверяваме дали имаме 5 точки
        if ($_SESSION['stars'] >= 5) {
            $_SESSION['stars'] -= 5; // Вземаме 5 точки
            $_SESSION['hints_used']++;
            $_SESSION['hint_active'] = true; // Активираме жокера за текущото ниво
            $_SESSION['hint_active_for_level'] = $_SESSION['level']; // Запомняме за кое ниво е активиран жокера
            $showHint = true;
            syncWithDatabase($pdo); // Синхронизираме с базата след използване на жокер
        } else {
            // Ако няма точки, не показваме жокера и даваме грешка
            $hintError = "⚠️ Нужни са 5 точки за жокер!";
        }
    }
     // След активиране на жокера, премахваме GET параметъра от URL-то
    if ($showHint) {
        header("Location: " . strtok($_SERVER['REQUEST_URI'], '?'));
        exit;
    }
}

// АКО има активен жокер от предишно зареждане
if (isset($_SESSION['hint_active']) && $_SESSION['hint_active_for_level'] === $_SESSION['level']) {
    $showHint = true;
}

// ОТГОВОР 
if (!$finished && isset($_POST['answer'])) {
    $userAnswer = mb_strtolower(trim($_POST['answer']));
    // mb_strtolower прави текста малък (за да няма значение дали пишеш "Дърво" или "дърво")
    // trim премахва излишни интервали в началото или края
    if ($userAnswer === $riddles[$level]['answer']) {
        $_SESSION['stars'] += 10;
        $_SESSION['level']++;
        $_SESSION['hint_active'] = false; // Деактивираме жокера при правилен отговор
        $_SESSION['hint_active_for_level'] = null;
        syncWithDatabase($pdo); // Синхронизираме с базата след правилен отговор

        header("Location: index.php");
        exit;
    } else {
        $result = '❌ Грешен отговор!';
    }
}

// Обновяваме $level и $finished след евентуален пропуск
$level    = $_SESSION['level'];
$finished = $level >= count($riddles);
 
// Изчисляваме оставащите жокери за показване
$totalFreeHints = 3 + $_SESSION['bonus_hints'];
$hintsUsed      = $_SESSION['hints_used'];
$freeLeft       = max(0, $totalFreeHints - $hintsUsed);
?>
<!DOCTYPE html>
<html lang="bg">
<?php include 'include/head.php'; ?>
<body>
<div class="site">
<?php include 'include/header.php'; ?>

<main>

<?php if (!$_SESSION['started']): ?>
    <div class="level-card">
        <img src="images/duck-talisman.png" class="talisman" alt="Пате">
        <h2>🧠 Гатанки</h2>
        <p>Готов ли си да тестваш ума си?</p>
        <form method="post">
            <button name="start">▶ Старт</button>
        </form>
    </div>

<?php elseif ($finished): ?>
    <div class="level-card">
        <img src="images/duck-talisman.png" class="talisman" alt="Пате">
        <h2>🏁 Край на играта</h2>
        <p>Общ резултат:</p>
        <h3>⭐ <?= $_SESSION['stars'] ?> точки</h3>
        <a href="reset.php" class="btn">🔄 Играй отново</a>
    </div>

<?php else: ?>
    <div class="level-card">
        <img src="images/duck-talisman.png" class="talisman" alt="Пате">
        <h2>Ниво <?= $level + 1 ?></h2>
        <p>⭐ Точки: <?= $_SESSION['stars'] ?></p>
        <small style="display:block; margin-bottom:4px; color: white;">
            <?php if ($freeLeft > 0): ?>
                🎁 Остават <?= $freeLeft ?> безплатни жокера
            <?php else: ?>
                💰 Жокерите вече струват 5 точки
            <?php endif; ?>
        </small>
        
          <?php if (isset($_SESSION['user'])): ?>
        <small style="display:block; margin-bottom:10px; color: white;">
            ⏭️ Налични пропуски: <?= $_SESSION['skip_levels'] ?>
        </small>
        <?php endif; ?>

        <p class="riddle-text"><?= $riddles[$level]['text'] ?></p>

        <form method="post">
            <input type="text" name="answer" placeholder="Твоят отговор" required autocomplete="off">
            <button>Провери</button>
        </form>

        <?php if ($result): ?>
            <div class="result"><?= $result ?></div>
        <?php endif; ?>

        <?php if ($hintError): ?>
            <div class="result" style="color: #ffeb3b;"><?= $hintError ?></div>
        <?php endif; ?>

        <?php if (!$showHint): ?>
            <a href="?hint=1" class="btn hint-btn">
                <?= $freeLeft > 0 ? "Жокер (Безплатен)" : "Жокер (5 точки)" ?>
            </a>
        <?php else: ?>
            <div class="hint-wrapper">
                <img src="<?= $riddles[$level]['image'] ?>" class="hint-img">
                <p><small>(Жокерът е активиран)</small></p>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['user'])): ?>
        <form method="post" style="margin-top:10px;">
            <button name="skip" class="btn" style="background: linear-gradient(135deg,#e67e22,#d35400);">
                ⏭️ Пропусни нивото (<?= $_SESSION['skip_levels'] ?> налични)
            </button>
        </form>
        <?php endif; ?>
    </div>
<?php endif; ?>
</main>

<?php include 'include/footer.php'; ?>
</div>
</body>
</html>