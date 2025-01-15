<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
require_once dirname(__FILE__) . '/tcpdf/tcpdf.php';

class Pdf extends TCPDF {

    var $htmlHeader;
    var $htmlFooter;
    
    function __construct() {
        parent::__construct();
    }
    

    public function Header() {
        // Logo
//        $image_file = K_PATH_IMAGES . 'logo_example.jpg';
//        $this->Image($image_file, 10, 10, 15, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        $this->SetFont('helvetica', 'B', 20);
//        $this->Cell(0, 15, '<< TCPDF Example 003 >>', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->writeHTML($this->htmlHeader, true, 0, true, 0);
//        $headerData = $this->getHeaderData();
//        $this->SetFont('helvetica', 'B', 10);
//        $this->writeHTML($headerData['string']);
    }

    public function Footer() {
        // Logo
         $this->SetY(-15);
//        $image_file = K_PATH_IMAGES . 'logo_example.jpg';
//        $this->Image($image_file, 10, 10, 15, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        $this->SetFont('helvetica', 'B', 20);
//        $this->Cell(0, 15, '<< TCPDF Example 003 >>', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->writeHTML($this->htmlFooter, true, 0, true, 0);
//        $headerData = $this->getHeaderData();
//        $this->SetFont('helvetica', 'B', 10);
//        $this->writeHTML($headerData['string']);
    }
    // Page footer
//    public function Footer() {
//        // Position at 15 mm from bottom
//        $this->SetY(-15);
//        // Set font
//        $this->SetFont('helvetica', 'I', 8);
//        // Page number
//        $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
//    }
    
    
    public function setHtmlHeader($htmlHeader) {
        $this->htmlHeader = $htmlHeader;
    }

    public function setHtmlFooter($htmlFooter) {
        $this->htmlFooter = $htmlFooter;
    }
//    public function Header() {
//        $this->writeHTML($this->htmlHeader, true, 0, true, 0);
//    }

}

/*Author:Tutsway.com */
/* End of file Pdf.php */
/* Location: ./application/libraries/Pdf.php */