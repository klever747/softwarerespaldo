<link rel="stylesheet" href="<?= base_url() . "assets/" ?>/css/emoji.css">
<!--<link rel = "stylesheet" href = "<?= base_url() . "assets/" ?>img/emoji.png">-->


<div class="card-body text-center">

</div>

<script>
    var filtroActual = <?= json_encode($filtroActual) ?>;
    var orden_actual = <?= $orden_actual ?>;
    var item_id = 0;
    var producto_id = 0;
    var variante_id = 0;

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

    function mostrarRespuesta(r) {
        console.log("mostrarRespuesta ordenes ");
        if (analizarRespuesta(r)) {
            recargarPrincipal();
        }
    }

    function respuestaEmpaqueOrden(r) {
        if (analizarRespuesta(r)) {
            console.log(r);
            console.log(r.orden_id);
            loadOrdenCard(r.orden_id);
        }
    }

    function recargarPrincipalOrdenes(r) {
        if (r.error) {
            mostrarError("Hubo un error, por favor intentelo nuevamente");
        } else {
            mostrarExito("Actualizado");
            $("#btn_buscar").trigger("click");
        }

    }

    function mostrarOrdenNueva(r) {
        mostrarOrden(r, true);
    }

    function respuestaGenTarjMen(r) {
        if (analizarRespuesta(r) && (r.ruta_pdf != '')) {
            console.log("ruta es ");
            console.log(r.ruta_pdf);
            window.open(r.ruta_pdf, '_blank');
            $.each(r.ordenes_impresas, function (key, orden_id) {
                loadOrdenCard(orden_id);
            });
        }
    }

    function respuestaReenviarOrden(r) {
        if (analizarRespuesta(r)) {
            $('#confirmacion_accion').hide();
            console.log(r);
            orden_actual = r.nueva_orden_id;
            loadOrden();
        }
    }

    function respuestaEmpaqueMasivo(r) {
        if (analizarRespuesta(r)) {
            console.log(r);
            $.each(r.empacados, function (key, orden_id) {
                loadOrdenCard(orden_id);
            });
        }
    }

    function mostrarOrdenCard(r) {
        console.log("Mostrar orden card");
        console.log(r);
        $("#card_orden_" + r.orden_id).html(r.card);
    }

    function mostrarOrden(r, small = false) {
        console.log("mostrarOrden " + orden_actual);
        //        console.log(r.detalle_orden);
        //        console.log(r.error);
        //        console.log(small);
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
            var nowDate = new Date();
            //            var fechaEntrega = new Date()
            //            console.log("actualizarFechas");
            //        console.log(nowDate);
            //        console.log(new Date(r.fecha_entrega));
            var today = new Date(nowDate.getFullYear(), nowDate.getMonth(), nowDate.getDate(), 0, 0, 0, 0);
            $('.select_fecha').daterangepicker({
                singleDatePicker: true,
                locale: {
                    format: '<?= FORMATO_FECHA_DATEPICKER_JS ?>'
                },
                maxDate: '2050-12-13',
                autoApply: true
            });
            //esto deberia estar en el mismo orden_detalle
            $('.fecha_entrega').on('apply.daterangepicker', function (e, picker) {
                llamadaAjax('btn-accion_guardar_fecha', '<?= base_url() ?>ecommerce/orden/calcular_fechas_entrega', {
                    "orden_id": r.orden_id,
                    "fecha_entrega": picker.startDate.format('YYYY-MM-DD')
                }, actualizarFechas);
            });
            $('.fecha_carguera').on('apply.daterangepicker', function (e, picker) {
                llamadaAjax('btn-accion_guardar_fecha', '<?= base_url() ?>ecommerce/orden/calcular_fechas_entrega', {
                    "orden_id": r.orden_id,
                    "fecha_carguera": picker.startDate.format('YYYY-MM-DD')
                }, actualizarFechas);
            });
    }
    }

    //    function mostrarEdicion(r) {
    //        if (r.error) {
    //            mostrarError("No existe informaci&oacute;n disponible en estos momentos");
    //        } else {
    //            $("modalEdicionOrden .modal-content-orden").html(r.respuesta);
    //            $("#modalEdicionOrden").modal("show");
    //        }
    //    }

    function mostrarEliminacion(r) {
        if (r.error) {
            mostrarError("Hubo un problema durante la eliminaci&oacute;n");
        } else {
            location.reload();
        }
    }

    function loadOrden() {
        console.log("loadOrden " + orden_actual);
        llamadaAjax(false, '<?= base_url() ?>ecommerce/obtenerOrden', {
            "id": orden_actual,
            "perfil": <?= $perfil ?>
        }, mostrarOrden);
    }

    function loadOrdenCard(orden_id = false) {
        console.log("loadOrdenCard en ordenes " + orden_actual);
        orden_id = (!orden_id) ? orden_actual : orden_id;
        llamadaAjax(false, '<?= base_url() ?>ecommerce/json_orden_card', {
            "orden_id": orden_id,
            "filtro": filtroActual
        }, mostrarOrdenCard);
    }

    function recargarPrincipal() {
        console.log("RecargarPrincipal en ordenes");
        loadOrden();
        loadOrdenCard();
    }

    $(document).ready(function () {

        $("#tracking_number").focus();
        //        $("#tracking_number").on('keypress', function (e) {
        //            if (e.which == 13) {
        //                alert('You pressed enter!');
        //            }
        //        });

        /*************** ACCIONES PANTALLA PRINCIPAL *********************/
        $(document).on('click', '.btn-accion', function () {
            unsoloclick('.btn-accion');
            if ($(this).val() === "agregar_orden") {
                alert('Desde la administración de Clientes puede crear una orden nueva');
            }
        });
        $(document).on('click', '.btn-orden-numero', function () {
            orden_actual = $(this).data('orden_id');
            loadOrden();
        });
        //        $("#modalEdicionOrden").on('shown.bs.modal', function () {
        ////            alert('The modal is fully shown.');
        ////            $(this).find('p').text("This is changed text after alert");
        //        });

        $(document).on('click', '.btn-accion-logistica', function () {
            if ($(this).val() === "meter_en_caja") {
                llamadaAjax(true, '<?= base_url() ?>produccion/logistica/orden_meter_a_caja', {
                    "orden_id": $(this).data('id'),
                }, respuestaEmpaqueOrden);
            }
        });

        function respuestaObtenerReenvio(r) {
            console.log(r.error);
            console.log(r.error === true);
            if (r.error === true) {
                $("#confirmacion_accion .modal-body").html("Seleccione el tipo de reenvio");
                $("#confirmacion_accion .modal-title").html("Reenvio de Orden");
                $("#btn_accion_orden_1").css("display", "block");
                $("#btn_accion_orden_2").css("display", "block");

                $("#confirmacion_accion #btn_accion_orden_1").html("Parcial");
                $("#confirmacion_accion #btn_accion_orden_1").data("orden_id", r.orden_id);
                $("#confirmacion_accion #btn_accion_orden_1").data("accion", "reenvio_orden_parcial");

                $("#confirmacion_accion #btn_accion_orden_2").html("Total");
                $("#confirmacion_accion #btn_accion_orden_2").data("orden_id", r.orden_id);
                $("#confirmacion_accion #btn_accion_orden_2").data("accion", "reenvio_orden_total");

                $('#confirmacion_accion').modal();
            } else {
                mostrarExito(r.mensaje);
                orden_actual = r.reenvio_orden_id;
                loadOrden();
            }
        }

        function respuestaObtenerClonacion(r) {
            console.log(r.error);
            console.log(r.error === true);
            if (r.error === true) {
                $("#confirmacion_accion .modal-body").html("Desea clonar esta orden?");
                $("#confirmacion_accion .modal-title").html("Clonaci&oacute;n de Orden");
                $("#btn_accion_orden_1").css("display", "block");
                $("#btn_accion_orden_2").css("display", "none");

                $("#confirmacion_accion #btn_accion_orden_1").html("Si");
                $("#confirmacion_accion #btn_accion_orden_1").data("orden_id", r.orden_id);
                $("#confirmacion_accion #btn_accion_orden_1").data("accion", "clonacion_orden");

                $('#confirmacion_accion').modal();
            } else {
                mostrarExito(r.mensaje);
                orden_actual = r.clonacion_orden_id;
                loadOrden();
            }
        }

        $(document).on('click', '.btn-accion-orden', function () {
            console.log("btn_accion_orden");
            if ($(this).val() === "imprimir_mensaje") {
                llamadaAjax(true, '<?= base_url() ?>ecommerce/orden/json_imprimir_tarjeta', {
                    "orden_id": $(this).data('orden_id'),
                }, respuestaGenTarjMen);
            } else if ($(this).val() === "imprimir_mensaje_eternizadas") {
                llamadaAjax(true, '<?= base_url() ?>ecommerce/orden/json_imprimir_tarjeta_eternizadas', {
                    "orden_id": $(this).data('orden_id'),
                }, respuestaGenTarjMen);
            } else if ($(this).val() === "reenviar_orden") {
                //vamos a verificar si existe una orden asociada, para cargar esa
                console.log("Reenviar orden");
                llamadaAjax(true, '<?= base_url() ?>produccion/orden/json_obtener_reenvio', {
                    "orden_id": $(this).data('orden_id'),
                }, respuestaObtenerReenvio);

            } else if ($(this).val() === "clonar_orden") {
                //vamos a verificar si existe una orden asociada, para cargar esa
                console.log("Clonar orden");
                llamadaAjax(true, '<?= base_url() ?>produccion/orden/json_obtener_clonacion', {
                    "orden_id": $(this).data('orden_id'),
                }, respuestaObtenerClonacion);

            }
        });
        //-------------------------------
        // boton imprimir-tarjetas-masivo
        //-------------------------------        
        $(document).on('click', '.btn-imprimir', function () {

            if ($(this).val() === "ordenes_seleccionadas") {
                //obtenemos todas las ordenes que tengan un visto
                var ids = '';
                $('input[type=checkbox][name=orden_impresion]').each(function () {
                    if (this.checked) {
                        console.log($(this).val());
                        ids += "-" + $(this).val();
                    }
                });
                if (ids.length > 0) {
                    llamadaAjax("imprimir_tarjeta", '<?= base_url() ?>ecommerce/orden/imprimir_tarjeta_masivo', {
                        "ordenes_id": ids,
                    }, respuestaGenTarjMen);
                }
            }
        });
        $(document).on('click', '#empacar_ordenes', function () {
            var ids = '';
            $('input[type=checkbox][name=orden_impresion]').each(function () {
                if (this.checked) {
                    //                    console.log($(this).val());
                    ids += "-" + $(this).val();
                }
            });
            if (ids.length > 0) {
                llamadaAjax("empacar_ordenes", '<?= base_url() ?>produccion/logistica/empacar_masivo', {
                    "ordenes_id": ids,
                }, respuestaEmpaqueMasivo);
            }
        });
<?php if ($orden_actual) {
    ?>
            loadOrden();
    <?php
}
?>

    });
    /****** CLIENTE_DIRECCION_DESTINO ***********/
    $(document).on('click', '#orden_detalle_destino .btn-accion', function () {
        if ($(this).val() === "editar_destino_orden") {
            llamadaAjax(false, '<?= base_url() ?>ecommerce/cliente/orden_direccion_envio_edicion', {
                "direccion_id": $(this).data('direccion_id'),
                "cliente_id": $(this).data('cliente_id'),
                "orden_id": $(this).data('orden_id')
            }, mostrarEdicion);
        } else if ($(this).val() === "cambiar_destino_orden") {
            console.log("Cambiar destino orden");
            console.log($(this).data('direccion_id'));
            console.log($(this).data('cliente_id'));
            console.log($(this).data('orden_id'));
            llamadaAjax(false, '<?= base_url() ?>ecommerce/cliente/cliente_direcciones_envio_listado', {
                "direccion_id": $(this).data('direccion_id'),
                "cliente_id": $(this).data('cliente_id'),
                "orden_id": $(this).data('orden_id')
            }, mostrarEdicion);
        }
    });

    function actualizarDireccionOrden(orden_id, nueva_direccion_id) {
        llamadaAjax(false, '<?= base_url() ?>ecommerce/actualizar_direccion_orden', {
            "orden_id": orden_id,
            "cliente_direccion_id": nueva_direccion_id
        }, recargarPrincipal);
    }

    $(document).on('click', '#orden_detalle_destino_edicion .btn-accion', function () {
        if ($(this).val() === "seleccionar_direccion_destino_orden") {
            console.log("Ai en seleccionar direccion destino orden");
            actualizarDireccionOrden($(this).data('orden_id'), $(this).data('direccion_id'));
        }
    });
    /*********** ORDEN DETALLE CLIENTE *************/
    $(document).on('click', '#orden_detalle_cliente .btn-accion', function () {
        unsoloclick('.btn-accion');
        if ($(this).val() === "editar_cliente_orden") {
            llamadaAjax(false, '<?= base_url() ?>ecommerce/cliente/cliente_obtener', {
                "id": $(this).data('id'),
                "mostrar_direccion_envio": 0
            }, mostrarEdicion);
        }
    });

    /***************** ORDEN ACCIONES *****************/
    $(document).on('click', '.btn-preguntar_accion_orden', function () {

        if ($(this).val() === "cancelar_orden") {
            $("#confirmacion_accion .modal-body").html("Confirme la cancelaci&oacute;n de la orden");
            $("#confirmacion_accion .modal-title").html("Cancelar Orden");
            $("#btn_accion_orden_1").css("display", "block");
            $("#btn_accion_orden_2").css("display", "none");

            $("#confirmacion_accion .modal-content").parent().addClass("modal-xs");
            $("#confirmacion_accion .modal-content").parent().removeClass("modal-lg");
            $("#confirmacion_accion .modal-content").parent().removeClass("modal-xl");

            $("#confirmacion_accion #btn_accion_orden_1").html("Cancelar Orden");
            $("#confirmacion_accion #btn_accion_orden_1").data("orden_id", $(this).data('orden_id'));
            $("#confirmacion_accion #btn_accion_orden_1").data("accion", "cancelar_orden");

            $('#confirmacion_accion').modal();
        } else if ($(this).val() === "cambiar_fecha_orden") {
            $("#confirmacion_accion .modal-body").html("Confirme el guardar la fecha de la orden <br/> <ul><li>Si existen items de la orden marcados como bonchados/vestidos estos ser&aacute;n desmarcados.</li></ul>");
            $("#confirmacion_accion .modal-title").html("Cambio fecha de Orden");
            $("#btn_accion_orden_1").css("display", "block");
            $("#btn_accion_orden_2").css("display", "none");

            $("#confirmacion_accion .modal-content").parent().removeClass("modal-xs");
            $("#confirmacion_accion .modal-content").parent().addClass("modal-lg");
            $("#confirmacion_accion .modal-content").parent().removeClass("modal-xl");

            $("#confirmacion_accion #btn_accion_orden_1").html("Guardar fecha de la orden");
            $("#confirmacion_accion #btn_accion_orden_1").data("orden_id", $(this).data('orden_id'));
            $("#confirmacion_accion #btn_accion_orden_1").data("accion", "cambiar_fecha_orden");

            $('#confirmacion_accion').modal();
        }
    });

    $(document).on('click', '.btn_accion_orden', function () {
        console.log($(this).data('accion'));
        if ($(this).data('accion') === "cancelar_orden") {
            llamadaAjax('btn_accion_orden', '<?= base_url() ?>ecommerce/orden/cancelar_orden', {
                "orden_id": $(this).data('orden_id')
            }, recargarPrincipalOrdenes);
        }
        if ($(this).data('accion') === "cambiar_fecha_orden") {
            llamadaAjax('btn-accion_guardar_fecha', '<?= base_url() ?>ecommerce/orden/actualizar_fecha_orden', {
                "orden_id": $(this).data('orden_id'),
                "fecha_entrega": $("#fecha_entrega").val(),
                "fecha_carguera": $("#fecha_carguera").val(),
                "fecha_preparacion": $("#fecha_preparacion").val()
            }, mostrarRespuesta);
        }
        if ($(this).data('accion') === "reenvio_orden_total") {
            llamadaAjax('btn_accion_orden', '<?= base_url() ?>produccion/orden/json_reenviar_orden_total', {
                "orden_id": $(this).data('orden_id')
            }, respuestaReenviarOrden);
        }
        if ($(this).data('accion') === "reenvio_orden_parcial") {
            llamadaAjax('btn_accion_orden', '<?= base_url() ?>produccion/orden/json_reenviar_orden_parcial', {
                "orden_id": $(this).data('orden_id')
            }, respuestaReenviarOrden);
        }
        if ($(this).data('accion') === "clonacion_orden") {
            llamadaAjax('btn_accion_orden', '<?= base_url() ?>produccion/orden/json_clonar_orden', {
                "orden_id": $(this).data('orden_id')
            }, respuestaReenviarOrden);
        }
    });
</script>