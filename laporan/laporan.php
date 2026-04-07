<?php
require_once '../config/database.php';
require_once '../includes/auth_check.php';
$allowed_roles = ['admin', 'member'];
require_once '../includes/role_check.php';
require_once __DIR__ . '/laporan_helpers.php';

$latestTanggal = laporanGetLatestTanggal($conn);
$tanggalLaporan = laporanNormalizeTanggalInput($_GET['tanggal_laporan'] ?? '', $latestTanggal);

$errorMessage = '';
if (isset($_GET['err'])) {
    if ($_GET['err'] === 'no_data') {
        $errorMessage = 'Tidak ada data harga untuk tanggal yang dipilih.';
    } elseif ($_GET['err'] === 'template_missing') {
        $errorMessage = 'Template laporan Excel tidak ditemukan.';
    } else {
        $errorMessage = 'Laporan belum bisa diunduh. Coba lagi.';
    }
}
?>

<div class="layout">

    <?php require_once '../includes/sidebar.php'; ?>

    <div style="flex:1; display:flex; flex-direction:column;">

        <?php require_once '../includes/header.php'; ?>

        <div class="content">
            <div class="container theme-page-shell">
                <div class="theme-page-header">
                    <div class="theme-page-title">
                        <h1>Laporan Harga</h1>
                        <p class="subtitle">Unduh laporan Excel berdasarkan template resmi dengan satu tanggal laporan.</p>
                    </div>
                </div>

                <div class="theme-panel" style="max-width:720px;">
                    <div class="theme-section-head">
                        <div>
                            <h2>Pilih Tanggal Laporan</h2>
                            <p>Sistem akan mengambil harga pada tanggal terpilih lalu membandingkannya dengan tanggal data sebelumnya.</p>
                        </div>
                    </div>

                    <?php if ($errorMessage !== ''): ?>
                        <p class="list-flash-msg error"><?= htmlspecialchars($errorMessage) ?></p>
                    <?php endif; ?>

<form id="laporan-form" method="GET" action="laporan_excel.php" class="theme-form-grid theme-form-grid-single">
                        <label class="theme-field" for="tanggal_laporan">
                            <span>Tanggal Laporan</span>
                            <input
                                type="date"
                                id="tanggal_laporan"
                                name="tanggal_laporan"
                                value="<?= htmlspecialchars($tanggalLaporan) ?>"
                                required
                            >
                        </label>
                        <div style="grid-column: 1">
                            <button type="button" id="download-btn" class="theme-primary-btn" style="margin-top: 16px; width: 100%;">📥 Download Laporan</button>
                        </div>
                    </form>

<p class="subtitle" style="margin:16px 0 0;">
    Data terbaru yang tersedia di sistem: <strong><?= htmlspecialchars(laporanFormatTanggalIndonesia($latestTanggal)) ?></strong>.
    Template yang dipakai: <strong>Format Laporan.xlsx</strong>.
    Gambar akan muncul di bagian Dokumentasi PDF.
</p>
                </div>
            </div>
        </div>

    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
