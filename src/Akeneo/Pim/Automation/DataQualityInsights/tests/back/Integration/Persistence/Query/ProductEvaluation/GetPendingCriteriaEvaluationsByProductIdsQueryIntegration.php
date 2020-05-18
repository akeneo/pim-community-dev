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
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\CriterionEvaluationRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationResultStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

final class GetPendingCriteriaEvaluationsByProductIdsQueryIntegration extends TestCase
{
    /** @var CriterionEvaluationRepositoryInterface */
    private $productCriterionEvaluationRepository;

    /** @var CriterionEvaluationRepositoryInterface */
    private $productModelCriterionEvaluationRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productCriterionEvaluationRepository = $this->get('akeneo.pim.automation.data_quality_insights.repository.product_criterion_evaluation');
        $this->productModelCriterionEvaluationRepository = $this->get('akeneo.pim.automation.data_quality_insights.repository.product_model_criterion_evaluation');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_finds_product_pending_criteria_evaluations()
    {
        $criteriaEvaluations = $this->getPendingCriteriaEvaluationsSample();
        $this->persistProductCriteriaEvaluations($criteriaEvaluations);

        $this->givenAProductWithOnlyEvaluationsDone(new ProductId(777));

        $evaluations = $this->get('akeneo.pim.automation.data_quality_insights.query.get_product_pending_criteria_evaluations')
            ->execute([42, 777, 123]);

        $expectedCriteriaEvaluationsProduct42 = (new Write\CriterionEvaluationCollection())
            ->add($criteriaEvaluations['product_42_pending_completeness'])
            ->add($criteriaEvaluations['product_42_pending_spelling'])
        ;
        $expectedCriteriaEvaluationsProduct123 = (new Write\CriterionEvaluationCollection())
            ->add($criteriaEvaluations['product_123_pending_spelling'])
        ;

        $this->assertCount(2, $evaluations);
        $this->assertArrayHasKey(42, $evaluations);
        $this->assertArrayHasKey(123, $evaluations);
        $this->assertEqualsCriteriaEvaluations($expectedCriteriaEvaluationsProduct42, $evaluations[42]);
        $this->assertEqualsCriteriaEvaluations($expectedCriteriaEvaluationsProduct123, $evaluations[123]);
    }

    public function test_it_finds_product_models_pending_criteria_evaluations()
    {
        $criteriaEvaluations = $this->getPendingCriteriaEvaluationsSample();
        $this->persistProductModelCriteriaEvaluations($criteriaEvaluations);

        $evaluations = $this->get('akeneo.pim.automation.data_quality_insights.query.get_product_model_pending_criteria_evaluations')
            ->execute([42, 123]);

        $expectedCriteriaEvaluationsProduct42 = (new Write\CriterionEvaluationCollection())
            ->add($criteriaEvaluations['product_42_pending_completeness'])
            ->add($criteriaEvaluations['product_42_pending_spelling'])
        ;
        $expectedCriteriaEvaluationsProduct123 = (new Write\CriterionEvaluationCollection())
            ->add($criteriaEvaluations['product_123_pending_spelling'])
        ;

        $this->assertCount(2, $evaluations);
        $this->assertArrayHasKey(42, $evaluations);
        $this->assertArrayHasKey(123, $evaluations);
        $this->assertEqualsCriteriaEvaluations($expectedCriteriaEvaluationsProduct42, $evaluations[42]);
        $this->assertEqualsCriteriaEvaluations($expectedCriteriaEvaluationsProduct123, $evaluations[123]);
    }

    public function test_it_returns_an_empty_array_if_there_is_no_pending_criteria_evaluations()
    {
        $this->assertEmpty($this->get('akeneo.pim.automation.data_quality_insights.query.get_product_pending_criteria_evaluations')
            ->execute([42]));
    }

    private function persistProductCriteriaEvaluations(array $criteriaEvaluations): void
    {
        $criterionEvaluationCollection = new Write\CriterionEvaluationCollection();
        foreach ($criteriaEvaluations as $criterionEvaluation) {
            $criterionEvaluationCollection->add($criterionEvaluation);
        }

        $this->productCriterionEvaluationRepository->create($criterionEvaluationCollection);
    }

