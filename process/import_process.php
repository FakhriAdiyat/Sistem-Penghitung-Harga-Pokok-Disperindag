<?php
require_once '../config/database.php';
require_once '../includes/auth_check.php';
$allowed_roles = ['admin', 'member'];
require_once '../includes/role_check.php';
require_once '../includes/update_statistik_harga.php';
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as SpreadsheetDate;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

if (session_status() === PHP_SESSION_ACTIVE) {
    session_write_close();
}

function setImportFlashAndRedirect(string $type, string $title, string $message, array $meta = [], string $redirectTo = 'import.php'): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    $_SESSION['import_flash'] = [
        'type' => $type, // success | error
        'title' => $title,
        'message' => $message,
        'meta' => $meta,
    ];
    // File ini berada di folder `process`, jadi redirect harus kembali ke `../pages/`
    header("Location: ../pages/" . ltrim($redirectTo, '/'));
    exit;
}

function normalizeImportText($value)
{
    if ($value === null) {
        return '';
    }

    $text = trim((string) $value);
    $text = preg_replace('/\s+/u', ' ', $text);

    return trim((string) $text);
}

function cleanImportedNamaBahan($value)
{
    $nama = normalizeImportText($value);
    if ($nama === '') {
        return '';
    }

    $nama = preg_replace('/\*+/u', '', $nama);
    $nama = preg_replace('/\s+/u', ' ', $nama);

    return trim((string) $nama);
}

function getMonthNumberFromName($monthName)
{
    static $monthMap = [
        'januari' => 1,
        'january' => 1,
        'fenuari' => 2,
        'feruari' => 2,
        'februari' => 2,
        'february' => 2,
        'maret' => 3,
        'march' => 3,
        'april' => 4,
        'mei' => 5,
        'may' => 5,
        'juni' => 6,
        'june' => 6,
        'juli' => 7,
        'july' => 7,
        'agustus' => 8,
        'august' => 8,
        'september' => 9,
        'oktober' => 10,
        'october' => 10,
        'november' => 11,
        'desember' => 12,
        'december' => 12,
    ];

    $key = strtolower(normalizeImportText($monthName));

    return $monthMap[$key] ?? null;
}

function extractMonthYearFromText($text)
{
    $text = normalizeImportText($text);
    if ($text === '') {
        return null;
    }

    if (preg_match('/(januari|january|fenuari|feruari|februari|february|maret|march|april|mei|may|juni|june|juli|july|agustus|august|september|oktober|october|november|desember|december)\s+(\d{4})/iu', $text, $m)) {
        $month = getMonthNumberFromName($m[1]);
        if ($month !== null) {
            return [
                'month' => $month,
                'year' => (int) $m[2],
            ];
        }
    }

    return null;
}

function tryParseTanggal($value)
{
    if ($value === null || $value === '') {
        return null;
    }

    if (is_numeric($value)) {
        try {
            return date('Y-m-d', SpreadsheetDate::excelToTimestamp((float) $value));
        } catch (\Throwable $e) {
            // Abaikan nilai numerik yang bukan serial tanggal Excel.
        }
    }

    $text = normalizeImportText($value);
    if ($text === '') {
        return null;
    }

    if (preg_match('/(\d{4})-(\d{2})-(\d{2})/', $text, $m)) {
        return sprintf('%04d-%02d-%02d', (int) $m[1], (int) $m[2], (int) $m[3]);
    }

    if (preg_match('/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{2,4})/', $text, $m)) {
        $year = strlen($m[3]) === 2 ? (int) ('20' . $m[3]) : (int) $m[3];

        return sprintf('%04d-%02d-%02d', $year, (int) $m[2], (int) $m[1]);
    }

    if (preg_match('/(\d{1,2})\s+(januari|january|fenuari|feruari|februari|february|maret|march|april|mei|may|juni|june|juli|july|agustus|august|september|oktober|october|november|desember|december)\s+(\d{2,4})/iu', $text, $m)) {
        $month = getMonthNumberFromName($m[2]);
        $year = strlen($m[3]) === 2 ? (int) ('20' . $m[3]) : (int) $m[3];
        if ($month !== null && checkdate($month, (int) $m[1], $year)) {
            return sprintf('%04d-%02d-%02d', $year, $month, (int) $m[1]);
        }
    }

    $timestamp = strtotime($text);
    if ($timestamp !== false) {
        return date('Y-m-d', $timestamp);
    }

    return null;
}

