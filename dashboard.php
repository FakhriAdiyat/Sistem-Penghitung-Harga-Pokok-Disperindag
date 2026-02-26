<?php
require_once 'config/database.php';
require_once 'includes/auth_check.php';

// ================= DATA STATISTIK (UNTUK ADMIN SAJA) =================
if ($_SESSION['role'] === 'admin') {


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

// ================= FILTER PERIODE =================
$periode = $_GET['periode'] ?? 'mingguan';

$labels = [];
$data   = [];

if ($periode == 'mingguan') {

    $query = mysqli_query($conn, "
        SELECT tanggal, AVG(harga) as rata
        FROM harga
        WHERE tanggal >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        GROUP BY tanggal
        ORDER BY tanggal ASC
    ");

    while ($row = mysqli_fetch_assoc($query)) {
        $labels[] = date('d M', strtotime($row['tanggal']));
        $data[]   = (float)$row['rata'];
    }

} elseif ($periode == 'bulanan') {

    $query = mysqli_query($conn, "
        SELECT DATE_FORMAT(tanggal,'%Y-%m') as bulan,
               AVG(harga) as rata
        FROM harga
        WHERE tanggal >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
        GROUP BY bulan
        ORDER BY bulan ASC
    ");

    while ($row = mysqli_fetch_assoc($query)) {
        $labels[] = $row['bulan'];
        $data[]   = (float)$row['rata'];
    }

} else { // tahunan

    $query = mysqli_query($conn, "
        SELECT YEAR(tanggal) as tahun,
               AVG(harga) as rata
        FROM harga
        GROUP BY tahun
        ORDER BY tahun ASC
    ");

    while ($row = mysqli_fetch_assoc($query)) {
        $labels[] = $row['tahun'];
        $data[]   = (float)$row['rata'];
    }
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
                

                <div class="stat-card-naik">
                    <h2><?= $naik ?></h2>
                    <p>Harga Naik</p>
                </div>

                <div class="stat-card-stabil">
                    <h2><?= $stabil ?></h2>
                    <p>Harga Stabil</p>
                </div>

                <div class="stat-card-turun">
                    <h2><?= $turun ?></h2>
                    <p>Harga Turun</p>
                </div>

                
            </div>

            <hr class="divider">

            <!-- FILTER PERIODE -->
            <div class="filter-area">
                <form method="GET">
                    <select name="periode" onchange="this.form.submit()">
                        <option value="mingguan" <?= $periode=='mingguan'?'selected':'' ?>>Mingguan</option>
                        <option value="bulanan" <?= $periode=='bulanan'?'selected':'' ?>>Bulanan</option>
                        <option value="tahunan" <?= $periode=='tahunan'?'selected':'' ?>>Tahunan</option>
                    </select>
                </form>
            </div>

            <div class="chart-container">
                <h3>Grafik Perubahan Harga</h3>
                <canvas id="dashboardChart"></canvas>
            </div>

        <?php } else { ?>

            <!-- DASHBOARD MEMBER -->
            <div class="statistik"> 
                <div class="member-area-card">
                    <h2>Member Area</h2>
                    <p>Anda login sebagai Member</p>
                </div>
                
                <div class="stat-card">
                    <h2><?= $total_user ?? 0 ?></h2>
                    <p>Total User</p>
                </div>

                <div class="stat-card">
                    <h2><?= $naik ?? 0 ?></h2>
                    <p>Harga Naik</p>
                </div>

                <div class="stat-card">
                    <h2><?= $turun ?? 0 ?></h2>
                    <p>Harga Turun</p>
                </div>

                <div class="stat-card">
                    <h2><?= $stabil ?? 0 ?></h2>
                    <p>Harga Stabil</p>
                </div>
            </div>

            <hr class="divider">

            <div class="chart-container">
                <h3>Grafik Perubahan Harga</h3>
                <canvas id="dashboardChart"></canvas>
            </div>

        <?php } ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const ctx = document.getElementById("dashboardChart");
    if (!ctx) return;

    new Chart(ctx, {
        type: "line",
        data: {
            labels: <?= json_encode($labels); ?>,
            datasets: [{
                label: "Rata-rata Harga",
                data: <?= json_encode($data); ?>,
                borderColor: "#16a34a",
                backgroundColor: "rgba(22,163,74,0.2)",
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: false
                }
            }
        }
    });

});
</script>

<?php require_once 'includes/footer.php'; ?>
