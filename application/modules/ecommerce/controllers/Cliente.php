<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Cliente extends MY_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model("ecommerce/service_ecommerce");
        $this->load->model("ecommerce/service_ecommerce_cliente");
        $this->load->model("ecommerce/service_ecommerce_orden");
        $this->load->model("ecommerce/service_ecommerce_producto");
    }

    /*     * ********************* CLIENTES ********************** */

    public function clientes() {
        $listadoClientes = $cuantosClientes = false;
        $data['texto_busqueda'] = $this->input->post('texto_busqueda');
        $data['store_id'] = $this->input->post('store_id');
        $data['estado_id'] = $this->input->post('estado_id');
        $data['perfil'] = PANTALLA_LOGISTICA;
        if ($this->input->post('btn_buscar')) {
            List($listadoClientes, $cuantosClientes) = $this->service_ecommerce_cliente->existeClienteCustomerStore($data['store_id'], false, false, $data['texto_busqueda'], $data['estado_id']);
        }
        $data['clientes'] = $listadoClientes;
        $data['cuantos'] = $cuantosClientes;
        $data['sel_store'] = $this->service_ecommerce->obtenerTiendasSel();
        $this->mostrarVista('clientes.php', $data);
    }

    public function clientes_listado() {
        $data['cliente_id'] = $this->input->post('cliente_id');
        $data['orden_id'] = $this->input->post('orden_id');

        $data['texto_busqueda'] = $this->input->post('texto_busqueda');
        $data['store_id'] = $this->input->post('store_id');
        $data['estado_id'] = $this->input->post('estado_id');
        List($listadoClientes, $cuantosClientes) = $this->service_ecommerce_cliente->existeClienteCustomerStore($data['store_id'], false, false, $data['texto_busqueda'], $data['estado_id']);
        $data['clientes'] = $listadoClientes;
        $data['cuantos'] = $cuantosClientes;
        $data['sel_store'] = $this->service_ecommerce->obtenerTiendasSel();

        $data['regpp'] = 10;
        $data['pagina'] = $this->paginacion['pagina'];
        if (isset($data['cuantos'])) {
            $data['itemsPaginacion'] = $this->calcularPaginacion($data['cuantos']);
        }

        $clientes_det = $this->load->view('clientes_listado.php', $data, true);

        $respuesta = array("error" => (!$listadoClientes ? true : false), "respuesta" => $clientes_det);
        header('Content-Type: application/json');
        echo json_encode($respuesta);
    }

    public function cliente_nuevo() {
        $this->cliente_obtener();
    }

    public function cliente_obtener() {
        $data['direcciones'] = '';
        if ($this->input->post('id') != null) {
            $id = $this->input->post('id');
            $data['operacion'] = '<i class="fa fa-pencil-alt my-float"></i> Editar información';
            $cliente = $this->service_ecommerce_cliente->obtenerCliente($id);
        } else {
            $data['operacion'] = '<i class="fa fa-plus my-float"></i> Registro de cliente';
            $cliente = $this->service_ecommerce_cliente->obtenerNuevoCliente();
        }

        $data['cliente'] = $cliente;
        $data['sel_store'] = $this->service_ecommerce->obtenerTiendasSel();

        if ($this->input->post('mostrar_direccion_envio') && isset($cliente->id)) {
            $data['direcciones'] = $this->service_ecommerce_cliente->obtenerClienteDireccionEnvio(false, $cliente->id);
            $data['direcciones'] = $this->load->view('cliente_direccion_envio_listado.php', $data, true);
        }
        $cliente_det = $this->load->view('cliente_edicion.php', $data, true);

        $respuesta = array("error" => (!$cliente ? true : false), "respuesta" => $cliente_det);
        header('Content-Type: application/json');
        echo json_encode($respuesta);
    }

    public function cliente_eliminar() {

        $id = $this->input->post('id');
        $actualizacion = $this->service_ecommerce_cliente->actualizarCliente(array("id" => $id, "estado" => ESTADO_INACTIVO), true);
        if (!$actualizacion) {
            $respuesta = 'Existe un problema durante la inactivaci&oacute;n';
        } else {
            $respuesta = 'Registro inactivado';
        }
        $this->session->set_userdata(($actualizacion) ? 'exito' : 'error', $respuesta);
        $respuesta = array("error" => !$actualizacion, "respuesta" => $respuesta);

        header('Content-Type: application/json');
        echo json_encode($respuesta);
    }

    public function cliente_guardar() {
        $actualizacion = false;
        if ($this->input->post('id') != null) {
            $id = $this->input->post('id');
            $obj = $this->service_ecommerce_cliente->obtenerCliente($id);
        } else {
            $obj = $this->service_ecommerce_cliente->obtenerNuevoCliente();
        }
        $arr = array();

//        error_log(print_r($obj, true));
        if ($obj) {

            foreach ($obj as $field => $value) {
                if (!(strpos(strtoupper($field), 'CREACION_') === 0 || strpos(strtoupper($field), 'ACTUALIZACION_') === 0 || strpos(strtoupper($field), 'INFO_') === 0)) {
                    $arr[$field] = $this->input->post($field);
                }
            }
//            error_log(print_r($arr, true));
            if ($this->input->post('id') != null) {
                $actualizacion = $this->service_ecommerce_cliente->actualizarCliente($arr, true);
                if (!$actualizacion) {
                    $respuesta = 'Existe un problema durante la actualizaci&oacute;n';
                } else {
                    $respuesta = 'Registro actualizado';
                }
            } else {
                $actualizacion = $this->service_ecommerce_cliente->crearCliente($arr, true);
                if (!$actualizacion) {
                    $respuesta = 'Existe un problema durante la creaci&oacute;n';
                } else {
                    $respuesta = 'Registro creado';
                }
            }
        } else {
            $respuesta = 'No se encuentra el registro';
        }

        header('Content-Type: application/json');
        echo json_encode(array("error" => !$actualizacion, "respuesta" => $respuesta));
    }

    /*     * ********************* CLIENTE DIRECCION ENVIO ********************** */

    public function cliente_direccion_envio_nuevo() {
        $this->cliente_direccion_envio_obtener();
    }

    public function orden_direccion_envio_edicion() {
        $this->cliente_direccion_envio_obtener(1);
    }

    public function cliente_direccion_envio_obtener($plantilla = 0) {
        $id = $this->input->post('direccion_id');
        $cliente = $this->service_ecommerce_cliente->obtenerCliente($this->input->post('cliente_id'));
        if ($id) {
            $clienteDireccionEnvio = $this->service_ecommerce_cliente->obtenerClienteDireccionEnvio($id);
        } else {
            $clienteDireccionEnvio = $this->service_ecommerce_cliente->obtenerNuevoClienteDireccionEnvio();
            $clienteDireccionEnvio->cliente_id = $cliente->id;
        }

        $data['cliente'] = $cliente;
        $data['cliente_direccion_envio'] = $clienteDireccionEnvio;
        $data['sel_store'] = $this->service_ecommerce->obtenerTiendasSel();
        $data['plantilla'] = $plantilla;
        $data['orden_id'] = $this->input->post('orden_id');
        $cliente_det = $this->load->view('cliente_direccion_envio_edicion.php', $data, true);

        $respuesta = array("error" => (!$cliente ? true : false), "respuesta" => $cliente_det);
        header('Content-Type: application/json');
        echo json_encode($respuesta);
    }

    public function cliente_direccion_envio_eliminar() {

        $id = $this->input->post('id');
        $actualizacion = $this->service_ecommerce_cliente->eliminarClienteDireccionEnvio($id);
        if (!$actualizacion) {
            $respuesta = 'Existe un problema durante la inactivaci&oacute;n';
        } else {
            $respuesta = 'Registro inactivado';
        }
//        $this->session->set_userdata(($actualizacion) ? 'exito' : 'error', $respuesta);
        $respuesta = array("error" => !$actualizacion, "respuesta" => $respuesta);
        header('Content-Type: application/json');
        echo json_encode($respuesta);
    }

    public function cliente_direccion_envio_guardar() {

        $actualizacion = false;
        if ($this->input->post('id') != null) {
            $id = $this->input->post('id');
            $obj = $this->service_ecommerce_cliente->obtenerClienteDireccionEnvio($id);
        } else {
            $obj = $this->service_ecommerce_cliente->obtenerNuevoClienteDireccionEnvio();
        }
        $arr = array();

//        error_log(print_r($obj, true));
        if ($obj) {

            foreach ($obj as $field => $value) {
                if (!(strpos(strtoupper($field), 'CREACION_') === 0 || strpos(strtoupper($field), 'ACTUALIZACION_') === 0 || strpos(strtoupper($field), 'INFO_') === 0)) {
                    $arr[$field] = $this->input->post($field);
                }
            }
//            error_log(print_r($arr, true));die;
            if ($this->input->post('id') != null) {
                $actualizacion = $this->service_ecommerce_cliente->actualizarClienteDireccionEnvio($arr);
                if (!$actualizacion) {
                    $respuesta = 'Existe un problema durante la actualizaci&oacute;n';
                } else {
                    $respuesta = 'Registro actualizado';
                }
            } else {
                $actualizacion = $this->service_ecommerce_cliente->crearClienteDireccionEnvio($arr);
                if (!$actualizacion) {
                    $respuesta = 'Existe un problema durante la creaci&oacute;n';
                } else {
                    $respuesta = 'Registro creado';
                }
            }
        } else {
            $respuesta = 'No se encuentra el registro';
        }
        header('Content-Type: application/json');
        echo json_encode(array("error" => !$actualizacion, "respuesta" => $respuesta, "nuevo_id" => $actualizacion));
    }

    public function cliente_direcciones_envio_listado() {
        $data['orden_id'] = $this->input->post('orden_id');
        $data['cliente_id'] = $this->input->post('cliente_id');
        $data['direccion_id'] = $this->input->post('direccion_id');

        $data['direcciones'] = $this->service_ecommerce_cliente->obtenerClienteDireccionEnvio(false, $data['cliente_id'], ESTADO_ACTIVO);

        $direcciones_det = $this->load->view('orden_detalle_destino_edicion.php', $data, true);

        $respuesta = array("error" => (!$data['direcciones'] ? true : false), "respuesta" => $direcciones_det);
        header('Content-Type: application/json');
        echo json_encode($respuesta);
    }

}
