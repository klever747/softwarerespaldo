<?php

class Service_ecommerce extends My_Model {
    /*     * *********************************************************** */
    /*     * *************STORE************ */

    public function obtenerTiendas($id = false, $session_finca = false) {
        $this->db->select('s.*');
        $this->db->from('ecommerce.store s');
        $this->db->where('s.estado', ESTADO_ACTIVO);
        $arrayfinca = explode(",", $session_finca);
        if ($id) {
            $this->db->where('s.estado', ESTADO_ACTIVO);
            $this->db->where('id', $id);
            return $this->retornarUno();

        } else if (!in_array(FINCA_ROSAHOLICS_ID,$arrayfinca)) {
            $this->db->join('general.store_tipo_finca stf', 'stf.store_id = s.id', 'left');
            $this->db->join('general.finca f ', ' f.tipo_finca = stf.tipo_finca', 'left');
            $this->db->where('s.estado', ESTADO_ACTIVO);
            $this->db->where('f.estado', ESTADO_ACTIVO);
            $srt = "f.id in (".$session_finca.")";
            $this->db->where($srt);
            return $this->retornarMuchosSinPaginacion(true);
        } else {
            $this->db->where('s.estado', ESTADO_ACTIVO);
            return $this->retornarMuchosSinPaginacion();
        }
    }

    public function obtenerTiendasSel() {
        $session_finca = $this->session->userFincaId;
        $tiendas = $this->obtenerTiendas(false, $session_finca);
        return $this->retornarSel($tiendas, "store_name");
    }

    public function crearLinea($datos) {
        $datos['estado'] = ESTADO_ACTIVO;
        return $this->ingresar("ecommerce.orden_item", $datos, true, true);
    }

    public function actualizarLinea($obj) {
        $actualizacion = $this->actualizar("ecommerce.orden_item", $obj);
        return $actualizacion;
    }

    /*     * ******************************************************************* */

    public function convertir_items_orden($objOrden, $arr_ordenes) {
        print_r("</br>============================================= convertir_items_orden  ===================================================");
        $contieneTinturados = false;
        $linea_creada_id = false;
        $error_linea = false;
        print_r($arr_ordenes);
        $secuencial = 1;
        $propAdicionalPadre = '';
        $propAdicional = '';
        foreach ($arr_ordenes as $fecha => $item) {
            //INICIO TEMA ORDEN ID
            if (sizeof($item) == 0) {
                continue;
            }
            $orden_id = 0;
            print_r("</br>Fecha : ");
            print_r($fecha);
            $objOrdenaux = $objOrden;
            if ($fecha != 'NO_DEFINIDO') {
                $fecha_entrega = convertirFechaStore($fecha);
                print_r("</br>Nueva:</br>");
                print_r($fecha_entrega);
                // print_r("</br>***********************************</br>");
                error_log(print_r($objOrden, true));
                //print_r("</br>***********************************</br>");
                $objOrdenaux['fecha_entrega'] = $fecha_entrega;
            } else {
                $fecha_entrega = false;
            }
            $objOrdenaux['secuencial'] = $secuencial;
            $orden = $this->service_ecommerce_orden->existeOrden($objOrdenaux);
            //      print_r("</br>********************************************************************************************************</br>");
            //     print_r("Orden: ");print_r($orden);
            // print_r("</br>***********************************</br>");
            // print_r("Item:  ");print_r($item);
            // print_r("</br>***********************************</br>");
            //  die();
            print_r("</br>******************************************************************************************************************************</br>");
            if ($orden) {
                //echo "<br/>Actualizamos orden<br/>";
                print_r("</br> + Actualizada </br>");
                error_log(print_r("Actualizamos orden", true));
                $objOrdenaux['id'] = $orden->id;
                if (!$this->service_ecommerce_orden->actualizarOrden($objOrdenaux)) {
                    return array("error" => "Problemas al momento de actualizar la orden");
                }
                $orden_id = $orden->id;
                $this->service_ecommerce_orden->eliminarLineasOrden($orden_id);
            } else {
                // echo "<br/>Orden Nueva<br/>";
                // error_log(print_r("Nueva orden", true));
                print_r("</br> + Nueva </br>");
                $orden_id = $this->service_ecommerce_orden->crearOrden($objOrdenaux);
                $objOrdenaux['id'] = $orden_id;
            }
            //FIN TEMA ORDEN ID
            $error_linea = false;

            print_r("  Orden:  ");
            print_r($orden_id);
            print_r("</br> * Fecha de entrega: ");
            print_r($fecha_entrega);
            print_r("</br>******************************************************************************************************************************</br>");
            //INI foreach items_details
            foreach ($item as $k => $item_details) {
                $error_linea = false;
                print_r("<br/>");
                // INICIO $det->shopify_product_id
                //  if ( $item_details['padre']->shopify_product_id != null )
                //   {
                if ((strpos($item_details['padre']->sku, 'AGR_P') !== false) || (strpos($item_details['padre']->sku, 'AGR_F') !== false)) {
                    if (strpos($item_details['padre']->sku, 'AGR_PT') !== false) {
                        $contieneTinturados = true;
                    }
                    $sku_prod_arr = explode("_", $item_details['padre']->sku);
                    $producto = array(
                        "titulo" => $item_details['padre']->title,
                        "descripcion" => $item_details['padre']->name,
                        "sku_prefijo" => $sku_prod_arr[0] . "_" . $sku_prod_arr[1] . "_" . $sku_prod_arr[2],
                    );
                    $producto_persistencia = $this->service_ecommerce_producto->persistenciaProducto($producto);

                    if (!$producto_persistencia[1]) {
                        $error_linea = "Problemas al momento de crear el producto";
                        $lineas_errores++;
                        break;
                    }
                    $producto_id = $producto_persistencia[1];
                    $producto_variante = array(
                        "producto_id" => $producto_id,
                        "titulo" => $item_details['padre']->variant_title,
                        "sku" => $item_details['padre']->sku,
                        "cantidad" => is_numeric($sku_prod_arr[3]) ? $sku_prod_arr[3] : 1,
                        "largo_cm" => is_numeric($sku_prod_arr[4]) ? $sku_prod_arr[4] : 1,
                    );
                    $producto_variante_persistencia = $this->service_ecommerce_producto->persistenciaProductoVariante($producto_variante);

                    if (!$producto_variante_persistencia[1]) {
                        $error_linea = "Problemas al momento de crear la variante";
                        $lineas_errores++;
                        break; //nos saltamos al siguiente item
                    }
                    $producto_variante_id = $producto_variante_persistencia[1];

                    $linea = array(
                        "orden_id" => $orden_id,
                        "producto_id" => $producto_id,
                        "variante_id" => $producto_variante_id,
                        "cantidad" => $item_details['padre']->quantity * 1,
                        "precio" => $item_details['padre']->price,
                    );

                    $linea_creada_id = $this->crearLinea($linea);
                    if (!$linea_creada_id) {
                        $error_linea = "Problemas al momento de crear linea de orden " . $orden_id;
                        $lineas_errores++;
                        break;
                    }
                }

                // print_r("Linea creada: "); print_r($linea_creada_id);
                //Comienzo a revisar las propiedades
                $arr_propiedades = json_decode($item_details['padre']->properties, true);
                $propAdicionalPadre = $item_details['padre']->properties;
                $propAdicional = '';
                //print_r("<br/>-------------------------------------- Propiedades --------------------------------------<br/>");
                foreach ($arr_propiedades as $valor) {
                    // print_r("<br/>");
                    //  print_r(" * ");print_r($valor['name']);print_r(" : ");print_r($valor['value']);

                    if ((strtoupper($valor['name']) === 'CUSTOM PRODUCT') || (strtoupper($valor['name']) === 'MAIN_ID')) {
                        print_r("<br/>");
                        foreach ($item_details['hijos'] as $s) {
                            $propAdicional = $s->properties;
                        }
                    }
                }
                $atr_hijos = $item_details['hijos'];

                /* Proceso para guardar a la tabla items_propiedades INI */
                if ((!empty($propAdicionalPadre)) || (!empty($propAdicional))) {
                    if (!$linea_creada_id) {
                        $error_linea = true;
                        continue;
                    }
                    $arr_propiedades = json_decode($propAdicionalPadre, true);
                    $arr_adicional = json_decode($propAdicional, true);
                    if ($arr_adicional != null) {
                        $arr_propiedades = array_merge($arr_propiedades, $arr_adicional);
                    }
                    $arr_propiedades_guardar = array();

                    foreach ($arr_propiedades as $valor) {
                        $propiedad = $this->service_ecommerce_producto->devolverOcrearPropiedad($valor['name']);
                        if (strtoupper($valor['name']) === 'MESSAGE') {
                            $arr = explode("\"name\":\"Message\",\"value\":\"", $valor['value']);
                            if (sizeof($arr) > 1) {
                                $arr = explode("\"}", $arr[1]);
                            }
                            $valor['value'] = utf8_encode(htmlentities(html_entity_decode($arr[0])));
                        }
                        $arr_propiedades_guardar[$propiedad->id] = $valor['value'];
                    }
                    /**/
                    // print_r("<br/>.................................. propiedades hijos toca revisar ........................................<br/>");

                    if (!empty($atr_hijos)) {
                        $arr_propiedades_hijos_guardar = array();
                        // print_r($atr_hijos);
                        print_r("</br>******************************************************************************************************************************</br>");
                        foreach ($atr_hijos as $val_atr_hijos) {
                            if (empty($val_atr_hijos->sku)) {
                                break;
                            } else {
                                $propiedad = $this->service_ecommerce_producto->devolverOcrearPropiedad($val_atr_hijos->sku); // ESTE
                                // ORDEN ITEM PROPIEDAS ID
                                // Y AGREGAR QUANTITY
                                print_r("<br/>");
                                //print_r($propiedad);
                                if (strtoupper($propiedad->nombre) == strtoupper($propiedad->descripcion)) {
                                    $obj = array(
                                        "id" => $propiedad->id,
                                        "nombre" => $propiedad->nombre,
                                        "descripcion" => $val_atr_hijos->title,
                                        "estado" => ESTADO_ACTIVO,);
                                    $this->service_ecommerce_producto->actualizarPropiedad($obj);
                                }
                                print_r("</br>");
                                print_r(" - Propiedad creada:  ");
                                print_r($propiedad->id);
                                print_r(" Descripcion  ");
                                print_r($propiedad->descripcion);
                                print_r(" Cantidad  ");
                                print_r($val_atr_hijos->quantity);
                                $this->persistenciaOrdenItemPropiedad($linea_creada_id, $propiedad->id, $val_atr_hijos->quantity);
                            }
                        }
                        print_r("</br>******************************************************************************************************************************");
                    }

                    //print_r("<br/>.................................. propiedades hijos toca revisar ........................................<br/>");
                    /**/
                    print_r(" ** Propiedades unificadas:  ");
                    foreach ($arr_propiedades_guardar as $k => $v) {
                        print_r("</br> . Propiedad creada:  ");
                        print_r($k);
                        print_r("  Descripcion  ");
                        print_r($v);
                        $this->persistenciaOrdenItemPropiedad($linea_creada_id, $k, $v);
                    }
                }
            }
            /* Proceso para guardar a la tabla items_propiedades FIN */
            $secuencial = $secuencial + 1;
            print_r("</br>Secuencial</br>");
            print_r($secuencial);
            /* Fecha entrega */

            if ($fecha_entrega) {
                $objOrdenaux['fecha_carguera'] = $this->calcularFechaCarguera($fecha_entrega);
                $objOrdenaux['fecha_preparacion'] = $this->calcularFechaPreparacion($objOrdenaux['fecha_carguera'], $contieneTinturados);
            }
            if (($error_linea) || !($fecha_entrega)) {
                $objOrdenaux['estado'] = ESTADO_ERROR;
                //$this->service_ecommerce_orden->actualizarOrden($objOrdenaux);
            }
            print_r("</br> dddd");
            print_r($objOrdenaux);
            print_r("</br>");
            if (!$this->service_ecommerce_orden->actualizarOrden($objOrdenaux)) {
                //return false;
            }

            /* Fecha entrega */
            // }//if no definido
        }//foreach principal
        print_r("</br>============================================= convertir_items_orden  ===================================================");
    }

