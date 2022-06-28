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
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
use Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\DataQualityInsightsTestCase;
use Webmozart\Assert\Assert;

final class FilterProductIdsWithCriterionNotEvaluatedSinceQueryIntegration extends DataQualityInsightsTestCase
{
    /** @var CriterionEvaluationRepositoryInterface */
    private $productCriterionEvaluationRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productCriterionEvaluationRepository = $this->get('akeneo.pim.automation.data_quality_insights.repository.product_criterion_evaluation');
    }

    public function test_it_filters_product_ids_with_a_given_criterion_not_evaluated_since_a_given_date()
    {
        $evaluatedSince = new \DateTimeImmutable('2020-06-22 10:21:34');
        $criterionCode = new CriterionCode(EvaluateAttributeOptionSpelling::CRITERION_CODE);
        $anotherCriterionCode = new CriterionCode('completeness');
        $productUuidsToFilter = [
            ProductUuid::fromUuid($this->createProductWithoutEvaluations('product_1')->getUuid()),
            ProductUuid::fromUuid($this->createProductWithoutEvaluations('product_2')->getUuid()),
            ProductUuid::fromUuid($this->createProductWithoutEvaluations('product_3')->getUuid()),
            ProductUuid::fromUuid($this->createProductWithoutEvaluations('product_4')->getUuid()),
            ProductUuid::fromUuid($this->createProductWithoutEvaluations('product_5')->getUuid()),
        ];
        $notInvolvedProduct = ProductUuid::fromUuid($this->createProductWithoutEvaluations('not_involved_product')->getUuid());

        $this->givenAPendingProductCriterion($productUuidsToFilter[0], $criterionCode, $evaluatedSince->modify('-2 MINUTE'));
        $this->givenAProductCriterionEvaluatedAt($productUuidsToFilter[1], $criterionCode, $evaluatedSince);
        $this->givenAProductCriterionEvaluatedAt($productUuidsToFilter[1], $anotherCriterionCode, $evaluatedSince->modify('-1 SECOND'));
        $this->givenAProductCriterionEvaluatedAt($notInvolvedProduct, $criterionCode, $evaluatedSince->modify('-1 HOUR'));

        $this->givenAProductCriterionEvaluatedAt($productUuidsToFilter[2], $criterionCode, $evaluatedSince->modify('-1 SECOND'));
        $this->givenAProductCriterionEvaluatedAt($productUuidsToFilter[3], $criterionCode, $evaluatedSince->modify('-3 DAY'));

        $productUuids = $this->get('akeneo.pim.automation.data_quality_insights.query.filter_product_ids_with_criterion_not_evaluated_since')
            ->execute(ProductUuidCollection::fromProductUuids($productUuidsToFilter), $evaluatedSince, $criterionCode);

        $this->assertEqualsCanonicalizing([$productUuidsToFilter[2], $productUuidsToFilter[3]], $productUuids->toArray());
    }

    private function givenAPendingProductCriterion(ProductUuid $productUuid, CriterionCode $criterionCode, \DateTimeImmutable $evaluatedAt): void
    {
        $criterionEvaluation = $this->createProductEvaluation($productUuid, $criterionCode);

        $this->evaluateProductCriterionAt($criterionEvaluation, $evaluatedAt);
    }

    private function givenAProductCriterionEvaluatedAt(ProductUuid $productUuid, CriterionCode $criterionCode, \DateTimeImmutable $evaluatedAt): void
    {
        $criterionEvaluation = $this->createProductEvaluation($productUuid, $criterionCode);

        $criterionEvaluation->end(new Write\CriterionEvaluationResult());
        $this->productCriterionEvaluationRepository->update((new Write\CriterionEvaluationCollection)->add($criterionEvaluation));

        $this->evaluateProductCriterionAt($criterionEvaluation, $evaluatedAt);
    }

    private function createProductEvaluation(ProductUuid $productUuid, CriterionCode $criterionCode): Write\CriterionEvaluation
    {
        $criterionEvaluation = new Write\CriterionEvaluation(
            $criterionCode,
            $productUuid,
            CriterionEvaluationStatus::pending()
        );

        $this->productCriterionEvaluationRepository->create((new Write\CriterionEvaluationCollection)->add($criterionEvaluation));

        return $criterionEvaluation;
    }

    private function evaluateProductCriterionAt(Write\CriterionEvaluation $criterionEvaluation, \DateTimeImmutable $evaluatedAt): void
    {
        $query = <<<SQL
UPDATE pim_data_quality_insights_product_criteria_evaluation e, pim_catalog_product p
SET e.evaluated_at = :evaluated_at
WHERE p.uuid = :productUuid AND e.product_uuid = p.uuid AND criterion_code = :criterionCode;
SQL;

        $productUuid = $criterionEvaluation->getEntityId();
        Assert::isInstanceOf($productUuid, ProductUuid::class);

        $this->get('database_connection')->executeQuery($query, [
            'evaluated_at' => $evaluatedAt->format(Clock::TIME_FORMAT),
            'productUuid' => $productUuid->toBytes(),
            'criterionCode' => $criterionEvaluation->getCriterionCode(),
        ]);
    }
}
