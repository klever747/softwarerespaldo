<nav class="main-header navbar navbar-expand navbar-white navbar-light collapse-navbar-collapse">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
    </ul>


    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
        <li class="nav-item">
            <a href="<?= base_url() ?>inicio/cerrar" class="nav-link">
                <i class="fas fa-th-large">Cerrar session</i>
            </a>
        </li>
    </ul>
</nav>

<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="<?= base_url() ?>" class="brand-link">
        <img src="<?= base_url() . "assets/app_especifico/" ?>img/avatar_default.jpg" alt="logo_principal" class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light"><?= $session_userName ?></span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <?php foreach ($session_userPerfil as $perfil) { ?>
                    <a href="#" class="d-block">
                        <?php echo $perfil->name; ?></a>
                <?php  } ?>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <?php foreach ($menu as $cabecera_id => $cabecera) {
                ?>
                    <li class="nav-item has-treeview">
                        <a href="<?= ($cabecera['url'] != null ? (strpos($cabecera['url'], "http") === false ? base_url() : "") . $cabecera['url'] : '#') ?>" class="nav-link">
                            <i class="nav-icon <?= $cabecera['icono'] ?>"></i>
                            <p><?= $cabecera['nombre'] ?>
                                <?php if (sizeof($cabecera['opciones']) > 0) { ?>
                                    <i class="right fas fa-angle-left"></i>
                                <?php } ?>
                            </p>
                        </a>
                        <?php
                        if (sizeof($cabecera['opciones']) > 0) {
                            echo '<ul class="nav nav-treeview">';
                            foreach ($cabecera['opciones'] as $op) {
                        ?>
                    <li class="nav-item">
                        <a <?= (strpos($op['url'], "http") === false ? 'href="' . base_url() . $op['url'] . '"' : 'href="' . $op['url'] . '" target="_blank"') ?> class="nav-link">
                            <i class="nav-icon <?= $op['icono'] ?>"></i>
                            <p><?= $op['nombre'] ?></p>
                        </a>
                    </li>
            <?php
                            }
                            echo '</ul>';
                        }
            ?>
            </li>
        <?php } ?>
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>