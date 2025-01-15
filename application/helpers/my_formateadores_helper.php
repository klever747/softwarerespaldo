<?php
define("ESPACIADO_ELEMENTOS_NORMAL", "p-2 col-12 col-sm-6 col-md-5 col-lg-4 col-xl-3");
define("ESPACIADO_ELEMENTOS_FILTRO", "p-2 col-12 col-sm-6 col-md-4 col-lg-3 col-xl-3");
define("ESPACIADO_ELEMENTOS_DOBLE", "p-2 col-12 col-sm-12 col-md-6 col-lg-5 col-xl-4");
define("ESPACIADO_ELEMENTOS_TRIPLE", "p-2 col-12 col-md-8 col-lg-6");

function fechaActual($formato = false) {
    $datetime_variable = new DateTime();
    $datetime_formatted = date_format($datetime_variable, ($formato ? $formato : FORMATO_FECHA_COMPLETO));
    return $datetime_formatted;
}

function convertirFechaBD($fecha) {
    if ($fecha != null) {
        return date(FORMATO_FECHA, strtotime($fecha));
    }
    return null;
}

function convertirFechaJS($fecha) {
    if ($fecha != null) {
        return date(FORMATO_FECHA_DATEPICKER_PHP, strtotime($fecha));
    }
    return null;
}

function convertirFechaStore($fecha) {
//    date_default_timezone_set('UTC');
    $datetime_formatted = null;
    if ($fecha != null) {
//        print_r("<br/>Formato Fecha Store " . FORMATO_FECHA_STORE);
        $datetime_variable = DateTime::createFromFormat(FORMATO_FECHA_STORE, $fecha);
//        var_dump($datetime_variable);
        if ($datetime_variable == false) {
            $datetime_variable = DateTime::createFromFormat(FORMATO_FECHA_STORE . " Y", $fecha);
            if ($datetime_variable) {
                $datetime_formatted = date_format($datetime_variable, 'Y-m-d');
            }
            //return $datetime_formatted;
        } else {

//            print_r("<br/>datetime_variable " . print_r($datetime_variable, true));
            $datetime_formatted = date_format($datetime_variable, 'Y-m-d');
//            print_r("<br/>datetime_formatted " . print_r($datetime_formatted, true));
            //return $datetime_formatted;
        }
    }
    return $datetime_formatted;
}

function convertirStringDate($fecha, $formatoOrigen) {
    return DateTime::createFromFormat($formatoOrigen, $fecha);
}

function convertirDateString($fecha, $formatoDestino) {
    return date_format($fecha, $formatoDestino);
}

function inputFecha($label, $id, $fechaSeleccionada) {
    ?>
    <div class="input-group mb-0">
        <div class="input-group-prepend col-5 col-sm-4 col-md-12 col-xl-4">
            <span class="text-truncate font-weight-bold align-self-center w-100" id="basic-<?= $id ?>"><?= $label ?></span>
        </div>
        <input type="text" class="form-control col-7 col-sm-8 col-md-12 col-xl-8 select_fecha <?= $id ?>" id="<?= $id ?>" name="<?= $id ?>" aria-describedby="basic-<?= $id ?>"  value="<?= $fechaSeleccionada ?>">
    </div>
    <?php
}

function selFechasRangoFull($tipo_calendario, $rango, $tipoCal = 0) {
    switch ($tipoCal) {
        case 0://shopify
            $selTipoCalendario = array(0 => "Carga Tracking");
            break;
        default:
            $selTipoCalendario = array(0 => "--", 1 => "--");
            break;
    }

    $dd_js = array(
        "id" => "tipo_calendario_full",
        "class" => "form-control select2",
    );
    ?>
    <div class="<?= ESPACIADO_ELEMENTOS_TRIPLE ?>">
        <div class="input-group">
            <div class="input-group-prepend ajustar_altura">
                <span class="input-group-text">
                    <i class="fas fa-calendar-alt"></i>
                </span>
                <?= form_dropdown($dd_js["id"], $selTipoCalendario, $tipo_calendario, $dd_js) ?>
            </div>

            <input type="text" class="form-control float-right fecha_rango_full w-100" id="rango_busqueda_full" name = "rango_busqueda_full" value="<?= $rango ?>">
        </div>
    </div>
    <?php
}

function selFechasRango($tipo_calendario, $rango, $tipoCal) {
    switch ($tipoCal) {
        case 0://shopify
            $selTipoCalendario = array(0 => "Creacion"); //, 2 => "Ingreso Sistema", 3 => "Actualizada");
            break;
        case 1://ecommerce
            $selTipoCalendario = array(0 => "Carguera", 1 => "Entrega", 2 => "Compra", 3 => "Sin Fecha"); //, 2 => "Ingreso Sistema", 3 => "Actualizada");
            break;
        case 3://componentes
            $selTipoCalendario = array(0 => "Carguera"); //, 2 => "Ingreso Sistema", 3 => "Actualizada");
            break;
        case 4://preparacion
            $selTipoCalendario = array(0 => "Carguera", 1 => "Entrega"); //, 2 => "Ingreso Sistema", 3 => "Actualizada");
            break;
        case 5://ups
            $selTipoCalendario = array(0 => "Carguera", 4 => "En Kardex"); //, 2 => "Ingreso Sistema", 3 => "Actualizada");
            break;
        default:
            $selTipoCalendario = array(0 => "--", 1 => "--"); //, 2 => "Ingreso Sistema", 3 => "Actualizada");
            break;
    }

    $dd_js = array(
        "id" => "tipo_calendario",
        "class" => "form-control select2",
    );
    ?>
    <div class="<?= ESPACIADO_ELEMENTOS_DOBLE ?>">
        <div class="input-group">
            <div class="input-group-prepend ajustar_altura">
                <span class="input-group-text">
                    <i class="fas fa-calendar-alt"></i>
                </span>
                <?= form_dropdown($dd_js["id"], $selTipoCalendario, $tipo_calendario, $dd_js) ?>
            </div>
            <input type="text" class="form-control float-right w30 fecha_rango" id="rango_busqueda" name = "rango_busqueda" value="<?= $rango ?>">
        </div>
    </div>
    <?php
}

function selFechasUnico($tipo_calendario, $rango, $tipoCal) {
    switch ($tipoCal) {
        case 0://shopify
            $selTipoCalendario = array(0 => "Creacion"); //, 2 => "Ingreso Sistema", 3 => "Actualizada");
            break;
        case 1://ecommerce
            $selTipoCalendario = array(0 => "Carguera", 1 => "Entrega", 2 => "Compra", 3 => "Sin Fecha"); //, 2 => "Ingreso Sistema", 3 => "Actualizada");
            break;
        case 3://componentes
            $selTipoCalendario = array(0 => "Carguera"); //, 2 => "Ingreso Sistema", 3 => "Actualizada");
            break;
        case 4://preparacion
            $selTipoCalendario = array(0 => "Carguera", 1 => "Entrega"); //, 2 => "Ingreso Sistema", 3 => "Actualizada");
            break;
        case 5://ups
            $selTipoCalendario = array(0 => "Carguera", 4 => "En Kardex"); //, 2 => "Ingreso Sistema", 3 => "Actualizada");
            break;
        case 6://ups
            $selTipoCalendario = array(0 => "Carguera"); //, 2 => "Ingreso Sistema", 3 => "Actualizada");
            break;
        default:
            $selTipoCalendario = array(0 => "--", 1 => "--"); //, 2 => "Ingreso Sistema", 3 => "Actualizada");
            break;
    }

    $dd_js = array(
        "id" => "tipo_calendario_unico",
        "class" => "form-control select2",
    );
    ?>
    <div class="<?= ESPACIADO_ELEMENTOS_DOBLE ?>">
        <div class="input-group">
            <div class="input-group-prepend ajustar_altura">
                <span class="input-group-text">
                    <i class="fas fa-calendar-alt"></i>
                </span>
    <?= form_dropdown($dd_js["id"], $selTipoCalendario, $tipo_calendario, $dd_js) ?>
            </div>
            <input type="text" class="form-control float-right w30 fecha_unica" id="fecha_busqueda" name = "fecha_busqueda" value="<?= $rango ?>">
        </div>
    </div>
    <?php
}

