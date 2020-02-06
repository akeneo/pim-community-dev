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

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Persistence\Query;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\CriterionEvaluationResult;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\CriterionRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetLatestCriteriaEvaluationsByProductIdQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\CriterionEvaluationRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Clock\SystemClock;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\GetLatestCriteriaEvaluationsByProductIdQuery;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository\CriterionEvaluationRepository;
use Akeneo\Test\Integration\TestCase;

final class GetLatestCriteriaEvaluationsByProductIdQueryIntegration extends TestCase
{
    private const LATEST_COMPLETENESS_EVALUATION_ID = 'c328c229-d5f8-491a-8292-e34e44676968';
    private const LATEST_SPELLING_EVALUATION_ID = '905be2a7-1397-4d56-9cab-b6bc3f2a5b7a';
    private const LATEST_GRAMMAR_EVALUATION_ID = '089c4358-231b-48ed-9484-48dbe14b1d51';

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_gives_the_latest_criteria_evaluations_of_a_product()
    {
        $this->givenTwoCompletenessEvaluationsDone();
        $this->givenAPendingSpellingEvaluationAndAnOlderSpellingEvaluationInProgress();
        $this->givenATimeOutGrammarEvaluationAndAnOlderGrammarEvaluationDone();
        $this->givenACompletenessEvaluationDoneForAnotherProduct();

        $latestEvaluations = $this->getQuery()->execute(new ProductId(42));
        $this->assertCount(3, $latestEvaluations, 'There should be 3 latest evaluations');

        $latestCompletenessEvaluation = $latestEvaluations->get(new CriterionCode('completeness'));
        $this->assertNotNull($latestCompletenessEvaluation, 'There should be a latest completeness evaluation');
        $this->assertEquals(self::LATEST_COMPLETENESS_EVALUATION_ID, strval($latestCompletenessEvaluation->getId()), 'The completeness evaluation found should be the latest');

        $latestSpellingEvaluation = $latestEvaluations->get(new CriterionCode('spelling'));
        $this->assertNotNull($latestSpellingEvaluation, 'There should be a latest spelling evaluation');
        $this->assertEquals(self::LATEST_SPELLING_EVALUATION_ID, strval($latestSpellingEvaluation->getId()), 'The spelling evaluation found should be the latest');

        $latestGrammarEvaluation = $latestEvaluations->get(new CriterionCode('grammar'));
        $this->assertNotNull($latestGrammarEvaluation, 'There should be a latest grammar evaluation');
        $this->assertEquals(self::LATEST_GRAMMAR_EVALUATION_ID, strval($latestGrammarEvaluation->getId()), 'The grammar evaluation found should be the latest');
    }

    private function givenTwoCompletenessEvaluationsDone(): void
    {
        $completeness = new CriterionCode('completeness');
        $productId = new ProductId(42);
        $repository = $this->getRepository();

        $latestCompletenessEvaluationDone = new Write\CriterionEvaluation(
            new CriterionEvaluationId(self::LATEST_COMPLETENESS_EVALUATION_ID),
            $completeness,
            $productId,
            $this->getClock()->fromString('2019-11-08 09:52:37.344'),
            CriterionEvaluationStatus::done()
        );

        $repository->create((new Write\CriterionEvaluationCollection())->add($latestCompletenessEvaluationDone));
        $latestCompletenessEvaluationDone->start();
        $latestCompletenessEvaluationDone->end(new CriterionEvaluationResult(CriterionRateCollection::fromArray([
            'ecommerce' => [
                'en_US' => 90,
                'fr_FR' => 75,
            ],
            'mobile' => [
                'en_US' => 100,
                'fr_FR' => 85,
            ]
        ]), []));
        $repository->update($latestCompletenessEvaluationDone);

        $olderCompletenessEvaluationDone = new Write\CriterionEvaluation(
            new CriterionEvaluationId(),
            $completeness,
            $productId,
            $this->getClock()->fromString('2019-11-08 09:52:37.343'),
            CriterionEvaluationStatus::done()
        );

        $repository->create((new Write\CriterionEvaluationCollection())->add($olderCompletenessEvaluationDone));
        $olderCompletenessEvaluationDone->start();
        $olderCompletenessEvaluationDone->end(new CriterionEvaluationResult(new CriterionRateCollection(), []));
        $repository->update($olderCompletenessEvaluationDone);
    }

