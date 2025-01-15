<div class="wrapper">
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Cajas agrupadas por stems</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Inicio</a></li>
                            <li class="breadcrumb-item">Producci&oacute;n</li>
                            <li class="breadcrumb-item">Resumen Cajas</li>
                        </ol>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>
        <section class="content">
            <div class="container-fluid">
                <div class="card card-default" id="soloLectura">
                    <?=
                    filtroBusqueda($url_busqueda, array("rango_busqueda" => $rango_busqueda, "tipo_calendario" => $tipo_calendario, "uso_calendario" => 1,
                        "store_id" => $store_id, "sel_store" => $sel_store,
//                                "con_tracking_number" => $con_tracking_number,
                        "exportar_pdf" => true, "exportar_xls" => true)
                    );
                    ?>                                        
                    <div class="card-body text-center">                        
                        <div class="row">
                            <?php
                            if ($data) {
                                foreach ($data as $i => $datos) {

//                                        var_dump($datos);
                                    ?>
                                    <div class="col-12 row mb-5">
                                        <div class="col-12 bg-cyan"><b><?= print_r($sel_store[$i], true) ?></b></div>
                                        <table class="table table-striped table-bordered">                                            
                                            <thead>
                                                <tr>
                                                    <th></th>
                                                    <th width="20%">Largo</th>
                                                    <th width="20%">Total Cajas</th>
                                                    <th width="20%">Total Stems</th>
                                                    <th width="20%">Total $</th>
                                                </tr>
                                                <tr>
                                                    <th></th>
                                                    <th width="20%"></th>
                                                    <th></th>
                                                    <th><?= $datos['total_stems'] ?></th>
                                                    <th class="text-right">$ <?= number_format($datos['total_dolares'], 2); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody> 
                                                <?php
                                                foreach ($datos['cajas'] as $j => $item) {
                                                    $resumen_ordenes = '';
                                                    $resumen_ordenes .= procesarListaOrdenes($i . "_" . $j, "full", $item['ordenes'], $item['nombre_caja']);
                                                    if (sizeof($item['tipo_producto']) == 0) {
                                                        continue;
                                                    }
                                                    ?>                                            
                                                    <tr>
                                                        <th class="bg-black" data-toggle="collapse" data-target="#det_items_<?= $i . "_" . $j . "_full" ?>">
                                                            <?= $item['nombre_caja'] ?> 
                                                        </th>                                                   
                                                        <th></th>
                                                        <th><?= sizeof($item['ordenes']) ?></th>
                                                        <th><?= $item['total_stems'] ?></th>
                                                        <th class="text-right">$ <?= number_format($item['total_dolar'], 2); ?></th>
                                                    </tr>

                                                    <?php
                                                    foreach ($item['tipo_producto'] as $k => $r) {
                                                        if (sizeof($r['largo_cm']) == 0) {
                                                            continue;
                                                        }
                                                        $tipo_prod = ($k == 'N' ? 'Normal' : ($k == 'NXL' ? 'Normal XL' : ($k == 'T' ? 'Tinturado' : 'Tinturado XL')));

                                                        $totnum = 0;
                                                        foreach ($r['largo_cm'] as $x => $y) {
                                                            $totnum += sizeof($y['totales_caja']);
                                                        }
                                                        ?> 
                                                        <tr>
                                                            <th rowspan="<?= sizeof($r['largo_cm']) + $totnum + 1 ?>" data-toggle="collapse" data-target="#det_items_<?= $i . "_" . $j . "_" . $k ?>">
                                                                <?= $tipo_prod ?> <?php /* (<?= $r['total_cajas'] ?>) */ ?>
                                                            </th>
                                                        </tr>
                                                        <?php
                                                        $resumen_ordenes .= procesarListaOrdenes($i . "_" . $j, $k, $r['ordenes'], $tipo_prod);
                                                        foreach ($r['largo_cm'] as $x => $y) {
                                                            $resumen_ordenes .= procesarListaOrdenes($i . "_" . $j . "_" . $k, $x, $y['ordenes'], $tipo_prod . " " . $x . " cm");
                                                            ?>
                                                            <tr>                                                                
                                                                <th rowspan="<?= sizeof($y['totales_caja']) + 1 ?>" data-toggle="collapse" data-target="#det_items_<?= $i . "_" . $j . "_" . $k . "_" . $x ?>"><?= print_r($x, true); ?> cm.</th>
                                                                <th><?= sizeof($y['ordenes'], true); ?></th>
                                                                <th><?= print_r($y['total_stems'], true); ?></th>
                                                                <th class="text-right">$ <?= number_format($y['total_dolar'], 2); ?></th>
                                                            </tr>
                                                            <?php
                                                            foreach ($y['totales_caja'] as $u => $o) {
                                                                $resumen_ordenes .= procesarListaOrdenes($i . "_" . $j . "_" . $k . "_" . $x, $u, $o['ordenes'], $tipo_prod . " " . $x . " cm" . " ($" . $u . ")");
                                                                ?>
                                                                <tr>
                                                                    <td colpsan="2"><?= sizeof($o["ordenes"]) ?></td>
                                                                    <td><?= $o["total_stems"] ?></td>
                                                                    <td class="text-right" data-toggle="collapse" data-target="#det_items_<?= $i . "_" . $j . "_" . $k . "_" . $x . "_" . reemplazarPunto($u, "_") ?>" >$ <?= number_format($u, 2) ?></td>
                                                                </tr>
                                                                <?php
                                                            }
                                                        }
                                                        ?>                                                         
                                                    <?php } ?>
                                                    <tr>
                                                        <td colspan="5"><?= $resumen_ordenes ?></td>
                                                    </tr>


                                                <?php } ?>

                                            </tbody>
                                        </table>
                                    </div>
                                    <?php
                                }
                            }
                            ?>
                        </div>
                    </div>
                </div>
        </section>
    </div>
    <!-- /.content-wrapper -->
