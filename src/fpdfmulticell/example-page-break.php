<?php

//include fpdf class
require_once("fpdf.php");

/**
* myfpdf extends fpdf class, it is used to draw the header and footer
*/
require_once("myfpdf.php");


//Tag Based Multicell Class
require_once("class.fpdfmultiCell.php");


$pdf = new myFpdf();

$pdf->Open();
$pdf->SetMargins(20, 20, 20);

//set default font/colors
$pdf->SetFont('arial','',11);
$pdf->SetTextColor(200,10,10);
$pdf->SetFillColor(254,255,245);

//add the page
$pdf->AddPage();
$pdf->AliasNbPages(); 

/**
* Create the multicell class and pass the fpdf object as a parameter to the constructor
*/
$oMulticell = new FpdfmultiCell($pdf);



$oMulticell->setStyle("p","times","",11,"130,0,30");
$oMulticell->setStyle("pb","times","B",11,"130,0,30");
$oMulticell->setStyle("t1","arial","",11,"80,80,260");
$oMulticell->setStyle("t3","times","B",14,"203,0,48");
$oMulticell->setStyle("t4","arial","BI",11,"0,151,200");
$oMulticell->setStyle("hh","times","B",11,"255,189,12");
$oMulticell->setStyle("ss","arial","",7,"203,0,48");
$oMulticell->setStyle("font","helvetica","",10,"0,0,255");
$oMulticell->setStyle("style","helvetica","BI",10,"0,0,220");
$oMulticell->setStyle("size","times","BI",13,"0,0,120");
$oMulticell->setStyle("color","times","BI",13,"0,255,255");

$txt1 = "Created by <t1 href='mailto:andy@interpid.eu'>Andrei Bintintan</t1>";

$txt2 = "";
for ($i=0; $i<100; $i++)
    $txt2 .= "Line $i\n";
    
$txt2 = "<p>$txt2</p>";

$oMulticell->multiCell(0, 5, $txt2, 1, "J", 1, 0, 0, 0, 0); $pdf->Ln(10);

//$pdf->multiCell(0, 5, $txt2, 1, "J", 1, 3, 3, 3, 3); $pdf->Ln(10);

$pdf->Output();

?>