<?php
// Pola QR dekoratif (bukan QR sungguhan) untuk kartu e-ticket di panel kiri.
$qrCells='';
$isFinder=function(int $x,int $y):bool{foreach([[0,0],[14,0],[0,14]] as [$fx,$fy]){if($x>=$fx-1&&$x<=$fx+7&&$y>=$fy-1&&$y<=$fy+7)return true;}return false;};
for($y=0;$y<21;$y++)for($x=0;$x<21;$x++){if($isFinder($x,$y))continue;if((($x*7+$y*13+$x*$y)%5)<2)$qrCells.='<rect x="'.$x.'" y="'.$y.'" width="1" height="1"/>';}
foreach([[0,0],[14,0],[0,14]] as [$fx,$fy])$qrCells.='<path d="M'.$fx.' '.$fy.'h7v7h-7z M'.($fx+1).' '.($fy+1).'v5h5v-5z" fill-rule="evenodd"/><rect x="'.($fx+2).'" y="'.($fy+2).'" width="3" height="3"/>';
?><!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Masuk — Sistem Invitation USC</title>
<link rel="icon" type="image/png" href="<?=BASE_URL?>/assets/logo.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,500&display=swap" rel="stylesheet">
<script>window.BASE=<?=json_encode(BASE_URL,JSON_UNESCAPED_SLASHES)?>;</script>
<style>
:root{--ink:#0f1f2b;--navy:#0b1b2b;--green:#43a320;--green-d:#167320;--leaf:#c6ff7f;--lime:#90e365;--teal:#022d36;--paper:#f7f9f8;--line:#e3e9e6;--muted:#6b7a75;}
*{box-sizing:border-box}
html,body{margin:0;height:100%;}
body{font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Helvetica,Arial,sans-serif;color:var(--ink);background:var(--paper);letter-spacing:-.005em;}
@media (prefers-reduced-motion: no-preference){
@view-transition{navigation:auto;}
::view-transition-old(root),::view-transition-new(root){animation-duration:.32s;animation-timing-function:cubic-bezier(.22,.61,.36,1);}
}
.wrap{display:grid;grid-template-columns:1fr;min-height:100vh;}
@media(min-width:960px){.wrap{grid-template-columns:1.26fr 1fr;height:100vh;}}

@keyframes riseIn{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}
@keyframes lineDraw{to{transform:scaleX(1)}}
@keyframes spin{to{transform:rotate(360deg)}}
@keyframes shake{10%,90%{transform:translateX(-1px)}20%,80%{transform:translateX(2px)}30%,50%,70%{transform:translateX(-5px)}40%,60%{transform:translateX(5px)}}
@keyframes drift{0%,100%{transform:translate(0,0) scale(1)}50%{transform:translate(var(--dx),var(--dy)) scale(1.08)}}
@keyframes gcIn{from{opacity:0;transform:translate3d(40px,30px,-160px) rotateY(-38deg) rotateX(14deg);filter:blur(8px)}to{opacity:1;transform:none;filter:blur(0)}}
@keyframes float3d{0%,100%{transform:translateY(0) rotateX(0) rotateY(0)}33%{transform:translateY(-8px) rotateX(5deg) rotateY(-6deg)}66%{transform:translateY(-3px) rotateX(-3deg) rotateY(5deg)}}
@keyframes sheen{0%,70%{transform:translateX(-120%) skewX(-18deg)}100%{transform:translateX(220%) skewX(-18deg)}}
@keyframes fillX{from{width:0}}
@keyframes growY{from{transform:scaleY(0)}}
@keyframes scanY{0%,100%{top:4px}50%{top:calc(100% - 6px)}}
@keyframes ping{0%{transform:scale(1);opacity:.7}80%,100%{transform:scale(2.6);opacity:0}}
.anim{animation:riseIn .6s cubic-bezier(.22,.61,.36,1) backwards;}

/* ---- Left: gradient + glass cards ---- */
.brand{position:relative;display:block;overflow:hidden;color:#fff;background:linear-gradient(150deg,#011a20 0%,#022d36 50%,#07413f 100%);}
.glow{position:absolute;border-radius:50%;filter:blur(70px);pointer-events:none;animation:drift var(--t,14s) ease-in-out infinite;}
.glow.g1{width:420px;height:420px;right:-90px;top:-110px;background:rgba(144,227,101,.30);--dx:-40px;--dy:30px;--t:15s;}
.glow.g2{width:380px;height:380px;left:-120px;bottom:-140px;background:rgba(37,167,100,.30);--dx:40px;--dy:-30px;--t:18s;}
.glow.g3{width:300px;height:300px;right:18%;bottom:8%;background:rgba(198,255,127,.18);--dx:-30px;--dy:-24px;--t:12s;}
.gridbg{position:absolute;inset:0;opacity:.4;pointer-events:none;background-image:linear-gradient(rgba(255,255,255,.05) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.05) 1px,transparent 1px);background-size:44px 44px;-webkit-mask-image:radial-gradient(ellipse 80% 70% at 70% 45%,#000 30%,transparent 80%);mask-image:radial-gradient(ellipse 80% 70% at 70% 45%,#000 30%,transparent 80%);}
.brand-inner{position:relative;z-index:2;height:100%;display:flex;flex-direction:column;padding:clamp(32px,5vh,56px) clamp(28px,3vw,48px);}
.b-top{display:flex;align-items:center;gap:22px;}
.b-top img{height:38px;width:auto;display:block;}
.b-top span{font-size:11.5px;letter-spacing:.18em;text-transform:uppercase;font-weight:500;line-height:1.7;color:#e6f0f2;max-width:230px;}
.b-mid{margin-top:auto;margin-bottom:auto;padding:5vh 0 3vh;max-width:560px;}
.kicker{display:flex;align-items:center;gap:16px;margin-bottom:22px;}
.kicker i{display:block;width:46px;height:2px;background:var(--leaf);transform-origin:left;transform:scaleX(0);animation:lineDraw .7s .35s cubic-bezier(.22,.61,.36,1) forwards;}
.kicker span{font-size:11px;letter-spacing:.3em;text-transform:uppercase;color:#bbd6de;font-weight:500;}
.b-mid h2{font-size:clamp(26px,2.5vw,38px);line-height:1.25;font-weight:600;margin:0 0 16px;letter-spacing:-.015em;}
.b-mid h2 em{font-style:normal;color:var(--leaf);}
.b-mid p{font-size:14px;line-height:1.7;color:#d2e0e6;margin:0 0 clamp(24px,4vh,40px);max-width:420px;}
.feats{display:flex;gap:clamp(20px,3vw,44px);flex-wrap:wrap;}
.feat{width:108px;font-size:12.5px;line-height:1.4;color:#eaf3f4;}
.feat .ic{width:48px;height:48px;border-radius:12px;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.12);display:flex;align-items:center;justify-content:center;color:var(--leaf);margin-bottom:12px;transition:background .25s,border-color .25s;}
.feat:hover .ic{background:rgba(98,197,138,.16);border-color:rgba(98,197,138,.5);}
.feat .ic svg{width:22px;height:22px;}
.b-foot{display:flex;flex-direction:column;gap:14px;}
.b-foot i{display:block;width:46px;height:2px;background:var(--leaf);}
.b-foot span{font-size:12px;letter-spacing:.03em;color:#94b8c3;line-height:1.7;}

.stage{position:absolute;z-index:3;right:clamp(28px,3vw,48px);top:50%;transform:translateY(-50%);width:240px;display:none;perspective:1100px;}
.scene{position:relative;display:flex;flex-direction:column;gap:14px;transform-style:preserve-3d;transform:rotateX(calc(10deg + var(--py,0)*-14deg)) rotateY(calc(-18deg + var(--px,0)*20deg));will-change:transform;}
/* bayangan lantai: bergeser berlawanan arah kartu */
.floor{position:absolute;left:10%;right:0;bottom:-46px;height:46px;border-radius:50%;background:radial-gradient(closest-side,rgba(0,0,0,.45),transparent);filter:blur(10px);transform:translate3d(calc(var(--px,0)*-26px),0,0) scaleX(calc(1 - var(--py,0)*.2));opacity:.75;pointer-events:none;}
/* partikel lime */
.sparks{position:absolute;inset:-40px -30px;pointer-events:none;}
.sparks i{position:absolute;bottom:0;width:4px;height:4px;border-radius:50%;background:#c6ff7f;box-shadow:0 0 8px 2px rgba(198,255,127,.55);opacity:0;animation:spark var(--t,9s) linear infinite;animation-delay:var(--d,0s);}
@keyframes spark{0%{transform:translate3d(0,0,0);opacity:0}10%{opacity:.9}80%{opacity:.6}100%{transform:translate3d(var(--x,10px),-420px,0);opacity:0}}
@media(min-width:1180px){
.stage{display:flex;}
.b-mid{max-width:calc(100% - 270px);}
.feats{gap:24px;}
.feat{width:100px;}
}
.gc-wrap{transform-style:preserve-3d;translate:0 0 var(--z,0px);transform:translate3d(calc(var(--px,0)*var(--k,10)*1px),calc(var(--py,0)*var(--k,10)*1px),0);will-change:transform;animation:gcIn 1s cubic-bezier(.22,.61,.36,1) backwards;animation-delay:var(--d,0s);}
.gc-glare{position:absolute;inset:0;border-radius:inherit;pointer-events:none;background:radial-gradient(circle at calc(50% + var(--px,0)*130%) calc(40% + var(--py,0)*130%),rgba(255,255,255,.20),transparent 55%);mix-blend-mode:screen;}
.gc-wrap.pop .gc{animation:float3d var(--f,6s) ease-in-out infinite,cardPop .6s ease;}
@keyframes cardPop{0%{box-shadow:0 0 0 0 rgba(198,255,127,.0)}40%{box-shadow:0 0 0 3px rgba(198,255,127,.45),0 18px 40px -10px rgba(144,227,101,.55)}100%{box-shadow:0 0 0 0 rgba(198,255,127,0)}}
.gc{padding:13px 15px;border-radius:16px;background:rgba(255,255,255,.1);-webkit-backdrop-filter:blur(20px);backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,.2);
 box-shadow:0 0 0 1px rgba(0,0,0,.06),0 1px 1px -.5px rgba(0,0,0,.06),0 3px 3px -1.5px rgba(0,0,0,.06),0 6px 6px -3px rgba(0,0,0,.06),0 12px 12px -6px rgba(0,0,0,.06),0 24px 24px -12px rgba(0,0,0,.14);
 position:relative;overflow:hidden;animation:float3d var(--f,6s) ease-in-out infinite;animation-delay:var(--fd,0s);}
.gc::after{content:"";position:absolute;top:0;bottom:0;left:0;width:40%;background:linear-gradient(90deg,transparent,rgba(255,255,255,.14),transparent);pointer-events:none;animation:sheen var(--sh,7s) ease-in-out infinite;animation-delay:var(--d,0s);}
.gc-wrap:nth-child(1){--z:60px;--k:14;}
.gc-wrap:nth-child(2){--z:120px;--k:26;}
.gc-wrap:nth-child(3){--z:30px;--k:8;}
.gc-wrap:nth-of-type(1){margin-left:-26px;}
.gc-wrap:nth-of-type(2){margin-left:18px;}
.gc-wrap:nth-of-type(3){margin-left:-8px;}
.gc-head{display:flex;align-items:center;gap:8px;font-size:11.5px;font-weight:600;color:#e6f0f2;}
.gc-head .grow{flex:1;}
.gc-sub{font-size:10.5px;color:#94b8c3;margin-top:2px;}
.dot{position:relative;width:7px;height:7px;border-radius:50%;background:#90e365;flex:none;}
.dot.live::after{content:"";position:absolute;inset:0;border-radius:50%;background:#90e365;animation:ping 1.6s cubic-bezier(0,0,.2,1) infinite;}
.pill{font-size:9.5px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;padding:3px 7px;border-radius:20px;background:rgba(198,255,127,.12);color:#c6ff7f;border:1px solid rgba(198,255,127,.35);display:inline-flex;align-items:center;gap:5px;}
.gc-num{font-size:22px;font-weight:700;margin:8px 0 8px;letter-spacing:-.01em;}
.gc-num small{font-size:11px;font-weight:500;color:#94b8c3;margin-left:4px;}
.bar{height:5px;border-radius:5px;background:rgba(255,255,255,.12);overflow:hidden;}
.bar i{display:block;height:100%;width:var(--w);transition:width .8s cubic-bezier(.22,.61,.36,1);border-radius:5px;background:linear-gradient(90deg,#58b639,#c6ff7f);animation:fillX 1.4s cubic-bezier(.22,.61,.36,1) backwards;animation-delay:calc(var(--d,0s) + .4s);}
.ticket{display:flex;align-items:center;gap:12px;}
.qr{position:relative;flex:none;width:58px;height:58px;padding:5px;border-radius:9px;background:#fff;overflow:hidden;}
.qr svg{display:block;width:100%;height:100%;}
.qr .scan{position:absolute;left:3px;right:3px;height:2px;border-radius:2px;background:#90e365;box-shadow:0 0 8px 1px rgba(198,255,127,.85);animation:scanY 2.4s ease-in-out infinite;}
.ticket .t-lbl{font-size:9.5px;letter-spacing:.14em;text-transform:uppercase;color:#94b8c3;font-weight:600;}
.ticket .t-name{font-size:13px;font-weight:700;margin:2px 0 6px;}
.ticket .pill svg{width:10px;height:10px;}
.bars{display:grid;grid-template-columns:repeat(7,1fr);gap:6px;align-items:end;height:52px;margin-top:12px;}
.bars span{height:100%;transform:scaleY(var(--h,.5));transition:transform .9s cubic-bezier(.22,.61,.36,1);display:block;border-radius:4px 4px 2px 2px;background:linear-gradient(180deg,#c6ff7f,rgba(88,182,57,.5));transform-origin:bottom;animation:growY .8s cubic-bezier(.22,.61,.36,1) backwards;}
.bars-lbl{display:grid;grid-template-columns:repeat(7,1fr);gap:6px;margin-top:5px;font-size:9px;color:#8aa9b3;text-align:center;}

/* ---- Right: form ---- */
.formside{position:relative;display:flex;padding:40px 28px;overflow-y:auto;}
.formbox{position:relative;width:100%;max-width:470px;margin:auto;transition:opacity .35s ease,transform .35s cubic-bezier(.22,.61,.36,1);}
.login-toast{display:flex;align-items:center;gap:10px;background:var(--teal);color:#fff;border-radius:12px;padding:11px 16px;font-size:13px;font-weight:600;margin-bottom:14px;box-shadow:0 10px 26px -8px rgba(2,45,54,.45);animation:toastIn .35s cubic-bezier(.22,.61,.36,1) both;}
.login-toast .lt-ic{width:22px;height:22px;border-radius:50%;background:var(--lime);color:var(--teal);display:flex;align-items:center;justify-content:center;flex:none;}
.login-toast .lt-ic svg{width:13px;height:13px;}
.login-toast small{display:block;font-weight:500;color:#bbd6de;font-size:11.5px;margin-top:1px;}
@keyframes toastIn{from{opacity:0;transform:translateY(-8px) scale(.97)}to{opacity:1;transform:none}}
.btn-submit.done{pointer-events:none;background:var(--lime);}
.btn-submit .ok{display:none;width:18px;height:18px;}
.btn-submit.done .ok{display:inline-block;animation:toastIn .3s ease both;}
.btn-submit.done .spin,.btn-submit.done .arr{display:none;}
.input-wrap input:disabled{background:#f6f8f7;color:#8a9893;}
.illus{width:84px;height:72px;margin:0 0 4px -6px;display:block;}
.eyebrow{font-size:11px;letter-spacing:.28em;text-transform:uppercase;color:#7c8b86;font-weight:500;margin-bottom:4px;}
.formbox h1{font-size:clamp(24px,2vw,30px);font-weight:800;margin:0 0 12px;letter-spacing:-.02em;color:var(--navy);}
.formbox p.sub{font-size:13.5px;line-height:1.65;color:var(--muted);margin:0 0 26px;max-width:380px;}
.card{background:#fff;border:1px solid var(--line);border-radius:16px;padding:28px 28px 24px;box-shadow:0 12px 40px rgba(11,27,43,.05);}
.field{margin-bottom:20px;}
.field label{display:flex;align-items:center;gap:8px;font-size:12.5px;font-weight:600;color:var(--ink);margin-bottom:9px;transition:color .18s;}
.field label svg{width:17px;height:17px;color:var(--green);}
.field:focus-within label{color:var(--green);}
.input-wrap{position:relative;}
.input-wrap input{width:100%;padding:12px 14px;border-radius:10px;border:1.5px solid var(--line);background:#fff;color:var(--ink);font:inherit;font-size:13.5px;outline:none;transition:border-color .2s,box-shadow .2s;}
.input-wrap input::placeholder{color:#a3afab;}
.input-wrap input:focus{border-color:var(--green);box-shadow:0 0 0 4px rgba(144,227,101,.28);}
.toggle-eye{position:absolute;right:14px;top:50%;transform:translateY(-50%);width:22px;height:22px;padding:0;background:none;border:0;color:#7c8b86;cursor:pointer;}
.toggle-eye:hover{color:var(--green);}
.toggle-eye svg{position:absolute;inset:2px;transition:opacity .2s,transform .28s cubic-bezier(.22,.61,.36,1);}
.toggle-eye svg.hide{opacity:0;transform:scale(.55) rotate(-25deg);}
.btn-submit{width:100%;padding:13px;margin-top:6px;border:0;border-radius:10px;background:var(--lime);color:var(--teal);font:inherit;font-weight:700;font-size:14px;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:10px;transition:background .18s,transform .18s,box-shadow .18s;}
.btn-submit:hover{background:#a6e983;box-shadow:0 8px 20px -6px rgba(88,182,57,.55);}
.btn-submit .arr{width:18px;height:18px;transition:transform .25s cubic-bezier(.22,.61,.36,1);}
.btn-submit:hover .arr{transform:translateX(4px);}
.btn-submit .spin{display:none;width:16px;height:16px;border:2px solid rgba(2,45,54,.25);border-top-color:#022d36;border-radius:50%;animation:spin .7s linear infinite;}
.btn-submit.loading{pointer-events:none;background:#a6e983;}
.btn-submit.loading .spin{display:inline-block;}
.btn-submit.loading .arr{display:none;}
.alert-err{background:#fdeceb;border:1px solid #f3c6c2;color:#a3372b;padding:11px 14px;border-radius:10px;font-size:13px;margin-bottom:18px;animation:shake .55s cubic-bezier(.36,.07,.19,.97) both;}
.foot{margin-top:18px;text-align:center;font-size:12px;color:#8a9893;}

@media (prefers-reduced-motion: reduce){
.scene,.gc-wrap,.floor{transform:none!important;}
.sparks{display:none;}
.anim,.kicker i,.alert-err,.btn-submit .spin,.glow,.gc-wrap,.gc,.gc::after,.bar i,.bars span,.qr .scan,.dot.live::after{animation:none;}
.kicker i{transform:none;}
.qr .scan{display:none;}
.formbox,.toggle-eye svg{transition:none;}
}
/* ---- Responsive ---- */
@media(min-width:1600px){
.stage{transform:translateY(-50%) scale(1.15);transform-origin:right center;}
.b-mid{max-width:calc(100% - 320px);}
}
@media(min-width:960px) and (max-height:700px){
.stage{transform:translateY(-50%) scale(.86);transform-origin:right center;}
}
@media(min-width:960px) and (max-height:600px){
.feats{display:none;}
}
@media(max-width:959px){
.brand-inner{padding:22px 24px 28px;}
.b-top{gap:14px;}
.b-top img{height:30px;}
.b-top span{font-size:10px;max-width:none;}
.b-mid{margin:0;padding:22px 0 0;max-width:560px;}
.kicker{margin-bottom:12px;}
.kicker i{width:30px;}
.kicker span{font-size:10px;}
.b-mid h2{font-size:24px;margin:0;}
.b-mid p{font-size:13px;margin:10px 0 0;}
.feats,.b-foot{display:none;}
.glow{filter:blur(50px);}
.glow.g1{width:260px;height:260px;}
.glow.g2,.glow.g3{width:220px;height:220px;}
.formside{padding:32px 24px 40px;}
.illus{display:none;}
}
@media(max-width:560px){
.brand-inner{padding:18px 18px 22px;}
.b-top span br{display:none;}
.b-mid{padding-top:18px;}
.b-mid h2{font-size:21px;}
.b-mid p{display:none;}
.formside{padding:26px 16px 32px;}
.card{padding:22px 18px 18px;border-radius:14px;}
.formbox h1{font-size:23px;}
.formbox p.sub{font-size:13px;margin-bottom:18px;}
.input-wrap input{font-size:16px;}
}
@media (min-width:960px) and (max-height:760px){
.illus{display:none;}
.b-mid{padding:2vh 0;}
.b-mid p{margin-bottom:26px;}
.card{padding:22px 24px 20px;}
.field{margin-bottom:16px;}
.formbox p.sub{margin-bottom:18px;}
.stage{gap:10px;}
.gc{padding:11px 14px;}
}
</style>
</head><body>
<div class="wrap">

<aside class="brand">
<div class="glow g1"></div><div class="glow g2"></div><div class="glow g3"></div>
<div class="gridbg"></div>
<div class="brand-inner">
<div class="b-top anim" style="animation-delay:.05s"><img src="<?=BASE_URL?>/assets/logo-mark.png" alt="USC"><span>PT.United Steel Center Indonesia</span></div>
<div class="b-mid">
<div class="kicker anim" style="animation-delay:.1s"><i></i><span>Sistem Invitation</span></div>
<h2 class="anim" style="animation-delay:.2s">Pengelolaan Undangan<br>Acara yang <em>Terpadu.</em></h2>
<p class="anim" style="animation-delay:.3s">Sistem resmi untuk menyusun daftar undangan, mengirimkan e-ticket QR kepada peserta, serta memantau kehadiran dan pembagian souvenir dalam satu platform.</p>
<div class="feats anim" style="animation-delay:.4s">
<div class="feat"><div class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4.5" width="18" height="16" rx="2.5"/><line x1="16" y1="2.5" x2="16" y2="6.5"/><line x1="8" y1="2.5" x2="8" y2="6.5"/><line x1="3" y1="10" x2="21" y2="10"/><circle cx="12" cy="15" r="1.4" fill="currentColor" stroke="none"/></svg></div>Undangan<br>Digital</div>
<div class="feat"><div class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l7.5 3v5.5c0 4.5-3.1 8-7.5 9.5-4.4-1.5-7.5-5-7.5-9.5V6L12 3z"/><path d="M8.6 12l2.4 2.4 4.4-4.6"/></svg></div>Akses<br>Terkontrol</div>
<div class="feat"><div class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8.5" r="3.2"/><circle cx="5.5" cy="10" r="2.3"/><circle cx="18.5" cy="10" r="2.3"/><path d="M6.5 19c0-3 2.5-5 5.5-5s5.5 2 5.5 5"/><path d="M1.8 17.5c.2-1.9 1.6-3.3 3.7-3.5M22.2 17.5c-.2-1.9-1.6-3.3-3.7-3.5"/></svg></div>Pemantauan<br>Kehadiran</div>
</div>
</div>
<div class="b-foot anim" style="animation-delay:.6s"><i></i><span>&copy; <?=date('Y')?> PT United Steel Center Indonesia</span></div>
</div>

<div class="stage" id="stage" aria-hidden="true"><div class="sparks"><?php foreach([[8,0,9,14],[22,-3,11,-10],[38,-6,8,18],[55,-1.5,12,-16],[70,-4.5,10,8],[84,-7.5,9,-12],[95,-2,13,10]] as [$l,$d,$t,$x]):?><i style="left:<?=$l?>%;--d:<?=$d?>s;--t:<?=$t?>s;--x:<?=$x?>px"></i><?php endforeach;?></div><div class="scene" id="scene">
<div class="gc-wrap" style="--d:.5s"><div class="gc" style="--f:6.5s"><span class="gc-glare"></span>
<div class="gc-head"><span class="dot"></span><span class="grow">Undangan Terkirim</span><span class="pill">Event</span></div>
<div class="gc-num"><span id="sentNum">128</span><small>/ 140 peserta</small></div>
<div class="bar"><i id="sentBar" style="--w:91%"></i></div>
</div></div>
<div class="gc-wrap" id="ticketCard" style="--d:.7s"><div class="gc" style="--f:7.5s;--fd:-2s"><span class="gc-glare"></span>
<div class="ticket">
<div class="qr"><svg viewBox="0 0 21 21" fill="#0b1b2b" shape-rendering="crispEdges"><?=$qrCells?></svg><span class="scan"></span></div>
<div><div class="t-lbl">E-Ticket</div><div class="t-name">Peserta Undangan</div><span class="pill"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="5 12.5 10 17.5 19 7"/></svg>Check-in</span></div>
</div>
</div></div>
<div class="gc-wrap" style="--d:.9s"><div class="gc" style="--f:7s;--fd:-4s"><span class="gc-glare"></span>
<div class="gc-head"><span class="grow">Kehadiran per Jam</span><span class="pill"><span class="dot live"></span>Live</span></div>
<div class="gc-sub">Tingkat kehadiran <span id="attPct">86</span>%</div>
<div class="bars" id="bars"><?php foreach([30,55,90,75,60,82,45] as $i=>$h):?><span style="--h:<?=$h/100?>;animation-delay:<?=1.1+$i*.08?>s"></span><?php endforeach;?></div>
<div class="bars-lbl"><span>08</span><span>09</span><span>10</span><span>11</span><span>12</span><span>13</span><span>14</span></div>
</div></div>
</div><div class="floor"></div></div>
</aside>

<section class="formside">
<div class="formbox">
<svg class="illus anim" style="animation-delay:.05s" viewBox="0 0 120 100" fill="none" aria-hidden="true"><path d="M14 58c-6-22 8-44 34-46 22-2 30-6 48 4 18 10 26 34 12 50-14 15-38 16-58 12-16-3-30-4-36-20z" fill="#daf6ca"/><rect x="34" y="34" width="52" height="42" rx="6" fill="#43a320"/><path d="M34 42l26 18 26-18" stroke="#daf6ca" stroke-width="3.4" stroke-linecap="round" stroke-linejoin="round"/><rect x="43" y="24" width="34" height="30" rx="4" fill="#f0fcea" stroke="#43a320" stroke-width="2.6"/><circle cx="60" cy="35" r="4.4" stroke="#43a320" stroke-width="2.4"/><path d="M51 48c1-5 4-7 9-7s8 2 9 7" stroke="#43a320" stroke-width="2.4" stroke-linecap="round"/><path d="M92 26l4-4M96 34h5M90 19l1-5" stroke="#43a320" stroke-width="2.4" stroke-linecap="round"/></svg>
<div class="eyebrow anim" style="animation-delay:.12s">Selamat datang di</div>
<h1 class="anim" style="animation-delay:.18s">Sistem Invitation</h1>
<p class="sub anim" style="animation-delay:.24s">Masuk menggunakan akun administrator untuk mengelola undangan, peserta, dan kehadiran acara.</p>
<div id="loginToast" role="status" aria-live="polite"></div>
<div class="card anim" style="animation-delay:.3s">
<?php if($m=flash('error')):?><div class="alert-err"><?=$m?></div><?php endif;?>
<form method="post" id="loginForm">
<?=csrf_field()?>
<div class="field"><label for="loginInput"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4.5 20c.8-3.6 3.8-6 7.5-6s6.7 2.4 7.5 6"/></svg>Username atau Email</label><div class="input-wrap"><input type="text" id="loginInput" name="login" placeholder="Contoh: admin" autocomplete="username" autocapitalize="none" spellcheck="false" required autofocus></div></div>
<div class="field"><label for="pwInput"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="4.5" y="10.5" width="15" height="10" rx="2.5"/><path d="M8 10.5V8a4 4 0 0 1 8 0v2.5"/></svg>Kata Sandi</label><div class="input-wrap">
<input type="password" name="password" id="pwInput" placeholder="Masukkan kata sandi" required>
<button type="button" class="toggle-eye" id="toggleEye" aria-label="Tampilkan kata sandi">
<svg id="eyeOpen" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
<svg id="eyeClosed" class="hide" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a18.6 18.6 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
</button>
</div></div>
<button class="btn-submit" type="submit"><span class="spin"></span><svg class="ok" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="5 12.5 10 17.5 19 7"/></svg><span class="lbl">Masuk</span><svg class="arr" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" y1="12" x2="19" y2="12"/><polyline points="13 6 19 12 13 18"/></svg></button>
</form>
<div class="foot">Akses terbatas untuk administrator yang berwenang.</div>
</div>
</div>
</section>

</div>
<script>
// Animasi 3D panel kiri: semua dihitung di browser (tanpa beban server). Hanya mengubah transform/opacity,
// berhenti otomatis saat tab tidak aktif (requestAnimationFrame), dan mati bila pengguna memilih kurangi animasi.
(function(){
 const brand=document.querySelector('.brand'),stage=document.getElementById('stage'),scene=document.getElementById('scene');
 if(!brand||!stage||matchMedia('(prefers-reduced-motion: reduce)').matches)return;
 const wide=matchMedia('(min-width:1180px)');
 let tx=0,ty=0,x=0,y=0,hover=false,t0=performance.now();
 brand.addEventListener('pointermove',e=>{const r=brand.getBoundingClientRect();tx=(e.clientX-r.left)/r.width-.5;ty=(e.clientY-r.top)/r.height-.5;hover=true;});
 brand.addEventListener('pointerleave',()=>{hover=false;});
 function frame(now){
  requestAnimationFrame(frame);
  if(!wide.matches)return;
  if(!hover){const t=(now-t0)/1000;tx=Math.sin(t*.45)*.22;ty=Math.cos(t*.33)*.14;} // goyang pelan saat diam
  x+=(tx-x)*.06;y+=(ty-y)*.06; // inersia
  stage.style.setProperty('--px',x.toFixed(4));stage.style.setProperty('--py',y.toFixed(4));
 }
 requestAnimationFrame(frame);
 // Data contoh yang "hidup": angka terkirim naik, grafik berganti, kartu e-ticket berkedip saat ada check-in.
 const num=document.getElementById('sentNum'),bar=document.getElementById('sentBar'),pct=document.getElementById('attPct'),bars=[...document.querySelectorAll('#bars span')],ticket=document.getElementById('ticketCard');
 let sent=128;
 setInterval(()=>{
  if(document.hidden||!wide.matches)return;
  sent=sent>=140?118:sent+1+Math.floor(Math.random()*2);
  if(sent>140)sent=140;
  num.textContent=sent;bar.style.setProperty('--w',Math.round(sent/140*100)+'%');
  bars.forEach(b=>b.style.setProperty('--h',(.25+Math.random()*.7).toFixed(2)));
  pct.textContent=80+Math.floor(Math.random()*15);
  ticket.classList.remove('pop');void ticket.offsetWidth;ticket.classList.add('pop');
 },3200);
})();
const pw=document.getElementById('pwInput'),eyeBtn=document.getElementById('toggleEye'),eo=document.getElementById('eyeOpen'),ec=document.getElementById('eyeClosed');
eyeBtn.addEventListener('click',()=>{const show=pw.type==='password';pw.type=show?'text':'password';eo.classList.toggle('hide',show);ec.classList.toggle('hide',!show);});
const form=document.getElementById('loginForm'),sub=form.querySelector('.btn-submit'),box=document.querySelector('.formbox');
const lbl=sub.querySelector('.lbl'),inputs=[...form.querySelectorAll('input:not([type=hidden])')],card=form.closest('.card');
function setBusy(on){sub.classList.toggle('loading',on);lbl.textContent=on?'Memproses...':'Masuk';inputs.forEach(i=>i.disabled=on);eyeBtn.disabled=on;}
function showError(msg){let el=card.querySelector('.alert-err');if(el)el.remove();el=document.createElement('div');el.className='alert-err';el.textContent=msg;card.prepend(el);}
function showSuccess(name){
 const t=document.getElementById('loginToast');
 t.className='login-toast';
 t.innerHTML='<span class="lt-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="5 12.5 10 17.5 19 7"/></svg></span><div><span class="lt-t"></span><small>Mengalihkan ke halaman utama...</small></div>';
 t.querySelector('.lt-t').textContent='Login berhasil'+(name?' — Selamat datang, '+name+'!':'');
 sub.classList.remove('loading');sub.classList.add('done');lbl.textContent='Berhasil';
}
form.addEventListener('submit',async e=>{
 if(!form.checkValidity())return;
 e.preventDefault();
 const data=new FormData(form);
 setBusy(true);
 try{
  const r=await fetch(BASE+'/login',{method:'POST',body:data,headers:{'X-Requested-With':'fetch','Accept':'application/json'},credentials:'same-origin'});
  const j=await r.json();
  if(j.ok){showSuccess(j.name);setTimeout(()=>{location.href=j.redirect;},700);return;}
  setBusy(false);showError(j.message||'Login gagal.');pw.value='';pw.focus();
 }catch(err){
  // fallback: kirim form biasa kalau fetch gagal
  inputs.forEach(i=>i.disabled=false);form.submit();
 }
});
window.addEventListener('pageshow',e=>{if(e.persisted){setBusy(false);sub.classList.remove('done');const t=document.getElementById('loginToast');t.className='';t.innerHTML='';}});
</script>
</body></html>
