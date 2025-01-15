<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Orden extends MY_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model("produccion/service_orden");
        $this->load->model("produccion/service_logistica");
        $this->load->model("ecommerce/service_ecommerce");
    }

//    public function ordenes($orden_id = false) {
//        $data['order_number'] = $data['texto_busqueda'] = "";
//
//        $data['store_id'] = 1;
//        $data['ordenes'] = array();
//        $data['perfil'] = PERFIL_LOGISTICA; //perfil_empaque
//        $data['asignadoCaja'] = $data['preparado'] = $data['terminado'] = $data['tarjeta_impresa'] = 'T';
//        $data['rango_busqueda'] = $data['tracking_number'] = '';
//        $data['tipo_calendario'] = 0;
//        $data['totales'] = false;
//        $cuantasOrdenes = 0;
//        $data['orden_actual'] = ($orden_id) ? $orden_id : 0;
//        $data['sel_store'] = $this->service_ecommerce->obtenerTiendasSel();
//        if ($this->input->post('btn_buscar') != null) {
//            $data['texto_busqueda'] = $this->input->post('texto_busqueda');
//            $data['tracking_number'] = $this->input->post('tracking_number');
//            $data['rango_busqueda'] = $this->input->post('rango_busqueda');
//            $data['tipo_calendario'] = $this->input->post('tipo_calendario');
//            $data['store_id'] = $this->input->post('store_id');
//            $data['asignadoCaja'] = $this->input->post('asignadoCaja');
//            $data['preparado'] = $this->input->post('preparado');
//            $data['terminado'] = $this->input->post('terminado');
//            $data['tarjeta_impresa'] = $this->input->post('tarjeta_impresa');
//
//            if ($this->input->post('order_number') != null) {
//                $data['order_number'] = $this->input->post('order_number');
//            }
//            if ($this->input->post('busqueda') != null) {
//                $data['texto_busqueda'] = $this->input->post('busqueda');
//            }
//
//            $listadoOrdenes = false;
//            if (!empty($data['tracking_number'])) {
//                $orden_caja = $this->service_logistica->buscarTrackingNumber($data['tracking_number']);
//                if (!$orden_caja && is_numeric($data['tracking_number'])) {
//                    //busquemos con el tracking_number como numero de caja
//                    $orden_caja = $this->service_logistica->buscarCajaId($data['tracking_number']);
//                }
//                if ($orden_caja) {
//                    //marco la caja como empacada
////                    if ($orden_caja->empacada == 'S') {
////                        $data['error'] = 'ERROR CAJA ' . $data['tracking_number'] . ' YA EMPACADA!!!! ';
////                    } else {
////                        $accion = $this->service_empaque->ordenCajaEmpacada($orden_caja->id);
////                        $data['exito'] = 'Empacada ' . $data['tracking_number'];
////                    }
//                    List($listadoOrdenes, $cuantasOrdenes) = $this->service_orden->obtenerOrdenes(array("orden_id" => $orden_caja->orden_id));
//                    if ($cuantasOrdenes == 1) {
//                        $data['orden_actual'] = $orden_caja->orden_id;
//                    }
//                } else {
//                    $data['error'] = "No existe informacion para los datos ingresados";
//                }
//            } else {
//                List($listadoOrdenes, $cuantasOrdenes) = $this->service_orden->obtenerOrdenes($data);
//            }
//
//            if ($listadoOrdenes) {
//                foreach ($listadoOrdenes as $orden) {
//                    $data['listadoOrdenesId'] = $orden->id;
//                }
//            }
//            $filtro = array(
//                "store_id" => $data['store_id'],
//                "tipo_calendario" => $data['tipo_calendario'],
//                "rango_busqueda" => $data['rango_busqueda'],
//            );
////            error_log(">>>>>>>>>>>>>>>>>>>>>>>");
//            $data['totales'] = $this->service_ecommerce_orden->obtenerTotales($filtro);
//        }
//
//        $data['cuantos'] = $cuantasOrdenes;
//
//        $data['url_busqueda'] = "ecommerce/ordenes";
//        $this->mostrarVista('ordenes.php', $data);
//    }
//
//    private function orden_card($orden_id, $perfil) {
//        $orden = $this->service_orden->obtenerOrden($orden_id);
//        $orden->detalle = $this->service_orden->obtenerOrdenDetalle($orden_id);
//        $orden->perfil = $perfil;
//
//        $card = $this->load->view('produccion/orden_card.php', $orden, true);
//
//        return $card;
//    }
//
//    public function json_orden_card() {
//        $orden_id = $this->input->post('orden_id');
//        $perfil_id = $this->input->post('perfil_id');
//        $card = $this->orden_card($orden_id, $perfil_id);
//        $respuesta = array("error" => false, "orden_id" => $orden_id, "card" => $card);
//
//        header('Content-Type: application/json');
//        echo json_encode($respuesta);
//    }

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

    public function obtenerOrden($perfil = false) {
        $data['perfil'] = PANTALLA_LOGISTICA;

        if (!$perfil) {
            $perfil = $this->input->post('perfil');
        }
        if ($perfil) {
            $data['perfil'] = $perfil;
        }
        $orden_id = $this->input->post('id');

        $orden = $this->service_ecommerce_orden->existeOrden(array('id' => $orden_id));

        if ($orden) {
            $data['orden'] = $orden;
            $items = $this->service_ecommerce->obtenerOrdenItems($orden->id);
//            error_log(print_r($items,true));
//            die;
            $data['orden_items'] = false;
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
                    $item->propiedades = $this->load->view('orden_detalle_item_propiedades.php', array("propiedades" => $propiedades, "orden_item_id" => $item->id, "perfil" => $data['perfil']), true);
                    $item->card = $this->load->view('orden_item.php', array("item" => $item, "logistica" => false, "perfil" => $data['perfil']), true);
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
                $cajasOrden = $this->service_ecommerce_logistica->obtenerOrdenCajas($orden->id);
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

                $data['orden_logistica_resumen'] = $this->load->view('orden_detalle_logistica.php', $data, true);
            }
            $detalle_orden = $this->load->view('orden_detalle.php', $data, true);
            $respuesta = array("error" => false,
                "orden_id" => $orden_id,
                "detalle_orden" => $detalle_orden,
                "fecha_entrega" => $orden->fecha_entrega,
                "fecha_carguera" => $orden->fecha_carguera,
                "fecha_preparacion" => $orden->fecha_preparacion);
        } else {
            $respuesta = array("error" => "No existe informacion de la orden $orden_id");
        }

        header('Content-Type: application/json');
        echo json_encode($respuesta);
    }

    public function json_obtener_clonacion() {
        $orden_id = $this->input->post('orden_id');
        $nuevaOrdenId = $this->service_orden->obtenerOrdenClonada($orden_id);
        $clonacionOrdenId = false; //no hay reenvio
        if ($nuevaOrdenId) {
            $clonacionOrdenId = $nuevaOrdenId->id;
        }
        $respuesta = array("error" => !$clonacionOrdenId, "orden_id" => $orden_id, "clonacion_orden_id" => $clonacionOrdenId, "mensaje" => (!$clonacionOrdenId ? "" : "Clonacion creada previamente, orden #" . $clonacionOrdenId . ", para su cambio de estado a Activo debe guardar la fecha de entrega" ));

        header('Content-Type: application/json');
        echo json_encode($respuesta);
    }

    public function json_clonar_orden() {
        //vamos a copiar la orden
        $orden_id = $this->input->post('orden_id');
        $orden = $this->service_orden->obtenerOrden($orden_id);

        $objOrdenReenvio = array(
            "store_id" => $orden->store_id,
            "cliente_id" => $orden->cliente_id,
            "cliente_direccion_id" => $orden->cliente_direccion_id,
            "referencia_order_number" => $orden->referencia_order_number,
            "referencia_order_id" => $orden->referencia_order_id,
            "secuencial" => $orden->secuencial,
            "fecha_compra" => fechaActual(),
            "fecha_entrega" => $orden->fecha_entrega,
            "fecha_carguera" => $orden->fecha_carguera,
            "fecha_preparacion" => $orden->fecha_preparacion,
            "clonacion_orden_id" => $orden->id,
            "estado" => ESTADO_ORDEN_CLONADA);
        $nuevaOrdenId = $this->service_orden->crearOrden($objOrdenReenvio);
        if ($nuevaOrdenId) {
            //obtenemos los detalles
            $items = $this->service_orden->obtenerOrdenDetalle($orden_id);
            foreach ($items as $item) {
                $linea = array(
                    "orden_id" => $nuevaOrdenId,
                    "producto_id" => $item->producto_id,
                    "variante_id" => $item->variante_id,
                    "cantidad" => $item->cantidad,
                );
                $linea_id = $this->service_orden->crearOrdenItem($linea);

                //copiamos los accesorios del item al item nuevo
                $propiedades = $this->service_ecommerce->obtenerOrdenItemPropiedades($item->id);
                if ($propiedades) {
                    foreach ($propiedades as $propiedad) {
                        $this->service_ecommerce->crearOrdenItemPropiedad($linea_id, $propiedad->propiedad_id, $propiedad->valor);
                    }
                }
            }
        }

        $respuesta = array("error" => !$nuevaOrdenId, "nueva_orden_id" => $nuevaOrdenId, "mensaje" => (!$nuevaOrdenId ? "No se pudo duplicar orden, informe a sistemas por favor" : "Orden #" . $nuevaOrdenId . " creada, para su cambio de estado a Activo debe guardar la fecha de entrega"));

        header('Content-Type: application/json');
        echo json_encode($respuesta);
    }

    public function json_obtener_reenvio() {
        $orden_id = $this->input->post('orden_id');
        $nuevaOrdenId = $this->service_orden->obtenerOrdenReenvio($orden_id);
        $reenvioOrdenId = false; //no hay reenvio
        if ($nuevaOrdenId) {
            $reenvioOrdenId = $nuevaOrdenId->id;
        }
        $respuesta = array("error" => !$reenvioOrdenId, "orden_id" => $orden_id, "reenvio_orden_id" => $reenvioOrdenId, "mensaje" => (!$reenvioOrdenId ? "" : "Reenvio creado previamente, orden #" . $reenvioOrdenId . ", para su cambio de estado a Activo debe guardar la fecha de entrega" ));

        header('Content-Type: application/json');
        echo json_encode($respuesta);
    }

    public function json_reenviar_orden_total() {
        $orden_id = $this->input->post('orden_id');
        return $this->json_reenviar_orden($orden_id, true);
    }

    public function json_reenviar_orden_parcial() {
        $orden_id = $this->input->post('orden_id');
        return $this->json_reenviar_orden($orden_id, false);
    }

    private function json_reenviar_orden($orden_id, $total = false) {

        $nuevaOrdenId = $this->service_orden->obtenerOrdenReenvio($orden_id);
        if (!$nuevaOrdenId) {
            //vamos a copiar la orden
            $orden = $this->service_orden->obtenerOrden($orden_id);

            //veamos si no existe ya una orden de reenvio en estado ESTADO_ORDEN_REENVIADA
            $fechaEntrega = $this->service_logistica->calcularSiguienteFechaEntrega(new DateTime());
            $fechaCarguera = $this->service_ecommerce->calcularFechaCarguera($fechaEntrega);
            $objOrdenReenvio = array(
                "store_id" => $orden->store_id,
                "cliente_id" => $orden->cliente_id,
                "cliente_direccion_id" => $orden->cliente_direccion_id,
                "referencia_order_number" => $orden->referencia_order_number,
                "referencia_order_id" => $orden->referencia_order_id,
                "secuencial" => $orden->secuencial,
                "fecha_compra" => fechaActual(),
                "fecha_entrega" => $fechaEntrega,
                "fecha_carguera" => $fechaCarguera,
                "fecha_preparacion" => $this->service_ecommerce->calcularFechaPreparacion($fechaCarguera, $this->service_ecommerce->ordenContieneTinturados($orden->id)),
                "reenvio_orden_id" => $orden->id,
                "estado" => ESTADO_ORDEN_REENVIADA);
            $nuevaOrdenId = $this->service_orden->crearOrden($objOrdenReenvio);
            if ($nuevaOrdenId) {
                //obtenemos los detalles
                $items = $this->service_orden->obtenerOrdenDetalle($orden_id);
                foreach ($items as $item) {
                    $linea = array(
                        "orden_id" => $nuevaOrdenId,
                        "producto_id" => $item->producto_id,
                        "variante_id" => $item->variante_id,
                        "cantidad" => $item->cantidad,
                    );
                    $linea_id = $this->service_orden->crearOrdenItem($linea);

                    if ($total) {
                        //copiamos los accesorios del item al item nuevo
                        $propiedades = $this->service_ecommerce->obtenerOrdenItemPropiedades($item->id);
                        if ($propiedades) {
                            foreach ($propiedades as $propiedad) {
                                $this->service_ecommerce->crearOrdenItemPropiedad($linea_id, $propiedad->propiedad_id, $propiedad->valor);
                            }
                        }
                    }
                }
            }
        } else {
            $nuevaOrdenId = $nuevaOrdenId->id;
        }
        $respuesta = array("error" => !$nuevaOrdenId, "nueva_orden_id" => $nuevaOrdenId, "mensaje" => (!$nuevaOrdenId ? "No se pudo duplicar orden, informe a sistemas por favor" : "Orden #" . $nuevaOrdenId . " creada para reenvio, para su cambio de estado a Activo debe guardar la fecha de entrega"));

        header('Content-Type: application/json');
        echo json_encode($respuesta);
    }

}
