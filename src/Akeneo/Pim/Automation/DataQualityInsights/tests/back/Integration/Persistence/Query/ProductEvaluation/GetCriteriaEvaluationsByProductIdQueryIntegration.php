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

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Persistence\Query\ProductEvaluation;

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

final class GetCriteriaEvaluationsByProductIdQueryIntegration extends DataQualityInsightsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createChannel('ecommerce', ['locales' => ['en_US', 'fr_FR']]);
        $this->createAttribute('description');
        $this->createAttribute('name');
        $this->createAttribute('weight');
    }

    public function test_it_gives_the_criteria_evaluations_of_a_product()
    {
        $productId = new ProductId($this->createProductWithoutEvaluations('ziggy')->getId());
        $anotherProductId = new ProductId($this->createProductWithoutEvaluations('yggiz')->getId());

        $criterionEvaluationRepository = $this->get('akeneo.pim.automation.data_quality_insights.repository.product_criterion_evaluation');

        $expectedCompletenessEvaluation = $this->givenACompletenessEvaluationsDone($productId, $criterionEvaluationRepository);
        $expectedSpellingEvaluation = $this->givenAPendingSpellingEvaluation($productId, $criterionEvaluationRepository);
        $this->givenACompletenessEvaluationDoneForAnotherProduct($anotherProductId, $criterionEvaluationRepository);

        $evaluations = $this->get('akeneo.pim.automation.data_quality_insights.query.get_product_criteria_evaluations')
            ->execute($productId);

        $this->assertCount(2, $evaluations, 'There should be 2 evaluations');

        $completenessEvaluation = $evaluations->get($expectedCompletenessEvaluation->getCriterionCode());
        $this->assertNotNull($completenessEvaluation, 'There should be a completeness evaluation');
        $this->assertSameEvaluationResults($expectedCompletenessEvaluation->getResult(), $completenessEvaluation->getResult());
        $this->assertEquals($expectedCompletenessEvaluation->getStatus(), $completenessEvaluation->getStatus());

        $spellingEvaluation = $evaluations->get($expectedSpellingEvaluation->getCriterionCode());
        $this->assertNotNull($spellingEvaluation, 'There should be a spelling evaluation');
    }

    public function test_it_gives_the_criteria_evaluations_of_a_product_model()
    {
        $this->createMinimalFamilyAndFamilyVariant('a_family', 'a_family_variant');

        $productModelId = new ProductId($this->createProductModelWithoutEvaluations('ziggy', 'a_family_variant')->getId());
        $anotherProductModelId = new ProductId($this->createProductModelWithoutEvaluations('yggiz', 'a_family_variant')->getId());

        $criterionEvaluationRepository = $this->get('akeneo.pim.automation.data_quality_insights.repository.product_model_criterion_evaluation');

        $expectedCompletenessEvaluation = $this->givenACompletenessEvaluationsDone($productModelId, $criterionEvaluationRepository);
        $expectedSpellingEvaluation = $this->givenAPendingSpellingEvaluation($productModelId, $criterionEvaluationRepository);
        $this->givenACompletenessEvaluationDoneForAnotherProduct($anotherProductModelId, $criterionEvaluationRepository);

        $evaluations = $this->get('akeneo.pim.automation.data_quality_insights.query.get_product_model_criteria_evaluations')
            ->execute($productModelId);

        $this->assertCount(2, $evaluations, 'There should be 2 evaluations');

        $completenessEvaluation = $evaluations->get($expectedCompletenessEvaluation->getCriterionCode());
        $this->assertNotNull($completenessEvaluation, 'There should be a completeness evaluation');
        $this->assertSameEvaluationResults($expectedCompletenessEvaluation->getResult(), $completenessEvaluation->getResult());
        $this->assertEquals($expectedCompletenessEvaluation->getStatus(), $completenessEvaluation->getStatus());

        $spellingEvaluation = $evaluations->get($expectedSpellingEvaluation->getCriterionCode());
        $this->assertNotNull($spellingEvaluation, 'There should be a spelling evaluation');
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

    private function assertSameEvaluationResults(?Write\CriterionEvaluationResult $expectedResult, ?Read\CriterionEvaluationResult $result): void
    {
        if (null === $expectedResult) {
            $this->assertNull($result);
        }

        $this->assertEquals($expectedResult->getDataToArray(), $result->getData());
        $this->assertEquals($expectedResult->getRates()->toArrayInt(), $result->getRates()->toArrayInt());
        $this->assertEquals($expectedResult->getStatus()->toArrayString(), $result->getStatus()->toArrayString());
    }
}
