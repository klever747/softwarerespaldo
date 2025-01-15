<?php

class Service_manufactura extends My_Model {

    private function filtrar($filtro) {

        $this->db->where('o.estado', ESTADO_ACTIVO);

        if ($filtro['store_id'] != 0) {
            $this->db->where('o.store_id', $filtro['store_id']);
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

    public function obtenerListado($filtro) {
        $this->db->select("SUM(oi.cantidad) as total, oi.producto_id, p.titulo as producto_titulo");
        $this->db->from('ecommerce.orden o');
        $this->db->join('ecommerce.orden_item oi', "o.id = oi.orden_id AND oi.estado = '" . ESTADO_ACTIVO . "'", 'left');
        $this->db->join('ecommerce.orden_caja_item oci', "oi.id = oci.orden_item_id AND oci.estado = '" . ESTADO_ACTIVO . "'", 'left');
        $this->db->join('ecommerce.orden_caja oc', "oc.id = oci.orden_caja_id AND o.id = oc.orden_id AND oc.estado = '" . ESTADO_ACTIVO . "'", 'left');
        $this->db->join('ecommerce.producto p', "oi.producto_id = p.id", 'left');
        $this->db->join('ecommerce.producto_variante pv', "oi.variante_id = pv.id", 'left');
        $this->db->join('ecommerce.finca_caja fc', "fc.orden_caja_id = oc.id AND fc.estado = '" . ESTADO_ACTIVO . "'", 'left');
        unset($filtro['bonchado']);
        unset($filtro['vestido']);

        $this->filtrar($filtro);

        $this->db->group_by(array('oi.producto_id', 'p.titulo'));
        $this->db->order_by('1', 'DESC');

        return $this->retornarMuchosSinPaginacion(true);
    }

    public function obtenerListadoPorProducto($filtro, $producto_id) {
        $this->db->select("SUM(oi.cantidad) as total, oi.variante_id");
        $this->db->from('ecommerce.orden o');
        $this->db->join('ecommerce.orden_item oi', "o.id = oi.orden_id AND oi.estado = '" . ESTADO_ACTIVO . "'", 'left');
        $this->db->join('ecommerce.orden_caja_item oci', "oi.id = oci.orden_item_id AND oci.estado = '" . ESTADO_ACTIVO . "'", 'left');
        $this->db->join('ecommerce.orden_caja oc', "oc.id = oci.orden_caja_id AND o.id = oc.orden_id AND oc.estado = '" . ESTADO_ACTIVO . "'", 'left');
        $this->db->join('ecommerce.producto_variante pv', "oi.variante_id = pv.id", 'left');
        $this->db->join('ecommerce.producto p', "oi.producto_id = p.id", 'left');
        $this->db->join('ecommerce.finca_caja fc', "fc.orden_caja_id = oc.id AND fc.estado = '" . ESTADO_ACTIVO . "'", 'left');
        unset($filtro['bonchado']);
        unset($filtro['vestido']);

        $this->filtrar($filtro);

        $this->db->where('pv.producto_id', $producto_id);

        $this->db->group_by(array('oi.variante_id'));
        $this->db->order_by('1', 'DESC');

        return $this->retornarMuchosSinPaginacion();
    }

    public function obtenerListadoPorVariante($filtro, $variante_id) {
        $this->db->select("SUM(oi.cantidad) as total, SUM(CASE WHEN oi.preparado = 'S' THEN oi.cantidad ELSE 0 END) as total_bonchado, "
                . " SUM(CASE WHEN oi.terminado = 'S' THEN oi.cantidad ELSE 0 END) as total_vestido, oi.variante_id, pv.titulo as variante_titulo");
        $this->db->from('ecommerce.orden o');
        $this->db->join('ecommerce.orden_item oi', "o.id = oi.orden_id AND oi.estado = '" . ESTADO_ACTIVO . "'", 'left');
        $this->db->join('ecommerce.orden_caja_item oci', "oi.id = oci.orden_item_id AND oci.estado = '" . ESTADO_ACTIVO . "'", 'left');
        $this->db->join('ecommerce.orden_caja oc', "oc.id = oci.orden_caja_id AND o.id = oc.orden_id AND oc.estado = '" . ESTADO_ACTIVO . "'", 'left');
        $this->db->join('ecommerce.producto_variante pv', "oi.variante_id = pv.id", 'left');
        $this->db->join('ecommerce.producto p', "oi.producto_id = p.id", 'left');
        $this->db->join('ecommerce.finca_caja fc', "fc.orden_caja_id = oc.id AND fc.estado = '" . ESTADO_ACTIVO . "'", 'left');

        unset($filtro['bonchado']);
        unset($filtro['vestido']);
        $this->filtrar($filtro);

        $this->db->where('pv.id', $variante_id);

        $this->db->group_by(array('oi.variante_id', 'pv.titulo'));
        $this->db->order_by('1', 'DESC');

        return $this->retornarUno();
    }

    public function obtenerCantidadItemsEnOrdenesPorVariante($filtro, $variante_id) {
        $this->db->select("DISTINCT(o.id)");
        $this->db->from('ecommerce.orden o');
        $this->db->join('ecommerce.orden_item oi', "o.id = oi.orden_id AND oi.estado = '" . ESTADO_ACTIVO . "'", 'left');
        $this->db->join('ecommerce.orden_caja_item oci', "oi.id = oci.orden_item_id AND oci.estado = '" . ESTADO_ACTIVO . "'", 'left');
        $this->db->join('ecommerce.orden_caja oc', "oc.id = oci.orden_caja_id AND o.id = oc.orden_id AND oc.estado = '" . ESTADO_ACTIVO . "'", 'left');
        $this->db->join('ecommerce.producto_variante pv', "oi.variante_id = pv.id", 'left');
        $this->db->join('ecommerce.producto p', "oi.producto_id = p.id", 'left');
        $this->db->join('ecommerce.finca_caja fc', "fc.orden_caja_id = oc.id AND fc.estado = '" . ESTADO_ACTIVO . "'", 'left');

        $this->filtrar($filtro);

        $this->db->where('pv.id', $variante_id);

        //$this->db->group_by(array('oi.variante_id'));
        $this->db->order_by('1', 'DESC');

        return $this->retornarMuchosSinPaginacion();
    }

    public function obtenerListadoOrdenesPorVariante($filtro, $variante_id) {
        $this->db->select("DISTINCT(o.id)");
        $this->db->from('ecommerce.orden o');
        $this->db->join('ecommerce.orden_item oi', "o.id = oi.orden_id AND oi.estado = '" . ESTADO_ACTIVO . "'", 'left');
        $this->db->join('ecommerce.orden_caja_item oci', "oi.id = oci.orden_item_id AND oci.estado = '" . ESTADO_ACTIVO . "'", 'left');
        $this->db->join('ecommerce.orden_caja oc', "oc.id = oci.orden_caja_id AND o.id = oc.orden_id AND oc.estado = '" . ESTADO_ACTIVO . "'", 'left');
        $this->db->join('ecommerce.producto_variante pv', "oi.variante_id = pv.id", 'left');
        $this->db->join('ecommerce.producto p', "oi.producto_id = p.id", 'left');
        $this->db->join('ecommerce.finca_caja fc', "fc.orden_caja_id = oc.id AND fc.estado = '" . ESTADO_ACTIVO . "'", 'left');

        $this->filtrar($filtro);

        $this->db->where('pv.id', $variante_id);

        //$this->db->group_by(array('oi.variante_id'));
        $this->db->order_by('1', 'DESC');

        return $this->retornarMuchosSinPaginacion();
    }

    public function obtenerOrdenesWrap($filtro, $variante_id) {
        $this->db->select("o.id as id, s.alias, o.referencia_order_number, oip.valor as valor, "
                . " oi.id as orden_item_id,  oi.cantidad, oi.preparado as bonchado, oi.terminado as vestido, "
                . " CASE WHEN (oip.valor IS NULL) OR  UPPER(oip.valor) LIKE '%NO%' OR  UPPER(oip.valor) LIKE '%SIN%' OR  UPPER(oip.valor) LIKE '%WITHOUT%' THEN 2 WHEN UPPER(oip.valor) LIKE '%LUXURY%' THEN 1 ELSE 0 END as tipo_wrap");
        $this->db->from('ecommerce.orden o');
        $this->db->join('ecommerce.store s', "o.store_id = s.id", 'left');
        $this->db->join('ecommerce.orden_item oi', "o.id = oi.orden_id AND oi.estado = '" . ESTADO_ACTIVO . "'", 'left');
        $this->db->join('ecommerce.orden_item_propiedad oip', "oi.id = oip.orden_item_id AND oip.estado = '" . ESTADO_ACTIVO . "' AND (oip.propiedad_id = 10 OR oip.propiedad_id = 235 OR oip.propiedad_id = 236 OR oip.propiedad_id = 394) ", 'left');
        $this->db->join('ecommerce.orden_caja_item oci', "oi.id = oci.orden_item_id AND oci.estado = '" . ESTADO_ACTIVO . "'", 'left');
        $this->db->join('ecommerce.orden_caja oc', "oc.id = oci.orden_caja_id AND o.id = oc.orden_id AND oc.estado = '" . ESTADO_ACTIVO . "'", 'left');
        $this->db->join('ecommerce.producto_variante pv', "oi.variante_id = pv.id", 'left');
        $this->db->join('ecommerce.producto p', "oi.producto_id = p.id", 'left');
        $this->db->join('ecommerce.finca_caja fc', "fc.orden_caja_id = oc.id AND fc.estado = '" . ESTADO_ACTIVO . "'", 'left');
        

        $this->filtrar($filtro);

        $this->db->where('pv.id', $variante_id);

        //$this->db->group_by(array('oi.variante_id'));
        $this->db->order_by('1', 'ASC');

        return $this->retornarMuchosSinPaginacion();
    }

    public function item_bonchado($orden_item_id) {

        $data['preparado'] = 'S';
        $data['preparacion_fecha'] = fechaActual();
        $data['id'] = $orden_item_id;

        if ($this->actualizar("ecommerce.orden_item", $data, "id", true)) {
            $dato_log['orden_item_id'] = $orden_item_id;
            $dato_log['accion'] = "Item bonchado";

            $this->registrarLog("ecommerce.orden_item_log", $dato_log);
            return true;
        }
        return false;
    }

    public function item_desbonchado($orden_item_id) {

        $data['preparado'] = 'N';
        $data['preparacion_fecha'] = fechaActual();
        $data['id'] = $orden_item_id;

        if ($this->actualizar("ecommerce.orden_item", $data, "id", true)) {
            $dato_log['orden_item_id'] = $orden_item_id;
            $dato_log['accion'] = "Item desbonchado";

            $this->registrarLog("ecommerce.orden_item_log", $dato_log);
            return true;
        }
        return false;
    }

    public function item_vestido($orden_item_id, $preparadoTambien = false) {

        if ($preparadoTambien) {
            $data['preparado'] = 'S';
            $data['preparacion_fecha'] = fechaActual();
        }

        $data['terminado'] = 'S';
        $data['terminado_fecha'] = fechaActual();
        $data['id'] = $orden_item_id;

        if ($this->actualizar("ecommerce.orden_item", $data, "id", true)) {
            $dato_log['orden_item_id'] = $orden_item_id;
            $dato_log['accion'] = "Item vestido";

            $this->registrarLog("ecommerce.orden_item_log", $dato_log);
            return true;
        }
        return false;
    }

    public function item_desvestido($orden_item_id) {

        $data['terminado'] = 'N';
        $data['terminado_fecha'] = fechaActual();
        $data['id'] = $orden_item_id;

        if ($this->actualizar("ecommerce.orden_item", $data, "id", true)) {
            $dato_log['orden_item_id'] = $orden_item_id;
            $dato_log['accion'] = "Item desvestido";

            $this->registrarLog("ecommerce.orden_item_log", $dato_log);
            return true;
        }
        return false;
    }

    public function item_encerado($orden_item_id) {
        $data['preparado'] = 'N';
        $data['terminado'] = 'N';
        $data['id'] = $orden_item_id;

        if ($this->actualizar("ecommerce.orden_item", $data, "id", true)) {
            $dato_log['orden_item_id'] = $orden_item_id;
            $dato_log['accion'] = "Item encerado";

            $this->registrarLog("ecommerce.orden_item_log", $dato_log);
            return true;
        }
        return false;
    }

    public function obtenerListadoItems($filtro, $variante_id) {

        $this->db->select("oi.id as orden_item_id, oi.cantidad, oi.preparado as bonchado, oi.terminado as vestido, "
                . " CASE WHEN (oip.valor IS NULL) OR  UPPER(oip.valor) LIKE '%NO%' OR  UPPER(oip.valor) LIKE '%SIN%' OR  UPPER(oip.valor) LIKE '%WITHOUT%' THEN 2 WHEN UPPER(oip.valor) LIKE '%LUXURY%' THEN 1 ELSE 0 END as tipo_wrap");

        $this->db->from('ecommerce.orden o');
        $this->db->join('ecommerce.store s', "o.store_id = s.id", 'left');
        $this->db->join('ecommerce.orden_item oi', "o.id = oi.orden_id AND oi.estado = '" . ESTADO_ACTIVO . "'", 'left');
        $this->db->join('ecommerce.orden_item_propiedad oip', "oi.id = oip.orden_item_id AND oip.estado = '" . ESTADO_ACTIVO . "' AND (oip.propiedad_id = 10 OR oip.propiedad_id = 235 OR oip.propiedad_id = 236 OR oip.propiedad_id = 394)", 'left');
        //$this->db->join('ecommerce.orden_caja_item oci', "oi.id = oci.orden_item_id AND oci.estado = '" . ESTADO_ACTIVO . "'", 'left');
        //$this->db->join('ecommerce.orden_caja oc', "oc.id = oci.orden_caja_id AND o.id = oc.orden_id AND oc.estado = '" . ESTADO_ACTIVO . "'", 'left');
        //$this->db->join('ecommerce.producto_variante pv', "oi.variante_id = pv.id", 'left');
        $this->db->join('ecommerce.producto p', "oi.producto_id = p.id", 'left');
        $this->db->join('ecommerce.orden_caja oc', "oc.orden_id = o.id AND oc.estado = '" . ESTADO_ACTIVO . "'", 'left');
        $this->db->join('ecommerce.finca_caja fc', "fc.orden_caja_id = oc.id AND fc.estado = '" . ESTADO_ACTIVO . "'", 'left');
        $this->filtrar($filtro);

        $this->db->where('oi.variante_id', $variante_id);

        //$this->db->group_by(array('oi.variante_id'));
        $this->db->order_by('1', 'ASC');

        return $this->retornarMuchosSinPaginacion();
    }

    public function actualizacionMasiva($variante, $filtro, $variante_id, $ingresoB, $ingresoL, $ingresoS, $ingresoN) {

        $ingresoBO = $ingresoB;
        $ingresoLO = $ingresoL;
        $ingresoSO = $ingresoS;
        $ingresoNO = $ingresoN;
        $presentacion = obtenerPresentacion($variante->sku);

        //vamos a buscar todas las ordenes que entren en este rango
        $listado = $this->obtenerListadoItems($filtro, $variante->id);

        error_log(">>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>");
        error_log(print_r($listado, true));
        foreach ($listado as $item) {
//            if ($item->orden_item_id != 16098){ continue;}
            error_log(print_r($item, true));
            error_log(print_r($ingresoB, true));
            error_log(print_r($item->orden_item_id, true));
            if (($item->bonchado == 'N') && (($item->cantidad * ($presentacion[3] / $presentacion[0])) <= $ingresoB)) {
                $this->item_bonchado($item->orden_item_id);
                $ingresoB = $ingresoB - ($item->cantidad * ($presentacion[3] / $presentacion[0]));
            }
            if (($item->bonchado != 'N') && ($item->vestido == 'N')) {
                switch ($item->tipo_wrap) {
                    case 0: //standard
                        if ($ingreso->cantidad <= $ingresoS) {
                            $this->item_vestido_masivo($item->orden_item_id);
                            $ingresoS = $ingresoS - ($item->cantidad * ($presentacion[3] / $presentacion[0]));
                        }
                        break;
                    case 1: //luxury
                        if ($ingreso->cantidad <= $ingresoL) {
                            $this->item_vestido_masivo($item->orden_item_id);
                            $ingresoL = $ingresoL - ($item->cantidad * ($presentacion[3] / $presentacion[0]));
                        }
                        break;
                    case 2: //No wrap
                        if ($ingreso->cantidad <= $ingresoN) {
                            $this->item_vestido_masivo($item->orden_item_id);
                            $ingresoN = $ingresoN - ($item->cantidad * ($presentacion[3] / $presentacion[0]));
                        }
                        break;
                }
            }
        }

        if (($ingresoBO == $ingresoB) || ($ingresoLO == $ingresoL) || ($ingresoSO == $ingresoS) || ($ingresoNO == $ingresoN)) {
            return array($listado , array("ib" => $ingresoBO - $ingresoB, "il" => $ingresoLO - $ingresoL, "is" => $ingresoSO - $ingresoS, "in" => $ingresoNO - $ingresoN,));
        }
        return array(true, array());
    }
/************************Impresion de tarjetas  */
    public function generar_tarjetas_orden($id, $caja_id = false, $tipo = "normal", $session_finca = false, $finca_id = false,$orden_item) {
        $html = $this->obtener_html_tarjetas($id, $caja_id, $tipo,'T','T', $session_finca, $finca_id,$orden_item);
        if (!$html) {
            return false;
        }
        if ($tipo == TARJETA_NORMALES) {
            $page_format = array(
                'MediaBox' => FORMATO_10x15,
                'Dur' => 3,
                'trans' => array(
                    'D' => 1.5,
                    'S' => 'Split',
                    'Dm' => 'V',
                    'M' => 'O'
                ),
                'Rotate' => FORMATO_10x15_ROTACION,
                'PZ' => 1,
            );
            $pdf = $this->service_general->pdf_generacion($html, $page_format);
        } else if ($tipo == TARJETA_ETERNIZADAS) {
            $page_format = array(
                'MediaBox' => FORMATO_10x15,
                'Dur' => 3,
                'trans' => array(
                    'D' => 1.5,
                    'S' => 'Split',
                    'Dm' => 'V',
                    'M' => 'O'
                ),
                'Rotate' => FORMATO_10x15_ROTACION_ETERNIZADAS,
                'PZ' => 1,
            );
            $pdf = $this->service_general->pdf_generacion_eternizadas($html, $page_format);
        }

        return $pdf;
    }
    public function obtener_html_tarjetas($id, $orden_caja_id = false, $tipo = TARJETA_NORMALES, $empaque_filtro = 'T', $kardex_filtro = 'T', $session_finca = false, $finca_id = false,$orden_item) {
        error_log("obtener_html_tarjetas_" . $id);
        $orden = $this->service_ecommerce_orden->existeOrden(array('id' => $id));
        $this->load->model("Generales/service_general");
        $data['orden'] = $orden;
        if (isset($session_finca) && !empty($session_finca))
        {
            $cajas = $this->service_ecommerce_logistica->obtenerOrdenCajas($orden->id, ESTADO_ACTIVO, $orden_caja_id, $session_finca, $finca_id);
        }else
        {
            $cajas = $this->service_ecommerce_logistica->obtenerOrdenCajas($orden->id, ESTADO_ACTIVO, $orden_caja_id);
        }

        if (!$cajas) {
            error_log("No hay cajas");
            return false;
        }

        $arr_contenido = array();
        $num_cajas_total = $num_cajas = 0;
        $arr_items = array();
        foreach ($cajas as $caja) {
            $arr = array();
            $itemsCaja = $this->service_ecommerce_logistica->obtenerOrdenCajaItems($caja->id);
            if ($itemsCaja) {
                $num_cajas_total++;
                if ($empaque_filtro != 'T') {
                    if ($caja->empacada !== $empaque_filtro) {
                        continue;
                    }
                }
                if ($kardex_filtro != 'T') {
                    if ($caja->kardex_check !== $kardex_filtro) {
                        continue;
                    }
                }
                $florero = 0;
                $petalos = 0;
                foreach ($itemsCaja as $item) {
                   
                    $arr['items'][] = $item;
//                    $propiedad_mensaje = $this->service_ecommerce->existeOrdenItemPropiedad($item->id, 12);
//                    if ($propiedad_mensaje) {
//                        $arr['mensaje'] = $propiedad_mensaje->valor;
//                    }
                    $arr['cantidad'] = $item->cantidad;
                    $arr['titulo'] = $item->info_producto_titulo;
                    $arr['precio'] = $item->precio;
                }
               
                $arr['orden_caja'] = $orden->tienda_alias . "-" . (!empty($orden->referencia_order_number) ? $orden->referencia_order_number : $orden->id) . "-" . $caja->id . "-" .$item->id. " P#" . ($orden->impresiones + 1) . "";
                $arr['orden_identificador'] = $orden->tienda_alias . " " . (!empty($orden->referencia_order_number) ? $orden->referencia_order_number : $orden->id) . " BOX #" . $caja->id . " - P#" . ($orden->impresiones + 1) . "";
                $arr['orden_caja_id'] = $caja->id;
                $arr['orden_caja_tipo'] = $caja->info_nombre_caja;

                $arr['footer_left'] = $orden->tienda_alias . "" . (!empty($orden->referencia_order_number) ? $orden->referencia_order_number : $orden->id);
                $arr['footer_left'] .= "&nbsp;" . $caja->info_abreviado_caja . ($florero > 0 ? "&nbsp;-&nbsp;Fx" . $florero : "") . ($petalos > 0 ? "&nbsp;-&nbsp;" . "Px" . $petalos : "");

                $arr['footer_right'] = "BOX #" . $caja->id . " - P#" . ($orden->impresiones + 1);
                $arr_items[] = $arr;
            }
            $arr_contenido[] = $arr_items;
        }
        $html = array();

        $num_caja_actual = 0;

        foreach ($arr_items as $item) {//una pagina por cada mensaje
            error_log(print_r($item, true));

            $num_caja_actual++;
            $arr = array();
            
            if ($tipo == TARJETA_NORMALES) {
                $blanco = "&nbsp;";
                $footer = $item['footer_left'];
                while (strlen($footer . $blanco . $item['footer_right']) < 600) {
                    $footer .= $blanco;
                }
                $footer .= $item['footer_right'];
            } else if ($tipo == TARJETA_ETERNIZADAS) {
                $footer = 'Rosaholics.com';
                $blanco = "&nbsp;";
                while (strlen($footer . $blanco) < 80) {
                    $footer = $blanco . $footer;
                }
                while (strlen($footer . $blanco . $item['orden_identificador']) < 285) {
                    $footer .= $blanco;
                }
                $footer .= $item['orden_identificador'];
            }
            $arr['header'] = false;
            $arr['cantidad'] = $item['cantidad'];
            $arr['titulo'] = $item['titulo'];
            $arr['precio'] = $item['precio'];
            $arr['orden_caja'] = $item['orden_caja'];
            $arr['orden_caja_id'] = $item['orden_caja_id'];
            $arr['num_caja_actual'] = $num_caja_actual;
            $arr['total_cajas'] = $num_cajas_total;
            $arr['componentes'] = 0;

            error_log("Tipo es " . $tipo);
            if ($tipo == TARJETA_NORMALES) {
                if (array_key_exists('mensaje', $item) && (strlen($item['mensaje']) > 0)) {
                    $data['mensaje'] = '<p style="line-height: 120%;letter-spacing: 0.8pt;">'.$item['mensaje'].'</p>';
                    $data['imagen_firma'] = false;
                    $arr['footer'] = '<div style="font-size: 0.4em; padding: 0; margin-top: -50px;"><hr>' . $footer . '</div>';
                    error_log("Normal ");
                    $arr['mensaje'] = $this->load->view('detalle_tarjeta.php', $data, true);
                    $html[] = $arr;
                } else {
                    // $data['mensaje'] = 'On regards of all 500 people in the Rosaholics family, thanks for trusting us and we hope you enjoy your flowers.';
                     
                      $data['mensaje'] .= '<p style = "font-family:times new roman;font-size:8px; line-height:120%; text-align: justify; margin-top:-2000px; ">Cantidad: '.$item['cantidad'] .'</p>';
                      $data['mensaje'] .= '<p style = "font-family:times new roman;font-size:8px; line-height:120%; text-align: justify; margin-top:-2000px; ">Wrap: '.$orden_item["wrap"] .' Valor: '.$orden_item["wrap-valor"].'</p>';
                      $data['mensaje'] .= '<p style = "font-family:times new roman;font-size:8px; line-height:120%; text-align: justify; margin-top:-2000px; ">Producto: '.$item['titulo'].'    SKU: '.$item['items'][0]->info_variante_sku.'</p>';
                      $data['mensaje'] .= '<p style = "font-family:times new roman;font-size:8px; line-height:120%; text-align: justify; margin-top:-2000px; ">Precio: '.$item['precio'].'</p>';
                      $data['mensaje'] .= '<p style = "font-family:times new roman;font-size:8px; line-height:120%; text-align: justify; margin-top:-2000px; ">VARIANTES</p>';
                      $data['mensaje'] .= '<p style = "font-family:times new roman;font-size:8px; line-height:120%; text-align: justify; margin-top:-2000px; ">Cantidad: '.$orden_item["variante"]->cantidad .'</p>';
                      $data['mensaje'] .= '<p style = "font-family:times new roman;font-size:8px; line-height:120%; text-align: justify; margin-top:-2000px; ">Producto: '.$item['titulo'].'    SKU: '.$item['items'][0]->info_variante_sku.'</p>';
                      //$data['mensaje'] .= '<p style = "font-family:times new roman;font-size:8px; line-height:120%; text-align: justify; margin-top:-2000px; ">Producto-variante: '.$orden_item["variante"]->titulo.' Sku: '.$orden_item["variante"]->sku.'</p>';
                    //  $data['mensaje'] .= '<p style = "font-family:times new roman;font-size:8px; line-height:120%; text-align: justify; margin-top:-2000px; ">Unidad: '.$orden_item["variante"]->unidad.'</p>';
                      $data['imagen_firma'] = false;
                      $arr['footer'] = '<div style="font-size: 0.4em; padding: 0; margin-top: -50px;"><hr>' . $footer . '</div>';
                      $arr['mensaje'] = $this->load->view('detalle_tarjeta.php', $data, true);
                     // $html[] = $arr;
                      $orden_item["wrap-valor"];
                    // $data['mensaje'] = 'On regards of all 500 people in the Rosaholics family, thanks for trusting us and we hope you enjoy your flowers.';
                    // $data['imagen_firma'] = true;
                }
            }

//            $arr['mensaje'] = $this->load->view('orden_tarjeta_mensaje.php', $data, true);


            $data = array();
            $data['items'] = $item['items'];
            if ($tipo == TARJETA_NORMALES) {
                $arr['mensaje'] = $this->load->view('detalle_tarjeta.php', $data, true);
            }
            $arr['componentes'] = 1;
            $html[] = $arr;
        }
        return $html;
    }

}
