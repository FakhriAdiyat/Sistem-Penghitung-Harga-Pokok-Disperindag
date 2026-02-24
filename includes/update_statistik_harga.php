<?php
/**
 * Hitung dan update statistik harga untuk suatu bahan_id.
 * Dipakai oleh import_process.php dan list_process.php.
 */
function updateStatistikHarga($conn, $bahan_id) {
    $bahan_id = (int) $bahan_id;
    $resultHarga = mysqli_query($conn, "
        SELECT harga FROM harga 
        WHERE bahan_id = '$bahan_id'
        ORDER BY tanggal ASC
    ");
    $dataHarga = [];
    $total = 0;
    while ($h = mysqli_fetch_assoc($resultHarga)) {
        $dataHarga[] = (float) $h['harga'];
        $total += $h['harga'];
    }
    $jumlah_data = count($dataHarga);
    if ($jumlah_data === 0) return;

    $rata_rata = $total / $jumlah_data;
    $total_penyimpangan = 0;
    foreach ($dataHarga as $h) {
        $total_penyimpangan += abs($h - $rata_rata);
    }
    $rata_penyimpangan_rp = $total_penyimpangan / $jumlah_data;
    $persen_penyimpangan = ($rata_rata > 0) ? round(($rata_penyimpangan_rp / $rata_rata) * 100, 2) : 0;
    $fluktuasi = $persen_penyimpangan;
    $stabilitas = 100 - $fluktuasi;

    $persen_naik_turun = 0;
    $naik_turun_rp = null;
    if ($jumlah_data > 1) {
        $harga_lama = $dataHarga[$jumlah_data - 2];
        $harga_baru = $dataHarga[$jumlah_data - 1];
        $selisih = $harga_baru - $harga_lama;
        if ($selisih > 0) {
            $persen_naik_turun = round(($selisih / $harga_lama) * 100, 2);
            $naik_turun_rp = $selisih;
        } elseif ($selisih < 0) {
            $persen_naik_turun = -round((abs($selisih) / $harga_lama) * 100, 2);
            $naik_turun_rp = -abs($selisih);
        }
    }
    $naik_turun_rp_sql = $naik_turun_rp !== null ? "'$naik_turun_rp'" : 'NULL';

    $cek = mysqli_query($conn, "SHOW COLUMNS FROM harga LIKE 'persen_penyimpangan'");
    $struktur_baru = (mysqli_num_rows($cek) > 0);

    if ($struktur_baru) {
        mysqli_query($conn, "
            UPDATE harga 
            SET rata_rata = '$rata_rata',
                persen_penyimpangan = '$persen_penyimpangan',
                fluktuasi_persen = '$fluktuasi',
                stabilitas_persen = '$stabilitas',
                persen_naik_turun = '$persen_naik_turun',
                naik_turun_rp = $naik_turun_rp_sql
            WHERE bahan_id = '$bahan_id'
        ");
    } else {
        $rata_penyimpangan = $rata_penyimpangan_rp;
        $persen_kenaikan = $persen_naik_turun > 0 ? $persen_naik_turun : 0;
        $persen_penurunan = $persen_naik_turun < 0 ? abs($persen_naik_turun) : 0;
        $kenaikan_rp = ($naik_turun_rp !== null && $naik_turun_rp > 0) ? $naik_turun_rp : 'NULL';
        $penurunan_rp = ($naik_turun_rp !== null && $naik_turun_rp < 0) ? abs($naik_turun_rp) : 'NULL';
        if ($kenaikan_rp !== 'NULL') $kenaikan_rp = "'$kenaikan_rp'";
        if ($penurunan_rp !== 'NULL') $penurunan_rp = "'$penurunan_rp'";
        mysqli_query($conn, "
            UPDATE harga 
            SET rata_rata = '$rata_rata',
                rata_penyimpangan = '$rata_penyimpangan',
                fluktuasi_persen = '$fluktuasi',
                stabilitas_persen = '$stabilitas',
                persen_kenaikan = '$persen_kenaikan',
                persen_penurunan = '$persen_penurunan',
                kenaikan_rp = $kenaikan_rp,
                penurunan_rp = $penurunan_rp
            WHERE bahan_id = '$bahan_id'
        ");
    }
}
