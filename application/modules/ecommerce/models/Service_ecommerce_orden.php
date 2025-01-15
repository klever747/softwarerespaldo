<?php

class Service_ecommerce_orden extends My_Model {

    public function nuevaOrden() {
        return (object) [
                    'store_id' => '',
                    'estado' => ESTADO_ACTIVO,
        ];
    }

    private function obtenerRangoFechaOrdenes($filtro) {
        $arrRango = explode(" - ", $filtro['rango_busqueda']);
        if (sizeof($arrRango) != 2) {
            return array(false, -1);
        }
        $fechaIni = date_format(DateTime::createFromFormat(FORMATO_FECHA, convertirFechaBD($arrRango[0])), 'Y-m-d 00:00:00');
        $fechaFin = date_format(DateTime::createFromFormat(FORMATO_FECHA, convertirFechaBD($arrRango[1])), 'Y-m-d 23:59:59');

        switch ($filtro['tipo_calendario']) {
            case 0: //carguera
                $arrSelect = array('o.fecha_carguera >= ' => $fechaIni, 'o.fecha_carguera <= ' => $fechaFin);
                break;
            case 1: //entrega
                $arrSelect = array('o.fecha_entrega >= ' => $fechaIni, 'o.fecha_entrega <= ' => $fechaFin);
                break;
            case 2: //actualizacion
                $arrSelect = array('o.fecha_entrega >= ' => $fechaIni, 'o.fecha_entrega <= ' => $fechaFin);
                break;
            default:
                $arrSelect = array('o.fecha_entrega >= ' => $fechaIni, 'o.fecha_entrega <= ' => $fechaFin);
                break;
        }
        $this->db->where($arrSelect);
    }

//funcion para obtener el algoritmo
    public function obtenerAlgoritmoSku($sku) {
        $this->db->select('*');
        $this->db->from('ecommerce.sku_algoritmo sa');
        $this->db->where('sku', $sku);
        $this->db->where('sa.estado', ESTADO_ACTIVO);
        return $this->retornarUno();
    }

//funcion para asignar la finca
    public function asignoFinca($obj) {
        $id = $this->actualizar("ecommerce.orden_item", $obj, "id", false);
        if ($id) {
            $dato_log = array(
                "orden_item_id" => $obj['id'],
                "accion" => "caja despachada hacia la finca" . json_encode($obj),
            );
            $this->registrarLog("ecommerce.orden_item_log", $dato_log);
        }
        return $id;
    }

//funcion para asignar la caja con su finca
    public function asignoFincaCaja($obj) {
        error_log("Vamos a asignar una orden con su finca");
        $id = $this->ingresar("ecommerce.finca_caja", $obj, true, false);
        if ($id) {
            $dato_log = array(
                "finca_caja_id" => $id,
                "accion" => "Asignacion de una orden a una finca " . json_encode($obj),
            );
            $this->registrarLog("ecommerce.finca_caja_log", $dato_log);
        }
        return $id;
    }

//funcion para actualizar el estado de la orden una vez que fue asignada a laficna
    public function actualizarEstado($obj) {
        $id = $this->actualizar("ecommerce.orden_caja", $obj, "id", false);
        if ($id) {
            $dato_log = array(
                "orden_caja_id" => $obj['id'],
                "accion" => "caja despachada hacia la finca" . json_encode($obj),
            );
            $this->registrarLog("ecommerce.orden_caja_log", $dato_log);
        }
        return $id;
    }

//funcion para actualizar el cupo de la finca
    public function actualizarCupoFinca($obj) {
        $id = $this->actualizar("ecommerce.sku_algoritmo_detalle", $obj, "id", false);
        if ($id) {
            $dato_log = array(
                "algoritmo_detalle_id" => $obj['id'],
                "accion" => "Actualizar cupo de la finca" . json_encode($obj),
            );
            $this->registrarLog("ecommerce.sku_algoritmo_detalle_log", $dato_log);
        }
        return $id;
    }

//vistas actualizadas
    public function v_total_dia_sku_finca($sku, $fecha_carguera, $finca_id) {
        $this->db->select('*');
        $this->db->from('ecommerce.v_total_dia_sku_finca');
        $this->db->where('fecha_carguera', $fecha_carguera);
        $this->db->where('sku', $sku);
        $this->db->where('finca_id', $finca_id);
        $row = $this->retornarUno();
        if ($row) {
            return $row->cantidadtotal;
        }
        return 0;
    }

//vista para contar cuantos productos llevo despachados desde fecha inicio hasta la fecha de carguera
    public function v_total_semanal_sku_finca($sku, $fecha_carguera, $finca_id, $fecha_inicio) {
        $this->db->select('sum(cantidadtotal) as cantidadtotal');
        $this->db->from('ecommerce.v_total_dia_sku_finca');
        $this->db->where("fecha_carguera between ('$fecha_inicio') and ('$fecha_carguera')");
        $this->db->where('sku', $sku);
        $this->db->where('finca_id', $finca_id);
        $row = $this->retornarUno();
        if ($row) {
            return $row->cantidadtotal;
        }
        return 0;
    }

//buscar el cupo semanal que se tiene
    public function cupo_semanal($fecha_inicio, $fecha_fin) {
        $this->db->select('sum(oi.cantidad) AS cupo_semanal');
        $this->db->from('ecommerce.orden o');
        $this->db->join('ecommerce.orden_item oi', 'o.id = oi.orden_id', 'left');
        $this->db->where('o.estado', ESTADO_ACTIVO);
        $this->db->where('oi.estado', ESTADO_ACTIVO);
        $this->db->where("o.fecha_carguera between ('$fecha_inicio') and ('$fecha_fin')");
        $row = $this->retornarUno();
        if ($row) {
            return $row->cupo_semanal;
        }
        return 0;
    }

