<?php
require_once '../config/database.php';
require_once '../includes/auth_check.php';
$allowed_roles = ['admin', 'member'];
require_once '../includes/role_check.php';
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

function exportRedirectToList(string $query = ''): void
{
    $location = '../pages/list.php';
    if ($query !== '') {
        $location .= '?' . ltrim($query, '?&');
    }

    header('Location: ' . $location);
    exit;
}

function exportResolveRange(DateTime $today, string $period, string $startDateParam, string $endDateParam, string $yearParam = ''): array
{
    if (preg_match('/^\d{4}$/', $yearParam)) {
        $year = (int) $yearParam;

        return [
            new DateTime(sprintf('%04d-01-01', $year)),
            new DateTime(sprintf('%04d-12-31', $year)),
            'tahunan',
        ];
    }

    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDateParam) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDateParam)) {
        $start = new DateTime($startDateParam);
        $end = new DateTime($endDateParam);
        if ($start > $end) {
            [$start, $end] = [$end, $start];
        }

        return [$start, $end, 'periode'];
    }

    if ($period === 'weekly') {
        return [(clone $today)->modify('-6 days'), clone $today, 'mingguan'];
    }

    if ($period === 'monthly') {
        return [new DateTime($today->format('Y-m-01')), clone $today, 'bulanan'];
    }

    if ($period === 'yearly') {
        return [new DateTime($today->format('Y-01-01')), clone $today, 'tahunan'];
    }

    exportRedirectToList('err=invalid');
}

function exportTemplateCommodityName(string $name): string
{
    $name = trim(preg_replace('/\s+/u', ' ', $name));
    if ($name === '') {
        return '';
    }

    static $aliases = null;
    if ($aliases === null) {
        $aliases = [
            'Beras Ketan Putih' => 'Beras Ketan Putih',
            'Beras ketan Putih' => 'Beras Ketan Putih',
            'Buah-Buahan Jeruk' => 'Buah Jeruk',
            'Buah-Buahan Pisang' => 'Buah Pisang',
            'Cabai Biasa Merah' => 'Cabai Merah Biasa',
            'Cabai Biasa Hijau' => 'Cabai Hijau Biasa',
            'Daging Ayam Ayam Broiler' => 'Daging Ayam Broiler',
            'Daging Ayam Ayam Kampung' => 'Daging Ayam Kampung',
            'Daging Sapi Paha Depan' => 'Daging Sapi Paha Depan',
            'Daging Sapi murni Paha Depan' => 'Daging Sapi Paha Depan',
            'Daging Sapi Sapi murni Paha Depan' => 'Daging Sapi Paha Depan',
            'Daging Sapi Paha Belakang' => 'Daging Sapi Paha Belakang',
            'Daging Sapi murni Paha Belakang' => 'Daging Sapi Paha Belakang',
            'Daging Sapi Sapi murni Paha Belakang' => 'Daging Sapi Paha Belakang',
            'Daging Sapi Tetelan' => 'Daging Sapi Tetelan',
            'Daging Sapi Sapi Tetelan' => 'Daging Sapi Tetelan',
            'Daging Sapi Sirloin (Has Luar)' => 'Daging Sapi Sirloin (Has Luar)',
            'Daging Sapi Sapi Sirloin (Has Luar)' => 'Daging Sapi Sirloin (Has Luar)',
            'Daging Sapi Tenderloin (Has Dalam)' => 'Daging Sapi Tenderloin (Has Dalam)',
            'Daging Sapi Tanderloin (Has Dalam)' => 'Daging Sapi Tenderloin (Has Dalam)',
            'Daging Sapi Sapi Tanderloin (Has Dalam)' => 'Daging Sapi Tenderloin (Has Dalam)',
            'Daging Sapi Sandung Lemur' => 'Daging Sapi Sandung Lemur',
            'Daging Sapi Sapi Sandung Lemur' => 'Daging Sapi Sandung Lemur',
            'Garam Beriodium Bata' => 'Garam Bata',
            'Garam Beriodium Halus (250gr)' => 'Garam Halus',
            'Ikan Teri Asin Medan' => 'Ikan Asin Medan',
            'Ikan Teri Asin Kering' => 'Ikan Asin Kering',
            'Ikan Kembung Besar' => 'Ikan kembung Besar',
            'Ikan Ikan Mas' => 'Ikan Mas',
            'Ikan Udang Sedang' => 'Ikan Udang',
            'Jagung Jagung Manis' => 'Jagung Manis',
            'Jagung Pipilan Kering (bknu/ unggas' => 'Jagung Pipilan',
            'Kacang Kedelai' => 'Kacang Kedelai Lokal',
            'Minyak Goreng Tanpa merk (Curah)' => 'Minyak Goreng Curah',
            'Minyak Goreng Sanco botol 1ltr' => 'Minyak Goreng Sanco',
            'Susu Bubuk Balita SGM' => 'Susu Bubuk SGM',
            'Susu Bubuk Merk Dencow' => 'Susu Bubuk Dencow',
            'Susu Kental Manis Merk Bendera' => 'Susu Kental Manis Bendera',
            'Susu Kental Manis Merk Indomilk' => 'Susu Kental Manis Indomilk',
            'Timun Sedang' => 'Timun',
        ];
    }

    return $aliases[$name] ?? $name;
}

