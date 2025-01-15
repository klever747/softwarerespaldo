<div class="card-body text-center">
    <div class="card-header">
        <h3 class="card-title">Lista de menúes asociados</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-accion_detalle btn-tool" value="agregar" data-menu_id="<?= $menu->id ?>" ><i class="fas fa-plus-circle"></i></button>
        </div>
    </div>
    <div class="row">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <td class="text-left">Nombre</td>
                    <td class="text-left">icono</td>
                    <td class="text-left">Url</td>
                    <td class="text-left">Estado</td>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($variantes) {
                    foreach ($variantes as $k => $variante) {
                        ?>
                        <tr>
                            <th scope="row"><?= $variante->id ?></th>
                            <td class="text-left"><?= $variante->nombre ?></td>
                            <td class="text-left"><?= $variante->icono ?></td>
                            <td class="text-left"><?= $variante->url ?></td>
                            <td class="text-left">
                                <?= mostrarEstilos($variante->estado); ?>
                            </td>
                            <td>
                                <button type = "button" class="btn btn-accion_detalle btn-tool" data-id="<?= $variante->id ?>" data-dismiss="modal" value="eliminar"><i class="far fa-trash-alt"></i></button>
                                <button type = "button" class="btn btn-accion_detalle btn-tool" data-id="<?= $variante->id ?>" data-producto_id="<?= $variante->id ?>" value="editar"><i class="fas fa-pencil-alt"></i></button>
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