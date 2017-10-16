<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\Product\Query;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Pim\Bundle\CatalogBundle\tests\helper\EntityBuilder;

/**
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessGridFilterIntegration extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->loadFixtures();
    }

    public function testThatItFindsTheIncompleteProductIdentifier()
    {
        $inCompleteProductIds = $this->get('pim_catalog.doctrine.query.completeness_grid_filter')
            ->findVariantProductIdentifiers( true);

        $this->assertEquals([], $inCompleteProductIds);
    }

    public function testThatItFindsTheCompleteProductIdentifier()
    {
        $completeProductIds = $this->get('pim_catalog.doctrine.query.completeness_grid_filter')
            ->findVariantProductIdentifiers('mobile', 'fr_FR', false);

        $this->assertEquals([], $completeProductIds);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return new Configuration([Configuration::getTechnicalCatalogPath()]);
    }

    protected function loadFixtures() : void
    {
        $builder = new EntityBuilder(static::$kernel->getContainer());

        $builder->createFamilyVariant([
            'code'        => 'familyVariantA1',
            'family'      => 'familyA3',
            'variant_attribute_sets' => [
                [
                    'level' => 1,
                    'axes' => ['a_simple_select'],
                    'attributes' => ['a_text'],
                ],
                [
                    'level' => 2,
                    'axes' => ['a_yes_no'],
                    'attributes' => ['sku', 'a_localized_and_scopable_text_area'],
                ],
            ]
        ]);

        $rootProductModel = $builder->createProductModel(
            'root_product_model_two_level',
            'familyVariantA1',
            null,
            []
        );

        $subProductModel = $builder->createProductModel(
            'sub_product_model',
            'familyVariantA1',
            $rootProductModel,
            [
                'values'  => [
                    'a_simple_select'  => [['data' => 'optionA', 'locale' => null, 'scope' => null]],
                    'a_text'  => [['data' => 'text', 'locale' => null, 'scope' => null]],
                ]
            ]
        );

        // en_US ecommerce complete
        // en_US tablet incomplete (variant_product_2)
        // fr_FR ecommerce incomplete (variant_product_1, variant_product_2)
        // fr_FR tablet complete
        $builder->createVariantProduct(
            'variant_product_1',
            'familyVariantA1',
            'familyVariantA1',
            $subProductModel,
            [
                'sku'  => [['data' => 'm', 'locale' => null, 'scope' => null]],
                'a_yes_no'  => [['data' => '12345678', 'locale' => null, 'scope' => null]],
                'a_localized_and_scopable_text_area'  => [
                    ['data' => 'my text', 'locale' => 'en_US', 'scope' => 'ecommerce'],
                    ['data' => 'my text', 'locale' => 'en_US', 'scope' => 'tablet'],
                    ['data' => null, 'locale' => 'fr_FR', 'scope' => 'ecommerce'],
                    ['data' => 'my text', 'locale' => 'fr_FR', 'scope' => 'tablet'],
                ],
            ]
        );

        $builder->createVariantProduct(
            'variant_product_2',
            'familyVariantA1',
            'familyVariantA1',
            $subProductModel,
            [
                'sku'  => [['data' => 'm', 'locale' => null, 'scope' => null]],
                'a_yes_no'  => [['data' => '12345678', 'locale' => null, 'scope' => null]],
                'a_localized_and_scopable_text_area'  => [
                    ['data' => 'my text', 'locale' => 'en_US', 'scope' => 'ecommerce'],
                    ['data' => null, 'locale' => 'en_US', 'scope' => 'tablet'],
                    ['data' => null, 'locale' => 'fr_FR', 'scope' => 'ecommerce'],
                    ['data' => 'my text', 'locale' => 'fr_FR', 'scope' => 'tablet'],
                ],
            ]
        );

        $rootProductModelOneLevel = $builder->createProductModel(
            'root_product_model_two_level',
            'familyVariantA1',
            null,
            []
        );

        // en_US ecommerce complete
        // en_US tablet incomplete (variant_product_3)
        // fr_FR ecommerce incomplete (variant_product_3, variant_product_4)
        // fr_FR tablet complete
        $builder->createVariantProduct(
            'variant_product_3',
            'familyVariantA1',
            'familyVariantA1',
            $rootProductModelOneLevel,
            [
                'sku' => [['data' => 'm', 'locale' => null, 'scope' => null]],
                'a_yes_no' => [['data' => '12345678', 'locale' => null, 'scope' => null]],
                'a_localized_and_scopable_text_area' => [
                    ['data' => 'my text', 'locale' => 'en_US', 'scope' => 'ecommerce'],
                    ['data' => 'my text', 'locale' => 'en_US', 'scope' => 'tablet'],
                    ['data' => null, 'locale' => 'fr_FR', 'scope' => 'ecommerce'],
                    ['data' => 'my text', 'locale' => 'fr_FR', 'scope' => 'tablet'],
                ],
            ]
        );

        $builder->createVariantProduct(
            'variant_product_4',
            'familyVariantA1',
            'familyVariantA1',
            $rootProductModelOneLevel,
            [
                'sku'  => [['data' => 'm', 'locale' => null, 'scope' => null]],
                'a_yes_no'  => [['data' => '12345678', 'locale' => null, 'scope' => null]],
                'a_localized_and_scopable_text_area'  => [
                    ['data' => 'my text', 'locale' => 'en_US', 'scope' => 'ecommerce'],
                    ['data' => null, 'locale' => 'en_US', 'scope' => 'tablet'],
                    ['data' => null, 'locale' => 'fr_FR', 'scope' => 'ecommerce'],
                    ['data' => 'my text', 'locale' => 'fr_FR', 'scope' => 'tablet'],
                ],
            ]
        );
    }
}