    public function v_total_dia_sku($sku, $fecha_carguera) {
        $this->db->select('*');
        $this->db->from('ecommerce.v_total_general_items');
        $this->db->where('fecha_carguera', $fecha_carguera);
        $this->db->where('sku', $sku);
        $row = $this->retornarUno();
        if ($row) {
            return $row->cantidadtotal;
        }
        return 0;
    }

//vista para traer los porcentajes asignados a cada sku
    public function v_parametros_sku_algoritmo($sku) {

        $this->db->select('*');
        $this->db->from('ecommerce.v_parametros_sku_algoritmo');
        $this->db->where('sku', $sku);
        $this->db->where('finca_id > 1');
        $arr = $this->retornarMuchosSinPaginacion();
        return $arr;
    }

////////////////////
    public function ordenesByRefOrderNumber($objOrden) {
        $this->db->select('o.id');
        $this->db->from('ecommerce.orden o');
        $this->db->join('ecommerce.store s', 'o.store_id = s.id', 'left');
        $this->db->where('o.store_id', $objOrden['store_id']);
        $this->db->where('referencia_order_number', $objOrden['referencia_order_number']);

        $ordenes = $this->retornarMuchosSinPaginacion();
        return $ordenes;
    }

    public function existeOrden($objOrden) {
        $this->db->select('o.*, s.id as "tienda_id", s.store_name as "tienda_nombre", s.alias as "tienda_alias" ');
        $this->db->from('ecommerce.orden o');
        $this->db->join('ecommerce.store s', 'o.store_id = s.id', 'left');
        if (array_key_exists("id", $objOrden)) {
            $this->db->where('o.id', $objOrden['id']);
            // session_fincaid_
            // finca_id
            // en la vista ordenes
        } else {
            if ($objOrden['store_id'] != 0) {
                $this->db->where('o.store_id', $objOrden['store_id']);
            }
            $this->db->where('referencia_order_id', $objOrden['referencia_order_id']);
            $this->db->where('referencia_order_number', $objOrden['referencia_order_number']);
            $this->db->where('secuencial', $objOrden['secuencial']);
            if (array_key_exists('fecha_entrega', $objOrden)) {
                $this->db->where('fecha_entrega', $objOrden['fecha_entrega']);
            }
        }
        $orden = $this->retornarUno();
        return $orden;
    }

    public function actualizarPrecioOrdenItem($orden_item_id, $precio) {
        $data['precio'] = $precio;
        $data['id'] = $orden_item_id;
        return $this->actualizar("ecommerce.orden_item", $data, "id", true);
    }

    public function inactivarOrdenItem($orden_item_id) {
        $data['estado'] = ESTADO_INACTIVO;
        $data['id'] = $orden_item_id;

        return $this->actualizar("ecommerce.orden_item", $data, "id", true);
    }

    public function eliminarLineasOrden($orden_id) {
        $data['estado'] = ESTADO_INACTIVO;
//        $data['eliminacion_fecha'] = fechaActual();
//        $data['eliminacion_usu'] = $this->session->userdata("userId");
        $data['orden_id'] = $orden_id;

        return $this->actualizar("ecommerce.orden_item", $data, array("orden_id" => -1, "estado" => ESTADO_ACTIVO), true);
    }
        public function existeOrdenShipping($objOrden) {
        $this->db->select('od.*');
        $this->db->from('ecommerce.orden_descuento od');
        if (array_key_exists("id", $objOrden)) {
            $this->db->where('od.id', $objOrden['id']);
        } else {
            $this->db->where('od.shopify_order_id', $objOrden['shopify_order_id']);
            $this->db->where('od.referencia_order_number', $objOrden['referencia_order_number']);
        }
        $orden = $this->retornarUno();
        return $orden;
    }
    public function crearShippingOrden($datos) {
    $orden_descuento_id = $this->ingresar("ecommerce.orden_descuento", $datos, true, true);
    if ($orden_descuento_id) {
        $dato_log = array(
            "orden_descuento_id" => $orden_descuento_id,
            "accion" => "creacion de registro shipping" . json_encode($datos),
        );
        $this->registrarLog("ecommerce.orden_descuento_log", $dato_log);
    }
    return $orden_descuento_id;
    }

        public function actualizarShippingOrden($objOrden, $accion = false) {
        $actualizacion = $this->actualizar("ecommerce.orden_descuento", $objOrden, false, true);
        $dato_log = array(
            "orden_descuento_id" => $objOrden['id'],
            "accion" => ($accion ? $accion . " => " : "actualizacion de orden shipping") . json_encode($objOrden),
        );
        $this->registrarLog("ecommerce.orden_descuento_log", $dato_log);
        return $actualizacion;
    }

    public function crearOrden($datos) {
        $datos["estado"] = ESTADO_ACTIVO;
        $orden_id = $this->ingresar("ecommerce.orden", $datos, true, true);
//        echo "CREADO";
        if ($orden_id) {
//            die($orden_id);
            $dato_log = array(
                "orden_id" => $orden_id,
                "accion" => "creacion de orden" . json_encode($datos),
            );
            $this->registrarLog("ecommerce.orden_log", $dato_log);
        }
        return $orden_id;
    }

    public function actualizarOrden($objOrden, $accion = false) {
        $actualizacion = $this->actualizar("ecommerce.orden", $objOrden, false, true);
        $dato_log = array(
            "orden_id" => $objOrden['id'],
            "accion" => ($accion ? $accion . " => " : "actualizacion de orden ") . json_encode($objOrden),
        );
        $this->registrarLog("ecommerce.orden_log", $dato_log);
        return $actualizacion;
    }