    public function convertirItemsAOrden($order_detalle, $orden_id, $arrCustomization) {
        $fecha_delivery = false;
        $linea_assemble = false;
        $linea_creada_id = false;
        $contieneTinturados = false;
        $lineas_errores = 0;
        $arr_lineas_nueva_orden = array();
        //este es un arreglo que va a contener los items que van a crearse
        //en una nueva orden debido a que la fecha de entrega es diferente
//            error_log(print_r($order_detalle, true));  die;
//        $arrCustomization = array();
        //pRODUYCTO SI EXISTE LO CREO

        $error_linea = false;
        print_r("<br/>*********************CONVERTIRITEMSAORDEN****************<br/>");
        foreach ($order_detalle as $det) {
            $nuevaOrden = false;
            echo "<br/>";
            print_r("<br/>*********************DETALLE A PROCESAR****************<br/>");
            error_log(print_r(print_r($det, true)));
            print_r($det);
            echo "<br/>";
            $error_linea = false;
            print_r("<br/>ENTRAMOS<br/>");
            print_r($linea_creada_id);
            print_r("<br/>PASAMOS<br/>");

            if ($det->shopify_product_id != null) {
                print_r("<br/>ENTRAMOS if<br/>");
                if ((strpos($det->sku, 'AGR_P') !== false) || (strpos($det->sku, 'AGR_F') !== false)) {
                    print_r("<br/>ENTRAMOS68<br/>");
                    if (strpos($det->sku, 'AGR_PT') !== false) {
                        $contieneTinturados = true;
                    }
                    $sku_prod_arr = explode("_", $det->sku);
                    $producto = array(
                        "titulo" => $det->title,
                        "descripcion" => $det->name,
                        "sku_prefijo" => $sku_prod_arr[0] . "_" . $sku_prod_arr[1] . "_" . $sku_prod_arr[2], //AGR_TIPOELEMENTO_DENOMINACION
                    );
                    $producto_persistencia = $this->service_ecommerce_producto->persistenciaProducto($producto);

                    if (!$producto_persistencia[1]) {
                        $error_linea = "Problemas al momento de crear el producto";
                        $lineas_errores++;
                        break;
                    }

                    $producto_id = $producto_persistencia[1];
                    $producto_variante = array(
                        "producto_id" => $producto_id,
                        "titulo" => $det->variant_title,
                        "sku" => $det->sku,
                        "cantidad" => is_numeric($sku_prod_arr[3]) ? $sku_prod_arr[3] : 1,
                        "largo_cm" => is_numeric($sku_prod_arr[4]) ? $sku_prod_arr[4] : 1,
                    );
                    $producto_variante_persistencia = $this->service_ecommerce_producto->persistenciaProductoVariante($producto_variante);

                    if (!$producto_variante_persistencia[1]) {
                        $error_linea = "Problemas al momento de crear la variante";
                        $lineas_errores++;
                        break; //nos saltamos al siguiente item
                    }
                    $producto_variante_id = $producto_variante_persistencia[1];

                    $linea = array(
                        "orden_id" => $orden_id,
                        "producto_id" => $producto_id,
                        "variante_id" => $producto_variante_id,
                        "cantidad" => $det->quantity * 1,
                        "precio" => $det->price,
                    );

                    $linea_creada_id = $this->crearLinea($linea);
                    if (!$linea_creada_id) {
                        $error_linea = "Problemas al momento de crear linea de orden " . $orden_id;
                        $lineas_errores++;
                        break;
                    }
                }

                if (strpos($det->sku, 'AGR_P_ASS_XXX') !== false) {
                    print_r("<br/>ENTRAMOS118<br/>");
                    if (!$linea_assemble) {
                        print_r("<br/>NO existe una linea de assemble previa");
                        $linea_assemble = $this->obtenerOrdenItem($linea_creada_id);
                    } else {
                        print_r("<br/>SI, YA existe una linea de assemble previa");
                        //ya existe otro producto assemble si este nuevo assemble
                        //tiene la misma fecha se lo salta, si no tiene la misma fecha
                        //implica que estas lineas son de otra orden
                        //las agrupamos en el arreglo $arr_lineas_nueva_orden
                    }
//                } else if (strpos($det->sku, 'AGR_P') !== false) {
//                    //es un producto P o PT
                } else if (strpos($det->sku, 'AGR_C') !== false) {
                    print_r("<br/>ENTRAMOS132<br/>");
                    print_r("es un componente C o CT");
                    //pasan a ser un detalle del assemble
                    if ($linea_assemble) {
                        $propiedad = $this->service_ecommerce_producto->devolverOcrearPropiedad($det->sku);
                        $this->persistenciaOrdenItemPropiedad($linea_assemble->id, $propiedad->id, $det->quantity);
                    } else {
                        $error_linea = true;
                    }
                } else if (strpos($det->sku, 'AGR_A') !== false) {
                    //es un accesorio Vase, Wrap (STW, Petal
                    print_r("<br/>--------------------- ASEMBLE ---------------------<br/>");
                }
            }
            print_r("<br/>ENTRAMOS<br/>");
            print_r($linea_creada_id);
            print_r("<br/>PASAMOS<br/>");
//            //Para este item debemos ver si es un CUSTOMIZATION DEL PRODUCTO ANTERIOR
//            if (strpos($det->title, 'Customization Cost for') != false) {
//                //esta linea es customizacion de otra linea
//                //obtengo sus propiedades y lo guardo en un arreglo para poder asignarlo a quien corresponda
            $arr_propiedades = json_decode($det->properties, true);
            $propAdicional = '';
            print_r("Propiedades es ");
            print_r($arr_propiedades);
            foreach ($arr_propiedades as $valor) {
                if (strtoupper($valor['name']) === 'CUSTOM PRODUCT') {
                    if (array_key_exists($valor['value'], $arrCustomization)) {
                        $propAdicional = $arrCustomization[$valor['value']];
                    }
                }
            }
//            }
            //al producto anterior le guardamos el detalle de sus accesorios
            //$producto_anterior = $this->obtenerOrdenItem($linea_creada_id);
//            error_log(print_r($linea_creada_id, true));
            print_r("<br/>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>><br/>");
            print_r(">>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>><br/>");
            print_r(">>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>><br/>");
            var_dump($det->properties);
            var_dump($propAdicional);

            if (($det->properties != null) && (!empty($det->properties)) || (!empty($propAdicional))) {
                print_r("<br/>ENTRO</br>");
                if (!$linea_creada_id) {
                    $error_linea = true;
                    continue;
                }
                print_r("<br/>ENTRO</br>");
                $arr_propiedades = json_decode($det->properties, true);
                $arr_adicional = json_decode($propAdicional, true);
                if ($arr_adicional != null) {
                    $arr_propiedades = array_merge($arr_propiedades, $arr_adicional);
                }
                echo "<br/> Propeidades es :";
                print_r($arr_propiedades);
                echo "<br/>";
                $custom_product_esta_linea = false;
                $arr_propiedades_guardar = array();
                foreach ($arr_propiedades as $valor) {
                    $propiedad = $this->service_ecommerce_producto->devolverOcrearPropiedad($valor['name']);
                    if (strtoupper($valor['name']) === 'DATE OF DELIVERY') {
                        if (!$fecha_delivery) {
                            print_r("Fecha Delivery vacia");
                            $fecha_delivery = $valor['value'];
                            print_r("Fecha Delivery ahora es " . print_r($fecha_delivery, true));
                        } else {
                            //comparo las 2 fechas
                            if ($fecha_delivery != $valor['value']) {
                                print_r("Fecha Delivery no es vacia ni igual " . $valor['value']);
                                //tiene una fecha de entrega diferente,
                                //debo agregar este item a otra orden
                                $arr_lineas_nueva_orden[] = $det;
                                //inactivamos este item porque mas adelante se lo va a agregar

                                $obj = array("id" => $linea_creada_id, "estado" => ESTADO_INACTIVO);
                                $this->actualizarLinea($obj);
                                $nuevaOrden = true;
                            }
                        }
                    }
                    if (strtoupper($valor['name']) === 'CUSTOM PRODUCT') {
                        $custom_product_esta_linea = $valor['value'];
                    }

                    if (strtoupper($valor['name']) === 'MESSAGE') {
//                        echo "<br/>MMMMMMMMMMMMMMMMMMMMMMMMEEEEEEEEEEEEEEEEENNNNNNNNNNNNNNNNSSSSSSSSSSSAAAAAAAAAAAAAJJJJJJJJJJJJJEEEEEEEEEEEEE<br/>";
                        echo $det->properties;
                        $arr = explode("\"name\":\"Message\",\"value\":\"", $det->properties);
//                        echo "<<<<<<<<<<<<<<<<<<" . sizeof($arr);
                        if (sizeof($arr) > 1) {
                            $arr = explode("\"}", $arr[1]);
//                            print_r($arr[0]);
//                            echo "<br/>";
//                            print_r(html_entity_decode($arr[0]));
//                            echo "<br/>";
//                            print_r(utf8_decode($arr[0]));
//                            echo "<br/>";
//                            print_r(htmlentities($arr[0]));
//                            echo "<br/>";
//                            print_r(htmlentities(utf8_decode($arr[0])));
//                            echo "<br/>";
                            $valor['value'] = utf8_encode(htmlentities(html_entity_decode($arr[0])));
                        } else {
                            $arr = explode("\"name\":\"Message\",\"value\":\"", $propAdicional);
                            if (sizeof($arr) > 1) {
                                $arr = explode("\"}", $arr[1]);
                                print_r($arr[0]);
                                echo "<br/>";
                                print_r(html_entity_decode($arr[0]));
                                echo "<br/>";
                                print_r(utf8_decode($arr[0]));
                                echo "<br/>";
                                print_r(htmlentities($arr[0]));
                                echo "<br/>";
                                print_r(htmlentities(utf8_decode($arr[0])));
                                echo "<br/>";
                                $valor['value'] = utf8_encode(htmlentities(html_entity_decode($arr[0])));
                            }
                        }
                        echo "<br/>FIN ANALISIS DE MENSAJE<br/>";
                    }
                    $arr_propiedades_guardar[$propiedad->id] = $valor['value'];
                }
                if (!$nuevaOrden) {
                    foreach ($arr_propiedades_guardar as $k => $v) {
                        $this->persistenciaOrdenItemPropiedad($linea_creada_id, $k, $v);
                    }
                }
                print_r("<br/>custom_product_esta_linea es " . print_r($custom_product_esta_linea, true));
//                if ($custom_product_esta_linea) {
//                    if (array_key_exists($custom_product_esta_linea, $arrCustomization)) {
//
//                        $arr_propiedades = json_decode($arrCustomization[$custom_product_esta_linea], true);
//                        foreach ($arr_propiedades as $valor) {
//                            $propiedad = $this->service_ecommerce_producto->devolverOcrearPropiedad($valor['name']);
//                            if (strtoupper($valor['name']) === 'DATE OF DELIVERY') {
//                                if (!$fecha_delivery) {
//                                    $fecha_delivery = $valor['value'];
//                                    print_r("Fecha Delivery vacia");
//                                    print_r("Fecha Delivery ahora es " . print_r($fecha_delivery, true));
//                                } else {
//                                    //comparo las 2 fechas
//                                    if ($fecha_delivery != $valor['value']) {
//                                        //tiene una fecha de entrega diferente,
//                                        //debo agregar este item a otra orden
//                                        $arr_lineas_nueva_orden[] = $det;
//                                        $nuevaOrden = true;
//                                    }
//                                }
//                            }
//
//
//                            if (strtoupper($valor['name']) === 'MESSAGE') {
//                                $arr = explode("\"name\":\"Message\",\"value\":\"", $det->properties);
//                                if (sizeof($arr) > 1) {
//                                    $arr = explode("\"}", $arr[1]);
//                                    $valor['value'] = $arr[0];
//                                }
//                            }
//                            if (!$nuevaOrden) {
//                                unset($arr_lineas_nueva_orden[$custom_product_esta_linea]);
//                                $this->persistenciaOrdenItemPropiedad($linea_creada_id, $propiedad->id, $valor['value']);
//                            }
//                        }
//                    }
//                }
            }
        }

        return array($error_linea, $fecha_delivery, $arr_lineas_nueva_orden, $contieneTinturados);
    }

