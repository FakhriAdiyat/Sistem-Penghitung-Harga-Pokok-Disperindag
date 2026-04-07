<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';
$allowed_roles = ['admin', 'member'];
require_once __DIR__ . '/../includes/role_check.php';
require_once __DIR__ . '/laporan_helpers.php';

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

date_default_timezone_set('Asia/Jakarta');

function laporanRedirect(string $location): void
{
    header('Location: ' . $location);
    exit;
}

function laporanSetNumberCell($sheet, string $cell, $value, string $formatCode = '#,##0'): void
{
    if ($value === null || $value === '') {
        $sheet->setCellValue($cell, null);
        return;
    }

    $sheet->setCellValue($cell, (float) $value);
    $sheet->getStyle($cell)->getNumberFormat()->setFormatCode($formatCode);
}

$latestTanggal = laporanGetLatestTanggal($conn);
$tanggalLaporan = laporanNormalizeTanggalInput($_GET['tanggal_laporan'] ?? '', $latestTanggal);
$tanggalLaporanSafe = mysqli_real_escape_string($conn, $tanggalLaporan);

$tanggalPembanding = null;
$tanggalPembandingResult = mysqli_query(
    $conn,
    "SELECT MAX(tanggal) AS tanggal_pembanding FROM harga WHERE tanggal < '$tanggalLaporanSafe'"
);
if ($tanggalPembandingResult) {
    $tanggalPembandingRow = mysqli_fetch_assoc($tanggalPembandingResult);
    if (!empty($tanggalPembandingRow['tanggal_pembanding'])) {
        $tanggalPembanding = $tanggalPembandingRow['tanggal_pembanding'];
    }
}

$templatePath = __DIR__ . '/../assets/Format/Format Laporan.xlsx';
if (!is_file($templatePath)) {
    laporanRedirect('laporan.php?err=template_missing&tanggal_laporan=' . urlencode($tanggalLaporan));
}

$currentRows = laporanFetchLatestRowsByDate($conn, $tanggalLaporan);
$currentMap = laporanBuildTemplateRowMap($currentRows);

if (empty($currentMap)) {
    laporanRedirect('laporan.php?err=no_data&tanggal_laporan=' . urlencode($tanggalLaporan));
}

$previousMap = [];
if ($tanggalPembanding !== null) {
    $previousRows = laporanFetchLatestRowsByDate($conn, $tanggalPembanding);
    $previousMap = laporanBuildTemplateRowMap($previousRows);
}

$spreadsheet = IOFactory::load($templatePath);
$sheet = $spreadsheet->getSheet(0);

$sheet->setCellValueExplicit('C14', laporanFormatTanggalIndonesia($tanggalLaporan), DataType::TYPE_STRING);
$sheet->setCellValueExplicit('I14', 'Semua Pasar', DataType::TYPE_STRING);

foreach (laporanTemplateDefinitions() as $definition) {
    $rowNumber = (int) $definition['row'];

    foreach (['D', 'E', 'F', 'G', 'H', 'I'] as $column) {
        $sheet->setCellValue($column . $rowNumber, null);
    }

    $current = $currentMap[$rowNumber] ?? null;
    $previous = $previousMap[$rowNumber] ?? null;

    $hetHap = null;
    if ($current !== null && isset($current['het_hap']) && (float) $current['het_hap'] > 0) {
        $hetHap = (float) $current['het_hap'];
    } elseif ($previous !== null && isset($previous['het_hap']) && (float) $previous['het_hap'] > 0) {
        $hetHap = (float) $previous['het_hap'];
    }

    $hargaKemarin = $previous !== null && isset($previous['harga']) ? (float) $previous['harga'] : null;
    $hargaHariIni = $current !== null && isset($current['harga']) ? (float) $current['harga'] : null;
    $perubahanRp = null;
    $perubahanPersen = null;
    $terhadapHetHap = null;

    if ($hargaKemarin !== null && $hargaHariIni !== null) {
        $perubahanRp = $hargaHariIni - $hargaKemarin;
        if ((float) $hargaKemarin !== 0.0) {
            $perubahanPersen = round(($perubahanRp / $hargaKemarin) * 100, 2);
        } elseif ((float) $hargaHariIni === 0.0) {
            $perubahanPersen = 0.0;
        }
    }

    if ($hargaHariIni !== null && $hetHap !== null) {
        $terhadapHetHap = $hargaHariIni - $hetHap;
    }

    laporanSetNumberCell($sheet, 'D' . $rowNumber, $hetHap);
    laporanSetNumberCell($sheet, 'E' . $rowNumber, $hargaKemarin);
    laporanSetNumberCell($sheet, 'F' . $rowNumber, $hargaHariIni);
    laporanSetNumberCell($sheet, 'G' . $rowNumber, $perubahanRp);
    laporanSetNumberCell($sheet, 'H' . $rowNumber, $perubahanPersen, '#,##0.00');
    laporanSetNumberCell($sheet, 'I' . $rowNumber, $terhadapHetHap);
}

$filename = 'Laporan_Harga_' . $tanggalLaporan . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
