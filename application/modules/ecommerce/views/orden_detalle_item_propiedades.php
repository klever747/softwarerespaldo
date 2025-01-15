<?php
header('Content-type: text/html; charset=UTF-8');
?>
<div class="card-body text-center" id="orden_item_propiedad_<?= $orden_item_id ?>">
    <div class="row">
        <table class="table tabla_propiedades">
            <thead>
                <tr>
                    <th width='20%'>Propiedad</th>
                    <th width='60%'>Valor/Cantidad</th>
                    <?php if ($perfil == PANTALLA_LOGISTICA) { ?>
                        <th><button type="button" class="btn btn-accion-orden-item-propiedad btn-tool visible_<?= $no_editable ?>" data-id="<?= $orden_item_id ?>" value="agregar"><i class="fas fa-plus-circle"></i></button></th>
                    <?php } ?>
                </tr>
            </thead>
            <tbody id="orden_item_propiedad_<?= $orden_item_id ?>_det">
                <?php
                if ($propiedades) {
                    foreach ($propiedades as $k => $propiedad) {
                        if (!(strpos(strtoupper($propiedad->info_propiedad_nombre), '_') === 0) && !(strpos(strtoupper($propiedad->info_propiedad_nombre), 'DATE') === 0) && !(strpos(strtoupper($propiedad->info_propiedad_nombre), 'FATHER') === 0) && !(strpos(strtoupper($propiedad->info_propiedad_nombre), 'MAIN') === 0) && !(strpos(strtoupper($propiedad->info_propiedad_nombre), 'ACCESORIOS') === 0)) {
                            if ($perfil == PANTALLA_LOGISTICA) {
                                $prop = analizarPropiedad($propiedad, false, false);
                            } else {
                                $prop = analizarPropiedad($propiedad, true, true);
                            }
                            if (!$prop) {
                                $prop = new stdClass();
                                $prop->info_propiedad_descripcion = $propiedad->info_propiedad_nombre;
                                $prop->valor = $propiedad->valor;
                            }
                            if ($prop) {
                                if (strpos($propiedad->info_propiedad_nombre, "AGR_") !== false) {
                                    $arr = explode("_", $propiedad->info_propiedad_nombre);
                                    $prop->info_propiedad_descripcion .= " (" . $propiedad->info_propiedad_nombre . ")";
                                    $prop->valor = $prop->valor * intval($arr[3]);
                                }
                                ?>
                                <tr class="<?= $k % 2 === 0 ? "fila_par" : "fila_impar" ?>">
                                    <td class="text-left col-3">
                                        <?= $prop->info_propiedad_descripcion ?>
                                    </td>
                                    <td class="col-7">
                                        <?= $prop->valor . " " . (property_exists($prop, "cantidad") && $prop->cantidad ? "x $prop->cantidad" : "") ?>
                                    </td>
                                    <td class="col-2">
                                        <?php if ($perfil == PANTALLA_LOGISTICA) { ?>
                                            <button type = "button" class="btn btn-accion-orden-item-propiedad btn-tool visible_<?= $no_editable ?>" data-id="<?= $propiedad->id ?>" data-dismiss="modal" value="eliminar"><i class="far fa-trash-alt"></i></button>
                                            <button type = "button" class="btn btn-accion-orden-item-propiedad btn-tool visible_<?= $no_editable ?>" data-id="<?= $propiedad->id ?>" data-orden_item_id="<?= $orden_item_id ?>" value="editar"><i class="fas fa-pencil-alt"></i></button>
                                            <?php } ?>
                                    </td>
                                </tr>
                                <?php
                            }
                        }
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    $("body").delegate(".btn-accion-orden-item-propiedad", "click", function (e) {
        console.log("En el boton");
        e.preventDefault();
        e.stopImmediatePropagation();
        if (!unsoloclick()) {
            console.log("No hacemos el booton");
            return false;
        }

        if ($(this).val() === "eliminar") {
            llamadaAjax(false, '<?= base_url() ?>ecommerce/orden/orden_item_propiedad_eliminar', {"orden_item_propiedad_id": $(this).data('id')}, recargarPrincipal);
        } else if ($(this).val() === "editar") {
            llamadaAjax(false, '<?= base_url() ?>ecommerce/orden/orden_item_propiedad_editar', {"orden_item_propiedad_id": $(this).data('id'), "orden_item_id": $(this).data('orden_item_id')}, mostrarEdicionPropiedad);
        } else if ($(this).val() === "agregar") {
            console.log($(this).data('id'));
            llamadaAjax(false, '<?= base_url() ?>ecommerce/orden/orden_item_propiedad_nuevo', {"orden_item_id": $(this).data('id')}, mostrarEdicionPropiedad);
        }
    });
    function mostrarEdicionPropiedad(r) {

        if (analizarRespuesta(r)) {
            $("#modalEdicion .modal-content").html(r.respuesta);
            $("#modalEdicion").modal("show");
            $('#texto').hide();
            $('#numero').hide();
            if ($('#valor_numero').val() == "") {
                $('#texto').show();
            } else {
                $('#numero').show();
            }
            if (r.orden_item) {
                $('#propiedad_id').val(r.orden_item.producto_id);
                $('#propiedad_id').select2().trigger('change');

                $('#propiedad_id').val(r.variante_id);
                $('#propiedad_id').select2().trigger('change');
            }
            //llenarSelect("propiedad_id", '<?= base_url() ?>ecommerce/producto/propiedades_select', {"orden_item_id": <?= isset($orden_item_id) ? $orden_item_id : 0 ?>}, false);
            llenarSelect("propiedad_id", '<?= base_url() ?>ecommerce/producto/propiedades_select', {"orden_item_id": (r.orden_item_id ? r.orden_item_id : 0)}, function () {
                $('#propiedad_id').select2().trigger('change');
            });
            $(".soloNumeros").inputFilter(function (value) {
                return /^-?\d*$/.test(value);
            });
        }
    }
</script>