<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Empaque extends MY_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model("produccion/service_orden");
        $this->load->model("ecommerce/service_ecommerce");
        $this->load->model("ecommerce/service_ecommerce_cliente");
        $this->load->model("ecommerce/service_ecommerce_orden");
        $this->load->model("ecommerce/service_ecommerce_producto");
        $this->load->model("ecommerce/service_ecommerce_logistica");
        $this->load->model("ecommerce/service_ecommerce_formula");
        $this->load->model("produccion/service_produccion");
        $this->load->model("produccion/service_logistica");
        $this->load->model("produccion/service_empaque");
    }

    public function ordenes() {
        $data['order_number'] = $data['referencia_order_number'] = $data['texto_busqueda'] = $data['rango_busqueda'] = "";
        $data['tipo_calendario'] = 0;
        $data['store_id'] = 1;
        $data['ordenes'] = array();
        $data['cuantos'] = 0;
        $data['empacado'] = 'T';
        $data['preparado'] = 'T';
        $data['terminado'] = 'T';
        $data['reenviado'] = 'T';
        $data['perfil'] = PANTALLA_EMPAQUE; //perfil_empaque
        $data['sel_store'] = $this->service_ecommerce->obtenerTiendasSel();
        $data['orden_actual'] = 0;
        $data['tracking_number'] = '';
        $data['tarjeta_impresa'] = 'T';
        $data['con_tracking_number'] = 'T';
        $data['totales'] = false;
        $data['finca_id'] = 0;
        $data['session_finca'] = $this->session->userFincaId;
        $data['orden_estado_id'] = 'A';
        $data['sel_finca'] = $this->service_ecommerce_orden->obtenerFincaSel();
        $data['sel_orden_estado'] = $this->service_ecommerce_orden->obtenerSelEstadoOrden();
        $data['tipo_caja'] = '';
        $data['sel_tipo_caja'] = $this->service_logistica->obtenerTiposDeCajas();
        if ($this->input->post('btn_buscar') != null) {
            $data['texto_busqueda'] = $this->input->post('texto_busqueda');
            $data['order_number'] = $this->input->post('order_number');
            $data['referencia_order_number'] = $this->input->post('referencia_order_number');
            $data['rango_busqueda'] = $this->input->post('rango_busqueda');
            $data['tipo_calendario'] = $this->input->post('tipo_calendario');
            $data['store_id'] = $this->input->post('store_id');
            $data['empacado'] = $this->input->post('empacado');
            $data['preparado'] = $this->input->post('preparado');
            $data['terminado'] = $this->input->post('terminado');
            $data['tracking_number'] = $this->input->post('tracking_number');
            $data['tarjeta_impresa'] = $this->input->post('tarjeta_impresa');
            $data['tipo_caja_id'] = $this->input->post('tipo_caja_id');
            $data['tipo_caja'] = $this->input->post('tipo_caja');
            if ($this->input->post('order_number') != null) {
                $data['order_number'] = $this->input->post('order_number');
            }
            if ($this->input->post('con_tracking_number')) {
                $data['con_tracking_number'] = $this->input->post('con_tracking_number');
            }
            if ($this->input->post('busqueda') != null) {
                $data['texto_busqueda'] = $this->input->post('busqueda');
            }
            $data['finca_id'] = $this->input->post('finca_id');
//            $data['orden_estado_id'] = $this->input->post('orden_estado_id');//por el momento solo las ordenes en estado Activo
            $listadoOrdenes = false;
            if (!empty($data['tracking_number'])) {
                $orden_caja = $this->service_logistica->buscarTrackingNumber($data['tracking_number']);
                if (!$orden_caja && is_numeric($data['tracking_number'])) {
                    //busquemos con el tracking_number como numero de caja
                    $orden_caja = $this->service_logistica->buscarCajaId($data['tracking_number']);
                }

                if ($orden_caja) {
                    //marco la caja como empacada
                    if ($orden_caja->empacada == 'S') {
                        $data['error'] = 'ERROR CAJA ' . $data['tracking_number'] . ' YA EMPACADA!!!! ';
                    } else {
                        $accion = $this->service_empaque->ordenCajaEmpacada($orden_caja->id);
                        $data['exito'] = 'Empacada ' . $data['tracking_number'];
                    }

                    List($listadoOrdenes, $cuantasOrdenes) = $this->service_ecommerce_orden->obtenerOrdenesporCaja($data);
                    if ($cuantasOrdenes == 1) {
                        $data['orden_actual'] = $orden_caja->orden_id;
                    }
                } else {
                    $data['error'] = "No existe informacion para los datos ingresados";
                }
            } else {
                List($listadoOrdenes, $cuantasOrdenes) = $this->service_ecommerce_orden->obtenerOrdenesporCaja($data);
            }
            //TODO esta parte hay que mejorar para que la carga de las tarjetas sea asincrona y se cargue aun más rapido la pantalla
            if ($listadoOrdenes) {
                foreach ($listadoOrdenes as $orden) {
                    $orden->detalle = $this->service_orden->obtenerOrdenDetalle($orden->orden_id);
                    $orden->tag = '';
                    $orden->producto_filtro = true;
                    if ((empty($data['order_id'])) && (empty($data['order_number'])) && (!empty($data['texto_busqueda']))) {
                        $orden->producto_filtro = false;
                    }
                    $orden->items = $this->service_ecommerce_orden->obtenerOrdenItems($orden->orden_id);
                    ////////////////////////////
                    $items = $this->service_ecommerce_orden->obtenerOrdenItems($orden->orden_id);

                    if (!$items) {
                        $items = array();
                    } else {
                        foreach ($items as $k => $item) {
                            if ((empty($data['order_id'])) && (empty($data['order_number'])) && (!empty($data['texto_busqueda'])) && (strpos(strtoupper($item->info_producto_titulo), strtoupper($data['texto_busqueda'])) !== false)) {
                                error_log("Si cumple filtro producto");
                                $orden->producto_filtro = true;
                            }

                            $cajas = $this->service_ecommerce_logistica->obtenerOrdenItemCaja($item->id);
                            $items[$k]->cajas = $cajas;

                            $propiedades = $this->service_ecommerce->obtenerOrdenItemPropiedades($item->id);
                            $item->propiedades = array();
                            if ($propiedades) {
                                $item->propiedades = $propiedades;
                                foreach ($propiedades as $propiedad) {
                                    if ($propiedad->propiedad_id == 19) {//standing order
                                        $orden->tag .= 'STANDING ORDER';
                                    }
                                }
                            }
                        }
                    }
                    $orden->items = $items;
                    ///////////////////////////
                    $orden->perfil = $data['perfil'];
                    $orden->empacado = $data['empacado'];
//                    $orden->producto_filtro = $data['texto_busqueda'];
                    $orden->filtro_tarjeta_impresa = $data['tarjeta_impresa'];
                    $orden->card = $this->load->view('ecommerce/orden_card.php', $orden, true);
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
            $data['totales'] = $this->service_ecommerce_orden->obtenerTotales($filtro);

            $data['cuantos'] = $cuantasOrdenes;
        }

        $data['orden_caja_actual'] = 0;
        $data['tracking_number'] = '';
        $data['orden'] = '';
        $data['orden_actual'] = 0;
        $data['url_busqueda'] = "produccion/empaque/ordenes";
        $this->mostrarVista('ecommerce/ordenes.php', $data);
    }

    public function caja_empacada() {
        $orden_caja_id = $this->input->post('orden_caja_id');
        $accion = $this->service_empaque->ordenCajaEmpacada($orden_caja_id);
        $respuesta = array("error" => !$accion ? true : false, "respuesta" => $accion, "mensaje" => !$accion ? "Problemas en la ejecuci&oacute;n" : "Caja marcada como preparada");
        header('Content-Type: application/json');
        echo json_encode($respuesta);
    }

    public function caja_no_empacada() {
        $orden_caja_id = $this->input->post('orden_caja_id');
        $accion = $this->service_empaque->ordenCajaNoEmpacada($orden_caja_id);
        $respuesta = array("error" => !$accion ? true : false, "respuesta" => $accion);
        header('Content-Type: application/json');
        $respuesta = array("error" => !$accion ? true : false, "respuesta" => $accion, "mensaje" => !$accion ? "Problemas en la ejecuci&oacute;n" : "Caja marcada como No preparada");
        echo json_encode($respuesta);
    }

    public function kardex() {
        $cuantasCajas = 0;
        $data['tipo_calendario'] = 0;
        $data['rango_busqueda'] = '';
        $data['cajas'] = array();
        $data['store_id'] = 1;
        $data['session_finca'] = $this->session->userFincaId;
        $data['sel_store'] = $this->service_ecommerce->obtenerTiendasSel();
        $data['tipo_caja'] = '';
        if ($this->input->post('btn_buscar') != null) {
            $data['rango_busqueda'] = $this->input->post('rango_busqueda');
            $data['tipo_calendario'] = $this->input->post('tipo_calendario');
            $data['store_id'] = $this->input->post('store_id');
            $data['tipo_caja'] = $this->input->post('tipo_caja');
            $data['tracking_number'] = $this->input->post('tracking_number');
            $orden_caja_id = false;
            if (!empty($data['tracking_number'])) {
                $orden_caja = $this->service_logistica->buscarTrackingNumber($data['tracking_number']);
                if (!$orden_caja) {
                    //busquemos con el tracking_number como numero de caja
                    $orden_caja = $this->service_logistica->buscarCajaId($data['tracking_number']);
                }

                if ($orden_caja) {
                    //marco la caja como kardex_check
                    if ($orden_caja->kardex_check == 'S') {
                        $data['error'] = 'ERROR CAJA ' . $data['tracking_number'] . ' YA EN KARDEX CON ' . $orden_caja->info_nombre_caja;
                    } else {
                        $accion = $this->service_empaque->ordenCajaKardexCheck($orden_caja->id, $data['tipo_caja']);
                        $data['exito'] = 'Empacada ' . $data['tracking_number'];
                    }
                    $orden_caja_id = $orden_caja->id;
                }
            }
            List($listadoCajas, $cuantasCajas) = $this->service_empaque->obtenerCajas($data['store_id'], $data['rango_busqueda'], $data['tipo_calendario'], $data['tipo_caja'], $orden_caja_id);
//            if ($cuantasCajas == 1) {
//                $data['orden_actual'] = $orden_caja->orden_id;
//            }

            $data['tabla_datos'] = $this->tablaDatosKardex($listadoCajas);
        }

        $data['tracking_number'] = '';
        $data['cuantos'] = $cuantasCajas;
        $data['url_busqueda'] = "produccion/empaque/kardex";
        $data['sel_tipo_caja'] = $this->service_logistica->obtenerTiposDeCajas();
        $this->mostrarVista('produccion/kardex/kardex.php', $data);
    }

    private function tablaDatosKardex($data_tabla) {
        $data_tabla['cajas'] = $data_tabla;
        return $this->load->view('produccion/kardex/kardex_listado.php', $data_tabla, true);
    }

}
