<?php
declare(strict_types=1);
function env(string $key, mixed $default=null): mixed {
 static $loaded=false,$v=[];
 if(!$loaded){
  $p=dirname(__DIR__,2).'/.env';
  if(is_file($p)){
   foreach(file($p,FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES) as $line){
    $line=trim($line);
    if($line===''||str_starts_with($line,'#')||!str_contains($line,'='))continue;
    [$k,$x]=explode('=',$line,2);
    $v[trim($k)]=trim($x," \t\n\r\0\x0B\"");
   }
  }
  $loaded=true;
 }
 return $v[$key]??$_ENV[$key]??$default;
}
