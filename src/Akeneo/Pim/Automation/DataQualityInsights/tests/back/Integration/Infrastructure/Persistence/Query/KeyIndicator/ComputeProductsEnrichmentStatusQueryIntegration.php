<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Infrastructure\Persistence\Query\KeyIndicator;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluateProducts;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductUuidFactory;
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
        $expectedProductsEnrichmentStatus += $this->givenProductWithoutEvaluationResults();
        $this->givenNotInvolvedProduct();

        $productUuids = array_keys($expectedProductsEnrichmentStatus);

        $productUuidCollection = $this->get(ProductUuidFactory::class)->createCollection(array_map(fn($productUuid) => (string) $productUuid, $productUuids));
        $productsEnrichmentStatus = $this->get('akeneo.pim.automation.data_quality_insights.query.compute_products_enrichment_status_query')
            ->compute($productUuidCollection);

        $this->assertEquals($expectedProductsEnrichmentStatus, $productsEnrichmentStatus);
    }

    private function givenProductSampleA(): array
    {
        $productUuid = $this->createProduct('sample_A', [
            'family' => 'family_with_3_attributes',
            'values' => [
                'name' => [['scope' => null, 'locale' => null, 'data' => 'Sample A']],
                'description' => [['scope' => 'mobile', 'locale' => null, 'data' => 'Sample A']],
            ]
        ])->getUuid()->toString();

        $productUuidCollection = $this->get(ProductUuidFactory::class)->createCollection([$productUuid]);

        $this->get(EvaluateProducts::class)->forPendingCriteria($productUuidCollection);

        $expectedEnrichmentStatus = [$productUuid => [
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
        $productUuid = $this->createProduct('sample_B', [
            'family' => 'family_with_5_attributes',
            'values' => [
                'name' => [['scope' => null, 'locale' => null, 'data' => 'Sample A']],
                'title' => [['scope' => null, 'locale' => null, 'data' => 'Sample A']],
                'description' => [['scope' => 'ecommerce', 'locale' => null, 'data' => 'Sample A']],
            ]
        ])->getUuid()->toString();

        $productIdCollection = $this->get(ProductUuidFactory::class)->createCollection([$productUuid]);
        $this->get(EvaluateProducts::class)->forPendingCriteria($productIdCollection);

        $expectedEnrichmentStatus = [$productUuid => [
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
        $this->createProduct('not_involved_product', ['family' => 'family_with_5_attributes']);
    }

    private function givenProductWithoutEvaluations(): array
    {
        $productWithoutEvaluationsUuid = $this->createProductWithoutEvaluations(
            'product_without_evaluations',
            ['family' => 'family_with_5_attributes']
        )->getUuid()->toString();

        return [$productWithoutEvaluationsUuid => [
            'ecommerce' => [
                'en_US' => null,
                'fr_FR' => null,
            ],
            'mobile' => [
                'en_US' => null,
            ]
        ]];
    }

    private function givenProductWithoutEvaluationResults(): array
    {
        $product = $this->createProduct('product_without_results', [
            'family' => 'family_with_3_attributes',
            'values' => [
                'name' => [['scope' => null, 'locale' => null, 'data' => 'Sample A']],
                'description' => [['scope' => 'mobile', 'locale' => null, 'data' => 'Sample A']],
            ]
        ]);

        $this->get('database_connection')->executeQuery(<<<SQL
UPDATE pim_data_quality_insights_product_criteria_evaluation
SET result = null, evaluated_at = null, status = 'pending' 
WHERE product_uuid = :productUuid;
SQL,
            ['productUuid' => $product->getUuid()->getBytes()]
        );

        return [$product->getUuid()->toString() => [
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
