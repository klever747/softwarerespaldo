<?php

use setasign\Fpdi\Fpdi;
use Smalot\PdfParser\Parser;

defined('BASEPATH') or exit('No direct script access allowed');

class Reportes extends MY_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model("ecommerce/service_ecommerce");
        $this->load->model("generales/service_general_finca");
        $this->load->model("produccion/service_logistica");
        $this->load->model("produccion/service_master_shipping");
        $this->load->model("produccion/service_reportes");
    }
    public function resumenCajasSku(){
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
                                "store_total_stems" =>0,
                                "store_total_cajas" => 0,
                                "cajas" => array(),
                            );
      
                            //                    if ($tipo_caja->id != 1) continue;
                    $arr = $this->service_reportes->reporteVentasSKU_($filtro);
                   // $arr_orden_id=$this->service_reportes->reporteVentasObtenerOrdenes($filtro);
                    if($arr){
                        
                        foreach ($arr as $key => $datos) {
                            
                            if($datos->store_name == $store ){
                                
                                if(!array_key_exists($datos->tipo_caja_id,$arrTotales[$k]["cajas"])){
                                    $arrTotales[$k]["cajas"][$datos->tipo_caja_id] = array(
                                        "nombre_caja" => $datos->nombre ,
                                        "caja_total_dolar" => 0,
                                        "caja_total_stems" => 0,
                                        "total_de_cajas" => 0,
                                        "finca_id"=>0,
                                        "tipo_caja_id"=>0,
                                        "alias"=>0,
                                        "longitud"=>array(),
                                       "caja_cajas_id" => array(),
                                        "T" =>array("longitud"=>array(),"cantidad_cajas"=>0, "cantidad_stems"=>0, "precio_total_cajas"=>0),
                                        "N" =>array("longitud"=> array(),"cantidad_cajas"=>0, "cantidad_stems"=>0, "precio_total_cajas"=>0),
                                        "M" =>array("longitud"=> array(),"cantidad_cajas"=>0, "cantidad_stems"=>0, "precio_total_cajas"=>0),
                                    );
                                }
                                    $arr_orden_id=false;
                                      // $arr_orden_id=$this->service_reportes->reporteVentasObtenerOrdenes($filtro,$dataConsulta);
                                    $arrTotales[$datos->store_id]["cajas"][$datos->tipo_caja_id]["tipo_caja_id"] = $datos->tipo_caja_id; 
                                    $arrTotales[$datos->store_id]["cajas"][$datos->tipo_caja_id]["finca_id"] =$datos->finca_id; 
                                    $arrTotales[$datos->store_id]["cajas"][$datos->tipo_caja_id]["alias"] = $datos->alias;
                                    $arrTotales[$datos->store_id]["cajas"][$datos->tipo_caja_id]["caja_total_dolar"] +=$datos->precio_total_cajas * $datos->total_cajas;
                                    $arrTotales[$datos->store_id]["cajas"][$datos->tipo_caja_id]["caja_total_stems"] +=($datos->total_stems * $datos->total_cajas);
                                    $arrTotales[$datos->store_id]["cajas"][$datos->tipo_caja_id]["total_de_cajas"] +=$datos->total_cajas;
                                    
                                  
                                   // $arrTotales[$datos->store_id]["cajas"][$datos->tipo_caja_id]["longitud"][$datos->longitud]["tallos"][$datos->total_stems]["precios"][$datos->precio_total_cajas]["cantidad"] = $datos->total_cajas;
                                    
                                    if(!array_key_exists($datos->longitud ,$arrTotales[$datos->store_id]["cajas"][$datos->tipo_caja_id][$datos->tipo_caja_tnm]["longitud"])){
                                        $arrTotales[$datos->store_id]["cajas"][$datos->tipo_caja_id][$datos->tipo_caja_tnm]["longitud"][$datos->longitud]["tallos"]=array();
                                    }
                                    if(!array_key_exists($datos->total_stems,$arrTotales[$datos->store_id]["cajas"][$datos->tipo_caja_id][$datos->tipo_caja_tnm]["longitud"][$datos->longitud]["tallos"])){
                                        $arrTotales[$datos->store_id]["cajas"][$datos->tipo_caja_id][$datos->tipo_caja_tnm]["longitud"][$datos->longitud]["tallos"][$datos->total_stems]["precios"]=array();
                                    }
                                    if(!array_key_exists($datos->precio_total_cajas,$arrTotales[$datos->store_id]["cajas"][$datos->tipo_caja_id][$datos->tipo_caja_tnm]["longitud"][$datos->longitud]["tallos"][$datos->total_stems]["precios"])){
                                        $arrTotales[$datos->store_id]["cajas"][$datos->tipo_caja_id][$datos->tipo_caja_tnm]["longitud"][$datos->longitud]["tallos"][$datos->total_stems]["precios"][$datos->precio_total_cajas]=array();
                                        $arrTotales[$datos->store_id]["cajas"][$datos->tipo_caja_id][$datos->tipo_caja_tnm]["longitud"][$datos->longitud]["tallos"][$datos->total_stems]["precios"][$datos->precio_total_cajas]["cantidad"]= 0;
                                    }
                                    
                                    $arrTotales[$datos->store_id]["cajas"][$datos->tipo_caja_id][$datos->tipo_caja_tnm]["longitud"][$datos->longitud]["tallos"][$datos->total_stems]["precios"][$datos->precio_total_cajas]["cantidad"] += $datos->total_cajas;
                                    $arrTotales[$datos->store_id]["cajas"][$datos->tipo_caja_id][$datos->tipo_caja_tnm]["cantidad_cajas"]+=$datos->total_cajas;
                                    $arrTotales[$datos->store_id]["cajas"][$datos->tipo_caja_id][$datos->tipo_caja_tnm]["cantidad_stems"]+=($datos->total_stems * $datos->total_cajas);
                                    $arrTotales[$datos->store_id]["cajas"][$datos->tipo_caja_id][$datos->tipo_caja_tnm]["precio_total_cajas"]+=$datos->precio_total_cajas * $datos->total_cajas;
                                    if($arr_orden_id){
                                            foreach ($arr_orden_id as $key => $id_orden_) {
                                                $arrTotales[$datos->store_id]["cajas"][$datos->tipo_caja_id][$datos->tipo_caja_tnm]["longitud"][$datos->longitud]["tallos"][$datos->total_stems]["ordenes"][$datos->alias . "_" . $id_orden_->orden_id . "_" . $datos->tipo_caja] = $datos->alias . "_" . $id_orden_->orden_id . "_" . $datos->tipo_caja;

                                            }
                                        }else{
                                          
                                            $arrTotales[$datos->store_id]["cajas"][$datos->tipo_caja_id][$datos->tipo_caja_tnm]["longitud"][$datos->longitud]["tallos"][$datos->total_stems]["ordenes"][$datos->alias . "_" . $datos->precio_total_cajas . "_" . $datos->total_stems] = $datos->alias . "_" . $datos->precio_total_cajas. "_" . $datos->total_stems;
     
                                        }
                                        $arrTotales[$datos->store_id]["store_total_cajas"] += $datos->total_cajas;
                                        $arrTotales[$datos->store_id]["store_total_stems"] += ($datos->total_stems * $datos->total_cajas) ;
                                        $arrTotales[$datos->store_id]["store_total_dolar"] += $datos->precio_total_cajas * $datos->total_cajas;
                                
                            }
                        }
                    }

                    $data['arrTotales'] = $arrTotales;
                    $data['data_guias'] = $this->service_logistica->obtenerGuias($data['rango_busqueda']);
                }
        }

        $data['url_busqueda'] = "produccion/reportes/resumenCajasSku";
        $data['detalle'] = $detalle;

        $this->mostrarVista('cajas_resumen_precio_sku.php', $data);
    }
    public function json_buscar_ordenes(){
        $parametro_buscar = $this->input->post('parametro_buscar');
        $finca_id = $this->input->post('finca_id');
        $session_finca = $this->input->post('session_finca');
        $filtro = $this->input->post('filtro');
        $arr_datos= explode("_",$parametro_buscar);
        //$filtro['finca_id']=$arr_datos[3];
        $filtro['session_finca']=$this->session->userFincaId;
        $new = explode("m",$arr_datos[2]);
        if(sizeof($new)==2){
            $new_number= $new[0].".".$new[1];
        }else{
            $new_number=$arr_datos[2];
        }
        
        $dataConsulta=array(
            "alias"=>$arr_datos[0],
            "longitud"=>str_replace('-','.', $arr_datos[1]),
            "precioxcaja"=> $new_number,
            "finca_id"=>$arr_datos[3],
            "tipo_caja_id"=>$arr_datos[4],
            "tipo_caja_tnm"=>$arr_datos[5],
            "stemsxcaja"=>$arr_datos[6],
        );
        $arr_orden_id=$this->service_reportes->reporteVentasObtenerOrdenes($filtro,$dataConsulta);
        foreach ($arr_orden_id as $key => $ord_asignar) {
            if($ord_asignar->tipo_caja_tnm == $dataConsulta['tipo_caja_tnm']){
                $arr["ordenes"][$ord_asignar->alias . "_" . $ord_asignar->orden_id . "_" . $ord_asignar->caja_id] = $ord_asignar->alias . "_" .(isset($ord_asignar->referencia_order_number)?($ord_asignar->referencia_order_number):($ord_asignar->orden_id) ). "_" . $ord_asignar->caja_id;
            }
        }
        $id_div = $dataConsulta['alias'] . "_" .reemplazarPunto($dataConsulta['longitud'], "-")."_". ( $dataConsulta['precioxcaja'] >= 0  ? (reemplazarPunto($dataConsulta['precioxcaja'], "m")) :(sizeof($new)==2 ? $arr_datos[2] : $new_number ))."_".$dataConsulta['finca_id']."_".$dataConsulta['tipo_caja_id']."_".$dataConsulta['tipo_caja_tnm'];
        $resumen_ordenes = procesarListaOrdenes($id_div,$dataConsulta['stemsxcaja'], $arr["ordenes"], "$ " . $dataConsulta['precioxcaja'],10,true,false);

        $respuesta = array("error" => !$arr_orden_id, "orden_id" => $id_div.'_'.$dataConsulta['stemsxcaja'], "html"=>$resumen_ordenes, "mensaje" => (!$arr_orden_id ? "" : "ordenes a agregar" ));

        header('Content-Type: application/json');
        echo json_encode($respuesta);
    }
    public function resumen_cajas_precio_sku_xls(){
        $data = json_decode($this->input->get('filtro'), true);
        $finca_id = json_decode($this->input->get('finca_id'), true);
        $data['session_finca'] = $this->session->userFincaId;
        $data['finca_id'] = $finca_id;
        $filename = "resumenCajas_" . fechaActual('YmdHis') . ".xls";

        header("Content-type:application/octet-stream");
        header("Accept-Ranges:bytes");
        header("Content-type:application/vnd.ms-excel");
        header("Content-Disposition:attachment;filename=" . $filename);
        header("Pragma: no-cache");
        header("Expires: 0");

        $detalle = '';
        $data['store_id_seleccionado'] = $data['store_id'];
        //$data['arrTotales'] = $this->obtenerDesglosado($data);
        $arrTotales = $this->obtenerDesglosado($data);

        $tabla = $this->load->view('cajas_resumen_precio_sku_tabla.php', array('arrTotales' => $arrTotales, 'excel'=>true), true);

        $ruta_pdf = FCPATH . "uploads/xls/preparacion/";
        file_put_contents($ruta_pdf . $filename, $detalle);
        print_r($tabla);
    }
    public function obtenerDesglosado($data){
        //$store_id_seleccionado = $this->input->post('store_id');
        $data['sel_store'] = $this->service_ecommerce->obtenerTiendasSel();
        foreach ($data['sel_store'] as $k => $store) {

            if ( $data['store_id_seleccionado'] != 0 && ( $data['store_id_seleccionado'] != $k)) {
                continue;
            }

                    $arrTotales[$k] = array(
                        "nombre_tienda" => $store,
                        "store_total_dolar" => 0,
                        "store_total_stems" =>0,
                        "store_total_cajas" => 0,
                        "cajas" => array(),
                    );

                    //                    if ($tipo_caja->id != 1) continue;
            $arr = $this->service_reportes->reporteVentasSKU_($data);
           // $arr_orden_id=$this->service_reportes->reporteVentasObtenerOrdenes($filtro);
            if($arr){
                
                foreach ($arr as $key => $datos) {
                    if($datos->store_name == $store ){ 
                        
                        if(!array_key_exists($datos->tipo_caja_id,$arrTotales[$k]["cajas"])){
                            $arrTotales[$k]["cajas"][$datos->tipo_caja_id] = array(
                                "nombre_caja" => $datos->nombre ,
                                "caja_total_dolar" => 0,
                                "caja_total_stems" => 0,
                                "total_de_cajas" => 0,
                                "finca_id"=>0,
                                "session_finca"=>0,
                                "alias"=>0,
                               "caja_cajas_id" => array(),
                                "T" =>array("longitud"=>array(),"cantidad_cajas"=>0, "cantidad_stems"=>0, "precio_total_cajas"=>0),
                                "N" =>array("longitud"=> array(),"cantidad_cajas"=>0, "cantidad_stems"=>0, "precio_total_cajas"=>0),
                                "M" =>array("longitud"=> array(),"cantidad_cajas"=>0, "cantidad_stems"=>0, "precio_total_cajas"=>0),
                            );
                        }
                               $dataConsulta=array(
                                   "longitud"=>$datos->longitud,
                                   "total_stems"=>$datos->total_stems,
                                   "precioxcaja"=>$datos->precio_total_cajas,
                               );
                               $arr_orden_id=false;
                              // $arr_orden_id=$this->service_reportes->reporteVentasObtenerOrdenes($filtro,$dataConsulta);
                              $arrTotales[$datos->store_id]["cajas"][$datos->tipo_caja_id]["session_finca"] = $data['session_finca']; 
                              $arrTotales[$datos->store_id]["cajas"][$datos->tipo_caja_id]["finca_id"] =$datos->finca_id; 
                              $arrTotales[$datos->store_id]["cajas"][$datos->tipo_caja_id]["alias"] = $datos->alias;
                              $arrTotales[$datos->store_id]["cajas"][$datos->tipo_caja_id]["caja_total_dolar"] +=$datos->precio_total_cajas * $datos->total_cajas;
                               $arrTotales[$datos->store_id]["cajas"][$datos->tipo_caja_id]["caja_total_stems"] +=($datos->total_stems * $datos->total_cajas);
                               $arrTotales[$datos->store_id]["cajas"][$datos->tipo_caja_id]["total_de_cajas"] +=$datos->total_cajas;
                               $arrTotales[$datos->store_id]["cajas"][$datos->tipo_caja_id]["longitud"][$datos->longitud]["tallos"][$datos->total_stems]["precios"][$datos->precio_total_cajas]["cantidad"]=$datos->total_cajas;
                               $arrTotales[$datos->store_id]["cajas"][$datos->tipo_caja_id][$datos->tipo_caja_tnm]["longitud"][$datos->longitud]["tallos"][$datos->total_stems]["precios"][$datos->precio_total_cajas]["cantidad"] = $datos->total_cajas;
                               $arrTotales[$datos->store_id]["cajas"][$datos->tipo_caja_id][$datos->tipo_caja_tnm]["cantidad_cajas"]+=$datos->total_cajas;
                               $arrTotales[$datos->store_id]["cajas"][$datos->tipo_caja_id][$datos->tipo_caja_tnm]["cantidad_stems"]+=($datos->total_stems * $datos->total_cajas);
                               $arrTotales[$datos->store_id]["cajas"][$datos->tipo_caja_id][$datos->tipo_caja_tnm]["precio_total_cajas"]+=$datos->precio_total_cajas * $datos->total_cajas;
                               if($arr_orden_id){
                                    foreach ($arr_orden_id as $key => $id_orden_) {
                                        $arrTotales[$datos->store_id]["cajas"][$datos->tipo_caja_id][$datos->tipo_caja_tnm]["longitud"][$datos->longitud]["tallos"][$datos->total_stems]["ordenes"][$datos->alias . "_" . $id_orden_->orden_id . "_" . $datos->tipo_caja] = $datos->alias . "_" . $id_orden_->orden_id . "_" . $datos->tipo_caja;

                                    }
                                }else{
                                  
                                    $arrTotales[$datos->store_id]["cajas"][$datos->tipo_caja_id][$datos->tipo_caja_tnm]["longitud"][$datos->longitud]["tallos"][$datos->total_stems]["ordenes"][$datos->alias . "_" . $datos->precio_total_cajas . "_" . $datos->total_stems] = $datos->alias . "_" . $datos->precio_total_cajas. "_" . $datos->total_stems;

                                    
                                }
                                $arrTotales[$datos->store_id]["store_total_cajas"] += $datos->total_cajas;
                                $arrTotales[$datos->store_id]["store_total_stems"] += ($datos->total_stems * $datos->total_cajas) ;
                                $arrTotales[$datos->store_id]["store_total_dolar"] += $datos->precio_total_cajas * $datos->total_cajas;
                        
                    }
                }
            }

            $data['arrTotales'] = $arrTotales;
            //$data['data_guias'] = $this->service_logistica->obtenerGuias($data['rango_busqueda']);
        }
        return $data['arrTotales'];
    }


}