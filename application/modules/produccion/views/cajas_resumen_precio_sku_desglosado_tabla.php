<?php

if ($arrTotales) {
    foreach ($arrTotales as $empresa_id => $empresa) {
        ?>
        <div class="col-12 row mb-5">
            <div class="col-12 bg-cyan"><b><?= print_r($empresa['nombre_tienda'], true) ?></b></div>
            <div class="col-12 row">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th></th>
                            <th></th>
                            <th width="10%"></th>
                            <th width="10%">Largo</th>
                            <th width="10%">$/Caja</th>
                            <th width="10%">Stems/Caja</th>
                            <th width="10%">Total Cajas</th>
                            <th width="10%">Total Stems</th>
                            <th width="20%">Total $</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th><?= $empresa['store_total_cajas'] ?></th>
                            <th><?= $empresa['store_total_stems'] ?></th>
                            <th class="celda_moneda">$ <?= $empresa['store_total_dolar'] ?></th>
                        </tr>
                        <?php
                        foreach ($empresa['cajas'] as $caja_id => $caja) {
                           
                            ?>  
                            <tr>
                                <th class = "bg-black">
                                    <?= $caja['nombre_caja'] ?>
                                </th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th><?= $caja['total_de_cajas'] ?></th>
                                <th><?= $caja['caja_total_stems'] ?></th>
                                <th class="celda_moneda">$ <?= number_format($caja['caja_total_dolar'], 2) ?></th>
                            </tr>
                            <?php
                            foreach (array("T" => "Solo Tinturados", "N" => "Solo Naturales", "M" => "Mixtos") as $t => $ton) {

                                if (!array_key_exists($t, $caja)) {
                                    continue;
                                }
                                ?>
                                <tr>
                                    <th><?= $ton ?></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th><?= $caja[$t]['cantidad_cajas']  ?></th>
                                    <th><?=  $caja[$t]['cantidad_stems'] ?></th>
                                    <th class="celda_moneda"><?= number_format($caja[$t]['precio_total_cajas'], 2)?></th>

                                </tr>
                                <?php
                                $resumen_ordenes = '';

                                foreach ($caja[$t]['longitud'] as $longitud => $value) {
                                    foreach ($caja[$t]['longitud'][$longitud]['tallos'] as $stemsXcaja => $valor_stem) {
                                        foreach ($valor_stem['precios'] as $precioxstem => $precio) {
                                            $id_div = $caja['alias']."_".reemplazarPunto($longitud, "-")."_".reemplazarPunto($precioxstem, "-")."_".$caja['finca_id']."_".$caja['tipo_caja_id']."_".$t;
                                            if($valor_stem["ordenes"]){
                                                $resumen_ordenes .= procesarListaOrdenes( $id_div , $stemsXcaja, false, "$ " . $precioxstem);
                                            }  
                                            //$resumen_ordenes .= procesarListaOrdenes($empresa_id . "_" . $caja_id . "_" . $t, $precioxstem,  $value, "$ " . $precioxstem);


                                                if ($stemsXcaja == 0) {
                                                    $stemsXcaja = -1;
                                                }
                                                ?>
                                                <tr>
                                                    <th></th>
                                                    <td></td>
                                                    <td></td>
                                                    <th class ="mostrar_ordenes_id" data-ord_id ="<?=$id_div."_".$stemsXcaja?>" data-toggle="collapse" data-target="#det_items_<?=  $id_div."_" . $stemsXcaja  ?>"><?= $longitud ?> cm.</th>
                                                    <td class="celda_moneda">$ <?= number_format($precioxstem, 2) ?></td>
                                                    
                                                    <td><?= $stemsXcaja ?></td>
                                                    <td><?= $precio['cantidad'] ?></td>
                                                    <td><?= $stemsXcaja * $precio['cantidad'] ?></td>
                                                    <td class="celda_moneda">$ <?= number_format($precioxstem * $precio['cantidad'] , 2) ?></td>
                                                </tr>
                                                <?php
                                        }
                                        foreach ($valor_stem['detalle_orden'] as $kkk => $vvv) {
                                            ?>
                                            <tr>
                                                <th rowspan="<?= sizeof($vvv['detalle']) + 1 ?>"><?= procesarListaOrdenes($vvv['alias'], $vvv['alias'], array($kkk => $vvv['alias']), "", 0, false) ?></th>
                                                <th>Producto/Variante</th>
                                                <th>#Items</th>
                                                <th>cm</th>
                                                <th>P.U</th>  
                                                <th>#Stem</th>
                                                <th>T</th>
                                                <th>N</th>
                                                <th>Total</th>
                                            </tr>
                                            <?php
                                            foreach ($vvv['detalle'] as $linea_det) {
                                                ?>
                                                <tr>
                                                    <td class="text-left"><?= $linea_det['producto'] ?>-<?= $linea_det['variante'] ?><br></td>
                                                    <td><?= $linea_det['cantidad']  ?></td>
                                                    <td><?= $linea_det['largo_cm'] ?>cm.</td>
                                                    <td>$ <?= $linea_det['precio_unit'] ?></td>
                                                    <td><?= $linea_det['stems'] ?></td>
                                                    <td><?= $linea_det['t_tinturados'] ?></td>
                                                    <td><?= $linea_det['t_naturales'] ?></td>
                                                    <td class="celda_moneda">$ <?= $linea_det['precio_total']?></td>
                                                </tr>
                                                <?php
                                            }
                                        }
                                    }
                                }    
                                ?>
                               
                                <?php
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }
}
?>

                        