function numOrdenReferencia($numero_orden) {
    ?>
    <div class="<?= ESPACIADO_ELEMENTOS_NORMAL ?>">
        <div class="input-group mb-0">
            <div class="input-group-prepend ajustar_altura">
                <span class="input-group-text" id="basic-num_orden">
                    #OrdenTienda
                </span>
            </div>
            <input type="text" class="form-control text-right" id="referencia_order_number" name="referencia_order_number" aria-describedby="basic-num_orden" placeholder="000000" value="<?= $numero_orden ?>">
        </div>
    </div>
    <?php
}

function numOrden($numero_orden) {
    ?>
    <div class="<?= ESPACIADO_ELEMENTOS_NORMAL ?>">
        <div class="input-group mb-0">
            <div class="input-group-prepend ajustar_altura">
                <span class="input-group-text" id="basic-num_orden">
                    #OrdenInterno
                </span>
            </div>
            <input type="text" class="form-control text-right" id="order_number" name="order_number" aria-describedby="basic-num_orden" placeholder="000000" value="<?= $numero_orden ?>">
        </div>
    </div>
    <!--    <div class="p-2 col-12 col-sm-6 col-md-4 col-lg-3 col-xl-4 input-group row">
            <div class = "col-3 col-md-3 input-group-prepend ajustar_altura">
                <label for="order_number" class="col-form-label">Orden #</label>
            </div>
            <input type="text" class="form-control" name="order_number" id="order_number" placeholder="#orden" value="<?= $numero_orden ?>">
        </div>-->
    <?php
}

function textoBusqueda($texto_busqueda) {
    ?>
    <div class="<?= ESPACIADO_ELEMENTOS_DOBLE ?>">
        <div class="input-group mb-0">
            <div class="input-group-prepend ajustar_altura">
                <span class="input-group-text" id="basic-texto_busqueda">Contenga</span>
            </div>
            <input type="text" class="form-control" id="texto_busqueda" name="texto_busqueda" aria-describedby="basic-texto_busqueda" placeholder="texto busqueda" value="<?= $texto_busqueda ?>">
        </div>
    </div>
    <?php
}

function longitudBuscar($longitud_buscar) {
    ?>
    <div class="<?= ESPACIADO_ELEMENTOS_DOBLE ?>">
        <div class="input-group mb-0">
            <div class="input-group-prepend ajustar_altura">
                <span class="input-group-text" id="basic-longitud_busqueda">Contenga Longitud</span>
            </div>
            <input type="number" class="form-control" id="longitud_buscar" name="longitud_buscar" aria-describedby="basic-texto_busqueda" placeholder="Longitud Busqueda" value="<?= $longitud_buscar ?>">
        </div>
    </div>
    <?php
}

function textoTrackingNumber($tracking_number) {
    ?>
    <div class="<?= ESPACIADO_ELEMENTOS_DOBLE ?>">
        <div class="input-group mb-0">
            <div class="input-group-prepend ajustar_altura">
                <span class="input-group-text" id="basic-tracking_number">Tracking# / #Caja</span>
            </div>
            <input type="text" class="form-control" id="tracking_number" name="tracking_number" aria-describedby="basic-tracking_number" placeholder="tracking number o # caja" value="<?= $tracking_number ?>">
        </div>
    </div>
    <?php
}

function selTrackingNumber($con_tracking_number) {
    $sel_con_tracking_number = array('T' => 'Todos', 'S' => 'Con Tracking#', 'N' => 'Sin Tracking#');

    $dd_js = array(
        "id" => "con_tracking_number",
        "class" => "form-control select2",
        "aria-describedby" => "basic-" . $con_tracking_number . "",
    );
    ?>
    <div class="<?= ESPACIADO_ELEMENTOS_FILTRO ?>">
        <div class="input-group mb-0">
            <div class="input-group-prepend ajustar_altura">
                <span class="input-group-text" id="basic-<?= $con_tracking_number ?>">Con Tracking</span>
            </div>
    <?= form_dropdown($dd_js['id'], $sel_con_tracking_number, $con_tracking_number, $dd_js) ?>
        </div>
    </div>
    <?php
}

function selKardex($con_kardex) {
    $sel_con_kardex = array('T' => 'Todos', 'S' => 'Con Kardex', 'N' => 'Sin Kardex');

    $dd_js = array(
        "id" => "con_kardex",
        "class" => "form-control select2",
        "aria-describedby" => "basic-" . $con_kardex . "",
    );
    ?>
    <div class="<?= ESPACIADO_ELEMENTOS_FILTRO ?>">
        <div class="input-group mb-0">
            <div class="input-group-prepend ajustar_altura">
                <span class="input-group-text" id="basic-<?= $con_kardex ?>">Con Kardex</span>
            </div>
    <?= form_dropdown($dd_js['id'], $sel_con_kardex, $con_kardex, $dd_js) ?>
        </div>
    </div>
    <?php
}

function selEstado($estado_id) {
    $sel_stado = array(false => 'Todos', ESTADO_ACTIVO => 'Activos', ESTADO_INACTIVO => 'Inactivos');

    $dd_js = array(
        "id" => "estado_id",
        "class" => "form-control select2",
        "aria-describedby" => "basic-<?=$estado_id?>",
    );
    ?>
    <div class="<?= ESPACIADO_ELEMENTOS_NORMAL ?>">
        <div class="input-group mb-0">
            <div class="input-group-prepend ajustar_altura">
                <span class="input-group-text" id="basic-<?= $estado_id ?>">Estado</span>
            </div>
    <?= form_dropdown($dd_js['id'], $sel_stado, $estado_id, $dd_js) ?>
        </div>
    </div>
    <?php
}

function selTipoCaja($tipo_caja, $sel_tipo_caja_original) {
//    $sel_tipo_caja = array(0 => 'Todos', 1 => 'Cute', 2 => 'Perfec', 3 => 'Abundant', 4 => 'Fondos', 5 => 'QB-S (CORTADA)', 6 => 'HB-M', 7 => 'QB-S', 8 => 'QB-M', 9 => 'QB-L');
    $sel_tipo_caja[0] = "Todos";
    foreach ($sel_tipo_caja_original as $item) {
        $sel_tipo_caja[$item->id] = $item->nombre;
    }
    $dd_js = array(
        "id" => "tipo_caja",
        "class" => "form-control select2",
        "aria-describedby" => "basic-tipo_caja",
    );
    ?>
    <div class="<?= ESPACIADO_ELEMENTOS_NORMAL ?>">
        <div class="input-group mb-0">
            <div class="input-group-prepend ajustar_altura">
                <span class="input-group-text" id="basic-tipo_caja">Tipo Caja</span>
            </div>
    <?= form_dropdown($dd_js['id'], $sel_tipo_caja, $tipo_caja, $dd_js) ?>
        </div>
    </div>
    <?php
}

function selStore($store_id, $sel_store) {
    $sel_store[0] = "Todos";
    ?>
    <?php
    $dd_js = array(
        "id" => "store_id",
        "class" => "form-control select2",
        "aria-describedby" => "basic-store_id",
    );
    ?>
    <div class="<?= ESPACIADO_ELEMENTOS_NORMAL ?>">
        <div class="input-group mb-0">
            <div class="input-group-prepend ajustar_altura">
                <span class="input-group-text" id="basic-store_id"><i class="fas fa-store"></i></span>
            </div>
    <?= form_dropdown($dd_js['id'], $sel_store, $store_id, $dd_js) ?>
        </div>
    </div>
    <?php
}

function selFinca($finca_id, $sel_finca) {
    $sel_finca[0] = "Todos";
    ?>
    <?php
    $dd_js = array(
        "id" => "finca_id",
        "class" => "form-control select2",
        "aria-describedby" => "basic-store_id",
    );
    ?>
    <div class="<?= ESPACIADO_ELEMENTOS_NORMAL ?>">
        <div class="input-group mb-0">
            <div class="input-group-prepend ajustar_altura">
                <span class="input-group-text" id="basic-store_id"><i class="fas fa-warehouse"></i></span>
            </div>
    <?= form_dropdown($dd_js['id'], $sel_finca, $finca_id, $dd_js) ?>
        </div>
    </div>
    <?php
}

