function closePopup() {
  const popup = document.querySelector(".popup-overlay");
  if (popup) {
    popup.style.opacity = "0";
    setTimeout(() => {
      popup.style.display = "none";
    }, 300);

    try {
      const url = new URL(window.location.href);
      url.searchParams.delete("error");
      window.history.replaceState({}, document.title, url.pathname + url.search + url.hash);
    } catch (e) {
      // ignore
    }
  }
}

function ensureConfirmPopup() {
  let overlay = document.getElementById("confirmPopupOverlay");
  if (overlay) return overlay;

  overlay = document.createElement("div");
  overlay.id = "confirmPopupOverlay";
  overlay.style.cssText = [
    "position:fixed",
    "inset:0",
    "background:rgba(0,0,0,0.45)",
    "display:none",
    "align-items:center",
    "justify-content:center",
    "z-index:9999",
    "padding:16px",
  ].join(";");

  overlay.innerHTML = `
    <div style="background:#fff; width:100%; max-width:420px; border-radius:12px; padding:20px; box-shadow:0 12px 28px rgba(0,0,0,.2);">
      <h3 style="margin:0 0 8px; font-size:18px;">Konfirmasi</h3>
      <p id="confirmPopupMessage" style="margin:0 0 18px; color:#444; line-height:1.5;"></p>
      <div style="display:flex; gap:10px; justify-content:flex-end;">
        <button type="button" id="confirmPopupCancel" style="padding:9px 14px; border:1px solid #cfcfcf; background:#fff; border-radius:8px; cursor:pointer;">Batal</button>
        <button type="button" id="confirmPopupOk" style="padding:9px 14px; border:0; background:#d33; color:#fff; border-radius:8px; cursor:pointer;">Ya, Lanjutkan</button>
      </div>
    </div>
  `;

  document.body.appendChild(overlay);
  return overlay;
}

function openConfirmPopup(message, onConfirm) {
  const overlay = ensureConfirmPopup();
  const msg = document.getElementById("confirmPopupMessage");
  const btnOk = document.getElementById("confirmPopupOk");
  const btnCancel = document.getElementById("confirmPopupCancel");

  if (!overlay || !msg || !btnOk || !btnCancel) return;

  msg.textContent = message;
  overlay.style.display = "flex";

  const close = () => {
    overlay.style.display = "none";
    btnOk.removeEventListener("click", handleOk);
    btnCancel.removeEventListener("click", handleCancel);
    overlay.removeEventListener("click", handleOverlay);
  };

  const handleOk = () => {
    close();
    if (typeof onConfirm === "function") onConfirm();
  };

  const handleCancel = () => close();
  const handleOverlay = (event) => {
    if (event.target === overlay) close();
  };

  btnOk.addEventListener("click", handleOk);
  btnCancel.addEventListener("click", handleCancel);
  overlay.addEventListener("click", handleOverlay);
}

function showSessionTimeoutToast() {
  const toast = document.getElementById("toastTimeout");
  if (!toast) return;
  setTimeout(() => toast.classList.add("hide"), 4000);
}

function showResultPopup(message) {
  if (!message) return;

  const overlay = document.createElement("div");
  overlay.style.cssText = [
    "position:fixed",
    "inset:0",
    "background:rgba(0,0,0,0.35)",
    "display:flex",
    "align-items:center",
    "justify-content:center",
    "z-index:10000",
    "padding:16px",
  ].join(";");

  overlay.innerHTML = `
    <div style="background:#fff; width:100%; max-width:380px; border-radius:14px; padding:18px; text-align:center; box-shadow:0 14px 30px rgba(0,0,0,.2);">
      <div style="width:56px; height:56px; border-radius:999px; margin:0 auto 10px; background:rgba(22,163,74,.12); border:2px solid rgba(22,163,74,.45); position:relative;">
        <span style="position:absolute; top:27px; left:17px; width:10px; height:5px; border-left:4px solid #16a34a; border-bottom:4px solid #16a34a; transform:rotate(-45deg);"></span>
      </div>
      <h3 style="margin:0 0 6px; color:#111827;">Berhasil</h3>
      <p style="margin:0 0 14px; color:#374151;">${message}</p>
      <button type="button" id="resultPopupClose" style="border:none; background:linear-gradient(to right, #8ee05a, #c8e96b); color:#111; font-weight:700; padding:10px 16px; border-radius:10px; cursor:pointer;">Tutup</button>
    </div>
  `;

  const close = () => overlay.remove();
  overlay.addEventListener("click", (e) => {
    if (e.target === overlay) close();
  });

  document.body.appendChild(overlay);
  const btn = document.getElementById("resultPopupClose");
  if (btn) btn.addEventListener("click", close);
  setTimeout(close, 2600);
}

function openEditMemberModal() {
  const modal = document.getElementById("editMemberModal");
  if (!modal) return;
  modal.classList.add("show");
  modal.setAttribute("aria-hidden", "false");
}

function closeEditMemberModal() {
  const modal = document.getElementById("editMemberModal");
  if (!modal) return;
  modal.classList.remove("show");
  modal.setAttribute("aria-hidden", "true");
}

