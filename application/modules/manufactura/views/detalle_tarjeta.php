<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <style>
            body { font-family: DejaVu Sans; }
        </style>
    </head>
    <body>
        <br/>
        <?php
        $i = 10;

        $fuentes = array(
            '10' => 1.50,
            '20' => 2.00,
            '30' => 2.00,
            '40' => 2.00,
            '50' => 2.00,
            '60' => 1.90,
            '70' => 1.80,
            '80' => 1.70,
            '90' => 1.60,
            '100' => 1.50,
            '200' => 1.00,
            '300' => 0.95,
            '400' => 0.85,
            '500' => 0.75,
            '600' => 0.85,
            '700' => 0.75,
            '800' => 0.50,
            '900' => 1.40,
            '2000' => 0.50,
        );

        $font_size = false;
        foreach ($fuentes as $k => $v) {
            if (strlen($mensaje) < intval($k)) {
                $font_size = ($k / strlen($mensaje)) * 0.9 * $v;
                break;
            }
        }
        if (!$font_size) {
            $font_size = 0.9;
        }

        $font_size = $font_size . "em";
//echo "<br/>" .$font_size . "<br/>" . strlen($mensaje) . "<br/>"
        ?>
        <br/>
        <div style="text-align: <?= ($imagen_firma) ? 'justify;  text-justify: inter-word; ' : 'center; line-height: 1;' ?>; font-family:dejavusans; font-size: <?= $font_size ?>; background-color: #fff; margin-top: 15em">
            <?php
//    $mensaje = preg_replace("/\\\\u([0-9a-fA-F]{4})/", "&#x\\1;", $mensaje);
// Convert the entities to a UTF-8 string
//    $mensaje = html_entity_decode($mensaje, ENT_QUOTES, 'UTF-8');
// Convert the UTF-8 string to an ISO-8859-1 string
//    echo iconv("UTF-8", "ISO-8859-1//TRANSLIT", $mensaje);
//    $mensaje = nl2br($mensaje);
//    $mensaje = html_entity_decode($mensaje);
//    $mensaje = utf8_decode($mensaje);
//    $mensaje = str_replace(array("\\r\\n", "\r\n", "\r", "\n", "\\n"), "<br />", $mensaje);
//    $mensaje = decodeEmoticons($mensaje);
            echo mensajeHtml($mensaje);
            if ($imagen_firma) {
                ?>
                <br/>
                <div style="text-align:center">
                    <img src="<?= base_url() ?>assets/app_especifico/img/<?= RUTA_IMG?>" alt="Screenshot 01" width="150px">
                </div>
                <?php
            }
            ?>
        </div>
    </body>
</html>