<?php

class My_Model extends CI_Model {

    public $paginacion;

    public function warning_handler($errno, $errstr, $errfile, $errline, array $errcontex) {
        //error_log("Warning handler");
    }

    function __construct() {
        parent::__construct();
        $this->paginacion = $this->obtenerPaginacion();
    }

    public function registrarLog($tabla, $datos) {
        $this->ingresar($tabla, $datos, false, true);
    }

    public function obtenerPaginacion($pagina = 1) {
        $limit = 50;
        $offset = 0;
        if (($this->input->post('paginacion_pag') != null) && (!empty($this->input->post('paginacion_pag')))) {
            $pagina = $this->input->post('paginacion_pag');
        }
        if ($this->input->post('regpp') != null) {
            $limit = $this->input->post('regpp');
            $offset = $limit * ($pagina - 1);
        }
        return array("limit" => $limit, "offset" => $offset, "pagina" => $pagina);
    }

    public function retornarConteo($encerar = false) {

        //$this->db->limit($this->paginacion['limit'], $this->paginacion['offset']);

        return $this->db->count_all_results('', $encerar);
    }

    public function retornarUno($mostrarSql = false) {
        $resultado = $this->db->get();

        if ($mostrarSql) {
            error_log(print_r($this->db->last_query(), true));
        }

        if ($resultado->num_rows() == 1) {
            return $resultado->row();
        }
        return false;
    }

    public function retornarMuchosSinPaginacion($mostrarSql = false) {
        return $this->retornarMuchos(false, $mostrarSql);
    }

    public function retornarMuchosConPaginacion($mostrarSql = false) {
        return $this->retornarMuchos(true, $mostrarSql);
    }

    public function retornarMuchos($paginados = true, $mostrarSql = false) {
        if ($paginados) {
            if ($this->paginacion['limit'] > 0) {
                $this->db->limit($this->paginacion['limit'], $this->paginacion['offset']);
            }
        }
        $resultado = $this->db->get();

        if ($mostrarSql) {
            error_log(print_r($this->db->last_query(), true));
        }
        if ($resultado->num_rows() > 0) {
            return $resultado->result();
        }
        return false;
    }

    public function ingresar($tabla, $datos, $retornar_id = false, $log = false) {
        if ($log) {
            $datos['creacion_fecha'] = fechaActual();
            $datos['creacion_usu'] = (key_exists('creacion_usu', $datos)) ? $datos['creacion_usu'] : $this->session->userdata("userId");
            $datos['creacion_ip'] = $this->input->ip_address();
        }
        //  error_log(print_r($tabla, true));
        //  error_log(print_r($datos, true));
        $respuesta = $this->db->insert($tabla, $datos);
        
      // error_log(print_r($this->db->last_query(), true));

        if ($respuesta) {
            if ($retornar_id) {
                $last_id = $this->db->insert_id();
                $last_id = $this->db->insert_id($tabla . '_seq');
                return $last_id;
            }
            return true;
        } else {
            //error_log("ERROR DURANTE INSERT: " . print_r($this->db->last_query(), true));
            return false;
        }
    }

    public function actualizar($tabla, $datos, $id = "id", $log = false) {
        if ($log) {
            $datos['actualizacion_fecha'] = fechaActual();
            $datos['actualizacion_usu'] = $this->session->userdata("userId");
            $datos['actualizacion_ip'] = $this->input->ip_address();
        }

        //error_log($id);
        if (!$id) {
            $id = "id";
        }
        if (is_array($id)) {
            $condicion_actualizacion = array();
            foreach ($id as $k => $v) {
                $condicion_actualizacion[$k] = ($v == '-1' ? $datos[$k] : $v);
            }
        } else {
            $condicion_actualizacion = $id . " = " . $datos[$id];
        }
        $respuesta = $this->db->update($tabla, $datos, $condicion_actualizacion);
//        if (ENVIRONMENT !== 'production') {
//        error_log(print_r($this->db->last_query(), true));
//        die;
//        }
        if ($respuesta) {
            return true;
        } else {
            //error_log("ERROR DURANTE UPDATE: " . print_r($this->db->last_query(), true));
            return false;
        }
    }

