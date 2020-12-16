<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEnrichment\GetProductModelRawValuesQuery;
use Akeneo\Test\Integration\TestCase;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetProductModelRawValuesQueryIntegration extends TestCase
{
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    public function test_it_returns_the_raw_values_of_a_product_model_without_parent()
    {
        $productModelId = $this->givenAProductModelWithoutParent([
            'a_localized_and_scopable_text_area' => [
                [
                    'data' => 'My fancy pink tshirt',
                    'locale' => 'en_US',
                    'scope' => 'ecommerce',
                ],
                [
                    'data' => 'Pink tshirt',
                    'locale' => 'en_US',
                    'scope' => 'mobile',
                ],
            ],
            'a_text' => [
                [
                    'data' => 'some text',
                    'locale' => null,
                    'scope' => null,
                ],
            ],
        ]);

        $expectedRawValues = [
            'a_localized_and_scopable_text_area' => [
                'ecommerce' => [
                    'en_US' => 'My fancy pink tshirt',
                ],
                'mobile' => [
                    'en_US' => 'Pink tshirt',
                ],
            ],
            'a_text' => [
                '<all_channels>' => [
                    '<all_locales>' => 'some text'
                ],
            ]
        ];

        $rawValues = $this
            ->get(GetProductModelRawValuesQuery::class)
            ->execute($productModelId);

        $this->assertProductHasRawValues($expectedRawValues, $rawValues);
    }

    public function test_it_returns_the_raw_values_of_a_product_model_with_a_parent()
    {
        $this->givenAProductModelParent('product_model_parent', [
            'a_localized_and_scopable_text_area' => [
                [
                    'data' => 'My fancy pink tshirt',
                    'locale' => 'en_US',
                    'scope' => 'ecommerce',
                ],
                [
                    'data' => 'Pink tshirt',
                    'locale' => 'en_US',
                    'scope' => 'mobile',
                ],
            ],
        ]);

        $productModelId = $this->givenAProductModelWithParent('product_model_parent', [
            'a_text' => [
                [
                    'data' => 'some text',
                    'locale' => null,
                    'scope' => null,
                ],
            ],
        ]);

        $expectedRawValues = [
            'a_localized_and_scopable_text_area' => [
                'ecommerce' => [
                    'en_US' => 'My fancy pink tshirt',
                ],
                'mobile' => [
                    'en_US' => 'Pink tshirt',
                ],
            ],
            'a_text' => [
                '<all_channels>' => [
                    '<all_locales>' => 'some text'
                ],
            ]
        ];

        $rawValues = $this
            ->get(GetProductModelRawValuesQuery::class)
            ->execute($productModelId);

        $this->assertProductHasRawValues($expectedRawValues, $rawValues);
    }

    private function givenAProductModelWithoutParent(array $values): ProductId
    {
        $productModel = $this->get('akeneo_integration_tests.catalog.product_model.builder')
            ->withCode('a_product_model')
            ->withFamilyVariant('familyVariantA2')
            ->build();

        $this->get('pim_catalog.updater.product_model')->update($productModel, ['values' => $values]);
        $this->get('pim_catalog.saver.product_model')->save($productModel);

        return new ProductId($productModel->getId());
    }

    private function givenAProductModelWithParent(string $parentCode, array $values): ProductId
    {
        $productModel = $this->get('akeneo_integration_tests.catalog.product_model.builder')
            ->withCode('a_sub_product_model')
            ->withFamilyVariant('familyVariantA1')
            ->withParent($parentCode)
            ->build();

        $this->get('pim_catalog.updater.product_model')->update($productModel, ['values' => $values]);
        $this->get('pim_catalog.saver.product_model')->save($productModel);

        return new ProductId($productModel->getId());
    }

    private function givenAProductModelParent(string $code, array $values): ProductId
    {
        $productModel = $this->get('akeneo_integration_tests.catalog.product_model.builder')
            ->withCode($code)
            ->withFamilyVariant('familyVariantA1')
            ->build();

        $this->get('pim_catalog.updater.product_model')->update($productModel, ['values' => $values]);
        $this->get('pim_catalog.saver.product_model')->save($productModel);

        return new ProductId($productModel->getId());
    }

    private function assertProductHasRawValues(array $expectedRawValues, $productRawValues): void
    {
        foreach ($expectedRawValues as $attributeCode => $expectedAttributeRawValues) {
            $this->assertArrayHasKey($attributeCode, $productRawValues);
            $this->assertEquals($expectedAttributeRawValues, $productRawValues[$attributeCode]);
        }
    }
}
