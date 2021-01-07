<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;

use PhpSpec\ObjectBehavior;

final class CatalogQualityScoreEvolutionSpec extends ObjectBehavior
{
    public function it_returns_catalog_score_evolution()
    {
        $today = new \DateTimeImmutable('2020-12-05');
        $currentMonth = $today->format('Y-m-t');
        $lastMonth = $today->modify('last day of 1 month ago')->format('Y-m-t');
        $twoMonthsAgo = $today->modify('last day of 2 month ago')->format('Y-m-t');
        $threeMonthsAgo = $today->modify('last day of 3 month ago')->format('Y-m-t');
        $fourMonthsAgo = $today->modify('last day of 4 month ago')->format('Y-m-t');
        $fiveMonthsAgo = $today->modify('last day of 5 month ago')->format('Y-m-t');
        $sixMonthsAgo = $today->modify('last day of 6 month ago')->format('Y-m-t');

        $scores = [
            'average_ranks' => [
                'print' => [
                    'en_US' => 'rank_3',
                ],
            ],
            'monthly' => [
                $sixMonthsAgo => [
                    'print' => [
                        'en_US' => [
                            'rank_1' => 2,
                            'rank_2' => 5,
                            'rank_3' => 4,
                            'rank_4' => 3,
                            'rank_5' => 2,
                        ],
                    ],
                ],
                $fiveMonthsAgo => [
                    'print' => [
                        'en_US' => [
                            'rank_1' => 2,
                            'rank_2' => 5,
                            'rank_3' => 4,
                            'rank_4' => 3,
                            'rank_5' => 2,
                        ],
                    ],
                ],
                $fourMonthsAgo => [
                    'print' => [
                        'en_US' => [
                            'rank_1' => 2,
                            'rank_2' => 5,
                            'rank_3' => 4,
                            'rank_4' => 4,
                            'rank_5' => 2,
                        ],
                    ],
                ],
                $threeMonthsAgo => [
                    'print' => [
                        'en_US' => [
                            'rank_1' => 2,
                            'rank_2' => 4,
                            'rank_3' => 4,
                            'rank_4' => 40,
                            'rank_5' => 2,
                        ],
                    ],
                ],
                $twoMonthsAgo => [
                    'print' => [
                        'en_US' => [
                            'rank_1' => 2,
                            'rank_2' => 5,
                            'rank_3' => 30,
                            'rank_4' => 4,
                            'rank_5' => 2,
                        ],
                    ],
                ],
                $lastMonth => [
                    'print' => [
                        'en_US' => [
                            'rank_1' => 30,
                            'rank_2' => 5,
                            'rank_3' => 4,
                            'rank_4' => 3,
                            'rank_5' => 2,
                        ],
                    ],
                ],
            ],
        ];

        $this->beConstructedWith($today, $scores, 'print', 'en_US');

        $this->toArray()->shouldBeLike([
            'average_rank' => 'C',
            'data' => [
                $fiveMonthsAgo => 'C',
                $fourMonthsAgo => 'C',
                $threeMonthsAgo => 'D',
                $twoMonthsAgo => 'C',
                $lastMonth => 'B',
                $currentMonth => 'C',
            ],
        ]);
    }
}
