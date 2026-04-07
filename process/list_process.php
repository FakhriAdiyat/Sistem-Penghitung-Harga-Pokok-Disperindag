<?php
require_once '../config/database.php';
require_once '../includes/auth_check.php';
require_once '../includes/update_statistik_harga.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$search = $_POST['search_redirect'] ?? $_GET['search'] ?? '';
$returnQuery = trim((string) ($_POST['return_query'] ?? $_GET['return_query'] ?? ''));
$redirect = 'list.php';
if ($returnQuery !== '') {
    $redirect .= '?' . ltrim($returnQuery, '?&');
} elseif ($search !== '') {
    $redirect .= '?search=' . urlencode($search);
}

function redirect_with(string $base, string $query = ''): void {
    // Redirect ke ../pages/ dari folder process
    if ($query === '') {
        header("Location: ../pages/{$base}");
    } else {
        $join = (strpos($base, '?') !== false) ? '&' : '?';
        header("Location: ../pages/{$base}{$join}{$query}");
    }
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

    // Validasi: hanya ID integer positif
    $ids = array_filter($_POST['ids'], function($id) {
        return is_numeric($id) && intval($id) > 0;
    });
    $ids = array_map('intval', $ids);
    if (count($ids) === 0) {
        redirect_with($redirect, 'err=invalid');
    }
    $idList = implode(',', $ids);

    // Cek data yang benar-benar ada
    $result = mysqli_query($conn, "SELECT id, bahan_id FROM harga WHERE id IN ($idList)");
    $foundIds = [];
    $bahanIds = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $foundIds[] = (int)$row['id'];
        $bahanIds[] = (int)$row['bahan_id'];
    }
    if (count($foundIds) === 0) {
        redirect_with($redirect, 'err=notfound');
    }
    $foundIdList = implode(',', $foundIds);

    // Hapus data yang valid saja
    mysqli_query($conn, "DELETE FROM harga WHERE id IN ($foundIdList)");

    // Update statistik tiap bahan unik
    foreach (array_unique($bahanIds) as $bahanId) {
        updateStatistikHarga($conn, $bahanId);
    }

    redirect_with($redirect, 'success=hapus');
}
redirect_with($redirect);
