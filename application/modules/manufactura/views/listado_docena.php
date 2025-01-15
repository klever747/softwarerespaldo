<div class="wrapper">
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h4>Reporte por Docenas</h4>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Inicio</a></li>
                            <li class="breadcrumb-item">Manufactura</li>
                            <li class="breadcrumb-item">Reporte Docenas</li>
                        </ol>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>
        <section class="content">
            <div class="container-fluid">
                <div class="card card-default" id="soloLectura">
                    <?php
                    $filtroActual = array("rango_busqueda" => $rango_busqueda, "tipo_calendario" => $tipo_calendario, "uso_calendario" => 4,
                        "store_id" => $store_id, "sel_store" => $sel_store,
                        "perfil" => $perfil,
//                        'mostrarRestantes' => $mostrarRestantes,
                        "exportar_xls" => false,
                        "totales" => $totales,);
                    echo filtroBusqueda($url_busqueda, $filtroActual);
                    ?>

                    <div class="card-body text-center">
                        <div class="row" id="div_contenido" style="font-size: 0.8rem">
                            <table class="table table-dark">
                                <tr>
                                    <th>Producto</th>
                                    <th>Docenas</th>
                                </tr>
                                <?php
                                if ($listadoProductos) {
//                                print_r($listadoProductos);
                                    $total_docenas = $total_ordenes = 0;
                                    foreach ($listadoProductos as $prod) {
                                        if (empty($prod->data)) {
                                            continue;
                                        }

                                        $total_docenas += $prod->total_docenas;
                                        $total_ordenes += $prod->total_ordenes;
                                        ?>
                                        <tr>
                                            <th><?= $prod->producto_titulo ?></th>
                                            <td><?= $prod->data ?></td>
                                        </tr>
                                        <?php
                                    }
                                    echo "<br/>TotalGeneral<br/>";
                                    echo "<br/>Total docenas: " . $total_docenas;
                                    echo "<br/>Total ordenes: " . $total_ordenes;
                                    echo "<br/>Avg: " . round(($total_docenas / $total_ordenes), 2);
                                }
                                ?>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<!-- Modal -->
<div class="modal" id="modalEdicionOrden" tabindex="-1" role="dialog" aria-labelledby="wsanchez" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">

        </div>
    </div>
</div>

<div class="modal" id="modalActualizarTotales" tabindex="-1" role="dialog" aria-labelledby="wsanchez" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">

        </div>
    </div>
</div>

