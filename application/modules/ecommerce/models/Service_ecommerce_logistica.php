<?php

class Service_ecommerce_logistica extends My_Model {

    public function obtenerTiposCaja($tipo_caja_id = false) {
        $this->db->from('ecommerce.tipo_caja tc');
        if ($tipo_caja_id) {
            $this->db->where('tc.id', $tipo_caja_id);
            return $this->retornarUno();
        }
        $this->db->where('tc.estado', ESTADO_ACTIVO);
        return $this->retornarMuchosSinPaginacion();
    }

    public function crearOrdenCaja($orden_id, $tipo_caja_id) {
        $datos['orden_id'] = $orden_id;
        $datos['tipo_caja_id'] = $tipo_caja_id;
        $datos['estado'] = ESTADO_ACTIVO;
        $orden_caja_id = $this->ingresar("ecommerce.orden_caja", $datos, true, true);

        $this->logisticaCajaModificada($orden_id, false, "Caja creada " . $orden_caja_id . " ");
        return $orden_caja_id;
    }

    public function desempacarCaja($orden_caja_id) {
        $caja_items = $this->obtenerOrdenCajaItems($orden_caja_id);
        if ($caja_items) {
            foreach ($caja_items as $caja_item) {
                $this->sacarItemDeCaja($caja_item->id);
            }
        }
        $this->verificarCajaEstado($orden_caja_id);
    }

    public function empaqueAutomaticoOrden($orden_id) {
        //obtengo todas las cajas que tenga esa orden
        $cajas = $this->obtenerOrdenCajas($orden_id);
        if ($cajas) {
            foreach ($cajas as $caja) {
                $this->desempacarCaja($caja->id);
            }
        }
        //elimino


        $cajas_correctas = $this->ordenMeterItemsEnCaja($orden_id, true);
//        if ($cajas_correctas)
        //despues de meter items a una caja
        //asignamos cada caja a una finca de despacho
        $fincas_no_encontradas = $this->service_sku_algoritmo->fincaDespachoOrden($orden_id);

        if ($cajas_correctas && !$fincas_no_encontradas) {
            return true;
        }
        return false;
    }

    //Creacion de Finca_orden
    public function crearFincaOrdenCaja($orden_caja_id, $finca_id) {
        $datos['finca_id'] = $finca_id;
        $datos['orden_caja_id'] = $orden_caja_id;
        $datos['estado'] = ESTADO_ACTIVO;
        $finca_caja_id = $this->ingresar("ecommerce.finca_caja", $datos, true);
        if ($finca_caja_id) {
            $dato_log = array(
                "finca_caja_id" => $finca_caja_id,
                "accion" => "creacion de finca_caja" . json_encode($datos),
            );
            $this->registrarLog("ecommerce.finca_caja_log", $dato_log);
        }
        return $finca_caja_id;
    }

    //
    public function obtenerFincaOrdenCaja($orden_caja_id) {
        $this->db->select('fc.id');
        $this->db->from('ecommerce.finca_caja fc');
        $this->db->where('fc.orden_caja_id', $orden_caja_id);
        return $this->retornarUno();
    }

    //actualizar a inactivo cuando la caja relacionada pase a inactiva
    public function actualizarFincaOrdenCaja($orden_caja_id, $finca_id = false, $tipo_caja = false) {

        $fincaordencaja = $this->obtenerFincaOrdenCaja($orden_caja_id);
        //TODO este caso no deberia presentarse, ejecutar script de asignacion masiva antes de puesta en produccion
        if (!$fincaordencaja) {
            if (!$finca_id) {
                $finca_id = FINCA_ROSAHOLICS_ID;
            }
            $this->crearFincaOrdenCaja($orden_caja_id, $finca_id);
            $fincaordencaja = $this->obtenerFincaOrdenCaja($orden_caja_id);
        }
        $datos['id'] = $fincaordencaja->id;
        if ($finca_id) {
            $datos['estado'] = ESTADO_ACTIVO;
            $datos['finca_id'] = $finca_id;
        } else {
            $datos['estado'] = ESTADO_INACTIVO;
        }
        $data = 'actualización de tipo caja y finca, id de la caja: ' . $orden_caja_id . ' Id Tipo de caja ' . $tipo_caja . ' id de la finca: ' . $finca_id;
        $id = $this->actualizar("ecommerce.finca_caja", $datos, "id", false);
        if ($fincaordencaja->id) {
            $dato_log = array(
                "finca_caja_id" => $fincaordencaja->id,
                "accion" => "actualización de finca y caja" . json_encode($datos) . $data,
            );
            $this->registrarLog("ecommerce.finca_caja_log", $dato_log);
        }

        return $id;
    }

