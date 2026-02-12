<?php
require_once 'config/database.php';
require_once 'includes/auth_check.php';

// ================= DATA STATISTIK (UNTUK ADMIN SAJA) =================
if ($_SESSION['role'] === 'admin') {

    // total user
    $total_user = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM users"));

    // total bahan
    $q_bahan = mysqli_query($conn, "SELECT COUNT(*) as total FROM bahan_pokok");
    $total_bahan = mysqli_fetch_assoc($q_bahan)['total'] ?? 0;

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

    $status = mysqli_fetch_assoc($q_status);
    $naik   = $status['naik'] ?? 0;
    $turun  = $status['turun'] ?? 0;
    $stabil = $status['stabil'] ?? 0;
}
?>

<?php require_once 'includes/header.php'; ?>
<?php require_once 'includes/sidebar.php'; ?>

<div class="content">
    <div class="container">
        <h1>Dashboard</h1>
        <p class="subtitle">
            Selamat datang, <?= $_SESSION['username']; ?>
        </p>

        <?php if ($_SESSION['role'] === 'admin') { ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

            <!-- DASHBOARD ADMIN -->
            <div class="statistik">
                <div class="stat-card">
                    <h2><?= $total_user ?></h2>
                    <p>Total User</p>
                </div>

                <div class="stat-card">
                    <h2><?= $naik ?></h2>
                    <p>Harga Naik</p>
                </div>

                <div class="stat-card">
                    <h2><?= $turun ?></h2>
                    <p>Harga Turun</p>
                </div>

                <div class="stat-card">
                    <h2><?= $stabil ?></h2>
                    <p>Harga Stabil</p>
                </div>
            </div>

            <hr class="divider">

            <div class="chart-container">
                <h3>Grafik Perubahan Harga</h3>
                <canvas id="grafikHarga"></canvas>
            </div>

        <?php } else { ?>

            <!-- DASHBOARD MEMBER -->
            <div class="statistik"> 
                <div class="member-area-card">
                    <h2>Member Area</h2>
                    <p>Anda login sebagai Member</p>
                </div>
                <div class="stat-card">
                    <h2><?= $total_user ?></h2>
                    <p>Total User</p>
                </div>

                <div class="stat-card">
                    <h2><?= $naik ?></h2>
                    <p>Harga Naik</p>
                </div>

                <div class="stat-card">
                    <h2><?= $turun ?></h2>
                    <p>Harga Turun</p>
                </div>

                <div class="stat-card">
                    <h2><?= $stabil ?></h2>
                    <p>Harga Stabil</p>
                </div>
            </div>

            <hr class="divider">

            <div class="chart-container">
                <h3>Grafik Perubahan Harga</h3>
                <canvas id="grafikHarga"></canvas>
            </div>

        <?php } ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
