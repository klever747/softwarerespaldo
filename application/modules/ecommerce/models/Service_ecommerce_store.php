<?php

class Service_ecommerce_store extends My_Model {

    public function obtenerNuevaTienda() {
        return (object) [
                    'store_name' => '',
                    'url' => '',
                    'ecommerce' => '',
                    'estado' => ESTADO_ACTIVO,
                    'alias' => '',
        ];
    }

    public function obtenerStore($id = false, $estado = false, $texto_busqueda = false) {
        $this->db->select('s.*');
        $this->db->from('ecommerce.store s');

        if ($id) {
            $this->db->where('s.id', $id);
            return $this->retornarUno();
        }

        if ($estado) {
            $this->db->where('s.estado', $estado);
        }
        if ($texto_busqueda) {
            $this->db->where(" (UPPER(s.store_name) LIKE '%" . strtoupper($texto_busqueda) . "%' "
                    . "OR UPPER(s.alias) LIKE '%" . strtoupper($texto_busqueda) . "%' "
                    . "OR UPPER(s.ecommerce) LIKE '%" . strtoupper($texto_busqueda) . "%')");
        }
        $this->db->order_by('s.store_name', 'ASC');

        $conteo = $this->retornarConteo();
        $arr = $this->retornarMuchos();

        return array($arr, $conteo);
    }

    public function obtenerProductos($estado = false) {
        $this->db->select('o.*');
        $this->db->from('ecommerce.producto o');
        if ($estado) {
            $this->db->where('estado', $estado);
        }
        $conteo = $this->retornarConteo();
        $arr = $this->retornarMuchos();

        return array($arr, $conteo);
    }

    public function crearTienda($obj) {

        error_log("Vamos a crear una Tienda");
        $id = $this->ingresar("ecommerce.store", $obj, true, false);
        if ($id) {
            $dato_log = array(
                "store_id" => $id,
                "accion" => "creacion de tienda" . json_encode($obj),
            );
            $this->registrarLog("ecommerce.store_log", $dato_log);
        }
        return $id;
    }

    public function actualizarTienda($obj) {
        $id = $this->actualizar("ecommerce.store", $obj, "id", false);
        if ($id) {
            $dato_log = array(
                "store_id" => $obj['id'],
                "accion" => "actualizacion de tienda" . json_encode($obj),
            );
            $this->registrarLog("ecommerce.store_log", $dato_log);
        }
        return $id;
    }

    /*     * ******************* DATOS SHOPIFY-STORE ***************** */

    public function obtenerNuevoStoreShpify($store_id) {
        return (object) [
                    'store_id' => $store_id,
                    'api_version' => '',
                    'api_key' => '',
                    'password' => '',
                    'secret' => '',
                    'store' => '',
                    'domain' => '',
                    'estado' => ESTADO_ACTIVO,
                    'max_nro_dias_info' => '',
        ];
    }

    public function existeProductoVariante($objProductoVariante) {
        $this->db->select('o.*');
        $this->db->from('ecommerce.producto_variante o');
        $this->db->where('sku', $objProductoVariante['sku']);

        return $this->retornarUno();
    }

    public function obtenerStoreShopify($id = false, $estado = false, $texto_busqueda = false) {
        $this->db->select('sp.*');
        $this->db->from('ecommerce.shopify_parametros sp');

        if ($id) {
            $this->db->where('sp.id', $id);
            return $this->retornarUno();
        }

        if ($estado) {
            $this->db->where('sp.estado', $estado);
        }
        if ($texto_busqueda) {
            $this->db->where(" (UPPER(pv.titulo) LIKE '%" . strtoupper($texto_busqueda) . "%' "
                    . "OR UPPER(pv.sku) LIKE '%" . strtoupper($texto_busqueda) . "%')");
        }
        $this->db->order_by('sp.titulo', 'ASC');

        $conteo = $this->retornarConteo();
        $arr = $this->retornarMuchos();

        return array($arr, $conteo);
    }

    public function crearStoreShopify($obj) {
        $id = $this->ingresar("ecommerce.shopify_parametros", $obj, true, false);
        if ($id) {
            $dato_log = array(
                "store_id" => $id,
                "accion" => "creacion de store-shopify" . json_encode($obj),
            );
            $this->registrarLog("ecommerce.shopify_parametros_log", $dato_log);
        }
        return $id;
    }

    public function actualizarStoreShopify($obj) {
        $id = $this->actualizar("ecommerce.shopify_parametros", $obj, "id", false);
        if ($id) {
            $dato_log = array(
                "store_id" => $obj['id'],
                "accion" => "actualizacion de shopify-parametros" . json_encode($obj),
            );
            $this->registrarLog("ecommerce.shopify_parametros_log", $dato_log);
        }
        return $id;
    }

    public function obtenerShopifyParametros($store_id, $estado = false) {
        $this->db->select('sp.*');
        $this->db->from('ecommerce.shopify_parametros sp');
        if ($store_id) {
            $this->db->where('store_id', $store_id);
        }
        if ($estado) {
            $this->db->where('estado', $estado);
        }
        $this->db->order_by('sp.store', 'ASC');
        return $this->retornarMuchosSinPaginacion();
    }

}
