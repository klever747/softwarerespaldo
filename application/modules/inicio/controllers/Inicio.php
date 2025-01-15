<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Inicio extends MY_Controller {

    function __construct() {
        parent::__construct();
    }

    public function index() {
        $data = array();
        if (isset($_GET['error'])) {
            echo $_GET['error'];
            $data['error'] = urldecode($_GET['error']);
        }

        if ($this->session->userdata("logeado") == null) {
            $this->ingresoSistema($data);
        } else {
            $this->homepage();
        }
    }

    function ingresoSistema($data = array()) {
        $this->load->model("seguridad/usuario_model");

        if ($this->input->post("usuario") != null) {
            $email = $this->input->post("usuario");
            $password = $this->input->post("password");
            $usuario = $this->usuario_model->validarUsuario($email, md5($password));

            if ($usuario) {
                $this->session->set_userdata('logeado', true);
                $this->session->set_userdata('userId', $usuario->id);
                $this->session->set_userdata('userName', $usuario->usuario);
                $this->session->set_userdata('userEmail', $usuario->correo);
                $perfiles = $this->usuario_model->obtenerUsuarioPerfiles($usuario->id);
                $perfilesid = $this->usuario_model->obtenerUsuarioPerfiles($usuario->id, true);
                $fincas = $this->usuario_model->obtenerUsuarioFincas($usuario->id);
                $fincasid = $this->usuario_model->obtenerUsuarioFincas($usuario->id, true);
                $array_finca = array();
                foreach($fincasid as $item){
                    $thearray = (array) $item;
                    $array_finca[] = $thearray['id'];
                 }                
                $fincasid = implode(",", $array_finca);
                $this->session->set_userdata('userPerfil', $perfiles);
                $this->session->set_userdata('userPerfilId', $perfilesid);
                $this->session->set_userdata('userFinca', $fincas);
                $this->session->set_userdata('userFincaId', $fincasid);
                redirect(base_url());
                return;
            } else {
                $data['error'] = 'No existe un usuario registrado con la informaci&oacute;n ingresada';
            }
        }
        $data['permtirAsociacion'] = $this->session->userdata("permitirAsociacion");
        $data['claseBody'] = "hold-transition login-page";
        $this->mostrarVista('ingreso_sistema.php', $data);
    }

    public function homepage() {
//        $data['claseBody'] = "hold-transition";
//        $data['session_username'] = $this->session->userdata("userName");
//        $this->mostrarVista('homepage.php', $data);
        redirect(base_url("dashboard"));
    }

}

?>