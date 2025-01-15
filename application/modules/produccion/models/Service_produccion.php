<?php

class Service_produccion extends My_Model {

    public function obtenerTiendas($id = false, $session_finca = false) {
        $this->db->select('s.*');
        $this->db->from('ecommerce.store s');

        $arrayfinca = explode(",", $session_finca);

        if ($id) {
            $this->db->where('s.estado', ESTADO_ACTIVO);
            $this->db->where('id', $id);
            return $this->retornarUno();
         } else if (!in_array(FINCA_ROSAHOLICS_ID,$arrayfinca)) {
            $this->db->join('general.store_tipo_finca stf', 'stf.store_id = s.id', 'left');
            $this->db->join('general.finca f ', ' f.tipo_finca = stf.tipo_finca', 'left');
            $this->db->where('s.estado', ESTADO_ACTIVO);
            $this->db->where('f.estado', ESTADO_ACTIVO);
            $srt = "f.id in (".$session_finca.")";
            $this->db->where($srt);
            return $this->retornarMuchosSinPaginacion(true);
        } else {
            $this->db->where('s.estado', ESTADO_ACTIVO);
            return $this->retornarMuchosSinPaginacion();
        }
    }

    public function obtenerTiendasSel() {
        $session_finca = $this->session->userFincaId;
        $tiendas = $this->obtenerTiendas($id = false, $session_finca);
        return $this->retornarSel($tiendas, "store_name");
    }

    public function colores() {
        return array(
            "Amarillo" => 0,
            "Blanco" => 0,
            "Coral" => 0,
            "Crema" => 0,
            "Durazno" => 0,
            "Rojo" => 0,
            "Morado" => 0,
            "Novelty" => 0,
            "Naranja" => 0,
            "Lavanda" => 0,
            "Rosado" => 0,
            "Salmon" => 0,
            "Verde" => 0,
            "Bicolor-Amarillo" => 0,
            "Bicolor-Naranja" => 0,
            "Bicolor-Rosado" => 0,
            "Mix" => 0,
        );
    }

}
