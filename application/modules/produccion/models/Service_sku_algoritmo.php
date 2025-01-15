<?php

class Service_sku_algoritmo extends My_Model {

    public function obtenerNuevoProducto() {
        return (object) [
                    'titulo' => '',
                    'descripcion' => '',
                    'sku_prefijo' => '',
                    'tags' => '',
                    'estado' => ESTADO_ACTIVO,
        ];
    }

    public function obtenerParametro() {
        return (object) [
                    'sku_algoritmo_id' => '',
        ];
    }

    public function existeProducto($objProducto) {
        $this->db->select('o.*');
        $this->db->from('ecommerce.producto o');
        $this->db->where('sku_prefijo', $objProducto['sku_prefijo']);

        return $this->retornarUno();
    }

    public function existeAlgoritmo($sku) {
        $this->db->select('*');
        $this->db->from('ecommerce.sku_algoritmo');
        $this->db->where('sku', $sku);
        $this->db->where('estado', ESTADO_ACTIVO);
        return $this->retornarUno();
    }

    public function obtenerAlgoritmoDetalle($sku_id) {
        $this->db->select('*');
        $this->db->from('ecommerce.sku_algoritmo_detalle');
        $this->db->where('sku_algoritmo_id', $sku_id);
        $this->db->where('estado', ESTADO_ACTIVO);
        return $this->retornarMuchos();
    }

    public function obtenerProducto($id = false, $estado = false, $texto_busqueda = false) {
        $this->db->select('p.*');
        $this->db->from('ecommerce.producto p');
        //$this->db->join('ecommerce.producto_variante pv', 'pv.producto_id = p.id', 'left');

        if ($id) {
            $this->db->where('p.id', $id);
            return $this->retornarUno();
        }
        if ($estado) {
            $this->db->where('p.estado', $estado);
        }
        if ($texto_busqueda) {
            $this->db->where(" (UPPER(p.titulo) LIKE '%" . strtoupper($texto_busqueda) . "%' "
                    . "OR UPPER(p.descripcion) LIKE '%" . strtoupper($texto_busqueda) . "%' "
                    . "OR UPPER(p.sku_prefijo) LIKE '%" . strtoupper($texto_busqueda) . "%')");
        }
        $this->db->order_by('p.titulo', 'ASC');

        $conteo = $this->retornarConteo();
        $arr = $this->retornarMuchos();
        $variantes = $this->obtenerProductoVariante();

        return array($arr, $conteo);
    }

    public function obtenerProductos($estado = false) {
        $this->db->select('o.*');
        $this->db->from('ecommerce.producto o');
        if ($estado) {
            $this->db->where('estado', $estado);
        }
        $conteo = $this->retornarConteo();
        $arr = $this->retornarMuchos();

        return array($arr, $conteo);
    }

    public function obtenerProductoVariante($id = false, $estado = false, $texto_busqueda = false) {
        $this->db->select('pv.*');
        $this->db->from('ecommerce.producto_variante pv');

        if ($id) {
            $this->db->where('pv.id', $id);
            return $this->retornarUno();
        }

        if ($estado) {
            $this->db->where('pv.estado', $estado);
        }
        if ($texto_busqueda) {
            $this->db->where(" (UPPER(pv.titulo) LIKE '%" . strtoupper($texto_busqueda) . "%' "
                    . "OR UPPER(pv.sku) LIKE '%" . strtoupper($texto_busqueda) . "%')");
        }
        $this->db->order_by('pv.titulo', 'ASC');

        $conteo = $this->retornarConteo();
        $arr = $this->retornarMuchos();

        return array($arr, $conteo);
    }

