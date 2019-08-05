<?php

if (!file_exists(__DIR__ . '/../../app/AppKernel.php')) {
    die("Please run this command from your Symfony application root.");
}

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../app/AppKernel.php';

$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    (new Symfony\Component\Dotenv\Dotenv())->load($envFile);
}

$kernel = new AppKernel('prod', false);
$kernel->boot();
$container = $kernel->getContainer();

$builder = $container->get('akeneo_elasticsearch.client_builder');
$hosts = [$container->getParameter('index_hosts')];

$client = $builder->setHosts($hosts)->build()->indices();

echo 'Deleting alias akeneo_pim_product and index.';
if ($client->existsAlias(['name' => 'akeneo_pim_product' ])) {
    $productAlias = $client->getAlias(['name' => 'akeneo_pim_product' ]);
    $productIndex = array_keys($productAlias)[0];
    $client->deleteAlias([ 'index' => $productIndex, 'name' => 'akeneo_pim_product' ]);
    return $client->delete(['index' => $productIndex]);
}

echo 'Deleting alias akeneo_pim_product_model and index.';
if ($client->existsAlias(['name' => 'akeneo_pim_product_model' ])) {
    $productModelAlias = $client->getAlias(['name' => 'akeneo_pim_product_model' ]);
    $productModelIndex = array_keys($productModelAlias)[0];
    $client->deleteAlias([ 'index' => $productModelIndex, 'name' => 'akeneo_pim_product_model' ]);
    return $client->delete(['index' => $productModelIndex]);
}

echo "Done.\n";