    public function actualizarOrdenCaja($orden_caja_id, $tipo_caja_id = false, $estado = false) {
        $datos['id'] = $orden_caja_id;
        if ($tipo_caja_id) {
            $datos['tipo_caja_id'] = $tipo_caja_id;
        }
        if ($estado) {
            $datos['estado'] = $estado;
        }
        $orden_caja = $this->obtenerOrdenCaja($orden_caja_id);
        $this->logisticaCajaModificada($orden_caja->orden_id, false, "Caja actualizada " . $orden_caja_id . " datos " . json_encode($datos));
        return $this->actualizar("ecommerce.orden_caja", $datos, "id", true);
    }

    public function obtenerOrdenCaja($orden_caja_id) {
        $this->db->from('ecommerce.orden_caja oc');
        $this->db->where('oc.id', $orden_caja_id);
        return $this->retornarUno();
    }

    public function logisticaCajaModificada($orden_id, $orden_item_id, $accion) {
        /*         * ** por ahora vamos a mandar a cero el contador de impresiones de tarjeta de una orden **** */
        //TODO wsanchez REMOVER
        //buscamos la orden a la que pertenece esa caja
        if (!$orden_id) {
            $orden_item = $this->service_ecommerce->obtenerOrdenItem($orden_item_id);
            if (!$orden_item) {
                $orden_id = 0;
            } else {
                $orden_id = $orden_item->orden_id;
            }
        }
        $actOrden['id'] = $orden_id;
        $actOrden['impresiones'] = 0;
        $this->actualizar("ecommerce.orden", $actOrden, false);

        $dato_log['orden_id'] = $orden_id;
        $dato_log['accion'] = $accion;

        $this->registrarLog("ecommerce.orden_log", $dato_log);

        //////////////////////////////////////////////////
    }

    public function meterItemEnCaja($orden_item_id, $orden_caja_id) {
        $this->logisticaCajaModificada(false, $orden_item_id, "Item " . $orden_item_id . " ingresado a caja " . $orden_caja_id);

        $datos['orden_item_id'] = $orden_item_id;
        $datos['orden_caja_id'] = $orden_caja_id;
        $datos['estado'] = ESTADO_ACTIVO;
        return $this->ingresar("ecommerce.orden_caja_item", $datos, true, true);
    }

    public function sacarItemDeCaja($orden_item_id) {
        $actualizado = false;
        $datos['orden_item_id'] = $orden_item_id;
        $orden_item = $this->service_ecommerce->obtenerOrdenItem($orden_item_id);

        if ($orden_item) {
            $datos['estado'] = ESTADO_INACTIVO;
            $actualizado = $this->actualizar("ecommerce.orden_caja_item", $datos, "orden_item_id", true);

            if ($actualizado) {
                $this->logisticaCajaModificada($orden_item->orden_id, $orden_item_id, "Item " . $orden_item_id . " sacado de caja " . $orden_item->orden_caja_id);
                $orden_caja_items = $this->obtenerOrdenCajaItems($orden_item->orden_caja_id);
                if (!$orden_caja_items) {
                    $this->logisticaCajaModificada($orden_item->orden_id, false, "Caja vacia, inactivamos caja " . $orden_item->orden_caja_id . " ");
                    $this->service_ecommerce_logistica->actualizarOrdenCaja($orden_item->orden_caja_id, false, ESTADO_INACTIVO);
                    //aqui donde se inactiva la caja, tambien se incativa la finca
                    $this->actualizarFincaOrdenCaja($orden_item->orden_caja_id);
                }
            }
        }


        return $actualizado;
    }

