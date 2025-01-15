<?php

class Service_orden extends My_Model {

    public function nuevaOrden() {
        return (object) ['store_id' => '', 'estado' => ESTADO_ACTIVO,];
    }

    public function crearOrden($datos) {
        $orden_id = $this->ingresar("ecommerce.orden", $datos, true, true);
        if ($orden_id) {
            $dato_log = array(
                "orden_id" => $orden_id,
                "accion" => "creacion de orden" . json_encode($datos),
            );
            $this->registrarLog("ecommerce.orden_log", $dato_log);
        }
        return $orden_id;
    }

    public function crearOrdenItem($datos) {
        $datos['estado'] = ESTADO_ACTIVO;
        return $this->ingresar("ecommerce.orden_item", $datos, true, true);
    }

    /**
     * En base a la orden_id vamos a obtener la entidad de la orden
     * @param type $orden_id
     * @return orden
     */
    public function obtenerOrden($orden_id) {
        $this->db->select('o.*, s.id as "tienda_id", s.alias as "tienda_alias", '
                . 'c.id as "cliente_id", c.nombres, c.apellidos, c.nombre_comercial, c.email, '
                . 'cde.id as "cliente_direccion_envio_id", cde.alias, cde.destinatario_nombre, cde.destinatario_apellido, cde.destinatario_company, '
                . 'cde.country_code, cde.country, cde.state, cde.state_code, cde.city, cde.zip_code');
        $this->db->from('ecommerce.orden o');
        $this->db->join('ecommerce.store s', 'o.store_id = s.id', 'left');
        $this->db->join('ecommerce.cliente c', 'o.cliente_id = c.id', 'left');
        $this->db->join('ecommerce.cliente_direccion_envio cde', 'o.cliente_direccion_id = cde.id', 'left');
        $this->db->where('o.id', $orden_id);
        return $this->retornarUno(true);
    }

    public function obtenerOrdenReenvio($orden_id) {
        $this->db->select('o.id');
        $this->db->from('ecommerce.orden o');
        $this->db->where('o.reenvio_orden_id', $orden_id);
        $this->db->where('o.estado', ESTADO_ORDEN_REENVIADA);
        return $this->retornarUno();
    }

    public function obtenerOrdenClonada($orden_id) {
        $this->db->select('o.id');
        $this->db->from('ecommerce.orden o');
        $this->db->where('o.clonacion_orden_id', $orden_id);
        $this->db->where('o.estado', ESTADO_ORDEN_CLONADA);
        return $this->retornarUno();
    }

    public function obtenerOrdenDetalle($orden_id) {
        $this->db->select('oi.*, '
                . 'oc.id as caja_id, oc.tipo_caja_id, tc.nombre, oc.empacada, oc.kardex_check, '
                . 'octn.tracking_number, '
                . 'p.titulo as info_producto_titulo, p.descripcion as info_producto_descripcion, '
                . 'pv.titulo as info_variante_titulo, pv.sku as info_variante_sku');
        $this->db->from('ecommerce.orden_item oi');
        $this->db->join('ecommerce.producto p', 'oi.producto_id = p.id', 'left');
        $this->db->join('ecommerce.producto_variante pv', 'oi.variante_id = pv.id', 'left');
        $this->db->join('ecommerce.orden_caja_item oci', "oi.id = oci.orden_item_id AND oci.estado = '" . ESTADO_ACTIVO . "' ", 'left');
        $this->db->join('ecommerce.orden_caja oc', "oci.orden_caja_id = oc.id AND oc.estado = '" . ESTADO_ACTIVO . "' ", 'left');
        $this->db->join('ecommerce.finca_caja fc', "fc.orden_caja_id = oc.id AND fc.estado = '" . ESTADO_ACTIVO . "'", 'left');
        $this->db->join('logistica.orden_caja_tracking_number octn', "octn.orden_caja_id = oc.id AND octn.estado = '" . ESTADO_ACTIVO . "' ", 'left');
        $this->db->join('ecommerce.tipo_caja tc', "oc.tipo_caja_id = tc.id", 'left');
        $this->db->where('oi.orden_id', $orden_id);
        $this->db->where('oi.estado', ESTADO_ACTIVO);
        return $this->retornarMuchosSinPaginacion(true);
    }

