<?php
require_once 'config/database.php';
require_once 'includes/auth_check.php';
require_once 'includes/role_check.php';

// Ambil semua bahan untuk dropdown
$bahan = mysqli_query($conn, "SELECT id, nama_bahan FROM bahan_pokok ORDER BY nama_bahan ASC");
?>

<div class="layout">

    <?php require_once 'includes/sidebar.php'; ?>

    <div style="flex:1; display:flex; flex-direction:column;">

        <?php require_once 'includes/header.php'; ?>

        <div class="content">
            <div class="container">    
                <h1>Import Data Harga</h1>
                <p class="subtitle">Upload data harga bahan pokok</p>

                <div class="form-box">

                    <!-- Tambahan Judul di Dalam Card -->
                    <h3>Import Harga</h3>

                    <form action="import_process.php" method="post" enctype="multipart/form-data">

                        <!-- FILE -->
                        <label>File Excel / CSV</label>
                        <input type="file" name="file_import" accept=".csv, .xlsx" required>

                        <!-- PILIH BAHAN UNTUK UPDATE HET -->
                        <label>Pilih Bahan (Opsional - Untuk Update HET)</label>
                        <select name="bahan_keyword">
                            <option value="">-- Tidak Update HET --</option>
                            <?php while($b = mysqli_fetch_assoc($bahan)) { ?>
                                <option value="<?= $b['nama_bahan'] ?>">
                                    <?= $b['nama_bahan'] ?>
                                </option>
                            <?php } ?>
                        </select>

                        <!-- INPUT HET -->
                        <label>Masukkan HET/HAP</label>
                        <input type="number" name="het_hap" step="0.01" placeholder="Masukkan HET">

                        <button type="submit" class="btn-save">Import Data</button>

                    </form>
                </div>

            </div>
        </div>

    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
