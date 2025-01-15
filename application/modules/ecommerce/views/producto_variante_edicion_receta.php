<div class="modal-header">
    <h5 class="modal-title" id="exampleModalLabel"><span id="modal_edicion_detalle">Variante</span></h5>
    <button type="button" class="close" onclick="$('#modalEdicion').modal('hide');" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<div class="modal-body">
    <?php
    $fields = array();

    foreach ($variante as $field => $value) {
        if (!(strpos(strtoupper($field), 'CREACION_') === 0 || strpos(strtoupper($field), 'ACTUALIZACION_') === 0 || strpos(strtoupper($field), 'INFO_') === 0)) {
            $fields[$field] = array(
                "id" => $field,
                "name" => $field,
                "value" => $value,
                "tipo" => 'input'
            );
        }
    }

    if (key_exists('id', $fields)) {
        $fields['id']['tipo'] = 'hidden';
    } else {
        $fields['sku']['value'] = $sku_prefijo;
    }
//    $fields['producto_id']['value'] = $producto_id;
    $fields['producto_id']['tipo'] = 'hidden';
    $fields['sku']['tipo'] = 'label';
    $fields['titulo']['tipo'] = 'label';
    unset($fields['tipo_algoritmo']);
    $fields['estado']['tipo'] = 'select_disable';
    $fields['estado']['sel'] = array(ESTADO_ACTIVO => 'Activo', ESTADO_INACTIVO => 'Inactivo');
    $fields['cantidad']['tipo'] = 'label';
    $fields['unidad']['tipo'] = 'select_disable';
    $fields['largo_cm']['tipo'] = 'label';
    $fields['unidad']['sel'] = array(10 => 'Decena (10)', 12 => 'Docena (12)', 1 => 'Unidad', 18 => 'Bunch 18', 25 => 'Bunch 25', 30 => 'Bunch 30');
    ?>
    <?= form_open(base_url() . "ecommerce/producto/variante_receta_guardar", array("id" => "form_modal_edicion_variante")); ?>
    <div class="form-horizontal col-12 row">
        <?php
        foreach ($fields as $k => $field) {
            item_formulario_vertical($field);
        }
        ?>

    </div>

    <table class="table table-bordered" id="dynamic_field">
        <h5 class="modal-title" id="exampleModalLabel"><span id="modal_edicion_detalle">Agregar Receta</span>
            <button type="button" name="add" id="add" class="btn btn-accion btn-tool"><i class="fa fa-plus"></i></button>
        </h5>

        <?php
        if ($recetas) {
            foreach ($recetas as $k => $re) {
                ?>
                <tr  class="row_algoritmo_det" id="row<?= $k ?>">
                    <td>

                        <select  name="ingrediente_actualizar_id[]" id="" class="form-control ">

                            <option value="<?php echo $re->id; ?>"><?php echo $re->nombre; ?> - <?php echo $re->descripcion; ?> - <?php echo $re->longitud; ?> cm</option>

                        </select>

                    </td>
                    <td>
                        <input type="number" name="valor_actualizar[]" value="<?php echo $re->cantidad; ?>"placeholder="cantidad de Stems" class="form-control name_list" />
                    </td>
                    <td><button type="button" name="remove" id="<?php echo $re->id; ?>" fila="<?php echo $k; ?>" class="btn btn-danger btn_remover">X</button></td>
                </tr>
            <?php
            }
        }
        ?>
        </tr>
    </table>
<?= form_close(); ?>


</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary btn-salir-modal">Salir</button>
    <button type="button" class="btn btn-primary btn-guardar-modal-detalle">Guardar Cambios</button>
    <script>
        var i = <?= ($recetas ? count($recetas) : 0); ?>;
        $(document).on('click', '#modalEdicionDetalle .btn-salir-modal', function () {
            $("#modalEdicionDetalle").modal("hide");
        });

        $(document).on('click', '#modalEdicionDetalle .btn-guardar-modal-detalle', function (e) {
            e.preventDefault();
            $("#form_modal_edicion_variante").submit();
        });
        $(document).on('click', '.btn_remover', function () {
            console.log("aki");
            i--;
            var button_id = $(this).attr("id");
            id_receta = $(this).attr("fila");

            llamadaAjax(id_receta, '<?= base_url() ?>ecommerce/producto/borrar_variante', {"id": button_id, }, 0);
            $('#row' + id_receta + '').remove();


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
                success: function (data)
                {
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