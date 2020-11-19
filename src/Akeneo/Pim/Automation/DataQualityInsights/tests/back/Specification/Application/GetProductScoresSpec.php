<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetLatestProductScoresQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetLocalesByChannelQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductScoresSpec extends ObjectBehavior
{
    public function let(GetLatestProductScoresQueryInterface $getLatestProductScoresQuery, GetLocalesByChannelQueryInterface $getLocalesByChannelQuery)
    {
        $this->beConstructedWith($getLatestProductScoresQuery, $getLocalesByChannelQuery);
    }

    public function it_gives_the_scores_by_channel_and_locale_for_a_given_product($getLatestProductScoresQuery, $getLocalesByChannelQuery)
    {
        $productId = new ProductId(42);

        $getLocalesByChannelQuery->getChannelLocaleCollection()->willReturn(new ChannelLocaleCollection([
            'ecommerce' => ['en_US', 'fr_FR'],
            'mobile' => ['en_US']
        ]));


        $getLatestProductScoresQuery->byProductId($productId)->willReturn((new ChannelLocaleRateCollection())
            ->addRate(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new Rate(100))
            ->addRate(new ChannelCode('mobile'), new LocaleCode('en_US'), new Rate(80))
        );

        $this->get($productId)->shouldBeLike([
            'ecommerce' => [
                'en_US' => (new Rate(100))->toLetter(),
                'fr_FR' => null,
            ],
            'mobile' => [
                'en_US' => (new Rate(80))->toLetter()
            ],
        ]);
    }
}
