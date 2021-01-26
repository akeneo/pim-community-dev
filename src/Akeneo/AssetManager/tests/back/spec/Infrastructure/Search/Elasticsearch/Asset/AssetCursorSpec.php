<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Infrastructure\Search\Elasticsearch\Asset;

use Akeneo\AssetManager\Domain\Query\Asset\AssetQuery;
use Akeneo\AssetManager\Infrastructure\Search\Elasticsearch\Asset\AssetQueryBuilderInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 */
class AssetCursorSpec extends ObjectBehavior
{
    function let(
        AssetQueryBuilderInterface $queryBuilder,
        Client $assetClient
    ) {

        $assetQuery = AssetQuery::createFromNormalized(
            [
                'channel' => null,
                'locale' => null,
                'filters' => [],
                'page' => 0,
                'size' => 3,
            ]
        );

        $this->beConstructedWith(
            $queryBuilder,
            $assetClient,
            $assetQuery
        );
    }

    function it_can_fetch_a_first_page_of_assets($assetClient, $queryBuilder)
    {
        $esQuery = [
            '_source' => 'code',
            'size'    => 3,
            'query'   => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'filter' => [
                                [
                                    'term' => [
                                        'asset_family_code' => 'packshot',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'track_total_hits' => true,
        ];

        $queryBuilder->buildFromQuery(Argument::Any(), 'code')
            ->willReturn($esQuery);
        $assetClient->search($esQuery)
            ->willReturn([
                'hits' => [
                    'total' => ['value' => 4],
                    'hits' => [
                        [
                            '_source' => ['code' => 'nice'],
                        ],
                        [
                            '_source' => ['code' => 'cool'],
                        ],
                        [
                            '_source' => ['code' => 'awesome'],
                        ]
                    ]
                ]
            ], [
                'hits' => [
                    'total' => ['value' => 4],
                    'hits' => [
                        [
                            '_source' => ['code' => 'tricky'],
                        ]
                    ]
                ]
            ], [
                'hits' => [
                    'total' => ['value' => 4],
                    'hits' => []
                ]
            ]);

        $page1 = ['nice', 'cool', 'awesome'];
        $page2 = ['tricky'];
        $data = array_merge($page1, $page2);

        $this->shouldImplement(\Iterator::class);

        $this->rewind()->shouldReturn(null);
        for ($i = 0; $i < 4; $i++) {
            if ($i > 0) {
                $this->next()->shouldReturn(null);
            }
            $this->valid()->shouldReturn(true);
            $this->current()->shouldReturn($data[$i]);

            $n = 0 === $i % 3 ? 0 : $i;
            $this->key()->shouldReturn($n);
        }

        $this->next()->shouldReturn(null);
        $this->valid()->shouldReturn(false);

        // check behaviour after the end of data
        $this->current()->shouldReturn(null);
        $this->key()->shouldReturn(null);
    }

    function it_can_count_total_items($assetClient, $queryBuilder)
    {
        $esQuery = [
            '_source' => 'code',
            'size'    => 3,
            'query'   => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'filter' => [
                                [
                                    'term' => [
                                        'asset_family_code' => 'packshot',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'track_total_hits' => true,
        ];

        $queryBuilder->buildFromQuery(Argument::Any(), 'code')
            ->willReturn($esQuery);
        $assetClient->search($esQuery)
            ->willReturn([
                'hits' => [
                    'total' => ['value' => 253],
                    'hits' => [
                        [
                            '_source' => ['code' => 'nice'],
                        ],
                        [
                            '_source' => ['code' => 'cool'],
                        ],
                        [
                            '_source' => ['code' => 'awesome'],
                        ]
                    ]
                ]
            ]);


        $this->count()->shouldReturn(253);
    }
}
