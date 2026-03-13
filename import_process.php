<?php
require_once 'config/database.php';
require_once 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if (!isset($_FILES['file_import'])) {
    die('File tidak ditemukan');
}

$file = $_FILES['file_import'];
$ext  = pathinfo($file['name'], PATHINFO_EXTENSION);

// Ambil input HET dan keyword bahan
$het_input = $_POST['het_hap'] ?? null;
$bahan_keyword = $_POST['bahan_keyword'] ?? '';

if (!in_array($ext, ['csv', 'xlsx'])) {
    die('Format file tidak didukung');
}

/* ================= UPDATE HET BERDASARKAN NAMA MIRIP ================= */
if (!empty($het_input) && !empty($bahan_keyword)) {

    mysqli_query($conn, "
        UPDATE bahan_pokok 
        SET het_hap = '$het_input'
        WHERE nama_bahan LIKE '%$bahan_keyword%'
    ");
}

/* ================= XLSX ================= */
if ($ext === 'xlsx') {

    $spreadsheet = IOFactory::load($file['tmp_name']);
    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray();

    $success = 0;
    $failed  = 0;

    foreach ($rows as $index => $row) {

        if ($index === 0) continue;

        $nama_bahan = trim($row[1]);
        $kategori   = trim($row[2]);
        $satuan     = trim($row[3]);
        $harga      = $row[5];
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

        // INSERT HARGA BARU
        mysqli_query($conn, "
            INSERT INTO harga (bahan_id, harga, tanggal)
            VALUES ('$bahan_id', '$harga', '$tanggal')
        ");

        /* ================= PERHITUNGAN STATISTIK ================= */

        $resultHarga = mysqli_query($conn, "
            SELECT harga FROM harga 
            WHERE bahan_id = '$bahan_id'
            ORDER BY tanggal ASC
        ");

        $total = 0;
        $dataHarga = [];

        while ($h = mysqli_fetch_assoc($resultHarga)) {
            $dataHarga[] = $h['harga'];
            $total += $h['harga'];
        }

        $jumlah_data = count($dataHarga);

        if ($jumlah_data > 0) {

            $rata_rata = $total / $jumlah_data;

            $total_penyimpangan = 0;
            foreach ($dataHarga as $h) {
                $total_penyimpangan += abs($h - $rata_rata);
            }

            $rata_penyimpangan = $total_penyimpangan / $jumlah_data;

            $fluktuasi = ($rata_penyimpangan / $rata_rata) * 100;
            $stabilitas = 100 - $fluktuasi;

            // Hitung kenaikan / penurunan terakhir
            $persen_kenaikan = 0;
            $persen_penurunan = 0;
            $kenaikan_rp = 0;
            $penurunan_rp = 0;

            if ($jumlah_data > 1) {
                $harga_lama = $dataHarga[$jumlah_data - 2];
                $harga_baru = $dataHarga[$jumlah_data - 1];

                $selisih = $harga_baru - $harga_lama;

                if ($selisih > 0) {
                    $kenaikan_rp = $selisih;
                    $persen_kenaikan = ($selisih / $harga_lama) * 100;
                } elseif ($selisih < 0) {
                    $penurunan_rp = abs($selisih);
                    $persen_penurunan = (abs($selisih) / $harga_lama) * 100;
                }
            }

            // Update semua record harga bahan tersebut
            mysqli_query($conn, "
                UPDATE harga 
                SET rata_rata = '$rata_rata',
                    rata_penyimpangan = '$rata_penyimpangan',
                    fluktuasi_persen = '$fluktuasi',
                    stabilitas_persen = '$stabilitas',
                    persen_kenaikan = '$persen_kenaikan',
                    persen_penurunan = '$persen_penurunan',
                    kenaikan_rp = '$kenaikan_rp',
                    penurunan_rp = '$penurunan_rp'
                WHERE bahan_id = '$bahan_id'
            ");
        }

        $success++;
    }

    echo "
        <h3>Import Selesai</h3>
        <p>Berhasil: $success data</p>
        <p>Gagal: $failed data</p>
        <a href='import.php'>Kembali</a>
    ";
    exit;
}

/* ================= CSV ================= */
if ($ext === 'csv') {

    $handle = fopen($file['tmp_name'], 'r');
    fgetcsv($handle);

    while (($row = fgetcsv($handle)) !== false) {

        $nama_bahan = trim($row[0]);
        $harga      = $row[2];
        $tanggal    = $row[3];

        $result = mysqli_query($conn, "
            SELECT id FROM bahan_pokok 
            WHERE nama_bahan = '$nama_bahan'
        ");

        $data = mysqli_fetch_assoc($result);

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

header("Location: import.php?success=1");
exit;
