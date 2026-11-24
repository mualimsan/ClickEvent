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
<div class="col-md-6"><label>Nama <span class="req-star">*</span></label><input class="form-control" name="name" value="<?=e($fv['name']??'')?>" placeholder="Contoh: Budi Santoso" maxlength="50" pattern="[\p{L}][\p{L} .,'()\-]*" title="Huruf, spasi, titik, koma, tanda petik, tanda hubung, atau tanda kurung (maksimal 50 karakter)" oninput="nameTitle(this)" required><div class="form-text d-flex justify-content-end"><span><span id="nameCount"><?=mb_strlen($fv['name']??'')?></span>/50</span></div></div>
<div class="col-md-6"><label>Email <span class="req-star">*</span></label><input type="email" class="form-control" name="email" value="<?=e($fv['email']??'')?>" placeholder="Contoh: budi.santoso@usc-indonesia.co.id" required></div>
<div class="col-md-6"><label>Divisi <span class="req-star">*</span></label><div class="select-wrap"><select class="form-select" id="divSel" required></select><i class="bi bi-chevron-down select-arrow"></i></div><input class="form-control mt-2" name="division" id="divOther" value="<?=e($curDivision)?>" maxlength="150" placeholder="Tulis divisi lainnya" autocomplete="off" style="display:none"></div>
<div class="col-md-6"><label>Departemen <span class="req-star">*</span></label><div class="select-wrap"><select class="form-select" id="depSel" required></select><i class="bi bi-chevron-down select-arrow"></i></div><input class="form-control mt-2" name="department" id="depOther" value="<?=e($curDepartment)?>" maxlength="150" placeholder="Tulis departemen lainnya" autocomplete="off" style="display:none"></div>
<div class="col-md-6"><label>Section <span class="req-star">*</span></label><div class="select-wrap"><select class="form-select" id="secSel" required></select><i class="bi bi-chevron-down select-arrow"></i></div><input class="form-control mt-2" name="section" id="secOther" value="<?=e($curSection)?>" maxlength="150" placeholder="Tulis section lainnya" autocomplete="off" style="display:none"></div>
<div class="col-md-4"><label>Status</label><div class="select-wrap"><select name="status" class="form-select"><option value="ACTIVE" <?=($fv['status']??'ACTIVE')==='ACTIVE'?'selected':''?>>ACTIVE</option><option value="INACTIVE" <?=($fv['status']??'')==='INACTIVE'?'selected':''?>>INACTIVE</option></select><i class="bi bi-chevron-down select-arrow"></i></div></div></div><button class="btn-brand mt-4" style="width:100%;justify-content:center;padding:12px;font-size:14px;"><i class="bi bi-check2"></i>Simpan</button></form>
<div class="soft-card">
<h3 style="font-size:14px;font-weight:700;margin:0 0 10px;">Divisi &amp; Departemen</h3>
<p style="font-size:12.5px;color:#6b7570;margin:0;">Pilihan muncul otomatis dari data karyawan yang sudah ada (hasil import Excel kantor). Bila belum ada di daftar, pilih <b>Lainnya...</b> lalu tulis nilai baru.</p>
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
// Dropdown Divisi -> Departemen -> Section dari data karyawan yang sudah ada, plus "Lainnya..." untuk nilai baru.
const ORG=<?=json_encode($org,JSON_HEX_TAG|JSON_HEX_AMP)?>;
const OTHER='__OTHER__',F={};
['div','dep','sec'].forEach(k=>{F[k]={sel:document.getElementById(k+'Sel'),inp:document.getElementById(k+'Other')};});
const uniq=a=>[...new Set(a)].filter(Boolean).sort((x,y)=>x.localeCompare(y));
function options(k){
 const dv=F.div.inp.value,dp=F.dep.inp.value,known=ORG[dv];
 if(k==='div')return uniq(Object.keys(ORG));
 if(k==='dep')return uniq(known?Object.keys(known):Object.values(ORG).flatMap(Object.keys));
 if(known&&known[dp])return uniq(known[dp]);
 return uniq(known?Object.values(known).flat():Object.values(ORG).flatMap(x=>Object.values(x).flat()));
}
function build(k){
 const {sel,inp}=F[k],val=inp.value,list=options(k),esc=v=>v.replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;');
 sel.innerHTML='<option value="">-- Pilih --</option>'+list.map(v=>'<option value="'+esc(v)+'">'+esc(v)+'</option>').join('')+'<option value="'+OTHER+'">Lainnya...</option>';
 const other=val!==''&&!list.includes(val);
 sel.value=other?OTHER:val;
 inp.style.display=other?'':'none';inp.required=other;
}
function pick(k){
 const {sel,inp}=F[k];
 if(sel.value===OTHER){inp.value='';inp.style.display='';inp.required=true;inp.focus();}
 else{inp.value=sel.value;inp.style.display='none';inp.required=false;}
 if(k==='div'){F.dep.inp.value='';F.sec.inp.value='';build('dep');build('sec');}
 if(k==='dep'){F.sec.inp.value='';build('sec');}
}
['div','dep','sec'].forEach(k=>{F[k].sel.addEventListener('change',()=>pick(k));build(k);});
F.div.inp.addEventListener('input',()=>{build('dep');build('sec');});
F.dep.inp.addEventListener('input',()=>build('sec'));
</script>
<?php require __DIR__.'/../layout/footer.php';
