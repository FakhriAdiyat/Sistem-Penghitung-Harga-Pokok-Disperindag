<?php require_once __DIR__ . '/../config/app.php'; ?>
<div class="sidebar">

    <!-- <div class="sidebar-logo">
        <img src="<?= BASE_URL ?>assets/img/logo1.png" alt="Logo">
    </div> -->
    <!-- <div class="sidebar-title">Disperindag</div> -->

    <ul class="sidebar-menu">

        <?php if ($_SESSION['role'] == 'admin') { ?>

            <li>
                <a href="<?= BASE_URL ?>pages/dashboard.php">Dashboard</a>
            </li>

            <li>
                <a href="<?= BASE_URL ?>pages/member.php">Member</a>
            </li>

            <li>
                <a href="<?= BASE_URL ?>pages/list.php">List Data</a>
            </li> 

            <li>
                <a href="<?= BASE_URL ?>laporan/laporan.php">Laporan</a>
            </li>

        <?php } else { ?>

            <li>
                <a href="<?= BASE_URL ?>pages/dashboard.php">Dashboard</a>
            </li>

             <li>
                <a href="<?= BASE_URL ?>pages/list.php">List Data</a>
            </li> 

            <li>
                <a href="<?= BASE_URL ?>laporan/laporan.php">Laporan</a>
            </li>

        <?php } ?>

        <li class="logout">
            <a href="<?= BASE_URL ?>auth/logout.php" data-confirm-action="logout">Logout</a>
        </li>

    </ul>

</div>
