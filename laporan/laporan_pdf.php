<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';
$allowed_roles = ['admin', 'member'];
require_once __DIR__ . '/../includes/role_check.php';
require_once __DIR__ . '/laporan_helpers.php';

use Dompdf\Dompdf;
use Dompdf\Options;

date_default_timezone_set('Asia/Jakarta');

function laporanPdfRedirect(string $location): void
{
    header('Location: ' . $location);
    exit;
}

function laporanPdfImageDataUri(string $path): string
{
    if (!is_file($path)) {
        return '';
    }

    $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    $mime = 'image/png';
    if ($extension === 'jpg' || $extension === 'jpeg') {
        $mime = 'image/jpeg';
    } elseif ($extension === 'svg') {
        $mime = 'image/svg+xml';
    }

    $raw = file_get_contents($path);
    if ($raw === false) {
        return '';
    }

    return 'data:' . $mime . ';base64,' . base64_encode($raw);
}

function laporanPdfFormatRupiah($value): string
{
    if ($value === null || $value === '') {
        return '-';
    }

    return 'Rp ' . number_format((float) $value, 0, ',', '.');
}

function laporanPdfFormatPersen($value, bool $signed = false): string
{
    if ($value === null || $value === '') {
        return '-';
    }

    $numericValue = (float) $value;
    $formatted = number_format(abs($numericValue), 2, ',', '.') . '%';

    if (!$signed) {
        return $formatted;
    }

    if ($numericValue > 0) {
        return '+' . $formatted;
    }

    if ($numericValue < 0) {
        return '-' . $formatted;
    }

    return '0,00%';
}

function laporanPdfFormatSignedRupiah($value): string
{
    if ($value === null || $value === '') {
        return '-';
    }

    $numericValue = (float) $value;
    if ($numericValue > 0) {
        return '+ ' . laporanPdfFormatRupiah(abs($numericValue));
    }

    if ($numericValue < 0) {
        return '- ' . laporanPdfFormatRupiah(abs($numericValue));
    }

    return 'Rp 0';
}

$latestTanggal = laporanGetLatestTanggal($conn);
$tanggalLaporanInput = $_GET['tanggal_laporan'] ?? ($_GET['tanggal_akhir'] ?? ($_GET['tanggal_awal'] ?? ''));
$tanggalLaporan = laporanNormalizeTanggalInput($tanggalLaporanInput, $latestTanggal);
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

$templateGroups = laporanTemplateGroups();
if (empty($templateGroups)) {
    laporanPdfRedirect('laporan.php?err=template_missing&tanggal_laporan=' . urlencode($tanggalLaporan));
}

$currentRows = laporanFetchLatestRowsByDate($conn, $tanggalLaporan);
$currentMap = laporanBuildTemplateRowMap($currentRows);
if (empty($currentMap)) {
    laporanPdfRedirect('laporan.php?err=no_data&tanggal_laporan=' . urlencode($tanggalLaporan));
}

$previousMap = [];
if ($tanggalPembanding !== null) {
    $previousRows = laporanFetchLatestRowsByDate($conn, $tanggalPembanding);
    $previousMap = laporanBuildTemplateRowMap($previousRows);
}

$reportGroups = [];
foreach ($templateGroups as $group) {
    $items = [];

    foreach ($group['items'] as $item) {
        $rowNumber = (int) ($item['row'] ?? 0);
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

        $items[] = [
            'label' => (string) ($item['label'] ?? ''),
            'unit' => (string) ($item['unit'] ?? ''),
            'het_hap' => $hetHap,
            'harga_kemarin' => $hargaKemarin,
            'harga_hari_ini' => $hargaHariIni,
            'perubahan_rp' => $perubahanRp,
            'perubahan_persen' => $perubahanPersen,
            'terhadap_het_hap' => $terhadapHetHap,
        ];
    }

    $reportGroups[] = [
        'number' => (string) ($group['number'] ?? ''),
        'title' => (string) ($group['title'] ?? ''),
        'items' => $items,
    ];
}

$pasarLabel = 'Semua Pasar';
$tanggalLabel = laporanFormatTanggalIndonesia($tanggalLaporan);
$tanggalKemarinLabel = $tanggalPembanding !== null ? laporanFormatTanggalIndonesia($tanggalPembanding) : '-';
$css = file_get_contents(__DIR__ . '/laporan.css');
$dokumentasiImages = [];
if (isset($_SESSION['dokumentasi_images']) && is_array($_SESSION['dokumentasi_images'])) {
    $dokumentasiImages = $_SESSION['dokumentasi_images'];
    unset($_SESSION['dokumentasi_images']);
}

ob_start();
include __DIR__ . '/laporan_view.php';
$html = ob_get_clean();

$options = new Options();
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->setPaper('A4', 'portrait');
$dompdf->loadHtml($html);
$dompdf->render();
$dompdf->stream('Laporan_Harga_' . $tanggalLaporan . '.pdf', ['Attachment' => true]);
exit;
