<?php if ($_SESSION['role'] == 'admin') { ?>
    <a href="../admin/dashboard.php">Dashboard</a>
    <a href="../admin/bahan/index.php">Bahan Pokok</a>
    <a href="../admin/harga/index.php">Harga</a>
<?php } else { ?>
    <a href="../member/dashboard.php">Dashboard</a>
    <a href="../member/laporan.php">Laporan Harga</a>
<?php } ?>
