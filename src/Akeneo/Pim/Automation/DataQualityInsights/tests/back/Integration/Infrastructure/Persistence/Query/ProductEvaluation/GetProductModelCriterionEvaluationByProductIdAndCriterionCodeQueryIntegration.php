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
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation\GetProductModelCriterionEvaluationByProductIdAndCriterionCodeQuery;
use Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\DataQualityInsightsTestCase;

final class GetProductModelCriterionEvaluationByProductIdAndCriterionCodeQueryIntegration extends DataQualityInsightsTestCase
{
    protected function setUp(): void
    {
        {
            parent::setUp();

            $this->createChannel('ecommerce', ['locales' => ['en_US', 'fr_FR']]);
            $this->createAttribute('description');
            $this->createAttribute('name');
            $this->createAttribute('weight');
            $this->createSimpleSelectAttributeWithOptions('color', ['red', 'blue']);

            $this->createFamily('a_family', [
                'attributes' => ['description', 'name', 'weight', 'color'],
                'attribute_requirements' => [
                    'ecommerce' => ['name'],
                ],
            ]);

            $this->createFamilyVariant(
                'family_variant',
                'a_family',
                [
                    'variant_attribute_sets' => [
                        [
                            'level' => 1,
                            'axes' => ['color'],
                            'attributes' => ['description'],
                        ],
                    ],
                ]
            );
        }
    }

    public function test_it_retrieves_a_product_model_evaluation(): void
    {
        $productModelId = new ProductModelId($this->createProductModelWithoutEvaluations('ziggy', 'family_variant')->getId());
        $anotherProductModelId = new ProductModelId($this->createProductModelWithoutEvaluations('yggiz', 'family_variant')->getId());

        $criterionEvaluationRepository = $this->get('akeneo.pim.automation.data_quality_insights.repository.product_model_criterion_evaluation');

        $expectedCompletenessEvaluation = $this->givenACompletenessEvaluationsDone($productModelId, $criterionEvaluationRepository);
        $this->givenAPendingSpellingEvaluation($productModelId, $criterionEvaluationRepository);
        $this->givenACompletenessEvaluationDoneForAnotherProduct($anotherProductModelId, $criterionEvaluationRepository);

        $productEvaluation = $this->get(GetProductModelCriterionEvaluationByProductIdAndCriterionCodeQuery::class)->execute($productModelId, new CriterionCode('completeness'));

        $this->assertSameEvaluation($expectedCompletenessEvaluation, $productEvaluation);
    }

    private function givenACompletenessEvaluationsDone(ProductModelId $productId, CriterionEvaluationRepositoryInterface $repository): Write\CriterionEvaluation
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
            ->addRateByAttributes($channelEcommerce, $localeFr, ['description' => 0, 'weight' => 0]);

        $latestEvaluations = (new Write\CriterionEvaluationCollection())->add($completenessEvaluationDone);
        $repository->create($latestEvaluations);
        $completenessEvaluationDone->end($completenessEvaluationResult);
        $repository->update($latestEvaluations);

        return $completenessEvaluationDone;
    }

    private function givenAPendingSpellingEvaluation(ProductModelId $productModelId, CriterionEvaluationRepositoryInterface $repository): Write\CriterionEvaluation
    {
        $spellingEvaluationPending = new Write\CriterionEvaluation(
            new CriterionCode('spelling'),
            $productModelId,
            CriterionEvaluationStatus::pending()
        );

        $repository->create((new Write\CriterionEvaluationCollection())->add($spellingEvaluationPending));

        return $spellingEvaluationPending;
    }

    private function givenACompletenessEvaluationDoneForAnotherProduct(ProductModelId $productModelId, CriterionEvaluationRepositoryInterface $repository): void
    {
        $channelEcommerce = new ChannelCode('ecommerce');
        $localeEn = new LocaleCode('en_US');
        $localeFr = new LocaleCode('fr_FR');

        $completenessEvaluationDone = new Write\CriterionEvaluation(
            new CriterionCode('completeness'),
            $productModelId,
            CriterionEvaluationStatus::done()
        );

        $completenessEvaluationResult = (new Write\CriterionEvaluationResult())
            ->addRate($channelEcommerce, $localeEn, new Rate(100))
            ->addStatus($channelEcommerce, $localeEn, CriterionEvaluationResultStatus::done())
            ->addRate($channelEcommerce, $localeFr, new Rate(75))
            ->addStatus($channelEcommerce, $localeFr, CriterionEvaluationResultStatus::done())
            ->addRateByAttributes($channelEcommerce, $localeFr, ['name' => 0, 'weight' => 0]);

        $latestEvaluations = (new Write\CriterionEvaluationCollection())->add($completenessEvaluationDone);
        $repository->create($latestEvaluations);
        $completenessEvaluationDone->end($completenessEvaluationResult);
        $repository->update($latestEvaluations);
    }

    private function assertSameEvaluation(Write\CriterionEvaluation $expectedEvaluation, Read\CriterionEvaluation $evaluation): void
    {
        $this->assertEquals($expectedEvaluation->getCriterionCode(), $evaluation->getCriterionCode());
        $this->assertEquals($expectedEvaluation->getEntityId(), $evaluation->getProductId());
        $this->assertEquals($expectedEvaluation->getStatus(), $evaluation->getStatus());

        $expectedResult = $expectedEvaluation->getResult();

        $this->assertEquals($expectedResult->getDataToArray(), $evaluation->getResult()?->getData());
        $this->assertEquals($expectedResult->getRates()->toArrayInt(), $evaluation->getResult()?->getRates()->toArrayInt());
        $this->assertEquals($expectedResult->getStatus()->toArrayString(), $evaluation->getResult()?->getStatus()->toArrayString());
    }
}