    public function obtener_html_tarjetas($id, $orden_caja_id = false, $tipo = TARJETA_NORMALES, $empaque_filtro = 'T', $kardex_filtro = 'T', $session_finca = false, $finca_id = false) {
        error_log("obtener_html_tarjetas_" . $id);
        $orden = $this->service_ecommerce_orden->existeOrden(array('id' => $id));
        $this->load->model("Generales/service_general");
        $data['orden'] = $orden;
        if (isset($session_finca) && !empty($session_finca)) {
            $cajas = $this->service_ecommerce_logistica->obtenerOrdenCajas($orden->id, ESTADO_ACTIVO, $orden_caja_id, $session_finca, $finca_id);
        } else {
            $cajas = $this->service_ecommerce_logistica->obtenerOrdenCajas($orden->id, ESTADO_ACTIVO, $orden_caja_id);
        }

        if (!$cajas) {
            error_log("No hay cajas");
            return false;
        }

        $arr_contenido = array();
        $num_cajas_total = $num_cajas = 0;
        $arr_items = array();
        foreach ($cajas as $caja) {
            $arr = array();
            $itemsCaja = $this->service_ecommerce_logistica->obtenerOrdenCajaItems($caja->id);
            if ($itemsCaja) {
                $num_cajas_total++;
                if ($empaque_filtro != 'T') {
                    if ($caja->empacada !== $empaque_filtro) {
                        continue;
                    }
                }
                if ($kardex_filtro != 'T') {
                    if ($caja->kardex_check !== $kardex_filtro) {
                        continue;
                    }
                }
                $florero = 0;
                $petalos = 0;
                foreach ($itemsCaja as $item) {
                    $totalStems = $this->service_ecommerce_formula->totalStemsRecetaSKU($item->info_variante_sku);
                    $totalStems = $totalStems->sum;
                    $propiedades = $this->service_ecommerce->obtenerOrdenItemPropiedades($item->id);
                    $item->propiedades = array();

                    if ($propiedades) {
                        $propiedades_filtradas = array();
                        foreach ($propiedades as $propiedad) {
                            if ((strpos($propiedad->info_propiedad_nombre, "AGR_") !== false) ||
                                    ($propiedad->propiedad_id == 18) || ($propiedad->propiedad_id == 372) || ($propiedad->propiedad_id == 373) || ($propiedad->propiedad_id == 398) || //florero
                                    ($propiedad->propiedad_id == 48) || ($propiedad->propiedad_id == 67) || ($propiedad->propiedad_id == 158) || //instrucciones
                                    ($propiedad->propiedad_id == 12) || //message
                                    ($propiedad->propiedad_id == 11) || ($propiedad->propiedad_id == 371) || //petalos
                                    ($propiedad->propiedad_id == 10) || ($propiedad->propiedad_id == 235) || ($propiedad->propiedad_id == 394) //wrap envoltura
                            ) {
                                if ($propiedad->propiedad_id == 18 || $propiedad->propiedad_id == 372 || $propiedad->propiedad_id == 373 || $propiedad->propiedad_id == 398) {
                                    $prop = analizarPropiedad($propiedad, true, true);
                                    if ($prop) {
                                        $florero++;
                                    }
                                }
                                if ($propiedad->propiedad_id == 11 || $propiedad->propiedad_id == 371) {
                                    $prop = analizarPropiedad($propiedad, true, true);
                                    if ($prop) {
                                        $petalos++;
                                    }
                                }

                                if ($propiedad->propiedad_id == 12) { //standing order
                                    $arr['mensaje'] = $propiedad->valor;
                                }
                                if (strpos(strtoupper($propiedad->info_propiedad_nombre), 'AGR_') === 0) {
                                    $total = $this->service_ecommerce_formula->totalStemsRecetaSKU($propiedad->info_propiedad_nombre);
                                    $totalStems += intval($total->sum) * intval($propiedad->valor);
                                }
                                $propiedades_filtradas[] = $propiedad;
                            }
                        }
                        $item->propiedades = $propiedades_filtradas;
                    }
//error_log(print_r($item->propiedades,true));die;
                    $item->totalStems = $totalStems;

                    $arr['items'][] = $item;
//                    $propiedad_mensaje = $this->service_ecommerce->existeOrdenItemPropiedad($item->id, 12);
//                    if ($propiedad_mensaje) {
//                        $arr['mensaje'] = $propiedad_mensaje->valor;
//                    }
                }

                $arr['orden_caja'] = $orden->tienda_alias . "-" . (!empty($orden->referencia_order_number) ? $orden->referencia_order_number : $orden->id) . "-" . $caja->id . " P#" . ($orden->impresiones + 1) . "";
                $arr['orden_identificador'] = $orden->tienda_alias . " " . (!empty($orden->referencia_order_number) ? $orden->referencia_order_number : $orden->id) . " BOX #" . $caja->id . " - P#" . ($orden->impresiones + 1) . "";
                $arr['orden_caja_id'] = $caja->id;
                $arr['orden_caja_tipo'] = $caja->info_nombre_caja;

                $arr['footer_left'] = $orden->tienda_alias . "" . (!empty($orden->referencia_order_number) ? $orden->referencia_order_number : $orden->id);
                $arr['footer_left'] .= "&nbsp;" . $caja->info_abreviado_caja . ($florero > 0 ? "&nbsp;-&nbsp;Fx" . $florero : "") . ($petalos > 0 ? "&nbsp;-&nbsp;" . "Px" . $petalos : "");

                $arr['footer_right'] = "BOX #" . $caja->id . " - P#" . ($orden->impresiones + 1);
                $arr_items[] = $arr;
            }
            $arr_contenido[] = $arr_items;
        }
        $html = array();

        $num_caja_actual = 0;

        foreach ($arr_items as $item) {//una pagina por cada mensaje
            error_log(print_r($item, true));

            $num_caja_actual++;
            $arr = array();

            if ($tipo == TARJETA_NORMALES) {
                $blanco = "&nbsp;";
                $footer = $item['footer_left'];
                while (strlen($footer . $blanco . $item['footer_right']) < 600) {
                    $footer .= $blanco;
                }
                $footer .= $item['footer_right'];
            } else if ($tipo == TARJETA_ETERNIZADAS) {
                $footer = 'Rosaholics.com';
                $blanco = "&nbsp;";
                while (strlen($footer . $blanco) < 80) {
                    $footer = $blanco . $footer;
                }
                while (strlen($footer . $blanco . $item['orden_identificador']) < 285) {
                    $footer .= $blanco;
                }
                $footer .= $item['orden_identificador'];
            }
            $arr['header'] = false;
            $arr['orden_caja'] = $item['orden_caja'];
            $arr['orden_caja_id'] = $item['orden_caja_id'];
            $arr['num_caja_actual'] = $num_caja_actual;
            $arr['total_cajas'] = $num_cajas_total;
            $arr['componentes'] = 0;

            error_log("Tipo es " . $tipo);
            if ($tipo == TARJETA_NORMALES) {
                if (array_key_exists('mensaje', $item) && (strlen($item['mensaje']) > 0)) {
                    $data['mensaje'] = '<p style="line-height: 120%;letter-spacing: 0.8pt;">' . $item['mensaje'] . '</p>';
                    $data['imagen_firma'] = false;
                    $arr['footer'] = '<div style="font-size: 0.4em; padding: 0; margin-top: -50px;"><hr>' . $footer . '</div>';
                    error_log("Normal ");
                    $arr['mensaje'] = $this->load->view('orden_tarjeta_mensaje.php', $data, true);
                    $html[] = $arr;
                } else {
                    // $data['mensaje'] = 'On regards of all 500 people in the Rosaholics family, thanks for trusting us and we hope you enjoy your flowers.';
                    $data['mensaje'] = '<p style = "font-family:times new roman;font-size:12px; line-height:120%; text-align: justify; margin-top:-2000px; ">We love having the opportunity to share exceptional beauty with you. On behalf of all 500 people in the Rosaholics family. Thank you for being part of the Rosaholics journey. We Hope you enjoy your flowers!</p><p style="font-family:parisienne; align:justify;font-size:18px;">Juan Pablo Torres</p><p style="font-family:times new roman;line-height: -12em;font-size: 12px; margin-top:-10px;text-align: center;">CEO</p>';
                    $data['imagen_firma'] = false;
                    $arr['footer'] = '<div style="font-size: 0.4em; padding: 0; margin-top: -50px;"><hr>' . $footer . '</div>';
                    $arr['mensaje'] = $this->load->view('orden_tarjeta_mensaje.php', $data, true);
                    $html[] = $arr;
                    // $data['mensaje'] = 'On regards of all 500 people in the Rosaholics family, thanks for trusting us and we hope you enjoy your flowers.';
                    // $data['imagen_firma'] = true;
                }
            } else if ($tipo == TARJETA_ETERNIZADAS) {
                $data['mensaje'] = false;
                if (array_key_exists('mensaje', $item) && (strlen($item['mensaje']) > 0)) {
                    $data['mensaje'] = $item['mensaje'];
                }
                $arr['footer'] = '<div style="text-align:left; font-size: 8; font-weight:bold; font-family:monospace; padding: 0; margin: 0;">' . $footer . '</div>';
                $arr['mensaje'] = $this->load->view('orden_tarjeta_eternizadas.php', $data, true);
            }

//            $arr['mensaje'] = $this->load->view('orden_tarjeta_mensaje.php', $data, true);


            $data = array();
            $data['items'] = $item['items'];
            if ($tipo == TARJETA_NORMALES) {
                $arr['mensaje'] = $this->load->view('orden_tarjeta_componentes.php', $data, true);
            }
            $arr['componentes'] = 1;
            $html[] = $arr;
        }
        return $html;
    }

