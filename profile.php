<?php
session_start();
require_once 'include/db.php';

// Защита: потребителят трябва да е влязъл
if (!isset($_SESSION['user'])) {
    header('Location: login/login.php');
    exit;
}

// Зареждаме най-актуалните данни за потребителя от базата, за да сме сигурни, че имаме правилния баланс и дата на последно завъртане
$stmt = $pdo->prepare("SELECT Stars, Last_spin_date, Current_level, Skip_levels, Bonus_hints FROM Users WHERE Username = ?");
$stmt->execute([$_SESSION['user']]);
$userData = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$userData) {
    // Ако по някаква причина няма данни за потребителя, изчистваме сесията и го пренасочваме към логина
    session_destroy();
    header('Location: login/login.php');
    exit;
} 

// Синхронизираме сесията с базата
$_SESSION['stars'] = (int)$userData['Stars'];
$_SESSION['last_spin_date'] = $userData['Last_spin_date'] ?? '';    
$_SESSION['level'] = (int)$userData['Current_level'];
$_SESSION['skip_levels'] = (int)$userData['Skip_levels'];
$_SESSION['bonus_hints'] = (int)$userData['Bonus_hints'];

$today = date('Y-m-d');
$freeSpinAvailable = ($_SESSION['last_spin_date'] !== $today);
$skipLevels = (int)$userData['Skip_levels'];

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
        <img src="images/duck-talisman.png" class="talisman" alt="Пате">
        <h2>👤 Профил</h2>
        <p>Здравей, <strong><?= htmlspecialchars($_SESSION['user']) ?></strong>!</p>
        <p>Баланс: <strong id="starBalance"><?= $_SESSION['stars'] ?> ⭐</strong></p>
        <p>Пропуски: <strong id="skipCount"><?= $skipLevels ?></strong></p>

        <div style="margin-top: 20px; text-align: left;">
            <p>Колелото на късмета е достъпно тук (само след влизане).</p>
        </div>

        <div style="margin-top: 20px; text-align: left;">
            <p>Завърти колелото на късмета за награди!</p>
        </div>
 
        <div class="wheel-wrapper">
            <div class="pointer"></div>
            <canvas id="wheel" width="320" height="320"></canvas>
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
<!-- Модал за наградата --> 
<div id="prizeModal" class="modal-overlay" style="display:none;">
    <div class="level-card" style="animation: cardFadeIn 0.4s ease-out; max-width:360px;">
        <img src="images/duck-talisman.png" class="talisman" alt="Пате">
        <h2>🎉 Честито!</h2>
        <p class="riddle-text">Ти спечели:</p>
        <h3 id="prizeText" style="color:#f1c40f; font-size:1.6em; margin:10px 0;"></h3>
        <p>Баланс: ⭐ <strong id="modalBalance"></strong></p>
        <button onclick="closeModal()" class="btn" style="margin-top:15px;">Супер! 🎊</button>
    </div>
</div>
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
    border-radius: 50%;
    transition: transform 4s cubic-bezier(0.15, 0, 0.15, 1);
    display:block;
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

.btn-free {
    background: #27ae60;
    color: white;
}

.btn-paid {
    background: #fff36a;
    color: #333;
}

button:disabled {
    background: #444 !important;
    color: #888 !important;
    cursor: not-allowed !important;
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
    background: rgba(0, 0, 0, 0.75); /* Тъмен фон */
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 2000;
    backdrop-filter: blur(6px);
}
</style>

<script>
// --- Рисуване на колелото с Canvas ---
const segments = [
    { label: '10 ⭐',   color: '#2980b9' },
    { label: 'Жокер',   color: '#f39c12' },
    { label: 'Пропуск', color: '#27ae60' },
    { label: '10 ⭐',   color: '#2980b9' },
    { label: 'Жокер',   color: '#f39c12' },
    { label: 'Пропуск', color: '#27ae60' },
];

const canvas  = document.getElementById('wheel');
const ctx     = canvas.getContext('2d');
const cx      = canvas.width / 2;
const cy      = canvas.height / 2;
const radius  = cx - 4;
const sliceAngle = (2 * Math.PI) / segments.length;
 
let currentRotation = 0; // следим ротацията в радиани
 
