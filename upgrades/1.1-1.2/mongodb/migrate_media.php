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
$productColl = new MongoCollection($db, $productCollName);
$mediaColl = new MongoCollection($db, $mediaCollName);

$medias = $mediaColl->find();

echo sprintf("Migrating %s medias to embedded documents...\n", $medias->count());

$originalFilenames = [];

foreach ($medias as $media) {
    $valueId = $media['value']['$id'];
    unset($media['value']);
    $originalFilenames[$media['filename']] = $media['originalFilename'];
    
    $result = $productColl->update(
        ['values._id' => $valueId],
        [
            '$set' => [
                'values.$.media' => $media
            ]
        ],
        ['w' => true]
    );

    if ($result['ok'] != 1) {
        echo "ERROR on migrating media:";
        print_r($result);
        print_r($media);
    }
}
echo "Migrating %s medias done.\n\n";

echo "Setting originalFilename on normalizedData....\n";

echo "Getting all media fields...\n";
$products = $productColl->find([], ['normalizedData']);

$mediaAttributes = [];
foreach ($products as $product) {
    foreach ($product['normalizedData'] as $attribute => $value) {
        if (isset($value['filename'])) {
            $mediaAttributes[$attribute][] = $value['filename'];
        }
    }
}
echo "Media fields found:\n";
echo implode(',', array_keys($mediaAttributes))."\n";

echo sprintf("Setting %s originalFilename on normalizedData...\n", count($originalFilenames));

foreach ($mediaAttributes as $attribute => $filenames) {
    foreach ($filenames as $filename) {
        $attrPath = sprintf('normalizedData.%s.filename', $attribute);
        $originalPath = sprintf('normalizedData.%s.originalFilename', $attribute);

        $result = $productColl->update(
            [ $attrPath => $filename],
            [
                '$set' => [
                    $originalPath => $originalFilenames[$filename]
                ]
            ],
            ['w' => true, 'multiple' => true]
        );

        if ($result['ok'] != 1) {
            echo "ERROR on setting up the originalFilename for Attribute $attribute and filename $filename:";
            print_r($result);
        }
    }
}
echo "All done !\n";
