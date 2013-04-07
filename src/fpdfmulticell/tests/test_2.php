<?php

//include fpdf class
require_once("../fpdf.php");

/**
* myfpdf extends fpdf class, it is used to draw the header and footer
*/
require_once("../myfpdf-multicell.php");


//Tag Based Multicell Class
require_once("../class.fpdfmulticell.php");


$oFpdf = new myFpdf();

$oFpdf->Open();
$oFpdf->SetMargins(20, 20, 20);

//set default font/colors
$oFpdf->SetFont('arial', '', 11);
$oFpdf->SetTextColor(200, 10, 10);
$oFpdf->SetFillColor(254, 255, 245);

//add the page
$oFpdf->AddPage();
$oFpdf->AliasNbPages(); 

/**
* Create the multicell class and pass the fpdf object as a parameter to the constructor
*/
$oMulticell = new FpdfmultiCell($oFpdf);

$oMulticell->setStyle("p",      "times",     "",     11, "130,0,30");
$oMulticell->setStyle("pb",     "times",     "B",    11, "130,0,30");
$oMulticell->setStyle("t1",     "arial",     "",     11, "80,80,260");
$oMulticell->setStyle("t3",     "times",     "B",    14, "203,0,48");
$oMulticell->setStyle("t4",     "arial",     "BI",   11, "0,151,200");
$oMulticell->setStyle("hh",     "times",     "B",    11, "255,189,12");
$oMulticell->setStyle("ss",     "arial",     "",     7,  "203,0,48");
$oMulticell->setStyle("font",   "helvetica", "",     10, "0,0,255");
$oMulticell->setStyle("style",  "helvetica", "BI",   10, "0,0,220");
$oMulticell->setStyle("size",   "times",     "BI",   13, "0,0,120");
$oMulticell->setStyle("color",  "times",     "BI",   13, "0,255,255");

$sTxt2 = file_get_contents("text2.txt");

//bigger text different widths

$oMulticell->multiCell(50, 5, $sTxt2, 1, "J", 0, 1, 1, 1, 5);
$oFpdf->Ln(5); //new line

$oMulticell->multiCell(100, 5, $sTxt2, 1, "J", 0, 1, 1, 1, 5);
$oFpdf->Ln(5); //new line

$oMulticell->multiCell(150, 5, $sTxt2, 1, "J", 0, 1, 1, 1, 5);
$oFpdf->Ln(5); //new line

$oMulticell->multiCell(0, 5, $sTxt2, 1, "J", 0, 1, 1, 1, 5);
$oFpdf->Ln(5); //new line


//very long 
$oMulticell->multiCell(0, 5, str_repeat($sTxt2, 1), 1, "J", 0, 1, 1, 1, 5);
$oFpdf->Ln(5); //new line


/**
* Paddings
* 
* @var myFpdf
*/

$oFpdf->Ln(10);   //new line

$oFpdf->Ln(10);   //new line

$oFpdf->Output();

?>