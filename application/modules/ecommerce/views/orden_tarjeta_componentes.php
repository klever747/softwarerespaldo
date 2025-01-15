<?php
if (!$items) {
    return;
}
$mensaje = "";
//error_log(print_r($items, true));
$repeticion = 1;
for ($i = 0; $i < $repeticion; $i++) {
    $items2 = $items;
    foreach ($items2 as $item) {
        $mensaje .= $item->info_producto_titulo;
        $mensaje .= $item->info_variante_titulo;
//    $mensaje .= $item->info_variante_sku;
        $mensaje .= $item->totalStems;
//        print_r($item->propiedades);die;
        if ($item->propiedades) {
            foreach ($item->propiedades as $prop) {
                $prop_1 = analizarPropiedad($prop, true, true);
                if ($prop_1) {
                    $mensaje .= $prop_1->info_propiedad_descripcion . ": " . $prop_1->valor;
                }
            }
//            error_log(">>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>");
//            error_log(">>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>");
//                            error_log(print_r($item->propiedades,true));
        }
    }
}

$msg_length_or = strlen($mensaje);


$fuentes = array(
    '10' => 2.25,
    '20' => 2.00,
    '30' => 1.75,
    '40' => 1.50,
    '50' => 1.40,
    '60' => 1.30,
    '70' => 1.25,
    '80' => 1.20,
    '90' => 1.20,
    '100' => 1.25,
    '125' => 1.20,
    '150' => 1.15,
    '175' => 1.00,
    '200' => 1.10,
    '300' => 1.20,
);

$font_size = false;
foreach ($fuentes as $k => $v) {
    if (strlen($mensaje) < intval($k)) {
        $font_size = ($k / strlen($mensaje)) * 0.6 * $v;
        break;
    }
}
if (!$font_size) {
    $font_size = 1;
}

$font_size = $font_size * 0.85;
$font_size = $font_size . "em";
?>

<?php
//echo strlen($mensaje)." ".$font_size;
$mensaje = '<table cellspacing="0" cellpadding="2" border="1">
            <tr>
                <th style="font-family:helvetica; font-size:0.65em; width: 150px">Producto/Variante</th>
                <th style="font-family:helvetica;font-size:0.65em; width: 40px">Stems</th>
                <th style="font-family:helvetica;font-size:0.65em; width: 160px">Detalle</th>
            </tr>';
for ($i = 0; $i < $repeticion; $i++) {
    foreach ($items as $item) {
        $mensaje .= '<tr>';
        $mensaje .= '<td style="font-family:helvetica;font-size:' . $font_size . '; text-align:left">' . $item->info_producto_titulo . '<br/>' . $item->info_variante_titulo . '</td>';
//    $mensaje .= '<td style="text-align: right">' . round(($item->totalStems->sum * $item->cantidad) / 12) . " " . (($item->totalStems->sum % 12) === 0 ? 'D' : 'U'). '</td>';
        $mensaje .= '<td style="font-family:helvetica;font-size:' . $font_size . '"><div></div>' . print_r($item->totalStems * $item->cantidad, true) . '</td>';
        $mensaje .= '<td style="font-family:helvetica;font-size:' . $font_size . '; text-align:left">';
        foreach ($item->propiedades as $prop) {
            $prop = analizarPropiedad($prop, true, true);
            error_log(print_r($prop, true));
            if ($prop) {
                $mensaje .= '<b>' . $prop->info_propiedad_descripcion . "</b>: " . $prop->valor . " " . ($prop->cantidad ? "x" . $prop->cantidad : "") . '<br style="margin-bottom: -.4em;">';
            }
        }

        $mensaje .= '</td>';
        $mensaje .= '</tr>';
    }
}

$mensaje .= '</table>';
//echo $font_size . " " . $msg_length_or. "<br/> ";
?>

<br/>

<div style="text-align: <?= ($imagen_firma) ? 'justify; font-family:helvetica;  text-justify: inter-word;' : 'center;' ?> font-size: <?= $font_size ?>; background-color: #fff; margin-top: 15em">
    <?= $mensaje ?><br/>
</div>



