(function () {
  var modal;
  var titleEl;
  var messageEl;
  var btnOk;
  var btnCancel;
  var backdrop;
  var resolveFn;
  var inited = false;

  function getEls() {
    if (!modal) {
      modal = document.getElementById("globalConfirmModal");
      if (!modal) return null;
      titleEl = document.getElementById("confirmModalTitle");
      messageEl = document.getElementById("confirmModalMessage");
      btnOk = document.getElementById("confirmModalBtnOk");
      btnCancel = document.getElementById("confirmModalBtnCancel");
      backdrop = modal.querySelector("[data-confirm-dismiss]");
    }
    return modal;
  }

  function closeModal(result) {
    var m = getEls();
    if (!m) return;
    m.classList.remove("show");
    m.setAttribute("aria-hidden", "true");
    document.body.style.overflow = "";
    var fn = resolveFn;
    resolveFn = null;
    if (fn) fn(!!result);
  }

  function openConfirmModal(options) {
    return new Promise(function (resolve) {
      var m = getEls();
      if (!m || !titleEl || !messageEl || !btnOk || !btnCancel) {
        resolve(false);
        return;
      }
      resolveFn = resolve;
      var opts = options || {};
      titleEl.textContent = opts.title || "Konfirmasi";
      messageEl.textContent = opts.message || "";
      btnOk.textContent = opts.confirmText || "Ya";
      btnCancel.textContent = opts.cancelText || "Batal";

      var danger = opts.danger === true;
      btnOk.classList.remove("btn-save", "btn-confirm-danger");
      if (danger) {
        btnOk.classList.add("btn-confirm-danger");
      } else {
        btnOk.classList.add("btn-save");
      }

      m.classList.add("show");
      m.setAttribute("aria-hidden", "false");
      document.body.style.overflow = "hidden";

      setTimeout(function () {
        btnOk.focus();
      }, 50);
    });
  }

  function onKeyDown(e) {
    if (!modal || !modal.classList.contains("show")) return;
    if (e.key === "Escape") {
      e.preventDefault();
      closeModal(false);
    }
  }

  function onDocumentClickCapture(e) {
    var a = e.target.closest("a[data-confirm]");
    if (!a) return;
    e.preventDefault();
    e.stopPropagation();
    var url = a.getAttribute("href");
    if (!url || url === "#") return;
    var danger =
      a.getAttribute("data-confirm-danger") === "1" ||
      a.getAttribute("data-confirm-danger") === "true";
    openConfirmModal({
      title: a.getAttribute("data-confirm-title") || "Konfirmasi",
      message:
        a.getAttribute("data-confirm-message") ||
        "Lanjutkan tindakan ini?",
      confirmText: a.getAttribute("data-confirm-ok") || "Ya",
      cancelText: a.getAttribute("data-confirm-cancel") || "Batal",
      danger: danger,
    }).then(function (ok) {
      if (ok) window.location.href = url;
    });
  }

  function init() {
    if (inited) return;
    getEls();
    if (!btnOk || !btnCancel) return;
    inited = true;

    btnOk.addEventListener("click", function () {
      closeModal(true);
    });
    btnCancel.addEventListener("click", function () {
      closeModal(false);
    });
    if (backdrop) {
      backdrop.addEventListener("click", function () {
        closeModal(false);
      });
    }
    document.addEventListener("keydown", onKeyDown);
    document.addEventListener("click", onDocumentClickCapture, true);
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }

  window.openConfirmModal = openConfirmModal;
})();
