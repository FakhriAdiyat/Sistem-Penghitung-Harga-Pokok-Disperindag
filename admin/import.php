<?php
require_once '../config/database.php';
require_once '../includes/auth_check.php';
require_once '../includes/role_check.php';
?>

<!DOCTYPE html>
<?php
require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<div class="content">
    <h1>Import Data Harga</h1>
    <p class="subtitle">Upload data harga bahan pokok</p>

    <!-- FORM IMPORT -->
    <div class="form-box">
        <form action="import_process.php" method="post" enctype="multipart/form-data">
            <label>File Excel / CSV</label>
            <input type="file" name="file_import" accept=".csv, .xlsx" required>

            <button type="submit" class="btn-save">Import Data</button>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
