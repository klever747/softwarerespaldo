<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Webservice extends MY_Controller {

    function __construct() {
        parent::__construct();
        //$this->load->model("generales/service_ws.php");
    }

    public function index() {
        $data = [];
        $this->mostrarVista('web_service.php', $data);
    }

}
