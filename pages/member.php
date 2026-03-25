<?php
require_once '../config/database.php';
require_once '../includes/auth_check.php';
require_once '../includes/role_check.php';

// Ambil semua member
$q_member = mysqli_query($conn, "SELECT * FROM users ORDER BY id DESC");

// total user
    $total_user = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM users"));

$member_popup = null;
if (isset($_GET['success'])) {
    if ($_GET['success'] === 'tambah') {
        $member_popup = ['type' => 'success', 'title' => 'Berhasil', 'message' => 'Member berhasil ditambahkan.'];
    } elseif ($_GET['success'] === 'hapus') {
        $member_popup = ['type' => 'success', 'title' => 'Berhasil', 'message' => 'Member berhasil dihapus.'];
    }
}
if (isset($_GET['error']) && $_GET['error'] === 'username') {
    $member_popup = ['type' => 'error', 'title' => 'Gagal', 'message' => 'Username sudah digunakan.'];
}
?>



<div class="layout">

    <!-- SIDEBAR -->
    <?php require_once '../includes/sidebar.php'; ?>

    <!-- WRAPPER HEADER + CONTENT -->
    <div style="flex:1; display:flex; flex-direction:column;">

        <!-- HEADER -->
        <?php require_once '../includes/header.php'; ?>

        <!-- CONTENT (INI HTML KAMU ASLI) -->
        <div class="content">
            <div class="container">

                <h1>Manajemen Member</h1>
                <p class="subtitle">Kelola akun member sistem</p>

                <?php if ($member_popup): ?>
                    <?php $ptype = $member_popup['type'] === 'error' ? 'error' : 'success'; ?>
                    <div class="popup-overlay" role="alert" aria-live="assertive">
                        <div class="popup-card <?= $ptype === 'success' ? 'popup-card-success' : 'popup-card-error' ?>" role="document">
                            <div class="popup-icon <?= $ptype === 'success' ? 'popup-icon-success' : 'popup-icon-error' ?>" aria-hidden="true">
                                <?php if ($ptype === 'success'): ?>
                                    <span class="popup-check popup-check-short"></span>
                                    <span class="popup-check popup-check-long"></span>
                                <?php else: ?>
                                    <span class="popup-x popup-x-left"></span>
                                    <span class="popup-x popup-x-right"></span>
                                <?php endif; ?>
                            </div>
                            <div class="popup-title"><?= htmlspecialchars($member_popup['title']) ?></div>
                            <div class="popup-message"><?= htmlspecialchars($member_popup['message']) ?></div>
                            <button type="button" class="popup-close" onclick="closePopup()">Tutup</button>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- FORM TAMBAH MEMBER -->
                <div class="form-box">
                    <h3>Tambah Member</h3>

                    <form action="<?= BASE_URL ?>process/member_process.php" method="POST">

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
                <a href="<?= BASE_URL ?>process/delete_member.php?id=<?= $m['id'] ?>"
                   class="btn-delete"
                   data-confirm="1"
                   data-confirm-title="Hapus member"
                   data-confirm-message="Yakin ingin menghapus user ini? Tindakan ini tidak dapat dibatalkan."
                   data-confirm-ok="Hapus"
                   data-confirm-danger="1">
                   Hapus
                </a>
            <?php } else { ?>
                <span style="color:gray;">Tidak bisa hapus diri sendiri</span>
            <?php } ?>
        </td>
    </tr>
    <?php $no++; } ?>
</table>


            </div>
        </div>

    </div>
</div>

<script src="<?= BASE_URL ?>assets/js/popup.js"></script>
<?php require_once '../includes/footer.php'; ?>
