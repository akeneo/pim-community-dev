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
    public function setUp()
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
            'sort'    => ['updated_at' => 'desc'],
            'query'   => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'filter' => [
                                [
                                    'term' => [
                                        'linked_to_records' => 'brand_kartell',
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
            'sort'    => ['updated_at' => 'desc'],
            'query'   => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'filter' => [
                                [
                                    'term' => [
                                        'linked_to_records' => 'unknown_record',
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
            'linked_to_records'       => ['brand_kartell'],
        ];
        $to = [
            'reference_entity_code'   => 'brand',
            'identifier'              => 'brand_kartell',
            'code'                    => 'kartell',
            'updated_at'              => date_create('2018-01-01')->getTimestamp(),
            'linked_to_records'       => [],
        ];

        $this->searchRecordIndexHelper->index([$from, $to]);
    }
}
