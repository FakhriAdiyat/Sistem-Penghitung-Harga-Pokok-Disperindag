<?php
session_start();

// Jika sudah login
if (isset($_SESSION['login'])) {

    // Cek role
    if ($_SESSION['role'] == 'admin') {
        header("Location: admin/dashboard.php");
        exit;
    } else {
        header("Location: member/dashboard.php");
        exit;
    }

} else {
    // Jika belum login
    header("Location: auth/login.php");
    exit;
}
