<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\KeyIndicator;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Dashboard\GetProductKeyIndicatorsQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CategoryCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\KeyIndicatorCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetKeyIndicatorsSpec extends ObjectBehavior
{
    public function let(GetProductKeyIndicatorsQueryInterface $getProductKeyIndicatorsQuery)
    {
        $this->beConstructedWith($getProductKeyIndicatorsQuery, 'good_enrichment', 'has_image');
    }

    public function it_gives_key_indicators_for_all_products($getProductKeyIndicatorsQuery)
    {
        $channel = new ChannelCode('ecommerce');
        $locale = new LocaleCode('en_US');
        $goodEnrichment = new KeyIndicatorCode('good_enrichment');
        $hasImage = new KeyIndicatorCode('has_image');

        $getProductKeyIndicatorsQuery->all($channel, $locale, $goodEnrichment, $hasImage)
            ->willReturn([
                'good_enrichment' => new KeyIndicator($goodEnrichment, 15, 60),
                'has_image' => new KeyIndicator($hasImage, 25, 26)
            ]);

        $this->all($channel, $locale)->shouldBeLike([
            'good_enrichment' => [
                'ratioGood' => 20,
                'totalGood' => 15,
                'totalToImprove' => 60,
            ],
            'has_image' => [
                'ratioGood' => 49,
                'totalGood' => 25,
                'totalToImprove' => 26,
            ]
        ]);
    }

    public function it_gives_key_indicators_for_a_given_family($getProductKeyIndicatorsQuery)
    {
        $family = new FamilyCode('shoes');
        $channel = new ChannelCode('ecommerce');
        $locale = new LocaleCode('en_US');
        $goodEnrichment = new KeyIndicatorCode('good_enrichment');
        $hasImage = new KeyIndicatorCode('has_image');

        $getProductKeyIndicatorsQuery->byFamily($channel, $locale, $family, $goodEnrichment, $hasImage)
            ->willReturn([
                'good_enrichment' => new KeyIndicator($goodEnrichment, 15, 60)
            ]);

        $this->byFamily($channel, $locale, $family)->shouldBeLike([
            'good_enrichment' => [
                'ratioGood' => 20,
                'totalGood' => 15,
                'totalToImprove' => 60,
            ],
        ]);
    }

    public function it_gives_key_indicators_for_a_given_category($getProductKeyIndicatorsQuery)
    {
        $category = new CategoryCode('shoes');
        $channel = new ChannelCode('ecommerce');
        $locale = new LocaleCode('en_US');
        $goodEnrichment = new KeyIndicatorCode('good_enrichment');
        $hasImage = new KeyIndicatorCode('has_image');

        $getProductKeyIndicatorsQuery->byCategory($channel, $locale, $category, $goodEnrichment, $hasImage)
            ->willReturn([
                'good_enrichment' => new KeyIndicator($goodEnrichment, 15, 60),
                'has_image' => new KeyIndicator($hasImage, 0, 0)
            ]);

        $this->byCategory($channel, $locale, $category)->shouldBeLike([
            'good_enrichment' => [
                'ratioGood' => 20,
                'totalGood' => 15,
                'totalToImprove' => 60,
            ],
            'has_image' => [
                'ratioGood' => 0,
                'totalGood' => 0,
                'totalToImprove' => 0,
            ]
        ]);
    }
}
