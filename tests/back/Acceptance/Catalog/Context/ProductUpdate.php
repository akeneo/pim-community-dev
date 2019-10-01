<?php

declare(strict_types=1);

namespace AkeneoEnterprise\Test\Acceptance\Catalog\Context;

use Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Repository\ProductRepository;
use Akeneo\Pim\Permission\Component\Updater\GrantedProductUpdater;
use Akeneo\Test\Acceptance\Product\InMemoryProductRepository;
use Behat\Behat\Context\Context;

/**
 * Use this context to update Products with EE only elements.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
class ProductUpdate implements Context
{
    /** @var ProductRepository */
    private $productRepository;

    /** @var GrantedProductUpdater */
    private $productUpdater;

    public function __construct(
        InMemoryProductRepository $productRepository,
        GrantedProductUpdater $productUpdater
    ) {
        $this->productRepository = $productRepository;
        $this->productUpdater = $productUpdater;
    }

    /**
     * @When /^the user adds more than (\d+) assets to the asset collection$/
     */
    public function theUserAddsMoreThanAssetsToTheAssetCollection($arg1)
    {
        $product = $this->productRepository->findOneByIdentifier('my_product');
        $assetCodes = [];
        for ($i = 0; $i < 60; $i++) {
            $assetCodes[] = sprintf('designer_%s', $i);
        }

        $this->productUpdater->update($product,
            [
                'values' => [
                    'my_assets' => [
                        ['locale' => null, 'scope' => null, 'data' => $assetCodes],
                    ],
                ]
            ]
        );

        $this->productRepository->save($product);
    }
}
