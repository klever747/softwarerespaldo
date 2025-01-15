<div class="wrapper">
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Reporte de Ventas por SKU Desglosado</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Inicio</a></li>
                            <li class="breadcrumb-item">Producci&oacute;n</li>
                            <li class="breadcrumb-item">Reporte de Ventas Por SKU Desglosado</li>
                        </ol>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>
        <section class="content">
            <div class="container-fluid">
                <div class="card card-default" id="soloLectura">
                    <?php
                    $filtroActual = array("rango_busqueda" => $rango_busqueda,
                        "tipo_calendario" => $tipo_calendario,
                        "uso_calendario" => 1,
                        "exportar_xls" => false,
                        "store_id" => $store_id,
                        "sel_store" => $sel_store,
                        "total_shipping"=>$total_shipping);
                        $arrayfinca = explode(",", $session_finca);
                    if ($session_finca == FINCA_ROSAHOLICS_ID) {
                        $filtroActual["finca_id"] = $finca_id;
                        $filtroActual["sel_finca"] = $sel_finca;
                    }else {
                        if (count($arrayfinca) > 1) {
                            $filtroActual["finca_id"] = $finca_id;
                            $filtroActual["sel_finca"] = $sel_finca;
                        }
                    }
                    echo filtroBusqueda($url_busqueda, $filtroActual);
                    ?>
                    <div class="card-body text-center">
                        <div class="row">

                            <?php
                            if ($data_guias) {
                                ?>
                                <div class="col-12 row mb-5">
                                    <div class="col-1"><b>Guia Madre:</b></div>
                                    <div class="col-6 text-left"><?= $data_guias->guia_madre ?></div>
                                </div>
                                <div class="col-12 row mb-5">
                                    <div class="col-1"><b>Guia Hija:</b></div>
                                    <div class="col-6 text-left"><?= $data_guias->guia_hija ?></div>
                                </div>
                                <?php
                            }
                            ?>
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
                                                                <th></th>
                                                                <th><?= $caja[$t]['cantidad_cajas']  ?></th>
                                                                <th><?=  $caja[$t]['cantidad_stems'] ?></th>
                                                                <th class="celda_moneda">$ <?= number_format($caja[$t]['precio_total_cajas'], 2)?></th>

                                                            </tr>
                                                            <?php
                                                            $resumen_ordenes = '';

                                                            foreach ($caja[$t]['longitud'] as $longitud => $value) {
                                                                foreach ($caja[$t]['longitud'][$longitud]['tallos'] as $stemsXcaja => $valor_stem) {
                                                                    foreach ($caja[$t]['longitud'][$longitud]['tallos'][$stemsXcaja]['precios'] as $precioxstem => $precio) {
                                                                        $id_div = $caja['alias']."_".reemplazarPunto($longitud, "-")."_".reemplazarPunto($precioxstem, "m")."_".$caja['finca_id']."_".$caja['tipo_caja_id']."_".$t;
                                                                        if($valor_stem["ordenes"]){
                                                                            $resumen_ordenes .= procesarListaOrdenes( $id_div , $stemsXcaja, false, false);
                                                                        }  
                                                                        //$resumen_ordenes .= procesarListaOrdenes($empresa_id . "_" . $caja_id . "_" . $t, $precioxstem,  $value, "$ " . $precioxstem);


                                                                            if ($stemsXcaja == 0) {
                                                                                $stemsXcaja = -1;
                                                                            }
                                                                            ?>
                                                                            <tr>
                                                                                <th></th>
                                                                                <td></td>
                                                                                <th></th>
                                                                                <th></th>
                                                                                <th class ="mostrar_ordenes_id" data-ord_id ="<?=$id_div."_".$stemsXcaja?>" data-toggle="collapse" data-target="#det_items_<?=  $id_div."_" . $stemsXcaja ?>" style="cursor: pointer;"><?= $longitud ?> cm.</th>
                                                                                <td class="celda_moneda">$ <?= number_format($precioxstem, 2) ?></td>
                                                                                
                                                                                <td><?= $stemsXcaja ?></td>
                                                                                <td><?= $precio['cantidad'] ?></td>
                                                                                <td><?= $stemsXcaja * $precio['cantidad'] ?></td>
                                                                                <td class="celda_moneda">$ <?= number_format($precioxstem * $precio['cantidad'] , 2) ?></td>
                                                                            </tr>
                                                                            
                                                                            <tbody  colspan="8" id= "det_items_<?=$id_div."_".$stemsXcaja?>" ></tbody>
                                                                           
                                                                            <?php
                                                                            
                                                                        
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
    var finca_id = <?= json_encode($finca_id) ?>;
    var session_finca = <?= json_encode($session_finca) ?>;
    var filtroActual = <?= json_encode($filtroActual) ?>;
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
        setTimeout(validaciones_buttons,1);
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
        llamadaAjax(false, '<?= base_url() ?>ecommerce/obtenerOrden', {"id": orden_actual,"finca_id": finca_id,"session_finca": session_finca, "perfil": <?= PANTALLA_LOGISTICA ?>}, mostrarOrden);

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
            window.open('<?= base_url() ?>produccion/reporteDesglosado/resumenCajasPrecioDesglosado_excel?finca_id='+finca_id+'&filtro=' + JSON.stringify(filtroActual), '_blank');
        });


        /*********** CARGA ORDENES ***********/
        $(document).on('click', '.btn-orden-numero', function (e) {
            orden_actual = $(this).data('orden_id');
            variante_actual_id = $(this).data('variante_id');
            loadOrden();
        });
          /*************** ACCIONES PARA EL MASTER SHIPPING *********************/
        $(".btn-accion").on('click', function () {
            unsoloclick('.btn-accion');
                if ($(this).val() === "visualizar") {
                llamadaAjax(false, '<?= base_url() ?>produccion/MasterShipping/json_imprimir_master_shipping', {"id": $(this).data('id')}, respuestaGenTarjMen);

            } 
        });
        $(document).on('click', '.mostrar_ordenes_id', function (e) {
            console.log("diste click");
            parametro_buscar = $(this).data('ord_id');
            llamadaAjax(false, '<?= base_url() ?>produccion/reporteDesglosado/json_buscar_ordenes_desglosado', { "finca_id":finca_id,"session_finca":session_finca,"filtro": filtroActual,"parametro_buscar": parametro_buscar},agregarInformacion);

        });
        function agregarInformacion(r){
            $('#det_items_'+r.orden_id).html(r.html);       
            $('#det_items_'+r.id_div).html(r.detalle_orden_html);  
        }
        function respuestaGenTarjMen(r) {
            if (analizarRespuesta(r) && (r.ruta_pdf != '')) {
                console.log("ruta a abrir es " + r.ruta_pdf);
                window.open(r.ruta_pdf, '_blank');
            }
        }
        function analizarRespuesta(r) {
            if (r.error) {
                mostrarError(r.mensaje);
                return false;
            } else {
                mostrarExito(r.mensaje);
                return true;
            }
            return false;
        }
    });

</script>