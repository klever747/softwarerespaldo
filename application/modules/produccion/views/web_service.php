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
                            <li class="breadcrumb-item"><a href="#">Inicio</a></li>
                            <li class="breadcrumb-item">Producci&oacute;n</li>
                            <li class="breadcrumb-item active">Web Services</li>
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
                                <!--<form id="f1">	-->
                                <?= form_open_multipart(base_url() . "produccion/webservice/subirArchivo", array("id" => "formulario1")) ?>                     
                                <div class="mb-3 row">
                                    <label for="staticEmail" class="col-sm-2 col-form-label">Email</label>
                                    <div class="col-sm-10">
                                        <input type="text" readonly class="form-control-plaintext" id="staticEmail" value="email@example.com">
                                    </div>
                                </div>
                                <div class="mb-3 row">
                                    <label for="inputPassword" class="col-sm-2 col-form-label">Password</label>
                                    <div class="col-sm-10">
                                        <input type="password" class="form-control" id="inputPassword">
                                    </div>
                                </div>
                                <div class="col">
                                    <button type="submit" class="btn btn-primary" onclick='activarIniciarScript();'>Subir Archivo</button>
                                </div>                    
                                <?= form_close() ?>                             
                                <!--</form>-->
                            </div>       
                        </div>  
                    </div>
                </div><!-- /.container-fluid -->   
        </section>
    </div>
</div>
<!-- /.content-wrapper -->


<script>

</script>