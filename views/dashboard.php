<?php require __DIR__.'/layout/header.php';
$cardIcons=['karyawan'=>'bi-people','event'=>'bi-calendar-event','undangan'=>'bi-envelope','checkin'=>'bi-qr-code-scan','stok'=>'bi-box-seam'];
$cardColors=['karyawan'=>'c-blue','event'=>'c-purple','undangan'=>'c-green','checkin'=>'c-orange','stok'=>'c-red'];
$cardBlobs=['karyawan'=>'#4a7fa8','event'=>'#7a62b8','undangan'=>'#1f7a4c','checkin'=>'#b77a3a','stok'=>'#b85a4e'];
$trendIcons=['up'=>'bi-arrow-up-short','down'=>'bi-arrow-down-short','flat'=>'bi-dash'];
$welcomeName=flash('welcome');
?><style>
@keyframes wtBackdropIn{from{opacity:0}to{opacity:1}}
@keyframes wtBackdropOut{from{opacity:1}to{opacity:0}}
@keyframes wtCardIn{from{opacity:0;transform:translateY(10px) scale(.96)}to{opacity:1;transform:translateY(0) scale(1)}}
@keyframes wtCardOut{from{opacity:1;transform:translateY(0) scale(1)}to{opacity:0;transform:translateY(6px) scale(.97)}}
.welcome-backdrop{position:fixed;inset:0;z-index:1080;background:rgba(13,31,23,.42);display:flex;align-items:center;justify-content:center;animation:wtBackdropIn .3s ease both;}
.welcome-backdrop.hide{animation:wtBackdropOut .3s ease forwards;}
.welcome-toast{position:relative;background:#fff;border-radius:16px;box-shadow:0 24px 60px rgba(13,31,23,.28);padding:32px 34px 26px;max-width:360px;width:calc(100% - 40px);text-align:center;animation:wtCardIn .38s cubic-bezier(.22,.61,.36,1) .05s both;}
.welcome-backdrop.hide .welcome-toast{animation:wtCardOut .28s cubic-bezier(.22,.61,.36,1) forwards;}
.welcome-toast .wt-icon{width:56px;height:56px;border-radius:50%;background:#e9f5ee;color:#1f7a4c;display:flex;align-items:center;justify-content:center;font-size:26px;margin:0 auto 16px;}
.welcome-toast .wt-title{font-size:17px;font-weight:700;color:#0d1f17;margin:0 0 6px;}
.welcome-toast .wt-sub{font-size:13.5px;color:#6b7570;margin:0 0 22px;}
.welcome-toast .wt-ok{background:#1f7a4c;border:0;color:#fff;font-weight:600;font-size:13.5px;border-radius:8px;padding:10px 28px;cursor:pointer;transition:background .15s;}
.welcome-toast .wt-ok:hover{background:#082418;}
.welcome-toast .wt-close{position:absolute;top:12px;right:12px;background:none;border:0;color:#9aa19c;cursor:pointer;font-size:17px;line-height:1;padding:4px;}
.welcome-toast .wt-close:hover{color:#3d453f;}
@media (prefers-reduced-motion: reduce){.welcome-backdrop,.welcome-toast{animation:none;}.welcome-backdrop.hide{animation:none;display:none;}}
@keyframes statCardIn{0%{opacity:0;transform:translateY(18px) scale(.96)}60%{opacity:1;transform:translateY(-2px) scale(1.01)}100%{opacity:1;transform:translateY(0) scale(1)}}
@keyframes blobFloat{0%,100%{transform:translate(0,0) scale(1)}50%{transform:translate(-6px,-8px) scale(1.12)}}
@keyframes iconPop{0%{transform:scale(.6) rotate(-8deg);opacity:0}60%{transform:scale(1.12) rotate(4deg);opacity:1}100%{transform:scale(1) rotate(0deg)}}
.dash-head{display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:14px;margin-bottom:22px;}
.page-title{font-size:24px;font-weight:700;letter-spacing:-.01em;margin:0 0 4px;color:#101c26;}
.dash-sub{margin:0;color:#6b7570;font-size:13.5px;}
.date-widget{display:flex;align-items:center;gap:11px;background:#fff;border:1px solid #e7e9e6;border-radius:12px;padding:10px 18px;}
.date-widget i{font-size:19px;color:var(--accent);}
.date-widget .dw-date{font-size:13px;font-weight:700;color:#101c26;}
.date-widget .dw-time{font-size:11.5px;color:#9aa19c;}
.stat-card{position:relative;cursor:pointer;overflow:hidden;transition:transform .22s cubic-bezier(.22,.61,.36,1),box-shadow .22s ease,border-color .22s ease;animation:statCardIn .5s cubic-bezier(.22,.61,.36,1) both;border:1px solid #e7e9e6!important;background:#fff;}
.stat-card::before{content:"";position:absolute;top:0;left:0;right:0;height:3px;background:var(--blob,#4a7fa8);transform:scaleX(0);transform-origin:left;transition:transform .3s ease;}
.stat-card:hover::before{transform:scaleX(1);}
.stat-card:hover{transform:translateY(-5px) scale(1.015);box-shadow:0 14px 30px -6px color-mix(in srgb,var(--blob,#4a7fa8) 45%,transparent);border-color:var(--blob,#4a7fa8)!important;}
.stat-card:active{transform:translateY(-2px) scale(1.005);}
.stat-card::after{content:"";position:absolute;right:-26px;bottom:-26px;width:92px;height:92px;border-radius:50%;background:var(--blob,#4a7fa8);opacity:.1;pointer-events:none;animation:blobFloat 5s ease-in-out infinite;}
.stat-card:hover::after{opacity:.16;}
.stat-card .icon-badge{position:relative;z-index:1;width:34px;height:34px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:16px;margin-bottom:14px;animation:iconPop .5s cubic-bezier(.22,.61,.36,1) both;animation-delay:inherit;transition:transform .25s cubic-bezier(.34,1.56,.64,1),box-shadow .25s ease;}
.stat-card:hover .icon-badge{transform:scale(1.1) rotate(-4deg);}
.stat-card .icon-badge.c-blue{background:#eaf2f7;color:#4a7fa8;}
.stat-card:hover .icon-badge.c-blue{box-shadow:0 0 0 6px #eaf2f7;}
.stat-card .icon-badge.c-purple{background:#f1edf9;color:#7a62b8;}
.stat-card:hover .icon-badge.c-purple{box-shadow:0 0 0 6px #f1edf9;}
.stat-card .icon-badge.c-green{background:#eaf6ee;color:#1f7a4c;}
.stat-card:hover .icon-badge.c-green{box-shadow:0 0 0 6px #eaf6ee;}
.stat-card .icon-badge.c-orange{background:#fbf1e7;color:#b77a3a;}
.stat-card:hover .icon-badge.c-orange{box-shadow:0 0 0 6px #fbf1e7;}
.stat-card .icon-badge.c-red{background:#fbecea;color:#b85a4e;}
.stat-card:hover .icon-badge.c-red{box-shadow:0 0 0 6px #fdeceb;}
.stat-card .lbl{position:relative;z-index:1;font-size:12.5px;color:#6b7570;font-weight:500;}
.stat-card h2{position:relative;z-index:1;font-size:28px;font-weight:700;margin:2px 0 0;color:#101c26;}
.stat-card .trend{position:relative;z-index:1;display:flex;align-items:center;gap:5px;font-size:11.5px;font-weight:600;margin-top:8px;}
.stat-card .trend.c-good{color:#1f7a4c;}
.stat-card .trend.c-bad{color:#c0392b;}
.stat-card .trend.c-warn{color:#b8860b;}
.stat-card .trend.c-neutral{color:#9aa19c;}
.recent-card{border:1px solid #e7e9e6;border-radius:12px;overflow:hidden;animation:statCardIn .45s cubic-bezier(.22,.61,.36,1) .3s both;}
.recent-card .card-header{background:#fff;font-weight:700;font-size:14px;padding:16px 20px;border-bottom:1px solid #e7e9e6;display:flex;align-items:center;justify-content:space-between;}
.recent-card .dash-pg{padding:12px 20px;border-top:1px solid #eef0ef;}
.recent-card .dash-pg:empty{display:none;}
.recent-card .rc-icon{width:28px;height:28px;border-radius:8px;background:var(--accent-soft);color:var(--accent);display:inline-flex;align-items:center;justify-content:center;font-size:14px;}
.recent-card .rc-link{font-size:12.5px;font-weight:600;color:var(--accent);text-decoration:none;display:inline-flex;align-items:center;gap:4px;}
.recent-card .rc-link:hover{text-decoration:underline;}
.recent-card table th{font-size:12.5px;text-transform:uppercase;letter-spacing:.03em;color:#6b7570;font-weight:600;border-bottom:1px solid #e7e9e6!important;}
.recent-card table td{font-size:14px;color:#1c2a22;vertical-align:middle;}
.row-icon{width:30px;height:30px;border-radius:8px;background:var(--accent-soft);color:var(--accent);display:inline-flex;align-items:center;justify-content:center;font-size:14px;flex:none;}
.dot-badge{display:inline-flex;align-items:center;gap:6px;padding:4px 10px;border-radius:20px;font-size:10.5px;font-weight:700;letter-spacing:.02em;}
.dot-badge .dot{width:6px;height:6px;border-radius:50%;display:inline-block;flex:none;}
.btn-row-menu{background:none;border:0;color:#9aa19c;padding:4px 8px;border-radius:6px;cursor:pointer;line-height:1;}
.btn-row-menu:hover{background:#f1f3f1;color:#3d453f;}
#cardInfoModal .modal-content{border:0;border-radius:16px;overflow:hidden;box-shadow:0 24px 60px rgba(8,30,20,.25);}
#cardInfoModal .modal-header{padding:22px 26px;border-bottom:1px solid #e7e9e6;align-items:center;}
#cardInfoModal .head-icon{width:42px;height:42px;border-radius:11px;background:#e8f1f9;color:#4a7fa8;display:flex;align-items:center;justify-content:center;font-size:19px;flex:none;margin-right:14px;}
#cardInfoModal .modal-title{font-size:18px;font-weight:700;color:#101c26;}
#cardInfoModal #cardInfoCount{font-size:12.5px;color:#6b7570;}
#cardInfoModal .modal-body{padding:18px 26px;background:#f9faf9;}
#cardInfoModal .modal-body::-webkit-scrollbar{width:8px;}
#cardInfoModal .modal-body::-webkit-scrollbar-thumb{background:#d5dbd7;border-radius:8px;}
#cardInfoModal .item-row{background:#fff;border:1px solid #e7e9e6;border-radius:12px;padding:14px 16px;margin-bottom:10px;transition:border-color .15s,box-shadow .15s;}
#cardInfoModal .item-row:hover{border-color:#4a7fa855;box-shadow:0 4px 14px rgba(15,40,60,.08);}
#cardInfoModal .item-row .title-row{display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;}
#cardInfoModal .item-row strong{font-size:14.5px;color:#101c26;}
#cardInfoModal .badge-pill{font-size:10.5px;font-weight:700;letter-spacing:.03em;padding:5px 10px;border-radius:20px;color:#fff;}
#cardInfoModal .field-lbl{font-size:11px;text-transform:uppercase;letter-spacing:.04em;color:#8a938d;display:block;margin-bottom:2px;}
#cardInfoModal .field-val{font-size:13.5px;color:#1c2a22;}
#cardInfoModal .modal-footer{padding:16px 26px;border-top:1px solid #e7e9e6;}
#cardInfoModal .btn-export{background:var(--lime);border:0;color:var(--forest);font-weight:700;font-size:13.5px;padding:9px 18px;border-radius:8px;transition:background .15s;}
#cardInfoModal .btn-export:hover{background:var(--lime-hover);color:var(--forest);}
#cardInfoModal .btn-close-modal{background:#fff;border:1px solid #dfe3e0;color:#3d453f;font-weight:600;font-size:13.5px;padding:9px 18px;border-radius:8px;transition:background .15s;}
#cardInfoModal .btn-close-modal:hover{background:#f1f3f1;}
#cardInfoModal .empty-state{text-align:center;padding:48px 20px;color:#8a938d;}
#cardInfoModal .empty-state i{font-size:32px;display:block;margin-bottom:10px;color:#c3cac5;}
</style>
<?php if($welcomeName):?><div class="welcome-backdrop" id="welcomeBackdrop"><div class="welcome-toast"><button type="button" class="wt-close" onclick="dismissWelcomeToast()">&times;</button><div class="wt-icon"><i class="bi bi-check-lg"></i></div><p class="wt-title">Selamat datang, <?=e($welcomeName)?>!</p><p class="wt-sub">Anda berhasil masuk ke sistem.</p><button type="button" class="wt-ok" onclick="dismissWelcomeToast()">Mengerti</button></div></div>
<script>
function dismissWelcomeToast(){const t=document.getElementById('welcomeBackdrop');if(!t)return;t.classList.add('hide');setTimeout(()=>t.remove(),320);}
setTimeout(dismissWelcomeToast,4000);
</script>
<?php endif;?>
<div class="dash-head">
<div><h2 class="page-title"><span class="page-icon c-blue"><i class="bi bi-grid-1x2"></i></span>Dashboard</h2><p class="dash-sub">Selamat datang di Sistem Event PT. United Steel Center Indonesia</p></div>
<div class="date-widget"><i class="bi bi-calendar3"></i><div><div class="dw-date"><?=e($todayLabel)?></div><div class="dw-time"><?=e($timeLabel)?></div></div></div>
</div>
<div class="row g-3 mb-4" data-live="stats"><?php foreach($cards as $idx=>$c):$trend=$c['trend']??null;?><div class="col-6 col-md"><div class="card stat-card p-3 h-100" style="animation-delay:<?=($idx*0.07)?>s;--blob:<?=$cardBlobs[$c['key']]??'#4a7fa8'?>;" data-bs-toggle="modal" data-bs-target="#cardInfoModal" onclick="showCardInfo('<?=e($idx)?>')"><div class="icon-badge <?=$cardColors[$c['key']]??'c-blue'?>"><i class="bi <?=$cardIcons[$c['key']]??'bi-graph-up'?>"></i></div><div class="lbl"><?=e($c['label'])?></div><h2><?=e((string)$c['value'])?><?php if(!empty($c['suffix'])):?><span class="fs-6 text-muted"><?=e($c['suffix'])?></span><?php endif;?></h2><?php if($trend):?><div class="trend c-<?=e($trend['color'])?>"><i class="bi <?=$trendIcons[$trend['icon']]??'bi-dash'?>"></i><span><?=e($trend['text'])?></span></div><?php endif;?></div></div><?php endforeach;?></div>
<div class="card recent-card mb-4" data-live="recent-events">
<div class="card-header"><span class="d-flex align-items-center gap-2"><span class="rc-icon"><i class="bi bi-calendar-event"></i></span>Event Terbaru</span><a href="<?=BASE_URL?>/events" class="rc-link">Lihat Semua <i class="bi bi-arrow-right"></i></a></div>
<div class="table-responsive"><table class="table mb-0"><tr><th>Event</th><th>Tanggal</th><th>Status</th><th>Diundang</th><th>Sudah Hadir</th><th></th></tr><?php $evtStatusColor=['DRAFT'=>'#6b7570','PUBLISHED'=>'#1f7a4c','CLOSED'=>'#1c2a26','CANCELLED'=>'#c0392b'];$evtStatusBg=['DRAFT'=>'#f1f3f1','PUBLISHED'=>'#eaf6ee','CLOSED'=>'#eceff1','CANCELLED'=>'#fdeceb'];foreach($events as $e):$sc=$evtStatusColor[$e['status']]??'#6b7570';$sb=$evtStatusBg[$e['status']]??'#f1f3f1';?><tr class="pg-row">
<td><div class="d-flex align-items-center gap-2"><span class="row-icon"><i class="bi bi-calendar-event"></i></span><div><div class="fw-semibold" style="color:#101c26;"><?=e($e['event_name'])?></div><?php if(!empty($e['location'])):?><div class="text-muted" style="font-size:11.5px;"><?=e($e['location'])?></div><?php endif;?></div></div></td>
<td><?=dmy($e['event_date'])?></td>
<td><span class="dot-badge" style="background:<?=$sb?>;color:<?=$sc?>;"><span class="dot" style="background:<?=$sc?>"></span><?=e($e['status'])?></span></td>
<td><i class="bi bi-person text-muted"></i> <?=$e['invited']?></td>
<td><i class="bi bi-person-check text-muted"></i> <?=$e['checked_in']?></td>
<td class="text-end"><div class="dropdown"><button type="button" class="btn-row-menu" data-bs-toggle="dropdown"><i class="bi bi-three-dots-vertical"></i></button><ul class="dropdown-menu dropdown-menu-end"><li><a class="dropdown-item" href="<?=BASE_URL?>/events/<?=$e['id']?>/invitations"><i class="bi bi-people me-2"></i>Peserta</a></li><li><a class="dropdown-item" href="<?=BASE_URL?>/events/<?=$e['id']?>/edit"><i class="bi bi-pencil me-2"></i>Edit</a></li><li><a class="dropdown-item" href="<?=BASE_URL?>/events/<?=$e['id']?>/souvenirs"><i class="bi bi-gift me-2"></i>Souvenir</a></li></ul></div></td>
</tr><?php endforeach;?></table></div><div class="dash-pg"></div></div>
<div class="card recent-card" data-live="recent-claims"><div class="card-header">Souvenir Terambil</div><div class="table-responsive"><table class="table mb-0"><tr><th>Waktu</th><th>Karyawan</th><th>Event</th><th>Souvenir</th><th>Qty</th></tr><?php if(empty($recentClaims)):?><tr><td colspan="5" class="text-muted text-center py-3">Belum ada souvenir yang diambil.</td></tr><?php else: foreach($recentClaims as $c):$dtParts=explode(' ',dmy($c['collected_at']),2);?><tr class="pg-row"><td><div class="dt-cell"><span class="dt-date"><?=e($dtParts[0]??'')?></span><?php if(!empty($dtParts[1])):?><span class="dt-time"><?=e($dtParts[1])?></span><?php endif;?></div></td><td><?=e($c['name'])?> <span class="text-muted small">(<?=e($c['nik'])?>)</span></td><td><?=e($c['event_name'])?></td><td><?=e($c['souvenir_name'])?></td><td><?=(int)$c['quantity']?></td></tr><?php endforeach;endif;?></table></div><div class="dash-pg"></div></div>
<script type="application/json" id="cardInfoJson" data-live="cardinfo"><?=json_encode(array_map(fn($c)=>['key'=>$c['key'],'label'=>$c['label'],'items'=>$c['items']??[]],$cards),JSON_HEX_TAG|JSON_HEX_AMP)?></script>
<div class="modal fade" id="cardInfoModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered modal-lg"><div class="modal-content">
<div class="modal-header"><div class="head-icon" id="cardInfoIcon"><i class="bi bi-graph-up"></i></div><div class="flex-grow-1"><h5 class="modal-title mb-0" id="cardInfoLabel"></h5><span id="cardInfoCount"></span></div><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body" style="max-height:60vh;overflow:auto" id="cardInfoList"></div>
<div class="modal-footer d-flex justify-content-end align-items-center gap-2"><a href="#" id="cardInfoExport" class="btn-export" target="_blank"><i class="bi bi-download me-1"></i>Export Excel</a><button type="button" class="btn-close-modal" data-bs-dismiss="modal">Tutup</button></div>
</div></div></div>
<script>
const CARD_ICONS=<?=json_encode($cardIcons)?>;
function cardInfo(){return JSON.parse(document.getElementById('cardInfoJson').textContent);}
// Tabel Event Terbaru & Souvenir Terambil: 5 baris per halaman (halaman aktif tetap saat data diperbarui real-time).
const DASH_PAGE=5,dashPg={};
function dashPaginate(){
 document.querySelectorAll('[data-live="recent-events"],[data-live="recent-claims"]').forEach(card=>{
  const key=card.dataset.live,rows=[...card.querySelectorAll('tr.pg-row')],box=card.querySelector('.dash-pg');if(!box)return;
  const pages=Math.max(1,Math.ceil(rows.length/DASH_PAGE));let pg=Math.min(dashPg[key]||1,pages);dashPg[key]=pg;
  rows.forEach((tr,i)=>{tr.style.display=(i>=(pg-1)*DASH_PAGE&&i<pg*DASH_PAGE)?'':'none';});
  drawPager(box,pg,pages,p=>{dashPg[key]=p;dashPaginate();},rows.length,DASH_PAGE);
 });
}
dashPaginate();
Live.on(['att','souv','inv','evt','emp','stock','alloc'],async()=>{await Live.swap(['stats','recent-events','recent-claims','cardinfo']);dashPaginate();});
const BADGE_COLORS={success:'#1f7a4c',warning:'#c9950a',danger:'#c0392b',secondary:'#6b7570'};
function esc(s){return (s??'').toString().replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));}
function showCardInfo(idx){
 const c=cardInfo()[idx];
 document.getElementById('cardInfoLabel').textContent=c.label;
 document.getElementById('cardInfoIcon').innerHTML='<i class="bi '+(CARD_ICONS[c.key]||'bi-graph-up')+'"></i>';
 document.getElementById('cardInfoExport').href=BASE+'/dashboard/export/'+c.key;
 const n=c.items.length;
 document.getElementById('cardInfoCount').textContent=n+' data ditemukan';
 const list=document.getElementById('cardInfoList');
 if(!n){list.innerHTML='<div class="empty-state"><i class="bi bi-inbox"></i>Tidak ada data.</div>';return;}
 list.innerHTML=c.items.map(it=>{
  const fields=it.fields.map(f=>'<div class="col-6"><span class="field-lbl">'+esc(f.label)+'</span><span class="field-val">'+esc(f.value||'-')+'</span></div>').join('');
  const color=BADGE_COLORS[it.badgeColor]||'#6b7570';
  return '<div class="item-row"><div class="title-row"><strong>'+esc(it.title)+'</strong><span class="badge-pill" style="background:'+color+'">'+esc(it.badge)+'</span></div><div class="row g-2">'+fields+'</div></div>';
 }).join('');
}
</script>
<?php require __DIR__.'/layout/footer.php';