    public function verificarCajaEstado($orden_caja_id) {
        $orden_caja_items = $this->obtenerOrdenCajaItems($orden_caja_id);
        $orden_caja = $this->service_logistica->obtenerOrdenCaja($orden_caja_id);
        if (!$orden_caja_items) {
            $this->logisticaCajaModificada($orden_caja->info_orden_id, false, "Caja vacia, inactivamos caja " . $orden_caja_id . " ");
            $this->service_ecommerce_logistica->actualizarOrdenCaja($orden_caja_id, false, ESTADO_INACTIVO);
            //aqui donde se inactiva la caja, tambien se incativa la finca
            $this->actualizarFincaOrdenCaja($orden_caja_id);
        }
    }

    public function obtenerOrdenCajas($orden_id, $estado = ESTADO_ACTIVO, $caja_id = false, $session_finca = false, $finca_id = false) {
        $this->db->select('oc.*, tc.nombre as info_nombre_caja, tc.id as info_tipo_caja_id, tc.abreviado as info_abreviado_caja, f.nombre as info_finca_caja, f.id as info_finca_id');
        $this->db->from('ecommerce.orden_caja oc');
        $this->db->join('ecommerce.tipo_caja tc', 'oc.tipo_caja_id = tc.id', 'left');
        $this->db->join('ecommerce.finca_caja fc', 'oc.id = fc.orden_caja_id', 'left');
        $this->db->join('general.finca f', 'fc.finca_id = f.id', 'left');
        $this->db->where('oc.orden_id', $orden_id);
        $this->db->where('oc.estado', $estado);

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
        if ($caja_id) {
            $this->db->where('oc.id', $caja_id);
        }
        $this->db->order_by('oc.id', 'ASC');
        return $this->retornarMuchosSinPaginacion();
    }

    public function obtenerOrdenItemCaja($orden_item_id) {
        $this->db->select('oc.id, oc.tipo_caja_id, tc.nombre as info_nombre_caja, oc.empacada ');
        $this->db->from('ecommerce.orden_caja_item oci');
        $this->db->join('ecommerce.orden_caja oc', 'oc.id = oci.orden_caja_id', 'left');
        $this->db->join('ecommerce.tipo_caja tc', 'oc.tipo_caja_id = tc.id', 'left');
        $this->db->where('oci.orden_item_id', $orden_item_id);
        $this->db->where('oci.estado', ESTADO_ACTIVO);
        return $this->retornarMuchosSinPaginacion();
    }

//    public function meterOrdenEnCaja($orden_id) {
//        $orden = $this->service_ecommerce->obtenerOrden($orden_id);
//        $orden_items = $this->service_ecommerce->obtenerOrdenItems($orden_id);
//        foreach ($orden_items as $item) {
//            //primero inactivamos la asignacion a caja previa
//            $this->sacarItemDeCaja($item->id);
//            //en base a la receta vamos a obtener la cantidad de stems
//            //y con eso aplicamos la regla para determinar que caja aplicar
//            //por ahora solo lo vamos a asignar directo a una caja cualquiera
//            $tipo_caja_id = 1;
//            $this->meterItemEnCaja($item->id, $tipo_caja_id);
//        }
//        return true;
//    }

