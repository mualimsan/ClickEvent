<?php
namespace App\Controllers;
use App\Auth;use App\Database;
// Sidik jari perubahan data per bagian. Browser membandingkannya tiap beberapa detik
// dan hanya memuat ulang bagian yang berubah (tanpa WebSocket, cocok untuk XAMPP biasa).
final class LiveController {
 public static function version():void{
  Auth::requireLogin();
  session_write_close(); // jangan mengunci session selama polling
  header('Content-Type: application/json');header('Cache-Control: no-store');
  $r=Database::connection()->query("SELECT
   CONCAT((SELECT COUNT(*) FROM attendances),'-',COALESCE((SELECT MAX(id) FROM attendances),0)) att,
   CONCAT((SELECT COUNT(*) FROM souvenir_transactions),'-',COALESCE((SELECT MAX(id) FROM souvenir_transactions),0)) souv,
   CONCAT((SELECT COUNT(*) FROM invitations),'-',COALESCE((SELECT MAX(COALESCE(updated_at,created_at)) FROM invitations),''),'-',(SELECT COALESCE(SUM(email_status='SENT'),0) FROM invitations)) inv,
   CONCAT((SELECT COUNT(*) FROM events),'-',COALESCE((SELECT MAX(COALESCE(updated_at,created_at)) FROM events),'')) evt,
   CONCAT((SELECT COUNT(*) FROM employees),'-',COALESCE((SELECT MAX(COALESCE(updated_at,created_at)) FROM employees),'')) emp,
   CONCAT((SELECT COUNT(*) FROM souvenirs),'-',COALESCE((SELECT MAX(COALESCE(updated_at,created_at)) FROM souvenirs),''),'-',COALESCE((SELECT SUM(stock) FROM souvenirs),0)) stock,
   CONCAT((SELECT COUNT(*) FROM event_souvenirs),'-',COALESCE((SELECT SUM(quantity_allocated) FROM event_souvenirs),0)) alloc,
   CONCAT((SELECT COUNT(*) FROM users),'-',COALESCE((SELECT MAX(COALESCE(updated_at,created_at)) FROM users),''),'-',(SELECT COALESCE(SUM(status='ACTIVE'),0) FROM users)) users")->fetch(\PDO::FETCH_ASSOC);
  echo json_encode(array_map(fn($v)=>substr(md5((string)$v),0,10),$r));
 }
}
