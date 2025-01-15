<div class="card card-default" id="orden_detalle_destino">
    <div class="card-header">
        <h6 class="card-title" data-toggle="collapse" data-target="#det_direccion">
            <?php
            if ($cliente_direccion) {
                echo "Destinatario: ";
                ?>
                <?= $cliente_direccion->alias . " " . $cliente_direccion->destinatario_nombre . " " . $cliente_direccion->destinatario_apellido . " " ?>
                <?= ($cliente_direccion->destinatario_company != null ? (($cliente_direccion->alias || $cliente_direccion->destinatario_nombre || $cliente_direccion->destinatario_apellido) ? "/" : "") . $cliente_direccion->destinatario_company : '') ?>
                <?php
            } else {
                echo "Sin destinatario";
            }
            ?>
        </h6>
        <div class="card-tools">
            <?php if ($cliente_direccion) { ?>
                <button type = "button" class="btn btn-accion btn-tool visible_<?= $no_editable ?>" data-direccion_id="<?= $cliente_direccion->id ?>" data-cliente_id="<?= $cliente->id ?>" data-orden_id="<?= $orden->id ?>" value="editar_destino_orden"><i class="fas fa-pencil-alt"></i></button>
                <button type = "button" class="btn btn-accion btn-tool visible_<?= $no_editable ?>" data-direccion_id="<?= $cliente_direccion->id ?>" data-cliente_id="<?= $cliente->id ?>" data-orden_id="<?= $orden->id ?>" value="cambiar_destino_orden"><i class="fas fa-exchange-alt"></i></button>            
                <!--<button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button>-->
            <?php } else if ($cliente) {
                ?>
                <button type = "button" class="btn btn-accion btn-tool" data-cliente_id="<?= $cliente->id ?>" data-orden_id="<?= $orden->id ?>" value="cambiar_destino_orden"><i class="fas fa-exchange-alt"></i></button>
                <?php
            }
            ?>
        </div>
    </div>    
    <div class="card-body collapse" id="det_direccion">
        <?php if ($cliente_direccion) { ?>
            <div class="row text-left">
                <div class="col-12 col-lg-7 row">
                    <h6 class="col-3 col-md-2"><b>Direcci&oacute;n: </b></h6>
                    <h6 class="col-9">
                        <?= (!$cliente_direccion) ? "Sin destinatario" : $cliente_direccion->country . "(" . $cliente_direccion->country_code . ") / " . $cliente_direccion->state . "(" . $cliente_direccion->state_code . ") / " . $cliente_direccion->city . "(" . $cliente_direccion->zip_code . ")"; ?>
                        <br/><?= $cliente_direccion->address_1 ?>
                        <br/><?= $cliente_direccion->address_2 ?>
                    </h6>
                </div>                            
                <div class="col-12 col-lg-5 row">
                    <h6 class="col-3"><b>Tel&eacute;fono: </b></h6>
                    <h6 class="col-9"><?= $cliente_direccion->phone ?></h6>
                </div>
            </div>
        <?php } ?>
    </div>

</div>

