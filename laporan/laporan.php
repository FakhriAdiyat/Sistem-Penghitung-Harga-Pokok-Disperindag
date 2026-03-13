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

                <h1>Laporan Harga</h1>
                <p class="subtitle">Pilih tanggal kemarin dan hari ini, lalu unduh PDF.</p>

                <div class="form-box">
                    <h3>Pilih Tanggal Laporan</h3>

                    <form method="GET" action="laporan/laporan_pdf.php">
                        <div class="form-group">
                            <label for="tanggal_awal">Tanggal Kemarin</label>
                            <input
                                type="date"
                                id="tanggal_awal"
                                name="tanggal_awal"
                                value="<?= date('Y-m-d', strtotime('-1 day')) ?>"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label for="tanggal_akhir">Tanggal Hari Ini</label>
                            <input
                                type="date"
                                id="tanggal_akhir"
                                name="tanggal_akhir"
                                value="<?= date('Y-m-d') ?>"
                                required
                            >
                        </div>

                        <button type="submit" class="btn-save">Download PDF</button>
                    </form>
                </div>

            </div>
        </div>

    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
