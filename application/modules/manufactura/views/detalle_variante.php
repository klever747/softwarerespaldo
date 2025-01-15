<?php
if (sizeof($arrOrdenes) == 0) {
    return "";
}
$presentacion = obtenerPresentacion($variante->sku);
$factorPresentacion = ($presentacion[3] / $presentacion[0]);
$colspan = 1;
$rowspan = 1;
?>
<?php if ($enpantalla) { ?>
    <div class="row col-12 col-md-6">
        <div class="col-12">
            <button type = "button" class="btn btn-accion btn-small text-left texto-oculto-expandible" data-accion="refrescar_variante" data-variante_id="<?= $variante->id ?>" >
                <b><?= $variante->titulo ?></b>
            </button>
        </div>
        <div class="col-12">
            <div data-toggle="collapse" data-target="#det_items_<?= $variante->id . "_full" ?>"><?= sizeof($arrOrdenes) ?> ordenes</div>
            <?= procesarListaOrdenes($variante->id, "full", $arrOrdenes, "Todas"); ?>
            <?= procesarListaOrdenes($variante->id, "luxury", $arrOrdenesLuxury, "Luxury"); ?>
            <?= procesarListaOrdenes($variante->id, "standard", $arrOrdenesStandard, "Standard"); ?>
            <?= procesarListaOrdenes($variante->id, "sin", $arrOrdenesSinWrap, "Sin Wrap"); ?>
        </div>
    </div>
    <div class="col-12 col-md-6">
    <?php } ?>
    <table class="table table-striped table-bordered">
        <thead class="thead-dark">
            <?php if (!$enpantalla) { ?>
                <tr>
                    <th><b><?= $variante->titulo ?></b></th>
                </tr>
            <?php } ?>
            <tr>
                <th colspan="1" rowspan="2" class="col-4"><b>cm</b></th>
                <?php if ($bonchado) { ?>
                    <th colspan="<?= ($bonchado == 'T' ? 2 : 1) ?>"><?= ($enpantalla ? '<i class="fas fa-spa col-12"></i>' : '<b>Bonchado</b>') ?></th>
                <?php } ?>
                <?php if ($vestido) { ?>
                    <th colspan="<?= ($vestido == 'T' ? 2 : 1) ?>"><?= ($enpantalla ? '<i class="fas fa-tshirt col-12"></i>' : '<b>Vestido</b>') ?></th>
                <?php } ?>
                <th colspan="1" rowspan="2" class="col-1"><b>Total</b></th>
                <th colspan="1" rowspan="2" class="col-1"><b>Unidad</b></th>
            </tr>
            <tr>
                <?php if ($bonchado) { ?>
                    <?php if ($bonchado == 'T' || $bonchado == 'N') { ?>
                        <th colspan="1"><?= ($enpantalla ? '<i class="fas fa-times"></i>' : 'N') ?></th>
                        <?php if ($bonchado == 'T' || $bonchado == 'S') { ?>
                            <th colspan="1"><?= ($enpantalla ? '<i class="fas fa-check"></i>' : 'S') ?></th>
                        <?php } ?>
                        <?php
                    }
                }
                ?>
                <?php if ($vestido) { ?>
                    <?php if ($vestido == 'T' || $vestido == 'N') { ?>
                        <th colspan="1"><?= ($enpantalla ? '<i class="fas fa-times"></i>' : 'N') ?></th>
                    <?php } ?>
                    <?php if ($vestido == 'T' || $vestido == 'S') { ?>
                        <th colspan="1"><?= ($enpantalla ? '<i class="fas fa-check"></i>' : 'S') ?></th>
                        <?php
                    }
                }
                ?>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><?= $variante->largo_cm ?></td>
                <?php if ($bonchado) { ?>
                    <?php if ($bonchado == 'T' || $bonchado == 'N') { ?>
                        <td><button type = "button" class="btn btn-accion btn-small" data-accion="actualizarTotalVariante" data-variante_id="<?= $variante->id ?>"><?= ($det->total - $det->total_bonchado) * $factorPresentacion ?></button></td>
                    <?php } ?>
                    <?php if ($bonchado == 'T' || $bonchado == 'S') { ?>
                        <td><button type = "button" class="btn btn-accion btn-small" data-accion="actualizarTotalVariante" data-variante_id="<?= $variante->id ?>"><?= ($det->total_bonchado) * $factorPresentacion ?></button></td>
                        <?php
                    }
                }
                ?>
                <?php if ($vestido) { ?>
                    <?php if ($vestido == 'T' || $vestido == 'N') { ?>
                        <td><button type = "button" class="btn btn-accion btn-small" data-accion="actualizarTotalVariante" data-variante_id="<?= $variante->id ?>"><?= ($det->total - $det->total_bonchado) * $factorPresentacion ?></button></td>
                    <?php } ?>
                    <?php if ($vestido == 'T' || $vestido == 'S') { ?>
                        <td><button type = "button" class="btn btn-accion btn-small" data-accion="actualizarTotalVariante" data-variante_id="<?= $variante->id ?>"><?= $det->total_bonchado * $factorPresentacion ?></button></td>
                        <?php
                    }
                }
                ?>
                <td><button type = "button" class="btn btn-accion btn-small" data-accion="actualizarTotalVariante" data-variante_id="<?= $variante->id ?>"><?= $det->total * $factorPresentacion ?></td>
                <td><?= $presentacion[1] ?></td>
            </tr>
            <?= $detalle_ordenes ?>
            <?php if (!$enpantalla) { ?>
                <tr>
                    <th colspan="7" style="text-align:left"><?= procesarListaOrdenes($variante->id, "full", $arrOrdenes, "Todas", false, false); ?></th>
                </tr>
            <?php } ?>
        </tbody>
    </table>
    <?php if ($enpantalla) { ?>
    </div>
<?php } ?>