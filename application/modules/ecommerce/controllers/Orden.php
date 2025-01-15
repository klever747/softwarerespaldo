<?php

use setasign\Fpdi\Fpdi;

defined('BASEPATH') OR exit('No direct script access allowed');

class Orden extends MY_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model("produccion/service_logistica");
        $this->load->model("ecommerce/service_ecommerce");
        $this->load->model("ecommerce/service_ecommerce_cliente");
        $this->load->model("ecommerce/service_ecommerce_orden");
        $this->load->model("ecommerce/service_ecommerce_producto");
        $this->load->model("ecommerce/service_ecommerce_logistica");
        $this->load->model("ecommerce/service_ecommerce_formula");
        $this->load->model("manufactura/service_manufactura");
    }

    public function orden_item_propiedad_eliminar() {

        $orden_item_propiedad_id = $this->input->post('orden_item_propiedad_id');
        $eliminacion = $this->service_ecommerce->eliminarOrdenItemPropiedad($orden_item_propiedad_id);
        $respuesta = array("error" => !$eliminacion ? true : false, "respuesta" => $eliminacion);
        header('Content-Type: application/json');
        echo json_encode($respuesta);
    }

    public function orden_item_propiedad_nuevo() {
        $data['orden_item_id'] = $this->input->post('orden_item_id');

        $items_det = $this->load->view('orden_detalle_item_propiedad_edicion.php', $data, true);
        $respuesta = array("error" => false, "respuesta" => $items_det);
        header('Content-Type: application/json');
        echo json_encode($respuesta);
    }

    public function orden_item_propiedad_editar() {
        $data['orden_item_propiedad_id'] = $this->input->post('orden_item_propiedad_id');
        $data['orden_item_id'] = $this->input->post('orden_item_id');
        $data['orden_item_propiedad'] = $this->service_ecommerce->obtenerOrdenItemPropiedad($data['orden_item_propiedad_id']);

        $items_det = $this->load->view('orden_detalle_item_propiedad_edicion.php', $data, true);

        $respuesta = array("error" => false, "respuesta" => $items_det);
        header('Content-Type: application/json');
        echo json_encode($respuesta);
    }

    public function orden_item_propiedad_guardar() {
        $actualizacion = false;
        $respuesta = false;
        $valor_guardar = utf8_encode(htmlentities($this->input->post('valor')));
        $valor_numero = utf8_encode(htmlentities($this->input->post('valor_numero')));
        if ($valor_numero) {
            $valor_guardar = utf8_encode(htmlentities($this->input->post('valor_numero')));
        }
        $propiedad = $this->service_ecommerce_producto->obtenerPropiedad($this->input->post('propiedad_id'));
        if ((strpos(strtoupper($propiedad->nombre), 'AGR_C') === 0) && !is_numeric($valor_guardar)) {
            $respuesta = 'Valor ingresado invalido';
            $linea_id = false;
        } else {
            $data = array(
                "orden_item_propiedad_id" => $this->input->post('orden_item_propiedad_id'),
                "orden_item_id" => $this->input->post('orden_item_id'),
                "propiedad_id" => $this->input->post('propiedad_id'),
                "valor" => $valor_guardar,
            );

            $linea_id = $this->service_ecommerce->persistenciaOrdenItemPropiedad($data['orden_item_id'], $data['propiedad_id'], $data['valor']);

            if (!$linea_id) {
                $respuesta = 'Existe un problema durante la creaci&oacute;n';
            } else {
                $respuesta = 'Registro ' . ($linea_id[0] == 1 ? "creado" : "actualizado");
            }
        }

        header('Content-Type: application/json');
        echo json_encode(array("error" => !$linea_id, "respuesta" => $respuesta, "nuevo_id" => $linea_id));
    }

    public function cancelar_orden() {
        $data['id'] = $this->input->post('orden_id');
        $data['estado'] = ESTADO_ORDEN_CANCELADA;
        $actualizacion = $this->service_ecommerce_orden->actualizarOrden($data);

        header('Content-Type: application/json');
        echo json_encode(array("error" => !$actualizacion, "respuesta" => $actualizacion ? "Orden Cancelada" : "Existe un problema durante la cancelaci&oacute;n"));
    }

    public function actualizar_fecha_orden() {
        $data['id'] = $this->input->post('orden_id');
        $data['estado'] = ESTADO_ACTIVO;
        $fecha_entrega = $this->input->post('fecha_entrega');
        $fecha_carguera = $this->input->post('fecha_carguera');
        $fecha_preparacion = $this->input->post('fecha_preparacion');
        $data['fecha_entrega'] = $fecha_entrega;
        $data['fecha_carguera'] = $fecha_carguera; //$this->calcularFechaCarguera($data['fecha_entrega']);
        $data['fecha_preparacion'] = $fecha_preparacion; //$this->calcularFechaPreparacion($data['fecha_carguera'], $contieneTinturados);
        error_log(print_r($data, true));
//        die;
//
//
//        $data['estado'] = ESTADO_ORDEN_CANCELADA;
        $actualizacion = $this->service_ecommerce_orden->actualizarOrden($data);

        //adicional al cambio de fecha ahora vamos a ir a sus items y los vamos a desmarcar como bonchados y preparados
        $items = $this->service_ecommerce->obtenerOrdenItems($data['id']);
        foreach ($items as $item) {
            $this->service_manufactura->item_encerado($item->id);
        }

        header('Content-Type: application/json');
        echo json_encode(array("error" => !$actualizacion, "mensaje" => $actualizacion ? "Fechas actualizadas correctamente" : "Existe un problema durante la actualizaci&oacute;n"));
    }

