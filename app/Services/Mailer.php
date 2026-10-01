<?php
namespace App\Services;
use PHPMailer\PHPMailer\PHPMailer;
final class Mailer {
 // Koneksi SMTP yang bisa dipakai ulang untuk banyak email (hemat ±1–2 detik handshake per email).
 public static function open():PHPMailer{
  $mail=new PHPMailer(true);$mail->CharSet=PHPMailer::CHARSET_UTF8;$mail->Encoding='base64';$mail->isSMTP();$mail->Host=env('MAIL_HOST');$mail->Port=(int)env('MAIL_PORT',587);
  $mail->SMTPAuth=true;$mail->Username=env('MAIL_USERNAME');$mail->Password=env('MAIL_PASSWORD');
  $mail->SMTPSecure=env('MAIL_ENCRYPTION','tls')==='ssl'?PHPMailer::ENCRYPTION_SMTPS:PHPMailer::ENCRYPTION_STARTTLS;
  $mail->SMTPKeepAlive=true;$mail->Timeout=20;
  // Gmail mendukung AUTH PLAIN: 1 kali bolak-balik (LOGIN butuh 3), login lebih cepat. Bisa diatur lewat MAIL_AUTH_TYPE.
  $auth=env('MAIL_AUTH_TYPE',str_contains((string)env('MAIL_HOST'),'gmail.com')?'PLAIN':'');if($auth)$mail->AuthType=$auth;
  $mail->setFrom(env('MAIL_FROM_ADDRESS'),env('MAIL_FROM_NAME','Event Committee'));
  return $mail;
 }
 // Tutup koneksi tanpa menunggu balasan QUIT dari server (hemat satu kali bolak-balik).
 public static function close(PHPMailer $mail):void{try{$smtp=$mail->getSMTPInstance();if($smtp->connected())$smtp->client_send("QUIT\r\n",'QUIT');$smtp->close();}catch(\Throwable $e){}}
 public static function sendInvitation(array $employee,array $event,string $token,?PHPMailer $shared=null):void{
  $mail=$shared??self::open();
  $mail->clearAddresses();$mail->clearAttachments();
  try{self::compose($mail,$employee,$event,$token);$mail->send();}
  catch(\Throwable $e){if($shared)$mail->getSMTPInstance()->reset();throw $e;}
  finally{if(!$shared)self::close($mail);}
 }
 private static function compose(PHPMailer $mail,array $employee,array $event,string $token):void{
  $mail->addAddress($employee['email'],$employee['name']);$mail->isHTML(true);
  $mail->Subject='Undangan - '.$event['event_name'];
  $qrBytes=QrService::pngBytes($token);
  $mail->addStringEmbeddedImage($qrBytes,'qrcode','qr.png','base64','image/png');
  $mail->Body=self::template($employee,$event);
  $mail->AltBody="Undangan: {$event['event_name']}\nTanggal: ".dmy($event['event_date'])."\nJam: ".self::hm($event['start_time']).' - '.self::hm($event['end_time']).' '.self::tz($event)."\nLokasi: {$event['location']}\n\nQR Code terlampir, mohon tunjukkan saat check-in dan pengambilan souvenir.";
  $mail->addStringAttachment($qrBytes,'QR-'.preg_replace('/[^A-Za-z0-9_-]+/','_',$event['event_name']).'-'.preg_replace('/[^A-Za-z0-9_-]+/','_',$employee['name']).'.png','base64','image/png');
 }
 private static function hm(?string $v):string{return $v?substr($v,0,5):'-';}
 private static function tz(array $event):string{return in_array($event['timezone']??'',['WIB','WITA','WIT'],true)?$event['timezone']:'WIB';}
 private static function longDate(?string $v):string{
  if(!$v)return '';
  $bulan=['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'];
  [$y,$m,$d]=explode('-',$v);
  return ((int)$d).' '.($bulan[$m]??$m).' '.$y;
 }
 private static function template(array $employee,array $event):string{
  $name=htmlspecialchars($employee['name']);
  $eventName=htmlspecialchars($event['event_name']);
  $date=htmlspecialchars(self::longDate($event['event_date']));
  $time=htmlspecialchars(self::hm($event['start_time']).'–'.self::hm($event['end_time']).' '.self::tz($event));
  $location=htmlspecialchars((string)$event['location']);
  return <<<HTML
<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="x-apple-disable-message-reformatting">
<!--[if mso]><style>body,table,td,p,div,span,strong{font-family:'Segoe UI',Arial,sans-serif !important;}</style><![endif]-->
</head>
<body style="margin:0;padding:0;background:#f2f4f3;font-family:'Segoe UI',Arial,Helvetica,sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#f2f4f3" style="background:#f2f4f3;">
<tr><td align="center" style="padding:32px 16px;">
<table role="presentation" width="560" align="center" cellpadding="0" cellspacing="0" border="0" bgcolor="#ffffff" style="max-width:560px;width:100%;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,0.08);">
<tr><td bgcolor="#022d36" style="background:#022d36;padding:24px 28px;border-bottom:4px solid #90e365;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr>
<td width="44" valign="top" style="padding-right:14px;">
<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="44" bgcolor="#ffffff" style="background:#ffffff;border-radius:10px;width:44px;height:44px;"><tr><td align="center" valign="middle" height="44" style="width:44px;height:44px;font-family:'Segoe UI',Arial,Helvetica,sans-serif;font-size:13px;font-weight:800;letter-spacing:0.5px;color:#167320;">USC</td></tr></table>
</td>
<td style="color:#ffffff;" valign="middle">
<div style="font-size:12px;letter-spacing:1px;text-transform:uppercase;color:#c6ff7f;">Undangan Resmi</div>
<div style="font-size:20px;font-weight:700;line-height:1.3;">{$eventName}</div>
</td>
</tr></table>
</td></tr>
<tr><td style="padding:28px;">
<p style="margin:0 0 16px;font-size:15px;color:#1f2937;">Yth. <strong>{$name}</strong>,</p>
<p style="margin:0 0 20px;font-size:14px;color:#4b5563;line-height:1.7;">Kami mengundang Anda untuk menghadiri <strong>{$eventName}</strong>, dengan jadwal sebagai berikut:</p>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#f3f9ef" style="background:#f3f9ef;border-radius:12px;border:1px solid #daf6ca;">
<tr>
<td style="padding:16px 20px;font-size:13px;color:#6b7280;width:36%;">Tanggal</td>
<td style="padding:16px 20px 16px 0;font-size:14px;color:#111827;font-weight:600;">{$date}</td>
</tr>
<tr>
<td style="padding:0 20px 16px;font-size:13px;color:#6b7280;">Waktu</td>
<td style="padding:0 20px 16px 0;font-size:14px;color:#111827;font-weight:600;">{$time}</td>
</tr>
<tr>
<td style="padding:0 20px 20px;font-size:13px;color:#6b7280;">Lokasi</td>
<td style="padding:0 20px 20px 0;font-size:14px;color:#111827;font-weight:600;">{$location}</td>
</tr>
</table>
<p style="margin:20px 0 16px;font-size:14px;color:#4b5563;line-height:1.7;">Mohon simpan email ini dan tunjukkan QR Code di bawah pada saat <strong>check-in</strong> serta <strong>pengambilan souvenir</strong> di lokasi acara.</p>
<p style="margin:0 0 4px;font-size:14px;color:#4b5563;line-height:1.7;">Kami menantikan kehadiran Anda.</p>
<p style="margin:0;font-size:14px;color:#4b5563;line-height:1.7;">Terima kasih atas perhatian dan partisipasinya.</p>
</td></tr>
<tr><td style="padding:0 28px;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr>
<td height="1" style="border-top:2px dashed #d1d9d4;font-size:1px;line-height:1px;mso-line-height-rule:exactly;">&nbsp;</td>
</tr></table>
</td></tr>
<tr><td style="padding:24px 28px 8px;text-align:center;">
<p style="margin:0 0 4px;font-size:12px;color:#6b7280;text-transform:uppercase;letter-spacing:1px;font-weight:700;">E-Ticket / QR Masuk</p>
<table role="presentation" align="center" cellpadding="0" cellspacing="0" border="0" style="margin:0 auto 16px;"><tr><td bgcolor="#fff8e1" style="background:#fff8e1;border:1px solid #fde8a8;border-radius:20px;padding:6px 14px;font-size:13px;color:#7a5c00;">Berlaku untuk <strong>Check-in</strong> &amp; <strong>Pengambilan Souvenir</strong></td></tr></table>
<table role="presentation" align="center" cellpadding="0" cellspacing="0" border="0" bgcolor="#ffffff" style="margin:0 auto;background:#ffffff;border:1px solid #e3ede6;border-radius:16px;">
<tr><td style="padding:16px;">
<img src="cid:qrcode" alt="QR Code" width="200" height="200" style="display:block;border:0;outline:none;text-decoration:none;-ms-interpolation-mode:bicubic;">
</td></tr>
</table>
<p style="margin:16px 0 0;font-size:13px;color:#374151;font-weight:600;">{$name}</p>
<p style="margin:4px 0 0;font-size:12px;color:#9ca3af;">QR ini juga terlampir sebagai file gambar pada email ini.</p>
</td></tr>
<tr><td bgcolor="#f9fafb" style="padding:24px 28px;background:#f9fafb;border-top:1px solid #eef0f0;">
<p style="margin:0 0 12px;font-size:11px;font-weight:700;color:#6b7280;text-align:center;text-transform:uppercase;letter-spacing:0.5px;">PT United Steel Center Indonesia</p>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr>
<td valign="top" width="50%" style="padding:0 10px 0 0;font-size:11px;color:#9ca3af;line-height:1.6;">
<strong style="color:#6b7280;">Karawang Plant (Head Office)</strong><br>
Mitra Karawang Industry Estate (KIM), Jl. Mitra Raya Selatan II Blok F No.1, Parungmulya, Ciampel, Karawang.<br>
Telp: (62-267) 440701-09 &nbsp;|&nbsp; Fax: (62-267) 440130
</td>
<td valign="top" width="50%" style="padding:0 0 0 10px;font-size:11px;color:#9ca3af;line-height:1.6;border-left:1px solid #eef0f0;">
<strong style="color:#6b7280;">&nbsp;Cibitung Plant</strong><br>
&nbsp;Kawasan Industri MM2100, Jl. Jawa Blok H-8, Ganda Mekar, Cikarang Barat.<br>
&nbsp;Telp: (62-21) 8980771
</td>
</tr></table>
<p style="margin:16px 0 0;font-size:11px;color:#c1c7cd;text-align:center;">Email ini dikirim otomatis oleh sistem Event Attendance. Mohon tidak membalas email ini.</p>
</td></tr>
</table>
</td></tr>
</table>
</body>
</html>
HTML;
 }
}
