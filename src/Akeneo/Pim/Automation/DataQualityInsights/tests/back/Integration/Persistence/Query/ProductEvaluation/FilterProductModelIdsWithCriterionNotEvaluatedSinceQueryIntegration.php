<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Consistency\EvaluateAttributeOptionSpelling;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\CriterionEvaluationRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\DataQualityInsightsTestCase;

final class FilterProductModelIdsWithCriterionNotEvaluatedSinceQueryIntegration extends DataQualityInsightsTestCase
{
    /** @var CriterionEvaluationRepositoryInterface */
    private $productCriterionEvaluationRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productCriterionEvaluationRepository = $this->get('akeneo.pim.automation.data_quality_insights.repository.product_model_criterion_evaluation');
    }

    public function test_it_filters_product_model_ids_with_a_given_criterion_not_evaluated_since_a_given_date()
    {
        $this->createMinimalFamilyAndFamilyVariant('a_family', 'a_family_variant');

        $evaluatedSince = new \DateTimeImmutable('2020-06-22 10:21:34');
        $criterionCode = new CriterionCode(EvaluateAttributeOptionSpelling::CRITERION_CODE);
        $anotherCriterionCode = new CriterionCode('completeness');
        $productModelIdsToFilter = [
            new ProductId($this->createProductModelWithoutEvaluations('pm_1', 'a_family_variant')->getId()),
            new ProductId($this->createProductModelWithoutEvaluations('pm_2', 'a_family_variant')->getId()),
            new ProductId($this->createProductModelWithoutEvaluations('pm_3', 'a_family_variant')->getId()),
            new ProductId($this->createProductModelWithoutEvaluations('pm_4', 'a_family_variant')->getId()),
            new ProductId($this->createProductModelWithoutEvaluations('pm_5', 'a_family_variant')->getId()),
        ];
        $notInvolvedProductModel = $this->createProductModelWithoutEvaluations('not_involved_product_model', 'a_family_variant')->getId();

        $this->givenAPendingProductModelCriterion($productModelIdsToFilter[0], $criterionCode, $evaluatedSince->modify('-2 MINUTE'));
        $this->givenAProductModelCriterionEvaluatedAt($productModelIdsToFilter[1], $criterionCode, $evaluatedSince);
        $this->givenAProductModelCriterionEvaluatedAt($productModelIdsToFilter[1], $anotherCriterionCode, $evaluatedSince->modify('-1 SECOND'));
        $this->givenAProductModelCriterionEvaluatedAt(new ProductId($notInvolvedProductModel), $criterionCode, $evaluatedSince->modify('-1 HOUR'));

        $this->givenAProductModelCriterionEvaluatedAt($productModelIdsToFilter[2], $criterionCode, $evaluatedSince->modify('-1 SECOND'));
        $this->givenAProductModelCriterionEvaluatedAt($productModelIdsToFilter[3], $criterionCode, $evaluatedSince->modify('-3 DAY'));

        $productModelIds = $this->get('akeneo.pim.automation.data_quality_insights.query.filter_product_model_ids_with_criterion_not_evaluated_since')
            ->execute($productModelIdsToFilter, $evaluatedSince, $criterionCode);

        $this->assertEqualsCanonicalizing([$productModelIdsToFilter[2], $productModelIdsToFilter[3]], $productModelIds);
    }

    private function givenAPendingProductModelCriterion(ProductId $productModelId, CriterionCode $criterionCode, \DateTimeImmutable $evaluatedAt): void
    {
        $criterionEvaluation = $this->createProductModelEvaluation($productModelId, $criterionCode);

        $this->evaluateProductCriterionAt($criterionEvaluation, $evaluatedAt);
    }

    private function givenAProductModelCriterionEvaluatedAt(ProductId $productModelId, CriterionCode $criterionCode, \DateTimeImmutable $evaluatedAt): void
    {
        $criterionEvaluation = $this->createProductModelEvaluation($productModelId, $criterionCode);

        $criterionEvaluation->end(new Write\CriterionEvaluationResult());
        $this->productCriterionEvaluationRepository->update((new Write\CriterionEvaluationCollection)->add($criterionEvaluation));

        $this->evaluateProductCriterionAt($criterionEvaluation, $evaluatedAt);
    }

    private function createProductModelEvaluation(ProductId $productModelId, CriterionCode $criterionCode): Write\CriterionEvaluation
    {
        $criterionEvaluation = new Write\CriterionEvaluation(
            $criterionCode,
            $productModelId,
            CriterionEvaluationStatus::pending()
        );

        $this->productCriterionEvaluationRepository->create((new Write\CriterionEvaluationCollection)->add($criterionEvaluation));

        return $criterionEvaluation;
    }

    private function evaluateProductCriterionAt(Write\CriterionEvaluation $criterionEvaluation, \DateTimeImmutable $evaluatedAt): void
    {
        $query = <<<SQL
UPDATE pim_data_quality_insights_product_model_criteria_evaluation
SET evaluated_at = :evaluated_at
WHERE product_id = :productModelId AND criterion_code = :criterionCode;
SQL;

        $this->get('database_connection')->executeQuery($query, [
            'evaluated_at' => $evaluatedAt->format(Clock::TIME_FORMAT),
            'productModelId' => $criterionEvaluation->getProductId()->toInt(),
            'criterionCode' => $criterionEvaluation->getCriterionCode(),
        ]);
    }
}
