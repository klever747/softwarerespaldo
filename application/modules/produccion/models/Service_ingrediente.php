<?php

class Service_ingrediente extends My_Model {

    public function obtenerNuevoIngrediente() {
        return (object) [
                    'nombre' => '',
                    'descripcion' => '',
                    'tipo' => '',
                    'longitud' => '',
                    'comentarios' => '',
                    'estado' => ESTADO_ACTIVO,
        ];
    }

    public function obtenerIngrediente($id = false, $estado = false, $texto_busqueda = false) {
        $this->db->select('i.id,i.nombre, i.descripcion, i.tipo, i.longitud, i.estado');
        $this->db->from('produccion.ingrediente i');

        if ($id) {
            $this->db->where('i.id', $id);
            return $this->retornarUno();
        }

        if ($estado) {
            $this->db->where('i.estado', $estado);
        }
        if ($texto_busqueda) {
            $this->db->where(" (UPPER(i.nombre) LIKE '%" . strtoupper($texto_busqueda) . "%' "
                    . "OR UPPER(i.descripcion) LIKE '%" . strtoupper($texto_busqueda) . "%' )");
        }

        $this->db->order_by('i.nombre', 'ASC');

        $conteo = $this->retornarConteo();
        $arr = $this->retornarMuchos();

        return array($arr, $conteo);
    }

    public function crearIngrediente($obj) {

        error_log("Vamos a crear una Tienda");
        $id = $this->ingresar("produccion.ingrediente", $obj, true, true);
        if ($id) {
            $dato_log = array(
                "ingrediente_id" => $id,
                "accion" => "creacion de un nuevo ingrediente" . json_encode($obj),
            );
            $this->registrarLog("produccion.ingrediente_log", $dato_log);
        }
        return $id;
    }

    public function actualizarIngrediente($obj) {
        $id = $this->actualizar("produccion.ingrediente", $obj, "id", true);
        if ($id) {
            $dato_log = array(
                "ingrediente_id" => $obj['id'],
                "accion" => "actualizacion del ingrediente" . json_encode($obj),
            );
            $this->registrarLog("produccion.ingrediente_log", $dato_log);
        }
        return $id;
    }

    public function eliminarIngrediente($obj) {
        $id = $this->actualizar("produccion.ingrediente", $obj, "id", true);
        if ($id) {
            $dato_log = array(
                "ingrediente_id" => $obj['id'],
                "accion" => "eliminacion del ingrediente" . json_encode($obj),
            );
            $this->registrarLog("produccion.ingrediente_log", $dato_log);
        }
        return $id;
    }

    public function buscarIngredienteRepetido($id = false, $nombre, $descripcion, $longitud) {
        $this->db->select('i.id,i.nombre, i.descripcion,i.longitud');
        $this->db->from('produccion.ingrediente i');
        $this->db->where('i.nombre', $nombre);
        $this->db->where('i.descripcion', $descripcion);
        $this->db->where('i.longitud', $longitud);
        if ($id) {
            $this->db->where('i.id !=' . $id);
        }
        $arr = $this->retornarMuchos();
        return $arr;
    }

}

?>