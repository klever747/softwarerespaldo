<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Componentes extends MY_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model("ecommerce/service_ecommerce");
        $this->load->model("ecommerce/service_ecommerce_orden");
//        $this->load->model("ecommerce/service_ecommerce_cliente");
//        $this->load->model("ecommerce/service_ecommerce_orden");
//        $this->load->model("ecommerce/service_ecommerce_producto");
//        $this->load->model("ecommerce/service_ecommerce_logistica");
//        $this->load->model("ecommerce/service_ecommerce_formula");
//        $this->load->model("produccion/service_produccion");
//        $this->load->model("produccion/service_empaque");
        $this->load->model("produccion/service_componentes");
    }

    public function procesarArreglo($arreglo, $lista, $unificado = false) {
        if ($lista) {
            foreach ($lista as $a) {
//desarmamos el sku para saber que medida
                $sku_prod_arr = explode("_", $a->sku);
                if (!array_key_exists(4, $sku_prod_arr)) {
//                    error_log(print_r($sku_prod_arr, true));
                    continue;
                }
                if ($unificado) {
                    $a->tipo = 'U';
                }
                $largo = $sku_prod_arr[4];
//                echo "<br/>";
//                print_r($arreglo[$a->tipo]);
//                echo "<br/>";
//                print_r($a->tipo);
//                echo "<br/>";
                if (!array_key_exists(' ' . $a->ingrediente_nombre, $arreglo[$a->tipo])) {
                    $arreglo[$a->tipo][' ' . $a->ingrediente_nombre]['total_ingrediente'] = intval(0);
                    $arreglo[$a->tipo][' ' . $a->ingrediente_nombre]['longitudes'] = array();
                    $arreglo[$a->tipo][' ' . $a->ingrediente_nombre]['elementos'] = array();
                }
                if (!array_key_exists($largo, $arreglo[$a->tipo][' ' . $a->ingrediente_nombre]['longitudes'])) {
                    $arreglo[$a->tipo][' ' . $a->ingrediente_nombre]['longitudes'][$largo] = intval(0);
                }
                if (!array_key_exists(' ' . $a->ingrediente_descripcion, $arreglo[$a->tipo][' ' . $a->ingrediente_nombre]['elementos'])) {
                    $arreglo[$a->tipo][' ' . $a->ingrediente_nombre]['elementos'][' ' . $a->ingrediente_descripcion]['total_descripcion'] = intval(0);
                    $arreglo[$a->tipo][' ' . $a->ingrediente_nombre]['elementos'][' ' . $a->ingrediente_descripcion]['longitudes'] = array();
                }
                if (!array_key_exists($largo, $arreglo[$a->tipo][' ' . $a->ingrediente_nombre]['elementos'][' ' . $a->ingrediente_descripcion]['longitudes'])) {
                    $arreglo[$a->tipo][' ' . $a->ingrediente_nombre]['elementos'][' ' . $a->ingrediente_descripcion]['longitudes'][$largo] = 0;
                }

                $arreglo[$a->tipo][' ' . $a->ingrediente_nombre]['total_ingrediente'] = $arreglo[$a->tipo][' ' . $a->ingrediente_nombre]['total_ingrediente'] + intval($a->sum);
                $arreglo[$a->tipo][' ' . $a->ingrediente_nombre]['longitudes'][$largo] = $arreglo[$a->tipo][' ' . $a->ingrediente_nombre]['longitudes'][$largo] + intval($a->sum);
                $arreglo[$a->tipo][' ' . $a->ingrediente_nombre]['elementos'][' ' . $a->ingrediente_descripcion]['total_descripcion'] = $arreglo[$a->tipo][' ' . $a->ingrediente_nombre]['elementos'][' ' . $a->ingrediente_descripcion]['total_descripcion'] + intval($a->sum);
                $arreglo[$a->tipo][' ' . $a->ingrediente_nombre]['elementos'][' ' . $a->ingrediente_descripcion]['longitudes'][$largo] = $arreglo[$a->tipo][' ' . $a->ingrediente_nombre]['elementos'][' ' . $a->ingrediente_descripcion]['longitudes'][$largo] + intval($a->sum);
            }
        }
        return $arreglo;
    }

    private function obtenerDatos($data) {
        $arreglo = array("T" => array(), "N" => array(), "U" => array());

        if (($data['mostrar_tinturados'] == 1) && ($data['mostrar_naturales'] == 1)) {
            $stemsX = $this->service_componentes->listado($data['store_id'], $data['rango_busqueda'], $data['tipo_calendario'], false, $data);
            $arreglo = $this->procesarArreglo($arreglo, $stemsX, true);
            $stemsXPropiedades = $this->service_componentes->listadoPropiedadesStems($data['store_id'], $data['rango_busqueda'], $data['tipo_calendario'], false, $data);
            $arreglo = $this->procesarArreglo($arreglo, $stemsXPropiedades, true);
        } else {
            if ($data['mostrar_tinturados'] == 1) {
                $stemsXTinturado = $this->service_componentes->listado($data['store_id'], $data['rango_busqueda'], $data['tipo_calendario'], 'T', $data);

                $arreglo = $this->procesarArreglo($arreglo, $stemsXTinturado);
                $stemsXPropiedades = $this->service_componentes->listadoPropiedadesStems($data['store_id'], $data['rango_busqueda'], $data['tipo_calendario'], 'T', $data);
                $arreglo = $this->procesarArreglo($arreglo, $stemsXPropiedades);
            }
            if ($data['mostrar_naturales'] == 1) {
                $stemsXNormal = $this->service_componentes->listado($data['store_id'], $data['rango_busqueda'], $data['tipo_calendario'], 'N', $data);
                $arreglo = $this->procesarArreglo($arreglo, $stemsXNormal);
                $stemsXPropiedades = $this->service_componentes->listadoPropiedadesStems($data['store_id'], $data['rango_busqueda'], $data['tipo_calendario'], 'N', $data);
                $arreglo = $this->procesarArreglo($arreglo, $stemsXPropiedades);
            }
        }
        if ($data['mostrar_accesorios'] == 1) {
            $accesorios = $this->service_componentes->listadoAccesorios($data['store_id'], $data['rango_busqueda'], $data['tipo_calendario'], $data);
            $data['componentes_accesorios'] = false;
            if ($accesorios) {
                $data['componentes_accesorios'] = array();
                foreach ($accesorios as $accesorio) {
                    $cantidad = $accesorio->sum;
                    $accesorio->info_propiedad_nombre = $accesorio->descripcion;
                    $accesorio->propiedad_id = $accesorio->id;
                    $accesorio->valor = $accesorio->valor;
                    $accesorio = analizarPropiedad($accesorio, true, true);
                    error_log(print_r($accesorio, true));
                    error_log("*******************SIGUIENTE*******************");
                    if (!$accesorio) {
                        continue;
                    }
                    error_log("*******************AVANZAMOS*******************");
                    if (!array_key_exists(strtoupper($accesorio->info_propiedad_nombre), $data['componentes_accesorios'])) {
                        $data['componentes_accesorios'][strtoupper($accesorio->info_propiedad_nombre)] = array();
                    }
                    if (!array_key_exists(strtoupper($accesorio->valor), $data['componentes_accesorios'][strtoupper($accesorio->info_propiedad_nombre)])) {
                        $data['componentes_accesorios'][strtoupper($accesorio->info_propiedad_nombre)][strtoupper($accesorio->valor)] = 0;
                    }
                    if (property_exists($accesorio, "cantidad") && $accesorio->cantidad) {
                        $cantidad = $cantidad * $accesorio->cantidad;
                    }
                    error_log("cantidad actual es " . print_r($data['componentes_accesorios'][strtoupper($accesorio->info_propiedad_nombre)][strtoupper($accesorio->valor)], true));
                    $data['componentes_accesorios'][strtoupper($accesorio->info_propiedad_nombre)][strtoupper($accesorio->valor)] += $cantidad;
                    error_log("cantidad posterior es " . print_r($data['componentes_accesorios'][strtoupper($accesorio->info_propiedad_nombre)][strtoupper($accesorio->valor)], true));
                    error_log(print_r($data, true));
                }
            }
        }

//        print_r($arreglo);

        $data['listado'] = $arreglo;
        return $this->load->view('componentes_listado.php', $data, true);
    }

    public function listado() {
        $data['tipo_calendario'] = 0;
        $data['rango_busqueda'] = '';
        $data['listado'] = array();
//        $data['agrupado_longitud'] = '';
        $data['agrupado_descripcion'] = '';
        $data['mostrar_tinturados'] = 1;
        $data['mostrar_naturales'] = '';
        $data['mostrar_accesorios'] = '';
        $data['store_id'] = 1;
        $data['sel_store'] = $this->service_ecommerce->obtenerTiendasSel();
        $data['componentes_listado'] = false;
        $data['finca_id'] = 0;
        $data['session_finca'] = $this->session->userFincaId;
        $data['sel_finca'] = $this->service_ecommerce_orden->obtenerFincaSel();

        if ($this->input->post('btn_buscar') != null) {
            $data['rango_busqueda'] = $this->input->post('rango_busqueda');
            $data['tipo_calendario'] = $this->input->post('tipo_calendario');
            $data['agrupado_descripcion'] = $this->input->post('agrupado_descripcion');
            $data['mostrar_tinturados'] = $this->input->post('mostrar_tinturados');
            $data['mostrar_naturales'] = $this->input->post('mostrar_naturales');
            $data['mostrar_accesorios'] = $this->input->post('mostrar_accesorios');
            $data['store_id'] = $this->input->post('store_id');
            $data['finca_id'] = $this->input->post('finca_id');
            $data['componentes_listado'] = $this->obtenerDatos($data);
        }

        $data['url_busqueda'] = "produccion/componentes/listado";
        $this->mostrarVista('componentes.php', $data);
    }

    public function resumenXLS() {
        $filtro = json_decode($this->input->get('filtro'), true);
        $fecha = explode("-", $filtro['rango_busqueda']);
        $filename = trim($fecha[0]) . "_" . $filtro['store_id'] . "_" . $filtro['tipo_calendario'] . "_" . $filtro['agrupado_descripcion'] . "_" . $filtro['mostrar_tinturados'] . "_" . $filtro['mostrar_naturales'] . "_" . $filtro['mostrar_accesorios'] . "_" . fechaActual('YmdHis') . ".xls";

        $detalle = $this->obtenerDatos($filtro);

        header("Pragma: public");
        header("Expires: 0");
        header("Content-type: application/x-msdownload");
        header("Content-Disposition: attachment; filename=$filename");
        header("Pragma: no-cache");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        $ruta_pdf = FCPATH . "uploads/xls/componentes/";
        file_put_contents($ruta_pdf . $filename, $detalle);
        echo $detalle;
    }

}
