<?php require __DIR__.'/../layout/header.php';?>
<div class="page-head"><h2 class="page-title">Alokasi Souvenir</h2></div>
<p style="font-size:14px;color:#3d453f;font-weight:600;margin:-14px 0 14px;"><?=e($event['event_name'])?></p>
<div class="info-note"><i class="bi bi-info-circle"></i><div>"Tersedia" = stok gudang dikurangi alokasi yang belum diambil di event lain yang masih aktif (DRAFT/PUBLISHED). Event yang sudah CLOSED/CANCELLED tidak lagi mengunci stok.</div></div>
<form method="post" id="allocForm" onsubmit="return validateAlloc(event);">
<?=csrf_field()?>
<div class="data-card"><div class="table-responsive"><table class="data-table"><thead><tr><th style="width:36px"><input type="checkbox" id="selAll"></th><th>Kode</th><th>Souvenir</th><th>Sisa Stok</th><th>Belum Diambil (Event Lain)</th><th>Tersedia</th><th>Alokasi Event</th></tr></thead><tbody><?php foreach($souvenirs as $s):$qty=$alloc[$s['id']]??0;$used=$elsewhere[$s['id']]??0;$available=max(0,(int)$s['stock']-$used);?><tr><td><input type="checkbox" class="selChk" data-qty="qty_<?=$s['id']?>" <?=$qty>0?'checked':''?>></td><td><?=$s['code']?></td><td class="fw-semibold"><?=e($s['name'])?></td><td><?=$s['stock']?></td><td><?=$used?></td><td><strong><?=$available?></strong></td><td style="max-width:140px;"><input type="number" min="0" max="<?=$available?>" id="qty_<?=$s['id']?>" class="form-control qtyInput" data-available="<?=$available?>" data-name="<?=e($s['name'])?>" name="qty[<?=$s['id']?>]" value="<?=e($qty)?>"><div class="invalid-feedback"></div></td></tr><?php endforeach;?></tbody></table></div></div>
<button class="btn-brand mt-3 w-100" style="justify-content:center;padding:12px;font-size:14px;"><i class="bi bi-check2"></i>Simpan Alokasi</button>
</form>
<script>
function checkRow(inp){
 const available=parseInt(inp.dataset.available,10),val=parseInt(inp.value||'0',10),fb=inp.nextElementSibling;
 if(val>available){inp.classList.add('is-invalid');fb.textContent='Melebihi sisa yang tersedia ('+available+').';fb.style.display='block';return false;}
 inp.classList.remove('is-invalid');fb.style.display='none';return true;
}
function syncRow(chk){
 const inp=document.getElementById(chk.dataset.qty);
 if(chk.checked){if(inp.value==='0')inp.focus();}
 else{inp.value='0';}
 checkRow(inp);
}
function syncCheckbox(inp){
 const chk=document.querySelector('.selChk[data-qty="'+inp.id+'"]');
 if(chk)chk.checked=parseInt(inp.value||'0',10)>0;
}
document.querySelectorAll('.qtyInput').forEach(inp=>inp.addEventListener('input',()=>{checkRow(inp);syncCheckbox(inp);}));
document.querySelectorAll('.selChk').forEach(chk=>{chk.addEventListener('change',()=>syncRow(chk));checkRow(document.getElementById(chk.dataset.qty));});
selAll.addEventListener('change',()=>{document.querySelectorAll('.selChk').forEach(chk=>{chk.checked=selAll.checked;syncRow(chk);});});
function validateAlloc(ev){
 let ok=true,hasOverflow=false,hasZero=false;
 document.querySelectorAll('.qtyInput').forEach(inp=>{if(!checkRow(inp)){ok=false;hasOverflow=true;}});
 document.querySelectorAll('.selChk:checked').forEach(chk=>{
  const inp=document.getElementById(chk.dataset.qty);
  const val=parseInt(inp.value||'0',10);
  if(val<=0){
   inp.classList.add('is-invalid');
   const fb=inp.nextElementSibling;fb.textContent='Wajib diisi lebih dari 0, atau hapus centangnya.';fb.style.display='block';
   hasZero=true;ok=false;
  }
 });
 if(!ok){
  ev.preventDefault();
  const msgs=[];
  if(hasZero)msgs.push('Ada souvenir yang dicentang tapi alokasinya masih 0. Isi jumlah alokasi (lebih dari 0) atau hapus centangnya.');
  if(hasOverflow)msgs.push('Ada alokasi yang melebihi sisa yang tersedia (setelah dikurangi alokasi event lain).');
  showAlert(msgs.join(' '),{title:'Periksa kembali alokasi'});
  return false;
 }
 let total=0;
 document.querySelectorAll('.qtyInput').forEach(inp=>total+=parseInt(inp.value||'0',10));
 if(total===0){
  ev.preventDefault();
  showConfirm('Anda belum mengalokasikan souvenir apapun untuk event ini (semua alokasi masih 0). Simpan tanpa alokasi souvenir?',()=>document.getElementById('allocForm').submit(),{title:'Alokasi masih kosong',okText:'Ya, Simpan'});
  return false;
 }
 return true;
}
</script>
<script>Live.reloadOn(['alloc','evt','souv','stock']);</script>
<?php require __DIR__.'/../layout/footer.php';
