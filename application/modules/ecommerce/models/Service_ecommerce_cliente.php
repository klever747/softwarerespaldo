<?php

class Service_ecommerce_cliente extends My_Model {

    public function obtenerNuevoCliente() {
        return (object) [
                    'nombres' => '',
                    'apellidos' => '',
                    'nombre_comercial' => '',
                    'email' => '',
                    'country' => '',
                    'state' => '',
                    'city' => '',
                    'zip_code' => '',
                    'phone' => '',
                    'store_id' => 1,
                    'address' => '',
                    'country_code' => '',
                    'state_code' => '',
                    'estado' => ESTADO_ACTIVO,
        ];
    }

    public function obtenerCliente($cliente_id) {
        return $this->existeClienteCustomerStore(false, false, $cliente_id, false);
    }

    public function crearCliente($objCliente) {
        $objCliente["estado"] = ESTADO_ACTIVO;
        $cliente_id = $this->ingresar("ecommerce.cliente", $objCliente, true);
        if ($cliente_id) {
            $dato_log = array(
                "cliente_id" => $cliente_id,
                "accion" => "creacion de cliente" . json_encode($objCliente),
            );
            $this->registrarLog("ecommerce.cliente_log", $dato_log);
        }
        return $cliente_id;
    }

    public function actualizarCliente($objCliente) {
        $cliente_id = $this->actualizar("ecommerce.cliente", $objCliente);
        if ($cliente_id) {
            $dato_log = array(
                "cliente_id" => $objCliente['id'],
                "accion" => "actualizacion de cliente" . json_encode($objCliente),
            );
            $this->registrarLog("ecommerce.cliente_log", $dato_log);
        }
        return $cliente_id;
    }

    public function existeClienteCustomerStore($store_id, $customer_id = false, $cliente_id = false, $busqueda = false, $estado = false) {
        $this->db->select('c.*');
        $this->db->from('ecommerce.cliente c');
        if ($customer_id) {
            $this->db->where('customer_id', $customer_id);
            return $this->retornarUno();
        }
        if ($store_id != 0) {
            $this->db->where('store_id', $store_id);
        }
        if ($cliente_id) {
            $this->db->where('id', $cliente_id);
            return $this->retornarUno();
        }
        if ($estado) {
            $this->db->where('estado', $estado);
        }
        if ($busqueda) {
            $this->db->where(" (UPPER(c.nombres) LIKE '%" . strtoupper($busqueda) . "%'  "
                    . "OR UPPER(c.apellidos) LIKE '%" . strtoupper($busqueda) . "%'  "
                    . "OR UPPER(c.nombre_comercial) LIKE '%" . strtoupper($busqueda) . "%'  "
                    . "OR UPPER(c.email) LIKE '%" . strtoupper($busqueda) . "%' ) ");
        }

        $this->db->order_by('c.nombres', 'ASC');
        $this->db->order_by('c.apellidos', 'ASC');
        $this->db->order_by('c.nombre_comercial', 'ASC');
        //error_log(print_r($this->db->get_compiled_select('',false), true));die;
        $conteo = $this->retornarConteo();
        $arr = $this->retornarMuchos();

        return array($arr, $conteo);
    }

    public function persistenciaCliente($objCliente, $objDireccionEnvio) {

        if (!$this->existeCliente($objCliente['store_id'], $objCliente['customer_id'])) {
            $nu = 1;
            $cab = $this->crearCliente($objCliente);
        } else {
            $nu = 2;
            $cab = $this->actualizarCliente($objCliente);
        }
        if ($cab) {
            $resp = true;
        } else {
            $resp = false;
        }
        return array($nu, $cab);
    }

    /*     * *********************************************************************** */

    public function obtenerNuevoClienteDireccionEnvio() {
        return (object) [
                    'alias' => '',
                    'cliente_id' => 0,
                    'destinatario_nombre' => '',
                    'destinatario_apellido' => '',
                    'destinatario_company' => '',
                    'address_1' => '',
                    'address_2' => '',
                    'country' => '',
                    'state' => '',
                    'country_code' => '',
                    'state_code' => '',
                    'city' => '',
                    'zip_code' => '',
                    'phone' => '',
                    'store_id' => 1,
                    'estado' => ESTADO_ACTIVO,
        ];
    }

    public function obtenerClienteDireccionEnvio($id, $cliente_id = false, $estado = false) {
        $this->db->select('o.*');
        $this->db->from('ecommerce.cliente_direccion_envio o');
        if ($id) {
            $this->db->where('id', $id);
            return $this->retornarUno();
        }
        if ($estado) {
            $this->db->where('estado', $estado);
        }
        if ($cliente_id) {
            $this->db->where('cliente_id', $cliente_id);
            return $this->retornarMuchos(false);
        }
    }

    public function existeClienteDireccionEnvio($objDireccion) {
        $this->db->select('o.*');
        $this->db->from('ecommerce.cliente_direccion_envio o');
        if (array_key_exists('id', $objDireccion)) {
            $this->db->where('id', $objDireccion['id']);
        } else {
            $this->db->where('cliente_id', $objDireccion['cliente_id']);

            if ($objDireccion['store_id'] != 0) {
                $this->db->where('store_id', $objDireccion['store_id']);
            }
            $this->db->where('destinatario_nombre', $objDireccion['destinatario_nombre']);
            $this->db->where('destinatario_apellido', $objDireccion['destinatario_apellido']);
            $this->db->where('destinatario_company', $objDireccion['destinatario_company']);
            $this->db->where('address_1', $objDireccion['address_1']);
            $this->db->where('address_2', $objDireccion['address_2']);
            $this->db->where('city', $objDireccion['city']);
            $this->db->where('state', $objDireccion['state']);
            $this->db->where('country', $objDireccion['country']);
            $this->db->where('state_code', $objDireccion['state_code']);
            $this->db->where('country_code', $objDireccion['country_code']);
            $this->db->where('zip_code', $objDireccion['zip_code']);
            $this->db->where('phone', $objDireccion['phone']);

//die($objDireccion['cliente_id']);
        }
        return $this->retornarUno();
    }

    public function crearClienteDireccionEnvio($datos) {
        $datos["estado"] = ESTADO_ACTIVO;
        return $this->ingresar("ecommerce.cliente_direccion_envio", $datos, true, true);
    }

    public function eliminarClienteDireccionEnvio($id) {
        return $this->actualizar("ecommerce.cliente_direccion_envio", array("id" => $id, "estado" => ESTADO_INACTIVO), "id", true);
    }

    public function actualizarClienteDireccionEnvio($obj) {
        //actualizamos la direccion actual a inactivo
        //creamos una nueva direccion        
        $actualizacion = $this->actualizar("ecommerce.cliente_direccion_envio", array("id" => $obj['id'], "estado" => ESTADO_INACTIVO), "id", true);
        if (!$actualizacion) {
            return false;
        }
        unset($obj['id']);
        return $this->crearClienteDireccionEnvio($obj);
    }

}
