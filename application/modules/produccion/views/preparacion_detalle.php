<?php
if ($producto_id != 0) {
    $fila_det = 0;
    foreach ($listadoProductos as $k => $item) {
        $producto = $item['producto'];

        echo '<div class="row col-12 ' . ($k % 2 === 0 ? 'color_par' : 'color_impar') . '" id="producto_' . $producto->id . '">';
        echo '<button type = "button" class="btn btn-accion-producto" data-id="' . $producto->id . '"><b>' . $producto->titulo . '</b></button>';
        if (!array_key_exists('variantes', $item)) {
            echo '<div class="row col-9 offset-3">No existen ordenes pendientes de preparar</div>';
            continue;
        }
        $variantes = $item['variantes'];
        ?>
        <div class="row col-12">
            <?php
            foreach ($variantes as $j => $variante) {
                List($divisor, $unidad, $assemble) = obtenerPresentacion($variante->sku);

                if ($variante->cantidad == 0) {
                    $variante->cantidad = 1;
                }
//                if ($divisor=0 || $divisor == null){
//                     $divisor = 1;
//                 }
                ?>
                <div class="row col-12 table-striped <?= (($k + $j + 1) % 2 === 0 ? 'color_par' : 'color_impar') ?>  align-items-start" id="variante_<?= $variante->id ?>">
                    <div class="row col-3 align-items-start">
                        <div class="row col-11 offset-1 align-items-start"><?= $variante->titulo ?></div>
                        <div class="row col-11 offset-1 align-items-start text-left">
                            <p style="text-align:left">
                                <?php foreach ($variante->ordenes as $ord) {
                                    ?>                                                        
                                    <a href="#modalOrden" class="btn btn-orden-numero align-items-start align-self-start" data-toggle="modal" data-target="#modalOrden" data-orden_id="<?= $ord->id ?>" style="text-align:left; font-size: 0.75em; padding:0">
                                        <b><?= $ord->tienda_alias ?>_<?= isset($ord->referencia_order_number) ? $ord->referencia_order_number : $ord->id ?></b>
                                    </a>
                                <?php }
                                ?>
                            </p>
                        </div>
                    </div>
                    <div class="col-1 text-center"><?= (round(($variante->cantidad_pedida * $variante->cantidad) / $divisor)) ?> &nbsp;</div>
                    <div class="col-1 text-center"><?= $unidad ?> &nbsp;</div>
                    <div class="row col-7">
                        <?php
                        foreach ($variante->propiedades as $p => $prop) {//                            $class_propiedad = ($fila_det % 2 == 0) ? "color_par" : "color_impar";
                            if (sizeof($prop['valores']) == 0) {
                                continue;
                            }
                            ?>
                            <div class="row col-12 <?= (($k + $j + 1) % 2 === 0 ? 'color_par' : 'color_impar') ?>">
                                <div class="col-2 text-left"><?= $prop['propiedad_descripcion'] ?></div>
                                <div class="row col-10 text-left">                                                                
                                    <?php
                                    foreach ($prop['valores'] as $v => $valor) {
//                                        $pos = strpos($v, 'No Vase');
//                                        if ($pos > -1) {
//                                            break;
//                                        }
//                                        $pos = strpos($v, 'Add Loose');
//                                        if ($pos > -1) {
//                                            break;
//                                        }
                                        ?>
                                        <div class="row col-12">
                                            <div class="col-4 text-left"><?php
                                                $pos = strpos($v, '(');
                                                if ($pos) {
                                                    $v = substr($v, 0, $pos);
                                                }
                                                $pos = strpos($v, '[');
                                                if ($pos) {
                                                    $v = substr($v, 0, $pos);
                                                }
                                                echo $v;
                                                ?></div>
                                            <div class="col-1"><?= $valor['numero'] ?></div>
                                            <div class="col-7">
                                                <?php foreach ($valor['ordenes'] as $vord) {
                                                    ?>
                                                    <a href="#modalOrden" class="btn btn-orden-numero" data-toggle="modal" data-target="#modalOrden" data-orden_id="<?= $vord->id ?>" style="text-align:left; font-size: 0.75em; padding:0">
                                                        <b><?= $vord->tienda_alias ?>_<?= isset($vord->referencia_order_number) ? $vord->referencia_order_number : $ord->id ?></b>
                                                    </a>
                                                    <?php
                                                }
                                                ?>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                </div>
                            </div>
                            <?php
                            $fila_det++;
                        }
                        ?>
                    </div>
                </div>
                <?php
            }
            echo '</div>';
            ?>
        </div>
        <?php
    }
} else if ($listadoProductos === -1) {
    echo "No hay resultados";
} else if ($listadoProductos) {
    ?>                            

    <?php
    $producto_actual = false;
    $producto = 0;
    foreach ($listadoProductos as $k => $item) {
        if (($preparado == 'N') && ($item->orden_item_variante_cantidad == $item->orden_item_variante_cantidad_preparado)) {
            continue;
        } else if (($preparado == 'S') && ($item->orden_item_variante_cantidad != $item->orden_item_variante_cantidad_preparado)) {
            continue;
        }
        if ($producto_actual !== $item->producto_id) {
            //nueva linea
            echo ($producto_actual ? '</div>' : '');
            echo '<div class="col-12 color-par">&nbsp;</div>';
            echo '<div class="row col-12 ' . ($producto % 2 === 0 ? 'color_par' : 'color_impar') . '" id="' . $item->producto_id . '">';
            $producto_actual = $item->producto_id;
            ?>
            <div class="row col-12">
                <div class="col-6 text-left">
                    <button type = "button" class="btn btn-accion-producto" data-id="<?= $item->producto_id ?>"><b><?= $item->producto_titulo ?></b></button>
                </div>
                <div class="col-1 text-center align-self-center"><b>Largo</b></div>
                <div class="col-1 text-center align-self-center"><b>Unidad</b></div>                
                <div class="col-1 text-center align-self-center"><b>Total</b></div>
                <div class="col-2 text-center align-self-center"><i class="fas fa-spa"></i></div>
                <div class="col-1 text-center align-self-center"><b>Faltan</b></div>
            </div>
            <?php
//            $producto++;
        }
        $assemble = false;
//        $docena = false;
        List($divisor, $unidad, $assemble) = obtenerPresentacion($item->variante_sku);
        if ($item->variante_cantidad == 0) {
            $item->variante_cantidad = 1;
        }
        $total = $item->orden_item_variante_cantidad * $item->variante_cantidad;
        $totalPreparados = $item->orden_item_variante_cantidad_preparado * $item->variante_cantidad;
        $totalPendientesPreparados = $total - $totalPreparados;
        ?>
        <div class="row col-12  border-dark border" id="variante_total_<?= $item->variante_id ?>">
            <div class="col-5 offset-1 text-left border-dark border-right border-bottom-0"><?= $item->variante_titulo . " (" . $item->variante_sku . ")" ?></div>
            <div class="col-1 text-right border-dark border-right border-bottom-0"><?= $item->largo_cm ?>cm.</div>
            <div class="col-1 text-right border-dark border-right border-bottom-0"><?= $unidad ?></div>
            <div class="col-1 text-right border-dark border-right border-bottom-0"><?= round($total / $divisor) ?></div>
            <div class="col-2 text-right border-dark border-right border-bottom-0">
                <?php
                if ($totalPendientesPreparados != 0 && !$assemble) {
                    $arr = array(
                        "id" => "restante_variante_" . $item->variante_id,
                        "name" => "restante_variante_" . $item->variante_id,
                        "value" => round($totalPendientesPreparados / $divisor),
                        "tipo" => 'number',
                        "max" => round($totalPendientesPreparados / $divisor),
                        "step" => $divisor,
                        "data-varante-id" => $item->variante_id,
                        "clase" => "col-4 restante_variante",
                    );
                    echo item_input($arr);
                    ?>
                    &nbsp;<button type = "button" class="btn btn-primary btn-accion-variante h-100" id="btn-guardar-<?= $item->variante_id ?>" data-variante-id="<?= $item->variante_id ?>"><i class="fas fa-check fa-xs"></i></button>
                    <?php
                } else {
                    if (!$assemble) {
                        echo (round(($totalPendientesPreparados) / $divisor));
                    } else {
                        echo '-';
                    }
                }
                ?>
            </div>
            <div class="col-1 text-right border-bottom-0">
                <?= (round(($totalPendientesPreparados) / $divisor)) ?> &nbsp;
            </div>
        </div>

        <?php
    }
    echo "</div>";
}
?>
