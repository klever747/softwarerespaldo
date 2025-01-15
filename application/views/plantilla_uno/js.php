
<script src="<?= base_url() . "assets/" ?>bootstrap4/js/jquery-ui.min.js"></script>
<script src="<?= base_url() . "assets/" ?>bootstrap4/js/pooper.min.js"></script>
<script src="<?= base_url() . "assets/" ?>bootstrap4/js/moment.min.js"></script>
<script src="<?= base_url() . "assets/" ?>bootstrap4/plugins/daterangepicker/js/daterangepicker.min.js"></script>

<script src="<?= base_url() . "assets/" ?>adminlte3/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
<script src="<?= base_url() . "assets/" ?>bootstrap4/js/bootstrap.bundle.min.js"></script>
<script src="<?= base_url() . "assets/" ?>adminlte3/plugins/toastr/toastr.min.js"></script>

<script src="<?= base_url() . "assets/" ?>adminlte3/plugins/select2/js/select2.full.min.js"></script>
<script src="<?= base_url() . "assets/" ?>adminlte3/plugins/bootstrap4-duallistbox/jquery.bootstrap-duallistbox.min.js"></script>
<script src="<?= base_url() . "assets/" ?>adminlte3/plugins/jquery-validation/jquery.validate.min.js"></script>
<script src="<?= base_url() . "assets/" ?>adminlte3/plugins/jquery-validation/additional-methods.min.js"></script>


<script src="<?= base_url() . "assets/" ?>adminlte3/plugins/sweetalert2/sweetalert2download.js"></script>

<script src="<?= base_url() . "assets/" ?>adminlte3/dist/js/adminlte.min.js"></script>

<script>
    $( document ).ready(function() {
        setTimeout(validaciones_buttons,1);
    });
    function validaciones_buttons(){
        if($('select').hasClass('no_editable') || $('button').hasClass('no_editable') || $('div').hasClass('no_editable')){
            $(".no_editable").addClass('disabled');
            $(".no_editable").click(function() { return false; }); 
            $('.no_editable').prop('disabled', true);
            $(".no_editable :input").attr("disabled", "disabled");
        }
    }
    var loadingBtn = "<div class='spinner-border spinner-border-sm' role='status'><span class='sr-only'>Loading...</span></div>";
    $(document).on('click', '#btn_buscar', function (e) {
        $(this).val("presionado");
        $("#btn_buscar").html(loadingBtn);
        $("#btn_buscar").unbind('click');
    });
    function llamadaAjax(idBoton, url, parametros, callback) {
//        console.log("Llamada Ajax "+idBoton+url+parametros+callback);
        if (idBoton) {
            var textoBtn = $("#" + idBoton).html();
            $("#" + idBoton).html(loadingBtn);
            $("#" + idBoton).attr('disabled', true);
        }
        $.ajax({
            url: url,
            type: 'post',
            data: parametros,
            cache: false,
            success: function (r) {
                if (idBoton) {
                    $("#" + idBoton).html(textoBtn);
                    $("#" + idBoton).attr('disabled', false);
                }
                if (callback !== 0) {
                    eval(callback)(r);
                }
            }
        });
    }
    //Modal
    function swal_info( _mensaje)
    {
        Swal.fire(
            'Información',
            _mensaje,
            'info');
    }

    function swal_listo(_mensaje )
    {
        Swal.fire({
                  position: 'top-end',
                  icon: 'success',
                  title: _mensaje,
                  showConfirmButton: false,
                  timer: 1500
                });
    }

    function swal_error(_mensaje )
    {
        Swal.fire({
                  position: 'top-end',
                  icon: 'error',
                  title: _mensaje,
                  showConfirmButton: false,
                  timer: 1500
                });
    }

        function swal_modal(texto, leyenda_afirmativa, leyenda_denegacion, url, parametros, callback)
    {
        var respuesta = false;
          Swal.fire({
          title: texto,
          icon: 'info',
          showConfirmButton: true,
          showDenyButton: true,
          showCancelButton: false,
          confirmButtonText: leyenda_afirmativa,
          denyButtonText: leyenda_denegacion
        }).then((result) => {
          /* Read more about isConfirmed, isDenied below */
          if (result.isConfirmed) {
              llamadaAjax(false, url, parametros, callback);
             // swal_listo('Registro inactivado');
          } else if (result.isDenied) {
             //Evento que se hará cuando se denegara
          }
        });
    }

    function btnLlamadaAjax(idBtn, url, funcionPrevia, callback) {
        $('#' + idBtn).on("click", function () {
            var parametros = eval(funcionPrevia)();
            console.log(idBtn + "  " + parametros);
            llamadaAjax(idBtn, url, parametros, callback);
        });
    }

    function llenarSelect(idSelect, url, parametros, callback) {
        $("#" + idSelect).html("Loading....");
        $("#" + idSelect).attr('disabled', true);
        $.ajax({
            url: url,
            type: 'post',
            data: parametros,
            cache: false,
            success: function (r) {
                $("#" + idSelect).html("");
                $.each(r, function (key, obj) {
                    $("#" + idSelect).attr('disabled', false);
                    var option = document.createElement('option');
                    option.appendChild(document.createTextNode(obj.valor));
                    option.value = obj.clave;
                    var datos = obj.arr_data;
                    if(datos != undefined){
                        if (Object.keys(datos).length > 0){ 
                            for(key in datos) {
                                if(datos.hasOwnProperty(key)) {
                                    var value = datos[key];
                                    option.dataset.nombre = value;
                                }
                            }  
                        }  
                    }
                   $("#" + idSelect).append(option);
                });
                $("#" + idSelect).select2();
                if (callback != 0) {
                    eval(callback)();
                }
            }
        });
    }

    function enlazarSelect(idSelOrigen, idSelDestino, url, callback) {
        console.log("enlazarSelect");
        $('#' + idSelOrigen).on("change", function () {
            var data = {
                "id": $('#' + idSelOrigen).val()
            };
            llenarSelect(idSelDestino, url, data, callback);
        });
    }
