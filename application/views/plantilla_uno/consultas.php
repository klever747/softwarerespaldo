	
<section id="home-banner" style="background-color: #000000;">
    <div class="content">
        <div class="container"  data-wow-duration="1s"> <span class="wow fadeIn">N O R V I D A</span>
            <h1 class="wow fadeInUp">Al <span>cuidado</span> de tu vida. </h1>
        </div>
    </div>
    <div class="arrow bounce"> <i class="fa fa-arrow-down fa-2x"></i> </div>
</section>
<!--page body-->

<div id="page-body">
    <div class="container">
        <div class="row"> 
            <!--blog posts container-->
            <div class="col-md-offset-3 col-md-6 page-block">
                <h1>Consulta de Resultados</h1>
                <p>Por favor ingrese su n&uacute;mero de c&eacute;dula y el c&oacute;digo del ex&aacute;men que desea descargar</p>
                <form action="<?= base_url() ?>principal/devolverPdf" method="post" style="padding-bottom:80px">
                    <div class="form-group">
                        <label for="cedula">C&eacute;dula</label>
                        <input type="text" class="form-control" id="cedula" name="cedula" placeholder="c&eacute;dula">
                    </div>
                    <div class="form-group">
                        <label for="codigo">C&oacute;digo</label>
                        <input type="text" class="form-control" id="codigo" name="codigo" placeholder="C&oacute;digo">
                    </div>                
                    <button type="submit" class="btn btn-primary">Enviar</button>
                </form>

                <!--blog posts container-->

            </div>
        </div>
    </div>
</div>
<div class="clearfix"></div>