<?php

$productCollName = 'pim_catalog_product';
$mediaCollName = 'pim_catalog_media';

if (count($argv) !== 3) {
    echo sprintf("Usage: %s <mongodb_server> <mongodb_database>\n", $argv[0]);
    echo sprintf("Example: %s mongodb://localhost:27017 akeneo_pim\n", $argv[0]);
    exit(1);
}

$server = $argv[1];
$database = $argv[2];

$client = new MongoClient();
$db = $client->$database;
$productCollection = new MongoCollection($db, $productCollName);

$products = $productCollection->find();

echo sprintf("Migrating %s product status values...\n", $products->count());

foreach ($products as $product) {
    $result = $productCollection->update(
        ['_id' => $product['_id']],
        [
            '$set' => [
                'normalizedData.enabled' => $product['enabled']
            ]
        ],
        ['w' => true]
    );

    if ($result['ok'] != 1) {
        echo "ERROR on migrating enabled value:";
        print_r($result);
        print_r($product);
    }
}

echo sprintf("Migrating %s product status values done.\n", $products->count());
