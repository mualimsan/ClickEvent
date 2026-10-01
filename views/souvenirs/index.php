<?php require __DIR__.'/../layout/header.php';$isAdmin=\App\Auth::user()['role']==='ADMIN';?>
<div class="page-head"><h2 class="page-title"><span class="page-icon c-red"><i class="bi bi-gift"></i></span>Souvenir</h2><?php if($isAdmin):?><a class="btn-brand" href="<?=BASE_URL?>/souvenirs/create"><i class="bi bi-plus-lg"></i>Tambah Souvenir</a><?php endif;?></div>
<form class="d-flex flex-wrap gap-2 mb-3"><input class="search-input flex-grow-1" style="max-width:420px" name="q" value="<?=e($q)?>" placeholder="Cari nama souvenir / kode"><button class="btn-outline-brand"><i class="bi bi-search"></i>Cari</button></form>
<form method="post" action="<?=BASE_URL?>/souvenirs/bulk-delete" id="souvenirsBulkDeleteForm" onsubmit="return confirmSouvenirsBulkDelete(event)">
<?=csrf_field()?>
<?php if($isAdmin):?><div class="mb-3 d-flex align-items-center gap-2"><button type="submit" id="delSelBtn" class="btn-danger-soft" disabled><i class="bi bi-trash3"></i>Hapus Terpilih</button><span class="text-muted small"><span id="selectedCount">0</span> dipilih</span></div><?php endif;?>
<div class="data-card" style="border-top:3px solid #c0392b"><div class="table-responsive"><table class="data-table"><thead><tr><?php if($isAdmin):?><th style="width:36px"><input type="checkbox" id="selAll"></th><?php endif;?><th>No</th><th>Kode</th><th>Nama</th><th>Stok</th><th>Status</th><th></th></tr></thead><tbody id="svTbody"><?php if(empty($souvenirs)):?><tr><td colspan="6" class="text-muted text-center py-4"><i class="bi bi-inbox me-2"></i>Belum ada souvenir.</td></tr><?php else: foreach($souvenirs as $idx=>$s):?><tr class="sv-row"><?php if($isAdmin):?><td><input class="sv" type="checkbox" name="souvenir_ids[]" value="<?=$s['id']?>"></td><?php endif;?><td class="text-muted"><?=$idx+1?></td><td><?=$s['code']?></td><td class="fw-semibold"><?=e($s['name'])?></td><td><?=$s['stock']?></td><td><span class="badge-pill" style="background:<?=$s['status']==='ACTIVE'?'#1f7a4c':'#6b7570'?>"><?=$s['status']?></span></td><td><?php if($isAdmin):?><a class="btn-edit" href="<?=BASE_URL?>/souvenirs/<?=$s['id']?>/edit">Edit</a><?php endif;?></td></tr><?php endforeach;endif;?></tbody></table></div></div>
<div id="svPagination" class="d-flex align-items-center gap-2 mt-3"></div>
</form>
<?php if($isAdmin):?><script>
function updateDelBtn(){const n=document.querySelectorAll('.sv:checked').length;delSelBtn.disabled=n===0;document.getElementById('selectedCount').textContent=n;}
selAll.onchange=()=>{document.querySelectorAll('.sv').forEach(x=>x.checked=selAll.checked);updateDelBtn();};
document.querySelectorAll('.sv').forEach(cb=>cb.addEventListener('change',updateDelBtn));
function confirmSouvenirsBulkDelete(ev){
 const n=document.querySelectorAll('.sv:checked').length;
 if(n===0)return false;
 ev.preventDefault();
 showConfirm('Hapus permanen '+n+' souvenir terpilih? Souvenir yang sudah pernah diklaim di riwayat transaksi tidak akan ikut terhapus.',()=>document.getElementById('souvenirsBulkDeleteForm').submit(),{title:'Hapus souvenir terpilih?'});
 return false;
}
</script><?php endif;?>
<script>
(function(){
 const PAGE_SIZE=15;
 const rows=Array.from(document.querySelectorAll('#svTbody .sv-row'));
 let page=1;
 function render(){
  const totalPages=Math.max(1,Math.ceil(rows.length/PAGE_SIZE));
  if(page>totalPages)page=totalPages;
  const start=(page-1)*PAGE_SIZE;
  rows.forEach((tr,i)=>{tr.style.display=(i>=start&&i<start+PAGE_SIZE)?'':'none';});
  const container=document.getElementById('svPagination');container.innerHTML='';
  if(totalPages<=1)return;
  const mk=(label,p,disabled)=>{const b=document.createElement('button');b.type='button';b.className='btn-outline-brand';b.style.padding='6px 12px';b.style.fontSize='12.5px';b.textContent=label;b.disabled=disabled;if(disabled)b.style.opacity='.5';b.onclick=()=>{page=p;render();};return b;};
  container.appendChild(mk('«',page-1,page<=1));
  container.appendChild(Object.assign(document.createElement('span'),{className:'small text-muted mx-1',textContent:'Halaman '+page+' / '+totalPages}));
  container.appendChild(mk('»',page+1,page>=totalPages));
 }
 render();
})();
</script>
<script>Live.reloadOn(['stock','souv','alloc']);</script>
<?php require __DIR__.'/../layout/footer.php';