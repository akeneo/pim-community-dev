<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application;

use Akeneo\Pim\Automation\DataQualityInsights\Application\GetKeyIndicatorsInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\KeyIndicator\AttributesWithPerfectSpelling;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\KeyIndicator;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\KeyIndicator\ComputeStructureKeyIndicator;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CategoryCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\KeyIndicatorCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use PhpSpec\ObjectBehavior;

final class PimEnterpriseGetKeyIndicatorsSpec extends ObjectBehavior
{
    public function let(GetKeyIndicatorsInterface $getProductsKeyIndicators, ComputeStructureKeyIndicator $computeAttributesPerfectSpelling)
    {
        $this->beConstructedWith($getProductsKeyIndicators, $computeAttributesPerfectSpelling);
    }

    public function it_gives_key_indicators_for_all_products_and_product_models($getProductsKeyIndicators, $computeAttributesPerfectSpelling)
    {
        $channel = new ChannelCode('ecommerce');
        $locale = new LocaleCode('en_US');

        $productKeyIndicators = [
            'good_enrichment' => [
                'products' => [
                    'totalGood' => 15,
                    'totalToImprove' => 60,
                ],
                'product_models' => [
                    'totalGood' => 10,
                    'totalToImprove' => 14,
                ]
            ],
            'has_image' => [
                'products' => [
                    'totalGood' => 25,
                    'totalToImprove' => 26,
                ],
                'product_models' => [
                    'totalGood' => 8,
                    'totalToImprove' => 12,
                ]
            ]
        ];

        $getProductsKeyIndicators->all($channel, $locale)->willReturn($productKeyIndicators);

        $computeAttributesPerfectSpelling->computeByLocale($locale)->willReturn(
            new KeyIndicator(new KeyIndicatorCode(AttributesWithPerfectSpelling::CODE), 12, 34)
        );

        $expectedKeyIndicators = $productKeyIndicators;
        $expectedKeyIndicators[AttributesWithPerfectSpelling::CODE] = [
            'totalGood' => 12,
            'totalToImprove' => 34,
            'extraData' => [],
        ];

        $this->all($channel, $locale)->shouldBeLike($expectedKeyIndicators);
    }

    public function it_gives_key_indicators_for_a_given_family($getProductsKeyIndicators, $computeAttributesPerfectSpelling)
    {
        $channel = new ChannelCode('ecommerce');
        $locale = new LocaleCode('en_US');
        $family = new FamilyCode('shoes');

        $productKeyIndicators = [
            'good_enrichment' => [
                'products' => [
                    'totalGood' => 15,
                    'totalToImprove' => 60,
                ],
                'product_models' => [
                    'totalGood' => 10,
                    'totalToImprove' => 14,
                ]
            ],
            'has_image' => [
                'products' => [
                    'totalGood' => 25,
                    'totalToImprove' => 26,
                ],
                'product_models' => [
                    'totalGood' => 8,
                    'totalToImprove' => 12,
                ]
            ]
        ];

        $getProductsKeyIndicators->byFamily($channel, $locale, $family)->willReturn($productKeyIndicators);

        $computeAttributesPerfectSpelling->computeByLocaleAndFamily($locale, $family)->willReturn(
            new KeyIndicator(new KeyIndicatorCode(AttributesWithPerfectSpelling::CODE), 0, 42)
        );

        $expectedKeyIndicators = $productKeyIndicators;
        $expectedKeyIndicators[AttributesWithPerfectSpelling::CODE] = [
            'totalGood' => 0,
            'totalToImprove' => 42,
            'extraData' => [],
        ];

        $this->byFamily($channel, $locale, $family)->shouldBeLike($expectedKeyIndicators);
    }

    public function it_gives_key_indicators_for_a_given_category($getProductsKeyIndicators, $computeAttributesPerfectSpelling)
    {
        $channel = new ChannelCode('ecommerce');
        $locale = new LocaleCode('en_US');
        $category = new CategoryCode('shoes');

        $productKeyIndicators = [
            'good_enrichment' => [
                'products' => [
                    'totalGood' => 15,
                    'totalToImprove' => 60,
                ],
                'product_models' => [
                    'totalGood' => 10,
                    'totalToImprove' => 14,
                ]
            ],
            'has_image' => [
                'products' => [
                    'totalGood' => 25,
                    'totalToImprove' => 26,
                ],
                'product_models' => [
                    'totalGood' => 8,
                    'totalToImprove' => 12,
                ]
            ]
        ];

        $getProductsKeyIndicators->byCategory($channel, $locale, $category)->willReturn($productKeyIndicators);

        $computeAttributesPerfectSpelling->computeByLocaleAndCategory($locale, $category)->willReturn(
            new KeyIndicator(new KeyIndicatorCode(AttributesWithPerfectSpelling::CODE), 23, 0)
        );

        $expectedKeyIndicators = $productKeyIndicators;
        $expectedKeyIndicators[AttributesWithPerfectSpelling::CODE] = [
            'totalGood' => 23,
            'totalToImprove' => 0,
            'extraData' => [],
        ];

        $this->byCategory($channel, $locale, $category)->shouldBeLike($expectedKeyIndicators);
    }

    public function it_does_not_ignore_empty_key_indicators($getProductsKeyIndicators, $computeAttributesPerfectSpelling)
    {
        $channel = new ChannelCode('ecommerce');
        $locale = new LocaleCode('en_US');
        $category = new CategoryCode('shoes');

        $productKeyIndicators = [
            'good_enrichment' => [
                'products' => [
                    'totalGood' => 15,
                    'totalToImprove' => 60,
                ],
                'product_models' => [
                    'totalGood' => 10,
                    'totalToImprove' => 14,
                ]
            ],
            'has_image' => [
                'products' => [
                    'totalGood' => 25,
                    'totalToImprove' => 26,
                ],
                'product_models' => [
                    'totalGood' => 8,
                    'totalToImprove' => 12,
                ]
            ],
        ];

        $attributes_key_indicators = [
            'attributes_perfect_spelling' => [
                'totalGood' => 0,
                'totalToImprove' => 0,
                'extraData' => []
            ]
        ];

        $getProductsKeyIndicators->byCategory($channel, $locale, $category)->willReturn($productKeyIndicators);

        $computeAttributesPerfectSpelling->computeByLocaleAndCategory($locale, $category)->willReturn(
            new KeyIndicator(new KeyIndicatorCode(AttributesWithPerfectSpelling::CODE), 0, 0)
        );

        $expectedResult = array_merge($productKeyIndicators, $attributes_key_indicators);

        $this->byCategory($channel, $locale, $category)->shouldBeLike($expectedResult);
    }
}
