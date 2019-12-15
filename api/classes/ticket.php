<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('Africa/Nairobi');

//includes
include 'fpdf/fpdf.php';
include 'fpdf/exfpdf.php';
include 'fpdf/easyTable.php';
include 'phpqrcode/qrlib.php';

class Ticket extends exFPDF
{
    public $pdf;

    public function addFonts():void
    {
        $this->pdf->AddFont('bahaus','','bauhausregular.php');
        $this->pdf->AddFont('coves','','Coves-Bold.php');
        $this->pdf->AddFont('covesl','','Coves-Light.php');
        $this->pdf->AddFont('aquatico','','Aquatico-Regular.php');
        $this->pdf->AddFont('moonb','','Moon-Bold.php');
        $this->pdf->AddFont('moonl','','Moon-Light.php');
    }

    public function addLogo($image=''):void
    {
        $this->pdf->SetFont('coves','',17);
        $this->pdf->setXY(27, 3);
        $this->pdf->setTextColor(255, 255, 255);
        $this->pdf->Cell(19,12,"This is your Ticket",0,0,"R");

        $this->pdf->setXY(26,$this->pdf->GetY()+5);
        $this->pdf->SetFont('coves','',10);
        $this->pdf->Cell(19,12,"Present it at the day of travel",0,0,"R");

        $this->pdf->image(__DIR__ .'/images/oyaa5.png',$this->pdf->GetPageWidth()/1.7,2,-140);
        $this->pdf->setDrawColor(234,67,53);
        $this->pdf->line(1,20,$this->pdf->GetPageWidth()-1,20);


        $this->pdf->SetFont('bahaus','',30);
        $this->pdf->setXY($this->pdf->GetPageWidth()-28, 4.5);
        $this->pdf->Cell(19,12,"Tickets",0,0,"R");
    }

}