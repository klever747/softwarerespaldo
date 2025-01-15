<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Perfil extends MY_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model("seguridad/service_seguridad_perfil");
    }

    /*     * ******************* PERFIL ********************* */

    public function perfiles() {
        $texto_busqueda = "";
        $listadoPerfiles = false;
        $estado_id = false;
        $cuantos = 0;
        if ($this->input->post('btn_buscar') != null) {
            $texto_busqueda = $this->input->post('texto_busqueda');
            $estado_id = $this->input->post('estado_id');

            list($listadoPerfiles, $cuantos) = $this->service_seguridad_perfil->obtenerPerfil(false, $estado_id, $texto_busqueda);
        }
        $data['estado_id'] = $estado_id;
        $data['perfiles'] = $listadoPerfiles;
        $data['cuantos'] = $cuantos;
        $data['texto_busqueda'] = $texto_busqueda;
        $this->mostrarVista('perfiles.php', $data);
    }

    public function perfil_nuevo() {
        $this->perfil_obtener();
    }

    public function perfil_obtener() {
        $error = false;

        if ($this->input->post('id') != null) {
            $id = $this->input->post('id');
            $data['perfil'] = $this->service_seguridad_perfil->obtenerPerfil($id);
        } else {
            $data['perfil'] = $this->service_seguridad_perfil->obtenerNuevoPerfil();
        }
        $perfil_det = $this->load->view('perfil_edicion.php', $data, true);
        $respuesta = array("error" => (!$data['perfil'] ? true : false), "respuesta" => $perfil_det);

        header('Content-Type: application/json');
        echo json_encode($respuesta);
    }

    public function perfil_guardar() {

        $actualizacion = false;
        if ($this->input->post('id') != null) {
            $id = $this->input->post('id');

            $obj = $this->service_seguridad_perfil->obtenerPerfil($id);
        } else {
            $obj = $this->service_seguridad_perfil->obtenerNuevoPerfil();
        }
        $arr = array();
        if ($obj) {
            foreach ($obj as $field => $value) {
                if (!(strpos(strtoupper($field), 'CREACION_') === 0 || strpos(strtoupper($field), 'ACTUALIZACION_') === 0 || strpos(strtoupper($field), 'INFO_') === 0)) {
                    $arr[$field] = $this->input->post($field);
                }
            }
            if ($this->input->post('id') != null) {
                $actualizacion = $this->service_seguridad_perfil->actualizarPerfil($arr, true);
                if (!$actualizacion) {
                    $respuesta = 'Existe un problema durante la actualización';
                } else {
                    $respuesta = 'Registro actualizado';
                }
            } else {

                $actualizacion = $this->service_seguridad_perfil->crearPerfil($arr, true);
                if (!$actualizacion) {
                    $respuesta = 'Existe un problema durante la creación';
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

    public function perfil_eliminar() {

        $id = $this->input->post('id');
        $actualizacion = $this->service_seguridad_perfil->actualizarPerfil(array("id" => $id, "estado" => ESTADO_INACTIVO), true);
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