    private function givenAPendingSpellingEvaluationAndAnOlderSpellingEvaluationInProgress(): void
    {
        $spelling = new CriterionCode('spelling');
        $productId = new ProductId(42);
        $repository = $this->getRepository();

        $spellingEvaluationInProgress = new Write\CriterionEvaluation(
            new CriterionEvaluationId(),
            $spelling,
            $productId,
            $this->getClock()->fromString('2019-11-08 09:52:34.967'),
            CriterionEvaluationStatus::pending()
        );

        $repository->create((new Write\CriterionEvaluationCollection())->add($spellingEvaluationInProgress));
        $spellingEvaluationInProgress->start();
        $repository->update($spellingEvaluationInProgress);

        $spellingEvaluationPending = new Write\CriterionEvaluation(
            new CriterionEvaluationId(self::LATEST_SPELLING_EVALUATION_ID),
            $spelling,
            $productId,
            $this->getClock()->fromString('2019-11-08 09:52:35.175'),
            CriterionEvaluationStatus::pending()
        );

        $repository->create((new Write\CriterionEvaluationCollection())->add($spellingEvaluationPending));
    }

    private function givenATimeOutGrammarEvaluationAndAnOlderGrammarEvaluationDone(): void
    {
        $grammar = new CriterionCode('grammar');
        $productId = new ProductId(42);
        $repository = $this->getRepository();

        $grammarEvaluationTimeout = new Write\CriterionEvaluation(
            new CriterionEvaluationId(self::LATEST_GRAMMAR_EVALUATION_ID),
            $grammar,
            $productId,
            $this->getClock()->fromString('2019-11-08 09:52:38.567'),
            CriterionEvaluationStatus::pending()
        );

        $repository->create((new Write\CriterionEvaluationCollection())->add($grammarEvaluationTimeout));
        $grammarEvaluationTimeout->flagsAsTimeout();
        $repository->update($grammarEvaluationTimeout);

        $grammarEvaluationDone = new Write\CriterionEvaluation(
            new CriterionEvaluationId(),
            $grammar,
            $productId,
            $this->getClock()->fromString('2019-11-08 09:52:37.567'),
            CriterionEvaluationStatus::pending()
        );

        $repository->create((new Write\CriterionEvaluationCollection())->add($grammarEvaluationDone));
        $grammarEvaluationDone->start();
        $grammarEvaluationDone->end(new CriterionEvaluationResult(new CriterionRateCollection(), []));
        $repository->update($grammarEvaluationDone);
    }

    private function givenACompletenessEvaluationDoneForAnotherProduct(): void
    {
        $completenessEvaluation = new Write\CriterionEvaluation(
            new CriterionEvaluationId(),
            new CriterionCode('completeness'),
            new ProductId(123),
            $this->getClock()->fromString('2019-11-08 09:52:37.344'),
            CriterionEvaluationStatus::done()
        );

        $repository = $this->getRepository();
        $repository->create((new Write\CriterionEvaluationCollection())->add($completenessEvaluation));
        $completenessEvaluation->start();
        $completenessEvaluation->end(new CriterionEvaluationResult(CriterionRateCollection::fromArray([
            'ecommerce' => [
                'en_US' => 90,
                'fr_FR' => 75,
            ],
            'mobile' => [
                'en_US' => 100,
                'fr_FR' => 85,
            ]
        ]), []));
        $repository->update($completenessEvaluation);
    }

    private function getClock(): Clock
    {
        return $this->get(SystemClock::class);
    }

    private function getRepository(): CriterionEvaluationRepositoryInterface
    {
        return $this->get(CriterionEvaluationRepository::class);
    }

    private function getQuery(): GetLatestCriteriaEvaluationsByProductIdQueryInterface
    {
        return $this->get(GetLatestCriteriaEvaluationsByProductIdQuery::class);
    }
}