    public function obtenerNuevaCaja($longitud_cm, $stems, $florero, $petalos, $store_id) {
        $tipo_caja_id = false;
        if ($longitud_cm >= 80) {
//                        error_log(print_r("Longitud en cm es mayor a 40", true));
            if ($longitud_cm == 90 || $longitud_cm == 100) {
                if ($this->verificarEntraItemsEnCaja(CAJA_FONDO_M_ID, $stems, $florero, $petalos)) {//FONDOS M
                    $tipo_caja_id = CAJA_FONDO_M_ID;
                } else if ($this->verificarEntraItemsEnCaja(CAJA_QB_L_ID, $stems, $florero, $petalos)) {//QB-L
                    $tipo_caja_id = CAJA_QB_L_ID;
                }
            }
            if (!$tipo_caja_id) {
                if ($this->verificarEntraItemsEnCaja(CAJA_FONDO_S_ID, $stems, $florero, $petalos)) {//FONDOS S
                    $tipo_caja_id = CAJA_FONDO_S_ID;
                } else if ($this->verificarEntraItemsEnCaja(CAJA_FONDO_M_ID, $stems, $florero, $petalos)) {//FONDOS M
                    $tipo_caja_id = CAJA_FONDO_M_ID;
                } else if ($this->verificarEntraItemsEnCaja(CAJA_QB_L_ID, $stems, $florero, $petalos)) {//QB-L
                    $tipo_caja_id = CAJA_QB_L_ID;
                }
            }
//                        error_log("Tipo caja es " . $tipo_caja_id);
        } else if ($longitud_cm === 40) {
//                        error_log(print_r("Longitud es 40 cm", true));
            if ($store_id === 2) {//Wholesales
//                            error_log(print_r("Wholesales", true));
//                            if ($this->verificarEntraItemsEnCaja(7, $stems, $florero, $petalos)) {//QB-S
//                                $tipo_caja_id = 1;
//                            } else if ($this->verificarEntraItemsEnCaja(6, $stems, $florero, $petalos)) {//HB-M
//                                $tipo_caja_id = 2;
//                            }
                error_log(print_r("Tipo de caja wholesale es " . $tipo_caja_id, true));
                $tipo_caja_id = 7;
            } else {
//                            error_log(print_r("No es Wholesales", true));
                if ($this->verificarEntraItemsEnCaja(CAJA_CUTE_ID, $stems, $florero, $petalos)) {//CUTE
                    $tipo_caja_id = 1;
                } else if ($this->verificarEntraItemsEnCaja(CAJA_PERFECT_ID, $stems, $florero, $petalos)) {//PERFECT
                    $tipo_caja_id = 2;
                } else if ($this->verificarEntraItemsEnCaja(CAJA_ABUNDANT_ID, $stems, $florero, $petalos)) {//ABUNDANT
                    $tipo_caja_id = 3;
                }

//                            if (!$tipo_caja_id){
//                                 $tipo_caja_id = 1;
//                            }
                error_log(print_r("Tipo de caja es " . $tipo_caja_id, true));
            }
        }
        return $tipo_caja_id;
    }

