<?php require __DIR__.'/../layout/header.php';$isAdmin=\App\Auth::user()['role']==='ADMIN';$depts=array_values(array_unique(array_filter(array_map(fn($x)=>$x['department'],$employees))));sort($depts);
$totalInv=count($invitations);$pending=array_values(array_filter($invitations,fn($i)=>empty($i['qr_generated_at'])));$pendingCount=count($pending);
$sendable=array_filter($invitations,fn($i)=>!empty($i['qr_generated_at'])&&$i['email_status']!=='SENT');$sendableCount=count($sendable);
$invDepts=array_values(array_unique(array_filter(array_map(fn($i)=>$i['department'],$invitations))));sort($invDepts);
$isPublished=$event['status']==='PUBLISHED';
$statusColor=['SENT'=>'#1f7a4c','PENDING'=>'#b8860b','FAILED'=>'#c0392b'];
$statusLabel=['SENT'=>'SENT','PENDING'=>'BELUM DIKIRIM','FAILED'=>'FAILED'];
?>
<div class="page-head"><h2 class="page-title"><?=e($event['event_name'])?> <span style="color:#8a938d;font-weight:500;">— Peserta</span></h2></div>

<div class="soft-card mb-4">
<div class="step-head"><span class="step-no">1</span><div><h3>Pilih Karyawan yang Diundang</h3><p>Centang karyawan, lalu klik <strong>Undang ke Event</strong>. Mereka akan masuk ke <strong>Daftar Peserta</strong> di bawah. Karyawan yang sudah diundang tidak muncul lagi di sini.</p></div></div>
<form method="post" action="<?=BASE_URL?>/events/<?=$event['id']?>/invitations/add">
<?=csrf_field()?>
<div class="d-flex flex-wrap gap-2 align-items-center mb-3">
<input type="text" id="searchFilter" class="search-input" style="max-width:240px" placeholder="Cari nama / NIK...">
<div class="select-wrap" style="max-width:220px"><select id="deptFilter" class="form-select"><option value="">Semua Departemen</option><?php foreach($depts as $d):?><option value="<?=e($d)?>"><?=e($d)?></option><?php endforeach;?></select><i class="bi bi-chevron-down select-arrow"></i></div>
<button type="button" class="btn-outline-brand c-blue" onclick="selectAllVisible()"><i class="bi bi-check2-square"></i>Pilih Semua</button>
<button type="button" id="deselectBtn" class="btn-danger-soft" style="display:none" onclick="deselectAll()"><i class="bi bi-x-circle"></i> Batalkan Pilihan</button>
<span class="count-pill"><span id="selectedCount">0</span> dipilih</span>
</div>
<div class="data-card" style="max-height:350px;overflow:auto"><table class="data-table" id="guestTable"><thead><tr><th style="width:36px"></th><th>NIK</th><th>Nama</th><th>Departemen</th><th>Email</th></tr></thead><tbody><?php foreach($employees as $x):?><tr data-dept="<?=e($x['department'])?>" data-search="<?=e(mb_strtolower($x['name'].' '.$x['nik']))?>"><td><input class="emp" type="checkbox" name="employee_ids[]" value="<?=$x['id']?>"></td><td><?=$x['nik']?></td><td><?=e($x['name'])?></td><td><?=e($x['department'])?></td><td><?=e($x['email'])?></td></tr><?php endforeach;?></tbody></table></div>
<div id="guestPagination" class="d-flex align-items-center gap-2 mt-3"></div>
<div class="d-flex align-items-center gap-2 flex-wrap mt-3"><button class="btn-brand" id="inviteBtn" disabled><i class="bi bi-person-plus"></i><span id="inviteLbl">Undang ke Event</span></button><span class="text-muted small" id="inviteHint">Centang minimal satu karyawan terlebih dahulu.</span></div>
</form>
</div>

