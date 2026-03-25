<?php require_once __DIR__ . '/../config/app.php'; ?>
<div class="footer">
    © <?= date('Y') ?> Disperindag
</div>

<div id="globalConfirmModal" class="confirm-modal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="confirmModalTitle">
    <div class="confirm-modal-backdrop" data-confirm-dismiss></div>
    <div class="confirm-modal-box">
        <h3 id="confirmModalTitle" class="confirm-modal-title"></h3>
        <p id="confirmModalMessage" class="confirm-modal-message"></p>
        <div class="confirm-modal-actions">
            <button type="button" id="confirmModalBtnCancel" class="btn-cancel-modal">Batal</button>
            <button type="button" id="confirmModalBtnOk" class="btn-save">Ya</button>
        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>assets/js/dashboard.js"></script>
<script src="<?= BASE_URL ?>assets/js/main.js"></script>
<script src="<?= BASE_URL ?>assets/js/confirm.js"></script>
<script src="<?= BASE_URL ?>assets/js/sidebar.js"></script>

</body>
</html>