function selOrdenEstado($orden_estado_id, $sel_orden_estado) {
    // $sel_orden_estado[0] = "Todos";
    ?>
    <?php
    $dd_js = array(
        "id" => "orden_estado_id",
        "class" => "form-control select2",
        "aria-describedby" => "basic-store_id",
    );
    ?>
    <div class="<?= ESPACIADO_ELEMENTOS_NORMAL ?>">
        <div class="input-group mb-0">
            <div class="input-group-prepend ajustar_altura">
                <span class="input-group-text" id="basic-store_id">Estado Orden</span>
            </div>
    <?= form_dropdown($dd_js['id'], $sel_orden_estado, $orden_estado_id, $dd_js) ?>
        </div>
    </div>
    <?php
}

function selRegistrosPorPagina($regpp) {
    $selRegPP = array(5 => "5", 10 => "10", 20 => "20", 50 => "50", 100 => "100", 0 => "500", 1000 => "1000", 2000 => "2000", 3000 => "3000");

    $dd_js = array(
        "id" => "regpp",
        "class" => "form-control select2",
        "aria-describedby" => "basic-regpp",
    );
    ?>
    <div class="<?= ESPACIADO_ELEMENTOS_NORMAL ?>">
        <div class="input-group mb-0">
            <div class="input-group-prepend ajustar_altura">
                <span class="input-group-text" id="basic-regpp"><i class="fas fa-grip-horizontal"></i></span>
            </div>
    <?= form_dropdown($dd_js['id'], $selRegPP, $regpp, $dd_js) ?>
        </div>
    </div>
    <?php
}

function selEmpacado($empacado) {

    $sel = array('T' => "Todos", 'S' => "Empacados", 'N' => "No Empacados");

    $dd_js = array(
        "id" => "empacado",
        "class" => "form-control select2",
        "aria-describedby" => "basic-empacado",
    );
    ?>
    <div class="<?= ESPACIADO_ELEMENTOS_DOBLE ?>">
        <div class="input-group mb-0">
            <div class="input-group-prepend ajustar_altura">
                <span class="input-group-text" id="basic-empacado"><i class="fas fa-box"></i></span>
            </div>
    <?= form_dropdown($dd_js['id'], $sel, $empacado, $dd_js) ?>
        </div>
    </div>
    <?php
}

function selAsignadoCaja($asignadoCaja) {

    $sel = array('T' => "Todos", 'S' => "Si / Total", 'N' => "No / Parcial");

    $dd_js = array(
        "id" => "asignadoCaja",
        "class" => "form-control select2",
        "aria-describedby" => "basic-regpp",
    );
    ?>
    <div class="<?= ESPACIADO_ELEMENTOS_DOBLE ?>">
        <div class="input-group mb-0">
            <div class="input-group-prepend ajustar_altura">
                <span class="input-group-text" id="basic-regpp">Asignado Caja</span>
            </div>
    <?= form_dropdown($dd_js['id'], $sel, $asignadoCaja, $dd_js) ?>
        </div>
    </div>
    <?php
}

function selPreparado($preparacion) {
    $sel = array('T' => "Todos", 'N' => "No", 'S' => "Si");

    $dd_js = array(
        "id" => "preparado",
        "class" => "form-control select2",
        "aria-describedby" => "basic-regpp",
    );
    ?>
    <div class="<?= ESPACIADO_ELEMENTOS_NORMAL ?>">
        <div class="input-group mb-0">
            <div class="input-group-prepend ajustar_altura">
                <span class="input-group-text" id="basic-regpp"><i class="fas fa-spa"></i></span>
            </div>
    <?= form_dropdown($dd_js['id'], $sel, $preparacion, $dd_js) ?>
        </div>
    </div>
    <?php
}

function selTerminado($terminado) {
    $sel = array('T' => "Todos", 'N' => "No", 'S' => "Si");

    $dd_js = array(
        "id" => "terminado",
        "class" => "form-control select2",
        "aria-describedby" => "basic-regpp",
    );
    ?>
    <div class="<?= ESPACIADO_ELEMENTOS_NORMAL ?>">
        <div class="input-group mb-0">
            <div class="input-group-prepend ajustar_altura">
                <span class="input-group-text" id="basic-regpp"><i class="fas fa-tshirt"></i></span>
            </div>
    <?= form_dropdown($dd_js['id'], $sel, $terminado, $dd_js) ?>
        </div>
    </div>
    <?php
}

function selBonchado($bonchado) {
    $sel = array('T' => "Todos", 'N' => "Pendientes", 'S' => "Bonchados");

    $dd_js = array(
        "id" => "bonchado",
        "class" => "form-control select2",
        "aria-describedby" => "basic-regpp",
    );
    ?>
    <div class="<?= ESPACIADO_ELEMENTOS_NORMAL ?>">
        <div class="input-group mb-0">
            <div class="input-group-prepend ajustar_altura">
                <span class="input-group-text" id="basic-regpp"><i class="fas fa-spa"></i></span>
            </div>
    <?= form_dropdown($dd_js['id'], $sel, $bonchado, $dd_js) ?>
        </div>
    </div>
    <?php
}

function selVestido($vestido) {
    $sel = array('T' => "Todos", 'N' => "Pendientes", 'S' => "Vestidos");

    $dd_js = array(
        "id" => "vestido",
        "class" => "form-control select2",
        "aria-describedby" => "basic-regpp",
    );
    ?>
    <div class="<?= ESPACIADO_ELEMENTOS_NORMAL ?>">
        <div class="input-group mb-0">
            <div class="input-group-prepend ajustar_altura">
                <span class="input-group-text" id="basic-regpp"><i class="fas fa-tshirt"></i></span>
            </div>
    <?= form_dropdown($dd_js['id'], $sel, $vestido, $dd_js) ?>
        </div>
    </div>
    <?php
}

function selReenviado($reenviado) {
    $sel = array('T' => "Todos", 'S' => "Reenviado", 'N' => "No Reenviado");

    $dd_js = array(
        "id" => "reenviado",
        "class" => "form-control select2",
        "aria-describedby" => "basic-regpp",
    );
    ?>
    <div class="<?= ESPACIADO_ELEMENTOS_NORMAL ?>">
        <div class="input-group mb-0">
            <div class="input-group-prepend ajustar_altura">
                <span class="input-group-text" id="basic-regpp">Reenviado</span>
            </div>
    <?= form_dropdown($dd_js['id'], $sel, $reenviado, $dd_js) ?>
        </div>
    </div>
    <?php
}

function selTarjetaImpresa($tarjeta_impresa) {
    $sel = array('T' => "Todos", 'N' => "NO impresas", 'S' => "Impresas");

    $dd_js = array(
        "id" => "tarjeta_impresa",
        "class" => "form-control select2",
        "aria-describedby" => "basic-regpp",
    );
    ?>
    <div class="<?= ESPACIADO_ELEMENTOS_NORMAL ?>">
        <div class="input-group mb-0">
            <div class="input-group-prepend ajustar_altura">
                <span class="input-group-text" id="basic-regpp">Tarjetas</span>
            </div>
    <?= form_dropdown($dd_js['id'], $sel, $tarjeta_impresa, $dd_js) ?>
        </div>
    </div>
    <?php
}

function chkSinFecha($estado) {
    ?>
    <div class="p-2 col-1">
        <input type="checkbox" name="sin_fecha"  <?= $estado == 1 ? "checked" : "" ?> data-bootstrap-switch data-off-color="danger" data-on-color="success" value="1">
    </div>
    <?php
}

function chkMostrarTinturados($estado) {
    ?>
    <div class="p-2 col-1">
        Tinturados
        <input type="checkbox" name="mostrar_tinturados" <?= $estado == 1 ? "checked" : "" ?> data-bootstrap-switch data-off-color="danger" data-on-color="success" value="1">
    </div>
    <?php
}

function chkMostrarNaturales($estado) {
    ?>
    <div class="p-2 col-1">
        Naturales
        <input type="checkbox" name="mostrar_naturales" <?= $estado == 1 ? "checked" : "" ?> data-bootstrap-switch data-off-color="danger" data-on-color="success" value="1">
    </div>
    <?php
}

