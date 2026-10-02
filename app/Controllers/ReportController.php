<?php
namespace App\Controllers;
use App\Auth;use App\Database;
use App\Services\ExcelExport;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
// Tabel laporan dan file Excel memakai fungsi *Rows() yang sama (urutan sama, termasuk pengurut cadangan id),
// dan saat ekspor halaman mengirim id baris yang sedang tampil, jadi isi Excel = isi tabel.
final class ReportController {
 private static function events(\PDO $db):array{return $db->query("SELECT id,event_name,event_date FROM events WHERE status!='DRAFT' ORDER BY event_date DESC")->fetchAll();}

 // ---------- Kehadiran ----------
 public static function attendance():void{
  Auth::requireLogin();$db=Database::connection();$events=self::events($db);
  // Laporan kehadiran selalu per satu event. Tanpa pilihan: event hari ini -> event terdekat berikutnya -> event terakhir.
  $event=(int)($_GET['event_id']??0);
  if(!$event||!in_array($event,array_map('intval',array_column($events,'id')),true))$event=self::defaultEvent($events);
  $rows=$event?array_map([self::class,'attendanceJson'],self::attendanceRows($db,$event)):[];
  require dirname(__DIR__,2).'/views/reports/attendance.php';
 }
 private static function defaultEvent(array $events):int{
  $today=date('Y-m-d');$next=null;
  foreach($events as $e){if($e['event_date']===$today)return (int)$e['id'];if($e['event_date']>$today&&($next===null||$e['event_date']<$next['event_date']))$next=$e;}
  return (int)($next['id']??($events[0]['id']??0));
 }
 public static function attendanceData():void{
  Auth::requireLogin();header('Content-Type: application/json');$db=Database::connection();$event=(int)($_GET['event_id']??0);
  echo json_encode(array_map([self::class,'attendanceJson'],self::attendanceRows($db,$event)));
 }
 private static function attendanceRows(\PDO $db,int $event):array{
  // souvenirs = daftar souvenir yang sudah diambil peserta ini (mis. "Tumbler, Tas (2)")
  $sql="SELECT i.id,ev.event_code,ev.event_name,ev.event_date,e.nik,e.name,e.section,e.department,e.division,e.email,i.email_status,a.checkin_at,
   (SELECT GROUP_CONCAT(CONCAT(s.name,IF(st.quantity>1,CONCAT(' (',st.quantity,')'),'')) ORDER BY st.collected_at,st.id SEPARATOR ', ') FROM souvenir_transactions st JOIN souvenirs s ON s.id=st.souvenir_id WHERE st.invitation_id=i.id) souvenirs,
   (SELECT MAX(st.collected_at) FROM souvenir_transactions st WHERE st.invitation_id=i.id) souvenir_at
   FROM invitations i JOIN events ev ON ev.id=i.event_id JOIN employees e ON e.id=i.employee_id LEFT JOIN attendances a ON a.invitation_id=i.id WHERE ev.status!='DRAFT'";$p=[];
  if($event){$sql.=" AND ev.id=?";$p[]=$event;}
  $sql.=" ORDER BY ev.event_date ASC,e.name ASC,i.id ASC";$s=$db->prepare($sql);$s->execute($p);return $s->fetchAll();
 }
 private static function attendanceJson(array $r):array{
  return ['id'=>(int)$r['id'],'event_code'=>$r['event_code'],'event_name'=>$r['event_name'],'event_date'=>$r['event_date'],'nik'=>$r['nik'],'name'=>$r['name'],'section'=>$r['section'],'department'=>$r['department'],'division'=>$r['division'],'email'=>$r['email'],'email_status'=>$r['email_status'],'attended'=>$r['checkin_at']!==null,'checkin_at'=>$r['checkin_at'],'souvenirs'=>$r['souvenirs']??null,'souvenir_at'=>$r['souvenir_at']??null];
 }

