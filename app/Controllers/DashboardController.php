<?php
namespace App\Controllers;
use App\Auth;use App\Database;use App\Services\ExcelExport;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
final class DashboardController {
 public static function index():void{
  Auth::requireRole(['ADMIN','EVENT_OPERATOR']);$db=Database::connection();
  $employees=(int)$db->query("SELECT COUNT(*) FROM employees WHERE status='ACTIVE'")->fetchColumn();
  $upcomingEvents=(int)$db->query("SELECT COUNT(*) FROM events WHERE status='PUBLISHED' AND event_date>=CURDATE()")->fetchColumn();
  $pendingInvites=(int)$db->query("SELECT COUNT(*) FROM invitations i JOIN events e ON e.id=i.event_id WHERE e.status='PUBLISHED' AND i.email_status!='SENT'")->fetchColumn();
  $checkinToday=(int)$db->query("SELECT COUNT(*) FROM attendances WHERE DATE(checkin_at)=CURDATE()")->fetchColumn();
  $lowStock=(int)$db->query("SELECT COUNT(*) FROM souvenirs WHERE status='ACTIVE' AND stock<5")->fetchColumn();

  $empRows=$db->query("SELECT nik,name,department,division,email FROM employees WHERE status='ACTIVE' ORDER BY name")->fetchAll();
  $empItems=array_map(fn($r)=>['title'=>$r['name'],'badge'=>'ACTIVE','badgeColor'=>'success','fields'=>[['label'=>'NIK','value'=>$r['nik']],['label'=>'Departemen','value'=>$r['department']],['label'=>'Divisi','value'=>$r['division']],['label'=>'Email','value'=>$r['email']]]],$empRows);

  $evRows=$db->query("SELECT e.id,e.event_name,e.event_date,e.location,COUNT(DISTINCT i.id) invited,COUNT(DISTINCT a.id) checked_in FROM events e LEFT JOIN invitations i ON i.event_id=e.id LEFT JOIN attendances a ON a.invitation_id=i.id WHERE e.status='PUBLISHED' AND e.event_date>=CURDATE() GROUP BY e.id ORDER BY e.event_date ASC")->fetchAll();
  $evItems=array_map(fn($r)=>['title'=>$r['event_name'],'badge'=>'PUBLISHED','badgeColor'=>'success','fields'=>[['label'=>'Tanggal','value'=>dmy($r['event_date'])],['label'=>'Lokasi','value'=>$r['location']],['label'=>'Diundang','value'=>(string)$r['invited']],['label'=>'Sudah Hadir','value'=>(string)$r['checked_in']]]],$evRows);

  $pendRows=$db->query("SELECT emp.name,emp.department,emp.email,ev.event_name,ev.event_date,i.email_status FROM invitations i JOIN events ev ON ev.id=i.event_id JOIN employees emp ON emp.id=i.employee_id WHERE ev.status='PUBLISHED' AND i.email_status!='SENT' ORDER BY ev.event_date ASC,emp.name")->fetchAll();
  $pendItems=array_map(fn($r)=>['title'=>$r['name'],'badge'=>$r['email_status']==='PENDING'?'BELUM DIKIRIM':$r['email_status'],'badgeColor'=>$r['email_status']==='FAILED'?'danger':'warning','fields'=>[['label'=>'Event','value'=>$r['event_name']],['label'=>'Tanggal Event','value'=>dmy($r['event_date'])],['label'=>'Departemen','value'=>$r['department']],['label'=>'Email','value'=>$r['email']]]],$pendRows);
  $nearestPendingDays=null;
  foreach($pendRows as $r){$d=(int)((strtotime($r['event_date'])-strtotime(date('Y-m-d')))/86400);if($nearestPendingDays===null||$d<$nearestPendingDays)$nearestPendingDays=$d;}
  $undanganRisk=$pendingInvites===0?'ok':($nearestPendingDays!==null&&$nearestPendingDays<=3?'danger':'warn');

  $checkinRows=$db->query("SELECT emp.name,emp.nik,emp.department,ev.event_name,a.checkin_at FROM attendances a JOIN invitations i ON i.id=a.invitation_id JOIN employees emp ON emp.id=i.employee_id JOIN events ev ON ev.id=i.event_id WHERE DATE(a.checkin_at)=CURDATE() ORDER BY a.checkin_at DESC")->fetchAll();
  $checkinItems=array_map(fn($r)=>['title'=>$r['name'],'badge'=>'HADIR','badgeColor'=>'success','fields'=>[['label'=>'NIK','value'=>$r['nik']],['label'=>'Event','value'=>$r['event_name']],['label'=>'Departemen','value'=>$r['department']],['label'=>'Waktu Check-in','value'=>dmy($r['checkin_at'])]]],$checkinRows);
  $todayEventRows=$db->query("SELECT e.event_name,COUNT(DISTINCT i.id) invited FROM events e LEFT JOIN invitations i ON i.event_id=e.id WHERE e.status='PUBLISHED' AND e.event_date=CURDATE() GROUP BY e.id")->fetchAll();
  $invitedToday=array_sum(array_column($todayEventRows,'invited'));
  // Check-in hari ini khusus untuk event yang tanggalnya hari ini (pembanding "x dari y diundang" harus dari event yang sama).
  $checkinTodayEvent=(int)$db->query("SELECT COUNT(*) FROM attendances a JOIN invitations i ON i.id=a.invitation_id JOIN events e ON e.id=i.event_id WHERE DATE(a.checkin_at)=CURDATE() AND e.event_date=CURDATE() AND e.status='PUBLISHED'")->fetchColumn();
  $checkinOther=$checkinToday-$checkinTodayEvent;
  if(empty($todayEventRows)){
   if($checkinToday>0){$checkinRisk='warn';$checkinSub='Scan untuk event tanggal lain';}
   else{$checkinRisk='neutral';$checkinSub='Tidak ada event hari ini';}
  }
  elseif($checkinTodayEvent===0 && $invitedToday>0){$checkinRisk='danger';$checkinSub='0 dari '.$invitedToday.' diundang';}
  else{$checkinRisk='ok';$checkinSub=$checkinTodayEvent.' dari '.$invitedToday.' diundang'.($checkinOther>0?' (+'.$checkinOther.' event lain)':'');}

  $stockRows=$db->query("SELECT sv.code,sv.name,sv.stock,
    (SELECT ev.event_name FROM event_souvenirs es JOIN events ev ON ev.id=es.event_id WHERE es.souvenir_id=sv.id AND ev.status='PUBLISHED' AND ev.event_date>=CURDATE() ORDER BY ev.event_date ASC LIMIT 1) nearest_event,
    (SELECT ev.event_date FROM event_souvenirs es JOIN events ev ON ev.id=es.event_id WHERE es.souvenir_id=sv.id AND ev.status='PUBLISHED' AND ev.event_date>=CURDATE() ORDER BY ev.event_date ASC LIMIT 1) nearest_date
   FROM souvenirs sv WHERE sv.status='ACTIVE' AND sv.stock<5 ORDER BY sv.stock ASC")->fetchAll();
  $stockItems=array_map(fn($r)=>['title'=>$r['name'],'badge'=>'MENIPIS','badgeColor'=>'danger','fields'=>[['label'=>'Kode','value'=>$r['code']],['label'=>'Sisa Stok','value'=>(string)$r['stock']],['label'=>'Event Terdekat','value'=>$r['nearest_event']??'Belum dialokasikan ke event manapun']]],$stockRows);
  $stokRisk='ok';
  foreach($stockRows as $r){if($r['nearest_date'] && (int)((strtotime($r['nearest_date'])-strtotime(date('Y-m-d')))/86400)<=7){$stokRisk='danger';break;}if($lowStock>0)$stokRisk='warn';}

  $trend=fn(string $color,string $text,string $icon='flat')=>['icon'=>$icon,'color'=>$color,'text'=>$text];
  $hMinus=function(string $date):string{$d=(int)round((strtotime($date)-strtotime(date('Y-m-d')))/86400);return $d===0?'hari ini':'H-'.$d;};
  // Karyawan
  $totalEmployees=(int)$db->query("SELECT COUNT(*) FROM employees")->fetchColumn();$inactive=$totalEmployees-$employees;
  $trendKaryawan=$totalEmployees===0?$trend('neutral','Belum ada data karyawan'):($inactive>0?$trend('neutral',$inactive.' nonaktif dari '.$totalEmployees):$trend('good','Semua karyawan aktif'));
  // Event
  $draftEvents=(int)$db->query("SELECT COUNT(*) FROM events WHERE status='DRAFT' AND event_date>=CURDATE()")->fetchColumn();
  if($evRows){$n=$evRows[0];$trendEvent=$trend('neutral','Terdekat '.dmy($n['event_date']).' ('.$hMinus($n['event_date']).')');}
  else{$trendEvent=$trend('neutral',$draftEvents?$draftEvents.' event masih DRAFT':'Belum ada event terjadwal');}
  // Undangan: rincian kenapa belum terkirim
  $pendNoQr=0;$pendFailed=0;$pendReady=0;
  foreach($db->query("SELECT (i.qr_generated_at IS NULL) noqr,i.email_status FROM invitations i JOIN events e ON e.id=i.event_id WHERE e.status='PUBLISHED' AND i.email_status!='SENT'") as $r){
   if($r['noqr'])$pendNoQr++;elseif($r['email_status']==='FAILED')$pendFailed++;else $pendReady++;
  }
  $parts=[];if($pendReady)$parts[]=$pendReady.' siap dikirim';if($pendNoQr)$parts[]=$pendNoQr.' belum generate QR';if($pendFailed)$parts[]=$pendFailed.' gagal';
  $trendUndanganNew=$pendingInvites===0?$trend('good','Semua sudah terkirim'):$trend($undanganRisk==='danger'||$pendFailed?'bad':'warn',implode(' · ',array_slice($parts,0,2)),'down');
  // Stok
  $trendStokNew=$lowStock===0?$trend('good','Semua stok aman'):$trend($stokRisk==='danger'?'bad':'warn','Terendah: '.$stockRows[0]['name'].' (sisa '.$stockRows[0]['stock'].')','down');
  $trendUndangan=$pendingInvites===0?['icon'=>'flat','color'=>'neutral','text'=>'Tidak ada yang tertunda']:($undanganRisk==='danger'?['icon'=>'down','color'=>'bad','text'=>'Perlu segera dikirim']:['icon'=>'down','color'=>'warn','text'=>'Menunggu dikirim']);
  $trendCheckin=['neutral'=>$trend('neutral',$checkinSub),'warn'=>$trend('warn',$checkinSub,'down'),'danger'=>$trend('bad',$checkinSub,'down')][$checkinRisk]??$trend('good',$checkinSub,'up');
  $trendStok=$stokRisk==='ok'?['icon'=>'flat','color'=>'neutral','text'=>'Stok aman']:($stokRisk==='danger'?['icon'=>'down','color'=>'bad','text'=>'Perlu restock segera']:['icon'=>'down','color'=>'warn','text'=>'Perlu dipantau']);

  $cards=[
   ['key'=>'karyawan','label'=>'Karyawan Aktif','value'=>$employees,'risk'=>'neutral','trend'=>$trendKaryawan,'why'=>'Basis karyawan yang tersedia untuk diundang ke event. Kalau angka ini tiba-tiba turun drastis, itu tanda ada data terhapus tidak sengaja.','items'=>$empItems],
   ['key'=>'event','label'=>'Event Akan Datang','value'=>$upcomingEvents,'risk'=>'neutral','trend'=>$trendEvent,'why'=>'Jumlah event berstatus PUBLISHED dengan tanggal hari ini atau setelahnya. Event DRAFT belum dihitung.','items'=>$evItems],
   ['key'=>'undangan','label'=>'Undangan Belum Terkirim','value'=>$pendingInvites,'risk'=>$undanganRisk,'trend'=>$trendUndanganNew,'why'=>'Jumlah undangan pada event PUBLISHED yang emailnya belum terkirim: belum generate QR, sudah siap tapi belum dikirim, atau gagal terkirim.','items'=>$pendItems],
   ['key'=>'checkin','label'=>'Check-in Hari Ini','value'=>$checkinToday,'risk'=>$checkinRisk,'trend'=>$trendCheckin,'why'=>'Jumlah scan check-in yang terjadi hari ini. Keterangan di bawah angka membandingkan dengan jumlah undangan event yang tanggalnya hari ini.','items'=>$checkinItems],
   ['key'=>'stok','label'=>'Stok Souvenir Menipis','value'=>$lowStock,'risk'=>$stokRisk,'trend'=>$trendStokNew,'why'=>'Jumlah jenis souvenir dengan stok di bawah 5 — peringatan dini supaya bisa restock sebelum kehabisan di hari-H.','items'=>$stockItems],
  ];
  $events=$db->query("SELECT e.*,COUNT(DISTINCT i.id) invited,COUNT(DISTINCT a.id) checked_in FROM events e LEFT JOIN invitations i ON i.event_id=e.id LEFT JOIN attendances a ON a.invitation_id=i.id GROUP BY e.id ORDER BY e.event_date DESC LIMIT 25")->fetchAll();
  $hariMap=['Sunday'=>'Minggu','Monday'=>'Senin','Tuesday'=>'Selasa','Wednesday'=>'Rabu','Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu'];
  $bulanMap=['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'];
  $todayLabel=$hariMap[date('l')].', '.((int)date('d')).' '.$bulanMap[date('m')].' '.date('Y');
  $timeLabel=date('H:i').' WIB';
  require dirname(__DIR__,2).'/views/dashboard.php';
 }
 public static function exportCard(string $key):void{
  Auth::requireRole(['ADMIN','EVENT_OPERATOR']);$db=Database::connection();
  $printed='Dicetak: '.date('d-m-Y H:i');
  switch($key){
   case 'karyawan':
    $rows=$db->query("SELECT nik,name,department,division,email FROM employees WHERE status='ACTIVE' ORDER BY name")->fetchAll();
    $headers=['No','NIK','Nama','Departemen','Divisi','Email','Status'];
    [$ss,$sheet,$headerRow,$lastCol]=ExcelExport::newSheet('Karyawan Aktif',$printed,$headers);
    $r=$headerRow+1;$no=1;
    foreach($rows as $row){$c=1;
     $sheet->setCellValue([$c++,$r],$no++);
     $sheet->setCellValueExplicit([$c++,$r],$row['nik'],\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
     $sheet->setCellValue([$c++,$r],$row['name']);
     $sheet->setCellValue([$c++,$r],$row['department']);
     $sheet->setCellValue([$c++,$r],$row['division']);
     $sheet->setCellValue([$c++,$r],$row['email']);
     $sheet->setCellValue([$c,$r],'ACTIVE');$r++;
    }
    ExcelExport::finishSheet($ss,$sheet,$lastCol,$r-1,$headerRow);
    ExcelExport::outputXlsx($ss,'karyawan_aktif.xlsx');
    break;
   case 'event':
    $rows=$db->query("SELECT e.event_code,e.event_name,e.event_date,e.location,COUNT(DISTINCT i.id) invited,COUNT(DISTINCT a.id) checked_in FROM events e LEFT JOIN invitations i ON i.event_id=e.id LEFT JOIN attendances a ON a.invitation_id=i.id WHERE e.status='PUBLISHED' AND e.event_date>=CURDATE() GROUP BY e.id ORDER BY e.event_date ASC")->fetchAll();
    $headers=['No','Kode Event','Event','Tanggal','Lokasi','Diundang','Sudah Hadir'];
    [$ss,$sheet,$headerRow,$lastCol]=ExcelExport::newSheet('Event Akan Datang',$printed,$headers);
    $r=$headerRow+1;$no=1;
    foreach($rows as $row){$c=1;
     $sheet->setCellValue([$c++,$r],$no++);
     $sheet->setCellValue([$c++,$r],$row['event_code']);
     $sheet->setCellValue([$c++,$r],$row['event_name']);
     $sheet->setCellValue([$c,$r],ExcelDate::PHPToExcel(new \DateTime($row['event_date'])));$sheet->getStyle([$c,$r])->getNumberFormat()->setFormatCode('dd-mm-yyyy');$c++;
     $sheet->setCellValue([$c++,$r],$row['location']);
     $sheet->setCellValue([$c++,$r],(int)$row['invited']);
     $sheet->setCellValue([$c,$r],(int)$row['checked_in']);$r++;
    }
    ExcelExport::finishSheet($ss,$sheet,$lastCol,$r-1,$headerRow);
    ExcelExport::outputXlsx($ss,'event_akan_datang.xlsx');
    break;
   case 'undangan':
    $rows=$db->query("SELECT emp.nik,emp.name,emp.department,emp.email,ev.event_name,i.email_status FROM invitations i JOIN events ev ON ev.id=i.event_id JOIN employees emp ON emp.id=i.employee_id WHERE ev.status='PUBLISHED' AND i.email_status!='SENT' ORDER BY ev.event_date ASC,emp.name")->fetchAll();
    $headers=['No','Event','NIK','Nama','Departemen','Email','Status Email'];
    [$ss,$sheet,$headerRow,$lastCol]=ExcelExport::newSheet('Undangan Belum Terkirim',$printed,$headers);
    $r=$headerRow+1;$no=1;
    foreach($rows as $row){$c=1;
     $sheet->setCellValue([$c++,$r],$no++);
     $sheet->setCellValue([$c++,$r],$row['event_name']);
     $sheet->setCellValueExplicit([$c++,$r],$row['nik'],\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
     $sheet->setCellValue([$c++,$r],$row['name']);
     $sheet->setCellValue([$c++,$r],$row['department']);
     $sheet->setCellValue([$c++,$r],$row['email']);
     $sheet->setCellValue([$c,$r],$row['email_status']);
     $sheet->getStyle([$c,$r])->getFont()->setBold(true)->getColor()->setRGB($row['email_status']==='FAILED'?'C62828':'B8860B');$r++;
    }
    ExcelExport::finishSheet($ss,$sheet,$lastCol,$r-1,$headerRow);
    ExcelExport::outputXlsx($ss,'undangan_belum_terkirim.xlsx');
    break;
   case 'checkin':
    $rows=$db->query("SELECT emp.nik,emp.name,emp.department,ev.event_name,a.checkin_at FROM attendances a JOIN invitations i ON i.id=a.invitation_id JOIN employees emp ON emp.id=i.employee_id JOIN events ev ON ev.id=i.event_id WHERE DATE(a.checkin_at)=CURDATE() ORDER BY a.checkin_at DESC")->fetchAll();
    $headers=['No','Event','NIK','Nama','Departemen','Waktu Check-in'];
    [$ss,$sheet,$headerRow,$lastCol]=ExcelExport::newSheet('Check-in Hari Ini',$printed,$headers);
    $r=$headerRow+1;$no=1;
    foreach($rows as $row){$c=1;
     $sheet->setCellValue([$c++,$r],$no++);
     $sheet->setCellValue([$c++,$r],$row['event_name']);
     $sheet->setCellValueExplicit([$c++,$r],$row['nik'],\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
     $sheet->setCellValue([$c++,$r],$row['name']);
     $sheet->setCellValue([$c++,$r],$row['department']);
     $sheet->setCellValue([$c,$r],ExcelDate::PHPToExcel(new \DateTime($row['checkin_at'])));$sheet->getStyle([$c,$r])->getNumberFormat()->setFormatCode('dd-mm-yyyy hh:mm:ss');$r++;
    }
    ExcelExport::finishSheet($ss,$sheet,$lastCol,$r-1,$headerRow);
    ExcelExport::outputXlsx($ss,'checkin_hari_ini.xlsx');
    break;
   case 'stok':
    $rows=$db->query("SELECT code,name,stock FROM souvenirs WHERE status='ACTIVE' AND stock<5 ORDER BY stock ASC")->fetchAll();
    $headers=['No','Kode','Souvenir','Sisa Stok'];
    [$ss,$sheet,$headerRow,$lastCol]=ExcelExport::newSheet('Stok Souvenir Menipis',$printed,$headers);
    $r=$headerRow+1;$no=1;
    foreach($rows as $row){$c=1;
     $sheet->setCellValue([$c++,$r],$no++);
     $sheet->setCellValue([$c++,$r],$row['code']);
     $sheet->setCellValue([$c++,$r],$row['name']);
     $sheet->setCellValue([$c,$r],(int)$row['stock']);
     $sheet->getStyle([$c,$r])->getFont()->setBold(true)->getColor()->setRGB('C62828');$r++;
    }
    ExcelExport::finishSheet($ss,$sheet,$lastCol,$r-1,$headerRow);
    ExcelExport::outputXlsx($ss,'stok_souvenir_menipis.xlsx');
    break;
   default: http_response_code(404); exit('Not found');
  }
 }
}
