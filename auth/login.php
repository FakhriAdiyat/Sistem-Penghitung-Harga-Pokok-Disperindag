<?php require_once '../config/app.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login User</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body class="login-page">
    <?php if (isset($_GET['timeout'])): ?>
    <div id="toastTimeout" class="toast-timeout">
        <span>Sesi login telah habis. Silakan login ulang.</span>
    </div>
<?php endif; ?>
    

<div class="login-container">
    <img src="<?= BASE_URL ?>assets/img/logo1.png" class="login-logo" alt="Logo">
    <h2>Login</h2>

    <div class="login-box">
        <?php if (isset($_GET['error']) && $_GET['error'] === 'invalid'): ?>
            <div class="popup-overlay" role="alert" aria-live="assertive">
                <div class="popup-card" role="document">
                    <div class="popup-icon" aria-hidden="true">
                        <span class="popup-x popup-x-left"></span>
                        <span class="popup-x popup-x-right"></span>
                    </div>
                    <div class="popup-title">Login gagal</div>
                    <div class="popup-message">Username atau password salah.</div>
                    <button type="button" class="popup-close" onclick="closePopup()">Tutup</button>
                </div>
            </div>
        <?php endif; ?>

        <form action="<?= BASE_URL ?>auth/login_process.php" method="POST">
    
    <input type="text" name="username" placeholder="Username" required>

    <div class="password-wrapper">
        <input type="password" id="password" name="password" placeholder="Password" required>
        
        <span id="togglePassword" class="toggle-password">
            <svg id="iconEye" xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/>
  <circle cx="12" cy="12" r="3"/>
</svg>
        </span>
    </div>

    <button type="submit">Masuk</button>

</form>
    </div>
</div>

<script src="<?= BASE_URL ?>assets/js/popup.js"></script>
<script src="<?= BASE_URL ?>assets/js/main.js"></script>
</body>
</html>
