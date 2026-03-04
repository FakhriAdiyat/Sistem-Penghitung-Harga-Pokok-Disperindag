<?php
require_once 'config/database.php';
require_once 'includes/auth_check.php';

// SEARCH
$keyword = $_GET['search'] ?? '';
$keyword_safe = mysqli_real_escape_string($conn, $keyword);

// Cek apakah tabel sudah pakai struktur baru (persen_penyimpangan, persen_naik_turun, naik_turun_rp)
$cols = mysqli_query($conn, "SHOW COLUMNS FROM harga LIKE 'persen_penyimpangan'");
$pakai_struktur_baru = (mysqli_num_rows($cols) > 0);

if ($pakai_struktur_baru) {
    $query = "
    SELECT 
        h.id,
        h.bahan_id,
        b.nama_bahan,
        b.satuan,
        b.het_hap,
        h.harga,
        h.tanggal,
        h.rata_rata,
        h.persen_penyimpangan,
        h.fluktuasi_persen,
        h.stabilitas_persen,
        h.persen_naik_turun,
        h.naik_turun_rp
    FROM harga h
    JOIN bahan_pokok b ON h.bahan_id = b.id
    WHERE b.nama_bahan LIKE '%$keyword_safe%'
    ORDER BY h.tanggal DESC
    ";
} else {
    $query = "
    SELECT 
        h.id,
        h.bahan_id,
        b.nama_bahan,
        b.satuan,
        b.het_hap,
        h.harga,
        h.tanggal,
        h.rata_rata,
        h.rata_penyimpangan,
        h.fluktuasi_persen,
        h.stabilitas_persen,
        h.persen_kenaikan,
        h.persen_penurunan,
        h.kenaikan_rp,
        h.penurunan_rp
    FROM harga h
    JOIN bahan_pokok b ON h.bahan_id = b.id
    WHERE b.nama_bahan LIKE '%$keyword_safe%'
    ORDER BY h.tanggal DESC
    ";
}

$data = mysqli_query($conn, $query);

// Daftar bahan untuk dropdown Tambah
$bahan_list = mysqli_query($conn, "SELECT id, nama_bahan FROM bahan_pokok ORDER BY nama_bahan ASC");

$msg = '';
if (isset($_GET['success'])) {
    if ($_GET['success'] === 'tambah') $msg = 'Data berhasil ditambah.';
    elseif ($_GET['success'] === 'edit') $msg = 'Data berhasil diubah.';
    elseif ($_GET['success'] === 'hapus') $msg = 'Data berhasil dihapus.';
}
if (isset($_GET['err'])) $msg = 'Terjadi kesalahan. Coba lagi.';
?>

<div class="layout">

<?php require_once 'includes/sidebar.php'; ?>

<div style="flex:1; display:flex; flex-direction:column;">

<?php require_once 'includes/header.php'; ?>

<div class="content">
<div class="container">    

<h1>List Data Harga</h1>
<p class="subtitle">Data harga bahan pokok</p>

<?php if ($msg): ?>
<p class="list-flash-msg <?= isset($_GET['err']) ? 'error' : 'success' ?>"><?= htmlspecialchars($msg) ?></p>
<?php endif; ?>


<div class="list-table-toolbar">
    <form method="GET" class="search-box">
        <input type="text" name="search" placeholder="Cari bahan..." value="<?= htmlspecialchars($keyword) ?>">
        <button type="submit">Cari</button>
    </form>

    <button type="button" id="btnHapusBanyak" class="btn-hapus-banyak" disabled>
        🗑️ Hapus Terpilih
    </button>
</div>

<!-- TABLE WRAP -->
 <form method="post" action="list_process.php" id="formHapusBanyak">
    <input type="hidden" name="action" value="hapus_banyak">
<div class="list-table-wrap">
<table class="member-table">
<thead>
<tr>
     <th>
    <input type="checkbox" id="checkAll">
    </th>
    <th>No</th>
    <th>Bahan (satuan)</th>
    <th>Harga</th>
    <th>Rata² Harga</th>
    <th>Persen Penyimpangan</th>
    <th>Fluktuasi %</th>
    <th>Stabilitas %</th>
    <th>HET/HAP</th>
    <th>Naik/Turun (%)</th>
    <th>Naik/Turun (Rp)</th>
    <th>Tanggal</th>