</div>
<!-- Modal -->
<div class="modal" id="modalEdicionOrden" tabindex="-1" role="dialog" aria-labelledby="wsanchez" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">

        </div>
    </div>
</div>
<script>
    function mostrarResultadoNuevaVenta(data) {
        console.log(data);
        var mywindow = window.open('', '_blank');
        mywindow.document.write(data);
        //        var $a = $("<a>");
        //        $a.attr("href", data.file);
        //        $("body").append($a);
        //        $a.attr("download", "file.xls");
        //        $a[0].click();
        //        $a.remove();
    }

    function mostrarOrden(r, peque = false) {
        console.log("mostrarOrden");
        if (r.error) {
            mostrarError("No existe informaci&oacute;n disponible en estos momentos");
        } else {
            if (peque) {
                $("#modalEdicionOrden .modal-content").parent().addClass("modal-xs");
                $("#modalEdicionOrden .modal-content").parent().removeClass("modal-xl");
            } else {
                $("#modalEdicionOrden .modal-content").parent().addClass("modal-xl");
                $("#modalEdicionOrden .modal-content").parent().removeClass("modal-xs");
            }
            $("#modalEdicionOrden .modal-content").html(r.detalle_orden);
            $("#modalEdicionOrden").modal("show");
            var nowDate = new Date();
            var today = new Date(nowDate.getFullYear(), nowDate.getMonth(), nowDate.getDate(), 0, 0, 0, 0);
            $('.select_fecha').daterangepicker({
                singleDatePicker: true,
                locale: {
                    format: '<?= FORMATO_FECHA_DATEPICKER_JS ?>'
                },
                minDate: today,
                maxDate: '2050-12-13',
                autoApply: true
            });

            //esto deberia estar en el mismo orden_detalle
            $('.fecha_entrega').on('apply.daterangepicker', function (e, picker) {
                llamadaAjax('btn-accion_guardar_fecha', '<?= base_url() ?>ecommerce/orden/calcular_fechas_entrega', {"orden_id": r.orden_id, "fecha_entrega": picker.startDate.format('YYYY-MM-DD')}, actualizarFechas);
            });
            $('.fecha_carguera').on('apply.daterangepicker', function (e, picker) {
                llamadaAjax('btn-accion_guardar_fecha', '<?= base_url() ?>ecommerce/orden/calcular_fechas_entrega', {"orden_id": r.orden_id, "fecha_carguera": picker.startDate.format('YYYY-MM-DD')}, actualizarFechas);
            });
    }
    }
    function loadOrden() {
        console.log("Load Orden #" + orden_actual + " en manufactura ordenes.php");
        llamadaAjax(false, '<?= base_url() ?>ecommerce/obtenerOrden', {"id": orden_actual, "perfil": <?= PANTALLA_LOGISTICA ?>}, mostrarOrden);

    }

    function recargarPrincipal() {
        console.log("Recargar Principal en caja_resumen");
        loadOrden();
    }

    $(document).ready(function () {
        $("#tracking_number").focus();
        /*************** ACCIONES PANTALLA PRINCIPAL *********************/
        $("#exportar_excel").on('click', function () {
            unsoloclick('#exportar_excel');
            var form = $("#form_busqueda");
            console.log(form.serialize());
            //            llamadaAjax('exportar_excel', '<?= base_url() ?>produccion/logistica/cajasPorFecha_xls', form.serialize(), mostrarResultadoNuevaVenta);
            window.open('<?= base_url() ?>produccion/logistica/cajasPorFecha_xls?' + form.serialize(), '_blank');
        });


        /*********** CARGA ORDENES ***********/
        $(document).on('click', '.btn-orden-numero', function (e) {
            orden_actual = $(this).data('orden_id');
            variante_actual_id = $(this).data('variante_id');
            loadOrden();
        });
    });

</script>