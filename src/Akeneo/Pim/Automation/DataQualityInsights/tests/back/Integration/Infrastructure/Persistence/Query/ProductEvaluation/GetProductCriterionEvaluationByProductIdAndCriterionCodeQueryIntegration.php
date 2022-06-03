<?php

declare(strict_types=1);

namespace Akeneo\Test\Akeneo\Pim\Automation\DataQualityInsights\Integration\Infrastructure\Persistence\Query\ProductEvaluation;

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
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation\GetProductCriterionEvaluationByProductIdAndCriterionCodeQuery;
use Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\DataQualityInsightsTestCase;

final class GetProductCriterionEvaluationByProductIdAndCriterionCodeQueryIntegration extends DataQualityInsightsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createChannel('ecommerce', ['locales' => ['en_US', 'fr_FR']]);
        $this->createAttribute('description');
        $this->createAttribute('name');
        $this->createAttribute('weight');
    }

    public function test_it_retrieves_a_product_evaluation(): void
    {
        $productId = new ProductId($this->createProductWithoutEvaluations('ziggy')->getId());
        $anotherProductId = new ProductId($this->createProductWithoutEvaluations('yggiz')->getId());

        $criterionEvaluationRepository = $this->get('akeneo.pim.automation.data_quality_insights.repository.product_criterion_evaluation');

        $expectedCompletenessEvaluation = $this->givenACompletenessEvaluationsDone($productId, $criterionEvaluationRepository);
        $this->givenAPendingSpellingEvaluation($productId, $criterionEvaluationRepository);
        $this->givenACompletenessEvaluationDoneForAnotherProduct($anotherProductId, $criterionEvaluationRepository);

        $productEvaluation = $this->get(GetProductCriterionEvaluationByProductIdAndCriterionCodeQuery::class)->execute($productId, new CriterionCode('completeness'));

        $this->assertSameEvaluation($expectedCompletenessEvaluation, $productEvaluation);
    }

    private function givenACompletenessEvaluationsDone(ProductId $productId, CriterionEvaluationRepositoryInterface $repository): Write\CriterionEvaluation
    {
        $channelEcommerce = new ChannelCode('ecommerce');
        $localeEn = new LocaleCode('en_US');
        $localeFr = new LocaleCode('fr_FR');

        $completenessEvaluationDone = new Write\CriterionEvaluation(
            new CriterionCode('completeness'),
            $productId,
            CriterionEvaluationStatus::pending()
        );

        $completenessEvaluationResult = (new Write\CriterionEvaluationResult())
            ->addRate($channelEcommerce, $localeEn, new Rate(90))
            ->addStatus($channelEcommerce, $localeEn, CriterionEvaluationResultStatus::done())
            ->addRateByAttributes($channelEcommerce, $localeEn, ['description' => 0])

            ->addRate($channelEcommerce, $localeFr, new Rate(75))
            ->addStatus($channelEcommerce, $localeFr, CriterionEvaluationResultStatus::done())
            ->addRateByAttributes($channelEcommerce, $localeFr, ['description' => 0, 'weight' => 0])
        ;

        $latestEvaluations = (new Write\CriterionEvaluationCollection())->add($completenessEvaluationDone);
        $repository->create($latestEvaluations);
        $completenessEvaluationDone->end($completenessEvaluationResult);
        $repository->update($latestEvaluations);

        return $completenessEvaluationDone;
    }

    private function givenAPendingSpellingEvaluation(ProductId $productId, CriterionEvaluationRepositoryInterface $repository): Write\CriterionEvaluation
    {
        $spellingEvaluationPending = new Write\CriterionEvaluation(
            new CriterionCode('spelling'),
            $productId,
            CriterionEvaluationStatus::pending()
        );

        $repository->create((new Write\CriterionEvaluationCollection())->add($spellingEvaluationPending));

        return $spellingEvaluationPending;
    }

    private function givenACompletenessEvaluationDoneForAnotherProduct(ProductId $productId, CriterionEvaluationRepositoryInterface $repository): void
    {
        $channelEcommerce = new ChannelCode('ecommerce');
        $localeEn = new LocaleCode('en_US');
        $localeFr = new LocaleCode('fr_FR');

        $completenessEvaluationDone = new Write\CriterionEvaluation(
            new CriterionCode('completeness'),
            $productId,
            CriterionEvaluationStatus::done()
        );

        $completenessEvaluationResult = (new Write\CriterionEvaluationResult())
            ->addRate($channelEcommerce, $localeEn, new Rate(100))
            ->addStatus($channelEcommerce, $localeEn, CriterionEvaluationResultStatus::done())

            ->addRate($channelEcommerce, $localeFr, new Rate(75))
            ->addStatus($channelEcommerce, $localeFr, CriterionEvaluationResultStatus::done())
            ->addRateByAttributes($channelEcommerce, $localeFr, ['name' => 0, 'weight' => 0])
        ;

        $latestEvaluations = (new Write\CriterionEvaluationCollection())->add($completenessEvaluationDone);
        $repository->create($latestEvaluations);
        $completenessEvaluationDone->end($completenessEvaluationResult);
        $repository->update($latestEvaluations);
    }

    private function assertSameEvaluation(Write\CriterionEvaluation $expectedEvaluation, Read\CriterionEvaluation $evaluation): void
    {
        $this->assertEquals($expectedEvaluation->getCriterionCode(), $evaluation->getCriterionCode());
        $this->assertEquals($expectedEvaluation->getProductId(), $evaluation->getProductId());
        $this->assertEquals($expectedEvaluation->getStatus(), $evaluation->getStatus());

        $expectedResult = $expectedEvaluation->getResult();

        $this->assertEquals($expectedResult->getDataToArray(), $evaluation->getResult()?->getData());
        $this->assertEquals($expectedResult->getRates()->toArrayInt(), $evaluation->getResult()?->getRates()->toArrayInt());
        $this->assertEquals($expectedResult->getStatus()->toArrayString(), $evaluation->getResult()?->getStatus()->toArrayString());
    }
}
