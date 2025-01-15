<?php

class Service_componentes extends My_Model {

    public function obtenerColoresIngredientes() {
        $this->db->select("
            DISTINCT(color)");
        $this->db->from('produccion.ingrediente i');

        return $this->retornarMuchosSinPaginacion();
    }

    public function listado($store_id, $rango_busqueda, $tipo_calendario, $tipo = false, $filtro = false) {

        $this->db->select('
            i.nombre as ingrediente_nombre,
            i.descripcion as ingrediente_descripcion,
            i.tipo,
            r.sku,
            pv.largo_cm as variante_largo,
            SUM(oi.cantidad * r.cantidad) as sum');
        $this->db->from('ecommerce.orden o');
        $this->db->join('ecommerce.orden_item oi', 'o.id = oi.orden_id', 'left');
        $this->db->join('ecommerce.producto_variante pv', 'oi.variante_id = pv.id', 'left');
        $this->db->join('produccion.receta r', 'pv.sku = r.sku', 'left');
        $this->db->join('produccion.ingrediente i', 'r.ingrediente_id = i.id', 'left');
        $this->db->join('ecommerce.orden_caja oc', "oc.orden_id = o.id AND oc.estado = '" . ESTADO_ACTIVO . "'", 'left');
        $this->db->join('ecommerce.finca_caja fc', "fc.orden_caja_id = oc.id AND fc.estado = '" . ESTADO_ACTIVO . "'", 'left');
        if ($store_id != 0) {
            $this->db->where('o.store_id', $store_id);
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

        if (!empty($rango_busqueda)) {
            //rango_busqueda espera "dd/mm/YYYY - dd/mm/YYYY"
            $arrRango = explode(" - ", $rango_busqueda);
            $fechaIni = date_format(DateTime::createFromFormat(FORMATO_FECHA, convertirFechaBD($arrRango[0])), 'Y-m-d 00:00:00');
            $fechaFin = date_format(DateTime::createFromFormat(FORMATO_FECHA, convertirFechaBD($arrRango[1])), 'Y-m-d 23:59:59');
            if (sizeof($arrRango) != 2) {
                //siempre debe de ser 2, si no es un error y vamos a devolver un error
                return array(false, -1);
            }
            switch ($tipo_calendario) {
                case 0://carguera
                    $arrSelect = array('o.fecha_carguera >= ' => $fechaIni, 'o.fecha_carguera <= ' => $fechaFin);
                    break;
                case 1://entrega
                    $arrSelect = array('o.fecha_entrega >= ' => $fechaIni, 'o.fecha_entrega <= ' => $fechaFin);
                    break;
                case 2://actualizacion
                    $arrSelect = array('o.fecha_entrega >= ' => $fechaIni, 'o.fecha_entrega <= ' => $fechaFin);
                    break;
                case 3:// sin fecha
                    $arrSelect = 'o.fecha_entrega IS NULL';
                    break;
                default:
                    $arrSelect = array('o.fecha_entrega >= ' => $fechaIni, 'o.fecha_entrega <= ' => $fechaFin);
                    break;
            }

            $this->db->where($arrSelect);
        }

        $this->db->where('o.estado', ESTADO_ACTIVO);
        $this->db->where('oi.estado', ESTADO_ACTIVO);
        $this->db->where('r.estado', ESTADO_ACTIVO);
        if ($tipo) {
            $this->db->where('i.tipo', $tipo);
        }
        $this->db->where("i.tipo NOT LIKE 'A%'");
        $this->db->group_by(array('r.ingrediente_id', 'i.nombre', 'i.descripcion', 'i.tipo', 'r.sku', 'pv.largo_cm'));
        $this->db->order_by('i.nombre', 'DESC');
        $arr = $this->retornarMuchosSinPaginacion();
        //error_log(print_r($this->db->last_query(), true));
        return $arr;
    }

    public function listadoPropiedadesStems($store_id, $rango_busqueda, $tipo_calendario, $tipo = false, $filtro = false) {
        $this->db->select('
            i.nombre as ingrediente_nombre,
            i.descripcion as ingrediente_descripcion,
            i.tipo,
            r.sku,
            SUM(r.cantidad * CAST(oip.valor as FLOAT)) as sum');
        $this->db->from('ecommerce.orden o');
        $this->db->join('ecommerce.orden_item oi', "o.id = oi.orden_id AND oi.estado = 'A'", 'left');
        $this->db->join('ecommerce.orden_item_propiedad oip', "oip.orden_item_id = oi.id AND oip.estado = 'A'", 'left');
        $this->db->join('ecommerce.propiedad p', 'oip.propiedad_id = p.id', 'left');
        $this->db->join('produccion.receta r', 'p.nombre = r.sku', 'left');
        $this->db->join('produccion.ingrediente i', 'r.ingrediente_id = i.id', 'left');
        $this->db->join('ecommerce.orden_caja oc', "oc.orden_id = o.id AND oc.estado = '" . ESTADO_ACTIVO . "'", 'left');
        $this->db->join('ecommerce.finca_caja fc', "fc.orden_caja_id = oc.id AND fc.estado = '" . ESTADO_ACTIVO . "'", 'left');

        if ($store_id != 0) {
            $this->db->where('o.store_id', $store_id);
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
        if (!empty($rango_busqueda)) {
            //rango_busqueda espera "dd/mm/YYYY - dd/mm/YYYY"
            $arrRango = explode(" - ", $rango_busqueda);
            $fechaIni = date_format(DateTime::createFromFormat(FORMATO_FECHA, convertirFechaBD($arrRango[0])), 'Y-m-d 00:00:00');
            $fechaFin = date_format(DateTime::createFromFormat(FORMATO_FECHA, convertirFechaBD($arrRango[1])), 'Y-m-d 23:59:59');
            if (sizeof($arrRango) != 2) {
                //siempre debe de ser 2, si no es un error y vamos a devolver un error
                return array(false, -1);
            }
            switch ($tipo_calendario) {
                case 0://carguera
                    $arrSelect = array('o.fecha_carguera >= ' => $fechaIni, 'o.fecha_carguera <= ' => $fechaFin);
                    break;
                case 1://entrega
                    $arrSelect = array('o.fecha_entrega >= ' => $fechaIni, 'o.fecha_entrega <= ' => $fechaFin);
                    break;
                case 2://actualizacion
                    $arrSelect = array('o.fecha_entrega >= ' => $fechaIni, 'o.fecha_entrega <= ' => $fechaFin);
                    break;
                case 3:// sin fecha
                    $arrSelect = 'o.fecha_entrega IS NULL';
                    break;
                default:
                    $arrSelect = array('o.fecha_entrega >= ' => $fechaIni, 'o.fecha_entrega <= ' => $fechaFin);
                    break;
            }

            $this->db->where($arrSelect);
        }

        $this->db->where('o.estado', ESTADO_ACTIVO);
        $this->db->where('oi.estado', ESTADO_ACTIVO);
        $this->db->where('oip.estado', ESTADO_ACTIVO);
        $this->db->where('r.estado', ESTADO_ACTIVO);
        $this->db->where("p.nombre LIKE 'AGR_C" . ($tipo == 'T' ? 'T' : ($tipo == 'N' ?: '_')) . "%' ");

        $this->db->group_by(array('i.nombre', 'i.descripcion', 'i.tipo', 'r.sku'));
        $this->db->order_by('i.nombre', 'DESC');
        $arr = $this->retornarMuchosSinPaginacion();
        //error_log(print_r($this->db->last_query(), true));
        return $arr;
    }

    public function listadoAccesorios($store_id, $rango_busqueda, $tipo_calendario, $filtro = false) {

        $this->db->select('p.id, p.descripcion, oip.valor, sum(1)');
        $this->db->from('ecommerce.orden o');
        $this->db->join('ecommerce.orden_item oi', "o.id = oi.orden_id AND oi.estado = 'A'", 'left');
        $this->db->join('ecommerce.orden_item_propiedad oip', "oip.orden_item_id = oi.id AND oip.estado = 'A'", 'left');
        $this->db->join('ecommerce.propiedad p', 'oip.propiedad_id = p.id', 'left');
        $this->db->join('ecommerce.orden_caja oc', "oc.orden_id = o.id AND oc.estado = '" . ESTADO_ACTIVO . "'", 'left');
        $this->db->join('ecommerce.finca_caja fc', "fc.orden_caja_id = oc.id AND fc.estado = '" . ESTADO_ACTIVO . "'", 'left');
        if ($store_id != 0) {
            $this->db->where('o.store_id', $store_id);
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

        if (!empty($rango_busqueda)) {
            //rango_busqueda espera "dd/mm/YYYY - dd/mm/YYYY"
            $arrRango = explode(" - ", $rango_busqueda);
            $fechaIni = date_format(DateTime::createFromFormat(FORMATO_FECHA, convertirFechaBD($arrRango[0])), 'Y-m-d 00:00:00');
            $fechaFin = date_format(DateTime::createFromFormat(FORMATO_FECHA, convertirFechaBD($arrRango[1])), 'Y-m-d 23:59:59');
            if (sizeof($arrRango) != 2) {
                //siempre debe de ser 2, si no es un error y vamos a devolver un error
                return array(false, -1);
            }
            switch ($tipo_calendario) {
                case 0://carguera
                    $arrSelect = array('o.fecha_carguera >= ' => $fechaIni, 'o.fecha_carguera <= ' => $fechaFin);
                    break;
                case 1://entrega
                    $arrSelect = array('o.fecha_entrega >= ' => $fechaIni, 'o.fecha_entrega <= ' => $fechaFin);
                    break;
                case 2://actualizacion
                    $arrSelect = array('o.fecha_entrega >= ' => $fechaIni, 'o.fecha_entrega <= ' => $fechaFin);
                    break;
                case 3:// sin fecha
                    $arrSelect = 'o.fecha_entrega IS NULL';
                    break;
                default:
                    $arrSelect = array('o.fecha_entrega >= ' => $fechaIni, 'o.fecha_entrega <= ' => $fechaFin);
                    break;
            }

            $this->db->where($arrSelect);
        }

        $this->db->where('o.estado', ESTADO_ACTIVO);
        $this->db->where('p.id IN (18,372,373,10,235,11,371,398,394)');

        $this->db->group_by(array('p.id', 'p.descripcion', 'oip.valor'));
        $this->db->order_by('p.descripcion', 'ASC');
        $this->db->order_by('oip.valor', 'ASC');
        $arr = $this->retornarMuchosSinPaginacion();
        return $arr;
    }

}
