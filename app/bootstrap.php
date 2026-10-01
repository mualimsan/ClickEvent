<?php
declare(strict_types=1);
require_once __DIR__.'/Config/config.php';
require_once __DIR__.'/Support/helpers.php';
date_default_timezone_set('Asia/Jakarta');
if(session_status()!==PHP_SESSION_ACTIVE){
 $secure=filter_var(env('SESSION_SECURE_COOKIE',false),FILTER_VALIDATE_BOOL);
 session_set_cookie_params(['lifetime'=>0,'path'=>'/','secure'=>$secure,'httponly'=>true,'samesite'=>env('SESSION_SAME_SITE','Lax')]);
 session_start();
}
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: camera=(self)');
if(env('APP_ENV','production')==='production') header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
// Alamat dasar aplikasi: '' bila folder public/ adalah root website (mis. invitations.test),
// atau mis. '/invitations/public' bila aplikasi dibuka dari subfolder htdocs (XAMPP). Dipakai untuk semua link & redirect.
if(!defined('BASE_URL')){
 $__base=PHP_SAPI==='cli'?'':str_replace('\\','/',dirname($_SERVER['SCRIPT_NAME']??''));
 define('BASE_URL',($__base==='/'||$__base==='.'||$__base==='')?'':rtrim($__base,'/'));
}
