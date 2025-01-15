<?php

class Service_master_shipping extends My_Model {

    public function obtenerNuevoMasterShipping() {
        return (object) [
                    'nombre_master' => '',
                    'finca_id' => '2',
                    'estado' => ESTADO_ACTIVO,
                    'fecha_carguera' => '',
                    'numero_guia' => '',
        ];
    }

    public function obtenerMasterShipping($id = false, $finca_id, $rango_busqueda, $tipo_calendario, $texto_busqueda) {
        $this->db->select('f.nombre, ms.id, ms.nombre_master, ms.estado, ms.fecha_carguera, ms.numero_guia');
        $this->db->from('logistica.master_shipping ms');
        $this->db->join('general.finca f', 'f.id=ms.finca_id', 'left');
        if ($id) {
            $this->db->where('ms.id', $id);
            return $this->retornarUno();
        }

        if ($finca_id) {
            $this->db->where('ms.finca_id', $finca_id);
        }
        if ($texto_busqueda) {
            $this->db->where(" (UPPER(i.nombre) LIKE '%" . strtoupper($texto_busqueda) . "%' "
                    . "OR UPPER(i.descripcion) LIKE '%" . strtoupper($texto_busqueda) . "%' )");
        }
        if ($rango_busqueda) {
            $this->db->where('ms.fecha_carguera', $rango_busqueda);
        }
        $this->db->order_by('ms.fecha_carguera', 'DESC');
        $this->db->order_by('ms.nombre_master', 'DESC');

        $conteo = $this->retornarConteo();
        $arr = $this->retornarMuchosConPaginacion(true);

        return array($arr, $conteo);
    }

    public function crearMasterShipping($obj) {

        error_log("Vamos a subir un nuevo Master Shipping");
        $id = $this->ingresar("logistica.master_shipping", $obj, true, true);
        if ($id) {
            $dato_log = array(
                "master_shipping_id" => $id,
                "accion" => "creacion de un nuevo master Shipping" . json_encode($obj),
            );
            $this->registrarLog("logistica.master_shipping_log", $dato_log);
        }
        return $id;
    }

    public function eliminarMasterShipping($obj) {
        $id = $this->actualizar("logistica.master_shipping", $obj, "id", true);
        if ($id) {
            $dato_log = array(
                "master_shipping_id" => $obj['id'],
                "accion" => "eliminacion del master Shipping" . json_encode($obj),
            );
            $this->registrarLog("logistica.master_shipping_log", $dato_log);
        }
        return $id;
    }

    /* ------------------------------------Cargar datos para mostrar en las tablas de bonchado vestivo------------------------------ */

    public function obtener_master_shipping_totales($filtro) {
        $arrRango = explode(" - ", $filtro['rango_busqueda']);
        if (sizeof($arrRango) != 2) {
            return array(false, -1);
        }
        $fechaIni = date_format(DateTime::createFromFormat(FORMATO_FECHA, convertirFechaBD($arrRango[0])), 'Y-m-d 00:00:00');
        $fechaFin = date_format(DateTime::createFromFormat(FORMATO_FECHA, convertirFechaBD($arrRango[1])), 'Y-m-d 23:59:59');
        $this->db->select('f.nombre, ms.id, ms.nombre_master, ms.fecha_carguera, ms.numero_guia');
        $this->db->from('logistica.master_shipping ms');
        $this->db->join('general.finca f', 'f.id = ms.finca_id', 'left');
        $this->db->where('ms.estado', ESTADO_ACTIVO);
        $arrSelect = array('ms.fecha_carguera >= ' => $fechaIni, 'ms.fecha_carguera <= ' => $fechaFin);
        $this->db->where($arrSelect);

        
        
        $arrayfinca = explode(",", $filtro['session_finca']);
        if ($filtro && !in_array(FINCA_ROSAHOLICS_ID,$arrayfinca)) {
            $srt = "ms.finca_id in (".$filtro['session_finca'].")";
           $this->db->where($srt);
        }
        if (in_array(FINCA_ROSAHOLICS_ID,$arrayfinca)) {
            if ($filtro['finca_id'] != 0) {
                $this->db->where(' ms.finca_id', $filtro['finca_id']);
            }
        } else {
           $srt = "ms.finca_id in (".$filtro['session_finca'].")";
           $this->db->where($srt);
        }
        $arr = $this->retornarMuchosSinPaginacion();
        return $arr;
    }

}

?>