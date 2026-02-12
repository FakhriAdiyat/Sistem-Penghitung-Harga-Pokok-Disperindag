<?php
require_once 'config/database.php';
require_once 'includes/auth_check.php';

// Pastikan hanya admin
if ($_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

// Ambil ID
$id = $_GET['id'] ?? null;

// Jangan boleh hapus diri sendiri
if ($id == $_SESSION['id']) {
    header("Location: member.php");
    exit;
}

// Hapus user
mysqli_query($conn, "DELETE FROM users WHERE id='$id'");

header("Location: member.php");
exit;
