<?php
require_once 'config/database.php';
require_once 'includes/auth_check.php';
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Ambil data dari database
$query = mysqli_query($conn, "
    SELECT h.tanggal, b.nama_bahan, h.harga
    FROM harga h
    JOIN bahan_pokok b ON h.bahan_id = b.id
    ORDER BY h.tanggal DESC
");

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Header kolom
$sheet->setCellValue('A1', 'Tanggal');
$sheet->setCellValue('B1', 'Nama Bahan');
$sheet->setCellValue('C1', 'Harga');

$row = 2;

while ($data = mysqli_fetch_assoc($query)) {
    $sheet->setCellValue('A' . $row, $data['tanggal']);
    $sheet->setCellValue('B' . $row, $data['nama_bahan']);
    $sheet->setCellValue('C' . $row, $data['harga']);
    $row++;
}

// Set header download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="data_harga.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
