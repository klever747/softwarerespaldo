<?php

class Service_general extends My_Model {

    function __construct() {
        parent::__construct();
    }

    public function obtenerConfiguracion($nombre = false, $id = false, $estado = ESTADO_ACTIVO) {
        $this->db->select('u.*');
        $this->db->from((!DB_CON_ESQUEMAS ? '' : 'general.') . 'configuracion u');

        if ($id) {
            $this->db->where('u.id', $nombre);
            return $this->retornarUno();
        }

        if ($nombre) {
            $this->db->where('u.nombre', $nombre);
            $this->db->where('u.estado', ESTADO_ACTIVO);
            return $this->retornarUno();
        }
        $this->db->where('u.estado', $estado);
        $this->db->order_by('u.nombre', 'ASC');
        return $this->retornarMuchos();
    }

    public function guardarConfiguracion($datos) {
        if (key_exists('id', $datos)) {
            $inactivar = array(
                'id' => $datos['id'],
                'estado' => ESTADO_INACTIVO);
            $this->actualizar((!DB_CON_ESQUEMAS ? '' : 'general.') . 'configuracion', $inactivar, "id", true);
            unset($datos['id']);
            unset($datos['creacion_fecha']);
            unset($datos['creacion_usu']);
            unset($datos['creacion_ip']);
        }
        if ($datos['tipo'] == 0) {
            $datos['valor'] = trim(utf8_encode($datos['valor']));
        }

        return $this->ingresar((!DB_CON_ESQUEMAS ? '' : 'general.') . 'configuracion', $datos, true, true);
    }

    /*     * ************* PDF ***************** */

    public function pdf_generacion($arr_html, $page_format = false, $header = false, $footer = false) {

        if (!$page_format) {
            $page_format = array(
                'MediaBox' => FORMATO_10x15,
                'Dur' => 3,
                'trans' => array(
                    'D' => 1.5,
                    'S' => 'Split',
                    'Dm' => 'V',
                    'M' => 'O'
                ),
                'Rotate' => FORMATO_10x15_ROTACION,
                'PZ' => 1,
            );
        }
        if (ob_get_contents())
            ob_end_clean();
        $this->load->library('pdf');
        $pdf = new Pdf(PDF_PAGE_ORIENTATION, 'mm', PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->setFontSubsetting(true);
//        $pdf->addFont('DejaVuSans', '', 'DejaVuSans.ttf');
//        $pdf->SetFont('DejaVuSans', '', 10, '', true);
        $pdf->SetMargins(10, 10, 10, true);

        foreach ($arr_html as $html) {
            $pdf->AddPage('L', $page_format, false, false);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetAutoPageBreak(true);
            $pdf->SetAuthor('Rosaholics');
            $pdf->SetDisplayMode('real', 'default');
            $pdf->writeHTMLCell(0, 0, '', '', $html['mensaje'], 0, 1, 0, true, 'L', true);
//
            if ($html['componentes'] == 1) {
                $pdf->SetY(75);
                $pdf->SetFont('helvetica', '', 10);
// define barcode style
                $style = array(
                    'position' => 'C',
                    'align' => 'C',
                    'stretch' => false,
                    'fitwidth' => true,
                    'cellfitalign' => '',
                    'border' => true,
                    'hpadding' => 'auto',
                    'vpadding' => '0',
                    'fgcolor' => array(0, 0, 0),
                    'bgcolor' => false, //array(255,255,255),
                    'text' => false,
                    'font' => 'helvetica',
                    'fontsize' => 18,
                    'stretchtext' => 4
                );

                $pdf->write1DBarcode($html['orden_caja_id'], 'C39', '', '', '', 10, 0.4, $style, 'N');
                if ($html['total_cajas'] > 1) {
                    $html['orden_caja'] = $html['orden_caja'] . " CAJA " . $html['num_caja_actual'] . "/" . $html['total_cajas'];
                }
                $pdf->SetFont('helvetica', '', 20);
                $pdf->Cell(0, 0, $html['orden_caja'], '', '', 'C');


//                $pdf->Ln();
//                if ($html['header']) {
                $pdf->setHtmlHeader(false);
//                }
//                if ($html['footer']) {
                $pdf->setHtmlFooter(false);
//                }
            } else {

                $pdf->SetY(88.5);
                $pdf->SetFont('helvetica', '', 10);
// define barcode style
                $style = array(
                    'position' => 'C',
                    'align' => 'C',
                    'stretch' => false,
                    'fitwidth' => true,
                    'cellfitalign' => '',
                    'border' => true,
                    'hpadding' => 'auto',
                    'vpadding' => '0',
                    'fgcolor' => array(0, 0, 0),
                    'bgcolor' => false, //array(255,255,255),
                    'text' => false,
                    'font' => 'helvetica',
                    'fontsize' => 15,
                    'stretchtext' => 4
                );

                $pdf->write1DBarcode($html['orden_caja_id'], 'C39', '', '', '', 5, 0.4, $style, 'N');
//                if ($html['total_cajas'] > 1) {
//                    $html['orden_caja'] = $html['orden_caja'] . " CAJA " . $html['num_caja_actual'] . "/" . $html['total_cajas'];
//                }
//                $pdf->SetFont('helvetica', '', 10);
//                $pdf->Cell(0, 0, $html['orden_caja'], '', '', 'C');

                if ($html['header']) {
                    $pdf->setHtmlHeader($html['header']);
                }
                if ($html['footer']) {
                    $pdf->setHtmlFooter($html['footer']);
                }
            }
        }



        return $pdf;
//        $pdf->Output('pdf_softwareholics.pdf', 'FI');
    }

    public function pdf_generacion_eternizadas($arr_html, $page_format = false, $header = false, $footer = false) {
        if (!$page_format) {
            $page_format = array(
                'MediaBox' => FORMATO_07x10, //'urx' => 150, 'ury' => 100),
                'Dur' => 3,
                'trans' => array(
                    'D' => 1.5,
                    'S' => 'Split',
                    'Dm' => 'V',
                    'M' => 'O'
                ),
                'Rotate' => 90,
                'PZ' => 1,
            );
        }
        ob_end_clean();
        $this->load->library('Pdf');
        $pdf = new Pdf(PDF_PAGE_ORIENTATION, 'mm', PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->setFontSubsetting(true);
//        $pdf->addFont('DejaVuSans', '', 'DejaVuSans.ttf');
//        $pdf->SetFont('DejaVuSans', '', 10, '', true);
        $pdf->SetMargins(5, 0, 5, true);

        foreach ($arr_html as $html) {
            $pdf->AddPage('L', $page_format, false, false);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetAutoPageBreak(true);
            $pdf->SetAuthor('Rosaholics');
            $pdf->SetDisplayMode('real', 'default');
            $pdf->writeHTMLCell(0, 0, '', '', $html['mensaje'], 0, 1, 0, true, 'L', true);
//

            $pdf->SetY(90);
            $pdf->SetFont('helvetica', '', 8);
            $pdf->WriteHtmlCell(0, 0, '', '', $html['footer'], 0, 1, 0, true, 'L', true);
        }
        return $pdf;
    }

}
