<?php require __DIR__.'/../layout/header.php';
$fv=array_merge($employee?:[],$old??[]); // isian form: data karyawan, ditimpa isian terakhir bila simpan gagal
$divisions=\App\Controllers\EmployeeController::divisions();
$plantCodesMap=\App\Controllers\EmployeeController::plantCodesMap();
$sectionsMap=\App\Controllers\EmployeeController::sectionsMap();
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
<form method="post" class="form-card" style="max-width:none" onsubmit="return prepSectionSubmit();"><?=csrf_field()?><div class="row g-4">
<div class="col-md-6"><label>NIK <span class="req-star">*</span></label><input class="form-control" id="nikInput" name="nik" value="<?=e($fv['nik']??'')?>" maxlength="7" inputmode="numeric" pattern="\d{1,7}" title="Angka saja, maksimal 7 digit" placeholder="Contoh: 1234567" required></div>
<div class="col-md-6"><label>Nama <span class="req-star">*</span></label><input class="form-control" name="name" value="<?=e($fv['name']??'')?>" placeholder="Contoh: Budi Santoso" maxlength="50" pattern="[\p{L}][\p{L} .'\-]*" title="Hanya huruf, spasi, titik, tanda petik, atau tanda hubung (maksimal 50 karakter)" oninput="this.value=this.value.replace(/[^\p{L} .'\-]/gu,'').replace(/^[ .'\-]+/,'').slice(0,50);document.getElementById('nameCount').textContent=this.value.length" required><div class="form-text d-flex justify-content-between"><span>Hanya huruf dan spasi.</span><span><span id="nameCount"><?=mb_strlen($fv['name']??'')?></span>/50</span></div></div>
<div class="col-md-6"><label>Email <span class="req-star">*</span></label><input type="email" class="form-control" name="email" value="<?=e($fv['email']??'')?>" placeholder="Contoh: budi.santoso@usc-indonesia.co.id" required></div>
<div class="col-md-6"><label>Divisi <span class="req-star">*</span></label><div class="select-wrap"><select name="division" id="divisionSelect" class="form-select" required><option value="">-- Pilih Division --</option><?php foreach($divisions as $val=>$label):?><option value="<?=e($val)?>" <?=$curDivision===$val?'selected':''?>><?=e($label)?></option><?php endforeach;?></select><i class="bi bi-chevron-down select-arrow"></i></div></div>
<div class="col-md-6"><label>Departemen <span class="req-star">*</span></label><div class="select-wrap"><select name="department" id="departmentSelect" class="form-select" required><option value="">-- Pilih Departemen --</option></select><i class="bi bi-chevron-down select-arrow"></i></div></div>
<div class="col-md-6"><label>Section <span class="req-star">*</span></label><div class="select-wrap"><select id="sectionSelect" class="form-select" required><option value="">-- Pilih Section --</option></select><i class="bi bi-chevron-down select-arrow"></i></div><input type="text" id="sectionOther" name="section" class="form-control mt-2" placeholder="Tulis section lainnya" style="display:none"></div>
<div class="col-md-4"><label>Status</label><div class="select-wrap"><select name="status" class="form-select"><option value="ACTIVE" <?=($fv['status']??'ACTIVE')==='ACTIVE'?'selected':''?>>ACTIVE</option><option value="INACTIVE" <?=($fv['status']??'')==='INACTIVE'?'selected':''?>>INACTIVE</option></select><i class="bi bi-chevron-down select-arrow"></i></div></div></div><button class="btn-brand mt-4" style="width:100%;justify-content:center;padding:12px;font-size:14px;"><i class="bi bi-check2"></i>Simpan</button></form>
<div class="soft-card">
<h3 style="font-size:14px;font-weight:700;margin:0 0 14px;">Panduan Divisi &amp; Departemen</h3>
<p style="font-size:12px;color:#6b7570;margin:0 0 12px;">Kode Departemen = kode plant + akhiran <strong>-C</strong> (Cibitung) atau <strong>-K</strong> (Karawang).</p>
<div class="div-chip">AD → AD-C / AD-K</div>
<div class="div-chip">BD → BD-C / BD-K</div>
<div class="div-chip">DPC → DPC-C / DPC-K</div>
<div class="div-chip">FD → FD-C / FD-K</div>
<div class="div-chip">MD → MD-C / MD-K</div>
<div class="div-chip">HRD, GA &amp; PU → HR-C/K, GA-C/K, PU-C/K</div>
</div>
</div>
<script>
nikInput.addEventListener('input',()=>{nikInput.value=nikInput.value.replace(/\D/g,'').slice(0,7);});
const PLANT_CODES_MAP=<?=json_encode($plantCodesMap)?>;
const SECTIONS_MAP=<?=json_encode($sectionsMap)?>;
const CUR_DIVISION=<?=json_encode($curDivision)?>;
const CUR_DEPARTMENT=<?=json_encode($curDepartment)?>;
const CUR_SECTION=<?=json_encode($curSection)?>;
const divisionSelect=document.getElementById('divisionSelect');
const departmentSelect=document.getElementById('departmentSelect');
const sectionSelect=document.getElementById('sectionSelect');
const sectionOther=document.getElementById('sectionOther');

function rebuildDepartment(selected){
 const div=divisionSelect.value;
 departmentSelect.innerHTML='<option value="">-- Pilih Departemen --</option>';
 const codes=PLANT_CODES_MAP[div];
 if(!codes)return;
 codes.forEach(code=>{
  [`${code}-C`,`${code}-K`].forEach(v=>{
   const o=document.createElement('option');o.value=v;o.textContent=v;
   if(v===selected)o.selected=true;
   departmentSelect.appendChild(o);
  });
 });
}
function rebuildSection(selected){
 const div=divisionSelect.value;
 const list=SECTIONS_MAP[div]||[];
 sectionSelect.innerHTML='<option value="">-- Pilih Section --</option>';
 let matched=false;
 list.forEach(v=>{
  const o=document.createElement('option');o.value=v;o.textContent=v;
  if(v===selected){o.selected=true;matched=true;}
  sectionSelect.appendChild(o);
 });
 const other=document.createElement('option');other.value='__OTHER__';other.textContent='Lainnya...';
 sectionSelect.appendChild(other);
 if(selected && !matched && div!==''){
  other.selected=true;
  sectionOther.style.display='';
  sectionOther.value=selected;
 }else{
  sectionOther.style.display='none';
  sectionOther.value=matched?selected:'';
 }
}
sectionSelect.addEventListener('change',()=>{
 if(sectionSelect.value==='__OTHER__'){sectionOther.style.display='';sectionOther.value='';sectionOther.focus();}
 else{sectionOther.style.display='none';sectionOther.value=sectionSelect.value;}
});
divisionSelect.addEventListener('change',()=>{rebuildDepartment(null);rebuildSection(null);});
function prepSectionSubmit(){
 if(sectionSelect.value!=='__OTHER__')sectionOther.value=sectionSelect.value;
 return true;
}
rebuildDepartment(CUR_DEPARTMENT);
rebuildSection(CUR_SECTION);
</script>
<?php require __DIR__.'/../layout/footer.php';
