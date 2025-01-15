<?php

if ($variantes_det) {
?>
    <?php

    $totalDocenasProducto = 0;
    $totalOrdenesProducto = 0;
    foreach ($variantes_det as $k => $det) {
        $variante = $det[0];
        $ordenes = $det[1];
        $presentacion = obtenerPresentacion($variante->sku);
        $factorPresentacion = ($presentacion[3] / $presentacion[0]);
        $totalDocenas = $factorPresentacion * $ordenes['totalItemsPedidos'];
        $totalDocenasProducto += $totalDocenas;
        $totalOrdenesProducto += $ordenes['totalItemsPedidos'];
    }
    print_r($totalDocenasProducto . " docenas | " . $totalOrdenesProducto . " ordenes | avg: " . round(($totalDocenasProducto / $totalOrdenesProducto), 2) . "<br/>");
    ?>
<?php } ?>