<div class="wrapper">
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Precios por finca</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Produccion</a></li>
                            <li class="breadcrumb-item active">Precios por Finca</li>
                        </ol>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>
        <section class="content">
            <div class="container-fluid">
                <div class="card card-default">                    
                    <div class="card-header">
                        <h3 class="card-title">Precios Finca</h3>
                    </div>

                    <?= filtroBusqueda("produccion/PrecioFinca/precio_finca", array("texto_busqueda" => $texto_busqueda, "longitud_buscar" => $longitud_buscar, "finca_id" => $finca_id, "sel_finca" => $sel_finca, "regpp" => $regpp)); ?>

                    <div class="card-body text-center"> 
                        <?= isset($itemsPaginacion) ? ($itemsPaginacion) : ''; ?>
                    </div>
                    <div class="card-body centrado">
                        <div class="row">
                            <?php
                            if ($fincasPrecios) {
                                ?>
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">Nombre Finca</th>
                                            <th scope="col">Ingrediente</th>
                                            <th scope="col">cm</th>
                                            <th scope="col">Precio Unitario</th>
                                            <th scope="col">Fecha Inicio Vigencia </th>
                                            <th scope="col">Fecha Fin Vigencia</th>
                                            <th scope="col">Estado</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        foreach ($fincasPrecios as $k => $precios) {
                                            $fecha_ini = explode(" - ", $precios->fecha_inicio_vigencia);
                                            $fecha_inicio = date_format(DateTime::createFromFormat(FORMATO_FECHA, convertirFechaBD($fecha_ini[0])), 'Y-m-d');
                                            $fecha_fin = explode(" - ", $precios->fecha_fin_vigencia);
                                            $fecha_fin_vig = date_format(DateTime::createFromFormat(FORMATO_FECHA, convertirFechaBD($fecha_fin[0])), 'Y-m-d');
                                            ?>
                                            <tr>
                                                <th scope="row"><?= $k + 1 ?></th>
                                                <td class="text-left"><?= $precios->nombre_finca ?></td>
                                                <td class="text-left"><?= $precios->nombre_ingrediente ?></td>
                                                <td class="text-left"><?= $precios->longitud ?></td>
                                                <td class="text-justify"><?= $precios->precio_unitario ?></td>
                                                <td class="text-justify"><?= $fecha_inicio ?></td>
                                                <td class="text-justify"><?= $fecha_fin_vig ?></td>
                                                <!--   <td><?= ($precios->estado === ESTADO_ACTIVO ? 'Activo' : 'Inactivo') ?></td> -->
                                                <td class="text-left">
                                                    <?= mostrarEstilos($precios->estado); ?>
                                                </td>

                                                <td>
                                                    <button type = "submit" class="btn btn-accion btn-tool" data-id="<?= $precios->id ?>" value="eliminar"><i class="far fa-trash-alt"></i></button>
                                                    <button type = "submit" class="btn btn-accion btn-tool" data-id="<?= $precios->id ?>" value="editar"><i class="fas fa-pencil-alt"></i></button>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                        ?>

                                    </tbody>
                                </table>
                                <?php
                            }
                            ?>
                        </div>
                        <div class="row">
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<button type="button" class="float btn-accion" id= "agregar_precio" data-id="" value="agregar">
    <i class="fa fa-plus my-float"></i>
</button>

<div class="modal" id="modalEdicion" tabindex="-1" role="dialog" aria-labelledby="wsanchez" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content">

        </div>
    </div>
</div>

<script>

    var producto_actual = 0;
    function recargarPrincipal() {
        console.log("recargarPrincipal, Precio Finca");
        $("#btn_buscar").trigger("click");
    }
    function mostrarEdicion(r) {
        console.log("mostrarEdicion en tiendas.php linea 132");
        if (r.error) {
            mostrarError("No existe informaci&oacute;n disponible en estos momentos");
        } else {
            $("#modalEdicion .modal-content").html(r.respuesta);
            $("#modalEdicion").modal("show");
            if (r.edicion) {
                console.log('edit');
                llenarSelect("ingrediente_id", '<?= base_url() ?>produccion/precioFinca/ingredientes_select', {}, false);
            }
            var nowDate = new Date();
            var today = new Date(nowDate.getFullYear(), nowDate.getMonth(), nowDate.getDate(), 0, 0, 0, 0);
            $('.select_fecha').daterangepicker({
                singleDatePicker: true,
                locale: {
                    format: '<?= FORMATO_FECHA_DATEPICKER_JS ?>'
                },
                maxDate: '2050-12-13',
                autoApply: true
            });
        }
    }

    function mostrarEliminacion(r) {
        if (r.error) {
            mostrarError("Hubo un problema durante la eliminaci&oacute;n");
        } else {
            recargarPrincipal();
        }
    }

    function loadTienda() {
        llamadaAjax(false, '<?= base_url() ?>produccion/PrecioFinca/precio_finca_obtener', {"id": precio_finca_actual}, mostrarEdicion);
    }



    $(document).ready(function () {

        /*************** ACCIONES PANTALLA PRINCIPAL *********************/
        $(".btn-accion").on('click', function () {
            unsoloclick('.btn-accion');
            if ($(this).val() === "eliminar") {
                llamadaAjax(false, '<?= base_url() ?>produccion/PrecioFinca/precio_finca_eliminar', {"id": $(this).data('id')}, mostrarEliminacion);
            } else if ($(this).val() === "editar") {
                precio_finca_actual = $(this).data('id');
                loadTienda();
            } else if ($(this).val() === "agregar") {
                llamadaAjax(false, '<?= base_url() ?>produccion/PrecioFinca/precio_finca_nuevo', false, mostrarEdicion);
            }
        });

        $("#modalEdicion").on('shown.bs.modal', function () {
        });
        $("#modalEdicion").on('hide.bs.modal', function () {
            //recargarPrincipal();
        });
        /***************** ACCIONES MODAL DETALLE *******************/

        $("#texto_busqueda").on('keypress', function (e) {
            if (e.which === 13) {
                $("#btn_buscar").trigger("click");
            }
        });

    });
</script>