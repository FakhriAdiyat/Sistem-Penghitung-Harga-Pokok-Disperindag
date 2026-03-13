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

function setImportFlashAndRedirect(string $type, string $title, string $message, array $meta = [], string $redirectTo = 'import.php'): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['import_flash'] = [
        'type' => $type, // success | error
        'title' => $title,
        'message' => $message,
        'meta' => $meta,
    ];
    header("Location: " . $redirectTo);
    exit;
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
        $harga = null;
        if (is_numeric($f)) {
            $harga = (float)$f;
        } elseif (is_string($f) && preg_match('/[\d.,]+/', $f, $m)) {
            $harga = (float)str_replace(',', '.', preg_replace('/\.(?=\d{3})/', '', $f));
        }
        if ($b === '' && $harga === null) continue;
        if (is_numeric($a) && $a > 0) {
            $parent = $b;
            if ($harga !== null && $harga > 0) {
                $out[] = ['tanggal' => $tanggal, 'nama_bahan' => $parent, 'harga' => $harga, 'kategori' => '', 'satuan' => $c];
            }
            continue;
        }
        if ($b !== '' && $harga !== null && $harga > 0) {
            $nama = $parent !== '' ? $parent . ' ' . $b : $b;
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
    $header = array_map('trim', array_map('strtolower', (array) $row));
    $idx_tanggal = $idx_nama = $idx_harga = $idx_kategori = $idx_satuan = null;
    foreach ($header as $i => $cell) {
        $c = is_string($cell) ? $cell : '';
        if (preg_match('/tanggal|tgl|date/', $c)) $idx_tanggal = $i;
        if (preg_match('/nama|barang|bahan|item|komodit/i', $c)) $idx_nama = $i;
        if (preg_match('/harga|price/', $c)) $idx_harga = $i;
        if (preg_match('/kategori|category/', $c)) $idx_kategori = $i;
        if (preg_match('/satuan|unit/', $c)) $idx_satuan = $i;
    }
    if ($idx_nama !== null && $idx_harga !== null) {
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
        $nama = isset($row[$idx_nama]) ? trim((string) $row[$idx_nama]) : '';
        $harga = isset($row[$idx_harga]) ? $row[$idx_harga] : null;
        if ($nama === '' && $harga === null) continue;

        $tanggal = date('Y-m-d');
        if ($idx_tanggal >= 0 && isset($row[$idx_tanggal]) && $row[$idx_tanggal] !== '') {
            $t = $row[$idx_tanggal];
            if (is_numeric($t)) {
                $tanggal = date('Y-m-d', SpreadsheetDate::excelToTimestamp($t));
            } else {
                $ts = strtotime($t);
                if ($ts) $tanggal = date('Y-m-d', $ts);
            }
        }
        $harga = is_numeric($harga) ? (float) $harga : (preg_match('/[\d.,]+/', (string) $harga, $m) ? (float) str_replace(',', '.', $m[0]) : null);
        if ($harga === null || $harga <= 0) continue;

        $kategori = ($idx_kategori >= 0 && isset($row[$idx_kategori])) ? trim((string) $row[$idx_kategori]) : '';
        $satuan = ($idx_satuan >= 0 && isset($row[$idx_satuan])) ? trim((string) $row[$idx_satuan]) : '';

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
    $idx_tanggal = $indices['tanggal'] !== null ? $indices['tanggal'] : -1;
    $idx_nama = $indices['nama'];
    $idx_harga = $indices['harga'];

    $handle = fopen($file_path, 'r');
    for ($r = 0; $r <= $headerRow && ($row = fgetcsv($handle)) !== false; $r++) {
        if ($r < $headerRow) continue;
    }
    $out = [];
    while (($row = fgetcsv($handle)) !== false) {
        $nama = isset($row[$idx_nama]) ? trim((string) $row[$idx_nama]) : '';
        $harga = isset($row[$idx_harga]) ? $row[$idx_harga] : null;
        if ($nama === '') continue;

        $harga = is_numeric($harga) ? (float) $harga : (is_string($harga) && preg_match('/[\d.,]+/', $harga, $m) ? (float) str_replace(',', '.', $m[0]) : null);
        if ($harga === null || $harga <= 0) continue;

        $tanggal = date('Y-m-d');
        if ($idx_tanggal >= 0 && isset($row[$idx_tanggal]) && trim((string) $row[$idx_tanggal]) !== '') {
            $ts = strtotime(trim($row[$idx_tanggal]));
            if ($ts) $tanggal = date('Y-m-d', $ts);
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
                $nama = trim($m[1]);
                $harga = (float) str_replace(',', '.', str_replace('.', '', $m[2]));
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
            $nama = trim($m[1]);
            $harga = (float) str_replace(',', '.', str_replace('.', '', $m[2]));
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
    $str = trim($str);
    if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $str)) return $str;
    if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{2,4})$/', $str, $m)) {
        $y = strlen($m[3]) === 2 ? '20' . $m[3] : $m[3];
        return sprintf('%04d-%02d-%02d', $y, $m[2], $m[1]);
    }
    $ts = strtotime($str);
    return $ts ? date('Y-m-d', $ts) : date('Y-m-d');
}

/* ================= EKSTRAK BARIS DARI FILE ================= */
$tmp = $file['tmp_name'];
$rows = [];

if ($ext === 'csv') {
    $rows = getRowsFromCsv($tmp);
} elseif (in_array($ext, ['xlsx', 'xls', 'ods'])) {
    $rows = getRowsFromSpreadsheetTPID($tmp);
    if (empty($rows)) {
        $rows = getRowsFromSpreadsheet($tmp);
    }
} elseif ($ext === 'pdf') {
    $rows = getRowsFromPdf($tmp);
}

if (empty($rows)) {
    $msg = "Tidak ada data yang dapat dibaca. File harus berisi: Tanggal, Nama Bahan, dan Harga (kolom/urutan boleh berbeda). Pastikan ada baris header yang berisi kata-kata tersebut.";
    if ($ext === 'pdf') {
        $msg .= " Untuk PDF, pastikan teks berisi pola tanggal–nama–harga per baris, dan composer require smalot/pdfparser sudah dijalankan.";
    }
    setImportFlashAndRedirect('error', 'Import gagal', $msg, [], $redirectTo);
}

/* ================= SIMPAN KE DATABASE + HITUNG STATISTIK ================= */
$success = 0;
$failed = 0;

foreach ($rows as $r) {
    $nama_bahan = mysqli_real_escape_string($conn, $r['nama_bahan']);
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
