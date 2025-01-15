<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends MY_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model("produccion/service_produccion");
        $this->load->model("dashboard/service_dashboard");
    }

    public function index() {
        $data['session_finca'] = $this->session->userFincaId;

        $arrayfinca = explode(",", $data['session_finca']);
        $data['arrayfinca'] = $arrayfinca;
        $data['fechaactual'] = date('Y-m-d - Y-m-d');
        $data['reenviado'] = 'T';
        $data['store_id'] = 0;
        $data['rango_busqueda'] = '';
        $data['tipo_calendario'] = 0;
        $data['totales'] = false;
        $detalle = '';
        $data['detalle'] = $detalle;
        $data['claseBody'] = "hold-transition";
        if ($this->input->post('btn_buscar') != null) {
            $data['rango_busqueda'] = $this->input->post('rango_busqueda');
            $data['fechaactual'] = $this->input->post('rango_busqueda');
            $data['tipo_calendario'] = $this->input->post('tipo_calendario');
        }
        $arrRango = explode(" - ", $data['fechaactual']);
        $fecha = date_format(DateTime::createFromFormat(FORMATO_FECHA, convertirFechaBD($arrRango[0])), 'Y-m-d 00:00:00');
        $fechaFin = date_format(DateTime::createFromFormat(FORMATO_FECHA, convertirFechaBD($arrRango[1])), 'Y-m-d 23:59:59');

        $data['ordenesActual'] = $this->service_dashboard->ordenDiaria($fecha, $fechaFin, $data['tipo_calendario']);
        $data['ordenesActiva'] = $this->service_dashboard->ordenDiaria($fecha, $fechaFin, $data['tipo_calendario'], ESTADO_ACTIVO);
        $data['ordenesreenviada'] = $this->service_dashboard->ordenDiaria($fecha, $fechaFin, $data['tipo_calendario'], ESTADO_ACTIVO, true);
        $data['ordenesError'] = $this->service_dashboard->ordenDiaria($fecha, $fechaFin, $data['tipo_calendario'], ESTADO_ERROR);
        $data['ordenesCancelada'] = $this->service_dashboard->ordenDiaria($fecha, $fechaFin, $data['tipo_calendario'], ESTADO_ORDEN_CANCELADA);
        $data['ordensintraking'] = $this->service_dashboard->ordensintraking($fecha, $fechaFin, $data['tipo_calendario']);
        $data['ordenbonchada'] = $this->service_dashboard->ordenbonchada($fecha, $fechaFin, $data['tipo_calendario'], 'S');
        $data['ordenvestida'] = $this->service_dashboard->ordenvestida($fecha, $fechaFin, $data['tipo_calendario'], 'S');
        $data['ordenbonchadaT'] = $this->service_dashboard->ordenbonchada($fecha, $fechaFin, $data['tipo_calendario'], 'N');
        $data['ordenvestidaT'] = $this->service_dashboard->ordenvestida($fecha, $fechaFin, $data['tipo_calendario'], 'N');
        $data['totalcajas'] = $this->service_dashboard->cajasdiaria($fecha, $fechaFin, $data['tipo_calendario']);
        $data['totalcajasnodefinidas'] = $this->service_dashboard->cajasindefinida($fecha, $fechaFin, $data['tipo_calendario']);
        $data['fincanodefinidas'] = $this->service_dashboard->fincanodefinida($fecha, $fechaFin, $data['tipo_calendario']);
        $this->mostrarVista('dashboard.php', $data);
    }

}

?>