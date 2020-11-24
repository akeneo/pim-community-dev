<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\TimePeriod;
use PhpSpec\ObjectBehavior;

final class DashboardRatesSpec extends ObjectBehavior
{
    public function it_get_rates_by_channel_locale_and_time_period()
    {
        $yesterday = (new \DateTime('-1 DAY'))->format('Y-m-d');
        $twoDaysAgo = (new \DateTime('-2 DAY'))->format('Y-m-d');

        $rates = [
            "daily" => [
                $yesterday => [
                    "ecommerce" => [
                        "en_US" => [
                            "rank_1" => 12,
                            "rank_2" => 28,
                            "rank_3" => 10,
                            "rank_4" => 40,
                            "rank_5" => 10,
                        ],
                        "fr_FR" => [
                            "rank_1" => 30,
                            "rank_2" => 10,
                            "rank_3" => 20,
                            "rank_4" => 20,
                            "rank_5" => 20,
                        ]
                    ],
                    "mobile" => [
                        "en_US" => [
                            "rank_1" => 12,
                            "rank_2" => 28,
                            "rank_3" => 10,
                            "rank_4" => 40,
                            "rank_5" => 10,
                        ],
                        "fr_FR" => [
                            "rank_1" => 30,
                            "rank_2" => 10,
                            "rank_3" => 20,
                            "rank_4" => 20,
                            "rank_5" => 20,
                        ]
                    ],
                ],
                $twoDaysAgo => [
                    "ecommerce" => [
                        "en_US" => [
                            "rank_1" => 5,
                            "rank_2" => 20,
                            "rank_3" => 15,
                            "rank_4" => 35,
                            "rank_5" => 25,
                        ],
                        "fr_FR" => [
                            "rank_1" => 30,
                            "rank_2" => 10,
                            "rank_3" => 20,
                            "rank_4" => 20,
                            "rank_5" => 20,
                        ]
                    ],
                    "mobile" => [
                        "en_US" => [
                            "rank_1" => 12,
                            "rank_2" => 28,
                            "rank_3" => 10,
                            "rank_4" => 40,
                            "rank_5" => 10,
                        ],
                        "fr_FR" => [
                            "rank_1" => 30,
                            "rank_2" => 10,
                            "rank_3" => 20,
                            "rank_4" => 20,
                            "rank_5" => 20,
                        ]
                    ]
                ],
            ]
        ];

        $this->beConstructedWith($rates, new ChannelCode('ecommerce'), new LocaleCode('en_US'), new TimePeriod('daily'));

        $threeDaysAgo = (new \DateTime('-3 DAY'))->format('Y-m-d');
        $fourDaysAgo = (new \DateTime('-4 DAY'))->format('Y-m-d');
        $fiveDaysAgo = (new \DateTime('-5 DAY'))->format('Y-m-d');
        $sixDaysAgo = (new \DateTime('-6 DAY'))->format('Y-m-d');
        $sevenDaysAgo = (new \DateTime('-7 DAY'))->format('Y-m-d');

        $this->toArray()->shouldBeLike([
            $yesterday => [
                "rank_1" => 12,
                "rank_2" => 28,
                "rank_3" => 10,
                "rank_4" => 40,
                "rank_5" => 10,
            ],
            $twoDaysAgo => [
                "rank_1" => 5,
                "rank_2" => 20,
                "rank_3" => 15,
                "rank_4" => 35,
                "rank_5" => 25,
            ],
            $threeDaysAgo => [],
            $fourDaysAgo => [],
            $fiveDaysAgo => [],
            $sixDaysAgo => [],
            $sevenDaysAgo => [],
        ]);
    }
}
