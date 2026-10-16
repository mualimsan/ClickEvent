<?php
namespace App\Controllers;
use App\Auth;use App\Database;use App\Services\ExcelExport;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
final class DashboardController {
 // Dashboard per event: semua kartu menghitung data satu event yang dipilih.
 public static function index():void{
  Auth::requireRole(['ADMIN','EVENT_OPERATOR']);$db=Database::connection();
  $events=self::events($db);$ev=self::pickEvent($db,$events);
  $isAdmin=Auth::isAdmin();
  // Operator hanya melihat data operasional; undangan email & export khusus admin.
  $cards=$ev?array_values(array_filter(self::cards($db,$ev),fn($c)=>$isAdmin||$c['key']!=='undangan')):[];
  $panels=$ev?self::panels($db,$ev):null;
  $hariMap=['Sunday'=>'Minggu','Monday'=>'Senin','Tuesday'=>'Selasa','Wednesday'=>'Rabu','Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu'];
  $bulanMap=['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'];
  $todayLabel=$hariMap[date('l')].', '.((int)date('d')).' '.$bulanMap[date('m')].' '.date('Y');
  $timeLabel=date('H:i').' WIB';
  require dirname(__DIR__,2).'/views/dashboard.php';
 }
 // Daftar nama untuk satu kartu, diambil saat kartu diklik (tidak ikut dimuat di halaman).
 public static function cardData(string $key):void{
  Auth::requireRole(['ADMIN','EVENT_OPERATOR']);$db=Database::connection();header('Content-Type: application/json');
  $ev=self::pickEvent($db,self::events($db));$items=[];
  if($ev&&($key!=='undangan'||Auth::isAdmin()))foreach(self::cards($db,$ev) as $c)if($c['key']===$key)$items=$c['items'];
  echo json_encode($items);
 }
 public static function exportCard(string $key):void{
  Auth::requireRole(['ADMIN']);$db=Database::connection();
  $ev=self::pickEvent($db,self::events($db));if(!$ev){http_response_code(404);exit('Not found');}
  $card=null;foreach(self::cards($db,$ev) as $c)if($c['key']===$key)$card=$c;
  if(!$card){http_response_code(404);exit('Not found');}
  // Satu format untuk semua kartu: No, Nama, Status, lalu kolom rincian kartu tersebut.
  $fieldLabels=$card['items']?array_column($card['items'][0]['fields'],'label'):[];
  [$ss,$sheet,$headerRow,$lastCol]=ExcelExport::newSheet($card['label'],'Event: '.$ev['event_name'].' ('.dmy($ev['event_date']).')  |  Dicetak: '.date('d-m-Y H:i'),array_merge(['No',$key==='souvenir'?'Souvenir':'Nama','Status'],$fieldLabels));
  $r=$headerRow+1;
  foreach($card['items'] as $i=>$it){
   $c=1;$sheet->setCellValue([$c++,$r],$i+1);
   foreach(array_merge([$it['title'],$it['badge']],array_column($it['fields'],'value')) as $v)$sheet->setCellValueExplicit([$c++,$r],(string)$v,DataType::TYPE_STRING);
   $r++;
  }
  ExcelExport::finishSheet($ss,$sheet,$lastCol,$r-1,$headerRow);
  ExcelExport::outputXlsx($ss,'dashboard_'.$key.'_'.$ev['event_code'].'.xlsx');
 }

