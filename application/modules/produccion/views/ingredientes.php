<div class="wrapper">
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Mantenimiento de Ingredientes</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Inicio</a></li>
                            <li class="breadcrumb-item">Producci&oacute;n</li>
                            <li class="breadcrumb-item">Mantenimiento de ingredientes</li>
                        </ol>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>
        <section class="content">
            <div class="container-fluid">
                <div class="card card-default" id="soloLectura">
                    <?php
                    $filtroActual = array("texto_busqueda" => $texto_busqueda,
                        "estado_id" => $estado_id,
                        "regpp" => $regpp);

                    echo filtroBusqueda($url_busqueda, $filtroActual);
                    ?>  
                    <div class="card-body text-center"> 
                        <?= isset($itemsPaginacion) ? ($itemsPaginacion) : ''; ?>
                    </div>                                    
                    <div class="card-body text-center">                        
                        <div class="row">
                            <div class="col-12 row mb-5">
                                <div class="col-12 row">
                                    <?php
                                    if ($ingredientes) {
                                        ?>
                                        <table class="table">                                            
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Nombre</th>
                                                    <th>Descripcion</th>
                                                    <th>Tipo</th>
                                                    <th>Longitud</th>
                                                    <th>Estado</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                foreach ($ingredientes as $k => $ingrediente) {
                                                    ?>
                                                    <tr>
                                                        <th scope="row"><?= $k + 1 ?></th>
                                                        <td class="text-left"><?= $ingrediente->nombre ?></td>
                                                        <td class="text-left"><?= $ingrediente->descripcion ?></td>
                                                        <td class="text-justify"><?= $ingrediente->tipo ?></td>
                                                        <td class="text-justify"><?= $ingrediente->longitud ?></td>
                                                        <!--  <td class="text-left"><?= $ingrediente->estado ?></td> -->
                                                        <td class="text-left">
                                                            <?= mostrarEstilos($ingrediente->estado); ?>
                                                        </td>
                                                        <td>
                                                            <button type = "button" class="btn btn-accion btn-tool" data-id="<?= $ingrediente->id ?>" value="editar"><i class="fas fa-pencil-alt"></i></button>
                                                            <?php
                                                            if ($ingrediente->estado == 'A') {
                                                                ?>
                                                                <button type = "button" class="btn btn-accion btn-tool" data-id="<?= $ingrediente->id ?>" value="eliminar"><i class="far fa-trash-alt"></i></button>
                                                                <?php
                                                            }
                                                            ?>
                                                        </td>
                                                    </tr>
                                                    <?php
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                    <div class="card-body text-center"> 
                        <?= isset($itemsPaginacion) ? ($itemsPaginacion) : ''; ?>
                    </div>
                </div>
        </section>
    </div>
    <!-- /.content-wrapper -->
</div>
<!-- Modal -->
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
        console.log("recargarPrincipal, ingredientes");
        $("#btn_buscar").trigger("click");
    }

    function mostrarEdicion(r) {
        console.log("mostrarEdicion en ingrediente.php linea 132");
        if (r.error) {
            mostrarError("No existe informaci&oacute;n disponible en estos momentos");
        } else {
            $("#modalEdicion .modal-content").html(r.respuesta);
            $("#modalEdicion").modal("show");
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
        llamadaAjax(false, '<?= base_url() ?>produccion/Ingrediente/ingrediente_obtener', {"id": ingrediente_actual}, mostrarEdicion);
    }



    $(document).ready(function () {

        /*************** ACCIONES PANTALLA PRINCIPAL *********************/
        $(".btn-accion").on('click', function () {
            unsoloclick('.btn-accion');
            if ($(this).val() === "eliminar") {
                llamadaAjax(false, '<?= base_url() ?>produccion/Ingrediente/ingrediente_eliminar', {"id": $(this).data('id')}, mostrarEliminacion);
            } else if ($(this).val() === "editar") {
                ingrediente_actual = $(this).data('id');
                loadTienda();
            } else if ($(this).val() === "agregar") {
                llamadaAjax(false, '<?= base_url() ?>produccion/Ingrediente/ingrediente_nuevo', false, mostrarEdicion);
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