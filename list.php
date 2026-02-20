<?php
require_once 'config/database.php';
require_once 'includes/auth_check.php';

// SEARCH
$keyword = $_GET['search'] ?? '';

$query = "
SELECT 
    h.id,
    b.nama_bahan,
    b.het_hap,
    h.harga,
    h.tanggal,
    h.rata_rata,
    h.persen_kenaikan,
    h.persen_penurunan,
    h.kenaikan_rp,
    h.penurunan_rp
FROM harga h
JOIN bahan_pokok b ON h.bahan_id = b.id
WHERE b.nama_bahan LIKE '%$keyword%'
ORDER BY h.tanggal DESC
";

$data = mysqli_query($conn, $query);
?>

<div class="layout">

<?php require_once 'includes/sidebar.php'; ?>

<div style="flex:1; display:flex; flex-direction:column;">

<?php require_once 'includes/header.php'; ?>

<div class="content">
<div class="container">    

<h1>List Data Harga</h1>
<p class="subtitle">Data harga bahan pokok</p>

<!-- SEARCH -->
<form method="GET" class="search-box">
    <input type="text" name="search" placeholder="Cari bahan..." value="<?= htmlspecialchars($keyword) ?>">
    <button type="submit">Cari</button>
</form>

<!-- TABLE -->
<table class="member-table">
<tr>
    <th>No</th>
    <th>Bahan</th>
    <th>Harga</th>
    <th>Rata² Harga</th>
    <th>HET/HAP</th>
    <th>Kenaikan (Rp)</th>
    <th>Kenaikan (%)</th>
    <th>Penurunan (Rp)</th>
    <th>Penurunan (%)</th>
    <th>Tanggal</th>
</tr>

<?php 
$no = 1;
while($row = mysqli_fetch_assoc($data)) { 
?>
<tr>
    <td><?= $no++ ?></td>

    <td><?= htmlspecialchars($row['nama_bahan']) ?></td>

    <td>Rp <?= number_format($row['harga'],0,',','.') ?></td>

    <!-- RATA-RATA -->
    <td>
        <?php 
        if ($row['rata_rata'] != NULL) {
            echo "Rp " . number_format($row['rata_rata'],0,',','.');
        } else {
            echo "-";
        }
        ?>
    </td>

    <!-- HET -->
    <td>
        <?php 
        if ($row['het_hap'] > 0) {
            echo "Rp " . number_format($row['het_hap'],0,',','.');
        } else {
            echo "-";
        }
        ?>
    </td>

    <!-- KENAIKAN RP -->
    <td>
        <?php
        if (!empty($row['kenaikan_rp']) && $row['kenaikan_rp'] > 0) {
            echo "<span style='color:green;font-weight:bold;'>
                    Rp " . number_format($row['kenaikan_rp'],0,',','.') . "
                  </span>";
        } else {
            echo "-";
        }
        ?>
    </td>

    <!-- KENAIKAN % -->
    <td>
        <?php
        if (!empty($row['persen_kenaikan']) && $row['persen_kenaikan'] > 0) {
            echo "<span style='color:green;font-weight:bold;'>
                    " . number_format($row['persen_kenaikan'],2) . " %
                  </span>";
        } else {
            echo "-";
        }
        ?>
    </td>

    <!-- PENURUNAN RP -->
    <td>
        <?php
        if (!empty($row['penurunan_rp']) && $row['penurunan_rp'] > 0) {
            echo "<span style='color:red;font-weight:bold;'>
                    Rp " . number_format($row['penurunan_rp'],0,',','.') . "
                  </span>";
        } else {
            echo "-";
        }
        ?>
    </td>

    <!-- PENURUNAN % -->
    <td>
        <?php
        if (!empty($row['persen_penurunan']) && $row['persen_penurunan'] > 0) {
            echo "<span style='color:red;font-weight:bold;'>
                    " . number_format($row['persen_penurunan'],2) . " %
                  </span>";
        } else {
            echo "-";
        }
        ?>
    </td>

    <td><?= $row['tanggal'] ?></td>
</tr>
<?php } ?>
</table>

</div>
</div>
</div>
</div>

<?php require_once 'includes/footer.php'; ?>