    public function crearParametros($data, $masivo = false) {

        if ($masivo) {
            $data['sku_algoritmo_id'] = 'porcentaje';
//            $data['sku'] = $data[];
            $data['finca_id'] = [2];
            $data['diario'] = [0];
            $data['porcentaje'] = [100];
        }
        $nombre_parametro = 'cupo_max_diario';
        $sku_algoritmo = array(
            "sku" => $data['sku'],
            "tipo_algoritmo" => $data['sku_algoritmo_id'],
            "estado" => ESTADO_ACTIVO,
        );

        $id = $this->ingresar("ecommerce.sku_algoritmo", $sku_algoritmo, true, true);

        if ($id) {
            $dato_log = array(
                "sku_algoritmo_id" => $id,
                "accion" => "creacion de sku_algoritmo" . json_encode($sku_algoritmo),
            );
            $this->registrarLog("ecommerce.sku_algoritmo_log", $dato_log);
        }

        //si es algoritmo diario
        if ($data['sku_algoritmo_id'] == 'diario') {
            $nombre_parametro = ($data['sku_algoritmo_id'] == 'diario') ? 'cupo_max_diario' : 'cupo_max_semanal';

            foreach ($data['finca_id'] as $k => $finca) {
                $sku_algoritmo_detalle = array(
                    "sku_algoritmo_id" => $id,
                    "finca_id" => $finca,
                    "nombre_parametro" => $nombre_parametro,
                    "valor" => $data['diario'][$k],
                    "estado" => ESTADO_ACTIVO,
                );

                $id_det = $this->ingresar("ecommerce.sku_algoritmo_detalle", $sku_algoritmo_detalle, true, true);
                if ($id_det) {
                    $dato_log = array(
                        "sku_algoritmo_detalle_id" => $id_det,
                        "accion" => "creacion de sku_algoritmo_detalle" . json_encode($sku_algoritmo_detalle),
                    );

                    $this->registrarLog("ecommerce.sku_algoritmo_detalle_log", $dato_log);
                }
            }
        } else {

            //si es algoritmo semanal
            if ($data['sku_algoritmo_id'] == 'semanal') {
                $nombre_parametro = 'semanal';
                foreach ($data['finca_id'] as $k => $finca) {
                    $sku_algoritmo_detalle = array(
                        "sku_algoritmo_id" => $id,
                        "finca_id" => $finca,
                        "nombre_parametro" => $nombre_parametro,
                        "valor" => $data['semanal'][$k],
                        "estado" => ESTADO_ACTIVO,
                    );
                    $id_det = $this->ingresar("ecommerce.sku_algoritmo_detalle", $sku_algoritmo_detalle, true, true);
                    if ($id_det) {
                        $dato_log = array(
                            "sku_algoritmo_detalle_id" => $id_det,
                            "accion" => "creacion de sku_algoritmo_detalle" . json_encode($sku_algoritmo_detalle),
                        );

                        $this->registrarLog("ecommerce.sku_algoritmo_detalle_log", $dato_log);
                    }
                }

                $nombre_parametro = 'diario';
                foreach ($data['finca_id'] as $k => $finca) {
                    $sku_algoritmo_detalle = array(
                        "sku_algoritmo_id" => $id,
                        "finca_id" => $finca,
                        "nombre_parametro" => $nombre_parametro,
                        "valor" => $data['diario'][$k],
                        "estado" => ESTADO_ACTIVO,
                    );
                    $id_det = $this->ingresar("ecommerce.sku_algoritmo_detalle", $sku_algoritmo_detalle, true, true);
                    if ($id_det) {
                        $dato_log = array(
                            "sku_algoritmo_detalle_id" => $id_det,
                            "accion" => "creacion de sku_algoritmo_detalle" . json_encode($sku_algoritmo_detalle),
                        );

                        $this->registrarLog("ecommerce.sku_algoritmo_detalle_log", $dato_log);
                    }
                }
            } else {

                foreach ($data['finca_id'] as $k => $finca) {
                    $sku_algoritmo_detalle = array(
                        "sku_algoritmo_id" => $id,
                        "finca_id" => $finca,
                        "nombre_parametro" => $nombre_parametro,
                        "valor" => $data['diario'][$k],
                        "estado" => ESTADO_ACTIVO,
                    );

                    $id_det = $this->ingresar("ecommerce.sku_algoritmo_detalle", $sku_algoritmo_detalle, true, true);
                    if ($id_det) {
                        $dato_log = array(
                            "sku_algoritmo_detalle_id" => $id_det,
                            "accion" => "creacion de sku_algoritmo_detalle" . json_encode($sku_algoritmo_detalle),
                        );

                        $this->registrarLog("ecommerce.sku_algoritmo_detalle_log", $dato_log);
                    }
                }

                $nombre_parametro = 'porcentaje';
                foreach ($data['finca_id'] as $k => $finca) {
                    $sku_algoritmo_detalle = array(
                        "sku_algoritmo_id" => $id,
                        "finca_id" => $finca,
                        "nombre_parametro" => $nombre_parametro,
                        "valor" => $data['porcentaje'][$k],
                        "estado" => ESTADO_ACTIVO,
                    );

                    $id_det = $this->ingresar("ecommerce.sku_algoritmo_detalle", $sku_algoritmo_detalle, true, true);
                    if ($id_det) {
                        $dato_log = array(
                            "sku_algoritmo_detalle_id" => $id_det,
                            "accion" => "creacion de sku_algoritmo_detalle" . json_encode($sku_algoritmo_detalle),
                        );

                        $this->registrarLog("ecommerce.sku_algoritmo_detalle_log", $dato_log);
                    }
                }
            }
        }
        return $id;
    }

