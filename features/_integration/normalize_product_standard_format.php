<?php

//ini_set('xdebug.var_display_max_depth', 100);

/**
 * This script simply dumps the standard format of the products created in the
 * background of the feature {@see features/_integration/standard_format.feature}.
 *
 * It uses Symfony's VarDumper.
 */

$loader = require_once __DIR__ . '/../../app/bootstrap.php.cache';

require_once __DIR__ . '/../../app/AppKernel.php';

$kernel = new AppKernel('behat', true);
$kernel->loadClassCache();
$kernel->boot();

$container = $kernel->getContainer();

$serializer = $container->get('pim_serializer');
$repo = $container->get('pim_catalog.repository.product');

$products = [
    'bar' => $repo->findOneByIdentifier('bar'),
    'baz' => $repo->findOneByIdentifier('baz'),
    'foo' => $repo->findOneByIdentifier('foo')
];

foreach ($products as $sku => $product) {
    echo "\nPRODUCT $sku:\n";
    $standardFormat = $serializer->normalize($product, 'standard');
    dump($standardFormat);
}
