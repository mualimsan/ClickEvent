<?php
namespace App\Services;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
final class QrService {
 public static function token():string{return bin2hex(random_bytes(32));}
 public static function hash(string $token):string{return hash('sha256',$token);}
 public static function pngBytes(string $token):string{
  return (new Builder(writer:new PngWriter(),data:$token,size:320,margin:10))->build()->getString();
 }
 public static function pngDataUri(string $token):string{
  return 'data:image/png;base64,'.base64_encode(self::pngBytes($token));
 }
}
