<?php
require_once __DIR__ . '../vendor/autoload.php';
require_once __DIR__ . '../config/database.php';
require_once __DIR__ . '../includes/auth_check.php';
$allowed_roles = ['admin', 'member'];
require_once __DIR__ . '../includes/role_check.php';

use Dompdf\Dompdf;
use Dompdf\Options;

date_default_timezone_set('Asia/Jakarta');

function image_data_uri(string $path): string
{
    if (!is_file($path)) {
        return '';
    }
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    $mime = 'image/png';
    if ($ext === 'jpg' || $ext === 'jpeg') {
        $mime = 'image/jpeg';
    } elseif ($ext === 'svg') {
        $mime = 'image/svg+xml';
    }
    $raw = file_get_contents($path);
    if ($raw === false) {
        return '';
    }
    return 'data:' . $mime . ';base64,' . base64_encode($raw);
}

function fmt_id_date(DateTime $date): string
{
    $bulan = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];
    $d = (int)$date->format('j');
    $m = (int)$date->format('n');
    $y = $date->format('Y');
    return $d . ' ' . $bulan[$m] . ' ' . $y;
}

$tanggalAwalInput = trim((string)($_GET['tanggal_awal'] ?? ''));
$tanggalAkhirInput = trim((string)($_GET['tanggal_akhir'] ?? ''));

$tanggalAwalObj = DateTime::createFromFormat('Y-m-d', $tanggalAwalInput);
$tanggalAkhirObj = DateTime::createFromFormat('Y-m-d', $tanggalAkhirInput);

if (!$tanggalAwalObj || $tanggalAwalObj->format('Y-m-d') !== $tanggalAwalInput) {
    $tanggalAwalObj = new DateTime('today');
}
if (!$tanggalAkhirObj || $tanggalAkhirObj->format('Y-m-d') !== $tanggalAkhirInput) {
    $tanggalAkhirObj = clone $tanggalAwalObj;
}

$tanggalAwal = $tanggalAwalObj->format('Y-m-d');
$tanggalAkhir = $tanggalAkhirObj->format('Y-m-d');

$tanggalUnduh = fmt_id_date(new DateTime('today'));
$tanggalKemarinLabel = fmt_id_date($tanggalAwalObj);
$tanggalHariIniLabel = fmt_id_date($tanggalAkhirObj);

$kolomPrev = 'Harga Kemarin';
$kolomCurr = 'Harga Hari Ini';

$query = "
SELECT
    b.id AS bahan_id,
    b.nama_bahan,
    b.satuan,
    b.het_hap,
    curr.harga_periode_ini,
    prev.harga_periode_lalu,
    curr.rata_rata,
    curr.persen_penyimpangan,
    curr.fluktuasi_persen,
    curr.stabilitas_persen,
    curr.persen_naik_turun,
    curr.naik_turun_rp
FROM bahan_pokok b
LEFT JOIN (
    SELECT
        h.bahan_id,
        ROUND(AVG(h.harga), 2) AS harga_periode_ini,
        ROUND(AVG(h.rata_rata), 2) AS rata_rata,
        ROUND(AVG(h.persen_penyimpangan), 2) AS persen_penyimpangan,
        ROUND(AVG(h.fluktuasi_persen), 2) AS fluktuasi_persen,
        ROUND(AVG(h.stabilitas_persen), 2) AS stabilitas_persen,
        ROUND(AVG(h.persen_naik_turun), 2) AS persen_naik_turun,
        ROUND(AVG(h.naik_turun_rp), 2) AS naik_turun_rp
    FROM harga h
    WHERE h.tanggal = '$tanggalAkhir'
    GROUP BY h.bahan_id
) curr ON curr.bahan_id = b.id
LEFT JOIN (
    SELECT
        h.bahan_id,
        ROUND(AVG(h.harga), 2) AS harga_periode_lalu
    FROM harga h
    WHERE h.tanggal = '$tanggalAwal'
    GROUP BY h.bahan_id
) prev ON prev.bahan_id = b.id
ORDER BY b.nama_bahan ASC
";

$data = mysqli_query($conn, $query);

$summary = mysqli_query(
    $conn,
    "
    SELECT
        COUNT(*) AS total_komoditas,
        SUM(CASE WHEN curr.harga_periode_ini IS NOT NULL THEN 1 ELSE 0 END) AS komoditas_terisi
    FROM bahan_pokok b
    LEFT JOIN (
        SELECT h.bahan_id, ROUND(AVG(h.harga),2) AS harga_periode_ini
        FROM harga h
        WHERE h.tanggal = '$tanggalAkhir'
        GROUP BY h.bahan_id
    ) curr ON curr.bahan_id = b.id
"
);
$summaryRow = $summary ? mysqli_fetch_assoc($summary) : ['total_komoditas' => 0, 'komoditas_terisi' => 0];

$logo2Data = image_data_uri(__DIR__ . '/../assets/img/logo2.jpg');

$css = file_get_contents(__DIR__ . '/laporan.css');

$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->setPaper('A4', 'portrait');

ob_start();
include __DIR__ . '/laporan_view.php';
$html = ob_get_clean();

$dompdf->loadHtml($html);
$dompdf->render();
$dompdf->stream('Laporan_Harga_' . $tanggalAwal . '_sampai_' . $tanggalAkhir . '_' . date('His') . '.pdf', ['Attachment' => true]);
exit;