    public function inhabilitarParametros($algoritmoasignado) {
        $id = $this->actualizar("ecommerce.sku_algoritmo", $algoritmoasignado, "id", true);
        $algoritmo_detalle = $this->obtenerAlgoritmoDetalle($algoritmoasignado['id']);
        if ($algoritmo_detalle) {
            foreach ($algoritmo_detalle as $obj) {
                $detallealgoritmo = array(
                    "id" => $obj->id,
                    "estado" => ESTADO_INACTIVO,
                );
                $this->actualizar("ecommerce.sku_algoritmo_detalle", $detallealgoritmo, false, true);
            }
        }
        return $id;
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function getPorcentajes($sku) {
        $parametros = $this->service_ecommerce_orden->v_parametros_sku_algoritmo($sku);
        if (!$parametros) {
            return false;
        }
        $porcentajes = array();
        foreach ($parametros as $key => $parametro) {

            $porcentajes[$parametro->finca_id][$parametro->nombre_parametro] = $parametro->valor;
            $porcentajes[$parametro->finca_id]['finca_id'] = $parametro->finca_id;
        }



        return $porcentajes;
    }

    public function getAlgoritmoSku($sku) {
        $parametros = $this->service_ecommerce_orden->obtenerAlgoritmoSku($sku);
        if ($parametros) {
            return $parametros->tipo_algoritmo;
        }
        return false;
    }

    public function fincaDespachoOrden($orden_id) {
        //obtener la orden
        $finca_no_encontrada = false;
        $tipo_caja_id = 10;
        $totalBase = 10; //valor inicial base con el que se inicia
        $orden = $this->service_orden->obtenerOrden($orden_id);
        $orden_cajas = $this->service_ecommerce_logistica->obtenerOrdenCajas($orden_id);
        $fecha_carguera = $orden->fecha_carguera;

        $caja_finca_despacho = array();

//        echo json_encode($diaSemana);

        foreach ($orden_cajas as $k => $caja) {
            $finca_despacho_items_caja = array();
            $caja_items = $this->service_ecommerce_logistica->obtenerOrdenCajaItems($caja->id);
            $finca_despacho_caja = false;
            foreach ($caja_items as $k => $item) {
                $sku = $item->info_variante_sku;

                $total_productos_dia = $this->service_ecommerce_orden->v_total_dia_sku($sku, $fecha_carguera);
                $total_productos_dia = $total_productos_dia - $item->cantidad;
                $algoritmo = $this->getAlgoritmoSku($sku);

                $item_finca_despacho_id = FINCA_ROSAHOLICS_ID;
                switch ($algoritmo) {
                    case 'porcentaje':
                        $item_finca_despacho_id = $this->algoritmoPorcentajes($sku, $total_productos_dia, $totalBase, $algoritmo, $fecha_carguera, $item);
                        break;
                    case 'diario':
                        $item_finca_despacho_id = $this->fincaDespachoGeneral($orden_id, $data_items, $algoritmo);
                        break;

                    case 'semanal':
                        if ($fecha_carguera != null) {
                            $diaSemana = $this->diaSemana($fecha_carguera);
                            $item_finca_despacho_id = $this->fincaDespachoSemanal($orden_id, $fecha_carguera, $diaSemana, $data_items, $sku, $algoritmo);
                        }
                        break;

                    default:
                        break;
                }
                if (!$finca_despacho_caja) {
                    $finca_despacho_caja = $item_finca_despacho_id;
                }
                if ($finca_despacho_caja !== $item_finca_despacho_id) {
                    $finca_despacho_caja = FINCA_ROSAHOLICS_ID;
                }
                $finca_despacho_items_caja[$item->id] = $item_finca_despacho_id;
                $items_asignacion_finca[$item->id] = $item_finca_despacho_id;
            }
            $caja_finca_despacho[$caja->id] = $finca_despacho_caja;
        }

        foreach ($caja_finca_despacho as $orden_caja_id => $finca_id) {
            if ($finca_id == false) {
                $finca_id = 1;
            }
            if ($finca_id == FINCA_ROSAHOLICS_ID) {
                $finca_no_encontrada = true;
            }
//            $orden_caja_id = $this->service_ecommerce_logistica->crearOrdenCaja($orden_id, $tipo_caja_id);
            //usar el metodo meterItemEnCaja
//            $this->service_ecommerce_logistica->meterItemEnCaja($orden_item_id, $orden_caja_id);
            //asignamos la finca finca_caja
            $dataFinca = array("orden_caja_id" => $orden_caja_id, "finca_id" => $finca_id, "estado" => ESTADO_ACTIVO);
            $this->service_ecommerce_orden->asignoFincaCaja($dataFinca);
        }
        return $finca_no_encontrada;

//        echo json_encode($items_asignacion_finca);
    }

    public function fincaDespachoOrdenOld($orden_id) {
        //obtener la orden
        $tipo_caja_id = 10;
        $totalBase = 10; //valor inicial base con el que se inicia
        $orden = $this->service_orden->obtenerOrden($orden_id);
        $orden_items = $this->service_ecommerce->obtenerOrdenItems($orden_id);
        $fecha_carguera = $orden->fecha_carguera;
        $items_asignacion_finca = array();
        $diaSemana = $this->diaSemana($fecha_carguera);
        echo json_encode($diaSemana);

        foreach ($orden_items as $k => $item) {
            print_r($item);

            $data_items = (array("item_id" => $item->id, "info_sku" => $item->info_variante_sku, "item_cantidad" => $item->cantidad));
            $this->service_ecommerce_logistica->sacarItemDeCaja($item->id);
            $sku = $item->info_variante_sku;

            $total_productos_dia = $this->service_ecommerce_orden->v_total_dia_sku($sku, $fecha_carguera);

            $algoritmo = $this->getAlgoritmoSku($sku);

            switch ($algoritmo) {
                case 'porcentaje':
                    $items_asignacion_finca[$item->id] = $this->algoritmoPorcentajes($sku, $total_productos_dia, $totalBase, $algoritmo, $fecha_carguera, $item);
                    break;
                case 'diario':
                    $items_asignacion_finca[$item->id] = $this->fincaDespachoGeneral($orden_id, $data_items, $algoritmo);
                    break;

                case 'semanal':
                    $items_asignacion_finca[$item->id] = $this->fincaDespachoSemanal($orden_id, $fecha_carguera, $diaSemana, $data_items, $sku, $algoritmo);
                    break;

                default:
                    $items_asignacion_finca[$item->id] = FINCA_ROSAHOLICS_ID;
                    break;
            }
        }
        foreach ($items_asignacion_finca as $orden_item_id => $finca_id) {
            if ($finca_id == false) {
                $finca_id = 1;
            }
            $orden_caja_id = $this->service_ecommerce_logistica->crearOrdenCaja($orden_id, $tipo_caja_id);
            //usar el metodo meterItemEnCaja
            $this->service_ecommerce_logistica->meterItemEnCaja($orden_item_id, $orden_caja_id);
            //asignamos la finca finca_caja
            $dataFinca = array("orden_caja_id" => $orden_caja_id, "finca_id" => $finca_id, "estado" => ESTADO_ACTIVO);
            $this->service_ecommerce_orden->asignoFincaCaja($dataFinca);
        }

        echo json_encode($items_asignacion_finca);
    }

    public function algoritmoPorcentajes($sku, $total_productos_dia, $totalBase, $algoritmo, $fecha_carguera, $item) {
        //porcentaje por finca para ese sku por finca 
        $porcentajes = $this->getPorcentajes($sku); //--
        if (!$porcentajes) {
            return false;
        }
        $porcentaje_min = $this->encontrarPorcentajeMinimo($porcentajes); //--
        $porcentajes = $this->ordenarPorcentajesDesc($porcentajes);
        $totalBase = $this->totalBase($totalBase, $porcentaje_min); //--
        $total_prod_base = 0;
        foreach ($porcentajes as $finca_id => $porcentaje) { //--
            $cantidad_asignadoF = ceil($this->porcentaje($porcentaje['porcentaje'], $totalBase)); //--
//            echo '<br>';
//            echo '<br>';
//            echo "SKU: " . $sku;
//            echo '<br>';
//            echo "Tipo Algoritmo: " . $algoritmo;
//            echo '<br>';
//            echo '<br>';
//            echo "Id Finca: " . $porcentaje['finca_id'];
//            echo '<br>';
//            echo '<br>';
//            echo "Porcentaje: " . $porcentaje['porcentaje'];
//            echo '<br>';
//            echo '<br>';
//            echo "cupo diario: " . $porcentaje['cupo_max_diario'];
//            echo '<br>';
            // echo json_encode($cantidad_asignadoF);
            $porcentajes[$finca_id]['maximo_base'] = $cantidad_asignadoF; //--
            $total_prod_base += $cantidad_asignadoF; //--
        }
//        echo '<br>';
//        echo "Total de general de productos: " . $total_productos_dia;
//        echo '<br>';
        //saber la iteracion en la que estoy  
        $iteracion = ceil(($total_productos_dia + $item->cantidad) / $total_prod_base); //--
        foreach ($porcentajes as $finca_id => $porcentaje) {
            $cuantos_productos = $this->service_ecommerce_orden->v_total_dia_sku_finca($sku, $fecha_carguera, $porcentaje['finca_id']);
            $cantidad_maxima_iteracion = $porcentaje['maximo_base'] * $iteracion;
            if ($cuantos_productos + $item->cantidad <= $cantidad_maxima_iteracion && ($porcentaje['cupo_max_diario'] == 0 ||
                    ($cuantos_productos + $item->cantidad <= $porcentaje['cupo_max_diario']))
            ) {
                $items_asignacion_finca[$item->id] = $porcentaje['finca_id'];
                break;
            } else {
                $items_asignacion_finca[$item->id] = false;
            }
        }
        return $items_asignacion_finca[$item->id];
//        echo '<br>';
//        echo "Cantidad de productos por dia en finca: " . $cuantos_productos;
//        echo '<br>';
    }

    //despacho de ordenes con sus items de forma general

    public function fincaDespachoGeneral($orden_id, $data_items, $algoritmo) {
        $orden = $this->service_orden->obtenerOrden($orden_id);
        $orden_items = $this->service_ecommerce->obtenerOrdenItems($orden_id);
        $fecha_carguera = $orden->fecha_carguera;
        $items_asignacion_finca = array();
//        print_r($data_items);
        $this->service_ecommerce_logistica->sacarItemDeCaja($data_items['item_id']);
        $sku = $data_items['info_sku'];

        $total_productos_dia = $this->service_ecommerce_orden->v_total_dia_sku($sku, $fecha_carguera);

        $cupos_finca = $this->getPorcentajes($sku); //--

        $cupos_finca = $this->ordenarCupoDiarioDesc($cupos_finca);

        if (!$cupos_finca) {
            return false;
        }

        $porcentajes = $this->service_ecommerce_orden->v_parametros_sku_algoritmo($sku);
        $total_prod_base = 0;

        foreach ($cupos_finca as $finca_id => $cupos_dia) { //--
//            echo '<br>';
//            echo '<br>';
//            echo "SKU: " . $sku;
//            echo '<br>';
//            echo "Tipo Algoritmo: " . $algoritmo;
//            echo '<br>';
//            echo '<br>';
//            echo "Id Finca: " . $finca_id;
//            echo '<br>';
//
//            echo '<br>';
//            echo "cupo diario: " . $cupos_dia['cupo_max_diario'];
//            echo '<br>';
            $cuantos_productos = $this->service_ecommerce_orden->v_total_dia_sku_finca($sku, $fecha_carguera, $finca_id);

            if (
                    $cuantos_productos + $data_items['item_cantidad'] <= $cupos_dia['cupo_max_diario'] && ($cupos_dia['cupo_max_diario'] == 0 ||
                    ($cuantos_productos + $data_items['item_cantidad'] <= $cupos_dia['cupo_max_diario']))
            ) {
                $items_asignacion_finca[$data_items['item_id']] = $finca_id;
                break;
            } else {
                $items_asignacion_finca[$data_items['item_id']] = false;
            }

//            echo '<br>';
//            echo "Cantidad de productos por dia en finca: " . $cuantos_productos;
//            echo '<br>';
        }

//        echo '<br>';
//        echo "Total de general de productos: " . $total_productos_dia;
//        echo '<br>';

        return $items_asignacion_finca[$data_items['item_id']];
//        echo json_encode($items_asignacion_finca);
    }

    //despacho de ordenes semanales
    public function fincaDespachoSemanal($orden_id, $fecha_carguera, $diaSemana, $data_items, $sku, $algoritmo) {

        $orden = $this->service_orden->obtenerOrden($orden_id);
        $orden_items = $this->service_ecommerce->obtenerOrdenItems($orden_id);
        $fecha_carguera = $orden->fecha_carguera;
        $items_asignacion_finca = array();

        $cupos_diarios = $this->getPorcentajes($sku); //--
        $cupos_diarios = $this->ordenarCupoDiarioDesc($cupos_diarios);
        if (!$cupos_diarios) {
            return false;
        }
        print_r($data_items);

        $this->service_ecommerce_logistica->sacarItemDeCaja($data_items['item_id']);
        $sku = $data_items['info_sku'];

        $total_productos_dia = $this->service_ecommerce_orden->v_total_dia_sku($sku, $fecha_carguera);

        $porcentajes = $this->service_ecommerce_orden->v_parametros_sku_algoritmo($sku); //--
        $total_prod_base = 0;

        foreach ($cupos_diarios as $finca_id => $cupos_dia) { //--
//            echo '<br>';
//            echo '<br>';
//            echo "SKU: " . $sku;
//            echo '<br>';
//            echo "Tipo Algoritmo: " . $algoritmo;
//            echo '<br>';
//            echo '<br>';
//            echo "Id Finca: " . $finca_id;
//            echo '<br>';
//            echo "Porcentaje: " . $cupos_dia['cupo_max_semanal'];
//            echo '<br>';
//            echo '<br>';
//            echo "cupo diario: " . $cupos_dia['cupo_max_diario'];
//            echo '<br>';
            $cuantos_productos = $this->service_ecommerce_orden->v_total_semanal_sku_finca($sku, $diaSemana['ultimo_dia'], $finca_id, $diaSemana['primer_dia']);
//            print_r("Cuantos productos de despacho tiene: " . $cuantos_productos);

            $cupo_semanal = $this->service_ecommerce_orden->cupo_semanal($diaSemana['primer_dia'], $diaSemana['ultimo_dia']);

            if ($cuantos_productos + $data_items['item_cantidad'] <= $cupos_dia['cupo_max_semanal'] && ($cupos_dia['cupo_max_diario'] == 0 ||
                    ($cuantos_productos + $data_items['item_cantidad'] <= $cupos_dia['cupo_max_diario']))
            ) {
                $items_asignacion_finca[$data_items['item_id']] = $finca_id;
                break;
            } else {
                $items_asignacion_finca[$data_items['item_id']] = false;
            }


//            echo '<br>';
//            echo "Cantidad de productos por dia en finca: " . $cuantos_productos;
//            echo '<br>';
        }

//        echo '<br>';
//        echo "Total de general de productos: " . $total_productos_dia;
//        echo '<br>';

        return $items_asignacion_finca[$data_items['item_id']];
//        echo json_encode($items_asignacion_finca);
    }

    //funcion para obtener el dia de la semana inicio y fin
    public function diaSemana($fecha_carguera) {

        $arrRango = explode(" - ", $fecha_carguera);
        $year = date_format(DateTime::createFromFormat(FORMATO_FECHA, convertirFechaBD($arrRango[0])), 'Y');
        $month = date_format(DateTime::createFromFormat(FORMATO_FECHA, convertirFechaBD($arrRango[0])), 'm');
        $day = date_format(DateTime::createFromFormat(FORMATO_FECHA, convertirFechaBD($arrRango[0])), 'd');
        $fecha_carguera = date_format(DateTime::createFromFormat(FORMATO_FECHA, convertirFechaBD($arrRango[0])), 'Y-m-d');
        # Obtenemos el numero de la semana
        $semana = date("W");

        # Obtenemos el día de la semana de la fecha dada
        $diaSemana = date("w", mktime(0, 0, 0, $month, $day, $year));

        # el 0 equivale al domingo...
        if ($diaSemana == 0)
            $diaSemana = 7;

        # A la fecha recibida, le restamos el dia de la semana y obtendremos el lunes
        $primerDia = date("Y-m-d", mktime(0, 0, 0, $month, $day - $diaSemana + 1, $year));
        # A la fecha recibida, le sumamos el dia de la semana menos siete y obtendremos el domingo
        $ultimoDia = date("Y-m-d", mktime(0, 0, 0, $month, $day + (7 - $diaSemana), $year));
        $dias = array("primer_dia" => $primerDia, "ultimo_dia" => $ultimoDia);
        return $dias;
    }

    public function numeroMaximo($total_prod_dia, $totalBase) {
        return ($total_prod_dia / $totalBase);
    }

//    public function ordenesFinca($orden_id = false) {
//
//        //traer la lista de fincas
//
//        $data['url_busqueda'] = "ecommerce/ordenesFinca/'$orden_id'";
//
//        $sku = 'AGR_PT_PSZ_012_40';
//        $data['url_busqueda'] = "ecommerce/ordenesFinca/'$orden_id'";
//        $datos = $this->fincaDespachoOrden($orden_id);
//
////        echo '<br>';
////        echo '</br>';
//        exit;
//        $this->mostrarVista('ordenesFinca.php', $data);
//    }
    //funcion para calcular el valor base que vamos a empezar
    function totalBase($totalBase, $porcentaje_min) {
        return ($totalBase) / ($totalBase * ($porcentaje_min / 100));
    }

    //funcion para ordenar el array
    function encontrarPorcentajeMinimo($porcentajes) {
        $porcentajes_ordenados = array();
        foreach ($porcentajes as $k => $porcentaje) {
            array_push($porcentajes_ordenados, $porcentaje['porcentaje']);
        }

        return min($porcentajes_ordenados);
    }

    function ordenarCupoDiarioDesc($porcentajes) {
        arsort($porcentajes);
        echo json_encode($porcentajes);
        return $porcentajes;
    }

    public function ordenarPorcentajesDesc($porcentajes) {

        foreach ($porcentajes as $key => $row) {
            $cupo_max_diario[$key] = $row['cupo_max_diario'];
            $porcentaje[$key] = $row['porcentaje'];
            $finca_id[$key] = $porcentajes[$key];
        }

        $cupo_max_diario = array_column($porcentajes, 'cupo_max_diario');
        $porcentaje = array_column($porcentajes, 'porcentaje');
        $finca_id = array_column($porcentajes, 'finca_id');
        array_multisort($porcentaje, SORT_DESC, $porcentajes);


        return $porcentajes;
    }

    //funcion para determinar el porcentaje
    function porcentaje($porcentajes, $cant_ordenes) {
        $porcentajeRet = array();

        if ($cant_ordenes != 0) {
            if (is_array($porcentajes)) {

                foreach ($porcentajes as $k => $porcentaje) {

                    array_push($porcentajeRet, ($cant_ordenes * $porcentaje->porciento) / 100);
                }
                return ($porcentajeRet);
            } else {

                return ($cant_ordenes * $porcentajes) / 100;
            }
        }
    }

}
