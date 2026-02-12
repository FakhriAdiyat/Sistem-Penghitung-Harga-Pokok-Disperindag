<?php
// Pastikan setting cookie dilakukan SEBELUM session_start
ini_set('session.cookie_lifetime', 0);
ini_set('session.gc_maxlifetime', 900);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Jika belum login
if (!isset($_SESSION['login'])) {
    header("Location: ../auth/login.php");
    exit;
}

// Auto logout jika tidak aktif (15 menit)
$timeout = 900; // 15 menit

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
    session_unset();
    session_destroy();
    header("Location: ../auth/login.php?timeout=1");
    exit;
}

// Update aktivitas
$_SESSION['last_activity'] = time();
