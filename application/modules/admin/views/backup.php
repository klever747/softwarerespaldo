<div class="wrapper">
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Backup</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">                            
                            <li class="breadcrumb-item"><a href="#">Administrador</a></li>
                            <li class="breadcrumb-item active">Backup</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>
        <section class="content">

            <div class="container-fluid">
                <div class="card card-default" id="soloLectura">
                    <div class="card-header">
                        <div class="container">
                            <div class="row" style="padding:10px;">
                                <!--<form id="f1" action="#fileUpload" method="post" enctype="multipart/form-data">	-->
                                <?= form_open_multipart(base_url() . "admin/backup/subirArchivo", array("id" => "formulario1", 'class' => "form-inline")) ?>                     
                                <div class="col">                                    
                                    <input class="form-control" type="file" id="input" name="input" multiple /> 
                                </div>
                                <div class="col">
                                    <button type="submit" class="btn btn-primary" onclick='activarIniciarScript();'>Subir Archivo</button>
                                </div>                    
                                <?= form_close() ?>                             
                                <!--</form>-->
                                <?= form_open(base_url() . "admin/backup/ejecutarScript", array("id" => "formulario2", 'class' => "form-inline", "disabled" => "disabled")) ?>
                                <div class="col">
                                    <div class="input-group">                                        
                                        <select class="custom-select" id="option_script" name="option_script" disabled='disabled'>
                                            <option selected>Seleccionar...</option>
                                            <option value="1">[Productos] sp_update_products_variants</option>
                                            <option value="2">[Variantes] sp_update_products_variants</option>
                                            <option value="3">[Todo] sp_update_products_variants</option>
                                            <option value="4">[Ingredientes] sp_update_ingredients</option>                                            
                                        </select>
                                    </div>
                                </div>
                                <div class="col">
                                    <?= (isset($file_input)) ? form_hidden('file_input', $file_input) : '' ?> 
                                    <button id="button-script" type="submit" class="btn btn-primary" disabled='disabled'>Inicar Script</button>
                                </div>
                                <?= form_close() ?> 
                            </div>       
                        </div>  
                    </div>

                    <?php
                    if ($parseo) {
                        //var_dump($data);
                        ?>
                        <div class="card-body">     
                            <div class="table-responsive">                        
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr scope="row">
                                            <th scope="col">No</th>
                                            <th scope="col">DATA</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $i = 0;
                                        foreach ($data as $e) {
                                            echo "<tr scope='row'>";
                                            echo " <td scope='col'>" . $i . "</td>";
                                            echo " <td scope='col'>" . $e . "</td>";
                                            echo "</tr>";
                                            $i++;
                                        }
                                        ?>
                                    </tbody>    
                                </table>                           
                            </div>                   
                        </div> 
                        <?php
                    }
                    ?>
                </div><!-- /.container-fluid -->   
        </section>
    </div>
</div>
<!-- /.content-wrapper -->


<script>
    $(function () {
        activarIniciarScript();
    });

    function activarIniciarScript() {
        var _input = $("input[name='file_input']").val();
        console.log(_input);
        if (_input != 'error') {
            $('#formulario2').removeAttr('disabled');
            $('#option_script').removeAttr('disabled');
            $('#button-script').removeAttr('disabled');
        }
    }
</script>