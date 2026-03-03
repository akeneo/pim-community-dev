<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductModelIdFactory;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\CriterionEvaluationRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation\GetEvaluationRatesByProductModelsAndCriterionQuery;
use Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\DataQualityInsightsTestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetEvaluationRatesByProductModelsAndCriterionQueryIntegration extends DataQualityInsightsTestCase
{
    private CriterionEvaluationRepositoryInterface $productModelCriterionEvaluationRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productModelCriterionEvaluationRepository = $this->get('akeneo.pim.automation.data_quality_insights.repository.product_model_criterion_evaluation');

        $this->createMinimalFamilyAndFamilyVariant('a_family', 'a_family_variant');
    }

    public function testItGivesTheEvaluationRatesOfAGivenProductModelAndCriterion()
    {
        $this->createChannel('ecommerce', ['locales' => ['en_US', 'fr_FR']]);
        $this->createChannel('mobile', ['locales' => ['en_US']]);

        $expectedEvaluationRates = [];
        $expectedEvaluationRates += $this->givenProductModelA();
        $expectedEvaluationRates += $this->givenProductModelB();
        $expectedEvaluationRates += $this->givenAProductModelNotEvaluatedYet('spelling');

        $this->givenANotInvolvedProductModel();

        $productModelIds = array_keys($expectedEvaluationRates);
        $productModelIds[] = 12345; // Unknown product
        $productIdCollection = $this->get(ProductModelIdFactory::class)->createCollection(array_map(fn ($id) => (string) $id, $productModelIds));

        $evaluationRates = $this->get(GetEvaluationRatesByProductModelsAndCriterionQuery::class)
            ->execute($productIdCollection, new CriterionCode('spelling'));

        $this->assertEquals($expectedEvaluationRates, $evaluationRates);
    }

    private function givenProductModelA(): array
    {
        $productModel = $this->createProductModelWithoutEvaluations('product_model_A', 'a_family_variant');

        $expectedRates = [
            'ecommerce' => [
                'en_US' => 100,
                'fr_FR' => 50,
            ],
            'mobile' => [
                'en_US' => 76,
            ],
        ];
        $evaluationResults['spelling'] = $this->buildEvaluationResult($expectedRates);
        $evaluationResults['whatever'] = $this->buildEvaluationResult([
            'ecommerce' => [
                'en_US' => 54,
                'fr_FR' => 0,
            ],
            'mobile' => [
                'en_US' => 42,
            ],
        ]);

        $this->saveEvaluationResults($productModel->getId(), $evaluationResults);

        return [$productModel->getId() => $expectedRates];
    }

    private function givenProductModelB(): array
    {
        $productModel = $this->createProductModelWithoutEvaluations('product_model_B', 'a_family_variant');

        $expectedRates = [
            'ecommerce' => [
                'en_US' => 43,
                'fr_FR' => 87,
            ],
            'mobile' => [
                'en_US' => 0,
            ],
        ];
        $evaluationResults['spelling'] = $this->buildEvaluationResult($expectedRates);

        $this->saveEvaluationResults($productModel->getId(), $evaluationResults);

        return [$productModel->getId() => $expectedRates];
    }

    private function givenANotInvolvedProductModel(): void
    {
        $productModel = $this->createProductModelWithoutEvaluations('whatever', 'a_family_variant');

        $evaluationResults['spelling'] = $this->buildEvaluationResult([
            'ecommerce' => [
                'en_US' => 98,
                'fr_FR' => 1,
            ],
            'mobile' => [
                'en_US' => 47,
            ],
        ]);

        $this->saveEvaluationResults($productModel->getId(), $evaluationResults);
    }

    private function givenAProductModelNotEvaluatedYet(string $criterionCode): array
    {
        $productModelId = $this->createProductModel('not_evaluated_product', 'a_family_variant')->getId();

        $this->get('database_connection')->executeQuery(<<<SQL
REPLACE INTO pim_data_quality_insights_product_model_criteria_evaluation (product_id, criterion_code, evaluated_at, status, result) 
VALUES (:productId, :criterionCode, null, 'pending', null);
SQL,
            [
                'productId' => $productModelId,
                'criterionCode' => $criterionCode,
            ]
        );

        return [$productModelId => []];
    }

    private function saveEvaluationResults(int $productModelIdAsInt, array $evaluationResults): void
    {
        $productModelId = new ProductModelId($productModelIdAsInt);
        $evaluations = new Write\CriterionEvaluationCollection();

        foreach ($evaluationResults as $criterion => $evaluationResult) {
            $evaluation = new Write\CriterionEvaluation(
                new CriterionCode($criterion),
                $productModelId,
                CriterionEvaluationStatus::done()
            );
            $evaluation->end($evaluationResult);
            $evaluations->add($evaluation);
        }

        $this->productModelCriterionEvaluationRepository->create($evaluations);
        $this->productModelCriterionEvaluationRepository->update($evaluations);
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