    public function eliminarOrdenItem($orden_item_id) {
        $datos['id'] = $orden_item_id;
        $datos['estado'] = ESTADO_INACTIVO;
        $eliminacion = $this->actualizar("ecommerce.orden_item", $datos, "id", true);

        if ($eliminacion) {
            $this->service_ecommerce_logistica->sacarItemDeCaja($orden_item_id);
        }

        return $eliminacion;
    }

    public function registra_producto($order_detalle, $orden_id) {

    }

    public function search_date_of_delivery($arr_items) {
        $arr_fechas = array();
        $arr_fechas['NO_DEFINIDO'] = array();
        foreach ($arr_items as $key => $item) {
            // print_r("</br>Padre: ");
            // print_r($item['padre']->shopify_order_id);
            //  print_r("</br>");
            //print_r($item['padre']->id);
            $fecha_entrega = false;
            $det_pro = json_decode($item['padre']->properties, true);
            foreach ($det_pro as $j => $prop_padre) {
                if (strpos(strtoupper($prop_padre['name']), 'DATE OF DELIVERY') === 0) {
                    print_r("</br>");
                    print_r("Fecha delivery: ");
                    print_r($prop_padre['value']);
                    $fecha_entrega = $prop_padre['value'];
                    //print_r("590<br/>");
                    break;
                }
            }
            if (!$fecha_entrega) {
                foreach ($item['hijos'] as $j => $hijo2) {
                    // print_r("</br>hijos: ");
                    // print_r($item['hijos']->shopify_order_id);
                    //  print_r("</br>");
                    $det_pro_fecha = json_decode($hijo2->properties, true);
                    foreach ($det_pro_fecha as $l => $fechah) {
                        if (strpos(strtoupper($fechah['name']), 'DATE OF DELIVERY') === 0) {
                            print_r("</br>");
                            print_r("Fecha delivery hijo: ");
                            print_r($fechah['value']);
                            $fecha_entrega = $fechah['value'];
                            break;
                        }
                    }
                    if ($fecha_entrega) {
                        break;
                    }
                    print_r("<br/>");
                }
            }
            //  print_r("<br/>");
            // print_r("Fecha entrega: ");print_r($fecha_entrega);
            // print_r("<br/>");
            if ($fecha_entrega) {
                if (!array_key_exists($fecha_entrega, $arr_fechas)) {
                    $arr_fechas[$fecha_entrega] = array();
                }
                $arr_fechas[$fecha_entrega][] = $item;
            } else {
                $arr_fechas['NO_DEFINIDO'][] = $item;
            }
            print_r("************************************************************************************");
        }
        print_r("<br/>");
        return $arr_fechas;
    }