function parseHargaValue($value)
{
    if ($value === null || $value === '') {
        return null;
    }

    if (is_numeric($value)) {
        return (float) $value;
    }

    $text = normalizeImportText($value);
    if ($text === '') {
        return null;
    }

    $text = preg_replace('/[^0-9,.\-]/u', '', $text);
    if ($text === '' || $text === '-' || $text === '--') {
        return null;
    }

    $hasComma = strpos($text, ',') !== false;
    $hasDot = strpos($text, '.') !== false;

    if ($hasComma && $hasDot) {
        $lastComma = strrpos($text, ',');
        $lastDot = strrpos($text, '.');
        $decimalSeparator = $lastComma > $lastDot ? ',' : '.';
        $thousandSeparator = $decimalSeparator === ',' ? '.' : ',';
        $text = str_replace($thousandSeparator, '', $text);
        if ($decimalSeparator === ',') {
            $text = str_replace(',', '.', $text);
        }
    } elseif ($hasComma || $hasDot) {
        $separator = $hasComma ? ',' : '.';
        $parts = explode($separator, $text);

        if (count($parts) > 2) {
            $text = implode('', $parts);
        } else {
            $left = $parts[0];
            $right = $parts[1] ?? '';

            if ($right === '') {
                $text = $left;
            } elseif (strlen($right) === 3) {
                $text = $left . $right;
            } else {
                $text = $left . '.' . $right;
            }
        }
    }

    return is_numeric($text) ? (float) $text : null;
}

function extractGlobalDateFromRows($rows, $headerRow)
{
    for ($r = 0; $r < min($headerRow + 1, 10); $r++) {
        $row = array_values((array) ($rows[$r] ?? []));
        foreach ($row as $i => $cell) {
            $text = strtolower(normalizeImportText($cell));
            if ($text === '' || !preg_match('/\b(tanggal|tgl|date)\b/i', $text)) {
                continue;
            }

            for ($offset = 0; $offset <= 3; $offset++) {
                if (!array_key_exists($i + $offset, $row)) {
                    continue;
                }

                $parsed = tryParseTanggal($row[$i + $offset]);
                if ($parsed !== null) {
                    return $parsed;
                }
            }
        }
    }

    return null;
}

$redirectTo = (isset($_POST['redirect_to']) && $_POST['redirect_to'] === 'list') ? 'list.php' : 'import.php';

if (!isset($_FILES['file_import'])) {
    setImportFlashAndRedirect('error', 'Import gagal', 'File tidak ditemukan. Silakan pilih file dan coba lagi.', [], $redirectTo);
}

$file = $_FILES['file_import'];
$ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

$het_input = $_POST['het_hap'] ?? null;
$bahan_keyword = $_POST['bahan_keyword'] ?? '';

$format_didukung = ['csv', 'xlsx', 'xls', 'ods', 'pdf'];
if (!in_array($ext, $format_didukung)) {
    setImportFlashAndRedirect(
        'error',
        'Import gagal',
        'Format file tidak didukung. Gunakan: Excel (.xlsx, .xls), ODS, CSV, atau PDF. Isi file harus memuat: Tanggal, Nama Bahan, dan Harga.',
        [],
        $redirectTo
    );
}

