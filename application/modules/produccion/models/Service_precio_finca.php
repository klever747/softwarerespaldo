<?php

class Service_precio_finca extends My_Model {

    public function obtenerNuevoPrecioFinca() {
        return (object) [
                    'ingrediente_id' => '',
                    'finca_id' => '',
                    'precio_unitario' => '',
                    'fecha_inicio_vigencia' => '',
                    'fecha_fin_vigencia' => '',
                    'estado' => ESTADO_ACTIVO
        ];
    }

    public function obtenerPrecioFinca($id = false, $texto_busqueda = false, $longitud_buscar = false) {
        $this->db->select("ipf.id ,i.longitud,i.nombre || ' - ' || i.descripcion  as nombre_ingrediente ,f.nombre  as nombre_finca, ipf.precio_unitario, ipf.fecha_inicio_vigencia, ipf.fecha_fin_vigencia, ipf.estado");
        $this->db->from('produccion.ingrediente_precio_finca ipf');
        $this->db->join('produccion.ingrediente i', 'ipf.ingrediente_id = i.id', 'left');
        $this->db->join('general.finca f', 'ipf.finca_id = f.id ', 'left');
        $this->db->where('i.estado', ESTADO_ACTIVO);
        //$this->db->where('ipf.estado', ESTADO_ACTIVO);
        $this->db->where('f.estado', ESTADO_ACTIVO);
        if ($id) {
            $this->db->where('f.id', $id);
            //return $this->retornarUno();
        }

        if ($texto_busqueda) {
            $this->db->where(" (UPPER(i.descripcion) LIKE '%" . strtoupper($texto_busqueda) . "%' "
                    . "OR UPPER(i.nombre) LIKE '%" . strtoupper($texto_busqueda) . "%' )");
        }
        if ($longitud_buscar) {
            $this->db->where('i.longitud', $longitud_buscar);
        }
        $this->db->order_by('nombre_ingrediente', 'ASC');
        $this->db->order_by('i.longitud', 'ASC');
        $this->db->order_by('ipf.fecha_inicio_vigencia', 'ASC');
        $conteo = $this->retornarConteo();
        $arr = $this->retornarMuchosConPaginacion();
        return array($arr, $conteo);
    }

    public function obtenerListaIngredientes($id = false) {
        $this->db->select("i.id , i.nombre || ' | ' || i.descripcion || ' ' || i.longitud as descripcion, i.tipo");
        $this->db->from('produccion.ingrediente i');
        $this->db->where('i.estado', ESTADO_ACTIVO);
        if ($id) {
            $this->db->join('produccion.ingrediente_precio_finca ipf', 'i.id = ipf.ingrediente_id', 'left');
            $this->db->where('ipf.id', $id);
            $arrDatos[0] = $this->retornarUno();
            return $this->retornarSel($arrDatos, "descripcion", true);
        } else {
            $this->db->order_by('2 ASC');
            $arrDatos = $this->retornarMuchosSinPaginacion();
            return $this->retornarSel($arrDatos, "descripcion", false);
        }
    }

    public function verificarFechaFincaPrecio($ingrediente_id, $fecha_inicio_vigencia, $fecha_fin_vigencia, $finca_id, $ingrediente_precio_finca_id_excluir = false) {
        $this->db->select('ipf.id, ipf.fecha_inicio_vigencia, ipf.fecha_fin_vigencia');
        $this->db->from('produccion.ingrediente_precio_finca ipf');
        $this->db->where('ipf.estado', ESTADO_ACTIVO);
        $this->db->where('ipf.ingrediente_id', $ingrediente_id);
        $this->db->where('ipf.finca_id', $finca_id);
        $this->db->where("('$fecha_inicio_vigencia' BETWEEN ipf.fecha_inicio_vigencia and ipf.fecha_fin_vigencia OR '$fecha_fin_vigencia' BETWEEN ipf.fecha_inicio_vigencia and ipf.fecha_fin_vigencia)");
        if ($ingrediente_precio_finca_id_excluir) {
            $this->db->where("ipf.id <> " . $ingrediente_precio_finca_id_excluir);
        }
        return $this->retornarMuchosSinPaginacion();
    }

    public function guardarFechaFincaPrecio($obj) {
        $id = $this->ingresar("produccion.ingrediente_precio_finca", $obj, true, false);
        if ($id) {
            $dato_log = array(
                "ingrediente_precio_finca_id" => $id,
                "accion" => "creacion de un nuevo precio para ingrediente" . json_encode($obj),
            );
            $this->registrarLog("produccion.ingrediente_precio_finca_log", $dato_log);
        }
        return $id;
    }

    public function obtenerIngredientePrecio($id) {
        $this->db->select('ipf.id,i.nombre,i.descripcion, ipf.finca_id, ipf.fecha_inicio_vigencia,ipf.fecha_fin_vigencia, ipf.precio_unitario,ipf.estado, i.longitud,i.id as ingrediente_id');
        $this->db->from('produccion.ingrediente_precio_finca ipf');
        $this->db->join('produccion.ingrediente i', 'ipf.ingrediente_id = i.id', 'left');
        $this->db->where('i.estado', ESTADO_ACTIVO);
        // $this->db->where('ipf.estado', ESTADO_ACTIVO);
        $this->db->where('ipf.id', $id);
        $arrDatos = $this->retornarUno();
        return $arrDatos;
    }

    public function buscarIngredientePrecio($id) {
        $this->db->select('ipf.*');
        $this->db->from('produccion.ingrediente_precio_finca ipf');
        //$this->db->where('ipf.estado', ESTADO_ACTIVO);
        $this->db->where('ipf.id', $id);
        return $this->retornarUno();
    }

    public function actualizarFechaFincaPrecio($obj) {
        $id = $this->actualizar("produccion.ingrediente_precio_finca", $obj, "id", false);
        if ($id) {
            $dato_log = array(
                "ingrediente_precio_finca_id" => $obj['id'],
                "accion" => "actualizacion de precio en el ingrediente" . json_encode($obj),
            );
            $this->registrarLog("produccion.ingrediente_precio_finca_log", $dato_log);
        }
        return $id;
    }

}

?>