<?php
require_once '../config/database.php';
require_once '../includes/auth_check.php';
require_once '../includes/update_statistik_harga.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$search = $_POST['search_redirect'] ?? $_GET['search'] ?? '';
$redirect = 'list.php' . ($search !== '' ? '?search=' . urlencode($search) : '');

function redirect_with(string $base, string $query = ''): void {
    // Pastikan BASE_URL sudah didefinisikan dari config/app.php
    if (!defined('BASE_URL')) {
        require_once dirname(__DIR__) . '/config/app.php';
    }
    $url = BASE_URL . 'pages/' . $base;
    if ($query !== '') {
        $join = (strpos($base, '?') !== false) ? '&' : '?';
        $url .= $join . $query;
    }
    header("Location: $url");
    exit;
}

if ($action === 'tambah' || $action === 'add') {
    $bahan_id = (int) ($_POST['bahan_id'] ?? 0);
    $harga = (float) ($_POST['harga'] ?? 0);
    $tanggal = mysqli_real_escape_string($conn, $_POST['tanggal'] ?? date('Y-m-d'));
    if ($bahan_id <= 0 || $harga <= 0) {
        redirect_with($redirect, 'err=invalid');
    }
    mysqli_query($conn, "
        INSERT INTO harga (bahan_id, harga, tanggal)
        VALUES ('$bahan_id', '$harga', '$tanggal')
    ");
    updateStatistikHarga($conn, $bahan_id);
    redirect_with($redirect, 'success=tambah');
}

if ($action === 'edit') {
    $id = (int) ($_POST['id'] ?? 0);
    $harga = (float) ($_POST['harga'] ?? 0);
    $tanggal = mysqli_real_escape_string($conn, $_POST['tanggal'] ?? '');
    if ($id <= 0 || $harga <= 0) {
        redirect_with($redirect, 'err=invalid');
    }
    $row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT bahan_id FROM harga WHERE id = '$id'"));
    if (!$row) {
        redirect_with($redirect, 'err=notfound');
    }
    $bahan_id = (int) $row['bahan_id'];
    mysqli_query($conn, "
        UPDATE harga SET harga = '$harga', tanggal = '$tanggal' WHERE id = '$id'
    ");
    updateStatistikHarga($conn, $bahan_id);
    redirect_with($redirect, 'success=edit');
}

if ($action === 'hapus' || $action === 'delete') {
    $id = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);
    if ($id <= 0) {
        redirect_with($redirect, 'err=invalid');
    }
    $row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT bahan_id FROM harga WHERE id = '$id'"));
    if (!$row) {
        redirect_with($redirect, 'err=notfound');
    }
    $bahan_id = (int) $row['bahan_id'];
    mysqli_query($conn, "DELETE FROM harga WHERE id = '$id'");
    updateStatistikHarga($conn, $bahan_id);
    redirect_with($redirect, 'success=hapus');
}

if ($action === 'hapus_banyak') {

    if (!isset($_POST['ids']) || !is_array($_POST['ids']) || count($_POST['ids']) === 0) {
        redirect_with($redirect, 'err=invalid');
    }

    // sanitasi id
    $ids = array_map('intval', $_POST['ids']);
    $idList = implode(',', $ids);

    // ambil bahan_id yang terdampak
    $bahanQuery = mysqli_query(
        $conn,
        "SELECT DISTINCT bahan_id FROM harga WHERE id IN ($idList)"
    );

    // hapus data
    mysqli_query($conn, "DELETE FROM harga WHERE id IN ($idList)");

    // update statistik tiap bahan
    while ($b = mysqli_fetch_assoc($bahanQuery)) {
        updateStatistikHarga($conn, (int)$b['bahan_id']);
    }

    redirect_with($redirect, 'success=hapus');
}
redirect_with($redirect);
