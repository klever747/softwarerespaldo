<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Ejecucion extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model("ecommerce/service_ecommerce");
        $this->load->model("ecommerce/service_ecommerce_cliente");
        $this->load->model("ecommerce/service_ecommerce_orden");
        $this->load->model("ecommerce/service_ecommerce_producto");
        $this->load->model("ecommerce/service_ecommerce_logistica");
        $this->load->model("ecommerce/service_ecommerce_formula");
        $this->load->model("shopify/shopify_model");
        
        $this->load->model("produccion/service_orden");
        $this->load->model("produccion/service_sku_algoritmo");
        
        $this->load->model("produccion/service_logistica");
    }

    public function ok() {
        echo "ok";
        $this->load->model("shopify/service_sincronizacion_shopify");
        $tiendas = $this->shopify_model->obtenerTiendas();
        foreach ($tiendas as $tienda) {
            print_r($tienda);
            $sincronizacion = $this->service_sincronizacion_shopify->sincronizarOrdenes($tienda->id);
            print_r($sincronizacion);
        }

        $this->convertirOrdenesShopifyaRosaholics();
    }

    public function convertirOrdenesShopifyaRosaholics($shopify_order_id = false) {
        $this->session->set_userdata("userId", 1); //wsanchez por default en la ejecucion de acciones
        $this->load->model("shopify/shopify_model");

        if ($shopify_order_id) {
            $this->service_ecommerce->convertirOrdenShopifyaOrdenRosaholics($shopify_order_id);
            die;
        }
        $arr_shopify_orders = $this->shopify_model->obtenerOrdenesNoConvertidas();
        $total = $exitos = $errores = 0;
        if ($arr_shopify_orders) {
            foreach ($arr_shopify_orders as $orden) {
                $total++;
                print_r($orden->order_number);
                echo "<br/>\n";
                $ejecucion = false;
                try{
                    $ejecucion = $this->service_ecommerce->convertirOrdenShopifyaOrdenRosaholics($orden->shopify_order_id);
                } catch (Exception $ex) {
error_log($ejecucion);
print_r("EXCEPTION $orden->order_number");
                }
                if ($ejecucion) {
                    $exitos++;
                } else {
                    $errores++;
                }
            }
        }
        var_dump($total);
        var_dump($exitos);
        var_dump($errores);
    }

}
