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
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Masuk</button>
        </form>
    </div>
</div>

<script src="<?= BASE_URL ?>assets/js/popup.js"></script>
<script src="<?= BASE_URL ?>assets/js/main.js"></script>
</body>
</html>
