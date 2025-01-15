<table class="table table-striped">
    <thead>
        <tr>
            <th>#</th>
            <th>Store</th>
            <th>Referencia</th>
            <th>Orden</th>
            <th>Caja</th>
            <th>Tipo</th>
            <th>Kardex</th>
            <th>Tracking Number</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if ($cajas) {
            foreach ($cajas as $i => $caja) {
                ?>
                <tr>
                    <td><?= ($i + 1) ?></td>
                    <td><?= $caja->store_alias ?></td>
                    <td><?= $caja->referencia_order_number ?></td>
                    <td><?= $caja->orden_id ?></td>
                    <td><?= $caja->orden_caja_id ?></td>
                    <td><?= $caja->caja_nombre ?></td>
                  <!--   <td><?= $caja->kardex_check ?></td> -->
                    <td>     <?= mostrarEstilos($caja->kardex_check); ?></td>
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