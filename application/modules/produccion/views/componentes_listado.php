

<?php
foreach ($listado as $k => $v) {
    if (($mostrar_tinturados != null) && ($mostrar_naturales != null)) {
        if ($k !== 'U') {
            continue;
        }
    } else {
        if (($k == 'T') && ($mostrar_tinturados != 1)) {
            continue;
        }
        if (($k == 'N') && ($mostrar_naturales != 1)) {
            continue;
        }
        if ($k == 'U') {
            continue;
        }
    }

    $arr = array();
    foreach ($v as $m => $n) {
        $cabecera = '
                                            <tr>
                                                <td>' . $m . '</td>
                                                    ' . (($agrupado_descripcion == 1) ? "
                                                <td></td>
                                                <td></td>
                                                <td></td>" : "") . '
                                                <td>' . (array_key_exists(40, $n['longitudes']) ? $n['longitudes'][40] : 0) . '</td>
                                                <td>' . (array_key_exists(50, $n['longitudes']) ? $n['longitudes'][50] : 0) . '</td>
                                                <td>' . (array_key_exists(60, $n['longitudes']) ? $n['longitudes'][60] : 0) . '</td>
                                                <td>' . (array_key_exists(70, $n['longitudes']) ? $n['longitudes'][70] : 0) . '</td>
                                                <td>' . (array_key_exists(80, $n['longitudes']) ? $n['longitudes'][80] : 0) . '</td>
                                                <td>' . (array_key_exists(90, $n['longitudes']) ? $n['longitudes'][90] : 0) . '</td>
                                                <td>' . (array_key_exists(100, $n['longitudes']) ? $n['longitudes'][100] : 0) . '</td>
                                                <td style="text-align:right">' . print_r($n['total_ingrediente'], true) . '</td>
                                            </tr>
                                           ';
        ?>
        <?php
        $detalle = '';
        if ($agrupado_descripcion == 1) {
            foreach ($n['elementos'] as $w => $x) {
                $detalle .= '
                                                    <tr>
                                                        <td></td>
                                                        <td>' . print_r($w, true) . '</td>
                                                        <td></td>
                                                        <td></td>
                                                        <td>' . (array_key_exists(40, $x['longitudes']) ? $x['longitudes'][40] : 0 ) . '</td>
                                                        <td>' . (array_key_exists(50, $x['longitudes']) ? $x['longitudes'][50] : 0 ) . '</td>
                                                        <td>' . (array_key_exists(60, $x['longitudes']) ? $x['longitudes'][60] : 0 ) . '</td>
                                                        <td>' . (array_key_exists(70, $x['longitudes']) ? $x['longitudes'][70] : 0 ) . '</td>
                                                        <td>' . (array_key_exists(80, $x['longitudes']) ? $x['longitudes'][80] : 0 ) . '</td>
                                                        <td>' . (array_key_exists(90, $x['longitudes']) ? $x['longitudes'][90] : 0 ) . '</td>
                                                        <td>' . (array_key_exists(100, $x['longitudes']) ? $x['longitudes'][100] : 0 ) . '</td>
                                                        <td style="text-align:right">' . print_r($x['total_descripcion'], true) . '</td>
                                                    </tr>
                                                    ';
            }
        }
        ?>                                            
        <?php
        $p = str_pad($n['total_ingrediente'], 7, 0, STR_PAD_LEFT);
        $arr[$p . "_" . $m] = array("cabecera" => $cabecera, "detalle" => $detalle);
    }
    krsort($arr);
    ?>
    <div class="row">
        <div class="col-12"><b><?= ($k == 'U') ? 'Unificado' : ( ($k == 'T') ? 'Tinturados' : 'Naturales') ?></b></div>
        <div class="col-12">

            <table class="table table-striped text-left align-top">
                <thead>
                    <tr>
                        <th rowspan="2">Ingrediente</th>
                        <?php if ($agrupado_descripcion == 1) { ?>
                            <th rowspan="2" colspan="3">Descripci&oacute;n</th>
                        <?php } ?>
                        <th colspan="7" style="text-align:center">Largo_cm</th>
                        <th rowspan="2" style="text-align:right">Total</th>
                    </tr>
                    <tr>
                        <th>40</th>
                        <th>50</th>
                        <th>60</th>
                        <th>70</th>
                        <th>80</th>
                        <th>90</th>
                        <th>100</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($arr as $a) {
                        echo $a['cabecera'];
                        echo $a['detalle'];
                    }
                    ?>
                    <?php
                    if ($mostrar_accesorios == 1) {
                        ?>
                        <tr><th colspan="3">Accesorios</th></tr>
                        <tr>
                            <th>Elemento</th>
                            <th style="text-align:left" <?= ($mostrar_accesorios == 1 ? 'colspan="2"' : 'colspan="7"') ?>>Descripci&oacute;n</th>
                            <th style="text-align:right">Total</th>
                        </tr>    

                        <?php
                        if (isset($componentes_accesorios) && $componentes_accesorios) {

                            foreach ($componentes_accesorios as $cab => $componente) {
                                $cabecera_mostrada = false;
                                ?> 
                                <?php
                                foreach ($componente as $valor => $total) {
                                    ?>
                                    <tr>
                                        <td><?= !($cabecera_mostrada) ? $cab : '' ?></td>
                                        <td class="col-8 text-left"  <?= ($mostrar_accesorios == 1 ? 'colspan="2"' : 'colspan="7"') ?>>
                                            <?= $valor ?>
                                        </td>
                                        <td class="text-right"><?= $total ?></td>
                                    </tr>
                                    <?php
                                    $cabecera_mostrada = true;
                                }
                            }
                        }
                    }
                    ?>
                </tbody>
            </table>                                
        </div>
    </div>
    <div class="row">&nbsp;</div>
    <hr>
    <?php
}
?>

