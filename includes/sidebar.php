<div class="sidebar">

    <div class="sidebar-logo">
        <img src="assets/img/logo3.png" alt="Logo">
    </div>
    <!-- <div class="sidebar-title">Disperindag</div> -->

    <ul class="sidebar-menu">

        <?php if ($_SESSION['role'] == 'admin') { ?>

            <li>
                <a href="dashboard.php">Dashboard</a>
            </li>

            <li>
                <a href="member.php">Member</a>
            </li>

            <li>
                <a href="list.php">List Data</a>
            </li> 

            <li>
                <a href="import.php">Import Data</a>
            </li>

            <li>
                <a href="ekspor.php">Ekspor Data</a>
            </li>

        <?php } else { ?>

            <li>
                <a href="dashboard.php">Dashboard</a>
            </li>

            <li>
                <a href="member.php">Member</a>
            </li>

            <li>
                <a href="list.php">List Data</a>
            </li> 

            <li>
                <a href="import.php">Import Data</a>
            </li>

            <li>
                <a href="ekspor.php">Ekspor Data</a>
            </li>

        <?php } ?>

        <li class="logout">
            <a href="auth/logout.php" onclick="return confirmLogout()">Logout</a>
        </li>

    </ul>

</div>
