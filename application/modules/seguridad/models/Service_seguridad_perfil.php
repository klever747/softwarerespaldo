<?php

class Service_seguridad_perfil extends My_Model {

    public function obtenerNuevoPerfil() {
        return (object) [
                    'name' => '',
                    'estado' => ESTADO_ACTIVO,
        ];
    }

    public function existePerfil($objPerfil) {
        $this->db->select('p');
        $this->db->from('seguridad.perfil');
        $this->db->where('name', $objPerfil['name']);
        return $this->retornarUno();
    }

    public function obtenerPerfil($id = false, $estado = false, $texto_busqueda = false) {
        $this->db->select('*');
        $this->db->from('seguridad.perfil');

        if ($id) {
            $this->db->where('id', $id);
            return $this->retornarUno();
        }
        if ($estado) {
            $this->db->where('estado', $estado);
        }
        if ($texto_busqueda) {
            $this->db->where(" (UPPER(name) LIKE '%" . strtoupper($texto_busqueda) . "%')");
        }
        $this->db->order_by('name', 'ASC');

        $conteo = $this->retornarConteo();
        $arr = $this->retornarMuchos();

        return array($arr, $conteo);
    }

    public function obtenerPerfiles($estado = false) {
        $this->db->select('p.*');
        $this->db->from('seguridad.perfil p');
        if ($estado) {
            $this->db->where('estado', $estado);
        }
        $conteo = $this->retornarConteo();
        $arr = $this->retornarMuchos();

        return array($arr, $conteo);
    }

    public function crearPerfil($obj) {
        $id = $this->ingresar("seguridad.perfil", $obj, true);
        if ($id) {
            $dato_log = array(
                "perfil_id" => $id,
                "accion" => "creacion de perfil" . json_encode($obj),
            );
            $this->registrarLog("seguridad.perfil_log", $dato_log);
        }
        return $id;
    }

    public function actualizarPerfil($obj) {
        $id = $this->actualizar("seguridad.perfil", $obj, "id");
        if ($id) {
            $dato_log = array(
                "perfil_id" => $obj['id'],
                "accion" => "actualizacion de perfil" . json_encode($obj),
            );
            $this->registrarLog("seguridad.perfil_log", $dato_log);
        }
        return $id;
    }

    public function obtenerSelPerfil() {
        $this->db->select("*");
        $this->db->from('seguridad.perfil');
        $this->db->where('estado', ESTADO_ACTIVO);
        $arrDatos = $this->retornarMuchosSinPaginacion();
        return $this->retornarSel($arrDatos, "name");
    }

}
