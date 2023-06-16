<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\tests\back\Integration\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\EvaluateCompletenessOfNonRequiredAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluateProductModels;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductModelIdFactory;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\CriterionEvaluationResult;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation\GetEvaluationResultsByProductModelsAndCriterionQuery;
use Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\DataQualityInsightsTestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetEvaluationResultsByProductModelsAndCriterionQueryIntegration extends DataQualityInsightsTestCase
{
    public function test_it_gets_evaluation_results_by_products_and_criterion(): void
    {
        $this->createChannel('ecommerce', ['locales' => ['en_US', 'fr_FR']]);
        $this->createAttribute('name', ['scopable' => true, 'localizable' => true]);
        $this->createAttribute('image', ['scopable' => true, 'localizable' => false]);
        $this->createSimpleSelectAttributeWithOptions('color', ['red', 'blue']);

        $this->createFamily('a_family', ['attributes' => ['name', 'color', 'image']]);
        $this->createFamilyVariant('a_family_variant', 'a_family', [
            'variant_attribute_sets' => [
                [
                    'level' => 1,
                    'axes' => ['color'],
                    'attributes' => ['image'],
                ],
            ],
        ]);

        $productModelWithEvaluation = $this->givenAnEvaluatedProductModel();
        $productModelWithPendingEvaluation = $this->givenAProductModelWithPendingEvaluation();
        $productModelWithoutAnyEvaluation = $this->givenAProductModelWithoutAnyEvaluation();

        $productModelIdCollection = $this->get(ProductModelIdFactory::class)->createCollection([
            (string)$productModelWithEvaluation,
            (string)$productModelWithPendingEvaluation,
            (string)$productModelWithoutAnyEvaluation
        ]);

        $results = $this->get(GetEvaluationResultsByProductModelsAndCriterionQuery::class)->execute(
            $productModelIdCollection,
            new CriterionCode(EvaluateCompletenessOfNonRequiredAttributes::CRITERION_CODE)
        );

        $this->assertArrayHasKey($productModelWithEvaluation, $results, 'There should be an element for the evaluated product model in the results');
        $this->assertInstanceOf(CriterionEvaluationResult::class, $results[(string)$productModelWithEvaluation], 'The result for the evaluated product model should be an instance of CriterionEvaluationResult');

        $this->assertArrayHasKey($productModelWithPendingEvaluation, $results, 'There should be an element for the product model with pending evaluation in the results');
        $this->assertNull($results[$productModelWithPendingEvaluation], 'The result for the product model with pending evaluation should be null');

        $this->assertArrayNotHasKey($productModelWithoutAnyEvaluation, $results, 'There should not be a result for the product model without evaluation');
    }

    private function givenAnEvaluatedProductModel(): int
    {
        $productModelId = $this->createProductModel('an_evaluated_product_model', 'a_family_variant', [
            'values' => [
                'name' => [
                    ['scope' => 'ecommerce', 'locale' => 'en_US', 'data' => 'Foo'],
                    ['scope' => 'ecommerce', 'locale' => 'fr_FR', 'data' => 'Bar'],
                ],
            ]
        ])->getId();

        $productModelIdCollection = $this->get(ProductModelIdFactory::class)->createCollection([(string)$productModelId]);
        $this->get(EvaluateProductModels::class)->forPendingCriteria($productModelIdCollection);

        return $productModelId;
    }

    private function givenAProductModelWithPendingEvaluation(): int
    {
        return $this->createProductModel('a_product_model_with_pending_evaluation', 'a_family_variant')->getId();
    }

    private function givenAProductModelWithoutAnyEvaluation(): int
    {
        $productModelId = $this->createProductModel('a_product_model_without_evaluation', 'a_family_variant')->getId();
        $this->deleteProductModelCriterionEvaluations($productModelId);

        return $productModelId;
    }
}
