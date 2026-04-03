document.addEventListener("DOMContentLoaded", function () {
  var wrap = document.querySelector(".list-table-wrap");
  var popup = document.getElementById("rowActionPopup");
  var modalTambah = document.getElementById("modalTambah");
  var modalEdit = document.getElementById("modalEdit");
  var supportsHover =
    window.matchMedia &&
    window.matchMedia("(hover: hover) and (pointer: fine)").matches;

  var checkAll = document.getElementById("checkAll");
  var btnHapusBanyak = document.getElementById("btnHapusBanyak");
  var modalHapusBanyak = document.getElementById("modalHapusBanyak");
  var modalImport = document.getElementById("modalImport");
  var modalExport = document.getElementById("modalExport");
  var btnImportPopup = document.getElementById("btnImportPopup");
  var btnExportPopup = document.getElementById("btnExportPopup");
  var hapusBanyakText = document.getElementById("hapusBanyakText");
  var confirmHapusBanyak = document.getElementById("confirmHapusBanyak");
  var formHapusBanyak = document.getElementById("formHapusBanyak");

  var currentRow = null;
  var popupRow = null;

  function allDataRows() {
    return document.querySelectorAll(".list-data-row");
  }

  function checkedRows() {
    return document.querySelectorAll(".row-check:checked");
  }

  function updateBulkDeleteButton() {
    if (!btnHapusBanyak) return;
    var hasSelection = checkedRows().length > 0;
    btnHapusBanyak.disabled = !hasSelection;
    btnHapusBanyak.classList.toggle("active", hasSelection);
  }

  function hidePopup() {
    if (popup) {
      popup.classList.remove("show");
      popup.setAttribute("aria-hidden", "true");
    }
    if (popupRow) {
      popupRow.remove();
      popupRow = null;
    }
    allDataRows().forEach(function (row) {
      row.classList.remove("selected");
    });
    currentRow = null;
  }

  function showPopupBelowRow(row) {
    if (!popup || !row || !row.parentNode) return;
    if (currentRow === row && popup.classList.contains("show")) return;

    hidePopup();
    currentRow = row;
    row.classList.add("selected");

    popupRow = document.createElement("tr");
    popupRow.className = "row-popup-row";

    var popupCell = document.createElement("td");
    popupCell.className = "row-popup-cell";
    popupCell.colSpan = row.children.length;

    popupCell.appendChild(popup);
    popupRow.appendChild(popupCell);
    row.parentNode.insertBefore(popupRow, row.nextSibling);

    popup.classList.add("show");
    popup.setAttribute("aria-hidden", "false");
  }

  if (wrap) {
    if (supportsHover) {
      wrap.addEventListener("mouseover", function (e) {
        var row = e.target.closest(".list-data-row");
        if (row) {
          showPopupBelowRow(row);
        }
      });

      wrap.addEventListener("mouseleave", function (e) {
        if (!e.relatedTarget || !wrap.contains(e.relatedTarget)) {
          hidePopup();
        }
      });
    } else {
      wrap.addEventListener("click", function (e) {
        var row = e.target.closest(".list-data-row");

        if (row) {
          e.preventDefault();
          if (currentRow === row && popup && popup.classList.contains("show")) {
            hidePopup();
            return;
          }
          showPopupBelowRow(row);
        } else if (!e.target.closest(".row-action-popup")) {
          hidePopup();
        }
      });
    }
  }

  document.addEventListener("click", function (e) {
    if (
      popup &&
      popup.classList.contains("show") &&
      !e.target.closest(".list-table-wrap")
    ) {
      hidePopup();
    }
  });

  function openModalTambah() {
    if (!modalTambah) return;
    var sel = modalTambah.querySelector('select[name="bahan_id"]');
    if (sel && currentRow) {
      var bid = currentRow.getAttribute("data-bahan-id");
      sel.value = bid || "";
    }
    modalTambah.classList.add("show");
    modalTambah.setAttribute("aria-hidden", "false");
    hidePopup();
  }

  function openModalEdit(row) {
    if (!row || !modalEdit) return;
    document.getElementById("editId").value = row.getAttribute("data-id");
    document.getElementById("editBahanNama").value =
      row.getAttribute("data-bahan-nama");
    document.getElementById("editHarga").value = row.getAttribute("data-harga");
    document.getElementById("editTanggal").value =
      row.getAttribute("data-tanggal");
    modalEdit.classList.add("show");
    modalEdit.setAttribute("aria-hidden", "false");
    hidePopup();
  }

  if (popup) {
    popup.addEventListener("click", function (e) {
      var btn = e.target.closest(".row-action-btn");
      if (!btn || !currentRow) return;
      var action = btn.getAttribute("data-action");
      if (action === "tambah") openModalTambah();
      else if (action === "edit") openModalEdit(currentRow);
    });
  }

  document.querySelectorAll(".btn-cancel-modal").forEach(function (btn) {
    btn.addEventListener("click", function () {
      document.querySelectorAll(".list-modal").forEach(function (modal) {
        modal.classList.remove("show");
        modal.setAttribute("aria-hidden", "true");
      });
    });
  });

  document.querySelectorAll(".list-modal-backdrop").forEach(function (backdrop) {
    backdrop.addEventListener("click", function () {
      document.querySelectorAll(".list-modal").forEach(function (modal) {
        modal.classList.remove("show");
        modal.setAttribute("aria-hidden", "true");
      });
    });
  });

  if (checkAll) {
    checkAll.addEventListener("change", function () {
      document.querySelectorAll(".row-check").forEach(function (cb) {
        cb.checked = checkAll.checked;
      });
      updateBulkDeleteButton();
    });
  }

  document.querySelectorAll(".row-check").forEach(function (cb) {
    cb.addEventListener("click", function (e) {
      e.stopPropagation();
      updateBulkDeleteButton();

      if (checkAll) {
        var total = document.querySelectorAll(".row-check").length;
        var selected = checkedRows().length;
        checkAll.checked = total > 0 && total === selected;
      }
    });
  });

  if (btnHapusBanyak && modalHapusBanyak && formHapusBanyak) {
    btnHapusBanyak.addEventListener("click", function () {
      var selected = checkedRows().length;
      if (selected < 1) return;
      hapusBanyakText.innerHTML =
        "Yakin ingin menghapus <b>" + selected + "</b> data terpilih?";
      modalHapusBanyak.classList.add("show");
      modalHapusBanyak.setAttribute("aria-hidden", "false");
    });
  }

  if (confirmHapusBanyak && formHapusBanyak) {
    confirmHapusBanyak.addEventListener("click", function () {
      formHapusBanyak.submit();
    });
  }

  if (btnImportPopup && modalImport) {
    btnImportPopup.addEventListener("click", function () {
      modalImport.classList.add("show");
      modalImport.setAttribute("aria-hidden", "false");
    });
  }

  if (btnExportPopup && modalExport) {
    btnExportPopup.addEventListener("click", function () {
      modalExport.classList.add("show");
      modalExport.setAttribute("aria-hidden", "false");
    });
  }

  updateBulkDeleteButton();
});
