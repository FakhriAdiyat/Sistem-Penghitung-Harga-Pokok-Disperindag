<?php
require_once '../config/database.php';
require_once '../includes/auth_check.php';
$allowed_roles = ['admin', 'member'];
require_once '../includes/role_check.php';
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$period = $_GET['period'] ?? '';
$period = is_string($period) ? strtolower(trim($period)) : '';

$today = new DateTime('today');

if ($period === 'weekly') {
    $start = (clone $today)->modify('-6 days'); // inklusif: total 7 hari
    $end = (clone $today);
    $label = 'mingguan';
} elseif ($period === 'monthly') {
    $start = new DateTime($today->format('Y-m-01'));
    $end = (clone $today);
    $label = 'bulanan';
} elseif ($period === 'yearly') {
    $start = new DateTime($today->format('Y-01-01'));
    $end = (clone $today);
    $label = 'tahunan';
} else {
    if (!defined('BASE_URL')) {
        require_once dirname(__DIR__) . '/config/app.php';
    }
    header("Location: " . BASE_URL . "export-import/export.php");
    exit;
}

$startStr = $start->format('Y-m-d');
$endStr = $end->format('Y-m-d');

$query = mysqli_query(
    $conn,
    "
    SELECT
        h.tanggal,
        b.nama_bahan,
        h.harga,
        h.rata_rata,
        h.persen_penyimpangan,
        h.fluktuasi_persen,
        h.stabilitas_persen,
        h.persen_naik_turun,
        h.naik_turun_rp
    FROM harga h
    JOIN bahan_pokok b ON h.bahan_id = b.id
    WHERE h.tanggal BETWEEN '$startStr' AND '$endStr'
    ORDER BY h.tanggal ASC, b.nama_bahan ASC
"
);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

$sheet->setCellValue('A1', 'Tanggal');
$sheet->setCellValue('B1', 'Nama Bahan');
$sheet->setCellValue('C1', 'Harga');
$sheet->setCellValue('D1', 'Harga Rata-rata');
$sheet->setCellValue('E1', 'Persen Penyimpangan');
$sheet->setCellValue('F1', 'Fluktuasi Persen');
$sheet->setCellValue('G1', 'Stabilitas Persen');
$sheet->setCellValue('H1', 'Persen Naik/Turun');
$sheet->setCellValue('I1', 'Naik/Turun (Rp)');

$row = 2;
if ($query) {
    while ($data = mysqli_fetch_assoc($query)) {
        $sheet->setCellValue('A' . $row, $data['tanggal']);
        $sheet->setCellValue('B' . $row, $data['nama_bahan']);
        $sheet->setCellValue('C' . $row, (float) $data['harga']);
        $sheet->setCellValue('D' . $row, $data['rata_rata'] !== null ? (float) $data['rata_rata'] : null);
        $sheet->setCellValue('E' . $row, $data['persen_penyimpangan'] !== null ? (float) $data['persen_penyimpangan'] : null);
        $sheet->setCellValue('F' . $row, $data['fluktuasi_persen'] !== null ? (float) $data['fluktuasi_persen'] : null);
        $sheet->setCellValue('G' . $row, $data['stabilitas_persen'] !== null ? (float) $data['stabilitas_persen'] : null);
        $sheet->setCellValue('H' . $row, $data['persen_naik_turun'] !== null ? (float) $data['persen_naik_turun'] : null);
        $sheet->setCellValue('I' . $row, $data['naik_turun_rp'] !== null ? (float) $data['naik_turun_rp'] : null);
        $row++;
    }
}

$filename = sprintf('data_harga_%s_%s_sampai_%s.xlsx', $label, $startStr, $endStr);

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;

