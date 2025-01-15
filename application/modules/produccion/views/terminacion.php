<div class="wrapper">
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h4>Terminaci&oacute;n / Vestido</h4>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Inicio</a></li>
                            <li class="breadcrumb-item">Terminaci&oacute;n</li>                            
                        </ol>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>
        <section class="content">
            <div class="container-fluid">
                <div class="card card-default" id="soloLectura">
                    <div class="card-header">
                        <h3 class="card-title">Listado de Productos a Vestir</h3>
                    </div>

                    <?=
                    filtroBusqueda("produccion/terminacion/resumenPorWrap", array("rango_busqueda" => $rango_busqueda, "tipo_calendario" => $tipo_calendario, "uso_calendario" => 4,
                        "store_id" => $store_id, "sel_store" => $sel_store,
                        "preparado" => $preparado, "terminado" => $terminado,
                        "totales" => $totales,
                        "exportar_xls" => true), array("producto_id" => $producto_id, "variante_id" => $variante_id));
                    ?>

                    <?php if (isset($preparacion_detalle_wrap_luxury)) { ?>
                        <div class="card-body text-center">                        
                            <div class="row" id="div_contenido" style="font-size: 0.8rem">
                                <div class="col-12"><h4>Con Wrap Luxury</h4></div>
                                <?= $preparacion_detalle_wrap_luxury ?>
                            </div>
                            <div class="row" id="div_contenido" style="font-size: 0.8rem">
                                <div class="col-12"><br/><br/><h4>Con Wrap Standard</h4></div>
                                <?= $preparacion_detalle_wrap_normal ?>
                            </div>
                            <div class="row" id="div_contenido" style="font-size: 0.8rem">
                                <div class="col-12"><br/><br/><h4>Sin Wrap</h4></div>
                                <?= $preparacion_detalle_sin_wrap ?>
                            </div>
                        </div>
                    <?php } ?>
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

    var orden_actual = 0;
    var div_recargar = 'div_contenido';
    var item_id = 0;
    var producto_id = 0;
    var variante_id = 0;
    var wrap = '';

    function refrescar_div_producto() {
        console.log("refrescar_div_producto");
        console.log("Orden_actual:" + orden_actual);
        console.log("Wrap:" + wrap);
        console.log("Producto:" + producto_id);
        console.log("Variante:" + variante_id);
//        var form = $("#form_busqueda");
//        var url = form.attr('action');
        div_recargar = "variante_total_" + wrap + "_" + variante_id;
        ////"producto_" + producto_id;
//        //
//        //console.log($("#"+div_recargar).html());
//        //console.log(div_recargar);
////        $(div_recargar).html(loadingBtn);
//        llamadaAjax(div_recargar, url, form.serialize(), mostrarDetalleVariante);
//        
//        variante_id = $(this).data('variante-id');
//        wrap = $(this).data('wrap');
        valor_terminado = 0;
        llamadaAjax(div_recargar, "<?= base_url() ?>produccion/terminacion/actualizarTotalTerminadosVariante", {variante_id: variante_id, valor_terminado: valor_terminado, wrap: wrap, store_id: <?= $store_id ?>, rango_busqueda: '<?= $rango_busqueda ?>', tipo_calendario:<?= $tipo_calendario ?>, terminado: '<?= $terminado ?>', preparado: '<?= $preparado ?>'}, actualizarVarianteIdTotal);
    }

    function recargarPrincipal() {
        console.log("Recargar_Principal en terminacion");
        loadOrden();
        console.log("RefrescarDivProducto llamado desde recargarPrincipal");
        refrescar_div_producto();
//        llamadaAjax(true, url, form.serialize(), mostrarDetalleVariante);
    }
    function mostrarOrden(r, small = false) {
        console.log("mostrarOrden");
        console.log("Variante_id es " + variante_id);
        console.log("Wrap es " + wrap);
        if (r.error) {
            mostrarError("No existe informaci&oacute;n disponible en estos momentos");
        } else {
            if (small) {
                $("#modalEdicionOrden .modal-content").parent().addClass("modal-xs");
                $("#modalEdicionOrden .modal-content").parent().removeClass("modal-xl");
            } else {
                $("#modalEdicionOrden .modal-content").parent().addClass("modal-xl");
                $("#modalEdicionOrden .modal-content").parent().removeClass("modal-xs");
            }
            $("#modalEdicionOrden .modal-content").html(r.detalle_orden);
            $("#modalEdicionOrden").modal("show");

            $("#modalEdicionOrden").on('hide.bs.modal', function () {
//                refrescar_div_producto();
            });


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
                llamadaAjax(false, '<?= base_url() ?>ecommerce/orden/calcular_fechas_entrega', {"orden_id": r.orden_id, "fecha_entrega": picker.startDate.format('YYYY-MM-DD')}, actualizarFechas);
            });
            $('.fecha_carguera').on('apply.daterangepicker', function (e, picker) {
                llamadaAjax(false, '<?= base_url() ?>ecommerce/orden/calcular_fechas_entrega', {"orden_id": r.orden_id, "fecha_carguera": picker.startDate.format('YYYY-MM-DD')}, actualizarFechas);
            });
    }
    }

    function loadOrden() {
        console.log("Load Orden #" + orden_actual + " en ordenes.php");
        console.log("Variante_id es " + variante_id);
        console.log("Wrap es " + wrap);
        llamadaAjax(false, '<?= base_url() ?>ecommerce/obtenerOrdenTerminacion', {"id": orden_actual}, mostrarOrden);
    }

    function mostrarDetalleVariante(r) {
        console.log("mostrarDetalleVariante");
        if (r.error) {
            mostrarError("No existe informaci&oacute;n disponible en estos momentos");
        } else {
//            console.log(r);
//            $("#div_detalle").removeClass('d-none');
//            $("#div_principal").addClass('d-none');
            $("#" + div_recargar).html(r.detalle);

        }
    }

    $(document).on('click', '.btn-accion-variante', function (e) {
        variante_id = $(this).data('variante-id');
        wrap = $(this).data('wrap');
        valor_terminado = $("#restante_variante_" + variante_id).val();
        llamadaAjax("btn-guardar-" + variante_id + "_" + wrap, "<?= base_url() ?>produccion/terminacion/actualizarTotalTerminadosVariante", {variante_id: variante_id, valor_terminado: valor_terminado, wrap: wrap, store_id: <?= $store_id ?>, rango_busqueda: '<?= $rango_busqueda ?>', tipo_calendario:<?= $tipo_calendario ?>, terminado: '<?= $terminado ?>', preparado: '<?= $preparado ?>'}, actualizarVarianteIdTotal);
    });


    $(document).ready(function () {


        $("#modalEdicionOrden").on('shown.bs.modal', function () {
//            alert('The modal is fully shown.');
//            $(this).find('p').text("This is changed text after alert");
        });


    });

