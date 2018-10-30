<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Elasticsearch\IndexConfiguration;

/**
 * This integration tests checks that given an index configuration with metric data (float and integer values)
 * the metric research is consistent.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PimCatalogMetricIntegration extends AbstractPimCatalogTestCase
{
    public function testLowerThanOperatorWithNumberValue()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'range' => [
                            'values.a_metric-metric.<all_channels>.<all_locales>.base_data' => ['lt' => 10],
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_2', 'product_5', 'product_6']);
    }

    public function testLowerThanOperatorWithStringValue()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'range' => [
                            'values.a_metric-metric.<all_channels>.<all_locales>.base_data' => ['lt' => '10'],
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_2', 'product_5', 'product_6']);
    }

    public function testLowerThanOrEqualsOperatorWithNumberValue()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'range' => [
                            'values.a_metric-metric.<all_channels>.<all_locales>.base_data' => ['lte' => 10],
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_1', 'product_2', 'product_5', 'product_6']);
    }

    public function testLowerThanOrEqualsOperatorWithStringValue()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'range' => [
                            'values.a_metric-metric.<all_channels>.<all_locales>.base_data' => ['lte' => '10'],
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_1', 'product_2', 'product_5', 'product_6']);
    }

    public function testEqualsOperatorWithNumberValue()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'term' => [
                            'values.a_metric-metric.<all_channels>.<all_locales>.base_data' => 100.666,
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_3']);
    }

    public function testEqualsOperatorWithStringValue()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'term' => [
                            'values.a_metric-metric.<all_channels>.<all_locales>.base_data' => '100.666',
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_3']);
    }

    public function testNotEqualsOperatorWithNumberValue()
    {
        $query = [
            'query' => [
                'bool' => [
                    'must_not' => [
                        'term' => [
                            'values.a_metric-metric.<all_channels>.<all_locales>.base_data' => 100.666,
                        ],
                    ],
                    'filter'   => [
                        'exists' => [
                            'field' => 'values.a_metric-metric.<all_channels>.<all_locales>.base_data',
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument(
            $productsFound,
            ['product_1', 'product_2', 'product_4', 'product_5', 'product_6']
        );
    }

    public function testNotEqualsOperatorWithStringValue()
    {
        $query = [
            'query' => [
                'bool' => [
                    'must_not' => [
                        'term' => [
                            'values.a_metric-metric.<all_channels>.<all_locales>.base_data' => '100.666',
                        ],
                    ],
                    'filter'   => [
                        'exists' => [
                            'field' => 'values.a_metric-metric.<all_channels>.<all_locales>.base_data',
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument(
            $productsFound,
            ['product_1', 'product_2', 'product_4', 'product_5', 'product_6']
        );
    }

    public function testGreaterThanOrEqualsOperatorWithNumberValue()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'range' => [
                            'values.a_metric-metric.<all_channels>.<all_locales>.base_data' => ['gte' => 10],
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_1', 'product_3', 'product_4']);
    }

    public function testGreaterThanOrEqualsOperatorWithStringValue()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'range' => [
                            'values.a_metric-metric.<all_channels>.<all_locales>.base_data' => ['gte' => '10'],
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_1', 'product_3', 'product_4']);
    }

    public function testGreaterThanOperatorWithNumberValue()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'range' => [
                            'values.a_metric-metric.<all_channels>.<all_locales>.base_data' => ['gt' => 10],
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_3', 'product_4']);
    }

    public function testGreaterThanOperatorWithStringValue()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'range' => [
                            'values.a_metric-metric.<all_channels>.<all_locales>.base_data' => ['gt' => '10'],
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_3', 'product_4']);
    }

    public function testEmptyOperator()
    {
        $query = [
            'query' => [
                'bool' => [
                    'must_not' => [
                        'exists' => [
                            'field' => 'values.a_metric-metric.<all_channels>.<all_locales>.base_data',
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_7']);
    }

    public function testNotEmptyOperator()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'exists' => [
                            'field' => 'values.a_metric-metric.<all_channels>.<all_locales>.base_data',
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

    public function testSortAscending()
    {
        $query = [
            'query' => [
                'match_all' => new \stdClass(),
            ],
            'sort'  => [
                [
                    'values.a_metric-metric.<all_channels>.<all_locales>.base_data' => [
                        'order'   => 'asc',
                        'missing' => '_last',
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument(
            $productsFound,
            ['product_2', 'product_5', 'product_6', 'product_1', 'product_4', 'product_3', 'product_7']
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
                    'values.a_metric-metric.<all_channels>.<all_locales>.base_data' => [
                        'order'   => 'desc',
                        'missing' => '_last',
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument(
            $productsFound,
            ['product_3', 'product_4', 'product_1', 'product_6', 'product_5', 'product_2', 'product_7']
        );
    }

    /**
     * This method indexes dummy products in elastic search.
     *
     * A few information regarding the mapping of numbers and the data indexed in ES below.
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
                    'a_metric-metric' => [
                        '<all_channels>' => [
                            '<all_locales>' => [
                                'base_data' => '10.0',
                                'data' => '10000',
                                'base_unit' => 'CELSIUS',
                                'unit' => 'GRAM',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_2',
                'values'     => [
                    'a_metric-metric' => [
                        '<all_channels>' => [
                            '<all_locales>' => [
                                'base_data' => -1,
                                'base_unit' => 'KILOGRAM'
                            ]
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_3',
                'values'     => [
                    'a_metric-metric' => [
                        '<all_channels>' => [
                            '<all_locales>' => [
                                'base_data' => '100.666',
                                'data' => '152',
                                'base_unit' => 'KILOGRAM'
                            ]
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_4',
                'values'     => [
                    'a_metric-metric' => [
                        '<all_channels>' => [
                            '<all_locales>' => [
                                'base_data' => '25.89',
                                'base_unit' => 'KILOGRAM'
                            ]
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_5',
                'values'     => [
                    'a_metric-metric' => [
                        '<all_channels>' => [
                            '<all_locales>' => [
                                'base_data' => '-3900',
                                'base_unit'  => 'KILOGRAM',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_6',
                'values'     => [
                    'a_metric-metric' => [
                        '<all_channels>' => [
                            '<all_locales>' =>[
                                'base_data' => '7',
                                'base_unit'  => 'KILOGRAM',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_7',
                'values'     => [],
            ],
        ];

        $this->indexDocuments($products);
    }
}
