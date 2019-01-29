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

$client = $builder->setHosts($hosts)->build();

echo "Updating document_type for index {$container->getParameter('product_and_product_model_index_name')}...\n";

$client->updateByQuery(
    [
        'index' => $container->getParameter('product_and_product_model_index_name'),
        'type'  => 'pim_catalog_product',
        'body'  => [
            'script' => [
                'inline' => "ctx._source.document_type = 'Akeneo\\\\Pim\\\\Enrichment\\\\Component\\\\Product\\\\Model\\\\ProductInterface'",
            ],
            'query'  => [
                'term' => [
                    'document_type' => "Pim\\Component\\Catalog\\Model\\ProductInterface"
                ]
            ]
        ]
    ]
);

echo "Updating document_type for index {$container->getParameter('product_and_product_model_index_name')}...\n";

$client->updateByQuery(
    [
        'index' => $container->getParameter('product_and_product_model_index_name'),
        'type'  => 'pim_catalog_product',
        'body'  => [
            'script' => [
                'inline' => "ctx._source.document_type = 'Akeneo\\\\Pim\\\\Enrichment\\\\Component\\\\Product\\\\Model\\\\ProductModelInterface'",
            ],
            'query'  => [
                'term' => [
                    'document_type' => "Pim\\Component\\Catalog\\Model\\ProductModelInterface"
                ]
            ]
        ]
    ]
);

echo "Updating document_type for index {$container->getParameter('product_model_index_name')}...\n";

$client->updateByQuery(
    [
        'index' => $container->getParameter('product_model_index_name'),
        'type'  => 'pim_catalog_product',
        'body'  => [
            'script' => [
                'inline' => "ctx._source.document_type = 'Akeneo\\\\Pim\\\\Enrichment\\\\Component\\\\Product\\\\Model\\\\ProductModelInterface'",
            ],
            'query'  => [
                'term' => [
                    'document_type' => "Pim\\Component\\Catalog\\Model\\ProductModelInterface"
                ]
            ]
        ]
    ]
);

echo "Done.\n";
