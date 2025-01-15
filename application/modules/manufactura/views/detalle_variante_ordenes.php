<?php
$presentacion = obtenerPresentacion($variante->sku);
$factorPresentacion = ($presentacion[3] / $presentacion[0]);
$totalColumnas = 3;
if ($bonchado == 'T' || $bonchado == 'N') {
    $totalColumnas++;
}
if ($bonchado == 'T' || $bonchado == 'S') {
    $totalColumnas++;
}
if ($vestido == 'T' || $vestido == 'N') {
    $totalColumnas++;
}
if ($vestido == 'T' || $vestido == 'S') {
    $totalColumnas++;
}
?>

<?php if (sizeof($arrOrdenesLuxury) > 0) { ?>
    <tr>
        <td><div data-toggle="collapse" data-target="#det_items_<?= $variante->id . "_luxury" ?>"><b>Luxury</b> <?= (!$enpantalla ? sizeof($arrOrdenesLuxury) : sizeof($arrOrdenesLuxury)) ?> ord.</div></td>
        <?php
        if ($bonchado == 'T' || $bonchado == 'N') {
            ?>
            <td><button type = "button" class="btn btn-accion btn-small" data-accion="actualizarTotalVariante" data-variante_id="<?= $variante->id ?>"><?= ($totalLuxury - $totalLuxuryB) * $factorPresentacion ?></button></td>
        <?php } ?>
        <?php
        if ($bonchado == 'T' || $bonchado == 'S') {
            ?>
            <td><button type = "button" class="btn btn-accion btn-small" data-accion="actualizarTotalVariante" data-variante_id="<?= $variante->id ?>"><?= $totalLuxuryB * $factorPresentacion ?></button></td>
        <?php } ?>
        <?php
        if ($vestido == 'T' || $vestido == 'N') {
            ?>
            <td><button type = "button" class="btn btn-accion btn-small" data-accion="actualizarTotalVariante" data-variante_id="<?= $variante->id ?>"><?= ($totalLuxury - $totalLuxuryV) * $factorPresentacion ?></button></td>
        <?php } ?>
        <?php
        if ($vestido == 'T' || $vestido == 'S') {
            ?>
            <td><button type = "button" class="btn btn-accion btn-small" data-accion="actualizarTotalVariante" data-variante_id="<?= $variante->id ?>"><?= $totalLuxuryV * $factorPresentacion ?></button></td>
        <?php } ?>
        <td><button type = "button" class="btn btn-accion btn-small" data-accion="actualizarTotalVariante" data-variante_id="<?= $variante->id ?>"><?= $totalLuxury * $factorPresentacion ?></button></td>
        <td><?= $presentacion[1] ?></td>
    </tr>
    <?php
}
if (sizeof($arrOrdenesStandard) > 0) {
    ?>
    <tr>
        <td><div data-toggle="collapse" data-target="#det_items_<?= $variante->id . "_standard" ?>"><b>Standard</b> <?= (!$enpantalla ? sizeof($arrOrdenesStandard) : sizeof($arrOrdenesStandard)) ?> ord.</div></td>
        <?php if ($bonchado == 'T' || $bonchado == 'N') { ?>
            <td><button type = "button" class="btn btn-accion btn-small" data-accion="actualizarTotalVariante" data-variante_id="<?= $variante->id ?>"><?= ($totalStandard - $totalStandardB) * $factorPresentacion ?></button></td>
        <?php } ?>
        <?php if ($bonchado == 'T' || $bonchado == 'S') { ?>
            <td><button type = "button" class="btn btn-accion btn-small" data-accion="actualizarTotalVariante" data-variante_id="<?= $variante->id ?>"><?= $totalStandardB * $factorPresentacion ?></button></td>
        <?php } ?>
        <?php if ($vestido == 'T' || $vestido == 'N') { ?>
            <td><button type = "button" class="btn btn-accion btn-small" data-accion="actualizarTotalVariante" data-variante_id="<?= $variante->id ?>"><?= ($totalStandard - $totalStandardV) * $factorPresentacion ?></button></td>
        <?php } ?>
        <?php if ($vestido == 'T' || $vestido == 'S') { ?>
            <td><button type = "button" class="btn btn-accion btn-small" data-accion="actualizarTotalVariante" data-variante_id="<?= $variante->id ?>"><?= $totalStandardV * $factorPresentacion ?></button></td>
        <?php } ?>
        <td><button type = "button" class="btn btn-accion btn-small" data-accion="actualizarTotalVariante" data-variante_id="<?= $variante->id ?>"><?= $totalStandard * $factorPresentacion ?></button></td>
        <td><?= $presentacion[1] ?></td>
    </tr>
    <?php
}
if (sizeof($arrOrdenesSinWrap) > 0) {
    ?>
    <tr>
        <td><div data-toggle="collapse" data-target="#det_items_<?= $variante->id . "_sin" ?>"><b>Sin Wrap</b> <?= (!$enpantalla ? sizeof($arrOrdenesSinWrap) : sizeof($arrOrdenesSinWrap)) ?> ord.</div></td>
        <?php if ($bonchado == 'T' || $bonchado == 'N') { ?>
            <td><button type = "button" class="btn btn-accion btn-small" data-accion="actualizarTotalVariante" data-variante_id="<?= $variante->id ?>"><?= ($totalSin - $totalSinB) * $factorPresentacion ?></button></td>
        <?php } ?>
        <?php if ($bonchado == 'T' || $bonchado == 'S') { ?>
            <td><button type = "button" class="btn btn-accion btn-small" data-accion="actualizarTotalVariante" data-variante_id="<?= $variante->id ?>"><?= $totalSinB * $factorPresentacion ?></button></td>
        <?php } ?>
        <?php if ($vestido == 'T' || $vestido == 'N') { ?>
            <td><button type = "button" class="btn btn-accion btn-small" data-accion="actualizarTotalVariante" data-variante_id="<?= $variante->id ?>"><?= ($totalSin - $totalSinV) * $factorPresentacion ?></button></td>
        <?php } ?>
        <?php if ($vestido == 'T' || $vestido == 'S') { ?>
            <td><button type = "button" class="btn btn-accion btn-small" data-accion="actualizarTotalVariante" data-variante_id="<?= $variante->id ?>"><?= $totalSinV * $factorPresentacion ?></button></td>
        <?php } ?>
        <td><button type = "button" class="btn btn-accion btn-small" data-accion="actualizarTotalVariante" data-variante_id="<?= $variante->id ?>"><?= $totalSin * $factorPresentacion ?></button></td>
        <td><?= $presentacion[1] ?></td>            
    </tr>
<?php } ?>
