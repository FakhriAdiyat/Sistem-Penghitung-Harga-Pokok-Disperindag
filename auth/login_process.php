<?php
session_start();
require '../config/database.php';

$username = $_POST['username'];
$password = $_POST['password'];

$query = mysqli_query($conn, 
    "SELECT * FROM users WHERE username='$username'"
);

$data = mysqli_fetch_assoc($query);

if ($data && md5($password) == $data['password']) {

    $_SESSION['login'] = true;
    $_SESSION['id']    = $data['id'];
    $_SESSION['username']    = $data['username'];
    $_SESSION['role']  = $data['role'];

    if ($data['role'] == 'admin') {
        header("Location: ../admin/dashboard.php");
    } else {
        header("Location: ../member/dashboard.php");
    }

} else {
    echo "Username atau password salah!";
}