    public function crearOrdenRosaholics($objOrden, $order_detalle, $arrCustomization = array()) {
        print_r(">>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> crearOrdenRosaholics >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>");
        echo "<br/>";
        $orden = $this->service_ecommerce_orden->existeOrden($objOrden);
        error_log(print_r("Orden de existeOrden", true));
        // if ($orden) {
        //     echo "Actualizamos orden<br/>";
        //     error_log(print_r("Actualizamos orden", true));
        //     $objOrden['id'] = $orden->id;
        //     if (!$this->service_ecommerce_orden->actualizarOrden($objOrden)) {
        //         return array("error" => "Problemas al momento de actualizar la orden");
        //     }
        //     $orden_id = $orden->id;
        //     $proce_orden = 1;
        //     $this->service_ecommerce_orden->eliminarLineasOrden($orden_id);
        // } else {
        //     echo "Orden Nueva<br/>";
        //     error_log(print_r("Nueva orden", true));
        //     $orden_id = $this->service_ecommerce_orden->crearOrden($objOrden);
        //     $objOrden['id'] = $orden_id;
        //     $proce_orden = 0;
        // }

        $arr = array();
        if (sizeof($arrCustomization) == 0) {
            print_r("<br/>no hay customization arr<br/>");
            $arr_items = array();
            foreach ($order_detalle as $w => $det) {
                print_r("<br/>Customizacion for cost:    ");
                var_dump(strpos($det->name, 'Customization Cost for'));
                print_r($w);
                echo "<br/>";
                print_r("Detalle:    ");
                print_r($det);
                print_r("<br/>##############################################################################################<br/>");
                $arr_propiedades = json_decode($det->properties, true);
                //SINo tiene ni padre ni hijo se lo trata como un padre
                $val_ramas = false;
                foreach ($arr_propiedades as $k => $prop) {

                    if (strpos(strtoupper($prop['name']), 'MAIN_ID') === 0) {
                        $id_padre = $prop['value'];
                        if (!array_key_exists($prop['value'], $arr_items)) {
                            $arr_items[$prop['value']] = array('padre' => false,
                                'hijos' => array());
                        }
                        $arr_items[$prop['value']]['padre'] = $det;
                        $val_ramas = true;
                    } else if (( strpos(strtoupper($prop['name']), 'CUSTOM PRODUCT') === 0 ) && !(strpos(strtoupper($det->name), 'CUSTOMIZATION COST FOR') === 0 )) {
                        $id_padre = $prop['value'];
                        $arr_items[$prop['value']] = array('padre' => $det,
                            'hijos' => array()
                        );
                        $val_ramas = true;
                    } else if (strpos(strtoupper($prop['name']), 'FATHER_ID') === 0) {
                        print_r("</br> Hijo</br>");
                        if (!array_key_exists($prop['value'], $arr_items)) {
                            $arr_items[$prop['value']] = array('padre' => false,
                                'hijos' => array()
                            );
                        }
                        $arr_items[$prop['value']]['hijos'][] = $det;
                        $val_ramas = true;
                    } else if (( strpos(strtoupper($prop['name']), 'CUSTOM PRODUCT') === 0 ) && ( strpos(strtoupper($det->name), 'CUSTOMIZATION COST FOR') === 0 )) {
                        print_r("</br> Hijo 2</br>");
                        if (!array_key_exists($prop['value'], $arr_items)) {
                            $arr_items[$prop['value']] = array('padre' => false,
                                'hijos' => array()
                            );
                        }
                        $arr_items[$prop['value']]['hijos'][] = $det;
                        $val_ramas = true;
                    }
                }
                if (!$val_ramas) {
                     if ( $det->sku == PRODUCT_FREE_SHIPPING )
                    {
                        error_log(print_r($det->sku,true));
                        $orden_descuento = array("shopify_order_id" => $det->shopify_order_id,
                                            "referencia_order_number" => $objOrden['referencia_order_number'],
                                            "producto_id" => $det->shopify_product_id,
                                            "variante_id" => $det->shopify_product_variant_id,
                                            "estado" => $det->estado,
                                            "cantidad" => $det->quantity,
                                            "precio" => $det->price
                                        );
                        $existe_orden_shipping = $this->service_ecommerce_orden->existeOrdenShipping($orden_descuento);
                        if ( $existe_orden_shipping )
                        {
                            $orden_descuento['id'] = $existe_orden_shipping->id;
                            $orden_shipping = $this->service_ecommerce_orden->actualizarShippingOrden($orden_descuento);
                        }
                        else
                        {
                           $orden_shipping = $this->service_ecommerce_orden->crearShippingOrden($orden_descuento);
                         }
                    }
                    else
                    {
                        $arr_items[$det->shopify_order_item_id] = array('padre' => $det,
                        'hijos' => array()
                    );
                    }

                }
                print_r("<br/>##############################################################################################<br/>");
            }
            print_r("</br>   princ   </br>");
            print_r($arr_items);
            print_r("</br>   Padre   </br>");
            //print_r("<br/>****************************** Arreglo bidimensional ***************************<br/>");
            foreach ($arr_items as $key => $value) {
                // print_r($key);
                // print_r($value['padre']);
                $arr_0 = json_decode($value['padre']->properties, true);
                print_r("</br>   Padre   </br>");
                print_r($arr_0);

                //print_r($value['hijos']);
                foreach ($value['hijos'] as $j => $hijo) {
                    $arr_1 = json_decode($hijo->properties, true);
                    print_r("</br>   Hijo   </br>");
                    print_r($arr_1);
                    // print_r("<br/>****************************** FIN ***************************<br/>");
                }
            }
            $arr_fechas = $this->search_date_of_delivery($arr_items);
        } else {
            print_r("<br/>Ya existe customization arr");
            $arr = $order_detalle;
        }
        //INICIO Nuevo método
        $this->convertir_items_orden($objOrden, $arr_fechas);

        //FIN nuevo metodo

        print_r("<br/>####################################################<br/>");
        print_r("Info items:  ");
        print_r($arr);
        print_r("<br/>####################################################<br/>");
        //die();
        // List($error_linea, $fecha_delivery, $lineas_diferente_fecha, $contieneTinturados) = $this->convertirItemsAOrden($arr, $orden_id, $arrCustomization);
        /* print_r("<br/>####################################################<br/>");
          print_r($fecha_delivery);
          print_r($lineas_diferente_fecha);
          if ($error_linea) {
          //si existen errores se cambia el estado de error
          //para alertar al usuario y que analice el problema
          //y edite la orden de ser posible
          $objOrden['estado'] = ESTADO_ERROR;
          $this->service_ecommerce_orden->actualizarOrden($objOrden);
          } */
//$fecha_delivery .= " 2020";
        /* error_log(print_r("Fecha delivery es " . $fecha_delivery, true));
          echo "<br/>";
          print_r("Fecha delivery es " . $fecha_delivery);
          $fecha_entrega = convertirFechaStore($fecha_delivery);
          echo "<br/>";
          print_r("Fecha Entrega es " . $fecha_entrega);
          if ($fecha_entrega) {
          $objOrden['fecha_entrega'] = convertirFechaStore($fecha_delivery);
          $objOrden['fecha_carguera'] = $this->calcularFechaCarguera($objOrden['fecha_entrega']);
          $objOrden['fecha_preparacion'] = $this->calcularFechaPreparacion($objOrden['fecha_carguera'], $contieneTinturados);
          } else {
          print_r("F no hay fecha " . $orden_id);
          } */
        /*  print_r("<br/>OOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOO<br/>");
          print_r($lineas_diferente_fecha);
          print_r("<br/>CCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCC<br/>");
          if (sizeof($lineas_diferente_fecha) > 0) {
          //si hay lineas que no han sido procesadas, se debe crear una nueva orden
          $objOrdenNueva = $objOrden;
          if ($objOrdenNueva['secuencial'] < 10) {
          $objOrdenNueva['secuencial'] = $objOrden['secuencial'] + 1;
          unset($objOrdenNueva['id']);

          $creados = $this->crearOrdenRosaholics($objOrdenNueva, $lineas_diferente_fecha, $arrCustomization);
          if (!$creados) {
          return false;
          }
          }
          } */
        /*
          if (!$this->service_ecommerce_orden->actualizarOrden($objOrden)) {
          return false;
          } */

        //empacamos
//        $this->service_ecommerce_logistica->ordenMeterItemsEnCaja($orden_id);

        return true;
    }

    //Separaremos la lógica del cliente
    public function cliente_informacion($order) {
        $proce_cliente = -1; //0 nuevo 1 actualizado
        $cliente = array(
            "nombres" => $order->customer_first_name,
            "apellidos" => $order->customer_last_name,
            "nombre_comercial" => $order->customer_company,
            "email" => $order->customer_email,
            "address" => $order->customer_address1,
            "country" => $order->customer_country,
            "state" => $order->customer_province,
            "city" => $order->customer_city,
            "country_code" => $order->customer_country_code,
            "state_code" => $order->customer_province_code,
            "zip_code" => $order->customer_zip,
            "phone" => $order->customer_phone,
            "customer_id" => $order->customer_id,
            "store_id" => $order->store_id,
        );

        if (isset($cliente['customer_id']) && !empty($cliente['customer_id']))
            $cliente_existente = $this->service_ecommerce_cliente->existeClienteCustomerStore($cliente['store_id'], $cliente['customer_id']);
        else
            $cliente_existente = false;
        //echo "<br/><br/><br/>Cliente Existe<br/>";
        //print_r($cliente_existente);//Info del cliente
        if (!$cliente_existente) {
            $proce_cliente = 0;
            $objCliente = $this->service_ecommerce_cliente->crearCliente($cliente);
            if (!$objCliente)
                return array("error" => "Problemas al momento de crear el cliente");
            $cliente_id = $objCliente;
        }
        else {
            $proce_cliente = 1;
            $cliente['id'] = $cliente_existente->id;
            if (!$this->service_ecommerce_cliente->actualizarCliente($cliente))
                return array("error" => "Problemas al momento de actualizar el cliente");

            $cliente_id = $cliente_existente->id;
        }
        return $cliente_id;
    }

