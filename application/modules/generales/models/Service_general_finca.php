<?php

class Service_general_finca extends My_Model {

    public function obtenerNuevoFinca() {
        return (object) [
                    'nombre' => '',
                    'descripcion' => '',
                    'precio' => '',
                    'estado' => ESTADO_ACTIVO,
                    'tipo_finca' => 1
        ];
    }

    public function existeFinca($objFinca) {
        $this->db->select('p');
        $this->db->from('general.finca');
        $this->db->where('nombre', $objFinca['nombre']);
        return $this->retornarUno();
    }

    public function obtenerFinca($id = false, $estado = false, $texto_busqueda = false) {
        $this->db->select('*');
        $this->db->from('general.finca');
        if ($id) {
            $this->db->where('id', $id);
            return $this->retornarUno();
        }
        if ($estado) {
            $this->db->where('estado', $estado);
        }
        if ($texto_busqueda) {
            $this->db->where(" (UPPER(nombre) LIKE '%" . strtoupper($texto_busqueda) . "%' "
                    . "OR UPPER(descripcion) LIKE '%" . strtoupper($texto_busqueda) . "%')");
        }
        $this->db->order_by('nombre', 'ASC');
        $conteo = $this->retornarConteo();
        $arr = $this->retornarMuchos();
        return array($arr, $conteo);
    }

    public function obtenerFincas($estado = false) {
        $this->db->select('f.*');
        $this->db->from('general.finca f');
        if ($estado) {
            $this->db->where('f.estado', $estado);
        }
        $conteo = $this->retornarConteo();
        $arr = $this->retornarMuchos();

        return array($arr, $conteo);
    }

    public function crearFinca($obj) {
        $id = $this->ingresar("general.finca", $obj, true, true);
        if ($id) {
            $dato_log = array(
                "finca_id" => $id,
                "accion" => "creacion de finca" . json_encode($obj),
            );
            $this->registrarLog("general.finca_log", $dato_log);
        }
        return $id;
    }

    public function actualizarFinca($obj) {
        $id = $this->actualizar("general.finca", $obj, "id");
        if ($id) {
            $dato_log = array(
                "finca_id" => $obj['id'],
                "accion" => "actualizacion de finca" . json_encode($obj),
            );
            $this->registrarLog("general.finca_log", $dato_log);
        }
        return $id;
    }

    public function obtenerSelFinca($sinrsh = false) {
        $finca = $this->session->userFincaId;
        $this->db->select("*");
        $this->db->from('general.finca');
        $this->db->where('estado', ESTADO_ACTIVO);
        if ($sinrsh) { //sin la finca rosaholics
            $this->db->where('id > ' . FINCA_ROSAHOLICS_ID);
        }
        $arrayfinca = explode(",", $finca);
        if (!in_array(FINCA_ROSAHOLICS_ID,$arrayfinca)){
            if(count($arrayfinca) > 1){
             $srt = "id in (".$finca.")";
           $this->db->where($srt);  
            }
        }
        
        $arrDatos = $this->retornarMuchosSinPaginacion();
        return $this->retornarSel($arrDatos, "nombre");
    }

    public function obtenerFincaConPrecios($finca_id = false, $obt_precio = false) {
        if ($obt_precio) {
            $this->db->select("ipf.ingrediente_id,f.id, f.nombre,i.nombre,i.longitud,ipf.precio_unitario,ipf.ingrediente_id,ipf.fecha_inicio_vigencia,ipf.fecha_fin_vigencia");
            $this->db->from('produccion.ingrediente_precio_finca ipf');
            $this->db->join('general.finca f', 'ipf.finca_id = f.id', 'left');
            $this->db->join('produccion.ingrediente i', 'ipf.ingrediente_id = i.id', 'left');
        } else {
            $this->db->select("count(f.nombre), f.id ,f.nombre");
            $this->db->from('produccion.ingrediente_precio_finca ipf');
            $this->db->join('general.finca f', 'ipf.finca_id = f.id', 'left');
        }

        $this->db->where('ipf.precio_unitario IS NOT NULL');
        $this->db->where('ipf.estado', ESTADO_ACTIVO);
        $this->db->where('f.estado', ESTADO_ACTIVO);

        if (($finca_id) && (!$obt_precio)) {
            $this->db->where('f.id', $finca_id);
            $this->db->group_by('f.id,f.nombre');
            $arrDatos[0] = $this->retornarUno();
            return $this->retornarSel($arrDatos, "nombre", true);
        } elseif (($finca_id) && ($obt_precio)) {
            $this->db->where('ipf.finca_id', $finca_id);
            $this->db->order_by('i.nombre', 'DESC');
            $this->db->order_by('i.longitud', 'DESC');
            $this->db->order_by('ipf.fecha_inicio_vigencia', 'DESC');
            $arrDatos = $this->retornarMuchosSinPaginacion(true);
            return $arrDatos;
        } else {
            $this->db->group_by('f.id,f.nombre');
            $arrDatos = $this->retornarMuchosSinPaginacion();
            return $this->retornarSel($arrDatos, "nombre");
        }
    }

    public function clonacionDePrecios($arr_precios, $finca_id_creado) {
        $fecha_inicio_vigencia = date("Y-m-d h:i:s");
        $fecha_fin_vigencia = date("Y-m-d h:i:s", strtotime($fecha_inicio_vigencia . '+ 1 year'));
        foreach ($arr_precios as $k => $precios) {
            $resp = $this->buscarIngPrecioRepetido($precios->ingrediente_id, $finca_id_creado, $fecha_inicio_vigencia, $fecha_fin_vigencia);
            if (!$resp) {
                $arrayIngreso = array("ingrediente_id" => $precios->ingrediente_id,
                    "finca_id" => $finca_id_creado,
                    "precio_unitario" => $precios->precio_unitario,
                    "fecha_inicio_vigencia" => $fecha_inicio_vigencia,
                    "fecha_fin_vigencia" => $fecha_fin_vigencia,
                    "estado" => ESTADO_ACTIVO);
                $res = $this->ingresarIngrediente($arrayIngreso);
            }
        }
    }

    public function buscarIngPrecioRepetido($ingrediente_id, $finca_id_creado, $fecha_inicio_vigencia, $fecha_fin_vigencia) {
        $this->db->select('ipf.ingrediente_id');
        $this->db->from('produccion.ingrediente_precio_finca ipf');
        $this->db->where('ipf.ingrediente_id', $ingrediente_id);
        $this->db->where('ipf.finca_id', $finca_id_creado);
        return $this->retornarUno(true);
    }

    public function ingresarIngrediente($obj) {
        error_log("Vamos a ingresar un ingrediente con su precio");
        $id = $this->ingresar("produccion.ingrediente_precio_finca", $obj, true, true);
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
