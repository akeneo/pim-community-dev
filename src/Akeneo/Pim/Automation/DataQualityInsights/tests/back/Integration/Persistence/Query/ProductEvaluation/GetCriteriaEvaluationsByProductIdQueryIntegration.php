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
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Clock\SystemClock;
use Akeneo\Test\Integration\TestCase;

final class GetCriteriaEvaluationsByProductIdQueryIntegration extends TestCase
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

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_gives_the_criteria_evaluations_of_a_product()
    {
        $expectedCompletenessEvaluation = $this->givenACompletenessEvaluationsDone($this->productCriterionEvaluationRepository);
        $expectedSpellingEvaluation = $this->givenAPendingSpellingEvaluation($this->productCriterionEvaluationRepository);
        $this->givenACompletenessEvaluationDoneForAnotherProduct($this->productCriterionEvaluationRepository);

        $evaluations = $this->get('akeneo.pim.automation.data_quality_insights.query.get_product_criteria_evaluations')
            ->execute(new ProductId(42));

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
        $expectedCompletenessEvaluation = $this->givenACompletenessEvaluationsDone($this->productModelCriterionEvaluationRepository);
        $expectedSpellingEvaluation = $this->givenAPendingSpellingEvaluation($this->productModelCriterionEvaluationRepository);
        $this->givenACompletenessEvaluationDoneForAnotherProduct($this->productModelCriterionEvaluationRepository);

        $evaluations = $this->get('akeneo.pim.automation.data_quality_insights.query.get_product_model_criteria_evaluations')
            ->execute(new ProductId(42));

        $this->assertCount(2, $evaluations, 'There should be 2 evaluations');

        $completenessEvaluation = $evaluations->get($expectedCompletenessEvaluation->getCriterionCode());
        $this->assertNotNull($completenessEvaluation, 'There should be a completeness evaluation');
        $this->assertSameEvaluationResults($expectedCompletenessEvaluation->getResult(), $completenessEvaluation->getResult());
        $this->assertEquals($expectedCompletenessEvaluation->getStatus(), $completenessEvaluation->getStatus());

        $spellingEvaluation = $evaluations->get($expectedSpellingEvaluation->getCriterionCode());
        $this->assertNotNull($spellingEvaluation, 'There should be a spelling evaluation');
    }

    private function givenACompletenessEvaluationsDone(CriterionEvaluationRepositoryInterface $repository): Write\CriterionEvaluation
    {
        $channelEcommerce = new ChannelCode('ecommerce');
        $localeEn = new LocaleCode('en_US');
        $localeFr = new LocaleCode('fr_FR');

        $completenessEvaluationDone = new Write\CriterionEvaluation(
            new CriterionCode('completeness'),
            new ProductId(42),
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

    private function givenAPendingSpellingEvaluation(CriterionEvaluationRepositoryInterface $repository): Write\CriterionEvaluation
    {
        $spellingEvaluationPending = new Write\CriterionEvaluation(
            new CriterionCode('spelling'),
            new ProductId(42),
            CriterionEvaluationStatus::pending()
        );

        $repository->create((new Write\CriterionEvaluationCollection())->add($spellingEvaluationPending));

        return $spellingEvaluationPending;
    }

    private function givenACompletenessEvaluationDoneForAnotherProduct(CriterionEvaluationRepositoryInterface $repository): void
    {
        $channelEcommerce = new ChannelCode('ecommerce');
        $localeEn = new LocaleCode('en_US');
        $localeFr = new LocaleCode('fr_FR');

        $completenessEvaluationDone = new Write\CriterionEvaluation(
            new CriterionCode('completeness'),
            new ProductId(123),
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

    private function getClock(): Clock
    {
        return $this->get(SystemClock::class);
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