    public function cliente_direccion($id_cliente, $orden) {
        $proce_direccion = -1; //0 nuevo 1 actualizado
        $direccion = array(
            "cliente_id" => $id_cliente,
            "destinatario_nombre" => $orden->shipping_first_name,
            "destinatario_apellido" => $orden->shipping_last_name,
            "destinatario_company" => $orden->shipping_company,
            "address_1" => $orden->shipping_address1,
            "address_2" => $orden->shipping_address2,
            "city" => $orden->shipping_city,
            "state" => $orden->shipping_province,
            "country" => $orden->shipping_country,
            "state_code" => $orden->shipping_province_code,
            "country_code" => $orden->shipping_country_code,
            "zip_code" => $orden->shipping_zip,
            "phone" => $orden->shipping_phone,
            "store_id" => $orden->store_id,
        );
        //print_r($direccion);
        // print_r($order);
        $cliente_direccion_envio_existente = $this->service_ecommerce_cliente->existeClienteDireccionEnvio($direccion);

        if ($cliente_direccion_envio_existente) {
            $proce_direccion = 1;
            $cliente_direccion_id = $cliente_direccion_envio_existente->id;
        } else {
            $proce_direccion = 0;
            $cliente_direccion_id = $this->service_ecommerce_cliente->crearClienteDireccionEnvio($direccion);
        }
        return $cliente_direccion_id;
    }

    public function inactiva_ordenes($orden) {
        $objOrd = array(
            "store_id" => $orden->store_id,
            "referencia_order_number" => $orden->order_number,
        );
        $ordenesList = $this->service_ecommerce_orden->ordenesByRefOrderNumber($objOrd);
        //print_r($ordenesList);
        //print_r("<br/>????????????????????????????????????????????????????????????<br/>");
        if ($ordenesList) {
            foreach ($ordenesList as $ord) {
                $data['estado'] = ESTADO_ORDEN_CANCELADA;
                $data['id'] = $ord->id;
                $actualizacion = $this->service_ecommerce_orden->actualizarOrden($data);
            }
        }
        // print_r("<br/>----------------------------- Update ordenes estancadAS <br/>");
        // print_r($actualizacion);
        //  print_r("<br/>----------------------------- Update ordenes estancadAS <br/>");
    }

    public function convertirOrdenShopifyaOrdenRosaholics($order_id) {
        echo "convertirOrdenShopifyaOrdenRosaholics <br/>";
        $order = $this->shopify_model->existeShopifyOrder($order_id);
        //print_r($order);//Orden cabecera
        //echo "<br/>Detalle: <br/>";
        $order_detalle = $this->shopify_model->obtenerOrdenItems($order_id);
        //print_r($order_detalle);
        $errores = false;

        $proce_producto = -1; //0 nuevo 1 actualizado
        $proce_variante = -1; //0 nuevo 1 actualizado
        $proce_orden = -1; //0 nuevo 1 actualizado
        $proce_orden_item = -1; //0 nuevo 1 actualizado

        try {
            $this->db->trans_start();
            $cliente_id = $this->service_ecommerce->cliente_informacion($order);
            $cliente_direccion_id = $this->service_ecommerce->cliente_direccion($cliente_id, $order);
            //para evitar problemas con ordenes creadas por error, vamos a inactivar cualquier orden
            $this->service_ecommerce->inactiva_ordenes($order);

//print_r($order->created_at);echo "<br/>ssss";
//print_r(FORMATO_FECHA_COMPLETO);echo "<br/>xxx";
//print_r(convertirStringDate($order->created_at,FORMATO_FECHA_COMPLETO));
//print_r(convertirDateString(convertirStringDate($order->created_at,FORMATO_FECHA_COMPLETO), FOR));
            //creamos la orden
            $objOrden = array(
                "store_id" => $order->store_id,
                "cliente_id" => $cliente_id,
                "cliente_direccion_id" => $cliente_direccion_id,
                "referencia_order_number" => $order->order_number,
                "referencia_order_id" => $order->shopify_order_id,
                "secuencial" => 1,
                "fecha_compra" => $order->created_at, //convertirStringDate($order->created_at,FORMATO_FECHA_COMPLETO),
                "estado" => ESTADO_ACTIVO);

            if (!$this->crearOrdenRosaholics($objOrden, $order_detalle)) {
                $errores = true;
            }
//VOLVER A HABILITAR ESTAS 2 LINEAS!!!!!!!!
            $order->estado = ESTADO_ORDEN_PROCESADA;
//
            $this->shopify_model->actualizarShopifyOrder(json_decode(json_encode($order), true));
            $this->db->trans_complete();

            print_r("<br/>FINALIZADO CREACION DE ORDEN<br/>");

            $ordenes_id = $this->service_ecommerce_orden->ordenesByRefOrderNumber($objOrden);

            print_r("<br/>VAMOS AL EMPAQUE AUTOMATICO<br/>");
            $problemas_empaque = false;
            foreach ($ordenes_id as $orden_id) {
                if (!$this->service_ecommerce_logistica->empaqueAutomaticoOrden($orden_id->id)) {
                    print_r("Empaque automatico fallo para orden_id " . $orden_id->id);
                }
            }
        } catch (Exception $ex) {
            error_log("Problemas exception transaccion: " . $ex->getMessage());
        }

        if ($this->db->trans_status() === FALSE) {
            error_log("Problemas presentados durante la ejecucion de esta transaccion");
//            $this->db->trans_rollback();
        } else {
//            $this->db->trans_commit();
        }

        return $errores;
    }

    public function obtenerOrden($orden_id) {
        $this->db->select('o.*');
        $this->db->from('ecommerce.orden o');
        $this->db->where('o.id', $orden_id);
        return $this->retornarUno();
    }

    public function obtenerOrdenes($store_id, $tipo_calendario, $rango_busqueda, $orden_txt = '', $busqueda = '', $orden_id = '', $impresiones = 'T') {
        $this->db->select('o.*, s.id as "tienda_id", s.alias as "tienda_alias", '
                . 'c.id as cliente_id, c.nombres as cliente_nombre, c.apellidos as cliente_apellidos, c.nombre_comercial as nombre_comercial, '
                . 'd.id as direccion_id, d.country, d.state, d.city, d.destinatario_nombre, d.destinatario_apellido, d.destinatario_company');
        $this->db->from('ecommerce.orden o');
        $this->db->join('ecommerce.store s', 'o.store_id = s.id', 'left');
        $this->db->join('ecommerce.cliente c', 'o.cliente_id = c.id', 'left');
        $this->db->join('ecommerce.cliente_direccion_envio d', 'o.cliente_direccion_id = d.id', 'left');
        if ($store_id != 0) {
            $this->db->where('o.store_id', $store_id);
        }

        if ($impresiones != 'T') {
            if ($impresiones == 'N') {
                $this->db->where('o.impresiones = 0');
            }
            if ($impresiones == 'S') {
                $this->db->where('o.impresiones != 0');
            }
        }
        if (!empty($orden_id)) {
            $this->db->where("o.id = " . $orden_id . " ");
        } else if (!empty($orden_txt)) {
            $this->db->where("(o.id = " . $orden_txt . " OR o.referencia_order_number = '" . $orden_txt . "')");
            //$this->db->like('CAST(o.referencia_order_number as TEXT)', $orden, 'after');
        } else {
            if (!empty($rango_busqueda)) {
                //rango_busqueda espera "dd/mm/YYYY - dd/mm/YYYY"
                $arrRango = explode(" - ", $rango_busqueda);
                $fechaIni = date_format(DateTime::createFromFormat(FORMATO_FECHA, convertirFechaBD($arrRango[0])), 'Y-m-d 00:00:00');
                $fechaFin = date_format(DateTime::createFromFormat(FORMATO_FECHA, convertirFechaBD($arrRango[1])), 'Y-m-d 23:59:59');
                if (sizeof($arrRango) != 2) {
                    //siempre debe de ser 2, si no es un error y vamos a devolver un error
                    return array(false, -1);
                }
                switch ($tipo_calendario) {
                    case 0://carguera
                        $arrSelect = array('o.fecha_carguera >= ' => $fechaIni, 'o.fecha_carguera <= ' => $fechaFin);
                        break;
                    case 1://entrega
                        $arrSelect = array('o.fecha_entrega >= ' => $fechaIni, 'o.fecha_entrega <= ' => $fechaFin);
                        break;
                    case 2://actualizacion
                        $arrSelect = array('o.fecha_entrega >= ' => $fechaIni, 'o.fecha_entrega <= ' => $fechaFin);
                        break;
                    case 3:// sin fecha
                        $arrSelect = 'o.fecha_entrega IS NULL';
                        break;
                    default:
                        $arrSelect = array('o.fecha_entrega >= ' => $fechaIni, 'o.fecha_entrega <= ' => $fechaFin);
                        break;
                }

                $this->db->where($arrSelect);
//            error_log("rango_busqueda");
//            error_log(print_r($rango_busqueda, true));
            }

            if (!empty($busqueda)) {
                //Producto filtro
//                $this->db->where(" (UPPER(c.nombres) LIKE '%" . strtoupper($busqueda) . "%'  "
//                        . "OR UPPER(c.apellidos) LIKE '%" . strtoupper($busqueda) . "%'  "
//                        . "OR UPPER(c.nombre_comercial) LIKE '%" . strtoupper($busqueda) . "%'  "
//                        . "OR UPPER(c.email) LIKE '%" . strtoupper($busqueda) . "%'  "
//                        . "OR UPPER(c.country) LIKE '%" . strtoupper($busqueda) . "%'  "
//                        . "OR UPPER(c.state) LIKE '%" . strtoupper($busqueda) . "%'  "
//                        . "OR UPPER(c.city) LIKE '%" . strtoupper($busqueda) . "%'  "
//                        . "OR UPPER(c.address) LIKE '%" . strtoupper($busqueda) . "%' ) ");
            }
        }
        $conteo = $this->retornarConteo();

        $this->db->order_by('o.referencia_order_number', 'DESC');
        $this->db->order_by('o.id', 'ASC');
        $arrOrd = $this->retornarMuchos();
//        error_log(print_r($this->db->last_query(), true));

        if ($arrOrd) {
            foreach ($arrOrd as $j => $orden) {
                $orden->tag = '';
                $items = $this->obtenerOrdenItems($orden->id);
                $orden->producto_filtro = true;
                if (!empty($busqueda)) {
                    $orden->producto_filtro = false;
                }
                if (!$items) {
                    $items = array();
                } else {
                    foreach ($items as $k => $item) {
                        if (strpos($item->info_producto_prefijo, "AGR_PN") !== false) {
                            $orden->tag .= " MIAMI ";
                        }
                        if ((empty($orden_id)) && (empty($orden_txt)) && (!empty($busqueda)) && (strpos(strtoupper($item->info_producto_titulo), strtoupper($busqueda)) !== false)) {
//                            error_log(print_r($item->info_producto_titulo, true));
//                            error_log(strtoupper($item->info_producto_titulo));
//                            error_log(strtoupper($busqueda));
//                            error_log(strpos(strtoupper($item->info_producto_titulo), strtoupper($busqueda)));
                            $orden->producto_filtro = true;
//                            error_log(print_r($item->info_producto_titulo, true));
//                            echo "<br/>"; //die;
//                            unset($arrOrd[$j]);
//                            unset($orden);
//                            continue;
                        }
//                        error_log("SI CONTINUO");
                        $cajas = $this->service_ecommerce_logistica->obtenerOrdenItemCaja($item->id);
                        $items[$k]->cajas = $cajas;
                        $propiedades = $this->service_ecommerce->obtenerOrdenItemPropiedades($item->id);
                        $item->propiedades = array();
                        if ($propiedades) {
                            $item->propiedades = $propiedades;
                            foreach ($propiedades as $propiedad) {
                                if ($propiedad->propiedad_id == 19) {//standing order
                                    $orden->tag .= 'STANDING ORDER';
                                }
                            }
                        }
                    }
                }
                $orden->items = $items;
            }
        }

        return array($arrOrd, $conteo);
    }

