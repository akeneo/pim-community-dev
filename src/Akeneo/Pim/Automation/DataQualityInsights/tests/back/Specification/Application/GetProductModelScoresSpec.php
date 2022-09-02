<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application;

use Akeneo\Pim\Automation\DataQualityInsights\Application\GetScoresByCriteriaStrategy;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetLocalesByChannelQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation\GetUpToDateProductModelScoresQuery;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductModelScoresSpec extends ObjectBehavior
{
    public function let(
        GetUpToDateProductModelScoresQuery $getUpToDateProductModelScoresQuery,
        GetLocalesByChannelQueryInterface  $getLocalesByChannelQuery,
        GetScoresByCriteriaStrategy        $getScoresByCriteria,
    ) {
        $this->beConstructedWith($getUpToDateProductModelScoresQuery, $getLocalesByChannelQuery, $getScoresByCriteria);
    }

    public function it_gives_the_scores_by_channel_and_locale_for_a_given_product_model(
        $getUpToDateProductModelScoresQuery,
        $getLocalesByChannelQuery,
        $getScoresByCriteria
    ) {
        $productModelId = new ProductModelId(42);

        $getLocalesByChannelQuery->getChannelLocaleCollection()->willReturn(new ChannelLocaleCollection([
            'ecommerce' => ['en_US', 'fr_FR'],
            'mobile' => ['en_US']
        ]));

        $scores = new Read\Scores(
            (new ChannelLocaleRateCollection())
                ->addRate(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new Rate(100))
                ->addRate(new ChannelCode('mobile'), new LocaleCode('en_US'), new Rate(80)),
            (new ChannelLocaleRateCollection())
                ->addRate(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new Rate(90))
                ->addRate(new ChannelCode('mobile'), new LocaleCode('en_US'), new Rate(70))
        );

        $getUpToDateProductModelScoresQuery->byProductModelId($productModelId)->willReturn($scores);
        $getScoresByCriteria->__invoke($scores)->willReturn($scores->allCriteria());

        $this->get($productModelId)->shouldBeLike(
            [
                "evaluations_available" => true,
                "scores" => [
                    'ecommerce' => [
                        'en_US' => (new Rate(100))->toLetter(),
                        'fr_FR' => null,
                    ],
                    'mobile' => [
                        'en_US' => (new Rate(80))->toLetter()
                    ],
                ]
            ]
        );
    }
}
