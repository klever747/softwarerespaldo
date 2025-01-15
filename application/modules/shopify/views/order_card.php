<div class="card card-primary collapsed-card">
    <div class="card-header">
        <h3 class="card-title"><?= $order_number ?></h3>
        <div class="row">
            <div class="col-6">
                <small>
                    <b>US$:</b> <?= $total_price ?><br/>
                    <b>#items:</b> <?= sizeof($items) ?><br/>                        
                </small>
            </div>
            <div class="col-6 text-left">
                <small>
                    <b>Customer:</b> <?= $customer_last_name . " " . $customer_first_name ?><br/>
                    <b>Shipping to:</b> <?= $shipping_province . " " . $shipping_city ?><br/>                        
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
                <dd class="col-sm-12">
                    <?php
                    foreach ($items as $item) {
                        ?>
                        <dl class="row">
                            <dt class="col-3"><?= $item->title ?></dt>
                            <dd class="offset-1"><?= $item->sku ?></dd>
                            <dd class="offset-1">$<?= $item->quantity ?></dd>
                            <dd class="offset-1">$<?= $item->price ?></dd>
                        </dl>
                        <?php
                    }
                    ?>
                </dd>
            </dl>
        </small>
    </div>
</div>

