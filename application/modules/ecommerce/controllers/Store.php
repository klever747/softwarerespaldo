<?php

use SebastianBergmann\Environment\Console;

defined('BASEPATH') OR exit('No direct script access allowed');

class Store extends MY_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model("ecommerce/service_ecommerce");
        $this->load->model("ecommerce/service_ecommerce_store");
    }

    /*     * ******************* TIENDA ********************* */

    public function stores() {
        $texto_busqueda = "";
        $listadoTiendas = false;
        $estado_id = false;
        $cuantos = 0;
        if ($this->input->post('btn_buscar') != null) {
            $texto_busqueda = $this->input->post('texto_busqueda');
            $estado_id = $this->input->post('estado_id');

            List($listadoTiendas, $cuantos) = $this->service_ecommerce_store->obtenerStore(false, $estado_id, $texto_busqueda);
        }
        $data['estado_id'] = $estado_id;
        $data['stores'] = $listadoTiendas;
        $data['cuantos'] = $cuantos;

        $data['texto_busqueda'] = $texto_busqueda;

        $this->mostrarVista('stores.php', $data);
    }

    public function store_nuevo() {
        $this->store_obtener();
    }

    public function store_obtener() {
        $error = false;
        if ($this->input->post('id') != null) {
            $id = $this->input->post('id');
            $data['tienda'] = $this->service_ecommerce_store->obtenerStore($id);

            $data['variantes'] = $this->service_ecommerce_store->obtenerShopifyParametros($data['tienda']->id, ESTADO_ACTIVO);
            $data['variantes'] = $this->load->view('store_shopify_listado.php', $data, true);
        } else {
            $data['tienda'] = $this->service_ecommerce_store->obtenerNuevaTienda();
            $data['variantes'] = false;
        }


        $store_det = $this->load->view('store_edicion.php', $data, true);
        $respuesta = array("error" => (!$data['tienda'] ? true : false), "respuesta" => $store_det);

        header('Content-Type: application/json');
        echo json_encode($respuesta);
    }

    public function tienda_guardar() {
        $actualizacion = false;
        if ($this->input->post('id') != null) {
            $id = $this->input->post('id');
            $obj = $this->service_ecommerce_store->obtenerStore($id);
        } else {
            $obj = $this->service_ecommerce_store->obtenerNuevaTienda();
        }

        $arr = array();
        if ($obj) {

            foreach ($obj as $field => $value) {
                if (!(strpos(strtoupper($field), 'CREACION_') === 0 || strpos(strtoupper($field), 'ACTUALIZACION_') === 0 || strpos(strtoupper($field), 'INFO_') === 0)) {
                    $arr[$field] = $this->input->post($field);
                }
            }
            if ($this->input->post('id') != null) {
                $actualizacion = $this->service_ecommerce_store->actualizarTienda($arr, true);
                if (!$actualizacion) {
                    $respuesta = 'Existe un problema durante la actualizaci&oacute;n';
                } else {
                    $respuesta = 'Registro actualizado';
                }
            } else {
                $actualizacion = $this->service_ecommerce_store->crearTienda($arr, true);
                if (!$actualizacion) {
                    $respuesta = 'Existe un problema durante la creaci&oacute;n';
                } else {
                    $respuesta = 'Registro creado';
                }
            }
        } else {
            $respuesta = 'No se encuentra el registro';
        }

        $respuesta = array("error" => !$actualizacion, "respuesta" => $respuesta);
        header('Content-Type: application/json');
        echo json_encode($respuesta);
    }

    public function store_eliminar() {

        $id = $this->input->post('id');
        $actualizacion = $this->service_ecommerce_store->actualizarTienda(array("id" => $id, "estado" => ESTADO_INACTIVO), true);
        if (!$actualizacion) {
            $respuesta = 'Existe un problema durante la inactivaci&oacute;n';
        } else {
            $respuesta = 'Registro inactivado';
        }
        $respuesta = array("error" => !$actualizacion, "respuesta" => (!$actualizacion ? 'Existe un problema durante la inactivaci&oacute;n' : 'Registro inactivado'));
        header('Content-Type: application/json');
        echo json_encode($respuesta);
    }

    /*     * ****************** STORE-SHOPIFY ********************* */

    public function store_shopify_nuevo() {
        $this->store_shopify_obtener();
    }

    public function store_shopify_obtener() {
        $error = false;

        $data['store_id'] = $this->input->post('store_id');

        if ($this->input->post('id') != null) {
            $id = $this->input->post('id');
            $data['variante'] = $this->service_ecommerce_store->obtenerStoreShopify($id);
        } else {
            $data['variante'] = $this->service_ecommerce_store->obtenerNuevoStoreShpify($data['store_id']);
        }

        $tienda = $this->service_ecommerce_store->obtenerStore($data['store_id']);
        $data['store'] = $tienda->store_name;
        $variante = $this->load->view('store_shopify_edicion.php', $data, true);

        $respuesta = array("error" => (!$data['variante'] ? true : false), "respuesta" => $variante);

        header('Content-Type: application/json');
        echo json_encode($respuesta);
    }

    public function store_shopify_guardar() {
        $actualizacion = false;
        $data['store_id'] = $this->input->post('store_id');
        if ($this->input->post('id') != null) {
            $id = $this->input->post('id');
            $obj = $this->service_ecommerce_store->obtenerStoreShopify($id);
        } else {
            $obj = $this->service_ecommerce_store->obtenerNuevoStoreShpify($data['store_id']);
        }
        $arr = array();
        if ($obj) {

            foreach ($obj as $field => $value) {
                if (!(strpos(strtoupper($field), 'CREACION_') === 0 || strpos(strtoupper($field), 'ACTUALIZACION_') === 0 || strpos(strtoupper($field), 'INFO_') === 0)) {
                    $arr[$field] = $this->input->post($field);
                }
            }
            if ($this->input->post('id') != null) {
                $actualizacion = $this->service_ecommerce_store->actualizarStoreShopify($arr, true);
                if (!$actualizacion) {
                    $respuesta = 'Existe un problema durante la actualizaci&oacute;n';
                } else {
                    $respuesta = 'Registro actualizado';
                }
            } else {
                $actualizacion = $this->service_ecommerce_store->crearStoreShopify($arr, true);
                if (!$actualizacion) {
                    $respuesta = 'Existe un problema durante la creaci&oacute;n';
                } else {
                    $respuesta = 'Registro creado';
                }
            }
        } else {
            $respuesta = 'No se encuentra el registro';
        }

        $respuesta = array("error" => !$actualizacion, "respuesta" => $respuesta);
        header('Content-Type: application/json');
        echo json_encode($respuesta);
    }

    public function store_shopify_eliminar() {
        $id = $this->input->post('id');
        $actualizacion = $this->service_ecommerce_store->actualizarStoreShopify(array("id" => $id, "estado" => ESTADO_INACTIVO), true);
        $respuesta = array("error" => !$actualizacion, "respuesta" => (!$actualizacion ? 'Existe un problema durante la inactivaci&oacute;n' : 'Registro inactivado'));
        header('Content-Type: application/json');
        echo json_encode($respuesta);
    }

}