    public function generar_tarjetas_orden($id, $caja_id = false, $tipo = "normal", $session_finca = false, $finca_id = false) {
        $html = $this->obtener_html_tarjetas($id, $caja_id, $tipo, 'T', 'T', $session_finca, $finca_id);
        if (!$html) {
            return false;
        }
        if ($tipo == TARJETA_NORMALES) {
            $page_format = array(
                'MediaBox' => FORMATO_10x15,
                'Dur' => 3,
                'trans' => array(
                    'D' => 1.5,
                    'S' => 'Split',
                    'Dm' => 'V',
                    'M' => 'O'
                ),
                'Rotate' => FORMATO_10x15_ROTACION,
                'PZ' => 1,
            );
            $pdf = $this->service_general->pdf_generacion($html, $page_format);
        } else if ($tipo == TARJETA_ETERNIZADAS) {
            $page_format = array(
                'MediaBox' => FORMATO_10x15,
                'Dur' => 3,
                'trans' => array(
                    'D' => 1.5,
                    'S' => 'Split',
                    'Dm' => 'V',
                    'M' => 'O'
                ),
                'Rotate' => FORMATO_10x15_ROTACION_ETERNIZADAS,
                'PZ' => 1,
            );
            $pdf = $this->service_general->pdf_generacion_eternizadas($html, $page_format);
        }

        return $pdf;
    }

    public function impresion_masiva_pdf($ids) {
        $html = array();
        $ids_impresas = array();
        foreach ($ids as $idc) {
            $arr = explode('_', $idc);
            if (sizeof($arr) == 2) {
                $id = $arr[0];
                $id_caja = $arr[1];
            } else {
                $id = $idc;
                $id_caja = false;
            }

            error_log("id a obtener " . $id);
            $tarjeta_orden = $this->obtener_html_tarjetas($id, $id_caja, TARJETA_NORMALES, 'N');
            if ($tarjeta_orden) {
                error_log("tarjeta impresa");
                $ids_impresas[] = $id;
                $html = array_merge($html, $tarjeta_orden);
            }
        }
        if (sizeof($html) == 0) {
            return false;
        }
        $pdf = $this->service_general->pdf_generacion($html);
        foreach ($ids_impresas as $orden_id) {
            $orden = $this->service_ecommerce->obtenerOrden($orden_id);
            $data['id'] = $orden_id;
            $data['impresiones'] = $orden->impresiones + 1;
            $actualizacion = $this->service_ecommerce_orden->actualizarOrden($data, "Impresion tarjeta masiva");
        }
        return array($pdf, $ids_impresas);
    }

