<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migracion extends MY_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model("ecommerce/service_ecommerce");
        $this->load->model("ecommerce/service_ecommerce_cliente");
        $this->load->model("ecommerce/service_ecommerce_orden");
        $this->load->model("ecommerce/service_ecommerce_producto");
        $this->load->model("ecommerce/service_ecommerce_logistica");
        $this->load->model("ecommerce/service_ecommerce_formula");
        $this->load->model("shopify/shopify_model");
    }

    public function convertirOrdenes() {
        set_time_limit(0);
        //obtenemos todas las ordenes shopify que no se han convertido a ordenes rosaholics
//        $this->service_ecommerce->convertirOrdenShopifyaOrdenRosaholics(2988351488163);
//        $this->service_ecommerce->convertirOrdenShopifyaOrdenRosaholicsMigracion(2130045960243);
//        die;
        $arr_shopify_orders = $this->shopify_model->obtenerOrdenesNoConvertidasMigracion();
        foreach ($arr_shopify_orders as $orden) {
            print_r($orden->order_number);
            echo "<br/>";
            $this->service_ecommerce->convertirOrdenShopifyaOrdenRosaholicsMigracion($orden->shopify_order_id);
        }
        $data['ordenes'] = "";
        $this->mostrarVista('sincronizacion.php', $data);
    }

}