//    public function calcularFechas($fecha_entrega, $fecha_preparacion, $hay_tinturados) {
//        $objOrden['fecha_entrega'] = convertirFechaStore($fecha_entrega);
//        $objOrden['fecha_carguera'] = $this->calcularFechaCarguera($objOrden['fecha_entrega']);
//        $objOrden['fecha_preparacion'] = $this->calcularFechaPreparacion($objOrden['fecha_carguera'], $contieneTinturados);
//        return $fechas;
//    }

    public function ordenContieneTinturados($orden_id) {
        return $this->service_ecommerce->ordenContieneTinturados($orden_id);
    }

    public function calcular_fechas_entrega() {
        $fecha_entrega = $fecha_carguera = $fecha_preparacion = false;
        $orden_id = $this->input->post('orden_id');
//        $orden = $this->service_ecommerce_orden->existeOrden(array('id' => $orden_id));
        $contieneTinturados = $this->ordenContieneTinturados($orden_id);

        $fecha_entrega = $this->input->post('fecha_entrega');
        $fecha_carguera = $this->input->post('fecha_carguera');
        $fecha_preparacion = $this->input->post('fecha_preparacion');
        error_log("Fecha Entrega" . $fecha_entrega);
        error_log("Fecha Carguera" . $fecha_carguera);
        error_log("Fecha Preparacion" . $fecha_preparacion);
        if (isset($fecha_entrega)) {
            $fecha_carguera = $this->service_ecommerce->calcularFechaCarguera($fecha_entrega);
            $fecha_preparacion = $this->service_ecommerce->calcularFechaPreparacion($fecha_carguera, $contieneTinturados);
        } else if (isset($fecha_carguera)) {
            $fecha_entrega = false;
            $fecha_preparacion = $this->service_ecommerce->calcularFechaPreparacion($fecha_carguera, $contieneTinturados);
        }

        header('Content-Type: application/json');
        echo json_encode(array("fecha_entrega" => $fecha_entrega,
            "fecha_carguera" => $fecha_carguera,
            "fecha_preparacion" => $fecha_preparacion,));
    }

    public function guardar_precio() {
        $orden_item_id = $this->input->post('orden_item_id');
        $precio_actualizado = $this->input->post('precio_actualizado');
        $actualizacion = $this->service_ecommerce_orden->actualizarPrecioOrdenItem($orden_item_id, $precio_actualizado);
        header('Content-Type: application/json');
        echo json_encode(array("error" => !$actualizacion, "mensaje" => ($actualizacion ? 'Precio Actualizado' : 'Problema durante la actualizacion'),));
    }

    public function pdf_tarjeta_mensaje($orden_id, $output_path, $output_format = 'FI', $tipo = "normal", $session_finca = false, $finca_id = false) {
        if (isset($session_finca) && !empty($session_finca)) {
            $pdf = $this->service_ecommerce_orden->generar_tarjetas_orden($orden_id, false, $tipo, $session_finca, $finca_id);
        } else {
            $pdf = $this->service_ecommerce_orden->generar_tarjetas_orden($orden_id, false, $tipo);
        }
        //$pdf = $this->service_ecommerce_orden->generar_tarjetas_orden($orden_id, false, $tipo);
        if (!$pdf) {
            error_log("No hay pdf");
            return false;
        }
        //incrementamos el numero de veces que ha sido impresa las tarjetas de esta orden
        $orden = $this->service_ecommerce->obtenerOrden($orden_id);
        $data['id'] = $orden_id;
        $data['impresiones'] = $orden->impresiones + 1;
        $actualizacion = $this->service_ecommerce_orden->actualizarOrden($data, "Impresion tarjeta");
        $pdf->Output($output_path, $output_format);
        return true;
    }

    public function pdf_tarjeta_mensaje_caja($orden_id, $caja_id, $output_path, $output_format = 'FI', $tipo = "normal") {
        $pdf = $this->service_ecommerce_orden->generar_tarjetas_orden($orden_id, $caja_id, $tipo);
        if (!$pdf) {
            error_log("No hay pdf");
            return false;
        }
        //incrementamos el numero de veces que ha sido impresa las tarjetas de esta orden
        $orden = $this->service_ecommerce->obtenerOrden($orden_id);
        $data['id'] = $orden_id;
        $data['impresiones'] = $orden->impresiones + 1;
        $actualizacion = $this->service_ecommerce_orden->actualizarOrden($data, "Impresion tarjeta");
        $pdf->Output($output_path, $output_format);
        return true;
    }

    public function imprimir_tarjeta($orden_id) {
        $ruta_pdf = "uploads/tarjetas/orden_" . $orden_id . ".pdf";
        $this->pdf_tarjeta_mensaje($orden_id, FCPATH . $ruta_pdf, 'FI');
    }

    public function json_imprimir_tracking($caja_id = false) {
        $ruta_pdf = false;
        if (!$caja_id) {
            $caja_id = $this->input->post('orden_id');
            $obj = $this->service_logistica->obtenerTrackingNumberCaja($caja_id);
            if ($obj) {
                $ruta_pdf = "uploads/tracking/" . $obj->tracking_number . ".pdf";
            }
            header('Content-Type: application/json');
            echo json_encode(array("error" => !$obj, "mensaje" => (!$obj ? 'No se pudo generar el pdf' : 'El pdf  se abrir&aacute; en otra ventana'), 'ruta_pdf' => base_url() . $ruta_pdf));
        }
    }

    public function json_imprimir_tarjeta($orden_id = false) {
        if (!$orden_id) {
            $orden_id = $this->input->post('orden_id');
        }
        $finca_id = $this->input->post('finca_id');
        $session_finca = $this->input->post('session_finca');
        $ruta_pdf = "uploads/tarjetas/orden_" . $orden_id . ".pdf";
        $pdf = $this->pdf_tarjeta_mensaje($orden_id, FCPATH . $ruta_pdf, 'F', TARJETA_NORMALES, $session_finca, $finca_id);
        header('Content-Type: application/json');
        echo json_encode(array("error" => !$pdf, "mensaje" => (!$pdf ? 'No se pudo generar la tarjeta' : 'El pdf de la tarjeta se abrir&aacute; en otra ventana'), 'ruta_pdf' => base_url() . $ruta_pdf));
    }

    public function json_imprimir_tarjeta_caja($caja_id = false) {
        if (!$caja_id) {
            $caja_id = $this->input->post('caja_id');
            $orden_id = $this->input->post('orden_id');
        }
        $ruta_pdf = "uploads/tarjetas/caja_" . $caja_id . ".pdf";
        $pdf = $this->pdf_tarjeta_mensaje_caja($orden_id, $caja_id, FCPATH . $ruta_pdf, 'F');
        header('Content-Type: application/json');
        echo json_encode(array("error" => !$pdf, "mensaje" => (!$pdf ? 'No se pudo generar la tarjeta' : 'El pdf de la tarjeta se abrir&aacute; en otra ventana'), 'ruta_pdf' => base_url() . $ruta_pdf));
    }

    public function json_imprimir_tarjeta_eternizadas($orden_id = false) {
        if (!$orden_id) {
            $orden_id = $this->input->post('orden_id');
        }
        $ruta_pdf = "uploads/tarjetas/orden_" . $orden_id . ".pdf";
        $pdf = $this->pdf_tarjeta_mensaje($orden_id, FCPATH . $ruta_pdf, 'F', "eternizadas");
        header('Content-Type: application/json');
        echo json_encode(array("error" => !$pdf, "mensaje" => (!$pdf ? 'No se pudo generar la tarjeta' : 'El pdf de la tarjeta se abrir&aacute; en otra ventana'), 'ruta_pdf' => base_url() . $ruta_pdf));
    }

    public function imprimir_tarjeta_masivo() {
        $ordenes_id = $this->input->post('ordenes_id');
        $arr = explode("-", substr($ordenes_id, 1));
        $ids = array();
        foreach ($arr as $a) {
            $ids[] = $a;
        }
        $respuesta = $this->service_ecommerce_orden->impresion_masiva_pdf($ids);
        $ids_impresas = array();
        $pdf = false;
        if ($respuesta) {
            $pdf = $respuesta[0];
            $ids_impresas = $respuesta[1];
            $ruta_pdf = "uploads/tarjetas/ordenes_" . fechaActual("YmdHis") . ".pdf";
            $pdf->Output(FCPATH . $ruta_pdf, 'F');
        }
        header('Content-Type: application/json');
        echo json_encode(array("error" => !$pdf, "mensaje" => (!$pdf ? "No hay impresion por realizar" : "Un solo pdf se va a generar para este batch de impresi&oacute;n"), "ordenes_impresas" => $ids_impresas, "ruta_pdf" => ($pdf ? base_url() . $ruta_pdf : "")));
    }

    public function imprimir_tarjeta_masivo_con_tracking() {
        //cargo la librerias
        require_once('application/libraries/fpdi/src/autoload.php');
        require_once('application/libraries/fpdi/fpdf.php');
        //
        $dir2 = 'uploads/tracking/';
        $ordenes_id = $this->input->post('ordenes_id');
        $arr = explode("-", substr($ordenes_id, 1));
        $ids = array();
        $pdfeliminacion = array();
        $pdf = new Fpdi();
        foreach ($arr as $a) {
            $ids[] = $a;
        }
        foreach ($ids as $idc) {
            $array = explode('_', $idc);
            if (sizeof($array) == 2) {
                $id = $array[0];
                $id_caja = $array[1];
            } else {
                $id = $idc;
                $id_caja = false;
            }
        }
        foreach ($ids as $idc) {

            $array = explode('_', $idc);
            if (sizeof($array) == 2) {
                $orden_id = $array[0];
                $caja_id = $array[1];
                $ruta_pdf = "uploads/tarjetas/caja_" . $caja_id . ".pdf";
            } else {
                $orden_id = $idc;
                $caja_id = false;
                $ruta_pdf = "uploads/tarjetas/caja_" . $orden_id . ".pdf";
            }
            // ruta para el pdf caja
            $this->pdf_tarjeta_mensaje_caja($orden_id, $caja_id, FCPATH . $ruta_pdf, 'F');

            if ($caja_id) {
                $trackingnumber = $this->service_logistica->obtenerTrackingNumberCaja($caja_id);
                if ($trackingnumber) {
                    $nombre_fichero = $dir2 . $trackingnumber->tracking_number . '.pdf';
                    if (file_exists($nombre_fichero)) {
                        $files[] = $ruta_pdf;
                        $pdfeliminacion[] = $caja_id;
                        $files[] = $nombre_fichero;
                    } else {
                        $files[] = $ruta_pdf;
                        $pdfeliminacion[] = $caja_id;
                    }
                } else {
                    $pdfeliminacion[] = $caja_id;
                    $files[] = $ruta_pdf;
                }
            } else {
//                $pdfeliminacion[] = $caja_id;
                $files[] = $ruta_pdf;
            }
            $ids_impresas[] = $orden_id;
        }

        foreach ($files AS $file) {
            $pageCount = $pdf->setSourceFile($file);
            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $pageId = $pdf->ImportPage($pageNo);
                $s = $pdf->getTemplatesize($pageId);
                $pdf->AddPage($s['orientation'], $s);
                $pdf->useImportedPage($pageId);
            }
            $realizado = true;
        }

        $ruta_pdf = "uploads/tarjetas/ordenes_" . fechaActual("YmdHis") . ".pdf";
        $pdf->Output($ruta_pdf, 'F');

        header('Content-Type: application/json');
        echo json_encode(array("error" => !$realizado, "mensaje" => (!$realizado ? "No hay impresion por realizar" : "Un solo pdf se va a generar para este batch de impresi&oacute;n"), "ordenes_impresas" => $ids_impresas, "ruta_pdf" => ($pdf ? base_url() . $ruta_pdf : "")));
        //borro los pdfs generados
        /* foreach ($pdfeliminacion as $eliminacion) {
          unlink('uploads/tarjetas/caja_' . $eliminacion . '.pdf');
          } */
    }

    public function json_imprimir_traking_and_caja() {
        //cargo la librerias
        require_once('application/libraries/fpdi/src/autoload.php');
        require_once('application/libraries/fpdi/fpdf.php');
        //
        $dir2 = 'uploads/tracking/';
        $orden_id = $this->input->post('orden_id');
        $caja_id = $this->input->post('caja_id');
        // ruta para el pdf caja
        $ruta_pdf = "uploads/tarjetas/caja_" . $caja_id . ".pdf";
        $pdf = $this->pdf_tarjeta_mensaje_caja($orden_id, $caja_id, FCPATH . $ruta_pdf, 'F');

        $trackingnumber = $this->service_logistica->obtenerTrackingNumberCaja($caja_id);

        if ($trackingnumber) {
            $nombre_fichero = $dir2 . $trackingnumber->tracking_number . '.pdf';
            if (file_exists($nombre_fichero)) {
                $files = array($ruta_pdf, $nombre_fichero);
                $ruta_pdf_convine = "uploads/tarjetas/caja_" . $caja_id . "_tracking_" . $trackingnumber->tracking_number . ".pdf";
            } else {
                $ruta_pdf_convine = "uploads/tarjetas/caja_" . $caja_id . ".pdf";
                $files = array($ruta_pdf);
            }
        } else {
            $ruta_pdf_convine = "uploads/tarjetas/caja_" . $caja_id . ".pdf";
            $files = array($ruta_pdf);
        }

        $pdf = new Fpdi();
        foreach ($files AS $file) {
            $pageCount = $pdf->setSourceFile($file);
            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $pageId = $pdf->ImportPage($pageNo);
                $s = $pdf->getTemplatesize($pageId);
                $pdf->AddPage($s['orientation'], $s);
                $pdf->useImportedPage($pageId);
            }
        }
        $pdf_convine = $pdf->Output($ruta_pdf_convine, 'F');
        header('Content-Type: application/json');
        echo json_encode(array("error" => !$pdf, "mensaje" => (!$pdf ? 'No se pudo generar la tarjeta' : 'El pdf de la tarjeta se abrir&aacute; en otra ventana'), 'ruta_pdf' => base_url() . $ruta_pdf_convine));
    }

}
