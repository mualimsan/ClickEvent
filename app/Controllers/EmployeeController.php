<?php
namespace App\Controllers;
use App\Auth;use App\Database;use App\Support\Audit;
final class EmployeeController {
 // Daftar Divisi -> Departemen -> Section diambil dari data karyawan yang sudah ada (mengikuti Excel kantor).
 public static function orgTree(\PDO $db):array{
  $t=[];foreach($db->query("SELECT DISTINCT division,department,section FROM employees WHERE division<>'' ORDER BY division,department,section") as $r)$t[$r['division']][$r['department']][]=$r['section'];
  return $t;
 }
 // Nama karyawan: huruf (termasuk beraksen), spasi, titik, koma, tanda petik, tanda hubung, tanda kurung; 1-50 karakter.
 public const NAME_MAX=50;
 public static function validName(string $v):bool{return $v!==''&&mb_strlen($v)<=self::NAME_MAX&&(bool)preg_match("/^\\p{L}[\\p{L} .,'()\\-]*$/u",$v);}
 // NIK: karyawan tetap (angka) maupun PKL/outsourcing/internship/driver, mis. BAA-2409065, PSB-2509001, VOC-2608132, DRV.01, INT-NEW 001.
 // Spasi di tengah boleh (dirapikan jadi satu spasi), tidak boleh di awal/akhir.
 public const NIK_MAX=20;
 public static function normNik(string $v):string{return strtoupper(preg_replace('/\s+/',' ',trim($v)));}
 public static function validNik(string $v):bool{return strlen($v)<=self::NIK_MAX&&(bool)preg_match('/^[A-Z0-9](?:[A-Z0-9. \-]*[A-Z0-9.\-])?$/',$v);}
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
   $data=[self::normNik($in('nik')),$in('name'),$in('section'),$in('department'),$in('division'),$in('email'),in_array($_POST['status']??'',['ACTIVE','INACTIVE'],true)?$_POST['status']:'ACTIVE'];
   // Gagal: tampilkan pesan & kembalikan isian form supaya tidak perlu mengetik ulang.
   $fail=function(string $msg) use($back):never{$old=$_POST;unset($old['_csrf']);$_SESSION['_old_employee']=$old;flash('error',$msg);redirect($back);};
   if(!self::validNik($data[0]))$fail('NIK hanya boleh berisi huruf, angka, spasi, titik, atau tanda hubung (maksimal 20 karakter).');
   if($data[1]==='')$fail('Nama wajib diisi.');
   if(!self::validName($data[1]))$fail('Nama hanya boleh berisi huruf, spasi, titik, koma, tanda petik, tanda hubung, atau tanda kurung (maksimal 50 karakter).');
   if(!filter_var($data[5],FILTER_VALIDATE_EMAIL))$fail('Format email tidak valid.');
   if($data[4]===''||$data[3]===''||$data[2]==='')$fail('Divisi, departemen, dan section wajib diisi.');
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
  $old=$_SESSION['_old_employee']??[];unset($_SESSION['_old_employee']);$org=self::orgTree($db);
  require dirname(__DIR__,2).'/views/employees/form.php';
 }
 public static function delete(int $id):void{Auth::requireRole(['ADMIN']);verify_csrf();$db=Database::connection();$db->prepare("DELETE FROM employees WHERE id=?")->execute([$id]);Audit::log('DELETE','EMPLOYEE',$id);redirect('/employees');}
 public static function bulkDelete():void{
  Auth::requireRole(['ADMIN']);verify_csrf();$ids=array_filter(array_map('intval',$_POST['employee_ids']??[]));
  if($ids){$db=Database::connection();$in=implode(',',array_fill(0,count($ids),'?'));$db->prepare("DELETE FROM employees WHERE id IN ($in)")->execute($ids);Audit::log('DELETE','EMPLOYEE',null,'Bulk delete: '.implode(',',$ids));flash('success',count($ids).' employee dihapus.');}
  redirect('/employees');
 }
 // Template import: cek NIK (maks. 20 karakter), nama, email, dan dropdown Status. Divisi/Departemen/Section diisi sesuai data kantor.
 public static function importTemplate():void{
  Auth::requireRole(['ADMIN']);
  $DV=\PhpOffice\PhpSpreadsheet\Cell\DataValidation::class;
  $headers=['nik','name','email','division','department','section','status'];
  $rows=[
   ['2205049','Contoh Nama','contoh.nama@example.com','MD','CCC','CCC','ACTIVE'],
   ['INT-2610285','Contoh Internship','contoh.intern@example.com','HRD & GA','HRD & GA','HRD','ACTIVE'],
  ];
  [$ss,$sheet,$headerRow,$lastCol]=\App\Services\ExcelExport::newSheet('Template Import Karyawan','Isi mulai baris 5 (hapus 2 baris contoh). Bisa juga langsung upload Excel data karyawan kantor (kolom NIK, Nama, Divisi, Dept, Section, Email).',$headers);
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

  $mk=function(string $type,string $formula,string $title,string $err,string $prompt,string $style=\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP) use ($DV){
   $v=new \PhpOffice\PhpSpreadsheet\Cell\DataValidation();
   $v->setType($type);$v->setErrorStyle($style);$v->setAllowBlank(true);
   $v->setShowErrorMessage(true);$v->setShowInputMessage(true);$v->setShowDropDown(true);
   $v->setErrorTitle($title);$v->setError($err);$v->setPromptTitle($title);$v->setPrompt($prompt);
   $v->setFormula1($formula);return $v;
  };
  // NIK: 1–20 karakter (huruf, angka, spasi, titik, tanda hubung)
  $sheet->setDataValidation("A$first:A$last",$mk($DV::TYPE_CUSTOM,
   "AND(LEN(TRIM(A$first))>=1,LEN(A$first)<=20)",
   'NIK tidak valid','NIK maksimal 20 karakter.','Huruf, angka, spasi, titik, atau tanda hubung. Contoh: 2205049, BAA-2409065, DRV.01, INT-NEW 001'));
  $nameDv=$mk($DV::TYPE_TEXTLENGTH,'50','Nama tidak valid','Nama maksimal 50 karakter.','Nama lengkap karyawan, maksimal 50 karakter.');$nameDv->setOperator($DV::OPERATOR_LESSTHANOREQUAL);
  $sheet->setDataValidation("B$first:B$last",$nameDv);
  $sheet->setDataValidation("C$first:C$last",$mk($DV::TYPE_CUSTOM,
   "AND(ISNUMBER(FIND(\"@\",C$first)),ISNUMBER(FIND(\".\",MID(C$first,FIND(\"@\",C$first),200))),ISERROR(FIND(\" \",C$first)))",
   'Email tidak valid','Format email tidak valid. Contoh: nama@usc-indonesia.co.id (tanpa spasi).','Email aktif untuk menerima undangan. Contoh: nama@usc-indonesia.co.id'));
  $sheet->setDataValidation("G$first:G$last",$mk($DV::TYPE_LIST,'"ACTIVE,INACTIVE"','Status tidak valid','Pilih ACTIVE atau INACTIVE.','ACTIVE = aktif, INACTIVE = nonaktif.'));
  \App\Services\ExcelExport::outputXlsx($ss,'template_import_karyawan.xlsx');
 }
 public static function import():void{
  Auth::requireRole(['ADMIN']);if($_SERVER['REQUEST_METHOD']!=='POST'){require dirname(__DIR__,2).'/views/employees/import.php';return;}
  verify_csrf();if(empty($_FILES['file']['tmp_name'])){flash('error','File wajib dipilih.');redirect('/employees/import');}
  $ext=strtolower(pathinfo($_FILES['file']['name'],PATHINFO_EXTENSION));if(!in_array($ext,['csv','xlsx'],true)){flash('error','Format harus CSV/XLSX.');redirect('/employees/import');}
  $rows=[]; // [nomor baris di file => data per nama kolom]
  // Judul kolom yang dikenali (template sistem maupun Excel data karyawan kantor).
  $alias=['nik'=>'nik','no induk'=>'nik','name'=>'name','nama'=>'name','nama karyawan'=>'name','email'=>'email','e-mail'=>'email','division'=>'division','divisi'=>'division','department'=>'department','departemen'=>'department','dept'=>'department','section'=>'section','seksi'=>'section','status'=>'status'];
  $norm=fn($v)=>$alias[strtolower(trim((string)$v))]??'';
  if($ext==='csv'){
   $h=fopen($_FILES['file']['tmp_name'],'r');$headers=array_map($norm,fgetcsv($h)?:[]);$line=1;
   while(($r=fgetcsv($h))!==false){$line++;if(count(array_filter($r,fn($v)=>trim((string)$v)!==''))===0)continue;$rec=[];foreach($headers as $col=>$name)if($name!==''&&(($rec[$name]??'')===''))$rec[$name]=$r[$col]??'';$rows[$line]=$rec;}
   fclose($h);
  }else{
   $reader=\PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($_FILES['file']['tmp_name']);$reader->setReadDataOnly(true);
   $sheet=$reader->load($_FILES['file']['tmp_name'])->getSheet(0);
   $data=$sheet->toArray(null,true,false,true); // kunci = nomor baris asli di Excel
   $headers=null;
   foreach($data as $rowNo=>$row){
    $normalized=array_map($norm,$row);
    if($headers===null){if(in_array('nik',$normalized,true))$headers=$normalized;continue;}
    if(count(array_filter($row,fn($v)=>trim((string)$v)!==''))===0)continue;
    $rec=[];foreach($headers as $col=>$name)if($name!==''&&trim((string)($row[$col]??''))!=='')$rec[$name]=$row[$col];
    $rows[$rowNo]=$rec;
   }
   if($headers===null){flash('error','Kolom header tidak ditemukan. Gunakan template import (harus ada kolom "nik").');redirect('/employees/import');}
  }
  $db=Database::connection();$ok=0;$failed=0;$skipped=0;$reasons=[];$seen=[];$db->beginTransaction();
  try{$s=$db->prepare("INSERT INTO employees(nik,name,section,department,division,email,status) VALUES(?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE name=VALUES(name),section=VALUES(section),department=VALUES(department),division=VALUES(division),email=VALUES(email),status=VALUES(status),updated_at=NOW()");
   foreach($rows as $rowNum=>$r){
    $v=fn($k)=>trim((string)($r[$k]??''));
    $nik=self::normNik($v('nik'));$name=$v('name');$email=$v('email');$division=$v('division');$department=$v('department');$section=$v('section');$status=strtoupper($v('status'))?:'ACTIVE';
    if(preg_match('/^\d+\.0+$/',$nik))$nik=substr($nik,0,strpos($nik,'.')); // angka dari Excel mis. "2205049.0"
    if($nik===''&&$name===''&&$email===''){$skipped++;continue;} // baris kosong / slot cadangan tanpa nama
    $err=null;
    if($nik===''||$name===''||$email==='')$err='NIK, nama, dan email wajib diisi';
    elseif(!self::validNik($nik))$err='NIK "'.$nik.'" tidak valid (hanya huruf, angka, spasi, titik, tanda hubung, maksimal 20 karakter)';
    elseif(isset($seen[$nik]))$err='NIK '.$nik.' ganda di file (sudah ada di baris '.$seen[$nik].')';
    elseif(!self::validName($name))$err='nama "'.$name.'" tidak valid (hanya huruf dan tanda baca nama, maksimal 50 karakter)';
    elseif(!filter_var($email,FILTER_VALIDATE_EMAIL))$err='email "'.$email.'" tidak valid';
    elseif($division===''||$department===''||$section==='')$err='Divisi, departemen, dan section wajib diisi';
    elseif(!in_array($status,['ACTIVE','INACTIVE'],true))$err='Status "'.$status.'" tidak valid (ACTIVE/INACTIVE)';
    if($err){$failed++;if(count($reasons)<5)$reasons[]="Baris $rowNum: ".e($err);continue;}
    $seen[$nik]=$rowNum;
    $s->execute([$nik,$name,$section,$department,$division,$email,$status]);$ok++;
   }
   $db->commit();Audit::log('IMPORT','EMPLOYEE',null,"Imported $ok rows, failed $failed");
   $msg="Import selesai: $ok berhasil, $failed gagal".($skipped?", $skipped baris kosong dilewati":'').'.';if($reasons)$msg.=' Contoh gagal: '.implode('; ',$reasons).($failed>count($reasons)?'; ...':'');
   flash($failed?'error':'success',$msg);
  }catch(\Throwable $e){$db->rollBack();flash('error','Import gagal: '.e($e->getMessage()));} redirect('/employees');
 }
}
