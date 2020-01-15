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

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Periodicity;
use PhpSpec\ObjectBehavior;

final class DashboardRatesSpec extends ObjectBehavior
{
    public function it_get_rates_by_channel_locale_and_periodicity()
    {
        $yesterday = (new \DateTime('-1 DAY'))->format('Y-m-d');
        $twoDaysAgo = (new \DateTime('-2 DAY'))->format('Y-m-d');

        $rates = [
            "daily" => [
                $yesterday => [
                    "consistency" => [
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
                    "enrichment" => [
                        "ecommerce" => [
                            "en_US" => [
                                "rank_1" => 10,
                                "rank_2" => 40,
                                "rank_3" => 10,
                                "rank_4" => 28,
                                "rank_5" => 12,
                            ],
                            "fr_FR" => [
                                "rank_1" => 20,
                                "rank_2" => 20,
                                "rank_3" => 20,
                                "rank_4" => 10,
                                "rank_5" => 30,
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
                    ]
                ],
                $twoDaysAgo => [
                    "consistency" => [
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
                    'enrichment' => [
                        "ecommerce" => [
                            "en_US" => [
                                "rank_1" => 25,
                                "rank_2" => 35,
                                "rank_3" => 15,
                                "rank_4" => 20,
                                "rank_5" => 5,
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
                                "rank_1" => 10,
                                "rank_2" => 50,
                                "rank_3" => 10,
                                "rank_4" => 25,
                                "rank_5" => 12,
                            ],
                            "fr_FR" => [
                                "rank_1" => 20,
                                "rank_2" => 20,
                                "rank_3" => 20,
                                "rank_4" => 10,
                                "rank_5" => 30,
                            ]
                        ]
                    ],
                ],
            ]
        ];

        $this->beConstructedWith($rates, new ChannelCode('ecommerce'), new LocaleCode('en_US'), new Periodicity('daily'));

        $threeDaysAgo = (new \DateTime('-3 DAY'))->format('Y-m-d');
        $fourDaysAgo = (new \DateTime('-4 DAY'))->format('Y-m-d');
        $fiveDaysAgo = (new \DateTime('-5 DAY'))->format('Y-m-d');
        $sixDaysAgo = (new \DateTime('-6 DAY'))->format('Y-m-d');
        $sevenDaysAgo = (new \DateTime('-7 DAY'))->format('Y-m-d');

        $this->toArray()->shouldBeLike([
            "consistency" => [
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
            ],
            "enrichment" => [
                $yesterday => [
                    "rank_1" => 10,
                    "rank_2" => 40,
                    "rank_3" => 10,
                    "rank_4" => 28,
                    "rank_5" => 12,
                ],
                $twoDaysAgo => [
                    "rank_1" => 25,
                    "rank_2" => 35,
                    "rank_3" => 15,
                    "rank_4" => 20,
                    "rank_5" => 5,
                ],
                $threeDaysAgo => [],
                $fourDaysAgo => [],
                $fiveDaysAgo => [],
                $sixDaysAgo => [],
                $sevenDaysAgo => [],
            ]
        ]);
    }
}
