<?php
require '../config/database.php';

$password = 'admin123';
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "HASH: <br>";
echo $hash;

mysqli_query($conn, "
    INSERT INTO users (username, password, role)
    VALUES ('admin_fix', '$hash', 'admin')
");
