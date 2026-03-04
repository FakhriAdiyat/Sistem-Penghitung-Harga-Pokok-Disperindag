<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';

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