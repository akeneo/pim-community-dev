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
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\CriterionEvaluationRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationResultStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;
use Ramsey\Uuid\Uuid;

final class ProductCriterionEvaluationRepositoryIntegration extends TestCase
{
    /** @var Connection */
    private $db;

    /** @var CriterionEvaluationRepositoryInterface */
    private $productCriterionEvaluationRepository;

    private $productId1;

    private $productId2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = $this->get('database_connection');
        $this->productCriterionEvaluationRepository = $this->get('akeneo.pim.automation.data_quality_insights.repository.product_criterion_evaluation');

        $this->productId1 = $this->createProduct();
        $this->productId2 = $this->createProduct();
        $this->clearAllEvaluations();
    }

    public function test_it_creates_a_collection_of_product_criteria_evaluations()
    {
        $this->assertItCreatesACollectionOfCriteriaEvaluations(
            $this->productCriterionEvaluationRepository,
            function () { return $this->findAllProductEvaluations(); }
        );
    }

    private function assertItCreatesACollectionOfCriteriaEvaluations(
        CriterionEvaluationRepositoryInterface $criterionEvaluationRepository,
        callable $findAllEvaluations
    ) {
        $evaluations = $findAllEvaluations();
        $this->assertCount(0, $evaluations);

        $criteria = $this->buildCollection();
        $criterionEvaluationRepository->create($criteria);

        $evaluations = $findAllEvaluations();
        $this->assertCount(2, $evaluations);

        $this->assertEquals('95f124de-45cd-495e-ac58-349086ad6cd4', $evaluations[0]['id']);
        $this->assertEquals('completeness', $evaluations[0]['criterion_code']);
        $this->assertEquals($this->productId1, $evaluations[0]['product_id']);
        $this->assertEquals('2019-10-28 10:41:56.123', $evaluations[0]['created_at']);
        $this->assertEquals(CriterionEvaluationStatus::PENDING, $evaluations[0]['status']);

        $this->assertEquals('d7bcae1e-30c9-4626-9c4f-d06cae03e77e', $evaluations[1]['id']);
        $this->assertEquals('completion', $evaluations[1]['criterion_code']);
        $this->assertEquals($this->productId2, $evaluations[1]['product_id']);
        $this->assertEquals('2019-10-28 10:41:57.987', $evaluations[1]['created_at']);
        $this->assertEquals(CriterionEvaluationStatus::PENDING, $evaluations[1]['status']);
    }

    public function test_it_creates_only_one_product_pending_evaluation_per_criteria()
    {
        $this->assertItCreatesOnlyOnePendingEvaluationPerCriteria(
            $this->productCriterionEvaluationRepository,
            function () { return $this->findAllProductEvaluations(); }
        );
    }

    private function assertItCreatesOnlyOnePendingEvaluationPerCriteria(
        CriterionEvaluationRepositoryInterface $criterionEvaluationRepository,
        callable $findAllEvaluations
    ) {
        $criteria = $this->buildCollection();

        $criterionEvaluationRepository->create($criteria);
        $evaluations = $findAllEvaluations();
        $this->assertCount(2, $evaluations);

        $criterionEvaluationRepository->create($criteria);
        $evaluations = $findAllEvaluations();
        $this->assertCount(2, $evaluations);
    }

    public function test_it_updates_product_criteria_evaluations()
    {
        $this->assertItUpdatesCriteriaEvaluations(
            $this->productCriterionEvaluationRepository,
            function($criterionEvaluationId) { return $this->findOneProductCriterionEvaluationById($criterionEvaluationId); }
        );
    }

    private function assertItUpdatesCriteriaEvaluations(
        CriterionEvaluationRepositoryInterface $criterionEvaluationRepository,
        callable $findOneCriterionEvaluationById
    ) {
        $createdAt = new \DateTimeImmutable();
        $criterionEvaluationId = new CriterionEvaluationId('aeb3218d-e923-42cf-a0f9-a2fc3beaf628');

        $criterionEvaluation = new Write\CriterionEvaluation(
            $criterionEvaluationId,
            new CriterionCode('completeness'),
            new ProductId(567),
            $createdAt,
            CriterionEvaluationStatus::pending()
        );
        $criteriaEvaluationCollection = $this->buildCollection();
        $criteriaEvaluationCollection->add($criterionEvaluation);
        $criterionEvaluationRepository->create($criteriaEvaluationCollection);

        $evaluationResult = (new Write\CriterionEvaluationResult())
            ->addRate(new ChannelCode('mobile'), new LocaleCode('en_US'), new Rate(75))
            ->addStatus(new ChannelCode('mobile'), new LocaleCode('en_US'), CriterionEvaluationResultStatus::done())
            ->addImprovableAttributes(new ChannelCode('mobile'), new LocaleCode('en_US'), [])
        ;

        $criterionEvaluation->start();
        $criterionEvaluation->end($evaluationResult);
        $criterionEvaluationRepository->update((new Write\CriterionEvaluationCollection())->add($criterionEvaluation));

        $rawCriterionEvaluation = $findOneCriterionEvaluationById($criterionEvaluationId);

        $this->assertEquals('aeb3218d-e923-42cf-a0f9-a2fc3beaf628', $rawCriterionEvaluation['id']);
        $this->assertEquals('completeness', $rawCriterionEvaluation['criterion_code']);
        $this->assertEquals(567, $rawCriterionEvaluation['product_id']);
        $this->assertEquals($createdAt->format(Clock::TIME_FORMAT), $rawCriterionEvaluation['created_at']);
        $this->assertEquals(CriterionEvaluationStatus::DONE, $rawCriterionEvaluation['status']);
        $this->assertEquals($criterionEvaluation->getStartedAt()->format(Clock::TIME_FORMAT), $rawCriterionEvaluation['started_at']);
        $this->assertEquals($criterionEvaluation->getEndedAt()->format(Clock::TIME_FORMAT), $rawCriterionEvaluation['ended_at']);
        $this->assertJson($rawCriterionEvaluation['result']);
        $this->assertEquals([
            'rates' => [
                'mobile' => ['en_US' => 75]
            ],
            'status' => [
                'mobile' => ['en_US' => CriterionEvaluationResultStatus::DONE]
            ],
            'data' => [
                'attributes' => [
                    'mobile' => ['en_US' => []]
                ]
            ]
        ], json_decode($rawCriterionEvaluation['result'], true));
    }

    public function test_it_purges_product_evaluations_older_than_a_given_date()
    {
        $this->assertItPurgesEvaluationsOlderThanAGivenDate(
            $this->productCriterionEvaluationRepository,
            function($expectedCount) { $this->assertCountProductCriterionEvaluations($expectedCount); },
            function($criterionEvaluationId) { $this->assertProductCriterionEvaluationExists($criterionEvaluationId); }
        );
    }

    private function assertItPurgesEvaluationsOlderThanAGivenDate(
        CriterionEvaluationRepositoryInterface $criterionEvaluationRepository,
        callable $assertCountCriterionEvaluations,
        callable $assertCriterionEvaluationExists
    ) {
        $this->createEndedCriterionEvaluation($criterionEvaluationRepository, new Write\CriterionEvaluation(
            new CriterionEvaluationId('42_completeness_last_evaluation'),
            new CriterionCode('completeness'),
            new ProductId(42),
            new \DateTimeImmutable('2019-10-28 10:41:56.123'),
            CriterionEvaluationStatus::pending()
        ));
        $this->createEndedCriterionEvaluation($criterionEvaluationRepository, new Write\CriterionEvaluation(
            new CriterionEvaluationId('42_completeness_young_evaluation'),
            new CriterionCode('completeness'),
            new ProductId(42),
            new \DateTimeImmutable('2019-10-28 10:41:57.123'),
            CriterionEvaluationStatus::pending()
        ));
        $this->createEndedCriterionEvaluation($criterionEvaluationRepository, new Write\CriterionEvaluation(
            new CriterionEvaluationId('42_completeness_old_evaluation'),
            new CriterionCode('completeness'),
            new ProductId(42),
            new \DateTimeImmutable('2019-10-27 10:41:56.123'),
            CriterionEvaluationStatus::pending()
        ));
        $this->createEndedCriterionEvaluation($criterionEvaluationRepository, new Write\CriterionEvaluation(
            new CriterionEvaluationId('42_spelling_last_evaluation'),
            new CriterionCode('spelling'),
            new ProductId(42),
            new \DateTimeImmutable('2019-10-28 11:41:56.123'),
            CriterionEvaluationStatus::pending()
        ));
        $this->createEndedCriterionEvaluation($criterionEvaluationRepository, new Write\CriterionEvaluation(
            new CriterionEvaluationId('42_spelling_old_evaluation'),
            new CriterionCode('spelling'),
            new ProductId(42),
            new \DateTimeImmutable('2019-10-27 23:59:59.999'),
            CriterionEvaluationStatus::pending()
        ));
        $this->createEndedCriterionEvaluation($criterionEvaluationRepository, new Write\CriterionEvaluation(
            new CriterionEvaluationId('123_spelling_last_but_old_evaluation'),
            new CriterionCode('spelling'),
            new ProductId(123),
            new \DateTimeImmutable('2019-09-03 10:41:57.987'),
            CriterionEvaluationStatus::pending()
        ));
        $criterionEvaluationRepository->purgeUntil(new \DateTimeImmutable('2019-10-28 23:41:56'));

        $assertCountCriterionEvaluations(4);
        $assertCriterionEvaluationExists('42_completeness_last_evaluation');
        $assertCriterionEvaluationExists('42_completeness_young_evaluation');
        $assertCriterionEvaluationExists('42_spelling_last_evaluation');
        $assertCriterionEvaluationExists('123_spelling_last_but_old_evaluation');
    }

    public function test_it_deletes_all_deleted_products_pending_evaluations()
    {
        $criteria = $this->buildCollection();
        $criteria->add(
            new Write\CriterionEvaluation(
                new CriterionEvaluationId('56327717-7831-42d0-9ccf-28558eb3073c'),
                new CriterionCode('completeness'),
                new ProductId(666666),
                new \DateTimeImmutable('2019-10-28 10:41:56.000'),
                CriterionEvaluationStatus::pending()
            )
        );
        $this->productCriterionEvaluationRepository->create($criteria);

        $evaluations = $this->findAllProductEvaluations();
        $this->assertCount(3, $evaluations);

        $this->productCriterionEvaluationRepository->deleteUnknownProductsPendingEvaluations();

        $evaluations = $this->findAllProductEvaluations();
        $this->assertCount(2, $evaluations);
        $this->assertEquals($this->productId1, $evaluations[0]['product_id']);
        $this->assertEquals($this->productId2, $evaluations[1]['product_id']);
    }

    private function buildCollection(): Write\CriterionEvaluationCollection
    {
        $criteria = (new Write\CriterionEvaluationCollection)
            ->add(new Write\CriterionEvaluation(
                new CriterionEvaluationId('95f124de-45cd-495e-ac58-349086ad6cd4'),
                new CriterionCode('completeness'),
                $this->productId1,
                new \DateTimeImmutable('2019-10-28 10:41:56.123'),
                CriterionEvaluationStatus::pending()
            ))
            ->add(new Write\CriterionEvaluation(
                new CriterionEvaluationId('d7bcae1e-30c9-4626-9c4f-d06cae03e77e'),
                new CriterionCode('completion'),
                $this->productId2,
                new \DateTimeImmutable('2019-10-28 10:41:57.987'),
                CriterionEvaluationStatus::pending()
            ));

        return $criteria;
    }

    private function findAllProductEvaluations(): array
    {
        $stmt = $this->db->query('SELECT * FROM pimee_data_quality_insights_criteria_evaluation');

        return $stmt->fetchAll();
    }

    private function findOneProductCriterionEvaluationById(CriterionEvaluationId $criterionEvaluationId): ?array
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

    private function createEndedCriterionEvaluation(
        CriterionEvaluationRepositoryInterface $criterionEvaluationRepository,
        Write\CriterionEvaluation $criterionEvaluation
    ): void {
        $criteria = (new Write\CriterionEvaluationCollection)->add($criterionEvaluation);
        $criterionEvaluationRepository->create($criteria);

        $criterionEvaluation->end(new Write\CriterionEvaluationResult());
        $criterionEvaluationRepository->update((new Write\CriterionEvaluationCollection())->add($criterionEvaluation));
    }

    private function assertCountProductCriterionEvaluations(int $expectedCount): void
    {
        $stmt = $this->db->executeQuery(
            'SELECT COUNT(*) FROM pimee_data_quality_insights_criteria_evaluation'
        );
        $count = intval($stmt->fetchColumn());

        $this->assertSame($expectedCount, $count);
    }

    private function assertProductCriterionEvaluationExists(string $criterionEvaluationId): void
    {
        $stmt = $this->db->executeQuery(
            'SELECT 1 FROM pimee_data_quality_insights_criteria_evaluation WHERE id = :id',
            ['id' => $criterionEvaluationId]
        );

        $this->assertTrue((bool) $stmt->fetchColumn());
    }

    private function createProduct()
    {
        $product = $this->get('akeneo_integration_tests.catalog.product.builder')
            ->withIdentifier(strval(Uuid::uuid4()))
            ->build();
        $this->get('pim_catalog.saver.product')->save($product);

        return new ProductId((int) $product->getId());
    }

    private function clearAllEvaluations(): void
    {
        $this->db->executeQuery('DELETE FROM pimee_data_quality_insights_criteria_evaluation');
    }
}
