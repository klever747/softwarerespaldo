<table class="table table-striped">
    <thead>
        <tr>
            <th>Store</th>
            <th>Referencia</th>
            <th>Orden</th>
            <th>Caja</th>
            <th>Tipo</th>
            <th>Tracking Number</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if ($cajas) {
            foreach ($cajas as $caja) {
                ?>
                <tr>
                    <td><?= $caja->store_alias ?></td>
                    <td><?= $caja->referencia_order_number ?></td>
                    <td><?= $caja->orden_id ?></td>
                    <td><?= $caja->orden_caja_id ?></td>
                    <td><?= $caja->caja_nombre ?></td>
                    <td><?= $caja->tracking_number ?></td>
                </tr>
                <?php
            }
        }
        ?>
        <tr>
        </tr>
    </tbody>
</table>