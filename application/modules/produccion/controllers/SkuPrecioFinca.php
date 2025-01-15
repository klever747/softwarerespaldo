<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class SkuPrecioFinca extends MY_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model("ecommerce/service_ecommerce_orden");
        $this->load->model("ecommerce/service_ecommerce_producto");
        $this->load->model("produccion/service_sku_precio_finca");
        $this->load->model("produccion/service_precio_finca");
        $this->load->model("generales/service_general_finca");
    }
    public function sku_precio_finca() {
        $data['finca_id'] = 0;
        $texto_busqueda = "";
        $listadoPrecios = false;
        $estado_id = false;
        $cuantos = 0;
        $data['sel_finca'] = $this->service_general_finca->obtenerSelFinca();
        if ($this->input->post('btn_buscar') != null) {
            $texto_busqueda = $this->input->post('texto_busqueda');
            $finca_id = $this->input->post('finca_id');
            List($listadoPrecios, $cuantos) = $this->service_sku_precio_finca->obtenerSkuPrecioFinca($finca_id, $texto_busqueda);
        }
        $data['estado_id'] = $estado_id;
        $data['fincasPrecios'] = $listadoPrecios;
        $data['cuantos'] = $cuantos;

        $data['texto_busqueda'] = $texto_busqueda;
        $this->mostrarVista('sku_precio_finca.php', $data);
    } 
    public function json_sku_precio_finca_nuevo() {
        $this->json_sku_precio_finca_obtener();
    }
    public function json_sku_precio_finca_obtener() {
        $error = false;
        if ($this->input->post('id') != null) {
            $id = $this->input->post('id');
//            $data['producto'] = $this->service_sku_precio_finca->obtenerListaProductos($id);
//            $data['variante'] = $this->service_sku_precio_finca->obtenerListaVariantes($id);
            $data['nuevo_producto'] = $this->service_sku_precio_finca->obtenerPrecioFincaSku($id);
            //$data['variantes'] = $this->load->view('store_shopify_listado.php', $data, true);
            $data['listado_productos'] = false;
            $data['listado_fincas'] = $this->service_general_finca->obtenerSelFinca();
            $data['solo_nombre'] = true;
        } else {
            $id = false;
            $data['nuevo_producto'] = $this->service_sku_precio_finca->obtenerNuevoSkuPrecioFinca(); 
            $data['producto'] = $this->service_sku_precio_finca->obtenerListaProductos();
            $data['variante'] = $this->service_sku_precio_finca->obtenerListaVariantes();
            $data['listado_fincas'] = $this->service_general_finca->obtenerSelFinca();
            $data['solo_nombre'] = false;
        }


        $sku_det = $this->load->view('sku_precio_finca_edicion.php', $data, true);
        $respuesta = array("error" => (!$data['nuevo_producto'] ? true : false), "respuesta" => $sku_det, 'edicion' => $id);

        header('Content-Type: application/json');
        echo json_encode($respuesta);
    }
    public function sku_precio_finca_guardar() {
        $actualizacion = false;
        if ($this->input->post('id') != null) {
            $id = $this->input->post('id');
            $obj = $this->service_sku_precio_finca->obtenerPrecioFincaSku($id);
        } else {
            $obj = $this->service_sku_precio_finca->obtenerNuevoSkuPrecioFinca();
        }
        
        unset($obj->producto);
        unset($obj->variante);
        
        $arr = array();
        if ($obj) {

            foreach ($obj as $field => $value) {
                if (!(strpos(strtoupper($field), 'CREACION_') === 0 || strpos(strtoupper($field), 'ACTUALIZACION_') === 0 || strpos(strtoupper($field), 'INFO_') === 0)) {
                    if ($this->input->post($field) != null){
                        $arr[$field] = $this->input->post($field);
                    }
                }
            }
          
            if ($this->input->post('id') != null) {
                if ($arr['fecha_inicio_vigencia'] > $arr['fecha_fin_vigencia']) {
                    $respuesta = '</br>La fecha de inicio no debe ser mayor o igual a la fecha de fin';
                } else {
                    //verificar que no se repita la fecha de inicio y fecha fin de vigencia del precio
                    $actualizacion = $this->service_sku_precio_finca->verificarFechaFincaPrecioSku($arr['sku'], $arr['fecha_inicio_vigencia'], $arr['fecha_fin_vigencia'], $arr['finca_id'], $arr['id']);
                    if ($actualizacion) {
                        $actualizacion = false;
                        $respuesta = '</br>El rango de fechas ya existe, ingrese otro rango';
                    } else {
                        $actualizacion = $this->service_sku_precio_finca->actualizarFechaFincaPrecioSku($arr, true);
                        if (!$actualizacion) {
                            $respuesta = 'Existe un problema durante la creaci&oacute;n';
                        } else {
                            $respuesta = 'Registro actualizado';
                        }
                    }
                }
            } else {
                if ($arr['fecha_inicio_vigencia'] > $arr['fecha_fin_vigencia']) {
                    $respuesta = '</br>La fecha de inicio no debe ser mayor o igual a la fecha de fin';
                } else {
                    $data_sku = $this->service_sku_precio_finca->obtenerSkuVariante($arr['variante_id']);
                    $arr['sku']=$data_sku->sku;
                    unset($arr['producto_id']);
                    unset($arr['variante_id']);
                    //verificar que no se repita la fecha de inicio y fecha fin de vigencia del precio
                    $actualizacion = $this->service_sku_precio_finca->verificarFechaFincaPrecioSku($arr['sku'], $arr['fecha_inicio_vigencia'], $arr['fecha_fin_vigencia'], $arr['finca_id']);
                    if ($actualizacion) {
                        $actualizacion = false;
                        $respuesta = '</br>El rango de fechas ya existe, ingrese otro rango';
                    } else {
                        $actualizacion = $this->service_sku_precio_finca->guardarFechaFincaPrecio($arr, true);
                        if (!$actualizacion) {
                            $respuesta = 'Existe un problema durante la creaci&oacute;n';
                        } else {
                            $respuesta = 'Registro creado';
                        }
                    }
                }
            }
        } else {
            $respuesta = 'No se encuentra el registro';
        }

        $respuesta = array("error" => !$actualizacion, "respuesta" => $respuesta);
        header('Content-Type: application/json');
        echo json_encode($respuesta);
    }
    public function sku_precio_finca_eliminar() {
        $id = $this->input->post('id');
        $actualizacion = $this->service_sku_precio_finca->actualizarFechaFincaPrecioSku(array("id" => $id, "estado" => ESTADO_INACTIVO), true);
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