    public function obtenerOrdenFiltrado($filtro) {
        if ($filtro['store_id'] != 0) {
            $this->db->where('o.store_id', $filtro['store_id']);
        }
        if (array_key_exists('producto_id', $filtro) && !empty($filtro['producto_id'])) {
            $this->db->where('oi.producto_id', $filtro['producto_id']);
        }


        if (array_key_exists('tipo_caja', $filtro) && !empty($filtro['tipo_caja'])) {
            if ($filtro['tipo_caja'] != 0) {
                $this->db->where('otp.id', $filtro['tipo_caja']);
            }
        }

        if (array_key_exists('reenviado', $filtro)) {
            if ($filtro['reenviado'] != 'T') {
                if ($filtro['reenviado'] == 'S') {
                    $this->db->where('o.reenvio_orden_id is NOT NULL', null, false);
                } else {
                    $this->db->where('o.reenvio_orden_id is NULL');
                }
            }
        }
        if (array_key_exists('con_tracking_number', $filtro)) {
            if ($filtro['con_tracking_number'] !== 'T') {
                if ($filtro['con_tracking_number'] == 'S') {
                    $this->db->where("octn.tracking_number is NOT NULL ");
                } else {
                    $this->db->where("(octn.tracking_number is NULL OR octn.estado =  '" . ESTADO_INACTIVO . "')");
                }
            }
        }

        if (array_key_exists('session_finca', $filtro)) {
            $arrayfinca = explode(",", $filtro['session_finca']);
            if (in_array(FINCA_ROSAHOLICS_ID, $arrayfinca)) {
                if ($filtro['finca_id'] != 0) {
                    $srt = "fc.finca_id in (" . $filtro['finca_id'] . ")";
                }
            } else {
                if (array_key_exists('finca_id', $filtro)) {
                    if ($filtro['finca_id'] != 0) {
                        $srt = "fc.finca_id in (" . $filtro['finca_id'] . ")";
                    } else {
                        $srt = "fc.finca_id in (" . $filtro['session_finca'] . ")";
                    }
                } else {
                    $srt = "fc.finca_id in (" . $filtro['session_finca'] . ")";
                }
            }
            if(isset($srt)){
              $this->db->where($srt);
            }
        }

        if (array_key_exists('orden_estado_id', $filtro) && !empty($filtro['orden_estado_id'])) {
            if ($filtro['orden_estado_id'] != 'T') {
                $this->db->where('o.estado', $filtro['orden_estado_id']);
            }
        }

        if (array_key_exists('variante_id', $filtro) && !empty($filtro['variante_id'])) {
            $this->db->where('oi.variante_id', $filtro['variante_id']);
        }

        if (array_key_exists('order_number', $filtro) && !empty($filtro['order_number'])) {
            $this->db->where("(o.id = " . $filtro['order_number'] . " OR o.reenvio_orden_id = '" . $filtro['order_number'] . "' OR o.clonacion_orden_id = '" . $filtro['order_number'] . "')");
            return;
        }
        if (array_key_exists('referencia_order_number', $filtro) && !empty($filtro['referencia_order_number'])) {
            $this->db->where("o.referencia_order_number = '" . $filtro['referencia_order_number'] . "'");
            return;
        }


        if (array_key_exists('rango_busqueda', $filtro) && !empty($filtro['rango_busqueda'])) {
//rango_busqueda espera "dd/mm/YYYY - dd/mm/YYYY"
            $arrRango = explode(" - ", $filtro['rango_busqueda']);
            $fechaIni = date_format(DateTime::createFromFormat(FORMATO_FECHA, convertirFechaBD($arrRango[0])), 'Y-m-d 00:00:00');
            $fechaFin = date_format(DateTime::createFromFormat(FORMATO_FECHA, convertirFechaBD($arrRango[1])), 'Y-m-d 23:59:59');
            if (sizeof($arrRango) != 2) {
                return array(false, -1);
            }
            switch ($filtro['tipo_calendario']) {
                case 0: //carguera
                    $arrSelect = array('o.fecha_carguera >= ' => $fechaIni, 'o.fecha_carguera <= ' => $fechaFin);
                    break;
                case 1: //entrega
                    $arrSelect = array('o.fecha_entrega >= ' => $fechaIni, 'o.fecha_entrega <= ' => $fechaFin);
                    break;
                case 2: //actualizacion
                    $arrSelect = array('o.fecha_entrega >= ' => $fechaIni, 'o.fecha_entrega <= ' => $fechaFin);
                    break;
                default:
                    $arrSelect = array('o.fecha_entrega >= ' => $fechaIni, 'o.fecha_entrega <= ' => $fechaFin);
                    break;
            }

            $this->db->where($arrSelect);
        }

//            if (array_key_exists('busqueda', $filtro) && !empty($filtro['busqueda'])) {
//                $this->db->where(" ( UPPER(c.nombres) LIKE '%" . strtoupper($filtro['busqueda']) . "%'  "
//                        . "OR UPPER(c.apellidos) LIKE '%" . strtoupper($filtro['busqueda']) . "%'  "
//                        . "OR UPPER(c.nombre_comercial) LIKE '%" . strtoupper($filtro['busqueda']) . "%'  "
//                        . "OR UPPER(c.email) LIKE '%" . strtoupper($filtro['busqueda']) . "%'  "
//                        . "OR UPPER(c.country) LIKE '%" . strtoupper($filtro['busqueda']) . "%'  "
//                        . "OR UPPER(c.state) LIKE '%" . strtoupper($filtro['busqueda']) . "%'  "
//                        . "OR UPPER(c.city) LIKE '%" . strtoupper($filtro['busqueda']) . "%'  "
//                        . "OR UPPER(c.address) LIKE '%" . strtoupper($filtro['busqueda']) . "%' ) ");
//            }
        //$this->db->where('o.estado', ESTADO_ACTIVO);

        if ((array_key_exists('preparado', $filtro) && $filtro['preparado'] != null)) {
            if ($filtro['preparado'] != 'T') {
                $this->db->where('oi.preparado', $filtro['preparado']);
            }
        }
        if ((array_key_exists('terminado', $filtro) && $filtro['terminado'] != null)) {
            if ($filtro['terminado'] != 'T') {
                $this->db->where('oi.terminado', $filtro['terminado']);
            }
        }

        if (array_key_exists('empacado', $filtro) && $filtro['empacado'] != 'T' && $filtro['empacado'] != null) { //PARA FILTRAR SOLO LOS QUE NO ESTAN EN CAJA EMPACADOS
            $this->db->where('oc.empacada', $filtro['empacado']);
        }
    }

    public function obtenerOrdenesItems($filtro) {
        $this->db->select('o.id, o.fecha_entrega, o.referencia_order_number,  o.fecha_carguera, o.fecha_preparacion, o.estado as "orden_estado", '
                . ' oi.id as "orden_item_id", s.id as "tienda_id", s.alias as "tienda_alias", oi.estado as "estado_item", oi.cantidad as "orden_item_cantidad", '
                . ' oi.preparado as "preparado", oi.preparacion_fecha as "preparacion_fecha", '
                . ' oi.terminado as "terminado", oi.terminado_fecha as "terminado_fecha", '
                . ' oi.producto_id as "producto_id", '
                . ' oi.variante_id as "variante_id", pv.cantidad as "variante_cantidad", pv.largo_cm as "largo_cm" ');
        $this->db->from('ecommerce.orden o');
        $this->db->join('ecommerce.store s', 'o.store_id = s.id', 'left');
        $this->db->join('ecommerce.orden_item oi', "o.id = oi.orden_id AND oi.estado = '" . ESTADO_ACTIVO . "'", 'left');
        $this->db->join('ecommerce.orden_caja_item oci', "oi.id = oci.orden_item_id AND oci.estado = '" . ESTADO_ACTIVO . "'", 'left');
        $this->db->join('ecommerce.orden_caja oc', "oci.orden_caja_id = oc.id AND oci.estado = '" . ESTADO_ACTIVO . "'", 'left'); //PARA FILTRAR SOLO LOS QUE NO ESTAN EN CAJA EMPACADOS
        $this->db->join('ecommerce.finca_caja fc', "fc.orden_caja_id = oc.id AND fc.estado = '" . ESTADO_ACTIVO . "'", 'left');
        $this->db->join('ecommerce.producto_variante pv', 'oi.variante_id = pv.id', 'left');
//no mostramos los que son next day
        $this->db->where("pv.sku NOT LIKE 'AGR_PN%'");
        $this->db->where("(oc.empacada IS NULL OR oc.empacada = 'N')"); //filtro para mostrar lo que no esta en caja o esta en caja pero no empacado
//$this->db->where('o.id',11831);
        $this->obtenerOrdenFiltrado($filtro);

        $this->db->order_by('oi.id', 'ASC');
        return $this->retornarMuchosSinPaginacion();
    }

