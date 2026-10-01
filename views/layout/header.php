<?php
$__uri='/'.trim(parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH)??'','/');
if(BASE_URL!==''&&str_starts_with($__uri,BASE_URL))$__uri='/'.trim(substr($__uri,strlen(BASE_URL)),'/');
$__pageLabels=[
 '/dashboard'=>'Dashboard','/employees/import'=>'Import Karyawan','/employees/create'=>'Tambah Karyawan','/employees'=>'Karyawan',
 '/events/create'=>'Buat Event','/events'=>'Event','/souvenirs/create'=>'Tambah Souvenir','/souvenirs'=>'Souvenir',
 '/scanner/attendance'=>'Scanner Kehadiran','/scanner/souvenir'=>'Scanner Souvenir',
 '/reports/attendance'=>'Laporan Kehadiran','/reports/souvenir'=>'Laporan Souvenir','/profile'=>'Profil Saya',
 '/users/create'=>'Tambah Pengguna','/users'=>'Pengguna',
];
$__pageTitle=$__pageLabels[$__uri]??null;
if($__pageTitle===null){foreach($__pageLabels as $__prefix=>$__label){if(str_starts_with($__uri,$__prefix.'/')){$__pageTitle=$__label;break;}}}
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?=e($__pageTitle?($__pageTitle.' — Sistem Event'):env('APP_NAME','Event Attendance'))?></title>
<link rel="icon" type="image/png" href="<?=BASE_URL?>/assets/logo.png">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,500&display=swap" rel="stylesheet">
<style>
:root{--forest:#022d36;--forest-deep:#011a20;--accent:#167320;--accent-soft:#eef8e6;--lime:#90e365;--lime-bright:#c6ff7f;--lime-hover:#a6e983;--ink:#0f2429;--font-main:'Plus Jakarta Sans',-apple-system,'Segoe UI',Helvetica,Arial,sans-serif;}
*{box-sizing:border-box}
@media (prefers-reduced-motion: no-preference){
@view-transition{navigation:auto;}
::view-transition-old(root),::view-transition-new(root){animation-duration:.28s;animation-timing-function:cubic-bezier(.22,.61,.36,1);}
.sidebar{view-transition-name:app-sidebar;}
.topbar{view-transition-name:app-topbar;position:relative;z-index:100;}
}
body{background:#f4f7f7;font-family:var(--font-main);color:var(--ink);letter-spacing:-.005em;}
.app-shell{display:flex;min-height:100vh;}
.sidebar{width:264px;flex:none;height:100vh;position:sticky;top:0;align-self:flex-start;background:var(--forest);color:#dcefe4;display:flex;flex-direction:column;overflow-y:auto;overflow-x:hidden;transition:width .22s cubic-bezier(.22,.61,.36,1);}
.sidebar::before{content:"";position:absolute;inset:0;background-image:radial-gradient(circle,#ffffff10 1px,transparent 1px);background-size:20px 20px;opacity:.5;pointer-events:none;}
.sidebar .brand{position:relative;z-index:1;display:flex;align-items:center;gap:11px;padding:20px;border-bottom:1px solid #ffffff1f;}
.sidebar .brand .mark{width:34px;height:34px;background:#fff;border-radius:9px;display:flex;align-items:center;justify-content:center;flex:none;}
.sidebar .brand .mark img{width:72%;}
.sb-close{display:none;margin-left:auto;background:#ffffff14;border:0;color:#fff;width:34px;height:34px;border-radius:8px;align-items:center;justify-content:center;font-size:15px;cursor:pointer;}
.sb-close:hover{background:#ffffff26;}
.tb-brand{display:none;font-weight:700;font-size:14.5px;color:var(--forest);letter-spacing:.02em;}
.sidebar .brand .txt{font-weight:700;font-size:15px;letter-spacing:.05em;color:#fff;text-transform:uppercase;line-height:1.3;}
.sidebar .brand .txt small{display:block;font-size:11.5px;letter-spacing:.03em;color:var(--lime-bright);font-weight:600;text-transform:none;margin-top:2px;}
.sidebar nav{position:relative;z-index:1;flex:1;padding:16px 10px;display:flex;flex-direction:column;gap:2px;}
.sidebar .nav-label{font-size:10px;letter-spacing:.08em;text-transform:uppercase;color:#6f9aa3;font-weight:700;padding:8px 12px 6px;}
.sidebar nav a{display:flex;align-items:center;gap:11px;color:#b9d3d8;padding:10px 12px;border-radius:8px;text-decoration:none;font-size:13.5px;transition:background .15s,color .15s;position:relative;}
.sidebar nav a i{font-size:16px;width:18px;text-align:center;flex:none;}
.sidebar nav a:hover{background:#ffffff12;color:#fff;}
.sidebar nav a.active{background:rgba(198,255,127,.10);color:#fff;font-weight:600;}
.sidebar nav a.active::before{content:"";position:absolute;left:0;top:8px;bottom:8px;width:3px;border-radius:3px;background:var(--nc,var(--lime-bright));}
.sidebar nav a.nav-blue{--nc:#a9cfe3;}
.sidebar nav a.nav-purple{--nc:#cbbfe8;}
.sidebar nav a.nav-orange{--nc:#ecc9a0;}
.sidebar nav a.nav-red{--nc:#eab3ab;}
.sidebar nav a.nav-green{--nc:var(--lime-bright);}
.sidebar nav a.active i,.sidebar nav a:hover i{color:var(--nc,#fff);}
.sidebar .logout-row{position:relative;z-index:1;border-top:1px solid #ffffff1f;padding:10px;}
.sidebar .logout-row a{display:flex;align-items:center;gap:11px;color:#f0a8a0;padding:10px 12px;border-radius:8px;text-decoration:none;font-size:13.5px;font-weight:600;transition:background .15s,color .15s;}
.sidebar .logout-row a:hover{background:#c0392b26;color:#ff8a7d;}
@media(min-width:769px){
.sidebar.collapsed{width:76px;}
.sidebar.collapsed .brand{justify-content:center;padding:20px 8px;gap:0;}
.sidebar.collapsed .brand .txt{display:none;}
.sidebar.collapsed .nav-label{display:none;}
.sidebar.collapsed nav{padding:16px 10px;align-items:center;}
.sidebar.collapsed nav a{justify-content:center;padding:12px 0;width:44px;}
.sidebar.collapsed nav a span{display:none;}
.sidebar.collapsed nav a.active::before{left:auto;right:-10px;}
.sidebar.collapsed .logout-row a{justify-content:center;padding:12px 0;}
.sidebar.collapsed .logout-row a span{display:none;}
}
.main{flex:1;min-width:0;padding:28px 32px;}
.main .card{border:1px solid #e7e9e6;box-shadow:0 1px 3px #00000008;border-radius:12px;}
.topbar{display:flex;align-items:center;justify-content:space-between;gap:16px;background:#fff;border-bottom:1px solid #e7e9e6;padding:14px 32px;margin:-28px -32px 24px;}
.topbar .tb-left{display:flex;align-items:center;gap:14px;min-width:0;}
.topbar .tb-burger{background:none;border:0;color:#3d453f;font-size:18px;padding:4px;cursor:pointer;line-height:1;flex:none;}
.topbar .tb-burger:hover{color:var(--accent);}
.topbar .tb-burger i{display:inline-block;transition:opacity .2s ease;}
.topbar .tb-org{font-size:14px;font-weight:600;color:#0d1f17;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.topbar .user-chip{display:flex;align-items:center;gap:11px;background:none;border:0;padding:6px 10px 6px 6px;border-radius:12px;cursor:pointer;transition:background .15s;}
.topbar .user-chip:hover{background:#f1f6f5;}
.topbar .user-chip .avatar{width:36px;height:36px;border-radius:50%;background:var(--forest);color:var(--lime-bright);font-weight:700;font-size:15px;display:flex;align-items:center;justify-content:center;flex:none;}
.topbar .user-chip .u-info{display:flex;flex-direction:column;align-items:flex-start;line-height:1.3;}
.topbar .user-chip .u-name{font-size:14.5px;font-weight:700;color:#0d1f17;}
.topbar .user-chip .u-role{font-size:12.5px;color:#6b7570;}
.topbar .user-chip .bi-chevron-down{font-size:12px;color:#9aa19c;margin-left:2px;transition:transform .25s cubic-bezier(.22,.61,.36,1);}
.topbar .user-chip.show .bi-chevron-down{transform:rotate(180deg);}
.topbar .dropdown-menu{border:1px solid #e7e9e6;border-radius:12px;box-shadow:0 14px 34px rgba(13,31,23,.14);padding:8px;margin-top:10px!important;min-width:190px;}
.topbar .dropdown-menu .dropdown-item{border-radius:8px;padding:9px 12px;font-size:13.5px;font-weight:600;display:flex;align-items:center;transition:background .15s,color .15s;}
.topbar .dropdown-menu .dropdown-item:hover{background:#fbe9e7;color:#a3372b;}
@media(max-width:768px){.sidebar{position:fixed;top:0;left:0;bottom:0;height:100%;width:272px;max-width:84vw;z-index:1060;transform:translateX(-100%);transition:transform .25s cubic-bezier(.22,.61,.36,1);box-shadow:none;}.sidebar.open{transform:none;box-shadow:12px 0 40px rgba(0,0,0,.28);}.sb-close{display:flex!important;}.sb-backdrop{position:fixed;inset:0;background:rgba(2,20,24,.45);z-index:1055;opacity:0;pointer-events:none;transition:opacity .25s;}.sb-backdrop.show{opacity:1;pointer-events:auto;}body.sb-lock{overflow:hidden;}.tb-brand{display:flex!important;}.main{padding:20px 16px;}.topbar{margin:-20px -16px 20px;padding:12px 16px;}.topbar .tb-org{display:none;}}

/* ---- Shared page components ---- */
@keyframes fadeInUp{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}
.page-head{animation:fadeInUp .45s cubic-bezier(.22,.61,.36,1) both;}
.data-card,.form-card,.soft-card{animation:fadeInUp .45s cubic-bezier(.22,.61,.36,1) .08s both;}
@media (prefers-reduced-motion: reduce){.page-head,.data-card,.form-card,.soft-card{animation:none;}}
.page-head{display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;margin-bottom:22px;}
.page-title{font-size:24px;font-weight:700;letter-spacing:-.01em;color:var(--ink);margin:0;display:inline-flex;align-items:center;gap:12px;}
.page-title .page-icon{width:36px;height:36px;border-radius:10px;display:inline-flex;align-items:center;justify-content:center;font-size:17px;flex:none;}
.page-icon.c-blue{background:#eaf2f7;color:#4a7fa8;}
.page-icon.c-purple{background:#f1edf9;color:#7a62b8;}
.page-icon.c-green{background:var(--accent-soft);color:#3f8f2a;}
.page-icon.c-orange{background:#fbf1e7;color:#b77a3a;}
.page-icon.c-red{background:#fbecea;color:#b85a4e;}
.btn-brand{background:var(--lime);border:0;color:var(--forest);font-weight:700;border-radius:8px;padding:10px 18px;font-size:13.5px;text-decoration:none;display:inline-flex;align-items:center;gap:6px;transition:background .15s,opacity .15s;}
.btn-brand:hover{background:var(--lime-hover);color:var(--forest);box-shadow:0 6px 16px -6px rgba(88,182,57,.55);}
.btn-brand:disabled{background:#e7e9e6;color:#9aa19c;cursor:not-allowed;}
.btn-brand:disabled:hover{background:#e7e9e6;}
.btn-outline-brand{background:#fff;border:1.5px solid #dfe3e0;color:#3d453f;font-weight:600;border-radius:8px;padding:9px 17px;font-size:13.5px;text-decoration:none;display:inline-flex;align-items:center;gap:6px;transition:background .15s,border-color .15s;}
.btn-outline-brand:hover{background:#f4f6f5;border-color:#c8cfca;color:#1c2a22;}
.btn-outline-brand.c-blue{border-color:#d9d0ef;color:#6a52a8;background:#f5f2fb;}
.btn-outline-brand.c-blue:hover{background:#ece6f7;border-color:#c4b6e6;color:#56418f;}
.btn-outline-brand.c-excel{background:#e9f6ee;border-color:#8fcfa8;color:#1d6f42;box-shadow:0 2px 8px -3px rgba(29,111,66,.35);}
.btn-outline-brand.c-excel i{font-size:15px;}
.btn-outline-brand.c-excel:hover{background:#1d6f42;border-color:#1d6f42;color:#fff;transform:translateY(-1px);}
.btn-brand.c-blue{background:var(--forest);color:#fff;}
.btn-brand.c-blue:hover{background:#0b4a52;color:#fff;box-shadow:none;}
.btn-danger-soft{background:#dc2626;border:1px solid #dc2626;color:#fff;font-weight:600;border-radius:8px;padding:9px 17px;font-size:13.5px;display:inline-flex;align-items:center;gap:6px;box-shadow:0 2px 8px rgba(220,38,38,.28);transition:background .15s,box-shadow .15s,opacity .15s,transform .15s;}
.btn-danger-soft:disabled{opacity:.45;cursor:not-allowed;box-shadow:none;}
.btn-danger-soft:not(:disabled):hover{background:#b91c1c;border-color:#b91c1c;box-shadow:0 4px 14px rgba(220,38,38,.38);transform:translateY(-1px);}
.btn-danger-soft:not(:disabled):active{transform:none;}
.report-toolbar{background:#fff;border:1px solid #e7e9e6;border-radius:14px;padding:16px 18px;margin-bottom:14px;display:flex;flex-direction:column;gap:14px;}
.report-toolbar .rt-row{display:flex;flex-wrap:wrap;gap:14px 18px;align-items:flex-end;}
.report-toolbar .rt-field{display:flex;flex-direction:column;gap:6px;min-width:0;max-width:100%;}
.report-toolbar .rt-event{flex:0 1 340px;}
.report-toolbar .rt-grow{flex:1 1 300px;}
.report-toolbar .rt-label{font-size:11px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:#6b7570;}
.report-toolbar .form-select{padding-top:9px;padding-bottom:9px;}
.report-toolbar .rt-hint{display:flex;gap:7px;align-items:flex-start;font-size:12px;color:#7c8b86;border-top:1px dashed #e7e9e6;padding-top:12px;}
.report-toolbar .rt-hint i{color:var(--accent);margin-top:1px;}
.report-summary{display:flex;flex-wrap:wrap;align-items:center;gap:6px 8px;font-size:13px;color:#4b5563;margin:0 2px 12px;}
.report-summary b{color:var(--ink);}
.report-summary .rs-sep{color:#c3cac5;}
.report-summary .rs-reset{margin-left:auto;display:inline-flex;align-items:center;gap:6px;background:#fff;border:1.5px solid #f3c6c2;color:#b42318;font-weight:600;font-size:12.5px;border-radius:8px;padding:5px 11px;cursor:pointer;transition:background .15s;}
.report-summary .rs-reset:hover{background:#fdeceb;}
.att-tools{display:flex;flex-wrap:wrap;align-items:center;gap:10px;margin-bottom:16px;}
.att-tools .search-wrap{flex:1 1 320px;max-width:480px;}
.seg{display:inline-flex;flex-wrap:nowrap;align-items:center;max-width:100%;overflow-x:auto;scrollbar-width:none;background:#fff;border:1.5px solid #e7e9e6;border-radius:10px;padding:3px;gap:2px;}
.seg::-webkit-scrollbar{display:none;}
.seg .seg-more{flex:none;}
.seg .seg-more-btn{border:0;background:none;border-radius:7px;padding:7px 11px 7px 13px;font-size:13px;font-weight:600;color:#5b6663;display:inline-flex;align-items:center;gap:7px;cursor:pointer;white-space:nowrap;transition:background .15s,color .15s;}
.seg .seg-more-btn .lbl{display:inline-flex;align-items:center;gap:7px;}
.seg .seg-more-btn:hover{background:#f1f6f5;color:var(--ink);}
.seg .seg-more-btn .n{font-size:11px;font-weight:700;padding:1px 7px;border-radius:20px;background:#eef1f0;color:#5b6663;}
.seg .seg-more-btn.on{background:var(--forest);color:#fff;}
.seg .seg-more-btn.on .n{background:rgba(198,255,127,.18);color:var(--lime-bright);}
.seg .seg-more-btn .bi-chevron-down{font-size:11px;transition:transform .2s;}
.seg .seg-more-btn.show .bi-chevron-down{transform:rotate(180deg);}
.seg-menu{width:260px;padding:8px;border:1px solid #e7e9e6;border-radius:12px;box-shadow:0 14px 34px rgba(2,45,54,.14);}
.seg-menu .seg-search{position:relative;margin-bottom:6px;}
.seg-menu .seg-search i{position:absolute;left:11px;top:50%;transform:translateY(-50%);color:#9aa19c;font-size:13px;}
.seg-menu .seg-search input{width:100%;border:1.5px solid #e7e9e6;border-radius:8px;padding:8px 10px 8px 32px;font-size:13px;outline:none;}
.seg-menu .seg-search input:focus{border-color:#58b639;box-shadow:0 0 0 3px rgba(144,227,101,.22);}
.seg-menu .seg-list{max-height:260px;overflow-y:auto;}
.seg-menu .seg-opt{display:flex;align-items:center;gap:9px;width:100%;border:0;background:none;border-radius:8px;padding:8px 10px;font-size:13px;font-weight:600;color:#3d453f;text-align:left;cursor:pointer;}
.seg-menu .seg-opt:hover{background:#f1f6f5;}
.seg-menu .seg-opt.on{background:var(--forest);color:#fff;}
.seg-menu .seg-opt .nm{flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.seg-menu .seg-opt .n{font-size:11px;font-weight:700;padding:1px 7px;border-radius:20px;background:#eef1f0;color:#5b6663;}
.seg-menu .seg-opt.on .n{background:rgba(198,255,127,.18);color:var(--lime-bright);}
.seg-menu .seg-empty{display:none;padding:10px;font-size:12.5px;color:#9aa19c;text-align:center;}
.seg button{border:0;background:none;border-radius:7px;padding:7px 13px;font-size:13px;font-weight:600;color:#5b6663;display:inline-flex;align-items:center;gap:7px;cursor:pointer;transition:background .15s,color .15s;white-space:nowrap;}
.seg button:focus,.seg .seg-more-btn:focus{outline:none;box-shadow:none;}
.seg button:focus-visible,.seg .seg-more-btn:focus-visible{outline:2px solid #58b639;outline-offset:1px;}
.seg button:hover{background:#f1f6f5;color:var(--ink);}
.seg button .n{font-size:11px;font-weight:700;padding:1px 7px;border-radius:20px;background:#eef1f0;color:#5b6663;}
.seg button.on{background:var(--forest);color:#fff;}
.seg button.on .n{background:rgba(198,255,127,.18);color:var(--lime-bright);}
.seg button[data-st=hadir] .dot,.seg button[data-st=belum] .dot{width:7px;height:7px;border-radius:50%;}
.seg button[data-st=hadir] .dot{background:#3f9a2a;}
.seg button[data-st=belum] .dot{background:#9aa19c;}
.seg button .bi{font-size:13px;}
.step-head{display:flex;gap:12px;align-items:flex-start;margin-bottom:14px;}
.step-head .step-no{width:28px;height:28px;border-radius:50%;background:var(--forest);color:var(--lime-bright);font-weight:700;font-size:13px;display:flex;align-items:center;justify-content:center;flex:none;margin-top:1px;}
.step-head h3{font-size:15px;font-weight:700;margin:0 0 2px;}
.step-head p{font-size:12.5px;color:#6b7570;margin:0;line-height:1.5;}
.cam-note{display:flex;gap:7px;align-items:flex-start;font-size:12.5px;color:#7c8b86;margin:10px 0 4px;}
.cam-note i{color:var(--accent);margin-top:1px;}
.static-field{display:flex;align-items:center;gap:9px;min-height:42px;padding:9px 14px;border:1.5px solid #e7e9e6;border-radius:8px;background:#f6f8f7;font-size:13.5px;font-weight:600;color:#101c26;}
.static-field i{color:var(--accent);font-size:15px;}
.search-input{border:1.5px solid #e7e9e6;border-radius:8px;padding:10px 14px;font-size:13.5px;outline:none;transition:border-color .15s;}
.search-wrap{position:relative;}
.search-wrap .search-input{padding-left:38px;}
.search-wrap i{position:absolute;left:13px;top:50%;transform:translateY(-50%);color:#9aa19c;font-size:14px;pointer-events:none;}
.search-input:focus{border-color:#58b639;box-shadow:0 0 0 3px rgba(144,227,101,.22);}
.data-card{border:1px solid #e7e9e6;border-radius:12px;overflow:hidden;background:#fff;}
.data-table{width:100%;border-collapse:collapse;}
.data-table th{font-size:11.5px;text-transform:uppercase;letter-spacing:.04em;color:#6b7570;font-weight:700;padding:13px 16px;border-bottom:1px solid #e7e9e6;text-align:left;background:#fafbfa;}
.data-table td{font-size:14px;color:#1c2a22;padding:13px 16px;border-bottom:1px solid #f0f2f0;}
.data-table tbody tr{transition:background .12s;}
.data-table tbody tr:hover{background:#f9faf9;}
.data-table tbody tr:last-child td{border-bottom:0;}
.badge-pill{font-size:10.5px;font-weight:700;letter-spacing:.03em;padding:5px 10px;border-radius:20px;color:#fff;display:inline-block;}
.dt-cell{display:flex;flex-direction:column;line-height:1.35;}
.dt-cell .dt-date{font-weight:600;color:var(--ink);font-size:13px;}
.dt-cell .dt-time{font-size:11.5px;color:#9aa19c;}
.btn-edit{border:1.5px solid var(--accent);color:var(--accent);background:#fff;font-weight:600;font-size:11.5px;padding:4px 10px;border-radius:6px;text-decoration:none;transition:background .15s,color .15s;display:inline-flex;align-items:center;gap:4px;}
.btn-edit:hover{background:var(--accent);color:#fff;}
.btn-edit.c-blue{border-color:#8a74c4;color:#6a52a8;}
.btn-edit.c-blue:hover{background:#6a52a8;color:#fff;}
.btn-edit.c-amber{border-color:#b8860b;color:#b8860b;}
.btn-edit.c-amber:hover{background:#b8860b;color:#fff;}
.form-card{background:#fff;border:1px solid #e7e9e6;border-radius:14px;padding:32px;max-width:800px;}
.form-card label{display:block;font-size:12.5px;font-weight:600;color:#3d453f;margin-bottom:7px;}
.main .form-control,.main .form-select{border:1.5px solid #e7e9e6;border-radius:8px;padding:11px 14px;font-size:14px;color:var(--ink);transition:border-color .15s,box-shadow .15s;}
.main .form-control:focus,.main .form-select:focus{border-color:#58b639;box-shadow:0 0 0 3px rgba(144,227,101,.28);}
.main .form-control:disabled{background:#f4f6f5;color:#8a938d;}
.select-wrap{position:relative;display:block;}
.select-wrap select{appearance:none;-webkit-appearance:none;-moz-appearance:none;background-image:none!important;padding-right:34px!important;width:100%;}
.select-wrap .select-arrow{position:absolute;right:12px;top:50%;transform:translateY(-50%);pointer-events:none;color:var(--accent);font-size:12px;transition:transform .3s cubic-bezier(.22,.61,.36,1);}
.select-wrap select:focus~.select-arrow{transform:translateY(-50%) rotate(180deg);}
.soft-card{background:#fff;border:1px solid #e7e9e6;border-radius:14px;padding:24px;}
.alert-soft-warn{background:#fff8e6;border:1px solid #f4dfa0;color:#7a5c00;border-radius:12px;padding:16px 18px;font-size:13.5px;line-height:1.6;}
.count-pill{background:var(--forest);color:var(--lime-bright);font-size:11px;font-weight:700;padding:2px 9px;border-radius:20px;}
.alert-soft-success{background:#eaf6ee;border:1px solid #b9e2c6;color:#1f7a4c;border-radius:10px;padding:12px 16px;font-size:13.5px;}
.scan-res{--c:#1f8a4c;--bg:#e9f8ee;--ring:rgba(31,138,76,.28);display:flex;align-items:center;gap:12px;padding:10px 14px;border-radius:10px;background:var(--bg);border:1.5px solid var(--c);border-left-width:5px;box-shadow:0 6px 16px -10px var(--ring);position:relative;overflow:hidden;}
.scan-res.warn{--c:#d97706;--bg:#fff5e6;--ring:rgba(217,119,6,.3);}
.scan-res.err{--c:#dc2626;--bg:#fdeceb;--ring:rgba(220,38,38,.3);}
.scan-res .sr-ico{flex:0 0 auto;width:38px;height:38px;border-radius:50%;background:var(--c);color:#fff;display:flex;align-items:center;justify-content:center;font-size:19px;box-shadow:0 0 0 0 var(--ring);}
.scan-res .sr-body{min-width:0;flex:1;}
.scan-res .sr-title{font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:var(--c);margin-bottom:2px;}
.scan-res .sr-name{font-size:17px;font-weight:800;color:#12241c;line-height:1.2;word-break:break-word;}
.scan-res .sr-meta{font-size:12px;color:#4b5a52;margin-top:1px;display:flex;flex-wrap:wrap;gap:4px 14px;}
.scan-res .sr-meta i{color:var(--c);margin-right:4px;}
.scan-res .sr-msg{font-size:12.5px;font-weight:600;color:var(--c);margin-top:3px;}
.scan-res .sr-note{font-weight:800;text-decoration:underline;text-underline-offset:2px;}
.scan-res .sr-time{position:absolute;top:8px;right:12px;font-size:11px;color:#7a857f;}
@media (prefers-reduced-motion: no-preference){
 .scan-res.play{animation:srPop .45s cubic-bezier(.2,1.4,.4,1);}
 .scan-res.play .sr-ico{animation:srRing 1.1s ease-out 2;}
 .scan-res.err.play{animation:srPop .45s cubic-bezier(.2,1.4,.4,1),srShake .4s .45s;}
}
@keyframes srPop{0%{transform:scale(.92);opacity:0}100%{transform:scale(1);opacity:1}}
@keyframes srRing{0%{box-shadow:0 0 0 0 var(--ring)}100%{box-shadow:0 0 0 12px transparent}}
@keyframes srShake{0%,100%{transform:translateX(0)}20%,60%{transform:translateX(-6px)}40%,80%{transform:translateX(6px)}}
@media (max-width:576px){.scan-res{gap:10px;padding:10px 12px}.scan-res .sr-ico{width:34px;height:34px;font-size:17px}.scan-res .sr-name{font-size:16px}.scan-res .sr-time{position:static;display:block;margin-top:6px}}
.alert-soft-danger{background:#fdeceb;border:1px solid #f3c6c2;color:#a3372b;border-radius:10px;padding:12px 16px;font-size:13.5px;}
.toast-stack{position:fixed;top:18px;right:18px;z-index:1095;display:flex;flex-direction:column;gap:10px;width:min(420px,calc(100vw - 32px));pointer-events:none;}
.toast-stack:empty{display:none;}
@keyframes toastSlide{from{opacity:0;transform:translateX(24px) scale(.97)}to{opacity:1;transform:none}}
@keyframes toastBar{from{transform:scaleX(1)}to{transform:scaleX(0)}}
.flash-msg{position:relative;overflow:hidden;pointer-events:auto;display:flex;align-items:flex-start;gap:11px;border-radius:12px;padding:13px 14px 14px 16px;font-size:13.5px;line-height:1.5;background:#fff!important;box-shadow:0 16px 38px -12px rgba(2,45,54,.35),0 2px 6px rgba(2,45,54,.08);animation:toastSlide .35s cubic-bezier(.22,.61,.36,1) both;transition:opacity .28s,transform .28s;}
.flash-msg.out{opacity:0;transform:translateX(24px);}
.flash-msg .fm-body{flex:1;min-width:0;color:#3d453f;max-height:40vh;overflow:auto;}
.flash-msg .fm-title{font-weight:700;font-size:13.5px;margin-bottom:1px;}
.flash-msg .fm-bar{position:absolute;left:0;right:0;bottom:0;height:3px;background:currentColor;opacity:.35;transform-origin:left;animation:toastBar 5s linear forwards;}
@media(max-width:560px){.toast-stack{top:12px;right:16px;left:16px;width:auto;}}
.flash-msg .flash-icon{font-size:16px;flex:none;margin-top:1px;}
.flash-msg .flash-close{margin-left:auto;background:none;border:0;color:inherit;opacity:.55;cursor:pointer;font-size:15px;line-height:1;padding:2px;flex:none;transition:opacity .15s;}
.flash-msg .flash-close:hover{opacity:1;}
.flash-msg.flash-success{border:1px solid #b9e2c6;border-left:4px solid #1f7a4c;color:#1f7a4c;}
.flash-msg.flash-error{border:1px solid #f3c6c2;border-left:4px solid #c0392b;color:#b42318;}
@media (prefers-reduced-motion: reduce){.flash-msg,.flash-msg .fm-bar{animation:none;}}
.info-note{background:#eaf4f4;border:1px solid #c6dfe0;color:#0b5560;border-radius:10px;padding:11px 14px;font-size:13px;display:flex;align-items:flex-start;gap:9px;margin-bottom:22px;}
.info-note i{margin-top:1px;flex:none;}
.req-star{color:#c0392b;}
.scan-group{display:flex;max-width:520px;}
.scan-group input{border-top-right-radius:0;border-bottom-right-radius:0;border-right:0;}
.scan-group button{border-top-left-radius:0;border-bottom-left-radius:0;flex:none;}
#reader{border:1.5px dashed #dfe3e0;border-radius:12px;padding:24px 20px;background:#fafbfa;text-align:center;}
#reader img,#reader svg{max-width:56px;opacity:.35;margin:0 auto;}
#reader select{border:1.5px solid #e7e9e6;border-radius:8px;padding:8px 12px;font-size:13px;color:var(--ink);background:#fff;margin:6px 0;}
#reader span{font-size:12px;color:#9aa19c;}
#reader button{background:var(--lime);border:0;color:var(--forest);font-weight:600;font-size:13px;padding:9px 20px;border-radius:8px;cursor:pointer;transition:background .15s;margin:10px 0 6px;}
#reader button:hover{background:var(--lime-hover);}
#reader a{color:var(--accent);font-weight:600;font-size:12.5px;text-decoration:none;display:inline-block;margin-top:4px;}
#reader a:hover{text-decoration:underline;}
#reader__dashboard_section_csr,#reader__dashboard_section_swaplink{display:flex;flex-direction:column;align-items:center;}
#reader__scan_region{background:transparent!important;}
.live-notice{position:fixed;left:50%;bottom:22px;transform:translateX(-50%);z-index:1090;display:flex;align-items:center;gap:10px;background:var(--forest);color:#fff;padding:10px 12px 10px 16px;border-radius:12px;box-shadow:0 12px 32px rgba(0,0,0,.25);font-size:13px;animation:liveIn .3s cubic-bezier(.22,.61,.36,1);max-width:calc(100% - 32px);}
.live-notice i{color:var(--lime-bright);font-size:16px;}
.live-notice button{background:var(--lime);border:0;color:var(--forest);font-weight:600;font-size:12.5px;border-radius:8px;padding:6px 12px;cursor:pointer;white-space:nowrap;}
.live-notice button.ln-x{background:none;padding:2px 6px;font-size:18px;line-height:1;color:#94b8c3;}
@keyframes liveIn{from{opacity:0;transform:translate(-50%,12px)}to{opacity:1;transform:translate(-50%,0)}}
.live-swapped .stat-card,.live-swapped .stat-card .icon-badge,.live-swapped.recent-card{animation:none!important;}
</style><script>window.BASE=<?=json_encode(BASE_URL,JSON_UNESCAPED_SLASHES)?>;</script><script src="<?=BASE_URL?>/assets/live.js?v=2"></script></head><body>
<?php if(\App\Auth::check()):
 $uri='/'.trim(parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH)??'','/');
 if(BASE_URL!==''&&str_starts_with($uri,BASE_URL))$uri='/'.trim(substr($uri,strlen(BASE_URL)),'/');
 $navGroups=[
  'Menu Utama'=>[['/dashboard','Dashboard','bi-grid-1x2','blue'],['/employees','Karyawan','bi-people','blue'],['/events','Event','bi-calendar-event','purple'],['/souvenirs','Souvenir','bi-gift','red']],
  'Scanner'=>[['/scanner/attendance','Scanner Kehadiran','bi-qr-code-scan','orange'],['/scanner/souvenir','Scanner Souvenir','bi-upc-scan','red']],
  'Laporan'=>[['/reports/attendance','Laporan Kehadiran','bi-clipboard-data','orange'],['/reports/souvenir','Laporan Souvenir','bi-file-earmark-bar-graph','red']],
  'Pengaturan'=>[['/users','Pengguna','bi-person-gear','blue']],
 ];
 // Operator hanya melihat menu Scanner & Laporan.
 if(!\App\Auth::isAdmin()){unset($navGroups['Menu Utama'],$navGroups['Pengaturan']);if(\App\Auth::user()['role']!=='EVENT_OPERATOR')unset($navGroups['Scanner']);}
?><div class="app-shell"><aside class="sidebar" id="mainSidebar"><div class="brand"><div class="mark"><img src="<?=BASE_URL?>/assets/logo.png" alt="USC" onerror="this.parentElement.style.display='none'"></div><div class="txt">Sistem Event</div><button type="button" class="sb-close" onclick="toggleSidebar()" aria-label="Tutup menu"><i class="bi bi-x-lg"></i></button></div>
<nav><?php foreach($navGroups as $groupLabel=>$items):?><div class="nav-label"><?=e($groupLabel)?></div><?php foreach($items as [$href,$label,$icon,$color]):$isActive=$uri===$href||str_starts_with($uri,$href.'/');?><a href="<?=BASE_URL.$href?>" class="<?=$isActive?'active':''?> nav-<?=$color?>" title="<?=e($label)?>"><i class="bi <?=$icon?>"></i><span><?=$label?></span></a><?php endforeach;endforeach;?></nav>
</aside><div class="sb-backdrop" id="sbBackdrop" onclick="toggleSidebar(false)"></div>
<script>
// Desktop: sidebar bisa diciutkan (diingat). HP/tablet (<=768px): sidebar jadi menu laci yang muncul dari kiri.
const __sbMobile=()=>window.matchMedia('(max-width:768px)').matches;
try{if(localStorage.getItem('sidebarCollapsed')==='1')document.getElementById('mainSidebar').classList.add('collapsed');}catch(e){}
function toggleSidebar(force){
 const s=document.getElementById('mainSidebar'),b=document.getElementById('sbBackdrop');
 if(__sbMobile()){const open=typeof force==='boolean'?force:!s.classList.contains('open');s.classList.toggle('open',open);b.classList.toggle('show',open);document.body.classList.toggle('sb-lock',open);return;}
 s.classList.toggle('collapsed');try{localStorage.setItem('sidebarCollapsed',s.classList.contains('collapsed')?'1':'0');}catch(e){}
}
document.addEventListener('keydown',e=>{if(e.key==='Escape')toggleSidebar(false);});
window.matchMedia('(max-width:768px)').addEventListener('change',()=>toggleSidebar(false));
document.querySelectorAll('#mainSidebar nav a').forEach(a=>a.addEventListener('click',()=>{if(__sbMobile())toggleSidebar(false);}));
</script>
<main class="main">
<?php $u=\App\Auth::user();?>
<div class="topbar">
<div class="tb-left"><button type="button" class="tb-burger" onclick="toggleSidebar()" aria-label="Toggle menu"><i class="bi bi-list"></i></button><span class="tb-org">PT. United Steel Center Indonesia</span><span class="tb-brand">Sistem Event</span></div>
<div class="dropdown">
<button type="button" class="user-chip" data-bs-toggle="dropdown" aria-expanded="false"><span class="avatar"><?=e(mb_strtoupper(mb_substr($u['name'],0,1)))?></span><span class="u-info"><span class="u-name"><?=e($u['name'])?></span><span class="u-role"><?=e(\App\Auth::roleLabel($u['role']))?></span></span><i class="bi bi-chevron-down"></i></button>
<ul class="dropdown-menu dropdown-menu-end">
<li><a class="dropdown-item" href="<?=BASE_URL?>/profile"><i class="bi bi-person-circle me-2"></i>Profil Saya</a></li>
<li><hr class="dropdown-divider" style="margin:6px 0;"></li>
<li><a class="dropdown-item text-danger" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal"><i class="bi bi-box-arrow-right me-2"></i>Keluar</a></li>
</ul>
</div>
</div>
<div class="toast-stack" id="toastStack" aria-live="polite"><?php if($m=flash('success')):?><div class="flash-msg flash-success" role="status"><i class="bi bi-check-circle-fill flash-icon"></i><div class="fm-body"><div class="fm-title">Berhasil</div><span><?=$m?></span></div><button type="button" class="flash-close" aria-label="Tutup">&times;</button><i class="fm-bar"></i></div><?php endif;?><?php if($m=flash('error')):?><div class="flash-msg flash-error" role="alert"><i class="bi bi-exclamation-circle-fill flash-icon"></i><div class="fm-body"><div class="fm-title">Gagal</div><span><?=$m?></span></div><button type="button" class="flash-close" aria-label="Tutup">&times;</button></div><?php endif;?></div>
<script>
// Toast: pesan berhasil hilang sendiri setelah 5 detik, pesan gagal tetap sampai ditutup.
function showToast(type,title,msg){
 const t=document.createElement('div');t.className='flash-msg flash-'+(type==='success'?'success':'error');t.setAttribute('role',type==='success'?'status':'alert');
 t.innerHTML='<i class="bi '+(type==='success'?'bi-check-circle-fill':'bi-exclamation-circle-fill')+' flash-icon"></i><div class="fm-body"><div class="fm-title"></div><span></span></div><button type="button" class="flash-close" aria-label="Tutup">&times;</button>'+(type==='success'?'<i class="fm-bar"></i>':'');
 t.querySelector('.fm-title').textContent=title;t.querySelector('span').textContent=msg;
 document.getElementById('toastStack').appendChild(t);window.__toastInit&&window.__toastInit(t);
}
(function(){const close=t=>{t.classList.add('out');setTimeout(()=>t.remove(),280);};window.__toastInit=t=>{t.querySelector('.flash-close').onclick=()=>close(t);if(t.classList.contains('flash-success'))setTimeout(()=>close(t),5000);};document.querySelectorAll('#toastStack .flash-msg').forEach(t=>{t.querySelector('.flash-close').onclick=()=>close(t);if(t.classList.contains('flash-success'))setTimeout(()=>close(t),5000);});})();
</script>
<?php endif;