function exportTemplateMetricLabel(string $templateType): string
{
    return $templateType === 'koefisien' ? 'Koefisien Variasi Harga' : 'Stabilitas Harga';
}

function exportTemplateFilePath(string $templateType): string
{
    if ($templateType === 'koefisien') {
        return __DIR__ . '/../assets/Format/Format Koefisien Variasi Harga.xlsx';
    }

    return __DIR__ . '/../assets/Format/Format Stabilitas Harga.xlsx';
}

function exportSetNumberCell($sheet, string $cell, $value, string $formatCode = '0.00'): void
{
    if ($value === null || $value === '') {
        $sheet->setCellValue($cell, null);
        return;
    }

    $sheet->setCellValue($cell, (float) $value);
    $sheet->getStyle($cell)->getNumberFormat()->setFormatCode($formatCode);
}

function exportTemplateMonthFormat(string $templateType): string
{
    if ($templateType === 'stabilitas') {
        return '0.00"%"';
    }

    return 'Rp #,##0';
}

function exportBuildTemplateRowMap($sheet): array
{
    $map = [];
    for ($row = 5; $row <= $sheet->getHighestRow(); $row++) {
        $label = trim((string) $sheet->getCell('B' . $row)->getValue());
        if ($label === '') {
            continue;
        }

        $map[exportTemplateCommodityName($label)] = $row;
    }

    return $map;
}

function exportAverage(array $values): ?float
{
    if (empty($values)) {
        return null;
    }

    return array_sum($values) / count($values);
}

function exportSampleStdev(array $values): ?float
{
    $count = count($values);
    if ($count === 0) {
        return null;
    }

    if ($count === 1) {
        return 0.0;
    }

    $mean = exportAverage($values);
    if ($mean === null) {
        return null;
    }

    $sum = 0.0;
    foreach ($values as $value) {
        $sum += pow((float) $value - $mean, 2);
    }

    return sqrt($sum / ($count - 1));
}

function exportComputeTemplateMetrics(mysqli $conn, string $startStr, string $endStr, string $searchSafe): array
{
    $sql = "
        SELECT
            h.tanggal,
            b.nama_bahan,
            h.harga
        FROM harga h
        INNER JOIN (
            SELECT bahan_id, tanggal, MAX(id) AS latest_id
            FROM harga
            WHERE tanggal BETWEEN '$startStr' AND '$endStr'
            GROUP BY bahan_id, tanggal
        ) latest ON latest.latest_id = h.id
        INNER JOIN bahan_pokok b ON b.id = h.bahan_id
        WHERE b.nama_bahan LIKE '%$searchSafe%'
        ORDER BY b.nama_bahan ASC, h.tanggal ASC, h.id ASC
    ";

    $query = mysqli_query($conn, $sql);
    $pricesByCommodityMonth = [];

    if ($query) {
        while ($row = mysqli_fetch_assoc($query)) {
            $templateName = exportTemplateCommodityName((string) ($row['nama_bahan'] ?? ''));
            if ($templateName === '') {
                continue;
            }

            $month = (int) date('n', strtotime((string) $row['tanggal']));
            $pricesByCommodityMonth[$templateName][$month][] = (float) $row['harga'];
        }
    }

    $metrics = [
        'koefisien' => [],
        'stabilitas' => [],
    ];

    foreach ($pricesByCommodityMonth as $commodity => $months) {
        foreach ($months as $month => $prices) {
            $average = exportAverage($prices);
            $stdev = exportSampleStdev($prices);

            if ($average === null || (float) $average === 0.0) {
                $coef = null;
            } else {
                $coef = round(($stdev / $average) * 100, 2);
            }

            if ($coef === null) {
                $stability = null;
            } else {
                $stability = round(100 - $coef, 2);
                $stability = max(0, min(100, $stability));
            }

            $metrics['koefisien'][$commodity][$month] = $average === null ? null : round($average);
            $metrics['stabilitas'][$commodity][$month] = $stability;
        }
    }

    return $metrics;
}

