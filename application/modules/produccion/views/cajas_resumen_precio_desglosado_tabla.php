<?php
if ($arrTotales) {
    foreach ($arrTotales as $empresa_id => $empresa) {
        ?>        
        <table class="table table-striped table-bordered">                                            
            <thead>
                <tr class="bg-primary">
                    <th colspan="7"><b><?= print_r($empresa['nombre_tienda'], true) ?></b></th>
                </tr>
                <tr>
                    <th></th>
                    <th width="20%">$/Caja</th>
                    <th width="20%">Stems/Caja</th>
                    <th width="10%">#Cajas</th>
                    <th width="10%">#Stems</th>                                                        
                    <th width="10%">Largo</th>
                    <th width="20%">Total $</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th><?= $empresa['store_total_cajas'] ?></th>
                    <th><?= $empresa['store_total_stems'] ?></th>
                    <th></th>
                    <th><?= $empresa['store_total_dolar'] ?></th>
                </tr>
                <?php
                foreach ($empresa['cajas'] as $caja_id => $caja) {
                    if (sizeof($caja['caja_cajas_id']) == 0) {
                        continue;
                    }
                    ?>
                    <tr>
                        <th class="bg-black text-left">
                            <?= $caja['nombre_caja'] ?></th>
                        <th></th>
                        <th></th>
                        <th><?= sizeof($caja['caja_cajas_id']) ?></th>
                        <th><?= $caja['caja_total_stems'] ?></th>
                        <th></th>
                        <th><?= number_format($caja['caja_total_dolar'], 2) ?></th>
                    </tr>
                    <?php
                    foreach (array("T" => "Solo Tinturados", "N" => "Solo Naturales", "M" => "Mixtos") as $t => $ton) {

                        if (!array_key_exists($t, $caja)) {
                            continue;
                        }
                        ?>
                        <tr>
                            <th colspan="2" class="bg-black text-left"><?= $caja['nombre_caja'] . " " . $ton ?></th>
                            <th></th>
                            <th><?= sizeof($caja[$t]['cajas_id']) ?></th>
                            <th><?= $caja[$t]['total_stems'] ?></th>
                            <th></th>
                            <th><?= number_format($caja[$t]['total_dolar'], 2) ?></th>
                        </tr>
                        <?php
                        $resumen_ordenes = '';

                        foreach ($caja[$t]['precios'] as $precio_caja => $caja_precio) {
                            foreach ($caja[$t]['precios'][$precio_caja]['largo_cm'] as $largo_cm => $det) {
                                foreach ($caja[$t]['precios'][$precio_caja]['largo_cm'][$largo_cm] as $stemsxcaja => $det) {
                                    $resumen_ordenes .= procesarListaOrdenes($empresa_id . "_" . $caja_id . "_" . $t, $precio_caja, $det['cajas_id'], "$ " . $precio_caja);
                                    ?>
                                    <tr>
                                        <th colspan="7" class="bg-black text-left" data-toggle="collapse" data-target="#det_items_<?= $empresa_id . "_" . $caja_id . "_" . $t . "_" . reemplazarPunto($precio_caja, "_") ?>">
                                            <?= $caja['nombre_caja'] . " " . $ton . " " . $largo_cm ?> cm.
                                        </th>
                                    </tr>
                                    <tr>
                                        <th>

                                        </th>
                                        <td>$ <?= number_format($precio_caja, 2) ?></td>
                                        <td><?= $stemsxcaja ?></td>
                                        <td><?= sizeof($det['cajas_id']) ?></td>
                                        <td><?= $stemsxcaja * sizeof($det['cajas_id']) ?></td>
                                        <td><?= $largo_cm ?> cm.</td>
                                        <td>$ <?= number_format($det['total_dolar'], 2) ?></td>
                                    </tr>
                                    <?php foreach ($det['resumen_caja'] as $kkk => $vvv) {
                                        ?>
                                        <tr>
                                            <th rowspan="<?= sizeof($vvv['detalle']) + 1 ?>"><?= procesarListaOrdenes($vvv['alias'], $vvv['alias'], array($kkk => $vvv['alias']), "", 0, false) ?></th>
                                            <th>Producto/variante</th>
                                            <th>Ingrediente</th>
                                            <th>#Stems</th>
                                            <th>$/Stem</th>
                                            <th>largo_cm</th>
                                            <th>Total</th>
                                        </tr>
                                        <?php
                                        foreach ($vvv['detalle'] as $linea_det) {
                                            ?>
                                            <tr>
                                                <td class="text-left"><?= $linea_det['producto_variante'] ?></td>
                                                <td class="text-left"><?= $linea_det['ingrediente'] ?></td>
                                                <td><?= $linea_det['stems'] ?></td>
                                                <td>$ <?= number_format($linea_det['pps'], 2) ?></td>
                                                <td><?= $linea_det['largo_cm'] ?> cm.</td>
                                                <td>$ <?= number_format($linea_det['pagado'], 2) ?></td>
                                            </tr>
                                            <?php
                                        }
                                    }
                                }
                            }
                        }
                        ?>
                        <tr>
                            <td></td>
                            <td colspan="6"><?= $resumen_ordenes ?></td>
                        </tr>
                        <?php
                    }
                }
                ?>
            </tbody>
        </table>
        <?php
    }
}
?>
                        