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

//TAG Based Formatted text
$sTxt1 = "<p>Created by <t1 href='mailto:andy@interpid.eu'>Andrei Bintintan, </t1><t1 href='www.interpid.eu'>www.interpid.eu</t1></p>";

$sTxt2 = "<p><t3>Description</t3>\n
\tThis <pb>FPDF addon</pb> allows creation of an <pb>Advanced Multicell</pb> which uses as input a <pb>TAG based formatted string</pb> instead of a simple string. The use of tags allows to change the font, the style (<pb>bold</pb>, <pi>italic</pi>, <pu>underline</pu>), the size, and the color of characters and many other features.
\tThe call of the function is pretty similar to the Multicell function in the fpdf base class with some extended parameters.\n\n";

$sTxt2 .= "<t3>Features:</t3>
\t- Text can be <hh>aligned</hh>, <hh>cente~~~red</hh> or <hh>justified</hh>
\t- Different <font>Font</font>, <size>Sizes</size>, <style>Styles</style>, <color>Colors</color> can be used 
\t- The cell block can be framed and the background painted
\t- <style href='www.fpdf.org'>Links</style> can be used in any tag
\t- <t4>TAB</t4> spaces (<pb>\\t</pb>) can be used
\t- Variable Y relative positions can be used for <ss ypos='-0.8'>Subscript</ss> or <ss ypos='1.1'>Superscript</ss>
\t- Cell padding (left, right, top, bottom)
\t- Controlled Tag Sizes can be used</p>\n
\t<size size='50' >Paragraph Example:~~~</size><font> - Paragraph 1</font>
\t<p size='60' > ~~~</p><font> - Paragraph 2</font>
\t<p size='60' > ~~~</p> - Paragraph 2
\t<p size='70' >Sample text~~~</p><p> - Paragraph 3</p>
\t<p size='50' >Sample text~~~</p> - Paragraph 1
\t<p size='60' > ~~~</p><t4> - Paragraph 2</t4>\n\n";

$sTxt2 .= "<t3>Observations:</t3><p>
\t- If no <t4><TAG></t4> is specified then the FPDF current settings(font, style, size, color) are used
\t- The <t4>ttags</t4> tag name is reserved for the TAB SPACES
</p>";

//create an advanced multicell
$oMulticell->multiCell(150, 5, $sTxt1, 1, "L", 1, 5, 5, 5, 5); 
$oFpdf->Ln(10); //new line

//create an advanced multicell
$oMulticell->multiCell(0, 5, $sTxt2, 1, "J", 1, 3, 3, 3, 3); 
$oFpdf->Ln(10);   //new line

//send the pdf to the browser
$oFpdf->Output();