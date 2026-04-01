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
    <input type="password" name="password" id="passwordInput" placeholder="Password" required>

    <span class="toggle-password" onclick="togglePassword()">
        <!-- ICON MATA (SVG) -->
        <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="gray" viewBox="0 0 24 24">
            <path d="M12 5c-7 0-10 7-10 7s3 7 10 7 10-7 10-7-3-7-10-7zm0 12a5 5 0 1 1 0-10 5 5 0 0 1 0 10zm0-8a3 3 0 1 0 0 6 3 3 0 0 0 0-6z"/>
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