</tr>
</thead>
<tbody>
<?php 
$no = 1;
while($row = mysqli_fetch_assoc($data)) { 
    $bid = (int)($row['bahan_id'] ?? 0);
?>
<tr class="list-data-row" 
    data-id="<?= (int)$row['id'] ?>" 
    data-bahan-id="<?= $bid ?>" 
    data-bahan-nama="<?= htmlspecialchars($row['nama_bahan']) ?>" 
    data-harga="<?= (float)$row['harga'] ?>" 
    data-tanggal="<?= htmlspecialchars($row['tanggal']) ?>">

    <td>
        <input 
            type="checkbox" 
            class="row-check" 
            name="ids[]" 
            value="<?= (int)$row['id'] ?>"
            onclick="event.stopPropagation()"
        >
    </td>

    <td><?= $no++ ?></td>

    <td><?= htmlspecialchars($row['nama_bahan']) ?>
<?php if (!empty($row['satuan'])) : ?>
(<?= htmlspecialchars($row['satuan']) ?>)
<?php endif; ?></td>

    <td>Rp <?= number_format($row['harga'],0,',','.') ?></td>

    <!-- RATA-RATA -->
    <td>
        <?php 
        if ($row['rata_rata'] != NULL) {
            echo "Rp " . number_format($row['rata_rata'],0,',','.');
        } else {
            echo "-";
        }
        ?>
    </td>

    <?php
    // Nilai tampilan: dukung struktur lama dan baru
    if ($pakai_struktur_baru) {
        $persen_penyimpangan = $row['persen_penyimpangan'] ?? null;
        $pn = isset($row['persen_naik_turun']) && $row['persen_naik_turun'] !== '' ? (float)$row['persen_naik_turun'] : null;
        $rp = isset($row['naik_turun_rp']) && $row['naik_turun_rp'] !== '' ? (float)$row['naik_turun_rp'] : null;
    } else {
        $persen_penyimpangan = null;
        if (!empty($row['rata_rata']) && $row['rata_rata'] > 0 && isset($row['rata_penyimpangan']) && $row['rata_penyimpangan'] !== null) {
            $persen_penyimpangan = round(($row['rata_penyimpangan'] / $row['rata_rata']) * 100, 2);
        }
        if (!empty($row['persen_kenaikan']) && $row['persen_kenaikan'] > 0) {
            $pn = (float)$row['persen_kenaikan'];
            $rp = !empty($row['kenaikan_rp']) ? (float)$row['kenaikan_rp'] : null;
        } elseif (!empty($row['persen_penurunan']) && $row['persen_penurunan'] > 0) {
            $pn = -(float)$row['persen_penurunan'];
            $rp = !empty($row['penurunan_rp']) ? -(float)$row['penurunan_rp'] : null;
        } else {
            $pn = null;
            $rp = null;
        }
    }
    ?>
    <!-- PERSEN PENYIMPANGAN -->
    <td>
        <?= ($persen_penyimpangan !== null && $persen_penyimpangan !== '') ? number_format((float)$persen_penyimpangan, 2) . ' %' : '-' ?>
    </td>
    <!-- FLUKTUASI -->
    <td>
        <?= (isset($row['fluktuasi_persen']) && $row['fluktuasi_persen'] !== null && $row['fluktuasi_persen'] !== '') ? number_format((float)$row['fluktuasi_persen'], 2) . ' %' : '-' ?>
    </td>
    <!-- STABILITAS -->
    <td>
        <?= (isset($row['stabilitas_persen']) && $row['stabilitas_persen'] !== null && $row['stabilitas_persen'] !== '') ? number_format((float)$row['stabilitas_persen'], 2) . ' %' : '-' ?>
    </td>
    <!-- HET -->
    <td>
        <?php 
        if ($row['het_hap'] > 0) {
            echo "Rp " . number_format($row['het_hap'],0,',','.');
        } else {
            echo "-";
        }
        ?>
    </td>

    <!-- NAIK/TURUN % -->
    <td>
        <?php
        if ($pn !== null && $pn != 0) {
            $cls = $pn > 0 ? 'green' : 'red';
            echo "<span style='color:{$cls};font-weight:bold;'>" . number_format($pn, 2) . " %</span>";
        } else {
            echo "-";
        }
        ?>
    </td>

    <!-- NAIK/TURUN Rp -->
    <td>
        <?php
        if ($rp !== null && $rp != 0) {
            $cls = $rp > 0 ? 'green' : 'red';
            echo "<span style='color:{$cls};font-weight:bold;'>Rp " . number_format(abs($rp), 0, ',', '.') . ($rp < 0 ? ' ↓' : ' ↑') . "</span>";
        } else {
            echo "-";
        }
        ?>
    </td>

    <td><?= $row['tanggal'] ?></td>
</tr>
<?php } ?>
</tbody>
</table>
<!-- Modal Hapus Banyak -->
<div id="modalHapusBanyak" class="list-modal" aria-hidden="true">
  <div class="list-modal-backdrop"></div>
  <div class="list-modal-box">
    <h3>Hapus Data Terpilih</h3>
    <p id="hapusBanyakText">
      Yakin ingin menghapus <b>0</b> data terpilih?
    </p>

    <div class="list-modal-actions">
      <button type="button" id="confirmHapusBanyak" class="btn-save">Hapus</button>
      <button type="button" class="btn-cancel-modal">Batal</button>
    </div>
  </div>
