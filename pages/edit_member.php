<?php
require_once '../config/database.php';
require_once '../includes/auth_check.php';
require_once '../includes/role_check.php';

// Ambil ID dari URL
if (!isset($_GET['id'])) {
    header("Location: member.php");
    exit;
}

$id = $_GET['id'];

// Ambil data user
$q = mysqli_query($conn, "SELECT * FROM users WHERE id='$id'");
$data = mysqli_fetch_assoc($q);

// Kalau user tidak ditemukan
if (!$data) {
    header("Location: member.php");
    exit;
}
?>

<div class="layout">

    <!-- SIDEBAR -->
    <?php require_once '../includes/sidebar.php'; ?>

    <!-- WRAPPER -->
    <div style="flex:1; display:flex; flex-direction:column;">

        <!-- HEADER -->
        <?php require_once '../includes/header.php'; ?>

        <!-- CONTENT -->
        <div class="content">
            <div class="container theme-page-shell">
                <div class="theme-page-header">
                    <div class="theme-page-title">
                        <h1>Edit Member</h1>
                        <p class="subtitle">Perbarui data akun dengan tampilan yang seragam bersama halaman lainnya.</p>
                    </div>
                    <div class="theme-page-badge">
                        <span>User</span>
                        <strong><?= htmlspecialchars($data['username']) ?></strong>
                    </div>
                </div>

                <div class="theme-panel" style="max-width:720px;">
                    <div class="theme-section-head">
                        <div>
                            <h2>Form Edit Member</h2>
                            <p>Ubah username, password, atau role sesuai kebutuhan.</p>
                        </div>
                    </div>

                    <form action="<?= BASE_URL ?>process/member_process.php" method="POST" data-confirm-action="edit-member" class="theme-form-grid theme-form-grid-single" autocomplete="off">
                        <input type="hidden" name="id" value="<?= $data['id'] ?>">

                        <label class="theme-field">
                            <span>Username</span>
                            <input type="text" name="username" value="<?= htmlspecialchars($data['username']) ?>" required>
                        </label>

                        <div class="theme-field">
                            <span>Password Baru</span>
                            <input type="password" name="password" placeholder="Kosongkan jika tidak ingin mengubah password" autocomplete="new-password" readonly onfocus="this.removeAttribute('readonly');">
                        </div>

                        <label class="theme-field">
                            <span>Role</span>
                            <select name="role" required>
                                <option value="member" <?= $data['role']=='member'?'selected':'' ?>>Member</option>
                                <option value="admin" <?= $data['role']=='admin'?'selected':'' ?>>Admin</option>
                            </select>
                        </label>

                        <div class="theme-action-row" style="margin-top:0;">
                            <button type="submit" name="update" class="theme-primary-btn">Update</button>
                            <a href="member.php" class="theme-secondary-btn">Kembali</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