function chkMostrarAccesorios($estado) {
    ?>
    <div class="p-2 col-1">
        Accesorios
        <input type="checkbox" name="mostrar_accesorios" <?= $estado == 1 ? "checked" : "" ?> data-bootstrap-switch data-off-color="danger" data-on-color="success" value="1">
    </div>
    <?php
}

function chkAgrupadoLongitud($estado) {
    ?>
    <div class="p-2 col-1">
        Detalle Longitud
        <input type="checkbox" name="agrupado_longitud" <?= $estado == 1 ? "checked" : "" ?> data-bootstrap-switch data-off-color="danger" data-on-color="success" value="1">
    </div>
    <?php
}

function chkAgrupadoDescripcion($estado) {
    ?>
    <div class="p-2 col-1">
        Agrupados por Descripci&oacute;n
        <input type="checkbox" name="agrupado_descripcion" <?= $estado == 1 ? "checked" : "" ?> data-bootstrap-switch data-off-color="danger" data-on-color="success" value="1">
    </div>
        <?php
    }

    function chkGenerico($arr) {
        ?>
    <div class="p-2 col-1">
    <?= $arr['descripcion'] ?>
        <input type="checkbox" name="<?= $arr['id'] ?>" <?= $arr['estado'] == 1 ? "checked" : "" ?> data-bootstrap-switch data-off-color="danger" data-on-color="success" value="1">
    </div>
    <?php
}

function btnBuscar() {
    ?>
    <div class="p-2 col-12 col-sm-2 col-md-1 ">
        <button type = "submit" name="btn_buscar" id="btn_buscar" class = "btn btn-primary btn-block"><i class="fas fa-search"></i></button>
    </div>
    <?php
}

function btn($arr) {
    ?>
    <div class="col-1 text-right">
        <button type = "button" name="<?= $arr['id'] ?>" id="<?= $arr['id'] ?>" class = "btn btn-primary btn-block pull-right" value="<?= $arr['valor'] ?>"><?= $arr['valor'] ?></button>
    </div>
    <?php
}

function accion($tipo, $tooltip = false) {
    $tooltip = $tooltip ?: $tipo;
    switch ($tipo) {
        case 'pdf':
            ?>
            <button type = "button" name="exportar" id="exportar_pdf" class = "btn pull-right btn-exportacion float-right" value="pdf" title="<?= $tooltip ?: 'Exportar a pdf' ?>"><i class="fas fa-file-pdf"></i></button>
            <?php
            break;
        case 'imprimir_tarjetas':
            ?>
            <button type = "button" name="imprimir_tarjeta" id="imprimir_tarjeta" class = "btn pull-right btn-imprimir float-right" value="ordenes_seleccionadas" title="<?= $tooltip ?: 'Imprimir tarjeas de ordenes' ?>"><i class="fas fa-print"></i></button>
            <?php
            break;
        case 'excel':
            ?>
            <button type = "button" name="exportar" id="exportar_excel" class = "btn pull-right btn-exportacion float-right" value="excel" title="<?= $tooltip ?: 'Exportar a excel' ?>"><i class="far fa-file-excel"></i></button>
            <?php
            break;
        case 'empacar':
            ?>
            <button type = "button" name="empacar_ordenes" id="empacar_ordenes" class = "btn pull-right btn-empacar float-right" value="empacar" title="<?= $tooltip ?: 'empacar faltantes' ?>"><i class="fas fa-box"></i></button>
            <?php
            break;
        case 'tracking_carga'
            ?>
            <button type = "button" name="exportar_tracking_carga" id="exportar_tracking_carga" class = "btn pull-right btn-exportacion float-right" value="tracking_carga" title="<?= $tooltip ?: 'generar archivo para carga de tracking number' ?>"><i class="fas fa-table"></i></button>
            <button type = "button" name="exportar_tracking_carga_completo" id="exportar_tracking_carga_completo" class = "btn pull-right btn-exportacion float-right" value="tracking_carga" title="<?= $tooltip ? $tooltip . " completo" : 'generar archivo para carga de tracking number completo' ?>"><i class="fas fa-border-none"></i></button>
            <?php
            break;
        default:
            break;
    }
}

function seleccionarColores($colores) {
    ?>
    <div class="<?= ESPACIADO_ELEMENTOS_DOBLE ?>">
        <div class="input-group mb-0">
            <div class="input-group-prepend ajustar_altura">
                <span class="input-group-text" id="basic-regpp"><i class="fas fa-tags"></i></span>
            </div>
            <select class="select2bs4" multiple="multiple" id="colores" name="colores[]" style='width:75% !important' >
    <?php
    foreach ($colores as $k => $v) {
        echo '<option ' . ($v == 1 ? "selected" : "") . '>' . $k . '</option>';
    }
    ?>
            </select>
        </div>
    </div>

    <?php
}

