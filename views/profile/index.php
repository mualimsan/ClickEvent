<?php require __DIR__.'/../layout/header.php';?>
<div class="page-head"><h2 class="page-title">Profil Saya</h2></div>
<style>
.profile-grid{display:grid;grid-template-columns:1.2fr .8fr;gap:20px;align-items:start;}
@media(max-width:900px){.profile-grid{grid-template-columns:1fr;}}
.info-list{display:flex;flex-direction:column;gap:14px;}
.info-list .info-row{display:flex;justify-content:space-between;align-items:center;padding-bottom:14px;border-bottom:1px solid #f0f2f0;}
.info-list .info-row:last-child{border-bottom:0;padding-bottom:0;}
.info-list .info-lbl{font-size:12.5px;color:#6b7570;display:flex;align-items:center;gap:8px;}
.info-list .info-lbl i{color:var(--accent);font-size:14px;}
.info-list .info-val{font-size:13.5px;font-weight:600;color:var(--ink);}
.tip-item{display:flex;gap:10px;font-size:12.5px;color:#4b5563;line-height:1.6;margin-bottom:12px;}
.tip-item:last-child{margin-bottom:0;}
.tip-item i{color:var(--accent);font-size:14px;margin-top:1px;flex:none;}
</style>
<div class="profile-grid">
<div class="form-card" style="max-width:none">
<div class="d-flex align-items-center gap-3 mb-4">
<div style="width:56px;height:56px;border-radius:50%;background:var(--accent);color:#fff;font-weight:700;font-size:22px;display:flex;align-items:center;justify-content:center;flex:none;"><?=e(mb_strtoupper(mb_substr($user['name'],0,1)))?></div>
<div><div style="font-weight:700;font-size:16px;color:var(--ink);"><?=e($user['name'])?></div><div style="font-size:12.5px;color:#6b7570;"><?=e(ucfirst(strtolower($user['role'])))?></div></div>
</div>
<form method="post">
<?=csrf_field()?>
<div class="row g-4">
<div class="col-md-6"><label>Nama <span class="req-star">*</span></label><input class="form-control" name="name" value="<?=e($user['name'])?>" required></div>
<div class="col-md-6"><label>Username <span class="req-star">*</span></label><input class="form-control" name="username" value="<?=e($user['username']??'')?>" placeholder="Contoh: budi" pattern="[a-z0-9._\-]{3,30}" title="3–30 karakter: huruf kecil, angka, titik, garis bawah, atau strip" autocapitalize="none" spellcheck="false" required oninput="this.value=this.value.toLowerCase().replace(/\s+/g,'')"></div>
<div class="col-md-6"><label>Email <span class="req-star">*</span></label><input type="email" class="form-control" name="email" value="<?=e($user['email'])?>" required></div>
</div>
<hr style="border-color:#eef1ee;margin:24px 0;">
<div class="info-note"><i class="bi bi-info-circle"></i><div>Masukkan kata sandi saat ini untuk menyimpan perubahan. Kosongkan kolom kata sandi baru jika tidak ingin menggantinya.</div></div>
<div class="row g-4">
<div class="col-12"><label>Kata Sandi Saat Ini <span class="req-star">*</span></label><input type="password" class="form-control" name="current_password" placeholder="Wajib diisi untuk menyimpan perubahan" required autocomplete="current-password"></div>
<div class="col-md-6"><label>Kata Sandi Baru</label><input type="password" class="form-control" name="new_password" placeholder="Kosongkan jika tidak diganti" autocomplete="new-password"></div>
<div class="col-md-6"><label>Konfirmasi Kata Sandi Baru</label><input type="password" class="form-control" name="confirm_password" placeholder="Ulangi kata sandi baru" autocomplete="new-password"></div>
</div>
<button class="btn-brand mt-4" style="width:100%;justify-content:center;padding:12px;font-size:14px;"><i class="bi bi-check2"></i>Simpan Perubahan</button>
</form>
</div>
<div>
<div class="soft-card mb-3">
<h3 style="font-size:14px;font-weight:700;margin:0 0 16px;">Informasi Akun</h3>
<div class="info-list">
<div class="info-row"><span class="info-lbl"><i class="bi bi-shield-check"></i>Role</span><span class="info-val"><?=e(ucfirst(strtolower($user['role'])))?></span></div>
<div class="info-row"><span class="info-lbl"><i class="bi bi-toggle-on"></i>Status</span><span class="badge-pill" style="background:<?=$user['status']==='ACTIVE'?'#1f7a4c':'#6b7570'?>"><?=e($user['status'])?></span></div>
<div class="info-row"><span class="info-lbl"><i class="bi bi-clock-history"></i>Login Terakhir</span><span class="info-val"><?=$user['last_login_at']?dmy($user['last_login_at']):'-'?></span></div>
<div class="info-row"><span class="info-lbl"><i class="bi bi-calendar-plus"></i>Akun Dibuat</span><span class="info-val"><?=dmy($user['created_at'])?></span></div>
</div>
</div>
<div class="soft-card">
<h3 style="font-size:14px;font-weight:700;margin:0 0 16px;">Tips Keamanan</h3>
<div class="tip-item"><i class="bi bi-check-circle"></i><div>Gunakan kata sandi minimal 8 karakter, kombinasi huruf dan angka.</div></div>
<div class="tip-item"><i class="bi bi-check-circle"></i><div>Jangan bagikan kata sandi akun Anda kepada siapa pun.</div></div>
<div class="tip-item"><i class="bi bi-check-circle"></i><div>Ganti kata sandi secara berkala untuk menjaga keamanan akun.</div></div>
</div>
</div>
</div>
<?php require __DIR__.'/../layout/footer.php';
