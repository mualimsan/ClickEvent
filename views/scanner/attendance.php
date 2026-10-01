<?php require __DIR__.'/../layout/header.php';?>
<h2 class="page-title mb-3"><span class="page-icon c-orange"><i class="bi bi-qr-code-scan"></i></span>Scanner Kehadiran</h2>
<?php if(!$events):
 $__hari=['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];$__bln=[1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
 $__tgl=function(string $d)use($__hari,$__bln){$t=strtotime($d);return $__hari[(int)date('w',$t)].', '.(int)date('j',$t).' '.$__bln[(int)date('n',$t)].' '.date('Y',$t);};
?>
<div class="soft-card" style="max-width:640px;text-align:center;padding:36px 28px;">
<div style="width:56px;height:56px;border-radius:50%;background:#eef8e6;color:var(--accent);display:flex;align-items:center;justify-content:center;font-size:24px;margin:0 auto 14px;"><i class="bi bi-calendar2-week"></i></div>
<h3 style="font-size:17px;font-weight:700;margin:0 0 6px;">Belum Ada Event Hari Ini</h3>
<p class="text-muted" style="font-size:13.5px;margin:0 0 18px;">Scanner akan aktif secara otomatis pada tanggal pelaksanaan event.</p>
<?php if($nextEvents):?><div style="text-align:left;border-top:1px solid #eef0ef;padding-top:16px;"><div class="text-muted fw-semibold mb-2" style="font-size:11px;letter-spacing:.06em;">JADWAL BERIKUTNYA</div><?php foreach($nextEvents as $ne):$d=(int)round((strtotime($ne['event_date'])-strtotime(date('Y-m-d')))/86400);?><div class="d-flex justify-content-between align-items-center gap-3 py-2" style="border-bottom:1px dashed #eef0ef;"><div><div class="fw-semibold"><?=e($ne['event_name'])?></div><div class="text-muted" style="font-size:12.5px;"><i class="bi bi-calendar-event me-1"></i><?=$__tgl($ne['event_date'])?></div></div><span class="badge-pill" style="background:#eef8e6;color:var(--accent);white-space:nowrap;"><?=$d===1?'Besok':$d.' hari lagi'?></span></div><?php endforeach;?></div><?php endif;?>
</div>
<?php require __DIR__.'/../layout/footer.php';return;endif;?>
<div class="mb-3 select-wrap" style="max-width:420px"><select id="eventId" class="form-select" data-auto-static data-icon="bi-calendar-event"><?php foreach($events as $e):?><option value="<?=$e['id']?>"><?=e($e['event_name'])?> — <?=dmy($e['event_date'])?></option><?php endforeach;?></select><i class="bi bi-chevron-down select-arrow"></i></div>
<div class="soft-card mb-3">
<label class="mb-2">Scanner USB / Input Token</label>
<div class="scan-group"><input id="manualToken" class="form-control form-control-lg" placeholder="Arahkan scanner USB ke sini lalu tembak QR" autocomplete="off" autofocus><button id="manualBtn" class="btn-brand" type="button">Proses</button></div>
<div id="result" class="mt-3 d-none"></div>
<details class="mt-3"><summary class="text-muted" style="cursor:pointer">Gunakan kamera / upload gambar</summary><div id="camwarn" class="cam-note d-none"></div><div id="reader" class="mt-2" style="max-width:520px"></div></details>
</div>
<div class="soft-card">
<h6 class="mb-3 d-flex align-items-center gap-2">Sudah Check-in <span class="count-pill" id="checkedInCount">0</span><span class="text-muted small fw-normal ms-1">15 terbaru</span></h6>
<div class="table-responsive"><table class="data-table"><thead><tr><th>Waktu</th><th>NIK</th><th>Nama</th><th>Departemen</th></tr></thead><tbody id="checkedInList"><tr><td colspan="4" class="text-muted">Memuat...</td></tr></tbody></table></div>
</div>
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script><script>
const out=document.getElementById('result');function show(j){showScanResult(out,j,{ok:'Check-in Berhasil',warn:'Peserta Sudah Check-in',err:'Check-in Ditolak'});}
function esc(s){return (s??'').toString().replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));}
function dmy(s){if(!s)return '';const [d,t]=s.split(' ');const [y,m,dd]=d.split('-');if(!y||!m||!dd)return s;return dd+'-'+m+'-'+y+(t?' '+t:'');}
function dtCell(s){if(!s)return '';const [d,t]=dmy(s).split(' ');return '<div class="dt-cell"><span class="dt-date">'+esc(d)+'</span>'+(t?'<span class="dt-time">'+esc(t)+'</span>':'')+'</div>';}
const CHECKIN_RECENT_LIMIT=15;
async function refreshCheckedIn(){
 try{
  const r=await fetch(BASE+'/reports/attendance/data?event_id='+eventId.value);
  const data=await r.json();
  const checkedInData=data.filter(x=>x.attended).sort((a,b)=>(b.checkin_at||'').localeCompare(a.checkin_at||''));
  document.getElementById('checkedInCount').textContent=checkedInData.length;
  const recent=checkedInData.slice(0,CHECKIN_RECENT_LIMIT);
  document.getElementById('checkedInList').innerHTML=recent.length?recent.map(x=>'<tr><td>'+dtCell(x.checkin_at)+'</td><td>'+esc(x.nik)+'</td><td>'+esc(x.name)+'</td><td>'+esc(x.department)+'</td></tr>').join(''):'<tr><td colspan="4" class="text-muted">Belum ada yang check-in.</td></tr>';
 }catch(e){}
}
const CSRF_TOKEN=<?=json_encode(csrf_token())?>;
let busy=false;async function success(text){if(busy)return;busy=true;const f=new FormData();f.append('_csrf',CSRF_TOKEN);f.append('event_id',eventId.value);f.append('token',text);try{show(await (await fetch(BASE+'/api/scanner/attendance',{method:'POST',body:f})).json());refreshCheckedIn();}finally{setTimeout(()=>busy=false,1200);manualToken.focus();}}
manualBtn.onclick=()=>{const t=manualToken.value.trim();if(t){success(t);manualToken.value='';}};
manualToken.addEventListener('keydown',ev=>{if(ev.key==='Enter'){ev.preventDefault();manualBtn.click();}});
manualToken.focus();
document.addEventListener('click',()=>{const t=document.activeElement.tagName;if(t!=='INPUT'&&t!=='TEXTAREA'&&t!=='SELECT')manualToken.focus();});
eventId.addEventListener('change',refreshCheckedIn);
refreshCheckedIn();
Live.on(['att','inv','emp'],refreshCheckedIn);
Live.on(['evt'],()=>Live.notice('Ada perubahan data event. Muat ulang untuk memperbarui daftar event.'));
new Html5QrcodeScanner('reader',{fps:10,qrbox:250},false).render(success);
Html5Qrcode.getCameras().then(d=>{if(!d.length)throw 0;}).catch(()=>{camwarn.classList.remove('d-none');camwarn.innerHTML='<i class="bi bi-info-circle"></i><span>Kamera tidak tersedia di perangkat ini. Gunakan scanner USB pada kolom di atas, atau pilih <strong>upload gambar</strong> QR.</span>';});
</script><?php require __DIR__.'/../layout/footer.php';