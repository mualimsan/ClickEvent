<?php require __DIR__.'/../layout/header.php';?>
<div class="page-head"><h2 class="page-title">Import Karyawan</h2><a class="btn-outline-brand c-excel" href="<?=BASE_URL?>/employees/import/template"><i class="bi bi-file-earmark-excel-fill"></i>Download Template</a></div>
<style>
.import-grid{display:grid;grid-template-columns:1.15fr .85fr;gap:20px;align-items:start;}
@media(max-width:900px){.import-grid{grid-template-columns:1fr;}}
.spec-table{width:100%;border-collapse:collapse;font-size:13px;}
.spec-table th{text-align:left;font-size:10.5px;text-transform:uppercase;letter-spacing:.04em;color:#6b7570;font-weight:700;padding:9px 10px;border-bottom:1px solid #e7e9e6;background:#fafbfa;}
.spec-table td{padding:9px 10px;border-bottom:1px solid #f0f2f0;vertical-align:top;color:#1c2a22;}
.spec-table tr:last-child td{border-bottom:0;}
.spec-table code{background:#eaf3fa;color:#4a7fa8;border-radius:5px;padding:1px 6px;font-size:12px;}
.div-chip{display:inline-block;background:#f4f6f5;border:1px solid #e7e9e6;color:#3d453f;font-size:11.5px;font-weight:600;border-radius:6px;padding:3px 8px;margin:2px 4px 2px 0;}
.dropzone{border:2px dashed #cfe9d8;border-radius:12px;padding:30px 18px;text-align:center;cursor:pointer;transition:border-color .15s,background .15s;background:#fbfdfc;}
.dropzone:hover,.dropzone.drag{border-color:var(--accent);background:#f2faf5;}
.dropzone i{font-size:30px;color:var(--accent);}
.dropzone .dz-title{font-weight:600;font-size:13.5px;color:var(--ink);margin-top:8px;}
.dropzone .dz-sub{font-size:12px;color:#6b7570;margin-top:2px;}
.dropzone .dz-file{font-size:13px;font-weight:600;color:var(--accent);margin-top:10px;word-break:break-all;}
</style>
<div class="import-grid">
<div class="form-card" style="max-width:none">
<h3 style="font-size:15px;font-weight:700;margin:0 0 4px;">Format Kolom</h3>
<p style="font-size:12.5px;color:#6b7570;margin:0 0 14px;">Gunakan <strong>Download Template</strong>: kolom sudah dilengkapi dropdown dan pengecekan otomatis, jadi salah ketik langsung ditolak oleh Excel. Urutan kolom boleh berbeda, yang penting nama header-nya sama.</p>
<div class="data-card" style="margin-bottom:18px;"><table class="spec-table"><thead><tr><th>Kolom</th><th>Keterangan</th><th>Contoh</th></tr></thead><tbody>
<tr><td><code>nik</code></td><td>Hanya angka (0-9), maksimal 7 digit, unik</td><td>2205049</td></tr>
<tr><td><code>name</code></td><td>Nama lengkap, hanya huruf, maksimal 50 karakter</td><td>Contoh Nama</td></tr>
<tr><td><code>email</code></td><td>Alamat email aktif untuk menerima undangan</td><td>nama@usc-indonesia.co.id</td></tr>
<tr><td><code>division</code></td><td>Dropdown: pilih Division terlebih dulu</td><td>AD</td></tr>
<tr><td><code>department</code></td><td>Dropdown menyesuaikan Division (-C Cibitung, -K Karawang)</td><td>AD-C</td></tr>
<tr><td><code>section</code></td><td>Dropdown menyesuaikan Division, boleh diisi section lain</td><td>IT</td></tr>
<tr><td><code>status</code></td><td>Dropdown: ACTIVE atau INACTIVE (kosong = ACTIVE)</td><td>ACTIVE</td></tr>
</tbody></table></div>
<div class="info-note" style="margin-bottom:0;"><i class="bi bi-info-circle"></i><div>Saat import, setiap baris dicek ulang oleh sistem (NIK hanya angka, email valid, Department sesuai Division, NIK tidak ganda). Baris yang salah dilewati dan dilaporkan beserta nomor barisnya di Excel.</div></div>
</div>
<div class="form-card" style="max-width:none">
<h3 style="font-size:15px;font-weight:700;margin:0 0 14px;">Unggah File</h3>
<form method="post" enctype="multipart/form-data" id="importForm">
<?=csrf_field()?>
<label class="dropzone" id="dropzone" for="fileInput">
<i class="bi bi-cloud-arrow-up"></i>
<div class="dz-title">Klik untuk pilih file, atau tarik &amp; lepas di sini</div>
<div class="dz-sub">Format .csv atau .xlsx</div>
<div class="dz-file" id="dzFileName"></div>
</label>
<input type="file" name="file" id="fileInput" accept=".csv,.xlsx" required hidden>
<button class="btn-brand w-100 mt-3" style="justify-content:center"><i class="bi bi-upload"></i>Import</button>
</form>
<hr style="border-color:#eef1ee;margin:18px 0;">
<h3 style="font-size:13px;font-weight:700;margin:0 0 10px;">Division &amp; Department Valid</h3>
<div class="div-chip">AD → AD-C / AD-K</div>
<div class="div-chip">BD → BD-C / BD-K</div>
<div class="div-chip">DPC → DPC-C / DPC-K</div>
<div class="div-chip">FD → FD-C / FD-K</div>
<div class="div-chip">MD → MD-C / MD-K</div>
<div class="div-chip">HRD, GA &amp; PU → HR-C/K, GA-C/K, PU-C/K</div>
</div>
</div>
<script>
const dz=document.getElementById('dropzone'),fi=document.getElementById('fileInput'),dzName=document.getElementById('dzFileName');
fi.addEventListener('change',()=>{dzName.textContent=fi.files.length?fi.files[0].name:'';});
['dragover','dragenter'].forEach(ev=>dz.addEventListener(ev,e=>{e.preventDefault();dz.classList.add('drag');}));
['dragleave','drop'].forEach(ev=>dz.addEventListener(ev,e=>{e.preventDefault();dz.classList.remove('drag');}));
dz.addEventListener('drop',e=>{if(e.dataTransfer.files.length){fi.files=e.dataTransfer.files;dzName.textContent=fi.files[0].name;}});
</script>
<?php require __DIR__.'/../layout/footer.php';
