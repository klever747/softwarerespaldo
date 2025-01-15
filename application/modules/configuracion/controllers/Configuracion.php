<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Configuracion extends MY_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model("configuracion/Service_configuracion");
    }

    public function index() {
        $texto_busqueda = "";
        $listadoMenu = false;
        $estado_id = false;
        $cuantos = 0;
        $sel_menu = 0;

        $listadoMenuHijos = false;

        if ($this->input->post('btn_buscar') != null) {
            $texto_busqueda = $this->input->post('texto_busqueda');
            $estado_id = $this->input->post('estado_id');
            List($listadoMenu, $cuantos) = $this->Service_configuracion->obtenerListaMenu(false, $estado_id, $texto_busqueda);
            $data['menueshijos'] = $this->Service_configuracion->obtenerListaMenu(false, $estado_id, $texto_busqueda, true);
        }

        $data['estado_id'] = $estado_id;
        $data['menues'] = $listadoMenu;
        $data['cuantos'] = $cuantos;

        $data['texto_busqueda'] = $texto_busqueda;
        $data['claseBody'] = "hold-transition";
        $this->mostrarVista('configuracion.php', $data);
    }

    public function menu_nuevo() {
        $this->obtenerMenu();
    }

    public function obtenerMenu() {
        $error = false;
        $data['variantes'] = false;
        if ($this->input->post('id') != null) {
            $id = $this->input->post('id');
            $data['operacion'] = '<i class="fa fa-pencil-alt my-float"></i> Editar opción';
            $data['menu'] = $this->Service_configuracion->obtenerListaMenu($id);
        } else {
            $data['operacion'] = '<i class="fa fa-plus my-float"></i> Registro de nueva opción';
            $data['menu'] = $this->Service_configuracion->obtenerNuevoMenu();
            $data['variantes'] = false;
        }

        $data['sel_menu'] = $this->Service_configuracion->obtenerMenuSel();

        $menu_det = $this->load->view('configuracion_edicion.php', $data, true);
        $respuesta = array("error" => (!$data['menu'] ? true : false), "respuesta" => $menu_det);
        header('Content-Type: application/json');
        echo json_encode($respuesta);
    }

    public function menu_guardar() {
        $actualizacion = false;
        if ($this->input->post('id') != null) {
            $id = $this->input->post('id');
            $obj = $this->Service_configuracion->obtenerListaMenu($id);
        } else {
            $obj = $this->Service_configuracion->obtenerNuevoMenu();
        }
        $arr = array();
        if ($obj) {

            foreach ($obj as $field => $value) {
                if (!(strpos(strtoupper($field), 'CREACION_') === 0 || strpos(strtoupper($field), 'ACTUALIZACION_') === 0 || strpos(strtoupper($field), 'INFO_') === 0)) {
                    $arr[$field] = $this->input->post($field);
                }
            }
            if ($this->input->post('id') != null) {
                $actualizacion = $this->Service_configuracion->actualizarMenu($arr, true);
                if (!$actualizacion) {
                    $respuesta = 'Existe un problema durante la actualizaci&oacute;n';
                } else {
                    $respuesta = 'Registro actualizado';
                }
            } else {
                $actualizacion = $this->Service_configuracion->crearMenu($arr, true);
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

    public function menu_eliminar() {

        $id = $this->input->post('id');
        $actualizacion = $this->Service_configuracion->actualizarMenu(array("id" => $id, "estado" => ESTADO_INACTIVO), true);
        if (!$actualizacion) {
            $respuesta = 'Existe un problema durante la inactivaci&oacute;n';
        } else {
            $respuesta = 'Registro inactivado';
        }
        $respuesta = array("error" => !$actualizacion, "respuesta" => (!$actualizacion ? 'Existe un problema durante la inactivaci&oacute;n' : 'Registro inactivado'));
        header('Content-Type: application/json');
        echo json_encode($respuesta);
    }

}

?>