<?php

use setasign\Fpdi\Fpdi;
use Smalot\PdfParser\Parser;

defined('BASEPATH') or exit('No direct script access allowed');

class Logistica extends MY_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model("ecommerce/service_ecommerce");
        $this->load->model("ecommerce/service_ecommerce_cliente");
        $this->load->model("ecommerce/service_ecommerce_orden");
        $this->load->model("ecommerce/service_ecommerce_producto");
        $this->load->model("ecommerce/service_ecommerce_logistica");
        $this->load->model("ecommerce/service_ecommerce_formula");
        $this->load->model("produccion/service_produccion");
        $this->load->model("produccion/service_logistica");
        $this->load->model("produccion/service_sku_algoritmo");
        $this->load->model("generales/service_excel");
        $this->load->model("generales/service_general_finca");
        $this->load->model("produccion/service_orden");
        $this->load->model("produccion/service_master_shipping");
    }

    /*     * ************************ ORDENES *************************************** */

    public function orden_meter_a_caja() {
        $orden_id = $this->input->post('orden_id');
        $empacados = $this->service_ecommerce_logistica->empaqueAutomaticoOrden($orden_id);

        header('Content-Type: application/json');
        echo json_encode(array("error" => !$empacados, "orden_id" => $orden_id, "mensaje" => ($empacados ? "Items de la orden fueron metidos en caja" : "Problemas al meter items en caja")));
    }

    public function empacar_masivo() {
        $ordenes_id = $this->input->post('ordenes_id');
        $ids = explode("-", substr($ordenes_id, 1));
        $total_ids = sizeof($ids);
        $total_empacados = 0;
        $empacados = array();
        foreach ($ids as $orden_id) {
//            if ($this->service_ecommerce_logistica->ordenMeterItemsEnCaja($orden_id)) {
            if ($this->service_ecommerce_logistica->empaqueAutomaticoOrden($orden_id)) {
                $total_empacados++;
            }

            $empacados[] = $orden_id;
        }
        $error = !($total_empacados == $total_ids);
        header('Content-Type: application/json');
        echo json_encode(array("error" => $error, "mensaje" => ($error ? "No todas las ordenes pudieron empacarse correctamente" : "Todas las ordenes fueron empacadas"), "empacados" => $empacados));
    }

    public function orden_item_sacar_caja() {
        $orden_item_id = $this->input->post('orden_item_id');
        $sacadoCaja = $this->service_ecommerce_logistica->sacarItemDeCaja($orden_item_id);

        header('Content-Type: application/json');
        echo json_encode(array("error" => !$sacadoCaja, "respuesta" => ($sacadoCaja ? "Item sacado de caja" : "Problemas al sacar item")));
    }

    public function empaqueAutomaticoOrden($orden_id) {
        $this->service_ecommerce_logistica->empaqueAutomaticoOrden($orden_id);
    }

    public function orden_item_meter_caja() {
        $orden_item_id = $this->input->post('orden_item_id');
        $orden_item = $this->service_ecommerce->obtenerOrdenItem($orden_item_id);
        $orden_caja_id = $this->input->post('orden_caja_id');

        if ($orden_caja_id == '-1') {
            //creamos la caja nueva en base a la tipo_caja_id
            $tipo_caja_id = $this->input->post('tipo_caja_id');
            $orden_caja_id = $this->service_ecommerce_logistica->crearOrdenCaja($orden_item->orden_id, $tipo_caja_id);
            //error_log(print_r('aqui estaaa', true));
            if ($this->input->post('finca_id')) {
                $finca_id = $this->input->post('finca_id');
                $this->service_ecommerce_logistica->crearFincaOrdenCaja($orden_caja_id, $finca_id);
            }
        }


        // error_log(print_r('aqui esta el id de la finca'.$finca_id, true));
        // error_log(print_r('aqui esta el id de la caja '.$orden_caja_id, true));


        $meterCaja = $this->service_ecommerce_logistica->meterItemEnCaja($orden_item_id, $orden_caja_id);

        header('Content-Type: application/json');
        echo json_encode(array("error" => !$meterCaja, "respuesta" => ($meterCaja ? "Item ingresado a caja" : "Problemas al ingresar item a caja")));
    }

    public function editar_caja_finca() {
        $data['session_finca'] = $this->session->userFincaId;
        $data['finca_id'] = $this->input->post('finca_id');
        $data['tipo_caja_id'] = $this->input->post('tipo_caja_id');
        $data['caja_id'] = $this->input->post('caja_id');
        //actualizar la ordencaja
        //error_log(print_r($data['caja_id'], true));
        $actualizacion = $this->service_ecommerce_logistica->actualizarOrdenCaja($data['caja_id'], $data['tipo_caja_id']);
        if (!$actualizacion) {
            $respuesta = 'Existe un problema durante la actualización de la caja de la orden';
        } else {
            $actualizacion = $this->service_ecommerce_logistica->actualizarFincaOrdenCaja($data['caja_id'], $data['finca_id'], $data['tipo_caja_id']);
            if (!$actualizacion) {
                $respuesta = 'Existe un problema durante la actualización de la finca de despacho de la caja';
            } else {
                $respuesta = 'Caja actualizada';
            }
        }
        $respuesta = array("error" => !$actualizacion, "respuesta" => $respuesta);

        header('Content-Type: application/json');
        echo json_encode($respuesta);
    }

    public function orden_item_editar_caja() {
        $data['orden_item_id'] = $this->input->post('orden_item_id');
        $orden_item = $this->service_ecommerce->obtenerOrdenItem($data['orden_item_id']);
        $data['session_finca'] = $this->session->userFincaId;
        $data['orden_item'] = $orden_item;

        $data['orden_cajas'] = $this->service_ecommerce_logistica->obtenerOrdenCajas($orden_item->orden_id);
        //$data['sel_tipo_caja'] = $this->service_ecommerce_logistica->obtenerTiposCaja();
        $data['sel_tipo_caja'] = $this->service_logistica->obtenerTiposDeCajas();
        $data['sel_finca'] = $this->service_ecommerce_orden->obtenerFincaSelect();

        //        $data['orden_id'] = $data['orden_item']->orden_id;
        //
        //        $data['sel_producto'] = $this->service_ecommerce_producto->obtenerProducto($data['orden_item']->producto_id);
        //        $data['sel_variante'] = array();
        //
        //        $data['propiedades'] = $this->service_ecommerce->obtenerOrdenItemPropiedades($data['orden_item_id']);

        $items_logistica_det = $this->load->view('orden_detalle_item_logistica_edicion.php', $data, true);

        $respuesta = array("error" => false, "respuesta" => $items_logistica_det);
        header('Content-Type: application/json');
        echo json_encode($respuesta);
    }

    public function obtener_contenido_caja() {
        $data['orden_caja_id'] = $this->input->post('orden_caja_id');
        $orden_caja_items = $this->service_ecommerce_logistica->obtenerOrdenCajaItems($data['orden_caja_id']);
        //traer la fecha de carguera de la caja a la cual pertenece el item enviado
        $datos_caja = $this->service_logistica->obtenerOrdenCaja($data['orden_caja_id']);

        foreach ($orden_caja_items as $item) {
            //fecha carguera, id finca enviar hacer join con tabla ingrediente_precio_finca
            $item->totalStems = $this->service_ecommerce_formula->totalStemsSKUdesglosado($item->info_variante_sku, $datos_caja->fecha_carguera, $datos_caja->finca_id);
            $data['orden_caja_items'][] = $item;
        }
        $respuesta = array("error" => !$orden_caja_items, "respuesta" => $data['orden_caja_items']);
        header('Content-Type: application/json');
        echo json_encode($respuesta);
    }

    public function obtener_stems_caja($orden_caja_id) {
        $totalTinturados = $totalNormales = $totalStems = 0;
        $totalTinturadosPrecio = $totalNormalesPrecio = 0;
        $itemsEnCaja = $this->service_ecommerce_logistica->obtenerOrdenCajaItems($orden_caja_id);
        if ($itemsEnCaja) {
            foreach ($itemsEnCaja as $item) {
                $stemsItem = $this->service_ecommerce_formula->totalStemsSKUdesglosado($item->info_variante_sku, $item->fecha_carguera, $item->finca_id);

                if ($stemsItem) {
                    $totalTinturados += $stemsItem->totaltinturado * $item->cantidad;
                    $totalNormales += $stemsItem->totalnormal * $item->cantidad;
                    $totalStems += $stemsItem->cantidad * $item->cantidad;

                    $totalTinturadosPrecio += $stemsItem->totaltinturadoprecio * $item->cantidad;
                    $totalNormalesPrecio += $stemsItem->totalnormalprecio * $item->cantidad;
                }
                $propiedades = $this->service_ecommerce->obtenerOrdenItemPropiedades($item->id);
                if ($propiedades) {
                    foreach ($propiedades as $propiedad) {
                        if (strpos(strtoupper($propiedad->info_propiedad_nombre), 'AGR_') === 0) {

                            $stemsPropiedad = $this->service_ecommerce_formula->totalStemsSKUdesglosado($propiedad->info_propiedad_nombre, $item->fecha_carguera, $item->finca_id);
                            if ($stemsPropiedad) {
                                $totalTinturados += $stemsPropiedad->totaltinturado * intval($propiedad->valor);
                                $totalNormales += $stemsPropiedad->totalnormal * intval($propiedad->valor);
                                $totalStems += $stemsPropiedad->cantidad * intval($propiedad->valor);

                                $totalTinturadosPrecio += $stemsPropiedad->totaltinturadoprecio * intval($propiedad->valor);
                                $totalNormalesPrecio += $stemsPropiedad->totalnormalprecio * intval($propiedad->valor);
                            }
                        }
                    }
                }
            }
        }

        return array(
            "totalTinturados" => $totalTinturados,
            "totalNormales" => $totalNormales,
            "totalTinturadosPrecio" => $totalTinturadosPrecio,
            "totalNormalesPrecio" => $totalNormalesPrecio,
            "totalStems" => $totalStems,
        );
    }

    /*     * ************************ FIN ORDENES *************************************** */

    private function tablaDatos($pantalla = false, $filtro) {
        //$store_id, $rango_busqueda, $tipo_calendario, $con_tracking_number = 'T', $con_kardex = 'T') {
        $data_tabla['cajas'] = $this->service_logistica->listado($filtro);
        $data_tabla['pantalla'] = true;

        $UpsDAtos = $this->service_logistica->obtenerLogisticaUPS();
        $data_tabla['logistica'] = array();
        foreach ($UpsDAtos as $ups) {
            $data_tabla['logistica'][$ups->store_id][$ups->grupo_caja] = $ups;
        }
        return $this->load->view('cajas_listado_' . ($pantalla ? 'pantalla' : 'ups') . '.php', $data_tabla, true);
    }

    public function cajasPorFecha() {
        $data['tipo_calendario'] = 0;
        $data['rango_busqueda'] = '';

        $data['tipo_calendario_full'] = 0;
        $data['rango_busqueda_full'] = '';
        $data['finca_id'] = 0;
        $data['session_finca'] = $this->session->userFincaId;
        $data['sel_finca'] = $this->service_ecommerce_orden->obtenerFincaSel();
        $data['cajas'] = array();
        $data['store_id'] = 1;
        $data['con_tracking_number'] = 'N';
        $data['con_kardex'] = 'S';
        $data['sel_store'] = $this->service_ecommerce->obtenerTiendasSel();
        if ($this->input->post('btn_buscar') != null) {
            $data['rango_busqueda'] = $this->input->post('rango_busqueda');
            $data['tipo_calendario'] = $this->input->post('tipo_calendario');
            $data['finca_id'] = $this->input->post('finca_id');
            $data['rango_busqueda_full'] = $this->input->post('rango_busqueda_full');
            $data['tipo_calendario_full'] = $this->input->post('tipo_calendario_full');

            $data['store_id'] = $this->input->post('store_id');
            $data['con_tracking_number'] = $this->input->post('con_tracking_number');
            $data['con_kardex'] = $this->input->post('con_kardex');
            $data['tabla_datos'] = $this->tablaDatos(true, $data);
        }
        $data['url_busqueda'] = "produccion/logistica/cajasPorFecha";
        $this->mostrarVista('cajas_por_fecha.php', $data);
    }

    public function subirArchivoUps() {

        $data = array();
        $data['parseo'] = false;
        $data['error_parseo'] = false;
        $trakings_excel = array();
        $fallocompu = true; //aqui cambiar a false para aplicar validacion de pdf
        if (isset($_FILES['file_ups']) && is_uploaded_file($_FILES['file_ups']['tmp_name'])) {

            //obtener el array de los tracking del pdf
            if ($_FILES['file_tracking']['tmp_name'][0]) {
                $trakings_pdf = $this->subirArchivoPdfTracking($_FILES['file_tracking']);
            } else {
                $trakings_pdf = [];
            }
            $archivo = $this->cargarArchivo("file_ups", "ups_" . fechaActual('YmdHis') . "_" . uniqid());

            $parseo = SimpleXLSX::parse(FCPATH . "uploads/ups/cargados/" . $archivo);

            if ($parseo) {
                $xlsx = array();
                $total_actualizados = 0;
                $i = 0;
                $col_anulado = 0;
                $col_tracking_number = 3;
                $col_refrencia = 4;
                // traigo el array de todos los trackings del excel
                foreach ($parseo->rows() as $trak) {
                    if ($trak[$col_refrencia]) {
                        if ($trak[$col_anulado] == 'N') {
                            $trakings_excel[] = $trak[$col_tracking_number];
                        }
                    }
                }
                // traigo la diferencia de los dos arrays
                $diferencia2 = array_diff($trakings_pdf, $trakings_excel);

                $diferencia1 = array_diff($trakings_excel, $trakings_pdf);

                // arrays debe tener la misma cantidad
                if ((sizeof($diferencia1) == 0) && (sizeof($diferencia2) == 0) || $fallocompu) {
                    //se ejecuta los insert del excel
                    foreach ($parseo->rows() as $elt) {
                        if ($elt[$col_anulado] == 'N') {
                            if (!empty($elt[$col_refrencia])) {
                                $arr = explode("-", $elt[$col_refrencia]);
                                $caja_id = false;
                                if (sizeof($arr) == 2) {
                                    $caja_id = $arr[1];
                                } else if (sizeof($arr) == 3) {
                                    $caja_id = $arr[2];
                                }

                                if ($caja_id) {
                                    //obtengo la caja primero
                                    $caja = $this->service_logistica->obtenerOrdenCaja($caja_id);
                                    if ($caja) {
                                        $this->service_logistica->inactivarTrackingNumberAnteriores($caja_id);
                                        if ($this->service_logistica->actualizarTrackingNumberOrdenCaja($caja_id, $elt[$col_tracking_number], 1)) { //1 es UPS
                                            $total_actualizados++;
                                            $xlsx[$i] = array(
                                                $caja->info_store_alias,
                                                $caja->info_referencia_order_number,
                                                $caja->info_orden_id,
                                                $caja->id,
                                                $elt[$col_tracking_number],
                                                $elt[$col_tracking_number]
                                            );
                                        }
                                    }
                                }
                            }
                            //                        foreach ($elt as $k => $c) {
                            //                            $xlsx[$i][] = $c;
                            //                        }
                            $i++;
                        }
                    }
                    $data['total_actualizados'] = $total_actualizados;
                } else {
                    //borro los pdfs generados
                    foreach ($trakings_pdf as $trakings_pdf) {
                        unlink('uploads/tracking/' . $trakings_pdf . '.pdf');
                    }

                    $data['error_parseo'] = 'Los dos documentos no contiene la misma informaci&oacute;n </br>';

                    if (sizeof($diferencia1) > 0) {

                        $data['error_parseo'] .= 'El pdf no contiene los siguientes tracking </br>';
                        foreach ($diferencia1 as $dife) {
                            $data['error_parseo'] .= $dife . '</br>';
                        }
                    }
                    if (sizeof($diferencia2) > 0) {
//                        if(count($trakings_excel) < count($trakings_pdf) ){
//                          $data['error_parseo'] = 'El archivo excel no contiene el traking: </br>';
//                        }else{
//                          $data['error_parseo'] = 'El archivo pdf no contiene el traking: </br>';
//                        }
                        $data['error_parseo'] .= 'El excel no contiene los siguientes tracking </br>';
                        foreach ($diferencia2 as $dife) {
                            $data['error_parseo'] .= $dife . '</br>';
                        }
                    }
                }

                $data['parseo'] = $xlsx;
            } else {
                $data['parseo'] = false;
                $data['error_parseo'] = SimpleXLSX::parseError();
            }

            //            $content = file_get_contents(FCPATH . "uploads/ups/cargados/" . $archivo);
            //            $lines = array_map("rtrim", explode("\n", $content));
            //            $data['archivo'] = $lines;
        }
        $this->mostrarVista('carga_archivo_ups.php', $data);
    }

    function cargarArchivo($fotoId, $fotoName) {
        $this->load->library('upload');
        $this->load->helper(array('form', 'url'));

        $config['upload_path'] = FCPATH . "uploads/ups/cargados";
        $config['allowed_types'] = 'xls|xlsx';
        $config['max_size'] = '2048';
        $config['max_width'] = '1024';
        $config['max_height'] = '768';
        $config['file_name'] = $fotoName;
        $config['file_ext_tolower'] = true;
        $config['overwrite'] = true;

        $this->load->library('upload', $config);
        $this->upload->initialize($config);
        if ($this->upload->do_upload($fotoId)) {
            $data = array('upload_data' => $this->upload->data());
            return $this->upload->data('file_name');
        } else {
            error_log(print_r($this->upload->display_errors(), true));
            return $this->upload->display_errors();
        }
    }

    public function gen() {
        $a = $this->service_excel->gen();
    }

    //    public function cajasPorFecha_xls2() {
    //        $data['rango_busqueda'] = $this->input->get('rango_busqueda');
    //        $data['tipo_calendario'] = $this->input->get('tipo_calendario');
    //        $data['store_id'] = $this->input->post('store_id');
    //        $fecha = explode("-", $data['rango_busqueda']);
    //        $filename = trim($fecha[0]) . "_" . $data['store_id'] . "_" . $data['tipo_calendario'] . "_" . uniqid() . ".xls";
    //
    //        header("Pragma: public");
    //        header("Expires: 0");
    //        header("Content-type: application/x-msdownload");
    //        header("Content-Disposition: attachment; filename=$filename");
    //        header("Pragma: no-cache");
    //        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    //
    //        $data['tabla_datos'] = $this->tablaDatos(true, $data);//tablaDatos($data['store_id'], $data['rango_busqueda'], $data['tipo_calendario'], false);
    //
    //        $ruta_pdf = FCPATH . "uploads/xls/";
    //        file_put_contents($ruta_pdf . $filename, $data['tabla_datos']);
    //        echo $data['tabla_datos'];
    //    }

    public function cajasPorFecha_xls() {
        set_time_limit(0);
        $data['rango_busqueda'] = $this->input->get('rango_busqueda');
        $data['tipo_calendario'] = $this->input->get('tipo_calendario');
        $data['rango_busqueda_full'] = $this->input->get('rango_busqueda_full');
        $data['tipo_calendario_full'] = $this->input->get('tipo_calendario_full');
        $data['store_id'] = $this->input->get('store_id');
        $data['con_tracking_number'] = $this->input->get('con_tracking_number');
        $data['con_kardex'] = $this->input->get('con_kardex');
        $data['finca_id'] = $this->input->get('finca_id');
        $data['session_finca'] = $this->session->userFincaId;
        $fecha = explode("-", $data['rango_busqueda']);
        $filename = trim($fecha[0]) . "_" . $data['store_id'] . "_" . $data['tipo_calendario'] . "_" . uniqid() . ".xls";

        //        header("Pragma: public");
        //        header("Expires: 0");
        //        header("Content-type: application/x-msdownload");
        //        header("Content-Disposition: attachment; filename=$filename");
        //        header("Pragma: no-cache");
        //        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        //        $data['tabla_datos'] = $this->tablaDatos($data['store_id'], $data['rango_busqueda'], $data['tipo_calendario'], false);
        //      $ruta_pdf = FCPATH . "uploads/xls/";
        //        file_put_contents($ruta_pdf . $filename, $data['tabla_datos']);
        //        echo $data['tabla_datos'];


        $data_tabla['cajas'] = $this->service_logistica->listado($data);

        $UpsDAtos = $this->service_logistica->obtenerLogisticaUPS();
        $logistica = array();
        foreach ($UpsDAtos as $ups) {
            $logistica[$ups->store_id][$ups->grupo_caja] = $ups;
        }

        $spreadsheet = $this->service_excel->crear();
        $sheet = $spreadsheet->getActiveSheet();
        //        $sheet->setCellValue('A1', 'Hello World !');
        //        $cellC1 = $workSheet->getCell('C1');
        //echo 'Value: ', $cellC1->getValue(), '; Address: ', $cellC1->getCoordinate(), PHP_EOL;

        $spreadsheet->getProperties()
                ->setCreator('Washington Sanchez')
                ->setLastModifiedBy('Washington Sanchez')
                ->setTitle('Softwareholic archivo ups')
                ->setSubject('Softwareholic archivo para ups')
                ->setDescription('Archivo generado para ups desde el Softwareholics')
                ->setKeywords('softwareholics ups')
                ->setCategory('Carga archivo');

        $spreadsheet->setActiveSheetIndex(0)
                ->setCellValue('A1', 'Companyshipto')
                ->setCellValue('B1', 'contactshipto')
                ->setCellValue('C1', 'address1shipto')
                ->setCellValue('D1', 'address2shipto')
                ->setCellValue('E1', 'address3shipto')
                ->setCellValue('F1', 'cityshipto')
                ->setCellValue('G1', 'stateshipto')
                ->setCellValue('H1', 'zipshipto')
                ->setCellValue('I1', 'Countryshipto')
                ->setCellValue('J1', 'phoneshipto')
                ->setCellValue('K1', 'Reference1')
                ->setCellValue('L1', 'Reference2')
                ->setCellValue('M1', 'Quantity')
                ->setCellValue('N1', 'Item')
                ->setCellValue('O1', 'ProdDesc')
                ->setCellValue('P1', 'Length')
                ->setCellValue('Q1', 'width')
                ->setCellValue('R1', 'height')
                ->setCellValue('S1', 'WeightKg')
                ->setCellValue('T1', 'DclValue')
                ->setCellValue('U1', 'Service')
                ->setCellValue('V1', 'PkgType')
                ->setCellValue('W1', 'GenDesc')
                ->setCellValue('X1', 'Currency')
                ->setCellValue('Y1', 'Origin')
                ->setCellValue('Z1', 'UOM')
                ->setCellValue('AA1', 'TPComp')
                ->setCellValue('AB1', 'TPAttn')
                ->setCellValue('AC1', 'TPAdd1')
                ->setCellValue('AD1', 'TPCity')
                ->setCellValue('AE1', 'TPState')
                ->setCellValue('AF1', 'TPCtry')
                ->setCellValue('AG1', 'TPZip')
                ->setCellValue('AH1', 'TPPhone')
                ->setCellValue('AI1', 'TPAcct')
                ->setCellValue('AJ1', 'SatDlv');

        $i = 1;
        foreach ($data_tabla['cajas'] as $caja) {

            $stems_caja = $this->obtener_stems_caja($caja->orden_caja_id);
            error_log(print_r($stems_caja, true));
            //            $valor_caja = ($caja->precio > 0 ? $caja->precio : 35);
            $valor_caja = round($stems_caja['totalTinturadosPrecio'] + $stems_caja['totalNormalesPrecio'], 2);
            $i++;
            //depuramos el telefono
            $caja->phone = str_replace("(", "", $caja->phone);
            $caja->phone = str_replace(")", "", $caja->phone);
            $caja->phone = str_replace("-", "", $caja->phone);
            $caja->phone = str_replace("+", "", $caja->phone);
            $caja->phone = str_replace(" ", "", $caja->phone);
            $caja->phone = str_pad($caja->phone, 11, "1", STR_PAD_LEFT);
            $spreadsheet->setActiveSheetIndex(0)
                    ->setCellValue('A' . $i, (isset($caja->destinatario_company) ? $caja->destinatario_company : '') . ' ' . $caja->destinatario_nombre . " " . $caja->destinatario_apellido)
                    ->setCellValue('B' . $i, $caja->destinatario_nombre . " " . $caja->destinatario_apellido)
                    ->setCellValue('C' . $i, $caja->address_1)
                    ->setCellValue('D' . $i, ' ' . $caja->address_2)
                    ->setCellValue('E' . $i, '')
                    ->setCellValue('F' . $i, $caja->city)
                    ->setCellValue('G' . $i, $caja->state_code)
                    ->setCellValue('H' . $i, intval($caja->zip_code))
                    ->setCellValue('I' . $i, (trim(strtoupper($caja->country_code)) === "UN" ? "US" : $caja->country_code))
                    ->setCellValue('J' . $i, $caja->phone)
                    ->setCellValue('K' . $i, $caja->store_alias . "-" . ($caja->referencia_order_number != '' ? $caja->referencia_order_number : $caja->orden_id) . "-" . $caja->orden_caja_id)
                    ->setCellValue('L' . $i, '')
                    ->setCellValue('M' . $i, 1)
                    ->setCellValue('N' . $i, $logistica[$caja->store_id][$caja->grupo]->ups_item)
                    ->setCellValue('O' . $i, $logistica[$caja->store_id][$caja->grupo]->ups_prod_desc)
                    ->setCellValue('P' . $i, '=' . $caja->length)
                    ->setCellValue('Q' . $i, '=' . $caja->width)
                    ->setCellValue('R' . $i, '=' . $caja->height)
                    ->setCellValue('S' . $i, '=' . $caja->weight)
                    ->setCellValue('T' . $i, '=' . $valor_caja)
                    ->setCellValue('U' . $i, $logistica[$caja->store_id][$caja->grupo]->ups_service)
                    ->setCellValue('V' . $i, $logistica[$caja->store_id][$caja->grupo]->ups_pkg_type)
                    ->setCellValue('W' . $i, $logistica[$caja->store_id][$caja->grupo]->ups_gen_desc)
                    ->setCellValue('X' . $i, $logistica[$caja->store_id][$caja->grupo]->ups_currency)
                    ->setCellValue('Y' . $i, $logistica[$caja->store_id][$caja->grupo]->ups_origin)
                    ->setCellValue('Z' . $i, $logistica[$caja->store_id][$caja->grupo]->ups_uom)
                    ->setCellValue('AA' . $i, $logistica[$caja->store_id][$caja->grupo]->ups_tp_comp)
                    ->setCellValue('AB' . $i, $logistica[$caja->store_id][$caja->grupo]->ups_tp_attn)
                    ->setCellValue('AC' . $i, $logistica[$caja->store_id][$caja->grupo]->ups_tp_add1)
                    ->setCellValue('AD' . $i, $logistica[$caja->store_id][$caja->grupo]->ups_tp_city)
                    ->setCellValue('AE' . $i, $logistica[$caja->store_id][$caja->grupo]->ups_tp_state)
                    ->setCellValue('AF' . $i, $logistica[$caja->store_id][$caja->grupo]->ups_tp_ctry)
                    ->setCellValue('AG' . $i, $logistica[$caja->store_id][$caja->grupo]->ups_tp_zip)
                    ->setCellValue('AH' . $i, $logistica[$caja->store_id][$caja->grupo]->ups_tp_phone)
                    ->setCellValue('AI' . $i, $logistica[$caja->store_id][$caja->grupo]->ups_tp_acct)
                    ->setCellValue('AJ' . $i, $logistica[$caja->store_id][$caja->grupo]->ups_sat_dlv);
        }

        //        $spreadsheet->setActiveSheetIndex(0)
        //                ->setCellValue('A4', 'Miscellaneous glyphs')
        //                ->setCellValue('A5', 'éàèùâêîôûëïüÿäöüç');
        //
        //        $spreadsheet->getActiveSheet()
        //                ->setCellValue('A8', "Hello\nWorld");
        //        $spreadsheet->getActiveSheet()
        //                ->getRowDimension(8)
        //                ->setRowHeight(-1);
        //        $spreadsheet->getActiveSheet()
        //                ->getStyle('A8')
        //                ->getAlignment()
        //                ->setWrapText(true);
        //        $value = "-ValueA\n-Value B\n-Value C";
        //        $spreadsheet->getActiveSheet()
        //                ->setCellValue('A10', $value);
        //        $spreadsheet->getActiveSheet()
        //                ->getRowDimension(10)
        //                ->setRowHeight(-1);
        //        $spreadsheet->getActiveSheet()
        //                ->getStyle('A10')
        //                ->getAlignment()
        //                ->setWrapText(true);
        //        $spreadsheet->getActiveSheet()
        //                ->getStyle('A10')
        //                ->setQuotePrefix(true);
        // Rename worksheet
        //        $worksheet = $spreadsheet->addSheet(new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'Client Timesheet'));

        $spreadsheet->addNamedRange(new \PhpOffice\PhpSpreadsheet\NamedRange('ENVIOS', $spreadsheet->getActiveSheet(), '$A$1:$AJ$' . $i));

        $spreadsheet->getActiveSheet()
                ->setTitle('Simple');

        $nombre = "archiv_ups_";
        $writer = $this->service_excel->crearXlsx($spreadsheet, $nombre);
    }

    public function trackingCargaPorFechaCompleto_xls() {
        return $this->trackingCargaPorFecha_xls(true);
    }

    public function trackingCargaPorFecha_xls($completo = false) {
        $data['rango_busqueda'] = $this->input->get('rango_busqueda');
        $data['tipo_calendario'] = $this->input->get('tipo_calendario');
        $data['rango_busqueda_full'] = $this->input->get('rango_busqueda_full');
        $data['tipo_calendario_full'] = $this->input->get('tipo_calendario_full');
        $data['store_id'] = $this->input->get('store_id');
        $data['con_tracking_number'] = $this->input->get('con_tracking_number');
        $data['con_kardex'] = $this->input->get('con_kardex');
        $data['finca_id'] = $this->input->get('finca_id');
        $fecha = explode("-", $data['rango_busqueda']);
        $filename = trim($fecha[0]) . "_" . $data['store_id'] . "_" . $data['tipo_calendario'] . "_" . uniqid() . ".xls";

        $data_tabla['cajas'] = $this->service_logistica->listado($data);

        $spreadsheet = $this->service_excel->crear();
        $sheet = $spreadsheet->getActiveSheet();

        $spreadsheet->getProperties()
                ->setCreator('Washington Sanchez')
                ->setLastModifiedBy('Washington Sanchez')
                ->setTitle('Softwareholic archivo ups')
                ->setSubject('Softwareholic archivo para ups')
                ->setDescription('Archivo generado para ups desde el Softwareholics')
                ->setKeywords('softwareholics ups')
                ->setCategory('Carga archivo tracking numbers');

        $spreadsheet->setActiveSheetIndex(0)
                ->setCellValue('A1', 'ORDER_NAME')
                ->setCellValue('B1', 'TRACKING_NUMBER')
                ->setCellValue('C1', 'TRACKING_COMPANY')
                ->setCellValue('D1', 'TRACKING_URL')
                ->setCellValue('E1', 'CONTENIDO_DUPLICADOS');
        if ($completo) {
            $spreadsheet->setActiveSheetIndex(0)
                    ->setCellValue('F1', 'ID_ORDEN')
                    ->setCellValue('G1', 'TIENDA')
                    ->setCellValue('H1', 'TIPO_CAJA');
        }

        $i = 1;
        $duplicados = $normales = array();
        if ($data_tabla['cajas']) {
            foreach ($data_tabla['cajas'] as $caja) {
                $cajas_orden = $this->service_ecommerce_logistica->obtenerOrdenCajas($caja->orden_id);
                $contenido = '';
                if (sizeof($cajas_orden) > 1) {
                    $items = $this->service_ecommerce_logistica->obtenerOrdenCajaItems($caja->orden_caja_id);
                    foreach ($items as $item) {
                        $contenido .= $item->info_producto_titulo . " " . $item->info_variante_titulo . "|";
                    }
                    if ($completo) {
                        $duplicados[] = array(
                            "ORDER_NAME" => $caja->referencia_order_number,
                            "TRACKING_NUMBER" => $caja->tracking_number,
                            "TRACKING_COMPANY" => 'UPS',
                            "TRACKING_URL" => 'https://www.ups.com/WebTracking?loc=en_US&requester=ST&trackNums=' . $caja->tracking_number,
                            "CONTENIDO_DUPLICADOS" => $contenido,
                            "ORDEN_ID" => $caja->orden_id,
                            "TIENDA" => $caja->store_alias,
                            "TIPO_CAJA" => $caja->caja_nombre
                        );
                    } else {
                        $duplicados[] = array(
                            "ORDER_NAME" => $caja->referencia_order_number,
                            "TRACKING_NUMBER" => $caja->tracking_number,
                            "TRACKING_COMPANY" => 'UPS',
                            "TRACKING_URL" => 'https://www.ups.com/WebTracking?loc=en_US&requester=ST&trackNums=' . $caja->tracking_number,
                            "CONTENIDO_DUPLICADOS" => $contenido
                        );
                    }
                } else {
                    if ($completo) {
                        $items = $this->service_ecommerce_logistica->obtenerOrdenCajaItems($caja->orden_caja_id);
                        foreach ($items as $item) {
                            $contenido .= $item->info_producto_titulo . " " . $item->info_variante_titulo . "|";
                        }
                    }
                    if ($completo) {
                        $normales[] = array(
                            "ORDER_NAME" => $caja->referencia_order_number,
                            "TRACKING_NUMBER" => $caja->tracking_number,
                            "TRACKING_COMPANY" => 'UPS',
                            "TRACKING_URL" => 'https://www.ups.com/WebTracking?loc=en_US&requester=ST&trackNums=' . $caja->tracking_number,
                            "CONTENIDO_DUPLICADOS" => $contenido,
                            "ORDEN_ID" => $caja->orden_id,
                            "TIENDA" => $caja->store_alias,
                            "TIPO_CAJA" => $caja->caja_nombre
                        );
                    } else {
                        $normales[] = array(
                            "ORDER_NAME" => $caja->referencia_order_number,
                            "TRACKING_NUMBER" => $caja->tracking_number,
                            "TRACKING_COMPANY" => 'UPS',
                            "TRACKING_URL" => 'https://www.ups.com/WebTracking?loc=en_US&requester=ST&trackNums=' . $caja->tracking_number,
                            "CONTENIDO_DUPLICADOS" => $contenido,
                        );
                    }
                }
            }
        }
        foreach ($duplicados as $item) {
            $i++;
            $spreadsheet->setActiveSheetIndex(0)
                    ->setCellValue('A' . $i, $item['ORDER_NAME'])
                    ->setCellValue('B' . $i, $item['TRACKING_NUMBER'])
                    ->setCellValue('C' . $i, $item['TRACKING_COMPANY'])
                    ->setCellValue('D' . $i, $item['TRACKING_URL'])
                    ->setCellValue('E' . $i, $item['CONTENIDO_DUPLICADOS']);
            if ($completo) {
                $spreadsheet->setActiveSheetIndex(0)
                        ->setCellValue('F' . $i, $item['ORDEN_ID'])
                        ->setCellValue('G' . $i, $item['TIENDA'])
                        ->setCellValue('H' . $i, $item['TIPO_CAJA']);
            }
        }
        foreach ($normales as $item) {
            $i++;
            $spreadsheet->setActiveSheetIndex(0)
                    ->setCellValue('A' . $i, $item['ORDER_NAME'])
                    ->setCellValue('B' . $i, $item['TRACKING_NUMBER'])
                    ->setCellValue('C' . $i, $item['TRACKING_COMPANY'])
                    ->setCellValue('D' . $i, $item['TRACKING_URL'])
                    ->setCellValue('E' . $i, $item['CONTENIDO_DUPLICADOS']);
            if ($completo) {
                $spreadsheet->setActiveSheetIndex(0)
                        ->setCellValue('F' . $i, $item['ORDEN_ID'])
                        ->setCellValue('G' . $i, $item['TIENDA'])
                        ->setCellValue('H' . $i, $item['TIPO_CAJA']);
            }
        }
        $spreadsheet->addNamedRange(new \PhpOffice\PhpSpreadsheet\NamedRange('TRACKING_NUMBERS', $spreadsheet->getActiveSheet(), '$A$1:$D$' . $i));

        $spreadsheet->getActiveSheet()
                ->setTitle('Simple');

        $nombre = "archivo_carga_";
        $writer = $this->service_excel->crearXlsx($spreadsheet, $nombre);
    }

    private function ayudaResumen2($arr, $arrPrecios, $resumenCajas) {
        if ($resumenCajas) {
            foreach ($resumenCajas as $resumen) {
                if ((strpos($resumen->largo_cm, "GR_") == 1)) {
                    //debo obtener el largo real de la propiedad
                    $lrg = explode("_", $resumen->largo_cm);
                    $resumen->largo_cm = $lrg[4];
                }


                if (($resumen->tipo_producto == 'N') && ($resumen->largo_cm == 40)) {
                    $grupo = "N";
                } else if (($resumen->tipo_producto == 'N') && ($resumen->largo_cm > 40)) {
                    $grupo = "NXL";
                } else if (($resumen->tipo_producto == 'T') && ($resumen->largo_cm == 40)) {
                    $grupo = "T";
                } else if (($resumen->tipo_producto == 'T') && ($resumen->largo_cm > 40)) {
                    $grupo = "TXL";
                } else {
                    continue;
                }

                $arr[$resumen->store_id][$resumen->tipo_caja_id]["ordenes"][$resumen->orden_id] = $resumen->alias . "_" . $resumen->orden_caja_id;
                $arr[$resumen->store_id][$resumen->tipo_caja_id]["total_cajas"] += 1;
                if ($resumen->store_id == 3) {
                    $tot_dol = $resumen->total_precio;
                } else {
                    $tot_dol = $resumen->total_stems * $arrPrecios[$resumen->store_id][$resumen->largo_cm][$resumen->tipo_producto];
                }

                if (!array_key_exists("total_dolar", $arr[$resumen->store_id][$resumen->tipo_caja_id])) {
                    $arr[$resumen->store_id][$resumen->tipo_caja_id]["total_dolar"] = array();
                }
                //                var_dump(intval($tot_dol));
                if (!array_key_exists(intval($tot_dol), $arr[$resumen->store_id][$resumen->tipo_caja_id]["total_dolar"])) {
                    $arr[$resumen->store_id][$resumen->tipo_caja_id]["total_dolar"][$tot_dol]["total_ordenes"][$tot_dol] = 0;
                    $arr[$resumen->store_id][$resumen->tipo_caja_id]["total_dolar"][$tot_dol]["ordenes"] = array();
                }
                $arr[$resumen->store_id][$resumen->tipo_caja_id]["total_dolar"][$tot_dol]["total_ordenes"] ++;
                $arr[$resumen->store_id][$resumen->tipo_caja_id]["total_dolar"][$tot_dol]["ordenes"][$resumen->orden_id] = $resumen->alias . "_" . $resumen->orden_caja_id;
            }
        }
        return $arr;
    }

    public function resumenCajasStems_deprecated() {
        $data['store_id'] = $data['tipo_calendario'] = 0;
        $data['rango_busqueda'] = '';
        $data['totales'] = false;
        $data['data'] = false;
        $data['sel_store'] = $this->service_ecommerce->obtenerTiendasSel();
        $data['perfil'] = $this->session->userdata('userPerfil');
        $detalle = '';
        $data['data_guias'] = false;
        if ($this->input->post('btn_buscar') != null) {
            $data['store_id'] = $this->input->post('store_id');
            $data['rango_busqueda'] = $this->input->post('rango_busqueda');
            $data['tipo_calendario'] = $this->input->post('tipo_calendario');

            //            $data['con_tracking_number'] = $this->input->post('con_tracking_number');

            $data['tinturado'] = "T";
            $resumenCajaProducto_tinturados = $this->service_logistica->obtenerResumen($data);
            $resumenCajaPropiedades_tinturados = $this->service_logistica->obtenerResumenPropiedades($data);
            $data['tinturado'] = "N";
            $resumenCajaProducto_normales = $this->service_logistica->obtenerResumen($data);
            $resumenCajaPropiedades_normales = $this->service_logistica->obtenerResumenPropiedades($data);
            $arrPrecios = array();
            $sel_tipo_caja = $this->service_logistica->obtenerTiposDeCajas();
            foreach ($data['sel_store'] as $k => $store) {
                foreach ($sel_tipo_caja as $tipo_caja) {
                    $data['data'][$k]['total_dolares'] = 0;
                    $data['data'][$k]['total_stems'] = 0;
                    $data['data'][$k]['cajas'][$tipo_caja->id] = array(
                        "nombre_caja" => $tipo_caja->nombre,
                        "total_cajas" => 0,
                        "total_dolar" => 0,
                        "total_cajas" => 0,
                        "total_stems" => 0,
                        "tipo_producto" => array(),
                        "ordenes" => array(),
                    );
                }
            }
            if ($resumenCajaProducto_tinturados) {
                $data['data'] = $this->ayudaResumenStemsOld1($data['data'], $arrPrecios, $resumenCajaProducto_tinturados, "T");
            }
            if ($resumenCajaProducto_normales) {
                $data['data'] = $this->ayudaResumenStemsOld1($data['data'], $arrPrecios, $resumenCajaProducto_normales, "N");
            }
            if ($resumenCajaPropiedades_tinturados) {
                $data['data'] = $this->ayudaResumenStemsOld1($data['data'], $arrPrecios, $resumenCajaPropiedades_tinturados, "T");
            }
            if ($resumenCajaPropiedades_normales) {
                $data['data'] = $this->ayudaResumenStemsOld1($data['data'], $arrPrecios, $resumenCajaPropiedades_normales, "N");
            }
            $data['data_guias'] = $this->service_logistica->obtenerGuias($data['rango_busqueda']);
        }

        error_log(print_r($data['data'], true));
        $data['url_busqueda'] = "produccion/logistica/resumenCajasStems";
        $data['detalle'] = $detalle;

        $this->mostrarVista('cajas_resumen_stems.php', $data);
    }

    public function resumenCajasOld() {
        $data['store_id'] = $data['tipo_calendario'] = 0;
        $data['rango_busqueda'] = '';
        $data['con_tracking_number'] = 'T';
        $data['totales'] = false;
        $data['data'] = false;
        $data['sel_store'] = $this->service_ecommerce->obtenerTiendasSel();
        $data['perfil'] = $this->session->userdata('userPerfil');
        $detalle = '';
        if ($this->input->post('btn_buscar') != null) {
            $data['store_id'] = $this->input->post('store_id');
            $data['rango_busqueda'] = $this->input->post('rango_busqueda');
            $data['tipo_calendario'] = $this->input->post('tipo_calendario');

            //            $data['con_tracking_number'] = $this->input->post('con_tracking_number');

            $resumenCajaProducto = $this->service_logistica->obtenerResumen($data);
            $resumenCajaPropiedades = $this->service_logistica->obtenerResumenPropiedades($data);

            $precios = $this->service_logistica->obtenerCostosFlor();
            $arrPrecios = array();
            foreach ($precios as $precio) {
                error_log(print_r($precio, true));
                $arrPrecios[$precio->store_id][$precio->largo_cm][$precio->tipo] = $precio->costo;
            }
            error_log(print_r($arrPrecios, true));
            $sel_tipo_caja = $this->service_logistica->obtenerTiposDeCajas();
            foreach ($data['sel_store'] as $k => $store) {
                foreach ($sel_tipo_caja as $tipo_caja) {
                    $data['data'][$k][$tipo_caja->id] = array(
                        "nombre_caja" => $tipo_caja->nombre,
                        "total_cajas" => 0,
                        "tipo_producto" => array(),
                        "ordenes" => array(),
                    );
                }
            }
            if ($resumenCajaProducto) {
                $data['data'] = $this->ayudaResumenOld($data['data'], $arrPrecios, $resumenCajaProducto);
            }
            if ($resumenCajaPropiedades) {
                $data['data'] = $this->ayudaResumenOld($data['data'], $arrPrecios, $resumenCajaPropiedades);
            }
        }
        $data['url_busqueda'] = "produccion/logistica/resumenCajas";
        $data['detalle'] = $detalle;

        $this->mostrarVista('cajas_resumen.php', $data);
    }

    private function ayudaResumenStemsOld1($arr, $arrPrecios, $resumenCajas, $tinturado) {
        if ($resumenCajas) {
            foreach ($resumenCajas as $k => $resumen) {

                //                error_log(print_r($resumen,true));
                error_log((strpos($resumen->largo_cm, "GR_") == 1));
                if ((strpos($resumen->largo_cm, "GR_") == 1)) {
                    //debo obtener el largo real de la propiedad
                    error_log($resumen->largo_cm);
                    $lrg = explode("_", $resumen->largo_cm);
                    error_log(print_r($lrg, true));
                    $resumen->largo_cm = $lrg[4];
                } else if (($resumen->largo_cm == 1)) {
                    //debo obtener el largo real de la propiedad
                    $resumen->largo_cm = 40;
                }


                //                if (($resumen->tipo_producto == 'N') && ($resumen->largo_cm == 40)) {
                //                    $grupo = "N";
                //                } else if (($resumen->tipo_producto == 'N') && ($resumen->largo_cm > 40)) {
                //                    $grupo = "NXL";
                //                } else if (($resumen->tipo_producto == 'T') && ($resumen->largo_cm == 40)) {
                //                    $grupo = "T";
                //                } else if (($resumen->tipo_producto == 'T') && ($resumen->largo_cm > 40)) {
                //                    $grupo = "TXL";
                //                } else {
                //                    continue;
                //                }
                if (($tinturado == "N") && ($resumen->largo_cm == 40)) {
                    $grupo = "N";
                } else if (($tinturado == "N") && ($resumen->largo_cm > 40)) {
                    $grupo = "NXL";
                } else if (($tinturado == "T") && ($resumen->largo_cm == 40)) {
                    $grupo = "T";
                } else if (($tinturado == "T") && ($resumen->largo_cm > 40)) {
                    $grupo = "TXL";
                } else {
                    continue;
                }
                ///
                if ($resumen->store_id == 3) {
                    $tota_prec = $resumen->total_precio;
                } else {
                    error_log($resumen->total_stems);
                    error_log($resumen->store_id);
                    error_log($resumen->largo_cm);
                    error_log($tinturado);
                    $tota_prec = $resumen->total_stems * $arrPrecios[$resumen->store_id][$resumen->longitud][$resumen->tipo_producto];

                    error_log($tota_prec);
                }
                if ($tota_prec <= 0)
                    continue;
                $arr[$resumen->store_id]['total_dolares'] += $tota_prec;
                $arr[$resumen->store_id]['total_stems'] += $resumen->total_stems;
                $arr[$resumen->store_id]['cajas'][$resumen->tipo_caja_id]["ordenes"][$resumen->orden_id] = $resumen->alias . "_" . $resumen->orden_caja_id;
                $arr[$resumen->store_id]['cajas'][$resumen->tipo_caja_id]["total_cajas"] += 1;
                $arr[$resumen->store_id]['cajas'][$resumen->tipo_caja_id]["total_dolar"] += $tota_prec;
                $arr[$resumen->store_id]['cajas'][$resumen->tipo_caja_id]["total_stems"] += $resumen->total_stems;

                if (!array_key_exists($grupo, $arr[$resumen->store_id]['cajas'][$resumen->tipo_caja_id]["tipo_producto"])) {
                    $arr[$resumen->store_id]['cajas'][$resumen->tipo_caja_id]["tipo_producto"][$grupo] = array("largo_cm" => array(), "total_cajas" => 0, "ordenes" => array());
                }
                $arr[$resumen->store_id]['cajas'][$resumen->tipo_caja_id]["tipo_producto"][$grupo]["total_cajas"] ++;
                $arr[$resumen->store_id]['cajas'][$resumen->tipo_caja_id]["tipo_producto"][$grupo]["ordenes"][$resumen->orden_id] = $resumen->alias . "_" . $resumen->orden_caja_id;

                if (!array_key_exists($resumen->largo_cm, $arr[$resumen->store_id]['cajas'][$resumen->tipo_caja_id]["tipo_producto"][$grupo]["largo_cm"])) {
                    $arr[$resumen->store_id]['cajas'][$resumen->tipo_caja_id]["tipo_producto"][$grupo]["largo_cm"][$resumen->largo_cm]["total_stems"] = 0;
                    $arr[$resumen->store_id]['cajas'][$resumen->tipo_caja_id]["tipo_producto"][$grupo]["largo_cm"][$resumen->largo_cm]["total_dolar"] = 0;
                    $arr[$resumen->store_id]['cajas'][$resumen->tipo_caja_id]["tipo_producto"][$grupo]["largo_cm"][$resumen->largo_cm]["total_cajas"] = 0;
                    $arr[$resumen->store_id]['cajas'][$resumen->tipo_caja_id]["tipo_producto"][$grupo]["largo_cm"][$resumen->largo_cm]["ordenes"] = array();
                    $arr[$resumen->store_id]['cajas'][$resumen->tipo_caja_id]["tipo_producto"][$grupo]["largo_cm"][$resumen->largo_cm]["totales_caja"] = array();
                }
                $arr[$resumen->store_id]['cajas'][$resumen->tipo_caja_id]["tipo_producto"][$grupo]["largo_cm"][$resumen->largo_cm]["ordenes"][$resumen->orden_id] = $resumen->alias . "_" . $resumen->orden_caja_id;
                $arr[$resumen->store_id]['cajas'][$resumen->tipo_caja_id]["tipo_producto"][$grupo]["largo_cm"][$resumen->largo_cm]["total_stems"] += $resumen->total_stems;
                $arr[$resumen->store_id]['cajas'][$resumen->tipo_caja_id]["tipo_producto"][$grupo]["largo_cm"][$resumen->largo_cm]["total_cajas"] ++;

                $arr[$resumen->store_id]['cajas'][$resumen->tipo_caja_id]["tipo_producto"][$grupo]["largo_cm"][$resumen->largo_cm]["total_dolar"] += $tota_prec;
                if (!array_key_exists(strval($tota_prec), $arr[$resumen->store_id]['cajas'][$resumen->tipo_caja_id]["tipo_producto"][$grupo]["largo_cm"][$resumen->largo_cm]["totales_caja"])) {
                    $arr[$resumen->store_id]['cajas'][$resumen->tipo_caja_id]["tipo_producto"][$grupo]["largo_cm"][$resumen->largo_cm]["totales_caja"][strval($tota_prec)]["total_stems"] = 0;
                    $arr[$resumen->store_id]['cajas'][$resumen->tipo_caja_id]["tipo_producto"][$grupo]["largo_cm"][$resumen->largo_cm]["totales_caja"][strval($tota_prec)]["ordenes"] = array();
                }
                $arr[$resumen->store_id]['cajas'][$resumen->tipo_caja_id]["tipo_producto"][$grupo]["largo_cm"][$resumen->largo_cm]["totales_caja"][strval($tota_prec)]["total_stems"] += $resumen->total_stems;
                $arr[$resumen->store_id]['cajas'][$resumen->tipo_caja_id]["tipo_producto"][$grupo]["largo_cm"][$resumen->largo_cm]["totales_caja"][strval($tota_prec)]["ordenes"][$resumen->orden_id] = $resumen->alias . "_" . $resumen->orden_caja_id;
            }
        }
        return $arr;
    }

    private function ayudaResumenStems($arr, $arrPrecios, $resumenCajas, $tinturado) {
        if ($resumenCajas) {
            foreach ($resumenCajas as $resumen) {

                //                error_log(print_r($resumen,true));
                error_log((strpos($resumen->largo_cm, "GR_") == 1));
                if ((strpos($resumen->largo_cm, "GR_") == 1)) {
                    //debo obtener el largo real de la propiedad
                    error_log($resumen->largo_cm);
                    $lrg = explode("_", $resumen->largo_cm);
                    error_log(print_r($lrg, true));
                    $resumen->largo_cm = $lrg[4];
                } else if (($resumen->largo_cm == 1)) {
                    //debo obtener el largo real de la propiedad
                    $resumen->largo_cm = 40;
                }


                //                if (($resumen->tipo_producto == 'N') && ($resumen->largo_cm == 40)) {
                //                    $grupo = "N";
                //                } else if (($resumen->tipo_producto == 'N') && ($resumen->largo_cm > 40)) {
                //                    $grupo = "NXL";
                //                } else if (($resumen->tipo_producto == 'T') && ($resumen->largo_cm == 40)) {
                //                    $grupo = "T";
                //                } else if (($resumen->tipo_producto == 'T') && ($resumen->largo_cm > 40)) {
                //                    $grupo = "TXL";
                //                } else {
                //                    continue;
                //                }
                if (($tinturado == "N") && ($resumen->largo_cm == 40)) {
                    $grupo = "N";
                } else if (($tinturado == "N") && ($resumen->largo_cm > 40)) {
                    $grupo = "NXL";
                } else if (($tinturado == "T") && ($resumen->largo_cm == 40)) {
                    $grupo = "T";
                } else if (($tinturado == "T") && ($resumen->largo_cm > 40)) {
                    $grupo = "TXL";
                } else {
                    continue;
                }
                if ($resumen->store_id == 3) {
                    $tota_prec = $resumen->total_precio;
                } else {
                    error_log($resumen->total_stems);
                    error_log($resumen->store_id);
                    error_log($resumen->largo_cm);
                    error_log($tinturado);
                    $tota_prec = $resumen->total_stems * $arrPrecios[$resumen->store_id][$resumen->largo_cm][$tinturado];

                    error_log($tota_prec);
                }
                if ($tota_prec <= 0)
                    continue;
                $arr[$resumen->store_id]['total_dolares'] += $tota_prec;
                $arr[$resumen->store_id]['total_stems'] += $resumen->total_stems;
                $arr[$resumen->store_id]['cajas'][$resumen->tipo_caja_id]["ordenes"][$resumen->orden_id] = $resumen->alias . "_" . $resumen->orden_caja_id;
                $arr[$resumen->store_id]['cajas'][$resumen->tipo_caja_id]["total_cajas"] += 1;
                $arr[$resumen->store_id]['cajas'][$resumen->tipo_caja_id]["total_dolar"] += $tota_prec;
                $arr[$resumen->store_id]['cajas'][$resumen->tipo_caja_id]["total_stems"] += $resumen->total_stems;

                if (!array_key_exists($grupo, $arr[$resumen->store_id]['cajas'][$resumen->tipo_caja_id]["tipo_producto"])) {
                    $arr[$resumen->store_id]['cajas'][$resumen->tipo_caja_id]["tipo_producto"][$grupo] = array("largo_cm" => array(), "total_cajas" => 0, "ordenes" => array());
                }
                $arr[$resumen->store_id]['cajas'][$resumen->tipo_caja_id]["tipo_producto"][$grupo]["total_cajas"] ++;
                $arr[$resumen->store_id]['cajas'][$resumen->tipo_caja_id]["tipo_producto"][$grupo]["ordenes"][$resumen->orden_id] = $resumen->alias . "_" . $resumen->orden_caja_id;

                if (!array_key_exists($resumen->largo_cm, $arr[$resumen->store_id]['cajas'][$resumen->tipo_caja_id]["tipo_producto"][$grupo]["largo_cm"])) {
                    $arr[$resumen->store_id]['cajas'][$resumen->tipo_caja_id]["tipo_producto"][$grupo]["largo_cm"][$resumen->largo_cm]["total_stems"] = 0;
                    $arr[$resumen->store_id]['cajas'][$resumen->tipo_caja_id]["tipo_producto"][$grupo]["largo_cm"][$resumen->largo_cm]["total_dolar"] = 0;
                    $arr[$resumen->store_id]['cajas'][$resumen->tipo_caja_id]["tipo_producto"][$grupo]["largo_cm"][$resumen->largo_cm]["total_cajas"] = 0;
                    $arr[$resumen->store_id]['cajas'][$resumen->tipo_caja_id]["tipo_producto"][$grupo]["largo_cm"][$resumen->largo_cm]["ordenes"] = array();
                    $arr[$resumen->store_id]['cajas'][$resumen->tipo_caja_id]["tipo_producto"][$grupo]["largo_cm"][$resumen->largo_cm]["totales_caja"] = array();
                }
                $arr[$resumen->store_id]['cajas'][$resumen->tipo_caja_id]["tipo_producto"][$grupo]["largo_cm"][$resumen->largo_cm]["ordenes"][$resumen->orden_id] = $resumen->alias . "_" . $resumen->orden_caja_id;
                $arr[$resumen->store_id]['cajas'][$resumen->tipo_caja_id]["tipo_producto"][$grupo]["largo_cm"][$resumen->largo_cm]["total_stems"] += $resumen->total_stems;
                $arr[$resumen->store_id]['cajas'][$resumen->tipo_caja_id]["tipo_producto"][$grupo]["largo_cm"][$resumen->largo_cm]["total_cajas"] ++;

                $arr[$resumen->store_id]['cajas'][$resumen->tipo_caja_id]["tipo_producto"][$grupo]["largo_cm"][$resumen->largo_cm]["total_dolar"] += $tota_prec;
                if (!array_key_exists(strval($tota_prec), $arr[$resumen->store_id]['cajas'][$resumen->tipo_caja_id]["tipo_producto"][$grupo]["largo_cm"][$resumen->largo_cm]["totales_caja"])) {
                    $arr[$resumen->store_id]['cajas'][$resumen->tipo_caja_id]["tipo_producto"][$grupo]["largo_cm"][$resumen->largo_cm]["totales_caja"][strval($tota_prec)]["total_stems"] = 0;
                    $arr[$resumen->store_id]['cajas'][$resumen->tipo_caja_id]["tipo_producto"][$grupo]["largo_cm"][$resumen->largo_cm]["totales_caja"][strval($tota_prec)]["ordenes"] = array();
                }
                $arr[$resumen->store_id]['cajas'][$resumen->tipo_caja_id]["tipo_producto"][$grupo]["largo_cm"][$resumen->largo_cm]["totales_caja"][strval($tota_prec)]["total_stems"] += $resumen->total_stems;
                $arr[$resumen->store_id]['cajas'][$resumen->tipo_caja_id]["tipo_producto"][$grupo]["largo_cm"][$resumen->largo_cm]["totales_caja"][strval($tota_prec)]["ordenes"][$resumen->orden_id] = $resumen->alias . "_" . $resumen->orden_caja_id;
            }
        }
        return $arr;
    }

    private function ayudaResumenOld($arr, $arrPrecios, $resumenCajas) {
        if ($resumenCajas) {
            foreach ($resumenCajas as $resumen) {
                if ((strpos($resumen->largo_cm, "GR_") == 1)) {
                    //debo obtener el largo real de la propiedad
                    $lrg = explode("_", $resumen->largo_cm);
                    $resumen->largo_cm = $lrg[4];
                }


                if (($resumen->tipo_producto == 'N') && ($resumen->largo_cm == 40)) {
                    $grupo = "N";
                } else if (($resumen->tipo_producto == 'N') && ($resumen->largo_cm > 40)) {
                    $grupo = "NXL";
                } else if (($resumen->tipo_producto == 'T') && ($resumen->largo_cm == 40)) {
                    $grupo = "T";
                } else if (($resumen->tipo_producto == 'T') && ($resumen->largo_cm > 40)) {
                    $grupo = "TXL";
                } else {
                    continue;
                }

                $arr[$resumen->store_id][$resumen->tipo_caja_id]["ordenes"][$resumen->orden_id] = $resumen->alias . "_" . $resumen->orden_caja_id;
                $arr[$resumen->store_id][$resumen->tipo_caja_id]["total_cajas"] += 1;

                if (!array_key_exists($grupo, $arr[$resumen->store_id][$resumen->tipo_caja_id]["tipo_producto"])) {
                    $arr[$resumen->store_id][$resumen->tipo_caja_id]["tipo_producto"][$grupo] = array("largo_cm" => array(), "total_cajas" => 0, "ordenes" => array());
                }
                $arr[$resumen->store_id][$resumen->tipo_caja_id]["tipo_producto"][$grupo]["total_cajas"] ++;
                $arr[$resumen->store_id][$resumen->tipo_caja_id]["tipo_producto"][$grupo]["ordenes"][$resumen->orden_id] = $resumen->alias . "_" . $resumen->orden_caja_id;

                if (!array_key_exists($resumen->largo_cm, $arr[$resumen->store_id][$resumen->tipo_caja_id]["tipo_producto"][$grupo]["largo_cm"])) {
                    $arr[$resumen->store_id][$resumen->tipo_caja_id]["tipo_producto"][$grupo]["largo_cm"][$resumen->largo_cm]["total_stems"] = 0;
                    $arr[$resumen->store_id][$resumen->tipo_caja_id]["tipo_producto"][$grupo]["largo_cm"][$resumen->largo_cm]["total_dolar"] = 0;
                    $arr[$resumen->store_id][$resumen->tipo_caja_id]["tipo_producto"][$grupo]["largo_cm"][$resumen->largo_cm]["total_cajas"] = 0;
                    $arr[$resumen->store_id][$resumen->tipo_caja_id]["tipo_producto"][$grupo]["largo_cm"][$resumen->largo_cm]["ordenes"] = array();
                }
                $arr[$resumen->store_id][$resumen->tipo_caja_id]["tipo_producto"][$grupo]["largo_cm"][$resumen->largo_cm]["ordenes"][$resumen->orden_id] = $resumen->alias . "_" . $resumen->orden_caja_id;
                $arr[$resumen->store_id][$resumen->tipo_caja_id]["tipo_producto"][$grupo]["largo_cm"][$resumen->largo_cm]["total_stems"] += $resumen->total_stems;
                $arr[$resumen->store_id][$resumen->tipo_caja_id]["tipo_producto"][$grupo]["largo_cm"][$resumen->largo_cm]["total_cajas"] ++;
                if ($resumen->store_id == 3) {
                    $arr[$resumen->store_id][$resumen->tipo_caja_id]["tipo_producto"][$grupo]["largo_cm"][$resumen->largo_cm]["total_dolar"] += $resumen->total_precio;
                } else {
                    $arr[$resumen->store_id][$resumen->tipo_caja_id]["tipo_producto"][$grupo]["largo_cm"][$resumen->largo_cm]["total_dolar"] += $resumen->total_stems * $arrPrecios[$resumen->store_id][$resumen->largo_cm][$resumen->tipo_producto];
                }
            }
        }
        return $arr;
    }

    private function tabularCajas($arr, $arrPrecios, $resumenCajas, $tinturado) {
        if ($resumenCajas) {
            foreach ($resumenCajas as $resumen) {

                //                error_log(print_r($resumen,true));
                error_log((strpos($resumen->largo_cm, "GR_") == 1));
                if ((strpos($resumen->largo_cm, "GR_") == 1)) {
                    //debo obtener el largo real de la propiedad
                    error_log($resumen->largo_cm);
                    $lrg = explode("_", $resumen->largo_cm);
                    error_log(print_r($lrg, true));
                    $resumen->largo_cm = $lrg[4];
                } else if (($resumen->largo_cm == 1)) {
                    //debo obtener el largo real de la propiedad
                    $resumen->largo_cm = 40;
                }


                //                if (($resumen->tipo_producto == 'N') && ($resumen->largo_cm == 40)) {
                //                    $grupo = "N";
                //                } else if (($resumen->tipo_producto == 'N') && ($resumen->largo_cm > 40)) {
                //                    $grupo = "NXL";
                //                } else if (($resumen->tipo_producto == 'T') && ($resumen->largo_cm == 40)) {
                //                    $grupo = "T";
                //                } else if (($resumen->tipo_producto == 'T') && ($resumen->largo_cm > 40)) {
                //                    $grupo = "TXL";
                //                } else {
                //                    continue;
                //                }
                if (($tinturado == "N") && ($resumen->largo_cm == 40)) {
                    $grupo = "N";
                } else if (($tinturado == "N") && ($resumen->largo_cm > 40)) {
                    $grupo = "NXL";
                } else if (($tinturado == "T") && ($resumen->largo_cm == 40)) {
                    $grupo = "T";
                } else if (($tinturado == "T") && ($resumen->largo_cm > 40)) {
                    $grupo = "TXL";
                } else {
                    continue;
                }
                if ($resumen->store_id == 3) {
                    $tota_prec = $resumen->total_precio;
                } else {
                    error_log($resumen->total_stems);
                    error_log($resumen->store_id);
                    error_log($resumen->largo_cm);
                    error_log($tinturado);
                    $tota_prec = $resumen->total_stems * $arrPrecios[$resumen->store_id][$resumen->largo_cm][$tinturado];
                    error_log($tota_prec);
                }
                if ($tota_prec <= 0)
                    continue;
                $arr[$resumen->store_id]['total_dolares'] += $tota_prec;
                $arr[$resumen->store_id]['total_stems'] += $resumen->total_stems;
                $arr[$resumen->store_id]['cajas'][$resumen->tipo_caja_id]["ordenes"][$resumen->orden_id] = $resumen->alias . "_" . $resumen->orden_caja_id;
                $arr[$resumen->store_id]['cajas'][$resumen->tipo_caja_id]["total_cajas"] += 1;
                $arr[$resumen->store_id]['cajas'][$resumen->tipo_caja_id]["total_dolar"] += $tota_prec;
                $arr[$resumen->store_id]['cajas'][$resumen->tipo_caja_id]["total_stems"] += $resumen->total_stems;

                if (!array_key_exists($grupo, $arr[$resumen->store_id]['cajas'][$resumen->tipo_caja_id]["tipo_producto"])) {
                    $arr[$resumen->store_id]['cajas'][$resumen->tipo_caja_id]["tipo_producto"][$grupo] = array("largo_cm" => array(), "total_cajas" => 0, "ordenes" => array());
                }
                $arr[$resumen->store_id]['cajas'][$resumen->tipo_caja_id]["tipo_producto"][$grupo]["total_cajas"] ++;
                $arr[$resumen->store_id]['cajas'][$resumen->tipo_caja_id]["tipo_producto"][$grupo]["ordenes"][$resumen->orden_id] = $resumen->alias . "_" . $resumen->orden_caja_id;

                if (!array_key_exists($resumen->largo_cm, $arr[$resumen->store_id]['cajas'][$resumen->tipo_caja_id]["tipo_producto"][$grupo]["largo_cm"])) {
                    $arr[$resumen->store_id]['cajas'][$resumen->tipo_caja_id]["tipo_producto"][$grupo]["largo_cm"][$resumen->largo_cm]["total_stems"] = 0;
                    $arr[$resumen->store_id]['cajas'][$resumen->tipo_caja_id]["tipo_producto"][$grupo]["largo_cm"][$resumen->largo_cm]["total_dolar"] = 0;
                    $arr[$resumen->store_id]['cajas'][$resumen->tipo_caja_id]["tipo_producto"][$grupo]["largo_cm"][$resumen->largo_cm]["total_cajas"] = 0;
                    $arr[$resumen->store_id]['cajas'][$resumen->tipo_caja_id]["tipo_producto"][$grupo]["largo_cm"][$resumen->largo_cm]["ordenes"] = array();
                    $arr[$resumen->store_id]['cajas'][$resumen->tipo_caja_id]["tipo_producto"][$grupo]["largo_cm"][$resumen->largo_cm]["totales_caja"] = array();
                }
                $arr[$resumen->store_id]['cajas'][$resumen->tipo_caja_id]["tipo_producto"][$grupo]["largo_cm"][$resumen->largo_cm]["ordenes"][$resumen->orden_id] = $resumen->alias . "_" . $resumen->orden_caja_id;
                $arr[$resumen->store_id]['cajas'][$resumen->tipo_caja_id]["tipo_producto"][$grupo]["largo_cm"][$resumen->largo_cm]["total_stems"] += $resumen->total_stems;
                $arr[$resumen->store_id]['cajas'][$resumen->tipo_caja_id]["tipo_producto"][$grupo]["largo_cm"][$resumen->largo_cm]["total_cajas"] ++;

                $arr[$resumen->store_id]['cajas'][$resumen->tipo_caja_id]["tipo_producto"][$grupo]["largo_cm"][$resumen->largo_cm]["total_dolar"] += $tota_prec;
                if (!array_key_exists(strval($tota_prec), $arr[$resumen->store_id]['cajas'][$resumen->tipo_caja_id]["tipo_producto"][$grupo]["largo_cm"][$resumen->largo_cm]["totales_caja"])) {
                    $arr[$resumen->store_id]['cajas'][$resumen->tipo_caja_id]["tipo_producto"][$grupo]["largo_cm"][$resumen->largo_cm]["totales_caja"][strval($tota_prec)]["total_stems"] = 0;
                    $arr[$resumen->store_id]['cajas'][$resumen->tipo_caja_id]["tipo_producto"][$grupo]["largo_cm"][$resumen->largo_cm]["totales_caja"][strval($tota_prec)]["ordenes"] = array();
                }
                $arr[$resumen->store_id]['cajas'][$resumen->tipo_caja_id]["tipo_producto"][$grupo]["largo_cm"][$resumen->largo_cm]["totales_caja"][strval($tota_prec)]["total_stems"] += $resumen->total_stems;
                $arr[$resumen->store_id]['cajas'][$resumen->tipo_caja_id]["tipo_producto"][$grupo]["largo_cm"][$resumen->largo_cm]["totales_caja"][strval($tota_prec)]["ordenes"][$resumen->orden_id] = $resumen->alias . "_" . $resumen->orden_caja_id;
            }
        }
        return $arr;
    }

    public function valorCaja($filtro, $store_id) {
        $resumen_productos = $this->service_logistica->obtenerResumen($filtro); //resumen de las variantes
        $resumen_propiedades = $this->service_logistica->obtenerResumenPropiedades($filtro); //trae los somponentes flor, componentes
        $precio_caja = 0;
        $total_stems = 0;
        $largo_actual = 0;
        $tinturado_actual = false;
        $mixto = false;
        $detalle = array();

        if ($resumen_productos) {
            if ($resumen_propiedades) {
                $arr = array_merge($resumen_productos, $resumen_propiedades);
            } else {
                $arr = $resumen_productos;
            }
            foreach ($arr as $resumen) {
                $tinturado = "N";
                //print_r($resumen);
                if (strpos($resumen->largo_cm, "GR_C") == 1) {
                    $nombre_producto_ingrediente = $resumen->largo_cm . " | " . $resumen->descripcion;
                } else {
                    $nombre_producto_ingrediente = $resumen->titulo_producto . " | " . $resumen->titulo_variante;
                }
                if ((strpos($resumen->largo_cm, "GR_") == 1)) {
                    if (strpos($resumen->largo_cm, "GR_CT") == 1) {
                        $tinturado = "T";
                    }
                } else {
                    //TODO
                    //$resumen->tipo_producto tiene la explicacion si el ingrediente es natural o tinturado
                    //en ocasiones este ingrediente puede ser accesorio o assorted
                    if ($resumen->tipo_producto == 'T') {
                        $tinturado = "T";
                        //                    } else if (strpos($resumen->sku, "GR_PT") == 1) {
                        //                        $tinturado = "T";
                    }
                    if (($resumen->largo_cm == 1) || ($resumen->largo_cm == 3)) {
                        //debo obtener el largo real de la propiedad
                        $resumen->largo_cm = 40;
                    }
                }
                if ($largo_actual == 0) {
                    $largo_actual = $resumen->longitud;
                } else if ($largo_actual != $resumen->longitud) {
                    //el largo cambio, es una caja mixta, este item debe mostrarse en el grupo de cajas mixtas
                    $mixto = true;
                }
                //                error_log(print_r($mixto, true));
                //                error_log(print_r($tinturado, true));
                //                error_log(print_r($tinturado_actual, true));
                if (!$tinturado_actual) {
                    $tinturado_actual = $tinturado;
                } else if ($tinturado_actual != $tinturado) {
                    $mixto = true;
                }

                //                if ($store_id == 3) {
                //                    $tota_prec = $resumen->total_precio;
                //                } else {
                //                print_r($resumen->store_id);
                //                print_r($resumen->largo_cm);
                //                print_r($tinturado);
                //                }
                //la logica del precio por stem es que ahora sea tomado por lo definido en el ingrediente
                //
                $pps = 0;
                $fecha_carguera = $resumen->fecha_carguera;
                $finca_id = $resumen->finca_id;
                $sku = $resumen->sku;

                $datos_Stem = $this->service_logistica->obtenerPrecioPorStems($fecha_carguera, $finca_id, $resumen->ingrediente_id);
                if ($datos_Stem) {
                    $pps = $datos_Stem->precio_unitario;
                }

                $tota_prec = $resumen->total_stems * $pps;

                $total_stems += $resumen->total_stems;
                $precio_caja += $tota_prec;
                if ($resumen->total_stems != 0) {
                    $detalle[] = array(
                        "producto_variante" => $nombre_producto_ingrediente,
                        "ingrediente" => $resumen->ingrediente_descripcion,
                        "stems" => $resumen->total_stems,
                        "pps" => $pps,
                        "largo_cm" => $resumen->largo_cm,
                        "pagado" => $tota_prec
                    );
                }
            }
        }


        return array("valor_caja" => $precio_caja, "total_stems" => $total_stems, "largo_cm" => $largo_actual, "mixto" => $mixto, "detalle" => $detalle);
    }

    public function valorCajaOld($filtro, $arrPrecios, $store_id) {
        $resumen_productos = $this->service_logistica->obtenerResumen($filtro); //resumen de las variantes
        $resumen_propiedades = $this->service_logistica->obtenerResumenPropiedades($filtro); //trae los somponentes flor, componentes
        $precio_caja = 0;
        $total_stems = 0;
        $largo_actual = 0;
        $tinturado_actual = false;
        $mixto = false;
        $detalle = array();

        error_log(print_r($resumen_productos, true));
        error_log(print_r($resumen_propiedades, true));

        if ($resumen_productos) {
            if ($resumen_propiedades) {
                $arr = array_merge($resumen_productos, $resumen_propiedades);
            } else {
                $arr = $resumen_productos;
            }
            foreach ($arr as $resumen) {
                $tinturado = "N";
                //print_r($resumen);
                if (strpos($resumen->largo_cm, "GR_C") == 1) {
                    $nombre_producto_ingrediente = $resumen->largo_cm . " | " . $resumen->descripcion;
                } else {
                    $nombre_producto_ingrediente = $resumen->titulo_producto . " | " . $resumen->titulo_variante;
                }
                if ((strpos($resumen->largo_cm, "GR_") == 1)) {
                    if (strpos($resumen->largo_cm, "GR_CT") == 1) {
                        $tinturado = "T";
                    }
                } else {
                    //TODO
                    //$resumen->tipo_producto tiene la explicacion si el ingrediente es natural o tinturado
                    //en ocasiones este ingrediente puede ser accesorio o assorted
                    if ($resumen->tipo_producto == 'T') {
                        $tinturado = "T";
                        //                    } else if (strpos($resumen->sku, "GR_PT") == 1) {
                        //                        $tinturado = "T";
                    }
                    if (($resumen->largo_cm == 1) || ($resumen->largo_cm == 3)) {
                        //debo obtener el largo real de la propiedad
                        $resumen->largo_cm = 40;
                    }
                }
                if ($largo_actual == 0) {
                    $largo_actual = $resumen->longitud;
                } else if ($largo_actual != $resumen->longitud) {
                    //el largo cambio, es una caja mixta, este item debe mostrarse en el grupo de cajas mixtas
                    $mixto = true;
                }
                //                error_log(print_r($mixto, true));
                //                error_log(print_r($tinturado, true));
                //                error_log(print_r($tinturado_actual, true));
                if (!$tinturado_actual) {
                    $tinturado_actual = $tinturado;
                } else if ($tinturado_actual != $tinturado) {
                    $mixto = true;
                }

                //                if ($store_id == 3) {
                //                    $tota_prec = $resumen->total_precio;
                //                } else {
                //                print_r($resumen->store_id);
                //                print_r($resumen->largo_cm);
                //                print_r($tinturado);
                //                }
                //la logica del precio por stem es que ahora sea tomado por lo definido en el ingrediente
                //
                if ($store_id == 2) {
                    //EXCEPTO en el caso de wholesale que se maneja el metodo anterior
                    $pps = $arrPrecios[$resumen->store_id][$resumen->largo_cm][$tinturado];
                    $tota_prec = $resumen->total_stems * $pps;
                } else {
                    //vamos a obtener el valor del ingrediente
                    $pps = $resumen->{"costo_" . $resumen->largo_cm};
                    $tota_prec = $resumen->total_stems * $pps;
                }
                //                $pps = $arrPrecios[$resumen->store_id][$resumen->largo_cm][$tinturado];
                //                    $tota_prec = $resumen->total_stems * $pps;

                $total_stems += $resumen->total_stems;
                $precio_caja += $tota_prec;
                if ($resumen->total_stems != 0) {
                    $detalle[] = array(
                        "producto_variante" => $nombre_producto_ingrediente,
                        "ingrediente" => $resumen->ingrediente_descripcion,
                        "stems" => $resumen->total_stems,
                        "pps" => $pps,
                        "largo_cm" => $resumen->largo_cm,
                        "pagado" => $tota_prec
                    );
                }
            }
        }


        return array("valor_caja" => $precio_caja, "total_stems" => $total_stems, "largo_cm" => $resumen->largo_cm, "mixto" => $mixto, "detalle" => $detalle);
    }

    public function obtenerCajasTipo($filtro, $arrTotales) {
        $ordenes_cajas_tipo_caja = $this->service_logistica->obtenerCajasDeTipo($filtro);
        $tinturado_natural_original = $tinturado_natural = $filtro['tinturado'];
        $filtro['tinturado'] = false;

        if ($ordenes_cajas_tipo_caja) {
            foreach ($ordenes_cajas_tipo_caja as $orden_caja_id) {
                $tinturado_natural = $tinturado_natural_original;
                //                var_dump($orden_caja_id->orden_caja_id);
                $filtro['orden_caja_id'] = $orden_caja_id->orden_caja_id;
                $analisisCaja = $this->valorCaja($filtro, $filtro['store_id']);
                //                var_dump($analisisCaja);
                if ($analisisCaja["mixto"]) {
                    $tinturado_natural = "M";
                }
                $arrTotales[$filtro['store_id']]["cajas"][$filtro['tipo_caja_id']]["caja_total_dolar"] += $analisisCaja["valor_caja"];
                $arrTotales[$filtro['store_id']]["cajas"][$filtro['tipo_caja_id']]["caja_total_stems"] += $analisisCaja["total_stems"];
                $arrTotales[$filtro['store_id']]["cajas"][$filtro['tipo_caja_id']]["caja_cajas_id"][$orden_caja_id->alias . "_" . $orden_caja_id->orden_id . "_" . $orden_caja_id->orden_caja_id] = $orden_caja_id->alias . "_" . $orden_caja_id->orden_id . "_" . $orden_caja_id->orden_caja_id;
                $arrTotales[$filtro['store_id']]["cajas"][$filtro['tipo_caja_id']][$tinturado_natural]["total_dolar"] += $analisisCaja["valor_caja"];
                $arrTotales[$filtro['store_id']]["cajas"][$filtro['tipo_caja_id']][$tinturado_natural]["total_stems"] += $analisisCaja["total_stems"];
                $arrTotales[$filtro['store_id']]["cajas"][$filtro['tipo_caja_id']][$tinturado_natural]["cajas_id"][$orden_caja_id->alias . "_" . $orden_caja_id->orden_id . "_" . $orden_caja_id->orden_caja_id] = $orden_caja_id->alias . "_" . $orden_caja_id->orden_id . "_" . $orden_caja_id->orden_caja_id;
                if (!array_key_exists(strval($analisisCaja["valor_caja"]), $arrTotales[$filtro['store_id']]["cajas"][$filtro['tipo_caja_id']][$tinturado_natural]["precios"])) {
                    $arrTotales[$filtro['store_id']]["cajas"][$filtro['tipo_caja_id']][$tinturado_natural]["precios"][strval($analisisCaja["valor_caja"])]['largo_cm'] = array();
                }


                if (!array_key_exists($analisisCaja["largo_cm"], $arrTotales[$filtro['store_id']]["cajas"][$filtro['tipo_caja_id']][$tinturado_natural]["precios"][strval($analisisCaja["valor_caja"])]['largo_cm'])) {
                    $arrTotales[$filtro['store_id']]["cajas"][$filtro['tipo_caja_id']][$tinturado_natural]["precios"][strval($analisisCaja["valor_caja"])]['largo_cm'][$analisisCaja["largo_cm"]] = array();
                }
                if (!array_key_exists($analisisCaja["total_stems"], $arrTotales[$filtro['store_id']]["cajas"][$filtro['tipo_caja_id']][$tinturado_natural]["precios"][strval($analisisCaja["valor_caja"])]['largo_cm'][$analisisCaja["largo_cm"]])) {
                    $arrTotales[$filtro['store_id']]["cajas"][$filtro['tipo_caja_id']][$tinturado_natural]["precios"][strval($analisisCaja["valor_caja"])]['largo_cm'][$analisisCaja["largo_cm"]][$analisisCaja["total_stems"]] = array(
                        'cajas_id' => array(),
                        'total_dolar' => 0,
                        'total_stems' => 0,
                    );
                }

                $arrTotales[$filtro['store_id']]["cajas"][$filtro['tipo_caja_id']][$tinturado_natural]["precios"][strval($analisisCaja["valor_caja"])]['largo_cm'][$analisisCaja["largo_cm"]][$analisisCaja["total_stems"]]['cajas_id'][$orden_caja_id->alias . "_" . $orden_caja_id->orden_id . "_" . $orden_caja_id->orden_caja_id] = $orden_caja_id->alias . "_" . $orden_caja_id->orden_id . "_" . $orden_caja_id->orden_caja_id;
                $arrTotales[$filtro['store_id']]["cajas"][$filtro['tipo_caja_id']][$tinturado_natural]["precios"][strval($analisisCaja["valor_caja"])]['largo_cm'][$analisisCaja["largo_cm"]][$analisisCaja["total_stems"]]["total_dolar"] += $analisisCaja["valor_caja"];
                $arrTotales[$filtro['store_id']]["cajas"][$filtro['tipo_caja_id']][$tinturado_natural]["precios"][strval($analisisCaja["valor_caja"])]['largo_cm'][$analisisCaja["largo_cm"]][$analisisCaja["total_stems"]]["total_stems"] += $analisisCaja["total_stems"];
                //                print_r($analisisCaja);
                $arrTotales[$filtro['store_id']]["cajas"][$filtro['tipo_caja_id']][$tinturado_natural]["precios"][strval($analisisCaja["valor_caja"])]['largo_cm'][$analisisCaja["largo_cm"]][$analisisCaja["total_stems"]]["resumen_caja"][$orden_caja_id->alias . "_" . $orden_caja_id->orden_id . "_" . $orden_caja_id->orden_caja_id]['alias'] = $orden_caja_id->alias . "_" . (isset($orden_caja_id->referencia_order_number) ? $orden_caja_id->referencia_order_number : $orden_caja_id->orden_id) . "_" . $orden_caja_id->orden_caja_id;
                $arrTotales[$filtro['store_id']]["cajas"][$filtro['tipo_caja_id']][$tinturado_natural]["precios"][strval($analisisCaja["valor_caja"])]['largo_cm'][$analisisCaja["largo_cm"]][$analisisCaja["total_stems"]]["resumen_caja"][$orden_caja_id->alias . "_" . $orden_caja_id->orden_id . "_" . $orden_caja_id->orden_caja_id]['detalle'] = $analisisCaja["detalle"];

                $arrTotales[$filtro['store_id']]["store_total_cajas"] ++;
                $arrTotales[$filtro['store_id']]["store_total_stems"] += $analisisCaja["total_stems"];
                $arrTotales[$filtro['store_id']]["store_total_dolar"] += $analisisCaja["valor_caja"];
            }
        }

        return $arrTotales;
    }

    public function obtenerCajasTipoOld($filtro, $arrTotales, $arrPrecios) {
        $ordenes_cajas_tipo_caja = $this->service_logistica->obtenerCajasDeTipo($filtro);
        $tinturado_natural_original = $tinturado_natural = $filtro['tinturado'];
        $filtro['tinturado'] = false;

        if ($ordenes_cajas_tipo_caja) {
            foreach ($ordenes_cajas_tipo_caja as $orden_caja_id) {
                $tinturado_natural = $tinturado_natural_original;
                //                var_dump($orden_caja_id->orden_caja_id);
                $filtro['orden_caja_id'] = $orden_caja_id->orden_caja_id;
                $analisisCaja = $this->valorCaja($filtro, $arrPrecios, $filtro['store_id']);
                //                var_dump($analisisCaja);
                if ($analisisCaja["mixto"]) {
                    $tinturado_natural = "M";
                }
                //                var_dump($tinturado_natural);

                $arrTotales[$filtro['store_id']]["cajas"][$filtro['tipo_caja_id']]["caja_total_dolar"] += $analisisCaja["valor_caja"];
                $arrTotales[$filtro['store_id']]["cajas"][$filtro['tipo_caja_id']]["caja_total_stems"] += $analisisCaja["total_stems"];
                $arrTotales[$filtro['store_id']]["cajas"][$filtro['tipo_caja_id']]["caja_cajas_id"][$orden_caja_id->alias . "_" . $orden_caja_id->orden_id . "_" . $orden_caja_id->orden_caja_id] = $orden_caja_id->alias . "_" . $orden_caja_id->orden_id . "_" . $orden_caja_id->orden_caja_id;
                $arrTotales[$filtro['store_id']]["cajas"][$filtro['tipo_caja_id']][$tinturado_natural]["total_dolar"] += $analisisCaja["valor_caja"];
                $arrTotales[$filtro['store_id']]["cajas"][$filtro['tipo_caja_id']][$tinturado_natural]["total_stems"] += $analisisCaja["total_stems"];
                $arrTotales[$filtro['store_id']]["cajas"][$filtro['tipo_caja_id']][$tinturado_natural]["cajas_id"][$orden_caja_id->alias . "_" . $orden_caja_id->orden_id . "_" . $orden_caja_id->orden_caja_id] = $orden_caja_id->alias . "_" . $orden_caja_id->orden_id . "_" . $orden_caja_id->orden_caja_id;
                if (!array_key_exists(strval($analisisCaja["valor_caja"]), $arrTotales[$filtro['store_id']]["cajas"][$filtro['tipo_caja_id']][$tinturado_natural]["precios"])) {
                    $arrTotales[$filtro['store_id']]["cajas"][$filtro['tipo_caja_id']][$tinturado_natural]["precios"][strval($analisisCaja["valor_caja"])]['largo_cm'] = array();
                }


                if (!array_key_exists($analisisCaja["largo_cm"], $arrTotales[$filtro['store_id']]["cajas"][$filtro['tipo_caja_id']][$tinturado_natural]["precios"][strval($analisisCaja["valor_caja"])]['largo_cm'])) {
                    $arrTotales[$filtro['store_id']]["cajas"][$filtro['tipo_caja_id']][$tinturado_natural]["precios"][strval($analisisCaja["valor_caja"])]['largo_cm'][$analisisCaja["largo_cm"]] = array();
                }
                if (!array_key_exists($analisisCaja["total_stems"], $arrTotales[$filtro['store_id']]["cajas"][$filtro['tipo_caja_id']][$tinturado_natural]["precios"][strval($analisisCaja["valor_caja"])]['largo_cm'][$analisisCaja["largo_cm"]])) {
                    $arrTotales[$filtro['store_id']]["cajas"][$filtro['tipo_caja_id']][$tinturado_natural]["precios"][strval($analisisCaja["valor_caja"])]['largo_cm'][$analisisCaja["largo_cm"]][$analisisCaja["total_stems"]] = array(
                        'cajas_id' => array(),
                        'total_dolar' => 0,
                        'total_stems' => 0,
                    );
                }

                $arrTotales[$filtro['store_id']]["cajas"][$filtro['tipo_caja_id']][$tinturado_natural]["precios"][strval($analisisCaja["valor_caja"])]['largo_cm'][$analisisCaja["largo_cm"]][$analisisCaja["total_stems"]]['cajas_id'][$orden_caja_id->alias . "_" . $orden_caja_id->orden_id . "_" . $orden_caja_id->orden_caja_id] = $orden_caja_id->alias . "_" . $orden_caja_id->orden_id . "_" . $orden_caja_id->orden_caja_id;
                $arrTotales[$filtro['store_id']]["cajas"][$filtro['tipo_caja_id']][$tinturado_natural]["precios"][strval($analisisCaja["valor_caja"])]['largo_cm'][$analisisCaja["largo_cm"]][$analisisCaja["total_stems"]]["total_dolar"] += $analisisCaja["valor_caja"];
                $arrTotales[$filtro['store_id']]["cajas"][$filtro['tipo_caja_id']][$tinturado_natural]["precios"][strval($analisisCaja["valor_caja"])]['largo_cm'][$analisisCaja["largo_cm"]][$analisisCaja["total_stems"]]["total_stems"] += $analisisCaja["total_stems"];
                //                print_r($analisisCaja);
                $arrTotales[$filtro['store_id']]["cajas"][$filtro['tipo_caja_id']][$tinturado_natural]["precios"][strval($analisisCaja["valor_caja"])]['largo_cm'][$analisisCaja["largo_cm"]][$analisisCaja["total_stems"]]["resumen_caja"][$orden_caja_id->alias . "_" . $orden_caja_id->orden_id . "_" . $orden_caja_id->orden_caja_id] = $analisisCaja["detalle"];

                $arrTotales[$filtro['store_id']]["store_total_cajas"] ++;
                $arrTotales[$filtro['store_id']]["store_total_stems"] += $analisisCaja["total_stems"];
                $arrTotales[$filtro['store_id']]["store_total_dolar"] += $analisisCaja["valor_caja"];
            }
        }

        return $arrTotales;
    }

    public function migracionPreciosFinca() {
        //insertar datos en la tabla ingrediente precio finca

        $arrIngrediente = $this->service_logistica->obtenerIngredientes();
        foreach ($arrIngrediente as $k => $ingredienteI) {

            if ($ingredienteI->tipo == 'N') {
                if ($ingredienteI->longitud == 40) {
                    $arrayIngreso = array("ingrediente_id" => $ingredienteI->id, "finca_id" => 2, "precio_unitario" => 0.35, "fecha_inicio_vigencia" => '2020-01-01 12:45:53', "fecha_fin_vigencia" => '2022-12-31 12:45:53', "estado" => 'A');
                    $res = $this->service_logistica->ingresarIng($arrayIngreso);
                } else if ($ingredienteI->longitud == 50) {
                    $arrayIngreso = array("ingrediente_id" => $ingredienteI->id, "finca_id" => 2, "precio_unitario" => 0.45, "fecha_inicio_vigencia" => '2020-01-01 12:45:53', "fecha_fin_vigencia" => '2022-12-31 12:45:53', "estado" => 'A');
                    $res = $this->service_logistica->ingresarIng($arrayIngreso);
                } else if ($ingredienteI->longitud == 60) {
                    $arrayIngreso = array("ingrediente_id" => $ingredienteI->id, "finca_id" => 2, "precio_unitario" => 0.55, "fecha_inicio_vigencia" => '2020-01-01 12:45:53', "fecha_fin_vigencia" => '2022-12-31 12:45:53', "estado" => 'A');
                    $res = $this->service_logistica->ingresarIng($arrayIngreso);
                } else if ($ingredienteI->longitud == 70) {
                    $arrayIngreso = array("ingrediente_id" => $ingredienteI->id, "finca_id" => 2, "precio_unitario" => 0.65, "fecha_inicio_vigencia" => '2020-01-01 12:45:53', "fecha_fin_vigencia" => '2022-12-31 12:45:53', "estado" => 'A');
                    $res = $this->service_logistica->ingresarIng($arrayIngreso);
                } else if ($ingredienteI->longitud == 80) {
                    $arrayIngreso = array("ingrediente_id" => $ingredienteI->id, "finca_id" => 2, "precio_unitario" => 0.75, "fecha_inicio_vigencia" => '2020-01-01 12:45:53', "fecha_fin_vigencia" => '2022-12-31 12:45:53', "estado" => 'A');
                    $res = $this->service_logistica->ingresarIng($arrayIngreso);
                } else if ($ingredienteI->longitud == 90) {
                    $arrayIngreso = array("ingrediente_id" => $ingredienteI->id, "finca_id" => 2, "precio_unitario" => 0.85, "fecha_inicio_vigencia" => '2020-01-01 12:45:53', "fecha_fin_vigencia" => '2022-12-31 12:45:53', "estado" => 'A');
                    $res = $this->service_logistica->ingresarIng($arrayIngreso);
                } else if ($ingredienteI->longitud == 100) {
                    $arrayIngreso = array("ingrediente_id" => $ingredienteI->id, "finca_id" => 2, "precio_unitario" => 0.95, "fecha_inicio_vigencia" => '2020-01-01 12:45:53', "fecha_fin_vigencia" => '2022-12-31 12:45:53', "estado" => 'A');
                    $res = $this->service_logistica->ingresarIng($arrayIngreso);
                }
            } else if ($ingredienteI->tipo == 'T') {
                $arrayIngreso = array("ingrediente_id" => $ingredienteI->id, "finca_id" => 2, "precio_unitario" => 0.85, "fecha_inicio_vigencia" => '2020-01-01 12:45:53', "fecha_fin_vigencia" => '2022-12-31 12:45:53', "estado" => 'A');
                $res = $this->service_logistica->ingresarIng($arrayIngreso);
            } else if ($ingredienteI->tipo == 'ASS') {
                $arrayIngreso = array("ingrediente_id" => $ingredienteI->id, "finca_id" => 2, "precio_unitario" => 0, "fecha_inicio_vigencia" => '2020-01-01 12:45:53', "fecha_fin_vigencia" => '2022-12-31 12:45:53', "estado" => 'A');
                $res = $this->service_logistica->ingresarIng($arrayIngreso);
            } else if ($ingredienteI->tipo == 'A') {
                $arrayIngreso = array("ingrediente_id" => $ingredienteI->id, "finca_id" => 2, "precio_unitario" => 0, "fecha_inicio_vigencia" => '2020-01-01 12:45:53', "fecha_fin_vigencia" => '2022-12-31 12:45:53', "estado" => 'A');
                $res = $this->service_logistica->ingresarIng($arrayIngreso);
            }
        }
    }

    public function resumenCajas() {
        $data['store_id'] = $data['tipo_calendario'] = 0;
        $data['session_finca'] = $this->session->userFincaId;
        $data['rango_busqueda'] = '';
        $data['finca_id'] = 0;
        //        $data['con_tracking_number'] = 'T';
        $data['totales'] = false;
        $data['total_shipping'] = false;
        $data['data'] = false;
        $data['sel_store'] = $this->service_ecommerce->obtenerTiendasSel();
        $data['sel_finca'] = $this->service_general_finca->obtenerSelFinca();
        $data['perfil'] = $this->session->userdata('userPerfil');
        $detalle = '';
        $data['arrTotales'] = false;
        $data['data_guias'] = false;

        if ($this->input->post('btn_buscar') != null) {
            $store_id_seleccionado = $this->input->post('store_id');
            $data['rango_busqueda'] = $this->input->post('rango_busqueda');
            $data['tipo_calendario'] = $this->input->post('tipo_calendario');
            $sel_tipo_caja = $this->service_logistica->obtenerTiposDeCajas();
            $data['finca_id'] = $this->input->post('finca_id');
            $filtro = array(
                "rango_busqueda" => $data['rango_busqueda'],
                "tipo_calendario" => $data['tipo_calendario'],
                "finca_id" => $data['finca_id'],
                "session_finca" => $data['session_finca'],
            );
            $data['total_shipping'] = $this->service_master_shipping->obtener_master_shipping_totales($filtro);
            foreach ($data['sel_store'] as $k => $store) {

                if ($store_id_seleccionado != 0 && ($store_id_seleccionado != $k)) {
                    continue;
                }

                $arrTotales[$k] = array(
                    "nombre_tienda" => $store,
                    "store_total_dolar" => 0,
                    "store_total_stems" => 0,
                    "store_total_cajas" => 0,
                    "cajas" => array(),
                );

                $data['store_id'] = $k;
                foreach ($sel_tipo_caja as $tipo_caja) {
                    //                    if ($tipo_caja->id != 1) continue;
                    $arrTotales[$k]["cajas"][$tipo_caja->id] = array(
                        "nombre_caja" => $tipo_caja->nombre,
                        "caja_total_dolar" => 0,
                        "caja_total_stems" => 0,
                        "caja_cajas_id" => array(),
                        "T" => array("cajas_id" => array(), "total_dolar" => 0, "total_stems" => 0, "precios" => array()),
                        "N" => array("cajas_id" => array(), "total_dolar" => 0, "total_stems" => 0, "precios" => array()),
                        "M" => array("cajas_id" => array(), "total_dolar" => 0, "total_stems" => 0, "precios" => array()),
                    );
                    $valor_caja = 0;
                    $data['tipo_caja_id'] = $tipo_caja->id;
                    //print_r($arrPrecios);
                    $data['tinturado'] = "T";
                    $arrTotales = $this->obtenerCajasTipo($data, $arrTotales);
                    $data['tinturado'] = "N";
                    $arrTotales = $this->obtenerCajasTipo($data, $arrTotales);
                }

                $data['store_id'] = $this->input->post('store_id');
            }

            $data['arrTotales'] = $arrTotales;
            $data['data_guias'] = $this->service_logistica->obtenerGuias($data['rango_busqueda']);
        }

        $data['url_busqueda'] = "produccion/logistica/resumenCajas";
        $data['detalle'] = $detalle;

        $this->mostrarVista('cajas_resumen_precio.php', $data);
    }

    public function obtenerDesglosado($data) {
        //$precios = $this->service_logistica->obtenerCostosFlor();
        $arrTotales = array();
        $arrPrecios = array();
        /*
          foreach ($precios as $precio) {
          $arrPrecios[$precio->store_id][$precio->largo_cm][$precio->tipo] = $precio->costo;
          }
         */
        $sel_tipo_caja = $this->service_logistica->obtenerTiposDeCajas();

        foreach ($data['sel_store'] as $k => $store) {

            if ($data['store_id_seleccionado'] != 0 && ($data['store_id_seleccionado'] != $k)) {
                continue;
            }

            $arrTotales[$k] = array(
                "nombre_tienda" => $store,
                "store_total_dolar" => 0,
                "store_total_stems" => 0,
                "store_total_cajas" => 0,
                "cajas" => array(),
            );

            $data['store_id'] = $k;
            foreach ($sel_tipo_caja as $tipo_caja) {
                //                if ($tipo_caja->id != 1)
                //                    continue;
                $arrTotales[$k]["cajas"][$tipo_caja->id] = array(
                    "nombre_caja" => $tipo_caja->nombre,
                    "caja_total_dolar" => 0,
                    "caja_total_stems" => 0,
                    "caja_cajas_id" => array(),
                    "T" => array("cajas_id" => array(), "total_dolar" => 0, "total_stems" => 0, "precios" => array()),
                    "N" => array("cajas_id" => array(), "total_dolar" => 0, "total_stems" => 0, "precios" => array()),
                    "M" => array("cajas_id" => array(), "total_dolar" => 0, "total_stems" => 0, "precios" => array()),
                );
                $data['tipo_caja_id'] = $tipo_caja->id;

                $data['tinturado'] = "T";
                $arrTotales = $this->obtenerCajasTipo($data, $arrTotales, $arrPrecios);
                $data['tinturado'] = "N";
                $arrTotales = $this->obtenerCajasTipo($data, $arrTotales, $arrPrecios);
            }
        }

        return $arrTotales;
    }

    function resumenCajasPrecioDesglosado() {
        $data['store_id'] = $data['tipo_calendario'] = 0;
        $data['session_finca'] = $this->session->userFincaId;
        $data['rango_busqueda'] = '';
        $data['finca_id'] = 0;
        //        $data['con_tracking_number'] = 'T';
        $data['totales'] = false;
        $data['data'] = false;
        $data['sel_store'] = $this->service_ecommerce->obtenerTiendasSel();
        $data['sel_finca'] = $this->service_general_finca->obtenerSelFinca();
        $data['perfil'] = $this->session->userdata('userPerfil');
        $detalle = '';
        $data['tabla'] = '';
        $data['data_guias'] = false;
        $data['total_shipping'] = false;
        if ($this->input->post('btn_buscar') != null) {
            $data['finca_id'] = $this->input->post('finca_id');
            $data['store_id_seleccionado'] = $this->input->post('store_id');
            $data['rango_busqueda'] = $this->input->post('rango_busqueda');
            $data['tipo_calendario'] = $this->input->post('tipo_calendario');

            $arrTotales = $this->obtenerDesglosado($data);
            $filtro = array(
                "rango_busqueda" => $data['rango_busqueda'],
                "tipo_calendario" => $data['tipo_calendario'],
                "finca_id" => $data['finca_id'],
                "session_finca" => $data['session_finca'],
            );
            $data['total_shipping'] = $this->service_master_shipping->obtener_master_shipping_totales($filtro);
            $data['tabla'] = $this->load->view('cajas_resumen_precio_desglosado_tabla.php', array('arrTotales' => $arrTotales), true);

            $data['store_id'] = $this->input->post('store_id');
            $data['data_guias'] = $this->service_logistica->obtenerGuias($data['rango_busqueda']);
        }


        $data['url_busqueda'] = "produccion/logistica/resumenCajasPrecioDesglosado";
        $data['detalle'] = $detalle;

        $this->mostrarVista('cajas_resumen_precio_desglosado.php', $data);
    }

    public function resumenCajasPrecioDesglosado_excel() {

        $data = json_decode($this->input->get('filtro'), true);

        $filename = "resumenCajas_" . fechaActual('YmdHis') . ".xls";

        header("Content-type:application/octet-stream");
        header("Accept-Ranges:bytes");
        header("Content-type:application/vnd.ms-excel");
        header("Content-Disposition:attachment;filename=" . $filename);
        header("Pragma: no-cache");
        header("Expires: 0");

        //        header("Pragma: public");
        //        header("Expires: 0");
        //        header("Content-Type: application/vnd.ms-excel; charset=utf-8");
        //        header("Content-Disposition: attachment; filename=$filename");
        //        header("Pragma: no-cache");
        //        header("Expires: 0");
        //        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");

        $detalle = '';
        $data['store_id_seleccionado'] = $data['store_id'];
        $data['arrTotales'] = $this->obtenerDesglosado($data);
        $arrTotales = $this->obtenerDesglosado($data);

        $tabla = $this->load->view('cajas_resumen_precio_desglosado_tabla.php', array('arrTotales' => $arrTotales), true);
        //        $data['store_id'] = $this->input->post('store_id');

        $ruta_pdf = FCPATH . "uploads/xls/preparacion/";
        file_put_contents($ruta_pdf . $filename, $detalle);
        print_r($tabla);
    }

    //nuevo separar pdf y obtener el tracking
    function contiene_palabra($content, $palabra) {
        if (preg_match('*\b' . preg_quote($palabra) . '\b*i', $content, $matches, PREG_OFFSET_CAPTURE)) {
            return $matches[0][1];
        }
        return false;  // -1 cuando no se encuentra
    }

    public function subirArchivoPdfTracking($documento) {
        $trackings = array();
        require_once('application/libraries/fpdi/src/autoload.php');
        require_once('application/libraries/fpdi/fpdf.php');
        $dir = 'uploads/tracking/';

        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        $parseador = new Parser();
        foreach ($documento['tmp_name'] as $key => $fichero) {
            //muevo el pdf a mi carpeta tracking
            $fichero = $documento['tmp_name'][$key];
            move_uploaded_file($fichero, $dir . $documento['name'][$key]);

            //obtengo el pdf
            $nombreDocumento = $dir . $documento['name'][$key];
            $pdf = new Fpdi();

            $pageCount = $pdf->setSourceFile($nombreDocumento);

            $contador = 0;

            for ($i = 1; $i <= $pageCount; $i++) {

                $new_pdf = new Fpdi("P", "mm", array(100, 150));
                $new_pdf->AddPage();
                $new_pdf->setSourceFile($nombreDocumento);
                $new_pdf->useTemplate($new_pdf->importPage($i));
                try {

                    $nombre = trim('documento' . $key . $contador);

                    $contador = $contador + 1;
                    $new_filename = $dir . str_replace('.pdf', '', $nombre) . ".pdf";

                    //guardo el pdf
                    $new_pdf->Output($new_filename, "F");

                    $doc = $parseador->parseFile($new_filename);

                    $content = $doc->getText();
                    $posicion1 = $this->contiene_palabra($content, 'TRACKING') + 10;
                    $posicion2 = $this->contiene_palabra($content, 'BILLING');

                    if ($posicion2) {
                        $resultado = substr($content, $posicion1, $posicion2 - $posicion1);

                        $tracking = str_replace(' ', '', $resultado);
//
                        $rutaArchivo1 = $new_filename;
                        $rutaArchivo2 = $dir . trim($tracking) . ".pdf";
                        rename($rutaArchivo1, $rutaArchivo2);
                        $trackings[] = trim($tracking);
                    }
                } catch (Exception $e) {
                    echo 'Caugth exception: ', $e->getMessage(), "\n";
                    $trackings = false;
                }
            }
        }
        return $trackings;
    }
}
