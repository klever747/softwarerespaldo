<div class="wrapper">
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Producto</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Ecommerce</a></li>
                            <li class="breadcrumb-item active">Productos</li>
                        </ol>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>
        <section class="content">
            <div class="container-fluid">
                <div class="card card-default">
                    <div class="card-header">
                        <h3 class="card-title">Productos</h3>
                    </div>

                    <?= filtroBusqueda("produccion/SkuAlgoritmo/productos", array("texto_busqueda" => $texto_busqueda, "estado_id" => $estado_id, "regpp" => $regpp)); ?>

                    <div class="card-body text-center">
                        <?= isset($itemsPaginacion) ? ($itemsPaginacion) : ''; ?>
                    </div>
                    <div class="card-body centrado">
                        <div class="row">
                            <?php
                            if ($productos) {
                                ?>
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">Titulo</th>
                                            <th scope="col">Descripción</th>
                                            <th scope="col">SKU</th>
                                            <th scope="col">Estado</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        foreach ($productos as $k => $producto) {
                                            ?>
                                            <tr>
                                                <th scope="row"><?= $k + 1 ?></th>
                                                <td class="text-left"><?= $producto->titulo ?>
                                                    <br>

                                                    <?php
                                                    // foreach ($variantes as $variante) {
                                                    //  echo'&nbsp;  &nbsp;  &nbsp;';
                                                    //  echo $producto->variantetitulo.'</br>';
                                                    // }
                                                    ?>
                                                </td>
                                                <td class="text-left"><?= $producto->descripcion ?></td>
                                                <td class="text-justify"><?= $producto->sku_prefijo ?></td>
                                              <!--   <td><?= ($producto->estado === ESTADO_ACTIVO ? 'Activo' : 'Inactivo') ?></td> -->
                                                <td class="text-left">
                                                    <?= mostrarEstilos($producto->estado); ?>
                                                </td>

                                                <td>
                                                    <button type = "submit" class="btn btn-accion btn-tool" data-id="<?= $producto->id ?>" value="editar"><i class="fas fa-eye"></i></button>
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

<button type="button" class="float btn-accion" data-id="" value="agregar">
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
        console.log("recargarPrincipal, productos");
        $("#btn_buscar").trigger("click");
    }

    function mostrarEdicion(r) {
        console.log("mostrarEdicion en productos.php linea 132");
        if (r.error) {
            mostrarError("No existe informaci&oacute;n disponible en estos momentos");
        } else {
            $("#modalEdicion .modal-content").html(r.respuesta);
            $("#modalEdicion").modal("show");
        }
    }

    function mostrarEliminacion(r) {
        if (r.error) {
            mostrarError(r.respuesta);
        } else {
            mostrarExito(r.respuesta);
            recargarPrincipal();
        }
    }

    function loadProducto() {
        llamadaAjax(false, '<?= base_url() ?>ecommerce/producto/producto_obtener', {"id": producto_actual, "opcion": 'sku_algoritmo'}, mostrarEdicion);
    }
    var max_fincas = <?php echo count($fincas); ?>;
    var i = 0;

    function calcularValori(valori) {
        i = valori;
        console.log("Existe algoritmo i es " + i);
        console.log("Cantidad es " + max_fincas);
    }


    $(document).ready(function () {
        //cantidad de la fincas

        $(document).on('change', '#sku_algoritmo_id', function () {
            console.log("aqui");
            $('.row_algoritmo_det').each(function () {
                $(this).remove();
            });
            i = 0;
        });
        console.log("Cantidad es " + max_fincas);

        $(document).on('click', '#add', function () {
            console.log("click en add i es " + i);
            // solo se podra agregar segun la cantidad de fincas
            if (i < max_fincas) {
                i++;
                //inahilitar la opcion de elegir algoritmo
                //$('#sku_algoritmo_id').prop('disabled', 'disabled');
                var estado = $("#sku_algoritmo_id").val();
                if (estado == 'porcentaje') {
                    $('#dynamic_field').append('<tr id="row' + i + '" class="row_algoritmo_det"><td><select  name="finca_id[]"  class="form-control "><?php foreach ($fincas as $key => $finca) { ?><option value="<?php echo $key ?>"><?php echo $finca; ?></option><?php } ?></select> </td><td><input type="number" name="porcentaje[]" placeholder="porcentaje %" class="form-control name_list soloNumeros" /></td> <td><input type="number" name="diario[]" placeholder="cupo" class="form-control name_list soloNumeros" /></td> <td><button type="button" name="remove" id="' + i + '" class="btn btn-danger btn_remove">X</button></td></tr>');
                }
                if (estado == 'diario') {
                    $('#dynamic_field').append('<tr id="row' + i + '" class="row_algoritmo_det"><td><select  name="finca_id[]"  class="form-control "><?php foreach ($fincas as $key => $finca) { ?><option value="<?php echo $key ?>"><?php echo $finca; ?></option><?php } ?></select>  </td><td><input type="number" name="diario[]" placeholder="cupo" class="form-control name_list soloNumeros" /></td>  <td><button type="button" name="remove" id="' + i + '" class="btn btn-danger btn_remove">X</button></td></tr>');
                }
                if (estado == 'semanal') {
                    $('#dynamic_field').append('<tr id="row' + i + '" class="row_algoritmo_det"><td><select  name="finca_id[]"  class="form-control "><?php foreach ($fincas as $key => $finca) { ?><option value="<?php echo $key ?>"><?php echo $finca; ?></option><?php } ?></select>  </td><td><input type="number" name="semanal[]" placeholder="semanal" class="form-control name_list soloNumeros" /></td> <td><input type="number" name="diario[]" placeholder="diario" class="form-control name_list soloNumeros" /></td>  <td><button type="button" name="remove" id="' + i + '" class="btn btn-danger btn_remove">X</button></td></tr>');
                }
            } else {
                alert('Maximo número de fincas alcanzado');
            }
        });
        $(document).on('click', '.btn_remove', function () {
            i--;
            var button_id = $(this).attr("id");
            $('#row' + button_id + '').remove();
        });

        /*************** ACCIONES PANTALLA PRINCIPAL *********************/
        $(".btn-accion").on('click', function () {
            unsoloclick('.btn-accion');
            if ($(this).val() === "editar") {
                producto_actual = $(this).data('id');
                loadProducto();
            } else if ($(this).val() === "agregar") {
                llamadaAjax(false, '<?= base_url() ?>ecommerce/producto/producto_nuevo', false, mostrarEdicion);
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