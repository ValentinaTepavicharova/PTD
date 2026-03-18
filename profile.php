<?php
session_start();

// Защита: потребителят трябва да е влязъл
if (!isset($_SESSION['user'])) {
    header('Location: login/login.php');
    exit;
}

// Инициализиране на баланса, ако липсва
if (!isset($_SESSION['stars'])) {
    $_SESSION['stars'] = 100;
}

$today = date('Y-m-d');
$lastSpin = $_SESSION['last_spin_date'] ?? '';
$freeSpinAvailable = ($lastSpin !== $today);

// Смятаме колко секунди остават до полунощ (за таймера)
$secondsUntilMidnight = 0;
if (!$freeSpinAvailable) {
    $midnight = strtotime('tomorrow');
    $secondsUntilMidnight = max(0, $midnight - time());
}

?>
<!DOCTYPE html>
<html lang="bg">
<?php include 'include/head.php'; ?>
<body>
<div class="site">
<?php include 'include/header.php'; ?>

<main>
    <div class="level-card" style="position: relative;">
        <h2>👤 Профил</h2>
        <p>Здравей, <strong><?= htmlspecialchars($_SESSION['user']) ?></strong>!</p>
        <p>Баланс: <strong id="starBalance"><?= (int)$_SESSION['stars'] ?> ⭐</strong></p>

        <div style="margin-top: 20px; text-align: left;">
            <p>Колелото на късмета е достъпно тук (само след влизане).</p>
        </div>

        <div class="wheel-wrapper">
            <div class="pointer"></div>
            <div id="wheel"></div>
        </div>

        <div class="controls">
            <div class="btn-container">
                <button class="btn-free" id="freeBtn" onclick="spin('free')">🎁 Безплатно</button>
                <div id="timer" class="timer-text"></div>
            </div>
            <button class="btn-paid" id="paidBtn" onclick="spin('paid')">💎 50 Звезди</button>
        </div>

        <div style="margin-top: 22px;">
            <a href="index.php" class="btn">🏠 Към играта</a>
        </div>
    </div>
</main>

<?php include 'include/footer.php'; ?>
</div>

<style>
/* Wheel styles (вградени, за да не се налага допълнителен css файл) */
.wheel-wrapper {
    position: relative;
    width: 320px;
    height: 320px;
    margin: 20px auto 0 auto;
}

.pointer {
    position: absolute;
    top: -15px;
    left: 50%;
    transform: translateX(-50%);
    width: 0;
    height: 0;
    border-left: 15px solid transparent;
    border-right: 15px solid transparent;
    border-top: 30px solid #e74c3c;
    z-index: 10;
}

#wheel {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    border: 8px solid #333;
    background: conic-gradient(
        #2980b9 0deg 60deg,
        #f39c12 60deg 120deg,
        #27ae60 120deg 180deg,
        #2980b9 180deg 240deg,
        #f39c12 240deg 300deg,
        #27ae60 300deg 360deg
    );
    transition: transform 4s cubic-bezier(0.15, 0, 0.15, 1);
}

#wheel::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 20px;
    height: 20px;
    background: white;
    border-radius: 50%;
}

.controls {
    display: flex;
    gap: 20px;
    align-items: flex-start;
    justify-content: center;
    flex-wrap: wrap;
    margin-top: 20px;
}

.btn-container {
    display: flex;
    flex-direction: column;
    align-items: center;
}

button {
    padding: 12px 24px;
    border: none;
    border-radius: 20px;
    font-weight: bold;
    cursor: pointer;
    transition: 0.3s;
}

.btn-free {
    background: #27ae60;
    color: white;
}

.btn-paid {
    background: #f1c40f;
    color: #333;
}

button:disabled {
    background: #444;
    color: #888;
    cursor: not-allowed;
}

.timer-text {
    margin-top: 8px;
    font-size: 0.85rem;
    color: #aaa;
}
</style>

<script>
const wheel = document.getElementById('wheel');
const starBalance = document.getElementById('starBalance');
const freeBtn = document.getElementById('freeBtn');
const timerEl = document.getElementById('timer');

let isSpinning = false;
const freeSpinAvailable = <?= $freeSpinAvailable ? 'true' : 'false' ?>;
let secondsUntilMidnight = <?= (int)$secondsUntilMidnight ?>;

function updateTimer() {
    if (freeSpinAvailable) {
        timerEl.innerText = '';
        return;
    }

    if (secondsUntilMidnight <= 0) {
        freeBtn.disabled = false;
        timerEl.innerText = '';
        return;
    }

    const hours = Math.floor(secondsUntilMidnight / 3600);
    const mins = Math.floor((secondsUntilMidnight % 3600) / 60);
    const secs = secondsUntilMidnight % 60;

    timerEl.innerText = `Следващо след: ${hours}ч ${mins}м ${secs}с`;
}

function syncFreeBtn() {
    if (!freeSpinAvailable) {
        freeBtn.disabled = true;
        updateTimer();
        setInterval(() => {
            secondsUntilMidnight = Math.max(0, secondsUntilMidnight - 1);
            updateTimer();
        }, 1000);
    }
}

function spin(type) {
    if (isSpinning) return;
    isSpinning = true;

    fetch('wheel_spin.php?type=' + type)
        .then(resp => resp.json())
        .then(data => {
            if (!data.success) {
                alert(data.message);
                isSpinning = false;
                return;
            }

            wheel.style.transform = `rotate(${data.rotation}deg)`;

            setTimeout(() => {
                alert('🎉 ' + data.prize);
                starBalance.innerText = data.new_balance + ' ⭐';
                isSpinning = false;

                if (type === 'free') {
                    freeBtn.disabled = true;
                    secondsUntilMidnight = 24 * 60 * 60;
                    updateTimer();
                }
            }, 4100);
        })
        .catch(() => {
            alert('Грешка при въртенето. Опитайте пак.');
            isSpinning = false;
        });
}

syncFreeBtn();
</script>
</body>
</html>
