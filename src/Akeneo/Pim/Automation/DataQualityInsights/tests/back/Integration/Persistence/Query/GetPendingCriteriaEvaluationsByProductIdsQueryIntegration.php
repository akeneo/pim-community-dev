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
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\GetPendingCriteriaEvaluationsByProductIdsQuery;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository\CriterionEvaluationRepository;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

final class GetPendingCriteriaEvaluationsByProductIdsQueryIntegration extends TestCase
{
    /** @var CriterionEvaluationRepositoryInterface */
    private $repository;

    /** @var GetPendingCriteriaEvaluationsByProductIdsQuery */
    private $query;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->get(CriterionEvaluationRepository::class);
        $this->query = $this->get(GetPendingCriteriaEvaluationsByProductIdsQuery::class);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_finds_pending_criteria_evaluations_by_product_ids()
    {
        $criteriaEvaluations = [
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

        $this->persistCriteriaEvaluations($criteriaEvaluations);

        $evaluations = $this->query->execute([42, 123]);

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
        $this->assertEmpty($this->query->execute([42]));
    }

    private function persistCriteriaEvaluations(array $criteriaEvaluations): void
    {
        $criterionEvaluationCollection = new Write\CriterionEvaluationCollection();
        foreach ($criteriaEvaluations as $criterionEvaluation) {
            $criterionEvaluationCollection->add($criterionEvaluation);
        }

        $this->repository->create($criterionEvaluationCollection);
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
}
