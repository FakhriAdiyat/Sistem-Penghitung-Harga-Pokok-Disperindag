<?php
require_once 'config/database.php';
require_once 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if (!isset($_FILES['file_import'])) {
    die('File tidak ditemukan');
}

$file = $_FILES['file_import'];
$ext  = pathinfo($file['name'], PATHINFO_EXTENSION);

if (!in_array($ext, ['csv', 'xlsx'])) {
    die('Format file tidak didukung');
}

/* ================= CSV ================= */
if ($ext === 'csv') {
    $handle = fopen($file['tmp_name'], 'r');
    fgetcsv($handle); // skip header

    while (($row = fgetcsv($handle)) !== false) {

        $nama_bahan = trim($row[0]);
        $harga      = $row[2];
        $tanggal    = $row[3];

        // cari bahan_id dari tabel bahan_pokok
        $result = mysqli_query($conn, "SELECT id FROM bahan_pokok WHERE nama_bahan = '$nama_bahan'");
        $data   = mysqli_fetch_assoc($result);

        if ($data) {
            $bahan_id = $data['id'];

            mysqli_query($conn, "
                INSERT INTO harga (bahan_id, harga, tanggal)
                VALUES ('$bahan_id', '$harga', '$tanggal')
            ");
        }
    }

    fclose($handle);
}

/* ================= XLSX ================= */
if ($ext === 'xlsx') {

    $spreadsheet = IOFactory::load($file['tmp_name']);
    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray();

    $success = 0;
    $failed  = 0;

    foreach ($rows as $index => $row) {

        if ($index === 0) continue; // skip header

        $nama_bahan = trim($row[1]);   // Komoditas
        $kategori   = trim($row[2]);   // Jenis
        $satuan     = trim($row[3]);   // Satuan
        $harga      = $row[5];         // Harga Hari Ini
        $tanggal    = date('Y-m-d');

        if (!$nama_bahan || !$harga) {
            $failed++;
            continue;
        }

        // Cek apakah bahan sudah ada
        $cek = mysqli_query($conn, "
            SELECT id FROM bahan_pokok 
            WHERE nama_bahan = '$nama_bahan'
        ");

        if (mysqli_num_rows($cek) > 0) {

            $data = mysqli_fetch_assoc($cek);
            $bahan_id = $data['id'];

        } else {

            $insertBahan = mysqli_query($conn, "
                INSERT INTO bahan_pokok (nama_bahan, kategori, satuan, het_hap)
                VALUES ('$nama_bahan', '$kategori', '$satuan', 0)
            ");

            if ($insertBahan) {
                $bahan_id = mysqli_insert_id($conn);
            } else {
                $failed++;
                continue;
            }
        }

        // Insert ke tabel harga
        $insertHarga = mysqli_query($conn, "
            INSERT INTO harga (bahan_id, harga, tanggal)
            VALUES ('$bahan_id', '$harga', '$tanggal')
        ");

        if ($insertHarga) {
            $success++;
        } else {
            $failed++;
        }
    }

    echo "
        <h3>Import Selesai</h3>
        <p>Berhasil: $success data</p>
        <p>Gagal: $failed data</p>
        <a href='import.php'>Kembali</a>
    ";
    exit;
}




header("Location: import.php?success=1");
exit;