/* ================= UPDATE HET (OPSIONAL) ================= */
if (!empty($het_input) && !empty($bahan_keyword)) {
    $het_input = mysqli_real_escape_string($conn, $het_input);
    $bahan_keyword = mysqli_real_escape_string($conn, $bahan_keyword);
    mysqli_query($conn, "
        UPDATE bahan_pokok 
        SET het_hap = '$het_input'
        WHERE nama_bahan LIKE '%$bahan_keyword%'
    ");
}

/**
 * Format TPID (contoh: TPID Karawang - Tanggal di satu baris, header Komoditas / Hari Ini, data hierarki).
 * Return array of rows atau [] jika tidak dikenali.
 */
function getRowsFromSpreadsheetTPID($file_path) {
    $spreadsheet = IOFactory::load($file_path);
    $sheet = $spreadsheet->getActiveSheet();
    $highestRow = $sheet->getHighestRow();
    $colCount = Coordinate::columnIndexFromString($sheet->getHighestColumn());

    $tanggal = date('Y-m-d');
    $headerRow = null;
    $dataStartRow = null;
    $idxHarga = 6;

    for ($r = 1; $r <= min(25, $highestRow); $r++) {
        $rowText = '';
        for ($c = 1; $c <= min(10, $colCount); $c++) {
            $v = $sheet->getCell(Coordinate::stringFromColumnIndex($c) . $r)->getValue();
            $rowText .= ' ' . (is_string($v) ? $v : (string)$v);
        }
        if (preg_match('/Tanggal\s*:\s*(\d{1,2})\s+(Fenuari|Feruari|Februari|Januari|Maret|April|Mei|Juni|Juli|Agustus|September|Oktober|November|Desember)\s+(\d{4})/ui', $rowText, $m)) {
            $bulanId = $m[2];
            if (preg_match('/Fe[rn]uari|Februari/i', $bulanId)) $bulanEn = 'February';
            else {
                $map = ['Januari'=>'January','Maret'=>'March','April'=>'April','Mei'=>'May','Juni'=>'June','Juli'=>'July','Agustus'=>'August','September'=>'September','Oktober'=>'October','November'=>'November','Desember'=>'December'];
                $bulanEn = $map[$bulanId] ?? 'January';
            }
            $ts = strtotime((int)$m[1] . ' ' . $bulanEn . ' ' . (int)$m[3]);
            $tanggal = $ts ? date('Y-m-d', $ts) : date('Y-m-d');
        }
        $b = trim((string)$sheet->getCell('B' . $r)->getValue());
        $f = $sheet->getCell('F' . $r)->getValue();
        $e = $sheet->getCell('E' . $r)->getValue();
        if (preg_match('/Komoditas|^No\.?$/i', $b)) {
            $headerRow = $r;
        }
        if ($headerRow !== null && $r > $headerRow && (preg_match('/Hari Ini|Kemarin/i', (string)$f . (string)$e) || (trim($b) !== '' && is_numeric($f)))) {
            $dataStartRow = preg_match('/Hari Ini|Kemarin/i', (string)$f . (string)$e) ? $r + 1 : $r;
            break;
        }
    }
    if ($dataStartRow === null && $headerRow !== null) {
        $dataStartRow = $headerRow + 2;
    }
    if ($dataStartRow === null) {
        return [];
    }

    $out = [];
    $parent = '';
    for ($r = $dataStartRow; $r <= $highestRow; $r++) {
        $a = $sheet->getCell('A' . $r)->getValue();
        $b = trim((string)$sheet->getCell('B' . $r)->getValue());
        $c = trim((string)$sheet->getCell('C' . $r)->getValue());
        $f = $sheet->getCell('F' . $r)->getValue();
        $harga = parseHargaValue($f);
        if ($b === '' && $harga === null) continue;
        if (is_numeric($a) && $a > 0) {
            $parent = cleanImportedNamaBahan($b);
            if ($harga !== null && $harga > 0) {
                $out[] = ['tanggal' => $tanggal, 'nama_bahan' => $parent, 'harga' => $harga, 'kategori' => '', 'satuan' => $c];
            }
            continue;
        }
        if ($b !== '' && $harga !== null && $harga > 0) {
            $nama = $parent !== '' ? $parent . ' ' . cleanImportedNamaBahan($b) : cleanImportedNamaBahan($b);
            $out[] = ['tanggal' => $tanggal, 'nama_bahan' => $nama, 'harga' => $harga, 'kategori' => '', 'satuan' => $c];
        }
    }
    return $out;
}

/**
 * Cek apakah satu baris (array cell) berisi header yang punya minimal: tanggal, nama/barang, harga.
 * Return: ['tanggal'=>i, 'nama'=>i, 'harga'=>i, 'kategori'=>i, 'satuan'=>i] atau null.
 */
function detectHeaderRow($row) {
    $idx_tanggal = $idx_nama = $idx_harga = $idx_kategori = $idx_satuan = null;
    $nonEmptyCells = 0;

    foreach ((array) $row as $i => $cell) {
        $c = strtolower(normalizeImportText($cell));
        if ($c !== '') {
            $nonEmptyCells++;
        }
        if (preg_match('/tanggal|tgl|date/', $c)) $idx_tanggal = $i;
        if (preg_match('/nama|barang|bahan|item|komodit/i', $c)) $idx_nama = $i;
        if (preg_match('/harga|price/', $c)) $idx_harga = $i;
        if (preg_match('/kategori|category/', $c)) $idx_kategori = $i;
        if (preg_match('/satuan|unit/', $c)) $idx_satuan = $i;
    }

    if ($nonEmptyCells < 2) {
        return null;
    }

    if ($idx_nama !== null && $idx_harga !== null && $idx_nama !== $idx_harga) {
        return [
            'tanggal' => $idx_tanggal,
            'nama' => $idx_nama,
            'harga' => $idx_harga,
            'kategori' => $idx_kategori,
            'satuan' => $idx_satuan,
        ];
    }
    return null;
}

/**
 * Ekstrak baris dari file Excel (xlsx/xls/ods).
 * Mencari baris header di 10 baris pertama yang berisi: Tanggal, Nama Bahan, Harga (urutan bebas).
 * Return: array of ['tanggal','nama_bahan','harga','kategori','satuan'].
 */
function getRowsFromSpreadsheet($file_path) {
    $spreadsheet = IOFactory::load($file_path);
    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray();
    if (empty($rows)) return [];

    $headerRow = null;
    $indices = null;
    for ($r = 0; $r < min(10, count($rows)); $r++) {
        $indices = detectHeaderRow($rows[$r]);
        if ($indices !== null) {
            $headerRow = $r;
            break;
        }
    }
    if ($indices === null) {
        $indices = ['tanggal' => 0, 'nama' => 1, 'harga' => 2, 'kategori' => null, 'satuan' => null];
        $headerRow = 0;
    }

    $defaultTanggal = extractGlobalDateFromRows($rows, $headerRow) ?? date('Y-m-d');
    $idx_tanggal = $indices['tanggal'];
    $idx_nama = $indices['nama'];
    $idx_harga = $indices['harga'];
    $idx_kategori = $indices['kategori'];
    $idx_satuan = $indices['satuan'];
    if ($idx_tanggal === null) $idx_tanggal = -1;
    if ($idx_kategori === null) $idx_kategori = -1;
    if ($idx_satuan === null) $idx_satuan = -1;

    $out = [];
    for ($i = $headerRow + 1; $i < count($rows); $i++) {
        $row = $rows[$i];
        $nama = isset($row[$idx_nama]) ? cleanImportedNamaBahan($row[$idx_nama]) : '';
        $harga = isset($row[$idx_harga]) ? $row[$idx_harga] : null;
        if ($nama === '' && $harga === null) continue;

        $tanggal = $defaultTanggal;
        if ($idx_tanggal >= 0 && isset($row[$idx_tanggal]) && $row[$idx_tanggal] !== '') {
            $parsedTanggal = tryParseTanggal($row[$idx_tanggal]);
            if ($parsedTanggal !== null) {
                $tanggal = $parsedTanggal;
            }
        }
        $harga = parseHargaValue($harga);
        if ($harga === null || $harga <= 0) continue;

        $kategori = ($idx_kategori >= 0 && isset($row[$idx_kategori])) ? normalizeImportText($row[$idx_kategori]) : '';
        $satuan = ($idx_satuan >= 0 && isset($row[$idx_satuan])) ? normalizeImportText($row[$idx_satuan]) : '';

        $out[] = [
            'tanggal' => $tanggal,
            'nama_bahan' => $nama,
            'harga' => $harga,
            'kategori' => $kategori,
            'satuan' => $satuan,
        ];
    }
    return $out;
}

function getRowsFromSpreadsheetMonthlyReport($file_path, &$detectedFormat = false) {
    $spreadsheet = IOFactory::load($file_path);
    $sheet = $spreadsheet->getActiveSheet();
    $highestRow = $sheet->getHighestRow();
    $colCount = Coordinate::columnIndexFromString($sheet->getHighestColumn());

    $layout = null;
    for ($r = 1; $r <= min(15, $highestRow - 1); $r++) {
        $firstColText = strtolower(normalizeImportText($sheet->getCell('A' . $r)->getValue()));
        if ($firstColText !== 'no') {
            continue;
        }

        $namaCol = null;
        $tanggalCol = null;
        for ($c = 1; $c <= min($colCount, 15); $c++) {
            $cellText = strtolower(normalizeImportText($sheet->getCell(Coordinate::stringFromColumnIndex($c) . $r)->getValue()));
            if ($cellText === '') {
                continue;
            }

            if ($namaCol === null && preg_match('/nama|barang|bahan|item|komodit/i', $cellText)) {
                $namaCol = $c;
            }
            if ($tanggalCol === null && preg_match('/tanggal|tgl|date/i', $cellText)) {
                $tanggalCol = $c;
            }
        }

        if ($namaCol === null || $tanggalCol === null) {
            continue;
        }

        $dayColumns = [];
        for ($c = $tanggalCol; $c <= $colCount; $c++) {
            $dayValue = $sheet->getCell(Coordinate::stringFromColumnIndex($c) . ($r + 1))->getValue();
            if (is_numeric($dayValue)) {
                $day = (int) $dayValue;
                if ($day >= 1 && $day <= 31) {
                    $dayColumns[$c] = $day;
                    continue;
                }
            }

            if (!empty($dayColumns)) {
                break;
            }
        }

        if (count($dayColumns) >= 28) {
            $layout = [
                'data_start_row' => $r + 2,
                'nama_col' => $namaCol,
                'day_columns' => $dayColumns,
            ];
            break;
        }
    }

    if ($layout === null) {
        return [];
    }

    $detectedFormat = true;
    $period = null;
    for ($r = 1; $r <= min(10, $highestRow); $r++) {
        $rowText = '';
        for ($c = 1; $c <= min(6, $colCount); $c++) {
            $rowText .= ' ' . normalizeImportText($sheet->getCell(Coordinate::stringFromColumnIndex($c) . $r)->getValue());
        }

        $period = extractMonthYearFromText($rowText);
        if ($period !== null) {
            break;
        }
    }

    if ($period === null) {
        return [];
    }

    $out = [];
    for ($r = $layout['data_start_row']; $r <= $highestRow; $r++) {
        $nama = cleanImportedNamaBahan($sheet->getCell(Coordinate::stringFromColumnIndex($layout['nama_col']) . $r)->getValue());
        if ($nama === '') {
            continue;
        }

        foreach ($layout['day_columns'] as $columnIndex => $day) {
            if (!checkdate($period['month'], $day, $period['year'])) {
                continue;
            }

            $harga = parseHargaValue($sheet->getCell(Coordinate::stringFromColumnIndex($columnIndex) . $r)->getValue());
            if ($harga === null || $harga <= 0) {
                continue;
            }

            $out[] = [
                'tanggal' => sprintf('%04d-%02d-%02d', $period['year'], $period['month'], $day),
                'nama_bahan' => $nama,
                'harga' => $harga,
                'kategori' => '',
                'satuan' => '',
            ];
        }
    }

    return $out;
}

/**
 * Ekstrak baris dari CSV. Mencari baris header di 10 baris pertama yang berisi: Tanggal, Nama Bahan, Harga.
 */
function getRowsFromCsv($file_path) {
    $handle = fopen($file_path, 'r');
    if (!$handle) return [];
    $firstLines = [];
    for ($i = 0; $i < 10 && ($line = fgetcsv($handle)) !== false; $i++) {
        $firstLines[] = $line;
    }
    fclose($handle);
    if (empty($firstLines)) return [];

    $headerRow = null;
    $indices = null;
    foreach ($firstLines as $r => $row) {
        $indices = detectHeaderRow($row);
        if ($indices !== null) {
            $headerRow = $r;
            break;
        }
    }
    if ($indices === null) {
        $indices = ['tanggal' => 3, 'nama' => 0, 'harga' => 2, 'kategori' => null, 'satuan' => null];
        $headerRow = 0;
    }
    $defaultTanggal = extractGlobalDateFromRows($firstLines, $headerRow) ?? date('Y-m-d');
    $idx_tanggal = $indices['tanggal'] !== null ? $indices['tanggal'] : -1;
    $idx_nama = $indices['nama'];
    $idx_harga = $indices['harga'];

    $handle = fopen($file_path, 'r');
    for ($r = 0; $r <= $headerRow && ($row = fgetcsv($handle)) !== false; $r++) {
        if ($r < $headerRow) continue;
    }
    $out = [];
    while (($row = fgetcsv($handle)) !== false) {
        $nama = isset($row[$idx_nama]) ? cleanImportedNamaBahan($row[$idx_nama]) : '';
        $harga = isset($row[$idx_harga]) ? $row[$idx_harga] : null;
        if ($nama === '') continue;

        $harga = parseHargaValue($harga);
        if ($harga === null || $harga <= 0) continue;

        $tanggal = $defaultTanggal;
        if ($idx_tanggal >= 0 && isset($row[$idx_tanggal]) && trim((string) $row[$idx_tanggal]) !== '') {
            $parsedTanggal = tryParseTanggal($row[$idx_tanggal]);
            if ($parsedTanggal !== null) {
                $tanggal = $parsedTanggal;
            }
        }
        $out[] = [
            'tanggal' => $tanggal,
            'nama_bahan' => $nama,
            'harga' => $harga,
            'kategori' => '',
            'satuan' => '',
        ];
    }
    fclose($handle);
    return $out;
}

/**
 * Ekstrak baris dari PDF: cari pola tanggal, nama barang, harga di teks.
 * Return: array of ['tanggal','nama_bahan','harga','kategori'=>'','satuan'=>'']
 */
function getRowsFromPdf($file_path) {
    if (!class_exists('Smalot\PdfParser\Parser')) {
        return [];
    }
    try {
        $parser = new \Smalot\PdfParser\Parser();
        $pdf = $parser->parseFile($file_path);
        $text = $pdf->getText();
    } catch (Exception $e) {
        return [];
    }
    if (trim($text) === '') return [];

    $out = [];
    $lines = preg_split('/\r\n|\n|\r/', $text);
    // Pola: tanggal (berbagai format), lalu teks (nama), lalu angka (harga)
    $datePattern = '/(\d{4}-\d{2}-\d{2})|(\d{1,2}[\/\-]\d{1,2}[\/\-]\d{2,4})|(\d{1,2}\s+(?:Jan|Feb|Mar|Apr|Mei|Jun|Jul|Agu|Sep|Okt|Nov|Des)[a-z]*\s+\d{2,4})/i';
    $pricePattern = '/(\d{1,3}(?:[.,]\d{3})*(?:[.,]\d{2})?|\d+[.,]\d+)\s*$/';

    foreach ($lines as $line) {
        $line = trim(preg_replace('/\s+/', ' ', $line));
        if (strlen($line) < 5) continue;

        // Cari tanggal di awal baris
        if (preg_match($datePattern, $line, $dateMatch)) {
            $dateStr = $dateMatch[0];
            $tanggal = normalisasiTanggal($dateStr);
            $rest = trim(preg_replace($datePattern, '', $line, 1));
            if (preg_match('/^(.+?)\s+(\d{1,3}(?:[.,]\d{3})*(?:[.,]\d{2})?|\d+[.,]?\d*)\s*$/', $rest, $m)) {
                $nama = cleanImportedNamaBahan($m[1]);
                $harga = parseHargaValue($m[2]);
                if ($nama !== '' && $harga > 0) {
                    $out[] = [
                        'tanggal' => $tanggal,
                        'nama_bahan' => $nama,
                        'harga' => $harga,
                        'kategori' => '',
                        'satuan' => '',
                    ];
                }
            }
            continue;
        }

        // Baris tanpa tanggal di awal: cari pola "teks angka" (nama harga)
        if (preg_match('/^(.+?)\s+(\d{1,3}(?:[.,]\d{3})*(?:[.,]\d{2})?|\d+[.,]?\d*)\s*$/', $line, $m)) {
            $nama = cleanImportedNamaBahan($m[1]);
            $harga = parseHargaValue($m[2]);
            if ($nama !== '' && $harga > 0 && !preg_match('/^\d+$/', $nama)) {
                $out[] = [
                    'tanggal' => date('Y-m-d'),
                    'nama_bahan' => $nama,
                    'harga' => $harga,
                    'kategori' => '',
                    'satuan' => '',
                ];
            }
        }
    }
    return $out;
}

function normalisasiTanggal($str) {
    return tryParseTanggal($str) ?? date('Y-m-d');
}

/* ================= EKSTRAK BARIS DARI FILE ================= */
$tmp = $file['tmp_name'];
$rows = [];
$spreadsheetDetectedType = null;

if ($ext === 'csv') {
    $rows = getRowsFromCsv($tmp);
} elseif (in_array($ext, ['xlsx', 'xls', 'ods'])) {
    $monthlyDetected = false;
    $rows = getRowsFromSpreadsheetMonthlyReport($tmp, $monthlyDetected);
    if ($monthlyDetected) {
        $spreadsheetDetectedType = 'monthly';
    } else {
        $rows = getRowsFromSpreadsheetTPID($tmp);
        if (!empty($rows)) {
            $spreadsheetDetectedType = 'tpid';
        }
        if (empty($rows)) {
            $rows = getRowsFromSpreadsheet($tmp);
            $spreadsheetDetectedType = 'generic';
        }
    }
} elseif ($ext === 'pdf') {
    $rows = getRowsFromPdf($tmp);
}

if (empty($rows)) {
    $msg = "Tidak ada data yang dapat dibaca. File harus berisi: Tanggal, Nama Bahan, dan Harga (kolom/urutan boleh berbeda). Pastikan ada baris header yang berisi kata-kata tersebut.";
    if ($spreadsheetDetectedType === 'monthly') {
        $msg = "Format laporan bulanan terdeteksi, tetapi kolom harga harian pada tanggal 1-31 kosong atau tidak valid. Pastikan setiap komoditas memiliki angka harga pada kolom tanggal.";
    }
    if ($ext === 'pdf') {
        $msg .= " Untuk PDF, pastikan teks berisi pola tanggal–nama–harga per baris, dan composer require smalot/pdfparser sudah dijalankan.";
    }
    setImportFlashAndRedirect('error', 'Import gagal', $msg, [], $redirectTo);
}

/* ================= SIMPAN KE DATABASE + HITUNG STATISTIK ================= */
$success = 0;
$failed = 0;

foreach ($rows as $r) {
    $nama_bahan = mysqli_real_escape_string($conn, cleanImportedNamaBahan($r['nama_bahan']));
    $kategori   = mysqli_real_escape_string($conn, $r['kategori']);
    $satuan     = mysqli_real_escape_string($conn, $r['satuan']);
    $harga      = (float) $r['harga'];
    $tanggal    = mysqli_real_escape_string($conn, $r['tanggal']);

    $cek = mysqli_query($conn, "SELECT id FROM bahan_pokok WHERE nama_bahan = '$nama_bahan'");
    if (mysqli_num_rows($cek) > 0) {
        $bahan_id = (int) mysqli_fetch_assoc($cek)['id'];
    } else {
        $ins = mysqli_query($conn, "
            INSERT INTO bahan_pokok (nama_bahan, kategori, satuan, het_hap)
            VALUES ('$nama_bahan', '$kategori', '$satuan', 0)
        ");
        if (!$ins) { $failed++; continue; }
        $bahan_id = (int) mysqli_insert_id($conn);
    }

    $ins = mysqli_query($conn, "
        INSERT INTO harga (bahan_id, harga, tanggal)
        VALUES ('$bahan_id', '$harga', '$tanggal')
    ");
    if (!$ins) { $failed++; continue; }

    updateStatistikHarga($conn, $bahan_id);
    $success++;
}

$type = $success > 0 ? 'success' : 'error';
$title = $success > 0 ? 'Import berhasil' : 'Import gagal';
$message = "Berhasil: $success data. Gagal: $failed data.";
setImportFlashAndRedirect($type, $title, $message, ['success' => $success, 'failed' => $failed], $redirectTo);
