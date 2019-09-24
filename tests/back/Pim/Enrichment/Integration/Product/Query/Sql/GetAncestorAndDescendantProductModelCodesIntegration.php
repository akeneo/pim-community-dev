<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Product\Query\Sql;

use Akeneo\Pim\Enrichment\Bundle\Product\Query\Sql\GetAncestorAndDescendantsProductModelCodes;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetAncestorAndDescendantsProductModelCodesIntegration extends TestCase
{
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalSqlCatalog();
    }

    protected function getAncestorAndDescendantsProductModelCodes(): GetAncestorAndDescendantsProductModelCodes
    {
        return $this->get('akeneo.pim.enrichment.product.query.get_ancestor_and_descendants_product_model_codes');
    }

    public function testFromProductModelCodes()
    {
        $this->createFamilyVariant(
            [
                'code' => 'shirt_size_color',
                'family' => 'familyA',
                'variant_attribute_sets' => [
                    ['axes' => ['a_simple_select'], 'level' => 1],
                    ['axes' => ['a_simple_select'], 'level' => 2],
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
                    ['axes' => ['a_simple_select'], 'level' => 2],
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

        Assert::assertEqualsCanonicalizing(
            [],
            $this->getAncestorAndDescendantsProductModelCodes()
                ->fromProductModelCodes(['a_shirt', 'a_shoe'])
        );

        Assert::assertEqualsCanonicalizing(
            [],
            $this->getAncestorAndDescendantsProductModelCodes()
                ->fromProductModelCodes(['unknown'])
        );

        Assert::assertEqualsCanonicalizing(
            [],
            $this->getAncestorAndDescendantsProductModelCodes()
                ->fromProductModelCodes([])
        );

        Assert::assertEqualsCanonicalizing(
            ['a_shoe'],
            $this->getAncestorAndDescendantsProductModelCodes()
                ->fromProductModelCodes(['a_shirt', 'a_large_shoe'])
        );

        Assert::assertEqualsCanonicalizing(
            ['a_shoe'],
            $this->getAncestorAndDescendantsProductModelCodes()
                ->fromProductModelCodes(['a_shirt', 'a_large_shoe'])
        );

        Assert::assertEqualsCanonicalizing(
            ['a_shirt', 'a_shoe'],
            $this->getAncestorAndDescendantsProductModelCodes()
                ->fromProductModelCodes(['a_large_shirt', 'a_large_shoe'])
        );
    }

    private function createFamilyVariant(array $data = []): FamilyVariantInterface
    {
        $family = $this->get('pim_catalog.factory.family_variant')->create();
        $this->get('pim_catalog.updater.family_variant')->update($family, $data);

        $this->get('pim_catalog.saver.family_variant')->save($family);

        return $family;
    }

    private function createProductModel(array $data = []): ProductModelInterface
    {
        $productModel = $this->get('pim_catalog.factory.product_model')->create();
        $this->get('pim_catalog.updater.product_model')->update($productModel, $data);

        $errors = $this->get('pim_catalog.validator.product')->validate($productModel);
        if (0 !== $errors->count()) {
            throw new \Exception(
                sprintf(
                    'Impossible to setup test in %s: %s',
                    static::class,
                    $errors->get(0)->getMessage()
                )
            );
        }

        $this->get('pim_catalog.saver.product_model')->save($productModel);

        return $productModel;
    }
}
