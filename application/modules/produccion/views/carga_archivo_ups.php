<div class="wrapper">
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Listado de Cajas</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Inicio</a></li>
                            <li class="breadcrumb-item">Ecommerce</li>
                            <li class="breadcrumb-item">Cajas</li>
                        </ol>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>
        <section class="content">
            <div class="container-fluid">
                <div class="card card-default" id="soloLectura">
                    <div class="card-header">                    
                        <div class="row" style="padding:10px;">

                            <?= form_open_multipart(base_url() . "produccion/logistica/subirArchivoUps", array("id" => "formulario", 'class' => "form-inline")) ?>                    
                            <div class="col">
                                <label>EXCEL</label>
                                <input class="form-control" id="file_ups" name="file_ups" type="file" required accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" />  
                            </div>
                            <div class="col">
                                <label>PDFS</label>
                                <input class="form-control" type="file" name="file_tracking[]"  accept="application/pdf" multiple="">
                            </div> 
                            <div class="col">
                                <input  class="btn btn-primary" value="Subir" type="submit">
                            </div>
                            <?= form_close() ?>
                        </div> 
                    </div>
                </div>
            </div>

            <div class="card-body text-center">                     
                <?php
                if ($parseo) {
                    ?>
                    <div class="row col-12 text-center">
                        <b>Resultado de la carga del archivo:</b>
                    </div>
                    <table class='table table-striped'>
                        <thead>
                            <tr>
                                <th>Store</th>
                                <th>Referencia</th>
                                <th>Orden</th>
                                <th>Caja</th>
                                <th>Tracking Number</th>
                                <th>Tracking pdf</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 0;
                            foreach ($parseo as $elt) {
                                echo "<tr>";
                                foreach ($elt as $k => $c) {
                                    echo "<td>" . ($k == 5 ? '<a href="' . base_url() . 'uploads/tracking/' . $c . '.pdf" target ="_blank"><i class="fas fa-file-pdf"></i></a>' : $c ) . "</td>";
                                }
                                echo "</tr>";
                                $i++;
                            }
                            ?>
                        </tbody>
                    </table>
                    <?php
                } else if ($error_parseo) {
                    print_r($error_parseo);
                }
                ?>
            </div>
    </div>
</section>
</div>
</div>