function exportDownloadTemplate(string $templateType, mysqli $conn, string $startStr, string $endStr, DateTime $start, DateTime $end, string $searchSafe): void
{
    $templatePath = exportTemplateFilePath($templateType);
    if (!is_file($templatePath)) {
        exportRedirectToList('err=template_missing');
    }

    $metrics = exportComputeTemplateMetrics($conn, $startStr, $endStr, $searchSafe);
    $spreadsheet = IOFactory::load($templatePath);
    $sheet = $spreadsheet->getSheet(0);
    $rowMap = exportBuildTemplateRowMap($sheet);

    $sheet->setCellValue('A3', 'Periode: ' . $start->format('d M Y') . ' - ' . $end->format('d M Y'));
    $monthFormatCode = exportTemplateMonthFormat($templateType);

    for ($month = 1; $month <= 12; $month++) {
        $column = Coordinate::stringFromColumnIndex($month + 2);
        foreach ($rowMap as $rowNumber) {
            exportSetNumberCell($sheet, $column . $rowNumber, null);
        }
    }

    foreach ($rowMap as $commodityName => $rowNumber) {
        for ($month = 1; $month <= 12; $month++) {
            $value = $metrics[$templateType][$commodityName][$month] ?? null;
            $column = Coordinate::stringFromColumnIndex($month + 2);
            exportSetNumberCell($sheet, $column . $rowNumber, $value, $monthFormatCode);
        }

        if ($templateType === 'stabilitas') {
            $averageCell = 'O' . $rowNumber;
            $sheet->setCellValue(
                $averageCell,
                sprintf('=IF(COUNTA(C%d:N%d)=0,"",MIN(100,AVERAGE(C%d:N%d)))', $rowNumber, $rowNumber, $rowNumber, $rowNumber)
            );
            $sheet->getStyle($averageCell)->getNumberFormat()->setFormatCode('0.00"%"');
        }
    }

    $filename = sprintf(
        '%s_%s_sampai_%s.xlsx',
        str_replace(' ', '_', strtolower(exportTemplateMetricLabel($templateType))),
        $startStr,
        $endStr
    );

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
}

$templateType = strtolower(trim((string) ($_GET['template_type'] ?? '')));
$period = strtolower(trim((string) ($_GET['period'] ?? '')));
$search = trim((string) ($_GET['search'] ?? ''));
$searchSafe = mysqli_real_escape_string($conn, $search);
$yearParam = trim((string) ($_GET['year'] ?? ''));
$startDateParam = trim((string) ($_GET['start_date'] ?? ''));
$endDateParam = trim((string) ($_GET['end_date'] ?? ''));

$today = new DateTime('today');
$maxRes = mysqli_query($conn, "SELECT MAX(tanggal) AS max_tanggal FROM harga");
if ($maxRes) {
    $maxRow = mysqli_fetch_assoc($maxRes);
    $maxTanggal = $maxRow['max_tanggal'] ?? null;
    if (is_string($maxTanggal) && $maxTanggal !== '') {
        try {
            $today = new DateTime($maxTanggal);
        } catch (Exception $e) {
            // Keep fallback date.
        }
    }
}

[$start, $end, $label] = exportResolveRange($today, $period, $startDateParam, $endDateParam, $yearParam);
$startStr = $start->format('Y-m-d');
$endStr = $end->format('Y-m-d');

if (in_array($templateType, ['koefisien', 'stabilitas'], true)) {
    exportDownloadTemplate($templateType, $conn, $startStr, $endStr, $start, $end, $searchSafe);
}

$cols = mysqli_query($conn, "SHOW COLUMNS FROM harga LIKE 'persen_penyimpangan'");
$pakaiStrukturBaru = ($cols && mysqli_num_rows($cols) > 0);

if ($pakaiStrukturBaru) {
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
          AND b.nama_bahan LIKE '%$searchSafe%'
        ORDER BY h.tanggal ASC, b.nama_bahan ASC
    "
    );
} else {
    $query = mysqli_query(
        $conn,
        "
        SELECT
            h.tanggal,
            b.nama_bahan,
            h.harga,
            h.rata_rata,
            h.rata_penyimpangan,
            h.fluktuasi_persen,
            h.stabilitas_persen,
            h.persen_kenaikan,
            h.persen_penurunan,
            h.kenaikan_rp,
            h.penurunan_rp
        FROM harga h
        JOIN bahan_pokok b ON h.bahan_id = b.id
        WHERE h.tanggal BETWEEN '$startStr' AND '$endStr'
          AND b.nama_bahan LIKE '%$searchSafe%'
        ORDER BY h.tanggal ASC, b.nama_bahan ASC
    "
    );
}

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
        if (!$pakaiStrukturBaru) {
            $persenPenyimpangan = null;
            if (!empty($data['rata_rata']) && (float) $data['rata_rata'] > 0 && isset($data['rata_penyimpangan']) && $data['rata_penyimpangan'] !== null) {
                $persenPenyimpangan = round(((float) $data['rata_penyimpangan'] / (float) $data['rata_rata']) * 100, 2);
            }

            $persenNaikTurun = null;
            $naikTurunRp = null;
            if (!empty($data['persen_kenaikan']) && (float) $data['persen_kenaikan'] > 0) {
                $persenNaikTurun = (float) $data['persen_kenaikan'];
                $naikTurunRp = !empty($data['kenaikan_rp']) ? (float) $data['kenaikan_rp'] : null;
            } elseif (!empty($data['persen_penurunan']) && (float) $data['persen_penurunan'] > 0) {
                $persenNaikTurun = -(float) $data['persen_penurunan'];
                $naikTurunRp = !empty($data['penurunan_rp']) ? -(float) $data['penurunan_rp'] : null;
            }

            $data['persen_penyimpangan'] = $persenPenyimpangan;
            $data['persen_naik_turun'] = $persenNaikTurun;
            $data['naik_turun_rp'] = $naikTurunRp;
        }

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