async function loadEditMemberForm(url) {
  const body = document.getElementById("editMemberModalBody");
  if (!body) return;

  body.innerHTML = `<div class="member-edit-loading">Memuat form...</div>`;

  try {
    const res = await fetch(url, { credentials: "same-origin" });
    const html = await res.text();
    body.innerHTML = html;

    body.querySelectorAll("[data-close-edit-member]").forEach((btn) => {
      btn.addEventListener("click", closeEditMemberModal);
    });
  } catch (e) {
    body.innerHTML = `<div class="member-edit-loading" style="color:#991b1b;">Gagal memuat form.</div>`;
  }
}

document.addEventListener("DOMContentLoaded", function () {
  const popup = document.querySelector(".popup-overlay");
  if (popup) {
    try {
      const url = new URL(window.location.href);
      url.searchParams.delete("error");
      window.history.replaceState({}, document.title, url.pathname + url.search + url.hash);
    } catch (e) {
      // ignore
    }

    setTimeout(() => {
      popup.style.opacity = "0";
      setTimeout(() => {
        popup.style.display = "none";
      }, 300);
    }, 3000);
  }

  showSessionTimeoutToast();

  const flashEl = document.getElementById("flashPopupData");
  if (flashEl) {
    const message = flashEl.getAttribute("data-popup-message");
    showResultPopup(message);

    try {
      const url = new URL(window.location.href);
      url.searchParams.delete("success");
      window.history.replaceState({}, document.title, url.pathname + url.search + url.hash);
    } catch (e) {
      // ignore
    }
  }

  document.querySelectorAll("a[data-confirm-action]").forEach((el) => {
    el.addEventListener("click", function (event) {
      const action = el.getAttribute("data-confirm-action");
      if (!action) return;

      event.preventDefault();

      let message = "Apakah Anda yakin ingin melanjutkan aksi ini?";
      if (action === "logout") {
        message = "Apakah Anda yakin ingin logout?";
      } else if (action === "delete-member") {
        message = "Yakin ingin menghapus user ini?";
      }

      openConfirmPopup(message, () => {
        const href = el.getAttribute("href");
        if (href) window.location.href = href;
      });
    });
  });

  document.querySelectorAll("form[data-confirm-action]").forEach((form) => {
    form.addEventListener("submit", function (event) {
      const action = form.getAttribute("data-confirm-action");
      if (!action || form.dataset.confirmed === "true") return;

      event.preventDefault();

      let message = "Apakah Anda yakin ingin melanjutkan aksi ini?";
      if (action === "edit-member") {
        message = "Simpan perubahan data member ini?";
      }

      openConfirmPopup(message, () => {
        form.dataset.confirmed = "true";
        form.submit();
      });
    });
  });

  document.querySelectorAll("[data-open-edit-member]").forEach((btn) => {
    btn.addEventListener("click", async function (event) {
      const url = btn.getAttribute("data-edit-url");
      if (!url) return;

      event.preventDefault();
      openEditMemberModal();
      await loadEditMemberForm(url);
    });
  });

  document.querySelectorAll("[data-close-edit-member]").forEach((btn) => {
    btn.addEventListener("click", function () {
      closeEditMemberModal();
    });
  });

document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") closeEditMemberModal();
  });
});

// Laporan Format Popup
function showFormatPopup(date) {
  const overlay = document.createElement("div");
  overlay.id = "formatPopupOverlay";
  overlay.className = "format-popup-overlay";
  overlay.style.cssText = "position:fixed;inset:0;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;z-index:10001;padding:16px;";

  overlay.innerHTML = `
    <div class="format-popup-card">
      <h3 style="margin:0 0 8px;font-size:20px;color:#111827;">Pilih Format Laporan</h3>
      <p style="margin:0 0 24px;color:#64748b;font-size:14px;">Tanggal: <strong>${date}</strong></p>
      <div class="format-actions" style="display:flex;flex-direction:column;gap:12px;">
        <a href="laporan_pdf.php?tanggal_awal=${encodeURIComponent(date)}&tanggal_akhir=${encodeURIComponent(date)}" class="format-pdf-btn theme-primary-btn" style="text-decoration:none;">📄 Download PDF</a>
        <a href="laporan_excel.php?tanggal_laporan=${encodeURIComponent(date)}" class="format-excel-btn theme-primary-btn" style="text-decoration:none;background:linear-gradient(135deg,#dbeafe,#bfdbfe);color:#1d4ed8;">📊 Download Excel</a>
        <button type="button" id="formatCancel" class="format-cancel-btn" style="border:1px solid #cbd5e1;background:#fff;color:#475569;padding:12px;border-radius:12px;cursor:pointer;font-weight:600;">Batal</button>
      </div>
    </div>
  `;

  const close = () => overlay.remove();
  overlay.addEventListener("click", (e) => { if (e.target === overlay) close(); });
  document.body.appendChild(overlay);

  document.getElementById("formatCancel").addEventListener("click", close);
}

// Auto-trigger from laporan page - integrated into existing DOMContentLoaded