<div class="soft-card">
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
<div class="step-head mb-0"><span class="step-no">2</span><div><h3>Daftar Peserta &amp; Status Undangan</h3><p>Karyawan yang sudah diundang. Generate QR, lalu kirim undangan lewat email.</p></div></div>
<?php if($isAdmin):?><div class="d-flex gap-2 flex-wrap align-items-center">
<?php if($isPublished):?><?php if($totalInv===0):?><span class="badge-pill" style="background:#6b7570;font-size:13px;padding:8px 16px;">Belum ada peserta</span><?php elseif($pendingCount>0):?><form id="generateForm" method="post" action="<?=BASE_URL?>/events/<?=$event['id']?>/invitations/generate-qr" onsubmit="return confirmGenerate(event);"><?=csrf_field()?><input type="hidden" name="department" id="generateDept" value=""><button id="generateBtn" class="btn-brand c-blue"><i class="bi bi-qr-code"></i><?=$pendingCount===$totalInv?'Generate QR untuk Semua Peserta':'Generate QR untuk '.$pendingCount.' Peserta Baru'?></button></form><?php else:?><span class="badge-pill" style="background:#1f7a4c;font-size:13px;padding:8px 16px;">Semua peserta sudah digenerate</span><?php endif;?>
<form id="sendAllForm" method="post" action="<?=BASE_URL?>/events/<?=$event['id']?>/invitations/send-all" onsubmit="return confirmSendAll(event);"><?=csrf_field()?><input type="hidden" name="department" id="sendAllDept" value=""><button id="sendAllBtn" class="btn-brand" <?=$sendableCount>0?'':'disabled title="Tidak ada undangan yang siap dikirim"'?>><i class="bi bi-send"></i>Send Semua<?=$sendableCount>0?' ('.$sendableCount.')':''?></button></form>
<?php endif;?>
<button type="button" id="removeSelBtn" class="btn-danger-soft" disabled onclick="submitRemove()"><i class="bi bi-trash3"></i>Hapus Terpilih</button>
</div><?php endif;?>
</div>
<?php if(!$isPublished):?><div class="alert-soft-warn mb-3">Event ini belum berstatus <strong>PUBLISHED</strong> (status saat ini: <strong><?=$event['status']?></strong>), jadi tombol Generate QR &amp; Send Email belum tersedia dulu. Ubah status event ke PUBLISHED di halaman Edit untuk mengaktifkannya.</div><?php elseif($isAdmin && $pendingCount>0):?><div class="alert-soft-warn mb-3">Peserta baru wajib di-generate dulu sebelum tombol Send-nya bisa dipakai.</div><?php endif;?>
<div class="mb-3 select-wrap" style="max-width:220px"><select id="invDeptFilter" class="form-select"><option value="">Semua Departemen</option><?php foreach($invDepts as $d):?><option value="<?=e($d)?>"><?=e($d)?></option><?php endforeach;?></select><i class="bi bi-chevron-down select-arrow"></i></div>
<div class="data-card"><div class="table-responsive"><table class="data-table" id="invTable"><thead><tr><?php if($isAdmin):?><th style="width:36px"><input type="checkbox" id="invSelAll"></th><?php endif;?><th>QR</th><th>Nama</th><th>Departemen</th><th>Email</th><th>Status</th></tr></thead><tbody><?php foreach($invitations as $i):$rowReady=!empty($i['qr_generated_at']);?><tr data-dept="<?=e($i['department'])?>"><?php if($isAdmin):?><td><input type="checkbox" class="inv" value="<?=$i['id']?>"></td><?php endif;?><td><?php if($rowReady):?><img src="<?=BASE_URL?>/events/<?=$event['id']?>/invitations/<?=$i['id']?>/qr.png" loading="lazy" decoding="async" alt="QR" width="56" height="56" style="cursor:pointer;border-radius:6px;border:1px solid #e7e9e6" class="qr-thumb" data-name="<?=e($i['employee_name'])?>"><?php else:?><span class="text-muted small">Belum digenerate</span><?php endif;?></td><td class="fw-semibold"><?=e($i['employee_name'])?></td><td><?=e($i['department'])?></td><td><?=e($i['email'])?></td><td><?php if(!$rowReady):?><span class="badge-pill" style="background:#6b7570">BELUM DIGENERATE</span><?php else:?><span class="badge-pill" style="background:<?=$statusColor[$i['email_status']]??'#6b7570'?>" <?=$i['email_status']==='FAILED' && !empty($i['last_email_error'])?'title="'.e($i['last_email_error']).'"':''?>><?=$statusLabel[$i['email_status']]??$i['email_status']?><?=$i['email_status']==='FAILED'?' ⓘ':''?></span><?php endif;?></td></tr><?php endforeach;?></tbody></table></div></div>
<div id="invPagination" class="d-flex align-items-center gap-2 mt-3"></div>
</div>

