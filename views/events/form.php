<?php require __DIR__.'/../layout/header.php';$locked=$event && $event['status']==='PUBLISHED';?>
<h2 class="page-title mb-4"><?= $event?'Edit':'Buat' ?> Event</h2>
<style>
.event-form-grid{display:grid;grid-template-columns:1.3fr .7fr;gap:20px;align-items:start;}
@media(max-width:900px){.event-form-grid{grid-template-columns:1fr;}}
.status-guide .sg-item{display:flex;gap:10px;padding-bottom:14px;margin-bottom:14px;border-bottom:1px solid #f0f2f0;}
.status-guide .sg-item:last-child{border-bottom:0;margin-bottom:0;padding-bottom:0;}
.status-guide .sg-dot{width:9px;height:9px;border-radius:50%;margin-top:5px;flex:none;}
.status-guide .sg-title{font-size:13px;font-weight:700;color:var(--ink);margin-bottom:2px;}
.status-guide .sg-desc{font-size:12px;color:#6b7570;line-height:1.5;}
</style>
<div class="event-form-grid">
<form method="post" id="eventForm" class="form-card" style="max-width:none" onsubmit="return validateTimes();"><?=csrf_field()?>
<?php if(!$event):?><div class="info-note"><i class="bi bi-info-circle"></i><span>Kode event akan dibuat otomatis (format EVT-yymm-nnn).</span></div><?php endif;?>
<?php if($locked):?><div class="alert-soft-warn mb-4">Event sudah <strong>PUBLISHED</strong>, sehingga data acara dikunci agar tidak berubah setelah undangan disebar. Untuk memperbaiki data, ubah <strong>Status</strong> ke <strong>DRAFT</strong>. Perubahan langsung tersimpan dan form akan terbuka untuk diedit. Setelah selesai, pilih <strong>PUBLISHED</strong> lalu klik Simpan. Undangan yang sudah terkirim tidak akan dikirim ulang.</div><?php endif;?>
<div class="row g-4">
<?php if($event):?><div class="col-md-4"><label>Kode Event</label><input class="form-control" value="<?=e($event['event_code'])?>" disabled></div><?php endif;?>
<div class="col-md-<?=$event?'8':'12'?>"><label>Nama Event <span class="req-star">*</span></label><input class="form-control" name="event_name" value="<?=e($event['event_name']??'')?>" placeholder="Contoh: HUT Anniversary USC 2026" required <?=$locked?'disabled':''?>></div>
<div class="col-md-6"><label>Tanggal <span class="req-star">*</span></label><input type="date" id="eventDate" class="form-control" name="event_date" value="<?=e($event['event_date']??'')?>" <?php $minDate=date('Y-m-d');if($event&&$event['event_date']<$minDate)$minDate='';?><?=$minDate?'min="'.$minDate.'"':''?> required <?=$locked?'disabled':''?>></div>
<div class="col-md-6"><label>Zona Waktu <span class="req-star">*</span></label><div class="select-wrap"><select id="tzSelect" name="timezone" class="form-select" required <?=$locked?'disabled':''?>><?php if(!$event):?><option value="" selected>-- Pilih Zona --</option><?php endif;?><?php foreach(['WIB','WITA','WIT'] as $tzv):?><option value="<?=$tzv?>" <?=$tzv===($event['timezone']??'')?'selected':''?>><?=$tzv?></option><?php endforeach;?></select><i class="bi bi-chevron-down select-arrow"></i></div></div>
<div class="col-md-6"><label>Mulai <span class="req-star">*</span></label><input type="time" id="startTime" class="form-control" name="start_time" value="<?=e($event['start_time']??'')?>" required <?=$locked?'disabled':''?>></div>
<div class="col-md-6"><label>Selesai</label><input type="time" id="endTime" class="form-control" name="end_time" value="<?=e($event['end_time']??'')?>" <?=$locked?'disabled':''?>></div>
<div class="col-md-6"><label>Lokasi <span class="req-star">*</span></label><input class="form-control" name="location" value="<?=e($event['location']??'')?>" placeholder="Contoh: Ballroom Hotel Indonesia, Jakarta" required <?=$locked?'disabled':''?>></div>
<div class="col-md-6"><label>Status <span class="req-star">*</span></label><div class="select-wrap"><select id="statusSelect" name="status" class="form-select" required><?php foreach(['DRAFT','PUBLISHED','CLOSED','CANCELLED'] as $st):?><option <?=$st===($event['status']??'DRAFT')?'selected':''?>><?=$st?></option><?php endforeach;?></select><i class="bi bi-chevron-down select-arrow"></i></div></div>
<div class="col-12"><label>Deskripsi</label><textarea name="description" class="form-control" rows="3" placeholder="Contoh: Acara syukuran ulang tahun perusahaan yang diikuti seluruh karyawan, diisi dengan sambutan direksi, hiburan, dan pembagian souvenir." <?=$locked?'disabled':''?>><?=e($event['description']??'')?></textarea></div>
</div>
<button class="btn-brand mt-4" style="width:100%;justify-content:center;padding:12px;font-size:14px;"><i class="bi bi-check2"></i>Simpan</button>
</form>
<div class="soft-card status-guide">
<h3 style="font-size:14px;font-weight:700;margin:0 0 16px;">Panduan Status Event</h3>
<div class="sg-item"><span class="sg-dot" style="background:#6b7570"></span><div><div class="sg-title">DRAFT</div><div class="sg-desc">Event belum dipublikasikan. Semua field masih bisa diubah bebas, undangan belum bisa dikirim.</div></div></div>
<div class="sg-item"><span class="sg-dot" style="background:#1f7a4c"></span><div><div class="sg-title">PUBLISHED</div><div class="sg-desc">Event aktif dan siap. Semua field terkunci kecuali Status, supaya data tidak berubah setelah undangan disebar.</div></div></div>
<div class="sg-item"><span class="sg-dot" style="background:#1c2a26"></span><div><div class="sg-title">CLOSED</div><div class="sg-desc">Event sudah selesai berlangsung. Stok souvenir yang belum diambil dapat dialokasikan ulang ke event lain.</div></div></div>
<div class="sg-item"><span class="sg-dot" style="background:#c0392b"></span><div><div class="sg-title">CANCELLED</div><div class="sg-desc">Event dibatalkan dan tidak jadi dilaksanakan.</div></div></div>
</div>
</div>
<script>
const IS_CREATE=<?=json_encode(!$event)?>;
const ORIG_DATE=<?=json_encode($event['event_date']??'')?>,ORIG_START=<?=json_encode(substr((string)($event['start_time']??''),0,5))?>,ORIG_END=<?=json_encode(substr((string)($event['end_time']??''),0,5))?>;
const SENT_COUNT=<?=(int)($sentCount??0)?>;
let scheduleConfirmed=false;
const TZ_OFFSET={WIB:7,WITA:8,WIT:9};
function localNow(){const tz=document.getElementById('tzSelect')?.value||'WIB';return new Date(Date.now()+(TZ_OFFSET[tz]||7)*3600000);}
function todayStr(){const n=localNow();return n.getUTCFullYear()+'-'+String(n.getUTCMonth()+1).padStart(2,'0')+'-'+String(n.getUTCDate()).padStart(2,'0');}
function nowStr(){const n=localNow();return String(n.getUTCHours()).padStart(2,'0')+':'+String(n.getUTCMinutes()).padStart(2,'0');}
function applyMinStart(){
 const d=document.getElementById('eventDate'),s=document.getElementById('startTime'),st=document.getElementById('statusSelect');
 if(!d||!s)return;
 if(st && st.value==='PUBLISHED' && d.value===todayStr())s.min=nowStr();else s.removeAttribute('min');
}
function validateTimes(){
 const d=document.getElementById('eventDate'),s=document.getElementById('startTime'),e=document.getElementById('endTime'),st=document.getElementById('statusSelect');
 if(s && s.disabled)return true;
 if(s && !s.value){showAlert('Jam mulai wajib diisi.');return false;}
 const tzEl=document.getElementById('tzSelect');if(tzEl && !tzEl.disabled && !tzEl.value){showAlert('Zona waktu wajib dipilih.');return false;}
 if(IS_CREATE && d && d.value && d.value<todayStr()){showAlert('Tanggal event tidak boleh di masa lalu.');return false;}
 if(!IS_CREATE && d && d.value && d.value!==ORIG_DATE && d.value<todayStr()){showAlert('Tanggal event tidak boleh diubah ke tanggal yang sudah lewat.');return false;}
 if((IS_CREATE||scheduleChanged())&&d&&s&&s.value&&d.value===todayStr()&&s.value.slice(0,5)<nowStr()){showAlert('Jam mulai sudah lewat untuk hari ini. Pilih jam yang akan datang atau tanggal lain.');return false;}
 if(st && st.value!=='PUBLISHED')return confirmSchedule();
 if(d && d.value && (d.value<todayStr() || (d.value===todayStr() && s && s.value && s.value.slice(0,5)<nowStr()))){showAlert('Jadwal event sudah lewat. Pilih tanggal dan jam yang akan datang sebelum mempublikasikan.');return false;}
 if(s && e && s.value && e.value && e.value<=s.value){showAlert('Jam selesai harus setelah jam mulai.');return false;}
 if(d && s && s.value && d.value===todayStr() && s.value<nowStr()){showAlert('Untuk event hari ini, jam mulai tidak boleh kurang dari jam sekarang.');return false;}
 return confirmSchedule();
}
function scheduleChanged(){
 if(IS_CREATE)return false;
 const hm=v=>(v||'').slice(0,5);
 return document.getElementById('eventDate').value!==ORIG_DATE||hm(document.getElementById('startTime').value)!==ORIG_START||hm(document.getElementById('endTime').value)!==ORIG_END;
}
// Jadwal (tanggal/jam) diubah padahal undangan sudah terkirim: minta konfirmasi dulu.
function confirmSchedule(){
 if(IS_CREATE||!SENT_COUNT||scheduleConfirmed)return true;
 const hm=v=>(v||'').slice(0,5); // input jam bisa bernilai 09:00:00, bandingkan jam:menit saja
 const d=document.getElementById('eventDate').value,s=hm(document.getElementById('startTime').value),e=hm(document.getElementById('endTime').value);
 if(d===ORIG_DATE&&s===ORIG_START&&e===ORIG_END)return true;
 const fmt=v=>v?v.split('-').reverse().join('-'):'';
 const lama=fmt(ORIG_DATE)+(ORIG_START?' '+ORIG_START:''),baru=fmt(d)+(s?' '+s:'');
 showConfirm('Undangan sudah terkirim ke '+SENT_COUNT+' peserta dengan jadwal lama ('+lama+'). Perubahan jadwal ke '+baru+' tidak mengirim ulang email secara otomatis, jadi peserta perlu diinformasikan. Lanjutkan simpan?',
  ()=>{scheduleConfirmed=true;document.getElementById('eventForm').submit();},{title:'Jadwal event diubah',okText:'Ya, Simpan'});
 return false;
}
document.getElementById('eventDate')?.addEventListener('change',applyMinStart);
// Tanggal sebelum hari ini tidak bisa dipilih. Pengecualian: tanggal asli event (supaya event lama tetap bisa disimpan tanpa ganti tanggal).
document.getElementById('eventDate')?.addEventListener('change',e=>{
 const v=e.target.value;
 if(v&&v<todayStr()&&v!==ORIG_DATE){e.target.value=ORIG_DATE&&ORIG_DATE>=todayStr()?ORIG_DATE:(IS_CREATE?'':ORIG_DATE);showAlert('Tanggal sebelum hari ini tidak bisa dipilih.');applyMinStart();}
});
document.getElementById('statusSelect')?.addEventListener('change',applyMinStart);
// Event PUBLISHED (form terkunci): memilih status lain langsung disimpan setelah konfirmasi.
<?php if($locked):?>
(function(){
 const sel=document.getElementById('statusSelect');let confirmed=false;
 const info={DRAFT:'Data event akan terbuka untuk diedit. Selama DRAFT, scanner untuk event ini tidak aktif.',CLOSED:'Event ditandai selesai. Scanner untuk event ini tidak aktif lagi.',CANCELLED:'Event ditandai dibatalkan. Scanner untuk event ini tidak aktif lagi.'};
 sel.addEventListener('change',()=>{
  if(sel.value==='PUBLISHED')return;
  confirmed=false;
  showConfirm(info[sel.value]||'',()=>{confirmed=true;document.getElementById('eventForm').submit();},{title:'Ubah status ke '+sel.value+'?',okText:'Ya, Ubah Status'});
  document.getElementById('confirmModal').addEventListener('hidden.bs.modal',()=>{if(!confirmed){sel.value='PUBLISHED';applyMinStart();}},{once:true});
 });
})();
<?php endif;?>
document.getElementById('tzSelect')?.addEventListener('change',applyMinStart);
applyMinStart();
</script>
<?php require __DIR__.'/../layout/footer.php';