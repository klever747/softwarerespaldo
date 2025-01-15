<div class="card-body text-center">
    <div class="card-header">
        <h3 class="card-title">Variantes</h3>
        <div class="card-tools">

            <?php if (!$sku) { ?>
                <button type="button" class="btn btn-accion_detalle btn-tool" value="agregar" data-producto_id="<?= $producto->id ?>" ><i class="fas fa-plus-circle"></i></button>
            <?php } ?>

        </div>
    </div>
    <div class="row">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <td class="text-left">Titulos</td>
                    <td class="text-left">SKU</td>
                    <td class="text-left">Estado</td>
                    <?php if ($sku) { ?>
                        <td class="text-left">Algoritmo</td>
                    <?php } ?>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($variantes) {
                    foreach ($variantes as $k => $variante) {
                        ?>
                        <tr>
                            <th scope="row"><?= $k + 1 ?></th>
                            <td class="text-left"><?= $variante->titulo ?></td>
                            <td class="text-left"><?= $variante->sku ?></td>
                            <td class="text-left">
                                <?= mostrarEstilos($variante->estado); ?>
                            </td>
                            <?php if ($sku) { ?>
                                <td class="text-left">
                                    <?php
                                    if (!empty($variante->tipo_algoritmo)) {
                                        echo $variante->tipo_algoritmo;
                                    } else {
                                        echo 'Sin algoritmo asignado';
                                    }
                                    ?>

                                </td>
                            <?php } ?>
                            <td>
                                <?php if ($sku) { ?>
                                    <button type = "button" class="btn btn-accion_detalle btn-tool" data-id="<?= $variante->id ?>" data-producto_id="<?= $variante->producto_id ?>" value="algoritmo"><i class="fas fa-cubes"></i></button>
                                <?php } else { ?>
                                    <button type = "button" class="btn btn-accion_detalle btn-tool" data-id="<?= $variante->id ?>" data-dismiss="modal" value="eliminar"><i class="far fa-trash-alt"></i></button>
                                    <button type = "button" id="editSku" class="btn btn-accion_detalle btn-tool" data-id="<?= $variante->id ?>" data-producto_id="<?= $variante->producto_id ?>" value="editar"><i class="fas fa-pencil-alt"></i></button>                                
                                    <button type = "button" id="editSku" class="btn btn-accion_detalle btn-tool" data-id="<?= $variante->id ?>" data-producto_id="<?= $variante->producto_id ?>" value="editar_receta"><i class="fas fa-scroll"></i></button>
                                    <?php } ?>
                            </td>
                        </tr>
                        <?php
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<script>

    $(document).on("click", "#modalEdicion .btn-accion_detalle", function () {
        if ($(this).val() === "eliminar") {
            llamadaAjax(false, '<?= base_url() ?>ecommerce/producto/variante_eliminar', {"id": $(this).data('id')}, recargarProducto);
        } else if ($(this).val() === "editar") {
            llamadaAjax(false, '<?= base_url() ?>ecommerce/producto/variante_obtener', {"id": $(this).data('id'), "producto_id": $(this).data('producto_id'), }, mostrarEdicionDetalle);
        } else if ($(this).val() === "agregar") {
            llamadaAjax(false, '<?= base_url() ?>ecommerce/producto/variante_nuevo', {"producto_id": $(this).data('producto_id'), }, mostrarEdicionDetalle);
        } else if ($(this).val() === "algoritmo") {
            llamadaAjax(false, '<?= base_url() ?>produccion/skuAlgoritmo/algoritmo', {"id": $(this).data('id'), "producto_id": $(this).data('producto_id'), }, mostrarEdicionDetalle);
        } else if ($(this).val() === "editar_receta") {
            llamadaAjax(false, '<?= base_url() ?>ecommerce/producto/receta_obtener', {"id": $(this).data('id'), "producto_id": $(this).data('producto_id'), }, mostrarEdicionDetalle);
        }
    });

    function mostrarEdicionDetalle(r) {
        if (r.error) {
            mostrarError("No existe informaci&oacute;n disponible en estos momentos");
        } else {
            $("#modalEdicionDetalle .modal-content").html(r.respuesta);
            $("#modalEdicionDetalle").modal("show");
        }
    }


    function mostrarEliminacionDetalle(r) {
        mostrarExito("mostrarEliminacionDetalle");
        console.log(r);
        if (r.error) {
            mostrarError("Hubo un problema durante la eliminaci&oacute;n");
        } else {
            mostrarExito("Registro Eliminado");
            console.log("producto_variante_listado 99");
            $("#modalEdicionDetalle").modal("hide");
        }
    }
</script>