<?php if($isAdmin):?><form id="removeForm" method="post" action="<?=BASE_URL?>/events/<?=$event['id']?>/invitations/remove" style="display:none"><?=csrf_field()?><div id="removeIdsHolder"></div></form><?php endif;?>
<div class="modal fade" id="sendModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"><div class="modal-dialog modal-dialog-centered"><div class="modal-content" style="border:0;border-radius:16px;overflow:hidden;">
<div class="modal-body" style="padding:28px 28px 22px;">
<h5 id="sendTitle" style="font-size:16px;font-weight:700;margin:0 0 4px;">Mengirim undangan...</h5>
<p id="sendSub" class="text-muted" style="font-size:13px;margin:0 0 18px;">Jangan tutup atau pindah dari halaman ini sampai selesai.</p>
<div style="height:10px;border-radius:10px;background:#eef1ee;overflow:hidden;"><div id="sendBar" style="height:100%;width:0;background:linear-gradient(90deg,#1f7a4c,#34d399);border-radius:10px;transition:width .4s ease;"></div></div>
<div class="d-flex justify-content-between mt-2" style="font-size:12.5px;"><span id="sendCount" class="fw-semibold">0 / 0</span><span id="sendPct" class="text-muted">0%</span></div>
<div class="d-flex gap-3 mt-3" style="font-size:12.5px;"><span style="color:#1f7a4c"><i class="bi bi-check-circle-fill"></i> Terkirim: <b id="sendOk">0</b></span><span style="color:#c0392b"><i class="bi bi-x-circle-fill"></i> Gagal: <b id="sendFail">0</b></span><span class="text-muted ms-auto" id="sendEta"></span></div>
<div id="sendErr" class="alert-soft-danger mt-3" style="display:none;font-size:12px;"></div>
<div class="d-flex justify-content-end gap-2 mt-4"><button type="button" id="sendStopBtn" class="btn-outline-brand">Hentikan</button><button type="button" id="sendDoneBtn" class="btn-brand" style="display:none" onclick="location.reload()">Selesai</button></div>
</div></div></div></div>
<div class="modal fade" id="qrModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content" style="border:0;border-radius:16px;overflow:hidden;"><div class="modal-header" style="border-bottom:1px solid #eef1ee;"><h5 class="modal-title" id="qrname" style="font-weight:700;"></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body text-center py-4"><img id="qrimg" src="" style="max-width:320px"></div></div></div></div>
<script>
const PAGE_SIZE=15;
function renderPagination(container,totalPages,page,onGo){
 container.innerHTML='';
 if(totalPages<=1)return;
 const mk=(label,p,disabled,active)=>{const b=document.createElement('button');b.type='button';b.className=active?'btn-brand':'btn-outline-brand';b.style.padding='6px 12px';b.style.fontSize='12.5px';b.textContent=label;b.disabled=disabled;if(disabled)b.style.opacity='.5';b.onclick=()=>onGo(p);return b;};
 container.appendChild(mk('«',page-1,page<=1,false));
 container.appendChild(Object.assign(document.createElement('span'),{className:'small text-muted mx-1',textContent:'Halaman '+page+' / '+totalPages}));
 container.appendChild(mk('»',page+1,page>=totalPages,false));
}

