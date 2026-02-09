<?php
require_once '../config/database.php';
require_once '../includes/auth_check.php';

require_once '../includes/header.php';
require_once '../includes/sidebar.php';

// SEARCH
$keyword = $_GET['search'] ?? '';

$query = "
SELECT 
    h.id,
    b.nama_bahan,
    h.harga,
    h.tanggal
FROM harga h
JOIN bahan_pokok b ON h.bahan_id = b.id
WHERE b.nama_bahan LIKE '%$keyword%'
ORDER BY h.tanggal DESC
";

$data = mysqli_query($conn, $query);
?>

<div class="content">
    <h1>List Data Harga</h1>
    <p class="subtitle">Data harga bahan pokok</p>

    <!-- SEARCH -->
    <form method="GET" class="search-box">
        <input type="text" name="search" placeholder="Cari bahan..." value="<?= $keyword ?>">
        <button type="submit">Cari</button>
    </form>

    <!-- TABLE -->
    <table border="1" cellpadding="10" style="background:white; border-collapse:collapse; width:100%;">
        <tr>
            <th>No</th>
            <th>Bahan</th>
            <th>Harga</th>
            <th>Tanggal</th>
        </tr>

        <?php 
        $no = 1;
        while($row = mysqli_fetch_assoc($data)) { 
        ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= $row['nama_bahan'] ?></td>
            <td>Rp <?= number_format($row['harga'],0,',','.') ?></td>
            <td><?= $row['tanggal'] ?></td>
        </tr>
        <?php } ?>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>
