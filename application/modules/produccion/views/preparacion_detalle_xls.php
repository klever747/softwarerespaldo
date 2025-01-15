<style>
    table, th, td{
        border: 1px solid black;
        border-collapse: collapse;
    }
</style>
<table class="table table-striped" style="border:1px">
<!--    <thead>
    </thead>-->
    <tbody>
        <?php
        $fila_det = 0;
        foreach ($listadoProductos as $k => $item) {
            $producto = $item['producto'];
            ?>
            <tr>
                <th><b><?= $producto->titulo ?></b></th>
                <th>Unidad</th>
                <th>Total</th>
                <th>Accesorio</th>
                <th>Tipo</th>
                <th>Cantidad</th>
                <th>Ordenes</th>
            </tr>
            <?php
            $variantes = $item['variantes'];
            foreach ($variantes as $j => $variante) {
                List($divisor, $unidad, $assemble) = obtenerPresentacion($variante->sku);
                if ($divisor == 0 || $divisor == null) {
                    $divisor = 1;
                }
                ?>
                <tr>
                    <td><?= $variante->titulo ?>
                        <?php
                        $totalPedido = 0;
                        foreach ($variante->ordenes as $ord) {
                            $totalPedido += $ord->orden_item_cantidad;
                        }
                        ?>
                    </td>
                    <td>
                        <?= $unidad ?> &nbsp;
                    </td>
                    <td><?= ($totalPedido * $variante->cantidad) / $divisor ?></td>
                    <td>

                    </td>
                    <td>

                    </td>
                    <td>

                    </td>
                    <td>

                    </td>
                </tr>

                <?php
                foreach ($variante->propiedades as $p => $prop) {
//                            $class_propiedad = ($fila_det % 2 == 0) ? "color_par" : "color_impar";
                    if (sizeof($prop['valores']) == 0) {
//                        continue;
                    }
                    ?>                    
                    <?php
                    foreach ($prop['valores'] as $v => $valor) {
                        $pos = strpos($v, 'No Vase');
                        if ($pos > -1) {
                            break;
                        }
                        $pos = strpos($v, 'Add Loose');
                        if ($pos > -1) {
                            break;
                        }
                        ?>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>
                                <?= $prop['propiedad_descripcion'] ?>
                            </td>
                            <td>
                                <?php
                                $pos = strpos($v, '(');
                                if ($pos) {
                                    $v = substr($v, 0, $pos);
                                }
                                $pos = strpos($v, '[');
                                if ($pos) {
                                    $v = substr($v, 0, $pos);
                                }
                                echo $v;
                                ?>
                            </td>
                            <td><?= ($valor['numero'] * $variante->cantidad) / $divisor ?></td>
                            <td>

                            </td>                                
                            <?php
                        }
                        ?>
                    </tr>                                             
                    <?php
                    $fila_det++;
                }
            }
        }
        ?>
    </tbody>
</table>
