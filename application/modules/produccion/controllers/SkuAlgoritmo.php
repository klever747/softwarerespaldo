<?php

defined('BASEPATH') or exit('No direct script access allowed');

class SkuAlgoritmo extends MY_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model("generales/service_general_finca");
        $this->load->model("produccion/service_sku_algoritmo");
        $this->load->model("ecommerce/service_ecommerce_producto");

        $this->load->model("produccion/service_orden");
        $this->load->model("ecommerce/service_ecommerce");
        $this->load->model("ecommerce/service_ecommerce_logistica");

        $this->load->model("ecommerce/service_ecommerce_orden");
    }

    public function algoritmo() {
        $error = false;
        $data['algoritmoasignado'] = false;
        $data['algoritmo_detalle'] = array();
        $data['producto_id'] = $this->input->post('producto_id');

        if ($this->input->post('id') != null) {
            $id = $this->input->post('id');
            $data['variante'] = $this->service_sku_algoritmo->obtenerProductoVariante($id);
            $data['algoritmoasignado'] = $this->service_sku_algoritmo->existeAlgoritmo($data['variante']->sku);
        }
        $data['parametro'] = $this->service_sku_algoritmo->obtenerParametro();

        $data['fincas'] = $this->service_general_finca->obtenerSelFinca(true);
        $producto = $this->service_sku_algoritmo->obtenerProducto($data['producto_id']);
        $data['sku_prefijo'] = $producto->sku_prefijo;

        if ($data['algoritmoasignado']) {
            $data['algoritmo_detalle'] = $this->service_sku_algoritmo->obtenerAlgoritmoDetalle($data['algoritmoasignado']->id);
        }
        $variante = $this->load->view('sku_algoritmo_editar.php', $data, true);
        $respuesta = array("error" => (!$data['variante'] ? true : false), "respuesta" => $variante);

        header('Content-Type: application/json');

        echo json_encode($respuesta);
    }

    /*     * ***********Ingredientes Recetas*************** */

    public function migracionMasivaSku() {
        $respuesta = '';
        $actualizacion = true;

        $data = $this->service_ecommerce_producto->obtenerVariantes(true);
        foreach ($data as $value) {
            $this->service_sku_algoritmo->crearParametros(array("sku" => $value->sku), true);
        }
        $respuesta = array("error" => !$actualizacion, "respuesta" => $respuesta);

        header('Content-Type: application/json');
        echo json_encode($respuesta);
    }

    public function algoritmo_guardar() {
        $valor = 100;
        $respuesta = '';
        $actualizacion = false;
        $data['porcentaje'] = null;
        $data['sku_algoritmo_id'] = $this->input->post('sku_algoritmo_id'); //tipo algoritmo
        $data['id'] = $this->input->post('id'); //id variante
        $data['producto_id'] = $this->input->post('producto_id');
        $data['sku'] = $this->input->post('sku');
        $data['finca_id'] = $this->input->post('finca_id');
        $data['valor'] = $this->input->post('valor');

        if (!isset($data['finca_id'])) {
            $respuesta = "No existen valores definidos";
        } else {


//            foreach ($data['valor'] as $val) {
//                if (empty($val)) {
//                    $respuesta = 'No pueden haber valores vacios';
//                }
//            }

            if ($data['sku_algoritmo_id'] == 'semanal') {
                $data['semanal'] = $this->input->post('semanal');
                foreach ($data['semanal'] as $val) {
                    if (empty($val) || $val == 0) {
                        $respuesta .= '</br>No pueden haber valores vacios semanales';
                    }
                }
                $data['diario'] = $this->input->post('diario');
                foreach ($data['diario'] as $val) {
                    if (is_null($val) || ($val == 0)) {
                        $respuesta .= '</br>No pueden haber valores vacios diarios';
                    }
                }
            } else if ($data['sku_algoritmo_id'] == 'diario') {
                $data['diario'] = $this->input->post('diario');
                foreach ($data['diario'] as $val) {
                    if (is_null($val) || ($val == 0)) {
                        $respuesta .= '</br>No pueden haber valores vacios diarios';
                    }
                }
            } else if ($data['sku_algoritmo_id'] == 'porcentaje') {
                $data['diario'] = $this->input->post('diario');
                foreach ($data['diario'] as $val) {
                    if (empty($val) && ($val != 0)) {
                        $respuesta .= '</br>No pueden haber valores vacios';
                    }
                }
                $valor = 0;
                $data['porcentaje'] = $this->input->post('porcentaje');
                foreach ($data['porcentaje'] as $porcentaje) {
                    $valor = $valor + $porcentaje;
                }
            }

            //validar si existe el registro-- si existe inavilitamos el algoritmo y creamos el nuevo
            $algoritmoasignado = $this->service_sku_algoritmo->existeAlgoritmo($data['sku']);

            if (count($data['finca_id']) > count(array_unique($data['finca_id']))) {
                $respuesta .= '</br> Finca repetida'; // "¡Hay repetidos!";
            }

            if ($valor != 100) {
                $respuesta .= '</br>El total de la suma de los porcentaje debe dar 100';
            } else if (empty($respuesta)) {

                //si ya existe algoritmo, entonces actualizo
                if ($algoritmoasignado) {
                    $actualizacion = $this->service_sku_algoritmo->inhabilitarParametros(array("id" => $algoritmoasignado->id, "estado" => ESTADO_INACTIVO), true);
                    $actualizacion = $this->service_sku_algoritmo->crearParametros($data);
                    if (!$actualizacion) {
                        $respuesta .= '</br>Existe un problema durante la actualización';
                    } else {
                        $respuesta .= '</br>Parametros actualizados';
                    }
                } else {
                    $actualizacion = $this->service_sku_algoritmo->crearParametros($data);

                    if (!$actualizacion) {
                        $respuesta .= '</br>Existe un problema durante la creación';
                    } else {
                        $respuesta .= '</br>Parametros registrados';
                    }
                }
            }
        }

        $respuesta = array("error" => !$actualizacion, "respuesta" => $respuesta);

        header('Content-Type: application/json');
        echo json_encode($respuesta);
    }

    public function productos() {
        $texto_busqueda = "";
        // $array_variantes = [];
        $listadoProductos = false;
        $estado_id = false;
        // $variantes = false;
        $cuantos = 0;
        if ($this->input->post('btn_buscar') != null) {
            $texto_busqueda = $this->input->post('texto_busqueda');
            $estado_id = $this->input->post('estado_id');

            list($listadoProductos, $cuantos) = $this->service_sku_algoritmo->obtenerProducto(false, $estado_id, $texto_busqueda);
            //List($variantes, $cuantos2) = $this->service_sku_algoritmo->obtenerProductoVariante(false, $estado_id, $texto_busqueda);
        }
        // foreach($variantes as $variante){
        //     $array_variantes[] = $variante->titulo;
        // }
        $data['fincas'] = $this->service_general_finca->obtenerSelFinca(true);
        $data['estado_id'] = $estado_id;
        $data['productos'] = $listadoProductos;
        $data['cuantos'] = $cuantos;
        //$data['variantes'] = $variantes;
        $data['texto_busqueda'] = $texto_busqueda;

        $this->mostrarVista('sku_algoritmo.php', $data);
    }

    public function producto_obtener() {
        $error = false;

        if ($this->input->post('id') != null) {
            $id = $this->input->post('id');
            $data['operacion'] = '<i class="fa fa-pencil-alt my-float"></i> Editar opción';
            $data['producto'] = $this->service_sku_algoritmo->obtenerProducto($id);
            $data['variantes'] = $this->service_sku_algoritmo->obtenerVariantesProducto($data['producto']->id, ESTADO_ACTIVO);
            $data['variantes'] = $this->load->view('producto_variante_listado.php', $data, true);
        } else {
            $data['operacion'] = '<i class="fa fa-plus my-float"></i> Registro de nuevo producto';
            $data['producto'] = $this->service_sku_algoritmo->obtenerNuevoProducto();
            $data['variantes'] = false;
        }

        $producto_det = $this->load->view('sku_algoritmo_editar.php', $data, true);

        $respuesta = array("error" => (!$data['producto'] ? true : false), "respuesta" => $producto_det);

        header('Content-Type: application/json');
        echo json_encode($respuesta);
    }

    // ASIGNACION DE FINCA DE DESPACHO DE LOS ITEMS DE UNA CAJA
    //algoritmo finca
    public function determinarFincaDespachoOrden($orden_id) {
        $this->service_sku_algoritmo->fincaDespachoOrden($orden_id);
    }

}
