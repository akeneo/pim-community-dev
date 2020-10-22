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
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;
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
use Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\DataQualityInsightsTestCase;
use Doctrine\DBAL\Connection;

final class ProductModelCriterionEvaluationRepositoryIntegration extends DataQualityInsightsTestCase
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

        $this->deleteAllProductModelCriterionEvaluations();
    }

    public function test_it_creates_a_collection_of_product_model_criteria_evaluations()
    {
        $productModelId = new ProductId($this->createProductModel('ziggy', 'familyVariantA1')->getId());
        $this->deleteAllProductModelCriterionEvaluations();

        $this->assertCountProductModelCriterionEvaluations(0);

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
        $productIdWithExistingEvaluation = new ProductId($this->createProductModel('ziggy', 'familyVariantA1')->getId());
        $productIdWithoutEvaluation = new ProductId($this->createProductModel('yggiz', 'familyVariantA1')->getId());
        $criterionCode = new CriterionCode('completeness');
        $this->deleteAllProductModelCriterionEvaluations();

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

        $updatedEvaluation = $this->findCriterionEvaluation($productIdWithExistingEvaluation, $criterionCode);
        $this->assertSame($existingEvaluation->getEvaluatedAt()->format(Clock::TIME_FORMAT), $updatedEvaluation->getEvaluatedAt()->format(Clock::TIME_FORMAT));
        $this->assertSame(CriterionEvaluationStatus::PENDING, strval($updatedEvaluation->getStatus()));
        $this->assertNotNull($updatedEvaluation->getResult());
    }

    public function test_it_updates_product_model_criteria_evaluations()
    {
        $productModelId = new ProductId($this->createProductModel('ziggy', 'familyVariantA1')->getId());
        $this->deleteAllProductModelCriterionEvaluations();

        $criterionEvaluationA = new Write\CriterionEvaluation(
            new CriterionCode('completeness'),
            $productModelId,
            CriterionEvaluationStatus::pending()
        );
        $criterionEvaluationB = new Write\CriterionEvaluation(
            new CriterionCode('spelling'),
            $productModelId,
            CriterionEvaluationStatus::pending()
        );
        $criteriaEvaluationCollection = $this->buildCollection();
        $criteriaEvaluationCollection->add($criterionEvaluationA)->add($criterionEvaluationB);
        $this->productModelCriterionEvaluationRepository->create($criteriaEvaluationCollection);

        $evaluationResultA = (new Write\CriterionEvaluationResult())
            ->addRate(new ChannelCode('tablet'), new LocaleCode('en_US'), new Rate(75))
            ->addStatus(new ChannelCode('tablet'), new LocaleCode('en_US'), CriterionEvaluationResultStatus::done())
        ;
        $evaluationResultB = (new Write\CriterionEvaluationResult())
            ->addRate(new ChannelCode('tablet'), new LocaleCode('en_US'), new Rate(64))
            ->addStatus(new ChannelCode('tablet'), new LocaleCode('en_US'), CriterionEvaluationResultStatus::done())
            ->addRateByAttributes(new ChannelCode('tablet'), new LocaleCode('en_US'), ['a_text_area' => 13])
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

        $updatedCriterionEvaluationA = $this->findCriterionEvaluation($criterionEvaluationA->getProductId(), $criterionEvaluationA->getCriterionCode());
        $updatedCriterionEvaluationB = $this->findCriterionEvaluation($criterionEvaluationB->getProductId(), $criterionEvaluationB->getCriterionCode());

        $this->assertCriterionEvaluationEquals($criterionEvaluationA, $updatedCriterionEvaluationA);
        $this->assertCriterionEvaluationEquals($criterionEvaluationB, $updatedCriterionEvaluationB);
    }

    private function buildCollection(): Write\CriterionEvaluationCollection
    {
        return (new Write\CriterionEvaluationCollection)
            ->add(new Write\CriterionEvaluation(
                new CriterionCode('completeness'),
                new ProductId($this->createProductModel('a_product_model', 'familyVariantA1')->getId()),
                CriterionEvaluationStatus::pending()
            ))
            ->add(new Write\CriterionEvaluation(
                new CriterionCode('completion'),
                new ProductId($this->createProductModel('another_product_model', 'familyVariantA1')->getId()),
                CriterionEvaluationStatus::pending()
            ));
    }

    private function findAllProductModelEvaluations(): array
    {
        $stmt = $this->db->query('SELECT * FROM pim_data_quality_insights_product_model_criteria_evaluation');

        return $stmt->fetchAll();
    }

    private function findCriterionEvaluation(ProductId $productId, CriterionCode $criterionCode): ?Read\CriterionEvaluation
    {
        $evaluations = $this->get('akeneo.pim.automation.data_quality_insights.query.get_product_model_criteria_evaluations')
            ->execute($productId);

        return $evaluations->get($criterionCode);
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

    private function assertCriterionEvaluationEquals(Write\CriterionEvaluation $expectedCriterionEvaluation, Read\CriterionEvaluation $criterionEvaluation): void
    {
        $this->assertEquals($expectedCriterionEvaluation->getCriterionCode(), $criterionEvaluation->getCriterionCode());
        $this->assertEquals($expectedCriterionEvaluation->getProductId(), $criterionEvaluation->getProductId());
        $this->assertEvaluatedAtEquals($expectedCriterionEvaluation->getEvaluatedAt(), $criterionEvaluation->getEvaluatedAt());
        $this->assertEquals($expectedCriterionEvaluation->getStatus(), $criterionEvaluation->getStatus());
        $this->assertEquals($expectedCriterionEvaluation->getResult()->getRates()->toArrayInt(), $criterionEvaluation->getResult()->getRates()->toArrayInt());
        $this->assertEquals($expectedCriterionEvaluation->getResult()->getStatus()->toArrayString(), $criterionEvaluation->getResult()->getStatus()->toArrayString());
        $this->assertEquals($expectedCriterionEvaluation->getResult()->getDataToArray(), $criterionEvaluation->getResult()->getData());
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
            ->addRateByAttributes($channelEcommerce, $localeEn, ['a_text_area' => rand(0, 100)])
        ;

        $evaluation->end($evaluationResult);

        $this->productModelCriterionEvaluationRepository->update($evaluations);

        return $evaluation;
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