    //buscar todas las cajas por finca_id en rango de fecha,tipo de caja, orden_caja_id, store_id, textobusqueda= para nombre_producto,sku_variante
    public function obtenerOrdenesporCaja($filtro) {
        $this->db->select('DISTINCT(oc.*),o.id as orden_id,o.referencia_order_number, o.fecha_entrega,f.nombre as finca,octn.tracking_number as tracking, s.id as "tienda_id", s.store_name as "tienda_nombre", s.alias as "tienda_alias", '
                . ' c.id as cliente_id, c.nombres as cliente_nombre, c.apellidos as cliente_apellidos, c.nombre_comercial as nombre_comercial, '
                . ' d.id as direccion_id, d.country, d.state, d.city, d.destinatario_nombre, d.destinatario_apellido, d.destinatario_company');
        $this->db->from('ecommerce.orden_caja oc');
        $this->db->join('ecommerce.orden o', "o.id = oc.orden_id AND oc.estado = '" . ESTADO_ACTIVO . "' ", 'left');
        $this->db->join('ecommerce.cliente c', 'o.cliente_id = c.id', 'left');
        $this->db->join('ecommerce.finca_caja fc', "fc.orden_caja_id = oc.id AND fc.estado = '" . ESTADO_ACTIVO . "'", 'left');
        $this->db->join('general.finca f', 'f.id = fc.finca_id', 'left');
        $this->db->join('ecommerce.orden_caja_item oci', "oc.id = oci.orden_caja_id AND oci.estado = '" . ESTADO_ACTIVO . "' ", 'left');
        $this->db->join('ecommerce.cliente_direccion_envio d', 'o.cliente_direccion_id = d.id', 'left');
        $this->db->join('ecommerce.orden_item oi', "oi.id = oci.orden_item_id AND oi.estado = '" . ESTADO_ACTIVO . "' ", 'left');
        $this->db->join('ecommerce.tipo_caja otp', "otp.id = oc.tipo_caja_id AND otp.estado = '" . ESTADO_ACTIVO . "' ", 'left');
        $this->db->join('ecommerce.store s', 'o.store_id = s.id', 'left');
        $this->db->join('logistica.orden_caja_tracking_number octn', "octn.orden_caja_id = oc.id ", 'left');

        $this->obtenerOrdenFiltrado($filtro);
        //TODO verificar si debe ir en obtenerOrdenFiltrado wsanchez
        $this->db->where("(octn.tracking_number is NULL OR octn.estado =  '" . ESTADO_ACTIVO . "')");
        $conteo = $this->retornarConteo();
        $this->db->order_by('oc.id', 'ASC');
        //echo "Estoy aqui";
        // print_r($this->db->last_query(), false);
        $arr = $this->retornarMuchosConPaginacion(true);
        return array($arr, $conteo);
    }

    public function obtenerOrdenes($filtro) {
        $this->db->select('DISTINCT(o.*), s.id as "tienda_id", s.store_name as "tienda_nombre", s.alias as "tienda_alias", '
                . ' c.id as cliente_id, c.nombres as cliente_nombre, c.apellidos as cliente_apellidos, c.nombre_comercial as nombre_comercial, '
                . ' d.id as direccion_id, d.country, d.state, d.city, d.destinatario_nombre, d.destinatario_apellido, d.destinatario_company');
        $this->db->from('ecommerce.orden o');
        $this->db->join('ecommerce.cliente c', 'o.cliente_id = c.id', 'left');
        $this->db->join('ecommerce.orden_caja oc', "oc.orden_id = o.id AND oc.estado = '" . ESTADO_ACTIVO . "' ", 'left');
        $this->db->join('ecommerce.orden_caja_item oci', "oc.id = oci.orden_caja_id AND oci.estado = '" . ESTADO_ACTIVO . "' ", 'left');
        $this->db->join('ecommerce.cliente_direccion_envio d', 'o.cliente_direccion_id = d.id', 'left');
        $this->db->join('ecommerce.orden_item oi', "oi.id = oci.orden_item_id AND oi.estado = '" . ESTADO_ACTIVO . "' ", 'left');
        $this->db->join('ecommerce.store s', 'o.store_id = s.id', 'left');
        $this->db->join('ecommerce.finca_caja fc', "fc.orden_caja_id = oc.id AND fc.estado = '" . ESTADO_ACTIVO . "'", 'left');
        $this->obtenerOrdenFiltrado($filtro);

        $conteo = $this->retornarConteo();
        $this->db->order_by('o.id', 'ASC');
        $arr = $this->retornarMuchosConPaginacion(true);
        return array($arr, $conteo);
    }

    public function obtenerOrdenItems($orden_id) {
        return $this->obtenerOrdenItem(false, $orden_id);
    }

    public function obtenerOrdenItem($orden_item_id, $orden_id = false, $orden_caja_id = false) {
        $select = 'oi.*, p.titulo as info_producto_titulo, p.sku_prefijo as info_producto_prefijo, pv.titulo as info_variante_titulo, pv.sku as info_variante_sku, ';
        $select .= ' oc.id as orden_caja_id, tc.id as info_tipo_caja_id, tc.nombre as info_tipo_caja_nombre ';
        $this->db->select($select);
        $this->db->from('ecommerce.orden_item oi');
        $this->db->join('ecommerce.producto p', 'oi.producto_id = p.id', 'left');
        $this->db->join('ecommerce.producto_variante pv', 'oi.variante_id = pv.id', 'left');
        $this->db->join('ecommerce.orden_caja_item oci', 'oci.orden_item_id = oi.id AND oci.estado = \'' . ESTADO_ACTIVO . '\'', 'left');
        $this->db->join('ecommerce.orden_caja oc', 'oci.orden_caja_id = oc.id AND oc.estado = \'' . ESTADO_ACTIVO . '\'', 'left');
        $this->db->join('ecommerce.tipo_caja tc', 'oc.tipo_caja_id = tc.id', 'left');
        $this->db->join('ecommerce.finca_caja fc', "fc.orden_caja_id = oc.id AND fc.estado = '" . ESTADO_ACTIVO . "'", 'left');
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

        return $this->retornarMuchosSinPaginacion();
    }

