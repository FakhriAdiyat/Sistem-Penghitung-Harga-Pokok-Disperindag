<?php
require_once '../config/database.php';
require_once '../includes/auth_check.php';
require_once '../includes/role_check.php';

if (!isset($_POST['ids']) || !is_array($_POST['ids'])) {
    header("Location: ../pages/list.php");
    exit;
}

$ids = array_map('intval', $_POST['ids']);
$id_list = implode(',', $ids);

mysqli_query($conn, "DELETE FROM harga WHERE id IN ($id_list)");

header("Location: ../pages/list.php?success=hapus");
exit;
