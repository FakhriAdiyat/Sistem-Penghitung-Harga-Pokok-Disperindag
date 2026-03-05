<?php
$bulan = 'Maret 2025';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan TPID</title>
    <link rel="stylesheet" href="laporan_style.css">
</head>
<body>

<!-- JUDUL -->
<div class="judul">
    <div class="judul-utama">LAPORAN PERKEMBANGAN HARGA BAHAN POKOK</div>
    <div class="judul-sub">KABUPATEN KARAWANG</div>
    <div class="judul-sub">BULAN <?= strtoupper($bulan) ?></div>
</div>

<table>
    <thead>
        <tr>
            <th rowspan="2">No</th>
            <th rowspan="2">Komoditas</th>
            <th rowspan="2">Satuan</th>
            <th rowspan="2">Harga</th>
            <th rowspan="2">Rata²</th>
            <th rowspan="2">Penyimpangan<br>(%)</th>
            <th rowspan="2">Fluktuasi<br>(%)</th>
            <th rowspan="2">Stabilitas<br>(%)</th>
            <th rowspan="2">HET/HAP</th>
            <th colspan="2">Naik / Turun</th>
        </tr>
        <tr>
            <th>(%)</th>
            <th>(Rp)</th>
        </tr>
    </thead>
    <tbody>
<?php
$no = 1;
while ($row = mysqli_fetch_assoc($data)):

$pn = $row['persen_naik_turun'] ?? 0;
$rp = $row['naik_turun_rp'] ?? 0;
?>
<tr>
    <td class="center"><?= $no++ ?></td>
    <td><?= htmlspecialchars($row['nama_bahan']) ?></td>
    <td class="center"><?= htmlspecialchars($row['satuan']) ?></td>

    <td class="right"><?= number_format($row['harga'],0,',','.') ?></td>

    <td class="right">
        <?= $row['rata_rata'] ? number_format($row['rata_rata'],0,',','.') : '-' ?>
    </td>

    <td class="right">
        <?= $row['persen_penyimpangan'] !== null ? number_format($row['persen_penyimpangan'],2) : '-' ?>
    </td>

    <td class="right">
        <?= $row['fluktuasi_persen'] !== null ? number_format($row['fluktuasi_persen'],2) : '-' ?>
    </td>

    <td class="right">
        <?= $row['stabilitas_persen'] !== null ? number_format($row['stabilitas_persen'],2) : '-' ?>
    </td>

    <td class="right">
        <?= $row['het_hap'] > 0 ? number_format($row['het_hap'],0,',','.') : '-' ?>
    </td>

    <td class="right">
        <?= $pn != 0 ? number_format($pn,2) : '-' ?>
    </td>

    <td class="right">
        <?= $rp != 0 ? number_format($rp,0,',','.') : '-' ?>
    </td>
</tr>
<?php endwhile; ?>
</tbody>
</table>

</body>
</html>