function filtroBusqueda($urlBusqueda, $arrCampos, $ocultos = false) {//wsanchez TODO corregir el valor de estas variantes
    ?>
    <?= form_open(base_url() . $urlBusqueda, array("id" => "form_busqueda")) ?>
    <input type = "hidden" name = "paginacion_pag" id = "paginacion_pag" value = "<?php $pagina ?>" />
    <?php
    if ($ocultos) {
        foreach ($ocultos as $k => $v) {
            ?>
            <input type = "hidden" name = "<?= $k ?>" id = "<?= $k ?>" value = "<?php $v ?>" />
                    <?php
                }
            }
            ?>
    <div class = "card-body card-busqueda">
        <div class = "form-row small" style="font-size: 0.8rem">
            <?= (array_key_exists('store_id', $arrCampos)) ? selStore($arrCampos['store_id'], $arrCampos['sel_store']) : '' ?>
            <?= (array_key_exists('finca_id', $arrCampos)) ? selFinca($arrCampos['finca_id'], $arrCampos['sel_finca']) : '' ?>
            <?= (array_key_exists('tipo_caja', $arrCampos)) ? selTipoCaja($arrCampos['tipo_caja'], $arrCampos['sel_tipo_caja']) : '' ?>
            <?= (array_key_exists('estado_id', $arrCampos)) ? selEstado($arrCampos['estado_id']) : '' ?>
            <?= (array_key_exists('rango_busqueda', $arrCampos)) ? selFechasRango($arrCampos['tipo_calendario'], $arrCampos['rango_busqueda'], $arrCampos['uso_calendario']) : '' ?>
            <?= (array_key_exists('fecha_busqueda', $arrCampos)) ? selFechasUnico($arrCampos['tipo_calendario_unico'], $arrCampos['fecha_busqueda'], $arrCampos['uso_calendario_unico']) : '' ?>
            <?= (array_key_exists('orden_estado_id', $arrCampos)) ? selOrdenEstado($arrCampos['orden_estado_id'], $arrCampos['sel_orden_estado']) : '' ?>
            <?= (array_key_exists('texto_busqueda', $arrCampos)) ? textoBusqueda($arrCampos['texto_busqueda']) : '' ?>
            <?= (array_key_exists('longitud_buscar', $arrCampos)) ? longitudBuscar($arrCampos['longitud_buscar']) : '' ?>
            <?= (array_key_exists('tracking_number', $arrCampos)) ? textoTrackingNumber($arrCampos['tracking_number']) : '' ?>
    <?= (array_key_exists('order_number', $arrCampos)) ? numOrden($arrCampos['order_number']) : '' ?>
            <?= (array_key_exists('referencia_order_number', $arrCampos)) ? numOrdenReferencia($arrCampos['referencia_order_number']) : '' ?>

            <?= (array_key_exists('empacado', $arrCampos)) ? selEmpacado($arrCampos['empacado']) : '' ?>
            <?= (array_key_exists('asignadoCaja', $arrCampos)) ? selAsignadoCaja($arrCampos['asignadoCaja']) : '' ?>


            <?= (array_key_exists('preparado', $arrCampos)) ? selPreparado($arrCampos['preparado']) : '' ?>
            <?= (array_key_exists('terminado', $arrCampos)) ? selTerminado($arrCampos['terminado']) : '' ?>
            <?= (array_key_exists('bonchado', $arrCampos)) ? selBonchado($arrCampos['bonchado']) : '' ?>
            <?= (array_key_exists('vestido', $arrCampos)) ? selVestido($arrCampos['vestido']) : '' ?>
            <?= (array_key_exists('reenviado', $arrCampos)) ? selReenviado($arrCampos['reenviado']) : '' ?>

            <?= (array_key_exists('colores', $arrCampos)) ? seleccionarColores($arrCampos['colores']) : '' ?>
            <?= (array_key_exists('tarjeta_impresa', $arrCampos)) ? selTarjetaImpresa($arrCampos['tarjeta_impresa']) : '' ?>

            <?= (array_key_exists('con_tracking_number', $arrCampos)) ? selTrackingNumber($arrCampos['con_tracking_number']) : '' ?>
            <?= (array_key_exists('con_kardex', $arrCampos)) ? selKardex($arrCampos['con_kardex']) : '' ?>

            <?= (array_key_exists('sin_fecha', $arrCampos)) ? chkSinFecha($arrCampos['sin_fecha']) : '' ?>
            <?= (array_key_exists('mostrar_tinturados', $arrCampos)) ? chkMostrarTinturados($arrCampos['mostrar_tinturados']) : '' ?>
            <?= (array_key_exists('mostrar_naturales', $arrCampos)) ? chkMostrarNaturales($arrCampos['mostrar_naturales']) : '' ?>
            <?= (array_key_exists('mostrar_accesorios', $arrCampos)) ? chkMostrarAccesorios($arrCampos['mostrar_accesorios']) : '' ?>
            <?= (array_key_exists('agrupado_longitud', $arrCampos)) ? chkAgrupadoLongitud($arrCampos['agrupado_longitud']) : '' ?>
            <?= (array_key_exists('agrupado_descripcion', $arrCampos)) ? chkAgrupadoDescripcion($arrCampos['agrupado_descripcion']) : '' ?>

            <?= (array_key_exists('mostrarRestantes', $arrCampos)) ? chkGenerico(array("id" => "mostrarRestantes", "descripcion" => "Mostrar Total Pendiente", "estado" => $arrCampos['mostrarRestantes'])) : '' ?>
            <!--<div class="col-12 flex-wrap form-group row filtro_busqueda_general">-->
    <?= (array_key_exists('rango_busqueda_full', $arrCampos)) ? selFechasRangoFull($arrCampos['tipo_calendario_full'], $arrCampos['rango_busqueda_full'], $arrCampos['uso_calendario_full']) : '' ?>

    <?= (array_key_exists('regpp', $arrCampos)) ? selRegistrosPorPagina($arrCampos['regpp']) : ''; ?>
    <?= btnBuscar(); ?>
            <!--</div>-->

        </div>
    </div>
                <?= form_close() ?>


    <div class = "card-body small">
        <div class="row clearfix">
            <div class="col-10 totales_resumen">
                <?php
                if (array_key_exists('totales', $arrCampos) && $arrCampos['totales']) {
                    echo mostrarTotalesOrdenes($arrCampos['totales']);
                }
                if (array_key_exists('total_shipping', $arrCampos) && $arrCampos['total_shipping']) {
                    echo mostrarTotalShipping($arrCampos['total_shipping']);
                }
                ?>
            </div>
            <div class="col-2">
    <?= (array_key_exists('empacar', $arrCampos)) ? accion('empacar') : '' ?>
    <?= (array_key_exists('exportar_pdf', $arrCampos)) ? accion('pdf') : '' ?>
    <?= (array_key_exists('exportar_xls', $arrCampos)) ? accion('excel') : '' ?>
    <?= (array_key_exists('carga_trackingnumber', $arrCampos)) ? accion('tracking_carga') : '' ?>
    <?= (array_key_exists('imprimir_tarjetas', $arrCampos)) ? accion('imprimir_tarjetas', $arrCampos['imprimir_tarjetas']) : '' ?>
            </div>
        </div>
    </div>
    <?php
}

//Descripcion Funcion para poner color a los estados
function mostrarEstilos($estado) {
    $lv_estado = "";
    switch ($estado) {
        case ESTADO_ACTIVO:
            $lv_estado = "<span class='badge bg-success'>ACTIVO</span>";
            break;
        case ESTADO_INACTIVO:
            $lv_estado = "<span class='badge bg-danger'>INACTIVO</span>";
            break;
        case 'S':
            $lv_estado = "<span class='badge bg-success'>Si</span>";
            break;
        case 'N':
            $lv_estado = "<span class='badge bg-danger'>No</span>";
            break;
        default:
            $lv_estado = "<span class='badge bg-info'>NO DEFINIDO</span>";
            break;
    }
    return $lv_estado;
}

function boxesRamas($principal, $id_padre) {
    $respuesta = "";
    foreach ($principal as $e => $prin) {
        if ($prin->padre_id == $id_padre) {
            $respuesta .= '<div class="col-12 col-sm-12 col-md-12">
            <div class="info-box mb-3">
                 <span class="info-box-icon elevation-1" ' . BACKGROUND_BOXES . '><i class="' . $prin->icono . '"></i></span>
                <div class="info-box-content">
                    <div class="info-box-content">
                      <span class="info-box-text">Opción: ' . $prin->nombre . '</span>
                      <span class="info-box-number">Url: ' . $prin->url . '</span>
                      <span class="info-box-text">Estado: ' . mostrarEstilos($prin->estado) . '</span>
                      <span class="info-box-text">
                            <div class="row">
                                    <div class="col-12 col-sm-6 col-md-6 col-xs-6">
                                    <button type = "submit" title="Editar" class="btn btn-accion btn-tool" data-id="' . $prin->id . '" value="eliminar"><i class="far fa-trash-alt"></i></button>
                                    </div>
                                    <div class="col-12 col-sm-6 col-md-6 col-xs-6">
                                    <button type = "submit" title="Eliminar" class="btn btn-accion btn-tool" data-id="' . $prin->id . '" value="editar"><i class="fas fa-pencil-alt"></i></button>
                                    </div>
                            </div>';


            $respuesta .= '<div class="col-12 col-sm-12 col-md-12 col-xs-12">
                                                  <div class="card">
                                                    <div class="card-header">
                                                    <h3 class="card-title">Submenus disponibles</h3>

                                                        <div class="card-tools">
                                                          <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                                            <i class="fas fa-minus"></i>
                                                          </button>
                                                        </div>
                                                    </div>
                                                      <div class="card-body p-0">
                                                     ';
            $respuesta .= boxesRamas($principal, $prin->id);
            $respuesta .= '  </div>
                                                  </div>
                                                    </div></div>
                 </div>
            </div>
        </div>';
        }
    }
    return $respuesta;
}

