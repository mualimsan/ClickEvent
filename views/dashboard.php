<?php require __DIR__.'/layout/header.php';
$cardIcons=['diundang'=>'bi-people','undangan'=>'bi-envelope','hadir'=>'bi-person-check','belum'=>'bi-person-dash','souvenir'=>'bi-gift'];
$cardColors=['diundang'=>'c-blue','undangan'=>'c-purple','hadir'=>'c-green','belum'=>'c-orange','souvenir'=>'c-red'];
$cardBlobs=['diundang'=>'#4a7fa8','undangan'=>'#7a62b8','hadir'=>'#1f7a4c','belum'=>'#b77a3a','souvenir'=>'#b85a4e'];
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
.dp-legend{margin-left:auto;display:inline-flex;align-items:center;gap:6px;font-size:11.5px;font-weight:500;color:#5f6b66;}
.dp-legend i{width:10px;height:10px;border-radius:3px;display:inline-block;margin-left:6px;}
.lg-ck{background:var(--accent);}.lg-no{background:#d6dcd9;}
.vc-chart{display:flex;align-items:flex-end;gap:8px;height:250px;padding-top:22px;border-bottom:1px solid #e3e7e5;margin-bottom:26px;background:repeating-linear-gradient(to top,transparent 0,transparent 56px,#f1f3f2 56px,#f1f3f2 57px);}
.vc-col{flex:1 1 0;min-width:0;height:100%;display:flex;flex-direction:column;justify-content:flex-end;align-items:center;position:relative;}
.vc-top{font-size:11px;color:#7c8b86;margin-bottom:4px;white-space:nowrap;}
.vc-top b{color:#101c26;}
.vc-stack{width:100%;max-width:46px;display:flex;flex-direction:column;gap:2px;transition:height .5s ease;}
.vc-seg{min-height:3px;transition:flex .5s ease;}
.vc-seg.no{background:#d6dcd9;border-radius:4px 4px 0 0;}
.vc-seg.ck{background:var(--accent);}
.vc-seg.ck:first-child{border-radius:4px 4px 0 0;}
.vc-col:hover .vc-seg.ck{background:#14532d;}.vc-col:hover .vc-seg.no{background:#c3cbc7;}
.vc-x{position:absolute;bottom:-20px;font-size:10.5px;font-weight:700;color:#3d453f;white-space:nowrap;}
@media(max-width:768px){.vc-chart{gap:4px;}.vc-x{font-size:9px;transform:rotate(-45deg);transform-origin:center;bottom:-24px;}.vc-top{font-size:9.5px;}}
.dpanel{background:#fff;border:1px solid #e7e9e6;border-radius:14px;padding:16px 18px;}
.dp-head{display:flex;align-items:center;gap:9px;font-weight:700;font-size:14px;color:#101c26;margin-bottom:14px;}
.dp-ico{width:28px;height:28px;border-radius:8px;background:var(--accent-soft);color:var(--accent);display:inline-flex;align-items:center;justify-content:center;font-size:14px;}
.dp-empty{text-align:center;color:#8a938d;font-size:13px;padding:34px 10px;}
.dp-empty i{display:block;font-size:26px;color:#c3cac5;margin-bottom:6px;}
.ev-bar{display:flex;flex-wrap:wrap;align-items:flex-end;gap:14px 22px;background:#fff;border:1px solid #e7e9e6;border-radius:14px;padding:14px 18px;margin-bottom:18px;}
.ev-bar .ev-pick{flex:0 1 540px;min-width:260px;}
.ev-bar .ev-pick .ev-lbl{display:flex;align-items:center;gap:6px;font-size:10.5px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#6b7570;margin-bottom:6px;}
.ev-bar .ev-pick .select-wrap{position:relative;}
.ev-bar .ev-pick .ev-ico{position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--accent);font-size:17px;pointer-events:none;z-index:1;}
.ev-bar .ev-pick select,.ev-bar .ev-pick .static-field{min-height:48px;padding-left:42px!important;font-size:15.5px!important;font-weight:700;color:var(--forest)!important;border:2px solid var(--lime)!important;background:#f7fcf3!important;border-radius:10px!important;box-shadow:0 0 0 4px rgba(144,227,101,.16);cursor:pointer;transition:box-shadow .2s,border-color .2s;}
.ev-bar .ev-pick .static-field{padding-left:14px!important;cursor:default;}
.ev-bar .ev-pick .static-field i{color:var(--accent);font-size:17px;}
.ev-bar .ev-pick select:hover{box-shadow:0 0 0 5px rgba(144,227,101,.28);}
.ev-bar .ev-pick select:focus{border-color:var(--accent)!important;box-shadow:0 0 0 5px rgba(144,227,101,.32);}
.ev-bar .ev-pick .select-arrow{color:var(--forest);font-size:13px;font-weight:700;}
.ev-bar .ev-meta{display:flex;flex-wrap:wrap;gap:8px 20px;font-size:13px;color:#4b5a52;}
.ev-bar .ev-meta span{display:inline-flex;align-items:center;gap:6px;}
.ev-bar .ev-meta i{color:var(--accent);}
.ev-st{font-size:10.5px;font-weight:700;letter-spacing:.04em;padding:3px 9px;border-radius:20px;}
.ev-st.PUBLISHED{background:#eaf6ee;color:#1f7a4c;}.ev-st.CLOSED{background:#eceff1;color:#1c2a26;}.ev-st.CANCELLED{background:#fdeceb;color:#c0392b;}
.ev-empty{background:#fff;border:1px solid #e7e9e6;border-radius:14px;padding:40px 20px;text-align:center;color:#6b7570;}
.ev-empty i{font-size:30px;color:#c3cac5;display:block;margin-bottom:8px;}
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
#cardInfoModal .modal-content{border:0;border-radius:16px;overflow:hidden;box-shadow:0 24px 60px rgba(8,30,20,.25);}
#cardInfoModal .modal-header{padding:22px 26px;border-bottom:1px solid #e7e9e6;align-items:center;}
#cardInfoModal .head-icon{width:42px;height:42px;border-radius:11px;background:#e8f1f9;color:#4a7fa8;display:flex;align-items:center;justify-content:center;font-size:19px;flex:none;margin-right:14px;}
#cardInfoModal .modal-title{font-size:18px;font-weight:700;color:#101c26;}
#cardInfoModal #cardInfoCount{font-size:12.5px;color:#6b7570;}
#cardInfoModal .modal-body{padding:18px 26px;background:#f9faf9;}
#cardInfoModal .ci-search{position:relative;padding:12px 26px;background:#fff;border-bottom:1px solid #eef0ef;}
#cardInfoModal .ci-search i{position:absolute;left:40px;top:50%;transform:translateY(-50%);color:#8a938d;}
#cardInfoModal .ci-search input{width:100%;border:1.5px solid #dfe5e2;border-radius:9px;padding:9px 12px 9px 36px;font-size:13.5px;outline:none;}
#cardInfoModal .ci-search input:focus{border-color:var(--lime);box-shadow:0 0 0 3px rgba(144,227,101,.18);}
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
<div><h2 class="page-title"><span class="page-icon c-blue"><i class="bi bi-grid-1x2"></i></span>Dashboard</h2><p class="dash-sub">Selamat datang di USC Click Event — PT. United Steel Center Indonesia</p></div>
<div class="date-widget"><i class="bi bi-calendar3"></i><div><div class="dw-date"><?=e($todayLabel)?></div><div class="dw-time"><?=e($timeLabel)?></div></div></div>
</div>
<?php if(!$ev):?>
<div class="ev-empty"><i class="bi bi-calendar-x"></i>Belum ada event yang dipublikasikan. Data dashboard akan tampil setelah ada event berstatus PUBLISHED.</div>
<?php else:$__d=(int)round((strtotime($ev['event_date'])-strtotime(date('Y-m-d')))/86400);?>
<div class="ev-bar">
 <div class="ev-pick"><div class="ev-lbl"><i class="bi bi-funnel"></i>Pilih Event</div><div class="select-wrap"><i class="bi bi-calendar-event ev-ico"></i><select id="dashEvent" class="form-select" data-auto-static data-icon="bi-calendar-event" aria-label="Pilih event"><?php foreach($events as $e):?><option value="<?=$e['id']?>" <?=$e['id']==$ev['id']?'selected':''?>><?=e($e['event_name'])?> — <?=dmy($e['event_date'])?></option><?php endforeach;?></select><i class="bi bi-chevron-down select-arrow"></i></div></div>
 <div class="ev-meta">
  <span><i class="bi bi-calendar3"></i><?=dmy($ev['event_date'])?> · <?=$__d===0?'Hari ini':($__d>0?'H-'.$__d:'Sudah lewat')?></span>
  <span><i class="bi bi-clock"></i><?=substr($ev['start_time'],0,5)?>–<?=substr($ev['end_time'],0,5)?> <?=e($ev['timezone'])?></span>
  <?php if($ev['location']):?><span><i class="bi bi-geo-alt"></i><?=e($ev['location'])?></span><?php endif;?>
  <span class="ev-st <?=e($ev['status'])?>"><?=e($ev['status'])?></span>
 </div>
</div>
<div class="row g-3 mb-4" data-live="stats"><?php foreach($cards as $idx=>$c):$trend=$c['trend']??null;?><div class="col-6 col-md"><div class="card stat-card p-3 h-100" style="animation-delay:<?=($idx*0.07)?>s;--blob:<?=$cardBlobs[$c['key']]??'#4a7fa8'?>;" data-bs-toggle="modal" data-bs-target="#cardInfoModal" onclick="showCardInfo('<?=e($c['key'])?>','<?=e($c['label'])?>')"><div class="icon-badge <?=$cardColors[$c['key']]??'c-blue'?>"><i class="bi <?=$cardIcons[$c['key']]??'bi-graph-up'?>"></i></div><div class="lbl"><?=e($c['label'])?></div><h2><?=e((string)$c['value'])?><?php if(!empty($c['suffix'])):?><span class="fs-6 text-muted"><?=e($c['suffix'])?></span><?php endif;?></h2><?php if($trend):?><div class="trend c-<?=e($trend['color'])?>"><i class="bi <?=$trendIcons[$trend['icon']]??'bi-dash'?>"></i><span><?=e($trend['text'])?></span></div><?php endif;?></div></div><?php endforeach;?></div>
<?php $P=$panels;?>
<div data-live="panels"><div class="dpanel">
 <div class="dp-head"><span class="dp-ico"><i class="bi bi-diagram-3"></i></span>Kehadiran per Departemen<span class="dp-legend"><i class="lg-ck"></i>Sudah Hadir <i class="lg-no"></i>Belum Hadir</span></div>
 <?php if(!$P['depts']):?><div class="dp-empty"><i class="bi bi-people"></i>Belum ada peserta diundang di event ini.</div>
 <?php else:$mx=max(array_column($P['depts'],'invited'))?:1;?>
 <div class="vc-chart" role="img" aria-label="Grafik sudah hadir dan belum hadir per departemen">
  <?php foreach($P['depts'] as $d):$h=(int)$d['hadir'];$b=(int)$d['invited']-$h;$pc=round($h/$d['invited']*100);?>
  <div class="vc-col" title="<?=e($d['dept'])?>: <?=$h?> hadir · <?=$b?> belum (<?=$pc?>%)">
   <span class="vc-top"><b><?=$h?></b>/<?=$d['invited']?></span>
   <div class="vc-stack" style="height:<?=round($d['invited']/$mx*100,2)?>%">
    <?php if($b):?><div class="vc-seg no" style="flex:<?=$b?>"></div><?php endif;?>
    <?php if($h):?><div class="vc-seg ck" style="flex:<?=$h?>"></div><?php endif;?>
   </div>
   <span class="vc-x"><?=e($d['dept'])?></span>
  </div>
  <?php endforeach;?>
 </div>
 <?php endif;?>
</div></div>
<?php endif;?>
<div class="modal fade" id="cardInfoModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered modal-lg"><div class="modal-content">
<div class="modal-header"><div class="head-icon" id="cardInfoIcon"><i class="bi bi-graph-up"></i></div><div class="flex-grow-1"><h5 class="modal-title mb-0" id="cardInfoLabel"></h5><span id="cardInfoCount"></span></div><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="ci-search"><i class="bi bi-search"></i><input type="text" id="cardInfoQ" placeholder="Cari nama, NIK, atau departemen" autocomplete="off"></div>
<div class="modal-body" style="max-height:60vh;overflow:auto" id="cardInfoList"></div>
<div class="modal-footer d-flex justify-content-end align-items-center gap-2"><?php if($isAdmin):?><a href="#" id="cardInfoExport" class="btn-export" target="_blank"><i class="bi bi-download me-1"></i>Export Excel</a><?php endif;?><button type="button" class="btn-close-modal" data-bs-dismiss="modal">Tutup</button></div>
</div></div></div>
<script>
const CARD_ICONS=<?=json_encode($cardIcons)?>;
const DASH_EVENT=<?=(int)($ev['id']??0)?>;
document.getElementById('dashEvent')?.addEventListener('change',e=>{location.href=BASE+'/dashboard?event_id='+e.target.value;});
Live.on(['att','souv','inv','evt','emp','alloc'],()=>Live.swap(['stats','panels']));
const BADGE_COLORS={success:'#1f7a4c',warning:'#c9950a',danger:'#c0392b',secondary:'#6b7570'};
function esc(s){return (s??'').toString().replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));}
let ciItems=[];
function renderCardInfo(){
 const q=(document.getElementById('cardInfoQ').value||'').trim().toLowerCase();
 const items=q?ciItems.filter(it=>[it.title,...it.fields.map(f=>f.value)].join(' ').toLowerCase().includes(q)):ciItems;
 document.getElementById('cardInfoCount').textContent=(q?items.length+' dari ':'')+ciItems.length+' data';
 const list=document.getElementById('cardInfoList');
 if(!items.length){list.innerHTML='<div class="empty-state"><i class="bi bi-inbox"></i>'+(ciItems.length?'Tidak ada yang cocok dengan pencarian.':'Tidak ada data.')+'</div>';return;}
 list.innerHTML=items.map(it=>{
  const fields=it.fields.map(f=>'<div class="col-6"><span class="field-lbl">'+esc(f.label)+'</span><span class="field-val">'+esc(f.value||'-')+'</span></div>').join('');
  const color=BADGE_COLORS[it.badgeColor]||'#6b7570';
  return '<div class="item-row"><div class="title-row"><strong>'+esc(it.title)+'</strong><span class="badge-pill" style="background:'+color+'">'+esc(it.badge)+'</span></div><div class="row g-2">'+fields+'</div></div>';
 }).join('');
}
// Daftar nama diambil saat kartu diklik, supaya halaman tetap ringan walau peserta ratusan.
async function showCardInfo(key,label){
 document.getElementById('cardInfoLabel').textContent=label;
 document.getElementById('cardInfoIcon').innerHTML='<i class="bi '+(CARD_ICONS[key]||'bi-graph-up')+'"></i>';
 const ex=document.getElementById('cardInfoExport');if(ex)ex.href=BASE+'/dashboard/export/'+key+'?event_id='+DASH_EVENT;
 document.getElementById('cardInfoQ').value='';ciItems=[];
 document.getElementById('cardInfoCount').textContent='Memuat...';
 document.getElementById('cardInfoList').innerHTML='<div class="empty-state"><i class="bi bi-hourglass-split"></i>Memuat data...</div>';
 try{ciItems=await (await fetch(BASE+'/dashboard/card/'+key+'?event_id='+DASH_EVENT)).json();}catch(e){ciItems=[];}
 renderCardInfo();
}
document.getElementById('cardInfoQ').addEventListener('input',renderCardInfo);
</script>
<?php require __DIR__.'/layout/footer.php';