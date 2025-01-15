<?php
$items = '';
$cajas_id = array();
$todosEnCaja = false;
if ($detalle) {

    $todosEnCaja = true;
    $fila = 0;
    if ($cajas) {
        foreach ($cajas as $det) {
            if ($det->tipo_caja_id == null) {
                $todosEnCaja = false;
            } else {
                if (!array_key_exists($det->id, $cajas_id)) {
                    $cajas_id[$det->id] = 0;
                }
                $cajas_id[$det->id] ++;
            }
            if ($det->id) {
                $items .= '<div class="row small rounded ' . ($fila % 2 === 0 ? "fila_par" : "fila_impar") . '">'
                        . '<div class="col-3 text-left text-truncate">Caja id: ' . $det->id . '</div>'
                        . '<div class="col-7 offset-1 text-left text-truncate"> Tracking #: ' . ($det->info_tracking_number ? $det->info_tracking_number : 'No') . '</div>'
                        . '<div class="col-5 text-right text-truncate"><i class="fas fa-box"></i> Tipo caja: ' . $det->info_nombre_caja . '</div>'
                        . '<div class="col-5 text-right text-truncate"><i class="fas fa-building"></i> Finca: ' . $det->info_nombre_finca . '</div>'
                        . '<div class="col-2 text-right"> <button type = "button" ' . ($det->info_tracking_number ? ' ' : 'disabled') . ' class="btn btn-accion-orden btn-tool " data-orden_id=' . $det->id . ' value="imprimir_tracking" data-toggle="tooltip" data-placement="bottom" title="Tracking"><i class="fas fa-print fa-sm"></i></button> </div>';

                foreach ($detalle as $deta) {
                    if ($deta->caja_id == $det->id) {
                        $items .= '<div class="col-11 offset-1 row small rounded ' . ($fila % 2 === 0 ? "fila_par" : "fila_impar") . '">'
                                . '<div class="col-12 text-left text-truncate">' . $deta->info_producto_titulo . '</div>'
                                . '<div class="col-7 offset-1 text-left text-truncate">' . $deta->info_variante_titulo . '</div>'
                                . '<div class="col-2 text-right">' . $deta->cantidad . '</div>'
                                . '<div class="col-2 text-right">' . ($deta->preparado == 'S' ? '<i class="fas fa-spa col-12"></i>' : '') . '</div>'
                                . '</div>';
                    }
                }

                $items .= '</div>';


                $items .= '<hr>';
                $fila++;
            }
        }
    }
    if ($detalle) {
        foreach ($detalle as $item_caja) {
            if (!$item_caja->caja_id) {
                $items .= '<div class="row small rounded ' . ($fila % 2 === 0 ? "fila_par" : "fila_impar") . '" style="background-color: red;"> '
                        . '<div class="col-12 text-left text-truncate">' . $item_caja->info_producto_titulo . '</div>'
                        . '<div class="col-7 offset-1 text-left text-truncate">' . $item_caja->info_variante_titulo . '</div>'
                        . '<div class="col-2 text-right">' . $item_caja->cantidad . '</div>'
                        . '<div class="col-2 text-right">' . ($item_caja->preparado == 'S' ? '<i class="fas fa-spa col-12"></i>' : '') . '</div>'
                        . '</div>';
                $items .= '<hr>';
                $todosEnCaja = false;
                $fila++;
            }
        }
    }
}
?>
<div class="card color_tienda_<?= $tienda_id ?> <?= (($estado == ESTADO_ORDEN_CANCELADA) ? "orden_cancelada" : (( $estado == ESTADO_ORDEN_REENVIADA || $estado == ESTADO_ORDEN_CLONADA) ? "orden_no_activa" : "")) ?>">
    <div class="card-header row col-12 pr-0 m-0 ">        
        <button class="btn btn-orden-numero row col-6" data-orden_id="<?= $id ?>"  >
            <div class="col-12 row">
