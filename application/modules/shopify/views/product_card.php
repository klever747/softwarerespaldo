<div class="card card-primary collapsed-card">
    <div class="card-header">
        <h3 class="card-title"><?= $title ?></h3>

        <div class="row card-title">
            <div class="col-2">
                <?php
                if (!isset($image)) {
                    echo '<i class="far fa-image"></i>';
                } else {
                    echo '<img class="img-fluid pad" src="' . $image->src . '" alt="--" loading="lazy" style="width:90%;height:auto" />';
                }
                ?>
            </div>
            <div class="col-10">
                <small>
                    <b>Template:</b> <?= $template_suffix ?><br/>
                    <b>Updated at:</b> <?= $updated_at ?><br/>                        
                </small>
            </div>
        </div>

        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-plus"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <small>
            <dl class="row">
                <dt class="col-sm-2">
                    <b>Tags:</b> 
                </dt>
                <dd class="col-sm-10">
                    <small><?= $tags ?></small>
                </dd>            
            </dl>        
            <dl class="row">
                <dt class="col-sm-2">
                    <b>Variants</b>                     
                </dt>
                <dd class="col-sm-10">
                    <?php
                    foreach ($variants as $v) {
//            echo "<p>" . print_r($v, true) . "</p>";
//            echo "<p>" . $v->title . "<br/>";
//            echo "" . $v->sku . "</br>";
//            echo "" . $v->price . "</br>";
//            echo "" . $v->option1 . " " . $v->option2 . " " . $v->option3 . "</p>";
                        ?>


                        <dl class="row">
                            <dt class="col-sm-5"><?= $v->title ?></dt>
                            <dd class="col-sm-5"><?= $v->sku ?></dd>
                            <dd class="col-sm-2">$<?= $v->price ?></dd>
                        </dl>
                        <?php
                    }
                    ?>
                </dd>
            </dl>
        </small>
    </div>
</div>