</script>

<script>
    (function ($) {
        $.fn.inputFilter = function (inputFilter) {
            return this.on("input keydown keyup mousedown mouseup select contextmenu drop", function () {
                if (inputFilter(this.value)) {
                    this.oldValue = this.value;
                    this.oldSelectionStart = this.selectionStart;
                    this.oldSelectionEnd = this.selectionEnd;
                } else if (this.hasOwnProperty("oldValue")) {
                    this.value = this.oldValue;
                    this.setSelectionRange(this.oldSelectionStart, this.oldSelectionEnd);
                } else {
                    this.value = "";
                }
            });
        };
    }(jQuery));
    function aplicarSoloNumeros() {
        $(".soloNumeros").inputFilter(function (value) {
            return /^-?\d*$/.test(value);
        });
    }
    function aplicarSoloNumerosDecimales() {
        $(".soloNumerosDecimales").inputFilter(function (value) {
//            return /^-?\d*$/.test(value);
            return /^-?\d*[.]?\d*$/.test(value);
        });
    }
    aplicarSoloNumeros();
    aplicarSoloNumerosDecimales();
    /*
     // Install input filters.
     //Integer
     $("#intTextBox").inputFilter(function(value) {
     return /^-?\d*$/.test(value); });
     //Integer > = 0
     $("#uintTextBox").inputFilter(function(value) {
     return /^\d*$/.test(value); });
     //Integer >= 0 and <= 500
     $("#intLimitTextBox").inputFilter(function(value) {
     return /^\d*$/.test(value) && (value === "" || parseInt(value) <= 500); });
     //Float (use . or , as decimal separator)
     $("#floatTextBox").inputFilter(function(value) {
     return /^-?\d*[.,]?\d*$/.test(value); });
     //Currency (at most two decimal places)
     $("#currencyTextBox").inputFilter(function(value) {
     return /^-?\d*[.,]?\d{0,2}$/.test(value); });
     //A-Z only
     $("#latinTextBox").inputFilter(function(value) {
     return /^[a-z]*$/i.test(value); });
     //Hexadecimal
     $("#hexTextBox").inputFilter(function(value) {
     return /^[0-9a-f]*$/i.test(value); });
     */

    function mostrarError($mensaje) {
        toastr.error($mensaje);
    }
    function mostrarExito($mensaje) {
        toastr.success($mensaje);
    }


    function actualizarTotalesResumen(store_id, rango_busqueda, tipo_calendario, tipo) {
        console.log("actualizarTotalesResumen");
        $(".totales_resumen").html('ali');

        $.ajax({
            type: "POST",
            url: "<?= base_url() ?>/ecommerce/totalResumen",
            cache: false,
            data: {store_id: store_id, rango_busqueda: rango_busqueda, tipo_calendario: tipo_calendario, tipo: tipo}, // serializes the form's elements.
            success: function (data)
            {
                console.log(data);
                if (data.error) {
                    mostrarError(data.respuesta);
                } else {
                    console.log("actualizo total");
                    $(".totales_resumen").html(data.respuesta);
                }
            }
        });

    }

    $(document).ready(function () {

$('body').on('hidden.bs.modal', function () {
if($('.modal.show').length > 0)
{
    $('body').addClass('modal-open');
}
});

        $(function () {
            $('[data-toggle="tooltip"]').tooltip()
        });


        $('.select2').select2({
//            minimumInputLength: 3
        });
        $('.fecha_rango').daterangepicker({
            locale: {
                format: '<?= FORMATO_FECHA_DATEPICKER_JS ?>'
            },
            autoApply: true
        });
        $('.fecha_unica').daterangepicker({
            singleDatePicker: true,
            locale: {
                format: '<?= FORMATO_FECHA_DATEPICKER_JS ?>'
            },
            autoApply: true
        });
        $('.fecha_rango_full').daterangepicker({
         timePicker: true,
         timePickerSeconds: true,
            locale: {
                format: '<?= FORMATO_FECHA_DATEPICKER_FULL_JS ?>'
            },
            autoApply: true
        });
        $('.select2bs4').select2({
            theme: 'bootstrap4'
        })


        /*para la paginacion*/
        $(".item_paginacion").on("click", function (e) {
            e.preventDefault();
            if ($(this).text() != '...') {
                $("#paginacion_pag").val($(this).text());
                $("#btn_buscar").trigger("click");
            }
        });
        $("#order_number").on('keypress', function (e) {
            if (e.which === 13) {
                $("#btn_buscar").trigger("click");
            }
        });
        $('#myModal').on('shown.bs.modal', function () {
            $('#myModal').trigger('focus');
        });
//        $("#btn_buscar").on("click", function (e) {
//            e.value = 'presionado';
//            $(this).html(loadingBtn);
//            $(this).attr('disabled', true);
//        });
    });