    public function obtenerTotalOrdenes($filtro) {
        $this->db->select('count(*) as totalOrdenes');
        $this->db->from('ecommerce.orden o');
        $this->db->join('ecommerce.orden_caja oc', 'o.id = oc.orden_id AND oc.estado = \'' . ESTADO_ACTIVO . '\'', 'left');
        $this->db->join('ecommerce.finca_caja fc', 'oc.id = fc.orden_caja_id AND fc.estado = \'' . ESTADO_ACTIVO . '\'', 'left');
        $this->obtenerOrdenFiltrado($filtro);
       $this->db->where('o.estado', ESTADO_ACTIVO);
        //    print_r($this->db->last_query());
        return $this->retornarUno();
    }

    public function obtenerTotalCajas($filtro) {
        $this->db->select('tc.id, tc.nombre, count(DISTINCT(oc.id)), SUM(CASE WHEN oc.empacada = \'S\' THEN 1 ELSE 0 END) as cajas_empacadas, SUM(CASE WHEN oc.kardex_check = \'S\' THEN 1 ELSE 0 END) as cajas_con_kardex');
        if ($filtro['store_id'] != 0) {
            $this->db->where('o.store_id', $filtro['store_id']);
        }
        $this->db->from('ecommerce.orden_caja oc');
        $this->db->join('ecommerce.orden o', 'o.id = oc.orden_id', 'left');
        $this->db->join('ecommerce.tipo_caja tc', 'tc.id = oc.tipo_caja_id', 'left');
        $this->db->join('ecommerce.finca_caja fc', 'fc.orden_caja_id = oc.id', 'left');
        if (array_key_exists('orden_estado_id', $filtro) && !empty($filtro['orden_estado_id'])) {
            if ($filtro['orden_estado_id'] > 1) {
                $this->db->where('o.estado = (select x.descripcion from ecommerce.orden_estado x where x.id=' . $filtro['orden_estado_id'] . ' )');
            }
        }
        $this->db->where('oc.estado', ESTADO_ACTIVO);
        if (array_key_exists('rango_busqueda', $filtro) && !empty($filtro['rango_busqueda'])) {
            $this->obtenerRangoFechaOrdenes($filtro);
        }
        if (array_key_exists('session_finca', $filtro)) {
            $arrayfinca = explode(",", $filtro['session_finca']);
            if (in_array(FINCA_ROSAHOLICS_ID, $arrayfinca)) {
                if ($filtro['finca_id'] != 0) {
                    $srt = "fc.finca_id in (" . $filtro['finca_id'] . ")";
                }
            } else {
                if (array_key_exists('finca_id', $filtro)) {
                    if ($filtro['finca_id'] != 0) {
                        $srt = "fc.finca_id in (" . $filtro['finca_id'] . ")";
                    } else {
                        $srt = "fc.finca_id in (" . $filtro['session_finca'] . ")";
                    }
                } else {
                    $srt = "fc.finca_id in (" . $filtro['session_finca'] . ")";
                }
            }
            if(isset($srt)){
              $this->db->where($srt);
            }
        }

        $this->db->group_by(array('tc.id', 'tc.nombre'));
        return $this->retornarMuchosSinPaginacion();
    }

