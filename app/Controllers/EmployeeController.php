<?php
namespace App\Controllers;
use App\Auth;use App\Database;use App\Support\Audit;
final class EmployeeController {
 // Daftar organisasi (satu sumber untuk form karyawan, template Excel, dan validasi import).
 public static function divisions():array{return ['AD'=>'Administration Division (AD)','BD'=>'Business Division (BD)','DPC'=>'Delivery Planning Control (DPC)','FD'=>'Finance Division (FD)','HRD, GA & PU'=>'HRD, GA & PU','MD'=>'Manufacture Division (MD)'];}
 public static function plantCodesMap():array{return ['AD'=>['AD'],'BD'=>['BD'],'DPC'=>['DPC'],'FD'=>['FD'],'HRD, GA & PU'=>['HR','GA','PU'],'MD'=>['MD']];}
 public static function sectionsMap():array{return [
  'AD'=>['EXIM','IT'],
  'BD'=>['Sales','Collector','Purchasing (material)'],
  'DPC'=>['Internal Sales','PPC','Delivery'],
  'FD'=>['Finance','Accounting','Invoicing','Tax','Costing'],
  'HRD, GA & PU'=>['HRD','General Affair','Purchasing (non material)'],
  'MD'=>['Engineering','Maintenance','Inventory','QC & QA','HSE','TKG','CBK','TWB','CCK','CCC'],
 ];}
 // Nama karyawan: hanya huruf (termasuk huruf beraksen), spasi, titik, tanda petik, tanda hubung; 1-50 karakter.
 public const NAME_MAX=50;
 public static function validName(string $v):bool{return $v!==''&&mb_strlen($v)<=self::NAME_MAX&&(bool)preg_match("/^\\p{L}[\\p{L} .'\\-]*$/u",$v);}
 public static function validDepartmentsFor(string $division):array{
  $map=self::plantCodesMap();if(!isset($map[$division]))return[];
  $out=[];foreach($map[$division] as $c){$out[]="$c-C";$out[]="$c-K";}return $out;
 }
 public static function index():void{
  Auth::requireRole(['ADMIN']);$q=trim($_GET['q']??'');$db=Database::connection();
  $s=$db->prepare("SELECT * FROM employees WHERE name LIKE ? OR nik LIKE ? OR department LIKE ? ORDER BY name LIMIT 1000");
  $x="%$q%";$s->execute([$x,$x,$x]);$employees=$s->fetchAll();require dirname(__DIR__,2).'/views/employees/index.php';
 }
 public static function form(?int $id=null):void{
  Auth::requireRole(['ADMIN']);$db=Database::connection();$employee=null;
  if($id){$s=$db->prepare("SELECT * FROM employees WHERE id=?");$s->execute([$id]);$employee=$s->fetch();if(!$employee){http_response_code(404);exit('Not found');}}
  if($_SERVER['REQUEST_METHOD']==='POST'){verify_csrf();
   $back=$id?'/employees/'.$id.'/edit':'/employees/create';$isNew=!$id;
   $in=fn($k)=>trim((string)($_POST[$k]??''));
   $data=[$in('nik'),$in('name'),$in('section'),$in('department'),$in('division'),$in('email'),in_array($_POST['status']??'',['ACTIVE','INACTIVE'],true)?$_POST['status']:'ACTIVE'];
   // Gagal: tampilkan pesan & kembalikan isian form supaya tidak perlu mengetik ulang.
   $fail=function(string $msg) use($back):never{$old=$_POST;unset($old['_csrf']);$_SESSION['_old_employee']=$old;flash('error',$msg);redirect($back);};
   if(!preg_match('/^\d{1,7}$/',$data[0]))$fail('NIK harus angka saja, maksimal 7 digit.');
   if($data[1]==='')$fail('Nama wajib diisi.');
   if(!self::validName($data[1]))$fail('Nama hanya boleh berisi huruf, spasi, titik, tanda petik, atau tanda hubung (maksimal 50 karakter).');
   if(!filter_var($data[5],FILTER_VALIDATE_EMAIL))$fail('Format email tidak valid.');
   if(!array_key_exists($data[4],self::plantCodesMap()))$fail('Division tidak valid.');
   if(!in_array($data[3],self::validDepartmentsFor($data[4]),true))$fail('Department tidak valid untuk Division yang dipilih.');
   if($data[2]==='')$fail('Section wajib diisi.');
   try{
    if($id){$s=$db->prepare("UPDATE employees SET nik=?,name=?,section=?,department=?,division=?,email=?,status=?,updated_at=NOW() WHERE id=?");$s->execute([...$data,$id]);Audit::log('UPDATE','EMPLOYEE',$id,$data[1]);}
    else{$s=$db->prepare("INSERT INTO employees(nik,name,section,department,division,email,status) VALUES(?,?,?,?,?,?,?)");$s->execute($data);$id=(int)$db->lastInsertId();Audit::log('CREATE','EMPLOYEE',$id,$data[1]);}
   }catch(\PDOException $e){
    if($e->getCode()==='23000')$fail('NIK '.e($data[0]).' sudah terdaftar pada karyawan lain.');
    throw $e;
   }
   flash('success',$isNew?'Karyawan <strong>'.e($data[1]).'</strong> (NIK '.e($data[0]).') berhasil ditambahkan.':'Data karyawan <strong>'.e($data[1]).'</strong> berhasil diperbarui.');
   redirect('/employees');
  }
  $old=$_SESSION['_old_employee']??[];unset($_SESSION['_old_employee']);
  require dirname(__DIR__,2).'/views/employees/form.php';
 }
 public static function delete(int $id):void{Auth::requireRole(['ADMIN']);verify_csrf();$db=Database::connection();$db->prepare("DELETE FROM employees WHERE id=?")->execute([$id]);Audit::log('DELETE','EMPLOYEE',$id);redirect('/employees');}
 public static function bulkDelete():void{
  Auth::requireRole(['ADMIN']);verify_csrf();$ids=array_filter(array_map('intval',$_POST['employee_ids']??[]));
  if($ids){$db=Database::connection();$in=implode(',',array_fill(0,count($ids),'?'));$db->prepare("DELETE FROM employees WHERE id IN ($in)")->execute($ids);Audit::log('DELETE','EMPLOYEE',null,'Bulk delete: '.implode(',',$ids));flash('success',count($ids).' employee dihapus.');}
  redirect('/employees');
 }
 // Template import: kolom NIK hanya angka (maks. 7 digit), dropdown Divisi -> Departemen & Section (menyesuaikan divisi),
 // dropdown Status, dan cek format email. Daftar pilihan disimpan di sheet tersembunyi "Daftar".
 public static function importTemplate():void{
  Auth::requireRole(['ADMIN']);
  $DV=\PhpOffice\PhpSpreadsheet\Cell\DataValidation::class;
  $headers=['nik','name','email','division','department','section','status'];
  $rows=[
   ['1234567','Contoh Nama','contoh.nama@example.com','AD','AD-C','IT','ACTIVE'],
   ['2345678','Contoh Kedua','contoh.kedua@example.com','HRD, GA & PU','HR-K','HRD','ACTIVE'],
  ];
  [$ss,$sheet,$headerRow,$lastCol]=\App\Services\ExcelExport::newSheet('Template Import Karyawan','Isi mulai baris 5 (hapus 2 baris contoh). Pilih Division dulu, lalu Department & Section akan menyesuaikan.',$headers);
  $first=$headerRow+1;$last=$headerRow+1000;
  // Kolom NIK diformat teks supaya angka 0 di depan tidak hilang
  $sheet->getStyle("A$first:A$last")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
  $r=$first;
  foreach($rows as $row){
   foreach($row as $c=>$v)$sheet->setCellValueExplicit([$c+1,$r],$v,\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
   $r++;
  }
  \App\Services\ExcelExport::finishSheet($ss,$sheet,$lastCol,$r-1,$headerRow);
  foreach(['A'=>14,'B'=>26,'C'=>32,'D'=>18,'E'=>14,'F'=>26,'G'=>12] as $col=>$w)$sheet->getColumnDimension($col)->setWidth($w);

  // Sheet tersembunyi berisi daftar pilihan
  $list=$ss->createSheet();$list->setTitle('Daftar');
  $divs=array_keys(self::divisions());$secMap=self::sectionsMap();
  $list->setCellValue('A1','Division');$list->setCellValue('B1','Department');
  $list->setCellValue('A10','Division');$list->setCellValue('B10','Section');
  $list->setCellValue('A20','Status');$list->setCellValue('A21','ACTIVE');$list->setCellValue('A22','INACTIVE');
  foreach($divs as $i=>$d){
   $list->setCellValueExplicit([1,2+$i],$d,\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
   foreach(self::validDepartmentsFor($d) as $j=>$dep)$list->setCellValue([2+$j,2+$i],$dep);
   $list->setCellValueExplicit([1,11+$i],$d,\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
   foreach($secMap[$d] as $j=>$sec)$list->setCellValue([2+$j,11+$i],$sec);
  }
  $n=count($divs);$divRange='Daftar!$A$2:$A$'.(1+$n);$secDivRange='Daftar!$A$11:$A$'.(10+$n);
  $list->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN);
  $ss->setActiveSheetIndex(0);

  $mk=function(string $type,string $formula,string $title,string $err,string $prompt,string $style=\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP) use ($DV){
   $v=new \PhpOffice\PhpSpreadsheet\Cell\DataValidation();
   $v->setType($type);$v->setErrorStyle($style);$v->setAllowBlank(true);
   $v->setShowErrorMessage(true);$v->setShowInputMessage(true);$v->setShowDropDown(true);
   $v->setErrorTitle($title);$v->setError($err);$v->setPromptTitle($title);$v->setPrompt($prompt);
   $v->setFormula1($formula);return $v;
  };
  // NIK: 1–7 karakter dan setiap karakter harus digit 0-9
  $sheet->setDataValidation("A$first:A$last",$mk($DV::TYPE_CUSTOM,
   "AND(LEN(A$first)>=1,LEN(A$first)<=7,LEN(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(A$first,\"0\",\"\"),\"1\",\"\"),\"2\",\"\"),\"3\",\"\"),\"4\",\"\"),\"5\",\"\"),\"6\",\"\"),\"7\",\"\"),\"8\",\"\"),\"9\",\"\"))=0)",
   'NIK tidak valid','NIK hanya boleh berisi angka (0-9), maksimal 7 digit. Huruf, spasi, dan tanda baca tidak diperbolehkan.','Isi dengan angka saja, maksimal 7 digit. Contoh: 2205049'));
  $nameDv=$mk($DV::TYPE_TEXTLENGTH,'50','Nama tidak valid','Nama maksimal 50 karakter dan hanya berisi huruf.','Nama lengkap karyawan: hanya huruf, maksimal 50 karakter.');$nameDv->setOperator($DV::OPERATOR_LESSTHANOREQUAL);
  $sheet->setDataValidation("B$first:B$last",$nameDv);
  $sheet->setDataValidation("C$first:C$last",$mk($DV::TYPE_CUSTOM,
   "AND(ISNUMBER(FIND(\"@\",C$first)),ISNUMBER(FIND(\".\",MID(C$first,FIND(\"@\",C$first),200))),ISERROR(FIND(\" \",C$first)))",
   'Email tidak valid','Format email tidak valid. Contoh: nama@usc-indonesia.co.id (tanpa spasi).','Email aktif untuk menerima undangan. Contoh: nama@usc-indonesia.co.id'));
  $sheet->setDataValidation("D$first:D$last",$mk($DV::TYPE_LIST,'='.$divRange,'Division tidak valid','Pilih Division dari daftar (klik panah dropdown).','Pilih Division dulu. Department & Section akan menyesuaikan.'));
  $sheet->setDataValidation("E$first:E$last",$mk($DV::TYPE_LIST,
   "=OFFSET(Daftar!\$B\$1,MATCH(\$D$first,$divRange,0),0,1,COUNTA(OFFSET(Daftar!\$B\$1,MATCH(\$D$first,$divRange,0),0,1,10)))",
   'Department tidak valid','Pilih Department dari daftar. Isinya menyesuaikan Division (akhiran -C = Cibitung, -K = Karawang). Pastikan kolom Division sudah diisi.','Pilih Division dulu, lalu pilih Department (-C Cibitung, -K Karawang).'));
  $sheet->setDataValidation("F$first:F$last",$mk($DV::TYPE_LIST,
   "=OFFSET(Daftar!\$B\$10,MATCH(\$D$first,$secDivRange,0),0,1,COUNTA(OFFSET(Daftar!\$B\$10,MATCH(\$D$first,$secDivRange,0),0,1,20)))",
   'Section di luar daftar','Section ini tidak ada di daftar untuk Division tersebut. Klik Yes bila memang section lain, atau No untuk memilih dari daftar.','Pilih Section sesuai Division, atau ketik section lain bila tidak ada di daftar.',\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_WARNING));
  $sheet->setDataValidation("G$first:G$last",$mk($DV::TYPE_LIST,'=Daftar!$A$21:$A$22','Status tidak valid','Pilih ACTIVE atau INACTIVE.','ACTIVE = aktif, INACTIVE = nonaktif.'));
  \App\Services\ExcelExport::outputXlsx($ss,'template_import_karyawan.xlsx');
 }
 public static function import():void{
  Auth::requireRole(['ADMIN']);if($_SERVER['REQUEST_METHOD']!=='POST'){require dirname(__DIR__,2).'/views/employees/import.php';return;}
  verify_csrf();if(empty($_FILES['file']['tmp_name'])){flash('error','File wajib dipilih.');redirect('/employees/import');}
  $ext=strtolower(pathinfo($_FILES['file']['name'],PATHINFO_EXTENSION));if(!in_array($ext,['csv','xlsx'],true)){flash('error','Format harus CSV/XLSX.');redirect('/employees/import');}
  $rows=[]; // [nomor baris di file => data per nama kolom]
  if($ext==='csv'){
   $h=fopen($_FILES['file']['tmp_name'],'r');$headers=array_map(fn($v)=>strtolower(trim((string)$v)),fgetcsv($h)?:[]);$line=1;
   while(($r=fgetcsv($h))!==false){$line++;if(count(array_filter($r,fn($v)=>trim((string)$v)!==''))===0)continue;$rows[$line]=array_combine($headers,array_pad(array_slice($r,0,count($headers)),count($headers),''));}
   fclose($h);
  }else{
   $reader=\PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($_FILES['file']['tmp_name']);$reader->setReadDataOnly(true);
   $sheet=$reader->load($_FILES['file']['tmp_name'])->getSheet(0);
   $data=$sheet->toArray(null,true,false,true); // kunci = nomor baris asli di Excel
   $headers=null;
   foreach($data as $rowNo=>$row){
    $normalized=array_map(fn($v)=>strtolower(trim((string)$v)),$row);
    if($headers===null){if(in_array('nik',$normalized,true))$headers=$normalized;continue;}
    if(count(array_filter($row,fn($v)=>trim((string)$v)!==''))===0)continue;
    $rec=[];foreach($headers as $col=>$name)if($name!=='')$rec[$name]=$row[$col]??'';
    $rows[$rowNo]=$rec;
   }
   if($headers===null){flash('error','Kolom header tidak ditemukan. Gunakan template import (harus ada kolom "nik").');redirect('/employees/import');}
  }
  $db=Database::connection();$ok=0;$failed=0;$reasons=[];$seen=[];$db->beginTransaction();
  try{$s=$db->prepare("INSERT INTO employees(nik,name,section,department,division,email,status) VALUES(?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE name=VALUES(name),section=VALUES(section),department=VALUES(department),division=VALUES(division),email=VALUES(email),status=VALUES(status),updated_at=NOW()");
   foreach($rows as $rowNum=>$r){
    $v=fn($k)=>trim((string)($r[$k]??''));
    $nik=$v('nik');$name=$v('name');$email=$v('email');$division=$v('division');$department=strtoupper($v('department'));$section=$v('section');$status=strtoupper($v('status'))?:'ACTIVE';
    if(preg_match('/^\d+\.0+$/',$nik))$nik=substr($nik,0,strpos($nik,'.')); // angka dari Excel mis. "2205049.0"
    $err=null;
    if($nik===''||$name===''||$email==='')$err='NIK, nama, dan email wajib diisi';
    elseif(!preg_match('/^\d{1,7}$/',$nik))$err='NIK "'.$nik.'" tidak valid (hanya angka, maksimal 7 digit)';
    elseif(isset($seen[$nik]))$err='NIK '.$nik.' ganda di file (sudah ada di baris '.$seen[$nik].')';
    elseif(!self::validName($name))$err='nama "'.$name.'" tidak valid (hanya huruf, maksimal 50 karakter)';
    elseif(!filter_var($email,FILTER_VALIDATE_EMAIL))$err='email "'.$email.'" tidak valid';
    elseif(!array_key_exists($division,self::plantCodesMap()))$err='Division "'.$division.'" tidak valid';
    elseif(!in_array($department,self::validDepartmentsFor($division),true))$err='Department "'.$department.'" tidak sesuai Division '.$division;
    elseif($section==='')$err='Section wajib diisi';
    elseif(!in_array($status,['ACTIVE','INACTIVE'],true))$err='Status "'.$status.'" tidak valid (ACTIVE/INACTIVE)';
    if($err){$failed++;if(count($reasons)<5)$reasons[]="Baris $rowNum: ".e($err);continue;}
    $seen[$nik]=$rowNum;
    $s->execute([$nik,$name,$section,$department,$division,$email,$status]);$ok++;
   }
   $db->commit();Audit::log('IMPORT','EMPLOYEE',null,"Imported $ok rows, failed $failed");
   $msg="Import selesai: $ok berhasil, $failed gagal.";if($reasons)$msg.=' Contoh gagal: '.implode('; ',$reasons).($failed>count($reasons)?'; ...':'');
   flash($failed?'error':'success',$msg);
  }catch(\Throwable $e){$db->rollBack();flash('error','Import gagal: '.e($e->getMessage()));} redirect('/employees');
 }
}
