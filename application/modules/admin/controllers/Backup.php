<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Backup extends MY_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model("service_admin");
    }

    public function index() {
        $data['claseBody'] = "hold-transition";
        $data['parseo'] = false;
        $data['file_input'] = 'error';
        $data['message'] = '';
        $this->mostrarVista('backup.php', $data);
    }

    function cargarArchivo($fileName, $filePath) {
        $this->load->library('upload');
        $this->load->helper(array('form', 'url'));

        $config['upload_path'] = FCPATH . "uploads/csv";
        $config['allowed_types'] = 'csv|CSV';
        $config['max_size'] = '2048';
        $config['max_width'] = '1024';
        $config['max_height'] = '768';
        $config['file_name'] = $filePath;
        $config['file_ext_tolower'] = true;
        $config['overwrite'] = true;

        $this->load->library('upload', $config);
        $this->upload->initialize($config);
        if ($this->upload->do_upload($fileName)) {
            $data = array('upload_data' => $this->upload->data());
            return $this->upload->data('file_name');
        } else {
            error_log(print_r($this->upload->display_errors(), true));
            return $this->upload->display_errors();
        }
    }

    public function subirArchivo() {
        $data = array();
        $data['parseo'] = false;
        $data['data'] = [];
        $data['file_input'] = 'error';
        $data['message'] = '';

        if (isset($_FILES['input']) && !empty($_FILES['input']['tmp_name'])) {
            $file_name = "backup_" . fechaActual('YmdHis') . "_" . uniqid();
            $archivo = $this->cargarArchivo("input", $file_name);
            $data['file_input'] = $file_name;

            $content = file_get_contents(FCPATH . "uploads\csv\\" . $archivo);
            //$lines = array_map("rtrim", explode("\n", $content));            
            $lines = $this->convertirCSVaJSON($content);

            $data['data'] = $lines;
            $data['parseo'] = true;
            $data['message'] = 'Archivo subido con exito.';
        }

        $this->mostrarVista('backup.php', $data);
    }

    private function convertirCSVaJSON($content) {
        $data_map = array_map("str_getcsv", explode("\n", $content));
        $headers = array_shift($data_map);
        $labels = explode(";", array_pop($headers));

        foreach ($labels as $label) {
            $txt = str_ireplace(["\"", "'"], "", $label);
            $column_name[] = $txt;
        }
        foreach ($data_map as $val) {
            $txt = str_ireplace(["\"", "'"], "", array_pop($val));
            $data_array[] = explode(";", $txt);
        }
        $final_data = [];
        $count = count($data_array);
        for ($j = 0; $j < $count; $j++) {
            $data = array_combine($column_name, $data_array[$j]);
            $final_data[$j] = json_encode($data);
        }

        return $final_data;
    }

    public function ejecutarScript() {
        $archivo = $this->input->post('file_input');
        $option = $this->input->post('option_script');

        $data = array();
        $data['parseo'] = false;
        $data['data'] = [];
        $data['file_input'] = 'error';
        $data['message'] = '';

        if ($archivo != 'error' && intval($option) > 0) {
            $content = file_get_contents(FCPATH . "uploads\csv\\" . $archivo . ".csv");
            $linesSql = [];

            $message = 'ACTUALIZADO MEDIANTE EJECUCION DE SCRIPT MASIVO AUTORIZADO POR Miguel Penafiel | ' . date('Y-m-d');

            /*
              $lines = array_map("rtrim", explode("\n", $content));
              foreach($lines as $val){
              $l = explode(';',$val);
              $linesSql[] = $this->service_admin->ejecutarScript($l, $message, $option);
              }
             */

            $lines = $this->convertirCSVaJSON($content);
            foreach ($lines as $val) {
                $l = json_decode($val, true);
                $linesSql[] = $this->service_admin->ejecutarScript($l, $message, $option);
            }

            $data['file_input'] = $archivo; // opcion reutilizar el archivo csv
            $data['data'] = $linesSql;
            $data['parseo'] = true;
            $data['message'] = 'Informacion actualizada con exito';
        }

        $this->mostrarVista('backup.php', $data);
    }

}
