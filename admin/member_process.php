<?php
require_once '../config/database.php';
require_once '../includes/auth_check.php';
require_once '../includes/role_check.php';

$username = $_POST['username'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
$role     = $_POST['role'];

mysqli_query($conn, "
    INSERT INTO users (username, password, role)
    VALUES ('$username', '$password', '$role')
");

header("Location: member.php");
