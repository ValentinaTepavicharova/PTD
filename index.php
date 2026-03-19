 <?php
 // Стартираме сесията, за да можем да помним точките и нивата на играча, докато той превключва страниците
session_start();

// --- ЛОГИКА ЗА ТОЧКИ И ЖОКЕРИ ---
if (!isset($_SESSION['started'])) {
    $_SESSION['started'] = false;// Показва дали играчът е натиснал бутона "Старт"
}

if (!isset($_SESSION['level'])) {
    $_SESSION['level'] = 0; // Започваме от първото ниво (индекс 0)
}

// Първоначално даваме 0 точки
if (!isset($_SESSION['points'])) {
    $_SESSION['points'] = 0;
}

// Нов брояч: колко пъти общо са използвани жокери в цялата игра
if (!isset($_SESSION['hints_used'])) {
    $_SESSION['hints_used'] = 0;
}
// Всеки елемент съдържа текст, верен отговор и път до снимка-жокер
$riddles = [
    ['text'=>'Имам корона, но не съм цар. Какво съм?', 'answer'=>'дърво', 'image'=>'images/image1.jpg'],
    ['text'=>'Лети без крила и плаче без очи. Какво е?', 'answer'=>'облак', 'image'=>'images/image2.jpg'],
    ['text'=>'Имам пера, но не съм птица. Имам чаршафи, но не съм легло.', 'answer'=>'възглавница', 'image'=>'images/image3.jpg'],
    ['text'=>'Колкото повече застарявам, толкова по-ниска ставам.', 'answer'=>'свещ', 'image'=>'images/image4.jpg'],
    ['text'=>'Имам зъби, но не мога да ям.', 'answer'=>'гребен', 'image'=>'images/image5.jpg'],
    ['text'=>'Крака няма,а тича. Уста няма,а шуми.', 'answer'=>'река', 'image'=>'images/image6.jpg'],
    ['text'=>'Падам, но никога не се наранявам.', 'answer'=>'дъжд', 'image'=>'images/image2.jpg'],
    ['text'=>'Колкото повече ме сушиш, толкова по-мокра ставам.', 'answer'=>'кърпа', 'image'=>'images/image8.jpg'],
    ['text'=>'Имам лице, но нямам очи. Имам стрелки, но нямам лък.', 'answer'=>'часовник', 'image'=>'images/image9.jpg'],
    ['text'=>'Имам градове, но няма къщи. Имам планини, но няма дървета.', 'answer'=>'карта', 'image'=>'images/image10.jpg'],
    ['text'=>'Винаги идвам, но никога не пристигам днес.', 'answer'=>'утре', 'image'=>'images/image11.jpg'],
    ['text'=>'Какво се пълни с празни ръце?', 'answer'=>'ръкавица', 'image'=>'images/image12.jpg'],
    ['text'=>'Колкото повече вземаш от мен, толкова по-голяма ставам.', 'answer'=>'дупка', 'image'=>'images/image13.jpg'],
    ['text'=>'Какво принадлежи на теб, но другите го ползват по-често? ', 'answer'=>'името', 'image'=>'images/image14.jpg'],
    ['text'=>'Какво можеш да го хванеш,но не и да хвърлиш?', 'answer'=>'болест', 'image'=>'images/image15.jpg'],
    ['text'=>'Какво се чупи, дори само ако му кажеш името?', 'answer'=>'тишина', 'image'=>'images/image16.jpg'],
    ['text'=>'Кое е това, което го хвърляш, когато ти трябва, и го прибираш, когато не ти трябва?', 'answer'=>'котва', 'image'=>'images/image17.jpg'],
    ['text'=>'Мога да запълня цяла стая, но не заемам никакво място.', 'answer'=>'светлина', 'image'=>'images/image18.jpg'],
    ['text'=>'Нямам глас, но ти отговарям винаги, когато ми говориш.', 'answer'=>'ехото', 'image'=>'images/image19.jpg'],
    ['text'=>'Колкото повече от него има, толкова по-малко виждаш.', 'answer'=>'мъгла', 'image'=>'images/image20.jpg'],
    ['text'=>'Винаги бяга от теб, но не можеш да го изпревариш.', 'answer'=>'хоризонт', 'image'=>'images/image21.jpg'],
    ['text'=>'Аз съм лек като перце, но и най-силният човек не може да ме държи дълго.', 'answer'=>'дъх', 'image'=>'images/image22.jpg'],
    ['text'=>'Какво има една дупка, когато влизаш, и две, когато излизаш?', 'answer'=>'панталони', 'image'=>'images/image23.jpg'],
    ['text'=>'Кое е това нещо, което се мокри, докато те пази от дъжда?', 'answer'=>'чадър', 'image'=>'images/image24.jpg'],
    ['text'=>'Кое е това, което минава през градове и полета, но никога не се движи?', 'answer'=>'път', 'image'=>'images/image25.jpg'],
    ['text'=>'Ако ме имаш, искаш да ме споделиш. Ако ме споделиш, вече ме нямаш.', 'answer'=>'тайна', 'image'=>'images/image26.jpg'],
    ['text'=>'Дай ми храна и ще живея. Дай ми вода и ще умра.', 'answer'=>'огън', 'image'=>'images/image27.jpg'],
    ['text'=>'Имам ключове, но няма ключалки. Имам пространство, но няма стаи. Можеш да влезеш, но не можеш да излезеш.', 'answer'=>'клавиатура', 'image'=>'images/image28.jpg'],
    ['text'=>'Аз не съм нищо, но имам име. Понякога съм висока, понякога ниска. Не мога да мисля, но се движа с теб.', 'answer'=>'сянка', 'image'=>'images/image29.jpg'],
];

