<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\tests\back\Integration\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\EvaluateCompletenessOfRequiredAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluateProducts;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductUuidFactory;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\CriterionEvaluationResult;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation\GetEvaluationResultsByProductsAndCriterionQuery;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\DataQualityInsightsTestCase;
use Ramsey\Uuid\UuidInterface;

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
            $this->get(ProductUuidFactory::class)->createCollection([
                (string) $productWithEvaluation,
                (string) $productWithPendingEvaluation,
                (string) $productWithoutAnyEvaluation,
            ]),
            new CriterionCode(EvaluateCompletenessOfRequiredAttributes::CRITERION_CODE)
        );

        $this->assertArrayHasKey((string) $productWithEvaluation, $results, 'There should be an element for the evaluated product in the results');
        $this->assertInstanceOf(CriterionEvaluationResult::class, $results[(string) $productWithEvaluation], 'The result for the evaluated product should be an instance of CriterionEvaluationResult');

        $this->assertArrayHasKey((string) $productWithPendingEvaluation, $results, 'There should be an element for the product with pending evaluation in the results');
        $this->assertNull($results[(string) $productWithPendingEvaluation], 'The result for the product with pending evaluation should be null');

        $this->assertArrayNotHasKey((string) $productWithoutAnyEvaluation, $results, 'There should not be result for the product without evaluation');
    }

    private function givenAnEvaluatedProduct(): UuidInterface
    {
        $productUuid = $this->createProduct('an_evaluated_product', [
            new SetFamily('a_family'),
            new SetTextValue('name', 'ecommerce', 'en_US', 'Foo'),
            new SetTextValue('name', 'ecommerce', 'fr_FR', 'Bar'),
        ])->getUuid();

        $productIdCollection = $this->get(ProductUuidFactory::class)->createCollection([(string)$productUuid]);
        ($this->get(EvaluateProducts::class))($productIdCollection);

        return $productUuid;
    }

    private function givenAProductWithPendingEvaluation(): UuidInterface
    {
        return $this->createProduct('a_product_with_pending_evaluation', [new SetFamily('a_family')])->getUuid();
    }

    private function givenAProductWithoutAnyEvaluation(): UuidInterface
    {
        $productUuid = $this->createProduct('a_product_without_evaluation', [new SetFamily('a_family')])->getUuid();
        $this->deleteProductCriterionEvaluations($productUuid);

        return $productUuid;
    }
}
