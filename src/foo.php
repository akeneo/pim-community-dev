<?php

use Pim\Component\Catalog\Query\Filter\Operators;

require __DIR__.'/../vendor/autoload.php';
$kernel = new AppKernel('test', false);
$kernel->boot();

$container = $kernel->getContainer();

$productQueryBuilder = $container->get('pim_catalog.query.product_query_builder_from_size_factory')->create([
    'limit' => 200
]);
$products = $productQueryBuilder
    ->addFilter('release', Operators::IS_EMPTY, [])
    ->execute();

var_dump('Products to iterate over: ' . $products->count());

$i = 0;
foreach ($products as $product) {
    $i++;
    var_dump($i);
    if (0 === $i % 100) {
        var_dump($product->getIdentifier());
        gc_collect_cycles();
        var_dump(sprintf('%s - %s', time(), memory_get_usage() / 1024));
    }
}


//$productModelRepository = $container->get('pim_catalog.repository.product_model');
//
//$rootProductModel = $productModelRepository->findOneByIdentifier('root_product_model');
//
//echo "Child of root_product_model:\n";
//foreach ($rootProductModel->getProductModels() as $productModel) {
//    echo $productModel->getCode() . "\n";
//
//    foreach ($productModel->getProducts() as $product) {
//        echo "-- " . $product->getIdentifier() . "\n";
//    }
//}
//
//echo "\n\n";
//
//$subProductModel = $productModelRepository->findOneByIdentifier('sub_product_model_1');
//
//echo "Child of sub_product_model_1:\n";
//foreach ($subProductModel->getProducts() as $product) {
//    echo $product->getIdentifier() . "\n";
//}
