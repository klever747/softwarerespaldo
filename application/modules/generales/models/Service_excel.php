<?php

defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Service_excel extends My_Model {

    function __construct() {
        parent::__construct();
    }

    public function crear() {
        $spreadsheet = new Spreadsheet();
        return $spreadsheet;
    }

    public function crearXlsx($spreadsheet, $nombre) {
        $writer = new Xlsx($spreadsheet);
        ob_end_clean();
//        $filename = 'name-of-the-generated-file';
//        header('Content-Type: application/vnd.ms-excel');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $nombre . '.xlsx"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');

//        return $writer;
    }

    public function generarXls() {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Hello World !');
        $writer = new Xlsx($spreadsheet);
        $filename = 'name-of-the-generated-file';
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');
    }

    public function gen() {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
                ->setCreator('Maarten Balliauw')
                ->setLastModifiedBy('Maarten Balliauw')
                ->setTitle('PhpSpreadsheet Test Document')
                ->setSubject('PhpSpreadsheet Test Document')
                ->setDescription('Test document for PhpSpreadsheet, generated using PHP classes.')
                ->setKeywords('office PhpSpreadsheet php')
                ->setCategory('Test result file');

//        $spreadsheet->setActiveSheetIndex(0)
//                ->setCellValue('A1', 'Hello')
//                ->setCellValue('B2', 'world!')
//                ->setCellValue('C1', 'Hello')
//                ->setCellValue('D2', 'world!');



        $spreadsheet->setActiveSheetIndex(0)
                ->setCellValue('A1', 'Companyshipto')
                ->setCellValue('B1', 'contactshipto')
                ->setCellValue('C1', 'address1shipto')
                ->setCellValue('D1', 'address2shipto')
                ->setCellValue('E1', 'address3shipto')
                ->setCellValue('F1', 'cityshipto')
                ->setCellValue('G1', 'stateshipto')
                ->setCellValue('H1', 'zipshipto')
                ->setCellValue('I1', 'Countryshipto')
                ->setCellValue('J1', 'phoneshipto')
                ->setCellValue('K1', 'Reference1')
                ->setCellValue('L1', 'Reference2')
                ->setCellValue('M1', 'Quantity')
                ->setCellValue('N1', 'Item')
                ->setCellValue('O1', 'ProdDesc')
                ->setCellValue('P1', 'Length')
                ->setCellValue('Q1', 'width')
                ->setCellValue('R1', 'height')
                ->setCellValue('S1', 'WeightKg')
                ->setCellValue('T1', 'DclValue')
                ->setCellValue('U1', 'Service')
                ->setCellValue('V1', 'PkgType')
                ->setCellValue('W1', 'GenDesc')
                ->setCellValue('X1', 'Currency')
                ->setCellValue('Y1', 'Origin')
                ->setCellValue('Z1', 'UOM')
                ->setCellValue('AA1', 'TPComp')
                ->setCellValue('AB1', 'TPAttn')
                ->setCellValue('AC1', 'TPAdd1')
                ->setCellValue('AD1', 'TPCity')
                ->setCellValue('AE1', 'TPState')
                ->setCellValue('AF1', 'TPCtry')
                ->setCellValue('AG1', 'TPZip')
                ->setCellValue('AH1', 'TPPhone')
                ->setCellValue('AI1', 'TPAcct')
                ->setCellValue('AJ1', 'SatDlv');

        $spreadsheet->setActiveSheetIndex(0)
                ->setCellValue('A4', 'Miscellaneous glyphs')
                ->setCellValue('A5', 'éàèùâêîôûëïüÿäöüç');

        $spreadsheet->getActiveSheet()
                ->setCellValue('A8', "Hello\nWorld");
        $spreadsheet->getActiveSheet()
                ->getRowDimension(8)
                ->setRowHeight(-1);
        $spreadsheet->getActiveSheet()
                ->getStyle('A8')
                ->getAlignment()
                ->setWrapText(true);

        $value = "-ValueA\n-Value B\n-Value C";
        $spreadsheet->getActiveSheet()
                ->setCellValue('A10', $value);
        $spreadsheet->getActiveSheet()
                ->getRowDimension(10)
                ->setRowHeight(-1);
        $spreadsheet->getActiveSheet()
                ->getStyle('A10')
                ->getAlignment()
                ->setWrapText(true);
        $spreadsheet->getActiveSheet()
                ->getStyle('A10')
                ->setQuotePrefix(true);

// Rename worksheet

        $spreadsheet->getActiveSheet()
                ->setTitle('Simple');

// Save
//        $helper->write($spreadsheet, __FILE__);

        $writer = new Xlsx($spreadsheet);
        $filename = 'wacho';
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
    }

}

?>