function crearBoxes(
$menues, $menueshijos, $padre_id) {
    $bandera = true;
    //   echo  $menues;
    $respuesta = "";
    foreach ($menues as $b => $menu) {
        $respuesta .= '<div class="col-12 col-sm-12 col-md-12">
            <div class="info-box mb-3">
                 <span class="info-box-icon elevation-1" ' . BACKGROUND_BOXES . '><i class="' . $menu->icono . '"></i></span>
                <div class="info-box-content">
                    <div class="info-box-content">
                      <span class="info-box-text">Opción: ' . $menu->nombre . '</span>
                      <span class="info-box-number">Url: ' . $menu->url . '</span>
                      <span class="info-box-text">Estado: ' . mostrarEstilos($menu->estado) . '</span>
                      <span class="info-box-text">
                        <div class="row">
                            <div class="col-12 col-sm-6 col-md-6 col-xs-6">
                            <button type = "submit" title="Editar" class="btn btn-accion btn-tool" data-id="' . $menu->id . '" value="eliminar"><i class="far fa-trash-alt"></i></button>
                            </div>
                            <div class="col-12 col-sm-6 col-md-6 col-xs-6">
                            <button type = "submit" title="Eliminar" class="btn btn-accion btn-tool" data-id="' . $menu->id . '" value="editar"><i class="fas fa-pencil-alt"></i></button>
                            </div>
                       </div>';
        foreach ($menueshijos as $d => $menuh) {
            if ($menuh->padre_id == $menu->id) {
                $respuesta .= '<div class="col-12 col-sm-12 col-md-12 col-xs-12">
                                                  <div class="card">
                                                    <div class="card-header">
                                                    <h3 class="card-title">Submenus disponibles</h3>

                                                        <div class="card-tools">
                                                          <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                                            <i class="fas fa-minus"></i>
                                                          </button>
                                                        </div>
                                                    </div>
                                                    <div class="card-body p-0">';

                $respuesta .= '<div class="col-12 col-sm-12 col-md-12">
                                                <div class="info-box mb-3">
                                                     <span class="info-box-icon elevation-1" ' . BACKGROUND_BOXES . '><i class="' . $menuh->icono . '"></i></span>
                                                    <div class="info-box-content">
                                                        <div class="info-box-content">
                                                          <span class="info-box-text">Opción: ' . $menuh->nombre . '</span>
                                                          <span class="info-box-number">Url: ' . $menuh->url . '</span>
                                                          <span class="info-box-text">Estado: ' . mostrarEstilos($menuh->estado) . '</span>
                                                          <span class="info-box-text">
                                                            <div class="row">
                                                                <div class="col-12 col-sm-6 col-md-6 col-xs-6">
                                                                <button type = "submit" title="Editar" class="btn btn-accion btn-tool" data-id="' . $menuh->id . '" value="eliminar"><i class="far fa-trash-alt"></i></button>
                                                                </div>
                                                                <div class="col-12 col-sm-6 col-md-6 col-xs-6">
                                                                <button type = "submit" title="Eliminar" class="btn btn-accion btn-tool" data-id="' . $menuh->id . '" value="editar"><i class="fas fa-pencil-alt"></i></button>
                                                                </div>
                                                           </div>';
                $respuesta .= boxesRamas($menueshijos, $menuh->id);
                $respuesta .= '</div>
                 </div>
            </div>
        </div>';
                $respuesta .= '</div>
                        </div>
                        </div>';
            }
        }

        $respuesta .= '</div>
                 </div>
            </div>
        </div>';
    }
    return $respuesta;
}

function mostrarTotalesOrdenes($totales) {

    $respuesta = '<div class="row small" style="line-height:0.25">';
    $respuesta .= '<div class="col-md-12 col-lg-12 col-xl-5"><table class="table table-striped text-center">
            <tbody>
                <tr>
                    <th rowspan="2">Total  <br/><br/><br/><br/> Ordenes</th>
                    <th colspan="3">Caja</th>
                    <th rowspan="2"></th>
                    <th colspan="3">Bonchados</th>
                    <th rowspan="2"></th>
                    <th colspan="3">Vestidas</th>
                </tr>
                <tr>
                    <th width="10%"><i class="fas fa-check"></i></th>
                    <th width="10%">Parcial</th>
                    <th width="10%"><i class="fas fa-times"></i></th>
                    <th width="10%"><i class="fas fa-check"></i></th>
                    <th width="10%">Parcial</th>
                    <th width="10%"><i class="fas fa-times"></i></th>
                    <th width="10%"><i class="fas fa-check"></i></th>
                    <th width="10%">Parcial</th>
                    <th width="10%"><i class="fas fa-times"></i></th>
                </tr>
                <tr>
                    <th>' . $totales['totalOrdenes'] . '</th>
                    <th>' . $totales['totalOrdenesTotalmenteAsignadasEnCaja'] . '</th>
                    <th>' . $totales['totalOrdenesParcialmenteEnCaja'] . '</th>
                    <th>' . $totales['totalOrdenesSinCaja'] . '</th>
                    <th>&nbsp;</th>
                    <th>' . $totales['totalOrdenesTotalmentePreparadas'] . '</th>
                    <th>' . $totales['totalOrdenesParcialmentePreparadas'] . '</th>
                    <th>' . $totales['totalOrdenesSinPreparar'] . '</th>
                    <th>&nbsp;</th>
                    <th>' . $totales['totalOrdenesTotalmenteTerminadas'] . '</th>
                    <th>' . $totales['totalOrdenesParcialmenteTerminadas'] . '</th>
                    <th>' . $totales['totalOrdenesSinTerminar'] . '</th>
                </tr>
            </tbody>
        </table></div>';

    if ($totales['totalCajas']) {
        $totalCajas = 0;
        $totalCajasEmpacadas = $totalCajasConKardex = 0;
        $totalTipos = sizeof($totales['totalCajas']);
        $cabeceras = array();
        $cantidades = array();
        $empacados = array();
        $con_kardex = array();
        $i = 0;
        foreach ($totales['totalCajas'] as $caja) {
            $cabeceras[$i] = $caja->nombre;
            $cantidades[$i] = $caja->count;
            $empacados[$i] = $caja->cajas_empacadas;
            $con_kardex[$i] = $caja->cajas_con_kardex;
            $totalCajas += $caja->count;
            $totalCajasEmpacadas += $caja->cajas_empacadas;
            $totalCajasConKardex += $caja->cajas_con_kardex;
            $i++;
        }

        $respuesta .= '<div class="offset-md-0 col-md-12 offset-lg-0 col-lg-12 offset-xl-1 col-xl-6 mt-3 border-top"><table class="table table-striped text-center"><tbody>';
        if (array_sum($empacados) > 0 && array_sum($con_kardex) > 0) {
            $respuesta .= '<tr><th></th><th>Empacados</th><th>Con Kardex</th><th>Total</th></tr>';
        } else {
            if (array_sum($empacados) == 0 && array_sum($con_kardex) == 0) {
                $respuesta .= '<tr><th></th><th>Total</th></tr>';
            } else {
                if (array_sum($empacados) == 0 && array_sum($con_kardex) > 0) {
                    $respuesta .= '<tr><th></th><th>Con Kardex</th><th>Total</th></tr>';
                } else {
                    $respuesta .= '<tr><th></th><th>Empacados</th><th>Total</th></tr>';
                }
            }
        }
        foreach ($cabeceras as $ord => $cab) {
            $respuesta .= '<tr>';
            $respuesta .= '<th class="text-right"> ' . $cab . '</th>';
            if (array_sum($empacados) > 0) {
                $respuesta .= '<th> ' . $empacados[$ord] . '</th>';
            }
            if (array_sum($con_kardex) > 0) {
                $respuesta .= '<th> ' . $con_kardex[$ord] . '</th>';
            }
            $respuesta .= '<th> ' . $cantidades[$ord] . '</th>';
            $respuesta .= '</tr>';
        }
        $respuesta .= '</tbody></table></div>';

        /* $respuesta .= '
          <table class="table table-striped text-center col-12 col-md-9">
          <tbody>
          <tr>
          <th rowspan="2"><br/><br/><br/><br/> Cajas</th>
          <th colspan="' . $totalTipos . '">Tipo Caja</th>
          </tr>
          <tr>';

          foreach ($cabeceras as $cab) {
          $respuesta .= '<th> ' . $cab . '</th>';
          }
          $respuesta .= '
          </tr>
          <tr>
          <th>Total: ' . $totalCajas . '</th>';

          foreach ($cantidades as $tot) {
          $respuesta .= '<td> ' . $tot . '</td>';
          }
          $respuesta .= '
          </tr>
          <tr>
          <th>Empacados: ' . $totalCajasEmpacadas . '</th>';

          foreach ($empacados as $tot) {
          $respuesta .= '<td> ' . $tot . '</td>';
          }
          $respuesta .= '
          </tr>
          <tr>
          <th>Con Kardex: ' . $totalCajasConKardex . '</th>';

          foreach ($con_kardex as $tot) {
          $respuesta .= '<td> ' . $tot . '</td>';
          }
          $respuesta .= '
          </tr>
          </tbody>
          </table>'; */
    }

    $respuesta .= '   </div>';
    return $respuesta;
}

