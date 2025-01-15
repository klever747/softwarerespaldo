<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Generales extends MY_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model("service_general");
    }

    public function configuracion() {
        //mostremos todas las configuraciones existentes
        $arr_configuracion = $this->service_general->obtenerConfiguracion();

        foreach ($arr_configuracion as $configuracion) {
            var_dump($configuracion);
        }

        //mostramos por pantalla todas las opciones posibles de cambiar
        //no implementamos paginacion porque no son catalogos
        //si no valores criticos del sistema
    }

}
