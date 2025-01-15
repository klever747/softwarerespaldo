<div class="modal-header">
    <h5 class="modal-title" id="exampleModalLabel"><span id="modal_edicion_detalle">
            <b><?= strtoupper($orden_item->info_producto_titulo) ?></b>
            <br/>
            <?= strtoupper($orden_item->info_variante_titulo) . " (" . $orden_item->info_variante_sku . ")" ?>
        </span></h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<div class="modal-body">   
    <?= form_open(base_url() . "produccion/logistica/orden_item_meter_caja", array("id" => "form_orden_item_logistica_guardar")); ?>
    <?= (isset($orden_item_id)) ? form_hidden("orden_item_id", $orden_item_id) : '' ?>

    <div class="row">
        <div class="col-12 col-md-5">
            <label>Caja</label>
            <select name="orden_caja_id" id="orden_caja_id" class="form-control input-lg" title="Selección de Caja">
                <option value="-1">Nueva</option>
                <?php
                foreach ($orden_cajas as $k => $orden_caja) {
                    echo "<option value='" . $orden_caja->id . "'>#" . ($k + 1) . " >>>> " . $orden_caja->info_nombre_caja . " >>>> " . $orden_caja->info_finca_caja . " (" . $orden_caja->id . ")</option>";
                }
                ?>
            </select>
        </div>
        <div class="col-12 col-md-5" id="bloque_finca_id">
            <label>Finca</label>
            <select name="finca_id" id="finca_id" class="form-control input-lg" title="Selección de Finca">
                <?php
                foreach ($sel_finca as $k => $finca) {
                    echo "<option value='" . $finca->id . "'> " . $finca->nombre . " </option>";
                }
                ?>
            </select>
        </div>
        <div class="col-12 col-md-7" id="bloque_tipo_caja_id">
            <label>Tipo</label>
            <select name="tipo_caja_id" id="tipo_caja_id" class="form-control input-lg" title="Selección de Caja">
                <?php
                foreach ($sel_tipo_caja as $tipo_caja) {
                    echo "<option value='" . $tipo_caja->id . "'>" . $tipo_caja->nombre . "</option>";
                }
                ?>
            </select>
        </div>
        <div class="col-12 col-md-10" id="bloque_contenido_caja_id" style="display:none">
            <label>Contiene</label>
            <div id="contenido_caja"></div>
        </div>
    </div>
    <?= form_close(); ?>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-primary btn-guardar-modal">Guardar Cambios</button>
</div>

<script>
    function obtenerContenidoCaja(data) {
        var contenido = "Sin contenido";
        if (!data.error) {
            obj = data.respuesta;
            console.log(obj);
            contenido = "";
            for (var i = 0; i < obj.length; ++i) {
                contenido += "<b>" + obj[i]['info_producto_titulo'] + "</b>" + " | " + obj[i]['info_variante_titulo'] + " Stems " + obj[i]['totalStems']['sum'] + "<br/>";
                console.log(contenido);
            }
        }
        $("#contenido_caja").html(contenido);
    }
    $('#orden_caja_id').on("change", function () {
        if ($(this).val() === '-1') {
            $('#bloque_tipo_caja_id').css("display", "block");
            $('#bloque_finca_id').css("display", "block");
            $('#bloque_contenido_caja_id').css("display", "none");
        } else {
            $('#bloque_tipo_caja_id').css("display", "none");
            $('#bloque_finca_id').css("display", "none");
            $('#bloque_contenido_caja_id').css("display", "block");
            llamadaAjax(false, '<?= base_url() ?>produccion/logistica/obtener_contenido_caja', {"orden_caja_id": $(this).val()}, obtenerContenidoCaja);
        }
    });

    $("body").delegate("#modalEdicion .btn-guardar-modal", "click", function (e) {
        e.preventDefault();
        $("#form_orden_item_logistica_guardar").submit();
    });

    $(document).on('submit', '#form_orden_item_logistica_guardar', function (e) {

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
                    recargarPrincipal();
                }
            }
        });
    });
</script>