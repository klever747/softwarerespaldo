<?php

use setasign\Fpdi\Fpdi;
use Smalot\PdfParser\Parser;

defined('BASEPATH') or exit('No direct script access allowed');

class Ingrediente extends MY_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model("ecommerce/service_ecommerce");
        $this->load->model("ecommerce/service_ecommerce_orden");
        $this->load->model("produccion/service_ingrediente");
    }

    public function ingredientes() {
        $texto_busqueda = "";
        $listadoIngredientes = false;
        $cuantos = 0;
        $data['session_finca'] = $this->session->userFincaId;
        $data['estado_id'] = 1;
        if ($this->input->post('btn_buscar') != null) {
            $texto_busqueda = $this->input->post('texto_busqueda');
            $estado_id = $this->input->post('estado_id');

            List($listadoIngredientes, $cuantos) = $this->service_ingrediente->obtenerIngrediente(false, $estado_id, $texto_busqueda);
        }
        $data['ingredientes'] = $listadoIngredientes;
        $data['cuantos'] = $cuantos;
        $data['url_busqueda'] = "produccion/Ingrediente/ingredientes";
        $data['texto_busqueda'] = $texto_busqueda;

        $this->mostrarVista('ingredientes.php', $data);
    }

    public function ingrediente_nuevo() {
        $this->ingrediente_obtener();
    }

    public function ingrediente_obtener() {
        $error = false;
        if ($this->input->post('id') != null) {
            $id = $this->input->post('id');
            $data['ingrediente'] = $this->service_ingrediente->obtenerIngrediente($id);
        } else {
            $data['ingrediente'] = $this->service_ingrediente->obtenerNuevoIngrediente();
        }


        $ingrediente_det = $this->load->view('ingrediente_edicion.php', $data, true);
        $respuesta = array("error" => (!$data['ingrediente'] ? true : false), "respuesta" => $ingrediente_det);

        header('Content-Type: application/json');
        echo json_encode($respuesta);
    }

    public function ingrediente_guardar() {
        $actualizacion = false;
        if ($this->input->post('id') != null) {
            $id = $this->input->post('id');
            $obj = $this->service_ingrediente->obtenerIngrediente($id);
        } else {
            $obj = $this->service_ingrediente->obtenerNuevoIngrediente();
        }

        $arr = array();
        if ($obj) {

            foreach ($obj as $field => $value) {
                if (!(strpos(strtoupper($field), 'CREACION_') === 0 || strpos(strtoupper($field), 'ACTUALIZACION_') === 0 || strpos(strtoupper($field), 'INFO_') === 0)) {
                    $arr[$field] = $this->input->post($field);
                }
            }
            if ($this->input->post('id') != null) {
                //verificar que el nombre del ingrediente no se repita
                if ($this->service_ingrediente->buscarIngredienteRepetido($id, $arr["nombre"], $arr["descripcion"], $arr["longitud"])) {
                    $actualizacion = false;
                    $respuesta = 'El nombre del ingrediente esta repetido y ';
                } else {
                    $actualizacion = $this->service_ingrediente->actualizarIngrediente($arr, true);
                }
                if (!$actualizacion) {
                    $respuesta = 'Existe un problema durante la actualizaci&oacute;n';
                } else {
                    $respuesta = 'Registro actualizado';
                }
            } else {
                //verificar que el nombre del ingrediente no se repita
                if ($this->service_ingrediente->buscarIngredienteRepetido($id = false, $arr["nombre"], $arr["descripcion"], $arr["longitud"])) {
                    $actualizacion = false;
                    $respuesta = 'El nombre del ingrediente esta repetido o ';
                } else {
                    $actualizacion = $this->service_ingrediente->crearIngrediente($arr, true);
                }
                if (!$actualizacion) {
                    $respuesta .= ' Existe un problema durante la creaci&oacute;n';
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

    public function ingrediente_eliminar() {

        $id = $this->input->post('id');
        $actualizacion = $this->service_ingrediente->eliminarIngrediente(array("id" => $id, "estado" => ESTADO_INACTIVO), true);
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