<?php
namespace App\Controllers;
use App\Auth;use App\Database;use App\Support\Audit;
final class ProfileController {
 public static function index():void{
  Auth::requireLogin();$db=Database::connection();
  $userId=Auth::user()['id'];
  $s=$db->prepare("SELECT * FROM users WHERE id=?");$s->execute([$userId]);$user=$s->fetch();
  if($_SERVER['REQUEST_METHOD']==='POST'){
   verify_csrf();
   $currentPassword=$_POST['current_password']??'';
   if(!password_verify($currentPassword,$user['password_hash'])){flash('error','Kata sandi saat ini salah.');redirect('/profile');}
   $name=trim($_POST['name']??'');
   $email=trim($_POST['email']??'');$username=strtolower(trim($_POST['username']??''));
   if($name===''){flash('error','Nama wajib diisi.');redirect('/profile');}
   if(!Auth::validUsername($username)){flash('error','Username wajib 3–30 karakter: huruf kecil, angka, titik, garis bawah, atau strip (tanpa spasi).');redirect('/profile');}
   $chk=$db->prepare("SELECT id FROM users WHERE username=? AND id!=?");$chk->execute([$username,$userId]);
   if($chk->fetch()){flash('error','Username "'.e($username).'" sudah dipakai akun lain.');redirect('/profile');}
   if(!filter_var($email,FILTER_VALIDATE_EMAIL)){flash('error','Format email tidak valid.');redirect('/profile');}
   $chk=$db->prepare("SELECT id FROM users WHERE email=? AND id!=?");$chk->execute([$email,$userId]);
   if($chk->fetch()){flash('error','Email sudah digunakan oleh akun lain.');redirect('/profile');}
   $newPassword=$_POST['new_password']??'';
   $confirmPassword=$_POST['confirm_password']??'';
   $passwordHash=$user['password_hash'];
   if($newPassword!==''){
    if(strlen($newPassword)<6){flash('error','Kata sandi baru minimal 6 karakter.');redirect('/profile');}
    if($newPassword!==$confirmPassword){flash('error','Konfirmasi kata sandi baru tidak cocok.');redirect('/profile');}
    $passwordHash=password_hash($newPassword,PASSWORD_DEFAULT);
   }
   $db->prepare("UPDATE users SET name=?,username=?,email=?,password_hash=?,updated_at=NOW() WHERE id=?")->execute([$name,$username,$email,$passwordHash,$userId]);
   $s->execute([$userId]);$user=$s->fetch();
   $_SESSION['user']=$user;
   Audit::log('UPDATE','USER',$userId,'Profil diperbarui'.($newPassword!==''?' (termasuk ganti password)':''));
   flash('success','Profil berhasil diperbarui.');
   redirect('/profile');
  }
  require dirname(__DIR__,2).'/views/profile/index.php';
 }
}
