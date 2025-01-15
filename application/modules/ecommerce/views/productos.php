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

                    <?= filtroBusqueda("ecommerce/producto/productos", array("texto_busqueda" => $texto_busqueda, "estado_id" => $estado_id, "regpp" => $regpp)); ?>

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
                                            <th scope="col">Descripci&oacute;n</th>
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
                                                <td class="text-left"><?= $producto->titulo ?></td>
                                                <td class="text-left"><?= $producto->descripcion ?></td>
                                                <td class="text-justify"><?= $producto->sku_prefijo ?></td>
                                              <!--   <td><?= ($producto->estado === ESTADO_ACTIVO ? 'Activo' : 'Inactivo') ?></td> -->
                                                <td class="text-left">
                                                    <?= mostrarEstilos($producto->estado); ?>
                                                </td>

                                                <td>
                                                    <?php
                                                    if ($producto->estado == 'A') {
                                                        ?>
                                                        <button type = "submit" class="btn btn-accion btn-tool" data-id="<?= $producto->id ?>" value="eliminar"><i class="far fa-trash-alt"></i></button>
                                                        <?php
                                                    }
                                                    ?>
                                                    <button type = "submit" class="btn btn-accion btn-tool" data-id="<?= $producto->id ?>" value="editar"><i class="fas fa-pencil-alt"></i></button>   
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
        llamadaAjax(false, '<?= base_url() ?>ecommerce/producto/producto_obtener', {"id": producto_actual}, mostrarEdicion);
    }
    var max_ingredientes = <?= (!$ingredientes_receta ? 0 : count($ingredientes_receta)); ?>;
    var i = 0;


    $(document).ready(function () {

        /*************** ACCIONES PANTALLA PRINCIPAL *********************/
        $(".btn-accion").on('click', function () {
            unsoloclick('.btn-accion');
            if ($(this).val() === "eliminar") {
                /**/
                console.log('Boton de eliminar');
                swal_modal('¿Est&aacute; seguro de eliminar el registro?',
                        'Si',
                        'No',
                        '<?= base_url() ?>ecommerce/producto/producto_eliminar',
                        {"id": $(this).data('id')},
                        mostrarEliminacion);
                /**/
                // llamadaAjax(false, '<?= base_url() ?>ecommerce/producto/producto_eliminar', {"id": $(this).data('id')}, mostrarEliminacion);
            } else if ($(this).val() === "editar") {
                producto_actual = $(this).data('id');
                loadProducto();
            } else if ($(this).val() === "agregar") {
                llamadaAjax(false, '<?= base_url() ?>ecommerce/producto/producto_nuevo', false, mostrarEdicion);
            }
        });

        $(document).on('click', '#add', function () {
            console.log("click en add i es " + i);

            // solo se podra agregar segun la cantidad de fincas
            if (i < max_ingredientes) {
                i++;
                //inahilitar la opcion de elegir algoritmo
                //$('#sku_algoritmo_id').prop('disabled', 'disabled');

                $('#dynamic_field').append('<tr id="row' + i + '" class="row_algoritmo_det"><td><select  name="ingrediente_id[]"  class="form-control "><?php
                            if ($ingredientes_receta) {
                                foreach ($ingredientes_receta as $key => $ingrediente) {
                                    ?><option value="<?php echo $ingrediente->id ?>"><?php echo $ingrediente->nombre; ?> - <?php echo $ingrediente->descripcion; ?> - <?php echo $ingrediente->tipo; ?> - <?php echo $ingrediente->longitud; ?> cm </option><?php
                                }
                            }
                            ?></select> </td> <td><input type="number" name="cantidad_stems[]" placeholder="cantidad de Stems" class="form-control name_list" /></td> <td><button type="button" name="remove" id="' + i + '" class="btn btn-danger btn_remove">X</button></td></tr>');

            }else {
                alert('Maximo número de ingredientes alcanzado');
            }
        });
        $(document).on('click', '.btn_remove', function () {
            i--;
            var button_id = $(this).attr("id");
            $('#row' + button_id + '').remove();
            console.log(i);
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