<?php

/**
 * Class extention for Header and Footer Definitions
 *
 */

class myFpdf extends FPDF
{
    
    public function Header()
    {
        $this->SetY(10);

        /**
        * yes, even here we can use the multicell tag!
        * this will be a local object
        */
        $oMulticell = fpdfMulticell::getInstance($this);
        
        $oMulticell->SetStyle("head1","arial","",6,"160,160,160");
        $oMulticell->SetStyle("head2","arial","",6,"0,119,220");
        
        $oMulticell->multiCell(100, 3, "<head1 href='www.interpid.eu/fpdf-addons'>FPDF Advanced Multicell (Fpdf Add On)\nAuthor:</head1><head2 href='mailto:andy@interpid.eu'> Andrei Bintintan</head2>");
        
        $this->Image(dirname(__FILE__) . '/images/interpid_logo.png', 160, 10, 40, 0, '', 'http://www.interpid.eu');
        $this->SetY($this->tMargin);
    }
    
    public function Footer()
    {
        $this->SetY(-10);
        $this->SetFont('Arial','I',7);
        $this->SetTextColor(170, 170, 170);
        $this->MultiCell(0, 4, "Page {$this->PageNo()} / {nb}", 0, 'C');
    }
} 

?>