<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';
require_once '../config/database.php';

// ambil bulan dari GET (opsional)
$bulan = $_GET['bulan'] ?? date('Y-m');

// ambil data harga
$query = "
SELECT 
    h.id,
    h.harga,
    h.tanggal,
    h.rata_rata,
    h.persen_penyimpangan,
    h.fluktuasi_persen,
    h.stabilitas_persen,
    h.persen_naik_turun,
    h.naik_turun_rp,
    b.nama_bahan,
    b.satuan,
    b.het_hap
FROM harga h
JOIN bahan_pokok b ON h.bahan_id = b.id
WHERE DATE_FORMAT(h.tanggal, '%Y-%m') = '$bulan'
ORDER BY b.nama_bahan ASC
";

$data = mysqli_query($conn, $query);

use Dompdf\Dompdf;
use Dompdf\Options;

$options = new Options();
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->setPaper('A4', 'portrait');

// ambil HTML laporan
ob_start();
include 'laporan_view.php';
$html = ob_get_clean();

$dompdf->loadHtml($html);
$dompdf->render();
$dompdf->stream("Laporan_TPID.pdf", ["Attachment" => true]);