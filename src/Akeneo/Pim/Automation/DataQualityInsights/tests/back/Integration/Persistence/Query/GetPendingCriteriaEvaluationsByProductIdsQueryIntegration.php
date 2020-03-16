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

namespace Akeneo\Pim\Automation\DataQualityInsights\tests\back\Integration\Persistence\Query;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\CriterionEvaluationRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
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
        $criteriaEvaluations = $this->getCriteriaEvaluationsSample();
        $this->persistProductCriteriaEvaluations($criteriaEvaluations);

        $evaluations = $this->get('akeneo.pim.automation.data_quality_insights.query.get_product_pending_criteria_evaluations')
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

    public function test_it_finds_product_models_pending_criteria_evaluations()
    {
        $criteriaEvaluations = $this->getCriteriaEvaluationsSample();
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
            $expectedCriterionEvaluation = $this->findCriterionEvaluationById($expectedCriteriaEvaluations, $criterionEvaluation->getId());
            $this->assertNotNull($expectedCriterionEvaluation);
            $this->assertEqualsCriterionEvaluation($expectedCriterionEvaluation, $criterionEvaluation);
        }
    }

    private function assertEqualsCriterionEvaluation(Write\CriterionEvaluation $expectedCriterionEvaluation, Write\CriterionEvaluation $criterionEvaluation): void
    {
        $this->assertEquals($expectedCriterionEvaluation->getId(), $criterionEvaluation->getId());
        $this->assertEquals($expectedCriterionEvaluation->getCriterionCode(), $criterionEvaluation->getCriterionCode());
        $this->assertEquals($expectedCriterionEvaluation->getStatus(), $criterionEvaluation->getStatus());
        $this->assertEquals($expectedCriterionEvaluation->getCreatedAt()->format(Clock::TIME_FORMAT), $criterionEvaluation->getCreatedAt()->format(Clock::TIME_FORMAT));
    }

    private function findCriterionEvaluationById(Write\CriterionEvaluationCollection $criteriaEvaluations, CriterionEvaluationId $id): ?Write\CriterionEvaluation
    {
        foreach ($criteriaEvaluations as $criterionEvaluation) {
            if ($criterionEvaluation->getId() == $id) {
                return $criterionEvaluation;
            }
        }
    }

    private function getCriteriaEvaluationsSample(): array
    {
        return [
            'product_42_pending_completeness' => new Write\CriterionEvaluation(
                new CriterionEvaluationId('95f124de-45cd-495e-ac58-349086ad6cd4'),
                new CriterionCode('completeness'),
                new ProductId(42),
                new \DateTimeImmutable('2019-10-28 10:41:56.123'),
                CriterionEvaluationStatus::pending()
            ),
            'product_42_done_completeness' => new Write\CriterionEvaluation(
                new CriterionEvaluationId('9a05eb2c-1d35-4465-acdd-c1f6fdd1dd35'),
                new CriterionCode('completeness'),
                new ProductId(42),
                new \DateTimeImmutable('2019-10-26 11:41:56.123'),
                CriterionEvaluationStatus::done()
            ),
            'product_42_pending_spelling' => new Write\CriterionEvaluation(
                new CriterionEvaluationId('bbd6cfd4-e2a8-47c0-8d7d-7d1a1a43bb39'),
                new CriterionCode('spelling'),
                new ProductId(42),
                new \DateTimeImmutable('2019-10-28 10:40:56.653'),
                CriterionEvaluationStatus::pending()
            ),
            'product_123_pending_spelling' => new Write\CriterionEvaluation(
                new CriterionEvaluationId('d7bcae1e-30c9-4626-9c4f-d06cae03e77e'),
                new CriterionCode('spelling'),
                new ProductId(123),
                new \DateTimeImmutable('2019-10-28 10:41:57.987'),
                CriterionEvaluationStatus::pending()
            ),
            'product_456_pending_spelling' => new Write\CriterionEvaluation(
                new CriterionEvaluationId('1774d7aa-6fc7-4519-a8a9-7d2887092aff'),
                new CriterionCode('spelling'),
                new ProductId(456),
                new \DateTimeImmutable('2019-10-28 10:41:47.234'),
                CriterionEvaluationStatus::pending()
            )
        ];
    }
}
