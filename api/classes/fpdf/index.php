<?php
include 'fpdf.php';
 include 'exfpdf.php';
 include 'easyTable.php';

 $pdf=new exFPDF();
 $pdf->AddPage(); 
 $pdf->SetFont('helvetica','',10);

 $table1=new easyTable($pdf, 2);
 $table1->easyCell('', 'img:logo.png, w70; align:L;');
 $table1->easyCell('', 'img:logo.gif, w80; align:R;');
 $table1->printRow();
 $table1->endTable(5);

 //====================================================================
 
$table=new easyTable($pdf, '{40, 30, 40, 30}','align:C{LCRR};border:1; border-color:#a1a1a1;  paddingY:2;border-width:0.3');
$table->rowStyle('align:{LLLL};font-style:B');
$table->easyCell('Ticket No','font-size:14;');
$table->easyCell('something', 'font-style:i');
$table->easyCell('Mobile No','font-size:15;');
$table->easyCell('another one', 'font-style:i');
$table->printRow();

$table->rowStyle('align:{LLLL};font-style:B');
$table->easyCell('First Name','font-size:14;');
$table->easyCell('something', 'font-style:i');
$table->easyCell('Last Name','font-size:15;');
$table->easyCell('another one', 'font-style:i');
$table->printRow();


$table->rowStyle('align:{LLLL};font-style:B');
$table->easyCell('From','font-size:14;');
$table->easyCell('something', 'font-style:i');
$table->easyCell('To','font-size:15;');
$table->easyCell('another one', 'font-style:i');
$table->printRow();


$table->rowStyle('align:{LLLL};font-style:B');
$table->easyCell('Date of Issue','font-size:14;');
$table->easyCell('something', 'font-style:i');
$table->easyCell('Date of Travel','font-size:15;');
$table->easyCell('another one', 'font-style:i');
$table->printRow();

$table->rowStyle('align:{LLLL};font-style:B');
$table->easyCell('Arrival Time','font-size:14;');
$table->easyCell('something', 'font-style:i');
$table->easyCell('Departure Time','font-size:15;');
$table->easyCell('another one', 'font-style:i');
$table->printRow();

$table->rowStyle('align:{LLLL};font-style:B');
$table->easyCell('Seats','font-size:14;');
$table->easyCell('something', 'font-style:i');
$table->easyCell('Total','font-size:15;');
$table->easyCell('another one', 'font-style:i');
$table->printRow();

$table->endTable();


 $pdf->Output('D','ticket.pdf'); 
?>