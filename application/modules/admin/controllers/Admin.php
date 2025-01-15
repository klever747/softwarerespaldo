<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends MY_Controller {

    function __construct() {
        parent::__construct();
//        $this->load->model("ecommerce/service_ecommerce");
//        $this->load->model("ecommerce/service_ecommerce_cliente");
//        $this->load->model("ecommerce/service_ecommerce_orden");
//        $this->load->model("ecommerce/service_ecommerce_producto");
//        $this->load->model("ecommerce/service_ecommerce_logistica");
//        $this->load->model("ecommerce/service_ecommerce_formula");
//        $this->load->model("produccion/service_produccion");
//        $this->load->model("produccion/service_preparacion");
//        $this->load->model("manufactura/service_manufactura");
    }

    public function index() {
        $data['claseBody'] = "hold-transition";
        $this->mostrarVista('admin.php', $data);
    }

    public function backup() {
        $data['claseBody'] = "hold-transition";
        $this->mostrarVista('backup.php', $data);
    }

}

?>