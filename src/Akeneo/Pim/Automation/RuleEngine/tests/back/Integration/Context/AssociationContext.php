<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\Pim\Automation\RuleEngine\Integration\Context;

use Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Saver\ProductSaver;
use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Webmozart\Assert\Assert;

class AssociationContext implements Context
{
    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var ObjectUpdaterInterface */
    private $productUpdater;

    /** @var ProductSaver */
    private $productSaver;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        ObjectUpdaterInterface $productUpdater,
        ProductSaver $productSaver
    ) {
        $this->productRepository = $productRepository;
        $this->productUpdater = $productUpdater;
        $this->productSaver = $productSaver;
    }

    /**
     * @Given the following associations for product :identifier:
     */
    public function theFollowingAssociationsForProduct(string $identifier, TableNode $table)
    {
        $product = $this->productRepository->findOneByIdentifier($identifier);

        $associations = [];
        foreach ($table->getHash() as $row) {
            $associationType = $row['association_type'];
            $associations = [$associationType => []];
            foreach (['products', 'product_models', 'groups'] as $key) {
                if ('' !== $row[$key]) {
                    $associations[$associationType][$key] = $this->listToArray($row[$key]);
                }
            }
        }
        $this->productUpdater->update($product, ['associations' => $associations]);
        $this->productSaver->save($product);
    }

    /**
     * @Then the :identifier product should have the following associations:
     */
    public function theProductShouldHaveTheFollowingAssociations(string $identifier, TableNode $table)
    {
        $product = $this->productRepository->findOneByIdentifier($identifier);
        foreach ($table->getHash() as $row) {
            $associatedProducts = $product->getAssociatedProducts('association_type');
            $associatedProductIdentifiers = $associatedProducts ?
                $associatedProducts->map(
                    function (ProductInterface $product): string {
                        return $product->getIdentifier();
                    }
                )->toArray() : [];
            sort($associatedProductIdentifiers);
            $expectedAssociatedProducts = $this->listToArray($row['products']);
            sort($expectedAssociatedProducts);
            Assert::eq(
                $associatedProductIdentifiers,
                $expectedAssociatedProducts,
                sprintf(
                    'Expected associated products to be "%s", "%s" found',
                    $row['products'],
                    implode(',', $associatedProductIdentifiers)
                )
            );

            $associatedProductModels = $product->getAssociatedProductModels('association_type');
            $associatedProductModelCodes = $associatedProductModels ?
                $associatedProductModels->map(
                    function (ProductModelInterface $productModel): string {
                        return $productModel->getCode();
                    }
                )->toArray() : [];
            sort($associatedProductModelCodes);
            $expectedAssociatedProductModels = $this->listToArray($row['product_models']);
            sort($expectedAssociatedProductModels);
            Assert::eq(
                $associatedProductModelCodes,
                $expectedAssociatedProductModels,
                sprintf(
                    'Expected associated product models to be "%s", "%s" found',
                    $row['product_models'],
                    implode(',', $associatedProductModelCodes)
                )
            );

            $associatedGroups = $product->getAssociatedGroups('association_type');
            $associatedGroupCodes = $associatedGroups ?
                $associatedGroups->map(
                    function (GroupInterface $group): string {
                        return $group->getCode();
                    }
                )->toArray() : [];
            sort($associatedGroupCodes);
            $expectedAssociatedGroups = $this->listToArray($row['groups']);
            sort($expectedAssociatedGroups);
            Assert::eq(
                $associatedGroupCodes,
                $expectedAssociatedGroups,
                sprintf(
                    'Expected associated groups to be "%s", "%s" found',
                    $row['groups'],
                    implode(',', $associatedGroupCodes)
                )
            );
        }
    }

    private function listToArray(string $list): array
    {
        return '' === trim($list) ? [] : array_map('trim', explode(',', $list));
    }
}
