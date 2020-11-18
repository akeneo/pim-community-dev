<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Persistence\Query\KeyIndicator;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\KeyIndicator\ComputeProductsEnrichmentStatusQuery;
use Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\DataQualityInsightsTestCase;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ComputeProductsEnrichmentStatusQueryIntegration extends DataQualityInsightsTestCase
{
    public function test_it_computes_products_enrichment_status()
    {
        $this->createChannel('ecommerce', ['locales' => ['en_US', 'fr_FR']]);
        $this->createChannel('mobile', ['locales' => ['en_US']]);

        foreach (['name', 'title', 'weight'] as $attribute) {
            $this->createAttribute($attribute, ['scopable' => false]);
        }
        $this->createAttribute('description', ['scopable' => true]);

        $this->createFamily(
            'family_with_3_attributes',
            [
                'attributes' => ['sku', 'name', 'description'],
                'attribute_requirements' => ['ecommerce' => ['sku'], 'mobile' => ['sku', 'name']],
            ],
        );
        $this->createFamily(
            'family_with_5_attributes',
            [
                'attributes' => ['sku', 'name', 'title', 'description', 'weight'],
                'attribute_requirements' => ['ecommerce' => ['sku', 'name', 'title'], 'mobile' => ['sku']]
            ]
        );

        $expectedProductsEnrichmentStatus = [];
        $expectedProductsEnrichmentStatus += $this->givenProductSampleA();
        $expectedProductsEnrichmentStatus += $this->givenProductSampleB();
        $expectedProductsEnrichmentStatus += $this->givenProductWithoutEvaluations();
        $this->givenNotInvolvedProduct();

        $productIds = array_keys($expectedProductsEnrichmentStatus);
        $productIds = array_map(fn(int $productId) => new ProductId($productId), $productIds);

        $productsEnrichmentStatus = $this->get(ComputeProductsEnrichmentStatusQuery::class)->compute($productIds);

        $this->assertEquals($expectedProductsEnrichmentStatus, $productsEnrichmentStatus);
    }

    private function givenProductSampleA(): array
    {
        $productId = $this->createProduct('sample_A', [
            'family' => 'family_with_3_attributes',
            'values' => [
                'sku' => [['scope' => null, 'locale' => null, 'data' => '123456789']],
                'name' => [['scope' => null, 'locale' => null, 'data' => 'Sample A']],
                'description' => [['scope' => 'mobile', 'locale' => null, 'data' => 'Sample A']],
            ]
        ])->getId();

        $expectedEnrichmentStatus = [$productId => [
            'ecommerce' => [
                'en_US' => false,
                'fr_FR' => false,
            ],
            'mobile' => [
                'en_US' => true,
            ]
        ]];

        return $expectedEnrichmentStatus;
    }

    private function givenProductSampleB(): array
    {
        $productId = $this->createProduct('sample_B', [
            'family' => 'family_with_5_attributes',
            'values' => [
                'sku' => [['scope' => null, 'locale' => null, 'data' => '987654321']],
                'name' => [['scope' => null, 'locale' => null, 'data' => 'Sample A']],
                'title' => [['scope' => null, 'locale' => null, 'data' => 'Sample A']],
                'description' => [['scope' => 'ecommerce', 'locale' => null, 'data' => 'Sample A']],
            ]
        ])->getId();

        $expectedEnrichmentStatus = [$productId => [
            'ecommerce' => [
                'en_US' => true,
                'fr_FR' => true,
            ],
            'mobile' => [
                'en_US' => false,
            ]
        ]];

        return $expectedEnrichmentStatus;
    }

    private function givenNotInvolvedProduct(): void
    {
        $this->createProduct('not_involved_product', ['family' => 'family_with_5_attributes'])->getId();
    }

    private function givenProductWithoutEvaluations(): array
    {
        $productWithoutEvaluationsId = $this->createProductWithoutEvaluations(
            'product_without_evaluations',
            ['family' => 'family_with_5_attributes']
        )->getId();

        return [$productWithoutEvaluationsId => [
            'ecommerce' => [
                'en_US' => null,
                'fr_FR' => null,
            ],
            'mobile' => [
                'en_US' => null,
            ]
        ]];
    }
}
