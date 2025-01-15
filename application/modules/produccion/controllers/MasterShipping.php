<?php

use setasign\Fpdi\Fpdi;
use Smalot\PdfParser\Parser;

defined('BASEPATH') or exit('No direct script access allowed');

class MasterShipping extends MY_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model("ecommerce/service_ecommerce");
        $this->load->model("ecommerce/service_ecommerce_cliente");
        $this->load->model("ecommerce/service_ecommerce_orden");
        $this->load->model("ecommerce/service_ecommerce_producto");
        $this->load->model("ecommerce/service_ecommerce_logistica");
        $this->load->model("ecommerce/service_ecommerce_formula");
        $this->load->model("produccion/service_produccion");
        $this->load->model("produccion/service_logistica");
        $this->load->model("produccion/service_sku_algoritmo");
        $this->load->model("generales/service_excel");
        $this->load->model("generales/service_general_finca");
        $this->load->model("produccion/service_orden");
        $this->load->model("produccion/service_master_shipping");
    }

    /* -------------------------------------Administracion Master Shipping--------------------------------------- */

    public function masterShipping() {
        $data['tipo_calendario_unico'] = 0;
        $data['estado_id'] = 1;
        $data['finca_id'] = 0;
        $cuantos = 0;
        $listadoMaster = false;
        $data['sel_finca'] = $this->service_general_finca->obtenerSelFinca();
        $data['session_finca'] = $this->session->userFincaId;
        $data['fecha_busqueda'] = '';
        $data['text_busqueda'] = '';
        if ($this->input->post('btn_buscar') != null) {
            $data['finca_id'] = $this->input->post('finca_id');
            $data['fecha_busqueda'] = $this->input->post('fecha_busqueda');
            $data['tipo_calendario_unico'] = $this->input->post('tipo_calendario_unico');
            $data['texto_busqueda'] = $this->input->post('text_busqueda');
            List($listadoMaster, $cuantos) = $this->service_master_shipping->obtenerMasterShipping(false, $data['finca_id'], $data['fecha_busqueda'], $data['tipo_calendario_unico'], $data['texto_busqueda']);
        }
        $data['cuantos'] = $cuantos;
        $data['listadoMaster'] = $listadoMaster;
        $data['url_busqueda'] = "produccion/MasterShipping/masterShipping";
        $this->mostrarVista('master_shipping.php', $data);
    }

    public function master_shipping_nuevo() {
        $this->shipping_obtener();
    }

    public function shipping_obtener() {
        $error = false;
        if ($this->input->post('id') != null) {
            $id = $this->input->post('id');
            $data['ingrediente'] = $this->service_ingrediente->obtenerIngrediente($id);
        } else {
            $data['master_shipping'] = $this->service_master_shipping->obtenerNuevoMasterShipping();
            $data['listado_fincas'] = $this->service_general_finca->obtenerSelFinca();
        }


        $master_shipp = $this->load->view('shipping_edicion.php', $data, true);
        $respuesta = array("error" => (!$data['master_shipping'] ? true : false), "respuesta" => $master_shipp);

        header('Content-Type: application/json');
        echo json_encode($respuesta);
    }

    public function master_shipp_guardar() {
        $actualizacion = false;
        if ($this->input->post('id') != null) {
            $id = $this->input->post('id');
            $obj = $this->service_precio_finca->buscarIngredientePrecio($id);
        } else {

            $obj = $this->service_master_shipping->obtenerNuevoMasterShipping();
        }

        $arr = array();
        if ($obj) {

            foreach ($obj as $field => $value) {
                if (!(strpos(strtoupper($field), 'CREACION_') === 0 || strpos(strtoupper($field), 'ACTUALIZACION_') === 0 || strpos(strtoupper($field), 'INFO_') === 0)) {
                    $arr[$field] = $this->input->post($field);
                }
            }
            if (isset($_FILES['nombre_master']) && is_uploaded_file($_FILES['nombre_master']['tmp_name'])) {

                //guardar el pdf y obtener el nombre 
                $nombre_master_shipp_pdf = $this->subirArchivoPdfShipping($_FILES['nombre_master']);
                $arr["estado"] = ESTADO_ACTIVO;
                $arr["nombre_master"] = $nombre_master_shipp_pdf;
            }
            if ($this->input->post('id') != null) {
                $actualizacion = $this->service_ecommerce_store->actualizarStoreShopify($arr, true);
                if (!$actualizacion) {
                    $respuesta = 'Existe un problema durante la actualizaci&oacute;n';
                } else {
                    $respuesta = 'Registro actualizado';
                }
            } else {
                $actualizacion = $this->service_master_shipping->crearMasterShipping($arr, true);
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

    public function subirArchivoPdfShipping($documento) {
        $shipping = array();
        require_once('application/libraries/fpdi/src/autoload.php');
        require_once('application/libraries/fpdi/fpdf.php');
        $dir = 'uploads/shipping/';

        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        $parseador = new Parser();
        $newName = "masterShipping-" . fechaActual('YmdHis') . "_" . rand(1, 200) . ".pdf";
        $fichero = $documento['tmp_name'];
        move_uploaded_file($fichero, $dir . $newName);
        return $newName;
    }

    public function master_shipping_eliminar() {

        $id = $this->input->post('id');
        $actualizacion = $this->service_master_shipping->eliminarMasterShipping(array("id" => $id, "estado" => ESTADO_INACTIVO), true);
        if (!$actualizacion) {
            $respuesta = 'Existe un problema durante la inactivaci&oacute;n';
        } else {
            $respuesta = 'Registro inactivado';
        }
        $respuesta = array("error" => !$actualizacion, "respuesta" => (!$actualizacion ? 'Existe un problema durante la inactivaci&oacute;n' : 'Registro inactivado'));
        header('Content-Type: application/json');
        echo json_encode($respuesta);
    }

    public function json_imprimir_master_shipping($nombre_master = false) {
        $ruta_pdf = false;
        $nombre_master = $this->input->post('id');
        if ($nombre_master) {
            $ruta_pdf = "uploads/shipping/" . $nombre_master;
        }
        header('Content-Type: application/json');
        echo json_encode(array("error" => !$nombre_master, "mensaje" => (!$nombre_master ? 'No se pudo generar el pdf' : 'El pdf  se abrir&aacute; en otra ventana'), 'ruta_pdf' => base_url() . $ruta_pdf));
    }

}
