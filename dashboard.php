<?php
require_once 'config/database.php';
require_once 'includes/auth_check.php';

$q_bahan = mysqli_query($conn, "SELECT id, nama_bahan FROM bahan_pokok ORDER BY nama_bahan ASC");
$bahan_list = [];
while ($q_bahan && ($b = mysqli_fetch_assoc($q_bahan))) {
    $bahan_list[] = $b;
}

$periode = strtolower(trim((string)($_GET['periode'] ?? 'mingguan')));
if (!in_array($periode, ['harian', 'mingguan', 'bulanan'], true)) {
    $periode = 'mingguan';
}

$tanggal_dari = $_GET['tanggal_dari'] ?? date('Y-m-d', strtotime('-7 days'));
$tanggal_sampai = $_GET['tanggal_sampai'] ?? date('Y-m-d');

$dari_obj = DateTime::createFromFormat('Y-m-d', $tanggal_dari) ?: new DateTime(date('Y-m-d', strtotime('-7 days')));
$sampai_obj = DateTime::createFromFormat('Y-m-d', $tanggal_sampai) ?: new DateTime(date('Y-m-d'));
if ($dari_obj > $sampai_obj) {
    $tmp = $dari_obj;
    $dari_obj = $sampai_obj;
    $sampai_obj = $tmp;
}
$tanggal_dari = $dari_obj->format('Y-m-d');
$tanggal_sampai = $sampai_obj->format('Y-m-d');

$dari_safe = mysqli_real_escape_string($conn, $tanggal_dari);
$sampai_safe = mysqli_real_escape_string($conn, $tanggal_sampai);

$default_bahan_id = 0;
$q_default = mysqli_query(
    $conn,
    "
    SELECT b.id
    FROM bahan_pokok b
    JOIN harga h ON h.bahan_id = b.id
    WHERE h.tanggal BETWEEN '$dari_safe' AND '$sampai_safe'
    GROUP BY b.id
    ORDER BY b.nama_bahan ASC
    LIMIT 1
"
);
if ($q_default && ($rdef = mysqli_fetch_assoc($q_default))) {
    $default_bahan_id = (int)$rdef['id'];
} elseif (!empty($bahan_list)) {
    $default_bahan_id = (int)$bahan_list[0]['id'];
}

$bahan_id = (int)($_GET['bahan_id'] ?? $default_bahan_id);
$bid = (int)$bahan_id;

if ($periode === 'harian') {
    $sql_chart = "
        SELECT
            h.tanggal AS label_raw,
            ROUND(AVG(h.harga), 2) AS harga_rata
        FROM harga h
        WHERE h.bahan_id = '$bid'
          AND h.tanggal BETWEEN '$dari_safe' AND '$sampai_safe'
        GROUP BY h.tanggal
        ORDER BY h.tanggal ASC
    ";
} elseif ($periode === 'mingguan') {
    $sql_chart = "
        SELECT
            DATE_SUB(h.tanggal, INTERVAL WEEKDAY(h.tanggal) DAY) AS label_raw,
            ROUND(AVG(h.harga), 2) AS harga_rata
        FROM harga h
        WHERE h.bahan_id = '$bid'
          AND h.tanggal BETWEEN '$dari_safe' AND '$sampai_safe'
        GROUP BY DATE_SUB(h.tanggal, INTERVAL WEEKDAY(h.tanggal) DAY)
        ORDER BY DATE_SUB(h.tanggal, INTERVAL WEEKDAY(h.tanggal) DAY) ASC
    ";
} else {
    $sql_chart = "
        SELECT
            DATE_FORMAT(h.tanggal, '%Y-%m-01') AS label_raw,
            ROUND(AVG(h.harga), 2) AS harga_rata
        FROM harga h
        WHERE h.bahan_id = '$bid'
          AND h.tanggal BETWEEN '$dari_safe' AND '$sampai_safe'
        GROUP BY DATE_FORMAT(h.tanggal, '%Y-%m-01')
        ORDER BY DATE_FORMAT(h.tanggal, '%Y-%m-01') ASC
    ";
}

