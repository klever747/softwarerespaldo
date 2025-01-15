<div class="card-body text-center">
    <div class="card-header">
        <h3 class="card-title">Shopify Parametros</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-accion_detalle btn-tool" value="agregar" data-store_id="<?= $tienda->id ?>" ><i class="fas fa-plus-circle"></i></button>
        </div>
    </div>
    <div class="row">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <td class="text-left">Store</td>                                                
                    <td class="text-left">Domain</td>                    
                    <td class="text-left">Estado</td>
                    <td class="text-left">Numero max Dias</td>
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
                            <td class="text-left"><?= $variante->store ?></td>
                            <td class="text-left"><?= $variante->domain ?></td>
                            <td class="text-left"><?= $variante->estado ?></td>
                            <td class="text-left"><?= $variante->max_nro_dias_info ?></td>
                            <td>
                                <button type = "button" class="btn btn-accion_detalle btn-tool" data-id="<?= $variante->id ?>" data-dismiss="modal" value="eliminar"><i class="far fa-trash-alt"></i></button>
                                <button type = "button" class="btn btn-accion_detalle btn-tool" data-id="<?= $variante->id ?>" data-store_id="<?= $variante->store_id ?>" value="editar"><i class="fas fa-pencil-alt"></i></button>
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
            llamadaAjax(false, '<?= base_url() ?>ecommerce/store/store_shopify_eliminar', {"id": $(this).data('id')}, recargarProducto);
        } else if ($(this).val() === "editar") {
            llamadaAjax(false, '<?= base_url() ?>ecommerce/store/store_shopify_obtener', {"id": $(this).data('id'), "store_id": $(this).data('store_id'), }, mostrarEdicionDetalle);
        } else if ($(this).val() === "agregar") {
            llamadaAjax(false, '<?= base_url() ?>ecommerce/store/store_shopify_nuevo', {"store_id": $(this).data('store_id'), }, mostrarEdicionDetalle);
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