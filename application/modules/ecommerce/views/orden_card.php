<?php
$items_card = '';
foreach ($detalle as $k => $deta) {
    if ($deta->caja_id == $id) {
        $items_card .= '<div class="col-11 offset-1 row small rounded ' . ($k % 2 === 0 ? "fila_par" : "fila_impar") . '">'
                . '<div class="col-12 text-left text-truncate">' . $deta->info_producto_titulo . '</div>'
                . '<div class="col-7 offset-1 text-left text-truncate">' . $deta->info_variante_titulo . '</div>'
                . '<div class="col-2 text-right">' . $deta->cantidad . '</div>'
                . '<div class="col-2 text-right">' . ($deta->preparado == 'S' ? '<i class="fas fa-spa col-12"></i>' : '') . '</div>'
                . '</div>';
        $items_card .= '</br>';
    }
}
?>
<?php
$clase_orden = "";
switch ($estado) {
    case ESTADO_ACTIVO: $color_estado = "grey";
        break;
    case ESTADO_ORDEN_CANCELADA: $color_estado = "black";
        $clase_orden = "orden_cancelada";
        break;
    case ESTADO_ERROR: $color_estado = "red";
        break;
//    case ESTADO_ORDEN_ACTUALIZADA: $color_estado = "red";
//        break;
//    case ESTADO_ORDEN_LOGISTICA: $color_estado = "red";
//        break;
//    case ESTADO_ORDEN_PREPARADA: $color_estado = "red";
//        break;
//    case ESTADO_ORDEN_EMPACADA: $color_estado = "red";
//        break;
    default:
        $color_estado = "red";
        break;
}


$todosPreparados = true;
$todosEnCaja = true;
$caja_id = array();
$total_items = $total_cajas = 0;
$pendiente_empacar = false;
if (sizeof($items) == 0) {
    $todosPreparados = false;
    $todosEnCaja = false;
}
foreach ($items as $item) {
    $total_items++;
    if ($item->preparado == 'N') {
        $todosPreparados = false;
    }
    if (!$item->cajas) {
        $todosEnCaja = false;
    } else {
        foreach ($item->cajas as $caja) {
            if (!key_exists($caja->id, $caja_id)) {
                $caja_id[$caja->id] = 1;
                $total_cajas++;
                if ($caja->empacada = 'N') {
                    $pendiente_empacar = true;
                }
            }
        }
    }
}
if ($estado != ESTADO_ORDEN_CANCELADA && $estado != ESTADO_ERROR) {
    $color_estado = false;
    if ($perfil == PANTALLA_LOGISTICA) {
        if ($todosPreparados) {
            $color_estado = "grey";
        }
    } else {
        if ($todosPreparados && $todosEnCaja) {
            $color_estado = "green";
        }
    }
}
if (isset($asignadoCaja)) {
    if ($asignadoCaja == 'S') {    //mostramos solo los asignadoCajas
        if (!$todosEnCaja) {
            return false;
        }
    } else if ($asignadoCaja == 'N') { //mostramos solo los que no estan asignadoCajas
        if ($todosEnCaja) {
            return false;
        }
    }
}
if (!$producto_filtro) {
    return false;
}
if (isset($filtro_tarjeta_impresa) && $filtro_tarjeta_impresa == 'N') {
    if ($impresiones > 0) {
        return false;
    }
}
?>
<div class="info-box color_tienda_<?= $tienda_id ?> <?= $clase_orden ?>" id="orden_<?= $id ?>"   style="<?= $color_estado ? 'background-color:' . $color_estado . ' !important;' : '' ?>">
    <span class="info-box-icon" style="width: 30%">
        <div class="row" style="text-align:left; margin-left: 1em;font-size: 0.80rem;">

            <a href="#modalOrden" class="btn btn-orden-numero" data-toggle="modal" data-target="#modalOrden"  data-orden_id="<?= $orden_id ?>" data-caja_id="<?= $id ?>" style="text-align:left; font-size: 1.00rem; padding:0">
                <b><?= $tienda_alias ?> <?= isset($referencia_order_number) ? $referencia_order_number : '' ?></b><br/>
                <b>ORDEN:<?= $orden_id ?></b><br/>
                <b>CAJA:<?= $id ?></b><br/>
            </a>
            <h6><b>
                    <?php if ($perfil == PANTALLA_LOGISTICA) { ?>
                        <button type = "button" class="btn btn-accion-logistica btn-tool col-12" id="btn-accion-logistica_<?= $id ?>" data-id="<?= $id ?>" value="meter_en_caja"  style="color:blue; text-align:left;padding:0;">
                        <?php } ?>
                        <i class="fas fa-box"></i> x <?= $total_cajas ?>&nbsp;&nbsp;
                        <?php if ($todosEnCaja) { ?>
                            <i class="fas fa-check"></i>
                        <?php } else {
                            ?>
                            <i class="fas fa-exclamation"></i>
                        <?php } ?>
                        <?php if ($perfil == PANTALLA_LOGISTICA) { ?>
                        </button>
                    <?php } ?>
                </b>
            </h6>

        </div>
    </span>
    <div class="info-box-content" style="margin-left: 0">

        <div class="row">
            <a href="#modalOrden" class="btn btn-orden-numero" data-toggle="modal" data-target="#modalOrden"  data-orden_id="<?= $orden_id ?>" data-caja_id="<?= $id ?>" style="text-align:left; font-size: 1.00rem; padding:0">
                <div class="col-12 text-left">
                    <small>
                        <h6><b>Entrega: <?= convertirFechaBD($fecha_entrega) ?></b></h6>
                        <h6><b> <?= $tag ?> </b></h6>
                        <b style="font-size: 0.75rem;">Destino: <?= $country . " | " . $state . " | " . $city ?><br/></b>
                        <b style="font-size: 0.75rem;">Customer: <?= $destinatario_nombre . " " . $destinatario_apellido . " " . $destinatario_company ?><br/></b>
                        <b>#items: </b> <?= sizeof($items) ?><br/>
                    </small>
                </div>
            </a> 
        </div>
        <div class="card-text small" >
            <?php echo $items_card; ?>
        </div>
    </div>
    <div class="info-box-content-right" style="margin-left: 0">

        <?= form_checkbox('orden_impresion', $orden_id . '_' . $id, ($estado == ESTADO_ACTIVO ? true : false)); ?>
        <button type = "button" class="btn btn-accion-orden btn-tool col-12" data-orden_id="<?= $orden_id ?>" data-caja_id="<?= $id ?>" value="imprimir_caja_and_traking">
            <i class="fas fa-print"></i>
        </button>
    </div>
</div>

