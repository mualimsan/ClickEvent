<?php
namespace App\Controllers;
use App\Auth;use App\Database;use App\Support\Audit;
final class SouvenirController {
 public static function index():void{
  Auth::requireRole(['ADMIN']);$db=Database::connection();$q=trim($_GET['q']??'');
  // taken = jumlah yang sudah diambil peserta; Total Stok = stock (sisa di gudang) + taken.
  $sql="SELECT s.*,COALESCE(t.q,0) taken FROM souvenirs s LEFT JOIN (SELECT souvenir_id,SUM(quantity) q FROM souvenir_transactions GROUP BY souvenir_id) t ON t.souvenir_id=s.id WHERE 1=1";$p=[];
  if($q!==''){$sql.=" AND (s.name LIKE ? OR s.code LIKE ?)";$x="%$q%";array_push($p,$x,$x);}
  $sql.=" ORDER BY s.code ASC";
  $s=$db->prepare($sql);$s->execute($p);$souvenirs=$s->fetchAll();
  require dirname(__DIR__,2).'/views/souvenirs/index.php';
 }
 public static function form(?int $id=null):void{
  Auth::requireRole(['ADMIN']);$db=Database::connection();$souv=null;if($id){$s=$db->prepare("SELECT * FROM souvenirs WHERE id=?");$s->execute([$id]);$souv=$s->fetch();}
  // Admin mengisi Total Stok (jumlah yang dimiliki). Kolom stock di database tetap menyimpan sisa di gudang
  // (dikurangi otomatis saat scan), jadi saat simpan: stock = Total Stok - yang sudah diambil.
  $takenOf=function()use($db,$id):int{if(!$id)return 0;$t=$db->prepare("SELECT COALESCE(SUM(quantity),0) FROM souvenir_transactions WHERE souvenir_id=?");$t->execute([$id]);return (int)$t->fetchColumn();};
  $taken=$takenOf();
  if($_SERVER['REQUEST_METHOD']==='POST'){verify_csrf();
   if(trim($_POST['name']??'')===''){flash('error','Nama wajib diisi.');redirect($id?'/souvenirs/'.$id.'/edit':'/souvenirs/create');}
   if(trim($_POST['stock']??'')===''){flash('error','Stok wajib diisi.');redirect($id?'/souvenirs/'.$id.'/edit':'/souvenirs/create');}
   if(!in_array($_POST['status']??'',['ACTIVE','INACTIVE'],true)){flash('error','Status tidak valid.');redirect($id?'/souvenirs/'.$id.'/edit':'/souvenirs/create');}
   $taken=$takenOf();$total=(int)$_POST['stock'];
   if($total<$taken){flash('error','Total Stok tidak boleh kurang dari jumlah yang sudah diambil peserta ('.$taken.').');redirect($id?'/souvenirs/'.$id.'/edit':'/souvenirs/create');}
   $d=[trim($_POST['name']),$total-$taken,$_POST['status']??'ACTIVE'];
   if($id){$s=$db->prepare("UPDATE souvenirs SET name=?,stock=?,status=?,updated_at=NOW() WHERE id=?");$s->execute([...$d,$id]);Audit::log('UPDATE','SOUVENIR',$id,$d[0]);}
   else{
    try{$code=next_sequence_code($db,'souvenirs','code','SOV');}
    catch(\RuntimeException $e){flash('error',$e->getMessage());redirect('/souvenirs/create');}
    $s=$db->prepare("INSERT INTO souvenirs(code,name,stock,status) VALUES(?,?,?,?)");$s->execute([$code,...$d]);$id=(int)$db->lastInsertId();Audit::log('CREATE','SOUVENIR',$id,$d[0]);
   }
   flash('success','Souvenir <strong>'.e($d[0]).'</strong> berhasil '.($souv?'diperbarui':'ditambahkan').'.');
   redirect('/souvenirs');
  } require dirname(__DIR__,2).'/views/souvenirs/form.php';
 }
 public static function allocate(int $eventId):void{
  Auth::requireRole(['ADMIN']);$db=Database::connection();$event=$db->prepare("SELECT * FROM events WHERE id=?");$event->execute([$eventId]);$event=$event->fetch();
  $souvenirs=$db->query("SELECT * FROM souvenirs WHERE status='ACTIVE' ORDER BY name")->fetchAll();
  if($_SERVER['REQUEST_METHOD']==='POST'){
   verify_csrf();
   $byId=[];foreach($souvenirs as $sv)$byId[$sv['id']]=$sv;
   $elsewhere=self::elsewhereUnclaimed($db,$eventId);
   $errors=[];
   foreach($_POST['qty']??[] as $sid=>$qty){
    $sid=(int)$sid;$qty=max(0,(int)$qty);
    if(!isset($byId[$sid]))continue;
    if($qty<=0)continue;
    $available=(int)$byId[$sid]['stock']-($elsewhere[$sid]??0);
    if($qty>$available){$errors[]=$byId[$sid]['name'].' (diminta '.$qty.', tersedia '.max($available,0).' setelah dikurangi alokasi event lain dari sisa stok '.$byId[$sid]['stock'].')';}
   }
   if($errors){flash('error','Alokasi melebihi stock yang tersedia: '.implode(', ',$errors).'.');redirect('/events/'.$eventId.'/souvenirs');}
   foreach($_POST['qty']??[] as $sid=>$qty){
    $sid=(int)$sid;$qty=max(0,(int)$qty);
    if(!isset($byId[$sid]))continue;
    $s=$db->prepare("INSERT INTO event_souvenirs(event_id,souvenir_id,quantity_allocated) VALUES(?,?,?) ON DUPLICATE KEY UPDATE quantity_allocated=VALUES(quantity_allocated)");$s->execute([$eventId,$sid,$qty]);
   }
   Audit::log('ALLOCATE_SOUVENIR','EVENT',$eventId);flash('success','Alokasi souvenir berhasil disimpan.');redirect('/events');
  }
  $s=$db->prepare("SELECT * FROM event_souvenirs WHERE event_id=?");$s->execute([$eventId]);$alloc=[];foreach($s->fetchAll() as $r)$alloc[$r['souvenir_id']]=$r['quantity_allocated'];
  $elsewhere=self::elsewhereUnclaimed($db,$eventId);
  require dirname(__DIR__,2).'/views/souvenirs/allocate.php';
 }
 public static function bulkDelete():void{
  Auth::requireRole(['ADMIN']);verify_csrf();$db=Database::connection();
  $ids=array_filter(array_map('intval',$_POST['souvenir_ids']??[]));
  if(!$ids){redirect('/souvenirs');}
  $ok=0;$blocked=[];
  foreach($ids as $id){
   try{
    $s=$db->prepare("SELECT name FROM souvenirs WHERE id=?");$s->execute([$id]);$name=$s->fetchColumn();
    $db->prepare("DELETE FROM souvenirs WHERE id=?")->execute([$id]);
    Audit::log('DELETE','SOUVENIR',$id,$name);$ok++;
   }catch(\PDOException $e){
    if($e->getCode()==='23000')$blocked[]=$name?:('ID '.$id);
    else throw $e;
   }
  }
  $msg=$ok.' souvenir dihapus.';
  if($blocked)$msg.=' '.count($blocked).' souvenir tidak bisa dihapus karena sudah pernah diklaim di riwayat transaksi: '.implode(', ',$blocked).'.';
  flash($blocked?'error':'success',$msg);
  redirect('/souvenirs');
 }
 private static function elsewhereUnclaimed(\PDO $db,int $eventId):array{
  $sql="SELECT es.souvenir_id,SUM(GREATEST(es.quantity_allocated-COALESCE(c.claimed,0),0)) qty
   FROM event_souvenirs es
   JOIN events e ON e.id=es.event_id
   LEFT JOIN (SELECT st.souvenir_id,ii.event_id,SUM(st.quantity) claimed FROM souvenir_transactions st JOIN invitations ii ON ii.id=st.invitation_id GROUP BY st.souvenir_id,ii.event_id) c ON c.souvenir_id=es.souvenir_id AND c.event_id=es.event_id
   WHERE es.event_id!=? AND e.status IN ('DRAFT','PUBLISHED')
   GROUP BY es.souvenir_id";
  $s=$db->prepare($sql);$s->execute([$eventId]);
  $out=[];foreach($s->fetchAll() as $r)$out[(int)$r['souvenir_id']]=(int)$r['qty'];
  return $out;
 }
}