$level = $_SESSION['level'];
$result = '';// Пази съобщението за верен/грешен отговор
$hintError = ''; // Променлива за грешка, ако няма точки за жокер

// --- ЛОГИКА ЗА КЛИКВАНЕ НА ЖОКЕР ---
if (isset($_GET['hint'])) {
    // Проверяваме дали вече сме използвали безплатните (3 броя)
    if ($_SESSION['hints_used'] < 3) {
        $_SESSION['hints_used']++;
        $showHint = true;
    } else {
        // Ако са свършили безплатните, проверяваме дали имаме 5 точки
        if ($_SESSION['points'] >= 5) {
            $_SESSION['points'] -= 5; // Вземаме 5 точки
            $_SESSION['hints_used']++;
            $showHint = true;
        } else {
            // Ако няма точки, не показваме жокера и даваме грешка
            $showHint = false;
            $hintError = "⚠️ Нужни са 5 точки за жокер!";
        }
    }
} else {
    $showHint = false;
}
// --- СТАРТИРАНЕ НА НОВА ИГРА ---
if (isset($_POST['start'])) {
    $_SESSION['started'] = true;
    $_SESSION['level'] = 0;
    $_SESSION['points'] = 0;
    $_SESSION['hints_used'] = 0; // Нулираме жокерите при нов старт
    header("Location: index.php");// Презареждаме страницата, за да изчистим POST данните
    exit;
}
// Проверка дали сме минали всички гатанки
$finished = $level >= count($riddles);
// --- ПРОВЕРКА НА ОТГОВОРА (POST заявка) ---
if (!$finished && isset($_POST['answer'])) {
    $userAnswer = mb_strtolower(trim($_POST['answer']));
    // mb_strtolower прави текста малък (за да няма значение дали пишеш "Дърво" или "дърво")
    // trim премахва излишни интервали в началото или края
    if ($userAnswer === $riddles[$level]['answer']) {
        $_SESSION['points'] += 10;// Даваме бонус точки
        $_SESSION['level']++;// Преминаваме на следващото ниво
        header("Location: index.php");// Презареждане към следващата гатанка
        exit;
    } else {
        $result = '❌ Грешен отговор!';
    }
}
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
        <h3>⭐ <?= $_SESSION['points'] ?> точки</h3>
        <a href="reset.php" class="btn">🔄 Играй отново</a>
    </div>

<?php else: ?>
    <div class="level-card">
        <img src="images/duck-talisman.png" class="talisman" alt="Пате">
        <h2>Ниво <?= $level + 1 ?></h2>
        <p>⭐ Точки: <?= $_SESSION['points'] ?></p>
        <small style="display:block; margin-bottom:10px; color:#eee;">
            <?php 
            if($_SESSION['hints_used'] < 3) {
                echo "<span class=\"hint-note\">🎁 Остават " . (3 - $_SESSION['hints_used']) . " безплатни жокера</span>";
            } else {
                echo "<span class=\"hint-note\">💰 Жокерите вече струват 5 точки</span>";
            }
            ?>
        </small>

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
                <?= ($_SESSION['hints_used'] < 3) ? "Жокер (Безплатен)" : "Жокер (5 точки)" ?>
            </a>
        <?php else: ?>
            <div class="hint-wrapper">
                <img src="<?= $riddles[$level]['image'] ?>" class="hint-img">
                <p><small>(Жокерът е активиран)</small></p>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

</main>

<?php include 'include/footer.php'; ?>
</div>
</body>
</html>