<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Shopify extends MY_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model("shopify/shopify_model");
    }

    public function index() {
//        $this->load->model("shopify/service_sincronizacion_shopify");
//        $productos = $this->service_sincronizacion_shopify->consultarProductos(1);
//        /*
//        foreach ($productos as $prod){
//            $prod->card = $this->load->view('product_card.php', $prod, true);
//            
//            $data['productos'][] = $prod;
//            
////            $id = $prod->id;
////            $title = $prod->title;            
////            $data['productos'][] = array(
////                "id" => $id,
////                "title" => $title,
////                "card" => $card,
////                );
//        }
//         * */
//        //$data['productos'] = $productos;
//        $data['claseBody'] = "hold-transition";
//        $this->mostrarVista('shopify_sync.php', $data);
    }

    public function scr_sincronizarOrdenes() {
        error_log("En sincronizar Ordenes");
        echo "INICIO Sincronizar Ordenes" . PHP_EOL;
        return "ok";
//        $this->load->model("shopify/service_sincronizacion_shopify");
//        echo $this->service_sincronizacion_shopify->sincronizarOrdenes($store_id);
//        echo "FIN Sincronizar Ordenes";
    }

    public function syncJson($tipo) {
        $store_id = $this->input->post('btn_value');

        if (isset($store_id)) {
            $this->load->model("shopify/service_sincronizacion_shopify");
            switch ($tipo) {
                case "productos":
                    $actualizacion = $this->service_sincronizacion_shopify->sincronizarProductos($store_id);
                    break;
                case "clientes":
                    $actualizacion = $this->service_sincronizacion_shopify->sincronizarClientes($store_id);
                    break;
                case "ordenes":
                    $actualizacion = $this->service_sincronizacion_shopify->sincronizarOrdenes($store_id);
                    break;

                default:
                    break;
            }
        } else {
            $actualizacion = array("error" => "No esta seleccionada la tienda a sincronizar");
        }
        header('Content-Type: application/json');
//        error_log("SyncProductos " . print_r($actualizacion, true));
        echo json_encode($actualizacion);
    }

    public function syncProductos() {
        return $this->syncJson("productos");
    }

    public function syncClientes() {
        return $this->syncJson("clientes");
    }

    public function syncOrdenes() {
        return $this->syncJson("ordenes");
    }

    public function sincronizacion() {
        $data['store_id'] = 1; //TODO wsanchez debe tomar este valor de la tienda en session o la tienda que tenga acceso el usuario del sistema
        $data['sel_store'] = $this->shopify_model->obtenerTiendasSel();
        $data['claseBody'] = "hold-transition";
        $this->mostrarVista('shopify_sync.php', $data);
    }

    public function productos() {

        $texto_busqueda = "";

        $data['store_id'] = 1;
        $data['productos'] = array();
        $cuantosProductos = 0;

        if ($this->input->post('btn_buscar') != null) {
            error_log("boton");
            $texto_busqueda = $this->input->post('texto_busqueda');
            error_log("texto");
            error_log(print_r($texto_busqueda, true));
            $data['store_id'] = $this->input->post('store_id');
            List($listadoProductos, $cuantosProductos) = $this->shopify_model->obtenerProductos($data['store_id'], $texto_busqueda);

            if ($listadoProductos) {
                foreach ($listadoProductos as $prod) {
                    $prod->image = json_decode($prod->image);
                    $prod->card = $this->load->view('product_card.php', $prod, true);
                    $data['productos'][] = $prod;
                }
            }
        }
        $data['cuantos'] = $cuantosProductos;
        error_log("texto");
        error_log(print_r($texto_busqueda, true));
        $data['texto_busqueda'] = $texto_busqueda;
        $data['sel_store'] = $this->shopify_model->obtenerTiendasSel();
        $this->mostrarVista('shopify_products.php', $data);
    }

    public function clientes() {
        $this->index();
    }

    public function ordenes() {

        $order_number = $texto_busqueda = $rango_busqueda = "";

        $tipo_calendario = 0;
        $data['store_id'] = 1;
        $data['ordenes'] = array();
        $cuantasOrdenes = 0;

        if ($this->input->post('btn_buscar') != null) {
            $texto_busqueda = $this->input->post('texto_busqueda');
            $order_number = $this->input->post('order_number');
            $rango_busqueda = $this->input->post('rango_busqueda');
            $tipo_calendario = $this->input->post('tipo_calendario');
            $data['store_id'] = $this->input->post('store_id');
            if ($this->input->post('order_number') != null) {
                $order_number = $this->input->post('order_number');
            }
            if ($this->input->post('busqueda') != null) {
                $texto_busqueda = $this->input->post('busqueda');
            }
            List($listadoOrdenes, $cuantasOrdenes) = $this->shopify_model->obtenerOrdenes($data['store_id'], $rango_busqueda, $order_number, $texto_busqueda);

            if ($listadoOrdenes) {
                foreach ($listadoOrdenes as $ord) {
                    //$ord->image = json_decode($ord->image);
                    $ord->card = $this->load->view('order_card.php', $ord, true);
                    $data['ordenes'][] = $ord;
                }
            }
        }
        $data['cuantos'] = $cuantasOrdenes;
        $data['order_number'] = $order_number;
        $data['texto_busqueda'] = $texto_busqueda;
        $data['rango_busqueda'] = $rango_busqueda;
        $data['tipo_calendario'] = $tipo_calendario;
        $data['sel_store'] = $this->shopify_model->obtenerTiendasSel();
        $this->mostrarVista('shopify_orders.php', $data);
    }

}

?>