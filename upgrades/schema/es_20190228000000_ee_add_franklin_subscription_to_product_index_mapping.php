<?php

if (!file_exists(__DIR__.'/../../app/AppKernel.php')) {
    echo 'Please run this command from your Symfony application root.', PHP_EOL;
    exit(1);
}

require __DIR__.'/../../vendor/autoload.php';
require __DIR__.'/../../app/AppKernel.php';

$envFile = __DIR__.'/../.env';
if (file_exists($envFile)) {
    (new Symfony\Component\Dotenv\Dotenv())->load($envFile);
}

$kernel = new AppKernel('prod', false);
$kernel->boot();
$container = $kernel->getContainer();

/** @var \Elasticsearch\ClientBuilder */
$builder = $container->get('akeneo_elasticsearch.client_builder');
$hosts = [$container->getParameter('index_hosts')];

$client = $builder->setHosts($hosts)->build();

$index = $container->getParameter('product_and_product_model_index_name');

echo "Adding 'franklin_subscription' to index '{$index}'...", PHP_EOL;

$client->indices()->putMapping([
    'index' => $index,
    'type' => 'pim_catalog_product',
    'body' => [
        'pim_catalog_product' => [
            'properties' => [
                'franklin_subscription' => [
                    'type' => 'boolean'
                ]
            ]
        ]
    ]
]);

echo 'Done.', PHP_EOL;
