document.addEventListener("DOMContentLoaded", function () {
  var modalImport = document.getElementById("modalImport");
  var modalExport = document.getElementById("modalExport");
  var modalTambah = document.getElementById("modalTambah");
  var modalEdit = document.getElementById("modalEdit");
  var btnImportPopup = document.getElementById("btnImportPopup");
  var btnExportPopup = document.getElementById("btnExportPopup");
  var exportTemplateForm = document.getElementById("exportTemplateForm");
  var popup = document.getElementById("rowActionPopup");
  var tableScroll = document.querySelector(".list-table-scroll");
  var popupEditButton = popup ? popup.querySelector('.row-action-edit') : null;
  var popupDeleteButton = popup ? popup.querySelector('.row-action-hapus') : null;
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
    syncPopupActions(row);
    popup.classList.add("show");
    popup.setAttribute("aria-hidden", "false");
  }

  function syncPopupActions(cell) {
    var hasRecord = !!(cell && cell.getAttribute("data-record-id"));

    if (popupEditButton) {
      popupEditButton.textContent = hasRecord ? "Edit Harga / HET" : "Tambah Harga / HET";
    }

    if (popupDeleteButton) {
      popupDeleteButton.disabled = !hasRecord;
      popupDeleteButton.setAttribute("aria-disabled", hasRecord ? "false" : "true");
      popupDeleteButton.title = hasRecord ? "" : "Belum ada data harga untuk dihapus";
    }
  }

  function submitDeleteRecord(recordId) {
    var form = document.createElement("form");
    form.method = "POST";
    form.action = "../process/list_process.php";

    var inputAction = document.createElement("input");
    inputAction.type = "hidden";
    inputAction.name = "action";
    inputAction.value = "hapus";

    var inputId = document.createElement("input");
    inputId.type = "hidden";
    inputId.name = "id";
    inputId.value = recordId;

    var inputReturn = document.createElement("input");
    inputReturn.type = "hidden";
    inputReturn.name = "return_query";
    inputReturn.value = new URLSearchParams(window.location.search).toString();

    form.appendChild(inputAction);
    form.appendChild(inputId);
    form.appendChild(inputReturn);
    document.body.appendChild(form);
    form.submit();
  }

  function formatTanggalLabel(value) {
    if (!value) return "";
    var date = new Date(value + "T00:00:00");
    if (Number.isNaN(date.getTime())) return value;

    return new Intl.DateTimeFormat("id-ID", {
      day: "2-digit",
      month: "long",
      year: "numeric"
    }).format(date);
  }

  function confirmDeleteRecord(cell) {
    if (!cell) return;

    var recordId = cell.getAttribute("data-record-id");
    if (!recordId) return;

    var bahanNama = cell.getAttribute("data-bahan-nama") || "komoditas ini";
    var tanggal = formatTanggalLabel(cell.getAttribute("data-record-tanggal"));
    var message = "Yakin ingin menghapus data harga " + bahanNama;
    if (tanggal) {
      message += " pada " + tanggal;
    }
    message += "? Semua data harga pada tanggal ini akan dikosongkan.";

    var doDelete = function () {
      submitDeleteRecord(recordId);
    };

    if (typeof openConfirmPopup === "function") {
      openConfirmPopup(message, doDelete);
      return;
    }

    if (window.confirm(message)) {
      doDelete();
    }
  }

  function setTambahValues(cell) {
    var bahanSelect = document.getElementById("addBahanSelect");
    var hetHapInput = document.getElementById("addHetHap");
    var tanggalInput = document.getElementById("addTanggal");
    var hargaInput = document.getElementById("addHarga");

    if (!cell) return;
    if (bahanSelect) {
      bahanSelect.value = cell.getAttribute("data-bahan-id") || "";
    }
    if (hetHapInput) {
      hetHapInput.value = cell.getAttribute("data-record-het-hap") || "";
    }
    if (tanggalInput) {
      tanggalInput.value = cell.getAttribute("data-record-tanggal") || new Date().toISOString().split('T')[0];
    }
    if (hargaInput) {
      hargaInput.value = "";
    }
  }

  function setEditValues(cell) {
    var editId = document.getElementById("editId");
    var editBahanNama = document.getElementById("editBahanNama");
    var editHarga = document.getElementById("editHarga");
    var editHetHap = document.getElementById("editHetHap");
    var editTanggal = document.getElementById("editTanggal");

    if (!cell) return false;
    var recordId = cell.getAttribute("data-record-id") || "";
    var commodityRow = cell.closest(".list-commodity-row");
    if (!recordId) {
      return false;
    }

    var bahanNama =
      cell.getAttribute("data-bahan-nama") ||
      (commodityRow ? commodityRow.getAttribute("data-bahan-nama") : "") ||
      cell.getAttribute("data-bahan-id") ||
      "";
    var recordHarga =
      cell.getAttribute("data-record-harga") ||
      (commodityRow ? commodityRow.getAttribute("data-latest-harga") : "") ||
      "";
    var recordHetHap =
      cell.getAttribute("data-record-het-hap") ||
      (commodityRow ? commodityRow.getAttribute("data-latest-het-hap") : "") ||
      "";
    var recordTanggal =
      cell.getAttribute("data-record-tanggal") ||
      (commodityRow ? commodityRow.getAttribute("data-latest-tanggal") : "") ||
      "";

    if (editId) editId.value = recordId;
    if (editBahanNama) editBahanNama.value = bahanNama;
    if (editHarga) editHarga.value = recordHarga;
    if (editHetHap) editHetHap.value = recordHetHap;
    if (editTanggal) editTanggal.value = recordTanggal;

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

  if (btnExportPopup && modalExport) {
    btnExportPopup.addEventListener("click", function () {
      openModal(modalExport);
    });
  }

  if (exportTemplateForm) {
    exportTemplateForm.addEventListener("submit", function () {
      closeModal(modalExport);
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

  // Tanggal header click to select for bulk delete
  document.querySelectorAll(".list-date-head").forEach(function (header) {
    header.style.cursor = "pointer";
    header.style.userSelect = "none";
    header.addEventListener("click", function () {
      var tanggal = this.getAttribute("data-tanggal");
      if (!tanggal) return;

      // Toggle selection
      this.classList.toggle("selected");
      
      // Collect all selected dates
      var selectedDates = [];
      document.querySelectorAll(".list-date-head.selected").forEach(function (selHeader) {
        selectedDates.push(selHeader.getAttribute("data-tanggal"));
      });

      // Update popup or prepare bulk delete
      if (selectedDates.length > 0) {
        console.log("Selected dates for delete:", selectedDates);
        // Could update popup to show "Hapus " + count + " tanggal"
      }
    });
  });

document.querySelectorAll(".list-metric-value.harga").forEach(function (cell) {
    cell.addEventListener("mouseenter", function () {
      showPopup(cell);
    });
    cell.addEventListener("mouseleave", function () {
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
      
      // Edit button - single cell edit
      if (action === "edit") {
        if (currentRow.getAttribute("data-record-id")) {
          if (setEditValues(currentRow)) {
            openModal(modalEdit);
          }
        } else {
          setTambahValues(currentRow);
          openModal(modalTambah);
        }
      } 
      // Hapus button - single cell delete
      else if (action === "hapus") {
        if (!button.disabled) {
          confirmDeleteRecord(currentRow);
        }
      }
      hidePopup();
    });
  }

  if (popupDeleteButton) {
    popupDeleteButton.addEventListener("click", function (event) {
      if (!popupDeleteButton.disabled) return;
      event.preventDefault();
      event.stopPropagation();
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
