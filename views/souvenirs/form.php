<?php require __DIR__.'/../layout/header.php';?>
<h2 class="page-title mb-4"><?= $souv?'Edit':'Tambah' ?> Souvenir</h2>
<style>
.souvenir-form-grid{display:grid;grid-template-columns:1.3fr .7fr;gap:20px;align-items:start;}
@media(max-width:900px){.souvenir-form-grid{grid-template-columns:1fr;}}
.status-guide .sg-item{display:flex;gap:10px;padding-bottom:14px;margin-bottom:14px;border-bottom:1px solid #f0f2f0;}
.status-guide .sg-item:last-child{border-bottom:0;margin-bottom:0;padding-bottom:0;}
.status-guide .sg-dot{width:9px;height:9px;border-radius:50%;margin-top:5px;flex:none;}
.status-guide .sg-title{font-size:13px;font-weight:700;color:var(--ink);margin-bottom:2px;}
.status-guide .sg-desc{font-size:12px;color:#6b7570;line-height:1.5;}
</style>
<div class="souvenir-form-grid">
<form method="post" class="form-card" style="max-width:none"><?=csrf_field()?>
<?php if(!$souv):?><div class="info-note"><i class="bi bi-info-circle"></i><span>Kode souvenir akan dibuat otomatis (format SOV-yymm-nnn).</span></div><?php endif;?>
<div class="row g-4">
<?php if($souv):?><div class="col-md-4"><label>Kode</label><input class="form-control" value="<?=e($souv['code'])?>" disabled></div><?php endif;?>
<div class="col-md-<?=$souv?'8':'12'?>"><label>Nama <span class="req-star">*</span></label><input name="name" class="form-control" value="<?=e($souv['name']??'')?>" placeholder="Contoh: Tumbler USC" required></div>
<div class="col-md-4"><label>Stok Gudang <span class="req-star">*</span></label><input type="number" min="0" name="stock" class="form-control" value="<?=e($souv['stock']??0)?>" placeholder="Contoh: 100" required><div class="form-text">Jumlah fisik yang ada di gudang saat ini. Saat restock, isi sesuai hasil hitung gudang.</div></div>
<div class="col-md-4"><label>Status <span class="req-star">*</span></label><div class="select-wrap"><select name="status" class="form-select" required><option>ACTIVE</option><option <?=($souv['status']??'')==='INACTIVE'?'selected':''?>>INACTIVE</option></select><i class="bi bi-chevron-down select-arrow"></i></div></div>
</div>
<button class="btn-brand mt-4" style="width:100%;justify-content:center;padding:12px;font-size:14px;"><i class="bi bi-check2"></i>Simpan</button>
</form>
<div class="soft-card status-guide">
<h3 style="font-size:14px;font-weight:700;margin:0 0 16px;">Panduan Status &amp; Stok</h3>
<div class="sg-item"><span class="sg-dot" style="background:#1f7a4c"></span><div><div class="sg-title">ACTIVE</div><div class="sg-desc">Souvenir tersedia dan bisa dialokasikan ke event serta muncul di Scanner Souvenir.</div></div></div>
<div class="sg-item"><span class="sg-dot" style="background:#6b7570"></span><div><div class="sg-title">INACTIVE</div><div class="sg-desc">Souvenir disembunyikan dari pilihan alokasi event dan scanner, tapi riwayat klaim yang sudah ada tetap tersimpan.</div></div></div>
<div class="sg-item"><span class="sg-dot" style="background:#4a7fa8"></span><div><div class="sg-title">Stok Gudang</div><div class="sg-desc">Jumlah fisik souvenir di gudang saat ini, berkurang otomatis setiap souvenir diserahkan ke peserta. Jumlah yang dialokasikan ke event aktif akan dicadangkan dan tidak dapat digunakan event lain sampai diambil peserta atau event selesai.</div></div></div>
</div>
</div>
<?php require __DIR__.'/../layout/footer.php';
