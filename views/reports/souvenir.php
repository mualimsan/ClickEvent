<?php require __DIR__.'/../layout/header.php';
?>
<style>
.stock-kpis{display:flex;align-items:stretch;gap:8px;margin-bottom:16px;flex-wrap:wrap;}
.stock-kpis .stock-kpi{flex:1 1 150px;min-width:0;}
.stock-kpis .k-op{flex:none;align-self:center;color:#b5bcb8;font-size:14px;display:flex;align-items:center;justify-content:center;}
.stock-kpis .k-sep{flex:none;align-self:stretch;width:1px;background:#e3e7e5;margin:0 6px;}
.stock-kpi.k-result{border:2px solid var(--lime);background:#f7fcf3;}
@media(max-width:900px){.stock-kpis .k-op,.stock-kpis .k-sep{display:none;}.stock-kpis .stock-kpi{flex:1 1 45%;}}
.stock-kpi{background:#fff;border:1px solid #e7e9e6;border-radius:14px;padding:14px 16px;display:flex;gap:12px;align-items:flex-start;}
.stock-kpi .k-ico{width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:17px;flex:none;}
.stock-kpi .k-lbl{font-size:12px;color:#6b7570;font-weight:600;}
.stock-kpi .k-val{font-size:24px;font-weight:800;color:var(--ink);line-height:1.2;}
.stock-kpi .k-sub{font-size:11.5px;color:#9aa19c;margin-top:2px;}
.k-blue .k-ico{background:#eaf2f7;color:#4a7fa8;}.k-amber .k-ico{background:#fbf1e7;color:#b77a3a;}.k-green .k-ico{background:#eef8e6;color:#3f8f2a;}.k-teal .k-ico{background:#e6f0f2;color:var(--forest);}
.stock-table td{vertical-align:middle;}
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
.pesan-panel{border:1px solid #e3ebe9;border-radius:10px;background:#fff;max-width:560px;margin-left:auto;overflow:hidden;}
.pesan-panel .pp-head{display:flex;justify-content:space-between;gap:10px;padding:9px 14px;font-size:12px;font-weight:700;color:var(--forest);background:#f6f8f7;border-bottom:1px solid #eef0ef;}
.pesan-panel .pp-head span{font-weight:500;color:#7c8b86;}
.pesan-panel .pp-body{max-height:220px;overflow-y:auto;}
.pesan-panel table{width:100%;border-collapse:collapse;font-size:12.5px;}
.pesan-panel td{padding:7px 14px;border-bottom:1px solid #f1f3f2;}
.pesan-panel tr:last-child td{border-bottom:0;}
.pesan-panel .st{font-size:10px;font-weight:700;letter-spacing:.03em;border-radius:5px;padding:2px 6px;}
.pesan-panel .st-PUBLISHED{background:#eef8e6;color:#2f7d1f;}.pesan-panel .st-DRAFT{background:#eef1f0;color:#5b6663;}
.souv-dd{min-width:240px;}
.souv-dd .souv-dd-ico{position:absolute;left:13px;top:50%;transform:translateY(-50%);color:var(--accent);font-size:15px;pointer-events:none;z-index:1;}
.souv-dd select{padding-left:38px!important;font-weight:600;}
.souv-dd select.active{border-color:var(--lime)!important;background:#f7fcf3!important;box-shadow:0 0 0 3px rgba(144,227,101,.18);color:var(--forest);}
</style>

<div class="page-head"><h2 class="page-title"><span class="page-icon c-red"><i class="bi bi-box-seam"></i></span>Laporan Souvenir</h2><a class="btn-brand" href="<?=BASE_URL?>/reports/souvenir/export?mode=stock&event_id=<?=(int)$event?>"><i class="bi bi-download"></i>Ekspor Stok</a></div>

<div class="report-toolbar">
 <div class="rt-row">
  <div class="rt-field rt-event"><label class="rt-label" for="eventSel">Tampilkan</label><div class="select-wrap"><select id="eventSel" class="form-select"><option value="0">Semua (posisi stok gudang)</option><?php foreach($events as $e):?><option value="<?=$e['id']?>" <?=$event==$e['id']?'selected':''?>>Event: <?=e($e['event_name'])?> — <?=dmy($e['event_date'])?></option><?php endforeach;?></select><i class="bi bi-chevron-down select-arrow"></i></div></div>
 </div>
 <div class="rt-hint"><i class="bi bi-info-circle"></i><span><?=$event?'Menampilkan alokasi souvenir untuk event ini dan jumlah yang sudah diambil peserta.':'Posisi stok seluruh souvenir di gudang. <strong>Dipesan Event</strong> = jatah event aktif (DRAFT/PUBLISHED) yang belum diambil; <strong>Tersedia</strong> = stok gudang yang masih bisa dialokasikan ke event lain.'?> Event berstatus DRAFT tidak muncul di pilihan.</span></div>
</div>

<div class="stock-kpis" id="stockKpis" data-live="stockkpis">
<?php
$sum=fn($k)=>array_sum(array_column($stock,$k));
$fmt=fn($v)=>is_int($v)?number_format($v,0,',','.'):$v;
// Kartu disusun mengikuti alur hitungan, dengan tanda operasi di antaranya (sama seperti kolom tabel).
if($event){
 $alok=$sum('alokasi');$ambil=$sum('diambil');
 $kpis=[['k-blue','bi-clipboard-check','Total Alokasi',$alok,'jatah souvenir event ini'],'−',['k-green','bi-bag-check','Telah Diserahkan',$ambil,'kepada peserta'],'=',['k-amber k-result','bi-hourglass-split','Belum Diserahkan',$sum('sisa'),'menunggu pengambilan'],'|',['k-teal','bi-graph-up','Realisasi Penyaluran',($alok?round($ambil/$alok*100):0).'%','dari total alokasi']];
}else{
 $kpis=[['k-teal','bi-box-seam','Stok Awal',$sum('stok_awal'),count($stock).' jenis souvenir'],'−',['k-blue','bi-bag-check','Sudah Diambil',$sum('diambil'),'sudah dibagikan'],'=',['k-teal','bi-building','Stok Gudang',$sum('stock'),'sisa fisik di gudang'],'−',['k-amber','bi-bookmark-check','Dipesan Event',$sum('dipesan'),'jatah event aktif'],'=',['k-green k-result','bi-check2-circle','Tersedia',max($sum('tersedia'),0),'bisa dialokasikan']];
}
foreach($kpis as $k):if(is_string($k)):?><?=$k==='|'?'<div class="k-sep"></div>':'<div class="k-op"><i class="bi bi-chevron-right"></i></div>'?><?php continue;endif;[$cls,$ico,$lbl,$val,$sub]=$k;?>
<div class="stock-kpi <?=$cls?>"><div class="k-ico"><i class="bi <?=$ico?>"></i></div><div><div class="k-lbl"><?=$lbl?></div><div class="k-val"><?=$fmt($val)?></div><div class="k-sub"><?=$sub?></div></div></div>
<?php endforeach;?>
</div>

<div class="soft-card mb-4">
 <div class="section-title"><h3><i class="bi bi-boxes"></i><?=$event?'Alokasi per Souvenir':'Stok per Souvenir'?></h3></div>
 <div class="data-card" data-live="stocktable"><div class="table-responsive"><table class="data-table stock-table">
 <?php if($event):?>
  <thead><tr><th>No</th><th>Kode</th><th>Souvenir</th><th class="num">Total Alokasi</th><th class="num">Telah Diserahkan</th><th class="num">Belum Diserahkan</th><th>Realisasi</th><th class="num">Stok Gudang</th><th>Kondisi</th></tr></thead><tbody>
  <?php if(!$stock):?><tr><td colspan="9" class="text-muted text-center py-4">Belum ada souvenir yang dialokasikan ke event ini.</td></tr><?php endif;?>
  <?php foreach($stock as $i=>$r):?><tr><td><?=$i+1?></td><td><?=e($r['code'])?></td><td class="fw-semibold"><?=e($r['name'])?></td><td class="num"><?=number_format($r['alokasi'],0,',','.')?></td><td class="num"><?=number_format($r['diambil'],0,',','.')?></td><td class="num fw-semibold"><?=number_format($r['sisa'],0,',','.')?></td><td><div class="small text-muted"><?=$r['persen']?>%</div><div class="stock-bar"><i style="width:<?=min($r['persen'],100)?>%"></i></div></td><td class="num"><?=number_format($r['stock'],0,',','.')?></td><td><span class="kond kond-<?=$r['kondisi']?>"><?=strtoupper($r['kondisi'])?></span></td></tr><?php endforeach;?>
 <?php else:?>
  <thead><tr><th>No</th><th>Kode</th><th>Souvenir</th><th class="num" title="Stok gudang ditambah yang sudah diambil">Stok Awal</th><th class="num" title="Jumlah yang sudah dibagikan ke peserta">Sudah Diambil</th><th class="num" title="Sisa fisik di gudang saat ini">Stok Gudang</th><th class="num" title="Jatah event DRAFT/PUBLISHED yang belum diambil">Dipesan Event</th><th class="num" title="Stok gudang yang masih bisa dialokasikan">Tersedia</th><th>Kondisi</th></tr></thead><tbody>
  <?php if(!$stock):?><tr><td colspan="9" class="text-muted text-center py-4">Belum ada data souvenir.</td></tr><?php endif;?>
  <?php $n=fn($v)=>number_format($v,0,',','.');foreach($stock as $i=>$r):?><tr><td><?=$i+1?></td><td><?=e($r['code'])?></td><td class="fw-semibold"><?=e($r['name'])?><?=$r['status']!=='ACTIVE'?' <span class="text-muted small fw-normal">(nonaktif)</span>':''?></td>
  <td class="num"><?=$n($r['stok_awal'])?></td><td class="num text-muted"><?=$n($r['diambil'])?></td><td class="num fw-semibold"><?=$n($r['stock'])?></td>
  <td class="num"><?=$n($r['dipesan'])?><?php if($r['dipesan_detail']):?><br><button type="button" class="pesan-toggle" aria-expanded="false" data-target="pesan-<?=$i?>"><?=count($r['dipesan_detail'])?> event<i class="bi bi-chevron-down"></i></button><?php endif;?></td>
  <td class="num fw-semibold <?=$r['tersedia']<0?'text-danger':''?>"><?=$n($r['tersedia'])?></td><td><span class="kond kond-<?=$r['kondisi']?>"><?=strtoupper($r['kondisi'])?></span></td></tr>
  <?php if($r['dipesan_detail']):?><tr class="pesan-row" id="pesan-<?=$i?>" hidden><td colspan="9"><div class="pesan-panel"><div class="pp-head">Rincian Dipesan Event · <?=e($r['name'])?><span><?=count($r['dipesan_detail'])?> event · total <?=$n($r['dipesan'])?></span></div><div class="pp-body"><table><?php foreach($r['dipesan_detail'] as $d):?><tr><td><?=e($d['event'])?></td><td><span class="st st-<?=$d['status']?>"><?=$d['status']?></span></td><td class="text-end fw-semibold"><?=$n($d['qty'])?></td></tr><?php endforeach;?></table></div></div></td></tr><?php endif;?>
  <?php endforeach;?>
 <?php endif;?>
 </tbody></table></div></div>
 <div class="legend"><span><b>AMAN</b> stok cukup</span><span><b>MENIPIS</b> <?=$event?'souvenir belum diserahkan ≤ 5 atau ≤ 10%':'stok atau tersedia &lt; 5'?></span><span><b>HABIS</b> <?=$event?'alokasi sudah habis diambil':'stok gudang 0'?></span><span><b>KURANG</b> <?=$event?'stok gudang lebih kecil dari souvenir yang belum diserahkan':'stok gudang lebih kecil dari yang dipesan event'?></span><?php if(!$event):?><span><b>Stok Gudang</b> adalah Stok Awal dikurangi Sudah Diambil.</span><span><b>Tersedia</b> adalah Stok Gudang dikurangi Dipesan Event (jatah event DRAFT/PUBLISHED yang belum diambil).</span><?php endif;?></div>
</div>

<div class="soft-card">
 <div class="section-title"><h3><i class="bi bi-people"></i>Pengambilan Souvenir Peserta</h3><?php if($event):?><button type="button" class="btn-outline-brand c-excel" id="psExportBtn"><i class="bi bi-file-earmark-excel-fill"></i><span id="psExportLbl">Ekspor</span></button><?php endif;?></div>
 <?php if(!$event):?>
 <div class="text-muted" style="font-size:13px;padding:6px 2px 4px;"><i class="bi bi-info-circle me-1"></i>Pilih satu event pada pilihan <strong>Tampilkan</strong> di atas untuk melihat peserta yang sudah dan belum mengambil souvenir.</div>
 <?php else:?>
 <div class="d-flex flex-wrap gap-2 align-items-end mb-3">
  <div class="search-wrap" style="flex:1 1 280px;max-width:420px"><i class="bi bi-search"></i><input type="text" id="psQ" class="search-input w-100" placeholder="Cari nama, NIK, departemen, atau souvenir"></div>
  <div class="seg" id="psSeg" role="group" aria-label="Filter status pengambilan">
   <button type="button" data-st="all" class="on">Semua hadir <span class="n" id="psAll">0</span></button>
   <button type="button" data-st="sudah"><span class="dot" style="background:#3f9a2a"></span>Sudah Ambil <span class="n" id="psSudah">0</span></button>
   <button type="button" data-st="belum"><span class="dot" style="background:#e0a458"></span>Belum Ambil <span class="n" id="psBelum">0</span></button>
  </div>
 </div>
 <div class="report-summary" id="psSummary"></div>
 <div class="data-card"><div class="table-responsive"><table class="data-table text-nowrap"><thead><tr><th>No</th><th>NIK</th><th>Nama</th><th>Departemen</th><th>Waktu Check-in</th><th>Souvenir Diambil</th><th>Waktu Ambil</th><th>Status</th></tr></thead><tbody id="psRows"></tbody></table></div><div id="psPagination" class="d-flex align-items-center gap-2 p-3"></div></div>
 <div class="legend">Hanya menampilkan peserta yang <b>sudah check-in</b>. Peserta yang belum hadir dapat dilihat di Laporan Kehadiran.</div>
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
function renderPagination(container,totalPages,pg,onGo){
 container.innerHTML='';
 if(totalPages<=1)return;
 const mk=(label,p,disabled)=>{const b=document.createElement('button');b.type='button';b.className='btn-outline-brand';b.style.padding='6px 12px';b.style.fontSize='12.5px';b.textContent=label;b.disabled=disabled;if(disabled)b.style.opacity='.4';b.onclick=()=>onGo(p);return b;};
 container.appendChild(mk('«',pg-1,pg<=1));
 container.appendChild(Object.assign(document.createElement('span'),{className:'small text-muted mx-1',textContent:'Halaman '+pg+' / '+totalPages}));
 container.appendChild(mk('»',pg+1,pg>=totalPages));
}
document.getElementById('eventSel').addEventListener('change',e=>{location.href=BASE+'/reports/souvenir'+(e.target.value!=='0'?'?event_id='+e.target.value:'');});
// ---- Status Pengambilan Peserta (peserta hadir: sudah / belum ambil souvenir) ----
let psData=[],psSt='all',psPage=1,psShown=[];
function psLoad(){psData=JSON.parse(document.getElementById('pesertaJson').textContent);}
function psRender(){
 if(!document.getElementById('psRows'))return;
 const q=(document.getElementById('psQ').value||'').trim().toLowerCase();
 const byQ=psData.filter(x=>!q||[x.nik,x.name,x.department,x.souvenirs].some(v=>(v||'').toString().toLowerCase().includes(q)));
 const sudah=byQ.filter(x=>x.souvenirs).length;
 document.getElementById('psAll').textContent=byQ.length;document.getElementById('psSudah').textContent=sudah;document.getElementById('psBelum').textContent=byQ.length-sudah;
 psShown=byQ.filter(x=>psSt==='all'||(psSt==='sudah'?!!x.souvenirs:!x.souvenirs));
 const total=Math.max(1,Math.ceil(psShown.length/PAGE_SIZE));if(psPage>total)psPage=total;
 const start=(psPage-1)*PAGE_SIZE,rows=psShown.slice(start,start+PAGE_SIZE);
 document.getElementById('psRows').innerHTML=rows.length?rows.map((x,i)=>'<tr><td>'+(start+i+1)+'</td><td>'+esc(x.nik)+'</td><td class="fw-semibold">'+esc(x.name)+'</td><td>'+esc(x.department)+'</td><td>'+dtCell(x.checkin_at)+'</td><td>'+(x.souvenirs?'<i class="bi bi-gift text-muted me-1"></i>'+esc(x.souvenirs):'<span class="text-muted">-</span>')+'</td><td>'+(x.souvenir_at?dtCell(x.souvenir_at):'<span class="text-muted">-</span>')+'</td><td>'+(x.souvenirs?'<span class="kond kond-Aman">SUDAH AMBIL</span>':'<span class="kond kond-Menipis">BELUM AMBIL</span>')+'</td></tr>').join('')
  :'<tr><td colspan="8" class="text-muted text-center py-4">'+(psData.length?'Tidak ada data yang cocok dengan filter.':'Belum ada peserta yang check-in di event ini.')+'</td></tr>';
 renderPagination(document.getElementById('psPagination'),total,psPage,p=>{psPage=p;psRender();});
 const parts=[];if(psSt!=='all')parts.push('Status: <b>'+(psSt==='sudah'?'Sudah Ambil':'Belum Ambil')+'</b>');const qq=document.getElementById('psQ').value.trim();if(qq)parts.push('Cari: <b>"'+esc(qq)+'"</b>');
 document.getElementById('psSummary').innerHTML='<span>Menampilkan <b>'+psShown.length+'</b> dari <b>'+psData.length+'</b> peserta hadir</span>'+(parts.length?'<span class="rs-sep">·</span>'+parts.join('<span class="rs-sep">·</span>')+'<button type="button" class="rs-reset" id="psReset"><i class="bi bi-x-circle"></i>Reset filter</button>':'');
 const rb=document.getElementById('psReset');if(rb)rb.onclick=()=>{document.getElementById('psQ').value='';psSetSt('all');};
 const eb=document.getElementById('psExportBtn');document.getElementById('psExportLbl').textContent='Ekspor ('+psShown.length+')';eb.disabled=psShown.length===0;
}
function psSetSt(v){psSt=v;psPage=1;document.querySelectorAll('#psSeg button').forEach(b=>b.classList.toggle('on',b.dataset.st===v));psRender();}
if(document.getElementById('psRows')){
 document.getElementById('psQ').addEventListener('input',()=>{psPage=1;psRender();});
 document.querySelectorAll('#psSeg button').forEach(b=>b.addEventListener('click',()=>psSetSt(b.dataset.st)));
 document.getElementById('psExportBtn').addEventListener('click',()=>{
  if(!psShown.length)return;
  const f=document.createElement('form');f.method='post';f.action=BASE+'/reports/souvenir/export';f.style.display='none';
  const add=(k,v)=>{const i=document.createElement('input');i.type='hidden';i.name=k;i.value=v;f.appendChild(i);};
  add('_csrf',CSRF);add('event_id',EVENT_ID);add('mode','status');add('status',psSt);add('q',document.getElementById('psQ').value.trim());add('ids',psShown.map(x=>x.id).join(','));
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
Live.on(['souv','stock','alloc','evt','emp','inv','att'],async()=>{await Live.swap(['stockkpis','stocktable','pesertadata']);psLoad();psRender();});
</script>
<?php require __DIR__.'/../layout/footer.php';