    public function ordenMeterItemsEnCaja($orden_id, $saltar_si_empacado = true) {
        $orden = $this->service_ecommerce->obtenerOrden($orden_id);
        $orden_items = $this->service_ecommerce->obtenerOrdenItems($orden_id);
        if ($orden_items && sizeof($orden_items) == 1) {
            $cajaActual_id = false;
            $longitud_cm = 0;
            $cajaActual_tipocaja_id = false;
            $cajaActualTotalStems = 0;
            $cajaActualTotalFlorero = 0;
            $cajaActualTotalPetalos = 0;
            foreach ($orden_items as $item) {
                if ($saltar_si_empacado) {
                    if ($item->orden_caja_id != null) {
                        continue;
                    }
                }
                $this->sacarItemDeCaja($item->id);
                $producto_variante = $this->service_ecommerce_producto->obtenerProductoVariante($item->variante_id);
                $totalesStems = $this->service_ecommerce_formula->totalStemsRecetaSKU($item->info_variante_sku);
                $stems = $totalesStems->sum * $item->cantidad;
                $florero = $this->service_ecommerce_producto->devolverOcrearPropiedad("FLOWER VASE");
                $florero_existe = $this->service_ecommerce->existeOrdenItemPropiedad($item->id, $florero->id, -1, ESTADO_ACTIVO);
                if (!$florero_existe) {
                    $florero_existe = $this->service_ecommerce->existeOrdenItemPropiedad($item->id, 372, -1, ESTADO_ACTIVO);
                    if (!$florero_existe) {
                        $florero_existe = $this->service_ecommerce->existeOrdenItemPropiedad($item->id, 373, -1, ESTADO_ACTIVO);
                        if (!$florero_existe) {
                            $florero_existe = $this->service_ecommerce->existeOrdenItemPropiedad($item->id, 398, -1, ESTADO_ACTIVO);
                        }
                    }
                }
                $florero = 0;
                if ($florero_existe) {
                    $prop = analizarPropiedad($florero_existe, true, false);
                    if (strpos(strtoupper($prop->valor), "SI") >= 0) {
                        $florero = 1;
                    }
                }

                $petalos = $this->service_ecommerce_producto->devolverOcrearPropiedad("PETALS");
//                error_log(print_r($petalos, true));
                $petalos_existe = $this->service_ecommerce->existeOrdenItemPropiedad($item->id, $petalos->id, -1, ESTADO_ACTIVO);

                if (!$petalos_existe) {
                    $petalos_existe = $this->service_ecommerce->existeOrdenItemPropiedad($item->id, 371, -1, ESTADO_ACTIVO);
                }

                $petalos = 0;
                if ($petalos_existe) {
                    error_log("Petalos existe");
                    $prop = analizarPropiedad($petalos_existe, true, true);
                    if ($prop) {
                        $petalos = 1;
                    }
                }
//                error_log("Stems " . $stems);
//                error_log("florero " . $florero);
//                error_log("petalos " . $petalos);

                if ($cajaActual_id && ($longitud_cm === $producto_variante->largo_cm)) {
//                    error_log(print_r("Si hay una caja verifico si este item entra alli", true));
//si hay una caja verifico si este item entra alli
                    if ($this->verificarEntraItemsEnCaja($cajaActual_tipocaja_id, $cajaActualTotalStems + $stems, $cajaActualTotalFlorero + $florero, $cajaActualTotalPetalos + $petalos)) {
                        $cajaActualTotalStems = $stems + $cajaActualTotalStems;
                        $cajaActualTotalFlorero = $cajaActualTotalFlorero + $florero;
                        $cajaActualTotalPetalos = $cajaActualTotalPetalos + $petalos;
                    } else {
//veamos si entra en otra caja la cantidad total que tenemos
                        $actualizado = false;
                        for ($i = 1; $i <= 4; $i++) {
//                            error_log(print_r("Iteraccion " . $i, true));
                            if ($this->verificarEntraItemsEnCaja($i, $cajaActualTotalStems + $stems, $cajaActualTotalFlorero + $florero, $cajaActualTotalPetalos + $petalos)) {
//                                error_log(print_r("Actualicemos el tipo de caja a " . $i, true));
                                if ($this->actualizarOrdenCaja($cajaActual_id, $i)) {
                                    error_log("Caja actualizada a " . $i);
                                    $actualizado = true;
                                }
                                break;
                            }
                        }
                        if (!$actualizado) {
                            error_log("Caja NO actualizada a ");
//vamos a ver en que caja entra y creamos una
                            $cajaActual_id = false;
                        }
                    }
                }

                if (!$cajaActual_id) {
                    $tipo_caja_id = $this->obtenerNuevaCaja($producto_variante->largo_cm, $stems, $florero, $petalos, $orden->store_id);
                    if (!$tipo_caja_id) {
                        for ($i = 1; $i <= 3; $i++) {
                            error_log(print_r("Iteraccion " . $i, true));
                            if ($this->verificarEntraItemsEnCaja($i, $stems, $florero, $petalos)) {
//                                error_log(print_r("Break en " . $i, true));
                                $tipo_caja_id = $i;
                                break;
                            }
                        }
                    }
                    if ($tipo_caja_id) {
                        error_log(print_r("Existe tipo_caja_id", true));
                        $cajaActual_id = $this->crearOrdenCaja($orden_id, $tipo_caja_id);
                        $cajaActualTotalStems = 0;
                        $cajaActualTotalFlorero = 0;
                        $cajaActualTotalPetalos = 0;
                    }
                }
                if ($cajaActual_id) {
                    $this->meterItemEnCaja($item->id, $cajaActual_id);
                } else {
                    $cajaActual_id = $this->crearOrdenCaja($orden_id, CAJA_NODEFINIDA_ID);
                    $this->meterItemEnCaja($item->id, $cajaActual_id);
                    return false;
                }
            }
            return true;
        } else {
            //cuando hay más de un producto
            $cajaActual_id = $this->crearOrdenCaja($orden_id, CAJA_NODEFINIDA_ID);
            //metemos todos los items en esta caja, para que pueda ser analizada y empacada manualmente
            foreach ($orden_items as $item) {
                $this->meterItemEnCaja($item->id, $cajaActual_id);
            }
        }
        return false;
    }

