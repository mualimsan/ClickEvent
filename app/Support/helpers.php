<?php
function e(mixed $v):string{return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');}
function url(string $path):string{return (defined('BASE_URL')?BASE_URL:'').$path;}
function redirect(string $url):never{if($url!==''&&$url[0]==='/'&&!str_starts_with($url,'//'))$url=url($url);header('Location: '.$url);exit;}
function csrf_token():string{if(empty($_SESSION['_csrf']))$_SESSION['_csrf']=bin2hex(random_bytes(32));return $_SESSION['_csrf'];}
function csrf_field():string{return '<input type="hidden" name="_csrf" value="'.e(csrf_token()).'">';}
function verify_csrf():void{if(!hash_equals($_SESSION['_csrf']??'',$_POST['_csrf']??'')){http_response_code(419);exit('Invalid CSRF token');}}
function flash(string $key,?string $value=null):?string{if($value!==null){$_SESSION['_flash'][$key]=$value;return null;}$v=$_SESSION['_flash'][$key]??null;unset($_SESSION['_flash'][$key]);return $v;}
function client_ip():string{return $_SERVER['REMOTE_ADDR']??'unknown';}
function dmy(?string $v):string{if(!$v)return '';$ts=strtotime($v);if(!$ts)return $v;return str_contains($v,':')?date('d-m-Y H:i:s',$ts):date('d-m-Y',$ts);}
function next_sequence_code(\PDO $db,string $table,string $column,string $prefix):string{
 $ym=date('ym');$like=$prefix.'-'.$ym.'-%';
 $stmt=$db->prepare("SELECT $column FROM $table WHERE $column LIKE ? ORDER BY $column DESC LIMIT 1");
 $stmt->execute([$like]);$last=$stmt->fetchColumn();
 $next=1;if($last && preg_match('/-(\d{3})$/',$last,$m))$next=(int)$m[1]+1;
 if($next>999)throw new \RuntimeException("Batas maksimal 999 $prefix untuk bulan ini sudah tercapai.");
 return $prefix.'-'.$ym.'-'.str_pad((string)$next,3,'0',STR_PAD_LEFT);
}
