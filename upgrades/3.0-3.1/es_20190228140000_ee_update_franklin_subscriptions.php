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

$esClientBuilder = $container->get('akeneo_elasticsearch.client_builder');
$hosts = [$container->getParameter('index_hosts')];
$indexName = $container->getParameter('product_and_product_model_index_name');
$esClient = $esClientBuilder->setHosts($hosts)->build();

$mysqlConnection = $container->get('database_connection');
$bulkSize = 100;

$sql = <<<SQL
SELECT id, product_id 
FROM pimee_franklin_insights_subscription
WHERE id > :last_id
ORDER BY id ASC
LIMIT $bulkSize
SQL;

$statement = $mysqlConnection->prepare($sql);
$lastId = 0;
$updatedProductsCount = 0;

echo "Starting update ES index for the products subscribed to Franklin Insights\n";

do {
    $statement->execute(['last_id' => $lastId]);
    $subscriptions = $statement->fetchAll(\PDO::FETCH_ASSOC);

    if (empty($subscriptions)) {
        break;
    }

    $productIdsToUpdate = [];
    foreach ($subscriptions as $subscription) {
        $productIdsToUpdate[] = sprintf('product_%s', $subscription['product_id']);
        $lastId = (int) $subscription['id'];
    }

    $esClient->updateByQuery([
        'index' => $indexName,
        'type'  => 'pim_catalog_product',
        'body'  => [
            'script' => [
                'inline' => 'ctx._source.franklin_subscription = true',
            ],
            'query'  => [
                'terms' => [
                    'id' => $productIdsToUpdate,
                ]
            ]
        ]
    ]);

    $updatedProductsCount += count($productIdsToUpdate);
} while (!empty($productIdsToUpdate));

echo $updatedProductsCount > 0
    ? "Done. $updatedProductsCount products have been updated\n"
    : "There are no products subscribed to Franklin Insights\n";
