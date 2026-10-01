<?php require __DIR__.'/../layout/header.php';$curRole=$usr['role']??'EVENT_OPERATOR';$curStatus=$usr['status']??'ACTIVE';?>
<h2 class="page-title mb-4"><?= $usr?'Edit':'Tambah' ?> Pengguna</h2>
<style>
.user-form-grid{display:grid;grid-template-columns:1.3fr .7fr;gap:20px;align-items:start;}
@media(max-width:900px){.user-form-grid{grid-template-columns:1fr;}}
.status-guide .sg-item{display:flex;gap:10px;padding-bottom:14px;margin-bottom:14px;border-bottom:1px solid #f0f2f0;}
.status-guide .sg-item:last-child{border-bottom:0;margin-bottom:0;padding-bottom:0;}
.status-guide .sg-dot{width:9px;height:9px;border-radius:50%;margin-top:5px;flex:none;}
.status-guide .sg-title{font-size:13px;font-weight:700;color:var(--ink);margin-bottom:2px;}
.status-guide .sg-desc{font-size:12px;color:#6b7570;line-height:1.5;}
</style>
<div class="user-form-grid">
<form method="post" class="form-card" style="max-width:none" autocomplete="off"><?=csrf_field()?>
<?php if($isSelf):?><div class="info-note"><i class="bi bi-info-circle"></i><span>Ini akun Anda sendiri, jadi role dan status tidak bisa diubah di sini.</span></div><?php endif;?>
<div class="row g-4">
<div class="col-md-6"><label>Nama <span class="req-star">*</span></label><input name="name" class="form-control" value="<?=e($usr['name']??'')?>" placeholder="Contoh: Budi Santoso" required></div>
<div class="col-md-6"><label>Username <span class="req-star">*</span></label><input name="username" class="form-control" value="<?=e($usr['username']??'')?>" placeholder="Contoh: budi" pattern="[a-z0-9._\-]{3,30}" title="3–30 karakter: huruf kecil, angka, titik, garis bawah, atau strip" autocapitalize="none" spellcheck="false" required oninput="this.value=this.value.toLowerCase().replace(/\s+/g,'')"></div>
<div class="col-md-12"><label>Email <span class="req-star">*</span></label><input type="email" name="email" class="form-control" value="<?=e($usr['email']??'')?>" placeholder="nama@usc-indonesia.co.id" required></div>
<div class="col-md-6"><label>Role <span class="req-star">*</span></label><div class="select-wrap"><select name="role" class="form-select" required <?=$isSelf?'disabled':''?>><?php foreach(['EVENT_OPERATOR','ADMIN'] as $r):?><option value="<?=$r?>" <?=$r===$curRole?'selected':''?>><?=\App\Auth::roleLabel($r)?></option><?php endforeach;?></select><i class="bi bi-chevron-down select-arrow"></i></div></div>
<div class="col-md-6"><label>Status <span class="req-star">*</span></label><div class="select-wrap"><select name="status" class="form-select" required <?=$isSelf?'disabled':''?>><option value="ACTIVE" <?=$curStatus==='ACTIVE'?'selected':''?>>Aktif</option><option value="INACTIVE" <?=$curStatus==='INACTIVE'?'selected':''?>>Nonaktif</option></select><i class="bi bi-chevron-down select-arrow"></i></div></div>
<div class="col-md-6"><label>Kata Sandi <?php if(!$usr):?><span class="req-star">*</span><?php endif;?></label><input type="password" name="password" class="form-control" minlength="6" placeholder="<?=$usr?'Kosongkan jika tidak diubah':'Minimal 6 karakter'?>" <?=$usr?'':'required'?> autocomplete="new-password"></div>
<div class="col-md-6"><label>Konfirmasi Kata Sandi <?php if(!$usr):?><span class="req-star">*</span><?php endif;?></label><input type="password" name="password_confirm" class="form-control" minlength="6" placeholder="Ulangi kata sandi" <?=$usr?'':'required'?> autocomplete="new-password"></div>
</div>
<button class="btn-brand mt-4" style="width:100%;justify-content:center;padding:12px;font-size:14px;"><i class="bi bi-check2"></i>Simpan</button>
</form>
<div class="soft-card status-guide">
<h3 style="font-size:14px;font-weight:700;margin:0 0 16px;">Panduan Role &amp; Status</h3>
<div class="sg-item"><span class="sg-dot" style="background:#b77a3a"></span><div><div class="sg-title">Operator</div><div class="sg-desc">Hanya bisa membuka menu Scanner (kehadiran &amp; souvenir) dan Laporan. Cocok untuk petugas di meja registrasi acara.</div></div></div>
<div class="sg-item"><span class="sg-dot" style="background:#4a7fa8"></span><div><div class="sg-title">Admin</div><div class="sg-desc">Akses penuh: dashboard, karyawan, event, souvenir, pengiriman undangan, dan pengelolaan pengguna.</div></div></div>
<div class="sg-item"><span class="sg-dot" style="background:#167320"></span><div><div class="sg-title">Username</div><div class="sg-desc">Dipakai untuk login selain email, supaya lebih singkat. Huruf kecil tanpa spasi, misalnya <strong>budi</strong> atau <strong>budi.santoso</strong>.</div></div></div>
<div class="sg-item"><span class="sg-dot" style="background:#6b7570"></span><div><div class="sg-title">Nonaktif</div><div class="sg-desc">Akun tidak bisa login. Kalau sedang login, akun langsung keluar di halaman berikutnya yang dibuka.</div></div></div>
</div>
</div>
<?php require __DIR__.'/../layout/footer.php';
