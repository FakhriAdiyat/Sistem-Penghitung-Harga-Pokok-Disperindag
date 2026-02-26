<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_harga_bapok";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

define('BASE_URL', 'http://localhost/Sistem-Penghitung-Harga-Pokok-Disperindag/');

?>
