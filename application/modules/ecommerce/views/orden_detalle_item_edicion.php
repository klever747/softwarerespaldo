<div class="modal-header">
    <h5 class="modal-title" id="exampleModalLabel"><span id="modal_edicion_detalle">Edici&oacute;n Detalle de Orden</span></h5>
    <button type="button" class="close" onclick="$('#modalEdicion').modal('hide');"  aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<div class="modal-body">    
    <?= form_open(base_url() . "ecommerce/orden_item_guardar", array("id" => "form_orden_item_guardar")); ?>
    <?= (isset($orden_item_id)) ? form_hidden("orden_item_id", $orden_item_id) : form_hidden("orden_item_id", 0) ?>
    <?= form_hidden("orden_id", $orden_id) ?>

    <div class="row">        
        <div class="col-12 col-md-6">
            <label>Producto</label>
            <select name="producto_id" id="producto_id" class="form-control input-lg" data-live-search="true" title="Selecci&oacute;n de Producto">
            </select>
        </div>
        <div class="col-12 col-md-6">
            <label>Variante</label>
            <select name="variante_id" id="variante_id" class="form-control input-lg" data-live-search="true" title="Selecci&oacute;n de Variante">
            </select>
        </div>
        <div class="col-12 col-md-6">
            <?php
            $arr = array(
                "id" => "cantidad",
                "name" => "cantidad",
                "label" => "<b>Cantidad</b>",
                "value" => (isset($orden_item)) ? round($orden_item->cantidad) : 1,
                "tipo" => 'number',
            );
            item_formulario_vertical($arr)
            ?>
        </div>        
    </div>
    <?= form_close(); ?>
</div>

<div class="modal-footer">
    <button type="button" id="btn-guardar-item" class="btn btn-primary btn-guardar-item">Guardar Cambios</button>
</div>




<div class="modal" id="modalOrdenItemPropiedadEdicion" tabindex="-1" role="dialog" aria-labelledby="wsanchez" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">

        </div>
    </div>
</div>


<script>

    $("body").delegate("#modalEdicion .btn-guardar-item", "click", function (e) {
        $("#form_orden_item_guardar").submit();
    });

    $(document).on('submit', '#form_orden_item_guardar', function (e) {
        e.preventDefault();
        if (!unsoloclick()) {
            console.log("No hacemos el submit");
            return false;
        }

        var form = $(this);
        var url = form.attr('action');
        console.log(form);
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
                    mostrarExito(data.respuesta);
                    $("#modalEdicion").modal("hide");
                    recargarPrincipal();
                }
            }
        });
    });

</script>