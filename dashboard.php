<?php
require_once 'config/database.php';
require_once 'includes/auth_check.php';

$role = $_SESSION['role'] ?? '';
$username = $_SESSION['username'] ?? 'User';

/* ======================================================
   FUNCTION HITUNG STABILITAS
====================================================== */
function hitungStabilitas($conn, $bahan_id, $periode)
{
    switch ($periode) {
        case 'mingguan':
            $filter = "INTERVAL 7 DAY";
            break;
        case 'bulanan':
            $filter = "INTERVAL 1 MONTH";
            break;
        case 'tahunan':
            $filter = "INTERVAL 1 YEAR";
            break;
        default:
            return 0;
    }

    $query = mysqli_query($conn, "
        SELECT harga
        FROM harga
        WHERE bahan_id = '$bahan_id'
        AND tanggal >= DATE_SUB(CURDATE(), $filter)
        ORDER BY tanggal DESC
    ");

    $prices = [];
    while ($row = mysqli_fetch_assoc($query)) {
        $prices[] = (float)$row['harga'];
    }

    if (count($prices) < 2) {
        return 0;
    }

    $avg = array_sum($prices) / count($prices);

    $total_dev = 0;
    foreach ($prices as $price) {
        $total_dev += abs($price - $avg);
    }

    $avg_dev = $total_dev / count($prices);
    $stabilitas = 100 - (($avg_dev / $avg) * 100);

    return round($stabilitas, 2);
}

/* ======================================================
   DATA ADMIN
====================================================== */
$total_user = 0;
$total_bahan = 0;
$naik = $turun = $stabil = 0;

if ($role === 'admin') {

    $total_user = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM users"));

    $result_bahan = mysqli_query($conn, "SELECT COUNT(*) as total FROM bahan_pokok");
    $total_bahan = mysqli_fetch_assoc($result_bahan)['total'] ?? 0;

    $result_status = mysqli_query($conn, "
    SELECT 
        SUM(naik) AS naik,
        SUM(turun) AS turun,
        SUM(stabil) AS stabil
    FROM (
        SELECT 
            bahan_id,
            CASE WHEN MAX(CASE WHEN tanggal = CURDATE() THEN harga END) >
                      MAX(CASE WHEN tanggal = DATE_SUB(CURDATE(), INTERVAL 1 DAY) THEN harga END)
                 THEN 1 ELSE 0 END AS naik,

            CASE WHEN MAX(CASE WHEN tanggal = CURDATE() THEN harga END) <
                      MAX(CASE WHEN tanggal = DATE_SUB(CURDATE(), INTERVAL 1 DAY) THEN harga END)
                 THEN 1 ELSE 0 END AS turun,

            CASE WHEN MAX(CASE WHEN tanggal = CURDATE() THEN harga END) =
                      MAX(CASE WHEN tanggal = DATE_SUB(CURDATE(), INTERVAL 1 DAY) THEN harga END)
                 THEN 1 ELSE 0 END AS stabil
        FROM harga
        WHERE tanggal IN (CURDATE(), DATE_SUB(CURDATE(), INTERVAL 1 DAY))
        GROUP BY bahan_id
    ) x
");

    $status = mysqli_fetch_assoc($result_status);
    $naik   = $status['naik'] ?? 0;
    $turun  = $status['turun'] ?? 0;
    $stabil = $status['stabil'] ?? 0;
}

/* ======================================================
   HITUNG STABILITAS SEMUA BAHAN
====================================================== */
$data_stabilitas = [];

$bahan_query = mysqli_query($conn, "SELECT id, nama_bahan FROM bahan_pokok");

while ($row = mysqli_fetch_assoc($bahan_query)) {
    $data_stabilitas[] = [
        'nama'     => $row['nama_bahan'],
        'mingguan' => hitungStabilitas($conn, $row['id'], 'mingguan'),
        'bulanan'  => hitungStabilitas($conn, $row['id'], 'bulanan'),
        'tahunan'  => hitungStabilitas($conn, $row['id'], 'tahunan')
    ];
}

/* ======================================================
   DATA GRAFIK DASHBOARD 
====================================================== */
$q_grafik = mysqli_query($conn, "
    SELECT 
        DATE_FORMAT(tanggal, '%b %Y') AS bulan,
        AVG(harga) AS rata_harga
    FROM harga
    WHERE tanggal >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)
    GROUP BY bulan
    ORDER BY MIN(tanggal)
");

while ($row = mysqli_fetch_assoc($q_grafik)) {
    $grafik_labels[] = $row['bulan'];
    $grafik_data[]   = round($row['rata_harga'], 2);
}

?>



<?php require_once 'includes/header.php'; ?>
<?php require_once 'includes/sidebar.php'; ?>



<div class="content">
    <div class="container">
        <h1>Dashboard</h1>
        <p class="subtitle">Selamat datang, <?= htmlspecialchars($username); ?></p>

        <?php if ($role === 'admin') : ?>

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

            <h3>Stabilitas Harga</h3>
            <div class="statistik">
                <?php foreach ($data_stabilitas as $item) : ?>
                    <div class="stat-card">
                        <h4><?= htmlspecialchars($item['nama']); ?></h4>
                        Mingguan: <?= $item['mingguan']; ?>% <br>
                        Bulanan: <?= $item['bulanan']; ?>% <br>
                        Tahunan: <?= $item['tahunan']; ?>%
                    </div>
                <?php endforeach; ?>
            </div>

            <hr class="divider">

            <div class="chart-container">
                <h3>Grafik Perubahan Harga</h3>
                <canvas id="dashboardchart"></canvas>
            </div>

        <?php else : ?>

            <div class="statistik">
                <div class="member-area-card">
                    <h2>Member Area</h2>
                    <p>Anda login sebagai Member</p>
                </div>
            </div>

        <?php endif; ?>

    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const grafikLabels = <?= json_encode($grafik_labels); ?>;
    const grafikData   = <?= json_encode($grafik_data); ?>;
</script>
<script src="assets/js/chart.js"></script>
<?php require_once 'includes/footer.php'; ?>
