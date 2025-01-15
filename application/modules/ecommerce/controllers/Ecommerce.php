<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Ecommerce extends MY_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model("ecommerce/service_ecommerce");
        $this->load->model("produccion/service_empaque");
        $this->load->model("ecommerce/service_ecommerce_cliente");
        $this->load->model("ecommerce/service_ecommerce_orden");
        $this->load->model("ecommerce/service_ecommerce_producto");
        $this->load->model("ecommerce/service_ecommerce_logistica");
        $this->load->model("ecommerce/service_ecommerce_formula");
        $this->load->model("produccion/service_orden");
        $this->load->model("produccion/service_logistica");
        $this->load->model("shopify/shopify_model");
        $this->load->model("produccion/service_orden");
    }

    public function convertirOrden($shopify_order_id) {
        echo $shopify_order_id;
        $this->service_ecommerce->convertirOrdenShopifyaOrdenRosaholics($shopify_order_id);
    }

    public function convertirOrdenes() {
        set_time_limit(600);
        //obtenemos todas las ordenes shopify que no se han convertido a ordenes rosaholics
        //        $this->service_ecommerce->convertirOrdenShopifyaOrdenRosaholics(2988351488163);
        //        $this->service_ecommerce->convertirOrdenShopifyaOrdenRosaholics("---");
        //        die;
        $arr_shopify_orders = $this->shopify_model->obtenerOrdenesNoConvertidas();
        foreach ($arr_shopify_orders as $orden) {
            print_r($orden->order_number);
            echo "<br/>";
            $this->service_ecommerce->convertirOrdenShopifyaOrdenRosaholics($orden->shopify_order_id);
        }
        $data['ordenes'] = "";
        $this->mostrarVista('sincronizacion.php', $data);
    }

    //////////////////////////

    public function ordenes($orden_id = false, $orden_caja_id = false) {
        $data['store_id'] = 1;
        $data['orden_caja_actual'] = 0;
        $data['ordenes'] = array();
        $data['perfil'] = PANTALLA_LOGISTICA; //perfil_empaque
        $data['asignadoCaja'] = 'T';
        $data['empacado'] = 'T';
        $data['preparado'] = 'T';
        $data['terminado'] = 'T';
        $data['rango_busqueda'] = "";
        $data['tipo_calendario'] = 0;
        $data['totales'] = false;
        $data['tracking_number'] = '';
        $data['tarjeta_impresa'] = 'T';
        $data['order_number'] = "";
        $data['texto_busqueda'] = "";
        $data['referencia_order_number'] = "";
        $data['finca_id'] = 0;
        $data['reenviado'] = 'T';
        $data['con_tracking_number'] = 'T';
        $data['session_finca'] = $this->session->userFincaId;
        $data['orden_estado_id'] = 'T';
        $data['tipo_caja'] = '';
        $cuantasOrdenes = 0;

        $data['orden_actual'] = 0;
        if ($orden_id) {
            $data['orden_actual'] = $orden_id;
        }
        if ($orden_caja_id) {
            $data['orden_caja_actual'] = $orden_caja_id;
        }
        $data['sel_store'] = $this->service_ecommerce->obtenerTiendasSel();   
        $data['sel_tipo_caja'] = $this->service_logistica->obtenerTiposDeCajas();
        $data['sel_finca'] = $this->service_ecommerce_orden->obtenerFincaSel();
        $data['sel_orden_estado'] = $this->service_ecommerce_orden->obtenerSelEstadoOrden();
        if ($this->input->post('btn_buscar') != null) {
            $data['texto_busqueda'] = $this->input->post('texto_busqueda');
            $data['order_number'] = $this->input->post('order_number');
            $data['referencia_order_number'] = $this->input->post('referencia_order_number');
            $data['tipo_caja_id'] = $this->input->post('tipo_caja_id');
            $data['tracking_number'] = $this->input->post('tracking_number');
            if ($this->input->post('reenviado')) {
                $data['reenviado'] = $this->input->post('reenviado');
            }
            if ($this->input->post('con_tracking_number')) {
                $data['con_tracking_number'] = $this->input->post('con_tracking_number');
            }
            $data['rango_busqueda'] = $this->input->post('rango_busqueda');
            $data['tipo_calendario'] = $this->input->post('tipo_calendario');

            $data['store_id'] = $this->input->post('store_id');
            $data['asignadoCaja'] = $this->input->post('asignadoCaja');
            $data['empacado'] = $this->input->post('empacado');
            $data['preparado'] = $this->input->post('preparado');
            $data['terminado'] = $this->input->post('terminado');
            $data['tarjeta_impresa'] = $this->input->post('tarjeta_impresa');
            $data['tipo_caja'] = $this->input->post('tipo_caja');
            if ($this->input->post('order_number') != null) {
                $data['order_number'] = $this->input->post('order_number');
            }
            //            error_log(print_r($data,true));
            if ($this->input->post('busqueda') != null) {
                $data['texto_busqueda'] = $this->input->post('busqueda');
            }
            $data['finca_id'] = $this->input->post('finca_id');
            $data['orden_estado_id'] = $this->input->post('orden_estado_id');
            $listadoOrdenes = false;
            if (!empty($data['tracking_number'])) {

                $orden_caja = $this->service_logistica->buscarTrackingNumber($data['tracking_number']);
                if (!$orden_caja && is_numeric($data['tracking_number'])) {
                    //busquemos con el tracking_number como numero de caja
                    $orden_caja = $this->service_logistica->buscarCajaId($data['tracking_number']);
                }

                if ($orden_caja) {
                    //marco la caja como empacada
                    //                    if ($orden_caja->empacada == 'S') {
                    //                        $data['error'] = 'ERROR CAJA ' . $data['tracking_number'] . ' YA EMPACADA!!!! ';
                    //                    } else {
                    //                        $accion = $this->service_empaque->ordenCajaEmpacada($orden_caja->id);
                    //                        $data['exito'] = 'Empacada ' . $data['tracking_number'];
                    //                    }


                    list($listadoOrdenes, $cuantasOrdenes) = $this->service_ecommerce->obtenerOrdenes(null, null, null, null, null, $orden_caja->orden_id);
                    if ($cuantasOrdenes == 1) {
                        $data['orden_actual'] = $orden_caja->orden_id;
                    }
                } else {
                    $data['error'] = "No existe informacion para los datos ingresados";
                }
            } else {
                //                List($listadoOrdenes, $cuantasOrdenes) = $this->service_ecommerce->obtenerOrdenes($data['store_id'], $data['tipo_calendario'], $data['rango_busqueda'], $data['order_number'], $data['texto_busqueda'], false, $tarjeta_impresa);
                list($listadoOrdenes, $cuantasOrdenes) = $this->service_orden->obtenerOrdenes($data, true);
            }
            $filtro = array("asignadoCaja" => $data['asignadoCaja'], "perfil" => $data['perfil'], "texto_busqueda" => $data['texto_busqueda'], "tarjeta_impresa" => $data['tarjeta_impresa'], "session_finca" => $data['session_finca'],
                "finca_id" => $data['finca_id'],);
            if ($listadoOrdenes) {
                foreach ($listadoOrdenes as $orden) {
                    //                    $orden->tag = '';
                    //                    $items = $this->service_ecommerce->obtenerOrdenItems($orden->id);
                    //                    $orden->items = $items;
                    //                    $data['orden_items'] = false;
                    //                    if ($items) {
                    //                        $data['orden_items'] = array();
                    //                        foreach ($items as $item) {
                    //                            $propiedades = $this->service_ecommerce->obtenerOrdenItemPropiedades($item->id);
                    //                            foreach ($propiedades as $propiedad) {
                    //                                if ($propiedad->propiedad_id == 19) {//standing order
                    //                                    $orden->tag .= 'STANDING ORDER';
                    //                                }
                    //                            }
                    //                        }
                    //                    }

                    $orden->asignadoCaja = $data['asignadoCaja'];
                    //                    $orden->filtro_tarjeta_impresa = $data['tarjeta_impresa'];
                    $orden->card = $this->orden_card($orden->id, $filtro); // $this->load->view('orden_card.php', $orden, true);
                    if ($orden->card != false) {
                        $data['ordenes'][] = $orden;
                    }
                }
            }
            $filtro = array(
                "store_id" => $data['store_id'],
                "tipo_calendario" => $data['tipo_calendario'],
                "rango_busqueda" => $data['rango_busqueda'],
                "session_finca" => $data['session_finca'],
                "finca_id" => $data['finca_id'],
                "orden_estado_id" => $data['orden_estado_id'],
            );
            //            error_log(">>>>>>>>>>>>>>>>>>>>>>>");
            $data['totales'] = $this->service_ecommerce_orden->obtenerTotales($filtro);
        }

        $data['cuantos'] = $cuantasOrdenes;
        $data['sel_tipo_caja'] = $this->service_logistica->obtenerTiposDeCajas();
        $data['url_busqueda'] = "ecommerce/ordenes";
        $this->mostrarVista('ordenes.php', $data);
    }

    private function orden_card($orden_id, $filtro = array()) {
        $orden = $this->service_orden->obtenerOrden($orden_id);
        $orden->detalle = $this->service_orden->obtenerOrdenDetalle($orden_id);
        $orden->cajas = $this->service_empaque->obtenerCajasPorIdOrden($orden_id);
        if ($orden->cajas) {
            foreach ($orden->cajas as $caja) {
                $this->service_ecommerce_logistica->verificarCajaEstado($caja->id);
            }
        }
        $orden->cajas = $this->service_empaque->obtenerCajasPorIdOrden($orden_id);
        //por cada caja vamos a ver su contenido

        /*         * ************************* */
        $items = '';
        $cajas_id = array();
        $todosEnCaja = false;
        $orden->tag = '';
        $orden_caja_id = $this->input->post('orden_caja_id');
        $finca_id = $this->input->post('finca_id');
        $session_finca = $this->input->post('session_finca');
        $vlidacion = $this->validacion_tracking($orden->id, $orden_caja_id, $session_finca, $finca_id);
        if ($vlidacion) {
            $orden->no_editable = 'no_editable';
        } else {
            $orden->no_editable = '';
        }
        if ($orden->detalle) {
            $todosEnCaja = true;
            foreach ($orden->detalle as $k => $det) {
                $orden->pdf_caja_existe = false;
                if ($det->tracking_number != null) {
                    //verificar pdf cargado
                    $orden->pdf_caja_existe = true;
                }

                if ($det->tipo_caja_id == null) {
                    $todosEnCaja = false;
                } else {
                    if (!array_key_exists($det->caja_id, $cajas_id)) {
                        $cajas_id[$det->caja_id] = 0;
                    }
                    $cajas_id[$det->caja_id] ++;
                }

                $propiedades = $this->service_ecommerce->obtenerOrdenItemPropiedades($det->id);
                if ($propiedades) {
                    foreach ($propiedades as $propiedad) {
                        if ($propiedad->propiedad_id == 19) { //standing order
                            $orden->tag .= 'STANDING ORDER';
                        }
                    }
                }
                $items .= '<div class="row small rounded ' . ($k % 2 === 0 ? "fila_par" : "fila_impar") . '">'
                        . '<div class="col-12 text-left text-truncate">' . $det->info_producto_titulo . '</div>'
                        . '<div class="col-7 offset-1 text-left text-truncate">' . $det->info_variante_titulo . '</div>'
                        . '<div class="col-2 text-right">x' . $det->cantidad . '</div>'
                        . '<div class="col-2 text-right">' . ($det->preparado == 'S' ? '<i class="fas fa-spa col-12"></i>' : '') . '</div>'
                        . '</div>';
            }
        }
        if (isset($filtro['asignadoCaja']) && $filtro['asignadoCaja'] != 'T') {
            if (($filtro['asignadoCaja'] == 'S') && (!$todosEnCaja)) {
                return false;
            }
            if (($filtro['asignadoCaja'] == 'N') && ($todosEnCaja)) {
                return false;
            }
        }
        /*         * **************************** */
        $orden->perfil = $filtro['perfil'];

        $card = $this->load->view('produccion/orden_card.php', $orden, true);

        return $card;
    }

    public function json_orden_card() {
        $orden_id = $this->input->post('orden_id');
        $filtro = $this->input->post('filtro');
        $card = $this->orden_card($orden_id, $filtro);
        $respuesta = array("error" => false, "orden_id" => $orden_id, "card" => $card);

        header('Content-Type: application/json');
        echo json_encode($respuesta);
    }

    public function orden_nueva() {
        $data = array();
        $data['sel_store'] = $this->shopify_model->obtenerTiendasSel();
        $data['cliente_id'] = $this->input->post('cliente_id');
        $detalle_orden = $this->load->view('orden_nueva.php', $data, true);
        $respuesta = array("error" => false, "detalle_orden" => $detalle_orden);

        header('Content-Type: application/json');
        echo json_encode($respuesta);
    }

    public function orden_nueva_cliente() {
        $cliente_id = $this->input->post('cliente_id');

        $cliente = $cliente = $this->service_ecommerce_cliente->obtenerCliente($cliente_id);

        //TODO buscar una orden del mismo cliente para evitar crear muchas ordenes vacias

        $objOrden = array(
            "cliente_id" => $cliente_id,
            "store_id" => $cliente->store_id,
            "fecha_compra" => fechaActual(),
            "estado" => ESTADO_ACTIVO,
        );
        $id = $this->service_ecommerce_orden->crearOrden($objOrden);

        header('Content-Type: application/json');
        echo json_encode(array("error" => !$id, "respuesta" => !$id ? "Hay un problema al momento de crear la orden" : "Nueva orden creada", "id" => $id));
    }

    public function orden_nueva_guardar() {
        $store_id = $this->input->post('store_id');

        $objOrden = array(
            "store_id" => $store_id,
            "fecha_compra" => fechaActual(),
            "estado" => ESTADO_ACTIVO,
        );
        $id = $this->service_ecommerce_orden->crearOrden($objOrden);

        header('Content-Type: application/json');
        echo json_encode(array("error" => !$id, "respuesta" => !$id ? "Hay un problema al momento de crear la orden" : "Nueva orden creada", "id" => $id));
    }

    public function obtenerOrdenTerminacion() {
        return $this->obtenerOrden(PANTALLA_TERMINACION);
    }

    public function obtenerOrdenPreparacion() {
        return $this->obtenerOrden(PANTALLA_PREPARACION);
    }

    public function obtenerOrdenEmpaque() {
        return $this->obtenerOrden(PANTALLA_EMPAQUE);
    }

    public function obtenerOrdenManufactura() {
        return $this->obtenerOrden(PANTALLA_MANUFACTURA);
    }

    public function validacion_tracking($orden_id, $orden_caja_id, $session_finca, $finca_id) {
        $cajasOrden = $this->service_ecommerce_logistica->obtenerOrdenCajas($orden_id, ESTADO_ACTIVO, $orden_caja_id, $session_finca, $finca_id);
        if ($cajasOrden) {
            foreach ($cajasOrden as $caja) {
                $trackingnumber = $this->service_logistica->obtenerTrackingNumberCaja($caja->id);
                if ($trackingnumber) {
                    return true;
                } else {
                    return false;
                }
            }
        }
    }

    public function obtenerOrden($perfil = false) {
        $data['perfil'] = PANTALLA_LOGISTICA;
        $data['session_finca'] = $this->session->userFincaId;
        $data['ecommerce'] = true;
        if (!$perfil) {
            $perfil = $this->input->post('perfil');
        }
        if ($perfil) {
            $data['perfil'] = $perfil;
        }
        $orden_id = $this->input->post('id');
        $orden_caja_id = $this->input->post('orden_caja_id');
        $finca_id = $this->input->post('finca_id');
        $session_finca = $this->input->post('session_finca');
        if ($orden_caja_id) {
            $data['ecommerce'] = false;
        }
        $orden = $this->service_ecommerce_orden->existeOrden(array('id' => $orden_id));

        if ($orden) {
            $data['orden'] = $orden;
            $items = $this->service_ecommerce->obtenerOrdenItems($orden->id, $session_finca, $finca_id);
            //            die;
            $data['orden_items'] = false;
            $vlidacion = $this->validacion_tracking($orden->id, $orden_caja_id, $session_finca, $finca_id);
            if ($vlidacion) {
                $data['no_editable'] = 'no_editable';
            } else {
                $data['no_editable'] = '';
            }
            if ($items) {
                $data['orden_items'] = array();
                foreach ($items as $item) {
                    //                    error_log(print_r($item, true));
                    //                    $cajas = $this->service_ecommerce_logistica->obtenerOrdenItemCaja($item->id);
                    //                    $item->cajas = $cajas;
                    $stems = $this->service_ecommerce_formula->totalStemsRecetaSKU($item->info_variante_sku);
                    $propiedades = $this->service_ecommerce->obtenerOrdenItemPropiedades($item->id);
                    $totalStems = $stems->sum * $item->cantidad;
                    if ($propiedades) {
                        foreach ($propiedades as $propiedad) {
                            if (strpos(strtoupper($propiedad->info_propiedad_nombre), 'AGR_') === 0) {
                                $total = $this->service_ecommerce_formula->totalStemsRecetaSKU($propiedad->info_propiedad_nombre);
                                $totalStems += intval($total->sum) * intval($propiedad->valor);
                            }
                        }
                    }
                    $item->totalStems = $totalStems;
                    $item->receta = $this->service_ecommerce_formula->obtenerRecetaProductoVariante($item->variante_id);
                    $item->propiedades = $this->load->view('orden_detalle_item_propiedades.php', array("propiedades" => $propiedades, "orden_item_id" => $item->id, "perfil" => $data['perfil'], "no_editable" => $data['no_editable']), true);
                    $item->card = $this->load->view('orden_item.php', array("item" => $item, "logistica" => false, "perfil" => $data['perfil'], "no_editable" => $data['no_editable']), true);
                    $data['orden_items'][] = $item;
                }
            }

            $data['cliente'] = $orden->cliente_id == null ? false : $this->service_ecommerce_cliente->existeClienteCustomerStore($orden->store_id, false, $orden->cliente_id);
            $data['cliente_direccion'] = $orden->cliente_direccion_id == null ? false : $this->service_ecommerce_cliente->existeClienteDireccionEnvio(array("id" => $orden->cliente_direccion_id));

            if ($data['perfil'] == PANTALLA_LOGISTICA) {
                $data['orden_cliente_resumen'] = $this->load->view('orden_detalle_cliente.php', $data, true);
                $data['orden_destino_resumen'] = $this->load->view('orden_detalle_destino.php', $data, true);
            }
            if (($data['perfil'] == PANTALLA_LOGISTICA) || ($data['perfil'] == PANTALLA_PREPARACION) || ($data['perfil'] == PANTALLA_TERMINACION) || ($data['perfil'] == PANTALLA_MANUFACTURA)) {
                $data['orden_items_resumen'] = $this->load->view('orden_detalle_items.php', $data, true);
            }
            if (($data['perfil'] == PANTALLA_LOGISTICA) || ($data['perfil'] == PANTALLA_EMPAQUE)) {
                //para logistica vamos a ver que cajas existen
                $data['cajas_orden'] = false;
                $cajasOrden = $this->service_ecommerce_logistica->obtenerOrdenCajas($orden->id, ESTADO_ACTIVO, $orden_caja_id, $session_finca, $finca_id);
                if ($cajasOrden) {
                    foreach ($cajasOrden as $caja) {
                        $trackingnumber = $this->service_logistica->obtenerTrackingNumberCaja($caja->id);
                        $caja->tracking_number = $trackingnumber;

                        $itemsEnCaja = $this->service_ecommerce_logistica->obtenerOrdenCajaItems($caja->id);
                        if ($itemsEnCaja) {
                            foreach ($itemsEnCaja as $item) {
                                $stems = $this->service_ecommerce_formula->totalStemsRecetaSKU($item->info_variante_sku);
                                $propiedades = $this->service_ecommerce->obtenerOrdenItemPropiedades($item->id);
                                $totalStems = $stems->sum * $item->cantidad;
                                if ($propiedades) {
                                    foreach ($propiedades as $propiedad) {
                                        if (strpos(strtoupper($propiedad->info_propiedad_nombre), 'AGR_') === 0) {
                                            $total = $this->service_ecommerce_formula->totalStemsRecetaSKU($propiedad->info_propiedad_nombre);
                                            $totalStems += intval($total->sum) * intval($propiedad->valor);
                                        }
                                    }
                                }
                                $item->totalStems = $totalStems;
                                $item->propiedades = $this->load->view('orden_detalle_item_propiedades.php', array("propiedades" => $propiedades, "orden_item_id" => $item->id), true);
                                $item->card = $this->load->view('orden_item.php', array("item" => $item, "logistica" => true, "perfil" => $data['perfil']), true);
                                $caja->items[] = $item;
                            }
                            $data['cajas_orden'][] = $caja;
                        }
                    }
                }
                //            $data['cajas_orden'] = false;
                //            if ($cajasOrden) {
                //                $data['cajas_orden'] = array();
                //                foreach ($cajasOrden as $caja) {
                //                    foreach ($caja->items as $item) {
                //                        $propiedades = $this->service_ecommerce_logistica->obtenerOrdenItemPropiedades($item->id);
                //                        $item->propiedades = $this->load->view('orden_detalle_item_propiedades.php', array("propiedades" => $propiedades, "orden_item_id" => $item->id), true);
                //                        $item->card = $this->load->view('orden_item.php', array("item" => $item, "logistica" => true), true);
                //                        $caja->elemento[] = $item;
                //                    }
                //                    $data['cajas_orden'][] = $caja;
                //                }
                //            }
                $data['sel_tipo_caja'] = $this->service_logistica->obtenerTiposDeCajas();
                $data['sel_finca'] = $this->service_ecommerce_orden->obtenerFincaSelect();
                $data['orden_logistica_resumen'] = $this->load->view('orden_detalle_logistica.php', $data, true);
            }
            $detalle_orden = $this->load->view('orden_detalle.php', $data, true);
            $respuesta = array(
                "error" => false,
                "orden_id" => $orden_id,
                "detalle_orden" => $detalle_orden,
                "fecha_entrega" => $orden->fecha_entrega,
                "fecha_carguera" => $orden->fecha_carguera,
                "fecha_preparacion" => $orden->fecha_preparacion
            );
        } else {
            $respuesta = array("error" => "No existe informacion de la orden $orden_id");
        }

        header('Content-Type: application/json');
        echo json_encode($respuesta);
    }

    public function obtenerOrdenTarjeta() {
        
    }

    public function imprimir() {

        try {
            $this->load->library('ReceiptPrint');
            $this->receiptprint->connect('192.168.100.195', 9100);
            $this->receiptprint->print_test_receipt('Hello World!');
        } catch (Exception $e) {
            log_message("error", "Error: Could not print. Message " . $e->getMessage());
            $this->receiptprint->close_after_exception();
        }
    }

    public function actualizar_direccion_orden() {
        $data['id'] = $this->input->post('orden_id');
        if ($this->input->post('cliente_id') != null) {
            $data['cliente_id'] = $this->input->post('cliente_id');
        }
        $data['cliente_direccion_id'] = $this->input->post('cliente_direccion_id');

        $actualizacion = $this->service_ecommerce_orden->actualizarOrden($data);
        if (!$actualizacion) {
            $respuesta = 'Existe un problema durante la creaci&oacute;n';
        } else {
            $respuesta = 'Registro creado';
        }
        header('Content-Type: application/json');
        echo json_encode(array("error" => !$actualizacion, "respuesta" => $respuesta));
    }

    public function actualizar_cliente_orden() {
        $data['id'] = $this->input->post('orden_id');
        $data['cliente_id'] = $this->input->post('cliente_id');

        //obtenemos la primera direccion activa del cliente seleccionado
        $direcciones = $this->service_ecommerce_cliente->obtenerClienteDireccionEnvio(false, $data['cliente_id'], ESTADO_ACTIVO);
        if (!$direcciones) {
            $actualizacion = false;
            $respuesta = "Cliente no posee direcciones activas";
        } else {
            $data['cliente_direccion_id'] = $respuesta[0]->cliente_id;
            $actualizacion = $this->service_ecommerce_orden->actualizarOrden($data);
            if (!$actualizacion) {
                $respuesta = 'Existe un problema durante la creaci&oacute;n';
            } else {
                $respuesta = 'Registro creado';
            }
        }
        header('Content-Type: application/json');
        echo json_encode(array("error" => !$actualizacion, "respuesta" => $respuesta));
    }

    public function orden_edicion_items() {
        $data['orden_id'] = $this->input->post('orden_id');
        $items = $this->service_ecommerce->obtenerOrdenItems($data['orden_id']);
        $arr_items = array();
        foreach ($items as $item) {
            $item->propiedades = $this->service_ecommerce->obtenerOrdenItemPropiedades($item->id);
            $item->card = $this->load->view('orden_item.php', array("item" => $item), true);
            $arr_items[] = $item;
        }
        $data['orden_items'] = $arr_items;

        $items_det = $this->load->view('orden_detalle_item_edicion.php', $data, true);

        $respuesta = array("error" => false, "respuesta" => $items_det);
        header('Content-Type: application/json');
        echo json_encode($respuesta);
    }

    public function eliminar_item_orden() {

        $orden_item_id = $this->input->post('orden_item_id');
        $eliminacion = $this->service_ecommerce->eliminarOrdenItem($orden_item_id);
        $respuesta = array("error" => !$eliminacion ? true : false, "respuesta" => $eliminacion);
        header('Content-Type: application/json');
        echo json_encode($respuesta);
    }

    public function nuevo_item_orden() {
        $data['orden_id'] = $this->input->post('orden_id');
        $data['propiedades'] = false;

        $items_det = $this->load->view('orden_detalle_item_edicion.php', $data, true);
        $respuesta = array("error" => false, "orden_item_id" => false, "respuesta" => $items_det);
        header('Content-Type: application/json');
        echo json_encode($respuesta);
    }

    public function editar_item_orden() {
        $data['orden_item_id'] = $this->input->post('orden_item_id');

        $data['orden_item'] = $this->service_ecommerce->obtenerOrdenItem($data['orden_item_id']);
        $data['orden_id'] = $data['orden_item']->orden_id;

        $data['sel_producto'] = $this->service_ecommerce_producto->obtenerProducto($data['orden_item']->producto_id);
        $data['sel_variante'] = array();

        $data['propiedades'] = $this->service_ecommerce->obtenerOrdenItemPropiedades($data['orden_item_id']);

        $items_det = $this->load->view('orden_detalle_item_edicion.php', $data, true);

        $respuesta = array("error" => false, "orden_item" => $data['orden_item'], "respuesta" => $items_det);
        header('Content-Type: application/json');
        echo json_encode($respuesta);
    }

    public function orden_producto_select() {
        $producto_id = $this->input->post('id');
        $texto = $this->input->post('texto');
        $arr = $this->service_ecommerce_producto->obtenerSelProductos($producto_id, $texto);
        header('Content-Type: application/json');
        echo json_encode($arr);
    }

    public function orden_variante_select() {
        $producto_id = $this->input->post('id');
        $variante_id = $this->input->post('variante_id');
        $texto = $this->input->post('texto');
        $arr = $this->service_ecommerce_producto->obtenerSelProductoVariantes($producto_id, $variante_id, $texto);
        header('Content-Type: application/json');
        echo json_encode($arr);
    }

    public function orden_item_guardar() {
        $actualizacion = false;

        $linea = array(
            "orden_id" => $this->input->post('orden_id'),
            "producto_id" => $this->input->post('producto_id'),
            "variante_id" => $this->input->post('variante_id'),
            "cantidad" => $this->input->post('cantidad'),
        );

        $linea_id = $this->service_ecommerce->crearLinea($linea);

        if (!$linea_id) {
            $respuesta = 'Existe un problema durante la creaci&oacute;n';
        } else {
            if ($this->input->post('orden_item_id') != null) {
                //primero voy a sacarlo de la caja a la cual este asignado, este metodo tambien inactivar la caja si es que esta vacia
                $this->service_ecommerce_logistica->sacarItemDeCaja($this->input->post('orden_item_id'));
                $obj = $this->service_ecommerce_orden->inactivarOrdenItem($this->input->post('orden_item_id'));
                //debo
                //obtenemos las propieades de la linea previa
                $propiedades = $this->service_ecommerce->obtenerOrdenItemPropiedades($this->input->post('orden_item_id'));
                if ($propiedades) {
                    foreach ($propiedades as $propiedad) {
                        $this->service_ecommerce->crearOrdenItemPropiedad($linea_id, $propiedad->propiedad_id, $propiedad->valor);
                    }
                }
                //las agregamos a la nueva linea
            }
            $respuesta = 'Registro creado';
        }

        header('Content-Type: application/json');
        echo json_encode(array("error" => !$linea_id, "respuesta" => $respuesta, "nuevo_id" => $linea_id));
    }

    public function totales() {
        $filtro = array(
            "tipo_calendario" => 0,
            "rango_busqueda" => "2021-01-18 - 2021-01-18",
        );
        $totalOrdenes = $this->service_ecommerce_orden->obtenerTotalOrdenes($filtro);
        echo "<br/>totalOrdenes es " . print_r($totalOrdenes->totalordenes, true);
        $totalOrdenesEnCaja = $this->service_ecommerce_orden->obtenerTotalOrdenesEnCaja($filtro);

        $totalOrdenes = $totalItems = $totalItemsEnCaja = $totalItemsNoEnCaja = 0;
        $totalOrdenesAsignadasEnCaja = 0;

        foreach ($totalOrdenesEnCaja as $ordenCaja) {
            echo "<br/>ordenCaja es " . print_r($ordenCaja, true);
            $totalOrdenes++;
            $totalItems += $ordenCaja->items_orden;
            $totalItemsEnCaja += $ordenCaja->items_en_caja;
            $totalItemsNoEnCaja += $ordenCaja->items_no_en_caja;
            if ($ordenCaja->items_en_caja == $ordenCaja->items_orden) {
                $totalOrdenesAsignadasEnCaja++;
            }
        }

        echo "<br/>Total Ordenes: " . $totalOrdenes;
        echo "<br/>Total totalItems: " . $totalItems;
        echo "<br/>Total totalItemsEnCaja: " . $totalItemsEnCaja;
        echo "<br/>Total totalItemsNoEnCaja: " . $totalItemsNoEnCaja;
        echo "<br/>Total Ordenes Asignadas en Caja: " . $totalOrdenesAsignadasEnCaja;
    }

    public function totalResumen() {
        $filtro = array(
            "store_id" => $this->input->post('store_id'),
            "tipo_calendario" => $this->input->post('tipo_calendario'),
            "rango_busqueda" => $this->input->post('rango_busqueda'),
        );
        if ($this->input->post('tipo') != null) {
            $tipo = $this->input->post('tipo');
        }
        $data['totales'] = $this->service_ecommerce_orden->obtenerTotales($filtro, $tipo);

        $totales = mostrarTotalesOrdenes($data['totales']);
        header('Content-Type: application/json');
        echo json_encode(array("error" => false, "respuesta" => $totales));
    }

}
