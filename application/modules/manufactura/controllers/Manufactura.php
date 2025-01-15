<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Manufactura extends MY_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model("ecommerce/service_ecommerce");
        $this->load->model("ecommerce/service_ecommerce_cliente");
        $this->load->model("ecommerce/service_ecommerce_orden");
        $this->load->model("ecommerce/service_ecommerce_producto");
        $this->load->model("ecommerce/service_ecommerce_logistica");
        $this->load->model("ecommerce/service_ecommerce_formula");
        $this->load->model("produccion/service_produccion");
        $this->load->model("produccion/service_preparacion");
        $this->load->model("ecommerce/service_ecommerce_orden");
        $this->load->model("manufactura/service_manufactura");
        $this->load->model("produccion/service_master_shipping");
    }

    public function bonchado() {
        $this->listado(PANTALLA_PREPARACION);
    }

    public function vestido() {
        $this->listado(PANTALLA_TERMINACION);
    }

    public function listado($perfil = false) {
        $data['perfil'] = (!$perfil) ? ($this->input->post('perfil') != null ? $this->input->post('perfil') : PANTALLA_MANUFACTURA) : $perfil;
        if (!$data['perfil']) {
            return print_r("Error, perfil no definido");
        } else {
            switch ($data['perfil']) {
                case PANTALLA_PREPARACION:
                    $data['url_busqueda'] = "manufactura/bonchado";
                    break;
                case PANTALLA_TERMINACION:
                    $data['url_busqueda'] = "manufactura/vestido";
                    break;
                case PANTALLA_MANUFACTURA:
                    $data['url_busqueda'] = "manufactura/listado";
                    break;
                default:
                    break;
            }
        }
        $data['store_id'] = $data['tipo_calendario'] = $data['producto_id'] = $data['variante_id'] = 0;
        $data['rango_busqueda'] = '';
        $data['empacado'] = 'T';
        $data['bonchado'] = $data['vestido'] = 'N';
        $data['listadoProductos'] = $data['totales'] = false;
        $data['total_shipping'] = false;
        $data['listadoProductosId'] = array();
        $data['colores'] = $this->service_produccion->colores();
        $data['finca_id'] = 0;
        $data['session_finca'] = $this->session->userFincaId;
        $listadoOrdenes = '';
        $detalle = '';
        $id_cajas_orden = '';
        if ($this->input->post('btn_buscar') != null) {
            $data['store_id'] = $this->input->post('store_id');
            $data['rango_busqueda'] = $this->input->post('rango_busqueda');
            $data['tipo_calendario'] = $this->input->post('tipo_calendario');
            $data['finca_id'] = $this->input->post('finca_id');
            $data['bonchado'] = $this->input->post('bonchado');
            $data['vestido'] = $this->input->post('vestido');

            $data['empacado'] = $this->input->post('empacado');

            if ($perfil == PANTALLA_PREPARACION) {
                unset($data["vestido"]);
            }
            if ($perfil == PANTALLA_TERMINACION) {
                unset($data["bonchado"]);
            }

            $colores = $this->input->post('colores');
            $data['colores'] = analizarColores($data['colores'], $colores);

            $data['listadoProductos'] = $this->mostrarListadoCompleto($data, true);
            $filtro = array(
                "store_id" => $data['store_id'],
                "rango_busqueda" => $data['rango_busqueda'],
                "tipo_calendario" => $data['tipo_calendario'],
                "finca_id" => $data['finca_id'],
                "session_finca" => $data['session_finca'],
            );
            $data['totales'] = $this->service_ecommerce_orden->obtenerTotales($filtro, false);
            $data['total_shipping'] = $this->service_master_shipping->obtener_master_shipping_totales($filtro);
            List($listadoOrdenes, $cuantasOrdenes) = $this->service_ecommerce_orden->obtenerOrdenesporCaja($filtro);
            if ($listadoOrdenes) {
                foreach ($listadoOrdenes as $ordenes) {
                    $id_cajas_orden = $id_cajas_orden . "-" . $ordenes->orden_id . "_" . $ordenes->id;
                }
            }
        }

        $data['detalle'] = $detalle;
        $data['listadoordenes'] = $id_cajas_orden;
        $data['sel_store'] = $this->service_produccion->obtenerTiendasSel();
        $data['sel_finca'] = $this->service_ecommerce_orden->obtenerFincaSel();

        $this->mostrarVista('manufactura/listado.php', $data);
    }

    private function mostrarListadoCompleto($filtro, $enPantalla = true) {
        $listadoProductos = $this->service_manufactura->obtenerListado($filtro);

        $detalle = array();
        if ($listadoProductos) {
            foreach ($listadoProductos as $prod) {
                $obj = new stdClass();
                $obj->producto_titulo = $prod->producto_titulo;
                $obj->producto_id = $prod->producto_id;
                $obj->data = $this->mostrarDetalleProducto($filtro, $prod->producto_id, $enPantalla);
                $detalle[] = $obj;
            }
        }
//error_log(print_r($detalle,true));
        return $detalle;
    }

    public function json_mostrarProductoDetalle() {
        $filtro = $this->input->post('filtro');
        $producto_id = $this->input->post('producto_id');
        $detalle = $this->mostrarDetalleProducto($filtro, $producto_id);
        $respuesta = array("error" => false, "producto_id" => $producto_id, "detalle" => $detalle, "mensaje" => "Busqueda actualizada");
        header('Content-Type: application/json');
        echo json_encode($respuesta);
    }
    private function mostrarDetalleProducto($filtro, $producto_id, $enPantalla = true) {
        $producto = $this->service_ecommerce_producto->obtenerProducto($producto_id);
        $listadoProductoVariantes = $this->service_manufactura->obtenerListadoPorProducto($filtro, $producto_id);
        $det_variante = false;
        if ($listadoProductoVariantes) {
            $det_variante = array();
            foreach ($listadoProductoVariantes as $item) {
                $detalle_variante = $this->mostrarDetalleVariante($filtro, $item->variante_id, $enPantalla);
                if ($detalle_variante != '') {
                    $det_variante[$item->variante_id] = $detalle_variante;
                }
            }
        }
        $detalle = $this->load->view('detalle_producto.php', array('producto' => $producto, 'variantes_det' => $det_variante, 'bonchado' => (array_key_exists('bonchado', $filtro) ? $filtro['bonchado'] : false), 'vestido' => (array_key_exists('vestido', $filtro) ? $filtro['vestido'] : false), 'enpantalla' => $enPantalla), true);
        return $detalle;
    }

    public function json_mostrarVarianteDetalle() {
        $filtro = $this->input->post('filtro');
        $filtro['session_finca'] = $this->session->userFincaId;
        $variante_id = $this->input->post('variante_id');
        $detalle = $this->mostrarDetalleVariante($filtro, $variante_id);
        $respuesta = array("error" => false, "variante_id" => $variante_id, "detalle" => $detalle, "mensaje" => "Busqueda actualizada");
        header('Content-Type: application/json');
        echo json_encode($respuesta);
    }

    private function mostrarDetalleVariante($filtro, $variante_id, $enPantalla = true) {
        $variante = $this->service_ecommerce_producto->obtenerProductoVariante($variante_id);
        $det = $this->service_manufactura->obtenerListadoPorVariante($filtro, $variante_id);
//        error_log(print_r($filtro, true));
        if ($det) {
//            error_log(print_r($det, true));
            $det_ordenes = $this->mostrarDetalleOrdenesVariantes($filtro, $variante_id, $variante, $enPantalla);
//            $det_ordenes = '';
//            error_log(print_r($filtro, true));
            $detalle = $this->load->view('detalle_variante.php', array('variante' => $variante, 'det' => $det, 'detalle_ordenes' => $det_ordenes, 'perfil' => $filtro['perfil'], 'bonchado' => (array_key_exists('bonchado', $filtro) ? $filtro['bonchado'] : false), 'vestido' => (array_key_exists('vestido', $filtro) ? $filtro['vestido'] : false), 'enpantalla' => $enPantalla), true);
        } else {
            $detalle = "";
        }
        return $detalle;
    }

    public function json_mostrarOrdenesDetalle() {
        $filtro = $this->input->post('filtro');
        $variante_id = $this->input->post('variante_id');
        $detalle = $this->mostrarDetalleOrdenesVariantes($filtro, $variante_id);
        $respuesta = array("error" => false, "variante_id" => $variante_id, "detalle" => $detalle, "mensaje" => "Busqueda actualizada");
        header('Content-Type: application/json');
        echo json_encode($respuesta);
    }

    private function mostrarDetalleOrdenesVariantes($filtro, $variante_id, $variante = false, $enPantalla = true) {
        if (!$variante) {
            $variante = $this->service_ecommerce_producto->obtenerProductoVariante($variante_id);
        }
//        $det = $this->service_manufactura->obtenerListadoOrdenesPorVariante($filtro, $variante_id);
        $detalle_por_wrap = $this->service_manufactura->obtenerOrdenesWrap($filtro, $variante_id);
        $det = $this->calculoTotales($detalle_por_wrap);
        $det['variante'] = $variante;
        $det['perfil'] = $filtro['perfil'];
        $det['bonchado'] = array_key_exists('bonchado', $filtro) ? $filtro['bonchado'] : false;
        $det['vestido'] = array_key_exists('vestido', $filtro) ? $filtro['vestido'] : false;
        $det['enpantalla'] = $enPantalla;
        $detalle = $this->load->view('detalle_variante_ordenes.php', $det, true);
        return $detalle;
    }

    public function json_mostrarActualizacionTotalesVariante() {
        $filtro = $this->input->post('filtro');
        $variante_id = $this->input->post('variante_id');

        $variante = $this->service_ecommerce_producto->obtenerProductoVariante($variante_id);
        $det = $this->service_manufactura->obtenerOrdenesWrap($filtro, $variante_id);
        $det = $this->calculoTotales($det);
        $det['variante'] = $variante;
        $det['presentacion'] = obtenerPresentacion($variante->sku);
        $det['perfil'] = $filtro['perfil'];
        $detalle = $this->load->view('actualizacion_totales.php', $det, true);
        $respuesta = array("error" => false, "variante_id" => $variante_id, "detalle" => $detalle, "mensaje" => "Valores actualizados");
        header('Content-Type: application/json');
        echo json_encode($respuesta);
    }

    private function calculoTotales($detalle_wrap) {
        $arrOrdenes = $arrOrdenesLuxury = $arrOrdenesStandard = $arrOrdenesSinWrap = array();
        $totalItemsPedidos = $totalLuxury = $totalStandard = $totalSin = 0;
        $totalItemsPedidosB = $totalLuxuryB = $totalStandardB = $totalSinB = 0;
        $totalItemsPedidosV = $totalLuxuryV = $totalStandardV = $totalSinV = 0;
        if ($detalle_wrap) {

            foreach ($detalle_wrap as $wrap) {
                $orden_alias = $wrap->alias . "_" . (isset($wrap->referencia_order_number) ? $wrap->referencia_order_number : $wrap->id);
                $arrOrdenes[$wrap->id] = $orden_alias;
                $totalItemsPedidos += $wrap->cantidad;

                if ($wrap->bonchado == 'S') {
                    $totalItemsPedidosB += $wrap->cantidad;
                }
                if ($wrap->vestido == 'S') {
                    $totalItemsPedidosV += $wrap->cantidad;
                }
                switch ($wrap->tipo_wrap) {
                    case 1://luxury
                        $arrOrdenesLuxury[$wrap->id] = $orden_alias;
                        $totalLuxury += $wrap->cantidad;
                        if ($wrap->bonchado == 'S') {
                            $totalLuxuryB += $wrap->cantidad;
                        }
                        if ($wrap->vestido == 'S') {
                            $totalLuxuryV += $wrap->cantidad;
                        }
                        break;
                    case 0://standard
                        $arrOrdenesStandard[$wrap->id] = $orden_alias;
                        $totalStandard += $wrap->cantidad;
                        if ($wrap->bonchado == 'S') {
                            $totalStandardB += $wrap->cantidad;
                        }
                        if ($wrap->vestido == 'S') {
                            $totalStandardV += $wrap->cantidad;
                        }
                        break;
                    default://sin wrap
                        $arrOrdenesSinWrap[$wrap->id] = $orden_alias;
                        $totalSin += $wrap->cantidad;
                        if ($wrap->bonchado == 'S') {
                            $totalSinB += $wrap->cantidad;
                        }
                        if ($wrap->vestido == 'S') {
                            $totalSinV += $wrap->cantidad;
                        }
                        break;
                }
            }
        }

        return array("arrOrdenes" => $arrOrdenes,
            "arrOrdenesLuxury" => $arrOrdenesLuxury,
            "arrOrdenesStandard" => $arrOrdenesStandard,
            "arrOrdenesSinWrap" => $arrOrdenesSinWrap,
            "totalItemsPedidos" => $totalItemsPedidos,
            "totalLuxury" => $totalLuxury,
            "totalStandard" => $totalStandard,
            "totalSin" => $totalSin,
            "totalItemsPedidosB" => $totalItemsPedidosB,
            "totalLuxuryB" => $totalLuxuryB,
            "totalStandardB" => $totalStandardB,
            "totalSinB" => $totalSinB,
            "totalItemsPedidosV" => $totalItemsPedidosV,
            "totalLuxuryV" => $totalLuxuryV,
            "totalStandardV" => $totalStandardV,
            "totalSinV" => $totalSinV);
    }

    public function json_actualizacionTotalesVariante() {
        $filtro = $this->input->post('filtro');
        $variante_id = $this->input->post('variante_id');
        $variante = $this->service_ecommerce_producto->obtenerProductoVariante($variante_id);
        $ingresoB = $this->input->post('b');
        $ingresoL = $this->input->post('l');
        $ingresoS = $this->input->post('s');
        $ingresoN = $this->input->post('n');

        List($actualizados, $detalle) = $this->service_manufactura->actualizacionMasiva($variante, $filtro, $variante, $ingresoB, $ingresoL, $ingresoS, $ingresoN);
      
        if (!$actualizados) {
            $expl = '';

            if (($ingresoB > 0) && ($detalle['ib'] >= 0)) {
                $expl .= "Se procesaron " . ($detalle['ib']) . " de " . $ingresoB . " Bonchado ingresado | ";
            }
            if (($ingresoL > 0) && ($detalle['il'] >= 0)) {
                $expl .= "Se procesaron " . ($detalle['il']) . " de " . $ingresoL . " Vestido Luxury ingresado | ";
            }
            if (($ingresoS > 0) && ($detalle['is'] >= 0)) {
                $expl .= "Se procesaron " . ($detalle['is']) . " de " . $ingresoS . " Vestido Standard ingresado | ";
            }
            if (($ingresoN > 0) && ($detalle['in'] >= 0)) {
                $expl .= "Se procesaron " . ($detalle['in']) . " de " . $ingresoN . " Vestido Sin Wrap ingresado |";
            }
        }
        $respuesta = array("error" => !$actualizados, "actualizados" => $actualizados, "variante_id" => $variante_id, "mensaje" => (!$actualizados ? $expl : "Valores actualizados"));
        header('Content-Type: application/json');
        echo json_encode($respuesta);
    }

    public function exportarExcel() {
        $filtro = json_decode($this->input->get('filtro'), true);

        $fecha = explode("-", $filtro['rango_busqueda']);
        $filename = trim($fecha[0]) . "_" . $filtro['store_id'] . "_" . $filtro['tipo_calendario'] . "_" . (isset($filtro['bonchado']) ? "b" . $filtro['bonchado'] : "") . "_" . (isset($filtro['vestido']) ? "v" . $filtro['vestido'] : "") . "_";
        if (array_key_exists('colores', $filtro)) {
            foreach ($filtro['colores'] as $color => $valor) {
                if ($valor) {
                    $filename .= $color;
                }
            }
        }

        $filename .= "_" . fechaActual('YmdHis') . ".xls";

        $listadoProductos = $this->mostrarListadoCompleto($filtro, false);

        header("Pragma: public");
        header("Expires: 0");
        header("Content-type: application/x-msdownload");
        header("Content-Disposition: attachment; filename=$filename");
        header("Pragma: no-cache");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");

        $detalle = '';
        foreach ($listadoProductos as $prod) {
            $detalle .= $prod->data;
        }

        $ruta_pdf = FCPATH . "uploads/xls/preparacion/";
        file_put_contents($ruta_pdf . $filename, $detalle);
        echo $detalle;
    }

    public function bonchado_marcar() {
        $orden_item_id = $this->input->post('orden_item_id');
        $accion = $this->service_manufactura->item_bonchado($orden_item_id);
        $respuesta = array("error" => !$accion ? true : false, "respuesta" => $accion, "mensaje" => !$accion ? "Problemas en la ejecuci&oacute;n" : "Item Marcado como Bonchado");
        header('Content-Type: application/json');
        echo json_encode($respuesta);
    }

    public function bonchado_desmarcar() {
        $orden_item_id = $this->input->post('orden_item_id');

        $orden_item = $this->service_ecommerce_orden->obtenerOrdenItem($orden_item_id);
        if ($orden_item->terminado == 'N') {
            $accion = $this->service_manufactura->item_desbonchado($orden_item_id);
            $respuesta = array("error" => !$accion ? true : false, "respuesta" => $accion, "mensaje" => !$accion ? "Problemas en la ejecuci&oacute;n" : "Item Desmarcado como Preparado", "producto_id" => $orden_item->producto_id, "variante_id" => $orden_item->variante_id);
        } else {
            $respuesta = array("error" => true, "respuesta" => "error", "mensaje" => "No puede desmarcar un item que ya está vestido");
        }

        header('Content-Type: application/json');
        echo json_encode($respuesta);
    }

    public function vestido_marcar() {
        $orden_item_id = $this->input->post('orden_item_id');
        $orden_item = $this->service_ecommerce_orden->obtenerOrdenItem($orden_item_id);
        if ($orden_item->preparado == 'S') {
            $accion = $this->service_manufactura->item_vestido($orden_item_id);
            $respuesta = array("error" => !$accion ? true : false, "respuesta" => $accion, "mensaje" => !$accion ? "Problemas en la ejecuci&oacute;n" : "Item Marcado como Vestido", "variante_id" => $orden_item->variante_id);
        } else {
            $accion = $this->service_manufactura->item_vestido($orden_item_id, true);
            $respuesta = array("error" => !$accion ? true : false, "respuesta" => $accion, "mensaje" => !$accion ? "Problemas en la ejecuci&oacute;n" : "Item Marcado como Vestido y Bonchado", "variante_id" => $orden_item->variante_id);
        }

        header('Content-Type: application/json');
        echo json_encode($respuesta);
    }

    public function vestido_desmarcar() {
        $orden_item_id = $this->input->post('orden_item_id');
        $orden_item = $this->service_ecommerce_orden->obtenerOrdenItem($orden_item_id);
        $accion = $this->service_manufactura->item_desvestido($orden_item_id);
        $respuesta = array("error" => !$accion ? true : false, "respuesta" => $accion, "mensaje" => !$accion ? "Problemas en la ejecuci&oacute;n" : "Item Desmarcado como Vestido", "variante_id" => $orden_item->variante_id);

        header('Content-Type: application/json');
        echo json_encode($respuesta);
    }
    /*****Imprimir ordenes con datos de sus items** */
    public function imprimir_data_items($variante_id=false){
        $orden_item_id = $this->input->post('orden_item_id');
        $producto_id = $this->input->post('producto_id');
        $orden_id = $this->input->post('orden_id');
        if(!$variante_id){
            $variante_id = $this->input->post('variante_id');
        }
        $filtro = $this->input->post('filtro');
        $orden_item = array();
        $variante = $this->service_ecommerce_producto->obtenerProductoVariante($variante_id);
        $det = $this->service_manufactura->obtenerOrdenesWrap($filtro, $variante_id);
        $orden_item["items"] = $this->service_ecommerce_orden->obtenerOrdenItem($orden_item_id);
        $orden_item["variante"] = $variante;
        foreach ($det as $key => $wrap) {
            if($wrap->id == $orden_id){
                $orden_item["wrap"]= $wrap->tipo_wrap;
                $orden_item["wrap-valor"]= $wrap->valor;
            }
        }
        $finca_id = $filtro["finca_id"];
        $session_finca = $filtro["session_finca"];
        $ruta_pdf = "uploads/tarjetas/orden_" . $orden_id . ".pdf";
        
        $pdf = $this->pdf_tarjeta($orden_id, FCPATH . $ruta_pdf, 'F', TARJETA_NORMALES, $session_finca, $finca_id,$orden_item);
        header('Content-Type: application/json');
        echo json_encode(array("error" => !$pdf, "mensaje" => (!$pdf ? 'No se pudo generar la tarjeta' : 'El pdf de la tarjeta se abrir&aacute; en otra ventana'), 'ruta_pdf' => base_url() . $ruta_pdf));
    }
    public function pdf_tarjeta($orden_id, $output_path, $output_format = 'FI', $tipo = "normal", $session_finca = false, $finca_id = false,$orden_item) {
        if (isset($session_finca) && !empty($session_finca)) {
            $pdf = $this->service_manufactura->generar_tarjetas_orden($orden_id, false, $tipo, $session_finca, $finca_id,$orden_item);
        } else {
            $pdf = $this->service_manufactura->generar_tarjetas_orden($orden_id, false, $tipo,$orden_item);
        }
        if (!$pdf) {
            error_log("No hay pdf");
            return false;
        }
        $pdf->Output($output_path, $output_format);
        return true;
    }
}
