<?php

class Service_preparacion extends My_Model {

    public function obtenerOrdenesProductosVariantes($store_id, $tipo_calendario, $rango_busqueda, $orden = '', $busqueda = '', $producto_id = null, $producto_variante_id = null, $preparado = 'T', $filtroColores = false) {
        $this->db->select("
            p.id as producto_id,
            p.titulo as producto_titulo,
            p.sku_prefijo as producto_sku,
            pv.id  as variante_id,
            pv.titulo as variante_titulo,
            pv.sku as variante_sku,
            pv.largo_cm,
            pv.cantidad as variante_cantidad,
            SUM( CASE oi.preparado WHEN 'S' THEN oi.cantidad ELSE 0 END) as orden_item_variante_cantidad_preparado,
            SUM(oi.cantidad) as orden_item_variante_cantidad");
        $this->db->from('ecommerce.orden o');
        $this->db->join('ecommerce.orden_item oi', 'o.id = oi.orden_id', 'left');
        $this->db->join('ecommerce.producto_variante pv', 'oi.variante_id = pv.id', 'left');
        $this->db->join('ecommerce.producto p', 'oi.producto_id = p.id', 'left');
        if ($store_id != 0) {
            $this->db->where('o.store_id', $store_id);
        }
        if ($filtroColores && (sizeof($filtroColores) > 0)) {
            $filtroColor = '';
            foreach ($filtroColores as $color) {
                $filtroColor .= " UPPER(p.tags) LIKE '%" . strtoupper($color) . "%' OR";
            }
            $filtroColor = substr($filtroColor, 0, strlen($filtroColor) - 3);
            $filtroColor = "(" . $filtroColor . ")";
            $this->db->where($filtroColor);
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

        $this->db->where('o.estado', ESTADO_ACTIVO);
        $this->db->where('oi.estado', ESTADO_ACTIVO);

        if ($preparado != 'T') {
            $this->db->where('oi.preparado', $preparado);
        }

        if ($producto_variante_id != '') {
            $this->db->where('oi.variante_id', $producto_variante_id);
        }

        $this->db->where("p.sku_prefijo NOT LIKE 'AGR_PN%'");
        //        
//        $this->db->where("p.sku_prefijo", "AGR_P_GLS");
//        $this->db->where("p.sku_prefijo","AGR_P_VTG");  
//        $this->db->where('pv.sku', 'AGR_PT_BMA_024_40');

        $this->db->group_by(array('pv.id', 'pv.titulo', 'pv.sku', 'pv.largo_cm', 'p.id', 'p.titulo', 'p.sku_prefijo'));
        $this->db->order_by('p.titulo', 'DESC');
        $this->db->order_by('pv.titulo', 'ASC');
        return $this->retornarMuchosSinPaginacion();
    }

    public function obtenerDetalleOrdenesProductoVariantes($producto_id, $variante_id, $store_id, $tipo_calendario, $rango_busqueda, $preparado = 'T', $unico = false) {
        $respuesta = array();
        $producto = $this->service_ecommerce_producto->obtenerProducto($producto_id);
        $variantes = $this->service_ecommerce_producto->obtenerVariantesProducto($producto_id);
        $respuesta[$producto->id]['producto'] = $producto;
//        error_log("PRODUCTO**************************");
//        error_log(print_r($producto_id, true));
//        error_log(print_r($variante_id, true));
//        error_log(print_r($producto, true));
//        error_log(print_r($variantes, true));
//        $arr_propiedades_existentes = array();
//        return $respuesta;
        foreach ($variantes as $variante) {
            if ((!empty($variante_id)) && $unico) {
//                error_log("Variante id no es empty");
//                error_log("Variante->id es " . $variante->id);
//                error_log("Variante id es " . $variante_id);
                if ($variante->id != $variante_id) {
                    error_log("Variante id no es igual a variante->id");
                    continue;
                }
            }
//            error_log("VARIANTE**************************");
//            error_log(print_r($variante, true));
//            if ($variante->sku !='AGR_PT_PAR_024_40'){
//                continue;
//            }

            $variante_propiedades = array();
            $variante_ordenes = array();
            $arr_ordenes = array();
            $cantidad_pedida = 0;
//            error_log("VARIANTE ID es " . $variante->id);
//            error_log(print_r($variante->id, true));
            $totalStemsVariante = $this->service_ecommerce_formula->totalStemsRecetaSKU($variante->sku);
            $totalStems = $totalStemsVariante->sum;
            $filtro = array(
                "store_id" => $store_id,
                "tipo_calendario" => $tipo_calendario,
                "rango_busqueda" => $rango_busqueda,
                "variante_id" => $variante->id,
                "preparado" => 'T', // $preparado
            );
            $ordenes = $this->service_ecommerce_orden->obtenerOrdenesItems($filtro);
//            die;
            error_log("ORDENES**************************");
            error_log(print_r($ordenes, true));
            if ($ordenes) {
                foreach ($ordenes as $orden) {
//                    error_log("ORDEN**************************");
//                    error_log(print_r($orden, true));die;
                    $propiedades = $this->service_ecommerce->obtenerOrdenItemPropiedades($orden->orden_item_id, ESTADO_ACTIVO);
                    if ($propiedades) {
                        foreach ($propiedades as $item_propiedad) {
                            if ($item_propiedad->propiedad_id == 10 || $item_propiedad->propiedad_id == 11 || $propiedad->propiedad_id == 371 || $item_propiedad->propiedad_id == 18 || $propiedad->propiedad_id == 372 || $propiedad->propiedad_id == 373) {//WRAP//petals//vase
//                            error_log("PROPIEDAD ES " . print_r($item_propiedad, true));
                                error_log("Propiedad id " . print_r($item_propiedad->propiedad_id, true));
                                if (!array_key_exists($item_propiedad->propiedad_id, $variante_propiedades)) {
                                    $variante_propiedades[$item_propiedad->propiedad_id]['propiedad_descripcion'] = $item_propiedad->info_propiedad_descripcion;
                                    $variante_propiedades[$item_propiedad->propiedad_id]['valores'] = array();
                                }
//                            error_log("Propiedad id  es" . print_r($item_propiedad->propiedad_id, true));
                                error_log("Variante propiedades es " . print_r($variante_propiedades, true));
//                            $pos = strpos($v, '(');
                                error_log("POSICION");
                                $pos = stripos($item_propiedad->valor, 'ADD LOOSE');
                                error_log($pos);
                                if ($pos === FALSE) {
                                    if (!array_key_exists($item_propiedad->valor, $variante_propiedades[$item_propiedad->propiedad_id]['valores'])) {
                                        error_log("No existe key");
                                        $variante_propiedades[$item_propiedad->propiedad_id]['valores'][$item_propiedad->valor]['numero'] = 0;
                                    } else {
                                        error_log("Ya existe key");
                                        error_log("Numero es " . $variante_propiedades[$item_propiedad->propiedad_id]['valores'][$item_propiedad->valor]['numero']);
                                    }
                                    $variante_propiedades[$item_propiedad->propiedad_id]['valores'][$item_propiedad->valor]['numero'] = $variante_propiedades[$item_propiedad->propiedad_id]['valores'][$item_propiedad->valor]['numero'] + 1;
                                    $variante_propiedades[$item_propiedad->propiedad_id]['valores'][$item_propiedad->valor]['ordenes'][$orden->id] = $orden;
                                    $variante_propiedades[$item_propiedad->propiedad_id]['ordenes'] = $orden;
                                }
                                error_log("Variante propiedades es " . print_r($variante_propiedades, true));
                            }
                        }
                    }
                    $arr_ordenes[] = $orden;
                    $cantidad_pedida += $orden->orden_item_cantidad;
                    error_log("ARR_ORDENES-------------------" . print_r($arr_ordenes, true));
                }

                $variante->ordenes = $arr_ordenes;
                $variante->cantidad_pedida = $cantidad_pedida;
                $variante->propiedades = $variante_propiedades;

                $respuesta[$producto->id]['variantes'][] = $variante;
                error_log("RESPUESTA-------------------" . print_r($respuesta, true));
            }
        }
        return $respuesta;
    }

}
