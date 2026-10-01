<?php
require dirname(__DIR__).'/vendor/autoload.php';require dirname(__DIR__).'/app/bootstrap.php';
use App\Database;
if($argc<4){exit("Usage: php cli/create_admin.php email name password\n");}
[$_, $email,$name,$password]=$argv;
$db=Database::connection();$s=$db->prepare("INSERT INTO users(name,email,password_hash,role,status) VALUES(?,?,?,'ADMIN','ACTIVE')");$s->execute([$name,$email,password_hash($password,PASSWORD_DEFAULT)]);
echo "Admin created: $email\n";
