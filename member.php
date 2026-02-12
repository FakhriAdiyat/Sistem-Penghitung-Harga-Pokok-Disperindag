<?php
require_once 'config/database.php';
require_once 'includes/auth_check.php';
require_once 'includes/role_check.php';

// Ambil semua member
$q_member = mysqli_query($conn, "SELECT * FROM users ORDER BY id DESC");
?>

<div class="layout">

    <!-- SIDEBAR -->
    <?php require_once 'includes/sidebar.php'; ?>

    <!-- WRAPPER HEADER + CONTENT -->
    <div style="flex:1; display:flex; flex-direction:column;">

        <!-- HEADER -->
        <?php require_once 'includes/header.php'; ?>

        <!-- CONTENT (INI HTML KAMU ASLI) -->
        <div class="content">
            <div class="member-container">

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
                            <input type="password" name="password" required>
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

                <table class="member-table">
    <tr>
        <th>ID</th>
        <th>Username</th>
        <th>Role</th>
        <th>Aksi</th>
    </tr>

    <?php while($m = mysqli_fetch_assoc($q_member)) { ?>
    <tr>
        <td><?= $m['id'] ?></td>
        <td><?= $m['username'] ?></td>
        <td>
            <?php if ($m['role'] == 'admin') { ?>
                <span class="role-admin">Admin</span>
            <?php } else { ?>
                <span class="role-member">Member</span>
            <?php } ?>
        </td>

        <td>
            <?php if ($m['id'] != $_SESSION['id']) { ?>
                <a href="delete_member.php?id=<?= $m['id'] ?>" 
                   class="btn-delete"
                   onclick="return confirm('Yakin ingin menghapus user ini?')">
                   Hapus
                </a>
            <?php } else { ?>
                <span style="color:gray;">Tidak bisa hapus diri sendiri</span>
            <?php } ?>
        </td>
    </tr>
    <?php } ?>
</table>


            </div>
        </div>

    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
