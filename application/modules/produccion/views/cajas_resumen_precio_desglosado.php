<div class="wrapper">
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Reporte de Ventas Desglosado</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Inicio</a></li>
                            <li class="breadcrumb-item">Producci&oacute;n</li>
                            <li class="breadcrumb-item">Cajas por Precio</li>
                        </ol>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>
        <section class="content">
            <div class="container-fluid">
                <div class="card card-default" id="soloLectura">
                    <?php
                    $filtroActual = array("rango_busqueda" => $rango_busqueda, "tipo_calendario" => $tipo_calendario, "uso_calendario" => 1,
                        "store_id" => $store_id, "sel_store" => $sel_store,
                        "exportar_xls" => false,
                        "total_shipping" => $total_shipping);
                    $arrayfinca = explode(",", $session_finca);
                    if (in_array(FINCA_ROSAHOLICS_ID, $arrayfinca)) {
                        $filtroActual["finca_id"] = $finca_id;
                        $filtroActual["sel_finca"] = $sel_finca;
                        //$filtroActual["sel_tipo_caja"] = $sel_tipo_caja;
                    } else {
                        if (count($arrayfinca) > 1) {
                            $filtroActual["finca_id"] = $finca_id;
                            $filtroActual["sel_finca"] = $sel_finca;
                        }
                    }
                    $filtroActual["session_finca"] = $session_finca;
                    echo filtroBusqueda($url_busqueda, $filtroActual);
                    $filtroActual["finca_id"] = $finca_id;
                    $filtroActual["session_finca"] = $session_finca;
                    ?>
                    <div class="card-body text-center">

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
                        <div class="row">
                            <?= $tabla ?>
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
        setTimeout(validaciones_buttons, 1);

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
        llamadaAjax(false, '<?= base_url() ?>ecommerce/obtenerOrden', {"id": orden_actual, "finca_id": finca_id, "session_finca": session_finca, "perfil": <?= PANTALLA_LOGISTICA ?>}, mostrarOrden);

    }

    function recargarPrincipal() {
        console.log("Recargar Principal en caja_resumen");
        loadOrden();
    }

    $(document).ready(function () {
        $("#tracking_number").focus();
        /*************** ACCIONES PANTALLA PRINCIPAL *********************/
//        $("#exportar_excel").on('click', function () {
//            unsoloclick('#exportar_excel');
//            var form = $("#form_busqueda");
//            console.log(form.serialize());
//            //            llamadaAjax('exportar_excel', '<?= base_url() ?>produccion/logistica/cajasPorFecha_xls', form.serialize(), mostrarResultadoNuevaVenta);
//            window.open('<?= base_url() ?>produccion/logistica/cajasPorFechaPrecio_xls?' + form.serialize(), '_blank');
//        });


        /*********** CARGA ORDENES ***********/
        $(document).on('click', '.btn-orden-numero', function (e) {
            orden_actual = $(this).data('orden_id');
            variante_actual_id = $(this).data('variante_id');
            loadOrden();
        });

        $("#exportar_excel").on('click', function () {
            unsoloclick('#exportar_excel');
            var form = $("#form_busqueda");
            console.log(form.serialize());
            window.open('<?= base_url() ?>produccion/logistica/resumenCajasPrecioDesglosado_excel?filtro=' + JSON.stringify(filtroActual), '_blank');
        });
        /*************** ACCIONES PARA EL MASTER SHIPPING *********************/
        $(".btn-accion").on('click', function () {
            unsoloclick('.btn-accion');
            if ($(this).val() === "visualizar") {
                llamadaAjax(false, '<?= base_url() ?>produccion/MasterShipping/json_imprimir_master_shipping', {"id": $(this).data('id')}, respuestaGenTarjMen);

            }
        });
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