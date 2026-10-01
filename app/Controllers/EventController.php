<?php
namespace App\Controllers;
use App\Auth;use App\Database;use App\Services\QrService;use App\Services\Mailer;use App\Support\Audit;
final class EventController {
 public static function index():void{
  Auth::requireRole(['ADMIN']);$db=Database::connection();$q=trim($_GET['q']??'');
  $sql="SELECT e.*,u.name creator,(SELECT COUNT(*) FROM invitations i WHERE i.event_id=e.id) invited,(SELECT COUNT(*) FROM invitations i WHERE i.event_id=e.id AND i.email_status='SENT') sent_count FROM events e LEFT JOIN users u ON u.id=e.created_by WHERE 1=1";$p=[];
  if($q!==''){$sql.=" AND (e.event_name LIKE ? OR e.event_code LIKE ? OR e.location LIKE ?)";$x="%$q%";array_push($p,$x,$x,$x);}
  $sql.=" ORDER BY e.event_code ASC";
  $s=$db->prepare($sql);$s->execute($p);$events=$s->fetchAll();
  require dirname(__DIR__,2).'/views/events/index.php';
 }
 public static function form(?int $id=null):void{
  Auth::requireRole(['ADMIN']);$db=Database::connection();$event=null;
  if($id){$s=$db->prepare("SELECT * FROM events WHERE id=?");$s->execute([$id]);$event=$s->fetch();if(!$event){http_response_code(404);exit('Not found');}}
  // Jumlah undangan yang sudah terkirim (untuk peringatan bila jadwal diubah)
  $sentCount=0;if($id){$s=$db->prepare("SELECT COUNT(*) FROM invitations WHERE event_id=? AND email_status='SENT'");$s->execute([$id]);$sentCount=(int)$s->fetchColumn();}
  if($_SERVER['REQUEST_METHOD']==='POST'){verify_csrf();
   if($id && $event['status']==='PUBLISHED'){
    $newStatus=$_POST['status']??$event['status'];
    if(!in_array($newStatus,['DRAFT','PUBLISHED','CLOSED','CANCELLED'],true)){flash('error','Status tidak valid.');redirect('/events/'.$id.'/edit');}
    $db->prepare("UPDATE events SET status=?,updated_at=NOW() WHERE id=?")->execute([$newStatus,$id]);
    Audit::log('UPDATE','EVENT',$id,'Status only -> '.$newStatus.' (event published, fields locked)');
    if($newStatus!=='PUBLISHED'){
     // Langsung kembali ke form yang sekarang sudah terbuka, supaya data bisa diperbaiki tanpa bolak-balik.
     flash('success','Status diubah menjadi <strong>'.$newStatus.'</strong>. Data event sekarang bisa diedit; pilih PUBLISHED lalu Simpan bila sudah selesai.');
     redirect('/events/'.$id.'/edit');
    }
    flash('success','Status event <strong>'.e($event['event_name']).'</strong> berhasil diubah menjadi '.$newStatus.'.');
    redirect('/events');
   }
   $start=$_POST['start_time']?:null;$end=$_POST['end_time']?:null;$newStatus=$_POST['status']??'DRAFT';
   if(!in_array($newStatus,['DRAFT','PUBLISHED','CLOSED','CANCELLED'],true)){flash('error','Status tidak valid.');redirect($id?'/events/'.$id.'/edit':'/events/create');}
   $tz=$_POST['timezone']??'';
   if(!in_array($tz,['WIB','WITA','WIT'],true)){flash('error',$tz===''?'Zona waktu wajib dipilih.':'Zona waktu tidak valid.');redirect($id?'/events/'.$id.'/edit':'/events/create');}
   $nowLocal=time()+['WIB'=>0,'WITA'=>3600,'WIT'=>7200][$tz];$localDate=date('Y-m-d',$nowLocal);$localTime=date('H:i',$nowLocal);
   if(!$id && ($_POST['event_date']??'')<$localDate){flash('error','Tanggal event tidak boleh di masa lalu.');redirect('/events/create');}
   // Edit: tanggal boleh tetap (walau sudah lewat), tapi tidak boleh DIUBAH ke tanggal lampau.
   if($id && ($_POST['event_date']??'')!==$event['event_date'] && ($_POST['event_date']??'')<$localDate){flash('error','Tanggal event tidak boleh diubah ke tanggal yang sudah lewat.');redirect('/events/'.$id.'/edit');}
   $scheduleChanged=$id&&(($_POST['event_date']??'')!==$event['event_date']||substr((string)$start,0,5)!==substr((string)$event['start_time'],0,5)||substr((string)$end,0,5)!==substr((string)$event['end_time'],0,5));
   // Jadwal baru tidak boleh di hari ini dengan jam yang sudah lewat (berlaku untuk semua status bila jadwal diubah)
   if(($scheduleChanged||!$id)&&$start&&($_POST['event_date']??'')===$localDate&&substr($start,0,5)<$localTime){flash('error','Jam mulai sudah lewat untuk hari ini. Pilih jam yang akan datang atau tanggal lain.');redirect($id?'/events/'.$id.'/edit':'/events/create');}
   if($newStatus==='PUBLISHED'){
    // Event dengan jadwal yang sudah lewat tidak boleh dipublikasikan (mis. event CLOSED dibuka lagi): admin wajib memilih jadwal baru.
    $evDate=$_POST['event_date']??'';
    if($evDate<$localDate||($evDate===$localDate&&$start&&substr($start,0,5)<$localTime)){flash('error','Jadwal event sudah lewat. Pilih tanggal dan jam yang akan datang sebelum mempublikasikan.');redirect($id?'/events/'.$id.'/edit':'/events/create');}
    if($start && $end && $end<=$start){flash('error','Jam selesai harus setelah jam mulai.');redirect($id?'/events/'.$id.'/edit':'/events/create');}
    if($start && ($_POST['event_date']??'')===$localDate && $start<$localTime){flash('error','Untuk event hari ini, jam mulai tidak boleh kurang dari jam sekarang.');redirect($id?'/events/'.$id.'/edit':'/events/create');}
   }
   if(trim($_POST['location']??'')===''){flash('error','Location wajib diisi.');redirect($id?'/events/'.$id.'/edit':'/events/create');}
   if(!$start){flash('error','Jam mulai wajib diisi.');redirect($id?'/events/'.$id.'/edit':'/events/create');}
   $d=[trim($_POST['event_name']),$_POST['event_date'],$start,$end,$tz,trim($_POST['location']),trim($_POST['description']),$_POST['status']??'DRAFT'];
   if($id){$s=$db->prepare("UPDATE events SET event_name=?,event_date=?,start_time=?,end_time=?,timezone=?,location=?,description=?,status=?,updated_at=NOW() WHERE id=?");$s->execute([...$d,$id]);Audit::log('UPDATE','EVENT',$id,$d[0]);}
   else{
    try{$code=next_sequence_code($db,'events','event_code','EVT');}
    catch(\RuntimeException $e){flash('error',$e->getMessage());redirect('/events/create');}
    $s=$db->prepare("INSERT INTO events(event_code,event_name,event_date,start_time,end_time,timezone,location,description,status,created_by) VALUES(?,?,?,?,?,?,?,?,?,?)");$s->execute([$code,...$d,Auth::user()['id']]);$id=(int)$db->lastInsertId();Audit::log('CREATE','EVENT',$id,$d[0]);
   }
   flash('success','Event <strong>'.e($d[0]).'</strong> berhasil '.($event?'diperbarui':'dibuat').'.');
   redirect('/events');
  }
  require dirname(__DIR__,2).'/views/events/form.php';
 }
 public static function invitations(int $id):void{
  Auth::requireRole(['ADMIN']);$db=Database::connection();$s=$db->prepare("SELECT * FROM events WHERE id=?");$s->execute([$id]);$event=$s->fetch();if(!$event)exit('Not found');
  $s=$db->prepare("SELECT * FROM employees WHERE status='ACTIVE' AND id NOT IN (SELECT employee_id FROM invitations WHERE event_id=?) ORDER BY name");$s->execute([$id]);$employees=$s->fetchAll();
  $s=$db->prepare("SELECT i.*,e.name employee_name,e.nik,e.department,e.email FROM invitations i JOIN employees e ON e.id=i.employee_id WHERE i.event_id=? ORDER BY e.name");$s->execute([$id]);$invitations=$s->fetchAll();
  require dirname(__DIR__,2).'/views/events/invitations.php';
 }
 public static function addInvitations(int $id):void{
  Auth::requireRole(['ADMIN']);verify_csrf();$db=Database::connection();
  $eids=array_filter(array_map('intval',$_POST['employee_ids']??[]));
  if(!$eids){flash('error','Pilih minimal satu karyawan terlebih dahulu sebelum menambahkan.');redirect('/events/'.$id.'/invitations');}
  $ins=$db->prepare("INSERT IGNORE INTO invitations(event_id,employee_id,qr_token_hash,qr_token_plain) VALUES(?,?,?,?)");
  $added=0;foreach($eids as $eid){$t=QrService::token();$added+=$ins->execute([$id,$eid,QrService::hash($t),$t])?$ins->rowCount():0;}
  Audit::log('ADD_INVITATIONS','EVENT',$id,"$added peserta ditambahkan dari ".count($eids)." dipilih");
  flash('success',$added.' karyawan berhasil diundang dan masuk ke <strong>Daftar Peserta</strong>. Langkah berikutnya: Generate QR, lalu Send Semua.');
  redirect('/events/'.$id.'/invitations');
 }
 public static function generateAllQr(int $id):void{
  Auth::requireRole(['ADMIN']);verify_csrf();$db=Database::connection();
  $s=$db->prepare("SELECT * FROM events WHERE id=?");$s->execute([$id]);$event=$s->fetch();if(!$event)exit('Not found');
  if($event['status']!=='PUBLISHED'){flash('error','Event harus berstatus PUBLISHED sebelum QR bisa digenerate. Status saat ini: '.$event['status'].'.');redirect('/events/'.$id.'/invitations');}
  $dept=trim($_POST['department']??'');
  $sql="SELECT i.id FROM invitations i JOIN employees e ON e.id=i.employee_id WHERE i.event_id=? AND i.qr_generated_at IS NULL";$params=[$id];
  if($dept!==''){$sql.=" AND e.department=?";$params[]=$dept;}
  $ids=$db->prepare($sql);$ids->execute($params);$invIds=$ids->fetchAll(\PDO::FETCH_COLUMN);
  if(!$invIds){flash('error','Tidak ada peserta'.($dept!==''?' di departemen "'.$dept.'"':'').' yang perlu digenerate.');redirect('/events/'.$id.'/invitations');}
  $upd=$db->prepare("UPDATE invitations SET qr_token_hash=?,qr_token_plain=?,qr_generated_at=NOW(),email_status='PENDING',updated_at=NOW() WHERE id=?");
  $db->beginTransaction();
  foreach($invIds as $iid){$t=QrService::token();$upd->execute([QrService::hash($t),$t,$iid]);}
  $db->commit();
  Audit::log('GENERATE_QR_BATCH','EVENT',$id,count($invIds).' invitations'.($dept!==''?" (dept: $dept)":''));
  flash('success','QR berhasil digenerate untuk '.count($invIds).' peserta. Undangan sekarang bisa dikirim.');
  redirect('/events/'.$id.'/invitations');
 }
 public static function sendAll(int $id):void{
  Auth::requireRole(['ADMIN']);verify_csrf();$db=Database::connection();
  $s=$db->prepare("SELECT * FROM events WHERE id=?");$s->execute([$id]);$event=$s->fetch();if(!$event)exit('Not found');
  if($event['status']!=='PUBLISHED'){flash('error','Event harus berstatus PUBLISHED sebelum undangan bisa dikirim. Status saat ini: '.$event['status'].'.');redirect('/events/'.$id.'/invitations');}
  $dept=trim($_POST['department']??'');
  $sql="SELECT i.*,e.name employee_name,e.email employee_email FROM invitations i JOIN employees e ON e.id=i.employee_id WHERE i.event_id=? AND i.qr_generated_at IS NOT NULL AND i.email_status!='SENT'";$params=[$id];
  if($dept!==''){$sql.=" AND e.department=?";$params[]=$dept;}
  $s=$db->prepare($sql);$s->execute($params);$rows=$s->fetchAll();
  if(!$rows){flash('error','Tidak ada undangan'.($dept!==''?' di departemen "'.$dept.'"':'').' yang siap dikirim.');redirect('/events/'.$id.'/invitations');}
  // Cadangan tanpa JavaScript: kirim satu batch saja, supaya request tidak melewati batas waktu PHP.
  $res=self::sendChunk($db,$event,$dept,0);
  flash($res['failed']?'error':'success',$res['sent'].' email terkirim'.($res['failed']?', '.$res['failed'].' gagal':'').'.'.($res['remaining']?' Masih ada '.$res['remaining'].' undangan, klik Send Semua lagi.':''));
  redirect('/events/'.$id.'/invitations');
 }
 // Dipanggil berulang oleh halaman Peserta (progress bar). Tiap panggilan mengirim sebagian email
 // dalam batas waktu ±20 detik, jadi aman untuk 1000+ peserta dan tidak kena max_execution_time.
 public static function sendBatch(int $id):void{
  Auth::requireRole(['ADMIN']);header('Content-Type: application/json');
  if(!hash_equals($_SESSION['_csrf']??'',(string)($_POST['_csrf']??''))){http_response_code(419);echo json_encode(['error'=>'Sesi kedaluwarsa, muat ulang halaman.']);return;}
  session_write_close(); // tab/halaman lain milik admin tetap bisa dipakai selama pengiriman
  $db=Database::connection();
  $s=$db->prepare("SELECT * FROM events WHERE id=?");$s->execute([$id]);$event=$s->fetch();
  if(!$event||$event['status']!=='PUBLISHED'){echo json_encode(['error'=>'Event harus berstatus PUBLISHED.']);return;}
  // Pengiriman berjalan di beberapa jalur paralel; tiap jalur memegang peserta dengan MOD(id, jumlah jalur) berbeda.
  // Kunci per jalur mencegah dua tab mengirim ke peserta yang sama (email ganda).
  // Permintaan tanpa nomor jalur (tampilan lama) mengunci semua jalur sekaligus.
  $lane=isset($_POST['lane'])?(int)$_POST['lane']:null;
  if($lane!==null&&($lane<0||$lane>=self::SEND_LANES)){echo json_encode(['error'=>'Jalur tidak valid.']);return;}
  $held=[];
  foreach($lane===null?range(0,self::SEND_LANES-1):[$lane] as $k){
   $name=$db->quote('send_event_'.$id.'_lane_'.$k);
   if(!(int)$db->query("SELECT GET_LOCK($name,0)")->fetchColumn()){foreach($held as $h)$db->query("SELECT RELEASE_LOCK($h)");echo json_encode(['busy'=>true]);return;}
   $held[]=$name;
  }
  try{
   $res=self::sendChunk($db,$event,trim($_POST['department']??''),max(0,(int)($_POST['after_id']??0)),$lane);
   if($res['sent']||$res['failed'])Audit::log('SEND_INVITATIONS_BATCH','EVENT',$id,$res['sent'].' terkirim, '.$res['failed'].' gagal'.($lane!==null?' (jalur '.($lane+1).')':''));
   echo json_encode($res);
  }finally{foreach($held as $h)$db->query("SELECT RELEASE_LOCK($h)");}
 }
 // Jumlah koneksi SMTP paralel. 4 aman untuk Google Workspace; lebih banyak berisiko ditolak sementara (421 too many connections).
 public const SEND_LANES=4;
 private static function sendChunk(\PDO $db,array $event,string $dept,int $afterId,?int $lane=null):array{
  set_time_limit(90);
  $budget=20.0;$max=50;$start=microtime(true);
  $base=" FROM invitations i JOIN employees e ON e.id=i.employee_id WHERE i.event_id=? AND i.qr_generated_at IS NOT NULL AND i.email_status!='SENT'";$p=[$event['id']];
  if($dept!==''){$base.=" AND e.department=?";$p[]=$dept;}
  if($lane!==null){$base.=" AND MOD(i.id,".self::SEND_LANES.")=?";$p[]=$lane;}
  $s=$db->prepare("SELECT i.id,i.qr_token_plain,e.name employee_name,e.email employee_email".$base." AND i.id>? ORDER BY i.id LIMIT $max");$s->execute([...$p,$afterId]);$rows=$s->fetchAll();
  $ok=0;$failed=0;$last=$afterId;$errors=[];$mail=null;$streak=0;$halt=false;
  $att=$db->prepare("UPDATE invitations SET email_attempts=email_attempts+1 WHERE id=?");
  $sent=$db->prepare("UPDATE invitations SET email_status='SENT',email_sent_at=NOW(),last_email_error=NULL,updated_at=NOW() WHERE id=?");
  $fail=$db->prepare("UPDATE invitations SET email_status='FAILED',last_email_error=?,updated_at=NOW() WHERE id=?");
  try{
   foreach($rows as $r){
    if(microtime(true)-$start>$budget)break;
    $att->execute([$r['id']]);
    try{$mail??=Mailer::open();Mailer::sendInvitation(['name'=>$r['employee_name'],'email'=>$r['employee_email']],$event,$r['qr_token_plain'],$mail);$sent->execute([$r['id']]);$ok++;$streak=0;}
    catch(\Throwable $e){$fail->execute([substr($e->getMessage(),0,500),$r['id']]);$failed++;$streak++;if(count($errors)<3)$errors[]=$r['employee_email'].': '.substr($e->getMessage(),0,160);}
    $last=(int)$r['id'];
    // 3 gagal berturut-turut = server email bermasalah (tidak terhubung, login salah, kena limit). Berhenti, jangan habiskan semua peserta.
    if($streak>=3){$halt=true;break;}
   }
  }finally{if($mail)Mailer::close($mail);}
  $c=$db->prepare("SELECT COUNT(*)".$base." AND i.id>?");$c->execute([...$p,$last]);
  $remaining=(int)$c->fetchColumn();
  return ['sent'=>$ok,'failed'=>$failed,'last_id'=>$last,'remaining'=>$remaining,'done'=>$remaining===0,'halt'=>$halt,'errors'=>$errors];
 }
 // Gambar QR per peserta (dimuat lazy oleh halaman Peserta, bukan digambar semua saat halaman dibuka).
 public static function qrImage(int $eventId,int $invId):void{
  Auth::requireRole(['ADMIN']);session_write_close();
  $s=Database::connection()->prepare("SELECT qr_token_plain FROM invitations WHERE id=? AND event_id=? AND qr_generated_at IS NOT NULL");$s->execute([$invId,$eventId]);$t=$s->fetchColumn();
  if(!$t){http_response_code(404);return;}
  $etag='"'.substr(hash('sha256',$t),0,16).'"';
  header('Cache-Control: private, max-age=86400');header('ETag: '.$etag);
  if(($_SERVER['HTTP_IF_NONE_MATCH']??'')===$etag){http_response_code(304);return;}
  header('Content-Type: image/png');echo QrService::pngBytes($t);
 }
 public static function removeInvitations(int $id):void{
  Auth::requireRole(['ADMIN']);verify_csrf();$db=Database::connection();
  $ids=array_filter(array_map('intval',$_POST['invitation_ids']??[]));
  if($ids){$in=implode(',',array_fill(0,count($ids),'?'));$db->prepare("DELETE FROM invitations WHERE event_id=? AND id IN ($in)")->execute([$id,...$ids]);Audit::log('DELETE','INVITATION',$id,'Removed invitations: '.implode(',',$ids));flash('success',count($ids).' peserta dihapus dari undangan.');}
  redirect('/events/'.$id.'/invitations');
 }
 public static function delete(int $id):void{Auth::requireRole(['ADMIN']);verify_csrf();self::deleteEvents([$id]);redirect('/events');}
 public static function bulkDelete():void{
  Auth::requireRole(['ADMIN']);verify_csrf();$ids=array_filter(array_map('intval',$_POST['event_ids']??[]));
  if($ids)self::deleteEvents($ids);
  redirect('/events');
 }
 // Event PUBLISHED yang undangannya sudah terkirim (email SENT) tidak boleh dihapus, karena QR-nya sedang dipakai peserta.
 // Status lain (DRAFT/CLOSED/CANCELLED) boleh dihapus walaupun sudah ada undangan terkirim.
 private static function deleteEvents(array $ids):void{
  $db=Database::connection();$in=implode(',',array_fill(0,count($ids),'?'));
  $s=$db->prepare("SELECT e.id,e.event_name FROM events e WHERE e.id IN ($in) AND e.status='PUBLISHED' AND EXISTS(SELECT 1 FROM invitations i WHERE i.event_id=e.id AND i.email_status='SENT')");$s->execute($ids);
  $locked=$s->fetchAll(\PDO::FETCH_KEY_PAIR);
  $del=array_values(array_diff($ids,array_keys($locked)));
  if($del){$in=implode(',',array_fill(0,count($del),'?'));$db->prepare("DELETE FROM events WHERE id IN ($in)")->execute($del);Audit::log('DELETE','EVENT',null,'Bulk delete: '.implode(',',$del));}
  if($locked){flash('error',($del?count($del).' event dihapus. ':'').count($locked).' event tidak bisa dihapus karena masih PUBLISHED dan undangan QR-nya sudah terkirim ke peserta: '.e(implode(', ',$locked)).'. Ubah statusnya dulu (misalnya ke CANCELLED atau CLOSED) kalau memang ingin dihapus.');}
  else flash('success',count($del).' event dihapus.');
 }
}