const deptFilter=document.getElementById('deptFilter');
const searchFilter=document.getElementById('searchFilter');
const guestRows=Array.from(document.querySelectorAll('#guestTable tbody tr'));
let guestPage=1;
function guestMatches(){
 const dept=deptFilter.value;const q=searchFilter.value.trim().toLowerCase();
 return guestRows.filter(tr=>{const deptOk=!dept||tr.dataset.dept===dept;const searchOk=!q||tr.dataset.search.includes(q);return deptOk&&searchOk;});
}
function renderGuestPage(){
 const matched=guestMatches();
 const totalPages=Math.max(1,Math.ceil(matched.length/PAGE_SIZE));
 if(guestPage>totalPages)guestPage=totalPages;
 const start=(guestPage-1)*PAGE_SIZE;
 const shown=new Set(matched.slice(start,start+PAGE_SIZE));
 guestRows.forEach(tr=>{tr.style.display=shown.has(tr)?'':'none';});
 renderPagination(document.getElementById('guestPagination'),totalPages,guestPage,p=>{guestPage=p;renderGuestPage();});
}
function applyFilters(){guestPage=1;renderGuestPage();}
function selectAllVisible(){guestMatches().forEach(tr=>{const cb=tr.querySelector('.emp');if(cb)cb.checked=true;});updateCount();}
function deselectAll(){guestRows.forEach(tr=>{const cb=tr.querySelector('.emp');if(cb)cb.checked=false;});updateCount();}
function updateCount(){const n=document.querySelectorAll('.emp:checked').length;document.getElementById('selectedCount').textContent=n;document.getElementById('deselectBtn').style.display=n>0?'':'none';
 const b=document.getElementById('inviteBtn');if(b){b.disabled=n===0;document.getElementById('inviteLbl').textContent=n?'Undang '+n+' Karyawan ke Event':'Undang ke Event';document.getElementById('inviteHint').textContent=n?'Akan ditambahkan ke Daftar Peserta di bawah.':'Centang minimal satu karyawan terlebih dahulu.';}}
deptFilter.addEventListener('change',applyFilters);
searchFilter.addEventListener('input',applyFilters);
guestRows.forEach(tr=>{const cb=tr.querySelector('.emp');if(cb)cb.addEventListener('change',updateCount);});
renderGuestPage();

