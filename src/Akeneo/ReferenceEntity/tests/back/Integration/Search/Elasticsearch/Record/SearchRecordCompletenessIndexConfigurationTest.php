<?php
declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Integration\Search\Elasticsearch\Record;

use Akeneo\ReferenceEntity\Integration\SearchIntegrationTestCase;
use PHPUnit\Framework\Assert;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SearchRecordCompletenessIndexConfigurationTest extends SearchIntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->loadDataset();
    }

    /**
     * @test
     */
    public function it_finds_the_complete_document_based_on_the_complete_value_keys_field()
    {
        $query = [
            '_source' => '_id',
            'sort' => ['updated_at' => 'desc'],
            'query' => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'filter' => [
                                [
                                    'exists' => [
                                        'field' => 'complete_value_keys.description_brand_fingerprint_ecommerce_en_US',
                                    ],
                                ],
                                [
                                    'exists' => [
                                        'field' => 'complete_value_keys.website_brand_fingerprint_ecommerce_en_US',
                                    ],
                                ],
                                [
                                    'exists' => [
                                        'field' => 'complete_value_keys.number_brand_fingerprint_ecommerce_en_US',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $matchingIdentifiers = $this->searchRecordIndexHelper->executeQuery($query);
        Assert::assertSame(['brand_kartell'], $matchingIdentifiers);
    }

    /**
     * @test
     */
    public function it_finds_multiple_documents_based_on_the_complete_value_keys_field()
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
                                        'field' => 'complete_value_keys.description_brand_fingerprint_ecommerce_en_US',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $matchingIdentifiers = $this->searchRecordIndexHelper->executeQuery($query);
        Assert::assertSame(['brand_kartell', 'brand_ikea'], $matchingIdentifiers);
    }

    private function loadDataset()
    {
        $complete = [
            'reference_entity_code'   => 'brand',
            'identifier'              => 'brand_kartell',
            'code'                    => 'kartell',
            'record_full_text_search' => [],
            'updated_at'              => date_create('2018-01-01')->getTimestamp(),
            'complete_value_keys' => [
                'description_brand_fingerprint_ecommerce_en_US' => true,
                'website_brand_fingerprint_ecommerce_en_US'     => true,
                'number_brand_fingerprint_ecommerce_en_US'      => true,
            ],
        ];

        $incomplete = [
            'reference_entity_code'   => 'brand',
            'identifier'              => 'brand_ikea',
            'code'                    => 'ikea',
            'record_full_text_search' => [],
            'updated_at'              => date_create('2018-01-01')->getTimestamp(),
            'complete_value_keys'                  => [
                'description_brand_fingerprint_ecommerce_en_US' => true,
                'number_brand_fingerprint_ecommerce_en_US'      => true,
            ],
        ];

        $this->searchRecordIndexHelper->index([$complete, $incomplete]);
    }
}
