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
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationResultStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;

final class ProductModelCriterionEvaluationRepositoryIntegration extends TestCase
{
    /** @var Connection */
    private $db;

    /** @var CriterionEvaluationRepositoryInterface */
    private $productModelCriterionEvaluationRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = $this->get('database_connection');
        $this->productModelCriterionEvaluationRepository = $this->get('akeneo.pim.automation.data_quality_insights.repository.product_model_criterion_evaluation');

        $this->clearAllEvaluations();
    }

    public function test_it_creates_a_collection_of_product_model_criteria_evaluations()
    {
        $this->assertCountProductModelCriterionEvaluations(0);

        $productModelId = new ProductId(42);
        $criteria = (new Write\CriterionEvaluationCollection)
            ->add(new Write\CriterionEvaluation(
                new CriterionCode('completeness'),
                $productModelId,
                CriterionEvaluationStatus::pending()
            ))
            ->add(new Write\CriterionEvaluation(
                new CriterionCode('completion'),
                $productModelId,
                CriterionEvaluationStatus::pending()
        ));
        $this->productModelCriterionEvaluationRepository->create($criteria);

        $evaluations = $this->findAllProductModelEvaluations();
        $this->assertCount(2, $evaluations);

        $this->assertEquals('completeness', $evaluations[0]['criterion_code']);
        $this->assertEquals($productModelId, $evaluations[0]['product_id']);
        $this->assertEquals(CriterionEvaluationStatus::PENDING, $evaluations[0]['status']);
        $this->assertNull($evaluations[0]['evaluated_at']);
        $this->assertNull($evaluations[0]['result']);

        $this->assertEquals('completion', $evaluations[1]['criterion_code']);
        $this->assertEquals($productModelId, $evaluations[1]['product_id']);
        $this->assertEquals(CriterionEvaluationStatus::PENDING, $evaluations[1]['status']);
        $this->assertNull($evaluations[1]['evaluated_at']);
        $this->assertNull($evaluations[1]['result']);
    }

    public function test_it_updates_a_criterion_evaluation_instead_of_creating_it_when_it_already_exists()
    {
        $productIdWithExistingEvaluation = new ProductId(42);
        $productIdWithoutEvaluation = new ProductId(123);
        $criterionCode = new CriterionCode('completeness');

        $existingEvaluation = $this->givenAnExistingCriterionEvaluation($criterionCode, $productIdWithExistingEvaluation);

        $this->productModelCriterionEvaluationRepository->create((new Write\CriterionEvaluationCollection)
            ->add(new Write\CriterionEvaluation(
                $criterionCode,
                $productIdWithExistingEvaluation,
                CriterionEvaluationStatus::pending()
            ))
            ->add(new Write\CriterionEvaluation(
                $criterionCode,
                $productIdWithoutEvaluation,
                CriterionEvaluationStatus::pending()
            )));

        $this->assertCountProductModelCriterionEvaluations(2);

        $updatedEvaluation = $this->findEvaluation($productIdWithExistingEvaluation, $criterionCode);
        $this->assertSame($existingEvaluation->getEvaluatedAt()->format(Clock::TIME_FORMAT), $updatedEvaluation['evaluated_at']);
        $this->assertSame(CriterionEvaluationStatus::PENDING, $updatedEvaluation['status']);
        $this->assertNotNull($updatedEvaluation['result']);
        $this->assertNotEmpty(json_decode($updatedEvaluation['result'], true));
    }

    public function test_it_updates_product_model_criteria_evaluations()
    {
        $criterionEvaluationA = new Write\CriterionEvaluation(
            new CriterionCode('completeness'),
            new ProductId(567),
            CriterionEvaluationStatus::pending()
        );
        $criterionEvaluationB = new Write\CriterionEvaluation(
            new CriterionCode('spelling'),
            new ProductId(567),
            CriterionEvaluationStatus::pending()
        );
        $criteriaEvaluationCollection = $this->buildCollection();
        $criteriaEvaluationCollection->add($criterionEvaluationA)->add($criterionEvaluationB);
        $this->productModelCriterionEvaluationRepository->create($criteriaEvaluationCollection);

        $evaluationResultA = (new Write\CriterionEvaluationResult())
            ->addRate(new ChannelCode('mobile'), new LocaleCode('en_US'), new Rate(75))
            ->addStatus(new ChannelCode('mobile'), new LocaleCode('en_US'), CriterionEvaluationResultStatus::done())
        ;
        $evaluationResultB = (new Write\CriterionEvaluationResult())
            ->addRate(new ChannelCode('mobile'), new LocaleCode('en_US'), new Rate(64))
            ->addStatus(new ChannelCode('mobile'), new LocaleCode('en_US'), CriterionEvaluationResultStatus::done())
            ->addRateByAttributes(new ChannelCode('mobile'), new LocaleCode('en_US'), ['description' => 13])
        ;

        $criterionEvaluationA->start();
        $criterionEvaluationA->end($evaluationResultA);
        $criterionEvaluationB->start();
        $criterionEvaluationB->end($evaluationResultB);
        $this->productModelCriterionEvaluationRepository->update(
            (new Write\CriterionEvaluationCollection())
                ->add($criterionEvaluationA)
                ->add($criterionEvaluationB)
        );

        $rawCriterionEvaluationA = $this->findEvaluation($criterionEvaluationA->getProductId(), $criterionEvaluationA->getCriterionCode());
        $rawCriterionEvaluationB = $this->findEvaluation($criterionEvaluationB->getProductId(), $criterionEvaluationB->getCriterionCode());

        $this->assertCriterionEvaluationEquals($criterionEvaluationA, $rawCriterionEvaluationA);
        $this->assertCriterionEvaluationEquals($criterionEvaluationB, $rawCriterionEvaluationB);
    }

    public function test_it_deletes_all_the_evaluations_of_unknown_product_models()
    {
        $existingProductModelId = $this->createProductModel();
        $unknownProductModelId = new ProductId(666666);

        $criteria = (new Write\CriterionEvaluationCollection)
            ->add(new Write\CriterionEvaluation(
                new CriterionCode('completeness'),
                $existingProductModelId,
                CriterionEvaluationStatus::pending()
            ))
            ->add(new Write\CriterionEvaluation(
                new CriterionCode('completeness'),
                $unknownProductModelId,
                CriterionEvaluationStatus::pending()
            ))
            ->add(new Write\CriterionEvaluation(
                new CriterionCode('spelling'),
                $unknownProductModelId,
                CriterionEvaluationStatus::error()
            ));

        $this->productModelCriterionEvaluationRepository->create($criteria);
        $this->assertCountProductModelCriterionEvaluations(3);

        $this->productModelCriterionEvaluationRepository->deleteUnknownProductsEvaluations();

        $evaluations = $this->findAllProductModelEvaluations();
        $this->assertCount(1, $evaluations);
        $this->assertSame(strval($existingProductModelId), $evaluations[0]['product_id']);
    }

    private function buildCollection(): Write\CriterionEvaluationCollection
    {
        return (new Write\CriterionEvaluationCollection)
            ->add(new Write\CriterionEvaluation(
                new CriterionCode('completeness'),
                new ProductId(1),
                CriterionEvaluationStatus::pending()
            ))
            ->add(new Write\CriterionEvaluation(
                new CriterionCode('completion'),
                new ProductId(2),
                CriterionEvaluationStatus::pending()
            ));
    }

    private function findAllProductModelEvaluations(): array
    {
        $stmt = $this->db->query('SELECT * FROM pim_data_quality_insights_product_model_criteria_evaluation');

        return $stmt->fetchAll();
    }

    private function findEvaluation(ProductId $productId, CriterionCode $criterionCode): array
    {
        $query = <<<SQL
SELECT * FROM pim_data_quality_insights_product_model_criteria_evaluation
WHERE product_id = :productId AND criterion_code = :criterionCode
SQL;

        $evaluation = $this->get('database_connection')->executeQuery($query, [
            'productId' => $productId->toInt(),
            'criterionCode' => $criterionCode,
        ])->fetch(\PDO::FETCH_ASSOC);

        return false !== $evaluation ? $evaluation : [];
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function assertCountProductModelCriterionEvaluations(int $expectedCount): void
    {
        $stmt = $this->db->executeQuery(
            'SELECT COUNT(*) FROM pim_data_quality_insights_product_model_criteria_evaluation'
        );
        $count = intval($stmt->fetchColumn());

        $this->assertSame($expectedCount, $count);
    }

    private function assertCriterionEvaluationEquals(Write\CriterionEvaluation $criterionEvaluation, array $rawCriterionEvaluation): void
    {
        $this->assertEquals(strval($criterionEvaluation->getCriterionCode()), $rawCriterionEvaluation['criterion_code']);
        $this->assertEquals($criterionEvaluation->getProductId()->toInt(), $rawCriterionEvaluation['product_id']);
        $this->assertEvaluatedAtEquals($criterionEvaluation->getEvaluatedAt(), $rawCriterionEvaluation['evaluated_at']);
        $this->assertEquals(strval($criterionEvaluation->getStatus()), $rawCriterionEvaluation['status']);
        $this->assertJson($rawCriterionEvaluation['result']);
        $this->assertEquals([
            'rates' => $criterionEvaluation->getResult()->getRates()->toArrayInt(),
            'status' => $criterionEvaluation->getResult()->getStatus()->toArrayString(),
            'data' => $criterionEvaluation->getResult()->getDataToArray(),
        ], json_decode($rawCriterionEvaluation['result'], true));
    }

    private function createProductModel()
    {
        $product = $this->get('akeneo_integration_tests.catalog.product_model.builder')
            ->withCode(strval(Uuid::uuid4()))
            ->withFamilyVariant('familyVariantA1')
            ->build();
        $this->get('pim_catalog.saver.product_model')->save($product);

        $this->clearAllEvaluations();

        return new ProductId((int) $product->getId());
    }

    private function clearAllEvaluations(): void
    {
        $this->db->executeQuery('DELETE FROM pim_data_quality_insights_product_model_criteria_evaluation');
    }

    private function givenAnExistingCriterionEvaluation(CriterionCode $criterionCode, ProductId $productModelId): Write\CriterionEvaluation
    {
        $evaluation = new Write\CriterionEvaluation(
                $criterionCode,
                $productModelId,
                CriterionEvaluationStatus::pending()
            );
        $evaluations = (new Write\CriterionEvaluationCollection)->add($evaluation);

        $this->productModelCriterionEvaluationRepository->create($evaluations);

        $channelEcommerce = new ChannelCode('ecommerce');
        $localeEn = new LocaleCode('en_US');

        $evaluationResult = (new Write\CriterionEvaluationResult())
            ->addRate($channelEcommerce, $localeEn, new Rate(rand(0, 100)))
            ->addStatus($channelEcommerce, $localeEn, CriterionEvaluationResultStatus::done())
            ->addRateByAttributes($channelEcommerce, $localeEn, ['description' => rand(0, 100)])
        ;

        $evaluation->end($evaluationResult);

        $this->productModelCriterionEvaluationRepository->update($evaluations);

        return $evaluation;
    }

    private function assertEvaluatedAtEquals(?\DateTimeImmutable $expectedDate, ?string $date): void
    {
        if (null === $expectedDate) {
            $this->assertNull($date);
        } else {
           $this->assertEquals($expectedDate->format(Clock::TIME_FORMAT), $date);
        }
    }
}
