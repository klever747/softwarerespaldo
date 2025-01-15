<div class="card-body text-center" id="orden_detalle_cliente_edicion">
    <div class="card-header">
        <h3 class="card-title">Selecci&oacute;n de Cliente</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-accion_detalle btn-tool" value="agregar"><i class="fas fa-plus"></i></button>
        </div>
    </div>
    <div class="row">
        <?= $clientes_det; ?>
    </div>
</div>
<script>
    $(document).on('click', '#orden_detalle_cliente .btn-accion', function () {
        unsoloclick('.btn-accion');
        if ($(this).val() === "editar_cliente_orden") {
            llamadaAjax(false, '<?= base_url() ?>ecommerce/cliente_obtener', {"id": $(this).data('id'), "mostrar_direccion_envio": 0}, mostrarEdicion);
        } else if ($(this).val() === "cambiar_cliente_orden") {
            llamadaAjax(false, '<?= base_url() ?>ecommerce/clientes_listado', {"cliente_id": <?= (!$cliente) ? 0 : $cliente->id ?>, "orden_id": <?= $orden->id ?>}, mostrarEdicion);
        }
    });

</script>