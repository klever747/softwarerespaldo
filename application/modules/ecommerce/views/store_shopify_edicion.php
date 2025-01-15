<div class="modal-header">
    <h5 class="modal-title" id="exampleModalLabel"><span id="modal_edicion_detalle">Store-Shopify</span></h5>
    <button type="button" class="close" onclick="$('#modalEdicionDetalle').modal('hide');" aria-label="Close">
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
                "tipo" => 'input',
            );
        }
    }

    if (key_exists('id', $fields)) {
        $fields['id']['tipo'] = 'hidden';
    } else {
        $fields['store']['value'] = $store;
    }
    $fields['max_nro_dias_info']['tipo'] = 'number';
    $fields['estado']['tipo'] = 'select';
    $fields['estado']['sel'] = array(ESTADO_ACTIVO => 'Activo', ESTADO_INACTIVO => 'Inactivo');
    ?>
    <?= form_open(base_url() . "ecommerce/store/store_shopify_guardar", array("id" => "form_modal_edicion_variante")); ?>
    <div class="form-horizontal col-12 row">
        <?php
        foreach ($fields as $k => $field) {
            item_formulario_vertical($field);
        }
        ?>
    </div>
    <?= form_close(); ?>

</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary btn-salir-modal">Salir</button>
    <button type="button" class="btn btn-primary btn-guardar-modal-detalle">Guardar Cambios</button>
    <script>
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