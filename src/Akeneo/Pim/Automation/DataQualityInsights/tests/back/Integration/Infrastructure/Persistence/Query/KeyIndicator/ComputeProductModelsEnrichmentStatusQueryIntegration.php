<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Infrastructure\Persistence\Query\KeyIndicator;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluateProductModels;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductIdCollection;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\DataQualityInsightsTestCase;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ComputeProductModelsEnrichmentStatusQueryIntegration extends DataQualityInsightsTestCase
{
    public function test_it_computes_product_models_enrichment_status()
    {
        $this->createChannel('ecommerce', ['locales' => ['en_US', 'fr_FR']]);
        $this->createChannel('mobile', ['locales' => ['en_US']]);

        foreach (['name', 'title', 'weight'] as $attribute) {
            $this->createAttribute($attribute, ['scopable' => false]);
        }
        $this->createAttribute('description', ['scopable' => true]);
        $this->createAttribute('size', ['type' => AttributeTypes::OPTION_SIMPLE_SELECT, 'scopable' => false]);

        $this->createFamily(
            'family_with_3_attributes',
            [
                'attributes' => ['sku', 'name', 'description', 'size'],
                'attribute_requirements' => ['ecommerce' => ['sku'], 'mobile' => ['sku', 'name']],
            ],
        );
        $this->createFamily(
            'family_with_5_attributes',
            [
                'attributes' => ['sku', 'name', 'title', 'description', 'weight', 'size'],
                'attribute_requirements' => ['ecommerce' => ['sku', 'name', 'title'], 'mobile' => ['sku']]
            ]
        );

        $expectedProductModelsEnrichmentStatus = [];
        $expectedProductModelsEnrichmentStatus += $this->givenProductModelSampleA();
        $expectedProductModelsEnrichmentStatus += $this->givenProductModelSampleB();
        $expectedProductModelsEnrichmentStatus += $this->givenProductModelWithoutEvaluations();
        $expectedProductModelsEnrichmentStatus += $this->givenProductModelWithoutEvaluationResults();
        $this->givenNotInvolvedProductModel();

        $productModelIds = array_keys($expectedProductModelsEnrichmentStatus);

        $productModelsEnrichmentStatus = $this->get('akeneo.pim.automation.data_quality_insights.query.compute_product_models_enrichment_status_query')
            ->compute(ProductIdCollection::fromInts($productModelIds));

        $this->assertEquals($expectedProductModelsEnrichmentStatus, $productModelsEnrichmentStatus);
    }

    private function givenProductModelSampleA(): array
    {
        $this->createFamilyVariant('family_V', 'family_with_3_attributes', [
            'variant_attribute_sets' => [
                [
                    'level' => 1,
                    'axes' => ['size'],
                    'attributes' => ['sku'],
                ],
            ]
        ]);

        $productModelId = $this->createProductModel('sample_A', 'family_V', [
            'values' => [
                'name' => [['scope' => null, 'locale' => null, 'data' => 'Sample A']],
                'description' => [['scope' => 'mobile', 'locale' => null, 'data' => 'Sample A']],
            ]
        ])->getId();

        ($this->get(EvaluateProductModels::class))(ProductIdCollection::fromInt($productModelId));

        $expectedEnrichmentStatus = [
            $productModelId => [
                'ecommerce' => [
                    'en_US' => false,
                    'fr_FR' => false,
                ],
                'mobile' => [
                    'en_US' => true,
                ]
            ]
        ];

        return $expectedEnrichmentStatus;
    }

    private function givenProductModelSampleB(): array
    {
        $this->createFamilyVariant('family_V1', 'family_with_5_attributes', [
            'variant_attribute_sets' => [
                [
                    'level' => 1,
                    'axes' => ['size'],
                    'attributes' => ['sku'],
                ],
            ]
        ]);

        $productModelId = $this->createProductModel('sample_B', 'family_V1', [
            'values' => [
                'name' => [['scope' => null, 'locale' => null, 'data' => 'Sample A']],
                'title' => [['scope' => null, 'locale' => null, 'data' => 'Sample A']],
                'description' => [['scope' => 'ecommerce', 'locale' => null, 'data' => 'Sample A']],
            ]
        ])->getId();

        ($this->get(EvaluateProductModels::class))(ProductIdCollection::fromInt($productModelId));

        $expectedEnrichmentStatus = [
            $productModelId => [
                'ecommerce' => [
                    'en_US' => true,
                    'fr_FR' => true,
                ],
                'mobile' => [
                    'en_US' => false,
                ]
            ]
        ];

        return $expectedEnrichmentStatus;
    }

    private function givenNotInvolvedProductModel(): void
    {
        $this->createMinimalFamilyAndFamilyVariant('a_family', 'a_family_variant');

        $this->createProductModel('not_involved_product_model', 'a_family_variant')->getId();
    }

    private function givenProductModelWithoutEvaluations(): array
    {
        $this->createFamilyVariant('family_V2', 'family_with_5_attributes', [
            'variant_attribute_sets' => [
                [
                    'level' => 1,
                    'axes' => ['size'],
                    'attributes' => ['sku'],
                ],
            ]
        ]);

        $productModelWithoutEvaluationsId = $this->createProductModelWithoutEvaluations('product_model_without_evaluations', 'family_V2')->getId();

        return [
            $productModelWithoutEvaluationsId => [
                'ecommerce' => [
                    'en_US' => null,
                    'fr_FR' => null,
                ],
                'mobile' => [
                    'en_US' => null,
                ]
            ]
        ];
    }

    private function givenProductModelWithoutEvaluationResults(): array
    {
        $this->createFamilyVariant('family_V3', 'family_with_3_attributes', [
            'variant_attribute_sets' => [
                [
                    'level' => 1,
                    'axes' => ['size'],
                    'attributes' => ['sku'],
                ],
            ]
        ]);

        $productModelId = $this->createProductModel('product_model_without_results', 'family_V3', [
            'values' => [
                'name' => [['scope' => null, 'locale' => null, 'data' => 'Sample A']],
                'description' => [['scope' => 'mobile', 'locale' => null, 'data' => 'Sample A']],
            ]
        ])->getId();

        $this->get('database_connection')->executeQuery(<<<SQL
UPDATE pim_data_quality_insights_product_model_criteria_evaluation
SET result = null, evaluated_at = null, status = 'pending' 
WHERE product_id = :productModelId;
SQL,
            ['productModelId' => $productModelId],
            ['productId' => \PDO::PARAM_INT]
        );

        return [
            $productModelId => [
                'ecommerce' => [
                    'en_US' => null,
                    'fr_FR' => null,
                ],
                'mobile' => [
                    'en_US' => null,
                ]
            ]
        ];
    }
}
