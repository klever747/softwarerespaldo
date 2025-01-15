<!doctype html>
<html>
<head>
<!-- Basic Page Needs
    ================================================== -->
<meta charset="utf-8">
<!--[if IE]><meta http-equiv="x-ua-compatible" content="IE=9" /><![endif]-->
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="shortcut icon" href="<?= base_url() ?>assets/app_especifico/img/favicon.png" type="image/png">

<title><?= $this->config->item('app_name_mini'); ?></title>
<meta name="description" content="">
<meta name="author" content="">

<?= $header ?>
</head>


<body id="page-top" class="<?= isset($claseBody) ? $claseBody : "" ?>">
    
    <?= isset($menu)? $menu:'' ?>
<!-- banner Page
    ==========================================-->
<?= $body ?>
<section id="footer">
<?= $footer ?>
</section>
<?= $includes_js ?>
</body>
</html>