    public function obtenerOrdenCajaItems($orden_caja_id) {
        return $this->service_ecommerce->obtenerOrdenItem(false, false, $orden_caja_id);
    }

    public function verificarEntraItemsEnCaja($tipo_caja_id, $stems, $florero, $petalos) {
        if ($petalos) {
//            switch ($tipo_caja_id) {
//                case 1: //cute
//                    if ($stems <= 15) {
//                        return true;
//                    }
//                    break;
//
//                case 2: //perfect
//                    if ($stems <= 30) {
//                        return true;
//                    }
//                    break;
//                case 3: //ABUNDANT
//                    if ($stems <= 50) {
//                        return true;
//                    }
//                    break;
//                case 4: //FONDOS //para longstems >= 70cm
//                    if ($stems <= 50) {
//                        return true;
//                    }
//                    break;
//                case 9: //QB-L //para longstems
//                    if ($stems <= 100) {
//                        return true;
//                    }
//                    break;
//                default:
//                    break;
//            }
            return false;
        }
        switch ($tipo_caja_id) {
            case CAJA_CUTE_ID://CUTE
                if ($stems <= 15) {
                    return true;
                } else {
                    if (!$florero) {
                        if ($stems <= 24) {
                            return true;
                        }
                    }
                }
                break;
            case CAJA_PERFECT_ID://PERFECT
                if ($florero) {
                    if ($stems <= 36) {
                        return true;
                    }
                } else {
                    if ($stems <= 50) {
                        return true;
                    }
                }
                break;
            case CAJA_ABUNDANT_ID: //ABUNDANT
                if ($florero) {
                    if ($stems <= 50) {
                        return true;
                    }
                }
                break;
            case CAJA_FONDO_M_ID: //FONDOS AHORA FONDOS M //para longstems >= 70cm
                if ($stems <= 50) {
                    return true;
                }
                break;
            case CAJA_QB_S_CORTADA_ID: //(CORTADA)
                break;
            case CAJA_HB_M_ID: //HB-M //lo usa mas wholesale
                if (($stems > 150) && ($stems <= 200)) {
                    return true;
                }
                break;
            case CAJA_QB_S_ID: //QB-S //lo usa mas wholesale
                if (($stems >= 100) && ($stems <= 150)) {
                    return true;
                }
                break;
            case CAJA_QB_M_ID: //QB-M
                break;
            case CAJA_QB_L_ID: //QB-L //para longstems
                if ($stems <= 100) {
                    return true;
                }
                break;
            case CAJA_FONDO_S_ID://FONDOS S
                if ($stems <= 36) {
                    return true;
                }
            default:
                break;
        }
        return false;
    }

}
