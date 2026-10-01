<?php
namespace App\Controllers;
use App\Auth;
final class AuthController {
 public static function login():void{
  if($_SERVER['REQUEST_METHOD']==='POST'){
   // Halaman login mengirim lewat fetch (X-Requested-With) supaya form tetap terlihat & bisa menampilkan status; tanpa JS tetap jalan biasa.
   $ajax=($_SERVER['HTTP_X_REQUESTED_WITH']??'')==='fetch';
   if($ajax&&!hash_equals($_SESSION['_csrf']??'',(string)($_POST['_csrf']??''))){header('Content-Type: application/json');echo json_encode(['ok'=>false,'message'=>'Sesi kedaluwarsa. Muat ulang halaman lalu coba lagi.']);return;}
   verify_csrf();
   $msg='Username/email atau kata sandi salah, atau akun sedang terkunci.';
   if(Auth::attempt((string)($_POST['login']??$_POST['email']??''),$_POST['password']??'')){
    if(Auth::isAdmin())flash('welcome',Auth::user()['name']);
    if($ajax){header('Content-Type: application/json');echo json_encode(['ok'=>true,'redirect'=>url(Auth::home()),'name'=>Auth::user()['name']]);return;}
    redirect(Auth::home());
   }
   if($ajax){header('Content-Type: application/json');echo json_encode(['ok'=>false,'message'=>$msg]);return;}
   flash('error',$msg);
  }
  require dirname(__DIR__,2).'/views/auth/login.php';
 }
 public static function logout():void{Auth::logout();redirect('/login');}
}
