<?php
if (!$listadoProductos) {
    echo '<div class="col-12 text-center">Sin detalle</div>';
    return;
}
?>



<?php
foreach ($listadoProductos as $k => $reg) {

    $producto_actual = false;
    $producto = 0;
    $item = $reg['producto'];

    $conVariantes = 0;
    foreach ($reg['variantes'] as $v => $w) {
        if (sizeof($w) > 0) {
            if (sizeof($w[0]->ordenes) > 0) {
                $conVariantes++;
                break;
            }
        }
    }
    if ($conVariantes == 0) {
        continue;
    }
//
//    if ($preparado == 'T') {
//        if ($terminado == 'S') {
//            if ($item->orden_item_variante_cantidad != $item->orden_item_variante_cantidad_terminado) {
//                continue;
//            }
//        } else if ($terminado == 'N') {
//            if ($item->orden_item_variante_cantidad == $item->orden_item_variante_cantidad_terminado) {
//                continue;
//            }
//        }
//    } else {
//        if ($preparado == 'N') {
//            if ($item->orden_item_variante_cantidad == $item->orden_item_variante_cantidad_preparado) {
//                continue;
//            }
//        } else if ($preparado == 'S') {
////            print_r("Preparado");
////        if ($item->orden_item_variante_cantidad != $item->orden_item_variante_cantidad_preparado) {
////            continue;
////        }
//        }
//    }
    ?>                            

    <div class="row col-12 <?= ($producto % 2 === 0 ? 'color_par' : 'color_impar') ?>" id="<?= $wrap . "_" . $item->producto_id ?>">
        <div class="row col-12">
            <div class="col-5 text-left">
                <button type = "button" class="btn btn-accion-producto" data-id="<?= $item->producto_id ?>"><b><?= $item->producto_titulo ?></b></button>
            </div>
            <div class="col-1 text-center align-self-center"><b>Largo</b></div>
            <div class="col-1 text-center align-self-center"><b>Unidad</b></div>                
            <div class="col-1 text-center align-self-center"><b>Wrap</b></div>                
    <!--            <div class="col-1 text-center align-self-center"><i class="fas fa-spa"></i></div>-->
            <div class="col-1 text-center align-self-center"><i class="fas fa-tshirt"></i></div>
            <div class="col-2 text-center align-self-center">INGRESO</div>
            <div class="col-1 text-center align-self-center"><b>Faltan</b></div>
        </div>
        <?php
        $k = $j = 0;
//        if (sizeof($reg['variantes'])==0){
//            continue;
//        }

        foreach ($reg['variantes'] as $v => $w) {
//            $docena = false;
            if (sizeof($w) == 0) {
                continue;
            }
//            print_r(">>>>>>>>>>>>>>>>>".$v."<<<<<<<<<<<<<<<<<<");
//            print_r(">>>>>>>>>>>>>>>>>".print_r(sizeof($w),true)."<<<<<<<<<<<<<<<<<<");
            $variante = $w[0];
            $totalOrden = 0;
            $totalPreparados = $totalPendientesPreparados = 0;
            $totalTerminados = $totalPendientesTerminados = 0;
            $assemble = false;
            $k++;
            List($divisor, $unidad, $assemble) = obtenerPresentacion($variante->sku);
            if ($variante->cantidad == 0) {
                $variante->cantidad = 1;
            }
//            if ($variante->cantidad == 0) {
//                $variante->cantidad = 1;
//                $docena = "Unidad(es)";
//                $assemble = true;
//            }
//            if (!$docena) {
//                $docena = ($variante->cantidad % 12 === 0) ? 'Docenas' : 'Tallos';
//            }
            if (sizeof($variante->ordenes) > 0) {
                foreach ($variante->ordenes as $orden) {
                    $totalOrdenActual = $orden->orden_item_cantidad * $variante->cantidad;
                    $totalOrden += $totalOrdenActual;
//                    if ($orden->preparado == 'S') {
//                        $totalPreparados += $totalOrden;
//                    } else {
//                        $totalPendientesPreparados += $totalOrden;
//                    }
                    if ($orden->terminado == 'S') {
                        $totalTerminados += $totalOrdenActual;
                    } else {
                        $totalPendientesTerminados += $totalOrdenActual;
                    }
                    $j++;
                }
                ?>        
                <div class="row col-12  border-dark border" id="variante_total_<?= $wrap . "_" . $variante->id ?>">                    
                    <div class="col-5 text-left border-dark border-right border-bottom-0" data-toggle="collapse" data-target="#detalle_<?= $wrap . "_" . $variante->id ?>">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= $variante->titulo . " (" . $variante->sku . ")" ?></div>
                    <div class="col-1 text-right border-dark border-right border-bottom-0"><?= $variante->largo_cm ?>cm. &nbsp;</div>
                    <div class="col-1 text-right border-dark border-right border-bottom-0"><?= $unidad ?></div>
                    <div class="col-1 text-right border-dark border-right border-bottom-0"><?= $wrap ?></div>
                    <div class="col-1 text-right border-dark border-right border-bottom-0"><?= round($totalTerminados / $divisor); ?></div>
                    <div class="col-2 text-right border-dark border-right border-bottom-0">
                        <?php
                        if ($totalPendientesTerminados != 0 && !$assemble) {
                            $arr = array(
                                "id" => "restante_variante_" . $variante->id,
                                "name" => "restante_variante_" . $variante->id,
//                                "value" => ($totalPreparados / $divisor),
                                "value" => ($totalPendientesTerminados / $divisor),
                                "tipo" => 'number',
//                                "max" => ($totalPreparados / $divisor),
//                                "max" => ($totalOrden / $divisor),
                                "step" => $divisor,
                                "data-varante-id" => $variante->id,
                                "clase" => "col-4 restante_variante",
                            );
                            echo item_input($arr);
                            ?>
                            &nbsp;<button type = "button" class="btn btn-primary btn-accion-variante h-100" id="btn-guardar-<?= $variante->id ?>_<?= $wrap ?>" data-wrap="<?= $wrap ?>" data-variante-id="<?= $variante->id ?>"><i class="fas fa-check fa-xs"></i></button>
                            <?php
                        } else {
                            if ($totalOrden == 0) {
                                echo ($totalOrden / $divisor);
                            } else {
//                                if (!$assemble) {
//                                    echo "No preparados";
//                                } else {
                                echo '-';
//                                }
                            }
                        }
                        ?>
                    </div>
                    <div class="col-1 text-right border-bottom-0">
                        <?= ($totalOrden / $divisor ) ?> &nbsp;
                    </div>
                    <div class="row col-12 collapse border-dark border border-top-0 border-left-0 border-right-0" id="detalle_<?= $wrap . "_" . $variante->id ?>">
                        <div class="row col-2 align-items-start text-left">
                            <p style="text-align:left">
                                <?php foreach ($variante->ordenes as $ord) {
                                    ?>                                                        
                                    <a href="#modalOrden" class="btn btn-orden-numero align-items-start align-self-start" data-toggle="modal" data-target="#modalOrden" data-orden_id="<?= $ord->id ?>"  data-variante_id="<?= $variante->id ?>"  data-wrap="<?= $wrap ?>" style="text-align:left; font-size: 0.75em; padding:0">
                                        <b><?= $ord->tienda_alias ?>_<?= isset($ord->referencia_order_number) ? $ord->referencia_order_number : $ord->id ?></b>
                                    </a>
                                <?php }
                                ?>
                            </p>
                        </div>                    
                        <div class="row col-10 align-items-start text-left">
                            <?php
                            foreach ($variante->propiedades as $p => $prop) {
                                if (sizeof($prop['valores']) == 0) {
                                    continue;
                                }
                                ?>
                                <div class="row col-10 <?= (($k + $j + 1) % 2 === 0 ? 'color_par' : 'color_impar') ?>">
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
                                                        <a href="#modalOrden" class="btn btn-orden-numero" data-toggle="modal" data-target="#modalOrden" data-orden_id="<?= $vord->id ?>" data-variante_id="<?= $variante->id ?>" data-wrap="<?= $wrap ?>" style="text-align:left; font-size: 0.75em; padding:0">
                                                            <b><?= $vord->tienda_alias ?>_<?= isset($vord->referencia_order_number) ? $vord->referencia_order_number : $vord->id ?></b>
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
                            }
                            ?>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>
            <?php
        }
        ?>
    </div>
    <div class="col-12">&nbsp;</div>
<?php } ?>
