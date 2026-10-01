-- Tambah kolom username untuk login (selain email).
-- Jalankan SEKALI di database server, misalnya lewat phpMyAdmin > tab SQL.
ALTER TABLE users ADD COLUMN username VARCHAR(50) NULL AFTER name;
ALTER TABLE users ADD UNIQUE KEY uq_users_username (username);

-- Isi username pengguna lama dari bagian depan email (admin@usc-indonesia.co.id -> admin).
-- Kalau ada bagian depan email yang sama, ditambah ID supaya tetap unik (mis. budi7).
UPDATE users u
JOIN (SELECT LOWER(SUBSTRING_INDEX(email,'@',1)) lp, COUNT(*) c FROM users GROUP BY lp) d
  ON d.lp = LOWER(SUBSTRING_INDEX(u.email,'@',1))
SET u.username = IF(d.c > 1, CONCAT(d.lp, u.id), d.lp)
WHERE u.username IS NULL;
