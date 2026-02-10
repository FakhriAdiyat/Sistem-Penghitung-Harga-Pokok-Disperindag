<?php
require_once '../config/database.php';
require_once '../includes/auth_check.php';
require_once '../includes/role_check.php';

// total user
$total_user = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM users"));

// total bahan
$q_bahan = mysqli_query($conn, "SELECT COUNT(*) as total FROM bahan_pokok");
$total_bahan = mysqli_fetch_assoc($q_bahan)['total'];

// total harga
$total_harga = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM harga"));

// status harga
$q_status = mysqli_query($conn, "
    SELECT 
        SUM(CASE WHEN h1.harga > h2.harga THEN 1 ELSE 0 END) AS naik,
        SUM(CASE WHEN h1.harga < h2.harga THEN 1 ELSE 0 END) AS turun,
        SUM(CASE WHEN h1.harga = h2.harga THEN 1 ELSE 0 END) AS stabil
    FROM harga h1
    JOIN harga h2 ON h1.bahan_id = h2.bahan_id
    WHERE h1.tanggal = CURDATE()
      AND h2.tanggal = DATE_SUB(CURDATE(), INTERVAL 1 DAY)
");

$q_jenis = mysqli_query($conn, "
    SELECT COUNT(DISTINCT kategori) AS total
    FROM bahan_pokok
");
$jenis_komoditas = mysqli_fetch_assoc($q_jenis)['total'] ?? 0;

// harga rata-rata hari ini
$q_avg = mysqli_query($conn, "
    SELECT AVG(harga) AS rata
    FROM harga
    WHERE tanggal = CURDATE()
");
$data_avg = mysqli_fetch_assoc($q_avg);
$harga_rata_rata = 'Rp ' . number_format($data_avg['rata'] ?? 0, 0, ',', '.');

$status = mysqli_fetch_assoc($q_status);
$naik   = $status['naik']   ?? 0;
$turun  = $status['turun']  ?? 0;
$stabil = $status['stabil'] ?? 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Admin</title>
    <!-- <link rel="stylesheet" href="../assets/css/style.css"> -->
</head>
<body>

<div class="layout">

    <?php require_once '../includes/sidebar.php'; ?>

    <div class="main">

        <?php require_once '../includes/header.php'; ?>

        <div class="content">

            <h1>Dashboard Admin</h1>
            <p class="subtitle">Monitoring harga bahan pokok</p>

            <div class="statistik">
                <div class="stat-card total">
                    <h2><?= $total_bahan ?></h2>
                    <p>Total Bahan Pokok</p>
                </div>
                <div class="stat-card naik">
                    <h2><?= $naik ?></h2>
                    <p>Harga Naik</p>
                </div>
                <div class="stat-card turun">
                    <h2><?= $turun ?></h2>
                    <p>Harga Turun</p>
                </div>
                <div class="stat-card stabil">
                    <h2><?= $stabil ?></h2>
                    <p>Harga Stabil</p>
                </div>

                <div class="card">
                    <h2><?= $total_user ?></h2>
                    <p>Total User</p>
                </div>

                <div class="card">
                    <h2><?= $total_bahan ?></h2>
                    <p>Komoditas</p>
                </div>

                <div class="card">
                    <h2><?= $jenis_komoditas ?></h2>
                    <p>Jenis Komoditas</p>
                </div>

                <div class="card">
                    <h2><?= $harga_rata_rata ?></h2>
                    <p>Harga Rata-rata</p>
                </div>
            </div>

            <div class="chart-container">
                <h3>Grafik Perubahan Harga</h3>
                <canvas id="grafikHarga"></canvas>
            </div>

        </div>
    </div>
</div>

<script>
const chartData = [<?= $naik ?>, <?= $turun ?>, <?= $stabil ?>];
</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="../assets/js/dashboard.js"></script>

</body>
</html>



<?php
require_once '../includes/footer.php';
?>
