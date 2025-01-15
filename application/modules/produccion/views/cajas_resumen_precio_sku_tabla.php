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
                                                        <th width="10%">Largo</th>
                                                        <th width="10%">$/Caja</th>
                                                        <th width="10%"></th>
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
                                                         
                                                        <th><?= $empresa['store_total_cajas'] ?></th>
                                                        <th><?= $empresa['store_total_stems'] ?></th>
                                                        <th><?= $empresa['store_total_dolar'] ?></th>
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
                                                            <th><?= $caja['total_de_cajas'] ?></th>
                                                            <th><?= $caja['caja_total_stems'] ?></th>
                                                            <th><?= number_format($caja['caja_total_dolar'], 2) ?></th>
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
                                                               
                                                                <th><?= $caja[$t]['cantidad_cajas']  ?></th>
                                                                <th><?=  $caja[$t]['cantidad_stems'] ?></th>
                                                                <th><?= number_format($caja[$t]['precio_total_cajas'], 2)?></th>

                                                            </tr>
                                                            <?php
                                                            $resumen_ordenes = '';

                                                            foreach ($caja[$t]['longitud'] as $longitud => $value) {
                                                                foreach ($caja[$t]['longitud'][$longitud]['tallos'] as $stemsXcaja => $valor_stem) {
                                                                    foreach ($caja[$t]['longitud'][$longitud]['tallos'][$stemsXcaja]['precios'] as $precioxstem => $precio) {
                                                                        if($valor_stem["ordenes"]){
                                                                            $resumen_ordenes .= procesarListaOrdenes($caja['alias'] . "_" . reemplazarPunto($precioxstem, "_")."_".$t , $stemsXcaja,  $valor_stem["ordenes"], "$ " . $precioxstem,10,true,true,false);

                                                                        }  
                                                                        //$resumen_ordenes .= procesarListaOrdenes($empresa_id . "_" . $caja_id . "_" . $t, $precioxstem,  $value, "$ " . $precioxstem);


                                                                            if ($stemsXcaja == 0) {
                                                                                $stemsXcaja = -1;
                                                                            }
                                                                            ?>
                                                                            <tr>
                                                                                <th></th>
                                                                                <th class ="mostrar_ordenes_id" data-ord_id ="<?=$longitud."_".$precioxstem."_".$stemsXcaja."_".$caja['finca_id']."_".$caja['session_finca']?>" data-toggle="collapse" data-target="#det_items_<?= $caja['alias'] .  "_" . reemplazarPunto($precioxstem, "_"). "_".$t."_" . $stemsXcaja  ?>"><?= $longitud ?> cm.</th>
                                                                                <td>$ <?= number_format($precioxstem, 2) ?></td>
                                                                                <td></td>
                                                                                <td><?= $stemsXcaja ?></td>
                                                                                <td><?= $precio['cantidad'] ?></td>
                                                                                <td><?= $stemsXcaja * $precio['cantidad'] ?></td>
                                                                                <td>$ <?= number_format($precioxstem * $precio['cantidad'] , 2) ?></td>
                                                                            </tr>
                                                                            <?php
                                                                        
                                                                    }
                                                                }
                                                            }    
                                                            ?>
                                                            <tr>
                                                                <td></td>
                                                                <td></td>
                                                                <td colspan="6"><?=$resumen_ordenes?></td>
                                                            </tr>
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