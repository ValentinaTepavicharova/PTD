 <?php
session_start();
require_once 'include/db.php';

// Достъп само за влезли потребители
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Трябва да влезете, ']);
    exit;
}

$type = $_GET['type'] ?? 'free';
$today = date("Y-m-d");

$stmt = $pdo->prepare("SELECT Stars, Last_spin_date, Current_level, Skip_levels, Hints_used, Bonus_hints FROM Users WHERE Username = ?");
$stmt->execute([$_SESSION['user']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'Потребителят не е намерен.']);
    exit;
}

$_SESSION['stars'] = (int)$user['Stars'];
$_SESSION['last_spin_date'] = $user['Last_spin_date'] ?? '';
$_SESSION['skip_levels'] = (int)$user['Skip_levels'];
$_SESSION['hints_used'] = (int)$user['Hints_used'];
$_SESSION['bonus_hints'] = (int)$user['Bonus_hints'];

// Награди
$prizes = [
    0 => ['name' => '10 Звезди', 'bonus' => 10, 'type' => 'stars'],
    1 => ['name' => 'Жокер', 'bonus' => 1, 'type' => 'hint'],
    2 => ['name' => 'Пропуск на ниво', 'bonus' => 1, 'type' => 'skip'],
    3 => ['name' => '10 Звезди', 'bonus' => 10, 'type' => 'stars'],
    4 => ['name' => 'Жокер', 'bonus' => 1, 'type' => 'hint'],
    5 => ['name' => 'Пропуск на ниво', 'bonus' => 1, 'type' => 'skip']
];

$win_index = rand(0, 5);
$selected = $prizes[$win_index];


 
$center    = $win_index * 60 + 30;           // центъра на спечелившия сегмент
$base      = (360 - $center + 270) % 360;           // колко да завъртим за 1 оборот
$rotation  = 360 * 6 + $base;                 // 6 пълни + корекция

// Тип на завъртане: безплатно или платено
if ($type === 'free') {
    if ($_SESSION['last_spin_date'] === $today) {
        echo json_encode(['success' => false, 'message' => 'Вече сте използвали безплатното завъртане днес.']);
        exit;
    }
    $_SESSION['last_spin_date'] = $today;
} else {
    if ($_SESSION['stars'] < 50) {
        echo json_encode(['success' => false, 'message' => 'Нямате достатъчно звезди (нужни 50).']);
        exit;
    }
    $_SESSION['stars'] -= 50;
}
// Приложим ефекта от наградата
$_SESSION['stars'] += $selected['bonus'];

// Ако е жокер, добавяме 1 към използваните жокери
if ($selected['type'] === 'hint') {
    $_SESSION['bonus_hints']++;
}
 
// Ако е пропуск — записваме в БД
$skipLevels = (int)$user['Skip_levels'];
if ($selected['type'] === 'skip') {
    $_SESSION['skip_levels'] += $selected['bonus'];
}

// Записваме новите данни в базата
$stmt = $pdo->prepare("UPDATE Users SET Stars = ?, Last_spin_date = ?, Skip_levels = ?, Bonus_hints = ? WHERE Username = ?");
$stmt->execute([$_SESSION['stars'], $_SESSION['last_spin_date'], $_SESSION['skip_levels'], $_SESSION['bonus_hints'], $_SESSION['user']]);


// ЗАПИСВАМЕ РЕЗУЛТАТА ЗА ПОП-ЪПА
echo json_encode([
    'success' => true,
    'prize' => $selected['name'],
    'prize_type' => $selected['type'],
    'new_balance' => $_SESSION['stars'],
    'rotation' => $rotation,
    'win_index' => $win_index
]);
exit;
?>