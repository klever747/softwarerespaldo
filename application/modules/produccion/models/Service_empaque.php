<?php

class Service_empaque extends My_Model {

    public function obtenerCaja($caja_id) {
        $this->db->select('oc.orden_id, oc.id, oc.empacada, tc.id as tipo_caja_id, tc.nombre as info_nombre_caja, oc.kardex_check');
        $this->db->from('ecommerce.orden_caja oc');
        $this->db->join('ecommerce.tipo_caja tc', 'oc.tipo_caja_id = tc.id', 'left');
        $this->db->where('oc.id', $caja_id);
        return $this->retornarUno();
    }

    public function obtenerCajasPorIdOrden($orden_id) {
        $this->db->select('oc.orden_id, oc.id, oc.empacada, tc.id as tipo_caja_id, tc.nombre as info_nombre_caja, oc.kardex_check, f.nombre as info_nombre_finca, octn.tracking_number as info_tracking_number');
        $this->db->from('ecommerce.orden_caja oc');
        $this->db->join('ecommerce.tipo_caja tc', 'oc.tipo_caja_id = tc.id', 'left');
        $this->db->join('ecommerce.finca_caja fc', 'oc.id = fc.orden_caja_id', 'left');
        $this->db->join('general.finca f', 'f.id = fc.finca_id', 'left');
        $this->db->join('logistica.orden_caja_tracking_number octn', "octn.orden_caja_id = oc.id AND octn.estado = '" . ESTADO_ACTIVO . "' ", 'left');
        $this->db->where('oc.orden_id', $orden_id);
        $this->db->where('oc.estado', ESTADO_ACTIVO);
        return $this->retornarMuchosSinPaginacion();
    }

    public function ordenCajaKardexCheck($orden_caja_id, $tipo_caja_id = 0) {

        $data['id'] = $orden_caja_id;
        $caja = $this->obtenerCaja($orden_caja_id);
        if ($tipo_caja_id != 0) {
            if ($caja->tipo_caja_id != $tipo_caja_id) {
                $data['tipo_caja_id'] = $tipo_caja_id;
                $dato_log = array(
                    "orden_caja_id" => $data['id'],
                    "accion" => "actualizamos caja" . json_encode($data),
                );
                $this->registrarLog("ecommerce.orden_caja_log", $dato_log);
            }
        }

        if ($caja->empacada != 'S') {
            $this->ordenCajaEmpacada($orden_caja_id, true);
        }
        $data['kardex_check'] = 'S';
        $data['kardex_fecha'] = fechaActual();
        $actualizacion = $this->actualizar("ecommerce.orden_caja", $data, "id", true);
        $dato_log = array(
            "orden_caja_id" => $data['id'],
            "accion" => "caja en kardex " . json_encode($data),
        );
        $this->registrarLog("ecommerce.orden_caja_log", $dato_log);
        return $actualizacion;
    }

    public function ordenCajaEmpacada($orden_caja_id, $empaqueKardex = false) {
        $data['empacada'] = 'S';
        $data['empaque_fecha'] = fechaActual();
        $data['id'] = $orden_caja_id;

        $actualizacion = $this->actualizar("ecommerce.orden_caja", $data, "id", true);
        $dato_log = array(
            "orden_caja_id" => $data['id'],
            "accion" => "caja empacada " . ($empaqueKardex ? "automaticamente por kardex" : "manualmente") . ": " . json_encode($data),
        );
        $this->registrarLog("ecommerce.orden_caja_log", $dato_log);
        return $actualizacion;
    }

    public function ordenCajaNoEmpacada($orden_caja_id) {
        $data['empacada'] = 'N';
        $data['empaque_fecha'] = fechaActual();
        $data['id'] = $orden_caja_id;

        $actualizacion = $this->actualizar("ecommerce.orden_caja", $data, "id", true);
        $dato_log = array(
            "orden_caja_id" => $data['id'],
            "accion" => "caja desempacada" . json_encode($data),
        );
        $this->registrarLog("ecommerce.orden_caja_log", $dato_log);
        return $actualizacion;
    }

    public function obtenerCajas($store_id, $rango_busqueda, $tipo_calendario, $tipo_caja, $orden_caja_id = false) {

        $this->db->select('
            o.id as orden_id,            
            o.store_id,
            o.referencia_order_number,
            octn.tracking_number,
            s.alias as store_alias,
            oc.id as orden_caja_id, 
            oc.empaque_fecha,
            oc.kardex_check,
            oc.kardex_fecha,
            o.fecha_carguera,            
            o.fecha_entrega,
            tc.nombre as caja_nombre,
            tc.grupo as grupo,
            tc.length,
            tc.width,
            tc.height,
            tc.weight');
        $this->db->from('ecommerce.orden_caja oc');
        $this->db->join('ecommerce.orden o', "o.id = oc.orden_id", 'left');
        $this->db->join('ecommerce.store s', "o.store_id = s.id", 'left');
        $this->db->join('ecommerce.tipo_caja tc', 'tc.id = oc.tipo_caja_id', 'left');
        $this->db->join('logistica.orden_caja_tracking_number octn', "octn.orden_caja_id = oc.id AND octn.estado = '" . ESTADO_ACTIVO . "'", 'left');
        $this->db->where('oc.estado', ESTADO_ACTIVO);

        if ($orden_caja_id) {
            $this->db->where('oc.id', $orden_caja_id);
        } else {
            if ($store_id != 0) {
                $this->db->where('o.store_id', $store_id);
            }
            $this->db->where('oc.kardex_check', 'S');
            if ($tipo_caja != 0) {
                $this->db->where('oc.tipo_caja_id', $tipo_caja);
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
                    default:
                        $arrSelect = array('o.fecha_entrega >= ' => $fechaIni, 'o.fecha_entrega <= ' => $fechaFin);
                        break;
                }

                $this->db->where($arrSelect);
            }
        }
        $conteo = $this->retornarConteo();
        $this->db->order_by('oc.kardex_fecha', 'ASC');
        $arr = $this->retornarMuchosSinPaginacion();
        return array($arr, $conteo);
    }

}
