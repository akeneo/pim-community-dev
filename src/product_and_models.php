<?php

use Pim\Component\Catalog\Model\Product;
use Pim\Component\Catalog\Model\ProductModel;
use Symfony\Component\HttpFoundation\Request;

$loader = require_once __DIR__ . '/../app/bootstrap.php.cache';

require_once __DIR__ . '/../app/AppKernel.php';

$kernel = new AppKernel('prod', true);
$kernel->boot();
$c = $kernel->getContainer();

$doctrine = $c->get('doctrine.dbal.default_connection');
$doctrine->exec('DELETE FROM pim_catalog_product');
$doctrine->exec('DELETE FROM pim_catalog_product_model');


$productSaver = $c->get('pim_catalog.saver.product');
$productUpdater = $c->get('pim_catalog.updater.product');
$productBuilder = $c->get('pim_catalog.builder.product');
$modelSaver = $c->get('pim_catalog.saver.product_model');
$modelUpdater = $c->get('pim_catalog.updater.product_model');

$modelDivided = new ProductModel();
$modelDivided->setIdentifier('Cotton t-shirt with a round neck Divided');
$modelUpdater->update(
    $modelDivided,
    [
        'values' => [
            'description' => [
                [
                    'locale' => 'en_US',
                    'scope'  => 'ecommerce',
                    'data'   => 'such a magnificient tshirt'
                ]
            ],
        ]
    ]
);
$modelSaver->save($modelDivided);


$subModelDividedOrange = new ProductModel();
$subModelDividedOrange->setIdentifier('Cotton t-shirt with a round neck Divided orange');
$subModelDividedOrange->setParent($modelDivided);
$modelUpdater->update(
    $subModelDividedOrange,
    [
        'values' => [
            'main_color'       => [
                [
                    'locale' => null,
                    'scope'  => null,
                    'data'   => 'orange'
                ]
            ],
            'tshirt_materials' => [
                [
                    'locale' => null,
                    'scope'  => null,
                    'data'   => 'cotton'
                ]
            ],
        ]
    ]
);
$modelSaver->save($subModelDividedOrange);


$modelKurt = new ProductModel();
$modelKurt->setIdentifier('T-shirt with a Kurt Cobain print motif');
$modelUpdater->update(
    $modelKurt,
    [
        'values' => [
            'main_color'       => [
                [
                    'locale' => null,
                    'scope'  => null,
                    'data'   => 'orange'
                ]
            ],
            'tshirt_materials' => [
                [
                    'locale' => null,
                    'scope'  => null,
                    'data'   => 'cotton'
                ]
            ],
        ]
    ]
);
$modelSaver->save($modelKurt);


$modelUniqueSize = new ProductModel();
$modelUniqueSize->setIdentifier('T-shirt unique size');
$modelUpdater->update(
    $modelUniqueSize,
    [
        'values' => [
            'clothing_size'    => [
                [
                    'locale' => null,
                    'scope'  => null,
                    'data'   => 'm'
                ]
            ],
            'tshirt_materials' => [
                [
                    'locale' => null,
                    'scope'  => null,
                    'data'   => 'cotton'
                ]
            ],
        ]
    ]
);
$modelSaver->save($modelUniqueSize);


$productUniqueSizeOrange = $productBuilder->createProduct('T-shirt unique size orange');
$productUniqueSizeOrange->setProductModel($modelUniqueSize);
$productUpdater->update(
    $productUniqueSizeOrange,
    [
        'values' => [
            'main_color' => [
                [
                    'locale' => null,
                    'scope'  => null,
                    'data'   => 'orange'
                ]
            ],
        ]
    ]
);
$productSaver->save($productUniqueSizeOrange);

echo "DONE!!!!!!";
