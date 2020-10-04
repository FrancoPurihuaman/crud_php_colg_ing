<?php namespace App\Pdf;

use FPDF;

class UserPdf extends FPDF{

    private $user;

    // Cabecera de página
    public function Header()
    {
        $logo = PUBLIC_PATH."/images/logo.png";
        $this->image($logo,10,6,20,20);
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(276, 5, "LISTA DE USUARIOS",0,0,'C');
        $this->ln();
        $this->SetFont('Times', '', 12);
        $this->Cell(276, 5, "Impreso por: ".$this->user,0,0,'C');
        $this->ln(20);
    }

    // Pie de página
    public function Footer()
    {
        // Posición: a 1,5 cm del final
        $this->SetY(-15);
        // Arial italic 8
        $this->SetFont('Arial','I',8);
        // Número de página
        $this->Cell(0,10,'Pagina'.$this->PageNo().'/{nb}',0,0,'C');
    }

    // Cabezeera de tabla
    public function headerTable(){
        $this->SetFont('Times','B',12);
        $this->Cell(20,10,'ID',1,0,'C');
        $this->Cell(115,10,'NOMBRE',1,0,'C');
        $this->Cell(50,10,'USUARIO',1,0,'C');
        $this->Cell(20,10,'ESTADO',1,0,'C');
        $this->Cell(20,10,'ACTIVO',1,0,'C');
        $this->Cell(50,10,'GRUPO',1,0,'C');
        $this->ln();
    }

    /**
     * LLenar los registros de la tabla
     * 
     * @param array arreglo de objetos stdClass (cada stdClass es un registro de base de datos)
     * @return void
     */
    public function bodyTable($stdData){
        $this->SetFont('Times','',12);
        foreach($stdData as $row){
            $this->Cell(20,10,utf8_decode($row->USU_ID),1,0,'C');
            $this->Cell(115,10,utf8_decode($row->USU_NOMBRE),1,0,'C');
            $this->Cell(50,10,utf8_decode($row->USU_USER),1,0,'C');
            $this->Cell(20,10,utf8_decode($row->USU_ESTADO),1,0,'C');
            $this->Cell(20,10,utf8_decode($row->USU_ACTIVO),1,0,'C');
            $this->Cell(50,10,utf8_decode($row->UG_NOMBRE),1,0,'C');
            $this->ln();
        }
    }

    /**
     * Muestra el pdf en el navegador
     * 
     * @param array arreglo de objetos stdClass (cada stdClass es un registro de base de datos)
     * @param string  usuario quien imprime el documento
     * @return void
     */
    public function showPdf($stdData, $user=''){
        $this->user = $user;
        $this->SetTitle('Usuarios', true);
        $this->AliasNbPages();
        $this->AddPage('L','A4',0);
        $this->SetFont('Times','',12);
        $this->headerTable();
        $this->bodyTable($stdData);
        $this->Output();
    }

}