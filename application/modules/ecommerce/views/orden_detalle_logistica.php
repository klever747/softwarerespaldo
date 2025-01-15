<div class="card card-default card-detalle-logistica">
    <div class="card-header" data-toggle="collapse" data-target="#det_items_logistica">
        <h6 class="card-title">Logistica</h6>
        <div class="card-tools">
            <!--<button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button>-->
        </div>
    </div>
    <div class="card-body <?= !($perfil == PANTALLA_LOGISTICA || $perfil == PANTALLA_EMPAQUE) ? 'collapse' : '' ?>" id="det_items_logistica">
        <?php
        if ($cajas_orden) {
            foreach ($cajas_orden as $k => $caja) {
                $tag = "";
                $clase = ($k % 2 == 0) ? "color_logistica_par" : "color_logistica_impar";
                $clase2 = "";
                if (($perfil == PANTALLA_LOGISTICA)) {
                    if ($item->preparado == "S") {
                        $clase2 = " orden_item_preparado";
                    }
                }
                //if (($perfil == PERFIL_EMPAQUE)) {
                if ($caja->empacada == "S") {
                    $clase = " orden_item_preparado";
                    $clase2 = ""; //en teoria si ya esta empacado es porque ya esta preparado
                    $tag = "EMPACADO!!!";
                }
                //}

                $totalStems = 0;
                ?>
                <div class="row p-2 <?= $clase ?>" style="background-color:<?= ($caja->empacada == "S" ? "red !important" : "green") ?>">
                    <div class="row <?= ($perfil == PANTALLA_EMPAQUE) ? 'col-10' : 'col-12' ?>">
                        <div class="col-12 col-md-9 order-1 <?= $clase2 ?>">
                            <?php if (strlen($tag) > 0) { ?><h3 class="btn red"><?= $tag ?></h3><?php } ?>
                            <?php
                            if ($caja->items) {
                                foreach ($caja->items as $k => $item) {
                                    ?>
                                    <div class="row p-0 border">
                                        <?= $item->card ?>
                                    </div>
                                    <?php
                                    $totalStems += $item->totalStems;
                                }
                            }
                            ?>
                        </div>

                        <div class="col-12 col-md-3 order-0 align-content-center align-self-center" id="finca_caja">
                            <?= form_open(base_url() . "produccion/logistica/editar_caja_finca", array("id" => "form_caja_finca_editar")); ?>
                            <?= (isset($caja->id)) ? form_hidden("caja_id", $caja->id) : '' ?>
                            <h2><b>Caja# <?= $caja->id ?></b></h2><br />
                            <?php if (!$ecommerce) { ?>
                                <button type = "button" class="btn btn-accion-orden btn-tool col-12 col-md-6 offset-md-6" data-caja_id="<?= $caja->id ?>" data-orden_id="<?= $caja->orden_id ?>" value="imprimir_mensaje_caja" data-toggle="tooltip" data-placement="bottom" title="Imprimir tarjeta">
                                    <i class="fas fa-print fa-sm"></i>Imprimir
                                </button>
                            <?php } ?>

                            <div class="col-12 col-md-12">

                                <label>Finca:</label>
                                <?php
                              
                                if ($perfil == PANTALLA_LOGISTICA || sizeof($sel_finca) > 1 ) {
                                    ?>
                                    <select name="finca_id" id="finca_id" class="form-control input-lg" title="Selección de Finca" style="background: #acded4;">

                                        <?php
                                        foreach ($sel_finca as $k => $finca) {
                                            if ($finca->id == $caja->info_finca_id) {
                                                echo "<option value='" . $finca->id . "' selected='' > " . $finca->nombre . " </option>";
                                            } else {
                                                echo "<option value='" . $finca->id . "' > " . $finca->nombre . " </option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                <?php } else { ?>
                                    <select class="form-control input-lg" title="Selección de Finca" style="background: #acded4;" disabled >
                                        <?php
                                        foreach ($sel_finca as $k => $finca) {
                                            if ($finca->id == $caja->info_finca_id) {
                                                echo "<option selected='' > " . $finca->nombre . " </option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                <?php } ?>
                            </div>
                            <div class="col-12 col-md-12" >
                                <label>Tipo caja  <i class="fas fa-box"></i></label>
                                <?php
                                if ($perfil == PANTALLA_LOGISTICA) {
                                    ?>

                                    <select name="tipo_caja_id" id="tipo_caja_id" class="form-control input-lg" title="Selección de Caja" style="background: #acded4;">
                                        <?php
                                        foreach ($sel_tipo_caja as $tipo_caja) {
                                            if ($tipo_caja->id == $caja->info_tipo_caja_id) {
                                                echo "<option value='" . $tipo_caja->id . "' selected='' >" . $tipo_caja->nombre . "</option>";
                                            } else {
                                                echo "<option value='" . $tipo_caja->id . "'>" . $tipo_caja->nombre . "</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                <?php } else { ?>
                                    <select class="form-control input-lg" title="Selección de Caja" style="background: #acded4;" disabled >
                                        <?php
                                        foreach ($sel_tipo_caja as $tipo_caja) {
                                            if ($tipo_caja->id == $caja->info_tipo_caja_id) {
                                                echo "<option selected='' > " . $tipo_caja->nombre . " </option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                <?php } ?>
                            </div>
                            <div class="row">
                                <div class="col-6 col-md-9">
                                    <b>Stems: <?= $totalStems ?></b> <br />
                                    <?php echo "<td>" . ($caja->tracking_number ? '<a href="' . base_url() . 'uploads/tracking/' . $caja->tracking_number->tracking_number . '.pdf" target ="_blank"><i class="fas fa-print"></i></a>' : ' ' ) . "</td>"; ?>
                                    <b>Tracking#: <?= ($caja->tracking_number ? $caja->tracking_number->tracking_number : 'NO') ?></b> <br />
                                </div>

                                <div class="col-6 col-md-3">
                                    <?php
                                    if ($perfil == PANTALLA_LOGISTICA) {
                                        ?>
                                        <button type="submit" class="btn  btn-tool" ><i class="fas fa-save"></i></button>
                                    <?php } ?>
                                </div>
                                <?= form_close(); ?>
                            </div>

                        </div>
                    </div>
                    <?php
                    if ($perfil == PANTALLA_EMPAQUE) {
                        ?>
                        <div class="row col-2">
                            <button type = "button" class="btn btn-accion-orden btn-tool col-12" data-orden_id="<?= $caja->orden_id ?>" data-caja_id="<?= $caja->id ?>" value="imprimir_caja_and_traking">
                                <i class="fas fa-print"></i>
                            </button>
                            <?php
                            if ($caja->empacada == 'N') {
                                ?>
                                <button type="button" class="btn btn-accion btn-primary btn-lg" id="btn_caja_<?= $caja->id ?>" data-id="<?= $caja->id ?>" value="marcar_caja_empacada">Marcar como Empacado</button>
                                <?php
                            } else {
                                ?>
                                <button type="button" class="btn btn-accion btn-primary btn-lg" id="btn_caja_<?= $caja->id ?>" data-id="<?= $caja->id ?>" value="marcar_caja_no_empacada">Marcar como No Empacado</button>
                                <?php
                            }
                            ?>
                        </div>
                        <?php
                    }
                    ?>

                </div>
                <?php
            }
        }
        ?>
    </div>
</div>

<script>

    $(document).on('submit', '#form_caja_finca_editar', function (event) {
        event.preventDefault();
        event.stopImmediatePropagation();
        if (!unsoloclick()) {
            console.log("No hacemos el submit");
            return false;
        }
        console.log("abro esta funcion");
        var form = $(this);
        var url = form.attr('action');
        $.ajax({
            type: "POST",
            url: url,
            cache: false,
            data: form.serialize(), // serializes the form's elements.
            success: function (data) {
                console.log(data);
                if (data.error) {
                    mostrarError(data.respuesta);
                } else {
                    mostrarExito(data.respuesta);
                    recargarPrincipal();
                }
            }
        });
    });

    $("body").delegate(".card-detalle-logistica .btn-accion", "click", function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        if (!unsoloclick()) {
            console.log("No hacemos el boton");
            return false;
        }
        if ($(this).val() === "marcar_caja_empacada") {
            llamadaAjax($(this).attr('id'), '<?= base_url() ?>produccion/empaque/caja_empacada', {
                "orden_caja_id": $(this).data('id')
            }, recargarPrincipal);
        } else if ($(this).val() === "marcar_caja_no_empacada") {
            llamadaAjax($(this).attr('id'), '<?= base_url() ?>produccion/empaque/caja_no_empacada', {
                "orden_caja_id": $(this).data('id')
            }, recargarPrincipal);
        }
    });
</script>