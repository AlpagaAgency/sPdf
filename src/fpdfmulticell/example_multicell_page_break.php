<?php
/**
* FPDF Advanced Multicell - Example
* Copyright (c) 2005-2012, Andrei Bintintan, http://www.interpid.eu
*/

//include fpdf class
require_once("fpdf.php");

/**
* myfpdf extends fpdf class, it is used to draw the header and footer
*/
require_once("myfpdf-multicell.php");


//Tag Based Multicell Class
require_once("class.fpdfmulticell.php");

//create the fpdf object and do some initialization
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
$oMulticell = new fpdfMulticell($oFpdf);

/**
* Set the styles
*/
$oMulticell->setStyle("p", "times", "", 11, "130,0,30");
$oMulticell->setStyle("pb", "times", "B", 11, "80,80,260");
$oMulticell->setStyle("pi", "times", "I", 11, "80,80,260");
$oMulticell->setStyle("pu", "times", "U", 11, "80,80,260");
$oMulticell->setStyle("t1", "arial", "", 11, "80,80,260");
$oMulticell->setStyle("t3", "times", "B", 14, "203,0,48");
$oMulticell->setStyle("t4", "arial", "BI", 11, "0,151,200");
$oMulticell->setStyle("hh", "times", "B", 11, "255,189,12");
$oMulticell->setStyle("ss", "arial", "", 7, "203,0,48");
$oMulticell->setStyle("font", "helvetica", "", 10, "0,0,255");
$oMulticell->setStyle("style", "helvetica", "BI", 10, "0,0,220");
$oMulticell->setStyle("size", "times", "BI", 13, "0,0,120");
$oMulticell->setStyle("color", "times", "BI", 13, "0,255,255");

$txt1 = "Created by <t1 href='mailto:andy@interpid.eu'>Andrei Bintintan</t1>";

$txt2 = '<p>';
for ($i=0; $i<100; $i++)
    $txt2 .= "Line <pb>$i</pb>\n";
    
$txt2 .= '</p>';

//create an advanced multicell
$oMulticell->multiCell(0, 5, $txt2, 1, "J", 1, 0, 0, 0, 0); 
$oFpdf->Ln(10);   //new line

//send the pdf to the browser
$oFpdf->Output();