const invDeptFilter=document.getElementById('invDeptFilter');
const sendAllBtn=document.getElementById('sendAllBtn');
const sendAllDept=document.getElementById('sendAllDept');
const generateBtn=document.getElementById('generateBtn');
const generateDept=document.getElementById('generateDept');
document.querySelectorAll('.qr-thumb').forEach(img=>img.addEventListener('click',()=>{document.getElementById('qrimg').src=img.src;document.getElementById('qrname').textContent=img.dataset.name;new bootstrap.Modal(document.getElementById('qrModal')).show();}));
const invRows=Array.from(document.querySelectorAll('#invTable tbody tr'));
let invPage=1;
function invMatches(){
 const dept=invDeptFilter.value;
 return invRows.filter(tr=>!dept||tr.dataset.dept===dept);
}
function renderInvPage(){
 const matched=invMatches();
 const totalPages=Math.max(1,Math.ceil(matched.length/PAGE_SIZE));
 if(invPage>totalPages)invPage=totalPages;
 const start=(invPage-1)*PAGE_SIZE;
 const shown=new Set(matched.slice(start,start+PAGE_SIZE));
 invRows.forEach(tr=>{tr.style.display=shown.has(tr)?'':'none';});
 renderPagination(document.getElementById('invPagination'),totalPages,invPage,p=>{invPage=p;renderInvPage();});
}
function countSendable(){return invMatches().filter(tr=>tr.querySelector('td img')&&tr.children[tr.children.length-1].textContent.trim()!=='SENT').length;}
function countPending(){return invMatches().filter(tr=>!tr.querySelector('td img')).length;}
function refreshBulkButtons(){
 const dept=invDeptFilter.value;
 if(sendAllDept)sendAllDept.value=dept;
 if(generateDept)generateDept.value=dept;
 if(sendAllBtn){const n=countSendable();sendAllBtn.innerHTML='<i class="bi bi-send"></i>Send Semua'+(n>0?' ('+n+')':'');sendAllBtn.disabled=n===0;sendAllBtn.title=n===0?'Tidak ada undangan yang siap dikirim'+(dept?' untuk departemen ini':''):'';}
 if(generateBtn){const n=countPending();generateBtn.disabled=n===0;generateBtn.title=n===0?'Tidak ada peserta yang perlu digenerate'+(dept?' untuk departemen ini':''):'';}
}
function confirmSendAll(ev){
 if(sendAllBtn.disabled)return false;
 ev.preventDefault();
 const n=countSendable();const dept=invDeptFilter.value;
 showConfirm('Kirim email undangan ke '+n+' peserta'+(dept?' departemen "'+dept+'"':'')+' yang belum terkirim?',()=>runSendBatches(n,dept),{title:'Kirim undangan?',okText:'Ya, Kirim'});
 return false;
}
// Kirim per batch (±20 detik per request) supaya 1000+ email tidak kena batas waktu PHP.
async function runSendBatches(total,dept){
 const modal=new bootstrap.Modal(document.getElementById('sendModal'));modal.show();
 const $=id=>document.getElementById(id);
 const LANES=<?=\App\Controllers\EventController::SEND_LANES?>;
 let ok=0,fail=0,stop=false,t0=Date.now(),errs=[],fatal='';
 const remaining=Array(LANES).fill(null);
 $('sendStopBtn').onclick=()=>{stop=true;$('sendStopBtn').disabled=true;$('sendStopBtn').textContent='Menghentikan...';};
 const warn=e=>{e.preventDefault();e.returnValue='';};
 addEventListener('beforeunload',warn);
 const paint=()=>{const done=ok+fail,pct=total?Math.min(100,Math.round(done/total*100)):100;$('sendBar').style.width=pct+'%';$('sendPct').textContent=pct+'%';$('sendCount').textContent=done+' / '+total;$('sendOk').textContent=ok;$('sendFail').textContent=fail;
  if(done>0&&done<total){const per=(Date.now()-t0)/done,left=Math.round(per*(total-done)/1000);$('sendEta').textContent='Sisa ±'+(left>=60?Math.ceil(left/60)+' menit':left+' detik');}else $('sendEta').textContent='';};
 paint();
 // Satu jalur: kirim batch berulang sampai kelompok pesertanya habis.
 async function lane(k){
  let after=0;
  while(!stop){
   const f=new FormData();f.append('_csrf',document.querySelector('#sendAllForm input[name=_csrf]').value);f.append('department',dept);f.append('after_id',after);f.append('lane',k);
   let r;
   try{const res=await fetch(BASE+'/events/<?=$event['id']?>/invitations/send-batch',{method:'POST',body:f});r=await res.json();}
   catch(e){fatal=fatal||'Koneksi ke server terputus. Undangan yang sudah terkirim tetap tersimpan; klik Send Semua lagi untuk melanjutkan sisanya.';stop=true;return;}
   if(r.busy){fatal=fatal||'Pengiriman untuk event ini sedang berjalan di tab/perangkat lain.';stop=true;return;}
   if(r.error){fatal=fatal||r.error;stop=true;return;}
   ok+=r.sent;fail+=r.failed;after=r.last_id;(r.errors||[]).forEach(x=>{if(errs.length<3)errs.push(x);});
   remaining[k]=r.remaining;
   if(remaining.every(x=>x!==null))total=Math.max(total,ok+fail+remaining.reduce((a,b)=>a+b,0));
   paint();
   if(r.halt){fatal=fatal||'Pengiriman dihentikan otomatis karena 3 email berturut-turut gagal. Kemungkinan server email tidak bisa dihubungi, login SMTP salah, atau batas kirim harian akun email sudah habis. Periksa pengaturan email lalu klik Send Semua lagi.';stop=true;return;}
   if(r.done||(!r.sent&&!r.failed))return;
  }
 }
 await Promise.all(Array.from({length:LANES},(_,k)=>lane(k)));
 removeEventListener('beforeunload',warn);
 const finished=!fatal&&remaining.every(x=>x===0);
 $('sendTitle').textContent=fatal?'Pengiriman terhenti':finished?'Pengiriman selesai':'Pengiriman dihentikan';
 $('sendSub').textContent=fatal||(finished?'Semua undangan sudah diproses.':'Sisa undangan belum dikirim. Klik Send Semua lagi untuk melanjutkan.');
 if(errs.length){$('sendErr').style.display='';$('sendErr').textContent='Detail error: '+errs.join(' | ');}
 $('sendStopBtn').style.display='none';$('sendDoneBtn').style.display='';
 // Semua berhasil: tutup otomatis, muat ulang daftar, lalu tampilkan notifikasi kecil.
 if(finished&&!fail){
  $('sendDoneBtn').style.display='none';
  try{sessionStorage.setItem('sendToast',JSON.stringify({ok,fail}));}catch(e){}
  setTimeout(()=>location.reload(),900);
 }
}
function confirmGenerate(ev){
 ev.preventDefault();
 const n=countPending();const dept=invDeptFilter.value;
 showConfirm('Generate QR untuk '+n+' peserta'+(dept?' departemen "'+dept+'"':'')+' yang belum digenerate? Peserta yang sudah pernah digenerate sebelumnya tidak akan berubah, dan setiap peserta hanya bisa digenerate sekali.',()=>document.getElementById('generateForm').submit(),{title:'Generate QR?'});
 return false;
}
invDeptFilter.addEventListener('change',()=>{invPage=1;renderInvPage();refreshBulkButtons();});
renderInvPage();
refreshBulkButtons();
const removeSelBtn=document.getElementById('removeSelBtn');
function updateRemoveBtn(){if(removeSelBtn)removeSelBtn.disabled=document.querySelectorAll('.inv:checked').length===0;}
invRows.forEach(tr=>{const cb=tr.querySelector('.inv');if(cb)cb.addEventListener('change',updateRemoveBtn);});
const invSelAll=document.getElementById('invSelAll');
if(invSelAll)invSelAll.addEventListener('change',()=>{invMatches().forEach(tr=>{const cb=tr.querySelector('.inv');if(cb)cb.checked=invSelAll.checked;});updateRemoveBtn();});
function submitRemove(){
 const checked=Array.from(document.querySelectorAll('.inv:checked'));
 if(!checked.length)return;
 const sentNames=checked.filter(cb=>cb.closest('tr').children[cb.closest('tr').children.length-1].textContent.trim()==='SENT').length;
 let msg='Hapus '+checked.length+' peserta dari daftar undangan event ini?';
 if(sentNames>0)msg+=' Perhatian: '+sentNames+' di antaranya sudah terkirim emailnya — QR yang sudah dikirim akan langsung tidak berlaku lagi.';
 showConfirm(msg,()=>{
  const holder=document.getElementById('removeIdsHolder');holder.innerHTML='';
  checked.forEach(cb=>{const inp=document.createElement('input');inp.type='hidden';inp.name='invitation_ids[]';inp.value=cb.value;holder.appendChild(inp);});
  document.getElementById('removeForm').submit();
 },{title:'Hapus peserta terpilih?'});
}
</script>
<script>Live.reloadOn(['inv','emp','evt']);</script>
<script>try{const t=JSON.parse(sessionStorage.getItem('sendToast')||'null');sessionStorage.removeItem('sendToast');if(t)showToast('success','Pengiriman selesai',t.ok+' undangan berhasil terkirim lewat email.');}catch(e){}</script>
<?php require __DIR__.'/../layout/footer.php';
