<?php

//include fpdf class
require_once("../fpdf.php");

/**
* myfpdf extends fpdf class, it is used to draw the header and footer
*/
require_once("../myfpdf-multicell.php");


//Tag Based Multicell Class
require_once("../class.fpdfmultiCell.php");


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

$oMulticell->setStyle("p", "times", "", 11, "130,0,30");
$oMulticell->setStyle("b", "times", "B", 11, "130,0,30");
$oMulticell->setStyle("t1", "arial", "", 11, "80,80,260");
$oMulticell->setStyle("t3", "times", "B", 11, "203,0,48");

$oMulticell->setStyle("tag1", "arial", "", 9, "80,80,260");
$oMulticell->setStyle("tag2", "arial", "b", 9, "80,40,260");
$oMulticell->setStyle("tag3", "times", "", 9, "40,80,260");

$sTxt1 = file_get_contents("text1.txt");
$sTxt2 = file_get_contents("text2.txt");

/**
* Alignments
*/
$oMulticell->multiCell(50, 5, "align: <b>left</b>", 1, "L", 0); 
$oMulticell->multiCell(50, 5, "align: <b>right</b>", 1, "R", 0); 
$oMulticell->multiCell(50, 5, "align: <b>center</b>", 1, "C", 0); 

//justified
$oMulticell->multiCell(100, 5, str_repeat("align: <b>justified</b> ", 10), 1, "J", 0); 
$oFpdf->Ln(5);
$oMulticell->multiCell(100, 5, $sTxt1, 1, "J", 0); 

//full width
$oFpdf->Ln(5);
$oMulticell->multiCell(0, 4, $sTxt1, 1, "J", 0); 

/**
* Paddings
*/
$oFpdf->Ln(5);
$oMulticell->multiCell(0, 4, $sTxt1, 1, "J", 0, 5, 5, 5, 5);
$oMulticell->multiCell(0, 4, $sTxt1, 1, "J", 0, 1, 5, 1, 1);
$oMulticell->multiCell(0, 4, $sTxt1, 1, "J", 0, 1, 1, 5, 1);
$oMulticell->multiCell(0, 4, $sTxt1, 1, "J", 0, 1, 1, 1, 5);

$oMulticell->multiCell(0, 4, $sTxt1, 1, "J", 0, 1, 1, 1, 5);



/**
* Paddings
* 
* @var myFpdf
*/

$oFpdf->Ln(10);   //new line

$oFpdf->Ln(10);   //new line

$oFpdf->Output();

?>