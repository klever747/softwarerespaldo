<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller {

    public $paginacion;

    public function __construct() {
        parent::__construct();
        $this->debeEstarLogeado();
        $this->load->model("generales/service_general");
        $this->paginacion = $this->service_general->paginacion;
    }

    public function marcar($anuncio) {
        $datetime_variable = new DateTime();
        error_log(print_r(date_format($datetime_variable, 'Y-m-d H:i:s'), true) . " " . $anuncio);
    }

    /**
     * Verifica que estemos en un lugar del website que debe tener login obligaotiramente
     */
    private function debeEstarLogeado() {
        if (($this->uri->segment(1) != null) && (substr($this->uri->segment(2), 0, 3) != "ws_") && (substr($this->uri->segment(2), 0, 3) != "scr_") && (!(($this->uri->segment(1) == "inicio") && ($this->uri->segment(2) == "ingresoSistema")))) {
            if (!$this->logeado()) {
                redirect(base_url());
            }
        }
    }

    public function logeado() {
        if ($this->session->userdata("logeado")) {
            return true;
        }
        return false;
    }

    public function cerrar() {
        $this->session->sess_destroy();
        redirect(base_url());
    }

    private function paginaItem($i) {
        $clase = ($this->paginacion['pagina'] == $i) ? 'active' : '';
        return '<li class="page-item ' . $clase . '"> <a class="page-link item_paginacion" href="#">' . $i . '</a></li>';
    }

    public function calcularPaginacion($totalRegistros) {
        $paginas = '';
        $inicioPage = 1;
        if ($totalRegistros > 0 && $this->paginacion['limit'] != 0) {
            $maximo_mostrar = 6;
            $primera_pagina = $ultima_pagina = false;
            $numSegmentos = ceil($totalRegistros / $this->paginacion['limit']);
            $maximaPagina = $numSegmentos;
            if (($numSegmentos > 0) && ($numSegmentos > $maximo_mostrar)) {
                $ultima_pagina = true;
                if ($this->paginacion['pagina'] > (ceil($maximo_mostrar / 2) + 2)) {
                    $primera_pagina = true;
                    $inicioPage = $this->paginacion['pagina'] - ceil($maximo_mostrar / 2) + 1;
                    $maximaPagina = $this->paginacion['pagina'] + ceil($maximo_mostrar / 2) - 1;
                    if ($maximaPagina >= $numSegmentos - 1) {
                        $maximaPagina = $numSegmentos;
                        $inicioPage = $maximaPagina - $maximo_mostrar;
                        $ultima_pagina = false;
                    }
                } else {
                    $maximaPagina = $maximo_mostrar + 1;
                }
            }

            $paginas .= '<ul class="pagination">';
            $paginas .= ($primera_pagina) ? $this->paginaItem(1) . $this->paginaItem('...') : '';
            for ($i = $inicioPage; $i <= $maximaPagina; $i++) {
                $paginas .= $this->paginaItem($i);
            }
            $paginas .= ($ultima_pagina) ? $this->paginaItem('...') . $this->paginaItem($numSegmentos) : '';
            $paginas .= '</ul>';
        }
        return $paginas;
    }

    public function mostrarVista($name, $data = array()) {

        $data['claseBody'] = (key_exists('claseBody', $data) ? $data['claseBody'] . " " : "") . "hold-transition";

        if ($this->logeado()) {
            $data['session_userName'] = $this->session->userdata("userName");
            $data['session_userEmail'] = $this->session->userdata("userName");
            $data['session_userPerfil'] = $this->session->userdata("userPerfil");
            $data['session_userFinca'] = $this->session->userdata("userFinca");
            $data['session_FincaId'] = $this->session->userdata("userFincaId");
            if (!isset($data['ocultarMenu']) || ($data['ocultarMenu'] == 0)) {
                $data['menu'] = $this->service_general->armarMenu($this->session->userdata("userPerfilId"));
                $data['menu'] = $this->load->view(PLANTILLA . 'menu.php', $data, true);
                $data['claseBody'] .= " sidebar-mini sidebar-collapse";
            }
            $data['regpp'] = $this->paginacion['limit'];
            $data['pagina'] = $this->paginacion['pagina'];
            if (isset($data['cuantos'])) {
                $data['itemsPaginacion'] = $this->calcularPaginacion($data['cuantos']);
            }
        }

        if ($this->session->userdata('error') != null) {
            $data['error'] = $this->session->userdata('error');
            $this->session->unset_userdata('error');
        }
        if ($this->session->userdata('exito') != null) {
            $data['exito'] = $this->session->userdata('exito');
            $this->session->unset_userdata('exito');
        }

        $data['body'] = $this->load->view($name, $data, true);

        $data['header'] = $this->load->view(PLANTILLA . 'header.php', $data, true);
        $data['includes_js'] = $this->load->view(PLANTILLA . 'js.php', $data, true);
        $data['footer'] = $this->load->view(PLANTILLA . 'footer.php', $data, true);

        $this->load->view(PLANTILLA . 'base.php', $data);
    }

    public function obtenerConfiguracion($valor) {
        return $this->service_general->obtenerConfiguracion($valor);
    }

}

?>