//$("body").delegate("#modalEdicion .btn-guardar-modal", "click", function (e) {
//        e.preventDefault();
//        $("#form_orden_item_guardar").submit();
//    });




//    $(document).on('submit', '#form_busqueda', function (e) {
//////            $(this).value = 'presionado'; 
//////            $("#btn_buscar").html(loadingBtn);
//////            $("#btn_buscar").attr('disabled', true);
////        e.preventDefault();
////        e.stopImmediatePropagation();
////        if (!unsoloclick()) {
////            console.log("No hacemos el submit");
////            return false;
////        }
////        $("#producto_id").val(0);
////        $("#variante_id").val(0);
////        
//////        div_recargar = "div_contenido";
//////        var form = $(this);
//////        var url = form.attr('action');
//////        llamadaAjax('btn_buscar', url, form.serialize(), mostrarDetalleVariante);
//    });

    $("#exportar_excel").on('click', function () {
        unsoloclick('#exportar_excel');
        var form = $("#form_busqueda");
        console.log(form.serialize());
//            llamadaAjax('exportar_excel', '<?= base_url() ?>produccion/logistica/cajasPorFecha_xls', form.serialize(), mostrarResultadoNuevaVenta);
        window.open('<?= base_url() ?>produccion/terminacion/resumenXLS?' + form.serialize(), '_blank');
    });

    $("#exportar_pdf").on('click', function () {
        unsoloclick('#exportar_pdf');
        var form = $("#form_busqueda");
        console.log(form.serialize());
//            llamadaAjax('exportar_excel', '<?= base_url() ?>produccion/logistica/cajasPorFecha_xls', form.serialize(), mostrarResultadoNuevaVenta);
        window.open('<?= base_url() ?>produccion/terminacion/resumenPDF?' + form.serialize(), '_blank');
    });

</script>


<script>

    function actualizarVarianteIdTotal(r) {
        console.log("actualizarVarianteIdTotal " + r.wrap + " " + r.producto_variante_id);
        console.log("en " + "#variante_total_" + r.wrap + "_" + r.producto_variante_id);
        $("#variante_total_" + r.wrap + "_" + r.producto_variante_id).html(r.detalle);
        //actualizarTotalesResumen(<?= $store_id ?>, '<?= $rango_busqueda ?>', <?= $tipo_calendario ?>);
        //console.log(r);
    }
    $(document).on('click', '.restante_variante', function (e) {
        //alert($(this).data('id'));
//            $("#variante_id").val($(this).data('id'));
//            $("#btn_buscar").click();
    });
    $(document).on('click', '.btn-orden-numero', function (e) {
        orden_actual = $(this).data('orden_id');
        variante_id = $(this).data('variante_id');
        wrap = $(this).data('wrap');
        console.log("Click btn Variante_id es " + variante_id);
        console.log("Click btn Wrap es " + wrap);
        loadOrden();
    });

//    $(document).on('click', '.btn-accion-producto', function (e) {
//        e.preventDefault();
//        e.stopImmediatePropagation();
//        if (!unsoloclick()) {
//            console.log("No hacemos el submit");
//            return false;
//        }
//        console.log("click en producto " + $(this).data('id'));
//        $("#producto_id").val($(this).data('id'));
//        div_recargar = "div_contenido";
//        var form = $("#form_busqueda");
//        var url = "<?= base_url() ?>/produccion/terminacion/terminacion_detalle";
//        llamadaAjax(div_recargar, url, form.serialize(), mostrarDetalleVariante);
//    });
</script>