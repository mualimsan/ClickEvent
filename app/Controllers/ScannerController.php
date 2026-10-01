<?php
namespace App\Controllers;
use App\Auth;use App\Database;use App\Services\QrService;use App\Support\Audit;
final class ScannerController {
 // Scan (kehadiran & souvenir) hanya boleh pada tanggal event, dihitung menurut zona waktu event (WIB/WITA/WIT).
 private static function localToday(?string $tz):string{return date('Y-m-d',time()+(['WIB'=>0,'WITA'=>3600,'WIT'=>7200][$tz??'WIB']??0));}
 private static function todayEvents(\PDO $db):array{
  return array_values(array_filter($db->query("SELECT * FROM events WHERE status='PUBLISHED' ORDER BY start_time,event_name")->fetchAll(),fn($e)=>$e['event_date']===self::localToday($e['timezone']??'WIB')));
 }
 private static function nextEvents(\PDO $db):array{
  return $db->query("SELECT event_name,event_date,timezone FROM events WHERE status='PUBLISHED' AND event_date>CURDATE() ORDER BY event_date ASC LIMIT 3")->fetchAll();
 }
 // Null bila boleh scan; selain itu berisi pesan penolakan.
 private static function scanDayError(\PDO $db,int $eventId):?string{
  $s=$db->prepare("SELECT status,event_date,timezone FROM events WHERE id=?");$s->execute([$eventId]);$ev=$s->fetch();
  if(!$ev)return 'Event tidak ditemukan.';
  if($ev['status']!=='PUBLISHED')return 'Event tidak aktif (status '.$ev['status'].').';
  $today=self::localToday($ev['timezone']??'WIB');
  if($ev['event_date']>$today)return 'Event ini dijadwalkan '.dmy($ev['event_date']).'. Scan hanya bisa dilakukan pada hari event.';
  if($ev['event_date']<$today)return 'Event ini sudah berlangsung pada '.dmy($ev['event_date']).'. Scan sudah ditutup.';
  return null;
 }
 public static function attendancePage():void{Auth::requireRole(['ADMIN','EVENT_OPERATOR']);$db=Database::connection();$events=self::todayEvents($db);$nextEvents=$events?[]:self::nextEvents($db);require dirname(__DIR__,2).'/views/scanner/attendance.php';}
 public static function souvenirPage():void{
  Auth::requireRole(['ADMIN','EVENT_OPERATOR']);$db=Database::connection();
  $events=self::todayEvents($db);$nextEvents=$events?[]:self::nextEvents($db);
  $souvenirs=$db->query("SELECT * FROM souvenirs WHERE status='ACTIVE' ORDER BY name")->fetchAll();
  $allocRows=$db->query("SELECT es.event_id,es.souvenir_id,es.quantity_allocated,(SELECT COALESCE(SUM(st.quantity),0) FROM souvenir_transactions st JOIN invitations ii ON ii.id=st.invitation_id WHERE st.souvenir_id=es.souvenir_id AND ii.event_id=es.event_id) claimed FROM event_souvenirs es WHERE es.quantity_allocated>0")->fetchAll();
  $allocMap=[];foreach($allocRows as $r)$allocMap[$r['event_id']][(int)$r['souvenir_id']]=['allocated'=>(int)$r['quantity_allocated'],'claimed'=>(int)$r['claimed']];
  require dirname(__DIR__,2).'/views/scanner/souvenir.php';
 }
 public static function attendanceApi():void{
  Auth::requireRole(['ADMIN','EVENT_OPERATOR']);header('Content-Type: application/json');
  if(!hash_equals($_SESSION['_csrf']??'',$_POST['_csrf']??'')){echo json_encode(['ok'=>false,'message'=>'Sesi kedaluwarsa, muat ulang halaman.']);return;}
  $eventId=(int)($_POST['event_id']??0);$token=trim($_POST['token']??'');if(!$eventId||strlen($token)<20){echo json_encode(['ok'=>false,'message'=>'Kode QR atau event tidak valid.']);return;}
  $db=Database::connection();
  if($err=self::scanDayError($db,$eventId)){echo json_encode(['ok'=>false,'message'=>$err]);return;}$s=$db->prepare("SELECT i.*,e.name,e.nik,e.department FROM invitations i JOIN employees e ON e.id=i.employee_id WHERE i.event_id=? AND i.qr_token_hash=? AND i.invitation_status='INVITED' LIMIT 1");$s->execute([$eventId,QrService::hash($token)]);$i=$s->fetch();
  if(!$i){echo json_encode(['ok'=>false,'message'=>'Kode QR tidak terdaftar pada event ini.','note'=>'Pastikan peserta menunjukkan undangan untuk event yang benar.']);return;}
  $s=$db->prepare("SELECT id,checkin_at FROM attendances WHERE invitation_id=?");$s->execute([$i['id']]);if($a=$s->fetch()){echo json_encode(['ok'=>false,'already'=>true,'message'=>'Kehadiran peserta telah tercatat pada '.date('d-m-Y',strtotime($a['checkin_at'])).' pukul '.date('H:i:s',strtotime($a['checkin_at'])).'.','note'=>'Pemindaian ulang tidak diperlukan.','employee'=>$i['name'],'nik'=>$i['nik'],'department'=>$i['department']]);return;}
  try{$db->prepare("INSERT INTO attendances(invitation_id,operator_user_id,device_name) VALUES(?,?,?)")->execute([$i['id'],Auth::user()['id'],gethostname()]);Audit::log('CHECKIN','INVITATION',(int)$i['id'],$i['name']);echo json_encode(['ok'=>true,'message'=>'Kehadiran peserta berhasil dicatat pada '.date('d-m-Y').' pukul '.date('H:i:s').'.','employee'=>$i['name'],'nik'=>$i['nik'],'department'=>$i['department']]);}
  catch(\Throwable $e){echo json_encode(['ok'=>false,'message'=>'Check-in tidak dapat diproses.','note'=>'Silakan pindai ulang kode QR.']);}
 }
 public static function souvenirApi():void{
  Auth::requireRole(['ADMIN','EVENT_OPERATOR']);header('Content-Type: application/json');
  if(!hash_equals($_SESSION['_csrf']??'',$_POST['_csrf']??'')){echo json_encode(['ok'=>false,'message'=>'Sesi kedaluwarsa, muat ulang halaman.']);return;}
  $eventId=(int)($_POST['event_id']??0);$sid=(int)($_POST['souvenir_id']??0);$token=trim($_POST['token']??'');$db=Database::connection();
  if($err=self::scanDayError($db,$eventId)){echo json_encode(['ok'=>false,'message'=>$err]);return;}
  $s=$db->prepare("SELECT i.*,e.name,e.nik,e.department FROM invitations i JOIN employees e ON e.id=i.employee_id WHERE i.event_id=? AND i.qr_token_hash=? AND i.invitation_status='INVITED'");$s->execute([$eventId,QrService::hash($token)]);$i=$s->fetch();if(!$i){echo json_encode(['ok'=>false,'message'=>'Kode QR tidak terdaftar pada event ini.','note'=>'Pastikan peserta menunjukkan undangan untuk event yang benar.']);return;}
  $s=$db->prepare("SELECT id FROM attendances WHERE invitation_id=?");$s->execute([$i['id']]);if(!$s->fetch()){echo json_encode(['ok'=>false,'message'=>'Kehadiran peserta belum tercatat.','note'=>'Mohon arahkan peserta ke meja registrasi terlebih dahulu.','employee'=>$i['name'],'nik'=>$i['nik'],'department'=>$i['department']]);return;}
  $s=$db->prepare("SELECT st.collected_at,sv.name FROM souvenir_transactions st JOIN souvenirs sv ON sv.id=st.souvenir_id WHERE st.invitation_id=? AND st.souvenir_id=?");$s->execute([$i['id'],$sid]);if($t=$s->fetch()){echo json_encode(['ok'=>false,'already'=>true,'message'=>$t['name'].' telah diterima peserta pada '.date('d-m-Y',strtotime($t['collected_at'])).' pukul '.date('H:i:s',strtotime($t['collected_at'])).'.','note'=>'Souvenir tidak dapat diberikan kembali.','employee'=>$i['name'],'nik'=>$i['nik'],'department'=>$i['department']]);return;}
  $s=$db->prepare("SELECT es.quantity_allocated,(SELECT COALESCE(SUM(st.quantity),0) FROM souvenir_transactions st JOIN invitations ii ON ii.id=st.invitation_id WHERE st.souvenir_id=es.souvenir_id AND ii.event_id=es.event_id) claimed FROM event_souvenirs es WHERE es.event_id=? AND es.souvenir_id=?");$s->execute([$eventId,$sid]);$a=$s->fetch();
  if(!$a || (int)$a['quantity_allocated']<=0){echo json_encode(['ok'=>false,'message'=>'Souvenir ini belum dialokasikan untuk event ini.','note'=>'Hubungi admin untuk mengatur alokasi.']);return;}
  $s=$db->prepare("SELECT stock FROM souvenirs WHERE id=? AND status='ACTIVE' FOR UPDATE");$s->execute([$sid]);$stock=$s->fetchColumn();
  if($stock===false){echo json_encode(['ok'=>false,'message'=>'Souvenir tidak tersedia atau berstatus nonaktif.','note'=>'Hubungi admin untuk memeriksa data souvenir.']);return;}
  if((int)$stock<1){echo json_encode(['ok'=>false,'message'=>'Stok souvenir di gudang telah habis.','note'=>'Hubungi admin untuk penambahan stok.']);return;}
  if((int)$a['claimed'] >= (int)$a['quantity_allocated']){echo json_encode(['ok'=>false,'message'=>'Alokasi souvenir untuk event ini telah habis.','note'=>'Hubungi admin untuk menambah alokasi.']);return;}
  $db->beginTransaction();
  try{
   $s=$db->prepare("SELECT stock FROM souvenirs WHERE id=? FOR UPDATE");$s->execute([$sid]);$current=(int)$s->fetchColumn();if($current<1)throw new \RuntimeException('Stock habis');
   $db->prepare("INSERT INTO souvenir_transactions(invitation_id,souvenir_id,operator_user_id,device_name) VALUES(?,?,?,?)")->execute([$i['id'],$sid,Auth::user()['id'],gethostname()]);
   $db->prepare("UPDATE souvenirs SET stock=stock-1,updated_at=NOW() WHERE id=?")->execute([$sid]);$db->commit();Audit::log('SOUVENIR_CLAIM','INVITATION',(int)$i['id'],$i['name']);$n=$db->prepare("SELECT name FROM souvenirs WHERE id=?");$n->execute([$sid]);echo json_encode(['ok'=>true,'message'=>'Silakan serahkan 1 '.$n->fetchColumn().' kepada peserta.','employee'=>$i['name'],'nik'=>$i['nik'],'department'=>$i['department']]);
  }catch(\Throwable $e){if($db->inTransaction())$db->rollBack();echo json_encode(['ok'=>false,'message'=>'Transaksi tidak dapat diproses.','note'=>'Silakan pindai ulang kode QR.']);}
 }
}
