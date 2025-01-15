<?php

class Service_dashboard extends My_Model {

    public function resumenOrdenes() {
        return true;
    }

    public function ordenDiaria($fecha, $fechaFin, $tipo_calendario, $estado = false, $reenvio = false) {
        $finca = $this->session->userFincaId;
        switch ($tipo_calendario) {
            case 0://carguera
                $arrSelect = array('o.fecha_carguera >= ' => $fecha, 'o.fecha_carguera <= ' => $fechaFin);
                break;
            case 1://entrega
                $arrSelect = array('o.fecha_entrega >= ' => $fecha, 'o.fecha_entrega <= ' => $fechaFin);
                break;
        }
        $this->db->select('DISTINCT(o.id)');
        $this->db->from('ecommerce.orden o');
        $this->db->join('ecommerce.orden_caja oc', 'o.id = oc.orden_id ', 'left');
        $this->db->join('ecommerce.finca_caja fc', "fc.orden_caja_id = oc.id AND fc.estado = '" . ESTADO_ACTIVO . "'", 'left');
        if ($estado) {
            $this->db->where('o.estado', $estado);
        }
        $this->db->where('oc.estado', ESTADO_ACTIVO);

        if ($reenvio) {
            $this->db->where('o.reenvio_orden_id is NOT NULL', null, false);
        }
        $arrayfinca = explode(",", $finca);
        if (!in_array(FINCA_ROSAHOLICS_ID, $arrayfinca)) {
            $srt = "fc.finca_id in (" . $finca . ")";
            $this->db->where($srt);
        }

        $this->db->group_by('o.id');
        $this->db->where($arrSelect);
        $total = $this->retornarConteo(true);
        return $total;
    }

    public function ordensintraking($fecha, $fechaFin, $tipo_calendario) {
        $finca = $this->session->userFincaId;
        switch ($tipo_calendario) {
            case 0://carguera
                $arrSelect = array('o.fecha_carguera >= ' => $fecha, 'o.fecha_carguera <= ' => $fechaFin);
                break;
            case 1://entrega
                $arrSelect = array('o.fecha_entrega >= ' => $fecha, 'o.fecha_entrega <= ' => $fechaFin);
                break;
        }
        $this->db->select('o.*');
        $this->db->from('ecommerce.orden o');
        $this->db->join('ecommerce.orden_caja oc', 'o.id = oc.orden_id', 'left');
        $this->db->join('ecommerce.finca_caja fc', "fc.orden_caja_id = oc.id AND fc.estado = '" . ESTADO_ACTIVO . "'", 'left');
        $this->db->join('logistica.orden_caja_tracking_number octn', 'oc.id = octn.orden_caja_id', 'left');
        $this->db->where("(octn.tracking_number is NULL OR octn.estado =  '" . ESTADO_INACTIVO . "')");
        $this->db->where('o.estado', ESTADO_ACTIVO);
        $this->db->where('oc.estado', ESTADO_ACTIVO);
        $this->db->where($arrSelect);

        $arrayfinca = explode(",", $finca);
        if (!in_array(FINCA_ROSAHOLICS_ID, $arrayfinca)) {
            $srt = "fc.finca_id in (" . $finca . ")";
            $this->db->where($srt);
        }
        $total = $this->retornarConteo(true);
        return $total;
    }

    public function ordenbonchada($fecha, $fechaFin, $tipo_calendario, $estado) {
        $finca = $this->session->userFincaId;
        switch ($tipo_calendario) {
            case 0://carguera
                $arrSelect = array('o.fecha_carguera >= ' => $fecha, 'o.fecha_carguera <= ' => $fechaFin);
                break;
            case 1://entrega
                $arrSelect = array('o.fecha_entrega >= ' => $fecha, 'o.fecha_entrega <= ' => $fechaFin);
                break;
        }
        $this->db->select('o.*');
        $this->db->from('ecommerce.orden o');
        $this->db->join('ecommerce.orden_item oi', 'o.id = oi.orden_id', 'left');
        $this->db->join('ecommerce.orden_caja oc', 'o.id = oc.orden_id', 'left');
        $this->db->join('ecommerce.finca_caja fc', "fc.orden_caja_id = oc.id AND fc.estado = '" . ESTADO_ACTIVO . "'", 'left');
        $this->db->where('o.estado', ESTADO_ACTIVO);
        $this->db->where('oc.estado', ESTADO_ACTIVO);
        $this->db->where($arrSelect);
        $this->db->where('oi.estado', ESTADO_ACTIVO);
        $this->db->where('oi.preparado', $estado);

        $arrayfinca = explode(",", $finca);
        if (!in_array(FINCA_ROSAHOLICS_ID, $arrayfinca)) {
            $srt = "fc.finca_id in (" . $finca . ")";
            $this->db->where($srt);
        }
        $total = $this->retornarConteo(true);
        return $total;
    }

