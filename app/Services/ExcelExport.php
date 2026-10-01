<?php
namespace App\Services;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
final class ExcelExport {
 public static function newSheet(string $title,string $subtitle,array $headers):array{
  $ss=new Spreadsheet();$sheet=$ss->getActiveSheet();$sheet->setTitle('Laporan');
  $sheet->setShowGridlines(false);
  $lastColIdx=count($headers);$lastCol=\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($lastColIdx);
  $logoPath=dirname(__DIR__,2).'/public/assets/logo.png';
  $titleCol='A';
  if(is_file($logoPath)){
   $sheet->mergeCells('A1:A2');
   $drawing=new Drawing();$drawing->setName('USC Logo');$drawing->setDescription('United Steel Center Indonesia');$drawing->setPath($logoPath);$drawing->setHeight(52);$drawing->setCoordinates('A1');$drawing->setOffsetX(32);$drawing->setOffsetY(13);$drawing->setWorksheet($sheet);
   $titleCol='B';
  }
  $sheet->mergeCells($titleCol.'1:'.$lastCol.'1');
  $sheet->setCellValue($titleCol.'1','PT UNITED STEEL CENTER INDONESIA — '.$title);
  $sheet->getStyle($titleCol.'1')->getFont()->setBold(true)->setSize(14)->getColor()->setRGB('FFFFFF');
  $sheet->getStyle($titleCol.'1')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setHorizontal(Alignment::HORIZONTAL_LEFT)->setIndent(1);
  $sheet->mergeCells($titleCol.'2:'.$lastCol.'2');
  $sheet->setCellValue($titleCol.'2',$subtitle);
  $sheet->getStyle($titleCol.'2')->getFont()->setItalic(true)->setSize(10)->getColor()->setRGB('DDF4E4');
  $sheet->getStyle($titleCol.'2')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setIndent(1);
  $sheet->getRowDimension(1)->setRowHeight(35);
  $sheet->getRowDimension(2)->setRowHeight(24);
  $sheet->getStyle($titleCol.'1:'.$lastCol.'1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('176B3A');
  $sheet->getStyle($titleCol.'2:'.$lastCol.'2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('239B50');
  if($titleCol==='B'){
   $sheet->getStyle('A1:A2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFFFFF');
   $sheet->getStyle('A1:A2')->getBorders()->getRight()->setBorderStyle(Border::BORDER_MEDIUM)->getColor()->setRGB('D7E6DB');
  }
  $headerRow=4;
  foreach($headers as $i=>$h){
   $col=\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i+1);
   $sheet->setCellValue($col.$headerRow,$h);
  }
  $sheet->getStyle('A'.$headerRow.':'.$lastCol.$headerRow)->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
  $sheet->getStyle('A'.$headerRow.':'.$lastCol.$headerRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('1F7A40');
  $sheet->getStyle('A'.$headerRow.':'.$lastCol.$headerRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
  $sheet->getStyle('A'.$headerRow.':'.$lastCol.$headerRow)->getAlignment()->setWrapText(true);
  $sheet->getRowDimension($headerRow)->setRowHeight(22);
  $sheet->setAutoFilter('A'.$headerRow.':'.$lastCol.$headerRow);
  $sheet->getColumnDimension('A')->setWidth($titleCol==='B'?16:18);
  if($titleCol==='B')$sheet->getColumnDimension('B')->setWidth(24);
  $sheet->freezePane('A'.($headerRow+1));
  return [$ss,$sheet,$headerRow,$lastCol];
 }
 public static function finishSheet(Spreadsheet $ss,$sheet,string $lastCol,int $lastRow,int $headerRow):void{
  $lastColIdx=\PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($lastCol);
  for($i=1;$i<=$lastColIdx;$i++){
   $col=\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
   if($col==='A' || $col==='B')continue;
   $sheet->getColumnDimension($col)->setAutoSize(true);
  }
  if($lastRow>$headerRow){
   $range='A'.($headerRow+1).':'.$lastCol.$lastRow;
   $sheet->getStyle($range)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('DDDDDD');
   for($r=$headerRow+1;$r<=$lastRow;$r++){
    if(($r-$headerRow)%2===0)$sheet->getStyle('A'.$r.':'.$lastCol.$r)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F4F9F5');
   }
  }
 }
 public static function outputXlsx(Spreadsheet $ss,string $filename):void{
  header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
  header('Content-Disposition: attachment; filename="'.$filename.'"');
  header('Cache-Control: max-age=0');
  (new Xlsx($ss))->save('php://output');
  exit;
 }
}
