<?php
namespace App\Support;
use App\Database;
use App\Auth;
final class Audit {
 public static function log(string $action,?string $type=null,?int $id=null,?string $desc=null):void{
  try{Database::connection()->prepare("INSERT INTO audit_logs(user_id,action,entity_type,entity_id,description,ip_address,user_agent) VALUES(?,?,?,?,?,?,?)")
   ->execute([Auth::user()['id']??null,$action,$type,$id,$desc,$_SERVER['REMOTE_ADDR']??null,substr($_SERVER['HTTP_USER_AGENT']??'',0,500)]);}catch(\Throwable $e){}
 }
}
