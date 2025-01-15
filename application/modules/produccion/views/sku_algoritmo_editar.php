<div class="modal-header">
    <h5 class="modal-title" id="exampleModalLabel"><span id="modal_edicion_detalle"><?php echo $variante->titulo; ?></span></h5>
    <button type="button" class="close" onclick="$('#modalEdicion').modal('hide');" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<div class="modal-body">
    <?php
    $fields = array();
    $valor1 = array();
    $valor2 = array();
    foreach ($parametro as $field => $value) {
        if (!(strpos(strtoupper($field), 'CREACION_') === 0 || strpos(strtoupper($field), 'ACTUALIZACION_') === 0 || strpos(strtoupper($field), 'INFO_') === 0)) {
            $fields[$field] = array(
                "id" => $field,
                "name" => $field,
                "value" => $value,
                "tipo" => 'input',
            );
        }
    }
    foreach ($variante as $field => $value) {
        if (!(strpos(strtoupper($field), 'CREACION_') === 0 || strpos(strtoupper($field), 'ACTUALIZACION_') === 0 || strpos(strtoupper($field), 'INFO_') === 0)) {
            $fields[$field] = array(
                "id" => $field,
                "name" => $field,
                "value" => $value,
                "tipo" => 'input',
            );
        }
    }

    unset($fields['estado']);
    unset($fields['largo_cm']);
    unset($fields['cantidad']);
    unset($fields['unidad']);
    $fields['id']['tipo'] = 'hidden';
    $fields['producto_id']['tipo'] = 'hidden';
    $fields['titulo']['tipo'] = 'hidden';
    $fields['sku']['tipo'] = 'hidden';

    $fields['sku_algoritmo_id']['tipo'] = 'select';
    $fields['sku_algoritmo_id']['sel'] = array('diario' => 'Algoritmo Cupo Diario', 'semanal' => 'Algoritmo Cupo Semanal', 'porcentaje' => 'Algoritmo Porcentaje');
    ?>

    <?= form_open(base_url() . "produccion/skuAlgoritmo/algoritmo_guardar", array("id" => "form_modal_edicion_variante")); ?>
    <div class="form-horizontal col-12 row">
        <table class="table table-bordered" id="dynamic_field">
            <?php
            if (!empty($algoritmo_detalle)) {
                $fields['sku_algoritmo_id']['value'] = $algoritmoasignado->tipo_algoritmo;
            }
            foreach ($fields as $k => $field) {
                item_formulario_vertical($field);
            }
            if (!empty($algoritmo_detalle)) {
                ?>
                <?php
                if ($algoritmoasignado->tipo_algoritmo == 'diario') {
                    foreach ($algoritmo_detalle as $k => $algoritmo) {
                        ?>
                        <tr id="row<?= $k ?>" class="row_algoritmo_det">
                            <td><select name="finca_id[]" class="form-control "><?php foreach ($fincas as $key => $finca) { ?><option value="<?php echo $key ?>" <?php if ($key == $algoritmo->finca_id) { ?> selected <?php } ?>><?php echo $finca; ?></option><?php } ?></select> </td>
                            <td><input type="text" name="diario[]" value='<?= $algoritmo->valor ?>' placeholder="cupo" class="form-control name_list" /></td>
                            <td><button type="button" name="remove" id="<?= $k ?>" class="btn btn-danger btn_remove">X</button></td>
                        </tr>
                        <?php
                    }
                }
                ?>
                <?php
                if ($algoritmoasignado->tipo_algoritmo == 'semanal') {
                    foreach ($algoritmo_detalle as $algoritmo) {
                        if ($algoritmo->nombre_parametro == 'semanal') {
                            $valor1[] = $algoritmo->valor;
                            $valorfinca[] = $algoritmo->finca_id;
                        } else {
                            $valor2[] = $algoritmo->valor;
                        }
                    }
                    foreach ($valor1 as $k => $semana) {
                        ?>
                        <tr id="row<?= $k ?>" class="row_algoritmo_det">
                            <td><select name="finca_id[]" class="form-control"><?php foreach ($fincas as $key => $finca) { ?><option value="<?php echo $key ?>" <?php if ($key == $valorfinca[$k]) { ?> selected <?php } ?>><?php echo $finca; ?></option><?php } ?></select> </td>
                            <td><input type="text" name="semanal[]" value='<?= $valor1[$k] ?>' placeholder="semanal" class="form-control name_list" /></td>
                            <td><input type="text" name="diario[]" value='<?= $valor2[$k] ?>' placeholder="diario" class="form-control name_list" /></td>
                            <td><button type="button" name="remove" id="<?= $k ?>" class="btn btn-danger btn_remove">X</button></td>

                        </tr>
                        <?php
                    }
                }
                ?>
                <?php
                if ($algoritmoasignado->tipo_algoritmo == 'porcentaje') {
                    foreach ($algoritmo_detalle as $algoritmo) {
                        if ($algoritmo->nombre_parametro == 'porcentaje') {
                            $valor1[] = $algoritmo->valor;
                            $valorfinca[] = $algoritmo->finca_id;
                        } else {
                            $valor2[] = $algoritmo->valor;
                        }
                    }
                    foreach ($valor1 as $k => $semana) {
                        ?>
                        <tr id="row<?= $k ?>" class="row_algoritmo_det">
                            <td><select name="finca_id[]" class="form-control "><?php foreach ($fincas as $key => $finca) { ?><option value="<?php echo $key ?>" <?php if ($key == $valorfinca[$k]) { ?> selected <?php } ?>><?php echo $finca; ?></option><?php } ?></select> </td>
                            <td><input type="text" name="porcentaje[]" value='<?= $valor1[$k] ?>' placeholder="porcentaje %" class="form-control name_list" /></td>
                            <td><input type="text" name="diario[]" value='<?= $valor2[$k] ?>' placeholder="cupo" class="form-control name_list" /></td>
                            <td><button type="button" name="remove" id="<?= $k ?>" class="btn btn-danger btn_remove">X</button></td>
                        </tr>
                        <?php
                    }
                }
                ?>


                <?php
            }
            ?>
            <button type="button" name="add" id="add" class="btn btn-accion btn-tool"><i class="fa fa-plus"></i></button>
        </table>
    </div>
    <?= form_close(); ?>

