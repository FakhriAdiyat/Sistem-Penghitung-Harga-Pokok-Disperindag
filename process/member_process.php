<?php
require_once '../config/database.php';
require_once '../includes/auth_check.php';
require_once '../includes/role_check.php';

// ================= TAMBAH MEMBER =================
if (!isset($_POST['update'])) {

    $username = $_POST['username'];
    $password = $_POST['password'];
    $role     = $_POST['role'];

    // Cek username sudah ada
    $cek = mysqli_query($conn, "SELECT id FROM users WHERE username='$username'");
    if (mysqli_num_rows($cek) > 0) {
        echo "Username sudah digunakan!";
        exit;
    }

    // Hash password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Simpan
    mysqli_query($conn, "
        INSERT INTO users (username, password, role)
        VALUES ('$username', '$password_hash', '$role')
    ");

    header("Location: ../pages/member.php");
    exit;
}


// ================= UPDATE MEMBER =================
if (isset($_POST['update'])) {

    $id       = $_POST['id'];
    $username = $_POST['username'];
    $role     = $_POST['role'];
    $password = $_POST['password'];

    // Cek username (kecuali milik sendiri)
    $cek = mysqli_query($conn, "SELECT id FROM users WHERE username='$username' AND id != '$id'");
    if (mysqli_num_rows($cek) > 0) {
        echo "Username sudah digunakan!";
        exit;
    }

    // Kalau password diisi → update semua
    if (!empty($password)) {

        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        mysqli_query($conn, "
            UPDATE users SET 
                username='$username',
                password='$password_hash',
                role='$role'
            WHERE id='$id'
        ");

    } else {

        // Kalau password kosong → jangan ubah password
        mysqli_query($conn, "
            UPDATE users SET 
                username='$username',
                role='$role'
            WHERE id='$id'
        ");
    }

    header("Location: ../pages/member.php");
    exit;
}