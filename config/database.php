<?php
$appConfig = __DIR__ . '/app.php';
if (is_file($appConfig)) {
    require_once $appConfig;
}

$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_harga_bapok";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

?>
