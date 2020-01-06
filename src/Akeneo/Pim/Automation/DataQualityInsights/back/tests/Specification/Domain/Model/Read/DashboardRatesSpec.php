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
        $rates = [
            "daily" => [
                "2019-12-17" => [
                    "consistency" => [
                        "ecommerce" => [
                            "en_US" => [
                                "1" => 12,
                                "2" => 28,
                                "3" => 10,
                                "4" => 40,
                                "5" => 10
                            ],
                            "fr_FR" => [
                                "1" => 30,
                                "2" => 10,
                                "3" => 20,
                                "4" => 20,
                                "5" => 20
                            ]
                        ],
                        "mobile" => [
                            "en_US" => [
                                "1" => 12,
                                "2" => 28,
                                "3" => 10,
                                "4" => 40,
                                "5" => 10
                            ],
                            "fr_FR" => [
                                "1" => 30,
                                "2" => 10,
                                "3" => 20,
                                "4" => 20,
                                "5" => 20
                            ]
                        ],
                    ],
                    "enrichment" => [
                        "ecommerce" => [
                            "en_US" => [
                                "1" => 10,
                                "2" => 40,
                                "3" => 10,
                                "4" => 28,
                                "5" => 12
                            ],
                            "fr_FR" => [
                                "1" => 20,
                                "2" => 20,
                                "3" => 20,
                                "4" => 10,
                                "5" => 30
                            ]
                        ],
                        "mobile" => [
                            "en_US" => [
                                "1" => 12,
                                "2" => 28,
                                "3" => 10,
                                "4" => 40,
                                "5" => 10
                            ],
                            "fr_FR" => [
                                "1" => 30,
                                "2" => 10,
                                "3" => 20,
                                "4" => 20,
                                "5" => 20
                            ]
                        ],
                    ]
                ],
                "2019-12-16" => [
                    "consistency" => [
                        "ecommerce" => [
                            "en_US" => [
                                "1" => 5,
                                "2" => 20,
                                "3" => 15,
                                "4" => 35,
                                "5" => 25
                            ],
                            "fr_FR" => [
                                "1" => 30,
                                "2" => 10,
                                "3" => 20,
                                "4" => 20,
                                "5" => 20
                            ]
                        ],
                        "mobile" => [
                            "en_US" => [
                                "1" => 12,
                                "2" => 28,
                                "3" => 10,
                                "4" => 40,
                                "5" => 10
                            ],
                            "fr_FR" => [
                                "1" => 30,
                                "2" => 10,
                                "3" => 20,
                                "4" => 20,
                                "5" => 20
                            ]
                        ]
                    ],
                    'enrichment' => [
                        "ecommerce" => [
                            "en_US" => [
                                "1" => 25,
                                "2" => 35,
                                "3" => 15,
                                "4" => 20,
                                "5" => 5
                            ],
                            "fr_FR" => [
                                "1" => 30,
                                "2" => 10,
                                "3" => 20,
                                "4" => 20,
                                "5" => 20
                            ]
                        ],
                        "mobile" => [
                            "en_US" => [
                                "1" => 10,
                                "2" => 50,
                                "3" => 10,
                                "4" => 25,
                                "5" => 12
                            ],
                            "fr_FR" => [
                                "1" => 20,
                                "2" => 20,
                                "3" => 20,
                                "4" => 10,
                                "5" => 30
                            ]
                        ]
                    ],
                ],
            ]
        ];

        $this->beConstructedWith($rates, new ChannelCode('ecommerce'), new LocaleCode('en_US'), new Periodicity('daily'));
        $this->toArray()->shouldBeLike([
            "consistency" => [
                "2019-12-17" => [
                    "1" => 12,
                    "2" => 28,
                    "3" => 10,
                    "4" => 40,
                    "5" => 10
                ],
                "2019-12-16" => [
                    "1" => 5,
                    "2" => 20,
                    "3" => 15,
                    "4" => 35,
                    "5" => 25
                ]
            ],
            "enrichment" => [
                "2019-12-17" => [
                    "1" => 10,
                    "2" => 40,
                    "3" => 10,
                    "4" => 28,
                    "5" => 12
                ],
                "2019-12-16" => [
                    "1" => 25,
                    "2" => 35,
                    "3" => 15,
                    "4" => 20,
                    "5" => 5
                ]
            ]
        ]);
    }
}
