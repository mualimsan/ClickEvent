<?php require __DIR__.'/../layout/header.php';?>
<div class="page-head"><h2 class="page-title d-flex align-items-center gap-2"><span class="page-icon c-orange"><i class="bi bi-clipboard-data"></i></span>Laporan Kehadiran</h2><button type="button" class="btn-brand" id="exportBtn"><i class="bi bi-download"></i><span id="exportLbl">Ekspor Excel</span></button></div>
<div class="report-toolbar">
 <div class="rt-row">
  <div class="rt-field rt-event"><label class="rt-label" for="eventSel">Event</label><div class="select-wrap"><select id="eventSel" class="form-select"><?php foreach($events as $e):?><option value="<?=$e['id']?>" <?=$event==$e['id']?'selected':''?>><?=e($e['event_name'])?> — <?=dmy($e['event_date'])?></option><?php endforeach;?></select><i class="bi bi-chevron-down select-arrow"></i></div></div>
  <div class="rt-field rt-grow"><label class="rt-label" for="searchQ">Cari</label><div class="search-wrap"><i class="bi bi-search"></i><input type="text" id="searchQ" class="search-input w-100" placeholder="Nama, NIK, section, departemen, atau souvenir"></div></div>
 </div>
 <div class="rt-field"><span class="rt-label">Status Kehadiran</span>
  <div class="seg" id="statusSeg" role="group" aria-label="Filter status kehadiran">
   <button type="button" data-st="all" class="on">Semua <span class="n" id="cntAll">0</span></button>
   <button type="button" data-st="hadir"><span class="dot"></span>Hadir <span class="n" id="cntHadir">0</span></button>
   <button type="button" data-st="belum"><span class="dot"></span>Belum Hadir <span class="n" id="cntBelum">0</span></button>
  </div>
 </div>
 <div class="rt-hint"><i class="bi bi-info-circle"></i>Event berstatus DRAFT tidak ditampilkan di laporan.</div>