    public function obtenerOrdenes($filtro, $calcularTotal = false) {
        $this->db->select("DISTINCT(o.id)");
        $this->db->from('ecommerce.orden o');
        $this->db->join('ecommerce.cliente c', 'o.cliente_id = c.id', 'left');
        $this->db->join('ecommerce.cliente_direccion_envio cde', 'o.cliente_direccion_id = cde.id', 'left');
        $this->db->join('ecommerce.orden_item oi', "o.id = oi.orden_id AND oi.estado = '" . ESTADO_ACTIVO . "'", 'left');
        $this->db->join('ecommerce.orden_caja_item oci', "oi.id = oci.orden_item_id AND oci.estado = '" . ESTADO_ACTIVO . "'", 'left');
        $this->db->join('ecommerce.orden_caja oc', "oc.id = oci.orden_caja_id AND o.id = oc.orden_id AND oc.estado = '" . ESTADO_ACTIVO . "'", 'left');
        $this->db->join('ecommerce.finca_caja fc', "fc.orden_caja_id = oc.id AND fc.estado = '" . ESTADO_ACTIVO . "'", 'left');
        $this->db->join('ecommerce.tipo_caja otp', "otp.id = oc.tipo_caja_id AND otp.estado = '" . ESTADO_ACTIVO . "'", 'left');
        $this->db->join('ecommerce.producto p', "oi.producto_id = p.id", 'left');
        $this->db->join('ecommerce.producto_variante pv', "oi.variante_id = pv.id", 'left');
        $this->db->join('logistica.orden_caja_tracking_number octn', 'oc.id = octn.orden_caja_id', 'left');

        unset($filtro['bonchado']);
        unset($filtro['vestido']);

        $this->filtrar($filtro);

        if ($calcularTotal) {
            $conteo = $this->retornarConteo();
        }
        $this->db->order_by('1', 'DESC');
        if ($calcularTotal) {
            return array($this->retornarMuchosConPaginacion(true), $conteo);
        }
        return $this->retornarMuchosConPaginacion(true);
    }

