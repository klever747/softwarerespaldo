<div class="card card-default" id="orden_detalle_items">
    <div class="card-header">
        <h6 class="card-title" data-toggle="collapse" data-target="#det_items">Items de la orden</h6>        
        <div class="card-tools">
            <button type="button" class="btn btn-crear-orden-item btn-tool visible_<?= $no_editable ?>" data-id="<?= $orden->id ?>" value="agregar_item_orden"><i class="fas fa-plus-circle"></i></button>
            <!--<button type="button" class="btn btn-tool" data-card-widget="collapse" data-parent="#orden_detalle_items" href="#det_items"><i class="fas fa-minus"></i></button>-->
        </div>
    </div>
    <div class="card-body <?= !($perfil == PANTALLA_LOGISTICA || $perfil == PANTALLA_PREPARACION || $perfil == PANTALLA_TERMINACION || $perfil == PANTALLA_MANUFACTURA) ? 'collapse' : '' ?>" id="det_items">
        <?php
        if ($orden_items) {
            foreach ($orden_items as $k => $item) {
                $clase = "color_" . ($k % 2 == 0) ? "par" : "impar";
                if ($perfil == PANTALLA_PREPARACION || $perfil == PANTALLA_MANUFACTURA) {
                    if ($item->preparado == "S") {
                        $clase .= " orden_item_preparado";
                    }
                }
                if ($perfil == PANTALLA_TERMINACION || $perfil == PANTALLA_MANUFACTURA) {
                    if ($item->terminado == "S") {
                        $clase .= " orden_item_terminado";
                    }
                }
                ?>
                <div class="row p-0 <?= $clase ?> orden_item">
                    <?= $item->card ?>
                </div>
                <?php
            }
        }
        ?>
    </div>
</div>

<script>
    $("body").delegate("#orden_detalle_items .btn-crear-orden-item", "click", function (e) {
        unsoloclick('.btn-crear-orden-item');
        llamadaAjax(false, '<?= base_url() ?>ecommerce/nuevo_item_orden', {"orden_id": $(this).data('id')}, mostrarEdicionOrdenItem);
//        console.log("En el boton");
//        e.preventDefault();
//        e.stopImmediatePropagation();
//        if (!unsoloclick()) {
//            console.log("No hacemos el boton");
//            return false;
//        }
//        if (!$(this).prop('disabled')) {
//            $(".btn-accion").prop('disabled', true);
//            setTimeout(function () {
//                console.log('quitamos lo deshabilitado orden_detalle_items');
//                $(".btn-accion").removeAttr('disabled');
//            }, 1000);
//
//            if ($(this).val() === "agregar_item_orden") {
//                llamadaAjax(false, '<?= base_url() ?>ecommerce/nuevo_item_orden', {"orden_id": $(this).data('id')}, mostrarEdicion);
//            }
//        }
    });
    aplicarSoloNumerosDecimales();
</script>