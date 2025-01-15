<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <meta name="viewport" content="width=50, initial-scale=1">
        <style>
            body { font-family: DejaVu Sans; }
        </style>
    </head>
    <body>

        <?php
        $i = 10;

        $fuentes = array(
            '10' => 1.50,
            '20' => 1.50,
            '30' => 1.50,
            '40' => 1.50,
            '50' => 1.50,
            '60' => 1.45,
            '70' => 1.45,
            '80' => 1.45,
            '90' => 1.45,
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
            if (!empty($mensaje)) {
                if (strlen($mensaje) < intval($k)) {
                    $font_size = ($k / strlen($mensaje)) * 0.9 * $v;
                    break;
                }
            }
        }
        if (!$font_size) {
            $font_size = 0.9;
        }

        $font_size = $font_size . "em";
//echo "<br/>" .$font_size . "<br/>" . strlen($mensaje) . "<br/>" 
        ?>
        <br/>
        <table style="width:400px; font-family:montserrat;">
            <tr style="">
                <td style="width:190px;">
                    <div style="text-align: center; font-size: 10; font-family:montserrat-bold; font-weight: bold; line-height: 2;">Personal Note:</div>            
                    <div style="text-align: center; font-size: <?= $font_size ?>; ">
                        <?= mensajeHtml($mensaje); ?>
                    </div>        
                </td>
                <td style="width:20px;text-align: center; ">
                    <br/><br/><br/>
                    <img src="<?= base_url() ?>assets/app_especifico/img/vertical_separador_tarjeta.png" width="16px">
                </td>
                <td style="width:190px;">
                    <div style="text-align:center; line-height: 0;/*background-color: #efefff;*/border:1px solid red;">
                        <img src="<?= base_url() ?>assets/app_especifico/img/hello.png" width="80px" style="/*background-color: #efefef;*/border:1px solid red;">
                    </div>
                    <div style="text-align:center; line-height: 0;/*background-color: #efefff;*/border:1px solid red;">
                        <p style="text-align:center; font-size: 12;line-height: 0.5; /*background-color: #efefef;*/">Thank you very much for</p>
                        <p style="text-align:center; font-size: 12;line-height: 0.5; /*background-color: #efefef;*/">your order!</p>
                    </div>
                    <div style="text-align:center;font-size: 10;line-height: 0;margin-top:0; /*background-color: #efefff;*/border:1px solid red;">
                        <img src="<?= base_url() ?>assets/app_especifico/img/dots.png" width="25px" style="/*background-color: #efefef;*/">
                        <p style="text-align:center; font-size: 10;line-height: 0.5; /*background-color: #efefef;*/">Your support means the world</p>
                        <p style="text-align:center; font-size: 10;line-height: 0.5; /*background-color: #efefef;*/">to us. If you share a snap</p>
                        <p style="text-align:center; font-size: 10;line-height: 0.5; /*background-color: #efefef;*/">please tag us.</p>

                    </div>
                    <div style="text-align:center;font-size: 9;margin-top:0; font-weight:bold;/*background-color: #efefef;*/">
                        <img src="<?= base_url() ?>assets/app_especifico/img/redes.png" width="75px" style="clear:both">
                        <br/>
                        @rosaholics
                    </div>
                </td>
            </tr>
        </table>
    </body>
</html>