<?php

class Usuario_model extends My_Model {

    public function ingresarNuevoUsuario($data) {

        $datos = array(
            "nombre" => $data['email'],
            "usuario" => $data['email'],
            "correo" => $data['email'],
            "password" => $data['password'],
            "estado" => 'A');

        return $this->ingresar("usuario", $datos);
    }

    public function existeUsuario($id, $email, $password = false) {
        $this->db->select('u.*');
        $this->db->from((!DB_CON_ESQUEMAS ? '' : 'seguridad.') . 'usuario u');
        //$this->db->join((!DB_CON_ESQUEMAS ? '' : 'general.') . 'finca f', "u.finca_id = f.id");


        if ($id) {
            $this->db->where('id', $id);
        }
        if ($email) {
            $this->db->where('usuario', $email);
        }
        if ($password) {
            $this->db->where('password', $password);
        }
        return $this->retornarUno();
    }

    public function obtenerUsuarioPerfiles($id, $traerid = false) {
        if ($traerid) {
            $this->db->select('p.id');
        } else {
            $this->db->select('p.name');
        }
        $this->db->from('seguridad.usuario_perfil up');
        $this->db->join('seguridad.perfil p', 'up.perfil_id = p.id', 'left');
        $this->db->where('up.usuario_id', $id);
        $this->db->where('up.estado', ESTADO_ACTIVO);
        $arr = $this->retornarMuchos();
        return $arr;
    }
    public function obtenerUsuarioFincas($id, $traerid = false) {
        if ($traerid) {
            $this->db->select('f.id');
        } else {
            $this->db->select('f.nombre');
        }
        $this->db->from('seguridad.usuario_finca uf');
        $this->db->join('general.finca f', 'uf.finca_id = f.id', 'left');
        $this->db->where('uf.usuario_id', $id);
        $this->db->where('uf.estado', ESTADO_ACTIVO);
        $arr = $this->retornarMuchos();
        return $arr;
    }

    public function existeUsuarioCorreo($email) {
        return $this->existeUsuario(false, $email);
    }

    public function existeUsuarioId($id) {
        return $this->existeUsuario($id, false);
    }

    public function validarUsuario($email, $password) {
        return $this->existeUsuario(false, $email, $password);
    }

}

?> 