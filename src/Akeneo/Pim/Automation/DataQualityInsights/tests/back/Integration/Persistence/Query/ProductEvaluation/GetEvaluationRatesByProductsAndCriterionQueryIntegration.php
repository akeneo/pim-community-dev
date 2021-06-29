<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\CriterionEvaluationRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation\GetEvaluationRatesByProductsAndCriterionQuery;
use Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\DataQualityInsightsTestCase;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetEvaluationRatesByProductsAndCriterionQueryIntegration extends DataQualityInsightsTestCase
{
    private CriterionEvaluationRepositoryInterface $productCriterionEvaluationRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productCriterionEvaluationRepository = $this->get('akeneo.pim.automation.data_quality_insights.repository.product_criterion_evaluation');
    }

    public function test_it_gives_the_evaluation_rates_of_a_given_product_and_criterion()
    {
        $this->createChannel('ecommerce', ['locales' => ['en_US', 'fr_FR']]);
        $this->createChannel('mobile', ['locales' => ['en_US']]);

        $expectedEvaluationRates = [];
        $expectedEvaluationRates += $this->givenSampleA();
        $expectedEvaluationRates += $this->givenSampleB();
        $expectedEvaluationRates += $this->givenAProductNotEvaluatedYet('spelling');

        $this->givenANotInvolvedProduct();

        $productIds = array_keys($expectedEvaluationRates);
        $productIds[] = 12345; // Unknown product
        $productIds = array_map(fn(int $productId) => new ProductId($productId), $productIds);

        $evaluationRates = $this->get(GetEvaluationRatesByProductsAndCriterionQuery::class)
            ->toArrayInt($productIds, new CriterionCode('spelling'));

        $this->assertEquals($expectedEvaluationRates, $evaluationRates);
    }

    private function givenSampleA(): array
    {
        $product = $this->createProductWithoutEvaluations('product_A');

        $expectedRates = [
            'ecommerce' => [
                'en_US' => 100,
                'fr_FR' => 50,
            ],
            'mobile' => [
                'en_US' => 76,
            ]
        ];
        $evaluationResults['spelling'] = $this->buildEvaluationResult($expectedRates);
        $evaluationResults['whatever'] = $this->buildEvaluationResult([
            'ecommerce' => [
                'en_US' => 54,
                'fr_FR' => 0,
            ],
            'mobile' => [
                'en_US' => 42,
            ]
        ]);

        $this->saveEvaluationResults($product->getId(), $evaluationResults);

        return [$product->getId() => $expectedRates];
    }

    private function givenSampleB(): array
    {
        $product = $this->createProductWithoutEvaluations('product_B');

        $expectedRates = [
            'ecommerce' => [
                'en_US' => 43,
                'fr_FR' => 87,
            ],
            'mobile' => [
                'en_US' => 0,
            ]
        ];
        $evaluationResults['spelling'] = $this->buildEvaluationResult($expectedRates);

        $this->saveEvaluationResults($product->getId(), $evaluationResults);

        return [$product->getId() => $expectedRates];
    }

    private function givenANotInvolvedProduct(): void
    {
        $product = $this->createProductWithoutEvaluations('whatever');

        $evaluationResults['spelling'] = $this->buildEvaluationResult([
            'ecommerce' => [
                'en_US' => 98,
                'fr_FR' => 1,
            ],
            'mobile' => [
                'en_US' => 47,
            ]
        ]);

        $this->saveEvaluationResults($product->getId(), $evaluationResults);
    }

    private function givenAProductNotEvaluatedYet(string $criterionCode): array
    {
        $productId = $this->createProduct('not_evaluated_product')->getId();

        $this->get('database_connection')->executeQuery(<<<SQL
REPLACE INTO pim_data_quality_insights_product_criteria_evaluation (product_id, criterion_code, evaluated_at, status, result) 
VALUES (:productId, :criterionCode, null, 'pending', null);
SQL,
            [
                'productId' => $productId,
                'criterionCode' => $criterionCode,
            ]
        );

        return [$productId => []];
    }

    private function saveEvaluationResults(int $productId, array $evaluationResults): void
    {
        $productId = new ProductId($productId);
        $evaluations = new Write\CriterionEvaluationCollection();

        foreach ($evaluationResults as $criterion => $evaluationResult) {
            $evaluation = new Write\CriterionEvaluation(
                new CriterionCode($criterion),
                $productId,
                CriterionEvaluationStatus::done()
            );
            $evaluation->end($evaluationResult);
            $evaluations->add($evaluation);
        }

        $this->productCriterionEvaluationRepository->create($evaluations);
        $this->productCriterionEvaluationRepository->update($evaluations);
    }

    private function buildEvaluationResult(array $rates): Write\CriterionEvaluationResult
    {
        $evaluationResult = new Write\CriterionEvaluationResult();

        foreach ($rates as $channel => $localeRates) {
            $channel = new ChannelCode($channel);
            foreach ($localeRates as $locale => $rate) {
                $evaluationResult->addRate($channel, new LocaleCode($locale), new Rate($rate));
            }
        }

        return $evaluationResult;
    }
}
