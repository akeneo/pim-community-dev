<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Elasticsearch\IndexConfiguration;

/**
 * This integration tests checks that given an index configuration and some products indexed
 * the text research is consistent.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PimCatalogTextIntegration extends AbstractPimCatalogTestCase
{
    public function testStartWithOperator()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'query_string' => [
                            'default_field' => 'values.name-text.<all_channels>.<all_locales>',
                            'query'         => 'an*',
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_2', 'product_5']);
    }

    public function testStartWithOperatorWithWhiteSpace()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'query_string' => [
                            'default_field'       => 'values.name-text.<all_channels>.<all_locales>',
                            'query'               => 'My\ product*',
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_1']);
    }

    public function testContainsOperator()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'query_string' => [
                            'default_field' => 'values.name-text.<all_channels>.<all_locales>',
                            'query'         => '*Love*',
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_3', 'product_6', 'product_7', 'product_8']);
    }

    public function testContainsOperatorWithWhiteSpace()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'query_string' => [
                            'default_field' => 'values.name-text.<all_channels>.<all_locales>',
                            'query'         => '*Love\\ this*',
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_3', 'product_6']);
    }

    public function testDoesNotContainOperator()
    {
        $query = [
            'query' => [
                'bool' => [
                    'must_not' => [
                        'query_string' => [
                            'default_field' => 'values.name-text.<all_channels>.<all_locales>',
                            'query'         => '*Love*',
                        ],
                    ],
                    'filter' => [
                        'exists' => [
                            'field' => 'values.name-text.<all_channels>.<all_locales>',
                        ]
                    ]
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_1', 'product_2', 'product_5']);
    }

    public function testEqualsOperator()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'term' => [
                            'values.name-text.<all_channels>.<all_locales>' => 'I-love.dots',
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_8']);
    }

    public function testNotEqualsOperator()
    {
        $query = [
            'query' => [
                'bool' => [
                    'must_not' => [
                        'term' => [
                            'values.name-text.<all_channels>.<all_locales>' => 'I-love.dots',
                        ],
                    ],
                    'filter'   => [
                        'exists' => ['field' => 'values.name-text.<all_channels>.<all_locales>'],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument(
            $productsFound,
            ['product_1', 'product_2', 'product_3', 'product_5', 'product_6', 'product_7']
        );
    }

    public function testEmptyOperator()
    {
        $query = [
                'query' => [
                    'bool' => [
                        'must_not' => [
                            'exists' => ['field' => 'values.name-text.<all_channels>.<all_locales>'],
                        ],
                    ],
                ],
            ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_4']);
    }

    public function testNotEmptyOperator()
    {
        $query = [
                'query' => [
                    'bool' => [
                        'filter' => [
                            'exists' => ['field' => 'values.name-text.<all_channels>.<all_locales>'],
                        ],
                    ],
                ],
            ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument(
            $productsFound,
            ['product_1', 'product_2', 'product_3', 'product_5', 'product_6', 'product_7', 'product_8']
        );
    }

    public function testSortAscending()
    {
        $query = [
            'query' => [
                'match_all' => new \stdClass(),
            ],
            'sort'  => [
                [
                    'values.name-text.<all_channels>.<all_locales>' => [
                        'order'   => 'asc',
                        'missing' => '_last',
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument(
            $productsFound,
            ['product_5', 'product_2', 'product_8', 'product_7', 'product_6', 'product_1', 'product_3', 'product_4']
        );
    }

    public function testSortDescending()
    {
        $query = [
            'query' => [
                'match_all' => new \stdClass(),
            ],
            'sort'  => [
                [
                    'values.name-text.<all_channels>.<all_locales>' => [
                        'order'   => 'desc',
                        'missing' => '_last',
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument(
            $productsFound,
            ['product_3', 'product_1', 'product_6', 'product_7', 'product_8', 'product_2', 'product_5', 'product_4']
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function addDocuments()
    {
        $products = [
            [
                'identifier' => 'product_1',
                'values'     => [
                    'name-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'My product',
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_2',
                'values'     => [
                    'name-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'Another product',
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_3',
                'values'     => [
                    'name-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'Yeah, love this name',
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_4',
            ],
            [
                'identifier' => 'product_5',
                'values'     => [
                    'name-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'And an uppercase NAME',
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_6',
                'values'     => [
                    'name-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'Love this product',
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_7',
                'values'     => [
                    'name-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'I.love.dots',
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_8',
                'values'     => [
                    'name-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'I-love.dots',
                        ],
                    ],
                ],
            ],
        ];

        $this->indexDocuments($products);
    }
}