 // ---------- Souvenir ----------
 public static function souvenir():void{
  Auth::requireLogin();$db=Database::connection();$event=(int)($_GET['event_id']??0);
  $events=self::events($db);
  if($event&&!in_array($event,array_map('intval',array_column($events,'id')),true))$event=0;
  $stock=self::stockRows($db,$event);
  $peserta=$event?['items'=>self::pesertaItems($db,$event),'rows'=>self::pesertaRows($db,$event)]:['items'=>[],'rows'=>[]];
  require dirname(__DIR__,2).'/views/reports/souvenir.php';
 }
 // Peserta yang sudah hadir di satu event, beserta souvenir yang sudah diambil (untuk "Status Pengambilan Peserta").
 private static function hadirRows(\PDO $db,int $event):array{
  return array_values(array_filter(self::attendanceRows($db,$event),fn($r)=>$r['checkin_at']!==null));
 }
 // Kolom souvenir di tabel "Pengambilan Souvenir Peserta": souvenir yang dialokasikan ke event
 // (atau sudah pernah diambil di event itu). Bertambah otomatis saat alokasi event bertambah.
 private static function pesertaItems(\PDO $db,int $event):array{
  $s=$db->prepare("SELECT s.id,s.code,s.name FROM souvenirs s WHERE s.id IN (SELECT souvenir_id FROM event_souvenirs WHERE event_id=? AND quantity_allocated>0) OR s.id IN (SELECT st.souvenir_id FROM souvenir_transactions st JOIN invitations i ON i.id=st.invitation_id WHERE i.event_id=?) ORDER BY s.code");
  $s->execute([$event,$event]);
  return array_map(fn($r)=>['id'=>(int)$r['id'],'code'=>$r['code'],'name'=>$r['name']],$s->fetchAll());
 }
 // Peserta hadir + rincian per souvenir: take[souvenir_id] = ['q'=>jumlah,'at'=>waktu ambil terakhir].
 private static function pesertaRows(\PDO $db,int $event):array{
  $take=[];
  $s=$db->prepare("SELECT st.invitation_id,st.souvenir_id,SUM(st.quantity) q,MAX(st.collected_at) at FROM souvenir_transactions st JOIN invitations i ON i.id=st.invitation_id WHERE i.event_id=? GROUP BY st.invitation_id,st.souvenir_id");
  $s->execute([$event]);
  foreach($s as $t)$take[(int)$t['invitation_id']][(int)$t['souvenir_id']]=['q'=>(int)$t['q'],'at'=>$t['at']];
  return array_map(fn($r)=>self::attendanceJson($r)+['take'=>$take[(int)$r['id']]??[]],self::hadirRows($db,$event));
 }
 // Status pengambilan terhadap souvenir yang ditampilkan: semua diambil / sebagian / belum sama sekali.
 private static function takeStatus(array $take,array $items):array{
  $n=count(array_filter($items,fn($it)=>isset($take[$it['id']])));$all=count($items);
  if($n===0)return ['BELUM AMBIL','B76E1D'];
  if($n>=$all)return ['SUDAH AMBIL','2E7D32'];
  return ['SEBAGIAN ('.$n.'/'.$all.')','1D5FB7'];
 }
 // Monitoring stok per souvenir. Satu sumber untuk tabel di halaman & Excel.
 // Tanpa event: posisi gudang (stok fisik, dipesan event aktif, tersedia, total diambil).
 // Dengan event : alokasi event tsb vs yang sudah diambil.
 // "Dipesan" memakai aturan yang sama dengan alokasi souvenir: alokasi event DRAFT/PUBLISHED yang belum diambil.
 private static function stockRows(\PDO $db,int $event):array{
  $claimedPerEvent="SELECT st.souvenir_id,ii.event_id,SUM(st.quantity) claimed FROM souvenir_transactions st JOIN invitations ii ON ii.id=st.invitation_id GROUP BY st.souvenir_id,ii.event_id";
  if(!$event){
   // Rincian per event aktif (DRAFT/PUBLISHED). Event yang sudah selesai (CLOSED/CANCELLED, termasuk yang
   // otomatis CLOSED karena tanggalnya lewat) tidak dihitung lagi, sehingga Stok Awal mengikuti isi gudang.
   // Booked = alokasi event aktif; Sisa Event = Booked - Sudah Diambil.
   $evs=[];foreach($db->query("SELECT id,event_name,status,event_date FROM events") as $e)$evs[(int)$e['id']]=$e;
   $pairs=[];
   foreach($db->query("SELECT souvenir_id,event_id,quantity_allocated FROM event_souvenirs") as $x)$pairs[$x['souvenir_id'].'-'.$x['event_id']]=['sid'=>(int)$x['souvenir_id'],'eid'=>(int)$x['event_id'],'alloc'=>(int)$x['quantity_allocated'],'claimed'=>0];
   foreach($db->query($claimedPerEvent) as $x){$k=$x['souvenir_id'].'-'.$x['event_id'];$pairs[$k]=($pairs[$k]??['sid'=>(int)$x['souvenir_id'],'eid'=>(int)$x['event_id'],'alloc'=>0,'claimed'=>0]);$pairs[$k]['claimed']=(int)$x['claimed'];}
   uasort($pairs,fn($x,$y)=>[$evs[$x['eid']]['event_date']??'',$x['eid']]<=>[$evs[$y['eid']]['event_date']??'',$y['eid']]);
   $detail=[];
   foreach($pairs as $x){
    $ev=$evs[$x['eid']]??null;if(!$ev)continue;$active=in_array($ev['status'],['DRAFT','PUBLISHED'],true);
    if(!$active)continue;$booked=max($x['alloc'],$x['claimed']);if($booked<=0)continue;
    $detail[$x['sid']][]=['event'=>$ev['event_name'],'status'=>$ev['status'],'booked'=>$booked,'claimed'=>$x['claimed'],'sisa'=>$booked-$x['claimed']];
   }
   $rows=$db->query("SELECT s.id,s.code,s.name,s.status,s.stock,COALESCE(tk.qty,0) diambil,COALESCE(rs.alloc,0) dialokasikan,COALESCE(rs.qty,0) dipesan
    FROM souvenirs s
    LEFT JOIN (SELECT souvenir_id,SUM(quantity) qty FROM souvenir_transactions GROUP BY souvenir_id) tk ON tk.souvenir_id=s.id
    LEFT JOIN (SELECT es.souvenir_id,SUM(es.quantity_allocated) alloc,SUM(GREATEST(es.quantity_allocated-COALESCE(c.claimed,0),0)) qty FROM event_souvenirs es JOIN events e ON e.id=es.event_id LEFT JOIN ($claimedPerEvent) c ON c.souvenir_id=es.souvenir_id AND c.event_id=es.event_id WHERE e.status IN ('DRAFT','PUBLISHED') GROUP BY es.souvenir_id) rs ON rs.souvenir_id=s.id
    WHERE s.status='ACTIVE' OR COALESCE(tk.qty,0)>0 OR COALESCE(rs.alloc,0)>0 ORDER BY s.code")->fetchAll();
   return array_map(function($r)use($detail){
    // Stok Awal = Sisa Event + Sudah Diambil + Tersedia;  Tersedia = Stok Awal - Booked Event;  Stok Akhir = Tersedia + Sisa Event (= stok fisik)
    $det=$detail[(int)$r['id']]??[];
    // Sudah Diambil & Stok Awal hanya dari event aktif: Stok Awal = stok gudang + yang sudah diambil di event aktif.
    $stock=(int)$r['stock'];$diambil=array_sum(array_column($det,'claimed'));$stokAwal=$stock+$diambil;
    $sisaEvent=array_sum(array_column($det,'sisa'));$booked=$sisaEvent+$diambil;$tersedia=$stokAwal-$booked;$stokAkhir=$tersedia+$sisaEvent;
    $kondisi=$stokAkhir<=0?'Habis':($tersedia<0?'Kurang':($stokAkhir<5||$tersedia<5?'Menipis':'Aman'));
    return ['code'=>$r['code'],'name'=>$r['name'],'status'=>$r['status'],'stok_awal'=>$stokAwal,'booked'=>$booked,'tersedia'=>$tersedia,'diambil'=>$diambil,'sisa_event'=>$sisaEvent,'stok_akhir'=>$stokAkhir,'stock'=>$stock,'kondisi'=>$kondisi,
     'detail'=>$det,'detail_text'=>implode('; ',array_map(fn($d)=>$d['event'].' ('.$d['status'].'): booked '.$d['booked'].', diambil '.$d['claimed'].', sisa '.$d['sisa'],$det))];
   },$rows);
  }
  $s=$db->prepare("SELECT s.code,s.name,s.status,s.stock,COALESCE(es.quantity_allocated,0) alokasi,COALESCE(c.claimed,0) diambil
   FROM souvenirs s
   LEFT JOIN event_souvenirs es ON es.souvenir_id=s.id AND es.event_id=?
   LEFT JOIN ($claimedPerEvent) c ON c.souvenir_id=s.id AND c.event_id=?
   WHERE COALESCE(es.quantity_allocated,0)>0 OR COALESCE(c.claimed,0)>0 ORDER BY s.code");
  $s->execute([$event,$event]);
  return array_map(function($r){
   $alok=(int)$r['alokasi'];$ambil=(int)$r['diambil'];$sisa=max($alok-$ambil,0);$stock=(int)$r['stock'];
   $kondisi=$alok>0&&$sisa===0?'Habis':($stock<$sisa?'Kurang':($alok>0&&($sisa<=5||$sisa/$alok<=.1)?'Menipis':'Aman'));
   return ['code'=>$r['code'],'name'=>$r['name'],'status'=>$r['status'],'stock'=>$stock,'alokasi'=>$alok,'diambil'=>$ambil,'sisa'=>$sisa,'persen'=>$alok?round($ambil/$alok*100):0,'kondisi'=>$kondisi];
  },$s->fetchAll());
 }
 // Dipakai juga oleh Scanner Souvenir (daftar pengambilan terbaru).
 public static function souvenirData():void{
  Auth::requireLogin();header('Content-Type: application/json');$db=Database::connection();$event=(int)($_GET['event_id']??0);
  echo json_encode(array_map([self::class,'souvenirJson'],self::souvenirRows($db,$event)));
 }
 private static function souvenirRows(\PDO $db,int $event):array{
  $sql="SELECT st.id,ev.event_code,ev.event_name,ev.event_date,e.nik,e.name,e.section,e.department,e.division,e.email,s.id souvenir_id,s.code,s.name souvenir_name,st.quantity,st.collected_at FROM souvenir_transactions st JOIN invitations i ON i.id=st.invitation_id JOIN events ev ON ev.id=i.event_id JOIN employees e ON e.id=i.employee_id JOIN souvenirs s ON s.id=st.souvenir_id WHERE ev.status!='DRAFT'";$p=[];
  if($event){$sql.=" AND ev.id=?";$p[]=$event;}
  $sql.=" ORDER BY ev.event_date ASC,e.name ASC,st.id ASC";$s=$db->prepare($sql);$s->execute($p);return $s->fetchAll();
 }
 // Peserta undangan yang belum mengambil souvenir apa pun di event tersebut.
 private static function notTakenRows(\PDO $db,int $event):array{
  $sql="SELECT i.id,ev.event_code,ev.event_name,ev.event_date,e.nik,e.name,e.section,e.department,e.division,e.email,i.email_status,a.checkin_at FROM invitations i JOIN events ev ON ev.id=i.event_id JOIN employees e ON e.id=i.employee_id LEFT JOIN attendances a ON a.invitation_id=i.id WHERE ev.status!='DRAFT' AND NOT EXISTS(SELECT 1 FROM souvenir_transactions st WHERE st.invitation_id=i.id)";$p=[];
  if($event){$sql.=" AND ev.id=?";$p[]=$event;}
  $sql.=" ORDER BY ev.event_date ASC,e.name ASC,i.id ASC";$s=$db->prepare($sql);$s->execute($p);return $s->fetchAll();
 }
 private static function souvenirJson(array $r):array{
  return ['id'=>(int)$r['id'],'event_code'=>$r['event_code'],'event_name'=>$r['event_name'],'event_date'=>$r['event_date'],'nik'=>$r['nik'],'name'=>$r['name'],'section'=>$r['section'],'department'=>$r['department'],'division'=>$r['division'],'email'=>$r['email'],'souvenir_id'=>(int)$r['souvenir_id'],'souvenir_code'=>$r['code'],'souvenir_name'=>$r['souvenir_name'],'quantity'=>(int)$r['quantity'],'collected_at'=>$r['collected_at']];
 }
 // Item untuk tombol filter: souvenir yang dialokasikan ke event (atau semua souvenir aktif) + yang pernah diambil.
 private static function souvenirItems(\PDO $db,int $event):array{
  if($event){$s=$db->prepare("SELECT DISTINCT s.code,s.name FROM souvenirs s WHERE s.id IN (SELECT souvenir_id FROM event_souvenirs WHERE event_id=? AND quantity_allocated>0) OR s.id IN (SELECT st.souvenir_id FROM souvenir_transactions st JOIN invitations i ON i.id=st.invitation_id WHERE i.event_id=?) ORDER BY s.name");$s->execute([$event,$event]);}
  else{$s=$db->query("SELECT DISTINCT s.code,s.name FROM souvenirs s WHERE s.status='ACTIVE' OR s.id IN (SELECT souvenir_id FROM souvenir_transactions) ORDER BY s.name");}
  return $s->fetchAll();
 }

 // ---------- Ekspor ----------
 // POST dari halaman laporan: ids = id baris yang sedang tampil (dipisah koma, bukan array supaya tidak kena batas max_input_vars).
 // GET tanpa ids tetap didukung: ekspor semua baris event.
 private static function pickRows(array $rows):array{
  if($_SERVER['REQUEST_METHOD']!=='POST')return $rows;
  verify_csrf();
  $raw=trim((string)($_POST['ids']??''));
  $keep=$raw===''?[]:array_flip(array_map('intval',explode(',',$raw)));
  return array_values(array_filter($rows,fn($r)=>isset($keep[(int)$r['id']])));
 }
 private static function subtitle(\PDO $db,int $event,array $parts,int $total):string{
  $name='Semua Event';
  if($event){$s=$db->prepare("SELECT event_name FROM events WHERE id=?");$s->execute([$event]);$name=(string)($s->fetchColumn()?:'Event terpilih');}
  $q=trim((string)($_POST['q']??''));
  if($q!=='')$parts[]='Pencarian: "'.$q.'"';
  return implode('  |  ',array_merge(['Event: '.$name],$parts,['Total: '.$total.' data','Dicetak: '.date('d-m-Y H:i')]));
 }
 private static function fileName(string $base,array $parts):string{
  $slug=strtolower(trim(preg_replace('/[^A-Za-z0-9]+/','-',implode('-',array_filter($parts))),'-'));
  return $base.($slug!==''?'_'.$slug:'').'_'.date('Ymd-Hi').'.xlsx';
 }
 private static function dateCell($sheet,int $c,int $r,?string $v,string $fmt):void{
  if(!$v)return;
  $sheet->setCellValue([$c,$r],ExcelDate::PHPToExcel(new \DateTime($v)));$sheet->getStyle([$c,$r])->getNumberFormat()->setFormatCode($fmt);
 }
 private static function personCells($sheet,int &$c,int $r,array $row):void{
  $sheet->setCellValue([$c++,$r],$row['event_code']);
  $sheet->setCellValue([$c++,$r],$row['event_name']);
  self::dateCell($sheet,$c++,$r,$row['event_date'],'dd-mm-yyyy');
  $sheet->setCellValueExplicit([$c++,$r],(string)$row['nik'],DataType::TYPE_STRING);
  foreach(['name','section','department','division','email'] as $k)$sheet->setCellValueExplicit([$c++,$r],(string)$row[$k],DataType::TYPE_STRING);
 }
 private static function attendanceCells($sheet,int &$c,int $r,array $row,bool $withEmail):void{
  if($withEmail)$sheet->setCellValue([$c++,$r],$row['email_status']==='PENDING'?'BELUM DIKIRIM':$row['email_status']);
  $att=$row['checkin_at']!==null;
  $sheet->setCellValue([$c,$r],$att?'HADIR':'BELUM HADIR');
  $sheet->getStyle([$c,$r])->getFont()->setBold(true)->getColor()->setRGB($att?'2E7D32':'9E9E9E');$c++;
  self::dateCell($sheet,$c++,$r,$row['checkin_at'],'dd-mm-yyyy hh:mm:ss');
 }

 public static function exportAttendance():void{
  Auth::requireLogin();$db=Database::connection();$event=(int)($_REQUEST['event_id']??0);
  $rows=self::pickRows(self::attendanceRows($db,$event));
  $status=(string)($_REQUEST['status']??'all');
  if($_SERVER['REQUEST_METHOD']!=='POST'&&in_array($status,['hadir','belum'],true))$rows=array_values(array_filter($rows,fn($x)=>($x['checkin_at']!==null)===($status==='hadir')));
  $parts=$status==='hadir'?['Status: Hadir']:($status==='belum'?['Status: Belum Hadir']:[]);
  // Baris & urutan sama persis dengan tabel di halaman; kolom Excel tetap lengkap (format lama) + Souvenir Diambil.
  $headers=['No','Kode Event','Event','Tanggal','NIK','Nama','Section','Departemen','Divisi','Email','Status Email','Status Kehadiran','Waktu Check-in','Souvenir Diambil'];
  [$ss,$sheet,$headerRow,$lastCol]=ExcelExport::newSheet('Laporan Kehadiran',self::subtitle($db,$event,$parts,count($rows)),$headers);
  $r=$headerRow+1;
  foreach($rows as $i=>$row){
   $c=1;$sheet->setCellValue([$c++,$r],$i+1);
   self::personCells($sheet,$c,$r,$row);
   self::attendanceCells($sheet,$c,$r,$row,true);
   $sheet->setCellValueExplicit([$c++,$r],(string)($row['souvenirs']??'-')?:'-',DataType::TYPE_STRING);
   $r++;
  }
  ExcelExport::finishSheet($ss,$sheet,$lastCol,$r-1,$headerRow);
  ExcelExport::outputXlsx($ss,self::fileName('laporan_kehadiran',[$event?($rows[0]['event_code']??''):'semua-event',$status!=='all'?($status==='hadir'?'hadir':'belum-hadir'):'']));
 }
 public static function exportSouvenir():void{
  Auth::requireLogin();$db=Database::connection();$event=(int)($_REQUEST['event_id']??0);
  $mode=(string)($_REQUEST['mode']??'taken');
  if($mode==='status'){
   // Status pengambilan per peserta hadir. Baris = yang sedang tampil di halaman (ids); kolom souvenir = sama dengan tabel.
   $rows=self::pickRows(self::pesertaRows($db,$event));
   $items=self::pesertaItems($db,$event);$sid=(int)($_REQUEST['souvenir']??0);
   if($sid)$items=array_values(array_filter($items,fn($it)=>$it['id']===$sid));
   $parts=['Peserta hadir'];if($sid&&$items)$parts[]='Souvenir: '.$items[0]['name'];
   $headers=['No','NIK','Nama','Section','Departemen','Waktu Check-in','Status Pengambilan'];
   foreach($items as $it){$headers[]=$it['name'].' (Jumlah)';$headers[]=$it['name'].' (Waktu Ambil)';}
   [$ss,$sheet,$headerRow,$lastCol]=ExcelExport::newSheet('Status Pengambilan Souvenir',self::subtitle($db,$event,$parts,count($rows)),$headers);
   $r=$headerRow+1;
   foreach($rows as $i=>$row){
    $c=1;$sheet->setCellValue([$c++,$r],$i+1);
    foreach(['nik','name','section','department'] as $k)$sheet->setCellValueExplicit([$c++,$r],(string)$row[$k],DataType::TYPE_STRING);
    self::dateCell($sheet,$c++,$r,$row['checkin_at'],'dd-mm-yyyy hh:mm:ss');
    [$lbl,$rgb]=self::takeStatus($row['take'],$items);
    $sheet->setCellValue([$c,$r],$lbl);$sheet->getStyle([$c,$r])->getFont()->setBold(true)->getColor()->setRGB($rgb);$c++;
    foreach($items as $it){
     $t=$row['take'][$it['id']]??null;
     $sheet->setCellValue([$c++,$r],$t?$t['q']:'-');
     if($t)self::dateCell($sheet,$c,$r,$t['at'],'dd-mm-yyyy hh:mm:ss');else $sheet->setCellValue([$c,$r],'-');$c++;
    }
    $r++;
   }
   ExcelExport::finishSheet($ss,$sheet,$lastCol,$r-1,$headerRow);
   ExcelExport::outputXlsx($ss,self::fileName('status_pengambilan_souvenir',['event-'.$event,$sid&&$items?$items[0]['code']:'']));
   return;
  }
  if($mode==='stock'){
   $rows=self::stockRows($db,$event);
   if($event){
    $headers=['No','Kode','Souvenir','Total Alokasi','Telah Diserahkan','Belum Diserahkan','Realisasi Penyaluran (%)','Kondisi'];
    $keys=['code','name','alokasi','diambil','sisa','persen','kondisi'];
   }else{
    $headers=['No','Kode','Souvenir','Stok Awal','Booked Event','Tersedia','Sudah Diambil','Sisa Event','Stok Akhir','Kondisi','Rincian per Event'];
    $keys=['code','name','stok_awal','booked','tersedia','diambil','sisa_event','stok_akhir','kondisi','detail_text'];
   }
   [$ss,$sheet,$headerRow,$lastCol]=ExcelExport::newSheet('Monitoring Stok Souvenir',self::subtitle($db,$event,$event?[]:['Posisi stok gudang'],count($rows)),$headers);
   $r=$headerRow+1;
   foreach($rows as $i=>$row){$c=1;$sheet->setCellValue([$c++,$r],$i+1);foreach($keys as $k){is_int($row[$k])||is_float($row[$k])?$sheet->setCellValue([$c++,$r],$row[$k]):$sheet->setCellValueExplicit([$c++,$r],(string)$row[$k],DataType::TYPE_STRING);}$r++;}
   ExcelExport::finishSheet($ss,$sheet,$lastCol,$r-1,$headerRow);
   ExcelExport::outputXlsx($ss,self::fileName('stok_souvenir',[$event?'event-'.$event:'gudang']));
   return;
  }
  if($mode==='pending'){
   $rows=self::pickRows(self::notTakenRows($db,$event));
   $status=(string)($_REQUEST['status']??'all');
   $parts=['Belum Ambil Souvenir'];if($status==='hadir')$parts[]='Status: Sudah Hadir';if($status==='belum')$parts[]='Status: Belum Hadir';
   $headers=['No','Kode Event','Event','Tanggal','NIK','Nama','Section','Departemen','Divisi','Email','Status Kehadiran','Waktu Check-in'];
   [$ss,$sheet,$headerRow,$lastCol]=ExcelExport::newSheet('Belum Ambil Souvenir',self::subtitle($db,$event,$parts,count($rows)),$headers);
   $r=$headerRow+1;
   foreach($rows as $i=>$row){$c=1;$sheet->setCellValue([$c++,$r],$i+1);self::personCells($sheet,$c,$r,$row);self::attendanceCells($sheet,$c,$r,$row,false);$r++;}
   ExcelExport::finishSheet($ss,$sheet,$lastCol,$r-1,$headerRow);
   ExcelExport::outputXlsx($ss,self::fileName('belum_ambil_souvenir',[$event?($rows[0]['event_code']??''):'semua-event']));
   return;
  }
  $rows=self::pickRows(self::souvenirRows($db,$event));
  $souv=trim((string)($_REQUEST['souvenir']??''));
  if($_SERVER['REQUEST_METHOD']!=='POST'&&$souv!=='')$rows=array_values(array_filter($rows,fn($x)=>$x['code']===$souv));
  $parts=[];
  if($souv!==''){$s=$db->prepare("SELECT name FROM souvenirs WHERE code=?");$s->execute([$souv]);$parts[]='Souvenir: '.($s->fetchColumn()?:$souv);}
  $headers=['No','Kode Event','Event','Tanggal','NIK','Nama','Section','Departemen','Divisi','Email','Kode Souvenir','Souvenir','Jumlah','Waktu Diambil'];
  [$ss,$sheet,$headerRow,$lastCol]=ExcelExport::newSheet('Laporan Souvenir',self::subtitle($db,$event,$parts,count($rows)),$headers);
  $r=$headerRow+1;
  foreach($rows as $i=>$row){
   $c=1;$sheet->setCellValue([$c++,$r],$i+1);self::personCells($sheet,$c,$r,$row);
   $sheet->setCellValue([$c++,$r],$row['code']);$sheet->setCellValue([$c++,$r],$row['souvenir_name']);$sheet->setCellValue([$c++,$r],(int)$row['quantity']);
   self::dateCell($sheet,$c++,$r,$row['collected_at'],'dd-mm-yyyy hh:mm:ss');$r++;
  }
  ExcelExport::finishSheet($ss,$sheet,$lastCol,$r-1,$headerRow);
  ExcelExport::outputXlsx($ss,self::fileName('laporan_souvenir',[$event?($rows[0]['event_code']??''):'semua-event',$souv]));
 }
}
