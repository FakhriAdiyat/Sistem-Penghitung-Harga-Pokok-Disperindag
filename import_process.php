<?php
require_once 'config/database.php';
require_once 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if (!isset($_FILES['file_import'])) {
    die('File tidak ditemukan');
}

$file = $_FILES['file_import'];
$ext  = pathinfo($file['name'], PATHINFO_EXTENSION);

if (!in_array($ext, ['csv', 'xlsx'])) {
    die('Format file tidak didukung');
}

/* ================= CSV ================= */
if ($ext === 'csv') {
    $handle = fopen($file['tmp_name'], 'r');
    fgetcsv($handle); // skip header

    while (($row = fgetcsv($handle)) !== false) {
        $bahan    = $row[0];
        $kategori = $row[1];
        $harga    = $row[2];
        $tanggal  = $row[3];

        mysqli_query($conn, "
            INSERT INTO harga (bahan, kategori, harga, tanggal)
            VALUES ('$bahan', '$kategori', '$harga', '$tanggal')
        ");
    }
    fclose($handle);
}

/* ================= XLSX ================= */
if ($ext === 'xlsx') {
    $spreadsheet = IOFactory::load($file['tmp_name']);
    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray();

    foreach ($rows as $index => $row) {
        if ($index === 0) continue; // skip header

        $bahan    = $row[0];
        $kategori = $row[1];
        $harga    = $row[2];
        $tanggal  = $row[3];

        mysqli_query($conn, "
            INSERT INTO harga (bahan, kategori, harga, tanggal)
            VALUES ('$bahan', '$kategori', '$harga', '$tanggal')
        ");
    }
}

header("Location: import.php?success=1");
exit;
