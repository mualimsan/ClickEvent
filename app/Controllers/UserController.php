<?php
namespace App\Controllers;
use App\Auth;use App\Database;use App\Support\Audit;
final class UserController {
 private const ROLES=['EVENT_OPERATOR','ADMIN'];
 public static function index():void{
  Auth::requireRole(['ADMIN']);$db=Database::connection();$q=trim($_GET['q']??'');
  $sql="SELECT id,name,username,email,role,status,last_login_at,created_at FROM users WHERE 1=1";$p=[];
  if($q!==''){$sql.=" AND (name LIKE ? OR username LIKE ? OR email LIKE ?)";$x="%$q%";array_push($p,$x,$x,$x);}
  $sql.=" ORDER BY role='ADMIN' DESC,name";
  $s=$db->prepare($sql);$s->execute($p);$users=$s->fetchAll();
  require dirname(__DIR__,2).'/views/users/index.php';
 }
 public static function form(?int $id=null):void{
  Auth::requireRole(['ADMIN']);$db=Database::connection();$usr=null;
  if($id){$s=$db->prepare("SELECT * FROM users WHERE id=?");$s->execute([$id]);$usr=$s->fetch();if(!$usr){flash('error','Pengguna tidak ditemukan.');redirect('/users');}}
  $isSelf=$usr&&(int)$usr['id']===(int)Auth::user()['id'];
  if($_SERVER['REQUEST_METHOD']==='POST'){verify_csrf();
   $back=$id?'/users/'.$id.'/edit':'/users/create';
   $name=trim($_POST['name']??'');$email=trim($_POST['email']??'');$username=strtolower(trim($_POST['username']??''));
   $role=$_POST['role']??'';$status=$_POST['status']??'ACTIVE';
   $password=$_POST['password']??'';$confirm=$_POST['password_confirm']??'';
   // Admin tidak boleh menurunkan role atau menonaktifkan akunnya sendiri, supaya tidak terkunci dari sistem.
   if($isSelf){$role=$usr['role'];$status=$usr['status'];}
   if($name===''){flash('error','Nama wajib diisi.');redirect($back);}
   if(!Auth::validUsername($username)){flash('error','Username wajib 3–30 karakter: huruf kecil, angka, titik, garis bawah, atau strip (tanpa spasi).');redirect($back);}
   if(!filter_var($email,FILTER_VALIDATE_EMAIL)){flash('error','Format email tidak valid.');redirect($back);}
   $chk=$db->prepare("SELECT id FROM users WHERE username=? AND id!=?");$chk->execute([$username,$id??0]);
   if($chk->fetch()){flash('error','Username "'.e($username).'" sudah dipakai akun lain.');redirect($back);}
   if(!in_array($role,self::ROLES,true)){flash('error','Role tidak valid.');redirect($back);}
   if(!in_array($status,['ACTIVE','INACTIVE'],true)){flash('error','Status tidak valid.');redirect($back);}
   $chk=$db->prepare("SELECT id FROM users WHERE email=? AND id!=?");$chk->execute([$email,$id??0]);
   if($chk->fetch()){flash('error','Email sudah digunakan oleh akun lain.');redirect($back);}
   if(!$id&&$password===''){flash('error','Kata sandi wajib diisi.');redirect($back);}
   if($password!==''){
    if(strlen($password)<6){flash('error','Kata sandi minimal 6 karakter.');redirect($back);}
    if($password!==$confirm){flash('error','Konfirmasi kata sandi tidak cocok.');redirect($back);}
   }
   if($id){
    $db->prepare("UPDATE users SET name=?,username=?,email=?,role=?,status=?,updated_at=NOW() WHERE id=?")->execute([$name,$username,$email,$role,$status,$id]);
    if($password!=='')$db->prepare("UPDATE users SET password_hash=?,failed_login_count=0,locked_until=NULL WHERE id=?")->execute([password_hash($password,PASSWORD_DEFAULT),$id]);
    Audit::log('UPDATE','USER',$id,$email.' ('.$role.', '.$status.')'.($password!==''?' + reset password':''));
    flash('success','Pengguna '.e($name).' berhasil diperbarui.');
   }else{
    $db->prepare("INSERT INTO users(name,username,email,password_hash,role,status) VALUES(?,?,?,?,?,?)")->execute([$name,$username,$email,password_hash($password,PASSWORD_DEFAULT),$role,$status]);
    $id=(int)$db->lastInsertId();
    Audit::log('CREATE','USER',$id,$email.' ('.$role.')');
    flash('success','Pengguna '.e($name).' berhasil ditambahkan.');
   }
   redirect('/users');
  }
  require dirname(__DIR__,2).'/views/users/form.php';
 }
 public static function delete(int $id):void{
  Auth::requireRole(['ADMIN']);verify_csrf();$db=Database::connection();
  if($id===(int)Auth::user()['id']){flash('error','Anda tidak bisa menghapus akun Anda sendiri.');redirect('/users');}
  $s=$db->prepare("SELECT name,email,role FROM users WHERE id=?");$s->execute([$id]);$usr=$s->fetch();
  if(!$usr){redirect('/users');}
  if($usr['role']==='ADMIN'){
   $n=(int)$db->query("SELECT COUNT(*) FROM users WHERE role='ADMIN' AND status='ACTIVE'")->fetchColumn();
   if($n<=1){flash('error','Admin aktif terakhir tidak bisa dihapus.');redirect('/users');}
  }
  $db->prepare("DELETE FROM users WHERE id=?")->execute([$id]);
  Audit::log('DELETE','USER',$id,$usr['email']);
  flash('success','Pengguna '.e($usr['name']).' dihapus.');
  redirect('/users');
 }
}
