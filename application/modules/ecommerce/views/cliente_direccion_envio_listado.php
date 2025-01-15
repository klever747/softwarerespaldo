<div class="card-body text-center">
    <div class="card-header">
        <h3 class="card-title">Direcciones</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-accion_detalle btn-tool" value="agregar"><i class="fas fa-plus"></i></button>
        </div>
    </div>
    <div class="row">
        <table class="table table-striped ">
            <thead class="thead-dark">
                <tr>
                    <th scope="col">#</th>
                    <th class="text-left">Alias</th>
                    <th class="text-left">Destinatario</th>
                    <th class="text-left">Address1</th>
                    <th class="text-left">Address2</th>
                    <th class="text-left">Country</th>
                    <th class="text-left">State</th>
                    <th class="text-left">City</th>
                    <th class="text-left">Zip_code</th>
                    <th class="text-left">Phone#</th>
                    <th class="text-left">Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($direcciones) {
                    foreach ($direcciones as $k => $direccion) {
                        ?>
                        <tr>
                            <th scope="row"><?= $k + 1 ?></th>
                            <td class="text-left"><?= $direccion->alias ?></td>
                            <td class="text-left"><?= $direccion->destinatario_nombre . " " . $direccion->destinatario_apellido . " " . $direccion->destinatario_company ?></td>
                            <td class="text-left"><?= $direccion->address_1 ?></td>
                            <td class="text-left"><?= $direccion->address_2 ?></td>
                            <td class="text-left"><?= $direccion->country . " " . $direccion->country_code ?></td>
                            <td class="text-left"><?= $direccion->state . " " . $direccion->state_code ?></td>
                            <td class="text-left"><?= $direccion->city ?>
                            <td class="text-left"><?= $direccion->zip_code ?></td>                                                
                            <td class="text-left"><?= $direccion->phone ?></td>                                                
                            <td class="text-left"><?= ($direccion->estado == 'I' ? 'Inactivo' : 'Activo') ?></td>
                            <td>
                                <button type = "button" class="btn btn-accion_detalle btn-tool" data-id="<?= $direccion->id ?>" data-dismiss="modal" value="eliminar"><i class="far fa-trash-alt"></i></button>
                                <button type = "button" class="btn btn-accion_detalle btn-tool" data-id="<?= $direccion->id ?>" value="editar"><i class="fas fa-pencil-alt"></i></button>
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
    $("body").delegate("#modalEdicion .btn-accion_detalle", "click", function () {
        if ($(this).val() === "eliminar") {
            eliminarModalDetalle($(this).data('id'));
        } else if ($(this).val() === "editar") {
            obtenerDetalleDetalle($(this).data('id'));
        } else if ($(this).val() === "agregar") {
            agregarDetalleDetalle();
        }
    });
</script>