//    $(document).ready(function () {
//        $(window).on('resize', function () {
//            var winWidth = $(window).width();
//            if (winWidth < 768) {
//                console.log('Window Width: ' + winWidth + 'class used: col-xs');
//            } else if (winWidth <= 991) {
//                console.log('Window Width: ' + winWidth + 'class used: col-sm');
//            } else if (winWidth <= 1199) {
//                console.log('Window Width: ' + winWidth + 'class used: col-md');
//            } else {
//                console.log('Window Width: ' + winWidth + 'class used: col-lg');
//            }
//        });
//    });

    var deshabilitarClick = 0;
    function unsoloclick(clase = false) {
        deshabilitarClick++;
        console.log("deshabilitarClick " + deshabilitarClick);
        if (deshabilitarClick === 1) {
//        if (!$(clase).prop('disabled')) {
            if (clase) {
                $(clase).prop('disabled', true);
            }
            setTimeout(function () {
                console.log('quitamos lo deshabilitado js');
                if (clase) {
                    $(clase).removeAttr('disabled');
                }
                deshabilitarClick = 0;
            }, 1000);
            return true;
        } else {
            console.log("Se controla un clic innecesario");
            return false;
        }
    }

<?php
if (isset($error)) {
    if (is_array($error)) {
        foreach ($error as $err) {
            echo "mostrarError('" . htmlspecialchars(addslashes($err)) . "');";
        }
    } else {
        echo "mostrarError('" . $error . "');";
    }
}

if (isset($exito)) {
    if (is_array($exito)) {
        foreach ($exito as $xt) {
            echo "mostrarExito('" . htmlspecialchars(addslashes($xt)) . "');";
        }
    } else {
        echo "mostrarExito('" . $exito . "');";
    }
}
?>
</script>

