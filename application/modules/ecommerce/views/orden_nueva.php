<div class="modal-header">
    <h5 class="modal-title" id="exampleModalLabel"><span id="modal_edicion_master">Orden Nueva</span></h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<div class="modal-body">    
    <?= form_open(base_url() . "ecommerce/orden_nueva_guardar", array("id" => "form_nueva_orden")); ?>
    <div class="row">
        <?php
        selectTienda(1, $sel_store);
        ?>
    </div>
    <?= form_close(); ?>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary btn-salir-modal">Salir</button>
    <button type="button" class="btn btn-primary btn-guardar-modal">Guardar Cambios</button>

    <script>
        $(document).ready(function () {
            $('.select2').select2({
            });
        });

        $(document).on('click', '#modalEdicion .btn-salir-modal', function () {
            $('#modalEdicion').modal("hide");
        });

        $(document).on('click', '.btn-guardar-modal', function (e) {
            e.preventDefault();
            $("#form_nueva_orden").submit();
        });

        $(document).on('submit', '#form_nueva_orden', function (e) {

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
                        $("#modalEdicion").modal("hide");
                        orden_actual = data.id;
                        loadOrden();
                    }
                }
            });
        });

    </script>