<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\tests\back\Integration\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\EvaluateCompletenessOfRequiredAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluateProducts;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductIdFactory;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\CriterionEvaluationResult;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductIdCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation\GetEvaluationResultsByProductsAndCriterionQuery;
use Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\DataQualityInsightsTestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetEvaluationResultsByProductsAndCriterionQueryIntegration extends DataQualityInsightsTestCase
{
    public function test_it_gets_evaluation_results_by_products_and_criterion(): void
    {
        $this->createChannel('ecommerce', ['locales' => ['en_US', 'fr_FR']]);
        $this->createAttribute('name', ['scopable' => true, 'localizable' => true]);
        $this->createFamily('a_family', ['attributes' => ['name']]);

        $productWithEvaluation = $this->givenAnEvaluatedProduct();
        $productWithPendingEvaluation = $this->givenAProductWithPendingEvaluation();
        $productWithoutAnyEvaluation = $this->givenAProductWithoutAnyEvaluation();

        $results = $this->get(GetEvaluationResultsByProductsAndCriterionQuery::class)->execute(
            ProductIdCollection::fromInts([$productWithEvaluation, $productWithPendingEvaluation, $productWithoutAnyEvaluation]),
            new CriterionCode(EvaluateCompletenessOfRequiredAttributes::CRITERION_CODE)
        );

        $this->assertArrayHasKey($productWithEvaluation, $results, 'There should be an element for the evaluated product in the results');
        $this->assertInstanceOf(CriterionEvaluationResult::class, $results[$productWithEvaluation], 'The result for the evaluated product should be an instance of CriterionEvaluationResult');

        $this->assertArrayHasKey($productWithPendingEvaluation, $results, 'There should be an element for the product with pending evaluation in the results');
        $this->assertNull($results[$productWithPendingEvaluation], 'The result for the product with pending evaluation should be null');

        $this->assertArrayNotHasKey($productWithoutAnyEvaluation, $results, 'There should not be result for the product without evaluation');
    }

    private function givenAnEvaluatedProduct(): int
    {
        $productId = $this->createProduct('an_evaluated_product', [
            'family' => 'a_family',
            'values' => [
                'name' => [
                    ['scope' => 'ecommerce', 'locale' => 'en_US', 'data' => 'Foo'],
                    ['scope' => 'ecommerce', 'locale' => 'fr_FR', 'data' => 'Bar'],
                ],
            ]
        ])->getId();

        $productIdCollection = $this->get(ProductIdFactory::class)->createCollection([(string)$productId]);
        ($this->get(EvaluateProducts::class))($productIdCollection);

        return $productId;
    }

    private function givenAProductWithPendingEvaluation(): int
    {
        return $this->createProduct('a_product_with_pending_evaluation', ['family' => 'a_family'])->getId();
    }

    private function givenAProductWithoutAnyEvaluation(): int
    {
        $productId = $this->createProduct('a_product_without_evaluation', ['family' => 'a_family'])->getId();
        $this->deleteProductCriterionEvaluations($productId);

        return $productId;
    }
}
