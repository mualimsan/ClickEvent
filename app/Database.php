<?php
namespace App;
use PDO;
final class Database {
 private static ?PDO $pdo=null;
 public static function connection():PDO{
  if(self::$pdo)return self::$pdo;
  $dsn=sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',env('DB_HOST','127.0.0.1'),env('DB_PORT','3306'),env('DB_DATABASE','event_attendance'));
  self::$pdo=new PDO($dsn,env('DB_USERNAME'),env('DB_PASSWORD'),[
   PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
   PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
   PDO::ATTR_EMULATE_PREPARES=>false
  ]);
  return self::$pdo;
 }
}
