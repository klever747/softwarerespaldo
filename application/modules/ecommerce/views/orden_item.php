<div class="card-orden-item col-12 p-0" id="orden_item_<?= $item->id ?>" >
    <div class="card-orden-item-header ">
        <div class="card-header row" >
            <div class="col-12  row p-0 <?= ($perfil == PANTALLA_LOGISTICA) ? 'w-80' : 'w-100' ?>" style="font-size: 0.75rem;">
                <div class="row <?= ($perfil == PANTALLA_LOGISTICA) ? 'col-11' : 'col-7' ?>">                    
                    <div class="col-12 col-lg-7" data-toggle="collapse" data-target="#body_orden_item_<?= $item->id ?>" >
                        <b><?= strtoupper($item->info_producto_titulo) ?></b><br/><?= $item->id ?>
                        <?= strtoupper($item->info_variante_titulo) . " <br/> (" . $item->info_variante_sku . ")" ?>
                    </div>
                    <div class="row col-12 col-lg-5">
                        <div class="row col-12">
                            <div class="col-8 text-bold">Cantidad:</div>
                            <div class="col-4 text-right"><?= $item->cantidad * 1 ?></div>
                        </div>
                        <div class="row col-12">
                            <div class="col-8 text-bold">#Stems:</div>
                            <div class="col-4 text-right"><?= $item->totalStems ?></div>
                        </div>
                        <div class="card-tools">
                            <div class="col-12 text-bold">Imprimir:
                            <button type="button" class="btn btn-accion  btn-tool visible_<?=$no_editable?>" data-orden_id="<?= $item->orden_id ?>"  data-id="<?= $item->id ?>" data-producto="<?= $item->producto_id ?>" data-variante="<?= $item->variante_id ?>"  value="imprimir_item_orden"><i class="fas fa-file-pdf"></i></button>
                            </div>
                        </div>
                        <?php
                        if ($perfil == PANTALLA_LOGISTICA && $logistica === false) {
                            ?>
                            <div class="row col-12">
                                <div class="col-6 text-bold">Precio:</div>
                                <div class="col-6 text-right">
                                    <div class="input-group mb-3" id="item_precio_pen_<?= $item->id ?>">
                                        <label class="form-control text-right border-0"><?= $item->precio ?></label>
                                        <div class="input-group-append">
                                            <button class="btn btn-accion btn-tool visible_<?= $no_editable ?>" type="button" id="precio_edit_<?= $item->id ?>" data-item_id="<?= $item->id ?>" value="editar_precio"><i class="fas fa-pen"></i></button>
                                        </div>
                                    </div>
                                    <div class="input-group mb-3 d-none" id="item_precio_save_<?= $item->id ?>">                                            
                                        <?= form_input('precio_' . $item->id, $item->precio, array('id' => 'precio_' . $item->id, 'class' => 'soloNumerosDecimales form-control text-right')) ?>
                                        <div class="input-group-append">
                                            <button class="btn btn-accion btn-tool visible_<?= $no_editable ?>" type="button" id="precio_save_<?= $item->id ?>" data-item_id="<?= $item->id ?>" value="guardar_precio"><i class="fas fa-save"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                </div>
                <div class="<?= ($perfil == PANTALLA_LOGISTICA) ? 'col-1' : 'col-5' ?> text-center">
                    <?php
                    if ($perfil == PANTALLA_MANUFACTURA || $perfil == PANTALLA_PREPARACION || $perfil == PANTALLA_TERMINACION) {
                        if ($perfil == PANTALLA_MANUFACTURA || $perfil == PANTALLA_PREPARACION) {
                            ?>
                            <button type = "button" class="btn btn-accion btn-primary <?= ($item->preparado == 'N') ? 'pendiente' : 'listo' ?> w-100" id="marcar_item_preparado_<?= $item->id ?>" data-id="<?= $item->id ?>" data-producto="<?= $item->producto_id ?>" data-variante="<?= $item->variante_id ?>" value="<?= ($item->preparado == 'N') ? 'marcar' : 'desmarcar' ?>_item_preparado">Bonchado <?= ($item->preparado == 'N') ? '<i class="fas fa-times"></i>' : '<i class="fas fa-check"></i>' ?></button>
                            <?php
                        }
                        if ($perfil == PANTALLA_MANUFACTURA || $perfil == PANTALLA_TERMINACION) {
                            ?>
                            <button type = "button" class="btn btn-accion btn-primary <?= ($item->terminado == 'N') ? 'pendiente' : 'listo' ?> w-100" id="marcar_item_terminado_<?= $item->id ?>" data-id="<?= $item->id ?>" data-producto="<?= $item->producto_id ?>" data-variante="<?= $item->variante_id ?>" value="<?= ($item->terminado == 'N') ? 'marcar' : 'desmarcar' ?>_item_terminado">Vestido <?= ($item->terminado == 'N') ? '<i class="fas fa-times"></i>' : '<i class="fas fa-check"></i>' ?></button>
                            <?php
                        }
                    } else {
                        if (!$logistica) {
                            if (empty($item->orden_caja_id)) {
                                ?>
                                <button type = "button" class="btn btn-accion btn-tool visible_<?= $no_editable ?>" id="agregar_item_orden_<?= $item->id ?>" data-id="<?= $item->id ?>" value="agregar_item_a_caja"><i class="fas fa-box"></i></button>
                            <?php } ?>
                            <button type = "button" class="btn btn-accion btn-tool visible_<?= $no_editable ?>" id="eliminar_item_orden_<?= $item->id ?>" data-id="<?= $item->id ?>" value="eliminar_item_orden"><i class="far fa-trash-alt"></i></button>
                            <button type = "button" class="btn btn-accion btn-tool visible_<?= $no_editable ?>" id="editar_item_orden_<?= $item->id ?>" data-id="<?= $item->id ?>" value="editar_item_orden"><i class="fas fa-pencil-alt"></i></button>
                            <!--<button type="button" class="btn btn-tool" data-card-widget="collapse" data-target="#body_orden_item_<?= $item->id ?>" aria-expanded="true" aria-controls="body_orden_item_<?= $item->id ?>"><i class="fas fa-minus"></i></button>-->
                            <?php
                        } else {
                            if ($perfil == PANTALLA_LOGISTICA) {
                                ?>
                                <button type = "button" class="btn btn-accion btn-tool visible_<?= $no_editable ?>" id="sacar_item_orden_<?= $item->id ?>" data-id="<?= $item->id ?>" value="sacar_item_orden"><i class="far fa-trash-alt"></i></button>
                                <?php
                            }
                        }
                    }
                    ?>
                </div>
            </div>            
        </div>
        <div class="card-body collapse <?= ($perfil == PANTALLA_LOGISTICA) ? '' : 'show' ?>" data-parent="#orden_item_<?= $item->id ?>" id="body_orden_item_<?= $item->id ?>" style="padding-top: 0!important;">
            <div class="row">                
                <div class="col-12">
                    <?= print_r($item->propiedades, true) ?>
                </div>
            </div>
            <?php if (isset($item->receta)) { ?>
                <div class="row">
                    <div class="col-12 align-self-center text-left" data-toggle="collapse" data-target="#body_receta_<?= $item->id ?>">Receta</div>
                    <div class="col-12 collapse" data-parent="#body_orden_item_<?= $item->id ?>" id="body_receta_<?= $item->id ?>" >
                        <table class="table">
                            <thead>
    <!--                            <th>SKU</th>-->
                            <th>Ingrediente</th>
                            <th>Descripci&oacute;n</th>
                            <th>Cantidad</th>
                            </thead>
                            <tbody>
                                <?php foreach ($item->receta as $receta) {
                                    ?>
                                    <tr>
                                        <!--<td><?= $receta->sku ?></td>-->
                                        <td><?= $receta->nombre . "(" . $receta->sku . ")" ?></td>
                                        <td><?= $receta->descripcion ?></td>
                                        <td><?= $receta->cantidad ?></td>
                                    </tr>
                                    <?php
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <hr>
            <?php } ?>
        </div>
    </div>
</div>


<script>
    function recargarPrincipalConMensaje(r) {
        console.log("recargarPrincipalConMensaje");
        mostrarExito(r.mensaje);
//        if (typeof refrescar_div_producto === "function")
//        {
//          refrescar_div_producto();
//        }
        recargarPrincipal();
    }
    function respuestaGenTarjMen(r) {
        console.log(r);
        if (analizarRespuesta(r) && (r.ruta_pdf != '')) {
            console.log("ruta a abrir es " + r.ruta_pdf);
            window.open(r.ruta_pdf, '_blank');
           
        }
    }
    $("body").delegate(".card-orden-item .btn-accion", "click", function (e) {
        console.log("En el boton");
        e.preventDefault();
        e.stopImmediatePropagation();
        if (!unsoloclick()) {
            console.log("No hacemos el boton");
            return false;
        }
//        if (!$(this).prop('disabled')) {
//            $(".btn-accion").prop('disabled', true);
//            setTimeout(function () {
//                console.log('quitamos lo deshabilitado xxx');
//                $(".btn-accion").removeAttr('disabled');
//            }, 1000);
        console.log($(this).attr('id'));
        if ($(this).val() === "eliminar_item_orden") {
            llamadaAjax($(this).attr('id'), '<?= base_url() ?>ecommerce/eliminar_item_orden', {"orden_item_id": $(this).data('id')}, recargarPrincipal);
        } else if ($(this).val() === "editar_item_orden") {
            llamadaAjax($(this).attr('id'), '<?= base_url() ?>ecommerce/editar_item_orden', {"orden_item_id": $(this).data('id')}, mostrarEdicionOrdenItem);
        } else if ($(this).val() === "sacar_item_orden") {
            llamadaAjax($(this).attr('id'), '<?= base_url() ?>produccion/logistica/orden_item_sacar_caja', {"orden_item_id": $(this).data('id')}, recargarPrincipal);
        } else if ($(this).val() === "agregar_item_a_caja") {
            llamadaAjax($(this).attr('id'), '<?= base_url() ?>produccion/logistica/orden_item_editar_caja', {"orden_item_id": $(this).data('id')}, mostrarEdicion);
        } else if ($(this).val() === "editar_precio") {
            console.log("#item_precio_edit_" + $(this).data('item_id'));
            $("#item_precio_pen_" + $(this).data('item_id')).addClass("d-none");
            $("#item_precio_save_" + $(this).data('item_id')).removeClass("d-none");
        } else if ($(this).val() === "guardar_precio") {
            llamadaAjax($(this).attr('id'), '<?= base_url() ?>ecommerce/orden/guardar_precio', {"orden_item_id": $(this).data('item_id'), "precio_actualizado": $('#precio_' + $(this).data('item_id')).val()}, recargarPrincipalConMensaje);
        } else if ($(this).val() === "marcar_item_preparado") {
            producto_id = $(this).data('producto');
            variante_id = $(this).data('variante');
            llamadaAjax($(this).attr('id'), '<?= base_url() ?>manufactura/bonchado_marcar', {"orden_item_id": $(this).data('id')}, recargarPrincipalConMensaje);
        } else if ($(this).val() === "desmarcar_item_preparado") {
            producto_id = $(this).data('producto');
            variante_id = $(this).data('variante');
            llamadaAjax($(this).attr('id'), '<?= base_url() ?>manufactura/bonchado_desmarcar', {"orden_item_id": $(this).data('id')}, recargarPrincipalConMensaje);
        } else if ($(this).val() === "marcar_item_terminado") {
//            producto_id = $(this).data('producto');
//            variante_id = $(this).data('variante');
            llamadaAjax($(this).attr('id'), '<?= base_url() ?>manufactura/vestido_marcar', {"orden_item_id": $(this).data('id')}, recargarPrincipalConMensaje);
        } else if ($(this).val() === "desmarcar_item_terminado") {
//            producto_id = $(this).data('producto');
//            variante_id = $(this).data('variante');
            llamadaAjax($(this).attr('id'), '<?= base_url() ?>manufactura/vestido_desmarcar', {"orden_item_id": $(this).data('id')}, recargarPrincipalConMensaje);
        }else if($(this).val() === "imprimir_item_orden"){
            producto_id = $(this).data('producto');
            llamadaAjax($(this).attr('id'), '<?= base_url() ?>manufactura/imprimir_data_items', {"filtro": filtroActual,"variante_id": $(this).data('variante') ,"orden_id": $(this).data('orden_id'),"producto_id": producto_id,"orden_item_id": $(this).data('id')}, respuestaGenTarjMen);
        }

//        }
    });

//    $("body").delegate(".btn-accion-orden-item-propiedad", "click", function (e) {
//        console.log("En el boton");
//        e.preventDefault();
//        e.stopImmediatePropagation();
//        if (!unsoloclick()) {
//            console.log("No hacemos el booton");
//            return false;
//        }
//
//        if ($(this).val() === "eliminar") {
//            llamadaAjax(false, '<?= base_url() ?>ecommerce/orden/orden_item_propiedad_eliminar', {"orden_item_propiedad_id": $(this).data('id')}, recargarPrincipal);
//        } else if ($(this).val() === "editar") {
//            llamadaAjax(false, '<?= base_url() ?>ecommerce/orden/orden_item_propiedad_editar', {"orden_item_propiedad_id": $(this).data('id'), "orden_item_id": <?= $orden_item_id ?>}, mostrarEdicion);
//        } else if ($(this).val() === "agregar") {
//            llamadaAjax(false, '<?= base_url() ?>ecommerce/orden/orden_item_propiedad_nuevo', {"orden_item_id": }, mostrarEdicion);
//        }
//    });
</script>