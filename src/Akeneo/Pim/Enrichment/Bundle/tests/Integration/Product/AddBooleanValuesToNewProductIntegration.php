<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\tests\Integration\Product;

use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddBooleanValuesToNewProductIntegration extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    public function testBooleanValuesAreAddedToNewProduct(): void
    {
        $this->createAttribute([
            'code'  => 'another_yes_no',
            'group' => 'attributeGroupA',
            'type'  => 'pim_catalog_boolean',
        ]);

        $this->createFamily([
            'code' => 'family_with_booleans',
            'attributes' => [
                'sku',
                'a_text',
                'a_yes_no',
                'another_yes_no'
            ]
        ]);

        $product = $this->get('pim_catalog.builder.product')->createProduct('a_new_product', 'family_with_booleans');

        $this->get('pim_catalog.updater.product')->update($product, []);
        $this->get('pim_catalog.saver.product')->save($product);
        $this->get('doctrine.orm.entity_manager')->clear();

        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('a_new_product');

        $this->assertNotNull($product, 'The product has not been created');
        $this->assertProductHasValue($product, 'a_yes_no', false);
        $this->assertProductHasValue($product, 'another_yes_no', false);
    }

    public function testBooleanValuesAreAddedToNewFirstLevelProductVariant(): void
    {
        $this->createAttribute([
            'code'  => 'another_yes_no',
            'group' => 'attributeGroupA',
            'type'  => 'pim_catalog_boolean',
        ]);

        $this->createFamily([
            'code' => 'family_with_booleans',
            'attributes' => [
                'sku',
                'a_simple_select_color',
                'a_yes_no',
                'another_yes_no'
            ]
        ]);

        $this->createFamilyVariant([
            'code'                   => 'family_variant_with_boolean',
            'family'                 => 'family_with_booleans',
            'variant_attribute_sets' => [
                [
                    'axes'       => ['a_simple_select_color'],
                    'attributes' => ['another_yes_no'],
                    'level'      => 1,
                ],
                [
                    'axes'       => [],
                    'attributes' => [],
                    'level'      => 2,
                ]
            ],
        ]);

        $this->createProductModel([
            'code'           => 'product_model_with_boolean',
            'family_variant' => 'family_variant_with_boolean',
            'values'         => [
                'a_simple_select_color' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => 'blue',
                    ]
                ],
                'a_yes_no'              => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => true,
                    ]
                ]
            ]
        ]);

        $variantProduct = $this->get('pim_catalog.builder.product')
            ->createProduct('a_new_variant_product', 'family_with_booleans');

        $this->get('pim_catalog.updater.product')->update($variantProduct, ['parent' => 'product_model_with_boolean']);
        $this->get('pim_catalog.saver.product')->save($variantProduct);
        $this->get('doctrine.orm.entity_manager')->clear();

        $variantProduct = $this->get('pim_catalog.repository.product')->findOneByIdentifier('a_new_variant_product');

        $this->assertNotNull($variantProduct, 'The variant product has not been created');
        $this->assertProductHasValue($variantProduct, 'a_yes_no', true);
        $this->assertProductHasValue($variantProduct, 'another_yes_no', false);
    }

    public function testBooleanValuesAreAddedToNewSecondLevelProductVariant(): void
    {
        $this->createAttribute([
            'code'  => 'a_yes_no_level_1',
            'group' => 'attributeGroupA',
            'type'  => 'pim_catalog_boolean',
        ]);

        $this->createAttribute([
            'code'  => 'a_yes_no_level_2',
            'group' => 'attributeGroupA',
            'type'  => 'pim_catalog_boolean',
        ]);

        $this->createFamily([
            'code' => 'family_with_booleans',
            'attributes' => [
                'sku',
                'a_simple_select_color',
                'a_simple_select_size',
                'a_yes_no',
                'a_yes_no_level_1',
                'a_yes_no_level_2',

            ]
        ]);

        $this->createFamilyVariant([
            'code'                   => 'family_variant_with_two_levels_booleans',
            'family'                 => 'family_with_booleans',
            'variant_attribute_sets' => [
                [
                    'axes'       => ['a_simple_select_color'],
                    'attributes' => ['a_yes_no_level_1'],
                    'level'      => 1,
                ],
                [
                    'axes'       => ['a_simple_select_size'],
                    'attributes' => ['a_yes_no_level_2'],
                    'level'      => 2,
                ]
            ],
        ]);

        $this->createProductModel([
            'code'           => 'parent_product_model_with_boolean',
            'family_variant' => 'family_variant_with_two_levels_booleans',
            'values'         => [
                'a_simple_select_color' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => 'blue',
                    ]
                ],
                'a_yes_no'              => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => true,
                    ]
                ]
            ]
        ]);

        $this->createProductModel([
            'code'           => 'level_2_product_model_with_boolean',
            'family_variant' => 'family_variant_with_two_levels_booleans',
            'parent'         => 'parent_product_model_with_boolean',
            'values'         => [
                'a_simple_select_size' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => 'xl',
                    ]
                ],
                'a_yes_no_level_1'     => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => true,
                    ]
                ]
            ]
        ]);

        $variantProduct = $this->get('pim_catalog.builder.product')
            ->createProduct('a_new_variant_product', 'family_with_booleans');

        $this->get('pim_catalog.updater.product')->update($variantProduct, ['parent' => 'level_2_product_model_with_boolean']);
        $this->get('pim_catalog.saver.product')->save($variantProduct);
        $this->get('doctrine.orm.entity_manager')->clear();

        $variantProduct = $this->get('pim_catalog.repository.product')->findOneByIdentifier('a_new_variant_product');

        $this->assertNotNull($variantProduct, 'The variant product has not been created');
        $this->assertProductHasValue($variantProduct, 'a_yes_no', true);
        $this->assertProductHasValue($variantProduct, 'a_yes_no_level_1', true);
        $this->assertProductHasValue($variantProduct, 'a_yes_no_level_2', false);
    }

    /**
     * @param array $familyData
     *
     * @return FamilyInterface
     */
    private function createFamily(array $familyData): FamilyInterface
    {
        $family = $this->get('pim_catalog.factory.family')->create();

        $this->get('pim_catalog.updater.family')->update($family, $familyData);
        $this->get('pim_catalog.saver.family')->save($family);

        return $family;
    }

    /**
     * @param array $familyData
     *
     * @return FamilyVariantInterface
     */
    private function createFamilyVariant(array $familyData): FamilyVariantInterface
    {
        $familyVariant = $this->get('pim_catalog.factory.family_variant')->create();

        $this->get('pim_catalog.updater.family_variant')->update($familyVariant, $familyData);
        $this->get('pim_catalog.saver.family_variant')->save($familyVariant);

        return $familyVariant;
    }

    /**
     * @param array $attributeData
     *
     * @return AttributeInterface
     */
    private function createAttribute(array $attributeData): AttributeInterface
    {
        $attribute = $this->get('pim_catalog.factory.attribute')->create();

        $this->get('pim_catalog.updater.attribute')->update($attribute, $attributeData);
        $this->get('pim_catalog.saver.attribute')->save($attribute);

        return $attribute;
    }

    /**
     * @param array $productModelData
     *
     * @return ProductModelInterface
     */
    private function createProductModel(array $productModelData): ProductModelInterface
    {
        $productModel = $this->get('pim_catalog.factory.product_model')->create();

        $this->get('pim_catalog.updater.product_model')->update($productModel, $productModelData);
        $this->get('pim_catalog.saver.product_model')->save($productModel);

        return $productModel;
    }

    /**
     * @param ProductInterface $product
     * @param string           $attributeCode
     * @param mixed            $expectedValue
     */
    private function assertProductHasValue(ProductInterface $product, string $attributeCode, $expectedValue): void
    {
        $productValue = $product->getValue($attributeCode);

        $this->assertNotNull($productValue, sprintf(
            "The product %s doesn't have value for the attribute %s", $product->getIdentifier(), $attributeCode
        ));

        $this->assertSame($expectedValue, $productValue->getData(), sprintf(
            "The attribute %s doesn't have the expected value", $attributeCode
        ));
    }
}