    public function obtenerOrdenItemPropiedades($orden_item_id, $estado = ESTADO_ACTIVO) {
        return $this->existeOrdenItemPropiedad($orden_item_id, false, false, $estado);
    }

    public function obtenerOrdenItemPropiedad($id) {
        $this->db->select('oip.*, prp.nombre as info_propiedad_nombre, prp.descripcion as info_propiedad_descripcion');
        $this->db->from('ecommerce.orden_item_propiedad oip');
        $this->db->join('ecommerce.propiedad prp', 'oip.propiedad_id = prp.id', 'left');
        $this->db->where('oip.id', $id);
        return $this->retornarUno();
    }

    public function existeOrdenItemPropiedad($orden_item_id, $propiedad_id = false, $valor = -1, $estado = false) {
        $this->db->select('oip.*, prp.nombre as info_propiedad_nombre, prp.descripcion as info_propiedad_descripcion');
        $this->db->from('ecommerce.orden_item_propiedad oip');
        $this->db->join('ecommerce.propiedad prp', 'oip.propiedad_id = prp.id', 'left');
        $this->db->where('orden_item_id', $orden_item_id);

        if ($estado) {
            $this->db->where('oip.estado', $estado);
        }

        if ($propiedad_id) {
            if ($valor != -1) {
                $this->db->where('oip.valor', '"' . $valor . '"');
            }
            $this->db->where('oip.estado', ESTADO_ACTIVO);
            $this->db->where('oip.propiedad_id', $propiedad_id);
            return $this->retornarUno();
        } else {
            $this->db->order_by('prp.descripcion', 'ASC');
            return $this->retornarMuchosSinPaginacion();
        }
    }

    public function persistenciaOrdenItemPropiedad($orden_item_id, $propiedad_id, $valor) {
        $id = false;
        $orden_item_propiedad = $this->existeOrdenItemPropiedad($orden_item_id, $propiedad_id);
        $this->inactivarPropiedadOrdenItem($orden_item_id, $propiedad_id);
        $id = $this->crearOrdenItemPropiedad($orden_item_id, $propiedad_id, $valor);
        return $id;
//        if (!$orden_item_propiedad) {
//            $nu = 1;
//            $objCliente["estado"] = ESTADO_ACTIVO;
//            $id = $this->crearOrdenItemPropiedad($orden_item_id, $propiedad_id, $valor);
//        } else {
//            $nu = 2;
//
//            //inactivamos cualquier otro registro con propiedad_id
//
//
////            $objProductoVariante['id'] = $orden_item_propiedad->id;
////            if ($this->actualizarOrdenItemPropiedad($orden_item_propiedad->id, $orden_item_id, $propiedad_id, $valor)) {
////                $id = $orden_item_propiedad->id;
////            }
//        }
//        return array($nu, $id);
    }

    public function crearOrdenItemPropiedad($orden_item_id, $propiedad_id, $valor) {
        $datos['orden_item_id'] = $orden_item_id;
        $datos['propiedad_id'] = $propiedad_id;
        $datos['valor'] = utf8_encode($valor);
        $datos['estado'] = ESTADO_ACTIVO;

        return $this->ingresar("ecommerce.orden_item_propiedad", $datos, true, true);
    }

    public function inactivarPropiedadOrdenItem($orden_item_id, $propiedad_id) {
        $datos['propiedad_id'] = $propiedad_id;
        $datos['orden_item_id'] = $orden_item_id;
        $datos['estado'] = ESTADO_INACTIVO;
        error_log("************* actualizar a inactivo " . $orden_item_id . " " . $propiedad_id);
        $actualizacion = $this->actualizar("ecommerce.orden_item_propiedad", $datos, array("orden_item_id" => -1, "propiedad_id" => -1), true);
        return $actualizacion;
    }

    public function actualizarOrdenItemPropiedad($orden_item_propiedad_id, $orden_item_id, $descripcion, $valor) {
        $datos['id'] = $orden_item_propiedad_id;
        $datos['estado'] = ESTADO_INACTIVO;
        error_log("************* actualizar a inactivo " . $orden_item_id . " " . $orden_item_propiedad_id);
//        $actualizacion = $this->actualizar("ecommerce.orden_item_propiedad", $datos, array("orden_item_id" => -1, "descripcion" => -1), true);
        $actualizacion = $this->actualizar("ecommerce.orden_item_propiedad", $datos, "id", true);
//$datos['orden_item_id'] = $orden_item_id;
//        $datos['descripcion'] = $descripcion;
        return $this->crearOrdenItemPropiedad($orden_item_id, $descripcion, $valor);
    }

    public function eliminarOrdenItemPropiedad($orden_item_propiedad_id) {
        $datos['id'] = $orden_item_propiedad_id;
        $datos['estado'] = ESTADO_INACTIVO;
        return $this->actualizar("ecommerce.orden_item_propiedad", $datos, "id", true);
    }

