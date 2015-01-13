<?php

$productCollName = 'pim_catalog_product';

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

echo sprintf("Removing filePath from %s media...\n", $products->count());

foreach ($products as $product) {
    if (array_key_exists('values', $product)) {
        $countValues = count($product['values']);

        for ($i = 0; $i <= $countValues; $i++) {
            $result = $productCollection->update(
                ['_id' => $product['_id']],
                ['$unset' => [sprintf('values.%s.media.filePath', $i) => true]],
                ['w' => true]
            );

            if ($result['ok'] != 1) {
                echo "ERROR on migrating media value:";
                print_r($result);
                print_r($product);
            }
        }
    }
}

echo sprintf("Removing filePath from %s media done.\n", $products->count());
