# TODO: Pindah Popup ke Sel Data Harga Individual (Edit + Hapus)

## ✅ Plan Approved
- Pindah popup dari baris komoditas → **sel harga individual** (`.list-metric-value.harga`)
- Tambah tombol **Hapus** di popup
- Support edit harga + HET/HAP per sel

## 📋 Steps to Complete:

### ✅ Step 1: Update pages/list.php
- Tambah `data-record-*` attributes ke setiap `<td class="list-metric-value harga">`
- Tambah tombol `<button data-action="hapus">Hapus</button>` di `#rowActionPopup`

### ✅ Step 2: Update assets/js/list.js  
- Ganti hover target: `.list-data-row` → `.list-metric-value.harga`
- Update `setEditValues()` pakai `data-record-*`
- Tambah handler `action="hapus"` dengan konfirmasi + POST

### ✅ Step 3: Check process/list_process.php
- Pastikan handle `action="hapus"` dengan `$_POST['record_id']`

### ✅ Step 4: Test
- Hover sel harga → popup muncul di posisi sel
- Test Edit harga per sel
- Test Hapus record per sel  
- Test responsive + scroll

### ✅ Step 5: HET/HAP popup integration + button merge
- Merge Tambah/Edit ke 1 button "Edit Harga/HET"
- HET/HAP update via popup sel harga

**Current Progress: 5/5 steps DONE ✅ Task Complete!**