</div>
</div>
</form>

<!-- Popup menu aksi baris -->
<div id="rowActionPopup" class="row-action-popup" aria-hidden="true">
    <button type="button" class="row-action-btn row-action-tambah" data-action="tambah">➕ Tambah</button>
    <button type="button" class="row-action-btn row-action-edit" data-action="edit">✏️ Edit</button>
</div>
</div>

<!-- Modal Tambah -->
<div id="modalTambah" class="list-modal" role="dialog" aria-hidden="true">
    <div class="list-modal-backdrop"></div>
    <div class="list-modal-box">
        <h3>Tambah Data Harga</h3>
        <form method="post" action="list_process.php">
            <input type="hidden" name="action" value="tambah">
            <?php if ($keyword !== ''): ?><input type="hidden" name="search_redirect" value="<?= htmlspecialchars($keyword) ?>"><?php endif; ?>
            <div class="form-group">
                <label>Bahan</label>
                <select name="bahan_id" required>
                    <option value="">-- Pilih Bahan --</option>
                    <?php mysqli_data_seek($bahan_list, 0); while($b = mysqli_fetch_assoc($bahan_list)): ?>
                    <option value="<?= (int)$b['id'] ?>"><?= htmlspecialchars($b['nama_bahan']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Harga (Rp)</label>
                <input type="number" name="harga" min="0" step="100" required placeholder="Contoh: 14000">
            </div>
            <div class="form-group">
                <label>Tanggal</label>
                <input type="date" name="tanggal" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="list-modal-actions">
                <button type="submit" class="btn-save">Simpan</button>
                <button type="button" class="btn-cancel-modal">Batal</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit -->
<div id="modalEdit" class="list-modal" role="dialog" aria-hidden="true">
    <div class="list-modal-backdrop"></div>
    <div class="list-modal-box">
        <h3>Edit Data Harga</h3>
        <form method="post" action="list_process.php">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="editId">
            <?php if ($keyword !== ''): ?><input type="hidden" name="search_redirect" value="<?= htmlspecialchars($keyword) ?>"><?php endif; ?>
            <div class="form-group">
                <label>Bahan</label>
                <input type="text" id="editBahanNama" readonly class="form-readonly">
            </div>
            <div class="form-group">
                <label>Harga (Rp)</label>
                <input type="number" name="harga" id="editHarga" min="0" step="100" required>
            </div>
            <div class="form-group">
                <label>Tanggal</label>
                <input type="date" name="tanggal" id="editTanggal" required>
            </div>
            <div class="list-modal-actions">
                <button type="submit" class="btn-save">Simpan</button>
                <button type="button" class="btn-cancel-modal">Batal</button>
            </div>
        </form>
    </div>
</div>

</div>
</div>
</div>
</div>

<script>window.LIST_SEARCH = <?= json_encode($keyword) ?>;</script>
<?php require_once 'includes/footer.php'; ?>
<script src="assets/js/list.js"></script>
