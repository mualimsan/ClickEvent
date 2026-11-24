<?php require __DIR__.'/../layout/header.php';
$fv=array_merge($employee?:[],$old??[]); // isian form: data karyawan, ditimpa isian terakhir bila simpan gagal
$curDivision=$fv['division']??'';
$curDepartment=$fv['department']??'';
$curSection=$fv['section']??'';
?><h2 class="page-title mb-4"><?= $employee?'Edit':'Tambah' ?> Karyawan</h2>
<style>
.employee-form-grid{display:grid;grid-template-columns:1.3fr .7fr;gap:20px;align-items:start;}
@media(max-width:900px){.employee-form-grid{grid-template-columns:1fr;}}
.div-chip{display:inline-block;background:#f4f6f5;border:1px solid #e7e9e6;color:#3d453f;font-size:11.5px;font-weight:600;border-radius:6px;padding:3px 8px;margin:2px 4px 2px 0;}
</style>
<div class="employee-form-grid">
<form method="post" class="form-card" style="max-width:none"><?=csrf_field()?><div class="row g-4">
<div class="col-md-6"><label>NIK <span class="req-star">*</span></label><input class="form-control" id="nikInput" name="nik" value="<?=e($fv['nik']??'')?>" maxlength="20" pattern="[A-Za-z0-9]([A-Za-z0-9. \-]*[A-Za-z0-9.\-])?" title="Huruf, angka, spasi, titik, atau tanda hubung (maksimal 20 karakter)" placeholder="Contoh: 2205049 / BAA-2409065 / INT-NEW 001" style="text-transform:uppercase" required></div>
<div class="col-md-6"><label>Nama <span class="req-star">*</span></label><input class="form-control" name="name" value="<?=e($fv['name']??'')?>" placeholder="Contoh: Budi Santoso" maxlength="50" pattern="[\p{L}][\p{L} .,'()\-]*" title="Huruf, spasi, titik, koma, tanda petik, tanda hubung, atau tanda kurung (maksimal 50 karakter)" oninput="nameTitle(this)" required><div class="form-text d-flex justify-content-between"><span>Huruf awal setiap kata otomatis kapital.</span><span><span id="nameCount"><?=mb_strlen($fv['name']??'')?></span>/50</span></div></div>
<div class="col-md-6"><label>Email <span class="req-star">*</span></label><input type="email" class="form-control" name="email" value="<?=e($fv['email']??'')?>" placeholder="Contoh: budi.santoso@usc-indonesia.co.id" required></div>
<div class="col-md-6"><label>Divisi <span class="req-star">*</span></label><input class="form-control" name="division" id="divisionInput" list="divList" value="<?=e($curDivision)?>" maxlength="150" placeholder="Pilih atau ketik, mis. MD" autocomplete="off" required><datalist id="divList"></datalist></div>
<div class="col-md-6"><label>Departemen <span class="req-star">*</span></label><input class="form-control" name="department" id="departmentInput" list="depList" value="<?=e($curDepartment)?>" maxlength="150" placeholder="Pilih atau ketik, mis. CCC" autocomplete="off" required><datalist id="depList"></datalist></div>
<div class="col-md-6"><label>Section <span class="req-star">*</span></label><input class="form-control" name="section" id="sectionInput" list="secList" value="<?=e($curSection)?>" maxlength="150" placeholder="Pilih atau ketik section" autocomplete="off" required><datalist id="secList"></datalist></div>
<div class="col-md-4"><label>Status</label><div class="select-wrap"><select name="status" class="form-select"><option value="ACTIVE" <?=($fv['status']??'ACTIVE')==='ACTIVE'?'selected':''?>>ACTIVE</option><option value="INACTIVE" <?=($fv['status']??'')==='INACTIVE'?'selected':''?>>INACTIVE</option></select><i class="bi bi-chevron-down select-arrow"></i></div></div></div><button class="btn-brand mt-4" style="width:100%;justify-content:center;padding:12px;font-size:14px;"><i class="bi bi-check2"></i>Simpan</button></form>
<div class="soft-card">
<h3 style="font-size:14px;font-weight:700;margin:0 0 10px;">Divisi &amp; Departemen</h3>
<p style="font-size:12.5px;color:#6b7570;margin:0;">Pilihan muncul otomatis dari data karyawan yang sudah ada (hasil import Excel kantor). Ketik untuk mencari, atau tulis nilai baru bila belum ada di daftar.</p>
</div>
</div>
<script>
// Nama: huruf awal setiap kata otomatis kapital saat diketik (sama dengan yang disimpan sistem).
function nameTitle(el){
 const pos=el.selectionStart;
 el.value=el.value.replace(/[^\p{L} .,'()\-]/gu,'').replace(/^[ .'\-]+/,'').slice(0,50).toLowerCase().replace(/(^|[\s(\-])(\p{L})/gu,(m,a,b)=>a+b.toUpperCase());
 el.setSelectionRange(pos,pos);document.getElementById('nameCount').textContent=el.value.length;
}
nikInput.addEventListener('input',()=>{nikInput.value=nikInput.value.toUpperCase().replace(/[^A-Z0-9. \-]/g,'').replace(/^ +/,'').replace(/ {2,}/g,' ').slice(0,20);});
// Saran Divisi -> Departemen -> Section dari data karyawan yang sudah ada; nilai baru tetap boleh diketik.
const ORG=<?=json_encode($org,JSON_HEX_TAG|JSON_HEX_AMP)?>;
const divIn=document.getElementById('divisionInput'),depIn=document.getElementById('departmentInput');
const fill=(id,vals)=>{document.getElementById(id).innerHTML=[...new Set(vals)].sort().map(v=>'<option value="'+v.replace(/"/g,'&quot;')+'">').join('');};
function refreshLists(){
 const d=ORG[divIn.value]||{},deps=Object.keys(ORG[divIn.value]?d:Object.assign({},...Object.values(ORG)));
 fill('divList',Object.keys(ORG));fill('depList',deps);
 const secs=ORG[divIn.value]&&d[depIn.value]?d[depIn.value]:Object.values(ORG[divIn.value]||{}).flat();
 fill('secList',secs.length?secs:Object.values(ORG).flatMap(x=>Object.values(x).flat()));
}
divIn.addEventListener('input',refreshLists);depIn.addEventListener('input',refreshLists);
refreshLists();
</script>
<?php require __DIR__.'/../layout/footer.php';
