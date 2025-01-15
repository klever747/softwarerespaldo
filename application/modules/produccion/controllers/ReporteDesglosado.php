<?php

use setasign\Fpdi\Fpdi;
use Smalot\PdfParser\Parser;

defined('BASEPATH') or exit('No direct script access allowed');

class ReporteDesglosado extends MY_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model("ecommerce/service_ecommerce");
        $this->load->model("generales/service_general_finca");
        $this->load->model("produccion/service_logistica");
        $this->load->model("produccion/service_master_shipping");
        $this->load->model("produccion/service_reportes");
    }
    public function resumenCajasSkuDesglosado(){
        $data['store_id'] = $data['tipo_calendario'] = 0;
        $data['session_finca'] = $this->session->userFincaId;
        $data['rango_busqueda'] = '';
        $data['finca_id'] = 0;
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
                    $arr = $this->service_reportes->reporteVentasSKU_($filtro);
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
                                $arrTotales[$datos->store_id]["cajas"][$datos->tipo_caja_id]["tipo_caja_id"] = $datos->tipo_caja_id; 
                                $arrTotales[$datos->store_id]["cajas"][$datos->tipo_caja_id]["finca_id"] =$datos->finca_id; 
                                $arrTotales[$datos->store_id]["cajas"][$datos->tipo_caja_id]["alias"] = $datos->alias;
                                $arrTotales[$datos->store_id]["cajas"][$datos->tipo_caja_id]["caja_total_dolar"] +=$datos->precio_total_cajas * $datos->total_cajas;
                                $arrTotales[$datos->store_id]["cajas"][$datos->tipo_caja_id]["caja_total_stems"] +=($datos->total_stems * $datos->total_cajas);
                                $arrTotales[$datos->store_id]["cajas"][$datos->tipo_caja_id]["total_de_cajas"] +=$datos->total_cajas;
                                $arrTotales[$datos->store_id]["cajas"][$datos->tipo_caja_id]["longitud"][$datos->longitud]["tallos"][$datos->total_stems]["precios"][$datos->precio_total_cajas]["cantidad"]=$datos->total_cajas;
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

        $data['url_busqueda'] = "produccion/reporteDesglosado/resumenCajasSkuDesglosado";
        $data['detalle'] = $detalle;

        $this->mostrarVista('cajas_resumen_sku_precio_desglosado.php', $data);
    }
    public function resumenCajasPrecioDesglosado_excel(){
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
        $arrTotales = $this->obtenerDesglosado($data);

        $tabla = $this->load->view('cajas_resumen_precio_sku_desglosado_tabla.php', array('arrTotales' => $arrTotales), true);
        $ruta_pdf = FCPATH . "uploads/xls/preparacion/";
        file_put_contents($ruta_pdf . $filename, $detalle);
        print_r($tabla);
    }
    public function obtenerDesglosado($data){
        foreach ($data['sel_store'] as $k => $store) {

            if ($data['store_id_seleccionado'] != 0 && ($data['store_id_seleccionado']!= $k)) {
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
                        $var_data_precio = $datos->precio_total_cajas;
                        
                        if(!array_key_exists($datos->tipo_caja_id,$arrTotales[$k]["cajas"])){
                            $arrTotales[$k]["cajas"][$datos->tipo_caja_id] = array(
                                "nombre_caja" => $datos->nombre ,
                                "caja_total_dolar" => 0,
                                "caja_total_stems" => 0,
                                "total_de_cajas" => 0,
                                "finca_id"=>0,
                                "tipo_caja_id"=>0,
                                "alias"=>0,
                               "caja_cajas_id" => array(),
                                "T" =>array("longitud"=>array(),"cantidad_cajas"=>0, "cantidad_stems"=>0, "precio_total_cajas"=>0),
                                "N" =>array("longitud"=> array(),"cantidad_cajas"=>0, "cantidad_stems"=>0, "precio_total_cajas"=>0),
                                "M" =>array("longitud"=> array(),"cantidad_cajas"=>0, "cantidad_stems"=>0, "precio_total_cajas"=>0),
                            );
                        }
                            
                            $dataConsulta=array(
                                "alias"=>$datos->alias,
                                "longitud"=>$datos->longitud,
                                "precioxcaja"=>$var_data_precio ,
                                "finca_id"=>$datos->finca_id,
                                "tipo_caja_id"=>$datos->tipo_caja_id,
                                "tipo_caja_tnm"=>$datos->tipo_caja_tnm,
                                "stemsxcaja"=>$datos->total_stems,
                            );
                               $arr_orden_id=false;
                              $arr_orden_id=$this->service_reportes->reporteVentasObtenerOrdenes($data,$dataConsulta);
                              $arrTotales[$datos->store_id]["cajas"][$datos->tipo_caja_id]["tipo_caja_id"] = $datos->tipo_caja_id; 
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
                                        $arr_ordenes_desglosado=$this->service_reportes->reporteVentasObtenerOrdenesDesglosado($data,$dataConsulta,$id_orden_->orden_id); 
                                        unset($detalle_orden);
                                        foreach ($arr_ordenes_desglosado as $det_ord => $detalle) {
                                           
                                            $detalle_orden[$det_ord]=array(
                                                "id_caja"=>$detalle->caja_id,
                                                "alias"=>$datos->alias,
                                                "producto"=>$detalle->titulo_producto,
                                                "variante"=>$detalle->titulo_variante,
                                                "precio_unit"=>number_format($detalle->precio_unitario, 2),
                                                "cantidad"=>$detalle->cantidad_items,
                                                "stems"=>$detalle->total_stems,
                                                "t_tinturados"=>$detalle->totaltinturadoxcaja,
                                                "t_naturales"=>$detalle->totalnaturalesxcaja,
                                                "largo_cm"=>$detalle->longitud,
                                                "precio_total"=>number_format($detalle->precioxcaja, 2),
                                            );  
                                           
                                        }   
                                        $arrTotales[$datos->store_id]["cajas"][$datos->tipo_caja_id][$datos->tipo_caja_tnm]["longitud"][$datos->longitud]["tallos"][$datos->total_stems]["detalle_orden"][ $datos->alias . "_" . $id_orden_->orden_id . "_" . $datos->tipo_caja_id]['alias']=  $datos->alias . "_" . $id_orden_->referencia_order_number . "_" . $datos->tipo_caja_id;
                                        $arrTotales[$datos->store_id]["cajas"][$datos->tipo_caja_id][$datos->tipo_caja_tnm]["longitud"][$datos->longitud]["tallos"][$datos->total_stems]["detalle_orden"][ $datos->alias . "_" . $id_orden_->orden_id . "_" . $datos->tipo_caja_id]['detalle']=  $detalle_orden;      
                                        $arrTotales[$datos->store_id]["cajas"][$datos->tipo_caja_id][$datos->tipo_caja_tnm]["longitud"][$datos->longitud]["tallos"][$datos->total_stems]["ordenes"][$datos->alias . "_" . $id_orden_->orden_id . "_" . $datos->tipo_caja_id] = $datos->alias . "_" . $id_orden_->orden_id . "_" . $datos->tipo_caja_id;
                                        
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
        return  $data['arrTotales'];
    }
    public function json_buscar_ordenes_desglosado(){
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
            "precioxcaja"=>$new_number,
            "finca_id"=>$arr_datos[3],
            "tipo_caja_id"=>$arr_datos[4],
            "tipo_caja_tnm"=>$arr_datos[5],
            "stemsxcaja"=>$arr_datos[6],
        );
        $detalle_ordenes[]= array();
        $detalle_orden_html = "";
        $detalle_orden_html = '<tr>
                <th>Caja</th>
                <th width="20%">Producto/Variante</th>
                <th>#Accesorios </th>
                <th>#Items </th>
                <th>cm</th>
                <th>P.U</th>
                <th>Total Stems </th>
                <th>T</th>
                <th>N</th>             
                <th>Total</th>
                </tr>';
        $id_div = $dataConsulta['alias'] . "_" .reemplazarPunto($dataConsulta['longitud'], "-")."_".( $dataConsulta['precioxcaja'] >0? (reemplazarPunto($dataConsulta['precioxcaja'], "m")) : (sizeof($new)==2 ? $arr_datos[2] : $new_number ))."_".$dataConsulta['finca_id']."_".$dataConsulta['tipo_caja_id']."_".$dataConsulta['tipo_caja_tnm'];
        $arr_orden_id=$this->service_reportes->reporteVentasObtenerOrdenes($filtro,$dataConsulta);
        foreach ($arr_orden_id as $key => $ord_asignar) {
            if($ord_asignar->tipo_caja_tnm == $dataConsulta['tipo_caja_tnm']){
            $arr_ordenes_desglosado=$this->service_reportes->reporteVentasObtenerOrdenesDesglosado($filtro,$dataConsulta,$ord_asignar->orden_id);           
            if($arr_ordenes_desglosado){
                $tamaño=sizeof($arr_ordenes_desglosado)+1;
            }
            
            $detalle_orden_html .= '
            <tr>
            <th '.(sizeof($arr_ordenes_desglosado)>1 ? 'rowspan="'. $tamaño.'"':'rowspan=2').'>
                <a href="#modalOrden" class="btn btn-orden-numero" data-toggle="modal" data-target="#modalOrden" data-orden_id="'.$ord_asignar->orden_id.'" data-caja_id="'.$ord_asignar->caja_id.'" data-variante_id="RSH_'.$id_div.'" style="text-align:left; font-size: 0.75em; padding:0"><b>RSH_'.$ord_asignar->referencia_order_number."_".$ord_asignar->caja_id.'</b></a>
                </th>
            </tr>';
            foreach ($arr_ordenes_desglosado as $ord_id => $orden_desglosada) {
                $accesorio= '';
                if($orden_desglosada->sin_wrap >0 && $orden_desglosada->wrap >0 && $orden_desglosada->flower_vase >0){
                    $accesorio= 'Sin Wrap: '.$orden_desglosada->sin_wrap." ".'Wrap: '.$orden_desglosada->wrap." ". 'Flower Vase: '.$orden_desglosada->flower_vase;
                }else if($orden_desglosada->luxury >0 && $orden_desglosada->flower_vase >0){
                    $accesorio= 'Luxury: '.$orden_desglosada->luxury." ".'Flower Vase: '.$orden_desglosada->flower_vase;
                }else if($orden_desglosada->sin_wrap >0){
                    $accesorio= 'Sin Wrap: '.$orden_desglosada->sin_wrap;
                }else if($orden_desglosada->wrap >0){
                    $accesorio= 'Wrap: '.$orden_desglosada->wrap;
                }else if($orden_desglosada->luxury ){
                    $accesorio= 'Luxury: '.$orden_desglosada->luxury;
                }else if($orden_desglosada->flower_vase>0){
                    $accesorio= 'Flower Vase: '.$orden_desglosada->flower_vase;
                }else{
                    $accesorio = 'Sin accesorio';
                }
                $detalle_orden_html .= '
                <tr>
                    <td width="26%">'.$orden_desglosada->titulo_producto.'<br>'
                    .$orden_desglosada->titulo_variante.'</td>
                    <td>'.$accesorio.'</td>
                    <td>'.$orden_desglosada->cantidad_items.'</td>
                    <td>'.$orden_desglosada->longitud.'</td>
                    <td class="celda_moneda">$ '.number_format($orden_desglosada->precio_unitario, 2).'</td>
                    <td>'.$orden_desglosada->total_stems.'</td>
                    <td>'.$orden_desglosada->totaltinturadoxcaja.'</td>
                    <td>'.$orden_desglosada->totalnaturalesxcaja.'</td>
                    
                    <td class="celda_moneda">$ '.number_format($orden_desglosada->precioxcaja, 2).'</td>
                </tr>';
                $arr["ordenes"][$ord_asignar->alias . "_" . $ord_asignar->orden_id . "_" . $ord_asignar->caja_id] = $ord_asignar->alias . "_" .(isset($ord_asignar->referencia_order_number)?($ord_asignar->referencia_order_number):($ord_asignar->orden_id) ). "_" . $ord_asignar->caja_id;
            }
        }
        }
        $resumen_ordenes = procesarListaOrdenes($id_div,$dataConsulta['stemsxcaja'], $arr["ordenes"], "$ " . $dataConsulta['precioxcaja'],10,true,false);

        $respuesta = array("error" => !$arr_orden_id, "orden_id" => $id_div.'_'.$dataConsulta['stemsxcaja'], "html"=>$resumen_ordenes, "detalle_orden_html"=>$detalle_orden_html, "id_div"=>$id_div."_".$dataConsulta['stemsxcaja'], "mensaje" => (!$arr_orden_id ? "" : "ordenes a agregar" ));

        header('Content-Type: application/json');
        echo json_encode($respuesta);
    }
}