<?php
require_once '../config/database.php';
require_once '../includes/auth_check.php';
$allowed_roles = ['admin', 'member'];
require_once '../includes/role_check.php';

?>

<div class="layout">

    <?php require_once '../includes/sidebar.php'; ?>

    <div style="flex:1; display:flex; flex-direction:column;">

        <?php require_once '../includes/header.php'; ?>

        <div class="content">
            <div class="container theme-page-shell">
                <div class="theme-page-header">
                    <div class="theme-page-title">
                        <h1>Export Data</h1>
                        <p class="subtitle">Unduh data harga dengan tampilan dan palet yang sejalan dengan halaman List Data.</p>
                    </div>
                    <div class="theme-page-badge">
                        <span>Output</span>
                        <strong>Excel .xlsx</strong>
                    </div>
                </div>

                <div class="theme-panel">
                    <div class="theme-section-head">
                        <div>
                            <h2>Pilih Periode Export</h2>
                            <p>Pilih salah satu periode cepat untuk mengunduh ringkasan harga.</p>
                        </div>
                    </div>

                    <div class="theme-option-grid">
                        <article class="theme-option-card">
                            <div>
                                <h3>Mingguan</h3>
                                <p>Unduh data 7 hari terakhir untuk kebutuhan evaluasi cepat.</p>
                            </div>
                            <a href="../process/export_process.php?period=weekly" class="theme-primary-btn">Download Mingguan</a>
                        </article>

                        <article class="theme-option-card">
                            <div>
                                <h3>Bulanan</h3>
                                <p>Unduh seluruh data pada bulan berjalan untuk analisis periodik.</p>
                            </div>
                            <a href="../process/export_process.php?period=monthly" class="theme-primary-btn">Download Bulanan</a>
                        </article>

                        <article class="theme-option-card">
                            <div>
                                <h3>Tahunan</h3>
                                <p>Unduh rekap tahun berjalan untuk kebutuhan laporan besar.</p>
                            </div>
                            <a href="../process/export_process.php?period=yearly" class="theme-primary-btn">Download Tahunan</a>
                        </article>
                    </div>
                </div>

                <div class="theme-note-card">
                    File yang diunduh berformat Excel (.xlsx) dan berisi kolom Nama Bahan, Harga, Harga Rata-rata, Persen Penyimpangan, Fluktuasi Persen, Stabilitas Persen, Persen Naik/Turun, dan Naik/Turun (Rp).
                </div>
            </div>
        </div>

    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
