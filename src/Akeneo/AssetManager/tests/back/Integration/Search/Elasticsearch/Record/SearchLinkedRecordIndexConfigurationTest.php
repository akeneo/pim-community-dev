<?php
declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Integration\Search\Elasticsearch\Record;

use Akeneo\ReferenceEntity\Integration\SearchIntegrationTestCase;
use PHPUnit\Framework\Assert;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SearchLinkedRecordIndexConfigurationTest extends SearchIntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->loadDataset();
    }

    /**
     * @test
     */
    public function it_finds_records_linked_to_another_record_identifier()
    {
        $query = [
            '_source' => '_id',
            'query'   => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'filter' => [
                                [
                                    'term' => [
                                        'links.record.brand' => 'brand_kartell',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $matchingIdentifiers = $this->searchRecordIndexHelper->executeQuery($query);
        Assert::assertSame(['designer_stark'], $matchingIdentifiers);
    }

    /**
     * @test
     */
    public function it_does_not_find_records_if_it_is_not_linked()
    {
        $query = [
            '_source' => '_id',
            'query'   => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'filter' => [
                                [
                                    'term' => [
                                        'links.record.brand' => 'unknown_record',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $matchingIdentifiers = $this->searchRecordIndexHelper->executeQuery($query);
        Assert::assertSame([], $matchingIdentifiers);
    }

    /**
     * @test
     */
    public function it_finds_all_records_linked_to_a_specific_reference_entity()
    {
        $query = [
            '_source' => '_id',
            'sort'    => ['updated_at' => 'desc'],
            'query'   => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'filter' => [
                                [
                                    'exists' => [
                                        'field' => 'links.record.brand'
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $matchingIdentifiers = $this->searchRecordIndexHelper->executeQuery($query);
        Assert::assertSame(['designer_stark'], $matchingIdentifiers);
    }

    /**
     * @test
     */
    public function it_does_not_find_records_linked_to_a_specific_reference_entity()
    {
        $query = [
            '_source' => '_id',
            'query'   => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'filter' => [
                                [
                                    'exists' => [
                                        'field' => 'links.record.unknown'
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $matchingIdentifiers = $this->searchRecordIndexHelper->executeQuery($query);
        Assert::assertSame([], $matchingIdentifiers);
    }

    /**
     * @test
     */
    public function it_finds_records_linked_to_a_specific_attribute_option()
    {
        $query = [
            '_source' => '_id',
            'query'   => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'filter' => [
                                [
                                    'terms' => [
                                        'links.option.color_brand_fingerprint' => ['blue', 'yellow']
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $matchingIdentifiers = $this->searchRecordIndexHelper->executeQuery($query);
        Assert::assertSame(['designer_stark'], $matchingIdentifiers);
    }

    /**
     * @test
     */
    public function it_does_not_find_records_linked_to_an_attribute_option()
    {
        $query = [
            '_source' => '_id',
            'query'   => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'filter' => [
                                [
                                    'terms' => [
                                        'links.option.color_brand_fingerprint' => ['yellow']
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $matchingIdentifiers = $this->searchRecordIndexHelper->executeQuery($query);
        Assert::assertSame([], $matchingIdentifiers);
    }

    private function loadDataset()
    {
        $from = [
            'reference_entity_code'   => 'designer',
            'identifier'              => 'designer_stark',
            'code'                    => 'stark',
            'updated_at'              => date_create('2018-01-01')->getTimestamp(),
            'links'       => [
                'record' => [
                    'brand' => ['brand_kartell'], // Link to a specific reference entity and a specific record
                ],
                'option' => [
                    'color_brand_fingerprint' => ['red', 'blue'] // link to a specific attribute and a specific attribute option
                ]
            ],
        ];
        $to = [
            'reference_entity_code'   => 'brand',
            'identifier'              => 'brand_kartell',
            'code'                    => 'kartell',
            'updated_at'              => date_create('2018-01-01')->getTimestamp(),
            'links'       => [],
        ];

        $this->searchRecordIndexHelper->index([$from, $to]);
    }
}
