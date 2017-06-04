<?php

require_once __DIR__ . '/../../app/bootstrap.php.cache';
require_once __DIR__ . '/../../app/AppKernel.php';
require_once __DIR__ . '/../SchemaHelper.php';
require_once __DIR__ . '/../UpgradeHelper.php';

use Doctrine\DBAL\Driver\Connection;
use Pim\Component\Catalog\Normalizer\Storage\Product\ProductValuesNormalizer;
use Pim\Upgrade\SchemaHelper;

$kernel = new AppKernel('prod', false);
$kernel->loadClassCache();
$kernel->boot();
$container = $kernel->getContainer();

$db = $container->get('database_connection');

$schemaHelper = new SchemaHelper($container);
$productTable = $schemaHelper->getTableOrCollection('product');

$serializer = $container->get('pim_serializer');
$normalizer = new ProductValuesNormalizer();
$normalizer->setSerializer($serializer);

echo "Preparing the product table...\n";
prepareProductTable($db, $productTable);

$pqbFactory = $container->get('pim_catalog.query.product_query_builder_factory');
$pqb = $pqbFactory->create();
$saver = $container->get('pim_catalog.saver.product');

echo "Storing values as JSON...\n";
$i = 0;
$products = [];

foreach ($pqb->execute() as $product) {
    if (0 === $i % 100) echo "\tproduct " . $i . "...\n";

    $values = $product->getValues();
    $normalizedValues = $normalizer->normalize($values);

    $identifier = $product->getIdentifier()->getData();

    $product->setIdentifier($identifier);
    $product->setRawValues($normalizedValues);
    $products[] = $product;

    if (0 === $i % 100) {
        $saver->saveAll($products);
        $products = [];
    }

    $i++;
}

$saver->saveAll($products);

echo "Creating unique index on identifier...\n";
createUniqueIndexOnIdentifier($db, $productTable);

echo "Done :)\n";

function prepareProductTable(Connection $db, $productTable)
{
    $db->exec("ALTER TABLE $productTable ADD identifier VARCHAR(255) NOT NULL");
    $db->exec("ALTER TABLE $productTable ADD raw_values JSON NOT NULL COMMENT '(DC2Type:json_array)'");
}

function createUniqueIndexOnIdentifier(Connection $db, $productTable)
{
    $db->exec("CREATE UNIQUE INDEX UNIQ_91CD19C0772E836A ON $productTable (identifier)");
}
