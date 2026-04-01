<?php
require_once '../../config/database.php';
require_once '../../includes/auth_check.php';
require_once '../../includes/role_check.php';
require_once '../../config/app.php';

header('Content-Type: text/html; charset=UTF-8');

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo '<div style="color:#991b1b;">ID member tidak valid.</div>';
    exit;
}

$id = $_GET['id'];
$q = mysqli_query($conn, "SELECT * FROM users WHERE id='$id'");
$data = mysqli_fetch_assoc($q);

if (!$data) {
    http_response_code(404);
    echo '<div style="color:#991b1b;">Data member tidak ditemukan.</div>';
    exit;
}
?>

<p class="member-edit-modal-subtitle">Ubah data akun member</p>

<form action="<?= BASE_URL ?>process/member_process.php" method="POST" data-confirm-action="edit-member">
    <input type="hidden" name="id" value="<?= $data['id'] ?>">

    <div class="form-group">
        <label>Username</label>
        <input type="text" name="username" value="<?= htmlspecialchars($data['username']) ?>" required>
    </div>

    <div class="form-group">
        <label>Password Baru</label>
        <div class="password-wrapper member-edit-password-wrap">
            <input type="password" name="password" placeholder="Password baru">
            <span class="toggle-password" onclick="togglePassword(this)">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="gray" viewBox="0 0 24 24">
                    <path d="M12 5c-7 0-10 7-10 7s3 7 10 7 10-7 10-7-3-7-10-7zm0 12a5 5 0 1 1 0-10 5 5 0 0 1 0 10zm0-8a3 3 0 1 0 0 6 3 3 0 0 0 0-6z"/>
                </svg>
            </span>
        </div>
        <small class="member-edit-note">Kosongkan jika tidak ingin mengubah password</small>
    </div>

    <div class="form-group">
        <label>Role</label>
        <select name="role" required>
            <option value="member" <?= $data['role']=='member'?'selected':'' ?>>Member</option>
            <option value="admin" <?= $data['role']=='admin'?'selected':'' ?>>Admin</option>
        </select>
    </div>

    <div class="member-edit-actions">
        <button type="submit" name="update" class="btn-save member-edit-submit">Update</button>
        <button type="button" class="btn-cancel-modal member-edit-cancel" data-close-edit-member>Tutup</button>
    </div>
</form>

