<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\ProductGrid;

use Akeneo\Pim\Automation\DataQualityInsights\Application\GetScoresByCriteriaStrategy;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductModelScoresQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductScoresQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductIdCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use PhpSpec\ObjectBehavior;

final class GetQualityScoresFactorySpec extends ObjectBehavior
{
    public function let(
        GetProductScoresQueryInterface $getProductScoresQuery,
        GetProductModelScoresQueryInterface $getProductModelScoresQuery,
        GetScoresByCriteriaStrategy $getScoresByCriteria,
    ) {
        $this->beConstructedWith($getProductScoresQuery, $getProductModelScoresQuery, $getScoresByCriteria);
    }

    public function it_gets_quality_scores_for_products(
        $getProductScoresQuery,
        $getScoresByCriteria,
    ) {
        $productIds = ProductIdCollection::fromInts([42, 56]);
        $scores = $this->givenScores();

        $getProductScoresQuery->byProductIds($productIds)->willReturn($scores);
        $getScoresByCriteria->__invoke($scores[42])->willReturn($scores[42]->allCriteria());
        $getScoresByCriteria->__invoke($scores[56])->willReturn($scores[56]->allCriteria());

        $this->__invoke($productIds, 'product')->shouldReturn([
            42 => $scores[42]->allCriteria(),
            56 => $scores[56]->allCriteria(),
        ]);
    }

    public function it_gets_quality_scores_for_product_models(
        $getProductModelScoresQuery,
        $getScoresByCriteria,
    ) {
        $productModelIds = ProductIdCollection::fromInts([42, 56]);
        $scores = $this->givenScores();

        $getProductModelScoresQuery->byProductModelIds($productModelIds)->willReturn($scores);
        $getScoresByCriteria->__invoke($scores[42])->willReturn($scores[42]->allCriteria());
        $getScoresByCriteria->__invoke($scores[56])->willReturn($scores[56]->allCriteria());

        $this->__invoke($productModelIds, 'product_model')->shouldReturn([
            42 => $scores[42]->allCriteria(),
            56 => $scores[56]->allCriteria(),
        ]);
    }

    public function it_throws_an_exception_for_an_unknown_type()
    {
        $productIds = ProductIdCollection::fromInts([42, 56]);

        $this->shouldThrow(\InvalidArgumentException::class)->during('__invoke', [$productIds, 'whatever']);
    }

    private function givenScores(): array
    {
        $channel = new ChannelCode('ecommerce');
        $locale = new LocaleCode('en_US');

        return [
          42 => new Read\Scores(
              (new ChannelLocaleRateCollection)->addRate($channel, $locale, new Rate(76)),
              (new ChannelLocaleRateCollection)->addRate($channel, $locale, new Rate(65))
          ),
          56 => new Read\Scores(
              (new ChannelLocaleRateCollection)->addRate($channel, $locale, new Rate(98)),
              (new ChannelLocaleRateCollection)->addRate($channel, $locale, new Rate(84))
          ),
        ];
    }
}
