<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$odoo = app(App\Services\OdooService::class);
try {
    $res = $odoo->searchRead('product.product', [['id', '>', 0]], ['id', 'image_128', 'image_1920'], ['limit' => 1]);
    print_r(array_keys($res[0]));
    
    // Check if image_64 exists on the second run
    $res2 = $odoo->searchRead('product.product', [['id', '>', 0]], ['id', 'image_64'], ['limit' => 1]);
    print_r(array_keys($res2[0]));
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