function mostrarTotalShipping($total_shipping) {
    $respuesta = '<br>';
    $respuesta .= '<div class="row small" style="line-height:0.25" >';
    $respuesta .= '<div class="col-md-12 col-lg-12 col-xl-5"><table class="table table-striped text-center">
            <thead>
                <tr>
                    <th colspan="5">Documentacion de visa </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th>#</th>
                    <th>Finca</th>
                    <th>Numero Guia</th>
                    <th >Fecha Carguera</th>
                    <th ></th>
                </tr>';
    if ($total_shipping) {
        foreach ($total_shipping as $k => $shipping) {
            $respuesta .= '<tr>
                            <th>' . ($k + 1) . '</th>
                            <th>' . $shipping->nombre . '</th>
                            <th>' . $shipping->numero_guia . '</th>
                            <th>' . convertirDateString(date_create($shipping->fecha_carguera), "Y-m-d") . '</th>
                            <th>
                                <button type = "button" class="btn btn-accion btn-tool" data-id=' . $shipping->nombre_master . ' value="visualizar"><i class="fas fa-file-pdf"></i></button>
                            </th>
                        </tr>';
        }
    }
    $respuesta .= '</tbody>
        </table></div>';

    $respuesta .= '   </div>';
    return $respuesta;
}

/* * ******************* CANTIDADES VARIANTE ************* */

/**
 * Analiza el sku para determinar su presentacion
 * @param type $variante_sku
 * @return boolean FALSE si no hay una respuesta correcta, el sku esta mal armado
 * @return arreglo
 *          divisor
 *          presentacion
 *          es_assemble
 */
function obtenerPresentacion($variante_sku) {
    $respuesta = false;
    //normalmente es AGR_P_012_80
    //la tercera posicion determina la cantidad
    $arr = explode("_", $variante_sku);
    if ($arr[2] == 'PET') {
        $respuesta = array(1, 'Petalos ' . $arr[4], true, $arr[3]);
    }
    if ($arr[3] == 'XXX') {
        $respuesta = array(1, 'Producto(s)', true, 1);
    } else {
        $cantidad = intval($arr[3]);
        if ($cantidad === 0) {
            $respuesta = array(1, 'item(s)', false, 1);
        } else if ($cantidad === 1) {
            $respuesta = array(1, 'tallo(s)', false, $cantidad);
        } else if ($cantidad % 25 === 0) {
            $respuesta = array(25, 'Bunch(es)_25', false, $cantidad);
        } else if ($cantidad === 30) {
            $respuesta = array(15, 'Bunch(es)_15', false, $cantidad);
        } else if ($cantidad === 36) {
            $respuesta = array(18, 'Bunch(es)_18', false, $cantidad);
        } else if ($cantidad % 12 === 0) {
            $respuesta = array(12, 'Docena(s)', false, $cantidad);
        } else if ($cantidad === 18) {
            $respuesta = array(18, 'Bunch(es)_18', false, $cantidad);
        } else if ($cantidad === 16) {
            $respuesta = array(16, 'Bunch(es)_16', false, $cantidad);
        }
    }
    return $respuesta;
}

/* * ****************** PROPIEDADES DE ORDEN *************** */

function quitarPrecios($v) {
    $pos = strpos($v, '(');
    if ($pos) {
        $v = substr($v, 0, $pos);
    }
    $pos = strpos($v, '[');
    if ($pos) {
        $v = substr($v, 0, $pos);
    }
    $v = trim($v);
    return $v;
}

/**
 * Analiza las propiedades de un item para mostrarlo u ocultar en base a los parametros
 * @param type $prop orden_item_propiedad
 * @param type $soloVisibles falso por default
 * @param type $resumido falso por default
 * @return boolean o el mismo orden_item_propiedad con campos adicionales
 */
function analizarPropiedad($prop, $soloVisibles = false, $resumido = false) {
    $propiedad = clone $prop;
    $propiedad->cantidad = false;
    $visible = false;
    if (
            (strpos($propiedad->info_propiedad_nombre, "AGR_") !== false) ||
            ($propiedad->propiedad_id == 18) || ($propiedad->propiedad_id == 244) || ($propiedad->propiedad_id == 372) || ($propiedad->propiedad_id == 373) || ($propiedad->propiedad_id == 398) || //florero
            ($propiedad->propiedad_id == 48) || ($propiedad->propiedad_id == 67) || ($propiedad->propiedad_id == 158) || //instrucciones
            ($propiedad->propiedad_id == 12) || //message
            ($propiedad->propiedad_id == 11) || ($propiedad->propiedad_id == 239) || ($propiedad->propiedad_id == 371) || //petalos
            ($propiedad->propiedad_id == 10) || ($propiedad->propiedad_id == 235) || ($propiedad->propiedad_id == 236) || ($propiedad->propiedad_id == 394) //wrap envoltura
    ) {
        $visible = true;
    }
    if ($soloVisibles && !$visible) {
        return false;
    }

    $propiedad->valor_original = $propiedad->valor;

    if ($propiedad->propiedad_id == 12) {//mensaje
        if ($resumido) {
            $propiedad->valor = "Personalizado";
        } else {
            $propiedad->valor = mensajeHtml($propiedad->valor);
        }
    }

    if ($propiedad->propiedad_id == 18 || $propiedad->propiedad_id == 372 || $propiedad->propiedad_id == 373) {//florero
        $v = quitarPrecios($propiedad->valor);
        if (strpos(strtoupper($v), 'NO') !== false) {
            if ($resumido) {
                return false;
            }
            $propiedad->valor = 'Sin Florero';
        } else {
            $propiedad->valor = 'Si';
        }
    }

    if (($propiedad->propiedad_id == 10) || ($propiedad->propiedad_id == 235) || ($propiedad->propiedad_id == 236)) {//wrap envoltura
        $v = quitarPrecios($propiedad->valor);
        if ((strpos(strtoupper($v), 'NO') !== false) || (strpos(strtoupper($v), 'SIN') !== false)) {
            if ($resumido) {
                return false;
            }
            $propiedad->valor = 'Sin Wrap';
        } else if ((strpos(strtoupper($v), 'LUXURY') !== false)) {
            $propiedad->valor = 'Luxury';
        } else {
            $propiedad->valor = 'Standard';
        }
    }
    if ($propiedad->propiedad_id == 11 || $propiedad->propiedad_id == 371) {//petalos
        $v = quitarPrecios($propiedad->valor);

        if ((strpos(strtoupper($v), 'LOOSE') !== false) || (strpos(strtoupper($v), 'WITHOUT') !== false) || (strpos(strtoupper($v), 'NONE') !== false) || (strpos(strtoupper($v), 'NO') !== false)) {
            return false;
        }
        error_log(print_r("Petalos<br/>", true));
        error_log(print_r($propiedad, true));
        //formato normalmente es XXX petals / color
        $arr_cantidad = explode(" ", $propiedad->valor);
        $cantidad = $arr_cantidad[0];
        if (is_numeric($cantidad)) {

            error_log(print_r("Cantidad<br/>" . $cantidad, true));
            if (strpos(strtoupper($v), 'ALL COLOR') !== false) {
                $propiedad->valor = 'ALL COLORS';
            } else if (strpos(strtoupper($v), 'RED') !== false) {
                $propiedad->valor = 'RED';
            } else if (strpos(strtoupper($v), 'WHITE') !== false) {
                $propiedad->valor = 'WHITE';
            } else {
                $propiedad->valor = 'NO DEFINIDO';
            }
            error_log(print_r("Valor<br/>" . $propiedad->valor, true));
            $propiedad->cantidad = $cantidad / 150;
            $propiedad->valor .= " (150 petalos)";
        }
        error_log(print_r($propiedad, true));

        /*
          $pos = strpos($v, '/');
          if ($pos) {
          $arr_cantidad = explode(" ", $propiedad->valor);
          $cantidad = $arr_cantidad[0];//trim(substr($v, 0, $pos));
          print_r($cantidad);

          if ((strpos(strtoupper($v), 'LOOSE') !== false)||(strpos(strtoupper($v), 'WITHOUT') !== false)||(strpos(strtoupper($v), 'NONE') !== false)) {
          return false;
          }

          //            $propiedad->valor = " * " . $cantidad;
          $propiedad->cantidad = $cantidad/150;
          $propiedad->valor .=" x(150 petalos)";
          } else {
          $color = '';
          if (strpos(strtoupper($v), 'ALL COLOR') !== false) {
          $color = 'ALL COLORS';
          } else if (strpos(strtoupper($v), 'RED') !== false) {
          $color = 'RED';
          } else if (strpos(strtoupper($v), 'WHITE') !== false) {
          $color = 'WHITE';
          }

          if ($color == ''){
          $propiedad->valor = "_".$v;
          }
          else {
          $arr_cantidad = explode("_", $propiedad->valor);
          $propiedad->valor = $color." * ".$arr_cantidad[0];
          }
          //normalmente la cantidad es lo primero 300 red

          } */

//        $propiedad->valor = $cantidad;
    }
    return $propiedad;
}

