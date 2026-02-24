-- =============================================================================
-- MIGRASI TABEL HARGA (struktur lama → baru)
-- =============================================================================
--
-- PENTING: JANGAN jalankan file ini jika Anda baru meng-import db_harga_bapok.sql
--          karena db_harga_bapok.sql sudah memakai struktur baru. Jalankan migrate
--          HANYA jika database Anda masih punya struktur LAMA (kolom: rata_penyimpangan,
--          persen_kenaikan, persen_penurunan, kenaikan_rp, penurunan_rp).
--
-- Jika Anda dapat error "Duplicate column name 'persen_penyimpangan'" berarti
-- tabel Anda sudah struktur baru → abaikan file ini.
--
-- =============================================================================

-- 1. Tambah kolom baru (akan error jika kolom sudah ada = tabel sudah baru)
ALTER TABLE `harga`
  ADD COLUMN `persen_penyimpangan` decimal(8,2) DEFAULT NULL COMMENT 'persen penyimpangan' AFTER `rata_rata`,
  ADD COLUMN `persen_naik_turun` decimal(10,2) DEFAULT NULL COMMENT 'persen naik (+) atau turun (-)' AFTER `stabilitas_persen`,
  ADD COLUMN `naik_turun_rp` decimal(12,2) DEFAULT NULL COMMENT 'naik Rp (+) atau turun Rp (-)' AFTER `persen_naik_turun`;

-- 2. Isi data dari kolom lama ke kolom baru
UPDATE `harga` SET
  `persen_penyimpangan` = CASE
    WHEN `rata_rata` IS NOT NULL AND `rata_rata` > 0 AND `rata_penyimpangan` IS NOT NULL
    THEN ROUND((`rata_penyimpangan` / `rata_rata`) * 100, 2)
    ELSE NULL
  END,
  `persen_naik_turun` = CASE
    WHEN COALESCE(`persen_kenaikan`, 0) > 0 THEN `persen_kenaikan`
    WHEN COALESCE(`persen_penurunan`, 0) > 0 THEN -`persen_penurunan`
    ELSE 0
  END,
  `naik_turun_rp` = CASE
    WHEN COALESCE(`kenaikan_rp`, 0) > 0 THEN `kenaikan_rp`
    WHEN COALESCE(`penurunan_rp`, 0) > 0 THEN -`penurunan_rp`
    ELSE NULL
  END;

-- 3. Hapus kolom lama
ALTER TABLE `harga`
  DROP COLUMN `rata_penyimpangan`,
  DROP COLUMN `persen_kenaikan`,
  DROP COLUMN `persen_penurunan`,
  DROP COLUMN `kenaikan_rp`,
  DROP COLUMN `penurunan_rp`;
