<?php
require_once '../config/database.php';
require_once '../includes/auth_check.php';
require_once '../includes/role_check.php';

require_once '../includes/header.php';
require_once '../includes/sidebar.php';

// Ambil semua member
$q_member = mysqli_query($conn, "SELECT * FROM users ORDER BY id DESC");
?>

<div class="content">
    <h1>Manajemen Member</h1>
    <p class="subtitle">Kelola akun member sistem</p>

    <!-- FORM TAMBAH MEMBER -->
    <div class="form-box">
        <h3>Tambah Member</h3>

        <form action="member_process.php" method="POST">
            <label>Username</label>
            <input type="text" name="username" required>

            <label>Password</label>
            <input type="password" name="password" required>

            <label>Role</label>
            <select name="role" required>
                <option value="member">Member</option>
                <option value="admin">Admin</option>
            </select>

            <button type="submit" class="btn-save">Simpan</button>
        </form>
    </div>

    <hr style="margin:30px 0;">

    <!-- LIST MEMBER -->
    <h3>Daftar Member</h3>

    <table border="1" cellpadding="10" style="background:white; border-collapse:collapse;">
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Role</th>
        </tr>

        <?php while($m = mysqli_fetch_assoc($q_member)) { ?>
        <tr>
            <td><?= $m['id'] ?></td>
            <td><?= $m['username'] ?></td>
            <td><?= $m['role'] ?></td>
        </tr>
        <?php } ?>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>
