<?php
header('Content-Type: application/json');
session_start();

// Достъп само за влезли потребители
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Трябва да влезете, за да ползвате колелото.']);
    exit;
}

// Инициализиране на баланса и датата на последно въртене
if (!isset($_SESSION['stars'])) {
    $_SESSION['stars'] = 100;
}
if (!isset($_SESSION['last_spin_date'])) {
    $_SESSION['last_spin_date'] = '';
}

$type = $_GET['type'] ?? 'free';
$today = date("Y-m-d");

if ($type === 'free' && $_SESSION['last_spin_date'] === $today) {
    echo json_encode(['success' => false, 'message' => 'Вече завъртяхте днес!']);
    exit;
}

if ($type === 'paid' && $_SESSION['stars'] < 50) {
    echo json_encode(['success' => false, 'message' => 'Нямате достатъчно звезди!']);
    exit;
}

// Дефиниция на секторите
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

// Ъгълът, за да се позиционираме в сектора
$angle_offset = ($win_index * 60) + 30;
$total_rotation = (360 * 8) + (360 - $angle_offset);

if ($type === 'free') {
    $_SESSION['last_spin_date'] = $today;
} else {
    $_SESSION['stars'] -= 50;
}

$_SESSION['stars'] += $selected['bonus'];

echo json_encode([
    'success' => true,
    'rotation' => $total_rotation,
    'prize' => $selected['name'],
    'new_balance' => $_SESSION['stars']
]);
