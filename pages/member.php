<?php
require_once '../config/database.php';
require_once '../includes/auth_check.php';
require_once '../includes/role_check.php';

// Ambil semua member
$q_member = mysqli_query($conn, "SELECT * FROM users ORDER BY id DESC");

// total user
    $total_user = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM users"));

$success = $_GET['success'] ?? '';
$successMessage = '';
if ($success === 'tambah') {
    $successMessage = 'Member berhasil ditambahkan.';
} elseif ($success === 'hapus') {
    $successMessage = 'Member berhasil dihapus.';
} elseif ($success === 'edit') {
    $successMessage = 'Member berhasil diperbarui.';
}
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
            <div class="container theme-page-shell">
                <?php if ($successMessage !== ''): ?>
                    <div id="flashPopupData" data-popup-message="<?= htmlspecialchars($successMessage) ?>"></div>
                <?php endif; ?>

                <div class="theme-page-header">
                    <div class="theme-page-title">
                        <h1>Manajemen Member</h1>
                        <p class="subtitle">Kelola akun pengguna dengan tema yang sama seperti halaman List Data.</p>
                    </div>
                    <div class="theme-page-badge">
                        <span>Total User</span>
                        <strong><?= $total_user ?> akun</strong>
                    </div>
                </div>

                <div class="theme-layout-grid">
                    <div class="theme-panel">
                        <div class="theme-section-head">
                            <div>
                                <h2>Tambah Member</h2>
                                <p>Buat akun admin atau member baru untuk sistem.</p>
                            </div>
                        </div>

                        <form action="../process/member_process.php" method="POST" class="theme-form-grid theme-form-grid-single" autocomplete="off" data-clear-autofill>
                            <input type="text" name="fake_username" autocomplete="username" tabindex="-1" aria-hidden="true" style="position:absolute;left:-9999px;top:auto;width:1px;height:1px;opacity:0;">
                            <input type="password" name="fake_password" autocomplete="current-password" tabindex="-1" aria-hidden="true" style="position:absolute;left:-9999px;top:auto;width:1px;height:1px;opacity:0;">
                            <label class="theme-field">
                                <span>Username</span>
                                <input type="text" name="username" required autocomplete="off" autocapitalize="off" spellcheck="false" readonly onfocus="this.removeAttribute('readonly');" data-autofill-field>
                            </label>

                            <div class="theme-field">
                                <span>Password</span>
                                <div class="password-wrapper">
                                    <input type="password" name="password" id="passwordInput" placeholder="Password" required autocomplete="new-password" readonly onfocus="this.removeAttribute('readonly');" data-autofill-field>
                                    <span class="toggle-password" onclick="togglePassword()">
                                        <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="gray" viewBox="0 0 24 24">
                                            <path d="M12 5c-7 0-10 7-10 7s3 7 10 7 10-7 10-7-3-7-10-7zm0 12a5 5 0 1 1 0-10 5 5 0 0 1 0 10zm0-8a3 3 0 1 0 0 6 3 3 0 0 0 0-6z"/>
                                        </svg>
                                    </span>
                                </div>
                            </div>

                            <label class="theme-field">
                                <span>Role</span>
                                <select name="role" required>
                                    <option value="member">Member</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </label>

                            <button type="submit" class="theme-primary-btn">Simpan</button>
                        </form>
                    </div>

                    <div>
                        <div class="theme-stat-grid theme-stat-grid-single" style="margin-top:0;">
                            <article class="theme-stat-card is-stable">
                                <span>User Aktif</span>
                                <strong><?= $total_user ?></strong>
                                <p>Total akun yang terdaftar di sistem.</p>
                            </article>
                        </div>

                        <div class="theme-table-card">
                            <div class="theme-section-head" style="padding:18px 18px 0;">
                                <div>
                                    <h2>Daftar Member</h2>
                                    <p>Daftar akun yang bisa mengakses aplikasi saat ini.</p>
                                </div>
                            </div>
                            <div class="theme-table-scroll">
                                <table class="member-table theme-table">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Username</th>
                                            <th>Role</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php $no = 1; while($m = mysqli_fetch_assoc($q_member)) { ?>
                                        <tr>
                                            <td><?= $no ?></td>
                                            <td><?= htmlspecialchars($m['username']) ?></td>
                                            <td>
                                                <?php if ($m['role'] == 'admin') { ?>
                                                    <span class="role-admin theme-pill is-admin">Admin</span>
                                                <?php } else { ?>
                                                    <span class="role-member theme-pill is-member">Member</span>
                                                <?php } ?>
                                            </td>
                                            <td class="member-action-cell">
                                                <?php if ($m['id'] == $_SESSION['id']) { ?>
                                                    <div class="member-action-group">
                                                        <a href="edit_member.php?id=<?= $m['id'] ?>" class="btn-edit" data-open-edit-member data-edit-url="<?= BASE_URL ?>pages/partials/edit_member_form.php?id=<?= $m['id'] ?>">
                                                            Edit
                                                        </a>
                                                    </div>
                                                <?php } else { ?>
                                                    <div class="member-action-group">
                                                        <a href="edit_member.php?id=<?= $m['id'] ?>" class="btn-edit" data-open-edit-member data-edit-url="<?= BASE_URL ?>pages/partials/edit_member_form.php?id=<?= $m['id'] ?>">
                                                            Edit
                                                        </a>
                                                        <a href="<?= BASE_URL ?>process/delete_member.php?id=<?= $m['id'] ?>" class="btn-delete" data-confirm-action="delete-member">
                                                            Hapus
                                                        </a>
                                                    </div>
                                                <?php } ?>
                                            </td>
                                        </tr>
                                    <?php $no++; } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

<!-- MODAL EDIT MEMBER -->
<div id="editMemberModal" class="list-modal" aria-hidden="true">
    <div class="list-modal-backdrop" data-close-edit-member></div>
    <div class="list-modal-box member-edit-modal-box" role="dialog" aria-modal="true" aria-labelledby="editMemberModalTitle">
        <h3 id="editMemberModalTitle" class="member-edit-modal-title">Edit Member</h3>
        <div id="editMemberModalBody">
            <div class="member-edit-loading">Memuat form...</div>
        </div>
    </div>
</div>



            </div>
        </div>

    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
