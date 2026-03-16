<?php
require_once '../config/database.php';
require_once '../includes/auth_check.php';
$allowed_roles = ['admin', 'member'];
require_once '../includes/role_check.php';

// Ambil semua bahan untuk dropdown
$bahan = mysqli_query($conn, "SELECT id, nama_bahan FROM bahan_pokok ORDER BY nama_bahan ASC");
?>

<div class="layout">

    <?php require_once '../includes/sidebar.php'; ?>

    <div style="flex:1; display:flex; flex-direction:column;">

        <?php require_once '../includes/header.php'; ?>

        <div class="content">
            <div class="container">    
                <h1>Import Data Harga</h1>
                <p class="subtitle">Upload data harga bahan pokok. Semua format didukung selama isi file memuat: <strong>Tanggal</strong>, <strong>Nama Bahan</strong>, dan <strong>Harga</strong> (urutan kolom boleh berbeda).</p>

                <div class="form-box">

                    <h3>Import Harga</h3>
                    <p style="margin-bottom:1rem; color:#555; font-size:0.95rem;">Format file: Excel (.xlsx, .xls), ODS, CSV, atau PDF. Sistem akan mendeteksi kolom Tanggal, Nama Bahan, dan Harga secara otomatis, lalu menghitung statistik dan menyimpan ke database.</p>

                    <?php if (isset($_SESSION['import_flash'])): ?>
                        <?php
                            $flash = $_SESSION['import_flash'];
                            unset($_SESSION['import_flash']);
                            $type = ($flash['type'] ?? 'success') === 'error' ? 'error' : 'success';
                            $title = (string) ($flash['title'] ?? '');
                            $message = (string) ($flash['message'] ?? '');
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

                    <form action="import_process.php" method="post" enctype="multipart/form-data">

                        <div class="form-group">
                            <label>Pilih File (Excel / CSV / PDF)</label>
                            <input type="file" name="file_import" accept=".csv,.xlsx,.xls,.ods,.pdf" required>
                        </div>

                        <div class="form-group">
                            <label>Pilih Bahan (Opsional - Untuk Update HET)</label>
                            <select name="bahan_keyword">
                                <option value="">-- Tidak Update HET --</option>
                                <?php while($b = mysqli_fetch_assoc($bahan)) { ?>
                                    <option value="<?= $b['nama_bahan'] ?>">
                                        <?= $b['nama_bahan'] ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Masukkan HET/HAP (Opsional)</label>
                            <input type="number" name="het_hap" step="0.01" placeholder="Masukkan HET/HAP">
                        </div>

                        <button type="submit" class="btn-save">Import Data</button>

                    </form>
                </div>

                <hr style="margin:25px 0; border:none; border-top:2px solid #e5e7eb;">

                <p class="subtitle" style="margin:0;">
                    Tips: kalau Anda mengisi <strong>Pilih Bahan</strong> dan <strong>HET/HAP</strong>, maka HET/HAP bahan tersebut akan ikut ter-update saat import.
                </p>

            </div>
        </div>

    </div>
</div>

<script src="<?= BASE_URL ?>assets/js/popup.js"></script>
<?php require_once '../includes/footer.php'; ?>
