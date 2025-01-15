<div class="modal-header">
    <h5 class="modal-title" id="exampleModalLabel"><span id="modal_edicion_master">Master Shipping</span></h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<div class="modal-body">
    <?php
    $fields = array();
    foreach ($master_shipping as $field => $value) {
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
    }
    $fields['nombre_master']['tipo'] = 'file';
    $fields['finca_id']['label'] = 'Finca';
    $fields['finca_id']['tipo'] = 'select';
    $fields['finca_id']['sel'] = $listado_fincas;
    $fields['fecha_carguera']['clase'] = 'select_fecha';
    unset($fields['estado']);
    ?>
    <?= form_open_multipart(base_url() . "produccion/MasterShipping/master_shipp_guardar", array("id" => "form_modal_edicion")); ?>
    <div class="form-horizontal col-12 row">
        <?php
        foreach ($fields as $k => $field) {
            echo item_formulario_vertical($field);
        }
        ?>    
    </div>
    <?= form_close(); ?>

</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary btn-salir-modal">Salir</button>
    <button id="btn_buscar" type="button" class="btn btn-primary btn-guardar-modal">Guardar Cambios</button>


    <script>

        function recargarProducto() {
            loadProducto(<?= isset($master_shipping->id) ? $master_shipping->id : 0 ?>);
        }

        $(document).on('click', '#modalEdicion .btn-salir-modal', function () {
            $('#modalEdicion').modal("hide");
        });

        $(document).on('click', '.btn-guardar-modal', function (e) {
            e.preventDefault();
            $("#modalEdicion #form_modal_edicion").submit();
        });

        $(document).on('submit', '#modalEdicion #form_modal_edicion', function (e) {

            console.log("En el submit");
            e.preventDefault();
            e.stopImmediatePropagation();
            if (!unsoloclick()) {
                console.log("No hacemos el submit");
                return false;
            }
            var form = $(this);
            var url = form.attr('action');

            var formData = new FormData();
            var files = $('#nombre_master').prop("files")[0];
            var finca_id = $('#form_modal_edicion #finca_id').val()
            var estado = $('#estado').val();
            var fecha_carguera = $('#fecha_carguera').val();
            var numero_guia = $('#numero_guia').val();
            formData.append('nombre_master', files);
            formData.append('finca_id', finca_id);
            formData.append('estado', estado);
            formData.append('fecha_carguera', fecha_carguera);
            formData.append('numero_guia', numero_guia);
            console.log(finca_id);
            $.ajax({
                type: "POST",
                url: url,
                cache: false,
                data: formData, // serializes the form's elements.
                contentType: false,
                processData: false,
                success: function (data)
                {
                    console.log(data);
                    if (data.error) {
                        mostrarError(data.respuesta);
                    } else {
                        mostrarExito(data.respuesta);
                        $("#modalEdicion").modal("hide");
                        recargarPrincipal();
                    }
                }
            });
        });

    </script>

</div>

<!-- Modal -->
<div class="modal" id="modalEdicionDetalle" tabindex="-1" role="dialog" aria-labelledby="wsanchez" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">

        </div>
    </div>
</div>