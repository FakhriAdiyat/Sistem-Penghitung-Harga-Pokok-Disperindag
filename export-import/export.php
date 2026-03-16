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
            <div class="container">

                <h1>Export Data</h1>
                <p class="subtitle">Download data harga berdasarkan periode (mingguan, bulanan, atau tahunan).</p>

                <div class="form-box">
                    <h3>Pilih Periode Export</h3>

                    <div class="form-group">
                        <a href="export_process.php?period=weekly" class="btn-save" style="display:block; text-align:center; text-decoration:none;">
                            Download Mingguan (7 hari terakhir)
                        </a>
                    </div>

                    <div class="form-group">
                        <a href="export_process.php?period=monthly" class="btn-save" style="display:block; text-align:center; text-decoration:none;">
                            Download Bulanan (bulan ini)
                        </a>
                    </div>

                    <div class="form-group">
                        <a href="export_process.php?period=yearly" class="btn-save" style="display:block; text-align:center; text-decoration:none;">
                            Download Tahunan (tahun ini)
                        </a>
                    </div>

                    <p style="margin: 12px 0 0; color:#555; font-size:0.95rem;">
                        File yang diunduh berformat Excel (.xlsx) dan berisi kolom: Nama Bahan, Harga, Harga Rata-rata, Persen Penyimpangan, Fluktuasi Persen, Stabilitas Persen, Persen Naik/Turun, Naik/Turun (Rp).
                    </p>
                </div>

            </div>
        </div>

    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