<script>
    var filtroActual = <?= json_encode($filtroActual) ?>;
    console.log("filtro actual es ");
    console.log(filtroActual)
    var listaProductosId = <?= json_encode($listadoProductosId) ?>;
    var orden_actual = 0;
    var variante_actual_id = 0;

    function cargarTotalProducto(r) {
        console.log("cargaProducto " + r.producto_id + " " + r.detalle);
        if (r.detalle != '') {
            $("#div_prod_" + r.producto_id).html(r.detalle);
        } else {
            $(".producto_" + r.producto_id).css("display", "none");
        }
    }

    function cargarDetalleProducto(producto_id) {
        llamadaAjax("div_prod_" + producto_id, "<?= base_url() ?>manufactura/json_mostrarProductoDetalle", {"filtro": filtroActual, "producto_id": producto_id}, cargarTotalProducto);
    }

    function cargarTotalVariante(r) {
        console.log("Cargar TotalVariante de la variante " + r.variante_id + " es " + r.detalle);
        if (r.detalle != '') {
            $("#div_variante_" + r.variante_id).html(r.detalle);
        } else {
            cargarDetalleProducto($("#div_variante_" + r.variante_id).data('producto_id'));
        }


    }

    function cargarDetalleVariante(variante_id) {
        llamadaAjax("div_variante_" + variante_id, "<?= base_url() ?>manufactura/json_mostrarVarianteDetalle", {"filtro": filtroActual, "variante_id": variante_id}, cargarTotalVariante);
    }

    function cargarTotalOrdenesVariante(r) {
        $("#div_variante_ordenes_" + r.variante_id).html(r.detalle);
    }

    function cargarDetalleOrdenesVariante(variante_id) {
        llamadaAjax("div_variante_" + variante_id, "<?= base_url() ?>manufactura/json_mostrarOrdenesDetalle", {"filtro": filtroActual, "variante_id": variante_id}, cargarTotalOrdenesVariante);
    }

    $(document).ready(function () {
//        listaProductosId.forEach(function (producto_id, indice, array) {
//            console.log(producto_id, indice);
//            cargarDetalleProducto(producto_id);
//        });

        $(document).on('click', '.btn-accion', function (e) {
            switch ($(this).data('accion')) {
                case 'refrescar_producto':
                    cargarDetalleProducto($(this).data('producto_id'));
                    break;

                case 'refrescar_variante':
                    cargarDetalleVariante($(this).data('variante_id'));
                    break;

                case 'actualizarTotalVariante':
                    loadActualizacionTotales($(this).data('variante_id'));
                default:

                    break;
            }
        });

        $(document).on('click', '.btn-orden-numero', function (e) {
            orden_actual = $(this).data('orden_id');
            variante_actual_id = $(this).data('variante_id');
            loadOrden();
        });

        $(document).on('click', '.btn-actualizar_totales', function () {
            $('#modalActualizarTotales').modal();
        });
    });


    $("#modalEdicionOrden").on('hide.bs.modal', function () {
        cargarDetalleVariante(variante_actual_id);
    });

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
        var carga = "<?= ($perfil == PANTALLA_MANUFACTURA ? "obtenerOrdenManufactura" : ($perfil == PANTALLA_PREPARACION ? "obtenerOrdenPreparacion" : ($perfil == PANTALLA_TERMINACION ? "obtenerOrdenTerminacion" : ""))) ?>";
        llamadaAjax(false, '<?= base_url() ?>ecommerce/' + carga, {"id": orden_actual}, mostrarOrden);

    }

    function recargarPrincipal() {
        console.log("Recargar Principal en preparacion");
        loadOrden();
        actualizarTotalesResumen(<?= $filtroActual['store_id'] ?>, '<?= $filtroActual['rango_busqueda'] ?>', <?= $filtroActual['tipo_calendario'] ?>, false);
        //refrescar_div_producto();
//        llamadaAjax(true, url, form.serialize(), mostrarDetalleVariante);
    }



    /************ ACTUALIZACION DE TOTALES ****************/

    function mostrarActualizacionTotales(r) {
        console.log("mostrarTotales");
        if (r.error) {
            mostrarError("No existe informaci&oacute;n disponible en estos momentos");
        } else {
            $("#modalActualizarTotales .modal-content").html(r.detalle);
            $("#modalActualizarTotales").modal("show");
            aplicarSoloNumeros();
            aplicarSoloNumerosDecimales();
            calcularRestanteBonchado();
            calcularRestanteLuxury();
            calcularRestanteStandard();
            calcularRestanteSinWrap();
            $("#modalActualizarTotales").on('hide.bs.modal', function () {
//                refrescar_div_producto();
            });
        }
    }

    function loadActualizacionTotales(variante_id) {
        console.log("Load Actualizacion Totales#" + variante_id + " en manufactura");
        llamadaAjax(false, '<?= base_url() ?>manufactura/json_mostrarActualizacionTotalesVariante', {"filtro": filtroActual, "variante_id": variante_id}, mostrarActualizacionTotales);
    }


    function procesarActualizacionTotales(r) {
        if (r.error) {
            mostrarExito(r.mensaje);
        }
        cargarDetalleVariante(variante_actual_id);
        $("#modalActualizarTotales").modal("hide");
        actualizarTotalesResumen(<?= $filtroActual['store_id'] ?>, '<?= $filtroActual['rango_busqueda'] ?>', <?= $filtroActual['tipo_calendario'] ?>, false);
    }

    function calcularRestanteBonchado() {
        var totalItems = parseInt($("#totalItemsPedidos").text(), 10);
        var totalItemsBonchado = parseInt($("#totalItemsPedidosB").text(), 10);
        var ingresarB = parseInt($("#ingresoB").val(), 10);

        var total = totalItems - totalItemsBonchado;

        if (total === 0) {
            $("#ingresoB").text(0);
            $("#ingresoB").prop("disabled", true);
            return;
        }
        if (total < ingresarB) {
            $("#ingresoB").val(0);
            mostrarError('No puede ingresar un valor mayor al restante ' + total);
            return;
        }
        $("#restanteB").text(total - ingresarB);
    }

    function calcularRestanteLuxury() {
        var totalLuxury = parseInt($("#totalLuxury").text(), 10);
        var totalLuxuryV = parseInt($("#totalLuxuryV").text(), 10);
        var ingresarL = parseInt($("#ingresoL").val(), 10);

        var total = totalLuxury - totalLuxuryV;

        if (total === 0) {
            $("#ingresoL").text(0);
            $("#ingresoL").prop("disabled", true);
            return;
        }
        if (total < ingresarL) {
            $("#ingresoL").val(0);
            mostrarError('No puede ingresar un valor mayor al restante ' + total);
            return;
        }
        $("#restanteL").text(total - ingresarL);
    }

    function calcularRestanteStandard() {
        var totalStandard = parseInt($("#totalStandard").text(), 10);
        var totalStandardV = parseInt($("#totalStandardV").text(), 10);
        var ingresarS = parseInt($("#ingresoS").val(), 10);

        var total = totalStandard - totalStandardV;

        if (total === 0) {
            $("#ingresoS").text(0);
            $("#ingresoS").prop("disabled", true);
            return;
        }
        if (total < ingresarS) {
            $("#ingresoS").val(0);
            mostrarError('No puede ingresar un valor mayor al restante ' + total);
            return;
        }
        $("#restanteS").text(total - ingresarS);
    }

    function calcularRestanteSinWrap() {
        var totalSin = parseInt($("#totalSin").text(), 10);
        var totalSinV = parseInt($("#totalSinV").text(), 10);
        var ingresarN = parseInt($("#ingresoN").val(), 10);

        var total = totalSin - totalSinV;

        if (total === 0) {
            $("#ingresoN").text(0);
            $("#ingresoN").prop("disabled", true);
            return;
        }
        if (total < ingresarN) {
            $("#ingresoN").val(0);
            mostrarError('No puede ingresar un valor mayor al restante ' + total);
            return;
        }
        $("#restanteN").text(total - ingresarN);
    }

    $(document).on('keyup', '.ingresoPreparado', function (e) {
        calcularRestanteBonchado();
        calcularRestanteLuxury();
        calcularRestanteStandard();
        calcularRestanteSinWrap();
    });

    $(document).on('click', '#btn_actualizar_totales', function (e) {
        variante_actual_id = $(this).data('variante_id');
        var variante_id = $(this).data('variante_id');
        var b = $("#ingresoB").val() != '' ? $("#ingresoB").val() : 0;
        var l = $("#ingresoL").val() != '' ? $("#ingresoL").val() : 0;
        var s = $("#ingresoS").val() != '' ? $("#ingresoS").val() : 0;
        var n = $("#ingresoN").val() != '' ? $("#ingresoN").val() : 0;
        llamadaAjax(false, '<?= base_url() ?>manufactura/json_actualizacionTotalesVariante', {"filtro": filtroActual, "variante_id": variante_id, "b": b, "l": l, "s": s, "n": n}, procesarActualizacionTotales);
    });

    /************** EXPORTACION *******************/
    $("#exportar_excel").on('click', function () {
        unsoloclick('#exportar_excel');
        var form = $("#form_busqueda");
        console.log(form.serialize());
        window.open('<?= base_url() ?>manufactura/exportarExcel?filtro=' + JSON.stringify(filtroActual), '_blank');
    });
</script>