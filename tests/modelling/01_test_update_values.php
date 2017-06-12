<?php

// test that a product can be created, saved, and indexed after having decoupled the flexible values features

$loader = require_once __DIR__ . '/../../app/bootstrap.php.cache';
require_once __DIR__ . '/../../app/AppKernel.php';

$kernel = new AppKernel('dev', true);
$kernel->boot();
$c = $kernel->getContainer();

$repository = $c->get('pim_catalog.repository.product');
$builder = $c->get('pim_catalog.builder.product');
$updater = $c->get('pim_catalog.updater.product');
$saver = $c->get('pim_catalog.saver.product');
$remover = $c->get('pim_catalog.remover.product');

const IDENTIFIER = '501_graphic_red_s';

$existingProduct = $repository->findOneByIdentifier(IDENTIFIER);
if (null !== $existingProduct) {
    $remover->remove($existingProduct);
}

$product = $builder->createProduct(IDENTIFIER, 'tshirts');
$updater->update(
    $product,
    [
        'values' => [
            'name'        => [
                ['data' => 'LEVI\'S® 501 GRAPHIC TEE', 'locale' => null, 'scope' => null],
            ],
            'price'       => [
                [
                    'data'   => [['amount' => 29, 'currency' => 'EUR'], ['amount' => 34, 'currency' => 'USD'],],
                    'locale' => null,
                    'scope'  => null
                ],
            ],
            'description' => [
                [
                    'data'   => 'Ce t-shirt basique affiche une coupe standard et présente une sérigraphie sur le devant.',
                    'locale' => 'fr_FR',
                    'scope'  => 'ecommerce'
                ],
                ['data' => 'cool tshirt', 'locale' => 'en_US', 'scope' => 'ecommerce'],
            ],
            'picture' => [
                ['data' => __DIR__ . '/fixtures/levis501-black.jpg', 'locale' => null, 'scope' => null],
            ],
            'main_color' => [
                ['data' => 'black', 'locale' => null, 'scope' => null],
            ],
            'secondary_color' => [
                ['data' => 'red', 'locale' => null, 'scope' => null],
            ],
            'clothing_size' => [
                ['data' => 'S', 'locale' => null, 'scope' => null],
            ],
        ]
    ]
);

// the product is saved in mysql and indexed in elasticsearch
$saver->save($product);

//var_dump($product->getValues());
