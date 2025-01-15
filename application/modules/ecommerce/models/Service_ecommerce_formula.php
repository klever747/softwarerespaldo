<?php

class Service_ecommerce_formula extends My_Model {

    public function obtenerReceta($sku) {
        $this->db->select('r.id, r.sku, r.ingrediente_id, r.cantidad, i.nombre, i.descripcion, i.tipo');
        $this->db->from('produccion.receta r');
        $this->db->join('produccion.ingrediente i', 'r.ingrediente_id = i.id', 'left');
        $this->db->where('r.sku', $sku);
        $this->db->where('r.estado', ESTADO_ACTIVO);

        return $this->retornarMuchosSinPaginacion();
    }

    public function obtenerRecetaProductoVariante($producto_variante_id) {
        $this->db->select('r.id, r.sku, r.ingrediente_id, r.cantidad, i.nombre, i.descripcion, i.tipo');
        $this->db->from('produccion.receta r');
        $this->db->join('produccion.ingrediente i', 'r.ingrediente_id = i.id', 'left');
        $this->db->join('ecommerce.producto_variante pv', 'pv.sku = r.sku', 'left');
        $this->db->where('pv.id', $producto_variante_id);
        $this->db->where('r.estado', ESTADO_ACTIVO);
        $receta = $this->retornarMuchosSinPaginacion();
        error_log("obtenerRecetaProductoVariante " . $producto_variante_id);
        error_log(print_r($this->db->last_query(), true));

        return $receta;
    }

    public function crearReceta($datos) {
        return $this->ingresar("produccion.receta", $datos, true, true);
    }

//    public function totalStemsProductoVariante($producto_variante_id) {
//        $this->db->select("SUM(r.cantidad), 
//                SUM ( CASE WHEN i.tipo = 'T' THEN r.cantidad ELSE 0 END ) as totalTinturado, 
//                SUM ( CASE WHEN i.tipo = 'T' THEN r.cantidad * i.costo ELSE 0 END ) as totalTinturadoPrecio, 
//                SUM ( CASE WHEN i.tipo = 'N' THEN r.cantidad ELSE 0 END ) as totalNormal,
//                SUM ( CASE WHEN i.tipo = 'N' THEN r.cantidad * i.costo ELSE 0 END ) as totalNormalPrecio,
//                SUM ( CASE WHEN i.tipo = 'A' THEN r.cantidad ELSE 0 END ) as totalAccesorio,
//                SUM ( CASE WHEN i.tipo = 'A' THEN r.cantidad * i.costo ELSE 0 END ) as totalAccesorioPrecio");
//        $this->db->from('produccion.receta r');
//        $this->db->join('produccion.ingrediente i', 'r.ingrediente_id = i.id', 'left');
//        $this->db->join('ecommerce.producto_variante pv', 'pv.sku = r.sku', 'left');
//        $this->db->where('pv.id', $producto_variante_id);
//        $this->db->where('r.estado', ESTADO_ACTIVO);
////error_log();
//        return $this->retornarUno();
//    }

    public function totalStemsRecetaSKU($sku) {
        $this->db->select("SUM(r.cantidad)");
        $this->db->from('produccion.receta r');
        $this->db->join('produccion.ingrediente i', 'r.ingrediente_id = i.id', 'left');
        $this->db->where('r.sku', $sku);
        $this->db->where('r.estado', ESTADO_ACTIVO);
//error_log();
        return $this->retornarUno();
    }

    public function totalStemsSKUdesglosado($sku, $fecha_carguera, $finca_id) {
        $this->db->select('*');
        $this->db->from('produccion.v_total_stems_desglosado vtd');
        $this->db->where('vtd.sku', $sku);
        $this->db->where('vtd.finca_id', $finca_id);
        $this->db->where("'$fecha_carguera 'BETWEEN vtd.fecha_inicio_vigencia and vtd.fecha_fin_vigencia");

        return $this->retornarUno();
    }

    public function totalStemsSKUdesglosadoOld($sku) {
        error_log("stems SKU desglosado");
        $longitud = "40";
        $arr = explode("_", $sku);
        error_log(print_r($arr, true)); //die;
        if ((sizeof($arr) == 5) && is_numeric($arr[4]) && ($arr[4] >= 40)) {
            $longitud = $arr[4] * 1;
        }

        $this->db->select("SUM(r.cantidad), 
                SUM ( CASE WHEN i.tipo = 'T' THEN r.cantidad ELSE 0 END ) as totalTinturado, 
                SUM ( CASE WHEN i.tipo = 'T' THEN r.cantidad * i.costo_" . $longitud . " ELSE 0 END ) as totalTinturadoPrecio, 
                SUM ( CASE WHEN i.tipo = 'N' THEN r.cantidad ELSE 0 END ) as totalNormal,
                SUM ( CASE WHEN i.tipo = 'N' THEN r.cantidad * i.costo_" . $longitud . " ELSE 0 END ) as totalNormalPrecio,
                SUM ( CASE WHEN i.tipo = 'A' THEN r.cantidad ELSE 0 END ) as totalAccesorio,
                SUM ( CASE WHEN i.tipo = 'A' THEN r.cantidad * i.costo ELSE 0 END ) as totalAccesorioPrecio");
        $this->db->from('produccion.receta r');
        $this->db->join('produccion.ingrediente i', 'r.ingrediente_id = i.id', 'left');
        $this->db->where('r.sku', $sku);
        $this->db->where('r.estado', ESTADO_ACTIVO);
//error_log();
        return $this->retornarUno();
    }

    /*     * *************Nueva Estructura ********** */

    public function totalStemsSKUdesglosadoNuevo($sku, $fecha_carguera) {
        $this->db->select('*');
        $this->db->from('produccion.receta re');
        $this->db->join('produccion.ingrediente ig', 're.ingrediente_id = ig.id', 'left');
        $this->db->join('produccion.ingrediente_precio_finca ipf', 'ig.id = ipf.ingrediente_id', 'left');
        $this->db->join('general.finca f', 'ipf.finca_id = f.id', 'left');
        $this->db->where("" . $sku . "BETWEEN ipf.fecha_inicio_vigencia and ipf.fecha_fin_vigencia");
        $this->db->where('re.sku', $sku);
    }

}
