<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Product\Query\Sql;

use Akeneo\Pim\Enrichment\Bundle\Product\Query\Sql\GetDescendantVariantProductIds;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetDescendantVariantProductIdsIntegration extends TestCase
{
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->createFamilyVariant(
            [
                'code' => 'shirt_size_color',
                'family' => 'familyA',
                'variant_attribute_sets' => [
                    ['axes' => ['a_simple_select'], 'level' => 1],
                    ['axes' => ['a_yes_no'], 'level' => 2],
                ],
            ]
        );
        $this->createProductModel(['code' => 'a_shirt', 'family_variant' => 'shirt_size_color']);
        $mediumShirtProductModel = $this->createProductModel(
            [
                'code' => 'a_medium_shirt',
                'family_variant' => 'shirt_size_color',
                'parent' => 'a_shirt',
                'values' => [
                    'a_simple_select' => [
                        ['locale' => null, 'scope' => null, 'data' => 'optionA'],
                    ],
                ],
            ]
        );
        $largeShirtProductModel = $this->createProductModel(
            [
                'code' => 'a_large_shirt',
                'family_variant' => 'shirt_size_color',
                'parent' => 'a_shirt',
                'values' => [
                    'a_simple_select' => [
                        ['locale' => null, 'scope' => null, 'data' => 'optionB'],
                    ],
                ],
            ]
        );

        $this->createFamilyVariant(
            [
                'code' => 'shoe_size_color',
                'family' => 'familyA',
                'variant_attribute_sets' => [
                    ['axes' => ['a_simple_select'], 'level' => 1],
                    ['axes' => ['a_yes_no'], 'level' => 2],
                ],
            ]
        );
        $this->createProductModel(['code' => 'a_shoe', 'family_variant' => 'shoe_size_color']);
        $largeShoeProductModel = $this->createProductModel(
            [
                'code' => 'a_large_shoe',
                'family_variant' => 'shoe_size_color',
                'parent' => 'a_shoe',
                'values' => [
                    'a_simple_select' => [
                        ['locale' => null, 'scope' => null, 'data' => 'optionA'],
                    ],
                ],
            ]
        );

        $this->createProduct('medium_shirt_product1', $mediumShirtProductModel);
        $this->createProduct('large_shirt_product1', $largeShirtProductModel);
        $this->createProduct('large_shoe_product1', $largeShoeProductModel);
        $this->createProduct('large_shoe_product2', $largeShoeProductModel);
    }

    public function test_it_returns_descendant_variant_product_ids()
    {
        $mediumShirtProductModel = $this->get('pim_catalog.repository.product_model')->findOneByIdentifier('a_medium_shirt');
        $shoeProductModel = $this->get('pim_catalog.repository.product_model')->findOneByIdentifier('a_shoe');

        $largeShoeProduct1 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('large_shoe_product1');
        $largeShoeProduct2 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('large_shoe_product2');
        $mediumShirtProduct1 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('medium_shirt_product1');

        Assert::assertEqualsCanonicalizing(
            [$largeShoeProduct1->getId(), $largeShoeProduct2->getId(), $mediumShirtProduct1->getId()],
            $this->getDescendantVariantProductIds()->fromProductModelIds([
                $mediumShirtProductModel->getId(),
                $shoeProductModel->getId(),
            ])
        );

        Assert::assertEqualsCanonicalizing(
            [],
            $this->getDescendantVariantProductIds()->fromProductModelIds([])
        );
    }

    protected function getDescendantVariantProductIds(): GetDescendantVariantProductIds
    {
        return $this->get('akeneo.pim.enrichment.product.query.get_descendant_variant_product_ids');
    }

    private function createFamilyVariant(array $data = []): FamilyVariantInterface
    {
        $family = $this->get('pim_catalog.factory.family_variant')->create();
        $this->get('pim_catalog.updater.family_variant')->update($family, $data);
        $errors = $this->get('validator')->validate($family);
        Assert::assertCount(0, $errors);

        $this->get('pim_catalog.saver.family_variant')->save($family);

        return $family;
    }

    private function createProductModel(array $data = []): ProductModelInterface
    {
        $productModel = $this->get('pim_catalog.factory.product_model')->create();
        $this->get('pim_catalog.updater.product_model')->update($productModel, $data);

        $errors = $this->get('pim_catalog.validator.product')->validate($productModel);
        Assert::assertCount(0, $errors);

        $this->get('pim_catalog.saver.product_model')->save($productModel);

        return $productModel;
    }

    private function createProduct(
        string $identifier,
        ?ProductModelInterface $productModel,
        array $values = []
    ): ProductInterface {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier);

        $this->get('pim_catalog.updater.product')->update($product, [
            'parent' => $productModel->getCode(),
            'values' => $values
        ]);
        $this->get('pim_catalog.saver.product')->save($product);

        return $product;
    }
}