    public function obtenerTotalOrdenesEnCaja($filtro) {
        $this->db->select('o.id as orden_id,
                        SUM(1) as items_orden,
                        SUM(CASE WHEN oi.preparado = \'S\' THEN 1 ELSE 0 END) as items_preparados,
                        SUM(CASE WHEN oi.terminado = \'S\' THEN 1 ELSE 0 END) as items_terminados,
                        SUM(CASE WHEN oci.id IS NOT NULL THEN 1 ELSE 0 END) as items_en_caja,
                        SUM(CASE WHEN oci.id IS NULL THEN 1 ELSE 0 END) as items_no_en_caja
        ');
        if ($filtro['store_id'] != 0) {
            $this->db->where('o.store_id', $filtro['store_id']);
        }
        $this->db->from('ecommerce.orden_item oi');
        $this->db->join('ecommerce.orden o', 'oi.orden_id = o.id AND o.estado =  \'' . ESTADO_ACTIVO . '\'', 'left');
        $this->db->join('ecommerce.orden_caja_item oci', 'oci.orden_item_id = oi.id AND oci.estado = \'' . ESTADO_ACTIVO . '\'', 'left');
        $this->db->join('ecommerce.orden_caja oc', 'oci.orden_caja_id = oc.id AND oc.estado = \'' . ESTADO_ACTIVO . '\'', 'left');
        $this->db->join('ecommerce.finca_caja fc', 'oc.id = fc.orden_caja_id AND fc.estado = \'' . ESTADO_ACTIVO . '\'', 'left');
        if (array_key_exists('orden_estado_id', $filtro) && $filtro['orden_estado_id'] > 1) {
            $this->db->where('o.estado', $filtro['orden_estado_id']);
        }

        $this->db->where('oi.estado', ESTADO_ACTIVO);
        if (array_key_exists('session_finca', $filtro)) {
            $arrayfinca = explode(",", $filtro['session_finca']);
            if (in_array(FINCA_ROSAHOLICS_ID, $arrayfinca)) {
                if ($filtro['finca_id'] != 0) {
                    $srt = "fc.finca_id in (" . $filtro['finca_id'] . ")";
                }
            } else {
                if (array_key_exists('finca_id', $filtro)) {
                    if ($filtro['finca_id'] != 0) {
                        $srt = "fc.finca_id in (" . $filtro['finca_id'] . ")";
                    } else {
                        $srt = "fc.finca_id in (" . $filtro['session_finca'] . ")";
                    }
                } else {
                    $srt = "fc.finca_id in (" . $filtro['session_finca'] . ")";
                }
            }
            if(isset($srt)){
              $this->db->where($srt);
            }
        }

        if (array_key_exists('rango_busqueda', $filtro) && !empty($filtro['rango_busqueda'])) {
            $this->obtenerRangoFechaOrdenes($filtro);
        }
        $this->db->group_by('o.id');
        $this->db->order_by('o.id', 'ASC');

        return $this->retornarMuchosSinPaginacion(true);
    }

    public function obtenerTotales($filtro, $infoCaja = true) {
        $totalOrdenes = $totalItems = $totalItemsEnCaja = $totalItemsNoEnCaja = 0;
        $totalOrdenesTotalmenteAsignadasEnCaja = $totalOrdenesParcialmenteEnCaja = $totalOrdenesSinCaja = 0;
        $totalItemsPreparados = $totalOrdenesTotalmentePreparadas = $totalOrdenesParcialmentePreparadas = $totalOrdenesSinPreparar = 0;
        $totalItemsTerminados = $totalOrdenesTotalmenteTerminadas = $totalOrdenesParcialmenteTerminadas = $totalOrdenesSinTerminar = 0;
        $totalCajas = false;
        $totalOrdenesEnCaja = $this->obtenerTotalOrdenesEnCaja($filtro);
        $totalOrdenes = $this->obtenerTotalOrdenes($filtro);
        $totalOrdenes = $totalOrdenes->totalordenes;
        $total1 = $total2 = $total3 = $total4 = 0;

        if ($totalOrdenesEnCaja) {
            foreach ($totalOrdenesEnCaja as $ordenCaja) {

                $total1 += $ordenCaja->items_orden;
                $total2 += $ordenCaja->items_preparados;
                $total3 += $ordenCaja->items_en_caja;
                $total4 += $ordenCaja->items_no_en_caja;
//                $totalOrdenes++;
                $totalItems += $ordenCaja->items_orden;
                $totalItemsEnCaja += $ordenCaja->items_en_caja;
                $totalItemsNoEnCaja += $ordenCaja->items_no_en_caja;
                if ($ordenCaja->items_en_caja > 0) {
                    if ($ordenCaja->items_en_caja == $ordenCaja->items_orden) {
                        $totalOrdenesTotalmenteAsignadasEnCaja++;
                    } else {
                        $totalOrdenesParcialmenteEnCaja++;
                    }
                } else {
                    $totalOrdenesSinCaja++;
                }
                if ($ordenCaja->items_preparados > 0) {
                    if ($ordenCaja->items_preparados == $ordenCaja->items_orden) {
                        $totalOrdenesTotalmentePreparadas++;
                    } else {
                        $totalOrdenesParcialmentePreparadas++;
                    }
                } else {
                    $totalOrdenesSinPreparar++;
                }
                if ($ordenCaja->items_terminados > 0) {
                    if ($ordenCaja->items_terminados == $ordenCaja->items_orden) {
                        $totalOrdenesTotalmenteTerminadas++;
                    } else {
                        $totalOrdenesParcialmenteTerminadas++;
                    }
                } else {
                    $totalOrdenesSinTerminar++;
                }
            }

            if ($infoCaja) {
                $totalCajas = $this->obtenerTotalCajas($filtro);
            }
        }
        $resp = array(
            "totalOrdenes" => $totalOrdenes,
            "totalOrdenesTotalmenteAsignadasEnCaja" => $totalOrdenesTotalmenteAsignadasEnCaja,
            "totalOrdenesParcialmenteEnCaja" => $totalOrdenesParcialmenteEnCaja,
            "totalOrdenesSinCaja" => $totalOrdenes - $totalOrdenesTotalmenteAsignadasEnCaja - $totalOrdenesParcialmenteEnCaja,
            "totalProductosEnCaja" => $totalItemsEnCaja,
            "totalProductosSinCaja" => $totalItemsNoEnCaja,
            //"totalItemsPreparados" => $totalItemsPreparados,
            "totalOrdenesTotalmentePreparadas" => $totalOrdenesTotalmentePreparadas,
            "totalOrdenesParcialmentePreparadas" => $totalOrdenesParcialmentePreparadas,
            "totalOrdenesSinPreparar" => $totalOrdenesSinPreparar,
            //"totalItemsTerminados" => $totalItemsTerminados,
            "totalOrdenesTotalmenteTerminadas" => $totalOrdenesTotalmenteTerminadas,
            "totalOrdenesParcialmenteTerminadas" => $totalOrdenesParcialmenteTerminadas,
            "totalOrdenesSinTerminar" => $totalOrdenesSinTerminar,
            "totalCajas" => $totalCajas,
        );
        return $resp;
    }

    public function obtenerFincaSelect($id = false) {
        $finca = $this->session->userFincaId;
        $this->db->select('s.*');
        $this->db->from('general.finca s');
        $this->db->where('s.estado', ESTADO_ACTIVO);
        $arrayfinca = explode(",", $finca);
        if (!in_array(FINCA_ROSAHOLICS_ID, $arrayfinca)) {
            if (count($arrayfinca) > 1) {
                $srt = "s.id in (" . $finca . ")";
                $this->db->where($srt);
            }
        }
        if ($id) {
            $this->db->where('id', $id);

            $this->db->order_by('s.nombre', 'ASC');
            return $this->retornarUno();
        } else {
            $this->db->order_by('s.nombre', 'ASC');
            return $this->retornarMuchosSinPaginacion();
        }
    }

    public function obtenerFincaSel() {

        $fincaSelect = $this->obtenerFincaSelect();
        return $this->retornarSel($fincaSelect, "nombre");
    }

    public function obtenerEstadosOrden($tipo = 1) {
        $respuesta = false;
        switch ($tipo) {
            case 1:
                $respuesta = array(
                    array('id' => 'T', 'nombre' => 'Todos'),
                    array('id' => 'A', 'nombre' => 'Activo'),
                    array('id' => 'E', 'nombre' => 'Error'),
                    array('id' => 'C', 'nombre' => 'Cancelado'));
                break;
            default:
                break;
        }
        return (!$respuesta ? $respuesta : json_decode(json_encode($respuesta), FALSE));
    }

    public function obtenerSelEstadoOrden() {
        $fincaSelect = $this->obtenerEstadosOrden(1);
        return $this->retornarSel($fincaSelect, "nombre");
    }

}
