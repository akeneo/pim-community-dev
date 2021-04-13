<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\Record;

use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordQuery;
use Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\Record\RecordQueryBuilderInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 */
class RecordCursorSpec extends ObjectBehavior
{
    function let(RecordQueryBuilderInterface $queryBuilder, Client $recordClient)
    {
        $recordQuery = RecordQuery::createFromNormalized(
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
            $recordClient,
            $recordQuery
        );
    }

    function it_can_fetch_a_first_page_of_records(Client $recordClient, RecordQueryBuilderInterface $queryBuilder)
    {
        $firstRecordQuery = RecordQuery::createFromNormalized(
            [
                'channel' => null,
                'locale' => null,
                'filters' => [],
                'page' => 0,
                'size' => 3,
            ]
        );

        $firstElasticSearchQuery = [
            '_source' => 'code',
            'size'    => 3,
            'query'   => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'filter' => [
                                [
                                    'term' => [
                                        'reference_entity_code' => 'packshot',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'track_total_hits' => true,
        ];

        $queryBuilder->buildFromQuery($firstRecordQuery, 'code')->willReturn($firstElasticSearchQuery);
        $recordClient->search($firstElasticSearchQuery)->willReturn([
            'hits' => [
                'total' => ['value' => 4],
                'hits' => [
                    [
                        '_source' => ['code' => 'nice'],
                        'sort' => ['nice'],
                    ],
                    [
                        '_source' => ['code' => 'cool'],
                        'sort' => ['cool'],
                    ],
                    [
                        '_source' => ['code' => 'AWESOME'],
                        'sort' => ['awesome'],
                    ]
                ]
            ]
        ]);

        $secondRecordQuery = RecordQuery::createNextWithSearchAfter($firstRecordQuery, RecordCode::fromString('awesome'));
        $secondElasticSearchQuery = array_merge($firstElasticSearchQuery, ['search_after' => 'awesome']);
        $queryBuilder->buildFromQuery($secondRecordQuery, 'code')->willReturn($secondElasticSearchQuery);
        $recordClient->search($secondElasticSearchQuery)->willReturn([
            'hits' => [
                'total' => ['value' => 4],
                'hits' => [
                    [
                        '_source' => ['code' => 'tricky'],
                        'sort' => ['tricky'],
                    ]
                ]
            ]
        ]);

        $thirdQuery = RecordQuery::createNextWithSearchAfter($secondRecordQuery, RecordCode::fromString('tricky'));
        $thirdElasticSearchQuery = array_merge($firstElasticSearchQuery, ['search_after' => 'tricky']);
        $queryBuilder->buildFromQuery($thirdQuery, 'code')->willReturn($thirdElasticSearchQuery);
        $recordClient->search($thirdElasticSearchQuery)->willReturn([
            'hits' => [
                'total' => ['value' => 4],
                'hits' => []
            ]
        ]);

        $page1 = ['nice', 'cool', 'AWESOME'];
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

    function it_can_count_total_items($recordClient, $queryBuilder)
    {
        $recordQuery = RecordQuery::createFromNormalized(
            [
                'channel' => null,
                'locale' => null,
                'filters' => [],
                'page' => 0,
                'size' => 3,
            ]
        );

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
                                        'reference_entity_code' => 'packshot',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'track_total_hits' => true,
        ];

        $queryBuilder->buildFromQuery($recordQuery, 'code')
            ->willReturn($esQuery);
        $recordClient->search($esQuery)
            ->willReturn([
                'hits' => [
                    'total' => ['value' => 253],
                    'hits' => [
                        [
                            '_source' => ['code' => 'nice'],
                            'sort' => ['nice'],
                        ],
                        [
                            '_source' => ['code' => 'cool'],
                            'sort' => ['cool'],
                        ],
                        [
                            '_source' => ['code' => 'AWESOME'],
                            'sort' => ['awesome'],
                        ]
                    ]
                ]
            ]);


        $this->count()->shouldReturn(253);
    }
}
