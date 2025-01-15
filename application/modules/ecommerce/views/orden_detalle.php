<div class="modal-header color_tienda_<?= $orden->tienda_id ?> <?= $orden->estado == ESTADO_ORDEN_CANCELADA ? "orden_cancelada" : "" ?>">
    <div class="modal-title row col-10" id="Cabecera">
        <div class="row col-12 col-sm-6 col-lg-6 align-center align-items-center">
            <h6 class="col-6">
                Orden #<?= $orden->id ?>
            </h6>
            <?php if ($orden->referencia_order_number) { ?>
                <h5 class="col-6">
                    <?= isset($orden) ? $orden->tienda_alias . " " . $orden->referencia_order_number : '' ?>
                </h5>
            <?php } ?>
            <div class="col-12 col-md-3">
                <?php
                $avance = 10;
                switch ($orden->estado) {
                    case ESTADO_ACTIVO: $estado = '';
                        break;
                    case ESTADO_INACTIVO: $estado = 'INACTIVO';
                        break;
                    case ESTADO_ERROR: $estado = 'ERROR';
                        break;
                    case ESTADO_ORDEN_CANCELADA: $estado = 'CANCELADA';
                        break;
                    case ESTADO_ORDEN_ACTUALIZADA: $estado = 'ACTUALIZADA';
                        break;
                    case ESTADO_ORDEN_EMPACADA: $estado = 'EMPACADA';
                        $avance = 100;
                        break;
                    case ESTADO_ORDEN_LOGISTICA: $estado = 'LOGISTICA';
                        $avance = 50;
                        break;
                    case ESTADO_ORDEN_PREPARADA: $estado = 'PREPARADA';
                        $avance = 75;
                        break;
                    case ESTADO_ORDEN_PROCESADA: $estado = 'PROCESADA';
                        $avance = 25;
                        break;

                    case ESTADO_ORDEN_REENVIADA: $estado = 'REENVIO CREADO';
                        $avance = 25;
                        break;
                    case ESTADO_ORDEN_CLONADA: $estado = 'CLONACION REALIZADA';
                        $avance = 25;
                        break;
                    default: $estado = 'ERROR-INDEFINIDO';
                        break;
                }
                if (empty($estado) && ($orden->reenvio_orden_id != null)) {
                    $estado = "REENVIO";
                }
                ?>
                <?= !empty($estado) ? "<h2>" . $estado . "</h2>" : "" ?>
            </div>


        </div>
        <div class="row col-12 col-sm-6 col-lg-6 align-items-end ml-auto d-flex flex-row-reverse">
            <?php
            if (isset($orden)) {
                ?>
                <div class="row col-10 ml-auto">
                    <?php if ($orden->estado == ESTADO_ACTIVO) { ?>
                        <p class="col- ml-auto">
                            <?php if ($ecommerce) { ?>
                                <button type = "button" class="btn btn-accion-orden btn-tool col-12 col-md-6 offset-md-6" data-orden_id="<?= $orden->id ?>" value="imprimir_mensaje" data-toggle="tooltip" data-placement="bottom" title="Imprimir tarjeta">
                                    <i class="fas fa-print fa-sm"></i>Imprimir (x<?= $orden->impresiones ?>)
                                </button>
                                <?php
                            }
                            if ($perfil == PANTALLA_LOGISTICA) {
                                ?>
                            </p>
                            <p class="col- ml-auto"><button type="button" class="btn btn-tool btn-accion-orden" id="btn-preguntar_reenviar_orden" data-orden_id="<?= $orden->id ?>"  value="reenviar_orden" data-toggle="tooltip" data-placement="bottom" title="Reenviar Orden"><i class="fas fa-share-square"></i></button></p>
                            <p class="col- ml-auto"><button type="button" class="btn btn-tool btn-accion-orden" id="btn-preguntar_clonar_orden" data-orden_id="<?= $orden->id ?>"  value="clonar_orden" data-toggle="tooltip" data-placement="bottom" title="Clonar Orden"><i class="far fa-clone"></i></button></p>
                        <?php }
                    }
                    ?>
                    <?php
                    if (($perfil == PANTALLA_LOGISTICA) && ($orden->estado !== ESTADO_ORDEN_CANCELADA)) {
                        ?>
                        <p class="col- ml-auto">
                            <button type="button" class="btn btn-preguntar_accion_orden btn-tool visible_<?= $no_editable ?>" id="btn-preguntar_accion_orden" data-orden_id="<?= $orden->id ?>"  value="cancelar_orden"  data-toggle="tooltip" data-placement="bottom" title="Cancelar Orden"><i class="fas fa-trash"></i></button><br/>
                        </p>
                        <?php
                    }
                    ?>

                </div>
<?php } ?>
        </div>
    </div>

    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<div class="modal-body" style="font-size: 0.75rem">

    <div class="wrapper">
        <div class="">


            <!--  -->
            <section class="content">
                <!-- NVILLON -->
                <div class="container">
                    <div class="row">
                        <div class="col-sm-8 col-md-8">
                            <?php
                            if (isset($orden)) {
                                ?>
                                <input type="hidden" id="orden_id" name="orden_id" value="<?= $orden->id ?>">
                                <div class="card card-default">
                                    <!--                            <div class="card-header" data-toggle="collapse" data-target="#det_orden">
                                                                    <h6 class="card-title">Orden# <?= $orden->id ?></h6>
                                                                    <div class="card-tools">
                                                                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                                                                    </div>
                                                                </div>-->
                                    <div class="card-body" id="det_orden">
                                        <?php
                                        if ($perfil == PANTALLA_LOGISTICA) {
                                            ?>
                                            <div class="row">
                                                <div class="row col-10 <?= $no_editable ?>">
                                                    <div class="col-12 col-md-4 order-3 <?= $no_editable ?>">
        <?= inputFecha("Entrega", "fecha_entrega", $orden->fecha_entrega) ?>
                                                    </div>
                                                    <div class="col-12 col-md-4 order-2 <?= $no_editable ?>">
        <?= inputFecha("Embarque", "fecha_carguera", $orden->fecha_carguera) ?>
                                                    </div>
                                                    <div class="col-12 col-md-4 order-1 <?= $no_editable ?>">
        <?= inputFecha("Preparaci&oacute;n", "fecha_preparacion", $orden->fecha_preparacion) ?>
                                                    </div>
                                                </div>
                                                <div class="row col-2">
                                                    <div class="col-12 col-lg-3 order-4">
                                                        <button type="button" class="btn btn-preguntar_accion_orden btn-tool visible_<?= $no_editable ?>" id='btn-accion_guardar_fecha' data-orden_id="<?= $orden->id ?>" value="cambiar_fecha_orden"><i class="fas fa-save fa-2x"></i></button>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php
                                        } else {
                                            ?>
                                            <div class="row">
                                                <div class="col-12 col-lg-3 order-3">
                                                    <b>Fecha Entrega:</b></br> <?= substr($orden->fecha_entrega, 0, 10) ?>
                                                </div>
                                                <div class="col-12 col-lg-3 order-2">
                                                    <b>Fecha Embarque:</b></br> <?= substr($orden->fecha_carguera, 0, 10) ?>
                                                </div>
                                                <div class="col-12 col-lg-3 order-1">
                                                    <b>Fecha Preparaci&oacute;n:</b></br> <?= substr($orden->fecha_preparacion, 0, 10) ?>
                                                </div>
                                            </div>

                                            <?php
                                        }
                                        ?>
                                    </div>
                                </div>

                        <?php } ?>
                        </div>
                        <?php
                        if ($perfil == PANTALLA_EMPAQUE) {
                            ?>
                            <div class="col-sm-4 offset-sm-2 col-md-4 offset-md-0">
                                <div class="card text-dark">
                                    <div class="card-body" id="det_orden">
                                        <div class="row">
                                            <?php
                                            foreach ($cajas_orden as $k => $cajaobj) {
                                                $caja = $cajaobj;
                                            }
                                            ?>
                                            <div class="col-lg-6 col-md-6 col-xs-6 order-1">
                                                <button type = "button" class="btn btn-accion-orden  btn-primary" data-orden_id="<?= $caja->orden_id ?>" data-caja_id="<?= $caja->id ?>" value="imprimir_mensaje_caja">
                                                    <i class="fas fa-print"></i>Mensaje
                                                </button>
                                            </div>

                                            <div class="col-lg-6 col-md-6 col-xs-6 order-2">
                                              <!--  <button type = "button" class="btn btn-accion-orden btn-primary"  value="imprimir_mensaje_caja" data-toggle="tooltip" data-placement="bottom" title="Imprimir tarjeta"> <i class="fas fa-print fa-sm"></i> </button> -->
    <?php echo "" . ($caja->tracking_number ? '<a class="btn btn-accion-orden btn-primary" href="' . base_url() . 'uploads/tracking/' . $caja->tracking_number->tracking_number . '.pdf" target ="_blank"><i class="fas fa-print"> Ups/Fedex</i></a>' : ' ' ) . ""; ?>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                    </div>

                </div>
                <!-- NVILLON -->
            </section>
            <!--  -->

            <section class="content">
                <div class="container-fluid">

                    <?= isset($orden_cliente_resumen) ? $orden_cliente_resumen : '' ?>
                    <?= isset($orden_destino_resumen) ? $orden_destino_resumen : '' ?>
                    <?= isset($orden_items_resumen) ? $orden_items_resumen : '' ?>
<?= isset($orden_logistica_resumen) ? $orden_logistica_resumen : '' ?>
                </div>
            </section>
        </div>
    </div>
</div>
<?php /*
  <div class="modal-footer">
  <button type="button" class="btn btn-secondary" data-dismiss="modal">Salir</button>
  <button type="button" class="btn btn-primary btn-guardar-modal">Guardar Cambios</button>
  </div>
 */
?>

<div class="modal" tabindex="-1" id="modalEdicion" tabindex="-1" role="dialog" aria-labelledby="wsanchez" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">

        </div>
    </div>
</div>


<script>
    /********** ORDEN_DETALLE *************/



    function mostrarEdicion(r) {
        if (r.error) {
            mostrarError("No existe informaci&oacute;n disponible en estos momentos");
        } else {
            $("#modalEdicion .modal-content").html(r.respuesta);
            $("#modalEdicion").modal("show");
//            $('#modalEdicion').on('shown.bs.modal', function (e) {
////                 var link = $(e.relatedTarget);
////            scriptUnaVezCargado();
//            });
            $("#modalEdicion").on('hide.bs.modal', function () {
//                recargarPrincipal();
            });
        }
    }

    function setearProducto() {

    }
    function setearVariante() {

    }
    function mostrarEdicionOrdenItem(r) {
        console.log(r);
        if (analizarRespuesta(r)) {
            $("#modalEdicion .modal-content").html(r.respuesta);
            $("#modalEdicion").modal("show");
            console.log(r.orden_item);
            if (r.orden_item) {
                $('#producto_id').val(r.orden_item.producto_id);
                $('#producto_id').select2().trigger('change');

                $('#variante_id').val(r.variante_id);
                $('#variante_id').select2().trigger('change');


            }
            enlazarSelect('producto_id', 'variante_id', '<?= base_url() ?>ecommerce/orden_variante_select', false);
            llenarSelect("producto_id", '<?= base_url() ?>ecommerce/orden_producto_select', {"id": (r.orden_item ? r.orden_item.producto_id : 0)}, function () {
                $('#producto_id').select2().trigger('change');
            });

            $(".soloNumeros").inputFilter(function (value) {
                return /^-?\d*$/.test(value);
            });
        }
    }






    function actualizarFechas(r) {
        if (r.fecha_entrega) {
            $('#fecha_entrega').data('daterangepicker').remove();
            $('#fecha_entrega').daterangepicker({
                singleDatePicker: true,
                locale: {
                    format: '<?= FORMATO_FECHA_DATEPICKER_JS ?>'
                },
                startDate: r.fecha_entrega,
                endDate: r.fecha_entrega,
//                minDate: today,
                maxDate: '2050-01-01',
                autoApply: true
            });
        }
        if (r.fecha_carguera) {
            $('#fecha_carguera').data('daterangepicker').remove();
            $('#fecha_carguera').daterangepicker({
                singleDatePicker: true,
                locale: {
                    format: '<?= FORMATO_FECHA_DATEPICKER_JS ?>'
                },
                startDate: r.fecha_carguera,
                endDate: r.fecha_carguera,
                minDate: '2010-12-12',
                maxDate: r.fecha_entrega,
                autoApply: true
            });
        }
        if (r.fecha_preparacion) {
            $('#fecha_preparacion').data('daterangepicker').remove();
            $('#fecha_preparacion').daterangepicker({
                singleDatePicker: true,
                locale: {
                    format: '<?= FORMATO_FECHA_DATEPICKER_JS ?>'
                },
                startDate: r.fecha_preparacion,
                endDate: r.fecha_preparacion,
                minDate: '2010-12-12',
                maxDate: r.fecha_carguera,
                autoApply: true
            });
        }

        $('.fecha_entrega').on('apply.daterangepicker', function (e, picker) {
            llamadaAjax(false, '<?= base_url() ?>ecommerce/orden/calcular_fechas_entrega', {"orden_id": $(this).data('id'), "fecha_entrega": picker.startDate.format('YYYY-MM-DD')}, actualizarFechas);
        });
        $('.fecha_carguera').on('apply.daterangepicker', function (e, picker) {
            llamadaAjax(false, '<?= base_url() ?>ecommerce/orden/calcular_fechas_entrega', {"orden_id": $(this).data('id'), "fecha_carguera": picker.startDate.format('YYYY-MM-DD')}, actualizarFechas);
        });
        //recargarPrincipal();
    }


    /******************** orden_detalle_item_edicion ************************/

</script>