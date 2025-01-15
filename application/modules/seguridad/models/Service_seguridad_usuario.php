<?php

class Service_seguridad_usuario extends My_Model {

    public function obtenerNuevoUsuario() {
        return (object) [
                    'nombre' => '',
                    'usuario' => '',
                    'correo' => '',
                    'password' => '',
                    'finca_id' => '',
                    'estado' => ESTADO_ACTIVO,
                    'perfil_id' => '',
        ];
    }

    public function existeUsuario($objUsuario) {
        $this->db->select('u.id,u.nombre,u.usuario,u.correo,u.password,u.estado,u.finca_id');
        $this->db->from('seguridad.usuario u');
        $this->db->where('u.nombre', $objUsuario['u.nombre']);
        return $this->retornarUno();
    }

    public function obtenerUsuario($id = false, $estado = false, $texto_busqueda = false) {
        $this->db->select('u.*');
        $this->db->from('seguridad.usuario u');
        if ($id) {
            $this->db->where('u.id', $id);
            return $this->retornarUno();
        }
        if ($estado) {
            $this->db->where('u.estado', $estado);
        }
        if ($texto_busqueda) {
            $this->db->where(" (UPPER(u.usuario) LIKE '%" . strtoupper($texto_busqueda) . "%' "
                    . "OR UPPER(u.nombre) LIKE '%" . strtoupper($texto_busqueda) . "%' "
                    . "OR UPPER(u.correo) LIKE '%" . strtoupper($texto_busqueda) . "%')");
        }

        $this->db->order_by('u.usuario', 'ASC');

        $conteo = $this->retornarConteo();
        $arr = $this->retornarMuchos();
        return array($arr, $conteo);
    }

    public function obtenerUsuarioPerfil($id = false) {
        $this->db->select('*');
        $this->db->from('seguridad.usuario_perfil ');
        $this->db->where('usuario_id', $id);
        $this->db->where('estado', ESTADO_ACTIVO);
        $arr = $this->retornarMuchos();
        return $arr;
    }
    public function obtenerUsuarioFinca($id = false) {
        $this->db->select('*');
        $this->db->from('seguridad.usuario_finca ');
        $this->db->where('usuario_id', $id);
        $this->db->where('estado', ESTADO_ACTIVO);
        $arr = $this->retornarMuchos();
        return $arr;
    }

    public function obtenerUsuarios($estado = false) {
        $this->db->select('p*,pe.perfil_id');
        $this->db->from('seguridad.usuario');
        $this->db->join('seguridad.usuario pe', 'p.usuario_id = pe.id');

        if ($estado) {
            $this->db->where('estado', $estado);
        }
        $conteo = $this->retornarConteo();
        $arr = $this->retornarMuchos();

        return array($arr, $conteo);
    }

    public function crearUsuario($obj) {
        $obj1 = $this->prepararobjeto($obj);

        $id = $this->ingresar("seguridad.usuario", $obj1, true);
        if ($id) {
            $dato_log = array(
                "usuario_id" => $id,
                "accion" => "creacion de usuario" . json_encode($obj),
            );
            $this->registrarLog("seguridad.usuario_log", $dato_log);
        }

        return $id;
    }

    public function crearUsuarioPerfil($obj, $id) {
        $obj2 = $obj['perfil_id'];

        foreach ($obj2 as $new) {
            $dato_perfil = array(
                "usuario_id" => $id,
                "perfil_id" => $new,
                "estado" => ESTADO_ACTIVO,
                "accion" => "creacion de usuario_perfil" . json_encode($obj2),
            );

            $this->registrarLog("seguridad.usuario_perfil", $dato_perfil, true);
        }
        //error_log(print_r($obj2, true));
        return $id;
    }
    public function crearUsuarioFinca($obj, $id) {
        $obj2 = $obj['finca_id'];

        foreach ($obj2 as $new) {
            $dato_perfil = array(
                "usuario_id" => $id,
                "finca_id" => $new,
                "estado" => ESTADO_ACTIVO,
                "accion" => "creacion de usuario_finca" . json_encode($obj2),
            );

            $this->registrarLog("seguridad.usuario_finca", $dato_perfil, true);
        }
        return $id;
    }

    public function actualizarUsuario($obj) {
        $obj1 = $this->prepararobjeto($obj);
        //actualizo la informacion
        $id = $this->actualizar("seguridad.usuario", $obj1, "id");
        //error_log(print_r($id, true)); 
        if ($id) {
            $dato_log = array(
                "usuario_id" => $obj1['id'],
                "accion" => "actualizacion de usuario" . json_encode($obj1),
            );
            $this->registrarLog("seguridad.usuario_log", $dato_log);
        }
        return $id;
    }

    public function actualizarUsuarioPassword($id, $password) {
        $actualizado = $this->actualizar("seguridad.usuario", array("id" => $id, "password" => $password), "id");

        if ($actualizado) {
            $dato_log = array(
                "usuario_id" => $id,
                "accion" => "Cambio de contrasenia",
            );
            $this->registrarLog("seguridad.usuario_log", $dato_log);
        }
        return $id;
    }

    public function actualizarUsuarioPerfil($perfilespost, $obj) {
        $perfilviejo = $this->obtenerUsuarioPerfil($obj['id']);
        //elimino los perfiles de ese usuario
        if ($perfilviejo) {
            foreach ($perfilviejo as $new) {
                $dato_perfil = array(
                    "estado" => ESTADO_INACTIVO,
                );
                $this->actualizar("seguridad.usuario_perfil", $dato_perfil, ["id" => $new->id]);
            }
        }
        // registro los perfiles nuevos
        if ($perfilespost) {
            foreach ($perfilespost as $new2) {
                $dato_perfil2 = array(
                    "usuario_id" => $obj['id'],
                    "perfil_id" => $new2,
                    "estado" => ESTADO_ACTIVO,
                    "accion" => "actualizacion de perfil" . json_encode($obj),
                );
                $this->registrarLog("seguridad.usuario_perfil", $dato_perfil2);
            }
        }
        return $obj;
    }
    public function actualizarUsuarioFinca($fincaspost, $obj) {
        $fincaviejo = $this->obtenerUsuarioFinca($obj['id']);
        //elimino los fincas de ese usuario
        if ($fincaviejo) {
            foreach ($fincaviejo as $new) {
                $dato_finca = array(
                    "estado" => ESTADO_INACTIVO,
                );
                $this->actualizar("seguridad.usuario_finca", $dato_finca, ["id" => $new->id]);
            }
        }
        // registro las fincas nuevos
        if ($fincaspost) {
            foreach ($fincaspost as $new2) {
                $dato_finca2 = array(
                    "usuario_id" => $obj['id'],
                    "finca_id" => $new2,
                    "estado" => ESTADO_ACTIVO,
                    "accion" => "actualizacion de finca" . json_encode($obj),
                );
                $this->registrarLog("seguridad.usuario_finca", $dato_finca2);
            }
        }
        return $obj;
    }

    public function persistenciaUsuario($obj) {
        $id = false;
        $usuario = $this->existeUsuario($obj);

        if (!$usuario) {
            $nu = 1;
            $obj["estado"] = ESTADO_ACTIVO;
            $id = $this->crearUsuario($obj, true);
        } else {
            $nu = 2;
            $id = $usuario->id;
        }
        return array($nu, $id);
    }

    public function prepararobjeto($arr) {
        unset($arr['perfil_id']);
        unset($arr['finca_id']);
        return $arr;
    }

}
