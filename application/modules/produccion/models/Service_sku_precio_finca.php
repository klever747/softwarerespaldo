<?php

class Service_sku_precio_finca extends My_Model {

    public function obtenerNuevoSkuPrecioFinca() {
        return (object) [
                    'producto_id' => '',
                    'variante_id' => '',
                    'sku' => '',
                    'finca_id' => '',
                    'precio_unitario' => '',
                    'fecha_inicio_vigencia' => '',
                    'fecha_fin_vigencia' => '',
                    'estado' => ESTADO_ACTIVO
        ];
    }

    public function obtenerSkuPrecioFinca($id = false, $texto_busqueda = false) {
        $this->db->select("pv.sku ,spf.id, pr.titulo|| ' - ' || pv.titulo as descripcion_producto, f.nombre as nombre_finca, spf.precio_unitario, spf.fecha_inicio_vigencia, spf.fecha_fin_vigencia, spf.estado");
        $this->db->from('produccion.sku_precio_finca spf');
        $this->db->join('ecommerce.producto_variante pv', 'spf.sku = pv.sku  ', 'left');
        $this->db->join('ecommerce.producto pr', 'pr.id = pv.producto_id ', 'left');
        $this->db->join(' general.finca f', 'spf.finca_id = f.id', 'left');
        $this->db->where('pr.estado', ESTADO_ACTIVO);
        //$this->db->where('ipf.estado', ESTADO_ACTIVO);
        $this->db->where('f.estado', ESTADO_ACTIVO);
        if ($id) {
            $this->db->where('f.id', $id);
            //return $this->retornarUno();
        }

        if ($texto_busqueda) {
            $this->db->where(" (UPPER(pr.descripcion) LIKE '%" . strtoupper($texto_busqueda) . "%' "
                    . "OR UPPER(pr.titulo) LIKE '%" . strtoupper($texto_busqueda) . "%' " 
                    . "OR UPPER(pv.sku) LIKE '%" . strtoupper($texto_busqueda) . "%')");
        }
        $this->db->order_by('descripcion_producto', 'ASC');
        $this->db->order_by('spf.fecha_inicio_vigencia', 'ASC');
        $conteo = $this->retornarConteo();
        $arr = $this->retornarMuchosConPaginacion(true);
        return array($arr, $conteo);
    }

    public function verificarFechaFincaPrecioSku($sku, $fecha_inicio_vigencia, $fecha_fin_vigencia, $finca_id, $sku_precio_finca_id_excluir = false) {
        $this->db->select('spf.id, spf.fecha_inicio_vigencia, spf.fecha_fin_vigencia');
        $this->db->from('produccion.sku_precio_finca spf');
        $this->db->where('spf.estado', ESTADO_ACTIVO);
        $this->db->where('spf.sku', $sku);
        $this->db->where('spf.finca_id', $finca_id);
        $this->db->where("('$fecha_inicio_vigencia' BETWEEN spf.fecha_inicio_vigencia and spf.fecha_fin_vigencia OR '$fecha_fin_vigencia' BETWEEN spf.fecha_inicio_vigencia and spf.fecha_fin_vigencia)");
        if ($sku_precio_finca_id_excluir) {
            $this->db->where("spf.id <> " . $sku_precio_finca_id_excluir);
        }
        return $this->retornarMuchosSinPaginacion();
    }

    public function guardarFechaFincaPrecio($obj) {
        $id = $this->ingresar("produccion.sku_precio_finca", $obj, true, false);
        if ($id) {
            $dato_log = array(
                "sku_precio_finca_id" => $id,
                "accion" => "creacion de un nuevo producto-variante con precio" . json_encode($obj),
            );
            $this->registrarLog("produccion.sku_precio_finca_log", $dato_log);
        }
        return $id;
    }

    public function obtenerPrecioFincaSku($id) {
        $this->db->select(' pr.titulo as producto, pv.titulo as variante,pv.sku,spf.id, spf.finca_id, spf.fecha_inicio_vigencia,spf.fecha_fin_vigencia, spf.precio_unitario,spf.estado');
        $this->db->from('produccion.sku_precio_finca spf');
        $this->db->join('ecommerce.producto_variante pv', 'spf.sku = pv.sku', 'left');
        $this->db->join('ecommerce.producto pr', 'pr.id = pv.producto_id', 'left');
        
       // $this->db->where('spf.estado', ESTADO_ACTIVO);
        // $this->db->where('ipf.estado', ESTADO_ACTIVO);
        $this->db->where('spf.id', $id);
        $arrDatos = $this->retornarUno(true);
        return $arrDatos;
    }
    public function actualizarFechaFincaPrecioSku($obj) {
        $id = $this->actualizar("produccion.sku_precio_finca", $obj, "id", false);
        if ($id) {
            $dato_log = array(
                "sku_precio_finca_id" => $obj['id'],
                "accion" => "actualizacion de precio en el sku-precio-finca" . json_encode($obj),
            );
            $this->registrarLog("produccion.sku_precio_finca_log", $dato_log);
        }
        return $id;
    }
    /***********************************Metodo para traer datos de los productos*************************************** */
    public function obtenerListaProductos($id = false) {
        $this->db->select("p.id, p.titulo || ' ' || p.sku_prefijo as descripcion");
        $this->db->from('produccion.sku_precio_finca spf');
        $this->db->join('ecommerce.producto_variante pv', 'spf.sku = pv.sku', 'left');
        $this->db->join('ecommerce.producto p', 'pv.producto_id = p.id', 'left');
        $this->db->where('p.estado', ESTADO_ACTIVO);
        if ($id) {
            $this->db->where('spf.id', $id);
            $this->db->order_by("p.titulo", "ASC");
            $arrDatos[0] = $this->retornarUno();
        }else{
            $this->db->order_by("p.titulo", "ASC");
            $arrDatos = $this->retornarMuchosSinPaginacion();
        }
        
        
        
        return $this->retornarSel($arrDatos, "descripcion", true);

    }
    public function obtenerListaVariantes($id = false) {
        $this->db->select("pv.id, pv.titulo || ' ' || pv.sku as descripcion");
        $this->db->from('ecommerce.producto_variante pv');
        $this->db->join('produccion.sku_precio_finca spf', 'spf.sku = pv.sku', 'left');
        $this->db->where('pv.estado', ESTADO_ACTIVO);
        if ($id) {
            $this->db->where('spf.id', $id);
            $this->db->order_by("pv.titulo", "ASC");
            $arrDatos[0] = $this->retornarUno();
        }else{
            $this->db->order_by("pv.titulo", "ASC");
            $arrDatos = $this->retornarMuchosSinPaginacion();
        }  
        return $this->retornarSel($arrDatos, "descripcion", true);

    }
    public function obtenerSkuVariante($variante_id){
        $this->db->select("pv.sku ");
        $this->db->from('ecommerce.producto_variante pv');
        $this->db->where('pv.id', $variante_id);
        $this->db->where('pv.estado', ESTADO_ACTIVO);
        $arrDatos = $this->retornarUno();
        return $arrDatos;
    }
}

?>