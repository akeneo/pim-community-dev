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
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelIdCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
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
        GetProductScoresQueryInterface $getProductScoresQuery,
        GetScoresByCriteriaStrategy $getScoresByCriteria,
    ) {
        $productUuids = ProductUuidCollection::fromStrings([
            '0932dfd0-5f9a-49fb-ad31-a990339406a2',
            '3370280b-6c76-4720-aac1-ae3f9613d555'
        ]);
        $scores = $this->givenProductScores();

        $getProductScoresQuery->byProductUuidCollection($productUuids)->willReturn($scores);
        $getScoresByCriteria->__invoke($scores['0932dfd0-5f9a-49fb-ad31-a990339406a2'])->willReturn($scores['0932dfd0-5f9a-49fb-ad31-a990339406a2']->allCriteria());
        $getScoresByCriteria->__invoke($scores['3370280b-6c76-4720-aac1-ae3f9613d555'])->willReturn($scores['3370280b-6c76-4720-aac1-ae3f9613d555']->allCriteria());

        $this->__invoke($productUuids, 'product')->shouldReturn([
            '0932dfd0-5f9a-49fb-ad31-a990339406a2' => $scores['0932dfd0-5f9a-49fb-ad31-a990339406a2']->allCriteria(),
            '3370280b-6c76-4720-aac1-ae3f9613d555' => $scores['3370280b-6c76-4720-aac1-ae3f9613d555']->allCriteria(),
        ]);
    }

    public function it_gets_quality_scores_for_product_models(
        GetProductModelScoresQueryInterface $getProductModelScoresQuery,
        GetScoresByCriteriaStrategy $getScoresByCriteria,
    ) {
        $productModelIds = ProductModelIdCollection::fromStrings(['42', '56']);
        $scores = $this->givenProductModelScores();

        $getProductModelScoresQuery->byProductModelIdCollection($productModelIds)->willReturn($scores);
        $getScoresByCriteria->__invoke($scores[42])->willReturn($scores[42]->allCriteria());
        $getScoresByCriteria->__invoke($scores[56])->willReturn($scores[56]->allCriteria());

        $this->__invoke($productModelIds, 'product_model')->shouldReturn([
            42 => $scores[42]->allCriteria(),
            56 => $scores[56]->allCriteria(),
        ]);
    }

    public function it_throws_an_exception_for_an_unknown_type()
    {
        $productUuids = ProductUuidCollection::fromStrings([
            '0932dfd0-5f9a-49fb-ad31-a990339406a2',
            '3370280b-6c76-4720-aac1-ae3f9613d555'
        ]);

        $this->shouldThrow(\InvalidArgumentException::class)->during('__invoke', [$productUuids, 'whatever']);
    }

    private function givenProductScores(): array
    {
        $channel = new ChannelCode('ecommerce');
        $locale = new LocaleCode('en_US');

        return [
            '0932dfd0-5f9a-49fb-ad31-a990339406a2' => new Read\Scores(
                (new ChannelLocaleRateCollection)->addRate($channel, $locale, new Rate(76)),
                (new ChannelLocaleRateCollection)->addRate($channel, $locale, new Rate(65))
            ),
            '3370280b-6c76-4720-aac1-ae3f9613d555' => new Read\Scores(
                (new ChannelLocaleRateCollection)->addRate($channel, $locale, new Rate(98)),
                (new ChannelLocaleRateCollection)->addRate($channel, $locale, new Rate(84))
            ),
        ];
    }

    private function givenProductModelScores(): array
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