    public function obtenerOrdenItem($orden_item_id, $orden_id = false, $orden_caja_id = false, $session_finca = false, $finca_id = false) {
        //relacion finca caja y fecha de carguera desde tabla ordenes
        $select = 'oi.*, p.titulo as info_producto_titulo, p.sku_prefijo as info_producto_prefijo, pv.titulo as info_variante_titulo, pv.sku as info_variante_sku, ';
        $select .= ', oc.id as orden_caja_id, tc.id as info_tipo_caja_id, tc.nombre as info_tipo_caja_nombre, o.fecha_carguera, fc.finca_id  ';
        $this->db->select($select);
        $this->db->from('ecommerce.orden_item oi');
        $this->db->join('ecommerce.producto p', 'oi.producto_id = p.id', 'left');
        $this->db->join('ecommerce.producto_variante pv', 'oi.variante_id = pv.id', 'left');
        $this->db->join('ecommerce.orden_caja_item oci', 'oci.orden_item_id = oi.id AND oci.estado = \'' . ESTADO_ACTIVO . '\'', 'left');
        $this->db->join('ecommerce.orden_caja oc', 'oci.orden_caja_id = oc.id AND oc.estado = \'' . ESTADO_ACTIVO . '\'', 'left');
        $this->db->join('ecommerce.orden o', 'oi.orden_id = o.id', 'left');
        $this->db->join('ecommerce.finca_caja fc', "fc.orden_caja_id = oc.id AND fc.estado = '" . ESTADO_ACTIVO . "'", 'left');
        $this->db->join('ecommerce.tipo_caja tc', 'oc.tipo_caja_id = tc.id', 'left');


        if (isset($session_finca) && !empty($session_finca)) {
            $arrayfinca = explode(",", $session_finca);
            if (in_array(FINCA_ROSAHOLICS_ID, $arrayfinca)) {
                if ($finca_id != 0) {
                    $srt = "fc.finca_id in (" . $finca_id . ")";
                }
            } else {
                if ($finca_id) {
                    if ($finca_id != 0) {
                        $srt = "fc.finca_id in (" . $finca_id . ")";
                    } else {
                        $srt = "fc.finca_id in (" . $session_finca . ")";
                    }
                } else {
                    $srt = "fc.finca_id in (" . $session_finca . ")";
                }
            }
            if(isset($srt)){
              $this->db->where($srt);
            }
        }


//        $this->db->where('o.estado', ESTADO_ACTIVO);
//        $this->db->where("(fc.estado = '".ESTADO_ACTIVO."' OR fc.estado IS NULL )");
        if ($orden_item_id) {
            $this->db->where('oi.id', $orden_item_id);
            return $this->retornarUno();
        }

        if ($orden_id) {
            $this->db->where('oi.orden_id', $orden_id);
        }
        if ($orden_caja_id) {
            $this->db->where('oc.id', $orden_caja_id);
        }

        $this->db->where('oi.estado', ESTADO_ACTIVO);
//        $this->db->where('oc.estado', ESTADO_ACTIVO);
        return $this->retornarMuchosSinPaginacion(true);
    }

    public function obtenerOrdenItems($orden_id, $session_finca = false, $finca_id = false) {
        return $this->obtenerOrdenItem(false, $orden_id, false, $session_finca, $finca_id);
    }

    /*     * **************** RECETA ***************** */

    public function calcularFechaCarguera($fecha_entrega) {

        $fecha_delivery = DateTime::createFromFormat("Y-m-d", $fecha_entrega);
        /* Por SanValentin 2021 vamos a hacer una excepcion
         * Si la fecha de entrega es sabado 13 o domingo 14 vamos a poner la fecha de carguera el 9 de febrero
         */

        $fecha_actual = DateTime::createFromFormat("Y-m-d H:i:s", date("Y-m-d H:i:s"));
        $fecha_14feb = DateTime::createFromFormat("Y-m-d H:i:s", "2021-02-14 23:59:59");
        $fecha_13feb = DateTime::createFromFormat("Y-m-d H:i:s", "2021-02-13 23:59:59");
        $fecha_10feb = DateTime::createFromFormat("Y-m-d H:i:s", "2021-02-10 23:59:59");

//        print_r("<br/>Fecha Delivery es ") . " " . print_r($fecha_delivery);
//        print_r("<br/>Fecha 10 febrero es ") . " " . print_r($fecha_10feb);
//        print_r("<br/>Fecha Actual es ") . " " . print_r($fecha_actual);
        if ($fecha_actual < $fecha_10feb) {
//            $fecha_delivery = DateTime::createFromFormat("Y-m-d", "2021-02-13");
            if (($fecha_delivery->format("Y-m-d") == $fecha_14feb->format("Y-m-d")) || ($fecha_delivery->format("Y-m-d") == $fecha_13feb->format("Y-m-d"))) {
                $fecha_carguera_forzada = DateTime::createFromFormat("Y-m-d", "2021-02-09");
//                print_r("<br/>Fecha forzada de Carguera es ");
//                print_r($fecha_carguera_forzada->format("Y-m-d"));
                return $fecha_carguera_forzada->format("Y-m-d");
            }
        }

        //basandonos en el calendario de carguera
//        print_r("<br/>Fecha de entrega DIA es ");
//        print_r($fecha_delivery->format("D"));
        switch ($fecha_delivery->format("D")) {
            case "Mon":
                $fecha_carguera = $fecha_delivery->modify('-4 day');
                break;
            case "Tue":
                $fecha_carguera = $fecha_delivery->modify('-4 day');
                break;
            case "Wed":
                $fecha_carguera = $fecha_delivery->modify('-4 day');
                break;
            case "Thu":
                $fecha_carguera = $fecha_delivery->modify('-3 day');
                break;
            case "Fri":
                $fecha_carguera = $fecha_delivery->modify('-3 day');
                break;
            default:
                $fecha_carguera = $fecha_delivery->modify('-4 day');
                break;
        }
//        print_r("<br/>Fecha Carguera es ");print_r($fecha_carguera->format("Y-m-d"));
        if ($fecha_entrega)
            return $fecha_carguera->format("Y-m-d");
    }

    public function calcularFechaPreparacion($fecha_carguera, $hayTinturados) {
        $fecha_preparacion = DateTime::createFromFormat("Y-m-d", $fecha_carguera);
        if ($hayTinturados) {
            $fecha_preparacion = $fecha_preparacion->modify('-1 day');
        }
        return $fecha_preparacion->format("Y-m-d");
    }

    public function crearOrdenRosaholicsMigracion($objOrden, $order_detalle) {
        $orden = $this->service_ecommerce_orden->existeOrden($objOrden);

        if ($orden) {
            echo "Actualizamos orden";
            $objOrden['id'] = $orden->id;
            if (!$this->service_ecommerce_orden->actualizarOrden($objOrden)) {
                return array("error" => "Problemas al momento de actualizar la orden");
            }
            $orden_id = $orden->id;
            $proce_orden = 1;
            $this->service_ecommerce_orden->eliminarLineasOrden($orden_id);
        } else {
            echo "Orden Nueva";
            $orden_id = $this->service_ecommerce_orden->crearOrden($objOrden);
            $objOrden['id'] = $orden_id;
            $proce_orden = 0;
        }

        List($error_linea, $fecha_delivery, $lineas_diferente_fecha, $contieneTinturados) = $this->convertirItemsAOrdenMigracion($order_detalle, $orden_id, $arrCustomization);
//        print_r($fecha_delivery);
//        print_r($lineas_diferente_fecha);
        if ($error_linea) {
            //si existen errores se cambia el estado de error
            //para alertar al usuario y que analice el problema
            //y edite la orden de ser posible
            $objOrden['estado'] = ESTADO_ERROR;
            $this->service_ecommerce_orden->actualizarOrden($objOrden);
        }

        print_r("Fecha delivery es " . $fecha_delivery);
        $fecha_entrega = $fecha_delivery;
        if ($fecha_entrega) {
            $objOrden['fecha_entrega'] = substr($fecha_delivery, 0, 10);
            $objOrden['fecha_carguera'] = $this->calcularFechaCarguera($objOrden['fecha_entrega']);
            $objOrden['fecha_preparacion'] = $this->calcularFechaPreparacion($objOrden['fecha_carguera'], $contieneTinturados);
        } else {
            print_r("F no hay fecha " . $orden_id);
        }

        if (sizeof($lineas_diferente_fecha) > 0) {
            //si hay lineas que no han sido procesadas, se debe crear una nueva orden
            if ($objOrden['secuencial'] < 10) {
                $objOrden['secuencial'] = $objOrden['secuencial'] + 1;
                $creados = $this->crearOrdenRosaholicsMigracion($objOrden, $lineas_diferente_fecha);
                if (!$creados) {
                    return false;
                }
            }
        }

        if (!$this->service_ecommerce_orden->actualizarOrden($objOrden)) {
            return false;
        }

        //empacamos
//        $this->service_ecommerce_logistica->ordenMeterItemsEnCaja($orden_id);

        return true;
    }

