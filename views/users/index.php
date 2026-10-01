<?php require __DIR__.'/../layout/header.php';$meId=(int)\App\Auth::user()['id'];?>
<div class="page-head"><h2 class="page-title"><span class="page-icon c-blue"><i class="bi bi-person-gear"></i></span>Pengguna</h2><a class="btn-brand" href="<?=BASE_URL?>/users/create"><i class="bi bi-plus-lg"></i>Tambah Pengguna</a></div>
<form class="d-flex flex-wrap gap-2 mb-3"><input class="search-input flex-grow-1" style="max-width:420px" name="q" value="<?=e($q)?>" placeholder="Cari nama / username / email"><button class="btn-outline-brand"><i class="bi bi-search"></i>Cari</button></form>
<div class="data-card" style="border-top:3px solid #4a7fa8"><div class="table-responsive"><table class="data-table"><thead><tr><th>No</th><th>Nama</th><th>Username</th><th>Email</th><th>Role</th><th>Status</th><th>Login Terakhir</th><th>Aksi</th></tr></thead><tbody id="userTbody">
<?php if(empty($users)):?><tr><td colspan="8" class="text-muted text-center py-4"><i class="bi bi-inbox me-2"></i>Belum ada pengguna.</td></tr>
<?php else: foreach($users as $idx=>$row):$isMe=(int)$row['id']===$meId;?>
<tr class="user-row">
<td class="text-muted"><?=$idx+1?></td>
<td class="fw-semibold"><?=e($row['name'])?><?php if($isMe):?> <span class="text-muted small fw-normal">(Anda)</span><?php endif;?></td>
<td><?php if($row['username']):?><code style="color:var(--accent);background:var(--accent-soft);padding:2px 7px;border-radius:5px;font-size:12.5px;"><?=e($row['username'])?></code><?php else:?><span class="text-muted small">-</span><?php endif;?></td>
<td><?=e($row['email'])?></td>
<td><span class="badge-pill" style="background:<?=$row['role']==='ADMIN'?'#4a7fa8':'#b77a3a'?>"><?=e(\App\Auth::roleLabel($row['role']))?></span></td>
<td><span class="badge-pill" style="background:<?=$row['status']==='ACTIVE'?'#1f7a4c':'#6b7570'?>"><?=$row['status']==='ACTIVE'?'AKTIF':'NONAKTIF'?></span></td>
<td><?php if($row['last_login_at']):[$d,$t]=explode(' ',dmy($row['last_login_at']));?><div class="dt-cell"><span class="dt-date"><?=$d?></span><span class="dt-time"><?=$t?></span></div><?php else:?><span class="text-muted small">Belum pernah</span><?php endif;?></td>
<td><div class="d-flex gap-2 flex-nowrap"><a class="btn-edit" href="<?=BASE_URL?>/users/<?=$row['id']?>/edit"><i class="bi bi-pencil"></i> Edit</a><?php if(!$isMe):?><button type="button" class="btn-edit user-del" style="color:#dc2626" data-id="<?=$row['id']?>" data-name="<?=e($row['name'])?>"><i class="bi bi-trash3"></i> Hapus</button><?php endif;?></div></td>
</tr>
<?php endforeach;endif;?>
</tbody></table></div><div id="userPagination" class="d-flex align-items-center gap-2 p-3"></div></div>
<form method="post" id="userDeleteForm"><?=csrf_field()?></form>
<script>
document.querySelectorAll('.user-del').forEach(b=>b.addEventListener('click',()=>{
 showConfirm('Hapus akun "'+b.dataset.name+'"? Pengguna ini tidak akan bisa login lagi. Riwayat scan yang pernah dilakukannya tetap tersimpan.',()=>{const f=document.getElementById('userDeleteForm');f.action=BASE+'/users/'+b.dataset.id+'/delete';f.submit();},{title:'Hapus pengguna?',okText:'Ya, Hapus'});
}));
</script>
<script>
(function(){
 const PAGE_SIZE=15;
 const rows=Array.from(document.querySelectorAll('#userTbody .user-row'));
 let page=1;
 function render(){
  const totalPages=Math.max(1,Math.ceil(rows.length/PAGE_SIZE));
  if(page>totalPages)page=totalPages;
  const start=(page-1)*PAGE_SIZE;
  rows.forEach((tr,i)=>{tr.style.display=(i>=start&&i<start+PAGE_SIZE)?'':'none';});
  const container=document.getElementById('userPagination');container.innerHTML='';
  if(totalPages<=1)return;
  const mk=(label,p,disabled)=>{const b=document.createElement('button');b.type='button';b.className='btn-outline-brand';b.style.padding='6px 12px';b.style.fontSize='12.5px';b.textContent=label;b.disabled=disabled;if(disabled)b.style.opacity='.5';b.onclick=()=>{page=p;render();};return b;};
  container.appendChild(mk('«',page-1,page<=1));
  container.appendChild(Object.assign(document.createElement('span'),{className:'small text-muted mx-1',textContent:'Halaman '+page+' / '+totalPages}));
  container.appendChild(mk('»',page+1,page>=totalPages));
 }
 render();
})();
</script>
<script>Live.reloadOn(['users']);</script>
<?php require __DIR__.'/../layout/footer.php';