    private function filtrar($filtro) {

//        if (array_key_exists('estado', $filtro)){
//            $this->db->where('o.estado', ESTADO_ACTIVO);
//        }
//        else {
//            $this->db->where('o.estado', ESTADO_ACTIVO);
//        }
        if (array_key_exists('reenviado', $filtro)) {
            if ($filtro['reenviado'] != 'T') {
                if ($filtro['reenviado'] == 'S') {
                    $this->db->where('o.reenvio_orden_id is NOT NULL', null, false);
                } else {
                    $this->db->where('o.reenvio_orden_id is NULL');
                }
            }
        }

        if (array_key_exists('con_tracking_number', $filtro)) {
            if ($filtro['con_tracking_number'] !== 'T') {
                if ($filtro['con_tracking_number'] == 'S') {
                    $this->db->where("octn.tracking_number is NOT NULL ");
                } else {
                    $this->db->where("(octn.tracking_number is NULL OR octn.estado =  '" . ESTADO_INACTIVO . "')");
                }
            }
        }

        if ($filtro['store_id'] != 0) {
            $this->db->where('o.store_id', $filtro['store_id']);
        }

        if (array_key_exists('tipo_caja', $filtro) && !empty($filtro['tipo_caja'])) {
            if ($filtro['tipo_caja'] != 0) {
                $this->db->where('otp.id', $filtro['tipo_caja']);
            }
        }

        if ($filtro['orden_estado_id'] != 'T') {
            $this->db->where('o.estado', $filtro['orden_estado_id']);
        }

        if (array_key_exists('order_number', $filtro) && !empty($filtro['order_number'])) {
            $this->db->where("(o.id = " . $filtro['order_number'] . " OR o.reenvio_orden_id = '" . $filtro['order_number'] . "' OR o.clonacion_orden_id = '" . $filtro['order_number'] . "')");
            return;
        }
        if (array_key_exists('referencia_order_number', $filtro) && !empty($filtro['referencia_order_number'])) {
            $this->db->where("o.referencia_order_number = '" . $filtro['referencia_order_number'] . "'");
            return;
        }
        if (array_key_exists('session_finca', $filtro)) {
            $arrayfinca = explode(",", $filtro['session_finca']);
            if (in_array(FINCA_ROSAHOLICS_ID, $arrayfinca)) {
                if ($filtro['finca_id'] != 0) {
                    $srt = "fc.finca_id in (" . $filtro['finca_id'] . ")";
                }
            } else {
                if (array_key_exists('finca_id', $filtro)) {
                    if ($filtro['finca_id'] != 0) {
                        $srt = "fc.finca_id in (" . $filtro['finca_id'] . ")";
                    } else {
                        $srt = "fc.finca_id in (" . $filtro['session_finca'] . ")";
                    }
                } else {
                    $srt = "fc.finca_id in (" . $filtro['session_finca'] . ")";
                }
            }
            if(isset($srt)){
              $this->db->where($srt);  
            }
        }



        if ($filtro['tarjeta_impresa'] != 'T') {
            if ($filtro['tarjeta_impresa'] == 'N') {
                $this->db->where('o.impresiones = 0');
            }
            if ($filtro['tarjeta_impresa'] == 'S') {
                $this->db->where('o.impresiones != 0');
            }
        }
        if (!empty($filtro['texto_busqueda'])) {
            $this->db->where(" ( UPPER(p.titulo) LIKE '%" . strtoupper($filtro['texto_busqueda']) . "%' "
                    . "OR UPPER(pv.titulo) LIKE '%" . strtoupper($filtro['texto_busqueda']) . "%' "
                    . "OR UPPER(pv.sku) LIKE '%" . strtoupper($filtro['texto_busqueda']) . "%' "
                    . "OR UPPER(c.nombres) LIKE '%" . strtoupper($filtro['texto_busqueda']) . "%' "
                    . "OR UPPER(c.apellidos) LIKE '%" . strtoupper($filtro['texto_busqueda']) . "%' "
                    . "OR UPPER(c.nombre_comercial) LIKE '%" . strtoupper($filtro['texto_busqueda']) . "%' "
                    . "OR UPPER(c.email) LIKE '%" . strtoupper($filtro['texto_busqueda']) . "%' )");
        }
        if (!empty($filtro['rango_busqueda'])) {
            //rango_busqueda espera "dd/mm/YYYY - dd/mm/YYYY"
            $arrRango = explode(" - ", $filtro['rango_busqueda']);
            $fechaIni = date_format(DateTime::createFromFormat(FORMATO_FECHA, convertirFechaBD($arrRango[0])), 'Y-m-d 00:00:00');
            $fechaFin = date_format(DateTime::createFromFormat(FORMATO_FECHA, convertirFechaBD($arrRango[1])), 'Y-m-d 23:59:59');
            if (sizeof($arrRango) != 2) {
                //siempre debe de ser 2, si no es un error y vamos a devolver un error
                return array(false, -1);
            }
            switch ($filtro['tipo_calendario']) {
                case 0://carguera
                    $arrSelect = array('o.fecha_carguera >= ' => $fechaIni, 'o.fecha_carguera <= ' => $fechaFin);
                    break;
                case 1://entrega
                    $arrSelect = array('o.fecha_entrega >= ' => $fechaIni, 'o.fecha_entrega <= ' => $fechaFin);
                    break;
                case 2://compra
                    $arrSelect = array('o.fecha_compra >= ' => $fechaIni, 'o.fecha_compra <= ' => $fechaFin);
                    break;
                case 3://compra
                    $arrSelect = 'o.fecha_entrega IS NULL';
                    break;
                default:
                    die;
                    break;
            }

            $this->db->where($arrSelect);
        }


        if (array_key_exists('colores', $filtro) && (sizeof($filtro['colores']) > 0)) {
            $filtroColor = '';
            foreach ($filtro['colores'] as $color => $valor) {
                if ($valor == 1) {
                    $filtroColor .= " UPPER(p.tags) LIKE '%" . strtoupper($color) . "%' OR";
                }
            }
            if (strlen($filtroColor) > 0) {
                $filtroColor = substr($filtroColor, 0, strlen($filtroColor) - 3);
                $filtroColor = "(" . $filtroColor . ")";
                $this->db->where($filtroColor);
            }
        }
        if ((array_key_exists('preparado', $filtro) && $filtro['preparado'] != null)) {
            if ($filtro['preparado'] != 'T') {
                $this->db->where('oi.preparado', $filtro['preparado']);
            }
        }
        if ((array_key_exists('terminado', $filtro) && $filtro['terminado'] != null)) {
            if ($filtro['terminado'] != 'T') {
                $this->db->where('oi.terminado', $filtro['terminado']);
            }
        }
        if ((array_key_exists('bonchado', $filtro) && $filtro['bonchado'] !== 'T') || (array_key_exists('vestido', $filtro) && ($filtro['vestido'] !== 'T'))) {
            if (array_key_exists('bonchado', $filtro) && $filtro['bonchado'] != null && $filtro['bonchado'] !== 'T') {
                $this->db->where("oi.preparado", $filtro['bonchado']);
                if ($filtro['bonchado'] == 'S') {
                    if (array_key_exists('vestido', $filtro) && $filtro['vestido'] != null && $filtro['vestido'] !== 'T') {
                        $this->db->where("oi.terminado", $filtro['vestido']);
                    }
                }
            } else {
                $this->db->where("oi.preparado", 'S'); //solo lo bonchado se puede vestir
                if (array_key_exists('vestido', $filtro) && $filtro['vestido'] != null && $filtro['vestido'] !== 'T') {
                    $this->db->where("oi.terminado", $filtro['vestido']);
                }
            }
        }

        if ((array_key_exists('empacado', $filtro)) && ($filtro['empacado'] !== NULL) && ($filtro['empacado'] !== 'T')) {
            if ($filtro['empacado'] == 'S') {
                $this->db->where("oc.empacada", 'S');
            } else {
                $this->db->where("(oc.empacada IS NULL OR oc.empacada = 'N')");
            }
        }
    }

}