 private static function events(\PDO $db):array{return $db->query("SELECT * FROM events WHERE status!='DRAFT' ORDER BY event_date DESC,id DESC")->fetchAll();}
 // Event terpilih: dari ?event_id, kalau tidak ada: event hari ini → event terdekat berikutnya → event terakhir.
 private static function pickEvent(\PDO $db,array $events):?array{
  $id=(int)($_GET['event_id']??0);
  foreach($events as $e)if((int)$e['id']===$id)return $e;
  $today=date('Y-m-d');$next=null;
  foreach($events as $e){if($e['event_date']===$today&&$e['status']==='PUBLISHED')return $e;if($e['event_date']>$today&&$e['status']==='PUBLISHED')$next=$e;}
  return $next??($events[0]??null);
 }
 // Grafik bawah: jumlah sudah hadir & belum hadir per departemen untuk event terpilih.
 private static function panels(\PDO $db,array $ev):array{
  $s=$db->prepare("SELECT COALESCE(NULLIF(e.department,''),'-') dept,COUNT(*) invited,COUNT(a.id) hadir FROM invitations i JOIN employees e ON e.id=i.employee_id LEFT JOIN attendances a ON a.invitation_id=i.id WHERE i.event_id=? GROUP BY dept ORDER BY invited DESC,dept");
  $s->execute([(int)$ev['id']]);
  return ['depts'=>$s->fetchAll()];
 }
 private static function cards(\PDO $db,array $ev):array{
  $eid=(int)$ev['id'];$t=fn(string $color,string $text,string $icon='flat')=>['icon'=>$icon,'color'=>$color,'text'=>$text];
  $days=(int)round((strtotime($ev['event_date'])-strtotime(date('Y-m-d')))/86400);
  $when=$days===0?'hari ini':($days>0?'H-'.$days:'sudah lewat');
  $s=$db->prepare("SELECT i.id,i.email_status,i.qr_generated_at,e.nik,e.name,e.department,e.email,a.checkin_at,(SELECT u.name FROM users u WHERE u.id=a.operator_user_id) pic
   FROM invitations i JOIN employees e ON e.id=i.employee_id LEFT JOIN attendances a ON a.invitation_id=i.id WHERE i.event_id=? ORDER BY e.name");
  $s->execute([$eid]);$rows=$s->fetchAll();
  $emailLbl=fn($r)=>$r['email_status']==='PENDING'?'BELUM DIKIRIM':$r['email_status'];
  $hadir=array_values(array_filter($rows,fn($r)=>$r['checkin_at']!==null));
  $belum=array_values(array_filter($rows,fn($r)=>$r['checkin_at']===null));
  $pend=array_values(array_filter($rows,fn($r)=>$r['email_status']!=='SENT'));
  $n=count($rows);$nh=count($hadir);$nb=count($belum);$np=count($pend);

  // Undangan belum terkirim: rincian penyebabnya
  $noQr=count(array_filter($pend,fn($r)=>!$r['qr_generated_at']));$failed=count(array_filter($pend,fn($r)=>$r['qr_generated_at']&&$r['email_status']==='FAILED'));$ready=$np-$noQr-$failed;
  $parts=[];if($ready)$parts[]=$ready.' siap dikirim';if($noQr)$parts[]=$noQr.' belum generate QR';if($failed)$parts[]=$failed.' gagal';
  $pendRisk=$np===0?'ok':(($days>=0&&$days<=3)||$failed?'danger':'warn');

  // Souvenir: alokasi vs diserahkan per souvenir di event ini
  $s=$db->prepare("SELECT s.name,COALESCE(es.quantity_allocated,0) alloc,COALESCE((SELECT SUM(st.quantity) FROM souvenir_transactions st JOIN invitations ii ON ii.id=st.invitation_id WHERE ii.event_id=? AND st.souvenir_id=s.id),0) taken
   FROM souvenirs s LEFT JOIN event_souvenirs es ON es.souvenir_id=s.id AND es.event_id=? HAVING alloc>0 OR taken>0 ORDER BY s.code");
  $s->execute([$eid,$eid]);$sv=$s->fetchAll();
  $alloc=array_sum(array_column($sv,'alloc'));$taken=array_sum(array_column($sv,'taken'));

  $pct=fn($a,$b)=>$b?round($a/$b*100).'%':'0%';
  return [
   ['key'=>'diundang','label'=>'Peserta Diundang','value'=>$n,'risk'=>'neutral','trend'=>$t('neutral','Event '.dmy($ev['event_date']).' ('.$when.')'),
    'items'=>array_map(fn($r)=>['title'=>$r['name'],'badge'=>$r['checkin_at']?'HADIR':'BELUM HADIR','badgeColor'=>$r['checkin_at']?'success':'secondary','fields'=>[['label'=>'NIK','value'=>$r['nik']],['label'=>'Departemen','value'=>$r['department']],['label'=>'Email','value'=>$r['email']],['label'=>'Status Email','value'=>$emailLbl($r)]]],$rows)],
   ['key'=>'undangan','label'=>'Undangan Belum Terkirim','value'=>$np,'risk'=>$pendRisk,'trend'=>$np===0?$t('good','Semua sudah terkirim'):$t($pendRisk==='danger'?'bad':'warn',implode(' · ',array_slice($parts,0,2)),'down'),
    'items'=>array_map(fn($r)=>['title'=>$r['name'],'badge'=>$emailLbl($r),'badgeColor'=>$r['email_status']==='FAILED'?'danger':'warning','fields'=>[['label'=>'NIK','value'=>$r['nik']],['label'=>'Departemen','value'=>$r['department']],['label'=>'Email','value'=>$r['email']],['label'=>'QR Code','value'=>$r['qr_generated_at']?'Sudah dibuat':'Belum dibuat']]],$pend)],
   ['key'=>'hadir','label'=>'Sudah Hadir','value'=>$nh,'risk'=>$nh?'ok':'neutral','trend'=>$days>0?$t('neutral','Event belum dimulai'):$t($nh?'good':'neutral',$pct($nh,$n).' dari '.$n.' diundang',$nh?'up':'flat'),
    'items'=>array_map(fn($r)=>['title'=>$r['name'],'badge'=>'HADIR','badgeColor'=>'success','fields'=>[['label'=>'NIK','value'=>$r['nik']],['label'=>'Departemen','value'=>$r['department']],['label'=>'Waktu Check-in','value'=>dmy($r['checkin_at'])],['label'=>'PIC Check-in','value'=>$r['pic']??'-']]],$hadir)],
   ['key'=>'belum','label'=>'Belum Hadir','value'=>$nb,'risk'=>$nb&&$days<0?'warn':'neutral','trend'=>$nb===0&&$n?$t('good','Semua peserta hadir'):($days<0?$t('warn',$nb.' peserta tidak hadir','down'):$t('neutral',$pct($nb,$n).' dari '.$n.' diundang')),
    'items'=>array_map(fn($r)=>['title'=>$r['name'],'badge'=>'BELUM HADIR','badgeColor'=>'secondary','fields'=>[['label'=>'NIK','value'=>$r['nik']],['label'=>'Departemen','value'=>$r['department']],['label'=>'Email','value'=>$r['email']],['label'=>'Status Email','value'=>$emailLbl($r)]]],$belum)],
   ['key'=>'souvenir','label'=>'Souvenir Diserahkan','value'=>$taken,'risk'=>'neutral','trend'=>$alloc?$t($taken>=$alloc?'good':'neutral','dari '.$alloc.' alokasi · sisa '.max($alloc-$taken,0),$taken?'up':'flat'):$t('neutral','Belum ada alokasi souvenir'),
    'items'=>array_map(fn($r)=>['title'=>$r['name'],'badge'=>$r['taken'].'/'.$r['alloc'],'badgeColor'=>$r['taken']>=$r['alloc']?'success':'warning','fields'=>[['label'=>'Alokasi','value'=>(string)$r['alloc']],['label'=>'Telah Diserahkan','value'=>(string)$r['taken']],['label'=>'Belum Diserahkan','value'=>(string)max($r['alloc']-$r['taken'],0)],['label'=>'Realisasi','value'=>$pct($r['taken'],$r['alloc'])]]],$sv)],
  ];
 }
}