</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary btn-salir-modal">Salir</button>
    <button type="button" class="btn btn-primary btn-guardar-modal-detalle">Guardar Cambios</button>
    <script type="text/javascript">

<?php
if (!empty($algoritmo_detalle)) {
    ?>
            $(document).ready(function () {
                calcularValori(<?= floor(count($algoritmo_detalle) / (($algoritmoasignado->tipo_algoritmo == 'diario') ? 1 : 2)) ?>);
                aplicarSoloNumeros();
            });
    <?php
}
?>

        $(document).on('click', '#modalEdicionDetalle .btn-salir-modal', function () {
            $("#modalEdicionDetalle").modal("hide");
        });

        $(document).on('click', '#modalEdicionDetalle .btn-guardar-modal-detalle', function (e) {
            e.preventDefault();
            $("#form_modal_edicion_variante").submit();
        });


        $(document).on('submit', '#form_modal_edicion_variante', function (e) {

            console.log("En el submit");
            e.preventDefault();
            e.stopImmediatePropagation();
            if (!unsoloclick()) {
                console.log("No hacemos el submit");
                return false;
            }

            var form = $(this);
            var url = form.attr('action');

            $.ajax({
                type: "POST",
                url: url,
                cache: false,
                data: form.serialize(), // serializes the form's elements.
                success: function (data) {
                    if (data.error) {
                        mostrarError(data.respuesta);
                    } else {
                        console.log(data);
                        $('#modalEdicionDetalle').modal("hide");
                        mostrarExito(data.respuesta);
                        recargarProducto();
                    }
                }
            });


        });
    </script>
</div>