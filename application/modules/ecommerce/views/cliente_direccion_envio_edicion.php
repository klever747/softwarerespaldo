<div class="modal-header">
    <h5 class="modal-title" id="exampleModalLabel"><span id="modal_edicion_detalle">Direcci&oacute;n de Envio</span></h5>
    <button type="button" class="close" onclick="$('#modalEdicionDetalle').modal('hide');"  aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<div class="modal-body">
    <?php
    $fields = array();
    $fields['alias'] = array(); //para mostrar en el orden deseado

    foreach ($cliente_direccion_envio as $field => $value) {
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
    $fields['cliente_id']['value'] = $cliente->id;
    $fields['cliente_id']['tipo'] = 'hidden';

    $fields['store_id']['tipo'] = 'select';
    $fields['store_id']['sel'] = $sel_store;

    $fields['estado']['tipo'] = 'select';
    $fields['estado']['sel'] = array(ESTADO_ACTIVO => 'Activo', ESTADO_INACTIVO => 'Inactivo');

    /*     * ***************************************************** */
//    $fields['address_1']['tipo'] = 'textarea';    //TODO wsanchez, solucionar ubicacion para poner textarea
//    $fields['address_2']['tipo'] = 'textarea';
    $fields['state_code']['maxlength'] = 2;
    $fields['country_code']['maxlength'] = 2;
    $fields['zip_code']['maxlength'] = 10;
    ?>
    <?= form_open(base_url() . "ecommerce/cliente/cliente_direccion_envio_guardar", array("id" => "form_modal_edicion_direccion_envio")); ?>
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
        $(document).on('click', '<?= ($plantilla === 1 ? '#modalEdicion' : '#modalEdicionDetalle') ?> .btn-guardar-modal-detalle', function (e) {
            e.preventDefault();
            $("<?= ($plantilla === 1 ? '#modalEdicion' : '#modalEdicionDetalle') ?> #form_modal_edicion_direccion_envio").submit();
        });

        $(document).on('click', '<?= ($plantilla === 1 ? '#modalEdicion' : '#modalEdicionDetalle') ?> .btn-salir-modal', function () {
            $("<?= ($plantilla === 1 ? '#modalEdicion' : '#modalEdicionDetalle') ?>").modal("hide");
        });



        function actualizarDireccionOrden(orden_id, nueva_direccion_id) {
            llamadaAjax(false, '<?= base_url() ?>ecommerce/actualizar_direccion_orden', {"orden_id": orden_id, "cliente_direccion_id": nueva_direccion_id}, recargarPrincipal);
        }
        $(document).on('submit', '<?= ($plantilla === 1 ? '#modalEdicion' : '#modalEdicionDetalle') ?> #form_modal_edicion_direccion_envio', function (e) {

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
                    console.log("ajax de cliente_direccion_envio_edicion");
                    if (data.error) {
                        mostrarError(data.respuesta);
                    } else {
                        console.log(data);
                        mostrarExito(data.respuesta);
                        //debemos cerrar esta ventana
                        $("<?= ($plantilla === 1 ? '#modalEdicion' : '#modalEdicionDetalle') ?>").modal("hide");

                        //debemos actualizar la informacion del padre
<?php
if ($plantilla === 1) {
    ?>
                            actualizarDireccionOrden(<?= $orden_id ?>, data.nuevo_id);
    <?php
} else {
    ?>
                            obtenerDetalle(<?= $cliente->id ?>);
    <?php
}
?>
                    }
                }
            });


        });

        $("<?= ($plantilla === 1 ? '#modalEdicion' : '#modalEdicionDetalle') ?>").on('shown.bs.modal', function () {
        });
    </script>
</div>