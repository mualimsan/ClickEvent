<?php if(\App\Auth::check()):?></main></div>
<div class="modal fade" id="logoutModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered" style="max-width:380px"><div class="modal-content" style="border:0;border-radius:16px;overflow:hidden;">
<div class="p-4 text-center">
<div style="width:52px;height:52px;border-radius:50%;background:#fdeceb;color:#c0392b;display:flex;align-items:center;justify-content:center;font-size:24px;margin:0 auto 16px;"><i class="bi bi-box-arrow-right"></i></div>
<h5 style="font-weight:700;color:#0d1f17;margin-bottom:6px;">Keluar dari sistem?</h5>
<p style="font-size:13.5px;color:#6b7570;margin-bottom:22px;">Anda perlu login kembali untuk mengakses USC Click Event.</p>
<div class="d-flex gap-2 justify-content-center">
<button type="button" class="btn-outline-brand" data-bs-dismiss="modal" style="flex:1;justify-content:center;">Batal</button>
<a href="<?=BASE_URL?>/logout" style="flex:1;background:#c0392b;border:0;color:#fff;font-weight:600;border-radius:8px;padding:9px 17px;font-size:13.5px;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;gap:6px;transition:background .15s;" onmouseover="this.style.background='#a3372b'" onmouseout="this.style.background='#c0392b'">Ya, Keluar</a>
</div>
</div>
</div></div></div>
<div class="modal fade" id="confirmModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered" style="max-width:400px"><div class="modal-content" style="border:0;border-radius:16px;overflow:hidden;">
<div class="p-4 text-center">
<div id="confirmIcon" style="width:52px;height:52px;border-radius:50%;background:#fff8e6;color:#b8860b;display:flex;align-items:center;justify-content:center;font-size:24px;margin:0 auto 16px;"><i class="bi bi-exclamation-triangle"></i></div>
<h5 id="confirmTitle" style="font-weight:700;color:#0d1f17;margin-bottom:6px;">Konfirmasi</h5>
<p id="confirmMsg" style="font-size:13.5px;color:#6b7570;margin-bottom:22px;line-height:1.55;"></p>
<div class="d-flex gap-2 justify-content-center">
<button type="button" id="confirmCancelBtn" class="btn-outline-brand" data-bs-dismiss="modal" style="flex:1;justify-content:center;">Batal</button>
<button type="button" id="confirmOkBtn" style="flex:1;background:#dc2626;border:0;color:#fff;font-weight:600;border-radius:8px;padding:9px 17px;font-size:13.5px;cursor:pointer;transition:background .15s;" onmouseover="this.style.background=this.dataset.hoverBg||'#a3372b'" onmouseout="this.style.background=this.dataset.baseBg||'#c0392b'">Ya, Lanjutkan</button>
</div>
</div>
</div></div></div>
<?php endif;?><script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Hasil scan (scanner kehadiran & souvenir): kartu besar berwarna + bunyi, supaya operator langsung tahu hasilnya.
let __srAudio=null;
function scanBeep(kind){
 try{
  __srAudio=__srAudio||new (window.AudioContext||window.webkitAudioContext)();const ctx=__srAudio,t0=ctx.currentTime;
  const notes=kind==='ok'?[[880,0],[1320,.12]]:kind==='warn'?[[520,0],[520,.18]]:[[220,0]];
  notes.forEach(([f,dt])=>{const o=ctx.createOscillator(),g=ctx.createGain();o.type=kind==='err'?'square':'sine';o.frequency.value=f;o.connect(g);g.connect(ctx.destination);const d=kind==='err'?.35:.12;g.gain.setValueAtTime(.0001,t0+dt);g.gain.exponentialRampToValueAtTime(.25,t0+dt+.01);g.gain.exponentialRampToValueAtTime(.0001,t0+dt+d);o.start(t0+dt);o.stop(t0+dt+d+.02);});
 }catch(e){}
}
function showScanResult(el,j,titles){
 const kind=j.ok?'ok':(j.already?'warn':'err');
 const ico={ok:'bi-check-lg',warn:'bi-exclamation-lg',err:'bi-x-lg'}[kind];
 const e=s=>(s??'').toString().replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
 const now=new Date(),p=n=>String(n).padStart(2,'0');
 const meta=[j.nik?'<span><i class="bi bi-person-vcard"></i>'+e(j.nik)+'</span>':'',j.department?'<span><i class="bi bi-building"></i>'+e(j.department)+'</span>':''].join('');
 el.className='mt-3 scan-res '+(kind==='ok'?'':kind);
 el.setAttribute('role',kind==='ok'?'status':'alert');
 el.innerHTML='<div class="sr-ico"><i class="bi '+ico+'"></i></div><div class="sr-body"><div class="sr-title">'+e(titles[kind])+'</div>'
  +(j.employee?'<div class="sr-name">'+e(j.employee)+'</div>':'')+(meta?'<div class="sr-meta">'+meta+'</div>':'')
  +'<div class="sr-msg">'+e(j.message)+(j.note?' <strong class="sr-note">'+e(j.note)+'</strong>':'')+'</div></div><div class="sr-time">Scan '+p(now.getHours())+':'+p(now.getMinutes())+':'+p(now.getSeconds())+'</div>';
 void el.offsetWidth;el.classList.add('play');
 scanBeep(kind);
}
function showConfirm(message,onOk,opts){
 opts=opts||{};
 document.getElementById('confirmMsg').textContent=message;
 document.getElementById('confirmTitle').textContent=opts.title||'Konfirmasi';
 const okBtn=document.getElementById('confirmOkBtn');
 okBtn.textContent=opts.okText||'Ya, Lanjutkan';
 okBtn.style.background='#dc2626';okBtn.dataset.baseBg='#dc2626';okBtn.dataset.hoverBg='#b91c1c';
 document.getElementById('confirmCancelBtn').style.display='';
 const modalEl=document.getElementById('confirmModal');
 const modal=bootstrap.Modal.getOrCreateInstance(modalEl);
 const handler=function(){modal.hide();okBtn.removeEventListener('click',handler);onOk();};
 okBtn.addEventListener('click',handler);
 modalEl.addEventListener('hidden.bs.modal',function cleanup(){okBtn.removeEventListener('click',handler);modalEl.removeEventListener('hidden.bs.modal',cleanup);});
 modal.show();
}
function showAlert(message,opts){
 opts=opts||{};
 document.getElementById('confirmMsg').textContent=message;
 document.getElementById('confirmTitle').textContent=opts.title||'Perhatian';
 const okBtn=document.getElementById('confirmOkBtn');
 okBtn.textContent=opts.okText||'Mengerti';
 okBtn.style.background='#022d36';okBtn.dataset.baseBg='#022d36';okBtn.dataset.hoverBg='#0b4a52';
 document.getElementById('confirmCancelBtn').style.display='none';
 const modalEl=document.getElementById('confirmModal');
 const modal=bootstrap.Modal.getOrCreateInstance(modalEl);
 const handler=function(){modal.hide();okBtn.removeEventListener('click',handler);if(opts.onOk)opts.onOk();};
 okBtn.addEventListener('click',handler);
 modalEl.addEventListener('hidden.bs.modal',function cleanup(){okBtn.removeEventListener('click',handler);modalEl.removeEventListener('hidden.bs.modal',cleanup);});
 modal.show();
}
// Dropdown dengan data-auto-static: kalau pilihannya hanya satu, tampilkan sebagai teks statis.
function autoStatic(sel){
 const wrap=sel.closest('.select-wrap')||sel;
 let box=wrap.nextElementSibling;
 if(!box||!box.classList.contains('static-field')){box=document.createElement('div');box.className='static-field '+[...wrap.classList].filter(c=>/^m[tbsexy]?-\d$/.test(c)).join(' ');box.style.maxWidth=wrap.style.maxWidth;wrap.after(box);}
 const single=sel.options.length===1;
 wrap.style.display=single?'none':'';
 box.style.display=single?'':'none';
 if(single){box.innerHTML='<i class="bi '+(sel.dataset.icon||'bi-check2-circle')+'"></i><span></span>';box.querySelector('span').textContent=sel.options[0].text;}
}
document.querySelectorAll('select[data-auto-static]').forEach(sel=>{autoStatic(sel);new MutationObserver(()=>autoStatic(sel)).observe(sel,{childList:true});});
</script>
</body></html>