<!--<a href="#modalOrden" class="btn btn-orden-card" data-orden_id="<?= $id ?>" data-toggle="modal" data-target="#modalOrden" >-->
                <b class="col-12 text-left"><?= $tienda_alias ?> <?= isset($referencia_order_number) ? $referencia_order_number : '' ?><?= $secuencial > 1 ? '-' . $secuencial : '' ?></b>
                <b class="col-12 text-left small">Ord. #<?= $id ?></b>
            </div>
        </button>
        <div class="row col-6">
            <div class="col-12 row pr-0">
                <button type = "button" id="btn-accion-logistica_<?= $id ?>" class="btn <?= ($perfil == PANTALLA_LOGISTICA ? 'btn-accion-logistica' : '') ?> btn-tool col-8" data-id="<?= $id ?>" value="meter_en_caja"  style="color:blue; text-align:right;" data-toggle="tooltip" data-placement="top" title="Empacar automaticamente">
                    <i class="fas fa-box fa-lg"></i> x<?= sizeof($cajas_id) . " " . ((sizeof($cajas_id) > 0 && $todosEnCaja) ? '<i class="fas fa-check"></i>' : '<i class="fas fa-exclamation"></i>') ?>
                </button>                
                <div class="col-4">
                    <?= form_checkbox('orden_impresion', $id, ($estado == ESTADO_ACTIVO ? true : false), array("class" => "col-6")) ?> x <?= $impresiones ?>
                </div>
            </div>
            <div class="col-12 row pr-0">

                <?php
                if ($estado == ESTADO_ACTIVO) {
                    ?>
                    <button type = "button" class="btn btn-accion-orden btn-tool col-4" data-orden_id="<?= $id ?>" value="imprimir_mensaje" data-toggle="tooltip" data-placement="bottom" title="Imprimir tarjeta">
                        <i class="fas fa-print fa-sm"></i>
                    </button>
                    <button type = "button" class="btn btn-accion-orden btn-tool col-4" data-orden_id="<?= $id ?>" value="imprimir_mensaje_eternizadas" data-toggle="tooltip" data-placement="bottom" title="Imprimir tarjeta Eternizadas">
                        <i class="fas fa-fingerprint"></i>
                    </button>
                    <button type = "button" class="btn btn-accion-orden btn-tool col-4" data-orden_id="<?= $id ?>" value="reenviar_orden" data-toggle="tooltip" data-placement="bottom" title="Reenviar">
                        <i class="fas fa-share-square fa-sm"></i>
                    </button>
                    <button  type = "button" class="btn btn-accion-orden btn-tool col-4" data-orden_id="<?= $id ?>" value="clonar_orden" data-toggle="tooltip" data-placement="bottom" title="Clonar">
                        <i class="far fa-clone"></i>
                    </button>
                    <?php
                }
                ?>
            </div>
        </div>
        <div class="row col-12 text-left">
            <?= $estado == ESTADO_ORDEN_CANCELADA ? "<h2 class='col-12'>CANCELADA</h2>" : "" ?>
            <?= $estado == ESTADO_ERROR ? "<h2 class='col-12'>ERROR</h2>" : "" ?>
            <?= ($reenvio_orden_id != null) ? "<div class='row col-12'><h2 class='col-5'>Reenvio</h2>   <p  class='col align-self-center m-0'>" . $reenvio_orden_id . "</p></div>" : "" ?>
            <?= ($clonacion_orden_id != null) ? "<h2 class='col-12'>Clonaci&oacute;n</h2>" : "" ?>
            <h6><b> <?= $tag ?> </b></h6>
        </div>
    </div>
    <div class="card-body pt-1 pb-1">
        <h6 class="card-title col-12 row">
            <p class="col-4 text-left"><?= $country_code . "-" . $state_code ?></p>
            <p class="col-8 text-left"><small>Entrega:</small> <?= convertirFechaBD($fecha_entrega) ?></p>
        </h6>            
        <div class="card-text small">
            <?= $items; ?>
        </div>
    </div>
</div>
<script>

</script>