<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Persistence\Repository;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\CriterionEvaluationResult;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\CriterionRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\CriterionEvaluation;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\CriterionEvaluationCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\CriterionEvaluationRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository\CriterionEvaluationRepository;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;

final class CriterionEvaluationRepositoryIntegration extends TestCase
{
    /** @var Connection */
    private $db;

    /** @var CriterionEvaluationRepositoryInterface */
    private $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = $this->get('database_connection');
        $this->repository = $this->get(CriterionEvaluationRepository::class);
    }

    public function test_it_creates_a_collection_of_criteria_evaluations()
    {
        $evaluations = $this->findAllEvaluations();
        $this->assertCount(0, $evaluations);

        $criteria = $this->buildCollection();
        $this->repository->create($criteria);

        $evaluations = $this->findAllEvaluations();
        $this->assertCount(2, $evaluations);

        $this->assertEquals('95f124de-45cd-495e-ac58-349086ad6cd4', $evaluations[0]['id']);
        $this->assertEquals('completeness', $evaluations[0]['criterion_code']);
        $this->assertEquals(42, $evaluations[0]['product_id']);
        $this->assertEquals('2019-10-28 10:41:56.123', $evaluations[0]['created_at']);
        $this->assertEquals(CriterionEvaluationStatus::PENDING, $evaluations[0]['status']);

        $this->assertEquals('d7bcae1e-30c9-4626-9c4f-d06cae03e77e', $evaluations[1]['id']);
        $this->assertEquals('completion', $evaluations[1]['criterion_code']);
        $this->assertEquals(123, $evaluations[1]['product_id']);
        $this->assertEquals('2019-10-28 10:41:57.987', $evaluations[1]['created_at']);
        $this->assertEquals(CriterionEvaluationStatus::PENDING, $evaluations[1]['status']);
    }

    public function test_it_creates_only_one_pending_evaluation_per_criteria()
    {
        $criteria = $this->buildCollection();

        $this->repository->create($criteria);
        $evaluations = $this->findAllEvaluations();
        $this->assertCount(2, $evaluations);

        $this->repository->create($criteria);
        $evaluations = $this->findAllEvaluations();
        $this->assertCount(2, $evaluations);
    }

    public function test_it_updates_one_criteria()
    {
        $createdAt = new \DateTimeImmutable();
        $criterionEvaluationId = new CriterionEvaluationId('aeb3218d-e923-42cf-a0f9-a2fc3beaf628');

        $criterionEvaluation = new CriterionEvaluation(
            $criterionEvaluationId,
            new CriterionCode('completeness'),
            new ProductId(567),
            $createdAt,
            CriterionEvaluationStatus::pending()
        );
        $criteriaEvaluationCollection = $this->buildCollection();
        $criteriaEvaluationCollection->add($criterionEvaluation);
        $this->repository->create($criteriaEvaluationCollection);

        $rates = new CriterionRateCollection();
        $rates->addRate(new ChannelCode('mobile'), new LocaleCode('en_US'), new Rate(75));

        $criterionEvaluation->start();
        $criterionEvaluation->end(new CriterionEvaluationResult($rates, ['count' => 124]));
        $this->repository->update($criterionEvaluation);

        $rawCriterionEvaluation = $this->findOneCriterionEvaluationById($criterionEvaluationId);

        $this->assertEquals('aeb3218d-e923-42cf-a0f9-a2fc3beaf628', $rawCriterionEvaluation['id']);
        $this->assertEquals('completeness', $rawCriterionEvaluation['criterion_code']);
        $this->assertEquals(567, $rawCriterionEvaluation['product_id']);
        $this->assertEquals($createdAt->format(Clock::TIME_FORMAT), $rawCriterionEvaluation['created_at']);
        $this->assertEquals(CriterionEvaluationStatus::DONE, $rawCriterionEvaluation['status']);
        $this->assertEquals($criterionEvaluation->getStartedAt()->format(Clock::TIME_FORMAT), $rawCriterionEvaluation['started_at']);
        $this->assertEquals($criterionEvaluation->getEndedAt()->format(Clock::TIME_FORMAT), $rawCriterionEvaluation['ended_at']);
        $this->assertJson($rawCriterionEvaluation['result']);
    }

    public function test_it_finds_criterion_to_evaluate()
    {
        $criteria = $this->buildCollection();
        $this->repository->create($criteria);

        $evaluations = $this->repository->findPendingByProductIds([42, 123]);

        $this->assertSame('95f124de-45cd-495e-ac58-349086ad6cd4', strval($evaluations[0]->getId()));
        $this->assertSame('d7bcae1e-30c9-4626-9c4f-d06cae03e77e', strval($evaluations[1]->getId()));
    }

    public function test_it_returns_an_empty_array_if_it_founds_no_criterion_to_evaluate()
    {
        $this->assertEmpty($this->repository->findPendingByProductIds([456789]));
    }

    private function buildCollection(): CriterionEvaluationCollection
    {
        $criteria = (new CriterionEvaluationCollection)
            ->add(new CriterionEvaluation(
                new CriterionEvaluationId('95f124de-45cd-495e-ac58-349086ad6cd4'),
                new CriterionCode('completeness'),
                new ProductId(42),
                new \DateTimeImmutable('2019-10-28 10:41:56.123'),
                CriterionEvaluationStatus::pending()
            ))
            ->add(new CriterionEvaluation(
                new CriterionEvaluationId('d7bcae1e-30c9-4626-9c4f-d06cae03e77e'),
                new CriterionCode('completion'),
                new ProductId(123),
                new \DateTimeImmutable('2019-10-28 10:41:57.987'),
                CriterionEvaluationStatus::pending()
            ));

        return $criteria;
    }

    private function findAllEvaluations(): array
    {
        $stmt = $this->db->query('SELECT * FROM pimee_data_quality_insights_criteria_evaluation');

        return $stmt->fetchAll();
    }

    private function findOneCriterionEvaluationById(CriterionEvaluationId $criterionEvaluationId): ?array
    {
        $stmt = $this->db->executeQuery(
            'SELECT * FROM pimee_data_quality_insights_criteria_evaluation WHERE id = :id',
            ['id' => strval($criterionEvaluationId)]
        );

        $result = $stmt->fetch(FetchMode::ASSOCIATIVE);

        return false === $result ? null : $result;
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
