<?php require __DIR__.'/../layout/header.php';?>
<h2 class="page-title mb-3"><span class="page-icon c-red"><i class="bi bi-upc-scan"></i></span>Scanner Souvenir</h2>
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
<style>
/* Panel pengaturan scan: menonjol tapi tetap rapi, supaya operator selalu sadar souvenir mana yang sedang dibagikan */
.scan-setup{background:#fff;border:1px solid #e3ebe9;border-left:4px solid var(--lime);border-radius:14px;padding:16px 18px 18px;margin-bottom:16px;box-shadow:0 6px 18px -12px rgba(2,45,54,.25);}
.scan-setup .ss-head{display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:14px;font-weight:700;font-size:14px;color:var(--forest);}
.scan-setup .ss-head i{color:var(--accent);}
.scan-setup .ss-head .ss-hint{font-weight:500;font-size:12.5px;color:#7c8b86;margin-left:auto;}
.scan-setup .ss-grid{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1.25fr) 190px;gap:16px;align-items:end;}
@media(max-width:900px){.scan-setup .ss-grid{grid-template-columns:1fr;}.scan-setup .ss-head .ss-hint{margin-left:0;width:100%;}}
.scan-setup .ss-label{display:flex;align-items:center;gap:7px;font-size:11px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:#6b7570;margin-bottom:7px;}
.scan-setup .ss-no{width:18px;height:18px;border-radius:50%;background:var(--forest);color:var(--lime-bright);font-size:10.5px;display:inline-flex;align-items:center;justify-content:center;letter-spacing:0;}
.scan-setup .ss-souv{position:relative;}
.scan-setup .ss-souv .ss-ico{position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--accent);font-size:17px;pointer-events:none;z-index:1;}
.scan-setup .ss-souv select{padding-left:42px!important;font-size:16px!important;font-weight:700;color:var(--forest)!important;border:2px solid var(--lime)!important;background:#f7fcf3!important;box-shadow:0 0 0 4px rgba(144,227,101,.16);min-height:50px;}
.scan-setup .ss-main .static-field{min-height:50px;font-size:16px;font-weight:700;color:var(--forest);border:2px solid var(--lime);background:#f7fcf3;box-shadow:0 0 0 4px rgba(144,227,101,.16);}
.scan-setup .ss-main .static-field i{color:var(--accent);font-size:17px;}
.scan-setup .ss-ev .static-field,.scan-setup .ss-ev select{min-height:50px;font-size:16px!important;font-weight:700;color:var(--forest)!important;border-width:2px!important;}
.scan-setup .ss-ev .static-field i{font-size:17px;}
.claim-chips{display:flex;flex-wrap:wrap;gap:8px;}
#claimedList td:not(:last-child),#claimedList~* th,.claim-table th{white-space:nowrap;}
.claim-chip{display:inline-flex;align-items:center;gap:8px;padding:7px 14px;border-radius:22px;background:#eef8e6;border:1px solid #d6efc7;color:var(--forest);font-size:14.5px;font-weight:700;white-space:nowrap;}
.claim-chip i{color:#2f9a3a;font-size:15px;}
.claim-chip.pending{background:#f6f7f6;border:1.5px dashed #cfd6d2;color:#8a948f;font-weight:600;}
.claim-chip.pending i{color:#b8c0bb;}
.claim-chip.pending small{color:#a3aba6;font-style:italic;}
.claim-sum{align-self:center;font-size:12px;font-weight:800;color:#b77a3a;background:#fbf1e7;border-radius:12px;padding:3px 9px;}
.claim-sum.full{color:#2f7d1f;background:#eef8e6;}
.claim-chip small{font-weight:600;color:#5f6d66;font-size:13px;}
.scan-setup .ss-stock{border:1px solid #e3ebe9;border-radius:12px;padding:10px 14px;background:#fbfcfc;}
.scan-setup .ss-stock .ss-label{margin-bottom:2px;}
.scan-setup .ss-num{font-size:24px;font-weight:800;color:var(--forest);line-height:1.2;}
.scan-setup .ss-num small{font-size:12.5px;font-weight:600;color:#7c8b86;margin-left:3px;}
.scan-setup .ss-bar{height:6px;border-radius:6px;background:#eef1f0;overflow:hidden;margin-top:6px;}
.scan-setup .ss-bar i{display:block;height:100%;width:0;border-radius:6px;background:linear-gradient(90deg,#58b639,var(--lime));transition:width .5s ease;}
.scan-setup .ss-stock.low .ss-num{color:#b77a3a;}.scan-setup .ss-stock.low .ss-bar i{background:#e0a458;}
.scan-setup .ss-stock.out .ss-num{color:#c0392b;}.scan-setup .ss-stock.out .ss-bar i{background:#c0392b;}
</style>
<div class="scan-setup">
 <div class="ss-head"><i class="bi bi-sliders2"></i>Pengaturan Scan<span class="ss-hint"><i class="bi bi-info-circle me-1"></i>Pastikan souvenir sudah benar sebelum mulai scan.</span></div>
 <div class="ss-grid">
  <div class="ss-field ss-ev"><div class="ss-label"><span class="ss-no">1</span>Event</div><div class="select-wrap"><select id="eventId" class="form-select" data-auto-static data-icon="bi-calendar-event"><?php foreach($events as $e):?><option value="<?=$e['id']?>"><?=e($e['event_name'])?></option><?php endforeach;?></select><i class="bi bi-chevron-down select-arrow"></i></div></div>
  <div class="ss-field ss-main"><div class="ss-label"><span class="ss-no">2</span>Souvenir yang Dibagikan</div><div class="select-wrap ss-souv"><i class="bi bi-gift-fill ss-ico"></i><select id="souvenirId" class="form-select" data-auto-static data-icon="bi-gift-fill"></select><i class="bi bi-chevron-down select-arrow"></i></div><div id="noAllocWarn" class="form-text text-danger d-none mt-1">Belum ada souvenir yang dialokasikan untuk event ini.</div></div>
  <div class="ss-stock" id="ssStock"><div class="ss-label">Belum Diserahkan</div><div class="ss-num"><span id="ssLeft">0</span><small>dari <span id="ssTotal">0</span></small></div><div class="ss-bar"><i id="ssBar"></i></div></div>
 </div>
</div>
<div class="soft-card mt-3 mb-3">
<label class="mb-2">Scanner USB / Input Token</label>
<div class="scan-group"><input id="manualToken" class="form-control form-control-lg" placeholder="Arahkan scanner USB ke sini lalu tembak QR" autocomplete="off" autofocus><button id="manualBtn" class="btn-brand" type="button">Proses</button></div>
<div id="result" class="mt-3 d-none"></div>
<details class="mt-3"><summary class="text-muted" style="cursor:pointer">Gunakan kamera / upload gambar</summary><div id="camwarn" class="cam-note d-none"></div><div id="reader" class="mt-2" style="max-width:520px"></div></details>
</div>
<div class="soft-card">
<h6 class="mb-3 d-flex align-items-center gap-2">Sudah Diambil <span class="count-pill" id="claimedCount">0</span><span class="text-muted small fw-normal ms-1">peserta · 15 terbaru</span></h6>
<div class="table-responsive"><table class="data-table claim-table"><thead><tr><th>Terakhir Ambil</th><th>NIK</th><th>Nama</th><th>Departemen</th><th>Souvenir Diambil</th></tr></thead><tbody id="claimedList"><tr><td colspan="5" class="text-muted">Memuat...</td></tr></tbody></table></div>
</div>
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script><script>
const ALL_SOUVENIRS=<?=json_encode(array_map(fn($s)=>['id'=>$s['id'],'name'=>$s['name'],'stock'=>$s['stock']],$souvenirs))?>;
const ALLOC=<?=json_encode((object)$allocMap)?>;
function esc(s){return (s??'').toString().replace(/[<>&"]/g,c=>({'<':'&lt;','>':'&gt;','&':'&amp;','"':'&quot;'}[c]));}
function dmy(s){if(!s)return '';const [d,t]=s.split(' ');const [y,m,dd]=d.split('-');if(!y||!m||!dd)return s;return dd+'-'+m+'-'+y+(t?' '+t:'');}
function dtCell(s){if(!s)return '';const [d,t]=dmy(s).split(' ');return '<div class="dt-cell"><span class="dt-date">'+esc(d)+'</span>'+(t?'<span class="dt-time">'+esc(t)+'</span>':'')+'</div>';}
function refreshSouvenirOptions(){
 const prevVal=souvenirId.value;
 const alloc=ALLOC[eventId.value];
 const souvenirIds=alloc?Object.keys(alloc).map(Number):[];
 let html;
 if(souvenirIds.length){
  html=ALL_SOUVENIRS.filter(s=>souvenirIds.includes(s.id)).map(s=>{
   const a=alloc[s.id];const sisa=a.allocated-a.claimed;
   return '<option value="'+s.id+'" '+(sisa<=0?'disabled':'')+'>'+esc(s.name)+(sisa<=0?' (habis)':'')+'</option>';
  }).join('');
 }else{
  html='';
 }
 souvenirId.innerHTML=html;
 if([...souvenirId.options].some(o=>o.value===prevVal))souvenirId.value=prevVal;
 document.getElementById('noAllocWarn').classList.toggle('d-none',souvenirIds.length>0);
 souvenirId.disabled=souvenirIds.length===0;
 manualBtn.disabled=souvenirIds.length===0;
 manualToken.disabled=souvenirIds.length===0;
 paintSetup();
}
// Indikator sisa alokasi
function paintSetup(){
 const a=(ALLOC[eventId.value]||{})[Number(souvenirId.value)];
 const left=a?Math.max(a.allocated-a.claimed,0):0,total=a?a.allocated:0;
 document.getElementById('ssLeft').textContent=left;document.getElementById('ssTotal').textContent=total;
 document.getElementById('ssBar').style.width=(total?Math.round(left/total*100):0)+'%';
 const st=document.getElementById('ssStock');st.classList.toggle('out',!!a&&left===0);st.classList.toggle('low',!!a&&left>0&&(left<=5||left/total<=.1));
}
souvenirId.addEventListener('change',paintSetup);
const CLAIMED_RECENT_LIMIT=15;
async function refreshClaimed(){
 try{
  const r=await fetch(BASE+'/reports/souvenir/data?event_id='+eventId.value);
  const data=await r.json();
  // Satu baris per peserta; souvenir yang diambil berjajar ke samping beserta jam ambilnya.
  const byPerson={};
  data.forEach(x=>{const p=byPerson[x.nik]=byPerson[x.nik]||{nik:x.nik,name:x.name,department:x.department,last:'',items:[]};p.items.push(x);if((x.collected_at||'')>p.last)p.last=x.collected_at;});
  const people=Object.values(byPerson).sort((a,b)=>b.last.localeCompare(a.last));
  document.getElementById('claimedCount').textContent=people.length;
  const recent=people.slice(0,CLAIMED_RECENT_LIMIT);
  // Semua souvenir yang dialokasikan ke event ditampilkan: sudah diambil = centang hijau + jam, belum = abu-abu.
  const allocIds=Object.keys(ALLOC[eventId.value]||{}).map(Number);
  const allocList=ALL_SOUVENIRS.filter(s=>allocIds.includes(Number(s.id)));
  const chips=p=>{
   const taken={};p.items.forEach(x=>{taken[x.souvenir_id]=x;});
   const done=allocList.filter(s=>taken[s.id]).length;
   const list=allocList.map(s=>({t:taken[s.id]||null,name:s.name}))
    .concat(p.items.filter(x=>!allocIds.includes(Number(x.souvenir_id))).map(x=>({t:x,name:x.souvenir_name})));
   return list.map(c=>c.t
    ?'<span class="claim-chip"><i class="bi bi-check-circle-fill"></i>'+esc(c.name)+((c.t.quantity||1)>1?' ×'+c.t.quantity:'')+'<small>'+esc((c.t.collected_at||'').slice(11,19))+'</small></span>'
    :'<span class="claim-chip pending"><i class="bi bi-circle"></i>'+esc(c.name)+'<small>Belum</small></span>').join('')
    +(allocList.length>1?'<span class="claim-sum'+(done>=allocList.length?' full':'')+'">'+done+'/'+allocList.length+'</span>':'');
  };
  document.getElementById('claimedList').innerHTML=recent.length?recent.map(p=>'<tr><td>'+dtCell(p.last)+'</td><td>'+esc(p.nik)+'</td><td>'+esc(p.name)+'</td><td>'+esc(p.department)+'</td><td><div class="claim-chips">'+chips(p)+'</div></td></tr>').join(''):'<tr><td colspan="5" class="text-muted">Belum ada yang mengambil souvenir.</td></tr>';
  const claimedBySouvenir={};
  data.forEach(x=>{claimedBySouvenir[x.souvenir_id]=(claimedBySouvenir[x.souvenir_id]||0)+(x.quantity||1);});
  const alloc=ALLOC[eventId.value];
  if(alloc){Object.keys(alloc).forEach(sid=>{alloc[sid].claimed=claimedBySouvenir[sid]||0;});refreshSouvenirOptions();}
 }catch(e){}
}
eventId.addEventListener('change',()=>{claimedPage=1;refreshSouvenirOptions();refreshClaimed();});
refreshSouvenirOptions();
refreshClaimed();
Live.on(['souv'],refreshClaimed);
Live.on(['evt','alloc'],()=>Live.notice('Ada perubahan event atau alokasi souvenir. Muat ulang untuk memperbarui pilihan.'));
const out=document.getElementById('result');function show(j){showScanResult(out,j,{ok:'Souvenir Dapat Diserahkan',warn:'Souvenir Telah Diterima',err:'Pengambilan Ditolak'});}
const CSRF_TOKEN=<?=json_encode(csrf_token())?>;
let busy=false;async function success(text){if(busy)return;if(!souvenirId.value){show({ok:false,message:'Belum ada souvenir yang dialokasikan untuk event ini. Alokasikan dulu di halaman Event.'});return;}busy=true;const f=new FormData();f.append('_csrf',CSRF_TOKEN);f.append('event_id',eventId.value);f.append('souvenir_id',souvenirId.value);f.append('token',text);try{show(await (await fetch(BASE+'/api/scanner/souvenir',{method:'POST',body:f})).json());refreshClaimed();}finally{setTimeout(()=>busy=false,1200);manualToken.focus();}}
manualBtn.onclick=()=>{const t=manualToken.value.trim();if(t){success(t);manualToken.value='';}};
manualToken.addEventListener('keydown',ev=>{if(ev.key==='Enter'){ev.preventDefault();manualBtn.click();}});
manualToken.focus();
document.addEventListener('click',()=>{const t=document.activeElement.tagName;if(t!=='INPUT'&&t!=='TEXTAREA'&&t!=='SELECT')manualToken.focus();});
new Html5QrcodeScanner('reader',{fps:10,qrbox:250},false).render(success);
Html5Qrcode.getCameras().then(d=>{if(!d.length)throw 0;}).catch(()=>{camwarn.classList.remove('d-none');camwarn.innerHTML='<i class="bi bi-info-circle"></i><span>Kamera tidak tersedia di perangkat ini. Gunakan scanner USB pada kolom di atas, atau pilih <strong>upload gambar</strong> QR.</span>';});
</script><?php require __DIR__.'/../layout/footer.php';