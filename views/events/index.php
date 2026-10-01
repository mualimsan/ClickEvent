<?php require __DIR__.'/../layout/header.php';$isAdmin=\App\Auth::user()['role']==='ADMIN';?>
<div class="page-head"><h2 class="page-title"><span class="page-icon c-purple"><i class="bi bi-calendar-event"></i></span>Event</h2><?php if($isAdmin):?><a class="btn-brand" href="<?=BASE_URL?>/events/create"><i class="bi bi-plus-lg"></i>Buat Event</a><?php endif;?></div>
<form class="d-flex flex-wrap gap-2 mb-3"><input class="search-input flex-grow-1" style="max-width:420px" name="q" value="<?=e($q)?>" placeholder="Cari nama event / kode / lokasi"><button class="btn-outline-brand"><i class="bi bi-search"></i>Cari</button></form>
<form method="post" action="<?=BASE_URL?>/events/bulk-delete" id="eventsBulkDeleteForm" onsubmit="return confirmEventsBulkDelete(event)">
<?=csrf_field()?>
<?php if($isAdmin):?><div class="mb-3 d-flex align-items-center gap-2"><button type="submit" id="delSelBtn" class="btn-danger-soft" disabled><i class="bi bi-trash3"></i>Hapus Terpilih</button><span class="text-muted small"><span id="selectedCount">0</span> dipilih</span></div><?php endif;?>
<style>.evt-lock{color:#9aa19c;font-size:13px;cursor:help;}</style>
<div class="data-card" style="border-top:3px solid #7a62b8"><div class="table-responsive"><table class="data-table"><thead><tr><?php if($isAdmin):?><th style="width:36px"><input type="checkbox" id="selAll"></th><?php endif;?><th>No</th><th>Kode</th><th>Event</th><th>Tanggal</th><th>Lokasi</th><th>Status</th><th>Diundang</th><th>Aksi</th></tr></thead><tbody id="evtTbody"><?php if(empty($events)):?><tr><td colspan="<?=$isAdmin?9:8?>" class="text-muted text-center py-4"><i class="bi bi-inbox me-2"></i>Belum ada event.</td></tr><?php else: foreach($events as $idx=>$e):$statusColor=['DRAFT'=>'#6b7570','PUBLISHED'=>'#1f7a4c','CLOSED'=>'#1c2a22','CANCELLED'=>'#c0392b'][$e['status']]??'#6b7570';?><tr class="evt-row"><?php if($isAdmin):?><td><?php if($e['status']==='PUBLISHED'&&$e['sent_count']>0):?><i class="bi bi-lock-fill evt-lock" title="Tidak bisa dihapus: event masih PUBLISHED dan undangan QR sudah terkirim ke <?=$e['sent_count']?> peserta. Ubah status ke CANCELLED/CLOSED dulu jika ingin dihapus."></i><?php else:?><input class="evt" type="checkbox" name="event_ids[]" value="<?=$e['id']?>"><?php endif;?></td><?php endif;?><td class="text-muted"><?=$idx+1?></td><td><?=e($e['event_code'])?></td><td class="fw-semibold"><?=e($e['event_name'])?></td><td><?=dmy($e['event_date'])?></td><td><?=e($e['location'])?></td><td><span class="badge-pill" style="background:<?=$statusColor?>"><?=$e['status']?></span></td><td><?=$e['invited']?></td><td><div class="d-flex gap-2 flex-nowrap"><a class="btn-edit c-blue" href="<?=BASE_URL?>/events/<?=$e['id']?>/invitations"><i class="bi bi-people"></i> Peserta</a> <?php if($isAdmin):?><a class="btn-edit" href="<?=BASE_URL?>/events/<?=$e['id']?>/edit"><i class="bi bi-pencil"></i> Edit</a><a class="btn-edit c-amber" href="<?=BASE_URL?>/events/<?=$e['id']?>/souvenirs"><i class="bi bi-gift"></i> Souvenir</a><?php endif;?></div></td></tr><?php endforeach;endif;?></tbody></table></div></div>
<div id="evtPagination" class="d-flex align-items-center gap-2 mt-3"></div>
</form>
<?php if($isAdmin):?><script>
function updateDelBtn(){const n=document.querySelectorAll('.evt:checked').length;delSelBtn.disabled=n===0;document.getElementById('selectedCount').textContent=n;}
selAll.onchange=()=>{document.querySelectorAll('.evt').forEach(x=>x.checked=selAll.checked);updateDelBtn();};
document.querySelectorAll('.evt').forEach(cb=>cb.addEventListener('change',updateDelBtn));
function confirmEventsBulkDelete(ev){
 const n=document.querySelectorAll('.evt:checked').length;
 if(n===0)return false;
 ev.preventDefault();
 showConfirm('Hapus permanen '+n+' event terpilih? Seluruh data undangan, kehadiran, dan souvenir pada event tersebut juga akan ikut terhapus dan tidak bisa dikembalikan.',()=>document.getElementById('eventsBulkDeleteForm').submit(),{title:'Hapus event terpilih?'});
 return false;
}
</script><?php endif;?>
<script>
(function(){
 const PAGE_SIZE=15;
 const rows=Array.from(document.querySelectorAll('#evtTbody .evt-row'));
 let page=1;
 function render(){
  const totalPages=Math.max(1,Math.ceil(rows.length/PAGE_SIZE));
  if(page>totalPages)page=totalPages;
  const start=(page-1)*PAGE_SIZE;
  rows.forEach((tr,i)=>{tr.style.display=(i>=start&&i<start+PAGE_SIZE)?'':'none';});
  drawPager(document.getElementById('evtPagination'),page,totalPages,p=>{page=p;render();},rows.length,PAGE_SIZE);
 }
 render();
})();
</script>
<script>Live.reloadOn(['evt','inv']);</script>
<?php require __DIR__.'/../layout/footer.php';