</div>
<style>.souv-taken{display:inline-flex;align-items:center;gap:6px;color:var(--accent);font-weight:600;font-size:13px;}.souv-taken i{font-size:13px;}</style>
<div class="report-summary" id="summary"></div>
<div class="data-card"><div class="table-responsive"><table class="data-table text-nowrap"><thead><tr><th>No</th><th>NIK</th><th>Nama</th><th>Section</th><th>Departemen</th><th>Status Email</th><th>Status Kehadiran</th><th>Waktu Check-in</th><th>Souvenir Diambil</th></tr></thead><tbody id="rows"></tbody></table></div><div id="attPagination" class="d-flex align-items-center gap-2 p-3"></div></div>
<script>
const EVENT_ID=<?=(int)$event?>;
const EVENT_NAME=<?=json_encode($event?(array_values(array_filter($events,fn($x)=>(int)$x['id']===(int)$event))[0]['event_name']??'Event terpilih'):'Belum ada event')?>;
const CSRF=<?=json_encode(csrf_token())?>;
const PAGE_SIZE=15;
let attPage=1,attStatus='all',attData=<?=json_encode($rows,JSON_HEX_TAG|JSON_HEX_AMP)?>,shown=[];
const STATUS_LABEL={hadir:'Hadir',belum:'Belum Hadir'};
function esc(s){return (s??'').toString().replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));}
function dmy(s){if(!s)return '';const [d,t]=s.split(' ');const [y,m,dd]=d.split('-');if(!y||!m||!dd)return s;return dd+'-'+m+'-'+y+(t?' '+t:'');}
function dtCell(s){if(!s)return '';const [d,t]=dmy(s).split(' ');return '<div class="dt-cell"><span class="dt-date">'+esc(d)+'</span>'+(t?'<span class="dt-time">'+esc(t)+'</span>':'')+'</div>';}
function renderPagination(container,totalPages,page,onGo){
 container.innerHTML='';
 if(totalPages<=1)return;
 const mk=(label,p,disabled)=>{const b=document.createElement('button');b.type='button';b.className='btn-outline-brand';b.style.padding='6px 12px';b.style.fontSize='12.5px';b.textContent=label;b.disabled=disabled;if(disabled)b.style.opacity='.4';b.onclick=()=>onGo(p);return b;};
 container.appendChild(mk('«',page-1,page<=1));
 container.appendChild(Object.assign(document.createElement('span'),{className:'small text-muted mx-1',textContent:'Halaman '+page+' / '+totalPages}));
 container.appendChild(mk('»',page+1,page>=totalPages));
}
function query(){return (document.getElementById('searchQ').value||'').trim();}
function matchesQuery(x,q){
 if(!q)return true;
 return [x.nik,x.name,x.section,x.department,x.souvenirs].some(v=>(v||'').toString().toLowerCase().includes(q));
}
function renderSummary(){
 const q=query(),parts=['Event: <b>'+esc(EVENT_NAME)+'</b>'];
 if(attStatus!=='all')parts.push('Status: <b>'+STATUS_LABEL[attStatus]+'</b>');
 if(q)parts.push('Cari: <b>"'+esc(q)+'"</b>');
 const active=attStatus!=='all'||q!=='';
 document.getElementById('summary').innerHTML='<span>Menampilkan <b>'+shown.length+'</b> dari <b>'+attData.length+'</b> peserta</span><span class="rs-sep">·</span>'+parts.join('<span class="rs-sep">·</span>')+(active?'<button type="button" class="rs-reset" id="resetBtn"><i class="bi bi-x-circle"></i>Reset filter</button>':'');
 const rb=document.getElementById('resetBtn');if(rb)rb.onclick=()=>{document.getElementById('searchQ').value='';setStatus('all');};
 const eb=document.getElementById('exportBtn');
 document.getElementById('exportLbl').textContent='Ekspor Excel ('+shown.length+')';
 eb.disabled=shown.length===0;eb.title=shown.length?'Ekspor '+shown.length+' baris yang sedang tampil':'Tidak ada data untuk diekspor';
}
function renderAttPage(){
 const q=query().toLowerCase();
 const byQuery=attData.filter(x=>matchesQuery(x,q));
 const hadir=byQuery.filter(x=>x.attended).length;
 document.getElementById('cntAll').textContent=byQuery.length;
 document.getElementById('cntHadir').textContent=hadir;
 document.getElementById('cntBelum').textContent=byQuery.length-hadir;
 shown=byQuery.filter(x=>attStatus==='all'||(attStatus==='hadir'?x.attended:!x.attended));
 const totalPages=Math.max(1,Math.ceil(shown.length/PAGE_SIZE));
 if(attPage>totalPages)attPage=totalPages;
 const start=(attPage-1)*PAGE_SIZE;
 const pageRows=shown.slice(start,start+PAGE_SIZE);
 document.getElementById('rows').innerHTML=pageRows.length?pageRows.map((x,i)=>'<tr><td>'+(start+i+1)+'</td><td>'+esc(x.nik)+'</td><td class="fw-semibold">'+esc(x.name)+'</td><td>'+esc(x.section)+'</td><td>'+esc(x.department)+'</td><td>'+esc(x.email_status==='PENDING'?'BELUM DIKIRIM':x.email_status)+'</td><td>'+(x.attended?'<span class="badge-pill" style="background:#1f7a4c">HADIR</span>':'<span class="badge-pill" style="background:#6b7570">BELUM HADIR</span>')+'</td><td>'+dtCell(x.checkin_at)+'</td><td>'+(x.souvenirs?'<span class="souv-taken"><i class="bi bi-gift"></i>'+esc(x.souvenirs)+'</span>':'<span class="text-muted">-</span>')+'</td></tr>').join(''):'<tr><td colspan="9" class="text-muted text-center py-4">Tidak ada data yang cocok dengan filter.</td></tr>';
 renderPagination(document.getElementById('attPagination'),totalPages,attPage,p=>{attPage=p;renderAttPage();});
 renderSummary();
}
function setStatus(st){
 attStatus=st;attPage=1;
 document.querySelectorAll('#statusSeg button').forEach(x=>x.classList.toggle('on',x.dataset.st===st));
 renderAttPage();
}
document.getElementById('searchQ').addEventListener('input',()=>{attPage=1;renderAttPage();});
document.querySelectorAll('#statusSeg button').forEach(b=>b.addEventListener('click',()=>setStatus(b.dataset.st)));
document.getElementById('eventSel').addEventListener('change',e=>{location.href=BASE+'/reports/attendance?event_id='+e.target.value;});
// Ekspor: kirim id baris yang sedang tampil supaya isi Excel sama persis dengan tabel.
document.getElementById('exportBtn').addEventListener('click',()=>{
 if(!shown.length)return;
 const f=document.createElement('form');f.method='post';f.action=BASE+'/reports/attendance/export';f.style.display='none';
 const add=(k,v)=>{const i=document.createElement('input');i.type='hidden';i.name=k;i.value=v;f.appendChild(i);};
 add('_csrf',CSRF);add('event_id',EVENT_ID);add('status',attStatus);add('q',query());add('ids',shown.map(x=>x.id).join(','));
 document.body.appendChild(f);f.submit();f.remove();
});
async function refreshAttendance(){
 try{const r=await fetch(BASE+'/reports/attendance/data?event_id='+EVENT_ID);attData=await r.json();renderAttPage();}catch(e){}
}
renderAttPage();
Live.on(['att','inv','evt','emp','souv'],refreshAttendance);
</script>
<?php require __DIR__.'/../layout/footer.php';
