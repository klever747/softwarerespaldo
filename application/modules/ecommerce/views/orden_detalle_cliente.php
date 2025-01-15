<div class="card card-default" id="orden_detalle_cliente">
    <div class="card-header">
        <h6 class="card-title" data-toggle="collapse" data-target="#det_cliente">
            <?php
            if ($cliente) {
                echo "Comprador: " . $cliente->id;
                ?>
                <?= $cliente->nombres . " " . $cliente->apellidos . " " ?>
                <?=
                ($cliente->nombre_comercial != null) ?
                        (($cliente->nombres || $cliente->apellidos) ? "/" : "") . " " . $cliente->nombre_comercial : ''
                ?>
                <?php
            } else {
                echo "Sin Cliente";
            }
            ?>
        </h6>
        <div class="card-tools">
            <?php if ($cliente) {
                ?>
                <button type = "button" class="btn btn-accion btn-tool visible_<?= $no_editable ?>" data-id="<?= $cliente->id ?>" value="editar_cliente_orden"><i class="fas fa-pencil-alt"></i></button>
                <!--<button type = "button" class="btn btn-accion btn-tool" data-id="<?= $cliente->id ?>" value="cambiar_cliente_orden"><i class="fas fa-exchange-alt"></i></button>-->
            <?php } ?>

        </div>
    </div>

    <div class="card-body collapse" id="det_cliente"> 
        <?php if ($cliente) { ?>
            <div class="row text-left">
                <div class="col-12 row"><h6 class="col-3 col-lg-auto"><b>Direcci&oacute;n: </b></h6><h6 class="col-9"><?= $cliente->country_code . " / " . $cliente->state_code . " / " . $cliente->address ?></h6></div>
                <div class="col-12 col-lg-6 row"><h6 class="col-auto"><b>Tel&eacute;fono: </b></h6><h6 class="col-9"><?= $cliente->phone ?></h6></div>
                <div class="col-12 col-lg-6 row"><h6 class="col-auto"><b>Email: </b></h6><h6 class="col-9"><?= $cliente->email ?></h6></div>
            </div>
            <?php
        }
        ?>
    </div>
</div>

<script>


</script><!--


<script>
    $(document).on('click', '#clientes_listado #btn_buscar', function () {
        $(this).html(loadingBtn);
        $(this).attr('disabled', true);

        console.log("btn buscar en clientes_listado.php");
        $("#clientes_listado #form_busqueda").submit();

        return false;
    });

    $(document).on('submit', '#clientes_listado #form_busqueda', function (e) {

        console.log("en el submit de clientes_listado.php")
        e.preventDefault(); // avoid to execute the actual submit of the form.

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
                    mostrarEdicion(data);
                }
            }
        });
    });


</script>-->