document.addEventListener("DOMContentLoaded", function () {
  var modalImport = document.getElementById("modalImport");
  var modalTambah = document.getElementById("modalTambah");
  var modalEdit = document.getElementById("modalEdit");
  var btnImportPopup = document.getElementById("btnImportPopup");
  var popup = document.getElementById("rowActionPopup");
  var tableScroll = document.querySelector(".list-table-scroll");
  var currentRow = null;
  var popupHideTimer = null;

  function openModal(modal) {
    if (!modal) return;
    modal.classList.add("show");
    modal.setAttribute("aria-hidden", "false");
  }

  function closeModal(modal) {
    if (!modal) return;
    modal.classList.remove("show");
    modal.setAttribute("aria-hidden", "true");
  }

  function closeAllModals() {
    document.querySelectorAll(".list-modal").forEach(function (modal) {
      closeModal(modal);
    });
  }

  function clearPopupHideTimer() {
    if (popupHideTimer) {
      window.clearTimeout(popupHideTimer);
      popupHideTimer = null;
    }
  }

  function hidePopup() {
    clearPopupHideTimer();
    if (!popup) return;
    popup.classList.remove("show");
    popup.setAttribute("aria-hidden", "true");
    currentRow = null;
  }

  function scheduleHidePopup() {
    clearPopupHideTimer();
    popupHideTimer = window.setTimeout(hidePopup, 120);
  }

  function positionPopup(row) {
    if (!popup || !row) return;
    var rect = row.getBoundingClientRect();
    var top = rect.top + 12;
    var left = rect.left + 96;

    popup.style.top = top + "px";
    popup.style.left = left + "px";
  }

  function showPopup(row) {
    if (!popup || !row) return;
    clearPopupHideTimer();
    currentRow = row;
    positionPopup(row);
    popup.classList.add("show");
    popup.setAttribute("aria-hidden", "false");
  }

  function setTambahValues(row) {
    var bahanSelect = document.getElementById("addBahanSelect");
    var tanggalInput = document.getElementById("addTanggal");
    var hargaInput = document.getElementById("addHarga");

    if (!row) return;
    if (bahanSelect) {
      bahanSelect.value = row.getAttribute("data-bahan-id") || "";
    }
    if (tanggalInput) {
      tanggalInput.value = row.getAttribute("data-latest-tanggal") || tanggalInput.value;
    }
    if (hargaInput) {
      hargaInput.value = "";
    }
  }

  function setEditValues(row) {
    var editId = document.getElementById("editId");
    var editBahanNama = document.getElementById("editBahanNama");
    var editHarga = document.getElementById("editHarga");
    var editTanggal = document.getElementById("editTanggal");

    if (!row) return false;
    var latestId = row.getAttribute("data-latest-id") || "";
    if (!latestId) {
      return false;
    }

    if (editId) editId.value = latestId;
    if (editBahanNama) editBahanNama.value = row.getAttribute("data-bahan-nama") || "";
    if (editHarga) editHarga.value = row.getAttribute("data-latest-harga") || "";
    if (editTanggal) editTanggal.value = row.getAttribute("data-latest-tanggal") || "";

    return true;
  }

  function toggleCommodityGroup(trigger) {
    if (!trigger) return;
    var group = trigger.closest("[data-commodity-group]");
    if (!group) return;

    var expanded = group.classList.contains("is-expanded");
    group.classList.toggle("is-expanded", !expanded);
    group.classList.toggle("is-collapsed", expanded);
    trigger.setAttribute("aria-expanded", expanded ? "false" : "true");

    group.querySelectorAll("[data-metric-row]").forEach(function (row) {
      row.setAttribute("aria-hidden", expanded ? "true" : "false");
    });
  }

  if (btnImportPopup && modalImport) {
    btnImportPopup.addEventListener("click", function () {
      openModal(modalImport);
    });
  }

  document.querySelectorAll(".btn-cancel-modal").forEach(function (button) {
    button.addEventListener("click", function () {
      closeAllModals();
    });
  });

  document.querySelectorAll(".list-modal-backdrop").forEach(function (backdrop) {
    backdrop.addEventListener("click", function () {
      closeAllModals();
    });
  });

  document.querySelectorAll(".list-commodity-toggle").forEach(function (button) {
    button.addEventListener("click", function () {
      toggleCommodityGroup(button);
    });
  });

  document.querySelectorAll(".list-data-row").forEach(function (row) {
    row.addEventListener("mouseenter", function () {
      showPopup(row);
    });
    row.addEventListener("mouseleave", function () {
      scheduleHidePopup();
    });
  });

  if (popup) {
    popup.addEventListener("mouseenter", function () {
      clearPopupHideTimer();
    });

    popup.addEventListener("mouseleave", function () {
      scheduleHidePopup();
    });

    popup.addEventListener("click", function (event) {
      var button = event.target.closest(".row-action-btn");
      if (!button || !currentRow) return;

      var action = button.getAttribute("data-action");
      if (action === "tambah") {
        setTambahValues(currentRow);
        openModal(modalTambah);
      } else if (action === "edit") {
        if (setEditValues(currentRow)) {
          openModal(modalEdit);
        }
      }
      hidePopup();
    });
  }

  if (tableScroll) {
    tableScroll.addEventListener("scroll", function () {
      if (currentRow && popup && popup.classList.contains("show")) {
        positionPopup(currentRow);
      }
    });
  }

  window.addEventListener("scroll", function () {
    if (currentRow && popup && popup.classList.contains("show")) {
      positionPopup(currentRow);
    }
  });

  document.addEventListener("click", function (event) {
    if (popup && popup.classList.contains("show") && !event.target.closest(".row-action-popup") && !event.target.closest(".list-data-row")) {
      hidePopup();
    }
  });

  document.addEventListener("keydown", function (event) {
    if (event.key === "Escape") {
      closeAllModals();
      hidePopup();
    }
  });
});
