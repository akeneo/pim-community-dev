<?php

if (!file_exists(__DIR__ . '/../../app/AppKernel.php')) {
    die("Please run this command from your Symfony application root.");
}

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../app/AppKernel.php';

$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    (new Symfony\Component\Dotenv\Dotenv(true))->load($envFile);
}

$kernel = new AppKernel('prod', false);
$kernel->boot();
$container = $kernel->getContainer();

$builder = $container->get('akeneo_elasticsearch.client_builder');
$hosts = [$container->getParameter('index_hosts')];

$client = $builder->setHosts($hosts)->build();

echo "Updating ProductDraft document_type for index {$container->getParameter('product_proposal_index_name')}...\n";

$client->updateByQuery(
    [
        'index' => $container->getParameter('product_proposal_index_name'),
        'type'  => 'pimee_workflow_product_proposal',
        'body'  => [
            'script' => [
                'inline' => "ctx._source.document_type = 'Akeneo\\\\Pim\\\\WorkOrganization\\\\Workflow\\\\Component\\\\Model\\\\ProductDraft'",
            ],
            'query'  => [
                'term' => [
                    'document_type' => "PimEnterprise\\Component\\Workflow\\Model\\ProductDraft"
                ]
            ]
        ]
    ]
);

echo "Updating ProductModelDraft document_type for index {$container->getParameter('product_proposal_index_name')}...\n";

$client->updateByQuery(
    [
        'index' => $container->getParameter('product_proposal_index_name'),
        'type'  => 'pimee_workflow_product_proposal',
        'body'  => [
            'script' => [
                'inline' => "ctx._source.document_type = 'Akeneo\\\\Pim\\\\WorkOrganization\\\\Workflow\\\\Component\\\\Model\\\\ProductModelDraft'",
            ],
            'query'  => [
                'term' => [
                    'document_type' => "PimEnterprise\\Component\\Workflow\\Model\\ProductModelDraft"
                ]
            ]
        ]
    ]
);

echo "Updating ProductInterface document_type for index {$container->getParameter('published_product_and_product_model_index_name')}...\n";

$client->updateByQuery(
    [
        'index' => $container->getParameter('published_product_and_product_model_index_name'),
        'type'  => 'pimee_workflow_published_product',
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

echo "Done.\n";
