<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\ProductModel;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

/**
 * @author    Mathias METAYER <mathias.metayer@akeneo.com>
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetDescendantVariantProductIdentifiersIntegration extends TestCase
{
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalSqlCatalog();
    }

    public function test_that_it_gets_descendant_identifiers_of_sub_product_models()
    {
        $this->createFamilyVariant(
            [
                'code' => 'shirt_size',
                'family' => 'familyA',
                'variant_attribute_sets' => [
                    ['axes' => ['a_simple_select'], 'level' => 1],
                ],
            ]
        );
        $shirtProductModel = $this->createProductModel(['code' => 'a_shirt', 'family_variant' => 'shirt_size']);
        $this->createProduct('a_small_shirt', 'clothing_size_color', $shirtProductModel);
        $this->createProduct('a_medium_shirt', 'clothing_size_color', $shirtProductModel);
        $this->createProduct('a_large_shirt', 'clothing_size_color', $shirtProductModel);

        $this->createFamilyVariant(
            [
                'code' => 'shoe_size',
                'family' => 'familyA',
                'variant_attribute_sets' => [
                    ['axes' => ['a_simple_select'], 'level' => 1],
                ],
            ]
        );
        $shoeProductModel = $this->createProductModel(['code' => 'a_shoe', 'family_variant' => 'shoe_size']);
        $this->createProduct('a_small_shoe', 'clothing_size_color', $shoeProductModel);
        $this->createProduct('a_medium_shoe', 'clothing_size_color', $shoeProductModel);
        $this->createProduct('a_large_shoe', 'clothing_size_color', $shoeProductModel);

        Assert::assertEqualsCanonicalizing(
            ['a_small_shirt', 'a_medium_shirt', 'a_large_shirt', 'a_small_shoe', 'a_medium_shoe', 'a_large_shoe'],
            $this->get('akeneo.pim.enrichment.product.query.get_descendant_variant_product_identifiers')
                ->fromProductModelCodes(['a_shirt', 'a_shoe'])
        );
    }

    public function test_that_it_gets_descendant_identifiers_of_root_product_models()
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
        $this->createProduct('a_medium_red_shirt', 'shirt_size_color', $mediumShirtProductModel);
        $this->createProduct('a_medium_blue_shirt', 'shirt_size_color', $mediumShirtProductModel);
        $this->createProduct('a_large_black_shirt', 'shirt_size_color', $largeShirtProductModel);

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
        $this->createProduct('a_large_red_shoe', 'shoe_size_color', $largeShoeProductModel);
        $this->createProduct('a_large_green_shoe', 'shoe_size_color', $largeShoeProductModel);

        Assert::assertEqualsCanonicalizing(
            ['a_medium_red_shirt', 'a_medium_blue_shirt', 'a_large_black_shirt', 'a_large_red_shoe', 'a_large_green_shoe'],
            $this->get('akeneo.pim.enrichment.product.query.get_descendant_variant_product_identifiers')
                ->fromProductModelCodes(['a_shirt', 'a_shoe'])
        );
    }

    public function test_that_it_gets_descendant_identifiers_of_both_root_and_sub_product_models()
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
        $this->createProduct('a_medium_red_shirt', 'shirt_size_color', $mediumShirtProductModel);
        $this->createProduct('a_medium_blue_shirt', 'shirt_size_color', $mediumShirtProductModel);
        $this->createProduct('a_large_black_shirt', 'shirt_size_color', $largeShirtProductModel);

        $this->createFamilyVariant(
            [
                'code' => 'shoe_size',
                'family' => 'familyA',
                'variant_attribute_sets' => [
                    ['axes' => ['a_simple_select'], 'level' => 1],
                ],
            ]
        );
        $shoeProductModel = $this->createProductModel(['code' => 'a_shoe', 'family_variant' => 'shoe_size']);
        $this->createProduct('a_large_shoe', 'shoe_size_color', $shoeProductModel);
        $this->createProduct('a_medium_shoe', 'shoe_size_color', $shoeProductModel);

        Assert::assertEqualsCanonicalizing(
            ['a_medium_red_shirt', 'a_medium_blue_shirt', 'a_large_black_shirt', 'a_large_shoe', 'a_medium_shoe'],
            $this->get('akeneo.pim.enrichment.product.query.get_descendant_variant_product_identifiers')
                ->fromProductModelCodes(['a_shirt', 'a_shoe'])
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

    private function createProduct(
        string $identifier,
        ?string $familyCode,
        ?ProductModelInterface $productModel,
        array $values = []
    ): ProductInterface {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier, $familyCode);
        if (null !== $productModel) {
            $product->setParent($productModel);
        }

        $this->get('pim_catalog.updater.product')->update($product, ['values' => $values]);
        $this->get('pim_catalog.saver.product')->save($product);

        return $product;
    }
}
