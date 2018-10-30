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
class PimCatalogPriceCollectionIntegration extends AbstractPimCatalogTestCase
{
    public function testLowerThanOperator()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'range' => [
                            'values.a_price-prices.<all_channels>.<all_locales>.USD' => ['lt' => 10],
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_1', 'product_2']);

        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'range' => [
                            'values.a_price-prices.<all_channels>.<all_locales>.EUR' => ['lt' => '10'],
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_3']);
    }

    public function testLowerOrEqualThanOperator()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'range' => [
                            'values.a_price-prices.<all_channels>.<all_locales>.USD' => ['lte' => '10'],
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_1', 'product_2', 'product_4']);

        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'range' => [
                            'values.a_price-prices.<all_channels>.<all_locales>.EUR' => ['lte' => 10],
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_3', 'product_4']);
    }

    public function testEqualsOperator()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'term' => [
                            'values.a_price-prices.<all_channels>.<all_locales>.USD' => '10',
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_4']);
    }

    public function testNotEqualsOperator()
    {
        $query = [
            'query' => [
                'bool' => [
                    'must_not' => [
                        'term' => [
                            'values.a_price-prices.<all_channels>.<all_locales>.USD' => 10,
                        ],
                    ],
                    'filter'   => [
                        'exists' => [
                            'field' => 'values.a_price-prices.<all_channels>.<all_locales>.USD',
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument(
            $productsFound,
            ['product_1', 'product_2', 'product_3', 'product_6']
        );
    }

    public function testGreaterOrEqualThanOperator()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'range' => [
                            'values.a_price-prices.<all_channels>.<all_locales>.USD' => ['gte' => 10],
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_3', 'product_4', 'product_6']);

        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'range' => [
                            'values.a_price-prices.<all_channels>.<all_locales>.EUR' => ['gte' => '10'],
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_1', 'product_2', 'product_4']);
    }

    public function testGreaterThanOperator()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'range' => [
                            'values.a_price-prices.<all_channels>.<all_locales>.USD' => ['gt' => '10'],
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_3', 'product_6']);

        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'range' => [
                            'values.a_price-prices.<all_channels>.<all_locales>.EUR' => ['gt' => 10],
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_1', 'product_2']);
    }

    /**
     * Same as testEmptyOperator test.
     */
    public function testEmptyOnAllCurrenciesOperator()
    {
        $query = [
            'query' => [
                'bool' => [
                    'must_not' => [
                        'exists' => [
                            'field' => 'values.a_price-prices.<all_channels>.<all_locales>',
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_5']);
    }

    public function testEmptyForCurrencyOperator()
    {
        $query = [
            'query' => [
                'bool' => [
                    'must_not' => [
                        'exists' => [
                            'field' => 'values.a_price-prices.<all_channels>.<all_locales>.USD',
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_5', 'product_7']);

        $query = [
            'query' => [
                'bool' => [
                    'must_not' => [
                        'exists' => [
                            'field' => 'values.a_price-prices.<all_channels>.<all_locales>.CNY',
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument(
            $productsFound,
            ['product_1', 'product_2', 'product_3', 'product_4', 'product_5', 'product_6']
        );
    }

    /**
     * Same as testNotEmptyOperator
     */
    public function testNotEmptyOnAtLeastOneCurrencyOperator()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'exists' => [
                            'field' => 'values.a_price-prices.<all_channels>.<all_locales>',
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument(
            $productsFound,
            ['product_1', 'product_2', 'product_3', 'product_4', 'product_6', 'product_7']
        );
    }

    public function testNotEmptyForCurrencyOperator()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'exists' => [
                            'field' => 'values.a_price-prices.<all_channels>.<all_locales>.EUR',
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_1', 'product_2', 'product_3', 'product_4']);

        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'exists' => [
                            'field' => 'values.a_price-prices.<all_channels>.<all_locales>.CNY',
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_7']);
    }

    /**
     * {@inheritdoc}
     *
     * A few information regarding the mapping of prices and the data indexed in ES below.
     *
     * We indexed data of different types:
     *  - integer as a php integer
     *  - integer as a php string
     *  - float as a php float
     *  - float as a php string
     *
     * What we want to test is that our ES queries are still correctly working despite those variations (eg, the
     * resilience of the ES indexing).
     *
     * In this precise case, some data might not be catched by our dynamic mapping, but ES is capable of casting them
     * and the queries are still working.
     */
    protected function addDocuments()
    {
        $products = [
            [
                'identifier' => 'product_1',
                'values'     => [
                    'a_price-prices' => [
                        '<all_channels>' => [
                            '<all_locales>' => [
                                'USD' => 5,
                                'EUR' => '15.55',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_2',
                'values'     => [
                    'a_price-prices' => [
                        '<all_channels>' => [
                            '<all_locales>' => [
                                'USD' => '5',
                                'EUR' => '15.55',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_3',
                'values'     => [
                    'a_price-prices' => [
                        '<all_channels>' => [
                            '<all_locales>' => [
                                'USD' => '16',
                                'EUR' => 6.60,
                            ],
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_4',
                'values'     => [
                    'a_price-prices' => [
                        '<all_channels>' => [
                            '<all_locales>' => [
                                'USD' => '10',
                                'EUR' => 10,
                            ],
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_5',
            ],
            [
                'identifier' => 'product_6',
                'values'     => [
                    'a_price-prices' => [
                        '<all_channels>' => [
                            '<all_locales>' => [
                                'USD' => '150',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_7',
                'values'     => [
                    'a_price-prices' => [
                        '<all_channels>' => [
                            '<all_locales>' => [
                                'CNY' => 150,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->indexDocuments($products);
    }
}
