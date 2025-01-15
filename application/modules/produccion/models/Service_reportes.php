<?php

class Service_reportes extends My_Model {

    public function reporteVentasSku($filtro){
        $arrRango = explode(" - ", $filtro['rango_busqueda']);
        $fechaIni = date_format(DateTime::createFromFormat(FORMATO_FECHA, convertirFechaBD($arrRango[0])), 'Y-m-d 00:00:00');
        $fechaFin = date_format(DateTime::createFromFormat(FORMATO_FECHA, convertirFechaBD($arrRango[1])), 'Y-m-d 23:59:59');
        $this->db->select("s.alias,s.store_name,tc.nombre,oc.tipo_caja_id as tipo_caja, vtc.tinturados,vtc.naturales,oi.orden_id ,oi.id as item_id,
        pv.titulo, i.tipo as tipo_ingrediente ,
        pv.largo_cm, spf.sku,
        o.fecha_carguera,
        SUM(oi.cantidad * r.cantidad) as total_stems,
        SUM(spf.precio_unitario) as total_precio");

        $this->db->from('ecommerce.orden o ');
        $this->db->join('ecommerce.store s', 'o.store_id= s.id', 'left');
        $this->db->join('ecommerce.orden_caja oc', 'oc.orden_id = o.id', 'left');
        $this->db->join('ecommerce.tipo_caja tc', 'oc.tipo_caja_id= tc.id', 'left');
        $this->db->join('ecommerce.orden_caja_item oci', ' oci.orden_caja_id = oc.id', 'left');
        $this->db->join('ecommerce.orden_item oi', 'oi.id = oci.orden_item_id', 'left');
        $this->db->join('ecommerce.producto p', 'oi.producto_id = p.id', 'left');
        $this->db->join('ecommerce.producto_variante pv', 'oi.variante_id = pv.id', 'left');
        $this->db->join('produccion.receta r', 'pv.sku= r.sku', 'left');
        $this->db->join('produccion.ingrediente i', 'r.ingrediente_id = i.id', 'left');
        $this->db->join('ecommerce.finca_caja fc', "fc.orden_caja_id = oci.orden_caja_id AND fc.estado ='".ESTADO_ACTIVO."' ", 'left');
        $this->db->join('produccion.sku_precio_finca spf', "spf.sku = r.sku AND spf.finca_id = fc.finca_id", 'left');
        $this->db->join('produccion.v_tipo_cajas_nt vtc', ' pv.sku= vtc.sku', 'left');
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
            if (isset($srt)) {
                $this->db->where($srt);
            }
        }
        $this->db->where('o.estado', ESTADO_ACTIVO);
        $this->db->where('spf.estado', ESTADO_ACTIVO);
        $this->db->where('oi.estado', ESTADO_ACTIVO);
        $this->db->where('oc.estado', ESTADO_ACTIVO);
        $this->db->where('oci.estado', ESTADO_ACTIVO);
        $this->db->where('r.estado', ESTADO_ACTIVO);
        $this->filtrar($filtro);
        $this->db->where("'$fechaIni' BETWEEN spf.fecha_inicio_vigencia and spf.fecha_fin_vigencia");
        $this->db->group_by(array('s.alias','s.store_name','tc.nombre', 'oc.tipo_caja_id', 'vtc.tinturados', 'vtc.naturales', 'oi.orden_id', 'oi.id', 'pv.titulo', 'i.tipo', 'pv.largo_cm', 'spf.sku', 'o.fecha_carguera'));
        $arr = $this->retornarMuchosSinPaginacion(true);
        return $arr;
    }
    public function reporteVentasTotalSku($filtro){
        $arrRango = explode(" - ", $filtro['rango_busqueda']);
        $fechaIni = date_format(DateTime::createFromFormat(FORMATO_FECHA, convertirFechaBD($arrRango[0])), 'Y-m-d 00:00:00');
        $fechaFin = date_format(DateTime::createFromFormat(FORMATO_FECHA, convertirFechaBD($arrRango[1])), 'Y-m-d 23:59:59');
        $this->db->select("(CASE 

                            WHEN vtc.tinturados = 1 THEN
                                'T'::text
                            WHEN vtc.naturales = 1 THEN
                                'N'::text
                            ELSE
                                'M'::text
                        END) as Tipo_caja_TNM,
        s.id as store_id,s.alias,s.store_name,tc.nombre,oc.tipo_caja_id as tipo_caja,oi.orden_id ,
        i.tipo as tipo_ingrediente ,
        i.longitud, 
        SUM(oi.cantidad * r.cantidad) as total_stems,
        SUM(spf.precio_unitario) as total_precio,
        SUM(vtc.tinturados)as totalTinturadoXcaja,
        SUM(vtc.naturales)as totalNaturalesXcaja,
        count(o.id)as total_de_cajas");

        $this->db->from('ecommerce.orden o ');
        $this->db->join('ecommerce.store s', 'o.store_id= s.id', 'left');
        $this->db->join('ecommerce.orden_caja oc', 'oc.orden_id = o.id', 'left');
        $this->db->join('ecommerce.tipo_caja tc', 'oc.tipo_caja_id= tc.id', 'left');
        $this->db->join('ecommerce.orden_caja_item oci', ' oci.orden_caja_id = oc.id', 'left');
        $this->db->join('ecommerce.orden_item oi', 'oi.id = oci.orden_item_id', 'left');
        $this->db->join('ecommerce.producto p', 'oi.producto_id = p.id', 'left');
        $this->db->join('ecommerce.producto_variante pv', 'oi.variante_id = pv.id', 'left');
        $this->db->join('produccion.receta r', 'pv.sku= r.sku', 'left');
        $this->db->join('produccion.ingrediente i', 'r.ingrediente_id = i.id', 'left');
        $this->db->join('ecommerce.finca_caja fc', "fc.orden_caja_id = oci.orden_caja_id AND fc.estado ='".ESTADO_ACTIVO."' ", 'left');
        $this->db->join('produccion.sku_precio_finca spf', "spf.sku = r.sku AND spf.finca_id = fc.finca_id", 'left');
        $this->db->join('produccion.v_tipo_cajas_nt vtc', ' pv.sku= vtc.sku', 'left');
        if ($filtro['session_finca'] == 1) {
            if ($filtro['finca_id'] != 0) {
                $this->db->where("fc.finca_id", $filtro['finca_id']);
            }
        }
        else {
            $this->db->where("fc.finca_id", $filtro['session_finca']);
        }
        $this->db->where('o.estado', ESTADO_ACTIVO);
        $this->db->where('spf.estado', ESTADO_ACTIVO);
        $this->db->where('oi.estado', ESTADO_ACTIVO);
        $this->db->where('oc.estado', ESTADO_ACTIVO);
        $this->db->where('oci.estado', ESTADO_ACTIVO);
        $this->db->where('r.estado', ESTADO_ACTIVO);
        $this->filtrar($filtro);
        $this->db->where("'$fechaIni' BETWEEN spf.fecha_inicio_vigencia and spf.fecha_fin_vigencia");
        $this->db->group_by(array( 's.id', 'vtc.tinturados','vtc.naturales','s.alias','s.store_name','tc.nombre','oc.tipo_caja_id','oi.orden_id',
        'i.tipo',
        'i.longitud'));
        $arr = $this->retornarMuchosSinPaginacion(true);
        return $arr;
    }
    public function reporteVentasSKU_($filtro){
        $arrRango = explode(" - ", $filtro['rango_busqueda']);
        $fechaIni = date_format(DateTime::createFromFormat(FORMATO_FECHA, convertirFechaBD($arrRango[0])), 'Y-m-d 00:00:00');
        $fechaFin = date_format(DateTime::createFromFormat(FORMATO_FECHA, convertirFechaBD($arrRango[1])), 'Y-m-d 23:59:59');
        $this->db->select("  CASE
        WHEN ((vgpc.totaltinturadoxcaja > 0) AND (vgpc.totalnaturalesxcaja = 0)) THEN 'T'::text
        WHEN ((vgpc.totalnaturalesxcaja > 0) AND (vgpc.totaltinturadoxcaja = 0)) THEN 'N'::text
        ELSE 'M'::text
        END AS tipo_caja_tnm,
        vgpc.store_id,vgpc.alias,vgpc.store_name,
        vgpc.nombre,vgpc.tipo_caja_id,vgpc.longitud,vgpc.finca_id,
        (vgpc.total_stems)as total_stems,
        (vgpc.precioxcaja)as precio_total_cajas,
        (vgpc.totaltinturadoxcaja)as total_tinturados,
        (vgpc.totalnaturalesxcaja)as total_naturales,
        count(vgpc.orden_id)as total_cajas");

        $this->db->from('produccion.v_general_precio_cajas vgpc');
        $arrayfinca = explode(",", $filtro['session_finca']);
        if (array_key_exists('session_finca', $filtro)) {
            if (in_array(FINCA_ROSAHOLICS_ID, $arrayfinca)) {
                if ($filtro['finca_id'] != 0) {
                    $srt = "vgpc.finca_id in (" . $filtro['finca_id'] . ")";
                }
            } else {
                if (array_key_exists('finca_id', $filtro)) {
                    if ($filtro['finca_id'] != 0) {
                        $srt = "vgpc.finca_id in (" . $filtro['finca_id'] . ")";
                    } else {
                        $srt = "vgpc.finca_id in (" . $filtro['session_finca'] . ")";
                    }
                } else {
                    $srt = "vgpc.finca_id in (" . $filtro['session_finca'] . ")";
                }
            }
            if (isset($srt)) {
                $this->db->where($srt);
            }
        }
        $this->filtrar($filtro);
        $this->db->group_by(array( ' vgpc.store_id','vgpc.alias','vgpc.store_name',
        'vgpc.nombre','vgpc.tipo_caja_id','vgpc.longitud','vgpc.finca_id','vgpc.finca_id','vgpc.total_stems','vgpc.precioxcaja',
				'vgpc.totaltinturadoxcaja','vgpc.totalnaturalesxcaja'));
        $arr = $this->retornarMuchosSinPaginacion(true);
        return $arr;
    }
    public function reporteVentasObtenerOrdenes($filtro,$dataConsulta=false){
        $arrRango = explode(" - ", $filtro['rango_busqueda']);
        $fechaIni = date_format(DateTime::createFromFormat(FORMATO_FECHA, convertirFechaBD($arrRango[0])), 'Y-m-d 00:00:00');
        $fechaFin = date_format(DateTime::createFromFormat(FORMATO_FECHA, convertirFechaBD($arrRango[1])), 'Y-m-d 23:59:59');
        $this->db->select("CASE
            WHEN ((vgpc.totaltinturadoxcaja > 0) AND (vgpc.totalnaturalesxcaja = 0)) THEN 'T'::text
            WHEN ((vgpc.totalnaturalesxcaja > 0) AND (vgpc.totaltinturadoxcaja = 0)) THEN 'N'::text
            ELSE 'M'::text
        END AS tipo_caja_tnm,
        vgpc.orden_id,vgpc.referencia_order_number,vgpc.caja_id,vgpc.longitud,
        vgpc.total_stems,vgpc.precioxcaja,vgpc.alias");

        $this->db->from('produccion.v_general_precio_cajas vgpc');
        $arrayfinca = explode(",", $filtro['session_finca']);
        if (in_array(FINCA_ROSAHOLICS_ID, $arrayfinca)) {
            if ($filtro['finca_id'] != 0) {
                $srt = "vgpc.finca_id in (" . $filtro['finca_id'] . ")";
            }
        } else {
            if (array_key_exists('finca_id', $filtro)) {
                if ($filtro['finca_id'] != 0) {
                    $srt = "vgpc.finca_id in (" . $filtro['finca_id'] . ")";
                } else {
                    $srt = "vgpc.finca_id in (" . $filtro['session_finca'] . ")";
                }
            } else {
                $srt = "vgpc.finca_id in (" . $filtro['session_finca'] . ")";
            }
        }
        if (isset($srt)) {
            $this->db->where($srt);
        }
        if($dataConsulta){
            $this->db->where("vgpc.longitud", $dataConsulta['longitud']);
            $this->db->where("vgpc.total_stems", $dataConsulta['stemsxcaja']);
            
            $this->db->where("vgpc.precioxcaja", $dataConsulta['precioxcaja']);

            $this->db->where("vgpc.tipo_caja_id", $dataConsulta['tipo_caja_id']);
        }
        
        $this->filtrar($filtro);
        $this->db->group_by(array( 'vgpc.orden_id','vgpc.referencia_order_number','vgpc.caja_id',
        'vgpc.longitud', 'vgpc.total_stems','vgpc.precioxcaja','vgpc.alias','vgpc.totaltinturadoxcaja','vgpc.totalnaturalesxcaja','vgpc.tipo_caja_id'));
        $arr = $this->retornarMuchosSinPaginacion(true);
        return $arr;
    }
    public function reporteVentasObtenerOrdenesDesglosado($filtro,$dataConsulta=false,$orden_id){
        $arrRango = explode(" - ", $filtro['rango_busqueda']);
        $fechaIni = date_format(DateTime::createFromFormat(FORMATO_FECHA, convertirFechaBD($arrRango[0])), 'Y-m-d 00:00:00');
        $fechaFin = date_format(DateTime::createFromFormat(FORMATO_FECHA, convertirFechaBD($arrRango[1])), 'Y-m-d 23:59:59');
        $this->db->select("CASE
            WHEN ((vgpc.totaltinturadoxcaja > 0) AND (vgpc.totalnaturalesxcaja = 0)) THEN 'T'::text
            WHEN ((vgpc.totalnaturalesxcaja > 0) AND (vgpc.totaltinturadoxcaja = 0)) THEN 'N'::text
            ELSE 'M'::text
        END AS tipo_caja_tnm,*");

        $this->db->from('produccion.v_general_precio_desglosado vgpc');
        $arrayfinca = explode(",", $filtro['session_finca']);
        if (in_array(FINCA_ROSAHOLICS_ID, $arrayfinca)) {
            if ($filtro['finca_id'] != 0) {
                $srt = "vgpc.finca_id in (" . $filtro['finca_id'] . ")";
            }
        } else {
            if (array_key_exists('finca_id', $filtro)) {
                if ($filtro['finca_id'] != 0) {
                    $srt = "vgpc.finca_id in (" . $filtro['finca_id'] . ")";
                } else {
                    $srt = "vgpc.finca_id in (" . $filtro['session_finca'] . ")";
                }
            } else {
                $srt = "vgpc.finca_id in (" . $filtro['session_finca'] . ")";
            }
        }
        if (isset($srt)) {
            $this->db->where($srt);
        }
        if($dataConsulta){
            $this->db->where("vgpc.orden_id", $orden_id);
            $this->db->where("vgpc.longitud", $dataConsulta['longitud']);
           // $this->db->where("vgpc.precioxcaja", $dataConsulta['precioxcaja']);
        }
        
        $this->filtrar($filtro);
        

        $arr = $this->retornarMuchosSinPaginacion(true);
        return $arr;
    }
    public function reporteVentasDesglosadoSKU_($filtro){
        $arrRango = explode(" - ", $filtro['rango_busqueda']);
        $fechaIni = date_format(DateTime::createFromFormat(FORMATO_FECHA, convertirFechaBD($arrRango[0])), 'Y-m-d 00:00:00');
        $fechaFin = date_format(DateTime::createFromFormat(FORMATO_FECHA, convertirFechaBD($arrRango[1])), 'Y-m-d 23:59:59');
        $this->db->select("CASE
        WHEN ((vgpc.totaltinturadoxcaja > 0) AND (vgpc.totalnaturalesxcaja = 0)) THEN 'T'::text
        WHEN ((vgpc.totalnaturalesxcaja > 0) AND (vgpc.totaltinturadoxcaja = 0)) THEN 'N'::text
        ELSE 'M'::text
        END AS tipo_caja_tnm,
        vgpc.store_id,vgpc.alias,vgpc.store_name,
        vgpc.nombre,vgpc.tipo_caja,vgpc.longitud,vgpc.finca_id,vgpc.finca_id,vgpc.orden_id,
        vgpc.titulo,
        (vgpc.total_stems)as total_stems,
        (vgpc.precioxcaja)as precio_total_cajas,
        (vgpc.totaltinturadoxcaja)as total_tinturados,
        (vgpc.totalnaturalesxcaja)as total_naturales,
        count(vgpc.orden_id)as total_cajas");

        $this->db->from('produccion.v_general_precio_desglosado vgpc');
        $arrayfinca = explode(",", $filtro['session_finca']);
        if (in_array(FINCA_ROSAHOLICS_ID, $arrayfinca)) {
            if ($filtro['finca_id'] != 0) {
                $srt = "vgpc.finca_id in (" . $filtro['finca_id'] . ")";
            }
        } else {
            if (array_key_exists('finca_id', $filtro)) {
                if ($filtro['finca_id'] != 0) {
                    $srt = "vgpc.finca_id in (" . $filtro['finca_id'] . ")";
                } else {
                    $srt = "vgpc.finca_id in (" . $filtro['session_finca'] . ")";
                }
            } else {
                $srt = "vgpc.finca_id in (" . $filtro['session_finca'] . ")";
            }
        }
        if (isset($srt)) {
            $this->db->where($srt);
        }
        $this->filtrar($filtro);
        //$this->db->where("'2022-01-11' BETWEEN vgpc.fecha_inicio_vigencia and vgpc.fecha_fin_vigencia");
        $this->db->group_by(array( ' vgpc.store_id','vgpc.alias','vgpc.store_name',
        'vgpc.nombre','vgpc.tipo_caja','vgpc.longitud','vgpc.finca_id','vgpc.finca_id','vgpc.total_stems','vgpc.precioxcaja',
				'vgpc.totaltinturadoxcaja','vgpc.totalnaturalesxcaja','vgpc.orden_id','vgpc.titulo'));
        $arr = $this->retornarMuchosSinPaginacion(true);
        return $arr;
    }
    private function filtrar($filtro) {
       
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
                    $arrSelect = array('vgpc.fecha_carguera >= ' => $fechaIni, 'vgpc.fecha_carguera <= ' => $fechaFin);
                    break;
                case 1://entrega
                    $arrSelect = array('vgpc.fecha_entrega >= ' => $fechaIni, 'vgpc.fecha_entrega <= ' => $fechaFin);
                    break;
                case 2://compra
                    $arrSelect = array('vgpc.fecha_entrega >= ' => $fechaIni, 'vgpc.fecha_entrega <= ' => $fechaFin);
                    break;
                default:
                    die;
                    break;
            }

            $this->db->where($arrSelect);
        }
    }
}