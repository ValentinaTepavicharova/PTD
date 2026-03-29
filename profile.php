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
            <button class="btn-paid" id="paidBtn" onclick="spin('paid')">⭐ 50 Звезди</button>
        </div>

        <div style="margin-top: 22px;">
            <a href="index.php" class="btn">🏠 Към играта</a>
        </div>
    </div>
   <div id="win-popup" class="popup-overlay">
    <div class="popup-card">
        <div class="confetti"></div>
        <img src="images/duck-talisman.png" alt="Победа" class="popup-duck">
        <h2>Честито!</h2>
        <p id="popup-prize-text">Спечели награда!</p>
        <button onclick="closePopup()" class="btn">Затвори!</button>
    </div>
</div> 
</main>

<?php include 'include/footer.php'; ?>
</div>

<style>
    /* Фоново затъмняване */
.popup-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(8px);
    display: none; /* Скрито по подразбиране */
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

/* Самата карта */
.popup-card {
    background: linear-gradient(135deg, #ffffff, #f3f4f6);
    padding: 40px;
    border-radius: 30px;
    text-align: center;
    box-shadow: 0 20px 50px rgba(0,0,0,0.5);
    position: relative;
    max-width: 90%;
    width: 350px;
    transform: scale(0.7);
    animation: popupAppear 0.4s cubic-bezier(0.17, 0.89, 0.32, 1.27) forwards;
}

.popup-card h2 { color: #1e40af; font-size: 2em; margin-bottom: 10px; }
.popup-card p { color: #333; font-size: 1.2em; font-weight: bold; }

.popup-duck {
    width: 100px;
    margin-bottom: 15px;
    animation: floatDuck 2s ease-in-out infinite;
}

/* Анимации */
@keyframes popupAppear {
    to { transform: scale(1); }
}

@keyframes floatDuck {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

/* Клас за показване */
.popup-overlay.active {
    display: flex;
}
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
    background: #fff36a;
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
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7); /* Тъмен фон */
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 2000;
    backdrop-filter: blur(5px);
}

/* Използваме твоя съществуващ стил за картата */
.modal-overlay .level-card {
    animation: fadeIn 0.4s ease-out;
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
                 // Замени alert-а с това:
document.getElementById('popup-prize-text').innerText = "Ти спечели: " + data.prize;
document.getElementById('win-popup').classList.add('active');
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
function closePopup() {
    document.getElementById('win-popup').classList.remove('active');
}
syncFreeBtn();
</script>
</body>
</html>
<?php if (isset($_SESSION['show_prize_modal'])): ?>
    <div class="modal-overlay">
        <div class="level-card">
            <img src="images/duck-talisman.png" class="talisman" alt="Пате">
            <h2>Честито! 🎉</h2>
            <p class="riddle-text">Ти спечели:</p>
            <h3 style="color: #2563eb;"><?= $_SESSION['last_prize'] ?></h3>
            <p>Твоят нов баланс: ⭐ <?= $_SESSION['stars'] ?></p>
            
            <form method="post" style="margin-top: 20px;">
                <button name="close_modal" class="btn">Супер!</button>
            </form>
        </div>
    </div>
    <?php 
        // Изчистваме съобщението, за да не излиза пак при рефреш
        if (isset($_POST['close_modal'])) {
            unset($_SESSION['show_prize_modal']);
            header("Location: profile.php");
        }
    ?>
<?php endif; ?>