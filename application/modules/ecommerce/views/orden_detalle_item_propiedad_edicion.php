<div class="modal-header">
    <h5 class="modal-title" id="exampleModalLabel"><span id="modal_edicion_detalle">Edici&oacute;n <?= $orden_item_id ?></span></h5>
    <button type="button" class="close" onclick="$('#modalEdicion').modal('hide');" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<div class="modal-body">
    <?= form_open(base_url() . "ecommerce/orden/orden_item_propiedad_guardar", array("id" => "form_orden_item_propiedad_guardar")); ?>
    <?= (isset($orden_item_propiedad_id)) ? form_hidden("orden_item_propiedad_id", $orden_item_propiedad_id) : '' ?>
    <?= form_hidden("orden_item_id", $orden_item_id) ?>

    <div class="row">        
        <div class="col-12 col-md-4">
            <b>Propiedad:</b>
            <?php
            if (!isset($orden_item_propiedad)) {
                ?>                
                <select name="propiedad_id" id="propiedad_id" class="form-control input-lg" data-live-search="true" title="Selecci&oacute;n de Propiedad">
                </select>
                <?php
            } else {
                ?>                
                <b><?= $orden_item_propiedad->info_propiedad_descripcion ?></b>
                <?= form_hidden("propiedad_id", $orden_item_propiedad->propiedad_id) ?>
                <?php
            }
            ?>
        </div>
        <div id="texto"  class="col-12 col-md-8">            
            <?php
            $valor = '';
            if (isset($orden_item_propiedad)) {
                $valor = $orden_item_propiedad->valor;
                if ($orden_item_propiedad->propiedad_id == 12) {
                    $valor = str_replace(array("\\r\\n", "\r\n", "\r", "\n", "\\n"), "<br/>", $valor);
                    $valor = decodeEmoticons($valor);
                }
            }
            $arr = array(
                "id" => "valor",
                "name" => "valor",
                "label" => "<b>Valor</b>",
                "value" => (isset($orden_item_propiedad)) ? $valor : '',
                "tipo" => "textarea",
                "class" => "form-control input-lg"
            );
            echo item_input($arr);
            ?>
        </div>
        <div id="numero"  class="col-12 col-md-8">            
            <?php
            $valor_numero = '';
            if (isset($orden_item_propiedad)) {
                $valor_numero = $orden_item_propiedad->valor;
                if ($orden_item_propiedad->propiedad_id == 12) {
                    $valor_numero = str_replace(array("\\r\\n", "\r\n", "\r", "\n", "\\n"), "<br/>", $valor_numero);
                    $valor_numero = decodeEmoticons($valor_numero);
                }
            }
            $arr = array(
                "id" => "valor_numero",
                "name" => "valor_numero",
                "label" => "<b>Valor</b>",
                "value" => (isset($orden_item_propiedad)) ? $valor_numero : '',
                "tipo" => "number",
                "class" => "form-control input-lg"
            );
            echo item_input($arr);
            ?>
        </div>
    </div>
    <?= form_close(); ?>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-primary btn-guardar-modal">Guardar Cambios</button>
</div>

<script>
<?php
if (!isset($orden_item_propiedad)) {
    ?>
        console.log("Previo llenar select");
        console.log("ScritpUnaVezCargado en orden_detalle_item_propiedad_edicion");
        //llenarSelect("propiedad_id", '<?= base_url() ?>ecommerce/producto/propiedades_select', {"orden_item_id": <?= isset($orden_item_id) ? $orden_item_id : 0 ?>}, false);
    <?php
} else {
    
}
?>
    console.log("Posterior llenar select");
    $("body").delegate("#propiedad_id", "change", function (e) {
        console.log("se selecciona el valor " + $('#propiedad_id').val());
        $('#texto').hide();
        $('#numero').hide();
        var sku_comparar = $('#propiedad_id option:selected').attr('data-nombre');
        if (sku_comparar.includes("AGR_")) {
            $('#numero').show();
            console.log($('#propiedad_id').val());
        } else {
            $('#texto').show();
        }
    });


    var cuantos = 0;
    $("body").delegate("#modalEdicion .btn-guardar-modal", "click", function (e) {
        e.preventDefault();
        $("#form_orden_item_propiedad_guardar").submit();
    });

    $(document).on('submit', '#form_orden_item_propiedad_guardar', function (e) {
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

<div class="modal" id="modalOrdenItemPropiedadEdicion" tabindex="-1" role="dialog" aria-labelledby="wsanchez" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">

        </div>
    </div>
</div>