function convertir_utf8_ansi($valor = '') {

    $utf8_ansi2 = array(
        "\u00c0" => "À",
        "\u00c1" => "Á",
        "\u00c2" => "Â",
        "\u00c3" => "Ã",
        "\u00c4" => "Ä",
        "\u00c5" => "Å",
        "\u00c6" => "Æ",
        "\u00c7" => "Ç",
        "\u00c8" => "È",
        "\u00c9" => "É",
        "\u00ca" => "Ê",
        "\u00cb" => "Ë",
        "\u00cc" => "Ì",
        "\u00cd" => "Í",
        "\u00ce" => "Î",
        "\u00cf" => "Ï",
        "\u00d1" => "Ñ",
        "\u00d2" => "Ò",
        "\u00d3" => "Ó",
        "\u00d4" => "Ô",
        "\u00d5" => "Õ",
        "\u00d6" => "Ö",
        "\u00d8" => "Ø",
        "\u00d9" => "Ù",
        "\u00da" => "Ú",
        "\u00db" => "Û",
        "\u00dc" => "Ü",
        "\u00dd" => "Ý",
        "\u00df" => "ß",
        "\u00e0" => "à",
        "\u00e1" => "á",
        "\u00e2" => "â",
        "\u00e3" => "ã",
        "\u00e4" => "ä",
        "\u00e5" => "å",
        "\u00e6" => "æ",
        "\u00e7" => "ç",
        "\u00e8" => "è",
        "\u00e9" => "é",
        "\u00ea" => "ê",
        "\u00eb" => "ë",
        "\u00ec" => "ì",
        "\u00ed" => "í",
        "\u00ee" => "î",
        "\u00ef" => "ï",
        "\u00f0" => "ð",
        "\u00f1" => "ñ",
        "\u00f2" => "ò",
        "\u00f3" => "ó",
        "\u00f4" => "ô",
        "\u00f5" => "õ",
        "\u00f6" => "ö",
        "\u00f8" => "ø",
        "\u00f9" => "ù",
        "\u00fa" => "ú",
        "\u00fb" => "û",
        "\u00fc" => "ü",
        "\u00fd" => "ý",
        "\u00ff" => "ÿ");

    return strtr($valor, $utf8_ansi2);
}

function mensajeHtml($mensaje, $html = false) {
//    error_log("MENSAJE: ");
//    error_log(print_r($mensaje, true));
    $mensaje = nl2br($mensaje);
    if ($html) {
        $mensaje = decodeEmoticons($mensaje);
    }
//    error_log(print_r($mensaje, true));
    $mensaje = convertir_utf8_ansi($mensaje);
//    error_log(print_r($mensaje, true));
    $mensaje = html_entity_decode($mensaje);
//    error_log(print_r($mensaje, true));
    $mensaje = str_replace(array("\\r\\n", "\r\n", "\r", "\n", "\\n"), "<br />", $mensaje);
    $mensaje = str_replace(array("\\", "ufe0f"), "", $mensaje); //eliminamos caracter especial \
    $mensaje = str_replace(array("u2019", "&#8217;", "&#x2019;", "u201C", "&#8220;", "&#x201c;"), "'", $mensaje); //eliminamos caracter especial \
//    error_log(print_r($mensaje, true));
    $mensaje = str_replace(array("u2764", "&#10084;", "&#x2764;"), '<img src="assets/img/heart_red.png" alt="Red Heart on Apple iOS 14.2" width="12" height="12">', $mensaje);
    $mensaje = str_replace(array("u2665", "&#9829;", "&#x2665;", "u2764", "&#10084;", "&#x2764;"), '<img src="assets/img/heart_black.png" alt="Red Heart on Apple iOS 14.2" width="12" height="12">', $mensaje);
    $mensaje = str_replace(array("u2764", "&#10084;", "&#x2764;"), '<img src="assets/img/heart_red.png" alt="Red Heart on Apple iOS 14.2" width="12" height="12">', $mensaje);
    //$mensaje = str_replace(array("u2764", "&#10084;", "&#x2764;"), '<img src="assets/img/heart_red.png" alt="Red Heart on Apple iOS 14.2" width="12" height="12">', $mensaje);
//    error_log(print_r($mensaje, true));
//                                $mensaje = emoji_unified_to_html($mensaje);

    return $mensaje;
}

/* * ****************** EMOJI ************************ */

function userTextEncode($str) {
    if (!is_string($str))
        return $str;
    if (!$str || $str == 'undefined')
        return '';

    $text = json_encode($str); // exposes Unicode
    $text = preg_replace_callback("/(\\u[ed][0-9a-f]{3})/i", function ($str) {
        return addslashes($str[0]);
    }); // The rule here adds d to the original answer because I find that many of my Emoji actually begin with  ud, but I haven't found  UE for the time being.
    return json_decode($text);
}

/**
  Decode the above escalation
 */
function userTextDecode($str) {
    $text = json_encode($str); // exposes Unicode
    $text = preg_replace_callback("/\\\\/i", function ($str) {
        return '\\';
    }, $text); /// Turn two slashes into one, and the rest will not move.
    return json_decode($text);
}

function decodeEmoticons($src) {
    $replaced = preg_replace("/\\\\u([0-9A-F]{1,4})/i", "&#x$1;", $src);
    $result = mb_convert_encoding($replaced, "UTF-16", "HTML-ENTITIES");
    $result = mb_convert_encoding($result, 'utf-8', 'utf-16');
    return $result;
}

function reemplazarPunto($palabra, $cual) {
    $exploded_string = explode('.', $palabra);
    $new_string = implode($cual, $exploded_string);
    return $new_string;
}

function procesarListaOrdenes($id, $grupo, $arr, $alias, $max = 10, $oculto = true, $con_div = true, $con_a = true) {
    $id = reemplazarPunto($id, "_");
    $grupo = reemplazarPunto($grupo, "_");
    $str = "";
    if ($con_div) {
        $str = "
        <div class='col-12 listado_ordenes_expandible " . ($oculto ? 'collapse' : '') . " pr-4 text-left' id='det_items_" . $id . "_" . $grupo . "'>";
    }
    if (!empty($alias)) {
        $str .= "<b>" . $alias . ":</b>";
    }
    $cant = 0;
    if ($arr) {
        foreach ($arr as $k => $v) {
            $caja_id = false;
            if (strpos($k, '_') > 1) {
                $arr_k = explode('_', $k);
                if (array_key_exists(2, $arr_k)) {
                    $k = $arr_k[1]; //0 tienda_alias //1 orden_id //2 caja_id
                    $caja_id = $arr_k[2];
                }
            }

            $cant++;
            if ($con_a) {
                $str .= '<a  href="#modalOrden" class="btn btn-orden-numero" data-toggle="modal" data-target="#modalOrden" data-orden_id="' . $k . '" ' . ($caja_id ? 'data-caja_id="' . $caja_id . '"' : '') . ' data-variante_id="' . $id . '" style="text-align:left; font-size: 0.75em; padding:0">';
            }
            $str .= "<b>" . $v . "</b>";
            if ($con_a) {
                $str .= "</a>";
            }
        }
        if (sizeof($arr) > $cant) {
            $str .= "...";
        }
    }


    if ($con_div) {
        $str .= "</div>";
    }
    return $str;
}

function analizarColores($arr, $colores) {
    if (!empty($colores) && sizeof($colores) > 0) {
        foreach ($colores as $color) {
            if (array_key_exists($color, $arr)) {
                $arr[$color] = 1;
            }
        }
    }

    return $arr;
}
