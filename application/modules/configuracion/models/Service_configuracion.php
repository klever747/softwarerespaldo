<?php

class Service_configuracion extends My_Model {

    public function obtenerNuevoMenu() {
        return (object) [
                    'nombre' => '',
                    'icono' => '',
                    'url' => '',
                    'estado' => ESTADO_ACTIVO,
                    'padre_id' => 0
        ];
    }

    public function obtenerListaMenu($id = false, $estado = false, $texto_busqueda = false, $padre_id = false) {
        $this->db->select('p.*');
        $this->db->from('seguridad.menu p');

        if ($id) {
            $this->db->where('p.id', $id);
            return $this->retornarUno();
        }

        if ($padre_id) {
            return $this->retornarMuchos();
        }

        if ($estado) {
            $this->db->where('p.padre_id', 0);
            $this->db->where('p.estado', $estado);
        } else {
            $this->db->where('p.padre_id', 0);
        }
        if ($texto_busqueda) {
            $this->db->where(" (UPPER(p.nombre) LIKE '%" . strtoupper($texto_busqueda) . "%' "
                    . "OR UPPER(p.icono) LIKE '%" . strtoupper($texto_busqueda) . "%' "
                    . "OR UPPER(p.url) LIKE '%" . strtoupper($texto_busqueda) . "%')");
            $this->db->where('p.padre_id', 0);
        }
        $this->db->order_by('p.nombre', 'ASC');

        $conteo = $this->retornarConteo();
        $arr = $this->retornarMuchos();

        return array($arr, $conteo);
    }

    public function obtenerVariantesMenu($menu_id, $estado = false) {
        $this->db->select('mv.*');
        $this->db->from('seguridad.menu mv');
        if ($menu_id) {
            $this->db->where('padre_id', $menu_id);
        }
        if ($estado) {
            $this->db->where('estado', $estado);
        }
        $this->db->order_by('mv.id', 'ASC');
        return $this->retornarMuchosSinPaginacion();
//        $conteo = $this->retornarConteo();
//        $arr = $this->retornarMuchos();
//        return array($arr, $conteo);
    }

    public function crearMenu($obj) {
        error_log("Creacion de menu");
        $id = $this->ingresar("seguridad.menu", $obj, true, true);
        if ($id) {
            $dato_log = array(
                "menu_id" => $id,
                "accion" => "creacion de menu" . json_encode($obj),
            );
            $this->registrarLog("seguridad.menu_log", $dato_log);
        }
        return $id;
    }

    public function actualizarMenu($obj) {
        $id = $this->actualizar("seguridad.menu", $obj, "id", true);
        if ($id) {
            $dato_log = array(
                "menu_id" => $obj['id'],
                "accion" => "Actualizacion de menu" . json_encode($obj),
            );
            $this->registrarLog("seguridad.menu_log", $dato_log);
        }
        return $id;
    }

    public function obtenerMenuSelect($id = false) {
        $this->db->select('s.*');
        $this->db->from('seguridad.menu s');
        $this->db->where('s.estado', ESTADO_ACTIVO);
        if ($id) {
            $this->db->where('id', $id);
            $this->db->order_by('s.nombre', 'ASC');
            return $this->retornarUno();
        } else {
            $this->db->order_by('s.nombre', 'ASC');
            return $this->retornarMuchosSinPaginacion();
        }
    }

    public function obtenerMenuSel() {
        $menuSelect = $this->obtenerMenuSelect();
        return $this->retornarSel($menuSelect, "nombre");
    }

}