function drawWheel(rotRad) {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
 
    segments.forEach((seg, i) => {
        const start = rotRad + i * sliceAngle;
        const end   = start + sliceAngle;
 
        // Парче
        ctx.beginPath();
        ctx.moveTo(cx, cy);
        ctx.arc(cx, cy, radius, start, end);
        ctx.closePath();
        ctx.fillStyle = seg.color;
        ctx.fill();
        ctx.strokeStyle = '#fff';
        ctx.lineWidth = 2;
        ctx.stroke();
 
        // Текст
        ctx.save();
        ctx.translate(cx, cy);
        ctx.rotate(start + sliceAngle / 2);
        ctx.textAlign = 'right';
        ctx.fillStyle = '#fff';
        ctx.font = 'bold 14px Comfortaa, cursive';
        ctx.shadowColor = 'rgba(0,0,0,0.5)';
        ctx.shadowBlur  = 3;
        ctx.fillText(seg.label, radius - 12, 5);
        ctx.restore();
    });
 
    // Централен кръг
    ctx.beginPath();
    ctx.arc(cx, cy, 14, 0, 2 * Math.PI);
    ctx.fillStyle = '#fff';
    ctx.fill();
    ctx.strokeStyle = '#333';
    ctx.lineWidth = 3;
    ctx.stroke();
}
 
drawWheel(0);

// Логика за въртене и комуникация с backend 
const starBalance = document.getElementById('starBalance');
const freeBtn = document.getElementById('freeBtn');
const timerEl = document.getElementById('timer');
const modal = document.getElementById('prizeModal');

let isSpinning = false;
let freeSpinAvailable = <?= $freeSpinAvailable ? 'true' : 'false' ?>;
let secondsUntilMidnight = <?= (int)$secondsUntilMidnight ?>;

function updateTimer() {
    if (freeSpinAvailable || secondsUntilMidnight <= 0) {
        timerEl.innerText = '';
        return;
    }

    const h = Math.floor(secondsUntilMidnight / 3600);
    const m = Math.floor((secondsUntilMidnight % 3600) / 60);
    const s = secondsUntilMidnight % 60;
    timerEl.innerText = `Следващо след: ${h}ч ${m}м ${s}с`;
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
    document.getElementById('freeBtn').disabled = true;
    document.getElementById('paidBtn').disabled = true;

    fetch('wheel_spin.php?type=' + type)
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                alert(data.message);
                isSpinning = false;
                syncFreeBtn();
                document.getElementById('paidBtn').disabled = false;
                return;
            }

            // Изчисляваме целевата ротация в радиани
            // data.rotation е в градуси (360*6 + base)
            const targetDeg = currentRotation * (180 / Math.PI) + data.rotation;
            const targetRad = targetDeg * (Math.PI / 180);
 
            // Анимираме с requestAnimationFrame
            const startTime = performance.now();
            const duration  = 4000; // ms — трябва да съвпада с CSS transition
            const startRot  = currentRotation;
 
            function animate(now) {
                const elapsed  = now - startTime;
                const progress = Math.min(elapsed / duration, 1);
                // cubic-bezier(0.15, 0, 0.15, 1) апроксимация
                const ease = easeOut(progress);
                const rot  = startRot + (targetRad - startRot) * ease;
                drawWheel(rot);
 
                if (progress < 1) {
                    requestAnimationFrame(animate);
                } else {
                    currentRotation = targetRad % (2 * Math.PI);
                    drawWheel(currentRotation);
                    showPrize(data);
                    isSpinning = false;
                }
            }
 
            requestAnimationFrame(animate);
        })
        .catch(() => {
            alert('Грешка при въртенето. Опитайте пак.');
            isSpinning = false;
            document.getElementById('paidBtn').disabled = false;
            syncFreeBtn();
        });
}
 
function easeOut(t) {
    // cubic-bezier(0.15, 0, 0.15, 1)
    return 1 - Math.pow(1 - t, 3);
}
 
function showPrize(data) {
    document.getElementById('prizeText').innerText = data.prize;
    document.getElementById('modalBalance').innerText = data.new_balance;
    starBalance.innerText = data.new_balance + ' ⭐';
    modal.style.display = 'flex';
 
    // Обновяваме броя пропуски ако е спечелен
    if (data.prize_type === 'skip') {
        const skipEl = document.getElementById('skipCount');
        if (skipEl) skipEl.innerText = parseInt(skipEl.innerText || '0') + 1;
        const wrap = document.getElementById('skipCountWrap');
        if (wrap) wrap.style.color = '#f1c40f';
    }
}
 
function closeModal() {
    modal.style.display = 'none';
    document.getElementById('paidBtn').disabled = false;
    syncFreeBtn();
}
syncFreeBtn();
</script>
</body>
</html>