    public function ordenvestida($fecha, $fechaFin, $tipo_calendario, $estado) {
        $finca = $this->session->userFincaId;
        switch ($tipo_calendario) {
            case 0://carguera
                $arrSelect = array('o.fecha_carguera >= ' => $fecha, 'o.fecha_carguera <= ' => $fechaFin);
                break;
            case 1://entrega
                $arrSelect = array('o.fecha_entrega >= ' => $fecha, 'o.fecha_entrega <= ' => $fechaFin);
                break;
        }
        $this->db->select('o.*');

        $this->db->from('ecommerce.orden o');
        $this->db->join('ecommerce.orden_item oi', 'o.id = oi.orden_id', 'left');
        $this->db->join('ecommerce.orden_caja oc', 'o.id = oc.orden_id', 'left');
        $this->db->join('ecommerce.finca_caja fc', "fc.orden_caja_id = oc.id AND fc.estado = '" . ESTADO_ACTIVO . "'", 'left');

        $this->db->where('o.estado', ESTADO_ACTIVO);
        $this->db->where('oc.estado', ESTADO_ACTIVO);
        $this->db->where($arrSelect);
        $this->db->where('oi.estado', ESTADO_ACTIVO);
        $this->db->where('oi.terminado', $estado);

        $arrayfinca = explode(",", $finca);
        if (!in_array(FINCA_ROSAHOLICS_ID, $arrayfinca)) {
            $srt = "fc.finca_id in (" . $finca . ")";
            $this->db->where($srt);
        }
        $total = $this->retornarConteo(true);
        return $total;
    }

    public function cajasdiaria($fecha, $fechaFin, $tipo_calendario) {
        $finca = $this->session->userFincaId;
        switch ($tipo_calendario) {
            case 0://carguera
                $arrSelect = array('o.fecha_carguera >= ' => $fecha, 'o.fecha_carguera <= ' => $fechaFin);
                break;
            case 1://entrega
                $arrSelect = array('o.fecha_entrega >= ' => $fecha, 'o.fecha_entrega <= ' => $fechaFin);
                break;
        }
        $this->db->select('tc.*');
        $this->db->from('ecommerce.orden o');
        $this->db->join('ecommerce.orden_caja oc', 'o.id = oc.orden_id', 'left');
        $this->db->join('ecommerce.finca_caja fc', "fc.orden_caja_id = oc.id AND fc.estado = '" . ESTADO_ACTIVO . "'", 'left');
        $this->db->join('ecommerce.tipo_caja tc', 'oc.tipo_caja_id = tc.id', 'left');
        $this->db->where('o.estado', ESTADO_ACTIVO);
        $this->db->where('oc.estado', ESTADO_ACTIVO);
        $this->db->where($arrSelect);
        $this->db->where('oc.id is NOT NULL', NULL, FALSE);

        $arrayfinca = explode(",", $finca);
        if (!in_array(FINCA_ROSAHOLICS_ID, $arrayfinca)) {
            $srt = "fc.finca_id in (" . $finca . ")";
            $this->db->where($srt);
        }
        $total = $this->retornarConteo(true);
        return $total;
    }

    public function cajasindefinida($fecha, $fechaFin, $tipo_calendario) {
        $finca = $this->session->userFincaId;
        switch ($tipo_calendario) {
            case 0://carguera
                $arrSelect = array('o.fecha_carguera >= ' => $fecha, 'o.fecha_carguera <= ' => $fechaFin);
                break;
            case 1://entrega
                $arrSelect = array('o.fecha_entrega >= ' => $fecha, 'o.fecha_entrega <= ' => $fechaFin);
                break;
        }
        $this->db->select('oc.*');

        $this->db->from('ecommerce.orden o');
        $this->db->join('ecommerce.orden_caja oc', 'o.id = oc.orden_id', 'left');
        $this->db->join('ecommerce.finca_caja fc', "fc.orden_caja_id = oc.id AND fc.estado = '" . ESTADO_ACTIVO . "'", 'left');
        $this->db->where('o.estado', ESTADO_ACTIVO);
        $this->db->where($arrSelect);
        $this->db->where('oc.tipo_caja_id', CAJA_NODEFINIDA_ID);
        $this->db->where('oc.estado', ESTADO_ACTIVO);

        $arrayfinca = explode(",", $finca);
        if (!in_array(FINCA_ROSAHOLICS_ID, $arrayfinca)) {
            $srt = "fc.finca_id in (" . $finca . ")";
            $this->db->where($srt);
        }
        $total = $this->retornarConteo(true);
        return $total;
    }

    public function fincanodefinida($fecha, $fechaFin, $tipo_calendario) {
        $finca = $this->session->userFincaId;
        switch ($tipo_calendario) {
            case 0://carguera
                $arrSelect = array('o.fecha_carguera >= ' => $fecha, 'o.fecha_carguera <= ' => $fechaFin);
                break;
            case 1://entrega
                $arrSelect = array('o.fecha_entrega >= ' => $fecha, 'o.fecha_entrega <= ' => $fechaFin);
                break;
        }
        $this->db->select('o.*');
        $this->db->from('ecommerce.orden o');
        $this->db->join('ecommerce.orden_caja oc', 'o.id = oc.orden_id', 'left');
        $this->db->join('ecommerce.finca_caja fc', "fc.orden_caja_id = oc.id AND fc.estado = '" . ESTADO_ACTIVO . "'", 'left');
        $this->db->where('o.estado', ESTADO_ACTIVO);
        $this->db->where('oc.estado', ESTADO_ACTIVO);
        $this->db->where('fc.finca_id', FINCA_ROSAHOLICS_ID);
        $this->db->where($arrSelect);


        $arrayfinca = explode(",", $finca);
        if (!in_array(FINCA_ROSAHOLICS_ID, $arrayfinca)) {
            $srt = "fc.finca_id in (" . $finca . ")";
            $this->db->where($srt);
        }
        $total = $this->retornarConteo(true);
        return $total;
    }

}
