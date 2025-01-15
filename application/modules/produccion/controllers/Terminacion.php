<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Terminacion extends MY_Controller {

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
        $this->load->model("produccion/service_terminacion");
    }

    public function resumenPorWrap() {
        $data = array("order_number" => '',
            "texto_busqueda" => '',
            "rango_busqueda" => '');
        $data['tipo_calendario'] = 0;
        $data['store_id'] = 1;
        $data['producto_id'] = 0;
        $data['variante_id'] = 0;
        $data['ordenes'] = array();
//        $data['empacado'] = 'S'; //pendientes
        $data['preparado'] = 'S'; //pendientes
        $data['terminado'] = 'N'; //pendientes
        $data['listadoProductos'] = -1;
        $data['preparacion_detalle'] = '';
        $data['sel_store'] = $this->service_produccion->obtenerTiendasSel();
        $data['totales'] = false;
        if ($this->input->post('btn_buscar') != null) {
            $data['texto_busqueda'] = false;
            $data['order_number'] = $this->input->post('order_number');
            $data['rango_busqueda'] = $this->input->post('rango_busqueda');
            $data['tipo_calendario'] = $this->input->post('tipo_calendario');
            $data['producto_id'] = 0; //$this->input->post('producto_id');
            $data['variante_id'] = 0; //$this->input->post('variante_id');
            $data['listadoProductos'] = -1;
            $data['store_id'] = $this->input->post('store_id');
//            $data['empacado'] = $this->input->post('empacado');
            $data['preparado'] = $this->input->post('preparado');
            $data['terminado'] = $this->input->post('terminado');

            if ($data['preparado'] == 'N') {
                $data['terminado'] = 'T';
            }
            $data['productosConWrapLuxury'] = $data['productosConWrapNormal'] = $data['productosSinWrap'] = false;
            $data['productosConWrapLuxury'] = $this->service_terminacion->obtenerProductosVariantesPorWrap($data['store_id'], $data['tipo_calendario'], $data['rango_busqueda'], 'Luxury'); //, 'L');            
            $data['productosConWrapNormal'] = $this->service_terminacion->obtenerProductosVariantesPorWrap($data['store_id'], $data['tipo_calendario'], $data['rango_busqueda'], 'Standard');
            $data['productosSinWrap'] = $this->service_terminacion->obtenerProductosVariantesPorWrap($data['store_id'], $data['tipo_calendario'], $data['rango_busqueda'], 'NO');

            $data['preparacion_detalle_wrap_luxury'] = $data['preparacion_detalle_wrap_normal'] = $data['preparacion_detalle_sin_wrap'] = 'Sin resultados';
//            error_log($data['productosConWrapLuxury'], true);
            if ($data['productosConWrapLuxury']) {
                $data['listadoProductos'] = array();
                foreach ($data['productosConWrapLuxury'] as $productoList) {
                    $productos = $this->service_terminacion->obtenerDetalleOrdenesProductoVariantes($productoList->producto_id, $productoList->variante_id, $data['store_id'], $data['tipo_calendario'], $data['rango_busqueda'], $data['preparado'], $data['terminado'], 'LUXURY');
//                    print_r($productos); die;
                    $data['listadoProductos'][$productoList->producto_id]['producto'] = $productoList;
                    if ($productos) {
//                        $data['listadoProductos'] = array();
                        foreach ($productos as $producto) {
                            $data['listadoProductos'][$productoList->producto_id]['variantes'][$productoList->variante_id] = $producto['variantes'];
                        }
                    }
                }
                $data['producto_id'] = $productoList->producto_id;
                $data['wrap'] = 'luxury';
                $data['preparacion_detalle_wrap_luxury'] = $this->load->view('terminacion_detalle.php', $data, true);
            }

            if ($data['productosConWrapNormal']) {
                $data['listadoProductos'] = array();
                foreach ($data['productosConWrapNormal'] as $productoList) {
                    $productos = $this->service_terminacion->obtenerDetalleOrdenesProductoVariantes($productoList->producto_id, $productoList->variante_id, $data['store_id'], $data['tipo_calendario'], $data['rango_busqueda'], $data['preparado'], $data['terminado'], 'STANDARD');
                    $data['listadoProductos'][$productoList->producto_id]['producto'] = $productoList;
                    if ($productos) {
//                        $data['listadoProductos'] = array();
                        foreach ($productos as $producto) {
                            $data['listadoProductos'][$productoList->producto_id]['variantes'][$productoList->variante_id] = $producto['variantes'];
                        }
                    }
                }
                $data['producto_id'] = $productoList->producto_id;
                $data['wrap'] = 'standard';
                $data['preparacion_detalle_wrap_normal'] = $this->load->view('terminacion_detalle.php', $data, true);
            }

            if ($data['productosSinWrap']) {
                $data['listadoProductos'] = array();
                foreach ($data['productosSinWrap'] as $productoList) {
                    $productos = $this->service_terminacion->obtenerDetalleOrdenesProductoVariantes($productoList->producto_id, $productoList->variante_id, $data['store_id'], $data['tipo_calendario'], $data['rango_busqueda'], $data['preparado'], $data['terminado'], 'NO');
                    $data['listadoProductos'][$productoList->producto_id]['producto'] = $productoList;
                    if ($productos) {
//                        $data['listadoProductos'] = array();
                        foreach ($productos as $producto) {
                            $data['listadoProductos'][$productoList->producto_id]['variantes'][$productoList->variante_id] = $producto['variantes'];
                        }
                    }
                }
                $data['producto_id'] = $productoList->producto_id;
                $data['wrap'] = 'no';
                $data['preparacion_detalle_sin_wrap'] = $this->load->view('terminacion_detalle.php', $data, true);
            }

            $filtro = array(
                "store_id" => $data['store_id'],
                "tipo_calendario" => $data['tipo_calendario'],
                "rango_busqueda" => $data['rango_busqueda'],
            );
            $data['totales'] = $this->service_ecommerce_orden->obtenerTotales($filtro);
        }

        $this->mostrarVista('terminacion.php', $data);
    }

    public function actualizarTotalTerminadosVariante() {
        $data['variante_id'] = $this->input->post('variante_id');
        $cantidad_terminada = $this->input->post('valor_terminado');
        $data['rango_busqueda'] = $this->input->post('rango_busqueda');
        $data['tipo_calendario'] = $this->input->post('tipo_calendario');
        $data['store_id'] = $this->input->post('store_id');
        $data['wrap'] = $this->input->post('wrap');
        $data['preparado'] = $this->input->post('preparado');
        $data['terminado'] = $this->input->post('terminado');
        $wrap = $data['wrap'];

        $variante = $this->service_ecommerce_producto->obtenerProductoVariante($data['variante_id']);
        //vamos a obtener todas las ordenes que deben ser terminadas de esa variante_id
//        $productos = $this->service_preparacion->obtenerOrdenesProductosVariantes($data['store_id'], $data['tipo_calendario'], $data['rango_busqueda'], '', '', null, $data['variante_id'], 'T');
//        print_r($productos);
        $resultado = 'error';
        if ($variante) {

            List($divisor, $unidad, $assemble) = obtenerPresentacion($variante->sku);

//            if ($data['wrap'] == 'sin_wrap') {
//                $data['wrap'] = 'NO';
//            }
            if ($cantidad_terminada == 0) {
                
            } else if ($cantidad_terminada > 0) {
                //vamos a obtener todas las ordenes que contienen esa variante_id
                $ordenes = $this->service_terminacion->obtenerDetalleOrdenesProductoVariantes($variante->producto_id, $data['variante_id'], $data['store_id'], $data['tipo_calendario'], $data['rango_busqueda'], 'T', 'N', $data['wrap']); //wsanchez terminados solamente?
//                error_log("ORDENES ENCONTRADAS DE ESA VARIANTE");

                if ($ordenes) {
                    foreach ($ordenes as $orden) {
//                        error_log(print_r($orden, true));
                        if (array_key_exists('variantes', $orden) && (sizeof($orden['variantes']) > 0)) {
//                        print_r($orden);
                            $ordenesArr = ($orden['variantes'][0]->ordenes);
                            foreach ($ordenesArr as $ord) {
                                $cantidad_de_la_orden = ($ord->orden_item_cantidad * $ord->variante_cantidad) / $divisor;
//                            if ($docena) {
//                                $cantidad_de_la_orden = $cantidad_de_la_orden / 12; //para saber cuantas docenas vamos a disminuir
//                            }
//                                error_log("ORDEN " . print_r($ord, true));
//                                error_log("CANTIDAD DE LA ORDEN " . print_r($cantidad_de_la_orden, true));
//                                error_log("CANTIDAD DISPONIBLE " . print_r($cantidad_terminada, true));
                                if ($cantidad_terminada >= $cantidad_de_la_orden) {
                                    //a ese orden_item_id lo vamos a marcar como preparado
                                    $accion = $this->service_ecommerce_orden->ordenItemTerminacionMarcar($ord->orden_item_id, true); //si lo marcamos como terminado tambien como preparado
                                    error_log("Accion es " . print_r($accion, true));
                                    if ($accion) {
                                        $cantidad_terminada = $cantidad_terminada - $cantidad_de_la_orden;
                                    }
                                }
                            }
                        } else {
                            $error = "No existen ordenes pendientes";
                        }
                    }
                } else {
                    $resultado = 'error3';
                }
            }
//            obtenerOrdenesProductosVariantes($store_id, $tipo_calendario, $rango_busqueda, $orden = '', $busqueda = '', $producto_id = null, $producto_variante_id = null, $preparado = 'T', $terminado = 'T', $wrap = 'NO')
////            $productos = $this->service_terminacion->obtenerDetalleOrdenesProductoVariantes($productoList->producto_id, $productoList->variante_id, $data['store_id'], $data['tipo_calendario'], $data['rango_busqueda'], $data['preparado'], $data['terminado'], false);
////  die;
//            $productos = $this->service_terminacion->obtenerDetalleOrdenesProductoVariantes($variante->producto_id, $data['variante_id'], $data['store_id'], $data['tipo_calendario'], $data['rango_busqueda'], 'T', 'N', $data['wrap']); //wsanchez terminados solamente?
            $productos = $this->service_terminacion->obtenerOrdenesProductosVariantes($data['store_id'], $data['tipo_calendario'], $data['rango_busqueda'], '', '', null, $data['variante_id'], $data['preparado'], $data['terminado'], $data['wrap']);
//            error_log(">>>>>>>>>>>>>>>>>>>>>>>>>>>>>>><<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<");
//            die;
            $resultado = 'No existen elementos restantes';
            if ($productos) {
//                foreach($productos as $k => $v) {
//                    $item = $v;
//                }
//                $ordenes = 0;
//                obtenerDetalleOrdenesProductoVariantes
//                $productos = $this->service_terminacion->obtenerDetalleOrdenesProductoVariantes($productoList->producto_id, $productoList->variante_id, $data['store_id'], $data['tipo_calendario'], $data['rango_busqueda'], $data['preparado'], $data['terminado'], false);
//                    $data['listadoProductos'][$productoList->producto_id]['producto'] = $productoList;
//                    if ($productos) {
////                        $data['listadoProductos'] = array();
//                        foreach ($productos as $producto) {
//                            $data['listadoProductos'][$productoList->producto_id]['variantes'][$productoList->variante_id] = $producto['variantes'];
//                        }
//                    }
                $item = $productos[0];
//                error_log(print_r($item, true));

                $total = $item->orden_item_variante_cantidad * $item->variante_cantidad;
                $totalPreparados = $item->orden_item_variante_cantidad_preparado * $item->variante_cantidad;
                $totalTerminados = $item->orden_item_variante_cantidad_terminado * $item->variante_cantidad;
                $totalPendientes = $total - $totalPreparados;
                $totalPendientesTerminados = $total - $totalTerminados;



                $resultado = '';
                $resultado .= ''
                        . '<div class="col-6 text-left border-dark border-right border-bottom-0" data-toggle="collapse" data-target="#detalle_' . (($data['wrap']) ? $data['wrap'] : 'sin_wrap') . '_' . $data['variante_id'] . '">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $item->variante_titulo . ' (' . $item->variante_sku . ') </div>
                    <div class="col-1 text-right border-dark border-right border-bottom-0">' . $item->largo_cm . ' cm. &nbsp;</div>
                    <div class="col-1 text-right border-dark border-right border-bottom-0">' . $unidad . '</div>';
//                    <div class="col-1 text-right border-dark border-right border-bottom-0">' . ($docena ? round(($total) / 12) : ($total) ) . ' </div>
                $resultado .= '<div class="col-1 text-right border-dark border-right border-bottom-0">' . ($totalTerminados / $divisor) . '</div>
                    <div class="col-2 text-right border-dark border-right border-bottom-0">';

                if ($totalPendientesTerminados != 0 && !$assemble) {
                    $arr = array(
                        "id" => "restante_variante_" . $item->variante_id,
                        "name" => "restante_variante_" . $item->variante_id,
                        "value" => ($totalPendientesTerminados / $divisor),
                        "tipo" => 'number',
                        //"max" => ($totalPendientesTerminados / $divisor),
                        "step" => $divisor,
                        "data-varante-id" => $item->variante_id,
                        "clase" => "col-4 restante_variante",
                    );
                    $resultado .= item_input($arr);
                    $resultado .= '&nbsp;<button type = "button" class="btn btn-primary btn-accion-variante ajustar_altura" id="btn-guardar-' . $item->variante_id . '" data-variante-id="' . $item->variante_id . '"   data-wrap="' . $wrap . '" ><i class="fas fa-check fa-xs"></i></button>';
                }
                $resultado .= '

                        </div>
                        <div class="col-1 text-right">
                            ' . ($total / $divisor) . ' &nbsp;
                        </div>
                        ';

                $ordenes = $this->service_terminacion->obtenerDetalleOrdenesProductoVariantes($item->producto_id, $item->variante_id, $data['store_id'], $data['tipo_calendario'], $data['rango_busqueda'], $data['preparado'], $data['terminado'], $wrap);
                if ($ordenes) {
                    foreach ($ordenes as $h) {
                        $w = $h['variantes'];
                    }
                    error_log(">>>>>>>>>>>>>>>>>>>>>>>>>>>>>>><<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<");
                    error_log(">>>>>>>>>>>>>>>>>>>>>>>>>>>>>>><<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<");
                    error_log(print_r($w, true));
                    if (array_key_exists(0, $w)) {
//                error_log(print_r($w[0],true));
                        $item_detalle = $w[0];
                        $resultado .= '
                        <div class="row col-12 border-dark border border-top-0" id="detalle_' . $wrap . "_" . $variante->id . '">
                        <div class="row col-2 align-items-start text-left">
                            <p style="text-align:left">';
//                error_log(print_r($ordenes,true));die;
                        foreach ($item_detalle->ordenes as $ord) {
//                    error_log(print_r($ordenes,true));die;
                            $resultado .= '
                                    <a href="#modalOrden" class="btn btn-orden-numero align-items-start align-self-start" data-toggle="modal" data-target="#modalOrden" data-orden_id="' . $ord->id . '"  data-variante_id="' . $variante->id . '"  data-wrap="' . $wrap . '" style="text-align:left; font-size: 0.75em; padding:0">
                                        <b>' . $ord->tienda_alias . '_' . (isset($ord->referencia_order_number) ? $ord->referencia_order_number : $ord->id) . '</b>
                                    </a>';
                        }
                        $resultado .= '</p>
                        </div>                    
                        <div class="row col-10 align-items-start text-left">';

                        foreach ($item_detalle->propiedades as $p => $prop) {
                            if (sizeof($prop['valores']) == 0) {
                                continue;
                            }
                            $resultado .= '
                                <div class="row col-10">
                                    <div class="col-2 text-left">' . $prop['propiedad_descripcion'] . '</div>
                                    <div class="row col-10 text-left">';

                            foreach ($prop['valores'] as $v => $valor) {

                                $resultado .= '<div class="row col-12">
                                                <div class="col-4 text-left">';
                                $pos = strpos($v, '(');
                                if ($pos) {
                                    $v = substr($v, 0, $pos);
                                }
                                $pos = strpos($v, '[');
                                if ($pos) {
                                    $v = substr($v, 0, $pos);
                                }
                                $resultado .= $v;
                                $resultado .= '</div>
                                                <div class="col-1">' . $valor['numero'] . '</div>
                                                <div class="col-7">';
                                foreach ($valor['ordenes'] as $vord) {
                                    $resultado .= '
                                                        <a href="#modalOrden" class="btn btn-orden-numero" data-toggle="modal" data-target="#modalOrden" data-orden_id="' . $vord->id . '" data-variante_id="' . $variante->id . '" data-wrap="' . $wrap . '" style="text-align:left; font-size: 0.75em; padding:0">
                                                            <b>' . $vord->tienda_alias . '_' . (isset($vord->referencia_order_number) ? $vord->referencia_order_number : $vord->id) . '</b>
                                                        </a>';
                                }
                                $resultado .= '</div>
                                            </div>';
                            }
                            $resultado .= '</div>';
                            $resultado .= '</div>';
                        }
                        $resultado .= '</div>
                    </div>';
                    } else {
                        $resultado .= "No hay ordenes pendientes";
                    }
                } else {
                    $resultado .= "Sin pendientes";
                }
                $resultado .= '</div>';
//                $resultado = 'No hay un resultado porque esta mal armado';
            }
        } else {
            $resultado = 'error2';
        }
        header('Content-Type: application/json');
        echo json_encode(array("error" => strlen($resultado) < 6, "detalle" => $resultado, "wrap" => $wrap, "producto_variante_id" => $data['variante_id']));
    }

}
