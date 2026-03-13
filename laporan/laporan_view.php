<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan TPID</title>
    <style><?= $css ?></style>
</head>
<body>

<div class="logo-wrap">
    <?php if (!empty($logo2Data)): ?><img src="<?= $logo2Data ?>" alt="Logo 2" class="logo-item"><?php endif; ?>
</div>

<div class="judul">
    <div class="judul-utama">LAPORAN PERKEMBANGAN HARGA BAHAN POKOK</div>
    <div class="judul-sub">KABUPATEN KARAWANG</div>
    <div class="judul-sub">TANGGAL : <?= strtoupper($tanggalUnduh) ?></div>
</div>

<table class="summary-table">
    <tr>
        <th>Tanggal Kemarin</th>
        <td><?= htmlspecialchars($tanggalKemarinLabel) ?></td>
        <th>Tanggal Hari Ini</th>
        <td><?= htmlspecialchars($tanggalHariIniLabel) ?></td>
    </tr>
    <tr>
        <th>Total Komoditas</th>
        <td><?= (int)($summaryRow['total_komoditas'] ?? 0) ?></td>
        <th>Komoditas Terisi Hari Ini</th>
        <td><?= (int)($summaryRow['komoditas_terisi'] ?? 0) ?></td>
    </tr>
</table>

<table class="main-table">
    <thead>
        <tr>
            <th rowspan="2">No</th>
            <th rowspan="2">Komoditas</th>
            <th rowspan="2">Satuan</th>
            <th colspan="2">Perbandingan Harga</th>
            <th rowspan="2">Rata-rata</th>
            <th rowspan="2">Penyimpangan (%)</th>
            <th rowspan="2">Fluktuasi (%)</th>
            <th rowspan="2">Stabilitas (%)</th>
            <th rowspan="2">HET/HAP</th>
            <th rowspan="2">Naik/Turun (%)</th>
            <th rowspan="2">Naik/Turun (Rp)</th>
        </tr>
        <tr>
            <th><?= htmlspecialchars($kolomPrev) ?></th>
            <th><?= htmlspecialchars($kolomCurr) ?></th>
        </tr>
    </thead>
    <tbody>
<?php
$no = 1;
while ($row = mysqli_fetch_assoc($data)):
    $pn = isset($row['persen_naik_turun']) ? (float)$row['persen_naik_turun'] : 0;
    $rp = isset($row['naik_turun_rp']) ? (float)$row['naik_turun_rp'] : 0;
?>
<tr>
    <td class="center"><?= $no++ ?></td>
    <td><?= htmlspecialchars($row['nama_bahan']) ?></td>
    <td class="center"><?= htmlspecialchars($row['satuan']) ?></td>

    <td class="right">
        <?= $row['harga_periode_lalu'] !== null ? number_format((float)$row['harga_periode_lalu'], 0, ',', '.') : '-' ?>
    </td>

    <td class="right">
        <?= $row['harga_periode_ini'] !== null ? number_format((float)$row['harga_periode_ini'], 0, ',', '.') : '-' ?>
    </td>

    <td class="right">
        <?= $row['rata_rata'] !== null ? number_format((float)$row['rata_rata'], 0, ',', '.') : '-' ?>
    </td>

    <td class="right">
        <?= $row['persen_penyimpangan'] !== null ? number_format((float)$row['persen_penyimpangan'], 2, ',', '.') : '-' ?>
    </td>

    <td class="right">
        <?= $row['fluktuasi_persen'] !== null ? number_format((float)$row['fluktuasi_persen'], 2, ',', '.') : '-' ?>
    </td>

    <td class="right">
        <?= $row['stabilitas_persen'] !== null ? number_format((float)$row['stabilitas_persen'], 2, ',', '.') : '-' ?>
    </td>

    <td class="right">
        <?= (isset($row['het_hap']) && (float)$row['het_hap'] > 0) ? number_format((float)$row['het_hap'], 0, ',', '.') : '-' ?>
    </td>

    <td class="right">
        <?php if ($pn == 0): ?>
            -
        <?php else: ?>
            <span class="<?= $pn > 0 ? 'naik' : 'turun' ?>">
                <?= $pn > 0 ? '+' : '-' ?><?= number_format(abs($pn), 2, ',', '.') ?>
            </span>
        <?php endif; ?>
    </td>

    <td class="right">
        <?php if ($rp == 0): ?>
            -
        <?php else: ?>
            <span class="<?= $rp > 0 ? 'naik' : 'turun' ?>">
                <?= $rp > 0 ? '+' : '-' ?><?= number_format(abs($rp), 0, ',', '.') ?>
            </span>
        <?php endif; ?>
    </td>
</tr>
<?php endwhile; ?>
    </tbody>
</table>

</body>
</html>
