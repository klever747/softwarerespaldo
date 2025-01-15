<div class="modal-header"  <?= BACKGROUND_BOXES ?>>
    <h5 class="modal-title" id="exampleModalLabel"><span id="modal_edicion_master"><?= $operacion ?></span></h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<div class="modal-body">

    <?php
    $fields = array();
    foreach ($cliente as $field => $value) {
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
    $fields['email']['tipo'] = 'email';
    $fields['store_id']['tipo'] = 'select';
    $fields['store_id']['sel'] = $sel_store;
    $fields['estado']['tipo'] = 'select';
    $fields['estado']['sel'] = array(ESTADO_ACTIVO => 'Activo', ESTADO_INACTIVO => 'Inactivo');

    /*     * ******************************** */
//    $fields['state_code']['maxlength'] = 2;
//    $fields['country_code']['maxlength'] = 2;
    ?>
    <?= form_open(base_url() . "ecommerce/cliente/cliente_guardar", array("id" => "form_modal_edicion")); ?>
    <div class="form-horizontal col-12 row">
        <?php
        foreach ($fields as $k => $field) {
            echo item_formulario_vertical($field);
        }
        ?>
    </div>
    <?= form_close(); ?>

    <?= $direcciones ?>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary btn-salir-modal">Salir</button>
    <button type="button" class="btn btn-primary btn-guardar-modal">Guardar Cambios</button>
</div>

<script>

    $(document).on('click', '#modalEdicion .btn-salir-modal', function () {
        $('#modalEdicion').modal("hide");
    });


    /*************** Acciones1 en Modal *****************/
    /**
     *
     * @param {type} id
     * @returns {undefined}
     */
    function eliminarModalDetalle(id) {
        llamadaAjax(false, '<?= base_url() ?>ecommerce/cliente/cliente_direccion_envio_eliminar', {"id": id}, mostrarEliminacionDetalle);
    }
    function obtenerDetalleDetalle(id) {
        var parametros = {
            "id": id,
            "cliente_id": document.getElementsByName("id")[0].value,
        };
        llamadaAjax(false, '<?= base_url() ?>ecommerce/cliente/cliente_direccion_envio_obtener', parametros, mostrarEdicionDetalle);
    }

    function agregarDetalleDetalle() {
        var parametros = {
            "cliente_id": document.getElementsByName("id")[0].value,
        };
        llamadaAjax(false, '<?= base_url() ?>ecommerce/cliente/cliente_direccion_envio_nuevo', parametros, mostrarEdicionDetalle);
    }

    function mostrarEdicionDetalle(r) {
        if (r.error) {
            mostrarError("No existe informaci&oacute;n disponible en estos momentos");
        } else {
            $("#modalEdicionDetalle .modal-content").html(r.respuesta);
            $("#modalEdicionDetalle").modal("show");
        }
    }

<?php
if (isset($cliente->id)) {
    ?>
        function mostrarEliminacionDetalle(r) {
            mostrarExito("mostrarEliminacionDetalle");
            console.log(r);
            if (r.error) {
                mostrarError("Hubo un problema durante la eliminaci&oacute;n");
            } else {
                mostrarExito("Registro Eliminado");
                console.log("cliente_edicion 97");
                $("#modalEdicion").modal("hide");
                obtenerDetalle('<?= $cliente->id ?>');
            }
        }
    <?php
}
?>

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

        $.ajax({
            type: "POST",
            url: url,
            cache: false,
            data: form.serialize(), // serializes the form's elements.
            success: function (data)
            {
                console.log(data);
                if (data.error) {
                    mostrarError(data.respuesta);
                } else {
                    mostrarExito(data.respuesta);
                    //debemos cerrar esta ventana
                    $("#modalEdicion").modal("hide");
                    //debemos actualizar la informacion del padre
                    recargarPrincipal();
                }
            }
        });
    });

</script>


<!-- Modal -->
<div class="modal" id="modalEdicionDetalle" tabindex="-1" role="dialog" aria-labelledby="wsanchez" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">

        </div>
    </div>
</div>