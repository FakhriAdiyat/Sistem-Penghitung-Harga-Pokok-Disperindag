<?php
session_start();
require '../config/database.php';

$username = trim($_POST['username']);
$password = trim($_POST['password']);

$query = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
$data = mysqli_fetch_assoc($query);

if ($data) {

    if (password_verify($password, $data['password'])) {

        $_SESSION['login'] = true;
        $_SESSION['id'] = $data['id'];
        $_SESSION['username'] = $data['username'];
        $_SESSION['role'] = $data['role'];
        $_SESSION['last_activity'] = time();

        header("Location: ../dashboard.php");
        exit;


    } else {
        echo "Password salah";
        exit;
    }

} else {
    echo "Username tidak ditemukan";
    exit;
}
