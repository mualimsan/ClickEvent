-- NIK boleh huruf/angka/titik/tanda hubung, maks. 20 karakter
-- (karyawan tetap, PKL, outsourcing, internship, driver: 2205049, BAA-2409065, VOC-2608132, DRV.01, INT-2610285).
-- Jalankan sekali di phpMyAdmin (tab SQL) pada database aplikasi. Aman dijalankan ulang.

-- 1) Hapus aturan lama "NIK hanya angka" (nama aturannya dicari otomatis)
SET @c = (SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS
          WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'employees' AND CONSTRAINT_TYPE = 'CHECK' LIMIT 1);
SET @s = IF(@c IS NULL, 'SELECT 1', CONCAT('ALTER TABLE employees DROP CONSTRAINT `', @c, '`'));
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 2) Perpanjang kolom NIK menjadi 20 karakter (NIK tetap unik)
ALTER TABLE employees MODIFY nik VARCHAR(20) NOT NULL;
