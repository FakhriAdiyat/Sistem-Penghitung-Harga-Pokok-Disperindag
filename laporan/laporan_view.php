<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Harga</title>
    <style><?= $css ?></style>
</head>
<body>

<div class="report-header">
    <div class="report-title">DAFTAR HARGA BARANG KEBUTUHAN POKOK</div>
    <div class="report-subtitle">KABUPATEN KARAWANG</div>
</div>

<div class="report-meta">
    <div><strong>Tanggal:</strong> <?= htmlspecialchars($tanggalLabel) ?></div>
    <div><strong>Pasar:</strong> <?= htmlspecialchars($pasarLabel) ?></div>
</div>

<table class="report-table">
    <thead>
        <tr>
            <th rowspan="2" class="col-no">No</th>
            <th rowspan="2" class="col-komoditas">Komoditas</th>
            <th rowspan="2" class="col-satuan">Satuan</th>
            <th rowspan="2" class="col-money">HET/HAP</th>
            <th colspan="2">Harga</th>
            <th colspan="2">Perubahan</th>
            <th rowspan="2" class="col-money">Terhadap HET/HAP</th>
        </tr>
        <tr>
            <th class="col-money">Kemarin</th>
            <th class="col-money">Hari Ini</th>
            <th class="col-money">Rp</th>
            <th class="col-percent">Persen (%)</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($reportGroups as $group): ?>
            <tr class="group-row">
                <td class="center"><?= htmlspecialchars($group['number']) ?></td>
                <td colspan="8"><?= htmlspecialchars($group['title']) ?></td>
            </tr>
            <?php foreach ($group['items'] as $item): ?>
                <?php
                    $perubahanPersen = $item['perubahan_persen'];
                    $perubahanRp = $item['perubahan_rp'];
                    $perubahanClass = '';
                    if ($perubahanPersen !== null && (float) $perubahanPersen > 0) {
                        $perubahanClass = 'naik';
                    } elseif ($perubahanPersen !== null && (float) $perubahanPersen < 0) {
                        $perubahanClass = 'turun';
                    }
                    $terhadapHetClass = '';
                    if ($item['terhadap_het_hap'] !== null && (float) $item['terhadap_het_hap'] > 0) {
                        $terhadapHetClass = 'naik';
                    } elseif ($item['terhadap_het_hap'] !== null && (float) $item['terhadap_het_hap'] < 0) {
                        $terhadapHetClass = 'turun';
                    }
                ?>
                <tr>
                    <td class="center"></td>
                    <td class="komoditas-item"><?= htmlspecialchars($item['label']) ?></td>
                    <td class="center"><?= htmlspecialchars($item['unit']) ?></td>
                    <td class="right"><?= htmlspecialchars(laporanPdfFormatRupiah($item['het_hap'])) ?></td>
                    <td class="right"><?= htmlspecialchars(laporanPdfFormatRupiah($item['harga_kemarin'])) ?></td>
                    <td class="right"><?= htmlspecialchars(laporanPdfFormatRupiah($item['harga_hari_ini'])) ?></td>
                    <td class="right <?= htmlspecialchars($perubahanClass) ?>"><?= htmlspecialchars(laporanPdfFormatSignedRupiah($perubahanRp)) ?></td>
                    <td class="right <?= htmlspecialchars($perubahanClass) ?>"><?= htmlspecialchars(laporanPdfFormatPersen($perubahanPersen, true)) ?></td>
                    <td class="right <?= htmlspecialchars($terhadapHetClass) ?>"><?= htmlspecialchars(laporanPdfFormatSignedRupiah($item['terhadap_het_hap'])) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </tbody>
</table>

<div class="report-note">
    <strong>Pembanding kemarin:</strong> <?= htmlspecialchars($tanggalKemarinLabel) ?>
</div>

<div class="dokumentasi-section">
    <div class="dokumentasi-title">DOKUMENTASI KUNJUNGAN</div>
    <?php if (!empty($dokumentasiImages)): ?>
        <div class="dokumentasi-images">
            <?php foreach ($dokumentasiImages as $imgPath): ?>
                <?php $imgData = laporanPdfImageDataUri(__DIR__ . '/../' . $imgPath); ?>
                <?php if ($imgData !== ''): ?>
                    <img src="<?= $imgData ?>" alt="Dokumentasi" class="dok-image">
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="dokumentasi-empty">Belum ada gambar dokumentasi.</div>
    <?php endif; ?>
</div>

</body>
</html>
