<?php

declare(strict_types=1);

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
use Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\DataQualityInsightsTestCase;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProductCriterionEvaluationRepositoryIntegration extends DataQualityInsightsTestCase
{
    /** @var Connection */
    private $db;

    /** @var CriterionEvaluationRepositoryInterface */
    private $productCriterionEvaluationRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = $this->get('database_connection');
        $this->productCriterionEvaluationRepository = $this->get('akeneo.pim.automation.data_quality_insights.repository.product_criterion_evaluation');
    }

    public function test_it_creates_a_collection_of_product_criteria_evaluations()
    {
        $this->assertCountProductCriterionEvaluations(0);
        $productId = new ProductId($this->createProduct('ziggy')->getId());
        $this->deleteAllProductCriterionEvaluations();

        $criteria = (new Write\CriterionEvaluationCollection)
            ->add(new Write\CriterionEvaluation(
                new CriterionCode('completeness'),
                $productId,
                CriterionEvaluationStatus::pending()
            ))
            ->add(new Write\CriterionEvaluation(
                new CriterionCode('completion'),
                $productId,
                CriterionEvaluationStatus::pending()
        ));

        $this->productCriterionEvaluationRepository->create($criteria);

        $evaluations = $this->findAllProductEvaluations();
        $this->assertCount(2, $evaluations);

        $this->assertEquals('completeness', $evaluations[0]['criterion_code']);
        $this->assertEquals($productId, $evaluations[0]['product_id']);
        $this->assertEquals(CriterionEvaluationStatus::PENDING, $evaluations[0]['status']);
        $this->assertNull($evaluations[0]['evaluated_at']);
        $this->assertNull($evaluations[0]['result']);

        $this->assertEquals('completion', $evaluations[1]['criterion_code']);
        $this->assertEquals($productId, $evaluations[1]['product_id']);
        $this->assertEquals(CriterionEvaluationStatus::PENDING, $evaluations[1]['status']);
        $this->assertNull($evaluations[1]['result']);
    }

    public function test_it_updates_the_status_of_a_criterion_evaluation_instead_of_creating_it_when_it_already_exists()
    {
        $productIdWithExistingEvaluation = new ProductId($this->createProduct('product_with_evaluation')->getId());
        $productIdWithoutEvaluation = new ProductId($this->createProduct('product_without_evaluation')->getId());
        $criterionCode = new CriterionCode('completeness');
        $this->deleteAllProductCriterionEvaluations();

        $existingEvaluation = $this->givenAnExistingCriterionEvaluation($criterionCode, $productIdWithExistingEvaluation);

        $this->productCriterionEvaluationRepository->create((new Write\CriterionEvaluationCollection)
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

        $this->assertCountProductCriterionEvaluations(2);

        $updatedEvaluation = $this->findCriterionEvaluation($productIdWithExistingEvaluation, $criterionCode);
        $this->assertSame($existingEvaluation->getEvaluatedAt()->format(Clock::TIME_FORMAT), $updatedEvaluation->getEvaluatedAt()->format(Clock::TIME_FORMAT));
        $this->assertSame(CriterionEvaluationStatus::PENDING, strval($updatedEvaluation->getStatus()));
        $this->assertNotNull($updatedEvaluation->getResult());
    }

    public function test_it_updates_product_criteria_evaluations()
    {
        $this->createAttribute('description');
        $this->createChannel('mobile', ['locales' => ['en_US']]);

        $productId = new ProductId($this->createProduct('ziggy')->getId());

        $criterionEvaluationA = new Write\CriterionEvaluation(
            new CriterionCode('completeness'),
            $productId,
            CriterionEvaluationStatus::pending()
        );
        $criterionEvaluationB = new Write\CriterionEvaluation(
            new CriterionCode('spelling'),
            $productId,
            CriterionEvaluationStatus::pending()
        );
        $criteriaEvaluationCollection = $this->buildCollection();
        $criteriaEvaluationCollection->add($criterionEvaluationA)->add($criterionEvaluationB);
        $this->deleteAllProductCriterionEvaluations();

        $this->productCriterionEvaluationRepository->create($criteriaEvaluationCollection);

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
        $this->productCriterionEvaluationRepository->update(
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
                new ProductId($this->createProduct('a_product')->getId()),
                CriterionEvaluationStatus::pending()
            ))
            ->add(new Write\CriterionEvaluation(
                new CriterionCode('completion'),
                new ProductId($this->createProduct('another_product')->getId()),
                CriterionEvaluationStatus::pending()
            ));
    }

    private function findAllProductEvaluations(): array
    {
        $stmt = $this->db->query('SELECT * FROM pim_data_quality_insights_product_criteria_evaluation');

        return $stmt->fetchAll();
    }

    private function findCriterionEvaluation(ProductId $productId, CriterionCode $criterionCode): ?Read\CriterionEvaluation
    {
        $evaluations = $this->get('akeneo.pim.automation.data_quality_insights.query.get_product_criteria_evaluations')
            ->execute($productId);

        return $evaluations->get($criterionCode);
    }

    private function assertCountProductCriterionEvaluations(int $expectedCount): void
    {
        $stmt = $this->db->executeQuery(
            'SELECT COUNT(*) FROM pim_data_quality_insights_product_criteria_evaluation'
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

    private function givenAnExistingCriterionEvaluation(CriterionCode $criterionCode, ProductId $productId): Write\CriterionEvaluation
    {
        $evaluation = new Write\CriterionEvaluation(
            $criterionCode,
            $productId,
            CriterionEvaluationStatus::pending()
        );
        $evaluations = (new Write\CriterionEvaluationCollection())->add($evaluation);
        $this->productCriterionEvaluationRepository->create($evaluations);

        $channelEcommerce = new ChannelCode('ecommerce');
        $localeEn = new LocaleCode('en_US');
        $this->createAttribute('description');

        $evaluationResult = (new Write\CriterionEvaluationResult())
            ->addRate($channelEcommerce, $localeEn, new Rate(rand(0, 100)))
            ->addStatus($channelEcommerce, $localeEn, CriterionEvaluationResultStatus::done())
            ->addRateByAttributes($channelEcommerce, $localeEn, ['description' => rand(0, 100)])
        ;

        $evaluation->end($evaluationResult);
        $this->productCriterionEvaluationRepository->update($evaluations);

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
