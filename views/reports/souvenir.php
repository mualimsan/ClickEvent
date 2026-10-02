<?php require __DIR__.'/../layout/header.php';
?>
<style>
.stock-table td{vertical-align:middle;}
.stock-table>thead>tr>th,.stock-table>tbody>tr>td{padding-left:10px!important;padding-right:10px!important;}
.stock-table>thead>tr>th{white-space:nowrap;font-size:10.5px;}
.stock-table td:nth-child(3){white-space:nowrap;}
.stock-table td:nth-child(2){white-space:nowrap;}
.stock-table .num{text-align:right;font-variant-numeric:tabular-nums;white-space:nowrap;}
.stock-table th.num{text-align:right;}
.stock-bar{height:6px;border-radius:6px;background:#eef1f0;overflow:hidden;min-width:90px;margin-top:5px;}
.stock-bar i{display:block;height:100%;border-radius:6px;background:linear-gradient(90deg,#58b639,var(--lime));}
.kond{display:inline-flex;align-items:center;gap:6px;font-size:11px;font-weight:700;letter-spacing:.03em;padding:4px 10px;border-radius:20px;}
.kond::before{content:"";width:7px;height:7px;border-radius:50%;background:currentColor;}
.kond-Aman{background:#eef8e6;color:#2f7d1f;}.kond-Menipis{background:#fbf1e7;color:#b76e1d;}.kond-Habis{background:#fbecea;color:#b42318;}.kond-Kurang{background:#fbecea;color:#b42318;}
.section-title{display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;margin:0 0 12px;}
.section-title h3{font-size:15px;font-weight:700;margin:0;display:flex;align-items:center;gap:8px;}
.section-title h3 i{color:var(--accent);}
.legend{font-size:12px;color:#7c8b86;display:flex;gap:14px;flex-wrap:wrap;margin-top:10px;}
.legend b{color:#3d453f;}
.pesan-toggle{display:inline-flex;align-items:center;gap:4px;margin-top:4px;border:1px solid #dfe6e3;background:#f6f8f7;color:#4b5563;font-size:11px;font-weight:600;border-radius:20px;padding:2px 9px;cursor:pointer;white-space:nowrap;transition:background .15s,border-color .15s;}
.pesan-toggle:hover{background:#eef8e6;border-color:#cfe8bf;color:var(--accent);}
.pesan-toggle i{font-size:10px;transition:transform .2s;}
.pesan-toggle[aria-expanded="true"]{background:#eef8e6;border-color:#cfe8bf;color:var(--accent);}
.pesan-toggle[aria-expanded="true"] i{transform:rotate(180deg);}
.stock-table tr.pesan-row>td{background:#fafcfb;padding:0 16px 14px;border-bottom:1px solid #eef0ef;}
.pesan-panel{border:1px solid #e3ebe9;border-radius:10px;background:#fff;max-width:680px;margin-left:auto;overflow:hidden;}
.pesan-panel .pp-head{display:flex;justify-content:space-between;gap:10px;padding:9px 14px;font-size:12px;font-weight:700;color:var(--forest);background:#f6f8f7;border-bottom:1px solid #eef0ef;}
.pesan-panel .pp-head span{font-weight:500;color:#7c8b86;}
.pesan-panel .pp-body{max-height:220px;overflow-y:auto;}
.pesan-panel table{width:100%;border-collapse:collapse;font-size:12.5px;}
.pesan-panel td{padding:7px 14px;border-bottom:1px solid #f1f3f2;}
.pesan-panel tr:last-child td{border-bottom:0;}
.pesan-panel thead th{padding:7px 14px;font-size:10.5px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;color:#7c8b86;border-bottom:1px solid #eef0ef;background:#fbfcfc;white-space:nowrap;}
.pesan-panel tfoot td{font-weight:800;color:var(--forest);background:#f6f8f7;border-top:1px solid #e3ebe9;}
.pesan-panel .st{font-size:10px;font-weight:700;letter-spacing:.03em;border-radius:5px;padding:2px 6px;}
.pesan-panel .st-PUBLISHED{background:#eef8e6;color:#2f7d1f;}.pesan-panel .st-DRAFT{background:#eef1f0;color:#5b6663;}.pesan-panel .st-CLOSED{background:#e9ecef;color:#1c2a26;}.pesan-panel .st-CANCELLED{background:#fdeceb;color:#a3372b;}
.souv-dd{min-width:240px;}
.ps-table thead th{vertical-align:middle;}
.ps-table thead tr.ps-sub th{font-size:10.5px;padding-top:6px;padding-bottom:8px;color:#7a857f;}
.ps-table th.ps-grp{text-align:center;color:var(--forest);border-left:1px solid #e3e9e6;border-bottom:1px solid #e3e9e6;padding-bottom:6px;}
.ps-table th.ps-grp i{color:var(--accent);margin-right:5px;}
.ps-table .ps-total{display:inline-block;margin-left:6px;min-width:24px;padding:1px 8px;border-radius:12px;background:var(--forest);color:var(--lime-bright);font-size:11.5px;font-weight:800;letter-spacing:0;vertical-align:1px;}
.ps-table .g-start{border-left:1px solid #eef1ef;}
.ps-table td.ps-q{text-align:center;font-weight:700;color:var(--forest);}
.ps-table th.ps-q{text-align:center;}
.kond-Sebagian{background:#eaf2fb;color:#1d5fb7;}
.souv-dd .souv-dd-ico{position:absolute;left:13px;top:50%;transform:translateY(-50%);color:var(--accent);font-size:15px;pointer-events:none;z-index:1;}
.souv-dd select{padding-left:38px!important;font-weight:600;}
.souv-dd select.active{border-color:var(--lime)!important;background:#f7fcf3!important;box-shadow:0 0 0 3px rgba(144,227,101,.18);color:var(--forest);}
</style>

<div class="page-head"><h2 class="page-title"><span class="page-icon c-red"><i class="bi bi-box-seam"></i></span>Laporan Souvenir</h2><a class="btn-brand" href="<?=BASE_URL?>/reports/souvenir/export?mode=stock&event_id=<?=(int)$event?>"><i class="bi bi-download"></i>Ekspor Stok</a></div>

<div class="report-toolbar">
 <div class="rt-row">
  <div class="rt-field rt-event"><label class="rt-label" for="eventSel">Tampilkan</label><div class="select-wrap"><select id="eventSel" class="form-select"><option value="0">Semua (posisi stok gudang)</option><?php foreach($events as $e):?><option value="<?=$e['id']?>" <?=$event==$e['id']?'selected':''?>>Event: <?=e($e['event_name'])?> — <?=dmy($e['event_date'])?></option><?php endforeach;?></select><i class="bi bi-chevron-down select-arrow"></i></div></div>
 </div>
 <div class="rt-hint"><i class="bi bi-info-circle"></i><span><?=$event?'Menampilkan alokasi souvenir untuk event ini dan jumlah yang sudah diambil peserta.':'Hitungan stok: <strong>Tersedia = Stok Awal − Booked Event</strong> · <strong>Sisa Event = Booked Event − Sudah Diambil</strong> · <strong>Stok Akhir = Tersedia + Sisa Event</strong>. Dihitung dari event aktif (DRAFT/PUBLISHED); event yang sudah selesai atau lewat tanggalnya tidak ikut dihitung.'?> Event berstatus DRAFT tidak muncul di pilihan.</span></div>
</div>


<div class="soft-card mb-4">
 <div class="section-title"><h3><i class="bi bi-boxes"></i><?=$event?'Alokasi per Souvenir':'Stok per Souvenir'?></h3></div>
 <div class="data-card" data-live="stocktable"><div class="table-responsive"><table class="data-table stock-table">
 <?php if($event):?>
  <thead><tr><th>No</th><th>Kode</th><th>Souvenir</th><th class="num">Total Alokasi</th><th class="num">Telah Diserahkan</th><th class="num">Belum Diserahkan</th><th>Realisasi</th><th>Kondisi</th></tr></thead><tbody>
  <?php if(!$stock):?><tr><td colspan="8" class="text-muted text-center py-4">Belum ada souvenir yang dialokasikan ke event ini.</td></tr><?php endif;?>
  <?php foreach($stock as $i=>$r):?><tr><td><?=$i+1?></td><td><?=e($r['code'])?></td><td class="fw-semibold"><?=e($r['name'])?></td><td class="num"><?=number_format($r['alokasi'],0,',','.')?></td><td class="num"><?=number_format($r['diambil'],0,',','.')?></td><td class="num fw-semibold"><?=number_format($r['sisa'],0,',','.')?></td><td><div class="small text-muted"><?=$r['persen']?>%</div><div class="stock-bar"><i style="width:<?=min($r['persen'],100)?>%"></i></div></td><td><span class="kond kond-<?=$r['kondisi']?>"><?=strtoupper($r['kondisi'])?></span></td></tr><?php endforeach;?>
 <?php else:?>
  <thead><tr><th>No</th><th>Kode</th><th>Souvenir</th><th class="num" title="Jumlah souvenir yang dimiliki">Stok Awal</th><th class="num" title="Jumlah yang dialokasikan untuk event (termasuk yang sudah diambil)">Booked Event</th><th class="num" title="Stok Awal dikurangi Booked Event">Tersedia</th><th class="num" title="Sudah diserahkan ke peserta">Sudah Diambil</th><th class="num" title="Booked Event dikurangi Sudah Diambil">Sisa Event</th><th class="num" title="Tersedia ditambah Sisa Event (stok fisik di gudang)">Stok Akhir</th><th>Kondisi</th></tr></thead><tbody>
  <?php if(!$stock):?><tr><td colspan="10" class="text-muted text-center py-4">Belum ada data souvenir.</td></tr><?php endif;?>
  <?php $n=fn($v)=>number_format($v,0,',','.');foreach($stock as $i=>$r):?><tr><td><?=$i+1?></td><td><?=e($r['code'])?></td><td class="fw-semibold"><?=e($r['name'])?><?=$r['status']!=='ACTIVE'?' <span class="text-muted small fw-normal">(nonaktif)</span>':''?></td>
  <td class="num fw-semibold"><?=$n($r['stok_awal'])?></td>
  <td class="num"><?=$n($r['booked'])?><?php if($r['detail']):?><br><button type="button" class="pesan-toggle" aria-expanded="false" data-target="pesan-<?=$i?>"><?=count($r['detail'])?> event<i class="bi bi-chevron-down"></i></button><?php endif;?></td>
  <td class="num fw-bold <?=$r['tersedia']<0?'text-danger':''?>"><?=$n($r['tersedia'])?></td>
  <td class="num text-muted"><?=$n($r['diambil'])?></td>
  <td class="num"><?=$n($r['sisa_event'])?></td>
  <td class="num fw-semibold"><?=$n($r['stok_akhir'])?></td>
  <td><span class="kond kond-<?=$r['kondisi']?>"><?=strtoupper($r['kondisi'])?></span></td></tr>
  <?php if($r['detail']):?><tr class="pesan-row" id="pesan-<?=$i?>" hidden><td colspan="10"><div class="pesan-panel"><div class="pp-head">Rincian per Event · <?=e($r['name'])?><span><?=count($r['detail'])?> event</span></div><div class="pp-body"><table><thead><tr><th>Event</th><th>Status</th><th class="text-end">Booked</th><th class="text-end">Sudah Diambil</th><th class="text-end">Sisa Event</th></tr></thead><tbody><?php foreach($r['detail'] as $d):?><tr><td><?=e($d['event'])?></td><td><span class="st st-<?=$d['status']?>"><?=$d['status']?></span></td><td class="text-end"><?=$n($d['booked'])?></td><td class="text-end text-muted"><?=$n($d['claimed'])?></td><td class="text-end fw-bold"><?=$n($d['sisa'])?></td></tr><?php endforeach;?></tbody><?php if(count($r['detail'])>1):?><tfoot><tr><td colspan="2">Total</td><td class="text-end"><?=$n($r['booked'])?></td><td class="text-end"><?=$n($r['diambil'])?></td><td class="text-end"><?=$n($r['sisa_event'])?></td></tr></tfoot><?php endif;?></table></div></div></td></tr><?php endif;?>
  <?php endforeach;?>
 <?php endif;?>
 </tbody></table></div></div>
</div>

<div class="soft-card">
 <div class="section-title"><h3><i class="bi bi-people"></i>Pengambilan Souvenir Peserta</h3><?php if($event):?><button type="button" class="btn-outline-brand c-excel" id="psExportBtn"><i class="bi bi-file-earmark-excel-fill"></i><span id="psExportLbl">Ekspor</span></button><?php endif;?></div>
 <?php if(!$event):?>
 <div class="text-muted" style="font-size:13px;padding:6px 2px 4px;"><i class="bi bi-info-circle me-1"></i>Pilih satu event pada pilihan <strong>Tampilkan</strong> di atas untuk melihat peserta yang sudah dan belum mengambil souvenir.</div>
 <?php else:?>
 <div class="d-flex flex-wrap gap-2 align-items-end mb-3">
  <div class="search-wrap" style="flex:1 1 280px;max-width:420px"><i class="bi bi-search"></i><input type="text" id="psQ" class="search-input w-100" placeholder="Cari nama, NIK, departemen, atau souvenir"></div>
  <div class="select-wrap souv-dd"><i class="bi bi-gift souv-dd-ico"></i><select id="psSouv" class="form-select" aria-label="Filter souvenir"></select><i class="bi bi-chevron-down select-arrow"></i></div>
 </div>
 <div class="report-summary" id="psSummary"></div>
 <div class="data-card"><div class="table-responsive"><table class="data-table text-nowrap ps-table"><thead id="psHead"></thead><tbody id="psRows"></tbody></table></div><div id="psPagination" class="d-flex align-items-center gap-2 p-3"></div></div>
 <div class="legend"><span>Hanya menampilkan peserta yang <b>sudah check-in</b>. Peserta yang belum hadir dapat dilihat di Laporan Kehadiran.</span></div>
 <?php endif;?>
</div>

<script type="application/json" id="pesertaJson" data-live="pesertadata"><?=json_encode($peserta,JSON_HEX_TAG|JSON_HEX_AMP)?></script>
<script>
const EVENT_ID=<?=(int)$event?>;
const CSRF=<?=json_encode(csrf_token())?>;
const PAGE_SIZE=15;
function esc(s){return (s??'').toString().replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));}
function dmy(s){if(!s)return '';const [d,t]=s.split(' ');const [y,m,dd]=d.split('-');if(!y||!m||!dd)return s;return dd+'-'+m+'-'+y+(t?' '+t:'');}
function dtCell(s){if(!s)return '';const [d,t]=dmy(s).split(' ');return '<div class="dt-cell"><span class="dt-date">'+esc(d)+'</span>'+(t?'<span class="dt-time">'+esc(t)+'</span>':'')+'</div>';}
function renderPagination(container,totalPages,pg,onGo,total){drawPager(container,pg,totalPages,onGo,total,PAGE_SIZE);}
document.getElementById('eventSel').addEventListener('change',e=>{location.href=BASE+'/reports/souvenir'+(e.target.value!=='0'?'?event_id='+e.target.value:'');});
// ---- Status Pengambilan Peserta (peserta hadir: sudah / belum ambil souvenir) ----
// Kolom souvenir mengikuti souvenir yang dialokasikan ke event (psItems), jadi otomatis bertambah saat alokasi bertambah.
let psData=[],psItems=[],psSouv='',psPage=1,psShown=[],psItemsSig='';
function psLoad(){const d=JSON.parse(document.getElementById('pesertaJson').textContent);psData=d.rows||[];psItems=d.items||[];}
// Status terhadap souvenir yang ditampilkan: semua diambil / sebagian / belum sama sekali.
function psStatus(x,items){
 const n=items.filter(it=>x.take&&x.take[it.id]).length;
 if(!n)return '<span class="kond kond-Menipis">BELUM AMBIL</span>';
 if(n>=items.length)return '<span class="kond kond-Aman">SUDAH AMBIL</span>';
 return '<span class="kond kond-Sebagian">SEBAGIAN ('+n+'/'+items.length+')</span>';
}
function psSouvOptions(){
 const sel=document.getElementById('psSouv');
 if(psSouv&&!psItems.some(it=>String(it.id)===psSouv))psSouv='';
 const sig=psItems.map(it=>it.id+':'+it.name).join('|');
 if(sig!==psItemsSig){psItemsSig=sig;sel.innerHTML='<option value="">Semua souvenir ('+psItems.length+')</option>'+psItems.map(it=>'<option value="'+it.id+'">'+esc(it.name)+'</option>').join('');}
 sel.value=psSouv;sel.classList.toggle('active',psSouv!=='');
 sel.closest('.select-wrap').style.display=psItems.length>1?'':'none';
}
// Total souvenir yang sudah diambil (seluruh peserta hadir di event ini), untuk judul kolom per souvenir.
function psTaken(id){return psData.reduce((n,x)=>n+((x.take&&x.take[id])?x.take[id].q:0),0);}
function psRender(){
 if(!document.getElementById('psRows'))return;
 psSouvOptions();
 const items=psSouv?psItems.filter(it=>String(it.id)===psSouv):psItems;
 const q=(document.getElementById('psQ').value||'').trim().toLowerCase();
 psShown=psData.filter(x=>!q||[x.nik,x.name,x.department,x.souvenirs].some(v=>(v||'').toString().toLowerCase().includes(q)));
 const rs=items.length?' rowspan="2"':'';
 document.getElementById('psHead').innerHTML='<tr><th'+rs+'>No</th><th'+rs+'>NIK</th><th'+rs+'>Nama</th><th'+rs+'>Departemen</th><th'+rs+'>Waktu Check-in</th><th'+rs+'>Status</th>'
  +items.map(it=>'<th colspan="2" class="ps-grp"><i class="bi bi-gift"></i>'+esc(it.name)+' <span class="ps-total" title="Total sudah diambil">'+psTaken(it.id)+'</span></th>').join('')+'</tr>'
  +(items.length?'<tr class="ps-sub">'+items.map(()=>'<th class="ps-q g-start">Jumlah</th><th>Waktu Ambil</th>').join('')+'</tr>':'');
 const total=Math.max(1,Math.ceil(psShown.length/PAGE_SIZE));if(psPage>total)psPage=total;
 const start=(psPage-1)*PAGE_SIZE,rows=psShown.slice(start,start+PAGE_SIZE);
 const dash='<span class="text-muted">-</span>';
 document.getElementById('psRows').innerHTML=rows.length?rows.map((x,i)=>'<tr><td>'+(start+i+1)+'</td><td>'+esc(x.nik)+'</td><td class="fw-semibold">'+esc(x.name)+'</td><td>'+esc(x.department)+'</td><td>'+dtCell(x.checkin_at)+'</td><td>'+psStatus(x,items)+'</td>'
  +items.map(it=>{const t=x.take&&x.take[it.id];return '<td class="ps-q g-start">'+(t?t.q:dash)+'</td><td>'+(t?dtCell(t.at):dash)+'</td>';}).join('')
  +'</tr>').join('')
  :'<tr><td colspan="'+(6+items.length*2)+'" class="text-muted text-center py-4">'+(psData.length?'Tidak ada data yang cocok dengan pencarian.':'Belum ada peserta yang check-in di event ini.')+'</td></tr>';
 renderPagination(document.getElementById('psPagination'),total,psPage,p=>{psPage=p;psRender();},psShown.length);
 const parts=[];if(psSouv&&items.length)parts.push('Souvenir: <b>'+esc(items[0].name)+'</b>');const qq=document.getElementById('psQ').value.trim();if(qq)parts.push('Cari: <b>"'+esc(qq)+'"</b>');
 document.getElementById('psSummary').innerHTML='<span>Menampilkan <b>'+psShown.length+'</b> dari <b>'+psData.length+'</b> peserta hadir</span>'+(parts.length?'<span class="rs-sep">·</span>'+parts.join('<span class="rs-sep">·</span>')+'<button type="button" class="rs-reset" id="psReset"><i class="bi bi-x-circle"></i>Reset filter</button>':'');
 const rb=document.getElementById('psReset');if(rb)rb.onclick=()=>{document.getElementById('psQ').value='';psSouv='';psPage=1;psRender();};
 const eb=document.getElementById('psExportBtn');document.getElementById('psExportLbl').textContent='Ekspor ('+psShown.length+')';eb.disabled=psShown.length===0;
}
if(document.getElementById('psRows')){
 document.getElementById('psQ').addEventListener('input',()=>{psPage=1;psRender();});
 document.getElementById('psSouv').addEventListener('change',e=>{psSouv=e.target.value;psPage=1;psRender();});
 document.getElementById('psExportBtn').addEventListener('click',()=>{
  if(!psShown.length)return;
  const f=document.createElement('form');f.method='post';f.action=BASE+'/reports/souvenir/export';f.style.display='none';
  const add=(k,v)=>{const i=document.createElement('input');i.type='hidden';i.name=k;i.value=v;f.appendChild(i);};
  add('_csrf',CSRF);add('event_id',EVENT_ID);add('mode','status');add('souvenir',psSouv);add('q',document.getElementById('psQ').value.trim());add('ids',psShown.map(x=>x.id).join(','));
  document.body.appendChild(f);f.submit();f.remove();
 });
}
psLoad();psRender();
document.addEventListener('click',e=>{
 const b=e.target.closest('.pesan-toggle');if(!b)return;
 const row=document.getElementById(b.dataset.target);if(!row)return;
 const open=row.hidden;row.hidden=!open;b.setAttribute('aria-expanded',open?'true':'false');
});
// Real-time: stok, alokasi, dan status peserta ikut diperbarui
Live.on(['souv','stock','alloc','evt','emp','inv','att'],async()=>{await Live.swap(['stocktable','pesertadata']);psLoad();psRender();});
</script>
<?php require __DIR__.'/../layout/footer.php';
