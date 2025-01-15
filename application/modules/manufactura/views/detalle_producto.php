<?php
if ($variantes_det) {
    ?>
    <?php
    foreach ($variantes_det as $k => $variante) {
        if ($enpantalla) {
            echo '<div class="row col-12 border " id="div_variante_' . $k . '" data-producto_id="' . $producto->id . '">';
        } else {
            echo '<b>' . $producto->titulo . '</b>';
        }
        echo $variante;
        if ($enpantalla) {
            echo '<hr></div>';
        }
    }
    ?>
    <br/>
<?php } ?>