    public function convertirItemsAOrdenMigracion($order_detalle, $orden_id) {
        $fecha_delivery = false;
        $linea_assemble = false;
        $linea_creada_id = false;
        $contieneTinturados = false;
        $lineas_errores = 0;
        $arr_lineas_nueva_orden = array();
        //este es un arreglo que va a contener los items que van a crearse
        //en una nueva orden debido a que la fecha de entrega es diferente
//            error_log(print_r($order_detalle, true));  die;
//        print_r($order_detalle);
        foreach ($order_detalle as $det) {
            $nuevaOrden = false;
            print_r("*************************************");
            print_r(print_r($det, true));
            $error_linea = false;
            if ($det->shopify_product_id != null) {
                if (strpos($det->sku, "AGR_P") !== false) {
                    if (strpos($det->sku, "AGR_PT") !== false) {
                        $contieneTinturados = true;
                    }
                    $sku_prod_arr = explode("_", $det->sku);
                    $producto = array(
                        "titulo" => $det->title,
                        "descripcion" => $det->name,
                        "sku_prefijo" => $sku_prod_arr[0] . "_" . $sku_prod_arr[1] . "_" . $sku_prod_arr[2], //AGR_TIPOELEMENTO_DENOMINACION
                    );
                    $producto_persistencia = $this->service_ecommerce_producto->persistenciaProducto($producto);

                    if (!$producto_persistencia[1]) {
                        $error_linea = "Problemas al momento de crear el producto";
                        $lineas_errores++;
                        break;
                    }

                    $producto_id = $producto_persistencia[1];
                    $producto_variante = array(
                        "producto_id" => $producto_id,
                        "titulo" => !empty($det->variant_title) ? $det->variant_title : $det->title,
                        "sku" => $det->sku,
                        "cantidad" => is_numeric($sku_prod_arr[3]) ? $sku_prod_arr[3] : 1,
                        "largo_cm" => $sku_prod_arr[4]
                    );
                    $producto_variante_persistencia = $this->service_ecommerce_producto->persistenciaProductoVariante($producto_variante);
                    if (!$producto_variante_persistencia[1]) {
                        $error_linea = "Problemas al momento de crear la variante";
                        $lineas_errores++;
                        break; //nos saltamos al siguiente item
                    }
                    $producto_variante_id = $producto_variante_persistencia[1];

                    $linea = array(
                        "orden_id" => $orden_id,
                        "producto_id" => $producto_id,
                        "variante_id" => $producto_variante_id,
                        "cantidad" => $det->quantity * 1,
                        "precio" => $det->price,
                    );
                    $linea_creada_id = $this->crearLinea($linea);
                    if (!$linea_creada_id) {
                        $error_linea = "Problemas al momento de crear linea de orden " . $orden_id;
                        $lineas_errores++;
                        break;
                    }
                }

                if (strpos($det->sku, 'AGR_P_ASS_XXX') !== false) {
                    if (!$linea_assemble) {
                        $linea_assemble = $this->obtenerOrdenItem($linea_creada_id);
                    } else {
                        //ya existe otro producto assemble si este nuevo assemble
                        //tiene la misma fecha se lo salta, si no tiene la misma fecha
                        //implica que estas lineas son de otra orden
                        //las agrupamos en el arreglo $arr_lineas_nueva_orden
                    }
//                } else if (strpos($det->sku, 'AGR_P') !== false) {
//                    //es un producto P o PT
                } else if (strpos($det->sku, AGR_C) !== false) {
                    //es un componente C o CT
                    //pasan a ser un detalle del assemble
                    if ($linea_assemble) {
                        $propiedad = $this->service_ecommerce_producto->devolverOcrearPropiedad($det->sku);
                        $this->persistenciaOrdenItemPropiedad($linea_assemble->id, $propiedad->id, $det->quantity);
                    } else {
                        $error_linea = true;
                    }
                } else if (strpos($det->sku, 'AGR_A') !== false) {
                    //es un accesorio Vase, Wrap (STW, Petal
                }
            }

            //Para este item debemos ver si es un CUSTOMIZATION DEL PRODUCTO ANTERIOR
//                if (strpos($det->title, 'Customization Cost for') != false) {
            //al producto anterior le guardamos el detalle de sus accesorios
            //$producto_anterior = $this->obtenerOrdenItem($linea_creada_id);
//            error_log(print_r($linea_creada_id, true));
//            error_log(print_r($det->properties, true));
            //en migracion vamos a obtener las propiedades de campos diferentes al proceso del softwareholics
            $fecha_carguera = $det->date_carguera_at_alt;
            $fecha_entrega = $det->date_of_delivery_alt;
            $is_gift = $det->is_gift;
            $message = $det->message;
            $propiedades = $det->custom_options;

            if (!$linea_creada_id) {
                $error_linea = true;
                continue;
            }
            if (!$fecha_delivery) {
                $fecha_delivery = $fecha_entrega;
            } else {
                if ($fecha_delivery != $fecha_entrega) {
                    $arr_lineas_nueva_orden[] = $det;
                    $nuevaOrden = true;
                }
            }
            if (!empty($is_gift)) {
                $propiedad = $this->service_ecommerce_producto->devolverOcrearPropiedad('IS THIS ORDER A GIFT?');
                $this->persistenciaOrdenItemPropiedad($linea_creada_id, $propiedad->id, $is_gift);
            }
            if (!empty($message)) {
                $propiedad = $this->service_ecommerce_producto->devolverOcrearPropiedad('MESSAGE');
                $this->persistenciaOrdenItemPropiedad($linea_creada_id, $propiedad->id, $is_gift);
            }
            if (($propiedades != null) && (!empty($propiedades))) {
                $arr_propiedades = explode("|", $propiedades);

                foreach ($arr_propiedades as $prop) {
                    $prop = explode(":", $prop);
                    $valor['name'] = $prop[0];
                    $valor['value'] = $prop[1];
                    $propiedad = $this->service_ecommerce_producto->devolverOcrearPropiedad($valor['name']);

                    if (strtoupper($valor['name']) === 'MESSAGE') {
                        $arr = explode("\"name\":\"Message\",\"value\":\"", $det->properties);
                        if (sizeof($arr) > 1) {
                            $arr = explode("\"}", $arr[1]);
                            $valor['value'] = $arr[0];
                        }
                    }
                    if (!$nuevaOrden) {
                        $this->persistenciaOrdenItemPropiedad($linea_creada_id, $propiedad->id, $valor['value']);
                    }
                }
            }
        }

        return array($error_linea, $fecha_delivery, $arr_lineas_nueva_orden, $contieneTinturados);
    }

    public function convertirOrdenShopifyaOrdenRosaholicsMigracion($order_id) {
        $order = $this->shopify_model->existeShopifyOrderMigracion($order_id);
        $order_detalle = $this->shopify_model->obtenerOrdenItemsMigracion($order_id);
        $errores = false;

        $proce_cliente = -1; //0 nuevo 1 actualizado
        $proce_direccion = -1; //0 nuevo 1 actualizado
        $proce_producto = -1; //0 nuevo 1 actualizado
        $proce_variante = -1; //0 nuevo 1 actualizado
        $proce_orden = -1; //0 nuevo 1 actualizado
        $proce_orden_item = -1; //0 nuevo 1 actualizado

        try {
            $this->db->trans_start();
            $cliente = array(
                "nombres" => $order->customer_first_name,
                "apellidos" => $order->customer_last_name,
                "nombre_comercial" => $order->customer_company,
                "email" => $order->customer_email,
                "address" => $order->customer_address1,
                "country" => $order->customer_country,
                "state" => $order->customer_province,
                "city" => $order->customer_city,
                "country_code" => $order->customer_country_code,
                "state_code" => $order->customer_province_code,
                "zip_code" => $order->customer_zip,
                "phone" => $order->customer_phone,
                "customer_id" => $order->customer_id,
                "store_id" => 1,
            );
            $cliente_existente = $this->service_ecommerce_cliente->existeClienteCustomerStore($cliente['store_id'], $cliente['customer_id']);
            if (!$cliente_existente) {
                $proce_cliente = 0;
                $objCliente = $this->service_ecommerce_cliente->crearCliente($cliente);
                if (!$objCliente) {
                    return array("error" => "Problemas al momento de crear el cliente");
                }
                $cliente_id = $objCliente;
            } else {
                $proce_cliente = 1;
                $cliente['id'] = $cliente_existente->id;
                if (!$this->service_ecommerce_cliente->actualizarCliente($cliente)) {
                    return array("error" => "Problemas al momento de actualizar el cliente");
                }
                $cliente_id = $cliente_existente->id;
            }

            $direccion = array(
                "cliente_id" => $cliente_id,
                "destinatario_nombre" => $order->shipping_first_name,
                "destinatario_apellido" => $order->shipping_last_name,
                "destinatario_company" => $order->shipping_company,
                "address_1" => $order->shipping_address1,
                "address_2" => $order->shipping_address2,
                "city" => $order->shipping_city,
                "state" => $order->shipping_province,
                "country" => $order->shipping_country,
                "state_code" => $order->shipping_province_code,
                "country_code" => $order->shipping_country_code,
                "zip_code" => $order->shipping_zip,
                "phone" => $order->shipping_phone,
                "store_id" => 1,
            );
//            print_r($direccion);
//            print_r($order);
            $cliente_direccion_envio_existente = $this->service_ecommerce_cliente->existeClienteDireccionEnvio($direccion);

            if ($cliente_direccion_envio_existente) {
                $proce_direccion = 1;
                $cliente_direccion_id = $cliente_direccion_envio_existente->id;
            } else {
                $proce_direccion = 0;
                $cliente_direccion_id = $this->service_ecommerce_cliente->crearClienteDireccionEnvio($direccion);
            }

            //creamos la orden
            $objOrden = array(
                "store_id" => 1,
                "cliente_id" => $cliente_id,
                "cliente_direccion_id" => $cliente_direccion_id,
                "referencia_order_number" => $order->order_number,
                "referencia_order_id" => $order->shopify_order_id,
                "secuencial" => 1,
                "estado" => ESTADO_ACTIVO);

            if (!$this->crearOrdenRosaholicsMigracion($objOrden, $order_detalle)) {
                $errores = true;
            }

            $order->estado = ESTADO_ORDEN_PROCESADA;

            $this->shopify_model->actualizarShopifyOrderMigracion(json_decode(json_encode($order), true));
            $this->db->trans_complete();
        } catch (Exception $ex) {
            error_log("Problemas exception transaccion: " . $ex->getMessage());
        }

        if ($this->db->trans_status() === FALSE) {
            error_log("Problemas presentados durante la ejecucion de esta transaccion");
//            $this->db->trans_rollback();
        } else {
//            $this->db->trans_commit();
        }

        return $errores;
    }

    public function ordenContieneTinturados($orden_id) {
        $items = $this->service_ecommerce->obtenerOrdenItems($orden_id);
        if ($items) {
            foreach ($items as $item) {
                if (strpos($item->info_variante_sku, 'AGR_PT') !== false) {
                    return true;
                }
            }
        }
        return false;
    }

}
