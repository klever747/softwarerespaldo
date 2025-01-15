<div class="card card-default" id="clientes_listado">
    <div class="card-header">
        <h3 class="card-title">Listado de Clientes</h3>
    </div>

    <?= filtroBusqueda("ecommerce/clientes_listado", array("texto_busqueda" => $texto_busqueda, "store_id" => $store_id, "estado_id" => $estado_id, "sel_store" => $sel_store, "regpp" => $regpp)); ?>

    <div class="card-body text-center"> 
        <?= isset($itemsPaginacion) ? ($itemsPaginacion) : ''; ?>
    </div>
    <div class="card-body text-center">
        <div class="">
            <?php
            if ($clientes) {
                ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Nombres</th>
                            <th scope="col">Apellidos</th>
                            <th scope="col">Nombre Comercial</th>
                            <th scope="col">Pais</th>
                            <th scope="col">Email</th>
                            <th scope="col"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($clientes as $k => $cliente) {
                            ?>
                            <tr <?= $cliente->id === $cliente_id ? "style='background-color: green;'" : "" ?> >
                                <th scope="row"><?= $k + 1 ?></th>
                                <td class="text-left"><?= $cliente->nombres ?></td>
                                <td class="text-left"><?= $cliente->apellidos ?></td>
                                <td class="text-left"><?= $cliente->nombre_comercial ?></td>
                                <td class="text-left"><?= $cliente->country ?></td>
                                <td class="text-left"><?= $cliente->email ?></td>


                                <td>
                                    <?php
                                    if ($cliente->id != $cliente_id) {
                                        ?>
                                        <button type = "button" class="btn btn-accion btn-tool" data-id="<?= $cliente->id ?>" value="seleccionar" data-dismiss="modal"><i class="fas fa-check"></i></button>
                                        <?php
                                    }
                                    ?>
                                </td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>
                <?php
            }
            ?>
        </div>
    </div>
    <div class="card-body justify-content-md-center align-items-start"> 
        <?= isset($itemsPaginacion) ? ($itemsPaginacion) : ''; ?>
    </div>
</div>

<script>

    function confirmarClienteDeOrdenCambiado(r) {
        console.log("confirmarClienteDeOrdenCambiado");
        if (r.error) {
            mostrarError(r.respuesta);
        } else {
            console.log("vamos a recargar principal");
            recargarPrincipal();
        }
    }
    function actualizarOrdenCliente(orden_id, nuevo_cliente_id) {
        llamadaAjax(false, '<?= base_url() ?>ecommerce/actualizar_cliente_orden', {"orden_id": orden_id, "cliente_id": nuevo_cliente_id}, confirmarClienteDeOrdenCambiado);
    }

    $(document).on('click', '#clientes_listado .btn-accion', function () {
        if ($(this).val() === "seleccionar") {
            console.log("nuevo cliente");
            console.log($(this).data('id'));
            actualizarOrdenCliente(<?= $orden_id ?>, $(this).data('id'));
        }
    });

</script>