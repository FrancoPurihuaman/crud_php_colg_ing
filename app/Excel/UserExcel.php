<?php namespace App\Excel;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;

class UserExcel{

    private $spreadsheet;
    private $title;

    public function __construct()
    {
        $this->spreadsheet = new Spreadsheet();
    }

    private function head(){
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . "{$this->title}.xlsx" . '"');
        header('Cache-Control: max-age=0');
    }

    private function header($sheet){

        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1', 'NOMBRE');
        $sheet->setCellValue('C1', 'USUARIO');
        $sheet->setCellValue('D1', 'ESTADO');
        $sheet->setCellValue('E1', 'ACTIVO');
        $sheet->setCellValue('F1', 'GRUPO');
    }

    private function body($sheet, $array){
        $i =2;
        foreach($array as $oStdClas){
            $sheet->setCellValue("A{$i}", $oStdClas->USU_ID);
            $sheet->setCellValue("B{$i}", $oStdClas->USU_NOMBRE);
            $sheet->setCellValue("C{$i}", $oStdClas->USU_USER);
            $sheet->setCellValue("D{$i}", $oStdClas->USU_ESTADO);
            $sheet->setCellValue("E{$i}", $oStdClas->USU_ACTIVO);
            $sheet->setCellValue("F{$i}", $oStdClas->UG_NOMBRE);
            
            $i++;
        }
    }

    private function setAutoSize($sheet){
        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->getColumnDimension('D')->setAutoSize(true);
        $sheet->getColumnDimension('E')->setAutoSize(true);
        $sheet->getColumnDimension('F')->setAutoSize(true);
    }

    public function excel($array, $title){
        $this->title = $title;

        $sheet = $this->spreadsheet->getActiveSheet();
        $sheet->setTitle($this->title);

        $this->header($sheet);
        $this->body($sheet, $array);
        $this->setAutoSize($sheet);
        $this->head();
        
        $writer = IOFactory::createWriter($this->spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
    }
}