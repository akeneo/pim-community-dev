<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application\KeyIndicator;

use Akeneo\Pim\Automation\DataQualityInsights\Application\KeyIndicator\ProductKeyIndicatorsByFeatureRegistry;
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
    public function let(
        GetProductKeyIndicatorsQueryInterface $getProductKeyIndicatorsQuery,
        GetProductKeyIndicatorsQueryInterface $getProductModelKeyIndicatorsQuery,
        ProductKeyIndicatorsByFeatureRegistry $productKeyIndicatorsRegistry
    ) {
        $productKeyIndicatorsRegistry->getCodes()->WillReturn([
            new KeyIndicatorCode('good_enrichment'),
            new KeyIndicatorCode('has_image'),
        ]);
        $this->beConstructedWith($getProductKeyIndicatorsQuery, $getProductModelKeyIndicatorsQuery, $productKeyIndicatorsRegistry);
    }

    public function it_gives_key_indicators_for_all_products_and_product_models($getProductKeyIndicatorsQuery, $getProductModelKeyIndicatorsQuery)
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

        $getProductModelKeyIndicatorsQuery->all($channel, $locale, $goodEnrichment, $hasImage)
            ->willReturn([
                'good_enrichment' => new KeyIndicator($goodEnrichment, 23, 52),
                'has_image' => new KeyIndicator($hasImage, 24, 89)
            ]);

        $this->all($channel, $locale)->shouldBeLike([
            'good_enrichment' => [
                'products' => [
                    'totalGood' => 15,
                    'totalToImprove' => 60,
                ],
                'product_models' =>
                [
                    'totalGood' => 23,
                    'totalToImprove' => 52,
                ]
            ],
            'has_image' => [
                'products' => [
                    'totalGood' => 25,
                    'totalToImprove' => 26,
                ],
                'product_models' =>
                [
                    'totalGood' => 24,
                    'totalToImprove' => 89,
                ]
            ]
        ]);
    }

    public function it_gives_key_indicators_for_a_given_family($getProductKeyIndicatorsQuery, $getProductModelKeyIndicatorsQuery)
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

        $getProductModelKeyIndicatorsQuery->byFamily($channel, $locale, $family, $goodEnrichment, $hasImage)
            ->willReturn([
                'good_enrichment' => new KeyIndicator($goodEnrichment, 30, 40)
            ]);

        $this->byFamily($channel, $locale, $family)->shouldBeLike([
            'good_enrichment' => [
                'products' => [
                    'totalGood' => 15,
                    'totalToImprove' => 60,
                ],
                'product_models' =>
                [
                    'totalGood' => 30,
                    'totalToImprove' => 40,
                ]
            ],
            'has_image' => [
                'products' => [
                    'totalGood' => 0,
                    'totalToImprove' => 0,
                ],
                'product_models' =>
                [
                    'totalGood' => 0,
                    'totalToImprove' => 0,
                ]
            ]
        ]);
    }

    public function it_gives_key_indicators_for_a_given_category($getProductKeyIndicatorsQuery, $getProductModelKeyIndicatorsQuery)
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

        $getProductModelKeyIndicatorsQuery->byCategory($channel, $locale, $category, $goodEnrichment, $hasImage)
            ->willReturn([
                'good_enrichment' => new KeyIndicator($goodEnrichment, 45, 65),
                'has_image' => new KeyIndicator($hasImage, 0, 0)
            ]);

        $this->byCategory($channel, $locale, $category)->shouldBeLike([
            'good_enrichment' => [
                'products' => [
                    'totalGood' => 15,
                    'totalToImprove' => 60,
                ],
                'product_models' =>
                [
                    'totalGood' => 45,
                    'totalToImprove' => 65,
                ]
            ],
            'has_image' => [
                'products' => [
                    'totalGood' => 0,
                    'totalToImprove' => 0,
                ],
                'product_models' =>
                [
                    'totalGood' => 0,
                    'totalToImprove' => 0,
                ]
            ]
        ]);
    }
}