    private function persistProductModelCriteriaEvaluations(array $criteriaEvaluations): void
    {
        $criterionEvaluationCollection = new Write\CriterionEvaluationCollection();
        foreach ($criteriaEvaluations as $criterionEvaluation) {
            $criterionEvaluationCollection->add($criterionEvaluation);
        }

        $this->productModelCriterionEvaluationRepository->create($criterionEvaluationCollection);
    }

    private function assertEqualsCriteriaEvaluations(Write\CriterionEvaluationCollection $expectedCriteriaEvaluations, Write\CriterionEvaluationCollection $criteriaEvaluations): void
    {
        $this->assertCount(count($expectedCriteriaEvaluations), $criteriaEvaluations);

        foreach ($criteriaEvaluations as $criterionEvaluation) {
            $expectedCriterionEvaluation = $this->findCriterionEvaluation($expectedCriteriaEvaluations, $criterionEvaluation->getProductId(), $criterionEvaluation->getCriterionCode());
            $this->assertNotNull($expectedCriterionEvaluation);
            $this->assertEqualsCriterionEvaluation($expectedCriterionEvaluation, $criterionEvaluation);
        }
    }

    private function assertEqualsCriterionEvaluation(Write\CriterionEvaluation $expectedCriterionEvaluation, Write\CriterionEvaluation $criterionEvaluation): void
    {
        $this->assertEquals($expectedCriterionEvaluation->getCriterionCode(), $criterionEvaluation->getCriterionCode());
        $this->assertEquals($expectedCriterionEvaluation->getStatus(), $criterionEvaluation->getStatus());
        $this->assertEvaluatedAtEquals($expectedCriterionEvaluation->getEvaluatedAt(), $criterionEvaluation->getEvaluatedAt());
    }

    private function findCriterionEvaluation(Write\CriterionEvaluationCollection $criteriaEvaluations, ProductId $productId, CriterionCode $criterionCode): ?Write\CriterionEvaluation
    {
        foreach ($criteriaEvaluations as $criterionEvaluation) {
            if ($criterionEvaluation->getProductId() == $productId && $criterionEvaluation->getCriterionCode() == $criterionCode) {
                return $criterionEvaluation;
            }
        }

        return null;
    }

    private function getPendingCriteriaEvaluationsSample(): array
    {
        return [
            'product_42_pending_completeness' => new Write\CriterionEvaluation(
                new CriterionCode('completeness'),
                new ProductId(42),
                CriterionEvaluationStatus::pending()
            ),
            'product_42_pending_spelling' => new Write\CriterionEvaluation(
                new CriterionCode('spelling'),
                new ProductId(42),
                CriterionEvaluationStatus::pending()
            ),
            'product_123_pending_spelling' => new Write\CriterionEvaluation(
                new CriterionCode('spelling'),
                new ProductId(123),
                CriterionEvaluationStatus::pending()
            ),
            'product_456_pending_spelling' => new Write\CriterionEvaluation(
                new CriterionCode('spelling'),
                new ProductId(456),
                CriterionEvaluationStatus::pending()
            )
        ];
    }

    private function givenAProductWithOnlyEvaluationsDone(ProductId $productId)
    {
        $evaluation = new Write\CriterionEvaluation(
            new CriterionCode('spelling'),
            $productId,
            CriterionEvaluationStatus::pending()
        );

        $evaluations = (new Write\CriterionEvaluationCollection())->add($evaluation);
        $this->productCriterionEvaluationRepository->create($evaluations);

        $evaluation->end(new Write\CriterionEvaluationResult());
        $this->productCriterionEvaluationRepository->update($evaluations);
    }

    private function assertEvaluatedAtEquals(?\DateTimeImmutable $expectedDate, ?\DateTimeImmutable $date): void
    {
        if (null === $expectedDate) {
            $this->assertNull($date);
        } else {
           $this->assertEquals($expectedDate->format(Clock::TIME_FORMAT), $date->format(Clock::TIME_FORMAT));
        }
    }
}
