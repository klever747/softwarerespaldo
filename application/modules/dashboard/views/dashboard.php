<div class="wrapper">
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Dashboard</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Inicio</a></li>
                            <li class="breadcrumb-item active">Dashboard</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>
        <section class="content">
            <div class="container-fluid">
                <div class="card card-default" id="soloLectura">
                      <?= filtroBusqueda("dashboard", array("rango_busqueda" => $rango_busqueda,"tipo_calendario" => $tipo_calendario, "uso_calendario" => 4,)); ?>
  <?php 
  $finca = $session_finca;
   $url = "produccion/empaque/ordenes";
  if(in_array(FINCA_ROSAHOLICS_ID, $arrayfinca)){
      $finca = 0;
      $url = "ecommerce/ordenes";
  }else{
      if(count($arrayfinca)){
         $finca = 0; 
      }
  }
  $fecha_actual = $fechaactual;
  ?>                    
                    <div class="card-body">
                        <!-- Small boxes (Stat box) -->
                        <div class="row">
                               <?PHP  if(in_array(FINCA_ROSAHOLICS_ID, $arrayfinca)){ ?>
                            <div class="col-lg-3 col-6">
                                <!-- small box -->
                              
                                <div class="small-box bg-info">
                                    <div class="inner">
                                        <h3><?= $ordenesActual ?></h3>
                                        <p>Ordenes del dia</p>
                                    </div>
                                    <div class="icon">
                                        <i class="ion ion-bag"></i>
                                    </div>
                                     <?= form_open(base_url() . $url ); ?>
                                    <input id="store_id" name="store_id" type="hidden" value="0">  
                                    <input id="orden_estado_id" name="orden_estado_id" type="hidden" value="T">                                  
                                    <input id="empacado" name="empacado" type="hidden" value="T">
                                    <input id="asignadoCaja" name="asignadoCaja" type="hidden" value="T">
                                    <input id="tipo_calendario" name="tipo_calendario" type="hidden" value="<?= $tipo_calendario?>">
                                    <input id="tarjeta_impresa" name="tarjeta_impresa" type="hidden" value="T">
                                    <input id="regpp" name="regpp" type="hidden" value="50">
                                    <input id="rango_busqueda" name="rango_busqueda" type="hidden" value="<?= $fecha_actual?>">
                                    <input id="finca_id" name="finca_id" type="hidden" value="<?= $finca ?>">
                                    <button type="submit" name="btn_buscar" id="btn_buscar" class="btn btn-info btn-block">Ir a detalles <i class="fas fa-arrow-circle-right"></i></button>                        
                                     <?= form_close(); ?>
                                </div>
                            </div>
                                 <?php } ?>
                             <div class="col-lg-3 col-6">
                                <!-- small box -->
                                <div class="small-box bg-gradient-lime">
                                    <div class="inner">
                                        <h3><?= $ordenesActiva .'/' . $ordenesActual?></h3>
                                        <p>Ordenes activas</p>
                                    </div>
                                    <div class="icon">
                                        <i class="ion ion-bag"></i>
                                    </div>
                                      <?= form_open(base_url() . $url ); ?>
                                    <input id="store_id" name="store_id" type="hidden" value="0">  
                                    <input id="tipo_calendario" name="tipo_calendario" type="hidden" value="<?= $tipo_calendario?>">
                                    <input id="orden_estado_id" name="orden_estado_id" type="hidden" value="A">                                                                    
                                     <input id="rango_busqueda" name="rango_busqueda" type="hidden" value="<?= $fecha_actual?>">
                                    <input id="finca_id" name="finca_id" type="hidden" value="<?= $finca ?>">
                                    <button type="submit" name="btn_buscar"  id="btn_buscar" class="btn btn-gradient-lime btn-block">Ir a detalles <i class="fas fa-arrow-circle-right"></i></button>                        
                                     <?= form_close(); ?>
                                </div>
                            </div>
                            <?PHP  if(in_array(FINCA_ROSAHOLICS_ID, $arrayfinca)){?>
                            <!-- ./col -->
                            <div class="col-lg-3 col-6">
                                <!-- small box -->
                                <div class="small-box bg-danger">
                                    <div class="inner">
                                        <h3><?=$ordenesError .'/' . $ordenesActual ?></h3>
                                        <p>Ordenes con error</p>
                                    </div>
                                    <div class="icon">
                                        <i class="ion ion-stats-bars"></i>
                                    </div>
                                     <?= form_open(base_url() . $url ); ?>
                                    
                                    <input id="orden_estado_id" name="orden_estado_id" type="hidden" value="E">                                  
                                    <input id="empacado" name="empacado" type="hidden" value="T">
                                    <input id="asignadoCaja" name="asignadoCaja" type="hidden" value="T">
                                    <input id="tipo_calendario" name="tipo_calendario" type="hidden" value="<?= $tipo_calendario?>">
                                    <input id="store_id" name="store_id" type="hidden" value="0">  
                                    <input id="tarjeta_impresa" name="tarjeta_impresa" type="hidden" value="T">
                                    <input id="regpp" name="regpp" type="hidden" value="50">
                                    <input id="rango_busqueda" name="rango_busqueda" type="hidden" value="<?= $fecha_actual?>">
                                    <input id="finca_id" name="finca_id" type="hidden" value="<?= $finca ?>">
                                    <button type="submit" name="btn_buscar" id="btn_buscar" class="btn btn-danger btn-block">Ir a detalles <i class="fas fa-arrow-circle-right"></i></button>                        
                                     <?= form_close(); ?>
                                </div>
                            </div>
                          <!-- ./col -->
                            <div class="col-lg-3 col-6">
                                <!-- small box -->
                                <div class="small-box bg-warning">
                                    <div class="inner">
                                       <h3><?=$ordenesCancelada .'/' . $ordenesActual ?></h3>
                                        <p>Ordenes Canceladas</p>
                                    </div>
                                    <div class="icon">
                                        <i class="ion ion-pie-graph"></i>
                                    </div>
                                     <?= form_open(base_url() . $url ); ?>
                                    <input id="store_id" name="store_id" type="hidden" value="0">  
                                    <input id="orden_estado_id" name="orden_estado_id" type="hidden" value="C">                                  
                                    <input id="empacado" name="empacado" type="hidden" value="T">
                                    <input id="asignadoCaja" name="asignadoCaja" type="hidden" value="T">
                                    <input id="tipo_calendario" name="tipo_calendario" type="hidden" value="<?= $tipo_calendario?>">
                                    <input id="tarjeta_impresa" name="tarjeta_impresa" type="hidden" value="T">
                                    <input id="regpp" name="regpp" type="hidden" value="50">
                                     <input id="rango_busqueda" name="rango_busqueda" type="hidden" value="<?= $fecha_actual?>">
                                    <input id="finca_id" name="finca_id" type="hidden" value="<?= $finca?>">
                                    <button type="submit" name="btn_buscar" id="btn_buscar" class="btn btn-warning btn-block">Ir a detalles <i class="fas fa-arrow-circle-right"></i></button>                        
                                     <?= form_close(); ?>
                                </div>
                            </div>

                            <!-- ./col -->
                            <div class="col-lg-3 col-6">
                                <!-- small box -->
                                <div class="small-box bg-gradient-indigo">
                                    <div class="inner">
                                         <h3><?= $ordenesreenviada .'/' . $ordenesActual ?></h3>
                                        <p>Reenviado</p>
                                    </div>
                                    <div class="icon">
                                        <i class="ion ion-pie-graph"></i>
                                    </div>
                                    <?= form_open(base_url() . $url ); ?>
                                    <input id="store_id" name="store_id" type="hidden" value="0">  
                                    <input id="orden_estado_id" name="orden_estado_id" type="hidden" value="A">                                  
                                    <input id="empacado" name="empacado" type="hidden" value="T">
                                    <input id="reenviado" name="reenviado" type="hidden" value="S">
                                    <input id="asignadoCaja" name="asignadoCaja" type="hidden" value="T">
                                    <input id="preparado" name="preparado" type="hidden" value="T">
                                    <input id="tarjeta_impresa" name="tarjeta_impresa" type="hidden" value="T">
                                    <input id="regpp" name="regpp" type="hidden" value="50">
                                    <input id="tipo_calendario" name="tipo_calendario" type="hidden" value="<?= $tipo_calendario?>">
                                     <input id="rango_busqueda" name="rango_busqueda" type="hidden" value="<?= $fecha_actual?>">
                                    <input id="finca_id" name="finca_id" type="hidden" value="<?= $finca ?>">
                                    <button type="submit" name="btn_buscar" id="btn_buscar" class="btn btn-indigo btn-block">Ir a detalles <i class="fas fa-arrow-circle-right"></i></button>                        
                                     <?= form_close(); ?>
                                </div>
                            </div>
                           <?php } ?>
                            <!-- ./col -->
                            <div class="col-lg-3 col-6">
                                <!-- small box -->
                                <div class="small-box bg-gradient-gray">
                                    <div class="inner">
                                         <h3><?= $totalcajas ?></h3>
                                        <p>Cajas</p>
                                    </div>
                                    <div class="icon">
                                        <i class="ion ion-pie-graph"></i>
                                    </div>
                                    <?= form_open(base_url() . $url ); ?>
                                    <input id="store_id" name="store_id" type="hidden" value="0">  
                                    <input id="orden_estado_id" name="orden_estado_id" type="hidden" value="A">                                  
                                    <input id="empacado" name="empacado" type="hidden" value="T">
                                    <input id="asignadoCaja" name="asignadoCaja" type="hidden" value="T">
                                    <input id="preparado" name="preparado" type="hidden" value="T">
                                    <input id="tarjeta_impresa" name="tarjeta_impresa" type="hidden" value="T">
                                    <input id="regpp" name="regpp" type="hidden" value="50">
                                    <input id="tipo_calendario" name="tipo_calendario" type="hidden" value="<?= $tipo_calendario?>">
                                     <input id="rango_busqueda" name="rango_busqueda" type="hidden" value="<?= $fecha_actual?>">
                                    <input id="finca_id" name="finca_id" type="hidden" value="<?= $finca ?>">
                                    <button type="submit" name="btn_buscar" id="btn_buscar" class="btn btn-gradient-gray btn-block">Ir a detalles <i class="fas fa-arrow-circle-right"></i></button>                        
                                     <?= form_close(); ?>
                                </div>
                            </div>
                            <!-- ./col -->
                              <!-- ./col -->
                              <?PHP  if(in_array(FINCA_ROSAHOLICS_ID, $arrayfinca)){ ?>
                            <div class="col-lg-3 col-6">
                                <!-- small box -->
                                <div class="small-box bg-success">
                                    <div class="inner">
                                         <h3><?= $ordensintraking .'/'. $totalcajas ?></h3>
                                        <p>Cajas sin Tracking</p>
                                    </div>
                                    <div class="icon">
                                        <i class="ion ion-person-add"></i>
                                    </div>
                                    <?= form_open(base_url() . $url ); ?>
                                    <input id="store_id" name="store_id" type="hidden" value="0">  
                                    <input id="orden_estado_id" name="orden_estado_id" type="hidden" value="A">                                  
                                    <input id="empacado" name="empacado" type="hidden" value="T">
                                    <input id="con_tracking_number" name="con_tracking_number" type="hidden" value="N">
                                    <input id="asignadoCaja" name="asignadoCaja" type="hidden" value="T">
                                    <input id="preparado" name="preparado" type="hidden" value="T">
                                    <input id="tarjeta_impresa" name="tarjeta_impresa" type="hidden" value="T">
                                    <input id="regpp" name="regpp" type="hidden" value="50">
                                    <input id="tipo_calendario" name="tipo_calendario" type="hidden" value="<?= $tipo_calendario?>">
                                     <input id="rango_busqueda" name="rango_busqueda" type="hidden" value="<?= $fecha_actual?>">
                                    <input id="finca_id" name="finca_id" type="hidden" value="<?= $finca ?>">
                                    <button type="submit" name="btn_buscar" id="btn_buscar" class="btn btn-indigo btn-block">Ir a detalles <i class="fas fa-arrow-circle-right"></i></button>                        
                                     <?= form_close(); ?>
                                </div>
                            </div>
                            
                         
                            <div class="col-lg-3 col-6">
                                <!-- small box -->
                                <div class="small-box bg-gradient-purple">
                                    <div class="inner">
                                         <h3><?= $totalcajasnodefinidas .'/'. $totalcajas ?></h3>

                                        <p>Cajas no determinadas</p>
                                    </div>
                                    <div class="icon">
                                        <i class="ion ion-pie-graph"></i>
                                    </div>
                                       <?= form_open(base_url() . $url ); ?>
                                    <input id="store_id" name="store_id" type="hidden" value="0">  
                                    <input id="orden_estado_id" name="orden_estado_id" type="hidden" value="A">                                  
                                    <input id="empacado" name="empacado" type="hidden" value="T">
                                    <input id="tipo_caja" name="tipo_caja" type="hidden" value="1">
                                    <input id="asignadoCaja" name="asignadoCaja" type="hidden" value="T">
                                    <input id="preparado" name="preparado" type="hidden" value="T">
                                    <input id="tarjeta_impresa" name="tarjeta_impresa" type="hidden" value="T">
                                    <input id="regpp" name="regpp" type="hidden" value="50">
                                    <input id="tipo_calendario" name="tipo_calendario" type="hidden" value="<?= $tipo_calendario?>">
                                     <input id="rango_busqueda" name="rango_busqueda" type="hidden" value="<?= $fecha_actual?>">
                                    <input id="finca_id" name="finca_id" type="hidden" value="<?= $finca ?>">
                                    <button type="submit" name="btn_buscar" id="btn_buscar" class="btn btn-gradient-gray btn-block">Ir a detalles <i class="fas fa-arrow-circle-right"></i></button>                        
                                     <?= form_close(); ?>
                                </div>
                            </div>
                           <!-- ./col -->
                           <!-- ./col -->
                           
                            <div class="col-lg-3 col-6">
                                <!-- small box -->
                                <div class="small-box bg-gradient-lightblue">
                                    <div class="inner">
                                         <h3><?= $fincanodefinidas .'/'. $totalcajas ?></h3>

                                        <p>Orden con finca no determinada</p>
                                    </div>
                                    <div class="icon">
                                        <i class="ion ion-pie-graph"></i>
                                    </div>
                                      <?= form_open(base_url() . $url ); ?>
                                    <input id="store_id" name="store_id" type="hidden" value="0">  
                                    <input id="orden_estado_id" name="orden_estado_id" type="hidden" value="A">                                  
                                    <input id="empacado" name="empacado" type="hidden" value="T">
                                    <input id="tipo_caja" name="tipo_caja" type="hidden" value="T">
                                    <input id="asignadoCaja" name="asignadoCaja" type="hidden" value="T">
                                    <input id="preparado" name="preparado" type="hidden" value="T">
                                    <input id="tarjeta_impresa" name="tarjeta_impresa" type="hidden" value="T">
                                    <input id="regpp" name="regpp" type="hidden" value="50">
                                    <input id="tipo_calendario" name="tipo_calendario" type="hidden" value="<?= $tipo_calendario?>">
                                    <input id="rango_busqueda" name="rango_busqueda" type="hidden" value="<?= $fecha_actual?>">
                                    <input id="finca_id" name="finca_id" type="hidden" value="1">
                                    <button type="submit" name="btn_buscar" id="btn_buscar" class="btn btn-gradient-lightblue btn-block">Ir a detalles <i class="fas fa-arrow-circle-right"></i></button>                        
                                     <?= form_close(); ?>
                                </div>
                            </div>
                           <?PHP } ?>
                           <!-- ./col -->
                            <div class="col-lg-3 col-6">
                                <!-- small box -->
                                <div class="small-box bg-blue">
                                    <div class="inner">
                                         <h3><?= $ordenbonchada .'/'. ($ordenbonchadaT + $ordenbonchada)?></h3>
                                        <p>Bonchadas</p>
                                    </div>
                                    <div class="icon">
                                        <i class="ion ion-pie-graph"></i>
                                    </div>
                             <?= form_open(base_url() . $url ); ?>
                                    <input id="store_id" name="store_id" type="hidden" value="0">  
                                    <input id="orden_estado_id" name="orden_estado_id" type="hidden" value="A">                                  
                                    <input id="empacado" name="empacado" type="hidden" value="T">
                                    <input id="tipo_caja" name="tipo_caja" type="hidden" value="T">
                                    <input id="asignadoCaja" name="asignadoCaja" type="hidden" value="T">
                                    <input id="tarjeta_impresa" name="tarjeta_impresa" type="hidden" value="T">
                                    <input id="regpp" name="regpp" type="hidden" value="50">
                                    <input id="preparado" name="preparado" type="hidden" value="S">
                                    <input id="tipo_calendario" name="tipo_calendario" type="hidden" value="<?= $tipo_calendario?>">
                                     <input id="rango_busqueda" name="rango_busqueda" type="hidden" value="<?= $fecha_actual?>">
                                    <input id="finca_id" name="finca_id" type="hidden" value="<?= $finca ?>">
    
                                    <button type="submit" name="btn_buscar" id="btn_buscar" class="btn btn-gradient-lightblue btn-block">Ir a detalles <i class="fas fa-arrow-circle-right"></i></button>                        
                                     <?= form_close(); ?>
                                </div>
                            </div>
                            <!-- ./col -->
                            <div class="col-lg-3 col-6">
                                <!-- small box -->
                                <div class="small-box bg-gradient-fuchsia">
                                    <div class="inner">
                                         <h3><?= $ordenvestida .'/'. ($ordenvestidaT + $ordenvestida)?></h3>
                                        <p>Vestidos</p>
                                    </div>
                                    <div class="icon">
                                        <i class="ion ion-pie-graph"></i>
                                    </div>
                          <?= form_open(base_url() . $url ); ?>
                                    <input id="store_id" name="store_id" type="hidden" value="0">  
                                    <input id="orden_estado_id" name="orden_estado_id" type="hidden" value="A">                                  
                                    <input id="empacado" name="empacado" type="hidden" value="T">
                                    <input id="tipo_caja" name="tipo_caja" type="hidden" value="T">
                                    <input id="asignadoCaja" name="asignadoCaja" type="hidden" value="T">
                                    <input id="terminado" name="terminado" type="hidden" value="S">
                                    <input id="tarjeta_impresa" name="tarjeta_impresa" type="hidden" value="T">
                                    <input id="regpp" name="regpp" type="hidden" value="50">
                                    <input id="tipo_calendario" name="tipo_calendario" type="hidden" value="<?= $tipo_calendario?>">
                                     <input id="rango_busqueda" name="rango_busqueda" type="hidden" value="<?= $fecha_actual?>">
                                    <input id="finca_id" name="finca_id" type="hidden" value="<?= $finca ?>">
    
                                    <button type="submit" name="btn_buscar" id="btn_buscar" class="btn btn-gradient-lightblue btn-block">Ir a detalles <i class="fas fa-arrow-circle-right"></i></button>                        
                                     <?= form_close(); ?>
                                </div>
                            </div>

                        </div>

                    </div>
                </div>
        </section>
    </div>
</div>
<script>
$(document).on('click', '.btn-block', function(){
    $('.btn-block').each(function(){
        $(this).html(loadingBtn);
    });
});
</script>
<!-- /.content-wrapper -->
