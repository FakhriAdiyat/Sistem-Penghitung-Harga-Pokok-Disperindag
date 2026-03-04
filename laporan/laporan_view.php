<?php
// contoh data dummy dulu
$bulan = 'Maret 2025';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Harga TPID</title>
    <link rel="stylesheet" href="laporan_style.css">
</head>
<body>

<h3 style="text-align:center">
    LAPORAN PERKEMBANGAN HARGA BAHAN POKOK<br>
    KABUPATEN KARAWANG<br>
    BULAN <?= $bulan ?>
</h3>

<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Komoditas</th>
            <th>Satuan</th>
            <th>Harga</th>
            <th>Perubahan (%)</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>1</td>
            <td>Beras Medium</td>
            <td>Kg</td>
            <td>Rp 12.000</td>
            <td>+2%</td>
        </tr>
    </tbody>
</table>

</body>
</html>