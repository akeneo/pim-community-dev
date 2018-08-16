<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Behat\Context;

use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Behat\Behat\Context\Context;
use Doctrine\ORM\EntityManagerInterface;
use Webmozart\Assert\Assert;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class ProductAssetCollectionContext implements Context
{
    /** @var ObjectUpdaterInterface */
    private $productUpdater;

    /** @var SaverInterface */
    private $productSaver;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var EntityManagerInterface */
    private $entityManager;

    /**
     * @param ObjectUpdaterInterface     $productUpdater
     * @param SaverInterface             $productSaver
     * @param ProductRepositoryInterface $productRepository
     * @param EntityManagerInterface     $entityManager
     */
    public function __construct(
        ObjectUpdaterInterface $productUpdater,
        SaverInterface $productSaver,
        ProductRepositoryInterface $productRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->productUpdater = $productUpdater;
        $this->productSaver = $productSaver;
        $this->productRepository = $productRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @param string $orderedAssetCodes
     * @param string $attributeCode
     * @param string $productIdentifier
     *
     * @When /^assets ([^"]+) are ordered into the asset collection ([^"]+) of the product ([^"]+)$/
     */
    public function addAssetsInOrderToAssetCollection(
        string $orderedAssetCodes,
        string $attributeCode,
        string $productIdentifier
    ): void {
        $standardAssetCollection = [
            'locale' => null,
            'scope' => null,
            'data' => $this->listToArray($orderedAssetCodes),
        ];

        $product = $this->productRepository->findOneByIdentifier($productIdentifier);

        $this->productUpdater->update($product, [
            'values' =>[
                $attributeCode => [$standardAssetCollection],
            ],
        ]);

        $this->productSaver->save($product);
    }

    /**
     * @param string $attributeCode
     * @param string $productIdentifier
     * @param string $orderedAssetCodes
     *
     * @Then /^the asset collection ([^"]+) of the product ([^"]+) should be ordered as ([^"]+)$/
     */
    public function theAssetsShouldBeOrderedInTheCollection(
        string $attributeCode,
        string $productIdentifier,
        string $orderedAssetCodes
    ): void {
        $expectedAssetCodes = $this->listToArray($orderedAssetCodes);
        $currentAssetCodes = [];

        $this->entityManager->clear();
        $product = $this->productRepository->findOneByIdentifier($productIdentifier);
        $assetCollectionValue = $product->getValue($attributeCode);

        foreach ($assetCollectionValue->getData() as $asset) {
            $currentAssetCodes[] = $asset->getCode();
        }

        Assert::same($currentAssetCodes, $expectedAssetCodes);
    }

    /**
     * @param string $list
     *
     * @return array
     */
    private function listToArray(string $list): array
    {
        if (empty($list)) {
            return [];
        }

        return explode(', ', str_replace(' and ', ', ', $list));
    }
}