$labels = [];
$dataHarga = [];
$q_chart = mysqli_query($conn, $sql_chart);
while ($q_chart && ($row = mysqli_fetch_assoc($q_chart))) {
    $label_date = $row['label_raw'];
    if ($periode === 'harian') {
        $labels[] = date('Y-m-d', strtotime($label_date));
    } elseif ($periode === 'mingguan') {
        $labels[] = 'Minggu ' . date('d M Y', strtotime($label_date));
    } else {
        $labels[] = date('M Y', strtotime($label_date));
    }
    $dataHarga[] = (float)$row['harga_rata'];
}

$q_status = mysqli_query($conn, "
    SELECT
        SUM(CASE WHEN h.persen_naik_turun > 0 THEN 1 ELSE 0 END) AS naik,
        SUM(CASE WHEN h.persen_naik_turun < 0 THEN 1 ELSE 0 END) AS turun,
        SUM(CASE WHEN h.persen_naik_turun = 0 OR h.persen_naik_turun IS NULL THEN 1 ELSE 0 END) AS stabil
    FROM harga h
    WHERE h.bahan_id = '$bid'
      AND h.tanggal BETWEEN '$dari_safe' AND '$sampai_safe'
");
$status = $q_status ? mysqli_fetch_assoc($q_status) : ['naik' => 0, 'turun' => 0, 'stabil' => 0];
$naik = (int)($status['naik'] ?? 0);
$turun = (int)($status['turun'] ?? 0);
$stabil = (int)($status['stabil'] ?? 0);
?>

<?php require_once 'includes/header.php'; ?>
<?php require_once 'includes/sidebar.php'; ?>

<div class="content">
    <div class="container">
        <h1>Fluktuasi Harga Pangan</h1>
        <p class="subtitle">Monitor perubahan harga berdasarkan komoditas dan rentang tanggal.</p>

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

        <div class="dashboard-filter-card">
            <form method="GET" class="dashboard-filter-form">
                <div class="dash-field">
                    <label>Komoditas</label>
                    <select name="bahan_id" required>
                        <?php foreach ($bahan_list as $b): ?>
                            <option value="<?= (int)$b['id'] ?>" <?= ((int)$b['id'] === $bahan_id) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($b['nama_bahan']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="dash-field">
                    <label>Periode</label>
                    <select name="periode">
                        <option value="harian" <?= $periode === 'harian' ? 'selected' : '' ?>>Perhari</option>
                        <option value="mingguan" <?= $periode === 'mingguan' ? 'selected' : '' ?>>Perminggu</option>
                        <option value="bulanan" <?= $periode === 'bulanan' ? 'selected' : '' ?>>Perbulan</option>
                    </select>
                </div>

                <div class="dash-field">
                    <label>Dari</label>
                    <input type="date" name="tanggal_dari" value="<?= htmlspecialchars($tanggal_dari) ?>" required>
                </div>

                <div class="dash-field">
                    <label>Sampai</label>
                    <input type="date" name="tanggal_sampai" value="<?= htmlspecialchars($tanggal_sampai) ?>" required>
                </div>

                <button type="submit" class="dash-search-btn" aria-label="Cari">Cari</button>
            </form>
        </div>

        <div class="chart-container dashboard-chart-box">
            <canvas id="dashboardChart"></canvas>
        </div>
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
            labels: <?= json_encode($labels) ?>,
            datasets: [{
                label: "Harga (Rp)",
                data: <?= json_encode($dataHarga) ?>,
                borderColor: "#16a34a",
                backgroundColor: "rgba(22,163,74,0.15)",
                borderWidth: 3,
                pointRadius: 4,
                pointHoverRadius: 6,
                tension: 0.35,
                fill: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true
                }
            },
            scales: {
                y: {
                    title: {
                        display: true,
                        text: "Harga (Rp)"
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: "Tanggal"
                    }
                }
            }
        }
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
