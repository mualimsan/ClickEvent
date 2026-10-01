<?php
namespace App;
final class Auth {
 public static function user():?array{return $_SESSION['user']??null;}
 public static function check():bool{return self::user()!==null;}
 // Username: 3–30 karakter huruf kecil, angka, titik, garis bawah, atau strip.
 public static function validUsername(string $v):bool{return (bool)preg_match('/^[a-z0-9._-]{3,30}$/',$v);}
 // $login boleh berisi email (mengandung @) atau username.
 public static function attempt(string $login,string $password):bool{
  $db=Database::connection();$login=trim($login);
  if($login==='')return false;
  $field=str_contains($login,'@')?'email':'username';
  try{$s=$db->prepare("SELECT * FROM users WHERE $field=? LIMIT 1");$s->execute([$field==='username'?strtolower($login):$login]);$u=$s->fetch();}
  catch(\PDOException $e){if($field==='email')throw $e;$u=false;} // kolom username belum ada (migrasi belum dijalankan): tetap bisa login pakai email
  if(!$u||$u['status']!=='ACTIVE')return false;
  if($u['locked_until']&&strtotime($u['locked_until'])>time())return false;
  if(password_verify($password,$u['password_hash'])){
   $db->prepare("UPDATE users SET failed_login_count=0,locked_until=NULL,last_login_at=NOW() WHERE id=?")->execute([$u['id']]);
   session_regenerate_id(true);$_SESSION['user']=$u;return true;
  }
  $count=(int)$u['failed_login_count']+1;$max=(int)env('LOGIN_MAX_ATTEMPTS',5);
  if($count>=$max)$db->prepare("UPDATE users SET failed_login_count=0,locked_until=DATE_ADD(NOW(),INTERVAL ? MINUTE) WHERE id=?")->execute([(int)env('LOGIN_LOCK_MINUTES',15),$u['id']]);
  else $db->prepare("UPDATE users SET failed_login_count=? WHERE id=?")->execute([$count,$u['id']]);
  return false;
 }
 public static function logout():void{$_SESSION=[];if(ini_get('session.use_cookies')){ $p=session_get_cookie_params();setcookie(session_name(),'',['expires'=>time()-42000,'path'=>$p['path'],'domain'=>$p['domain'],'secure'=>$p['secure'],'httponly'=>$p['httponly'],'samesite'=>$p['samesite']??'Lax']);}session_destroy();}
 public static function isAdmin():bool{return (self::user()['role']??null)==='ADMIN';}
 // Halaman awal setelah login: admin ke dashboard, operator langsung ke scanner.
 public static function home():string{return ['ADMIN'=>'/dashboard','EVENT_OPERATOR'=>'/scanner/attendance'][self::user()['role']??'']??'/reports/attendance';}
 public static function roleLabel(?string $role):string{return ['ADMIN'=>'Admin','EVENT_OPERATOR'=>'Operator','VIEWER'=>'Viewer'][$role]??(string)$role;}
 public static function requireLogin():void{
  if(!self::check())redirect('/login');
  // Muat ulang data akun tiap request, supaya perubahan role/status dari admin langsung berlaku.
  static $fresh=false;if($fresh)return;$fresh=true;
  $s=Database::connection()->prepare("SELECT * FROM users WHERE id=?");$s->execute([self::user()['id']]);$u=$s->fetch();
  if(!$u||$u['status']!=='ACTIVE'){unset($_SESSION['user']);session_regenerate_id(true);flash('error','Akun Anda sudah dinonaktifkan. Hubungi administrator.');redirect('/login');}
  $_SESSION['user']=$u;
 }
 public static function requireRole(array $roles):void{
  self::requireLogin();
  if(in_array(self::user()['role'],$roles,true))return;
  $isPage=$_SERVER['REQUEST_METHOD']==='GET'&&!str_starts_with((string)parse_url($_SERVER['REQUEST_URI']??'',PHP_URL_PATH),'/api/')&&!str_contains((string)($_SERVER['HTTP_ACCEPT']??''),'application/json');
  if($isPage){flash('error','Anda tidak memiliki akses ke halaman tersebut.');redirect(self::home());}
  http_response_code(403);exit('Forbidden');
 }
}
