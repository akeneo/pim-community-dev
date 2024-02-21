<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Elasticsearch\IndexConfiguration;

class PimCatalogCompletenessIntegration extends AbstractPimCatalogTestCase
{
    public function testQueryCompleteOrIncompleteProductModel()
    {
        $this->assertDocument(
            $this->executeProductModelQuery('all_incomplete', 'ecommerce', 'en_US'),
            ['document_1', 'document_2']
        );

        $this->assertDocument(
            $this->executeProductModelQuery('all_incomplete', 'ecommerce', 'fr_FR'),
            ['document_2']
        );

        $this->assertDocument(
            $this->executeProductModelQuery('all_complete', 'ecommerce', 'en_US'),
            ['document_1']
        );

        $this->assertDocument(
            $this->executeProductModelQuery('all_complete', 'ecommerce', 'fr_FR'),
            []
        );
    }

    public function testQueryCompleteProductModelOrCompleteProduct()
    {
        $query = [
            'query' => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'filter' => [
                                [
                                    'bool' => [
                                        'should' => [
                                            ['term' => ['completeness.ecommerce.en_US' => 100]],
                                            ['term' => ['all_incomplete.ecommerce.en_US' => 0]],
                                        ],
                                        'minimum_should_match' => 1,
                                    ],
                                ],
                            ],
                        ],
                    ]
                ]
            ],
        ];

        $result = $this->getSearchQueryResults($query);

        $this->assertDocument(['document_1', 'document_2', 'document_3'], $result);
    }

    public function testQueryIncompleteProductModelOrIncompleteProduct()
    {
        $query = [
            'query' => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'filter' => [
                                [
                                    'bool' => [
                                        'should' => [
                                            ['range' => ['completeness.ecommerce.fr_FR' => ['lt' => 100]]],
                                            ['term' => ['all_complete.ecommerce.fr_FR' => 0]],
                                        ],
                                        'minimum_should_match' => 1,
                                    ],
                                ],
                            ],
                        ],
                    ]
                ]
            ],
        ];

        $result = $this->getSearchQueryResults($query);

        $this->assertDocument(['document_3'], $result);
    }

    /**
     * {@inheritdoc}
     */
    protected function addDocuments()
    {
        $products = [
            [
                'identifier' => 'document_1',
                'all_incomplete' => [
                    'ecommerce' => [
                        'en_US' => 0,
                        'fr_FR' => 1
                    ],
                ],
                'all_complete' => [
                    'ecommerce' => [
                        'en_US' => 0,
                        'fr_FR' => 1
                    ],
                ],
            ],
            [
                'identifier' => 'document_2',
                'all_incomplete' => [
                    'ecommerce' => [
                        'en_US' => 0,
                        'fr_FR' => 0,
                    ],
                ],
                'all_complete' => [
                    'ecommerce' => [
                        'en_US' => 1,
                        'fr_FR' => 1,
                    ],
                ],
            ],
            [
                'identifier' => 'document_3',
                'completeness' => [
                    'ecommerce' => [
                        'en_US' => 100,
                        'fr_FR' => 12,
                    ],
                ],
            ],
            [
                'identifier' => 'document_4',
            ],
        ];

        $this->indexDocuments($products);
    }

    /**
     * @param string $type
     * @param string $channel
     * @param string $locale
     *
     * @return array
     */
    private function executeProductModelQuery(string $type, string $channel, string $locale): array
    {
        $fieldName = sprintf('%s.%s.%s', $type, $channel, $locale);

        $query = [
            'query' => [
                'constant_score' => [
                    'filter' => [
                        'term' => [
                            $fieldName => 0,
                        ],
                    ],
                ],
            ],
        ];

        return $this->getSearchQueryResults($query);
    }
}
