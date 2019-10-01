<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Product\Query\Sql;

use Akeneo\Pim\Enrichment\Bundle\Product\Query\Sql\GetDescendantProductModelIds;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetDescendantProductModelIdsIntegration extends TestCase
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
        $this->createProductModel(
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
        $this->createProductModel(
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
        $this->createProductModel(
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
    }

    public function test_it_returns_descendant_product_model_ids()
    {
        $shirtProductModel = $this->get('pim_catalog.repository.product_model')->findOneByIdentifier('a_shirt');
        $shoeProductModel = $this->get('pim_catalog.repository.product_model')->findOneByIdentifier('a_shoe');

        $mediumShirtProductModel = $this->get('pim_catalog.repository.product_model')->findOneByIdentifier('a_medium_shirt');
        $largeShirtProductModel = $this->get('pim_catalog.repository.product_model')->findOneByIdentifier('a_large_shirt');
        $largeShoeProductModel = $this->get('pim_catalog.repository.product_model')->findOneByIdentifier('a_large_shoe');

        Assert::assertEqualsCanonicalizing(
            [],
            $this->getDescendantProductModelIds()->fromProductModelIds([$mediumShirtProductModel->getId()])
        );

        Assert::assertEqualsCanonicalizing(
            [$mediumShirtProductModel->getId(), $largeShirtProductModel->getId()],
            $this->getDescendantProductModelIds()->fromProductModelIds([$shirtProductModel->getId()])
        );

        Assert::assertEqualsCanonicalizing(
            [$mediumShirtProductModel->getId(), $largeShirtProductModel->getId(), $largeShoeProductModel->getId()],
            $this->getDescendantProductModelIds()->fromProductModelIds([$shirtProductModel->getId(), $shoeProductModel->getId()])
        );

        Assert::assertEqualsCanonicalizing(
            [],
            $this->getDescendantProductModelIds()->fromProductModelIds([])
        );
    }

    protected function getDescendantProductModelIds(): GetDescendantProductModelIds
    {
        return $this->get('akeneo.pim.enrichment.product.query.get_descendant_product_model_ids');
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
}
