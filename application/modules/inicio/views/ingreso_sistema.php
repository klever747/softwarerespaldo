<div class="register-box">
    <div class="register-logo">
        <img src="<?= base_url() . "assets/" ?>app_especifico/img/logo_main.png" width="240px" height="auto" />
    </div>
</div>
<div class="login-box">       
    <div class="card">
        <div class="card-body login-card-body">
            <!--<p class="login-box-msg">Ingrese su correo</p>-->

            <form action="<?= base_url() ?>inicio/ingresoSistema" method="post">
                <div class="input-group mb-3">
                    <input type="text" name="usuario" class="form-control" placeholder="Usuario">
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-envelope"></span>
                        </div>
                    </div>
                </div>
                <div class="input-group mb-3">
                    <input type="password" name="password" id="password" class="form-control" placeholder="Password" autocomplete="off">
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">           
                        <button type="submit" class="btn btn-primary btn-block" onclick="return validarFormulario()">Iniciar Sesi&oacute;n</button>
                    </div>
                    <!-- /.col -->
                </div>
            </form>
            <br/>
            <?php if ($permtirAsociacion) { ?>                
                <div class="row">
                    <div class="col-12 pull-right">
                        <a href = "<?= base_url() ?>pulsera/nuevousuario" class = "text-center">Crear un usuario</a>                    
                    </div>
                </div>
            <?php } ?>
        </div>

        <!-- /.login-card-body -->
    </div>
</div>

<script>
    function validarLongitud() {
        if ($("#codigoPulsera").val().length = 10) {
            return true;
        }
        return false;
    }
    function validarFormulario() {
        if ($("#email").val() == '') {
            toastr.error("Formato de email no valido");
            return false;
        } else if ($("#password").val().length < 8) {
            toastr.error("Password muy corto");
            return false;
        } else {
            return true;
        }
    }
</script>
