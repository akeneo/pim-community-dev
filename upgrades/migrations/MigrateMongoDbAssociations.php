<?php

use Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\AkeneoStorageUtilsExtension;
use Symfony\Component\Console\Input\ArgvInput;

require_once __DIR__.'/../../app/bootstrap.php.cache';
require_once __DIR__.'/../../app/AppKernel.php';

/**
 * In a Mongo Database, when you import associations through products, you can be in presence of these documents:
 *  {
 *      _id: ObjectId("58945ae949be10e4448b4568"),
 *      associations: [
 *          {
 *              _id: ObjectId("58945ae949be10e4448b4576"),
 *              products: [
 *                  {
 *                      $ref: 'pim_catalog_product',
 *                      $id: '58945ae949be10e4448b4568',
 *                      $db: ''
 *                  }
 *              ],
 *              ...
 *          }
 *      ],
 *      ...
 *  }
 *
 * This document is not correct because:
 * - The $id of the associated product is a string and should be an ObjectId
 * - the $db is empty
 *
 * This script allows to update all these documents by updating it to
 *  {
 *      _id: ObjectId("58945ae949be10e4448b4568"),
 *      associations: [
 *          {
 *              _id: ObjectId("58945ae949be10e4448b4576"),
 *              products: [
 *                  {
 *                      $ref: 'pim_catalog_product',
 *                      $id: ObjectId('58945ae949be10e4448b4568'),
 *                      $db: 'akeneo_pim'
 *                  }
 *              ],
 *              ...
 *          }
 *      ],
 *      ...
 *  }
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

$input = new ArgvInput($argv);
$env = $input->getParameterOption(['-e', '--env']);
if (!$env) {
    echo sprintf("Usage: %s --env=<environment>\nExample: %s --env=dev\n", $argv[0], $argv[0]);
    exit(1);
}
$kernel = new AppKernel($env, $env === 'dev');
$kernel->loadClassCache();
$kernel->boot();
$container = $kernel->getContainer();

$storageDriver = $container->getParameter('pim_catalog_product_storage_driver');
if (AkeneoStorageUtilsExtension::DOCTRINE_MONGODB_ODM !== $storageDriver) {
    echo "This script is only available for MongoDb storage.\n";
    exit(1);
}

$documentManager = $container->get('pim_catalog.object_manager.product');
$className = $container->getParameter('pim_catalog.entity.product.class');
$collection = $documentManager->getDocumentCollection($className);
$databaseName = $documentManager->getDocumentDatabase($className)->getName();

echo "Looking for documents to fix...\n";
$documentsToUpdate = $collection->find([
    'associations.products' => [
        '$elemMatch' => [
            '$id' => [
                // 2 === Type 'String': https://docs.mongodb.com/v2.4/reference/operator/query/type/#op._S_type
                '$type' => 2
            ]
        ]
    ]
]);

$total = count($documentsToUpdate);
echo sprintf("%d document(s) found\n", $total);

$count = 0;
foreach ($documentsToUpdate as $document) {
    $query = [ '_id' => $document['_id'] ];
    $updateQuery = [
        '$set' => [
            'associations' => updateAssociations($document['associations'])
        ]
    ];
    $collection->update($query, $updateQuery, [ 'multiple' => false ]);

    $count ++;
    if (0 === $count % 100) {
        echo sprintf("%d/%d ...\n", $count, $total);
    }
}

echo sprintf("\n%s document(s) fixed with success.\n", $total);

/**
 * @param $associations
 * @return array
 */
function updateAssociations($associations)
{
    $newAssociations = [];
    foreach ($associations as $association) {
        $newAssociations[] = updateAssociation($association);
    }

    return $newAssociations;
}

/**
 * @param $association
 * @return array
 */
function updateAssociation($association)
{
    return [
        '_id'             => $association['_id'],
        'associationType' => $association['associationType'],
        'owner'           => $association['owner'],
        'products'        => updateProducts($association['products']),
        'groupIds'        => $association['groupIds'],
    ];
}

/**
 * @param $products
 * @return array
 */
function updateProducts($products)
{
    $newProducts = [];
    foreach ($products as $product) {
        $newProducts[] = updateProduct($product);
    }

    return $newProducts;
}

/**
 * @param $product
 * @return array
 */
function updateProduct($product)
{
    global $databaseName;

    return [
        '$ref' => $product['$ref'],
        '$id'  => new \MongoId($product['$id']),
        '$db'  => $databaseName,
    ];
}
