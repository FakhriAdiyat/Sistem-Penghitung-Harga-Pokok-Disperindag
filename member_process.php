<?php
require_once 'config/database.php';
require_once 'includes/auth_check.php';
require_once 'includes/role_check.php';

$username = $_POST['username'];
$password = $_POST['password'];
$role     = $_POST['role'];

// Cek apakah username sudah ada
$cek = mysqli_query($conn, "SELECT id FROM users WHERE username='$username'");

if (mysqli_num_rows($cek) > 0) {
    echo "Username sudah digunakan!";
    exit;
}

// HASH PASSWORD
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// SIMPAN
mysqli_query($conn, "
INSERT INTO users (username, password, role)
VALUES ('$username', '$password_hash', '$role')
");

header("Location: member.php");
exit;
