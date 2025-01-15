<?php

class Service_logistica extends My_Model {

    public function obtenerTiposDeCajas() {
        $this->db->select('tc.*');
        $this->db->from('ecommerce.tipo_caja tc');
        $this->db->where('tc.estado', ESTADO_ACTIVO);
        return $this->retornarMuchosSinPaginacion();
    }

    private function filtrar($filtro) {
        if ($filtro['store_id'] != 0) {
            $this->db->where('o.store_id', $filtro['store_id']);
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
                    $arrSelect = array('o.fecha_entrega >= ' => $fechaIni, 'o.fecha_entrega <= ' => $fechaFin);
                    break;
                default:
                    die;
                    break;
            }

            $this->db->where($arrSelect);
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
            if (isset($srt)) {
                $this->db->where($srt);
            }
        }
    }

    public function calcularSiguienteFechaEntrega($fecha_delivery, $tinturado = false) {
        switch ($fecha_delivery->format("D")) {
            case "Mon":
                $fecha_delivery = $fecha_delivery->modify('+7 day');
                break;
            case "Tue":
                $fecha_delivery = $fecha_delivery->modify('+6 day');
                break;
            case "Wed":
                $fecha_delivery = $fecha_delivery->modify('+6 day');
                break;
            case "Thu":
                $fecha_delivery = $fecha_delivery->modify('+6 day');
                break;
            case "Fri":
                $fecha_delivery = $fecha_delivery->modify('+6 day');
                break;
            case "Sat":
                $fecha_delivery = $fecha_delivery->modify('+5 day');
                break;
            case "Sun":
                $fecha_delivery = $fecha_delivery->modify('+5 day');
                break;
            default:
                $fecha_delivery = $fecha_delivery->modify('+7 day');
                break;
        }

        return $fecha_delivery->format("Y-m-d");
    }

    public function obtenerOrdenCaja($orden_caja_id) {
        $this->db->select('oc.*, o.id as info_orden_id, o.referencia_order_number as info_referencia_order_number, s.alias as info_store_alias, tc.nombre as info_nombre_caja, fc.finca_id');
        $this->db->from('ecommerce.orden_caja oc');
        $this->db->join('ecommerce.orden o', 'oc.orden_id = o.id', 'left');
        $this->db->join('ecommerce.store s', 's.id = o.store_id', 'left');
        $this->db->join('ecommerce.finca_caja fc', "fc.orden_caja_id = oc.id AND fc.estado = '" . ESTADO_ACTIVO . "'", 'left');
        $this->db->join('ecommerce.tipo_caja tc', 'oc.tipo_caja_id = tc.id', 'left');
        $this->db->where('oc.id', $orden_caja_id);
//        $this->db->where('o.estado', ESTADO_ACTIVO);
//        $this->db->where('oc.estado', ESTADO_ACTIVO);
        return $this->retornarUno();
    }

    public function buscarTrackingNumber($tracking_number) {
        $this->db->select('oc.orden_id, oc.id, oc.empacada, tc.nombre as info_nombre_caja, oc.kardex_check');
        $this->db->from('logistica.orden_caja_tracking_number octn');
        $this->db->join('ecommerce.orden_caja oc', 'oc.id = octn.orden_caja_id', 'left');
        $this->db->join('ecommerce.tipo_caja tc', 'oc.tipo_caja_id = tc.id', 'left');
        $this->db->where('octn.tracking_number', $tracking_number);
        $this->db->where('octn.estado', ESTADO_ACTIVO);
        $this->db->where('oc.estado', ESTADO_ACTIVO);
        return $this->retornarUno();
    }

    public function obtenerTrackingNumberCaja($caja_id, $estado = ESTADO_ACTIVO) {
        $this->db->select('octn.*');
        $this->db->from('logistica.orden_caja_tracking_number octn');
        if ($estado) {
            $this->db->where('octn.estado', $estado);
        }
        $this->db->where('octn.orden_caja_id', $caja_id);
        return $this->retornarUno();
    }

    public function buscarCajaId($caja_id) {
        $this->db->select('oc.orden_id, oc.id, oc.empacada, tc.nombre as info_nombre_caja, oc.kardex_check');
        $this->db->from('ecommerce.orden_caja oc');
        $this->db->join('ecommerce.tipo_caja tc', 'oc.tipo_caja_id = tc.id', 'left');
        $this->db->where('oc.id', $caja_id);
        $this->db->where('oc.estado', ESTADO_ACTIVO);
        return $this->retornarUno();
    }

    public function listado($filtro) {
//error_log(print_r($store_id,true));
        $this->db->select('
            o.id as orden_id,
            o.store_id,
            o.referencia_order_number,
            octn.tracking_number,
            s.alias as store_alias,
            oc.id as orden_caja_id,
            oc.orden_id,
            oc.kardex_check, oc.kardex_fecha,
            count(oci.id) as items_en_caja,
            o.fecha_carguera,
            cde.id as direccion_entrega_id,
            cde.destinatario_nombre,
            cde.destinatario_apellido,
            cde.destinatario_company,
            cde.address_1,
            cde.address_2,
            cde.city,
            cde.state,
            cde.country,
            cde.zip_code,
            cde.phone,
            cde.state_code,
            cde.country_code,
            tc.nombre as caja_nombre,
            tc.grupo as grupo,
            tc.length,
            tc.width,
            tc.height,
            tc.weight');
        $this->db->from('ecommerce.orden o');
        $this->db->join('ecommerce.store s', "o.store_id = s.id", 'left');
        $this->db->join('ecommerce.orden_caja oc', "o.id = oc.orden_id AND oc.estado = '" . ESTADO_ACTIVO . "'", 'left');
        $this->db->join('logistica.orden_caja_tracking_number octn', "octn.orden_caja_id = oc.id AND octn.estado = '" . ESTADO_ACTIVO . "'", 'left');
        $this->db->join('ecommerce.orden_caja_item oci', "oci.orden_caja_id = oc.id AND oci.estado = '" . ESTADO_ACTIVO . "'", 'left');
        $this->db->join('ecommerce.orden_item oi', "oci.orden_item_id = oi.id AND oi.estado = '" . ESTADO_ACTIVO . "' ", 'left');
        $this->db->join('ecommerce.cliente_direccion_envio cde', 'cde.id = o.cliente_direccion_id', 'left');
        $this->db->join('ecommerce.tipo_caja tc', 'tc.id = oc.tipo_caja_id', 'left');
        $this->db->join('ecommerce.finca_caja fc', "fc.orden_caja_id = oc.id  AND fc.estado = '" . ESTADO_ACTIVO . "' ", 'left');
        if ($filtro['store_id'] != 0) {
            $this->db->where('o.store_id', $filtro['store_id']);
        }

        $this->db->where('o.estado', ESTADO_ACTIVO); //si se van a cambiar estados de una orden hay que revisar esta seccion
        $this->db->where('oc.estado', ESTADO_ACTIVO);
        $this->db->where('oci.estado', ESTADO_ACTIVO);

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
            if (isset($srt)) {
                $this->db->where($srt);
            }
        }

        if ($filtro['con_tracking_number'] !== 'T') {
            if ($filtro['con_tracking_number'] == 'S') {
                $this->db->where("octn.tracking_number is NOT NULL ");
            } else {
                $this->db->where("(octn.tracking_number is NULL OR octn.estado =  '" . ESTADO_INACTIVO . "')");
            }
        }

        if ($filtro['con_kardex'] !== 'T') {
            if ($filtro['con_kardex'] == 'S') {
                $this->db->where('oc.kardex_check', 'S');
            } else {
                $this->db->where('oc.kardex_check', 'N');
            }
        }


        $this->db->where('oi.estado', ESTADO_ACTIVO);

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
                case 2://actualizacion
                    $arrSelect = array('o.fecha_entrega >= ' => $fechaIni, 'o.fecha_entrega <= ' => $fechaFin);
                    break;
                default:
                    $arrSelect = array('o.fecha_entrega >= ' => $fechaIni, 'o.fecha_entrega <= ' => $fechaFin);
                    break;
            }

            $this->db->where($arrSelect);
        }

        if (($filtro['con_tracking_number'] == 'S') && (!empty($filtro['rango_busqueda_full']))) {
            //rango_busqueda espera "dd/mm/YYYY - dd/mm/YYYY"
            $arrRango = explode(" - ", $filtro['rango_busqueda_full']);
            $fechaIni = date_format(DateTime::createFromFormat(FORMATO_FECHA, convertirFechaBD($arrRango[0])), 'Y-m-d 00:00:00');
            $fechaFin = date_format(DateTime::createFromFormat(FORMATO_FECHA, convertirFechaBD($arrRango[1])), 'Y-m-d 23:59:59');
            if (sizeof($arrRango) != 2) {
                //siempre debe de ser 2, si no es un error y vamos a devolver un error
                return array(false, -1);
            }
            switch ($filtro['tipo_calendario']) {
                case 0://caja tracking
                    $arrSelect = array('octn.creacion_fecha >= ' => $fechaIni, 'octn.creacion_fecha <= ' => $fechaFin);
                    break;
                default:
                    $arrSelect = array('octn.creacion_fecha >= ' => $fechaIni, 'octn.creacion_fecha <= ' => $fechaFin);
                    break;
            }

            $this->db->where($arrSelect);
        }

        $this->db->group_by(array('o.id', 's.alias', 'o.store_id', 'o.referencia_order_number',
            'octn.tracking_number',
            'oc.id', 'oc.orden_id', 'o.fecha_carguera', 'cde.id',
            'oc.kardex_check', 'oc.kardex_fecha',
            'cde.destinatario_nombre',
            'cde.destinatario_apellido',
            'cde.destinatario_company',
            'cde.address_1',
            'cde.address_2',
            'cde.city',
            'cde.state',
            'cde.country',
            'cde.zip_code',
            'cde.phone',
            'cde.state_code',
            'cde.country_code',
            'tc.nombre',
            'tc.grupo',
            'tc.length',
            'tc.width',
            'tc.height',
            'tc.weight'));
        $this->db->order_by('oc.kardex_fecha', 'ASC');
        $this->db->order_by('oc.orden_id', 'ASC');
        $arrCajas = $this->retornarMuchosSinPaginacion();

        $arr = array();
        if ($arrCajas) {
            foreach ($arrCajas as $o) {
                $items = $this->service_ecommerce_orden->obtenerOrdenItem(false, $o->orden_id);
                $sum = 0;
                foreach ($items as $item) {
                    $sum += $item->precio;
                }
                $o->precio = $sum;
                $arr[] = $o;
            }
        }

        return $arr;
    }

    public function obtenerLogisticaUPS() {
        $this->db->from('ecommerce.ups_store ups');
        $this->db->where('ups.estado', ESTADO_ACTIVO);
        $this->db->order_by('ups.store_id', 'ASC');
        return $this->retornarMuchosSinPaginacion();
    }

    public function actualizarTrackingNumberOrdenCaja($orden_caja_id, $tracking_number, $empresa_logistica_id) {
        $datos['orden_caja_id'] = $orden_caja_id;
        $datos['tracking_number'] = $tracking_number;
        $datos['empresa_logistica_id'] = $empresa_logistica_id;
        $datos['estado'] = ESTADO_ACTIVO;
        return $this->ingresar("logistica.orden_caja_tracking_number", $datos, false, true);
    }

    public function inactivarTrackingNumberAnteriores($orden_caja_id) {
        $datos['orden_caja_id'] = $orden_caja_id;
        $datos['estado'] = ESTADO_INACTIVO;
        return $this->actualizar("logistica.orden_caja_tracking_number", $datos, array("orden_caja_id" => -1, "estado" => ESTADO_ACTIVO), true);
    }

    public function excelCajasPorFecha() {
        
    }

    public function obtenerResumen($filtro) {
        $arrRango = explode(" - ", $filtro['rango_busqueda']);
        $fechaIni = date_format(DateTime::createFromFormat(FORMATO_FECHA, convertirFechaBD($arrRango[0])), 'Y-m-d 00:00:00');
        $fechaFin = date_format(DateTime::createFromFormat(FORMATO_FECHA, convertirFechaBD($arrRango[1])), 'Y-m-d 23:59:59');
        $this->db->select("s.alias, o.id as orden_id, oc.id as orden_caja_id, o.store_id, 
        tc.id as tipo_caja_id, tc.nombre, p.titulo as titulo_producto, pv.titulo as titulo_variante, 
        pv.sku, pv.largo_cm, i.descripcion as ingrediente_descripcion,  i.tipo as tipo_producto, 
        SUM(oi.cantidad * r.cantidad) as total_stems, 
        SUM(oi.precio) as total_precio,  
        i.longitud, i.id as ingrediente_id, o.fecha_carguera, fc.finca_id");

        $this->db->from('ecommerce.orden_caja oc');
        $this->db->join('ecommerce.orden o', 'oc.orden_id = o.id', 'left');
        $this->db->join('ecommerce.store s', 'o.store_id = s.id', 'left');
        $this->db->join('ecommerce.tipo_caja tc ', ' oc.tipo_caja_id = tc.id', 'left');
        $this->db->join('ecommerce.orden_caja_item oci ', ' oci.orden_caja_id = oc.id', 'left');
        $this->db->join('ecommerce.orden_item oi ', ' oi.id = oci.orden_item_id', 'left');
        $this->db->join('ecommerce.producto p ', ' oi.producto_id = p.id', 'left');
        $this->db->join('ecommerce.producto_variante pv ', ' oi.variante_id = pv.id', 'left');
        $this->db->join('produccion.receta r ', ' pv.sku = r.sku', 'left');
        $this->db->join("ecommerce.finca_caja fc", "fc.orden_caja_id = oci.orden_caja_id AND fc.estado = '" . ESTADO_ACTIVO . "' ", 'left');
        $this->db->join('produccion.ingrediente i ', ' r.ingrediente_id = i.id', 'left');
        $this->db->join('produccion.ingrediente_precio_finca ipf', 'ipf.ingrediente_id = i.id AND ipf.finca_id = fc.finca_id', 'left');
        $this->db->join('logistica.orden_caja_tracking_number octn ', ' octn.orden_caja_id = oc.id', 'left');

        $this->db->where('o.estado', ESTADO_ACTIVO);
        $this->db->where('ipf.estado', ESTADO_ACTIVO);
        $this->db->where('oi.estado', ESTADO_ACTIVO);
        $this->db->where('oc.estado', ESTADO_ACTIVO);
        $this->db->where('oci.estado', ESTADO_ACTIVO);
        $this->db->where('r.estado', ESTADO_ACTIVO);

        $this->db->where("'$fechaIni' BETWEEN ipf.fecha_inicio_vigencia and ipf.fecha_fin_vigencia");
        //AND '2021-11-06' BETWEEN ipf.fecha_inicio_vigencia AND ipf.fecha_fin_vigencia
        $this->filtrar($filtro);

        $this->db->where("(octn.tracking_number is NULL OR octn.estado = '" . ESTADO_ACTIVO . "') ");
        if ($filtro['tinturado']) {
            if ($filtro['tinturado'] == "T") {
                $this->db->where("pv.sku LIKE 'AGR_PT\_%'");
            } else {
                $this->db->where("pv.sku LIKE 'AGR_P\_%'");
            }
        }

        if (array_key_exists("orden_caja_id", $filtro) && ($filtro["orden_caja_id"] != "T")) {
            $this->db->where("oc.id", $filtro["orden_caja_id"]);
        }
        $this->db->group_by(array('o.id', 's.alias', 'oc.id', 'o.store_id', 'tc.id', 'tc.nombre', 'p.titulo', 'pv.titulo', 'pv.sku', 'pv.largo_cm', 'i.tipo', 'i.descripcion', 'i.id', 'i.longitud', 'o.fecha_carguera', 'fc.finca_id'));
        $this->db->order_by('tc.id', 'ASC');
        $arr = $this->retornarMuchosSinPaginacion(true);
        return $arr;
    }

    public function obtenerResumenOld($filtro) {
        $this->db->select("s.alias, o.id as orden_id, oc.id as orden_caja_id, o.store_id, tc.id as tipo_caja_id, tc.nombre, p.titulo as titulo_producto, pv.titulo as titulo_variante, pv.sku, pv.largo_cm, i.descripcion as ingrediente_descripcion,  i.tipo as tipo_producto, SUM(oi.cantidad * r.cantidad) as total_stems, SUM(oi.precio) as total_precio, i.costo_40, i.costo_50, i.costo_60, i.costo_70, i.costo_80, i.costo_90, i.costo_100");

        $this->db->from('ecommerce.orden_caja oc');
        $this->db->join('ecommerce.orden o', 'oc.orden_id = o.id', 'left');
        $this->db->join('ecommerce.store s', 'o.store_id = s.id', 'left');
        $this->db->join('ecommerce.tipo_caja tc ', ' oc.tipo_caja_id = tc.id', 'left');
        $this->db->join('ecommerce.orden_caja_item oci ', ' oci.orden_caja_id = oc.id', 'left');
        $this->db->join('ecommerce.orden_item oi ', ' oi.id = oci.orden_item_id', 'left');
        $this->db->join('ecommerce.producto p ', ' oi.producto_id = p.id', 'left');
        $this->db->join('ecommerce.producto_variante pv ', ' oi.variante_id = pv.id', 'left');
        $this->db->join('produccion.receta r ', ' pv.sku = r.sku', 'left');
        $this->db->join('produccion.ingrediente i ', ' r.ingrediente_id = i.id', 'left');
        $this->db->join('logistica.orden_caja_tracking_number octn ', ' octn.orden_caja_id = oc.id', 'left');

        $this->db->where('o.estado', ESTADO_ACTIVO);

        $this->db->where('oi.estado', ESTADO_ACTIVO);
        $this->db->where('oc.estado', ESTADO_ACTIVO);
        $this->db->where('oci.estado', ESTADO_ACTIVO);
        $this->db->where('r.estado', ESTADO_ACTIVO);

        $this->filtrar($filtro);

//        if ($filtro['con_tracking_number'] !== 'T') {
//            if ($filtro['con_tracking_number'] == 'S') {
//                $this->db->where("octn.tracking_number is NOT NULL ");
//            } else {
//                $this->db->where("(octn.tracking_number is NULL OR octn.estado =  '" . ESTADO_INACTIVO . "')");
//            }
//        }

        $this->db->where("(octn.tracking_number is NULL OR octn.estado = '" . ESTADO_ACTIVO . "') ");

        if ($filtro['tinturado']) {
            if ($filtro['tinturado'] == "T") {
                $this->db->where("pv.sku LIKE 'AGR_PT\_%'");
            } else {
                $this->db->where("pv.sku LIKE 'AGR_P\_%'");
            }
        }
        if (array_key_exists("orden_caja_id", $filtro) && ($filtro["orden_caja_id"] != "T")) {
            $this->db->where("oc.id", $filtro["orden_caja_id"]);
        }
        $this->db->group_by(array('o.id', 's.alias', 'oc.id', 'o.store_id', 'tc.id', 'tc.nombre', 'p.titulo', 'pv.titulo', 'pv.sku', 'pv.largo_cm', 'i.tipo', 'i.descripcion', 'i.costo_40', 'i.costo_50', 'i.costo_60', 'i.costo_70', 'i.costo_80', 'i.costo_90', 'i.costo_100'));
        $this->db->order_by('tc.id', 'ASC');
        return $this->retornarMuchosSinPaginacion();
    }

    public function obtenerResumenPropiedades($filtro) {
        $arrRango = explode(" - ", $filtro['rango_busqueda']);
        $fechaIni = date_format(DateTime::createFromFormat(FORMATO_FECHA, convertirFechaBD($arrRango[0])), 'Y-m-d 00:00:00');
        $fechaFin = date_format(DateTime::createFromFormat(FORMATO_FECHA, convertirFechaBD($arrRango[1])), 'Y-m-d 23:59:59');
        $this->db->select("s.alias, o.id as orden_id, oc.id as orden_caja_id, o.store_id, tc.id as tipo_caja_id, tc.nombre, pd.descripcion, r.sku, pd.nombre as largo_cm, i.descripcion as ingrediente_descripcion, i.tipo as tipo_producto, SUM(oip.valor::FLOAT * r.cantidad) as total_stems, 0 as total_precio,i.id as ingrediente_id, i.longitud, o.fecha_carguera, fc.finca_id");
        $this->db->from('ecommerce.orden_caja oc');
        $this->db->join('ecommerce.orden o', 'oc.orden_id = o.id', 'left');
        $this->db->join('ecommerce.store s', 'o.store_id = s.id', 'left');
        $this->db->join('ecommerce.tipo_caja tc ', ' oc.tipo_caja_id = tc.id', 'left');
        $this->db->join('ecommerce.orden_caja_item oci ', ' oci.orden_caja_id = oc.id', 'left');
        $this->db->join('ecommerce.orden_item oi ', ' oi.id = oci.orden_item_id', 'left');
        $this->db->join('ecommerce.producto_variante pv ', ' oi.variante_id = pv.id', 'left');
        $this->db->join('ecommerce.orden_item_propiedad oip ', ' oi.id = oip.orden_item_id', 'left');
        $this->db->join('ecommerce.propiedad pd ', ' oip.propiedad_id = pd.id', 'left');
        $this->db->join('produccion.receta r ', ' pd.nombre = r.sku', 'left');
        $this->db->join('produccion.ingrediente i ', ' r.ingrediente_id = i.id', 'left');
        $this->db->join('ecommerce.finca_caja fc', "fc.orden_caja_id = oci.orden_caja_id AND fc.estado = '" . ESTADO_ACTIVO . "'", 'left');
        $this->db->join('produccion.ingrediente_precio_finca ipf', 'ipf.ingrediente_id = i.id AND ipf.finca_id = fc.finca_id', 'left');
        $this->db->join('logistica.orden_caja_tracking_number octn ', ' octn.orden_caja_id = oc.id', 'left');

        $this->db->where("'$fechaIni' BETWEEN ipf.fecha_inicio_vigencia and ipf.fecha_fin_vigencia");
        $this->db->where('o.estado', ESTADO_ACTIVO);
        $this->db->where('ipf.estado', ESTADO_ACTIVO);
        $this->db->where('oi.estado', ESTADO_ACTIVO);
        $this->db->where('oip.estado', ESTADO_ACTIVO);
        $this->db->where('oc.estado', ESTADO_ACTIVO);
        $this->db->where('oci.estado', ESTADO_ACTIVO);
        $this->db->where('r.estado', ESTADO_ACTIVO);
        $this->db->where("pd.nombre LIKE 'AGR_%'");

        $this->filtrar($filtro);

        $this->db->where("(octn.tracking_number is NULL OR octn.estado = '" . ESTADO_ACTIVO . "') ");
        if ($filtro['tinturado']) {
            if ($filtro['tinturado'] == "T") {
                $this->db->where("pd.nombre LIKE 'AGR_CT\_%'");
            } else {
                $this->db->where("pd.nombre LIKE 'AGR_C\_%'");
            }
        }
        if (array_key_exists("orden_caja_id", $filtro) && ($filtro["orden_caja_id"] != "T")) {
            $this->db->where("oc.id", $filtro["orden_caja_id"]);
        }
        $this->db->group_by(array('o.id', 's.alias', 'oc.id', 'o.store_id', 'tc.id', 'tc.nombre', 'pd.descripcion', 'r.sku', 'pd.nombre', 'i.tipo', 'i.descripcion', 'i.id', 'i.longitud', 'o.fecha_carguera', 'fc.finca_id'));
        $this->db->order_by('tc.id', 'ASC');
        return $this->retornarMuchosSinPaginacion(1);
    }

    public function obtenerResumenPropiedadesOld($filtro) {

        $this->db->select("s.alias, o.id as orden_id, oc.id as orden_caja_id, o.store_id, tc.id as tipo_caja_id, tc.nombre, pd.descripcion, r.sku, pd.nombre as largo_cm, i.descripcion as ingrediente_descripcion, i.tipo as tipo_producto, SUM(oip.valor::FLOAT * r.cantidad) as total_stems, 0 as total_precio, i.costo_40, i.costo_50, i.costo_60, i.costo_70, i.costo_80, i.costo_90, i.costo_100");

        $this->db->from('ecommerce.orden_caja oc');
        $this->db->join('ecommerce.orden o', 'oc.orden_id = o.id', 'left');
        $this->db->join('ecommerce.store s', 'o.store_id = s.id', 'left');
        $this->db->join('ecommerce.tipo_caja tc ', ' oc.tipo_caja_id = tc.id', 'left');
        $this->db->join('ecommerce.orden_caja_item oci ', ' oci.orden_caja_id = oc.id', 'left');
        $this->db->join('ecommerce.orden_item oi ', ' oi.id = oci.orden_item_id', 'left');
        $this->db->join('ecommerce.producto_variante pv ', ' oi.variante_id = pv.id', 'left');
        $this->db->join('ecommerce.orden_item_propiedad oip ', ' oi.id = oip.orden_item_id', 'left');
        $this->db->join('ecommerce.propiedad pd ', ' oip.propiedad_id = pd.id', 'left');
        $this->db->join('produccion.receta r ', ' pd.nombre = r.sku', 'left');
        $this->db->join('produccion.ingrediente i ', ' r.ingrediente_id = i.id', 'left');
        $this->db->join('logistica.orden_caja_tracking_number octn ', ' octn.orden_caja_id = oc.id', 'left');

        $this->db->where('o.estado', ESTADO_ACTIVO);
        $this->db->where('oi.estado', ESTADO_ACTIVO);
        $this->db->where('oip.estado', ESTADO_ACTIVO);
        $this->db->where('oc.estado', ESTADO_ACTIVO);
        $this->db->where('oci.estado', ESTADO_ACTIVO);
        $this->db->where('r.estado', ESTADO_ACTIVO);
        $this->db->where("pd.nombre LIKE 'AGR_%'");

        $this->filtrar($filtro);

//        if ($filtro['con_tracking_number'] !== 'T') {
//            if ($filtro['con_tracking_number'] == 'S') {
//                $this->db->where("octn.tracking_number is NOT NULL ");
//            } else {
//                $this->db->where("(octn.tracking_number is NULL OR octn.estado =  '" . ESTADO_INACTIVO . "')");
//            }
//        }
        $this->db->where("(octn.tracking_number is NULL OR octn.estado = '" . ESTADO_ACTIVO . "') ");
        if ($filtro['tinturado']) {
            if ($filtro['tinturado'] == "T") {
                $this->db->where("pd.nombre LIKE 'AGR_CT\_%'");
            } else {
                $this->db->where("pd.nombre LIKE 'AGR_C\_%'");
            }
        }
//$this->db->where("oc.id", "20257");
        if (array_key_exists("orden_caja_id", $filtro) && ($filtro["orden_caja_id"] != "T")) {
            $this->db->where("oc.id", $filtro["orden_caja_id"]);
        }
        $this->db->group_by(array('o.id', 's.alias', 'oc.id', 'o.store_id', 'tc.id', 'tc.nombre', 'pd.descripcion', 'r.sku', 'pd.nombre', 'i.tipo', 'i.descripcion', 'i.costo_40', 'i.costo_50', 'i.costo_60', 'i.costo_70', 'i.costo_80', 'i.costo_90', 'i.costo_100'));
        $this->db->order_by('tc.id', 'ASC');
        return $this->retornarMuchosSinPaginacion(1);
    }

    public function obtenerCostosFlor() {
        $this->db->select('ipf.id,i.nombre,i.tipo,i.longitud,re.sku,re.ingrediente_id,re.cantidad,ipf.finca_id,ipf.precio_unitario');
        $this->db->from('produccion.receta re');
        $this->db->join('produccion.ingrediente_precio_finca ipf', 're.ingrediente_id = ipf.ingrediente_id', 'left');
        $this->db->join('produccion.ingrediente i', 'i.id = ipf.ingrediente_id', 'left');
        $this->db->where('re.estado', ESTADO_ACTIVO);
        $this->db->where('ipf.estado', ESTADO_ACTIVO);
        return $this->retornarMuchosConPaginacion(true);
    }

    public function obtenerPrecioPorStems($fecha_carguera, $finca_id, $ingrediente_id) {
        $this->db->select('ipf.precio_unitario');
        $this->db->from('produccion.ingrediente_precio_finca ipf', 'i.id = ipf.ingrediente_id', 'left');
        $this->db->where('ipf.estado', ESTADO_ACTIVO);
        $this->db->where('ipf.ingrediente_id', $ingrediente_id);
        $this->db->where('ipf.finca_id', $finca_id);
        $this->db->where("'$fecha_carguera' BETWEEN ipf.fecha_inicio_vigencia and ipf.fecha_fin_vigencia");
        return $this->retornarUno();
    }

    public function obtenerPrecioSku($fecha_carguera, $finca_id, $sku) {
        //TODO SUMAR TOTAL PRECIO POR SKU 
        $this->db->select('re.sku, re.ingrediente_id, re.cantidad, i.longitud, ipf.ingrediente_id,
        ipf.finca_id, ipf.precio_unitario');
        $this->db->from('produccion.receta re');
        $this->db->join('produccion.ingrediente i', 're.ingrediente_id = i.id', 'left');
        $this->db->join('produccion.ingrediente_precio_finca ipf', 'i.id = ipf.ingrediente_id', 'left');
        $this->db->where('re.estado', ESTADO_ACTIVO);
        $this->db->where('ipf.estado', ESTADO_ACTIVO);
        $this->db->where('re.sku', $sku);
        $this->db->where('ipf.finca_id', $finca_id);
        $this->db->where("'$fecha_carguera' BETWEEN ipf.fecha_inicio_vigencia and ipf.fecha_fin_vigencia");
        return $this->retornarUno();
    }

    public function obtenerCostosFlorOld() {
        $this->db->from('produccion.costo_flor c');
        $this->db->where('c.estado', ESTADO_ACTIVO);
        return $this->retornarMuchosSinPaginacion();
    }

    public function obtenerCajasDeTipo($filtro) {
        if ($filtro['tinturado'] == "T") {
            $this->db->select('cct.orden_caja_id, cct.id as store_id, cct.alias, cct.orden_id, cct.tipo_caja_id, cct.finca_id');
            $this->db->from("ecommerce.v_cajas_con_tinturados cct");
            $this->db->join('ecommerce.orden o', 'cct.orden_id = o.id');
            $this->db->join('ecommerce.orden_caja oc', "oc.orden_id = o.id AND oc.estado = '" . ESTADO_ACTIVO . "'", 'left');
            $this->db->join('ecommerce.finca_caja fc', "fc.orden_caja_id = oc.id AND fc.estado = '" . ESTADO_ACTIVO . "'", 'left');
            if ($filtro['tipo_caja_id'] != "T") {
                $this->db->where("cct.tipo_caja_id", $filtro['tipo_caja_id']);
            }
            $this->filtrar($filtro);
            $this->db->group_by(array('cct.orden_caja_id', 'cct.id ', 'cct.alias', 'cct.orden_id', 'cct.tipo_caja_id', 'cct.finca_id'));
        } else {
            $this->db->select('cst.orden_caja_id, cst.id as store_id, cst.alias, cst.orden_id, cst.tipo_caja_id, cst.finca_id');
            $this->db->from("ecommerce.v_cajas_sin_tinturados cst");
            $this->db->join('ecommerce.orden o', 'cst.orden_id = o.id');
            $this->db->join('ecommerce.orden_caja oc', "oc.orden_id = o.id AND oc.estado = '" . ESTADO_ACTIVO . "'", 'left');
            $this->db->join('ecommerce.finca_caja fc', "fc.orden_caja_id = oc.id AND fc.estado = '" . ESTADO_ACTIVO . "'", 'left');
            if ($filtro['tipo_caja_id'] != "T") {
                $this->db->where("cst.tipo_caja_id", $filtro['tipo_caja_id']);
            }
            $this->filtrar($filtro);
            $this->db->group_by(array('cst.orden_caja_id', 'cst.id ', 'cst.alias', 'cst.orden_id', 'cst.tipo_caja_id', 'cst.finca_id'));
        }

       
        $arr =  $this->retornarMuchosSinPaginacion(true);
        return $arr;
    }

    public function obtenerGuias($fecha) {
        $arrRango = explode(" - ", $fecha);
        $fechaIni = date_format(DateTime::createFromFormat(FORMATO_FECHA, convertirFechaBD($arrRango[0])), 'Y-m-d 00:00:00');
        $fechaFin = date_format(DateTime::createFromFormat(FORMATO_FECHA, convertirFechaBD($arrRango[1])), 'Y-m-d 00:00:00');
        if ($fechaIni != $fechaFin) {
            return false;
        }
        $this->db->from("logistica.guia_despacho gd");
        $this->db->where('gd.fecha', $fechaIni);
        return $this->retornarUno();
    }

    /*     * codigo de migracion */

    public function obtenerIngredientes() {
        $this->db->select('i.id,i.longitud, i.tipo');
        $this->db->from('produccion.ingrediente i');
        return $this->retornarMuchosSinPaginacion();
    }

    public function ingresarIng($obj) {
        error_log("Vamos a ingresar un ingrediente con su precio");
        $id = $this->ingresar("produccion.ingrediente_precio_finca", $obj, true, false);
        if ($id) {
            $dato_log = array(
                "ingrediente_id" => $id,
                "accion" => "creacion de iningrediente con su precio" . json_encode($obj),
            );
            $this->registrarLog("produccion.ingrediente_log", $dato_log);
        }
        return $id;
    }

}
