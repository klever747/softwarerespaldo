<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Preparacion extends MY_Controller {

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
    }

    public function resumenPorProductoVariante() {
        $data = array("order_number" => '',
            "texto_busqueda" => '',
            "rango_busqueda" => '');
        $data['tipo_calendario'] = 0;
        $data['store_id'] = 0;
        $data['producto_id'] = 0;
        $data['variante_id'] = 0;
        $data['ordenes'] = array();
        $data['empacado'] = 'N'; //pendientes
        $data['preparado'] = 'N'; //pendientes
        $data['listadoProductos'] = -1;
        $data['preparacion_detalle'] = '';
        $data['sel_store'] = $this->service_produccion->obtenerTiendasSel();
        $data['totales'] = false;
        $data['arr_colores'] = $this->service_produccion->colores();
        if ($this->input->post('btn_buscar') != null) {
            $data['texto_busqueda'] = false;
            $data['order_number'] = $this->input->post('order_number');
            $data['rango_busqueda'] = $this->input->post('rango_busqueda');
            $data['tipo_calendario'] = $this->input->post('tipo_calendario');
            $data['producto_id'] = 0; //$this->input->post('producto_id');
            $data['variante_id'] = 0; //$this->input->post('variante_id');
            $data['listadoProductos'] = -1;
            $data['store_id'] = $this->input->post('store_id');
            $data['empacado'] = $this->input->post('empacado');
            $data['preparado'] = $this->input->post('preparado');
            $colores = $this->input->post('colores');
//            var_dump($colores);
//           error_log(print_r($colores,true));die;
//            $filtroColores = '';
            if (!empty($colores) && sizeof($colores) > 0) {
                foreach ($colores as $color) {
//                $filtroColores .= $color.',';
                    $data['arr_colores'][$color] = 1;
                }
            }
//            if (strlen($filtroColores)>0){
//                $filtroColores = substr($filtroColores,0, strlen($filtroColores)-1);
//            }
//            error_log("COLORES");
//            error_log(print_r($colores,true));
//            error_log(print_r($filtroColores,true));

            if ($data['producto_id'] != 0) {
                $productos = $this->service_preparacion->obtenerDetalleOrdenesProductoVariantes($data['producto_id'], $data['variante_id'], $data['store_id'], $data['tipo_calendario'], $data['rango_busqueda'], $data['preparado']);
            } else {
                $productos = $this->service_preparacion->obtenerOrdenesProductosVariantes($data['store_id'], $data['tipo_calendario'], $data['rango_busqueda'], $data['order_number'], $data['texto_busqueda'], '', '', $data['preparado'], $colores);
                error_log(print_r($productos, true));
            }
            if ($productos) {
                $data['listadoProductos'] = array();
                foreach ($productos as $producto) {
                    $data['listadoProductos'][] = $producto;
                }
            }

            $data['preparacion_detalle'] = $this->load->view('preparacion_detalle.php', $data, true);
            $filtro = array(
                "store_id" => $data['store_id'],
                "tipo_calendario" => $data['tipo_calendario'],
                "rango_busqueda" => $data['rango_busqueda'],
            );
            $data['totales'] = $this->service_ecommerce_orden->obtenerTotales($filtro);
        }

        $this->mostrarVista('preparacion.php', $data);
    }

    public function resumenPorProducto() {
        $data = array("order_number" => '',
            "texto_busqueda" => '',
            "rango_busqueda" => '');
        $data['tipo_calendario'] = 0;
        $data['store_id'] = 1;
        $data['producto_id'] = 0;
        $data['variante_id'] = 0;
        $data['ordenes'] = array();
        $data['empacado'] = 'N'; //pendientes
        $data['preparado'] = 'N'; //pendientes
//        $data['listadoProductos'] = -1;
        $data['preparacion_detalle'] = '';
//        if ($this->input->post('btn_buscar') != null) {
//            $data['preparacion_detalle'] = $this->preparacion_detalle();
//        }
//        $data['order_number'] = $order_number;
//        $data['texto_busqueda'] = $texto_busqueda;
//        $data['rango_busqueda'] = $rango_busqueda;
//        $data['tipo_calendario'] = $tipo_calendario;
        $data['sel_store'] = $this->service_produccion->obtenerTiendasSel();
        $this->mostrarVista('preparacion.php', $data);
    }

    public function preparacion_detalle() {
        $data['texto_busqueda'] = false;
        $data['order_number'] = $this->input->post('order_number');
        $data['rango_busqueda'] = $this->input->post('rango_busqueda');
        $data['tipo_calendario'] = $this->input->post('tipo_calendario');
        $data['producto_id'] = $this->input->post('producto_id');
        $data['variante_id'] = $this->input->post('variante_id');
        $data['listadoProductos'] = -1;
        $data['store_id'] = $this->input->post('store_id');
        $data['empacado'] = $this->input->post('empacado');
        $data['preparado'] = $this->input->post('preparado');

//            if ($this->input->post('order_number') != null) {
//                $order_number = $this->input->post('order_number');
//            }
//            if ($this->input->post('busqueda') != null) {
//                $texto_busqueda = $this->input->post('busqueda');
//            }


        if ($data['producto_id'] != 0) {
            $productos = $this->service_preparacion->obtenerDetalleOrdenesProductoVariantes($data['producto_id'], $data['variante_id'], $data['store_id'], $data['tipo_calendario'], $data['rango_busqueda'], $data['preparado']);
//        } else {
//            $productos = $this->service_preparacion->obtenerOrdenesProductosVariantes($data['store_id'], $data['tipo_calendario'], $data['rango_busqueda'], $data['order_number'], $data['texto_busqueda'], '', '', $data['preparado']);
        }
        if ($productos) {
            $data['listadoProductos'] = array();
            foreach ($productos as $producto) {
                $data['listadoProductos'][] = $producto;
            }
        }

        $detalle = $this->load->view('preparacion_detalle.php', $data, true);

        $respuesta = array("error" => false, "detalle" => $detalle, "mensaje" => "Busqueda actualizada");
        header('Content-Type: application/json');
        echo json_encode($respuesta);
    }

    public function detalle_variante() {
        $variante_id = $this->input->post('id');
        $detalle = "as dasd asdasdasda sdasda sdasda dasdas dasd";
        header('Content-Type: application/json');
        echo json_encode(array("error" => !$detalle, "detalle" => $detalle,));
    }

    public function resumenXLS() {
        $data['rango_busqueda'] = $this->input->get('rango_busqueda');
        $data['tipo_calendario'] = $this->input->get('tipo_calendario');
        $data['store_id'] = $this->input->get('store_id');
        $data['empacado'] = $this->input->get('empacado');
        $data['preparado'] = $this->input->get('preparado');
        $fecha = explode("-", $data['rango_busqueda']);
        $filename = trim($fecha[0]) . "_" . $data['store_id'] . "_" . $data['tipo_calendario'] . "_" . $data['empacado'] . "_" . $data['preparado'] . "_" . fechaActual('YmdHis') . ".xls";

        header("Pragma: public");
        header("Expires: 0");
        header("Content-type: application/x-msdownload");
        header("Content-Disposition: attachment; filename=$filename");
        header("Pragma: no-cache");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");


        $data['listadoProductos'] = array();
        $productos_existentes = array();
        $productos = $this->service_preparacion->obtenerOrdenesProductosVariantes($data['store_id'], $data['tipo_calendario'], $data['rango_busqueda'], '', '', '', '', $data['preparado']);
        if ($productos) {
            foreach ($productos as $producto) {
                if (!array_key_exists($producto->producto_id, $productos_existentes)) {
                    $productos_existentes[$producto->producto_id] = 1;
                    $productoDetalle = $this->service_preparacion->obtenerDetalleOrdenesProductoVariantes($producto->producto_id, $producto->variante_id, $data['store_id'], $data['tipo_calendario'], $data['rango_busqueda'], $data['preparado']);
                    if ($productoDetalle) {
                        foreach ($productoDetalle as $prod) {
                            $data['listadoProductos'][] = $prod;
                        }
                    }
                }
            }
        }
        error_log(print_r($data, true));
        $detalle = $this->load->view('preparacion_detalle_xls.php', $data, true);
//
        $ruta_pdf = FCPATH . "uploads/xls/preparacion/";
        file_put_contents($ruta_pdf . $filename, $detalle);
        echo $detalle;
    }

    public function resumenPDF() {
        
    }

    public function actualizarTotalPreparadosVariante() {
        $data['variante_id'] = $this->input->post('variante_id');
        $cantidad_preparada = $this->input->post('valor_preparado');
        $data['rango_busqueda'] = $this->input->post('rango_busqueda');
        $data['tipo_calendario'] = $this->input->post('tipo_calendario');
        $data['store_id'] = $this->input->post('store_id');

        $variante = $this->service_ecommerce_producto->obtenerProductoVariante($data['variante_id']);
        //vamos a obtener todas las ordenes que deben ser preparadas de esa variante_id
//        $productos = $this->service_preparacion->obtenerOrdenesProductosVariantes($data['store_id'], $data['tipo_calendario'], $data['rango_busqueda'], '', '', null, $data['variante_id'], 'T');
//        print_r($productos);
        $resultado = 'error';
        if ($variante) {

            List($divisor, $unidad, $assemble) = obtenerPresentacion($variante->sku);

//            $docena = ($variante->cantidad % 12 === 0);
            //vamos a obtener todas las ordenes que contienen esa variante_id
            $ordenes = $this->service_preparacion->obtenerDetalleOrdenesProductoVariantes($variante->producto_id, $data['variante_id'], $data['store_id'], $data['tipo_calendario'], $data['rango_busqueda'], 'N', true);

            if ($ordenes) {
                foreach ($ordenes as $orden) {
                    if (array_key_exists('variantes', $orden)) {
                        $ordenesArr = ($orden['variantes'][0]->ordenes);
                        foreach ($ordenesArr as $ord) {
                            $cantidad_de_la_orden = ($ord->orden_item_cantidad * $ord->variante_cantidad) / $divisor;
//                            if ($docena) {
//                                $cantidad_de_la_orden = $cantidad_de_la_orden / 12; //para saber cuantas docenas vamos a disminuir
//                            }
                            error_log("ORDEN " . print_r($ord, true));
                            error_log("CANTIDAD DE LA ORDEN " . print_r($cantidad_de_la_orden, true));
                            error_log("CANTIDAD DISPONIBLE " . print_r($cantidad_preparada, true));
                            if ($cantidad_preparada >= $cantidad_de_la_orden) {
                                //a ese orden_item_id lo vamos a marcar como preparado
                                $accion = $this->service_manufactura->item_bonchado($ord->orden_item_id);
                                error_log("Accion es " . print_r($accion, true));
                                if ($accion) {
                                    $cantidad_preparada = $cantidad_preparada - $cantidad_de_la_orden;
                                }
                            }
                        }
                    }
                }
            }

            $productos = $this->service_preparacion->obtenerOrdenesProductosVariantes($data['store_id'], $data['tipo_calendario'], $data['rango_busqueda'], '', '', null, $data['variante_id'], 'T');

            $resultado = 'error';
            if ($productos) {
                $item = $productos[0];


                $total = $item->orden_item_variante_cantidad * $item->variante_cantidad;
                $totalPreparados = $item->orden_item_variante_cantidad_preparado * $item->variante_cantidad;
                $totalPendientes = $total - $totalPreparados;



                $resultado = '';
                $resultado .= '<div class="col-5 offset-1 text-left border-dark border-right border-bottom-0">' . $item->variante_titulo . ' (' . $item->variante_sku . ') </div>
                    <div class="col-1 text-right border-dark border-right border-bottom-0">' . $item->largo_cm . 'cm.</div>
                    <div class="col-1 text-right border-dark border-right border-bottom-0">' . $unidad . '</div>
                    <div class="col-1 text-right border-dark border-right border-bottom-0">' . ($total / $divisor) . ' &nbsp;</div>
                    <div class="col-2 text-right border-dark border-right border-bottom-0">';

                if ($totalPendientes != 0) {
                    $arr = array(
                        "id" => "restante_variante_" . $item->variante_id,
                        "name" => "restante_variante_" . $item->variante_id,
                        "value" => $totalPendientes / $divisor,
                        "tipo" => 'number',
                        "max" => $totalPendientes / $divisor,
                        "step" => $divisor,
                        "data-varante-id" => $item->variante_id,
                        "clase" => "col-4 restante_variante",
                    );
                    $resultado .= item_input($arr);
                    $resultado .= '&nbsp;<button type = "button" class="btn btn-primary btn-accion-variante h-100" id="btn-guardar-' . $item->variante_id . '" data-variante-id="' . $item->variante_id . '"><i class="fas fa-check fa-xs"></i></button>';
                }
                $resultado .= '

                        </div>
                        <div class="col-1 text-right border-bottom-0">
                            ' . ($totalPendientes / $divisor) . ' &nbsp;
                        </div>
                        ';
            }
        }
        header('Content-Type: application/json');
        echo json_encode(array("error" => strlen($resultado) < 6, "detalle" => $resultado, "producto_variante_id" => $data['variante_id']));
    }

}