    public function retornarSel($arrDatos, $nombre = "nombre", $simple = true) {
        $arr = array();
        if ($arrDatos) {
            if ($simple) {
                foreach ($arrDatos as $dato) {
                    $arr[$dato->id] = $dato->{$nombre};
                }
            } else{
                foreach ($arrDatos as $dato) {
                    $arr[] = array("clave" => $dato->id, "valor" => $dato->{$nombre});
                }
            }
        }
        return $arr;
    }
    public function retonarSelConData($arrDatos, $nombre = "nombre") {
        $arr = array();
        if ($arrDatos) {
            foreach ($arrDatos as $dato) {
                $data_arr = array();
                foreach($dato as $key=>$val){
                    if (($key != "id") &&($key != $nombre)){
                        $data_arr[$key] = $val;
                    }
                }
                $arr[] = array("clave" => $dato->id, "valor" => $dato->{$nombre}, "arr_data" => $data_arr);
            }
        }
        return $arr;
    }
    /*     * ************************** MENU ******************* */

    public function obtenerMenu($id, $padre_id, $estado = ESTADO_ACTIVO) {
        $this->db->select('u.*, p.name as perfil');
        $this->db->from((!DB_CON_ESQUEMAS ? '' : 'seguridad.') . 'usuario u');
        $this->db->join((!DB_CON_ESQUEMAS ? '' : 'seguridad.') . 'perfil p', "u.perfil_id = p.id");

        if ($id) {
            $this->db->where('id', $id);
        }
        if ($email) {
            $this->db->where('correo', $email);
        }
        if ($password) {
            $this->db->where('password', $password);
        }

        return $this->retornarUno();
    }

    public function obtenerMenuDelPerfil($perfil_id, $estado = ESTADO_ACTIVO) {
        
        $this->db->select('m.*');
        $this->db->from((!DB_CON_ESQUEMAS ? '' : 'seguridad.') . 'menu m');
        $this->db->join((!DB_CON_ESQUEMAS ? '' : 'seguridad.') . 'perfil_menu pm', "m.id = pm.menu_id");

        if ($perfil_id) {
            foreach($perfil_id as $perfil){ 
                $this->db->or_where('pm.perfil_id', $perfil->id);
            }
        }
        if ($estado) {
            $this->db->where('m.estado', $estado);
            $this->db->where('pm.estado', $estado);
        }
        $this->db->order_by('pm.posicion', 'DESC');
        return $this->retornarMuchosSinPaginacion();
    }

    /**
     * En base al perfil se obtiene las opciones que posee
     * @param type $idPerfil
     */
    public function armarMenu($idPerfil) {
        $menuCabeceras = array();
        $arr_menu = $this->obtenerMenuDelPerfil($idPerfil);
        //como trae objectos repetidos por los perfiles, le sacos los objectos duplicados
        $arr_menu =  array_unique($arr_menu, SORT_REGULAR);

        if ($arr_menu) {
            foreach ($arr_menu as $op) {
                if ($op->padre_id == 0) {
                    if (!array_key_exists($op->id, $menuCabeceras)) {
                            $menuCabeceras[$op->id] = array("nombre" => $op->nombre, "icono" => $op->icono, "url" => $op->url, "opciones" => array());                       
                    } else {
                        $menuCabeceras[$op->id]["nombre"] = $op->nombre;
                        $menuCabeceras[$op->id]["icono"] = $op->icono;
                        $menuCabeceras[$op->id]["url"] = $op->url;
                    }
                } else {
                    if (!array_key_exists($op->padre_id, $menuCabeceras)) {

                        $menuCabeceras[$op->padre_id] = array("opciones" => array());

                    }
                    $menuCabeceras[$op->padre_id]['opciones'][] = array("nombre" => $op->nombre, "icono" => $op->icono, "url" => $op->url, "opciones" => array());
                }
            }
        }
        return $menuCabeceras;
    }

}
