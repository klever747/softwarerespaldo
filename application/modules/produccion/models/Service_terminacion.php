<?php

class Service_terminacion extends My_Model {

    public function obtenerOrdenesProductosVariantes($store_id, $tipo_calendario, $rango_busqueda, $orden = '', $busqueda = '', $producto_id = null, $producto_variante_id = null, $preparado = 'T', $terminado = 'T', $wrap = 'NO') {
        $this->db->select("
            p.id as producto_id,
            p.titulo as producto_titulo,
            p.sku_prefijo as producto_sku,
            pv.id  as variante_id,
            pv.titulo as variante_titulo,
            pv.sku as variante_sku,
            pv.largo_cm,
            pv.cantidad as variante_cantidad,
            SUM( CASE oi.terminado WHEN 'S' THEN oi.cantidad ELSE 0 END) as orden_item_variante_cantidad_terminado,
            SUM( CASE oi.preparado WHEN 'S' THEN oi.cantidad ELSE 0 END) as orden_item_variante_cantidad_preparado,
            SUM(oi.cantidad) as orden_item_variante_cantidad");
        $this->db->from('ecommerce.orden o');
        $this->db->join('ecommerce.orden_item oi', 'o.id = oi.orden_id', 'left');
        $this->db->join('ecommerce.producto_variante pv', 'oi.variante_id = pv.id', 'left');
        $this->db->join('ecommerce.producto p', 'oi.producto_id = p.id', 'left');
        if ($store_id != 0) {
            $this->db->where('o.store_id', $store_id);
        }
        $wrap = strtoupper($wrap);
        if ($wrap !== 'NO') {
            $this->db->join('ecommerce.orden_item_propiedad oip', 'oi.id = oip.orden_item_id AND oip.estado = \'' . ESTADO_ACTIVO . '\'', 'left');
            $this->db->where("oip.propiedad_id", 10);
            if (strtoupper($wrap) == 'LUXURY') {
                $this->db->where("UPPER(oip.valor) LIKE '%LUXURY%'");
            } else {
                $this->db->where("UPPER(oip.valor) NOT LIKE '%LUXURY%'");
            }
        } else {
            $this->db->join('ecommerce.orden_item_propiedad oip', 'oi.id = oip.orden_item_id AND oip.estado = \'' . ESTADO_ACTIVO . '\' AND oip.propiedad_id = 10', 'left');
            $this->db->where("oip.id", null);
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

//        if ($preparado != 'T') {
//            $this->db->where('oi.preparado', $preparado);
//        }
//        if ($terminado != 'T') {
//            $this->db->where('oi.terminado', $terminado);
//        }
//        $this->db->where("p.sku_prefijo","AGR_P_ASS");
//        $this->db->where("p.sku_prefijo", "AGR_P_VTG");
//        $this->db->where('pv.sku', 'AGR_PT_BMA_024_40');
//        $this->db->where("o.id",13433);
        $this->db->where("p.sku_prefijo NOT LIKE 'AGR_PN%'");

        if ($producto_variante_id != '') {
            $this->db->where('oi.variante_id', $producto_variante_id);
        }

        $this->db->group_by(array('pv.id', 'pv.titulo', 'pv.sku', 'pv.largo_cm', 'p.id', 'p.titulo', 'p.sku_prefijo'));
        $this->db->order_by('p.titulo', 'DESC');
        $this->db->order_by('pv.titulo', 'ASC');
        return $this->retornarMuchosSinPaginacion();
    }

    public function obtenerProductosVariantesPorWrap($store_id, $tipo_calendario, $rango_busqueda, $wrap) {
        return $this->obtenerOrdenesProductosVariantes($store_id, $tipo_calendario, $rango_busqueda, '', '', null, null, 'T', 'T', $wrap);
    }

    public function obtenerDetalleOrdenesProductoVariantes($producto_id, $variante_id, $store_id, $tipo_calendario, $rango_busqueda, $preparado = 'T', $terminado = 'T', $wrap = 'NO') {
        $respuesta = array();
        $producto = $this->service_ecommerce_producto->obtenerProducto($producto_id);
        $variantes = $this->service_ecommerce_producto->obtenerVariantesProducto($producto_id);
        $wrap = strtoupper($wrap);
//        $respuesta[$producto_id]['producto'] = $producto;
        error_log("PRODUCTO**************************");
        error_log(print_r($producto_id, true));
        error_log(print_r($variante_id, true));
        $respuesta[$producto_id]['variantes'] = array();
        if ($variantes) {
            foreach ($variantes as $variante) {
                if (!empty($variante_id)) {
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
                    "preparado" => 'T', //$preparado,
                    "terminado" => 'T', //$terminado,
                    "empacado" => 'N'
                );
                $ordenes = $this->service_ecommerce_orden->obtenerOrdenesItems($filtro);
                error_log("ORDENES**************************");
                error_log(print_r($ordenes, true));
                if ($ordenes) {
                    foreach ($ordenes as $orden) {
//                    error_log("ORDEN**************************");
//                    error_log(print_r($orden, true));die;
                        $propiedades = $this->service_ecommerce->obtenerOrdenItemPropiedades($orden->orden_item_id, ESTADO_ACTIVO);

                        if ($propiedades) {
                            //primero veo que contiene el wrap que busco, si no lo salto
                            $contiene_tipo_wrap = true;
                            error_log("Tiene tipo wrap consulta es " . $wrap);
                            if ($wrap != 'NO') {
                                $contiene_tipo_wrap = false;
                            } else {
                                $contiene_tipo_wrap = true;
                            }
                            $continuar = true;
                            foreach ($propiedades as $item_propiedad) {
                                if ($item_propiedad->propiedad_id == 10) {
                                    $valor_prop_10 = "_" . strtoupper($item_propiedad->valor);
                                    if ($wrap != 'NO') {
                                        if (strpos($valor_prop_10, $wrap) !== false) {
                                            $comparac = "Si";
                                        } else {
                                            $comparac = "No";
                                        }
                                        error_log($comparac);
                                        if ($wrap == 'LUXURY') {
                                            if (strpos($valor_prop_10, 'LUXURY') !== false) {
                                                $contiene_tipo_wrap = true;
                                            }
                                        } else if (strpos($valor_prop_10, 'LUXURY') == false) {
                                            $contiene_tipo_wrap = true;
                                        }
                                    } else {
                                        $contiene_tipo_wrap = false;
                                    }
                                    if (!$contiene_tipo_wrap) {
                                        $continuar = false;
                                        continue;
                                    }
                                    error_log("Contiene wrap " . $contiene_tipo_wrap);
                                }
                            }
                            if (!$continuar) {
                                error_log("CONTINUAR ES FALSO");
                                continue;
                            }
                            foreach ($propiedades as $item_propiedad) {

                                if ($item_propiedad->propiedad_id == 10 || $item_propiedad->propiedad_id == 11 || $propiedad->propiedad_id == 371 || $item_propiedad->propiedad_id == 18 || $propiedad->propiedad_id == 372 || $propiedad->propiedad_id == 373) {//WRAP//petals//vase
//                            error_log("PROPIEDAD ES " . print_r($item_propiedad, true));
//                                error_log("Propiedad id " . print_r($item_propiedad->propiedad_id, true));
                                    if (!array_key_exists($item_propiedad->propiedad_id, $variante_propiedades)) {
                                        $variante_propiedades[$item_propiedad->propiedad_id]['propiedad_descripcion'] = $item_propiedad->info_propiedad_descripcion;
                                        $variante_propiedades[$item_propiedad->propiedad_id]['valores'] = array();
                                    }
//                            error_log("Propiedad id  es" . print_r($item_propiedad->propiedad_id, true));
//                                error_log("Variante propiedades es " . print_r($variante_propiedades, true));
//                            $pos = strpos($v, '(');
//                                error_log("POSICION");
                                    $pos = stripos($item_propiedad->valor, 'ADD LOOSE');
//                                error_log($pos);
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
                            $arr_ordenes[] = $orden;
                            $cantidad_pedida += $orden->orden_item_cantidad;
                            error_log("ARR_ORDENES-------------------" . print_r($arr_ordenes, true));
                        } else {
                            if ($wrap == 'NO') {
                                $arr_ordenes[] = $orden;
                                $cantidad_pedida += $orden->orden_item_cantidad;
                            }
                        }
                    }

                    $variante->ordenes = $arr_ordenes;
                    $variante->cantidad_pedida = $cantidad_pedida;
                    $variante->propiedades = $variante_propiedades;

                    $respuesta[$producto_id]['variantes'][] = $variante;
                    error_log("RESPUESTA-------------------" . print_r($respuesta, true));
                }
            }
        }
        return $respuesta;
    }

}
