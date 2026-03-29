 <?php
session_start();

// Достъп само за влезли потребители
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

// Инициализиране на баланса
if (!isset($_SESSION['stars'])) {
    $_SESSION['stars'] = 100;
}
if (!isset($_SESSION['last_spin_date'])) {
    $_SESSION['last_spin_date'] = '';
}

$type = $_GET['type'] ?? 'free';
$today = date("Y-m-d");

// Проверки за лимит и баланс
if ($type === 'free' && $_SESSION['last_spin_date'] === $today) {
    $_SESSION['wheel_error'] = 'Вече завъртяхте днес!';
    header('Location: profile.php'); // Или страницата, където е колелото
    exit;
}

if ($type === 'paid' && $_SESSION['stars'] < 50) {
    $_SESSION['wheel_error'] = 'Нямате достатъчно звезди!';
    header('Location: profile.php');
    exit;
}

// Награди
$prizes = [
    0 => ['name' => '10 Звезди', 'bonus' => 10],
    1 => ['name' => 'Жокер', 'bonus' => 0],
    2 => ['name' => 'Скипване на ниво', 'bonus' => 0],
    3 => ['name' => '10 Звезди', 'bonus' => 10],
    4 => ['name' => 'Жокер', 'bonus' => 0],
    5 => ['name' => 'Скипване на ниво', 'bonus' => 0]
];

$win_index = rand(0, 5);
$selected = $prizes[$win_index];

// Обновяване на баланса
if ($type === 'free') {
    $_SESSION['last_spin_date'] = $today;
} else {
    $_SESSION['stars'] -= 50;
}

$_SESSION['stars'] += $selected['bonus'];

// ЗАПИСВАМЕ РЕЗУЛТАТА ЗА ПОП-ЪПА
$_SESSION['show_prize_modal'] = true;
$_SESSION['last_prize'] = $selected['name'];

// Връщаме се към профила
header('Location: profile.php');
exit;