<?php require __DIR__.'/../layout/header.php';$isAdmin=\App\Auth::user()['role']==='ADMIN';?>
<div class="page-head"><h2 class="page-title"><span class="page-icon c-blue"><i class="bi bi-people"></i></span>Karyawan</h2><?php if($isAdmin):?><div class="d-flex gap-2"><a class="btn-outline-brand" href="<?=BASE_URL?>/employees/import"><i class="bi bi-upload"></i>Import</a><a class="btn-brand" href="<?=BASE_URL?>/employees/create"><i class="bi bi-plus-lg"></i>Tambah Karyawan</a></div><?php endif;?></div>
<form class="d-flex flex-wrap gap-2 mb-3"><input class="search-input flex-grow-1" style="max-width:420px" name="q" value="<?=e($q)?>" placeholder="Cari nama karyawan / NIK / departemen"><button class="btn-outline-brand"><i class="bi bi-search"></i>Cari</button></form>
<form method="post" action="<?=BASE_URL?>/employees/bulk-delete" id="employeesBulkDeleteForm" onsubmit="return confirmEmployeesBulkDelete(event)">
<?=csrf_field()?>
<?php if($isAdmin):?><div class="mb-3 d-flex align-items-center gap-2"><button type="submit" id="delSelBtn" class="btn-danger-soft" disabled><i class="bi bi-trash3"></i>Hapus Terpilih</button><span class="text-muted small"><span id="selectedCount">0</span> dipilih</span></div><?php endif;?>
<div class="data-card" style="border-top:3px solid #4a7fa8"><div class="table-responsive"><table class="data-table"><thead><tr><?php if($isAdmin):?><th style="width:36px"><input type="checkbox" id="selAll"></th><?php endif;?><th>No</th><th>NIK</th><th>Nama</th><th>Section</th><th>Departemen</th><th>Divisi</th><th>Email</th><th>Status</th><th></th></tr></thead><tbody id="empTbody"><?php if(empty($employees)):?><tr><td colspan="9" class="text-muted text-center py-4"><i class="bi bi-inbox me-2"></i>Belum ada karyawan.</td></tr><?php else: foreach($employees as $idx=>$x):?><tr class="emp-row"><?php if($isAdmin):?><td><input class="emp" type="checkbox" name="employee_ids[]" value="<?=$x['id']?>"></td><?php endif;?><td class="text-muted"><?=$idx+1?></td><td><?=$x['nik']?></td><td class="fw-semibold"><?=e($x['name'])?></td><td><?=e($x['section'])?></td><td><?=e($x['department'])?></td><td><?=e($x['division'])?></td><td><?=e($x['email'])?></td><td><span class="badge-pill" style="background:<?=$x['status']==='ACTIVE'?'#1f7a4c':'#6b7570'?>"><?=e($x['status'])?></span></td><td><?php if($isAdmin):?><a class="btn-edit" href="<?=BASE_URL?>/employees/<?=$x['id']?>/edit">Edit</a><?php endif;?></td></tr><?php endforeach;endif;?></tbody></table></div></div>
<div id="empPagination" class="d-flex align-items-center gap-2 mt-3"></div>
</form>
<?php if($isAdmin):?><script>
function updateDelBtn(){const n=document.querySelectorAll('.emp:checked').length;delSelBtn.disabled=n===0;document.getElementById('selectedCount').textContent=n;}
selAll.onchange=()=>{document.querySelectorAll('.emp').forEach(x=>x.checked=selAll.checked);updateDelBtn();};
document.querySelectorAll('.emp').forEach(cb=>cb.addEventListener('change',updateDelBtn));
function confirmEmployeesBulkDelete(ev){
 const n=document.querySelectorAll('.emp:checked').length;
 if(n===0)return false;
 ev.preventDefault();
 showConfirm('Hapus permanen '+n+' employee terpilih? Seluruh riwayat undangan, kehadiran, dan klaim souvenir mereka juga akan ikut terhapus dan tidak bisa dikembalikan.',()=>document.getElementById('employeesBulkDeleteForm').submit(),{title:'Hapus employee terpilih?'});
 return false;
}
</script><?php endif;?>
<script>
(function(){
 const PAGE_SIZE=15;
 const rows=Array.from(document.querySelectorAll('#empTbody .emp-row'));
 let page=1;
 function render(){
  const totalPages=Math.max(1,Math.ceil(rows.length/PAGE_SIZE));
  if(page>totalPages)page=totalPages;
  const start=(page-1)*PAGE_SIZE;
  rows.forEach((tr,i)=>{tr.style.display=(i>=start&&i<start+PAGE_SIZE)?'':'none';});
  const container=document.getElementById('empPagination');container.innerHTML='';
  if(totalPages<=1)return;
  const mk=(label,p,disabled)=>{const b=document.createElement('button');b.type='button';b.className='btn-outline-brand';b.style.padding='6px 12px';b.style.fontSize='12.5px';b.textContent=label;b.disabled=disabled;if(disabled)b.style.opacity='.5';b.onclick=()=>{page=p;render();};return b;};
  container.appendChild(mk('«',page-1,page<=1));
  container.appendChild(Object.assign(document.createElement('span'),{className:'small text-muted mx-1',textContent:'Halaman '+page+' / '+totalPages}));
  container.appendChild(mk('»',page+1,page>=totalPages));
 }
 render();
})();
</script>
<script>Live.reloadOn(['emp']);</script>
<?php require __DIR__.'/../layout/footer.php';