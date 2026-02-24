(function () {
  var wrap = document.querySelector('.list-table-wrap');
  var popup = document.getElementById('rowActionPopup');
  var rows = document.querySelectorAll('.list-data-row');
  var modalTambah = document.getElementById('modalTambah');
  var modalEdit = document.getElementById('modalEdit');
  var modalHapus = document.getElementById('modalHapus');
  var hapusIdInputModal = document.getElementById('hapusIdModal');
  var hapusText = document.getElementById('hapusText');
  var currentRow = null;

  function hidePopup() {
    if (popup) {
      popup.classList.remove('show');
      popup.setAttribute('aria-hidden', 'true');
    }
    rows.forEach(function (r) { r.classList.remove('selected'); });
    currentRow = null;
  }

  function showPopupBelowRow(row) {
    if (!wrap || !popup) return;
    rows.forEach(function (r) { r.classList.remove('selected'); });
    row.classList.add('selected');
    currentRow = row;

    var rect = row.getBoundingClientRect();
    var wrapRect = wrap.getBoundingClientRect();
    popup.style.left = (rect.left - wrapRect.left + wrap.scrollLeft) + 'px';
    popup.style.top = (rect.bottom - wrapRect.top + wrap.scrollTop + 4) + 'px';
    popup.classList.add('show');
    popup.setAttribute('aria-hidden', 'false');
  }

  wrap && wrap.addEventListener('click', function (e) {
    var row = e.target.closest('.list-data-row');
    if (row) {
      e.preventDefault();
      if (currentRow === row && popup && popup.classList.contains('show')) {
        hidePopup();
        return;
      }
      showPopupBelowRow(row);
    } else if (!e.target.closest('.row-action-popup')) {
      hidePopup();
    }
  });

  document.addEventListener('click', function (e) {
    if (popup && popup.classList.contains('show') && !e.target.closest('.list-table-wrap')) {
      hidePopup();
    }
  });

  function openModalTambah() {
    if (modalTambah) {
      var sel = modalTambah.querySelector('select[name="bahan_id"]');
      if (sel && currentRow) {
        var bid = currentRow.getAttribute('data-bahan-id');
        if (bid) sel.value = bid;
      } else if (sel) {
        sel.value = '';
      }
      modalTambah.classList.add('show');
      modalTambah.setAttribute('aria-hidden', 'false');
    }
    hidePopup();
  }

  function openModalEdit(row) {
    if (!row || !modalEdit) return;
    var id = row.getAttribute('data-id');
    var nama = row.getAttribute('data-bahan-nama');
    var harga = row.getAttribute('data-harga');
    var tanggal = row.getAttribute('data-tanggal');
    document.getElementById('editId').value = id;
    document.getElementById('editBahanNama').value = nama;
    document.getElementById('editHarga').value = harga;
    document.getElementById('editTanggal').value = tanggal;
    modalEdit.classList.add('show');
    modalEdit.setAttribute('aria-hidden', 'false');
    hidePopup();
  }

  function doHapus(row) {
    if (!row || !modalHapus || !hapusIdInputModal) return;
    var id = row.getAttribute('data-id');
    if (!id) return;

    var nama = row.getAttribute('data-bahan-nama') || '';
    var harga = row.getAttribute('data-harga') || '';
    var tanggal = row.getAttribute('data-tanggal') || '';

    if (hapusText) {
      var hargaText = '';
      if (harga) {
        try {
          var n = Number(harga);
          if (!isNaN(n)) {
            hargaText = 'Rp ' + n.toLocaleString('id-ID');
          }
        } catch (e) {
          hargaText = harga;
        }
      }

      var parts = [];
      if (hargaText) parts.push(hargaText);
      if (nama) parts.push(nama);
      if (tanggal) parts.push('tanggal ' + tanggal);

      var detail = parts.length ? ' (' + parts.join(' · ') + ')' : '';
      hapusText.textContent = 'Yakin ingin menghapus data harga ini' + detail + '?';
    }

    hapusIdInputModal.value = id;
    modalHapus.classList.add('show');
    modalHapus.setAttribute('aria-hidden', 'false');
    hidePopup();
  }

  if (popup) {
    popup.addEventListener('click', function (e) {
      var btn = e.target.closest('.row-action-btn');
      if (!btn || !currentRow) return;
      var action = btn.getAttribute('data-action');
      if (action === 'tambah') openModalTambah();
      else if (action === 'edit') openModalEdit(currentRow);
      else if (action === 'hapus') doHapus(currentRow);
    });
  }

  document.querySelectorAll('.btn-cancel-modal').forEach(function (btn) {
    btn.addEventListener('click', function () {
      document.querySelectorAll('.list-modal').forEach(function (m) {
        m.classList.remove('show');
        m.setAttribute('aria-hidden', 'true');
      });
    });
  });

  document.querySelectorAll('.list-modal-backdrop').forEach(function (backdrop) {
    backdrop.addEventListener('click', function () {
      document.querySelectorAll('.list-modal').forEach(function (m) {
        m.classList.remove('show');
        m.setAttribute('aria-hidden', 'true');
      });
    });
  });
})();
