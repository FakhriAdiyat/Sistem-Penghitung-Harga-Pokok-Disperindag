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
            <div class="container">

                <h1>Edit Member</h1>
                <p class="subtitle">Ubah data akun member</p>

                <div class="form-box">

                    <form action="<?= BASE_URL ?>process/member_process.php" method="POST" data-confirm-action="edit-member">

                        <input type="hidden" name="id" value="<?= $data['id'] ?>">

                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" name="username" 
                                   value="<?= htmlspecialchars($data['username']) ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Password Baru</label>
                            <input type="password" name="password">
                            <small style="color:gray;">Kosongkan jika tidak ingin mengubah password</small>
                        </div>

                        <div class="form-group">
                            <label>Role</label>
                            <select name="role" required>
                                <option value="member" <?= $data['role']=='member'?'selected':'' ?>>Member</option>
                                <option value="admin" <?= $data['role']=='admin'?'selected':'' ?>>Admin</option>
                            </select>
                        </div>

                        <button type="submit" name="update" class="btn-save">Update</button>
                        <a href="member.php" class="btn-cancel">Kembali</a>

                    </form>

                </div>

            </div>
        </div>

    </div>
</div>

<?php require_once '../includes/footer.php'; ?>