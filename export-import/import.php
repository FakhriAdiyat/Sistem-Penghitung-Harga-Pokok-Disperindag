<?php
require_once '../config/database.php';
require_once '../includes/auth_check.php';
$allowed_roles = ['admin', 'member'];
require_once '../includes/role_check.php';

// Ambil semua bahan untuk dropdown
$bahan = mysqli_query($conn, "SELECT id, nama_bahan FROM bahan_pokok ORDER BY nama_bahan ASC");
$import_template_url = BASE_URL . 'assets/Format/' . rawurlencode('Format Import Data Bapok.xlsx');
$importFlash = null;
if (isset($_SESSION['import_flash']) && is_array($_SESSION['import_flash'])) {
    $importFlash = $_SESSION['import_flash'];
    unset($_SESSION['import_flash']);
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
                        <h1>Import Data Harga</h1>
                        <p class="subtitle">Upload data harga dengan tema dan tata letak yang sama seperti halaman List Data.</p>
                    </div>
                    <div class="theme-page-badge">
                        <span>Format</span>
                        <strong>Harian & Bulanan</strong>
                    </div>
                </div>

                <div class="theme-note-card">
                    Sistem mendukung format harian dengan kolom <strong>Tanggal</strong>, <strong>Nama Bahan</strong>, dan <strong>Harga</strong>, serta laporan bulanan perwilayah dengan kolom tanggal <strong>1-31</strong>.
                </div>

                <div class="theme-panel">
                    <div class="theme-section-head">
                        <div>
                            <h2>Import Harga</h2>
                            <p>Format file: Excel (.xlsx, .xls), ODS, CSV, atau PDF. Sistem akan membaca dua pola utama: format harian biasa dan laporan bulanan perwilayah.</p>
                        </div>
                    </div>

                    <div class="import-template-box import-template-box-page">
                        <div class="import-template-copy">
                            <p>Silakan unduh format data bapok sebelum melakukan import.</p>
                        </div>
                        <a href="<?= htmlspecialchars($import_template_url) ?>" class="import-template-download" download>
                            Download Format
                        </a>
                    </div>

                    <?php if ($importFlash): ?>
                        <?php
                            $type = ($importFlash['type'] ?? 'success') === 'error' ? 'error' : 'success';
                            $title = (string) ($importFlash['title'] ?? '');
                            $message = (string) ($importFlash['message'] ?? '');
                        ?>
                        <div class="popup-overlay" role="alert" aria-live="assertive">
                            <div class="popup-card <?= $type === 'success' ? 'popup-card-success' : 'popup-card-error' ?>" role="document">
                                <div class="popup-icon <?= $type === 'success' ? 'popup-icon-success' : 'popup-icon-error' ?>" aria-hidden="true">
                                    <?php if ($type === 'success'): ?>
                                        <span class="popup-check popup-check-short"></span>
                                        <span class="popup-check popup-check-long"></span>
                                    <?php else: ?>
                                        <span class="popup-x popup-x-left"></span>
                                        <span class="popup-x popup-x-right"></span>
                                    <?php endif; ?>
                                </div>
                                <div class="popup-title"><?= htmlspecialchars($title) ?></div>
                                <div class="popup-message"><?= htmlspecialchars($message) ?></div>
                                <button type="button" class="popup-close" onclick="closePopup()">Tutup</button>
                            </div>
                        </div>
                    <?php endif; ?>

                    <form action="../process/import_process.php" method="post" enctype="multipart/form-data" class="theme-form-grid theme-form-grid-single">
                        <label class="theme-field">
                            <span>Pilih File</span>
                            <input type="file" name="file_import" accept=".csv,.xlsx,.xls,.ods,.pdf" required>
                        </label>

                        <label class="theme-field">
                            <span>Pilih Bahan</span>
                            <select name="bahan_keyword">
                                <option value="">-- Tidak Update HET --</option>
                                <?php while($b = mysqli_fetch_assoc($bahan)) { ?>
                                    <option value="<?= htmlspecialchars($b['nama_bahan']) ?>">
                                        <?= htmlspecialchars($b['nama_bahan']) ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </label>

                        <label class="theme-field">
                            <span>HET / HAP</span>
                            <input type="number" name="het_hap" step="0.01" placeholder="Masukkan HET/HAP">
                        </label>

                        <button type="submit" class="theme-primary-btn">Import Data</button>
                    </form>
                </div>

                <div class="theme-note-card">
                    Tips: kalau Anda mengisi <strong>Pilih Bahan</strong> dan <strong>HET/HAP</strong>, maka HET/HAP bahan tersebut akan ikut ter-update saat import. Untuk laporan bulanan, pastikan kolom tanggal 1-31 terisi angka harga.
                </div>
            </div>
        </div>

    </div>
</div>
<?php require_once '../includes/footer.php'; ?>
