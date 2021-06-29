<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Enrichment;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Persistence\Query\Completeness\CompletenessTestCase;
use Akeneo\Pim\Structure\Component\AttributeTypes;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * The goal of this test is not to cover all the cases of the completeness calculation,
 *  but to test that the integration with the bounded context Enrichment is working.
 */
class CalculateProductModelCompletenessIntegration extends CompletenessTestCase
{
    public function test_it_calculates_the_completeness_of_the_required_attributes_for_a_product_model()
    {
        $this->givenAttributes([
            ['code' => 'a_non_required_text', 'type' => AttributeTypes::TEXT],
            ['code' => 'a_required_text', 'type' => AttributeTypes::TEXT, 'scopable' => true, 'localizable' => true],
            ['code' => 'a_required_textarea', 'type' => AttributeTypes::TEXTAREA, 'scopable' => true, 'localizable' => true],
            ['code' => 'a_required_variant_text', 'type' => AttributeTypes::TEXT, 'scopable' => true, 'localizable' => true],
            ['code' => 'a_variation_axis', 'type' => AttributeTypes::OPTION_SIMPLE_SELECT],
        ]);

        $this->givenFamilies([
            [
                'code' => 'familyA',
                'attribute_codes' => [
                    'sku',
                    'a_non_required_text',
                    'a_required_text',
                    'a_required_textarea',
                    'a_required_variant_text',
                    'a_variation_axis',
                ],
                'attribute_requirements' => [
                    'ecommerce' => [
                        'sku',
                        'a_required_text',
                        'a_required_textarea',
                        'a_required_variant_text',
                        'a_variation_axis',
                    ],
                ]
            ],
        ]);

        $this->givenAFamilyVariant([
            'code' => 'familyA_variant1',
            'family' => 'familyA',
            'variant_attribute_sets' => [
                [
                    'level' => 1,
                    'axes' => ['a_variation_axis'],
                    'attributes' => ['a_variation_axis', 'sku', 'a_required_variant_text'],
                ],
            ],
        ]);

        $productModel = $this->get('akeneo_integration_tests.catalog.product_model.builder')
            ->withCode('a_product_model')
            ->withFamilyVariant('familyA_variant1')
            ->build();

        $this->get('pim_catalog.saver.product_model')->save($productModel);
        $productModelId = new ProductId((int) $productModel->getId());

        $completenessResult = $this->get('akeneo.pim.automation.calculate_product_model_completeness_of_required_attributes')
            ->calculate($productModelId);

        $expectedMissingAttributes = ['ecommerce' => ['en_US' => ['a_required_text', 'a_required_textarea']]];
        $this->assertEquals($expectedMissingAttributes, $completenessResult->getMissingAttributes()->toArray());

        $expectedRates = ['ecommerce' => ['en_US' => 0]];
        $this->assertEquals($expectedRates, $completenessResult->getRates()->toArrayInt());

        $this->get('pim_catalog.updater.product_model')->update($productModel, [
            'values' => [
                'a_required_text' => [
                    [
                        'data' => 'Whatever',
                        'scope' => 'ecommerce',
                        'locale' => 'en_US',
                    ],
                ]
            ]
        ]);
        $this->get('pim_catalog.saver.product_model')->save($productModel);

        $completenessResult = $this->get('akeneo.pim.automation.calculate_product_model_completeness_of_required_attributes')
            ->calculate($productModelId);

        $expectedMissingAttributes = ['ecommerce' => ['en_US' => ['a_required_textarea']]];
        $this->assertEquals($expectedMissingAttributes, $completenessResult->getMissingAttributes()->toArray());

        $expectedRates = ['ecommerce' => ['en_US' => 50]];
        $this->assertEquals($expectedRates, $completenessResult->getRates()->toArrayInt());
    }
}
