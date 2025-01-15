<div class="card-body text-center" id="orden_detalle_destino_edicion">
    <div class="card-header">
        <h3 class="card-title">Direcciones</h3>
        <div class="card-tools">
            <!-- <button type="button" class="btn btn-accion_detalle btn-tool" value="agregar"><i class="fas fa-plus"></i></button> -->
        </div>
    </div>
    <div class="row" style="overflow-x:auto;">
        <?php if ($direcciones) { ?>
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <td class="text-left">Alias</td>
                        <td class="text-left">Destinatario</td>
                        <td class="text-left">Address 1</td>
                        <td class="text-left">Address 2</td>
                        <td class="text-left">Country</td>
                        <td class="text-left">Province</td>
                        <td class="text-left">City</td>
                        <td class="text-left">Zip_code</td>
                        <td class="text-left">Phone</td>
                        <td class="text-left">Estado</td>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($direcciones as $k => $direccion) {
                        ?>
                        <tr <?= $direccion->id === $direccion_id ? "style='background-color: green;'" : "" ?> >
                            <th scope="row"><?= $direccion->id ?></th>
                            <th scope="row"><?= $direccion->alias ?></th>
                            <td class="text-left"><?= $direccion->destinatario_nombre . " " . $direccion->destinatario_apellido . " " . $direccion->destinatario_company ?></td>
                            <td class="text-left"><?= $direccion->address_1 ?></td>
                            <td class="text-left"><?= $direccion->address_2 ?></td>
                            <td class="text-left"><?= $direccion->country . " " . $direccion->country_code ?></td>
                            <td class="text-left"><?= $direccion->state . " " . $direccion->state_code ?></td>
                            <td class="text-left"><?= $direccion->city ?>
                            <td class="text-left"><?= $direccion->zip_code ?></td>
                            <td class="text-left"><?= $direccion->phone ?></td>
                           <!--  <td class="text-left"><?= $direccion->estado ?></td> -->
                            <td class="text-left">
                                <?= mostrarEstilos($direccion->estado); ?>
                            </td>
                            <td>
                                <?php if ($direccion->id != $direccion_id) { ?>
                                    <button type = "button" class="btn btn-accion btn-tool" data-orden_id="<?= $orden_id ?>" data-direccion_id="<?= $direccion->id ?>" value="seleccionar_direccion_destino_orden" data-dismiss="modal"><i class="fas fa-check"></i></button>
                                    <?php } ?>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
            <?php
        }
        ?>
    </div>
</div>

