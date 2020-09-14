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

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Axis\Enrichment;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\AxisRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetLatestAxesRatesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AxisCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use PhpSpec\ObjectBehavior;

final class GetProductAxesRatesSpec extends ObjectBehavior
{
    public function let(GetLatestAxesRatesQueryInterface $getLatestProductAxesRatesQuery)
    {
        $this->beConstructedWith($getLatestProductAxesRatesQuery);
    }

    public function it_returns_the_axes_rates_of_a_product(GetLatestAxesRatesQueryInterface $getLatestProductAxesRatesQuery)
    {
        $productId = new ProductId(42);
        $channelMobile = new ChannelCode('mobile');
        $localeEn = new LocaleCode('en_US');
        $localeFr = new LocaleCode('fr_FR');

        $getLatestProductAxesRatesQuery->byProductId($productId)->willReturn(
            (new AxisRateCollection())
                ->add(new AxisCode(Enrichment::AXIS_CODE), (new ChannelLocaleRateCollection())
                    ->addRate($channelMobile, $localeEn, new Rate(82))
                    ->addRate($channelMobile, $localeFr, new Rate(77))
                )
        );

        $expectedRates = [
            Enrichment::AXIS_CODE => [
                'code' => Enrichment::AXIS_CODE,
                'rates' => [
                    'mobile' => [
                        'en_US' => 'B',
                        'fr_FR' => 'C',
                    ]
                ],
            ],
        ];

        $this->get($productId)->shouldReturn($expectedRates);
    }
}
