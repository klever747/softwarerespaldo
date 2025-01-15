<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Finca extends MY_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model("generales/service_general_finca");
        $this->load->model("produccion/service_logistica");
    }

    /*     * ******************* Finca ********************* */

    public function fincas() {
        $texto_busqueda = "";
        $listadoFincas = false;
        $estado_id = false;
        $cuantos = 0;
        if ($this->input->post('btn_buscar') != null) {
            $texto_busqueda = $this->input->post('texto_busqueda');
            $estado_id = $this->input->post('estado_id');

            list($listadoFincas, $cuantos) = $this->service_general_finca->obtenerFinca(false, $estado_id, $texto_busqueda);
        }
        $data['estado_id'] = $estado_id;
        $data['fincas'] = $listadoFincas;
        $data['cuantos'] = $cuantos;
        $data['texto_busqueda'] = $texto_busqueda;
        $this->mostrarVista('fincas.php', $data);
    }

    public function finca_nuevo() {
        $this->finca_obtener();
    }

    public function finca_obtener() {
        $error = false;
        $data['op_actualizar'] = false;
        if ($this->input->post('id') != null) {
            $id = $this->input->post('id');

            $data['op_actualizar'] = true;
            $data['finca'] = $this->service_general_finca->obtenerFinca($id);
        } else {
            //obtener el listado de fincas que tienen precios asignados
            $data['fincas_clonar'] = $this->service_general_finca->obtenerFincaConPrecios();
            $data['finca'] = $this->service_general_finca->obtenerNuevoFinca();
        }
        $finca_det = $this->load->view('finca_edicion.php', $data, true);
        $respuesta = array("error" => (!$data['finca'] ? true : false), "respuesta" => $finca_det);

        header('Content-Type: application/json');
        echo json_encode($respuesta);
    }

    public function finca_guardar() {

        $actualizacion = false;
        if ($this->input->post('id') != null) {
            $id = $this->input->post('id');

            $obj = $this->service_general_finca->obtenerFinca($id);
        } else {
            $obj = $this->service_general_finca->obtenerNuevoFinca();
        }
        $arr = array();
        if ($obj) {
            foreach ($obj as $field => $value) {
                if (!(strpos(strtoupper($field), 'CREACION_') === 0 || strpos(strtoupper($field), 'ACTUALIZACION_') === 0 || strpos(strtoupper($field), 'INFO_') === 0)) {
                    $arr[$field] = $this->input->post($field);
                }
            }
            if ($this->input->post('id') != null) {
                $actualizacion = $this->service_general_finca->actualizarFinca($arr, true);
                if (!$actualizacion) {
                    $respuesta = 'Existe un problema durante la actualización';
                } else {
                    $respuesta = 'Registro actualizado';
                }
            } else {
                $finca_id_clonar = $arr['precio'];
                unset($arr['precio']);
                $arr_precios = $this->service_general_finca->obtenerFincaConPrecios($finca_id_clonar, $obt_precio = true);
                $actualizacion = $this->service_general_finca->crearFinca($arr, true);
                if (!$actualizacion) {
                    $respuesta = 'Existe un problema durante la creación';
                } else {
                    $respuesta = 'Registro creado';
                    $finca_id_creado = $actualizacion;
                    //metodo para clonarme los precios de una finca al momento de crearla
                    $arr_precios = $this->service_general_finca->obtenerFincaConPrecios($finca_id_clonar, $obt_precio = true);
                    $this->service_general_finca->clonacionDePrecios($arr_precios, $finca_id_creado);
                }
            }
        } else {
            $respuesta = 'No se encuentra el registro';
        }

        $respuesta = array("error" => !$actualizacion, "respuesta" => $respuesta);
        header('Content-Type: application/json');
        echo json_encode($respuesta);
    }

    public function finca_eliminar() {

        $id = $this->input->post('id');
        $actualizacion = $this->service_general_finca->actualizarFinca(array("id" => $id, "estado" => ESTADO_INACTIVO), true);
        if (!$actualizacion) {
            $respuesta = 'Existe un problema durante la inactivación';
        } else {
            $respuesta = 'Registro inactivado';
        }
        $respuesta = array("error" => !$actualizacion, "respuesta" => (!$actualizacion ? 'Existe un problema durante la inactivación' : 'Registro inactivado'));
        header('Content-Type: application/json');
        echo json_encode($respuesta);
    }

}
