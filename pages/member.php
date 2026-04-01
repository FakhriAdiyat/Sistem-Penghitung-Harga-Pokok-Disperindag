<?php
require_once '../config/database.php';
require_once '../includes/auth_check.php';
require_once '../includes/role_check.php';

// Ambil semua member
$q_member = mysqli_query($conn, "SELECT * FROM users ORDER BY id DESC");

// total user
    $total_user = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM users"));
?>



<div class="layout">

    <!-- SIDEBAR -->
    <?php require_once '../includes/sidebar.php'; ?>

    <!-- WRAPPER HEADER + CONTENT -->
    <div style="flex:1; display:flex; flex-direction:column;">

        <!-- HEADER -->
        <?php require_once '../includes/header.php'; ?>

        <!-- CONTENT -->
        <div class="content">
            <div class="container">

                <h1>Manajemen Member</h1>
                <p class="subtitle">Kelola akun member sistem</p>

                <!-- FORM TAMBAH MEMBER -->
                <div class="form-box">
                    <h3>Tambah Member</h3>

                    <form action="member_process.php" method="POST">

                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" name="username" required>
                        </div>

                        <div class="form-group">
                            <label>Password</label>
                            <div class="password-wrapper">
    <input type="password" name="password" id="passwordInput" placeholder="Password" required>

    <span class="toggle-password" onclick="togglePassword()">
        <!-- ICON MATA (SVG) -->
        <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="gray" viewBox="0 0 24 24">
            <path d="M12 5c-7 0-10 7-10 7s3 7 10 7 10-7 10-7-3-7-10-7zm0 12a5 5 0 1 1 0-10 5 5 0 0 1 0 10zm0-8a3 3 0 1 0 0 6 3 3 0 0 0 0-6z"/>
        </svg>
    </span>
</div>
                        </div>

                        <div class="form-group">
                            <label>Role</label>
                            <select name="role" required>
                                <option value="member">Member</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>

                        <button type="submit" class="btn-save">Simpan</button>

                    </form>
                </div>

                <hr class="divider">

                <!-- LIST MEMBER -->
                <h3>Daftar Member</h3>
                <div class="statistik">
                    <div class="stat-card">
                    <h2><?= $total_user ?></h2>
                    <p>Total User</p>
                </div>

                </div>


                
    <table class="member-table">
<tr>
    <th>No</th>
    <th>Username</th>
    <th>Role</th>
    <th>Aksi</th>
</tr>

<?php $no = 1; while($m = mysqli_fetch_assoc($q_member)) { ?>
<tr>
    <td><?= $no ?></td>
    <td><?= htmlspecialchars($m['username']) ?></td>
    <td>
        <?php if ($m['role'] == 'admin') { ?>
            <span class="role-admin">Admin</span>
        <?php } else { ?>
            <span class="role-member">Member</span>
        <?php } ?>
    </td>

    <td>
    <?php if ($m['id'] == $_SESSION['id']) { ?>

        <!-- AKUN SENDIRI -->
        <a href="edit_member.php?id=<?= $m['id'] ?>" class="btn-edit">
            Edit
        </a>

    <?php } else { ?>

        <!-- USER LAIN -->
        <a href="edit_member.php?id=<?= $m['id'] ?>" class="btn-edit">
            Edit
        </a>

        <a href="<?= BASE_URL ?>process/delete_member.php?id=<?= $m['id'] ?>" 
           class="btn-delete"
           onclick="return confirm('Yakin ingin menghapus user ini?')">
           Hapus
        </a>

    <?php } ?>
</td>
</tr>
<?php $no++; } ?>
</table>



            </div>
        </div>

    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
